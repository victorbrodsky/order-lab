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
 * User: DevServer
 * Date: 9/3/15
 * Time: 12:00 PM
 */

namespace Oleg\UserdirectoryBundle\Util;


use Oleg\OrderformBundle\Entity\Educational;
use Oleg\UserdirectoryBundle\Entity\Institution;
use Oleg\UserdirectoryBundle\Entity\PerSiteSettings;
use Oleg\OrderformBundle\Security\Util\AperioUtil;
use Oleg\UserdirectoryBundle\Entity\AdminComment;
use Oleg\UserdirectoryBundle\Entity\AdministrativeTitle;
use Oleg\UserdirectoryBundle\Entity\AppointmentTitle;
use Oleg\UserdirectoryBundle\Entity\BoardCertification;
use Oleg\UserdirectoryBundle\Entity\CodeNYPH;
use Oleg\UserdirectoryBundle\Entity\Credentials;
use Oleg\UserdirectoryBundle\Entity\EmploymentStatus;
use Oleg\UserdirectoryBundle\Entity\Identifier;
use Oleg\UserdirectoryBundle\Entity\Location;
use Oleg\UserdirectoryBundle\Entity\MedicalTitle;
use Oleg\UserdirectoryBundle\Entity\ResearchLab;
use Oleg\UserdirectoryBundle\Entity\StateLicense;
use Oleg\UserdirectoryBundle\Entity\Training;
use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericSelectTransformer;
use Oleg\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;

class UserGenerator {

    private $em;
    private $sc;
    private $container;

    private $usernamePrefix = 'wcmc-cwid';

    public function __construct( $em, $sc, $container ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
    }


    //create template processing of the main user's fields
    public function generateUsersExcelV2() {

        ini_set('max_execution_time', 3600); //3600 seconds = 60 minutes;

        $inputFileName = __DIR__ . '/../../../../../importLists/ImportUsersTemplate.xlsx';
        //$inputFileName = __DIR__ . '/../../../../../importLists/UsersFull.xlsx';

        if (file_exists($inputFileName)) {
            echo "The file $inputFileName exists";
        } else {
            echo "The file $inputFileName does not exist";
            return -1;
        }

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( Exception $e ) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $count = 0;

        $default_time_zone = $this->container->getParameter('default_time_zone');

        $userUtil = new UserUtil();

        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);


