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

namespace Oleg\TranslationalResearchBundle\Util;


use Oleg\TranslationalResearchBundle\Entity\Project;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

class TransResImportData
{

    private $em;
    private $container;

    private $usernamePrefix = 'wcmc-cwid';

    public function __construct( $em, $container ) {
        $this->container = $container;
        $this->em = $em;

        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }


    // url: /import-old-data/
    public function importOldData() {

        ini_set('max_execution_time', 3600); //3600 seconds = 60 minutes

        $logger = $this->container->get('logger');
        $em = $this->em;
        $userSecUtil = $this->container->get('user_security_utility');
        $transresRequestUtil = $this->container->get('transres_request_util');

        //$email = "oli2002@med.cornell.edu";
        $requests = array();

        $default_time_zone = $this->container->getParameter('default_time_zone');
        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);

        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        $specialty = $this->em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation("ap-cp");
        if( !$specialty ) {
            exit("Project specialty not found by abbreviation=ap-cp");
        }

        $notExistingUsers = array();
        $count = 0;

        $inputFileName = __DIR__ . '/TRF_PROJECT_INFO.xlsx';

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
            //echo $row.": ";
            //var_dump($rowData);
            //echo "<br>";

            $exportId = $this->getValueByHeaderName('PROJECT_ID', $rowData, $headers);
            $exportId = trim($exportId);
            echo "exportId=".$exportId."<br>";

            //if( $exportId != 1840 ) {continue;} //testing

            $project = $this->em->getRepository('OlegTranslationalResearchBundle:Project')->findOneByExportId($exportId);
            if( $project ) {
                continue; //ignore existing request to prevent overwrite
            } else {
                //new Project
                $project = new Project();
            }

            $project->setVersion(1);

            if( !$project->getInstitution() ) {
                $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('Pathology and Laboratory Medicine');
                $project->setInstitution($institution);
            }

            //set order category
            if( !$project->getMessageCategory() ) {
                $categoryStr = "HemePath Translational Research Project";  //"Pathology Call Log Entry";
                //$categoryStr = "Nesting Test"; //testing
                $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);

