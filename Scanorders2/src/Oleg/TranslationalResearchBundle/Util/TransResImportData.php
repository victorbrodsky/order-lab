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
use Oleg\UserdirectoryBundle\Security\Authentication\AuthUtil;
use Oleg\UserdirectoryBundle\Security\Util\UserSecurityUtil;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Validator\Constraints\DateTime;

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
    //import projects from: TRF_DRAFT_PROJECT and RF_PROJECT_INFO
    public function importOldData() {

        ini_set('max_execution_time', 3600); //3600 seconds = 60 minutes

        $logger = $this->container->get('logger');
        $em = $this->em;
        $userSecUtil = $this->container->get('user_security_utility');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');

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

        $notExistingStatuses = array();
        $notExistingUsers = array();
        $count = 0;

        $inputFileName = __DIR__ . '/TRF_PROJECT_INFO.xlsx';

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

            $exportId = $this->getValueByHeaderName('PROJECT_ID', $rowData, $headers);
            $exportId = trim($exportId);
            echo "exportId=".$exportId."<br>";

            //if( $exportId != 1840 ) {continue;} //testing

            //Process Project
            if(0) {
                $res = $this->importProject($rowData, $headers, $exportId, $specialty, $systemUser, $notExistingStatuses, $notExistingUsers);
                $notExistingStatuses = $res['notExistingStatuses'];
                $notExistingUsers = $res['notExistingUsers'];
                $project = $res['project'];
            }

            if(0) {
                $exportId = 13443;
                $this->importAdminComments($rowData, $headers, $exportId);
                exit('end of comment');
            }

            $count++;

            //echo "<br>";
            //exit('$project OID='.$project->getOid());
        }//for each request

        $notExistingStatuses = array_unique($notExistingStatuses);
        foreach($notExistingStatuses as $notExistingStatus) {
            echo "$notExistingStatus <br>";
        }

        $errorCount=1;
        foreach($notExistingUsers as $notExistingUser) {
            echo $errorCount.": ".$notExistingUser."<br>";
            $errorCount++;
        }

        $result = "Imported requests = " . $count;
        exit($result);

        return $result;
    }

    public function importProject( $rowData, $headers, $exportId, $specialty, $systemUser, $notExistingStatuses, $notExistingUsers ) {
        $transresUtil = $this->container->get('transres_util');
        $logger = $this->container->get('logger');
        $em = $this->em;

        $project = $this->em->getRepository('OlegTranslationalResearchBundle:Project')->findOneByExportId($exportId);
        if( $project ) {
            //ignore existing request to prevent overwrite
            $res = array(
                'notExistingStatuses' => $notExistingStatuses,
                'notExistingUsers' => $notExistingUsers,
                'project' => $project
            );
            return $res;
        } else {
            //new Project
            $project = new Project();
        }

        $project->setVersion(1);
        $project->setImportDate(new \DateTime());

        if( !$project->getInstitution() ) {
            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('Pathology and Laboratory Medicine');
            $project->setInstitution($institution);
        }

        //set order category
//            if( !$project->getMessageCategory() ) {
//                $categoryStr = "HemePath Translational Research Project";  //"Pathology Call Log Entry";
//                //$categoryStr = "Nesting Test"; //testing
//                $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
//
//                if (!$messageCategory) {
//                    throw new \Exception("Message category is not found by name '" . $categoryStr . "'");
//                }
//                $project->setMessageCategory($messageCategory);
//            }

        $project->setExportId($exportId);

        $project->setProjectSpecialty($specialty);

        //CREATED_DATE
        $CREATED_DATE_STR = $this->getValueByHeaderName('CREATED_DATE', $rowData, $headers); //24-OCT-12
        if( $CREATED_DATE_STR ) {
            $CREATED_DATE = $this->transformDatestrToDate($CREATED_DATE_STR);
            $project->setCreateDate($CREATED_DATE);
        }

        //IRB_EXPIRATION_DATE
        $irbExpDate = null;
        $irbExpDateStr = $this->getValueByHeaderName('IRB_EXPIRATION_DATE', $rowData, $headers);
        //echo "irbExpDateStr=".$irbExpDateStr."<br>";
        if( $irbExpDateStr ) {
            $irbExpDate = $this->transformDatestrToDate($irbExpDateStr);
            if( $irbExpDate ) {
                $project->setIrbExpirationDate($irbExpDate);
                //$this->setValueToFormNodeNewProject($project, "IRB Expiration Date", $irbExpDate);
                //echo "irbExpDate=" . $irbExpDate->format('d-m-Y') . "<br>";
            }
        }

        //STATUS_ID
        $statusID = $this->getValueByHeaderName('STATUS_ID', $rowData, $headers);
        $statusStr = $this->statusMapper($statusID);
        if( $statusStr ) {
            $project->setState($statusStr);
        } else {
            echo "Status not define=".$statusID.":".$this->statusMapper($statusID,true) . "<br>";
            $notExistingStatuses[] = $this->statusMapper($statusID,true);
        }

        $requestersArr = array();
        $requestersStrArr = array();

        //SUBMITTED_BY
        $submitterCwid = $this->getValueByHeaderName('SUBMITTED_BY', $rowData, $headers);
        $requestersStrArr[] = "SUBMITTED_BY: ".$submitterCwid;
        $submitterUser = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($submitterCwid);
        if( $submitterUser ) {
            $project->setSubmitter($submitterUser);
        } else {
            $msg = "Submitter not found by SUBMITTED_BY=".$submitterUser;
            //exit($msg);
            //echo $msg."<br>";
            $logger->warning($msg);
        }

        //Contact
        $contactEmails = $this->getValueByHeaderName('EMAIL', $rowData, $headers);
        $requestersStrArr[] = "EMAIL: ".$contactEmails;
        $contactEmails = strtolower($contactEmails);
        $contactUsers = $this->getUserByEmail($contactEmails,$exportId,'EMAIL');
        if( count($contactUsers) > 0 ) {
            if( !$project->getSubmitter() ) {
                $submitter = $contactUsers[0];
                //echo "1 submitter=".$submitter."<br>";
                $project->setSubmitter($submitter);
            }
            foreach($contactUsers as $contactUser) {
                $project->addContact($contactUser);
                $requestersArr[] = $contactUser;
            }
        } else {
            $msg = "Contact user not found by EMAIL=".$contactEmails;
            //exit($msg);
            //echo $msg."<br>";
            $logger->warning($msg);
            //$notExistingUsers[] = $exportId." [###Critical###]: ".$msg;
        }

        //PI
        $piEmail = $this->getValueByHeaderName('PI_EMAIL', $rowData, $headers);
        $requestersStrArr[] = "PI_EMAIL: ".$piEmail;
        $piUsers = $this->getUserByEmail($piEmail,$exportId,'PI_EMAIL');
        if( count($piUsers) > 0 ) {
            foreach($piUsers as $user) {
                $project->addPrincipalInvestigator($user);
                $requestersArr[] = $user;
            }
        } else {
            if( $piEmail ) {
                $msg = "PI user not found by PI_EMAIL=" . $piEmail;
                //echo $msg . "<br>";
                $logger->warning($msg);
            }
            //try to get by PRI_INVESTIGATOR
            $priInvestigators = $this->getValueByHeaderName('PRI_INVESTIGATOR', $rowData, $headers);
            $priInvestigators = $this->cleanString($priInvestigators);
            $requestersStrArr[] = "PRI_INVESTIGATOR: ".$priInvestigators;
            $priInvestigators = $this->cleanUsername($priInvestigators);
            $priInvestigatorsArr = explode(",",$priInvestigators);
            foreach($priInvestigatorsArr as $pi) {
                //assume "amy chadburn": second if family name
                $thisUser = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByAnyNameStr($pi);
                if( $thisUser ) {
                    $project->addPrincipalInvestigator($thisUser);
                    $requestersArr[] = $thisUser;
                } else {
                    $msg = "PI user not found by PRI_INVESTIGATOR=".$pi;
                    //$notExistingUsers[] = $exportId.": ".$msg;
                    $logger->warning($msg);
                    //exit($msg);
                }
            }
        }

        //Pathologists Involved
        $pathEmail = $this->getValueByHeaderName('PATH_EMAIL', $rowData, $headers);
        $requestersStrArr[] = "PATH_EMAIL: ".$pathEmail;
        $pathUsers = $this->getUserByEmail($pathEmail,$exportId,'PATH_EMAIL');
        if( count($pathUsers) > 0 ) {
            foreach($pathUsers as $user) {
                $project->addPathologist($user);
                $requestersArr[] = $user;
            }
        } else {
            $msg = "Pathology user not found by PATH_EMAIL=".$pathEmail;
            //exit($msg);
            //echo $msg."<br>";
            if( $pathEmail ) {
                $logger->warning($msg);
                //$notExistingUsers[] = $exportId . ": " . $msg;
            }
        }

        //CO_INVESTIGATOR
        $coInvestigators = $this->getValueByHeaderName('CO_INVESTIGATOR', $rowData, $headers);
        $coInvestigators = $this->cleanString($coInvestigators);
        $requestersStrArr[] = "CO_INVESTIGATOR: ".$coInvestigators;
        $coInvestigators = $this->cleanUsername($coInvestigators);
        $coInvestigatorsArr = explode(",",$coInvestigators);
        foreach($coInvestigatorsArr as $coInvestigator) {
            //echo "coInvestigator=".$coInvestigator."<br>";
            //assume "amy chadburn": second if family name
            $thisUser = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByAnyNameStr($coInvestigator);
            if( $thisUser ) {
                $project->addCoInvestigator($thisUser);
                $requestersArr[] = $thisUser;
            } else {
                $msg = "Co-Investigator user not found by CO_INVESTIGATOR=".$coInvestigator;
                if( $coInvestigator ) {
                    $logger->warning($msg);
                    //$notExistingUsers[] = $exportId . ": " . $msg;
                }
                //exit($msg);
            }
            //}

        }

        $criticalErrorArr = array();
        if( !$project->getSubmitter() ) {
            if( count($requestersArr) > 0 ) {
                $submitter = $requestersArr[0];
                //echo "2 submitter=".$submitter."<br>";
                $project->setSubmitter($submitter);
                echo "Submitter is populated by first requester:";
                //print_r($requestersArr);
                foreach($requestersArr as $requester) {
                    echo $requester."<br>";
                }
                echo "<br>";
            } else {
                $criticalErrorArr[] = "Submitter";
            }
        }
        //add system user if not set
        if( !$project->getSubmitter() ) {
            //echo "3 submitter=".$submitter."<br>";
            $project->setSubmitter($systemUser);
        }

        $pis = $project->getPrincipalInvestigators();
        if( count($pis) == 0 ) {
            if( count($requestersArr) > 0 ) {
                $pi = $requestersArr[0];
                $project->addPrincipalInvestigator($pi);
                echo "PI is populated by first requester:";
                //print_r($requestersArr);
                foreach($requestersArr as $requester) {
                    echo $requester."<br>";
                }
                echo "<br>";
            } else {
                $criticalErrorArr[] = "PI";
            }
        }
        //add system user if not set
        $pis = $project->getPrincipalInvestigators();
        if( count($pis) == 0 ) {
            $project->addPrincipalInvestigator($systemUser);
        }

        if( count($criticalErrorArr) > 0 ) {
            $notexpired = false;
            if( $irbExpDate && $irbExpDate > new \DateTime("now") ) {
                //$notexpired = "***not expired/closed***";
                $notexpired = true;
            }
            $notclosed = false;
            if( $statusStr != "closed" ) {
                $notclosed = true;
            }

            if( $notexpired && $notclosed ) {
                $criticalErrorStr = $exportId . " (Status:" . $statusStr . "; Created:" . $CREATED_DATE_STR . "; IRB EXP:" . $irbExpDateStr . ")";
                $notExistingUsers[] = $criticalErrorStr ." ". implode(",", $criticalErrorArr) . " Undefined" . ". Requesters: " . implode("; ", $requestersStrArr);
            }
        }

        //Billing Contact
        if( $project->getSubmitter() ) {
            $project->setBillingContact($project->getSubmitter());
        }

        //DATE_APPROVAL
        $DATE_APPROVAL_STR = $this->getValueByHeaderName('DATE_APPROVAL', $rowData, $headers);
        //echo "DATE_APPROVAL_STR=".$DATE_APPROVAL_STR."<br>";
        if( $DATE_APPROVAL_STR ) {
            $DATE_APPROVAL = $this->transformDatestrToDate($DATE_APPROVAL_STR);
            $project->setApprovalDate($DATE_APPROVAL);
        }

        //PROJECT_TYPE_ID
        $PROJECT_TYPE_ID = $this->getValueByHeaderName('PROJECT_TYPE_ID', $rowData, $headers);
        $projectType = $this->projectTypeMapper($PROJECT_TYPE_ID);
        if( $projectType ) {
            $project->setProjectType($projectType);
        }

        //PROJECT_TITLE
        $title = $this->getValueByHeaderName('PROJECT_TITLE', $rowData, $headers);
        $project->setTitle($title);
        if( $title ) {
            //$this->setValueToFormNodeNewProject($project, "Title", $title);
            $project->setTitle($title);
        }
        //echo "title=".$title."<br>";

        //IRB_NUMBER
        $irbNumber = $this->getValueByHeaderName('IRB_NUMBER', $rowData, $headers);
        if( $irbNumber ) {
            //$this->setValueToFormNodeNewProject($project, "IRB Number", $irbNumber);
            $project->setIrbNumber($irbNumber);
            //echo "irbNumber=" . $irbNumber . "<br>";
        }

        //PROJECT_FUNDED
        $funded = $this->getValueByHeaderName('PROJECT_FUNDED', $rowData, $headers);
        if( isset($funded) ) {
            //$this->setValueToFormNodeNewProject($project, "Funded", $funded);
            $project->setFunded($funded);
        }

        //ACCOUNT_NUMBER
        $fundedAccountNumber = $this->getValueByHeaderName('ACCOUNT_NUMBER', $rowData, $headers);
        if( isset($fundedAccountNumber) ) {
            //$this->setValueToFormNodeNewProject($project, "If funded, please provide account number", $fundedAccountNumber);
            $project->setFundedAccountNumber($fundedAccountNumber);
        }

        //DESCRIPTION
        $DESCRIPTION = $this->getValueByHeaderName('DESCRIPTION', $rowData, $headers);
        if( $DESCRIPTION ) {
            //$this->setValueToFormNodeNewProject($project, "Brief Description", $DESCRIPTION);
            $project->setDescription($DESCRIPTION);
        }

        //BUDGET_OUTLINE
        $budgetSummary = $this->getValueByHeaderName('BUDGET_OUTLINE', $rowData, $headers);
        if( $budgetSummary ) {
            //$this->setValueToFormNodeNewProject($project, "Provide a Detailed Budget Outline/Summary", $budgetSummary);
            $project->setBudgetSummary($budgetSummary);
        }

        //ESTIMATED_COSTS
        $estimatedCost = $this->getValueByHeaderName('ESTIMATED_COSTS', $rowData, $headers);
        if( isset($estimatedCost) ) {
            //$this->setValueToFormNodeNewProject($project, "Estimated Total Costs ($)", $estimatedCost);
            $project->setTotalCost($estimatedCost);
        }
        /////////////////////


        //ADMIN_COMMENT
        $ADMIN_COMMENT = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);
        //TODO:???

        //BIO_STAT_COMMENT ???

        //PI_SUBMITTED_IRB ???

        //REQ_BIO_STAT ???

        //BIO_STAT_HAS_REVIEW ???

        //PREVIOUS_STATUS_ID ???

        //REVISED ???

        //HAS_FUNDING_APPROVAL ???

        //FUNDING_APPROVAL_DATE ???

        //FUNDING_APPROVAL_COMMENT ???


        //save project to DB before form nodes
        $saveFlag = true;
        //$saveFlag = false;
        if( $saveFlag ) {
            $em->persist($project);
            $em->flush();

            $project->generateOid();
            $em->flush($project);
        }

        $res = array(
            'notExistingStatuses' => $notExistingStatuses,
            'notExistingUsers' => $notExistingUsers,
            'project' => $project
        );

        return $res;
    }

    public function importAdminComments($rowData, $headers, $exportId) {
        $project = $this->em->getRepository('OlegTranslationalResearchBundle:Project')->findOneByExportId($exportId);
        if( !$project ) {
            exit("Project wit external ID '$exportId' does not exist.");
        }

        //ADMIN_COMMENT
        $adminComment = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);
        if( $adminComment ) {
            $threadId = "";
            $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($threadId);
            if (null === $thread) {
                $thread = $this->container->get('fos_comment.manager.thread')->createThread();
                $thread->setId($threadId);

                //http://localhost/order/translational-research/project/review/25
                //$thread->setPermalink();

                // Add the thread
                $this->container->get('fos_comment.manager.thread')->saveThread($thread);
            }
        }

    }

    public function processCommentsReviewers( $rowData, $headers, $exportId, $specialty, $notExistingStatuses, $notExistingUsers ) {
        $project = $this->em->getRepository('OlegTranslationalResearchBundle:Project')->findOneByExportId($exportId);
        if( !$project ) {
            exit("Project wit external ID '$exportId' does not exist.");
        }

        //ADMIN_COMMENT
        $adminComment = $this->getValueByHeaderName('ADMIN_COMMENT', $rowData, $headers);
        if( $adminComment ) {

        }

        //new: add all default reviewers. Do it when processing Committee comments
        //$transresUtil->addDefaultStateReviewers($project);
    }

    public function cleanUsername( $username ) {
        $username = str_replace(", MD","",$username);
        $username = str_replace(", M.D.","",$username);
        $username = str_replace(",M.D.","",$username);
        $username = str_replace(", PhD","",$username);
        $username = str_replace(", PH.D","",$username);
        $username = str_replace(", Ph.D","",$username);
        $username = str_replace("Dr.","",$username);
        $username = str_replace(" MD;","",$username);

        return $username;
    }

    public function cleanString( $string ) {
        $string = str_replace(" MD ","",$string);
        $string = str_replace(" PhD ","",$string);
        return $string;
    }

    public function getUserByEmail($emailStr,$exportId,$emailType) {
        $logger = $this->container->get('logger');

        $emailStr = strtolower($emailStr);
        $emailStr = str_replace(";",",",$emailStr);
        //if( strpos($emailStr,",") !== false ) {
            $emails = explode(",",$emailStr);
        //} else {
        //    $emails = array($emailStr);
        //}

        $users = array();
        foreach($emails as $email) {
            $email = trim($email);
            $emailParts = explode("@", $email);

            if( count($emailParts) == 0 || count($emailParts) == 1 ) {
                continue;
            }

            if( $emailParts[1] == "med.cornell.edu" || $emailParts[1] == "nyp.org" ) {
                //ok
            } else {
                $msg = "email [".$emailStr."] is not CWID user";
                //echo $msg."<br>";
                $logger->warning($msg);
            }

            $cwid = $emailParts[0];
            //$username = $cwid."_@_". $this->usernamePrefix;
            $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($cwid);
            if( $user ) {
                $users[] = $user;
            }

            if( !$user ) {
                $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByEmail($email);
                if( $user ) {
                    $users[] = $user;
                }
            }

            if( !$user ) {
                $userArr = $this->em->getRepository('OlegUserdirectoryBundle:User')->findUserByUserInfoEmail($email);
                if( count($userArr) == 1 ) {
                    $users[] = $userArr[0];
                }
                if( count($userArr) > 1 ) {
                    exit("multiple users found by email ".$email);
                }
            }

            //try to find and create by LDAP
            $user = $this->createNewUserByLdap($cwid);
            if( $user ) {
                $users[] = $user;
            }

            if( !$user ) {
                $msg = "Project Export ID=".$exportId.": No user found by email [".$email."]; type=".$emailType;
                //echo $msg."<br>";
                //exit($msg);
                $logger->warning($msg);
            }

        }

        return $users;
    }

    public function createNewUserByLdap($cwid) {

        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == "dev" ) {
            return NULL;
        }

        if( !$cwid ) {
            return NULL;
        }

        //first search this user if exists in ldap directory
        $authUtil = new AuthUtil($this->container,$this->em);
        $searchRes = $authUtil->searchLdap($cwid);
        if( $searchRes == NULL || count($searchRes) == 0 ) {
            $logger->error("LdapAuthentication: can not find user by usernameClean=".$cwid);
            return NULL;
        }

        //check if the user already exists in DB $cwid
        $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($cwid);
        if( $user ) {
            return $user;
        }

        $username = $cwid . "_@_" . "wcmc-cwid";

        $usernameClean = $userSecUtil->createCleanUsername($username);
        $usernamePrefix = $userSecUtil->getUsernamePrefix($username);

        //////////////////// constract a new user ////////////////////

        $logger->notice("LdapAuthentication: create a new user found by username=".$username);
        $user = $userSecUtil->constractNewUser($username);
        //echo "user=".$user->getUsername()."<br>";

        $user->setCreatedby('ldap-transerimport');

        //modify user: set keytype and primary public user id
        $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

        if( !$userkeytype ) {
            exit("keytype does not exists ".$usernamePrefix);
        }

        $user->setKeytype($userkeytype);
        $user->setPrimaryPublicUserId($usernameClean);

        $user->setLocked(false);

        if( $searchRes ) {
            $user->setEmail($searchRes['mail']);
            $user->setFirstName($searchRes['givenName']);
            $user->setLastName($searchRes['lastName']);
            $user->setDisplayName($searchRes['displayName']);
            $user->setPreferredPhone($searchRes['telephoneNumber']);
        }

        //assign minimum roles
        $siteObject = $this->em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation("translationalresearch");
        $lowestRoles = $siteObject->getLowestRoles();
        foreach($lowestRoles as $role) {
            $user->addRole($role);
        }

        //exit('ldap ok');

        //////////////////// save user to DB ////////////////////
        $userManager = $this->container->get('fos_user.user_manager');
        $userManager->updateUser($user);

        return $user;
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

            //echo "create formnode=".$thisFormNode."<br>";
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
        //echo "dateStr=".$datestr;
        $date = \DateTime::createFromFormat('j-M-y',$datestr);
        //echo " =>".$date->format("d-m-Y")."<br>";

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

    public function statusMapper( $statusId, $asOriginalStr=false ) {

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
        $statusNew = null;

        switch( $statusId ){
            case "0":
                $status = "draft";
                $statusNew = "draft";
                break;
            case "1":
                $status = "pending";
                $statusNew = "draft";
                break;
            case "2":
                $status = "admin-review";
                $statusNew = "admin_review";
                break;
            case "3":
                $status = "committee-review";
                $statusNew = "committee_review";
                break;
            case "4":
                $status = "committee-approval";
                $statusNew = "final_review";
                break;
            case "5":
                $status = "active";
                $statusNew = "final_approved";
                break;
            case "6":
                $status = "irb-review";
                $statusNew = "irb_review";
                break;
            case "7":
                $status = "admin-approval";
                $statusNew = "committee_review";
                break;
            case "8":
                $status = "bio-statistical consultation";
                $statusNew = "closed";
                break;
            case "9":
                $status = "pending resubmission";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
            case "10":
                $status = "pending revision";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
            case "11":
                $status = "pending bio-statistical revision";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
            case "12":
                $status = "closed";
                $statusNew = "closed";
                break;
            case "13":
                $status = "pending funding approval";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
            case "14":
                $status = "pending bio-statistical request";
                //$statusNew = "draft";
                $statusNew = "closed";
                break;
        }

        if( $asOriginalStr ) {
            return $status;
        }

        return $statusNew;
    }

    public function projectTypeMapper( $id ) {

        //1	Case Study	0
        //2	Descriptive Study	0
        //3	Association Study - Request Statistical Support	1

        $status = null;
        $statusNewSystem = null;

        switch( $id ){
            case "1":
                $status = "Case Study";
                $statusNewSystem = "Clinical Research (Case Study)";
                break;
            case "2":
                $status = "Descriptive Study";
                $statusNewSystem = "Experimental Research (Descriptive Study)";
                break;
            case "3":
                $status = "Association Study - Request Statistical Support";
                $statusNewSystem = "Education/Teaching (Pathology Faculty)";
                break;
        }

        if( $statusNewSystem ) {
            $listEntity = $this->em->getRepository('OlegTranslationalResearchBundle:ProjectTypeList')->findOneByName($statusNewSystem);
            return $listEntity;
        }

        return null;
    }

    public function userRoleMapper() {
//        1	Request
//        2	Admin Review
//        3	Committee Review
//        4	Final Approval
//        5	Admin Review/Final Approval
//        6	Admin View Only
//        9	Biostatistical Review
//        7	IRB Review
//        8	IRB Review/Committee Review
    }





}