        ////////////// add system user /////////////////
        $systemuser = $userUtil->createSystemUser($this->em,$userkeytype,$default_time_zone);
        ////////////// end of add system user /////////////////

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sections = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);

        $headers = $sheet->rangeToArray('A' . 2 . ':' . $highestColumn . 2,
            NULL,
            TRUE,
            FALSE);

        echo 'Start Foreach highestRow='.$highestRow."; highestColumn=".$highestColumn."<br>";

        //for each user in excel (start at row 2)
        for( $row = 3; $row <= $highestRow; $row++ ) {

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //Insert row data array into the database
//            echo $row.": ";
//            var_dump($rowData);
//            echo "<br>";

            //testing
//            $sectionName = "Location 1";
//            $sectionRange = $this->getMergedRangeBySectionName($sectionName,$sections,$sheet);
//            echo "sectionRange=".$sectionRange."<br>";
//            $fieldValue = $this->getValueBySectionHeaderName("Name",$rowData,$headers,$sectionRange);
//            echo "fieldValue=".$fieldValue."<br>";
//            exit('1');

            $sectionNameContactInfo = "Name and Preferred Contact Info";
            $sectionNameContactInfoRange = $this->getMergedRangeBySectionName($sectionNameContactInfo,$sections,$sheet);
            echo "sectionNameContactInfoRange=".$sectionNameContactInfoRange."<br>";

            $userType = $this->getValueBySectionHeaderName("Primary Public User ID Type",$rowData,$headers,$sectionNameContactInfoRange);
            echo "userType=".$userType."<br>";

            $username = $this->getValueBySectionHeaderName("Primary Public User ID",$rowData,$headers,$sectionNameContactInfoRange);
            echo "username(cwid)=".$username."<br>";

            if( !$username ) {
                continue; //ignore users without cwid
            }

            $usernamePrefix = null;
            if( $userType == "WCMC CWID" ) {
                $usernamePrefix = $this->usernamePrefix;
            }
            if( $userType == "Local User" ) {
                $usernamePrefix = "local-user";
            }
            if( $userType == "Aperio eSlide Manager" ) {
                $usernamePrefix = "aperio";
            }

            if( !$usernamePrefix ) {
                exit("usernamePrefix is not define for ".$userType);
            }

            //username: oli2002_@_wcmc-cwid
            $fillUsername = $username."_@_". $usernamePrefix;
            //echo "fillUsername=".$fillUsername."<br>";

            $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($fillUsername);
            //echo "DB user=".$user."<br>";

            if( $user ) {
                continue; //ignore existing users to prevent overwrite
            }

            //create user
            echo "create a new user ".$fillUsername."<br>";

            //create a new user from excel
            $user = new User();
            $user->setKeytype($userkeytype);
            $user->setPrimaryPublicUserId($username);

            //set unique username
            $usernameUnique = $user->createUniqueUsername();
            $user->setUsername($usernameUnique);
            //echo "before set username canonical usernameUnique=".$usernameUnique."<br>";
            $user->setUsernameCanonical($usernameUnique);

            $user->setEnabled(true);
            $user->setLocked(false);
            $user->setExpired(false);

            ////////////// Section: Name and Preferred Contact Info ////////////////
            $email = $this->getValueBySectionHeaderName("Preferred Email",$rowData,$headers,$sectionNameContactInfoRange);
            echo "email=".$email."<br>";
            $user->setEmail($email);
            $user->setEmailCanonical($email);

            $preferredName = $this->getValueBySectionHeaderName("Preferred Full Name for Display",$rowData,$headers,$sectionNameContactInfoRange);
            $firstName = $this->getValueBySectionHeaderName("First Name",$rowData,$headers,$sectionNameContactInfoRange);
            $middleName = $this->getValueBySectionHeaderName("Middle Name",$rowData,$headers,$sectionNameContactInfoRange);
            $lastName = $this->getValueBySectionHeaderName("Last Name",$rowData,$headers,$sectionNameContactInfoRange);
            $salutation = $this->getValueBySectionHeaderName("Salutation",$rowData,$headers,$sectionNameContactInfoRange);
            $suffix = $this->getValueBySectionHeaderName("Suffix",$rowData,$headers,$sectionNameContactInfoRange);
            $prefferedPhone = $this->getValueBySectionHeaderName("Preferred Phone Number",$rowData,$headers,$sectionNameContactInfoRange);
            $abbreviationName = $this->getValueBySectionHeaderName("Abbreviated name",$rowData,$headers,$sectionNameContactInfoRange);

            $user->setDisplayName($preferredName);
            $user->setFirstName($firstName);
            $user->setMiddleName($middleName);
            $user->setLastName($lastName);
            $user->setSalutation($salutation);
            $user->setSuffix($suffix);
            $user->setPreferredPhone($prefferedPhone);
            $user->setInitials($abbreviationName);

            $user->setPassword("");
            $user->setCreatedby('excel');
            $user->getPreferences()->setTimezone($default_time_zone);

            echo "new user=".$user."<br>";
            ////////////// EOF Section: Name and Preferred Contact Info ////////////////



            ////////////// Section: Global User Preferences ////////////////
            $sectionGlobal = "Global User Preferences";
            $sectionGlobalRange = $this->getMergedRangeBySectionName($sectionGlobal,$sections,$sheet);
            echo "sectionGlobalRange=".$sectionGlobalRange."<br>";

            $roles = $this->getValueBySectionHeaderName("Role",$rowData,$headers,$sectionGlobalRange);
            $timeZone = $this->getValueBySectionHeaderName("Time Zone",$rowData,$headers,$sectionGlobalRange);
            $language = $this->getValueBySectionHeaderName("Language",$rowData,$headers,$sectionGlobalRange);
            $locale = $this->getValueBySectionHeaderName("Locale",$rowData,$headers,$sectionGlobalRange);

            //Roles
            $rolesObjects = $this->processMultipleListObjects($roles,$systemuser,"Roles");
            $user->setRoles($rolesObjects);

            $user->getPreferences()->setTimezone($timeZone);
            $user->getPreferences()->addLanguage($language);
            $user->getPreferences()->setLocale($locale);
            ////////////// EOF Section: Global User Preferences ////////////////



            ////////////// Section: Employment Period ////////////////
            $sectionEmployment = "Employment Period";
            $sectionEmploymentRange = $this->getMergedRangeBySectionName($sectionEmployment,$sections,$sheet);
            echo "sectionEmploymentRange=".$sectionEmploymentRange."<br>";

            //user_employmentStatus_0_hireDate
            $dateHire = $this->getValueBySectionHeaderName("Date of Hire (MM/DD/YYYY)",$rowData,$headers,$sectionEmploymentRange);
            //$dateHire = date("m/d/Y", \PHPExcel_Shared_Date::ExcelToPHP($dateHire));
            //echo "dateHire=".$dateHire."<br>";
            $dateHire = \PHPExcel_Shared_Date::ExcelToPHP($dateHire);
            //$dateHire = new \DateTime(@$dateHire);
            //echo "dateHire=".$dateHire."<br>";
            $dateHire = new \DateTime("@$dateHire");
            echo "dateHire=".$dateHire->format('m/d/Y')."<br>";

            //Employee Type
            $employeeType = $this->getValueBySectionHeaderName("Employee Type",$rowData,$headers,$sectionEmploymentRange);
            echo "employeeType=".$employeeType."<br>";
            $employeeTypeObject = $this->getObjectByNameTransformerWithoutCreating("EmploymentType",$employeeType,$systemuser);
            echo "employeeTypeObject=".$employeeTypeObject."<br>";

            $jobDescription = $this->getValueBySectionHeaderName("Job Description Summary",$rowData,$headers,$sectionEmploymentRange);
            $jobDescriptionOfficial = $this->getValueBySectionHeaderName("Job Description (official, as posted)",$rowData,$headers,$sectionEmploymentRange);
            echo "jobDescriptionOfficial=".$jobDescriptionOfficial."<br>";

            //Institution
            $institutionStr = $this->getValueBySectionHeaderName("Institution",$rowData,$headers,$sectionEmploymentRange);
            echo "institutionStr=".$institutionStr."<br>"; //TODO: variable not defined???
            //exit('111');

            $departmentStr = $this->getValueBySectionHeaderName("Department",$rowData,$headers,$sectionEmploymentRange);
            $divisionStr = $this->getValueBySectionHeaderName("Division",$rowData,$headers,$sectionEmploymentRange);
            $serviceStr = $this->getValueBySectionHeaderName("Service",$rowData,$headers,$sectionEmploymentRange);
            echo "inst=".$institutionStr."; $departmentStr; $divisionStr; $serviceStr"."<br>";
            $institutionObject = $this->getEntityByInstitutionDepartmentDivisionService($institutionStr,$departmentStr,$divisionStr,$serviceStr);
            echo "institutionObject=".$institutionObject."<br>";

            //End of Employment Date (MM/DD/YYYY)
            $endHire = $this->getValueBySectionHeaderName("End of Employment Date (MM/DD/YYYY)",$rowData,$headers,$sectionEmploymentRange);
            $endHire = \PHPExcel_Shared_Date::ExcelToPHP($endHire);
            $endHire = new \DateTime("@$endHire");
            echo "endHire=".$endHire->format('m/d/Y')."<br>";

            //Type of End of Employment
            $employeeEndType = $this->getValueBySectionHeaderName("Type of End of Employment",$rowData,$headers,$sectionEmploymentRange);
            $employeeEndTypeObject = $this->getObjectByNameTransformerWithoutCreating("EmploymentTerminationType",$employeeEndType,$systemuser);

            $employeeEndReason = $this->getValueBySectionHeaderName("Reason for End of Employment",$rowData,$headers,$sectionEmploymentRange);

            if(
                $dateHire || $employeeTypeObject || $jobDescription ||
                $jobDescriptionOfficial || $institutionObject || $endHire || $employeeEndTypeObject || $employeeEndReason
            ) {
                $employmentStatus = new EmploymentStatus($systemuser);
                $user->addEmploymentStatus($employmentStatus);

                $employmentStatus->setEmploymentType($employeeTypeObject);

            }

            ////////////// EOF Section: Employment Period ////////////////

            exit('1');




            //Employee Type: user_employmentStatus_0_employmentType: EmploymentType
            $employmentType = $this->getValueByHeaderName('Employee Type', $rowData, $headers);
            if( $employmentType ) {
                $employmentStatus = new EmploymentStatus($systemuser);
                $employmentTypeObj = $this->getObjectByNameTransformer('EmploymentType',$employmentType,$systemuser);
                $employmentStatus->setEmploymentType($employmentTypeObj);
                $user->addEmploymentStatus($employmentStatus);
            }


//            $found_user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername( $user->getUsername() );
//            if( $found_user ) {
//                //
//            } else {
            //echo $username." not found ";
            $this->em->persist($user);
            $this->em->flush();
            $count++;


            //Assistants : s2id_oleg_userdirectorybundle_user_locations_0_assistant
            $assistants = $this->getValueByHeaderName('Assistants', $rowData, $headers);
            if( $assistants ) {
                $assistantsArr[$user->getId()] = $assistants;
            }


            //**************** create PerSiteSettings for this user **************//
            //TODO: this should be located on scanorder site
            $securityUtil = $this->container->get('order_security_utility');
            $perSiteSettings = $securityUtil->getUserPerSiteSettings($user);
            if( !$perSiteSettings ) {
                $perSiteSettings = new PerSiteSettings($systemuser);
                $perSiteSettings->setUser($user);
            }
            $params = $this->em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
            if( count($params) != 1 ) {
                throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
            }
            $param = $params[0];
            $institution = $param->getAutoAssignInstitution();
            $perSiteSettings->addPermittedInstitutionalPHIScope($institution);
            $this->em->persist($perSiteSettings);
            $this->em->flush();
            //**************** EOF create PerSiteSettings for this user **************//

            //record user log create
            $event = "User ".$user." has been created by ".$systemuser."<br>";
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'),$event,$systemuser,$user,null,'New user record added');

            //exit('eof user');

        }//for each user

        exit('exit import users V2');
        return $count;
    }

    //$string - , or ; separated string
    public function processMultipleListObjects( $string, $systemuser, $className ) {
        $objects = array();

        $separator = null;
        if( strpos($string,";") !== false ) {
            $separator = ";";
        }
        if( strpos($string,",") !== false ) {
            $separator = ",";
        }
        if( !$separator ) {
            return $objects;
        }

        $stringArr = explode($separator,$string);
        foreach( $stringArr as $nameStr ) {
            //                                                              $className,$nameStr,$systemuser,$params=null
            $stringObject = $this->getObjectByNameTransformerWithoutCreating($className,$nameStr,$systemuser);
            if( $stringObject ) {
                $objects[] = $stringObject;
                //echo $stringObject->getId().": ".$stringObject."<br>";
            }
        }

        return $objects;
    }

    public function getEntityByInstitutionDepartmentDivisionService( $institution, $department, $division, $service ) {
        $mapper = array(
            'prefix' => "Oleg",
            'className' => "Institution",
            'bundleName' => "UserdirectoryBundle"
        );
        $treeRepository = $this->em->getRepository($mapper['prefix'].$mapper['bundleName'].':'.$mapper['className']);

        return $treeRepository->findEntityByInstitutionDepartmentDivisionService($institution,$department,$division,$service,$mapper);

//        $institutionObject = $treeRepository->findNodeByName($institution,$mapper);
//        if( !$institutionObject ) {
//            return null;
//        }
//
//        $departmentObject = findByChildnameAndParent($department,$institution,$mapper);
//        if( !$departmentObject ) {
//            return $institutionObject;
//        }
//
//        $divisionObject = findByChildnameAndParent($division,$departmentObject,$mapper);
//        if( !$divisionObject ) {
//            return $departmentObject;
//        }
//
//        $serviceObject = findByChildnameAndParent($service,$departmentObject,$mapper);
//        if( !$serviceObject ) {
//            return $divisionObject;
//        }
//
//        return $serviceObject;
    }



    public function generateUsersExcelV1() {

        ini_set('max_execution_time', 3600); //3600 seconds = 60 minutes;

        $inputFileName = __DIR__ . '/../Util/UsersFullNew.xlsx';

        if (file_exists($inputFileName)) {
            //echo "The file $inputFileName exists";
        } else {
            echo "The file $inputFileName does not exist";
            return -1;
        }

        try {
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( Exception $e ) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        //var_dump($sheetData);

        $assistantsArr = array();

        $count = 0;
        //$serviceCount = 0;

        $default_time_zone = $this->container->getParameter('default_time_zone');

        $userUtil = new UserUtil();

        $userSecUtil = $this->container->get('user_security_utility');
        $userkeytype = $userSecUtil->getUsernameType($this->usernamePrefix);


        ////////////// add system user /////////////////
        $systemuser = $userUtil->createSystemUser($this->em,$userkeytype,$default_time_zone);
        ////////////// end of add system user /////////////////

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);



        //for each user in excel (start at row 2)
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


            $username = $this->getValueByHeaderName('CWID', $rowData, $headers);
            //echo "username(cwid)=".$username."<br>";

            if( !$username ) {
                continue; //ignore users without cwid
            }

            //echo "<br>divisions=".$rowData[0][2]." == ";
            //print_r($services);

            //username: oli2002_@_wcmc-cwid
            $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername( $username."_@_". $this->usernamePrefix);
            //echo "DB user=".$user."<br>";

            if( $user ) {

                //Assistants : s2id_oleg_userdirectorybundle_user_locations_0_assistant
                $assistants = $this->getValueByHeaderName('Assistants', $rowData, $headers);
                if( $assistants ) {
                    $assistantsArr[$user->getId()] = $assistants;
                }

                continue; //ignore existing users to prevent overwrite
            }

            if( !$user ) {
                //create excel user
                $user = new User();
                $user->setKeytype($userkeytype);
                $user->setPrimaryPublicUserId($username);

                //set unique username
                $usernameUnique = $user->createUniqueUsername();
                $user->setUsername($usernameUnique);
                //echo "before set username canonical usernameUnique=".$usernameUnique."<br>";
                $user->setUsernameCanonical($usernameUnique);
            }

            $email = $this->getValueByHeaderName('E-mail Address', $rowData, $headers);
            $user->setEmail($email);
            $user->setEmailCanonical($email);

            $lastName = $this->getValueByHeaderName('Last Name', $rowData, $headers);
            $firstName = $this->getValueByHeaderName('First Name', $rowData, $headers);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setDisplayName($firstName." ".$lastName);
            $user->setSalutation($this->getValueByHeaderName('Salut.', $rowData, $headers));
            $user->setMiddleName($this->getValueByHeaderName('Middle Name', $rowData, $headers));

            $user->setPassword("");
            $user->setCreatedby('excel');
            $user->getPreferences()->setTimezone($default_time_zone);

            echo "new user=".$user."<br>";

            //Degree: TrainingDegreeList - Multi
            $degreeStr = $this->getValueByHeaderName('Degree', $rowData, $headers);
            if( $degreeStr ) {

                $degreeArr = explode(";",$degreeStr);
                foreach( $degreeArr as $degree ) {
                    $degree = trim($degree);
                    if( $degree ) {
                        $training = new Training($systemuser);
                        $training->setStatus($training::STATUS_VERIFIED);
                        $degreeObj = $this->getObjectByNameTransformer('TrainingDegreeList',$degree,$systemuser);
                        $training->setDegree($degreeObj);
                        $training->setAppendDegreeToName(true);
                        $user->addTraining($training);
                    }
                }
            }

            //Employee Type: user_employmentStatus_0_employmentType: EmploymentType
            $employmentType = $this->getValueByHeaderName('Employee Type', $rowData, $headers);
            if( $employmentType ) {
                $employmentStatus = new EmploymentStatus($systemuser);
                $employmentTypeObj = $this->getObjectByNameTransformer('EmploymentType',$employmentType,$systemuser);
                $employmentStatus->setEmploymentType($employmentTypeObj);
                $user->addEmploymentStatus($employmentStatus);
            }

            //add default locations
            if( count($user->getLocations()) == 0 ) {
                $user = $this->addDefaultLocations($user,$systemuser);
            }

            //fax, office are stored in Location object
            $mainLocation = $user->getMainLocation();
            $mainLocation->setStatus($mainLocation::STATUS_VERIFIED);
            $mainLocation->setFax($this->getValueByHeaderName('Fax Number', $rowData, $headers));
            $mainLocation->setIc($this->getValueByHeaderName('Intercom', $rowData, $headers));
            $mainLocation->setPager($this->getValueByHeaderName('Pager', $rowData, $headers));


            //phone(s)
            $BusinessPhones = $this->getValueByHeaderName('Business Phone', $rowData, $headers);
            $BusinessPhonesArr = explode(";",$BusinessPhones);

            if( count($BusinessPhonesArr) > 0 ) {
                $BusinessPhone = array_shift($BusinessPhonesArr);
                $mainLocation->setPhone($BusinessPhone);
            }

            foreach( $BusinessPhonesArr as $BusinessPhone ) {
                $location = new Location();
                $location->setStatus($location::STATUS_VERIFIED);
                $location->setRemovable(true);
                $location->setName('Other Location');
                $otherLocType = $this->em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Employee Office");
                $location->addLocationType($otherLocType);
                $location->setPhone($BusinessPhone);
                $user->addLocation($location);
            }


            //set room object
            $office = $this->getValueByHeaderName('Office Location', $rowData, $headers);
            $roomObj = $this->getObjectByNameTransformer('RoomList',$office,$systemuser);
            $mainLocation->setRoom($roomObj);


            //title is stored in Administrative Title
            $administrativeTitleStr = $this->getValueByHeaderName('Administrative Title', $rowData, $headers);
            if( $administrativeTitleStr ) {
                //Administrative - Institution
                $Institution = $this->getValueByHeaderName('Administrative - Institution', $rowData, $headers);
                $Department = $this->getValueByHeaderName('Administrative - Department', $rowData, $headers);
                $Division = $this->getValueByHeaderName('Administrative - Division', $rowData, $headers);
                $Service = $this->getValueByHeaderName('Administrative - Service', $rowData, $headers);
                //Heads
                $HeadDepartment = $this->getValueByHeaderName('Administrative - Head of this Department', $rowData, $headers);
                $HeadDivision = $this->getValueByHeaderName('Administrative - Head of this Division', $rowData, $headers);
                $HeadService = $this->getValueByHeaderName('Administrative - Head of this Service', $rowData, $headers);
                //set institutional hierarchys
                $administrativeTitles = $this->addInstitutinalTree('AdministrativeTitle',$user,$systemuser,$administrativeTitleStr,$Institution,$Department,$HeadDepartment,$Division,$HeadDivision,$Service,$HeadService);

//                if( count($administrativeTitles) == 0 ) {
//                    $administrativeTitles[] = new AdministrativeTitle();
//                }
//
//                foreach( $administrativeTitles as $administrativeTitle ) {
//                    //set title object: Administrative Title
//                    $titleObj = $this->getObjectByNameTransformer('AdminTitleList',$administrativeTitleStr,$systemuser);
//                    $administrativeTitle->setName($titleObj);
//
//                    $user->addAdministrativeTitle($administrativeTitle);
//                }
                //echo "count admin titles=".count($administrativeTitles)."<br>";
                //exit('admin title end');
            }//if admin title

            //Medical Staff Appointment (MSA) Title
            $msaTitleStr = $this->getValueByHeaderName('Medical Staff Appointment (MSA) Title', $rowData, $headers);
            if( $msaTitleStr ) {

                //Administrative - Institution
                $Institution = $this->getValueByHeaderName('MSA - Institution', $rowData, $headers);
                $Department = $this->getValueByHeaderName('MSA - Department', $rowData, $headers);
                $Division = $this->getValueByHeaderName('MSA - Division', $rowData, $headers);
                $Service = $this->getValueByHeaderName('MSA - Service', $rowData, $headers);
                //Heads
                $HeadDepartment = $this->getValueByHeaderName('MSA - Head of Department', $rowData, $headers);
                $HeadDivision = $this->getValueByHeaderName('MSA - Head of Division', $rowData, $headers);
                $HeadService = $this->getValueByHeaderName('MSA - Head of Service', $rowData, $headers);
                //set institutional hierarchys
                $msaTitles = $this->addInstitutinalTree('MedicalTitle',$user,$systemuser,$msaTitleStr,$Institution,$Department,$HeadDepartment,$Division,$HeadDivision,$Service,$HeadService);

//                if( count($msaTitles) == 0 ) {
//                    $msaTitles[] = new MedicalTitle();
//                }
//
//                foreach( $msaTitles as $msaTitle ) {
//                    $titleObj = $this->getObjectByNameTransformer('MedicalTitleList',$msaTitleStr,$systemuser);
//                    $msaTitle->setName($titleObj);
//
//                    $user->addMedicalTitle($msaTitle);
//                }

            }

            //Academic Title
            $academicTitleStr = $this->getValueByHeaderName('Academic Title', $rowData, $headers);
            if( $academicTitleStr ) {

                //Administrative - Institution
                $Institution = $this->getValueByHeaderName('Academic Appt - Institution', $rowData, $headers);
                $Department = $this->getValueByHeaderName('Academic Appt - Department', $rowData, $headers);
                $Division = $this->getValueByHeaderName('Academic Appt - Division', $rowData, $headers);
                $Service = $this->getValueByHeaderName('Academic Appt - Service', $rowData, $headers);
                //Heads
                $HeadDepartment = $this->getValueByHeaderName('Academic Appt - Head of Department', $rowData, $headers);
                $HeadDivision = $this->getValueByHeaderName('Academic Appt - Head of Division', $rowData, $headers);
                $HeadService = $this->getValueByHeaderName('Academic Appt - Head of Service', $rowData, $headers);
                //set institutional hierarchys
                $academicTitles = $this->addInstitutinalTree('AppointmentTitle',$user,$systemuser,$academicTitleStr,$Institution,$Department,$HeadDepartment,$Division,$HeadDivision,$Service,$HeadService);

                //if( count($academicTitles) == 0 ) {
                //    $academicTitles[] = new AppointmentTitle();
                //}

                //Academic Appointment - Faculty Track => oleg_userdirectorybundle_user_appointmentTitles_0_positions
                //faculty Track can be multiple but the rest of title singular
                $facultyTrackObjArr = array();
                $facultyTrackStrMulti = $this->getValueByHeaderName('Academic Appointment - Faculty Track', $rowData, $headers);
                $facultyTrackStrArr = explode(";",$facultyTrackStrMulti);
                foreach( $facultyTrackStrArr as $facultyTrackStr ) {
                    $facultyTrackStr = trim($facultyTrackStr);
                    $facultyTrackObj = $this->getObjectByNameTransformer('PositionTrackTypeList',$facultyTrackStr,$systemuser);
                    $facultyTrackObjArr[] = $facultyTrackObj;
                }


                foreach( $academicTitles as $academicTitle ) {

                    foreach( $facultyTrackObjArr as $facultyTrackObj ) {
                        $academicTitle->addPosition($facultyTrackObj);
                    }

                    //Academic Appointment start date
                    $academicAppointmentStartDateStr = $this->getValueByHeaderName('Academic Appointment start date', $rowData, $headers);
                    $academicAppointmentStartDate = $this->transformDatestrToDate($academicAppointmentStartDateStr);
                    $academicTitle->setStartDate($academicAppointmentStartDate);
                }

            }

            //Research Lab Title : s2id_oleg_userdirectorybundle_user_researchLabs_0_name
            //TODO: not tested yet
            $researchLabTitleStr = $this->getValueByHeaderName('Research Lab Title', $rowData, $headers);
            if( $researchLabTitleStr ) {               
                $researchLab = $this->getObjectByNameTransformer('ResearchLab',$researchLabTitleStr,$systemuser);

                //get or generate research lab's institution if does not exists
                if( !$researchLab->getInstitution() ) {
                    //$params = array('type'=>'Medical','organizationalGroupType'=>'Research Lab');
                    //$researchLabInstitutionObj = $this->getObjectByNameTransformer('Institution',$researchLabTitleStr,$systemuser,$params);
                    $researchWcmc = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByAbbreviation("WCMC");
                    $researchMapper = array(
                        'prefix' => 'Oleg',
                        'bundleName' => 'UserdirectoryBundle',
                        'className' => 'Institution'
                    );
                    $researchPathology = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                        "Pathology and Laboratory Medicine",
                        $researchWcmc,
                        $researchMapper
                    );
                    $researchInstitution = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent(
                        $researchLabTitleStr,
                        $researchPathology,
                        $researchMapper
                    );
                    if (!$researchInstitution) {
                        $medicalType = $this->em->getRepository('OlegUserdirectoryBundle:InstitutionType')->findOneByName('Medical');
                        $researchLabOrgGroup = $this->em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName("Research Lab");
                        $researchInstitution = new Institution();
                        $userSecUtil->setDefaultList($researchInstitution, null, $user, $researchLabTitleStr);
                        $researchInstitution->setOrganizationalGroupType($researchLabOrgGroup);
                        $researchInstitution->addType($medicalType);
                        $researchPathology->addChild($researchInstitution);
                    }

                    $researchLab->setInstitution($researchInstitution);
                }

                $user->addResearchLab($researchLab);

                //Principle Investigator of this Lab
                $piStr = $this->getValueByHeaderName('Principle Investigator of this Lab', $rowData, $headers);
                if( strtolower($piStr) == 'yes' ) {
                    $researchLab->setPiUser($user);
                }

            }//researchLabTitleStr

            //credentials
            $boardCertSpec = $this->getValueByHeaderName('Board Certification - Specialty', $rowData, $headers);
            $nyphCodeStr = $this->getValueByHeaderName('NYPH Code', $rowData, $headers);
            $licenseNumberStr = $this->getValueByHeaderName('License number', $rowData, $headers);
            $PFI = $this->getValueByHeaderName('PFI', $rowData, $headers);
            $CLIAStr = $this->getValueByHeaderName('CLIA - Number', $rowData, $headers);
            $IdentifierNumberStr = $this->getValueByHeaderName('Identifier', $rowData, $headers);

            if( $boardCertSpec || $nyphCodeStr || $licenseNumberStr || $PFI || $CLIAStr || $IdentifierNumberStr ) {
                $addobjects = false;
                $credentials = new Credentials($systemuser,$addobjects);
                $user->setCredentials($credentials);
            }

            //Board Certification - Specialty : BoardCertifiedSpecialties
            if( $boardCertSpec ) {
                $this->processBoardCertification($credentials, $systemuser,$rowData, $headers, $boardCertSpec);
            }

            //NYPH Code: oleg_userdirectorybundle_user_credentials_codeNYPH_0_field
            if( $nyphCodeStr ) {
                $nyphCode = new CodeNYPH();
                $nyphCode->setField($nyphCodeStr);
                $credentials->addCodeNYPH($nyphCode);
            }

            //License number
            if( $licenseNumberStr ) {
                $licenseState = new StateLicense();

                $licenseState->setLicenseNumber($licenseNumberStr);

                $licenseStateStr = $this->getValueByHeaderName('License state', $rowData, $headers);
                $licenseStateObj = $this->getObjectByNameTransformer('States',$licenseStateStr,$systemuser);
                $licenseState->setState($licenseStateObj);

                //License expiration
                $expDateStr = $this->getValueByHeaderName('License expiration', $rowData, $headers);
                $expDate = $this->transformDatestrToDate($expDateStr);
                $licenseState->setLicenseExpirationDate($expDate);

                $credentials->addStateLicense($licenseState);
            }

            //Administrative Comment - Category
            $AdministrativeCommentCategory = $this->getValueByHeaderName('Administrative Comment - Category', $rowData, $headers);
            if( $AdministrativeCommentCategory ) {

                $AdministrativeCommentCategory = trim($AdministrativeCommentCategory);

                $comment = new AdminComment($systemuser);

                //Administrative Comment - Name
                $AdministrativeCommentName = $this->getValueByHeaderName('Administrative Comment - Name', $rowData, $headers);

                //Administrative Comment - Comment
                $AdministrativeCommentComment = $this->getValueByHeaderName('Administrative Comment - Comment', $rowData, $headers);

                //check if Category exists (root)
                $transformer = new GenericTreeTransformer($this->em, $systemuser, 'CommentTypeList', 'UserdirectoryBundle');
                $mapper = array('prefix'=>'Oleg','bundleName'=>'UserdirectoryBundle','className'=>'CommentTypeList','organizationalGroupType'=>'CommentGroupType');
                $AdministrativeCommentCategoryObj = $this->getObjectByNameTransformer('CommentTypeList',$AdministrativeCommentCategory,$systemuser);
                //$AdministrativeCommentCategoryObj = $transformer->createNewEntity($AdministrativeCommentCategory,$mapper['className'],$systemuser);
                $this->em->persist($AdministrativeCommentCategoryObj);

                $AdministrativeCommentNameObj = null;
                if( $AdministrativeCommentCategoryObj ) {
                    $AdministrativeCommentNameObj = $this->em->getRepository('OlegUserdirectoryBundle:CommentTypeList')->findByChildnameAndParent($AdministrativeCommentName,$AdministrativeCommentCategoryObj,$mapper);
                }

                if( !$AdministrativeCommentNameObj ) {
                    $AdministrativeCommentNameObj = $transformer->createNewEntity($AdministrativeCommentName,'CommentTypeList',$systemuser);

                    if( !$AdministrativeCommentNameObj->getParent() ) {
                        $AdministrativeCommentCategoryObj->addChild($AdministrativeCommentNameObj);
                        $organizationalGroupType = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->getDefaultLevelEntity($mapper, 1);
                        $AdministrativeCommentNameObj->setOrganizationalGroupType($organizationalGroupType);
                        $this->em->persist($AdministrativeCommentNameObj);
                    } else {
                        if( $AdministrativeCommentNameObj->getParent()->getId() != $AdministrativeCommentCategoryObj->getId() ) {
                            throw new \Exception('Comment Name: Tree node object ' . $AdministrativeCommentNameObj . ' already has a parent, but it is different: existing pid=' . $AdministrativeCommentNameObj->getParent()->getId() . ', new pid='.$AdministrativeCommentCategoryObj->getId());
                        }
                    }

//                    $AdministrativeCommentCategoryObj->addChild($AdministrativeCommentNameObj);
//                    $organizationalGroupType = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->getDefaultLevelEntity($mapper, 1);
//                    $AdministrativeCommentNameObj->setOrganizationalGroupType($organizationalGroupType);
//                    $this->em->persist($AdministrativeCommentNameObj);
                }

                //set comment category tree node
                if( $AdministrativeCommentCategoryObj ) {
                    $comment->setCommentType($AdministrativeCommentCategoryObj);
                }

                //overwrite comment category tree node
                if( $AdministrativeCommentNameObj ) {
                    $comment->setCommentType($AdministrativeCommentNameObj);
                }

                $comment->setComment($AdministrativeCommentComment);

                $user->addAdminComment($comment);
            }


            //Identifier: Multi
            if( $IdentifierNumberStr ) {

                $IdentifierNumberArr = explode(";", $IdentifierNumberStr);

                $IdentifierTypeStr = $this->getValueByHeaderName('Identifier - Type', $rowData, $headers);
                $IdentifierTypeArr = explode(";", $IdentifierTypeStr);

                $IdentifierLinkStr = $this->getValueByHeaderName('Identifier - link', $rowData, $headers);
                $IdentifierLinkArr = explode(";", $IdentifierLinkStr);


                $index = 0;
                foreach( $IdentifierNumberArr as $IdentifierStr ) {

                    $IdentifierTypeStr = null;
                    $IdentifierLinkStr = null;

                    if( array_key_exists($index, $IdentifierTypeArr) ) {
                        $IdentifierTypeStr = $IdentifierTypeArr[$index];
                    }
                    if( array_key_exists($index, $IdentifierLinkArr) ) {
                        $IdentifierLinkStr = $IdentifierLinkArr[$index];
                    }

                    $Identifier = new Identifier();
                    $Identifier->setStatus($Identifier::STATUS_VERIFIED);

                    $IdentifierTypeStr = trim($IdentifierTypeStr);
                    $IdentifierLinkStr = trim($IdentifierLinkStr);
                    $IdentifierStr = trim($IdentifierStr);

                    //Identifier
                    $Identifier->setField($IdentifierStr);

                    //Identifier - Type
                    $IdentifierTypeStrObj = $this->getObjectByNameTransformer('IdentifierTypeList',$IdentifierTypeStr,$systemuser);
                    $Identifier->setKeytype($IdentifierTypeStrObj);

                    //Identifier - link
                    $Identifier->setLink($IdentifierLinkStr);

                    $credentials->addIdentifier($Identifier);

                    $index++;
                }
            }

            //Certificate of Qualification - Code
            $CertificateCodeStr = $this->getValueByHeaderName('Certificate of Qualification - Code', $rowData, $headers);
            if( $CertificateCodeStr ) {
                $credentials->setCoqCode($CertificateCodeStr);
            }

            //Certificate of Qualification - Serial Number
            $CertificateSerialNumberStr = $this->getValueByHeaderName('Certificate of Qualification - Serial Number', $rowData, $headers);
            if( $CertificateSerialNumberStr ) {
                $credentials->setNumberCOQ($CertificateSerialNumberStr);
            }

            //Certificate of Qualification - Expiration Date
            $CertificateExpirationDateStr = $this->getValueByHeaderName('Certificate of Qualification - Expiration Date', $rowData, $headers);
            if( $CertificateExpirationDateStr ) {
                $CertificateExpirationDate = $this->transformDatestrToDate($CertificateExpirationDateStr);
                $credentials->setCoqExpirationDate($CertificateExpirationDate);
            }

            //CLIA - Number
            if( $CLIAStr ) {
                $credentials->setNumberCLIA($CLIAStr);
            }

            //CLIA - Expiration Date
            $CLIAExpDateStr = $this->getValueByHeaderName('CLIA - Expiration Date', $rowData, $headers);
            if( $CLIAExpDateStr ) {
                $CLIAExpDate = $this->transformDatestrToDate($CLIAExpDateStr);
                $credentials->setCliaExpirationDate($CLIAExpDate);
            }

            //PFI
            if( $PFI ) {
                $credentials->setNumberPFI($PFI);
            }

            //POPS Link => Identifier Type:POPS, Identifier:link, Link:link
            $POPS = $this->getValueByHeaderName('POPS Link', $rowData, $headers);
            if( $POPS ) {
                $popsIdentifier = new Identifier();
                $popsIdentifier->setStatus($popsIdentifier::STATUS_VERIFIED);
                $popsIdentifier->setPubliclyVisible(true);

                $popsIdentifierTypeObj = $this->getObjectByNameTransformer('IdentifierTypeList','POPS',$systemuser);
                $popsIdentifier->setKeytype($popsIdentifierTypeObj);
                $popsIdentifier->setLink($POPS);
                $popsIdentifier->setField($POPS);

                $credentials->addIdentifier($popsIdentifier);
            }

            //Pubmed Link
            $Pubmed = $this->getValueByHeaderName('Pubmed Link', $rowData, $headers);
            if( $Pubmed ) {
                $PubmedIdentifier = new Identifier();
                $PubmedIdentifier->setStatus($PubmedIdentifier::STATUS_VERIFIED);
                $PubmedIdentifier->setPubliclyVisible(true);

                $PubmedIdentifierTypeObj = $this->getObjectByNameTransformer('IdentifierTypeList','Pubmed',$systemuser);
                $PubmedIdentifier->setKeytype($PubmedIdentifierTypeObj);
                $PubmedIdentifier->setLink($Pubmed);
                $PubmedIdentifier->setField($Pubmed);

                $credentials->addIdentifier($PubmedIdentifier);
            }

            //VIVO link
            $VIVO = $this->getValueByHeaderName('VIVO link', $rowData, $headers);
            if( $VIVO ) {
                $VIVOIdentifier = new Identifier();
                $VIVOIdentifier->setStatus($VIVOIdentifier::STATUS_VERIFIED);
                $VIVOIdentifier->setPubliclyVisible(true);

                $VIVOIdentifierTypeObj = $this->getObjectByNameTransformer('IdentifierTypeList','VIVO',$systemuser);
                $VIVOIdentifier->setKeytype($VIVOIdentifierTypeObj);
                $VIVOIdentifier->setLink($VIVO);
                $VIVOIdentifier->setField($VIVO);

                $credentials->addIdentifier($VIVOIdentifier);
            }



            //add lowest roles for scanorder and userdirectory
            $user->addRole('ROLE_SCANORDER_SUBMITTER');
            $user->addRole('ROLE_USERDIRECTORY_OBSERVER');

            //add Platform Admin role and WCMC Institution for specific users
            //TODO: remove in prod
            if( $user->getUsername() == "cwid1_@_wcmc-cwid" || $user->getUsername() == "cwid2_@_wcmc-cwid" ) {
                $user->addRole('ROLE_PLATFORM_ADMIN');
            }

            //coordinator
            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                $user->addRole('ROLE_USERDIRECTORY_EDITOR');
                //$user->addRole('ROLE_FELLAPP_COORDINATOR');
                $user->addRole('ROLE_FELLAPP_COORDINATOR_WCMC_BREASTPATHOLOGY');
                $user->addRole('ROLE_FELLAPP_COORDINATOR_WCMC_CYTOPATHOLOGY');
                $user->addRole('ROLE_FELLAPP_COORDINATOR_WCMC_GYNECOLOGICPATHOLOGY');
                $user->addRole('ROLE_FELLAPP_COORDINATOR_WCMC_GASTROINTESTINALPATHOLOGY');
                $user->addRole('ROLE_FELLAPP_COORDINATOR_WCMC_GENITOURINARYPATHOLOGY');
                $user->addRole('ROLE_FELLAPP_COORDINATOR_WCMC_HEMATOPATHOLOGY');
                $user->addRole('ROLE_FELLAPP_COORDINATOR_WCMC_MOLECULARGENETICPATHOLOGY');
            }


            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                //$user->addRole('ROLE_FELLAPP_DIRECTOR');
                $user->addRole('ROLE_FELLAPP_DIRECTOR_WCMC_GASTROINTESTINALPATHOLOGY');
            }

            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                //$user->addRole('ROLE_FELLAPP_DIRECTOR');
                $user->addRole('ROLE_FELLAPP_DIRECTOR_WCMC_CYTOPATHOLOGY');
            }

            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                //$user->addRole('ROLE_FELLAPP_DIRECTOR');
                $user->addRole('ROLE_FELLAPP_DIRECTOR_WCMC_HEMATOPATHOLOGY');
            }

            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                //$user->addRole('ROLE_FELLAPP_DIRECTOR');
                $user->addRole('ROLE_FELLAPP_DIRECTOR_WCMC_HEMATOPATHOLOGY');
            }

            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                //$user->addRole('ROLE_FELLAPP_DIRECTOR');
                $user->addRole('ROLE_FELLAPP_DIRECTOR_WCMC_MOLECULARGENETICPATHOLOGY');
            }

            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                //$user->addRole('ROLE_FELLAPP_DIRECTOR');
                $user->addRole('ROLE_FELLAPP_DIRECTOR_WCMC_GYNECOLOGICPATHOLOGY');
            }

            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                //$user->addRole('ROLE_FELLAPP_DIRECTOR');
                $user->addRole('ROLE_FELLAPP_DIRECTOR_WCMC_BREASTPATHOLOGY');
            }

            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                //$user->addRole('ROLE_FELLAPP_DIRECTOR');
                $user->addRole('ROLE_FELLAPP_DIRECTOR_WCMC_BREASTPATHOLOGY');
            }

            if( $user->getUsername() == "cwid_@_wcmc-cwid" ) {
                //$user->addRole('ROLE_FELLAPP_DIRECTOR');
                $user->addRole('ROLE_FELLAPP_DIRECTOR_WCMC_BREASTPATHOLOGY');
            }

            //************** get Aperio group roles and ROLE_SCANORDER_ORDERING_PROVIDER for this user **************//
            //TODO: this should be located on scanorder site
            //TODO: rewrite using Aperio's DB not SOAP functions
            $aperioUtil = new AperioUtil();
            echo "username=".$username."<br>";
            $userid = $aperioUtil->getUserIdByUserName($username);
            if( $userid ) {
                echo "userid=".$userid."<br>";
                $aperioRoles = $aperioUtil->getUserGroupMembership($userid);
                $stats = $aperioUtil->setUserPathologyRolesByAperioRoles( $user, $aperioRoles );
            }
            //************** end of  Aperio group roles **************//

            $user->setEnabled(true);
            $user->setLocked(false);
            $user->setExpired(false);