                if (!$messageCategory) {
                    throw new \Exception("Message category is not found by name '" . $categoryStr . "'");
                }
                $project->setMessageCategory($messageCategory);
            }

            $project->setExportId($exportId);

            $project->setProjectSpecialty($specialty);

            //CREATED_DATE
            $CREATED_DATE_STR = $this->getValueByHeaderName('CREATED_DATE', $rowData, $headers); //24-OCT-12
            if( $CREATED_DATE_STR ) {
                $CREATED_DATE = $this->transformDatestrToDate($CREATED_DATE_STR);
                $project->setCreateDate($CREATED_DATE);
            }

            //SUBMITTED_BY
            $submitterCwid = $this->getValueByHeaderName('SUBMITTED_BY', $rowData, $headers);
            $submitterUser = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($submitterCwid);
            if( $submitterUser ) {
                $project->setSubmitter($submitterUser);
            } else {
                $msg = "Submitter not found by PrimaryPublicUserId=".$submitterUser;
                //exit($msg);
                echo $msg."<br>";
                $logger->warning($msg);
            }

            //Contact
            $contactEmail = $this->getValueByHeaderName('EMAIL', $rowData, $headers);
            $contactUsers = $this->getUserByEmail($contactEmail,$exportId,'EMAIL');
            if( count($contactUsers) > 0 ) {
                if( !$project->getSubmitter() ) {
                    $project->setSubmitter($contactUsers[0]);
                }
                foreach($contactUsers as $contactUser) {
                    $project->addContact($contactUser);
                }
            }

            //PI
            $piEmail = $this->getValueByHeaderName('PI_EMAIL', $rowData, $headers);
            $piUsers = $this->getUserByEmail($piEmail,$exportId,'PI_EMAIL');
            if( count($piUsers) > 0 ) {
                foreach($piUsers as $user) {
                    $project->addPrincipalInvestigator($user);
                }
            } else {
                //try to get by PRI_INVESTIGATOR
                $priInvestigators = $this->getValueByHeaderName('PRI_INVESTIGATOR', $rowData, $headers);
            }

            //Pathologists Involved
            $pathEmail = $this->getValueByHeaderName('PATH_EMAIL', $rowData, $headers);
            $pathUsers = $this->getUserByEmail($pathEmail,$exportId,'PATH_EMAIL');
            if( count($pathUsers) > 0 ) {
                foreach($pathUsers as $user) {
                    $project->addPathologist($user);
                }
            }

            //CO_INVESTIGATOR
            $coInvEmail = $this->getValueByHeaderName('CO_INVESTIGATOR', $rowData, $headers);
            $coInvEmails = $this->getUserByEmail($coInvEmail,$exportId,'CO_INVESTIGATOR');
            if( count($coInvEmails) > 0 ) {
                foreach($coInvEmails as $user) {
                    $project->addCoInvestigator($user);
                }
            }

            //DATE_APPROVAL
            $DATE_APPROVAL_STR = $this->getValueByHeaderName('DATE_APPROVAL', $rowData, $headers);
            echo "DATE_APPROVAL_STR=".$DATE_APPROVAL_STR."<br>";
            if( $DATE_APPROVAL_STR ) {
                $DATE_APPROVAL = $this->transformDatestrToDate($DATE_APPROVAL_STR);
                $project->setApprovalDate($DATE_APPROVAL);
            }

            //STATUS_ID
            $STATUS_ID = $this->getValueByHeaderName('STATUS_ID', $rowData, $headers);
            //$this->statusMapper($STATUS_ID);

            //PROJECT_TYPE_ID
            $PROJECT_TYPE_ID = $this->getValueByHeaderName('PROJECT_TYPE_ID', $rowData, $headers);
            //$this->typeMapper($PROJECT_TYPE_ID);


            //save project to DB before form nodes
            echo "before flush <br>";
            $em->persist($project);
            $em->flush();
            echo "after flush <br>";


            ////////// form nodes ///////////
            //PROJECT_TITLE
            $title = $this->getValueByHeaderName('PROJECT_TITLE', $rowData, $headers);
            $project->setTitle($title);
            if( $title ) {
                $this->setValueToFormNodeNewProject($project, "Title", $title);
            }
            echo "title=".$title."<br>";

            //IRB_NUMBER
            $irbNumber = $this->getValueByHeaderName('IRB_NUMBER', $rowData, $headers);
            if( $irbNumber ) {
                $this->setValueToFormNodeNewProject($project, "IRB Number", $irbNumber);
                echo "irbNumber=" . $irbNumber . "<br>";
            }

            //IRB_EXPIRATION_DATE
            $irbExpDateStr = $this->getValueByHeaderName('IRB_EXPIRATION_DATE', $rowData, $headers);
            //echo "irbExpDateStr=".$irbExpDateStr."<br>";
            if( $irbExpDateStr ) {
                $irbExpDate = $this->transformDatestrToDate($irbExpDateStr);
                if( $irbExpDate ) {
                    $project->setIrbExpirationDate($irbExpDate);
                    $this->setValueToFormNodeNewProject($project, "IRB Expiration Date", $irbExpDate);
                    echo "irbExpDate=" . $irbExpDate->format('d-m-Y') . "<br>";
                }
            }

            //PROJECT_FUNDED
            $funded = $this->getValueByHeaderName('PROJECT_FUNDED', $rowData, $headers);
            if( $funded) {
                $this->setValueToFormNodeNewProject($project, "Funded", $funded);
            }

            //ACCOUNT_NUMBER
            $fundedAccountNumber = $this->getValueByHeaderName('ACCOUNT_NUMBER', $rowData, $headers);
            if( $fundedAccountNumber ) {
                $this->setValueToFormNodeNewProject($project, "If funded, please provide account number", $fundedAccountNumber);
            }
            $project->setFundedAccountNumber($fundedAccountNumber);

            //DESCRIPTION
            $DESCRIPTION = $this->getValueByHeaderName('DESCRIPTION', $rowData, $headers);
            if( $DESCRIPTION ) {
                $this->setValueToFormNodeNewProject($project, "Brief Description", $DESCRIPTION);
            }

            //BUDGET_OUTLINE
            $BUDGET_OUTLINE = $this->getValueByHeaderName('BUDGET_OUTLINE', $rowData, $headers);
            if( $BUDGET_OUTLINE ) {
                $this->setValueToFormNodeNewProject($project, "Provide a Detailed Budget Outline/Summary", $BUDGET_OUTLINE);
            }

            //ESTIMATED_COSTS
            $ESTIMATED_COSTS = $this->getValueByHeaderName('ESTIMATED_COSTS', $rowData, $headers);
            if( $ESTIMATED_COSTS ) {
                $this->setValueToFormNodeNewProject($project, "Estimated Total Costs ($)", $ESTIMATED_COSTS);
            }
            /////////////////////



            //ADMIN_COMMENT
            $ADMIN_COMMENT = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);

            //BIO_STAT_COMMENT ???

            //PI_SUBMITTED_IRB ???

            //REQ_BIO_STAT ???

            //BIO_STAT_HAS_REVIEW ???

            //PREVIOUS_STATUS_ID ???

            //REVISED ???

            //HAS_FUNDING_APPROVAL ???

            //FUNDING_APPROVAL_DATE ???

            //FUNDING_APPROVAL_COMMENT ???

            $project->generateOid();
            $em->flush();

            //echo "after flush 2<br>";

            $count++;

            echo "<br>";

            //exit('$project OID='.$project->getOid());
        }//for each request

        //echo "finished looping<br><br>";

        //process not existing users
        //print_r($notExistingUsers);
        //echo "not existing users = ".count($notExistingUsers)."<br>";
