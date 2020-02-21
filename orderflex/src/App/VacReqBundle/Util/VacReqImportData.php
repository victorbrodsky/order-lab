<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/27/2016
 * Time: 11:35 AM
 */

namespace App\VacReqBundle\Util;


use App\UserdirectoryBundle\Util\UserUtil;
use App\VacReqBundle\Entity\VacReqRequest;
use App\VacReqBundle\Entity\VacReqRequestBusiness;
use App\VacReqBundle\Entity\VacReqRequestVacation;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

class VacReqImportData
{

    protected $em;
    protected $container;

    private $usernamePrefix = 'ldap-user';

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {

        $this->em = $em;
        $this->container = $container;

    }

    //REQUEST_STATUS_ID
    //BUS_REQUEST_STATUS_ID
    //VAC_REQUEST_STATUS_ID

    //PFVBTR_REQUEST_STATUS_SEL
    //1	pending
    //2	approved
    //3	rejected
    //4	completed
    //5	closed


    // url: /import-old-data/
    public function importOldData() {

        ini_set('max_execution_time', 3600); //3600 seconds = 60 minutes

        $logger = $this->container->get('logger');
        $em = $this->em;

        //$email = "oli2002@med.cornell.edu";
        $requests = array();

        $default_time_zone = $this->container->getParameter('default_time_zone');
        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);

        ////////////// add system user /////////////////
        $userUtil = new UserUtil();
        $systemuser = $userUtil->createSystemUser($this->em,$userkeytype,$default_time_zone);
        ////////////// end of add system user /////////////////

        //VacReqAvailabilityList
//        $emailAvailable = $this->em->getRepository('AppVacReqBundle:VacReqAvailabilityList')->findOneByAbbreviation('email');
//        $phoneAvailable = $this->em->getRepository('AppVacReqBundle:VacReqAvailabilityList')->findOneByAbbreviation('phone');
//        $otherAvailable = $this->em->getRepository('AppVacReqBundle:VacReqAvailabilityList')->findOneByAbbreviation('other');
//        $noneAvailable = $this->em->getRepository('AppVacReqBundle:VacReqAvailabilityList')->findOneByAbbreviation('none');
        $requestType = $this->em->getRepository('AppVacReqBundle:VacReqRequestTypeList')->findOneByAbbreviation("business-vacation");
        if( !$requestType ) {
            exit('No request type found with abbreviation "business-vacation"');
        }

        $notExistingUsers = array();
        $count = 0;

        $inputFileName = __DIR__ . '/vacreqExportData_full_before_27May2016.xls';

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( Exception $e ) {
            $error = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);


