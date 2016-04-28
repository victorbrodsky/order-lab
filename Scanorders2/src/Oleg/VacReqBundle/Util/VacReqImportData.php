<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/27/2016
 * Time: 11:35 AM
 */

namespace Oleg\VacReqBundle\Util;


use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\VacReqBundle\Entity\VacReqRequest;
use Oleg\VacReqBundle\Entity\VacReqRequestBusiness;
use Oleg\VacReqBundle\Entity\VacReqRequestVacation;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

class VacReqImportData
{

    protected $em;
    protected $sc;
    protected $container;

    private $usernamePrefix = 'wcmc-cwid';

    public function __construct( $em, $sc, $container ) {

        $this->em = $em;
        $this->sc = $sc;
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

    //PFVBTR_APPROVER_INFO
    //1	Barry Sleckman	bas2022@med.cornell.edu	Sara Lynch	sal2026@med.cornell.edu	Experimental Pathology	approver	yes
    //2	Dr. Jacob Rand	jar9135@med.cornell.edu	Sara 	sal2026@med.cornell.edu	Clinical Pathology	approver	yes
    //3	Attilio Orazi	ato9002@med.cornell.edu	Melissa Honore 	meh9043@nyp.org	Hematopathology	approver	yes
    //4	Alain Borczuk 	alb9003@med.cornell.edu	Rashida Eteng	rse9005@nyp.org	Surgical Pathology	approver	yes
    //5	Rana Hoda	rhoda@med.cornell.edu	Maxine Stevenson	mes9008@nyp.org	Cytopathology	approver	yes
    //20	Daniel M. Knowles	dknowles@med.cornell.edu	Sara Lynch	sal2026@med.cornell.edu	Executive Committee	executive	yes
    //6	Timothy Hla	tih2002@med.cornell.edu	Mario A. Castro Martinez	mcm2010@med.cornell.edu	Vascular Biology	approver	yes
    //19	Cynthia Magro				Dermatopathology	approver	no

    // url: /import-old-data/
    public function importOldData() {

        ini_set('max_execution_time', 900); //900 seconds = 15 minutes

        $logger = $this->container->get('logger');
        $email = "oli2002@med.cornell.edu";
        $requests = array();

        $default_time_zone = $this->container->getParameter('default_time_zone');
        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);

        ////////////// add system user /////////////////
        $userUtil = new UserUtil();
        $systemuser = $userUtil->createSystemUser($this->em,$userkeytype,$default_time_zone);
        ////////////// end of add system user /////////////////

        //VacReqAvailabilityList
        $emailAvailable = $this->em->getRepository('OlegVacReqBundle:VacReqAvailabilityList')->findOneByAbbreviation('email');
        $phoneAvailable = $this->em->getRepository('OlegVacReqBundle:VacReqAvailabilityList')->findOneByAbbreviation('phone');
        $otherAvailable = $this->em->getRepository('OlegVacReqBundle:VacReqAvailabilityList')->findOneByAbbreviation('other');
        $noneAvailable = $this->em->getRepository('OlegVacReqBundle:VacReqAvailabilityList')->findOneByAbbreviation('none');

        $notExistingUsers = array();

        $inputFileName = __DIR__ . '/vacreqExportData.xls';

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
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
//            echo $row.": ";
//            var_dump($rowData);
//            echo "<br>";

            $exportId = $this->getValueByHeaderName('FACULTY_REQUEST_ID', $rowData, $headers);
            echo "exportId=".$exportId."<br>";

            $requestDb = $this->em->getRepository('OlegVacReqBundle:VacReqRequest')->findOneByExportId( trim($exportId) );
            if( $requestDb ) {
                continue; //ignore existing request to prevent overwrite
            }

            $email = $this->getValueByHeaderName('FACULTY_EMAIL', $rowData, $headers);
            echo "email=".$email."<br>";
            if( !$email ) {
                //continue; //ignore existing request to prevent overwrite
                $error = 'Email not found for exportId='.$exportId;
                $logger->error($error);
                throw new \Exception( $error );
            }

            $emailParts = explode("@", $email);
            $cwid = $emailParts[0];
            echo "cwid=".$cwid."<br>";

            $BUS_FIRST_DAY_AWAY = $this->getValueByHeaderName('BUS_FIRST_DAY_AWAY', $rowData, $headers); //24-OCT-12
            $BUS_FIRST_DAY_AWAY_Date = $this->transformDatestrToDate($BUS_FIRST_DAY_AWAY);
            $VAC_FIRST_DAY_AWAY = $this->getValueByHeaderName('VAC_FIRST_DAY_AWAY', $rowData, $headers);
            $VAC_FIRST_DAY_AWAY_Date = $this->transformDatestrToDate($VAC_FIRST_DAY_AWAY);

            $username = $cwid."_@_". $this->usernamePrefix;
            $submitter = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($username);
            if( !$submitter ) {

                //get newest date
                $newestDate = $this->getNewestDate($BUS_FIRST_DAY_AWAY_Date,$VAC_FIRST_DAY_AWAY_Date);
                echo "submitter not found; newest date=".$this->convertDateTimeToStr($newestDate)."<br>";

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

            //set emergency
            //EMERGENCY_EMAIL
            $EMERGENCY_EMAIL = $this->getValueByHeaderName('EMERGENCY_EMAIL', $rowData, $headers);
            if( $EMERGENCY_EMAIL ) {
                $request->addAvailability($emailAvailable);
            }
            //EMERGENCY_PHONE
            $EMERGENCY_PHONE = $this->getValueByHeaderName('EMERGENCY_PHONE', $rowData, $headers);
            if( $EMERGENCY_PHONE ) {
                $request->addAvailability($phoneAvailable);
                //CELL_PHONE
                $CELL_PHONE = $this->getValueByHeaderName('CELL_PHONE', $rowData, $headers);
                $request->addEmergencyComment("Cell Phone: ".$CELL_PHONE);
            }
            //EMERGENCY_OTHER
            $EMERGENCY_OTHER = $this->getValueByHeaderName('EMERGENCY_OTHER', $rowData, $headers);
            if( $EMERGENCY_OTHER ) {
                $request->addAvailability($otherAvailable);
                //OTHER
                $OTHER = $this->getValueByHeaderName('OTHER', $rowData, $headers);
                $request->addEmergencyComment("Other: ".$OTHER);
            }
            //NOT_ACCESSIBLE
            $NOT_ACCESSIBLE = $this->getValueByHeaderName('NOT_ACCESSIBLE', $rowData, $headers);
            if( $NOT_ACCESSIBLE ) {
                $request->addAvailability($noneAvailable);
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
                        $requestBusiness->setPaidByOutsideOrganization($TRIP_PAID_BY_OUTSIDE);
                    }

                    //DESCRIPTION
                    $DESCRIPTION = $this->getValueByHeaderName('DESCRIPTION', $rowData, $headers);
                    if( $DESCRIPTION ) {
                        $requestBusiness->setDescription($DESCRIPTION);
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
                    $request->setNumberOfDays($VAC_DAYS_REQUESTED);

                    //FIRST_BACK_OFFICE
                    $FIRST_BACK_OFFICE = $this->getValueByHeaderName('FIRST_BACK_OFFICE', $rowData, $headers); //24-OCT-12
                    $FIRST_BACK_OFFICE_Date = $this->transformDatestrToDate($FIRST_BACK_OFFICE);
                    $requestVacation->setFirstDayBackInOffice($FIRST_BACK_OFFICE_Date);

                }

            }

            //FINAL_FIRST_DAY_AWAY
            //FINAL_FIRST_DAY_BACK

            //APPROVER_ID
            $APPROVER_ID = $this->getValueByHeaderName('APPROVER_ID', $rowData, $headers);
            $approver = $this->getApproverByUserId($APPROVER_ID);
            if( $approver ) {
                $request->setApprover($approver);
                //DATE_APPROVED_REJECTED
                $DATE_APPROVED_REJECTED = $this->getValueByHeaderName('DATE_APPROVED_REJECTED', $rowData, $headers);
                $DATE_APPROVED_REJECTED_Date = $this->transformDatestrToDate($DATE_APPROVED_REJECTED);
                $request->setApprovedRejectDate($DATE_APPROVED_REJECTED_Date);
            } else {
                $error = 'Approver not found for exportId='.$exportId . "; APPROVER_ID=".$APPROVER_ID;
                $logger->error($error);
                throw new \Exception( $error );
            }

            //REQUEST_STATUS_ID
            //BUS_REQUEST_STATUS_ID
            //VAC_REQUEST_STATUS_ID


            //COMMENTS


            echo "finished looping<br><br>";
            echo "<br>";
        }//for each request