//        foreach( $notExistingUsers as $email=>$newestDate ) {
//            $warning = "not existing user email=".$email."; newestDate=".$this->convertDateTimeToStr($newestDate);
//            //echo $warning."<br>";
//            $logger->warning($warning);
//        }

        //exit('1');

        $result = "Imported requests = " . $count;
        return $result;
    }

    public function getUserByEmail($emailStr,$exportId,$emailType) {
        $logger = $this->container->get('logger');

        $emailStr = str_replace(";",",",$emailStr);
        if( strpos($emailStr,",") !== false ) {
            $emails = explode(",",$emailStr);
        } else {
            $emails = array($emailStr);
        }

        $users = array();
        foreach($emails as $email) {
            $emailParts = explode("@", $email);

            if( count($emailParts) == 0 || count($emailParts) == 1 ) {
                continue;
            }

            if( $emailParts[1] == "med.cornell.edu" || $emailParts[1] == "nyp.org" ) {
                //ok
            } else {
                $msg = "email [".$emailStr."] is not CWID user";
                echo $msg."<br>";
                $logger->warning($msg);
            }

            $cwid = $emailParts[0];

            $username = $cwid."_@_". $this->usernamePrefix;
            $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($username);
            if( !$user ) {
                $users[] = $user;
            } else {
                $msg = "Project Export ID=".$exportId.": No user found by email [".$email."]; type=".$emailType;
                echo $msg."<br>";
                $logger->warning($msg);
            }

        }

        return $users;
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

    public function setValueToFormNodeNewProject( $project, $fieldName, $value ) {
        return null;

        $transresRequestUtil = $this->container->get('transres_request_util');
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $formNodeUtil = $this->container->get('user_formnode_utility');
        $receivingObject = $transresRequestUtil->setValueToFormNodeProject($project,$fieldName,$value);
        if( !$receivingObject ) {
            //$thisFormNode = $this->em->getRepository("OlegUserdirectoryBundle:FormNode")->find($formNodeId);
            $thisFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents($fieldName);

            echo "create formnode=".$thisFormNode."<br>";
            //$testing = true;
            $testing = false;
            $formNodeUtil->processFormNodeByType($thisFormNode,$value,$project,$testing);
        }
        //re-try
        $receivingObject = $transresRequestUtil->setValueToFormNodeProject($project,$fieldName,$value);
    }

    public function transformDatestrToDate($datestr)
    {
        //$userSecUtil = $this->container->get('user_security_utility');
        //$date = $userSecUtil->transformDatestrToDateWithSiteEventLog($datestr, $this->container->getParameter('translationalresearch.sitename'));
//        if( $date ) {
//            $date->setTimezone(new \DateTimeZone("UTC"));
//            //echo "ok<br>";
//        } else {
//            //exit("date object is null for datestr=".$datestr);
//        }

        //'j-M-Y', '15-Feb-2009'
        //23-APR-07
        echo "dateStr=".$datestr;
        $date = \DateTime::createFromFormat('j-M-y',$datestr);
        echo " =>".$date->format("d-m-Y")."<br>";

        return $date;
    }

    public function getApproverByUserId($userId) {
        $cwid = $this->userMapper($userId);
        $username = $cwid."_@_". $this->usernamePrefix;
        $approver = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($username);
        if( !$approver ) {
            $logger = $this->container->get('logger');
            $logger->error("Can not find user by username=".$username);
            //echo "Can not find user by username=".$username."<br>";
        }
        return $approver;
    }

    public function statusMapper( $statusId ) {

//        1	pending
//        2	admin-review
//        3	committee-review
//        4	committee-approval
//        5	active
//        0	draft
//        7	admin-approval
//        8	bio-statistical consultation
//        9	pending resubmission
//        10	pending revision
//        11	pending bio-statistical revision
//        6	irb-review
//        14	pending bio-statistical request
//        13	pending funding approval
//        12	closed

        $status = null;

        switch( $statusId ){
            case "0":
                $status = "draft";
                break;
            case "1":
                $status = "pending";
                break;
            case "2":
                $status = "admin-review";
                break;
            case "3":
                $status = "committee-review";
                break;
            case "4":
                $status = "committee-approval";
                break;
            case "5":
                $status = "active";
                break;
            case "6":
                $status = "irb-review";
                break;
            case "7":
                $status = "admin-approval";
                break;
            case "8":
                $status = "bio-statistical consultation";
                break;
            case "9":
                $status = "pending resubmission";
                break;
            case "10":
                $status = "pending revision";
                break;
            case "11":
                $status = "pending bio-statistical revision";
                break;
            case "12":
                $status = "closed";
                break;
            case "13":
                $status = "pending funding approval";
                break;
            case "14":
                $status = "pending bio-statistical request";
                break;
        }

        return $status;
    }

    public function typeMapper( $id ) {

        //1	Case Study	0
        //2	Descriptive Study	0
        //3	Association Study - Request Statistical Support	1

        $status = null;

        switch( $id ){
            case "1":
                $status = "Case Study";
                break;
            case "2":
                $status = "Descriptive Study";
                break;
            case "3":
                $status = "Association Study - Request Statistical Support";
                break;
        }

        return $status;
    }





}