//            $found_user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername( $user->getUsername() );
//            if( $found_user ) {
//                //
//            } else {
            //echo $username." not found ";
            $this->em->persist($user);
            $this->em->flush();
            $count++;


            //Assistants : s2id_oleg_userdirectorybundle_user_locations_0_assistant
            $assistants = $this->getValueByHeaderName('Assistants', $rowData, $headers);
            if( $assistants ) {
                $assistantsArr[$user->getId()] = $assistants;
            }


            //**************** create PerSiteSettings for this user **************//
            //TODO: this should be located on scanorder site
            $securityUtil = $this->container->get('order_security_utility');
            $perSiteSettings = $securityUtil->getUserPerSiteSettings($user);
            if( !$perSiteSettings ) {
                $perSiteSettings = new PerSiteSettings($systemuser);
                $perSiteSettings->setUser($user);
            }
            $params = $this->em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
            if( count($params) != 1 ) {
                throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
            }
            $param = $params[0];
            $institution = $param->getAutoAssignInstitution();
            $perSiteSettings->addPermittedInstitutionalPHIScope($institution);
            $this->em->persist($perSiteSettings);
            $this->em->flush();
            //**************** EOF create PerSiteSettings for this user **************//

            //record user log create
            $event = "User ".$user." has been created by ".$systemuser."<br>";
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'),$event,$systemuser,$user,null,'New user record added');
//            }

            //exit('eof user');

        }//for each user


        //process assistants
        echo "count ass=".count($assistantsArr)."<br>";
        if( count($assistantsArr) > 0 ) {
            foreach( $assistantsArr as $userid => $assistants ) {

                echo "userid=".$userid."assistants=".$assistants."<br>";
                $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($userid);
                $assistantsStrArr = explode(";",$assistants);

                foreach( $assistantsStrArr as $assistantsStr ) {
                    if( strtolower($assistantsStr) != 'null' ) {
                        $assistant = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByNameStr($assistantsStr,"AND");
                        if( !$assistant ) {
                            //try again with "last name OR first name"
                            $assistant = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByNameStr($assistantsStr,"OR");
                        }
                        echo "found assistant=".$assistant."<br>";
                        if( $assistant ) {
                            $mainLocation = $user->getMainLocation();
                            $mainLocation->addAssistant($assistant);
                        }

                    }
                } //foreach

                if( count($assistantsStrArr) > 0 ) {
                    $this->em->flush();
                }

            } //foreach
        } //if


        //exit();
        return $count;
    }



    public function getMergedRangeBySectionName($sectionName,$sections,$sheet) {
        $mergeRange = null;
        if( !$sectionName ) {
            return $mergeRange;
        }

        $sectionKey = array_search($sectionName, $sections[0]);
        //echo "<br>sectionKey=".$sectionKey."<br>";

        $cell = $sheet->getCellByColumnAndRow($sectionKey, 1);

        //$val = $cell->getValue();
        //echo "val=".$val."<br>";

        $mergeRange = $this->getMergeRange($cell,$sheet);
        //echo "mergeRange=".$mergeRange."<br>";

        return $mergeRange;
    }
    public function getMergeRange($cell,$sheet)
    {
        foreach ($sheet->getMergeCells() as $mergeRange) {
            if ($cell->isInRange($mergeRange)) {
                return $mergeRange;
            }
        }
        return false;
    }


    //$sectionName - "Name and Preferred Contact Info"
    //$header - "Primary Public User ID"
    public function getValueBySectionHeaderName( $header, $row, $headers, $range=null ) {
        $res = null;
        if( !$header ) {
            return $res;
        }

        $header = trim($header);

        if( !$range ) {
            return $this->getValueByHeaderName($header,$row,$headers);
        }

        $rangeColumnArr = $columnIndex = \PHPExcel_Cell::rangeBoundaries($range);
        $startColumn = $rangeColumnArr[0][0]; //52
        $endColumn = $rangeColumnArr[1][0];   //79
        //echo "<br>".$header.": startColumn=".$startColumn."; endColumn=".$endColumn."<br>";

        //echo "header=".$header."<br>";
        //echo "<pre>";
        //print_r($headers);
        //echo "</pre>";
        //print_r($row[0]);

        //1) find section cell range
        //$sectionKey = array_search($header, $headers[0]);
        //echo "<br>sectionKey=".$sectionKey."<br>";
        $found = false;
        $sectionKey = 1;
        foreach( $headers[0] as $thisHeader ) {
            $thisHeader = trim($thisHeader);
            $thisHeader = trim($thisHeader,chr(0xC2).chr(0xA0)); //remove non-breaking spaces
            //echo "?match thisHeader=[".$thisHeader."]<br>";
            if( $header == $thisHeader ) {
                //echo "match sectionKey=".$sectionKey."<br>"; //52, 80
                if( $sectionKey >= $startColumn && $sectionKey <= $endColumn ) {
                    //echo "InRange: sectionKey=".$sectionKey."<br>";
                    $found = true;
                    break;
                }
            }
            $sectionKey++;
        }

        if( !$found ) {
            exit("[".$header."] not found");
        }

        //shift sectionKey to start from zero
        $sectionKey = $sectionKey - 1;
        //echo "sectionKey=".$sectionKey."<br>";

        if( array_key_exists($sectionKey, $row[0]) ) {
            $res = $row[0][$sectionKey];
            $res = trim($res);
        }
        echo $header.": res=[".$res."]<br>";
        return $res;
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
            $res = trim($res);
        }

        //echo "res=".$res."<br>";
        return $res;
    }


    //add two default locations: Home and Main Office
    public function addDefaultLocations($subjectUser,$creator) {

        $em = $this->em;
        $container = $this->container;

        if( $creator == null ) {
            $userSecUtil = $container->get('user_security_utility');
            $creator = $userSecUtil->findSystemUser();

            if( !$creator ) {
                $creator = $subjectUser;
            }
        }

        //echo "creator=".$creator.", id=".$creator->getId()."<br>";

        //Main Office Location
        $mainLocation = new Location($creator);
        $mainLocation->setName('Main Office');
        $mainLocation->setRemovable(false);
        $mainLocType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Employee Office");
        $mainLocation->addLocationType($mainLocType);
        $subjectUser->addLocation($mainLocation);

        //Home Location
        $homeLocation = new Location($creator);
        $homeLocation->setName('Home');
        $homeLocation->setRemovable(false);
        $homeLocType = $em->getRepository('OlegUserdirectoryBundle:LocationTypeList')->findOneByName("Employee Home");
        $homeLocation->addLocationType($homeLocType);
        $subjectUser->addLocation($homeLocation);

        return $subjectUser;
    }

    public function getObjectByNameTransformer($className,$nameStr,$systemuser,$params=null) {
        $bundleName = null;
        $transformer = new GenericTreeTransformer($this->em, $systemuser, $className, $bundleName, $params);
        $nameStr = trim($nameStr);
        return $transformer->reverseTransform($nameStr);
    }

    public function getObjectByNameTransformerWithoutCreating($className,$nameStr,$systemuser,$params=null) {
        $bundleName = null;
        $transformer = new GenericSelectTransformer($this->em, $systemuser, $className, $bundleName, $params);
        $nameStr = trim($nameStr);
        return $transformer->reverseTransform($nameStr);
    }


    //$Institution, $Department, $HeadDepartment, $Division, $HeadDivision, $Service, $HeadService can be separated by ";"
    public function addInstitutinalTree( $holderClassName, $subjectUser, $systemuser, $titles, $Institution, $Department, $HeadDepartment, $Division, $HeadDivision, $Service, $HeadService ) {

        $holders = array();

        //echo "titles=".$titles."<br>";
        //echo "Institution=".$Institution."<br>";

        $titleArr = explode(";", $titles);

        $InstitutionArr = explode(";", $Institution);
        $DepartmentArr = explode(";", $Department);
        $DivisionArr = explode(";", $Division);
        $ServiceArr = explode(";", $Service);

        $HeadDepartmentArr = explode(";", $HeadDepartment);
        $HeadDivisionArr = explode(";", $HeadDivision);
        $HeadServiceArr = explode(";", $HeadService);

        //remove empty from array
        for( $i=0; $i<count($titleArr); $i++ ) {
            echo "el=".$titleArr[$i]."<br>";
            if( trim($titleArr[$i]) == "" ) {
                unset($titleArr[$i]);
            }
        }
        for( $i=0; $i<count($InstitutionArr); $i++ ) {
            echo "el=(".$InstitutionArr[$i].")<br>";
            if( trim($InstitutionArr[$i]) == "" ) {
                //echo "remove el=".$InstitutionArr[$i]."<br>";
                unset($InstitutionArr[$i]);
            }
        }
        //exit('1');

        if( count($InstitutionArr) != 0 && count($InstitutionArr) != count($titleArr) ) {
            throw new \Exception($holderClassName.': Title count='.count($titleArr).' is not equal to Institution count=' . count($InstitutionArr));
        }

//        //lead can be title or institution
//        if( count($InstitutionArr) > count($titleArr) ) {
//            //lead inst
//            $leadArr = $InstitutionArr;
//            $leadInst = true;
//            //echo "leadArr Inst<br>";
//        } else {
//            $leadArr = $titleArr;
//            $leadInst = false;
//            //echo "leadArr Title<br>";
//        }

        //echo "leadArr count=".count($leadArr)."<br>";

        //$lastInstitutionStr = null;
        //$lastDepartmentStr = null;
        //$lastDivisionStr = null;
        //$lastServiceStr = null;
        //$lastTitleStr = null;

        $index = 0;
        foreach( $titleArr as $titleStr ) {

            //echo "index=".$index."<br>";

            $titleStr = trim($titleStr);

            if( !$titleStr ) {
                continue;
            }

//            $InstitutionStr = null;
//            $titleStr = null;
//            if( $leadInst ) {
//                if( array_key_exists($index, $titleArr) ) {
//                    $titleStr = trim($titleArr[$index]);
//                    $lastTitleStr = $titleStr;
//                } else {
//                    $titleStr = $lastTitleStr;
//                }
//                $InstitutionStr = $leadStr;
//            } else {
//                if( array_key_exists($index, $InstitutionArr) ) {
//                    $InstitutionStr = trim($InstitutionArr[$index]);
//                    $lastInstitutionStr = $InstitutionStr;
//                } else {
//                    $InstitutionStr = $lastInstitutionStr;
//                }
//                $titleStr = $leadStr;
//            }


            $InstitutionStr = null;
            $DepartmentStr = null;
            $DivisionStr = null;
            $ServiceStr = null;

            $HeadDepartmentStr = null;
            $HeadDivisionStr = null;
            $HeadServiceStr = null;


            if( array_key_exists($index, $InstitutionArr) ) {
                $InstitutionStr = trim($InstitutionArr[$index]);
            }

            if( array_key_exists($index, $DepartmentArr) ) {
                $DepartmentStr = trim($DepartmentArr[$index]);
                //$lastDepartmentStr = $DepartmentStr;
            } else {
                //$DepartmentStr = $lastDepartmentStr;
            }

            if( array_key_exists($index, $DivisionArr) ) {
                $DivisionStr = trim($DivisionArr[$index]);
                //$lastDivisionStr = $DivisionStr;
            } else {
                //$DivisionStr = $lastDivisionStr;
            }

            if( array_key_exists($index, $ServiceArr) ) {
                $ServiceStr = trim($ServiceArr[$index]);
                //$lastServiceStr = $ServiceStr;
            } else {
                //$ServiceStr = $lastServiceStr;
            }

            if( array_key_exists($index, $HeadDepartmentArr) ) {
                $HeadDepartmentStr = trim($HeadDepartmentArr[$index]);
            }
            if( array_key_exists($index, $HeadDivisionArr) ) {
                $HeadDivisionStr = trim($HeadDivisionArr[$index]);
            }
            if( array_key_exists($index, $HeadServiceArr) ) {
                $HeadServiceStr = trim($HeadServiceArr[$index]);
            }

            $holder = $this->addSingleInstitutinalTree( $holderClassName,$systemuser,$InstitutionStr,$DepartmentStr,$HeadDepartmentStr,$DivisionStr,$HeadDivisionStr,$ServiceStr,$HeadServiceStr );

            //echo "holders < leadArr=".count($holders)." < ".count($leadArr)."<br>";
            if( !$holder && count($holders) < count($titleArr)-1 ) {
                $entityClass = "Oleg\\UserdirectoryBundle\\Entity\\".$holderClassName;
                $holder = new $entityClass($systemuser);
                $holder->setStatus($holder::STATUS_VERIFIED);
            }

            if( $holder ) {

                $holders[] = $holder;

                //$setMethod = "setName";

                //set title object: Administrative Title
                if( $holderClassName == 'AdministrativeTitle' ) {
                    $titleClassName = 'AdminTitleList';
                }
                if( $holderClassName == 'MedicalTitle' ) {
                    $titleClassName = 'MedicalTitleList';
                }
                if( $holderClassName == 'AppointmentTitle' ) {
                    $titleClassName = 'AppTitleList';
                    //$setMethod = "addPosition";
                }

                $titleObj = $this->getObjectByNameTransformer($titleClassName,$titleStr,$systemuser);
                //$holder->$setMethod($titleObj);
                $holder->setName($titleObj);
                $addMethod = "add".$holderClassName;
                $subjectUser->$addMethod($holder);

            }


            $index++;

        } //foreach

        return $holders;
    }

    public function addSingleInstitutinalTree( $holderClassName,$systemuser,$Institution,$Department,$HeadDepartment,$Division,$HeadDivision,$Service,$HeadService ) {

        $holder = null;

        $Institution = trim($Institution);
        $Department = trim($Department);
        $Division = trim($Division);
        $Service = trim($Service);

        $HeadDepartment = trim($HeadDepartment);
        $HeadDivision = trim($HeadDivision);
        $HeadService = trim($HeadService);

        //echo "Institution=(".$Institution.")<br>";
        if( !$Institution ) {
            //exit('no inst');
            return $holder;
        } else {
            //exit('inst ok');
        }

        $InstitutionObj = null;
        $DepartmentObj = null;
        $DivisionObj = null;
        $ServiceObj = null;

        $mapper = array('prefix'=>'Oleg','bundleName'=>'UserdirectoryBundle','className'=>'Institution','organizationalGroupType'=>'OrganizationalGroupType');

        $params = array('type'=>'Medical');

        $transformer = new GenericTreeTransformer($this->em, $systemuser, $mapper['className'], $mapper['bundleName'], $params);

        if( $Institution && strtolower($Institution) != 'null' ) {

            $entityClass = "Oleg\\UserdirectoryBundle\\Entity\\".$holderClassName;
            $holder = new $entityClass($systemuser);
            $holder->setStatus($holder::STATUS_VERIFIED);

            $InstitutionObj = $this->getObjectByNameTransformer('Institution',$Institution,$systemuser,$params);
            //$InstitutionObj = $transformer->createNewEntity($Institution,$mapper['className'],$systemuser);
            //$levelInstitution = $this->em->getRepository('OlegUserdirectoryBundle:OrganizationalGroupType')->findOneByName('Institution');
            //$InstitutionObj->setOrganizationalGroupType($levelInstitution);

            if( $InstitutionObj ) {
                //set Institution tree node
                $holder->setInstitution($InstitutionObj);
            }
        }

        //department
        if( $Institution && $Department && strtolower($Department) != 'null' && $InstitutionObj ) {

            $DepartmentObj = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent($Department,$InstitutionObj,$mapper);
            if( !$DepartmentObj ) {
                //$DepartmentObj = $this->getObjectByNameTransformer('Institution',$Department,$systemuser,$params);
                $DepartmentObj = $transformer->createNewEntity($Department,$mapper['className'],$systemuser);

                if( !$DepartmentObj->getParent() ) {
                    $InstitutionObj->addChild($DepartmentObj);
                    $organizationalGroupType = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->getDefaultLevelEntity($mapper, 1);
                    $DepartmentObj->setOrganizationalGroupType($organizationalGroupType);
                    $this->em->persist($DepartmentObj);
                } else {
                    if( $DepartmentObj->getParent()->getId() != $InstitutionObj->getId() ) {
                        throw new \Exception('Department: Tree node object ' . $DepartmentObj . ' already has a parent, but it is different: existing pid=' . $DepartmentObj->getParent()->getId() . ', new pid='.$InstitutionObj->getId());
                    }
                }
            }

            if( $DepartmentObj ) {
                if( strtolower($HeadDepartment) == 'yes' ) {
                    $HeadDepartmentObj = $this->getObjectByNameTransformer('PositionTypeList','Head of Department',$systemuser);
                    if( method_exists($holder,'addUserPosition') ) {
                        $holder->addUserPosition($HeadDepartmentObj);
                    }
                }
                //overwrite Institution tree node
                $holder->setInstitution($DepartmentObj);
            }
        }

        //division
        if( $Institution && $Department && $Division && strtolower($Division) != 'null' && $DepartmentObj ) {

            $DivisionObj = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent($Division,$DepartmentObj,$mapper);
            if( !$DivisionObj ) {
                //$DivisionObj = $this->getObjectByNameTransformer('Institution',$Division,$systemuser,$params);
                $DivisionObj = $transformer->createNewEntity($Division,$mapper['className'],$systemuser);

                if( !$DivisionObj->getParent() ) {
                    $DepartmentObj->addChild($DivisionObj);
                    $organizationalGroupType = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->getDefaultLevelEntity($mapper, 2);
                    $DivisionObj->setOrganizationalGroupType($organizationalGroupType);
                    $this->em->persist($DivisionObj);
                } else {
                    if( $DivisionObj->getParent()->getId() != $DepartmentObj->getId() ) {
                        throw new \Exception('Division: Tree node object ' . $DivisionObj . ' already has a parent, but it is different: existing pid=' . $DivisionObj->getParent()->getId() . ', new pid='.$DepartmentObj->getId());
                    }
                }
            }

            if( $DivisionObj ) {
                if( strtolower($HeadDivision) == 'yes' ) {
                    $HeadDivisionObj = $this->getObjectByNameTransformer('PositionTypeList','Head of Division',$systemuser);
                    if( method_exists($holder,'addUserPosition') ) {
                        $holder->addUserPosition($HeadDivisionObj);
                    }
                }
                //overwrite Institution tree node
                $holder->setInstitution($DivisionObj);
            }
        }

        //service
        if( $Institution && $Department && $Division && $Service && strtolower($Service) != 'null' && $DivisionObj ) {

            $ServiceObj = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->findByChildnameAndParent($Service,$DivisionObj,$mapper);
            if( !$ServiceObj ) {
                //$ServiceObj = $this->getObjectByNameTransformer('Institution',$Service,$systemuser,$params);
                $ServiceObj = $transformer->createNewEntity($Service,$mapper['className'],$systemuser);

                if( !$ServiceObj->getParent() ) {
                    $DivisionObj->addChild($ServiceObj);
                    $organizationalGroupType = $this->em->getRepository('OlegUserdirectoryBundle:Institution')->getDefaultLevelEntity($mapper, 3);
                    $ServiceObj->setOrganizationalGroupType($organizationalGroupType);
                    $this->em->persist($ServiceObj);
                } else {
                    if( $ServiceObj->getParent()->getId() != $DivisionObj->getId() ) {
                        throw new \Exception('Service: Tree node object ' . $ServiceObj . ' already has a parent, but it is different: existing pid=' . $ServiceObj->getParent()->getId() . ', new pid='.$DivisionObj->getId());
                    }
                }
            }

            if( $ServiceObj ) {
                if( strtolower($HeadService) == 'yes' ) {
                    $HeadServiceObj = $this->getObjectByNameTransformer('PositionTypeList','Head of Service',$systemuser);
                    if( method_exists($holder,'addUserPosition') ) {
                        $holder->addUserPosition($HeadServiceObj);
                    }
                }
                //overwrite Institution tree node
                $holder->setInstitution($ServiceObj);
            }
        }

//        echo "inst level title=".$InstitutionObj->getOrganizationalGroupType().", level=".$InstitutionObj->getLevel()."<br>";
//        echo "dep level title=".$DepartmentObj->getOrganizationalGroupType().", level=".$DepartmentObj->getLevel()."<br>";
//        echo "div level title=".$DivisionObj->getOrganizationalGroupType().", level=".$DivisionObj->getLevel()."<br>";
//        echo "ser level title=".$ServiceObj->getOrganizationalGroupType().", level=".$ServiceObj->getLevel()."<br>";
//        //exit();

        return $holder;
    }

    public function processBoardCertification($credentials, $systemuser, $rowData, $headers, $boardCertSpec) {
        $boardCertSpecArr = explode(";", $boardCertSpec);

        $CertifyingBoardOrganizationStr = $this->getValueByHeaderName('Certifying Board Organization', $rowData, $headers);
        $CertifyingBoardOrganizationArr = explode(";", $CertifyingBoardOrganizationStr);

        $issueDateStr = $this->getValueByHeaderName('Board Certification - Date Issued', $rowData, $headers);
        $issueDateArr = explode(";", $issueDateStr);

        $expDateStr = $this->getValueByHeaderName('Board Certification - Expiration Date', $rowData, $headers);
        $expDateArr = explode(";", $expDateStr);

        $recertDateStr = $this->getValueByHeaderName('Board Certification - Recertification Date', $rowData, $headers);
        $recertDateArr = explode(";", $recertDateStr);

        $index = 0;
        foreach( $boardCertSpecArr as $boardCertSpecStr ) {

            $issueDate = null;
            $expDate = null;
            $recertDate = null;
            $CertifyingBoardOrganization = null;

            if( array_key_exists($index, $issueDateArr) ) {
                $issueDate = $issueDateArr[$index];
            }
            if( array_key_exists($index, $expDateArr) ) {
                $expDate = $expDateArr[$index];
            }
            if( array_key_exists($index, $recertDateArr) ) {
                $recertDate = $recertDateArr[$index];
            }
            if( array_key_exists($index, $CertifyingBoardOrganizationArr) ) {
                $CertifyingBoardOrganization = $CertifyingBoardOrganizationArr[$index];
            }

            $boardCert = $this->addSingleBoardCertification($systemuser, $boardCertSpecStr, $issueDate, $expDate, $recertDate, $CertifyingBoardOrganization);
            if( $boardCert ) {
                $credentials->addBoardCertification($boardCert);
            }

            $index++;
        }

    }

    public function addSingleBoardCertification($systemuser, $boardCertSpecStr, $issueDate, $expDate, $recertDate, $CertifyingBoardOrganization) {
        if( $boardCertSpecStr && strtolower($boardCertSpecStr) != 'null' ) {
            $boardCert = new BoardCertification();
            $boardCertSpecObj = $this->getObjectByNameTransformer('BoardCertifiedSpecialties',$boardCertSpecStr,$systemuser);
            $boardCert->setSpecialty($boardCertSpecObj);

            //Board Certification - Date Issued
            if( strtolower($issueDate) != 'null' ) {
                $issueDate = $this->transformDatestrToDate($issueDate);
                $boardCert->setIssueDate($issueDate);
            }

            //Board Certification - Expiration Date
            if( strtolower($expDate) != 'null' ) {
                $expDate = $this->transformDatestrToDate($expDate);
                $boardCert->setExpirationDate($expDate);
            }

            //Board Certification - Recertification Date
            if( strtolower($recertDate) != 'null' ) {
                $recertDate = $this->transformDatestrToDate($recertDate);
                $boardCert->setRecertificationDate($recertDate);
            }

            //Certifying Board Organization
            $CertifyingBoardOrganization = 'American Board of Pathology'; //temporary fix => add to user excel
            if( strtolower($CertifyingBoardOrganization) != 'null' ) {
                $CertifyingBoardOrganizationObj = $this->getObjectByNameTransformer('CertifyingBoardOrganization',$CertifyingBoardOrganization,$systemuser);
                $boardCert->setCertifyingBoardOrganization($CertifyingBoardOrganizationObj);
            }

            return $boardCert;
        }
        return null;
    }


    public function transformDatestrToDate($datestr) {

        $userSecUtil = $this->container->get('user_security_utility');
        return $userSecUtil->transformDatestrToDateWithSiteEventLog($datestr,$this->container->getParameter('employees.sitename'));

//        $date = null;
//
//        if( !$datestr ) {
//            return $date;
//        }
//        $datestr = trim($datestr);
//        //echo "###datestr=".$datestr."<br>";
//
//        if( strtotime($datestr) === false ) {
//            // bad format
//            $msg = 'transformDatestrToDate: Bad format of datetime string='.$datestr;
//            //throw new \UnexpectedValueException($msg);
//            $logger = $this->container->get('logger');
//            $logger->error($msg);
//
//            //send email
//            $userSecUtil = $this->container->get('user_security_utility');
//            $systemUser = $userSecUtil->findSystemUser();
//            $event = "Fellowship Applicantions warning: " . $msg;
//            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Warning');
//
//            //exit('bad');
//            return $date;
//        }
//
////        if( !$this->valid_date($datestr) ) {
////            $msg = 'Date string is not valid'.$datestr;
////            throw new \UnexpectedValueException($msg);
////            $logger = $this->container->get('logger');
////            $logger->error($msg);
////        }
//
//        try {
//            $date = new \DateTime($datestr);
//        } catch (Exception $e) {
//            $msg = 'Failed to convert string'.$datestr.'to DateTime:'.$e->getMessage();
//            //throw new \UnexpectedValueException($msg);
//            $logger = $this->container->get('logger');
//            $logger->error($msg);
//        }
//
//        return $date;
    }

} 