        //process not existing users
        //print_r($notExistingUsers);
        echo "not existing users = ".count($notExistingUsers)."<br>";
        foreach( $notExistingUsers as $email=>$newestDate ) {
            $warning = "not existing user email=".$email."; newestDate=".$this->convertDateTimeToStr($newestDate);
            echo $warning."<br>";
            $logger->warning($warning);
        }

        exit('1');

        $result = "Imported requests = " . count($requests);
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
        return $userSecUtil->transformDatestrToDateWithSiteEventLog($datestr, $this->container->getParameter('vacreq.sitename'));
    }

    public function getApproverByUserId($userId) {
        $cwid = userMapper($userId);
        $username = $cwid."_@_". $this->usernamePrefix;
        $approver = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($username);
        return $approver;
    }

    public function userMapper( $userId ) {

        //PFVBTR_APPROVER_INFO
        //1	Barry Sleckman	bas2022@med.cornell.edu	Sara Lynch	sal2026@med.cornell.edu	Experimental Pathology	approver	yes
        //2	Dr. Jacob Rand	jar9135@med.cornell.edu	Sara 	sal2026@med.cornell.edu	Clinical Pathology	approver	yes
        //3	Attilio Orazi	ato9002@med.cornell.edu	Melissa Honore 	meh9043@nyp.org	Hematopathology	approver	yes
        //4	Alain Borczuk 	alb9003@med.cornell.edu	Rashida Eteng	rse9005@nyp.org	Surgical Pathology	approver	yes
        //5	Rana Hoda	rhoda@med.cornell.edu	Maxine Stevenson	mes9008@nyp.org	Cytopathology	approver	yes
        //20	Daniel M. Knowles	dknowles@med.cornell.edu	Sara Lynch	sal2026@med.cornell.edu	Executive Committee	executive	yes
        //6	Timothy Hla	tih2002@med.cornell.edu	Mario A. Castro Martinez	mcm2010@med.cornell.edu	Vascular Biology	approver	yes
        //19	Cynthia Magro				Dermatopathology	approver	no

        $cwid = null;

        switch( $userId ){
            case "1":
                $cwid = "bas2022";
                break;
            case "2":
                $cwid = "jar9135";
                break;
            case "3":
                $cwid = "ato9002";
                break;
            case "4":
                $cwid = "alb9003";
                break;
            case "5":
                $cwid = "rhoda";
                break;
            case "20":
                $cwid = "dknowles";
                break;
            case "6":
                $cwid = "tih2002";
                break;
            case "19":
                $cwid = "cym2003";
                break;
        }

        return $cwid;
    }




    public function importOldData_FROMDB_TODELETE() {

        $email = "oli2002@med.cornell.edu";

        $requests = array();

        $vacreqEm = $this->container->get('doctrine')->getManager('vacreq');
        echo "get vacreq em<br>";

        $vacreqConnection = $vacreqEm->getConnection();
        echo "after connection vacreq <br>";

        //select USERID,FIRST_NAME,LAST_NAME from profiler2015.USER_INFO where EMAIL LIKE '%gul%';
        $statement = $vacreqConnection->prepare(
            "select USERID,FIRST_NAME,LAST_NAME from profiler2015.USER_INFO where EMAIL LIKE :email"
        );
        $statement->bindValue('email', "'%".$email."%'");

        echo "before execute<br>";
        $statement->execute();
        echo "after execute<br>";

        $results = $statement->fetchAll();

        echo "<br>Result:<br>";
        print_r($results);
        echo "<br><br>";

        // for INSERT, UPDATE, DELETE queries
        $affected_rows = $statement->rowCount();
        echo "Affected Rows=".$affected_rows."<br>";


        if( $affected_rows != 1 && count($results) != 1 ) {
            throw $this->createNotFoundException('Unable to find request');
        }

        $request = $results[0]['FIRST_NAME'];
        echo "request=".$request."<br>";

        exit('1');

        $result = "Imported requests = " . count($requests);
        return $result;
    }


}