        //for each request in excel (start at row 2)
        for( $row = 2; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //Insert row data array into the database
            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            $exportId = $this->getValueByHeaderName('FACULTY_REQUEST_ID', $rowData, $headers);
            $exportId = trim($exportId);
            //echo "exportId=".$exportId."<br>";

            //if( $exportId != 1840 ) {continue;} //testing

            $request = $this->em->getRepository('AppVacReqBundle:VacReqRequest')->findOneByExportId($exportId);
            if( $request ) {
                continue; //ignore existing request to prevent overwrite
            }

            //$FACULTY_NAME = $this->getValueByHeaderName('FACULTY_NAME', $rowData, $headers);

            $email = $this->getValueByHeaderName('FACULTY_EMAIL', $rowData, $headers);
            //echo "email=".$email."<br>";
            if( !$email ) {
                //continue; //ignore existing request to prevent overwrite
                $error = 'Email not found for exportId='.$exportId;
                $logger->error($error);
                throw new \Exception( $error );
            }

            $emailParts = explode("@", $email);
            $cwid = $emailParts[0];

            //exceptions for some users

            //echo "cwid=".$cwid."<br>";

            $BUS_FIRST_DAY_AWAY = $this->getValueByHeaderName('BUS_FIRST_DAY_AWAY', $rowData, $headers); //24-OCT-12
            $BUS_FIRST_DAY_AWAY_Date = $this->transformDatestrToDate($BUS_FIRST_DAY_AWAY);
            $VAC_FIRST_DAY_AWAY = $this->getValueByHeaderName('VAC_FIRST_DAY_AWAY', $rowData, $headers);
            $VAC_FIRST_DAY_AWAY_Date = $this->transformDatestrToDate($VAC_FIRST_DAY_AWAY);

            $username = $cwid."_@_". $this->usernamePrefix;
            $submitter = $this->em->getRepository('AppUserdirectoryBundle:User')->findOneByUsername($username);
            if( !$submitter ) {

                //get newest date
                $newestDate = $this->getNewestDate($BUS_FIRST_DAY_AWAY_Date,$VAC_FIRST_DAY_AWAY_Date);
                //echo "submitter not found; newest date=".$this->convertDateTimeToStr($newestDate)."<br>";

                if( array_key_exists($email, $notExistingUsers) ) {
                    $existingNewestDate = $notExistingUsers[$email];
                    $notExistingUsers[$email] = $this->getNewestDate($existingNewestDate,$newestDate);
                } else {
                    $notExistingUsers[$email] = $this->getNewestDate($BUS_FIRST_DAY_AWAY_Date,$VAC_FIRST_DAY_AWAY_Date);
                }

                continue;
                //throw new \Exception( 'Submitter not found for exportId='.$exportId." by username=" . $username );
            }

            $request = new VacReqRequest($submitter);

            //set request type
            $request->setRequestType($requestType);

            $request->setExportId($exportId);

            //set emergency
            //EMERGENCY_EMAIL
            $EMERGENCY_EMAIL = $this->getValueByHeaderName('EMERGENCY_EMAIL', $rowData, $headers);
            if( $EMERGENCY_EMAIL ) {
                $request->setAvailableViaEmail(true);
                //FACULTY_EMAIL
                $FACULTY_EMAIL = $this->getValueByHeaderName('FACULTY_EMAIL', $rowData, $headers);
                $request->setAvailableEmail($FACULTY_EMAIL);
                //$request->addAvailability($emailAvailable);
            }
            //EMERGENCY_PHONE
            $EMERGENCY_PHONE = $this->getValueByHeaderName('EMERGENCY_PHONE', $rowData, $headers);
            if( $EMERGENCY_PHONE ) {
                $request->setAvailableCellPhone(true);
                //CELL_PHONE
                $CELL_PHONE = $this->getValueByHeaderName('CELL_PHONE', $rowData, $headers);
                $request->setAvailableCellPhone($CELL_PHONE);
            }
            //EMERGENCY_OTHER
            $EMERGENCY_OTHER = $this->getValueByHeaderName('EMERGENCY_OTHER', $rowData, $headers);
            if( $EMERGENCY_OTHER ) {
                $request->setAvailableViaOther(true);
                //OTHER
                $OTHER = $this->getValueByHeaderName('OTHER', $rowData, $headers);
                $request->setAvailableOther($OTHER);
            }
            //NOT_ACCESSIBLE
            $NOT_ACCESSIBLE = $this->getValueByHeaderName('NOT_ACCESSIBLE', $rowData, $headers);
            if( $NOT_ACCESSIBLE ) {
                $request->setAvailableNone(true);
            }

            //FACULTY_PHONE
            $FACULTY_PHONE = $this->getValueByHeaderName('FACULTY_PHONE', $rowData, $headers);
            if( $FACULTY_PHONE ) {
                $request->setPhone($FACULTY_PHONE);
            }

            //BUSINESS_REQUEST
            $BUSINESS_REQUEST = $this->getValueByHeaderName('BUSINESS_REQUEST', $rowData, $headers);
            if( $BUSINESS_REQUEST ) {
                //BUS_FIRST_DAY_AWAY
                if( $BUS_FIRST_DAY_AWAY_Date ) {
                    $requestBusiness = new VacReqRequestBusiness();
                    $request->setRequestBusiness($requestBusiness);

                    $requestBusiness->setStartDate($BUS_FIRST_DAY_AWAY_Date);
                    //BUS_LAST_DAY_AWAY
                    $BUS_LAST_DAY_AWAY = $this->getValueByHeaderName('BUS_LAST_DAY_AWAY', $rowData, $headers); //24-OCT-12
                    $BUS_LAST_DAY_AWAY_Date = $this->transformDatestrToDate($BUS_LAST_DAY_AWAY);
                    $requestBusiness->setEndDate($BUS_LAST_DAY_AWAY_Date);

                    //NUM_DAYS_OFFSITE
                    $NUM_DAYS_OFFSITE = $this->getValueByHeaderName('NUM_DAYS_OFFSITE', $rowData, $headers);
                    $requestBusiness->setNumberOfDays($NUM_DAYS_OFFSITE);

                    //ESTIMATED_EXPRESSES
                    $ESTIMATED_EXPRESSES = $this->getValueByHeaderName('ESTIMATED_EXPRESSES', $rowData, $headers);
                    $requestBusiness->setExpenses($ESTIMATED_EXPRESSES);

                    //TRIP_PAID_BY_OUTSIDE
                    $TRIP_PAID_BY_OUTSIDE = $this->getValueByHeaderName('TRIP_PAID_BY_OUTSIDE', $rowData, $headers);
                    if( $TRIP_PAID_BY_OUTSIDE ) {
                        $requestBusiness->setPaidByOutsideOrganization(true);
                    }

                    //DESCRIPTION
                    $DESCRIPTION = $this->getValueByHeaderName('DESCRIPTION', $rowData, $headers);
                    if( $DESCRIPTION ) {
                        $requestBusiness->setDescription($DESCRIPTION);
                    }

                    //BUS_REQUEST_STATUS_ID
                    $BUS_REQUEST_STATUS_ID = $this->getValueByHeaderName('BUS_REQUEST_STATUS_ID', $rowData, $headers);
                    $BUS_REQUEST_STATUS = $this->statusMapper($BUS_REQUEST_STATUS_ID);
                    if( $BUS_REQUEST_STATUS ) {
                        $requestBusiness->setStatus($BUS_REQUEST_STATUS);
                    }
                }
            }

            //VACATION_REQUEST
            $VACATION_REQUEST = $this->getValueByHeaderName('VACATION_REQUEST', $rowData, $headers);
            if( $VACATION_REQUEST ) {

                //VAC_FIRST_DAY_AWAY
                if( $VAC_FIRST_DAY_AWAY_Date ) {
                    $requestVacation = new VacReqRequestVacation();
                    $request->setRequestVacation($requestVacation);

                    $requestVacation->setStartDate($VAC_FIRST_DAY_AWAY_Date);
                    //VAC_LAST_DAY_AWAY
                    $VAC_LAST_DAY_AWAY = $this->getValueByHeaderName('VAC_LAST_DAY_AWAY', $rowData, $headers); //24-OCT-12
                    $VAC_LAST_DAY_AWAY_Date = $this->transformDatestrToDate($VAC_LAST_DAY_AWAY);
                    $requestVacation->setEndDate($VAC_LAST_DAY_AWAY_Date);

                    //VAC_DAYS_REQUESTED
                    $VAC_DAYS_REQUESTED = $this->getValueByHeaderName('VAC_DAYS_REQUESTED', $rowData, $headers);
                    $requestVacation->setNumberOfDays($VAC_DAYS_REQUESTED);

//                    //FIRST_BACK_OFFICE
//                    $FIRST_BACK_OFFICE = $this->getValueByHeaderName('FIRST_BACK_OFFICE', $rowData, $headers); //24-OCT-12
//                    $FIRST_BACK_OFFICE_Date = $this->transformDatestrToDate($FIRST_BACK_OFFICE);
//                    $requestVacation->setFirstDayBackInOffice($FIRST_BACK_OFFICE_Date);

                    //VAC_REQUEST_STATUS_ID
                    $VAC_REQUEST_STATUS_ID = $this->getValueByHeaderName('VAC_REQUEST_STATUS_ID', $rowData, $headers);
                    $VAC_REQUEST_STATUS = $this->statusMapper($VAC_REQUEST_STATUS_ID);
                    if( $VAC_REQUEST_STATUS ) {
                        $requestVacation->setStatus($VAC_REQUEST_STATUS);
                    }

                }

            }

            //APPROVER_ID
            $APPROVER_ID = $this->getValueByHeaderName('APPROVER_ID', $rowData, $headers);
            $approver = $this->getApproverByUserId($APPROVER_ID);
            if( $approver ) {

                $request->setApprover($approver);

                //DATE_APPROVED_REJECTED
                $DATE_APPROVED_REJECTED = $this->getValueByHeaderName('DATE_APPROVED_REJECTED', $rowData, $headers);
                $DATE_APPROVED_REJECTED_Date = $this->transformDatestrToDate($DATE_APPROVED_REJECTED);
                $request->setApprovedRejectDate($DATE_APPROVED_REJECTED_Date);

                //set organizational group
                $institution = null;
                $roles = $em->getRepository('AppUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($approver,"vacreq","ROLE_VACREQ_APPROVER");
                if( count($roles) > 0 ) {
                    $role = $roles[0];
                    //$note = 'ROLE_VACREQ_APPROVER role='.$role;
                    //$logger->notice($note);
                    $institution = $role->getInstitution();
                } else {
                    $error = 'ROLE_VACREQ_APPROVER not found for approver='.$approver;
                    $logger->error($error);
                }
                if( $institution ) {
                    $request->setInstitution($institution);

                    //assign submitter organizational group the same as approver
                    $roles = $em->getRepository('AppUserdirectoryBundle:User')->findRolesBySiteAndPartialRoleName("vacreq","ROLE_VACREQ_SUBMITTER",$institution);
                    if( count($roles) > 0 ) {
                        $role = $roles[0];
                        $roleName = $role->getName();
                        $submitter->addRole($roleName);
                        //exit($submitter.': added role '.$roleName);
                    } else {
                        $error = 'Submitter roles not found for exportId='.$exportId . "; APPROVER_ID=".$APPROVER_ID;
                        $logger->error($error);
                        throw new \Exception( $error );
                    }

                } else {
                    $error = 'Organizational group not found for exportId='.$exportId . "; APPROVER_ID=".$APPROVER_ID;
                    $logger->error($error);
                    throw new \Exception( $error );
                }

            } else {
                $error = 'Approver not found for exportId='.$exportId . "; APPROVER_ID=".$APPROVER_ID;
                $logger->error($error);
                throw new \Exception( $error );
            }

            //DATE_REQUESTED
            $DATE_REQUESTED = $this->getValueByHeaderName('DATE_REQUESTED', $rowData, $headers);
            $DATE_REQUESTED_Date = $this->transformDatestrToDate($DATE_REQUESTED);
            $request->setCreateDate($DATE_REQUESTED_Date);

            // Not used, but existing fields in the old site
            //REQUEST_STATUS_ID
            $REQUEST_STATUS_ID = $this->getValueByHeaderName('REQUEST_STATUS_ID', $rowData, $headers);
            $REQUEST_STATUS = $this->statusMapper($REQUEST_STATUS_ID);
            if( $REQUEST_STATUS ) {
                $request->setStatus($REQUEST_STATUS);
            }

            //FINAL_FIRST_DAY_AWAY
            $FINAL_FIRST_DAY_AWAY = $this->getValueByHeaderName('FINAL_FIRST_DAY_AWAY', $rowData, $headers); //24-OCT-12
            $FINAL_FIRST_DAY_AWAY_Date = $this->transformDatestrToDate($FINAL_FIRST_DAY_AWAY);
            $request->setFirstDayAway($FINAL_FIRST_DAY_AWAY_Date);

            //FINAL_FIRST_DAY_BACK
            $FINAL_FIRST_DAY_BACK = $this->getValueByHeaderName('FINAL_FIRST_DAY_BACK', $rowData, $headers); //24-OCT-12
            $FINAL_FIRST_DAY_BACK_Date = $this->transformDatestrToDate($FINAL_FIRST_DAY_BACK);
            $request->setFirstDayBackInOffice($FINAL_FIRST_DAY_BACK_Date);

            //COMMENTS
            $COMMENTS = $this->getValueByHeaderName('COMMENTS', $rowData, $headers);
            $request->setComment($COMMENTS);

            //UPDATE_COMMENTS
            $UPDATE_COMMENTS = $this->getValueByHeaderName('UPDATE_COMMENTS', $rowData, $headers);
            $request->setUpdateComment($UPDATE_COMMENTS);


            $em->persist($request);
            $em->flush();

//            if( $VACATION_REQUEST && $BUSINESS_REQUEST ) {
//                exit('finished exportId=' . $exportId);
//            }

            $count++;

            //echo "<br>";

        }//for each request

        //echo "finished looping<br><br>";

        //process not existing users
        //print_r($notExistingUsers);
        //echo "not existing users = ".count($notExistingUsers)."<br>";
        foreach( $notExistingUsers as $email=>$newestDate ) {
            $warning = "not existing user email=".$email."; newestDate=".$this->convertDateTimeToStr($newestDate);
            //echo $warning."<br>";
            $logger->warning($warning);
        }

        //exit('1');

        $result = "Imported requests = " . $count;
        return $result;
    }


    public function getNewestDate($date1,$date2) {
        if( $date1 && !$date2 ) {
            return $date1;
        }

        if( !$date1 && $date2 ) {
            return $date2;
        }

        if( $date1 && $date2 ) {
            //if( strtotime($date1) > strtotime($date2) ) {
            if( $date1 > $date2 ) {
                return $date1;
            } else {
                return $date2;
            }
        }

        return null;
    }

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }

    public function getValueByHeaderName($header, $row, $headers) {

        $res = null;

        if( !$header ) {
            return $res;
        }

        //echo "header=".$header."<br>";
        //print_r($headers);
        //print_r($row[0]);

        //echo "cwid=(".$headers[0][39].")<br>";

        $key = array_search($header, $headers[0]);
        //echo "<br>key=".$key."<br>";

        if( $key === false ) {
            //echo "key is false !!!!!!!!!!<br>";
            return $res;
        }

        if( array_key_exists($key, $row[0]) ) {
            $res = $row[0][$key];
        }

        //echo "res=".$res."<br>";
        return $res;
    }

    public function transformDatestrToDate($datestr)
    {
        $userSecUtil = $this->container->get('user_security_utility');
        $date = $userSecUtil->transformDatestrToDateWithSiteEventLog($datestr, $this->container->getParameter('vacreq.sitename'));
//        if( $date ) {
//            $date->setTimezone(new \DateTimeZone("UTC"));
//            //echo "ok<br>";
//        } else {
//            //exit("date object is null for datestr=".$datestr);
//        }
        return $date;
    }

    public function getApproverByUserId($userId) {
        $cwid = $this->userMapper($userId);
        $username = $cwid."_@_". $this->usernamePrefix;
        $approver = $this->em->getRepository('AppUserdirectoryBundle:User')->findOneByUsername($username);
        if( !$approver ) {
            $logger = $this->container->get('logger');
            $logger->error("Can not find user by username=".$username);
            //echo "Can not find user by username=".$username."<br>";
        }
        return $approver;
    }
    public function userMapper( $userId ) {

        $cwid = null;

        switch( $userId ){
            case "1":
                $cwid = "cwid";
                break;
            case "2":
                $cwid = "cwid";
                break;
            case "3":
                $cwid = "cwid";
                break;
            case "4":
                $cwid = "cwid";
                break;
            case "5":
                $cwid = "cwid";
                break;
            case "6":
                $cwid = "cwid";
                break;
            case "19":
                $cwid = "cwid";
                break;
            case "20":
                $cwid = "cwid";
                break;
        }

        return $cwid;
    }

    public function statusMapper( $id ) {

        //PFVBTR_REQUEST_STATUS_SEL
        //1	pending
        //2	approved
        //3	rejected
        //4	completed
        //5	closed

        $status = null;

        switch( $id ){
            case "1":
                $status = "pending";
                break;
            case "2":
                $status = "approved";
                break;
            case "3":
                $status = "rejected";
                break;
            case "4":
                $status = "completed";
                break;
            case "5":
                $status = "closed";
                break;
        }

        return $status;
    }





}