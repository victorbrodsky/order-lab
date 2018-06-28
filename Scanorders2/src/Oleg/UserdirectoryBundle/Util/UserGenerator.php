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
use Oleg\UserdirectoryBundle\Entity\GeoLocation;
use Oleg\UserdirectoryBundle\Entity\Institution;
use Oleg\UserdirectoryBundle\Entity\PerSiteSettings;
use Oleg\OrderformBundle\Security\Util\PacsvendorUtil;
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
    private $container;

    private $usernamePrefix = 'wcmc-cwid';

    public function __construct( $em, $container ) {
        $this->em = $em;
        $this->container = $container;
    }


    //create template processing of the main user's fields
    public function generateUsersExcelV2($inputFileName) {

        ini_set('max_execution_time', 3600); //3600 seconds = 60 minutes;

        //$inputFileName = __DIR__ . '/../../../../../importLists/ImportUsersTemplate.xlsx';
        //$inputFileName = __DIR__ . '/../../../../../importLists/UsersFull.xlsx';

        if (file_exists($inputFileName)) {
            //echo "The file $inputFileName exists";
        } else {
            //echo "The file $inputFileName does not exist";
            return "The file $inputFileName does not exist";
        }

        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch( Exception $e ) {
            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $count = 0;

        $em = $this->em;
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

        //echo 'Start Foreach highestRow='.$highestRow."; highestColumn=".$highestColumn."<br>";

        $sectionNameContactInfo = "Name and Preferred Contact Info";
        $sectionNameContactInfoRange = $this->getMergedRangeBySectionName($sectionNameContactInfo,$sections,$sheet);
        //echo "<br>sectionNameContactInfoRange=".$sectionNameContactInfoRange."<br>";

        if( !$sectionNameContactInfoRange ) {
            return "Invalid source excel file: no 'Name and Preferred Contact Info' section has been found in the source file. ";
        }

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

            $username = $this->getValueBySectionHeaderName("Primary Public User ID",$rowData,$headers,$sectionNameContactInfoRange);
            //echo "username(cwid)=".$username."<br>";

            if( !$username ) {
                echo "No Primary Public User ID (cwid) has been found in the source file";
                continue; //ignore users without cwid
            }

            $usernamePrefix = null;
            $userTypeName = $this->getValueBySectionHeaderName("Primary Public User ID Type",$rowData,$headers,$sectionNameContactInfoRange);
            //echo "userTypeName=".$userTypeName."<br>";
            $userType = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findOneByName($userTypeName);
            if( $userType ) {
                $usernamePrefix = $userType->getAbbreviation();
            }

//            if( $userType == "WCM CWID" ) {
//                $usernamePrefix = $this->usernamePrefix;
//            }
//            if( $userType == "Local User" ) {
//                $usernamePrefix = "local-user";
//            }
//            if( $userType == "External Authentication" ) {
//                $usernamePrefix = "external";
//            }

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
            //$user->setLocked(false);
            //$user->setExpired(false);

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
            echo "<br>sectionGlobalRange=".$sectionGlobalRange."<br>";

            $roles = $this->getValueBySectionHeaderName("Role",$rowData,$headers,$sectionGlobalRange);
            $timeZone = $this->getValueBySectionHeaderName("Time Zone",$rowData,$headers,$sectionGlobalRange);

            $language = $this->getValueBySectionHeaderName("Language",$rowData,$headers,$sectionGlobalRange);
            $languageObject = $this->getObjectByNameTransformerWithoutCreating("LanguageList",$language,$systemuser);

            $locale = $this->getValueBySectionHeaderName("Locale",$rowData,$headers,$sectionGlobalRange);
            $localeObject = $this->getObjectByNameTransformerWithoutCreating("LocaleList",$locale,$systemuser);

            //Roles
            $rolesObjects = $this->processMultipleListObjects($roles,$systemuser,"Roles");
            $user->setRoles($rolesObjects);

            $user->getPreferences()->setTimezone($timeZone);
            $user->getPreferences()->addLanguage($languageObject);
            $user->getPreferences()->setLocale($localeObject);
            ////////////// EOF Section: Global User Preferences ////////////////



            ////////////// Section: Employment Period ////////////////
            $sectionEmployment = "Employment Period";
            $sectionEmploymentRange = $this->getMergedRangeBySectionName($sectionEmployment,$sections,$sheet);
            echo "<br>sectionEmploymentRange=".$sectionEmploymentRange."<br>";

            //user_employmentStatus_0_hireDate
            $dateHire = $this->getValueBySectionHeaderName("Date of Hire (MM/DD/YYYY)",$rowData,$headers,$sectionEmploymentRange);
            //$dateHire = date("m/d/Y", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateHire));
            //echo "dateHire=".$dateHire."<br>";
            //$dateHire = \PHPExcel_Shared_Date::ExcelToPHP($dateHire);
            $dateHire = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateHire);
            //$dateHire = new \DateTime(@$dateHire);
            //echo "dateHire=".$dateHire."<br>";
            $dateHire = new \DateTime("@$dateHire");
            //echo "dateHire=".$dateHire->format('m/d/Y')."<br>";

            //Employee Type
            $employeeType = $this->getValueBySectionHeaderName("Employee Type",$rowData,$headers,$sectionEmploymentRange);
            //echo "employeeType=".$employeeType."<br>";
            $employeeTypeObject = $this->getObjectByNameTransformerWithoutCreating("EmploymentType",$employeeType,$systemuser);
            //echo "employeeTypeObject=".$employeeTypeObject."<br>";

            $jobDescriptionSummary = $this->getValueBySectionHeaderName("Job Description Summary",$rowData,$headers,$sectionEmploymentRange);
            $jobDescription = $this->getValueBySectionHeaderName("Job Description (official, as posted)",$rowData,$headers,$sectionEmploymentRange);
            //echo "jobDescriptionSummary=".$jobDescriptionSummary."<br>";

            //Institution
            $institutionStr = $this->getValueBySectionHeaderName("Institution",$rowData,$headers,$sectionEmploymentRange);
            $departmentStr = $this->getValueBySectionHeaderName("Department",$rowData,$headers,$sectionEmploymentRange);
            $divisionStr = $this->getValueBySectionHeaderName("Division",$rowData,$headers,$sectionEmploymentRange);
            $serviceStr = $this->getValueBySectionHeaderName("Service",$rowData,$headers,$sectionEmploymentRange);
            //echo "inst=".$institutionStr."; $departmentStr; $divisionStr; $serviceStr"."<br>";
            $institutionObject = $this->getEntityByInstitutionDepartmentDivisionService($institutionStr,$departmentStr,$divisionStr,$serviceStr);
            //echo "institutionObject=".$institutionObject."<br>";

            //End of Employment Date (MM/DD/YYYY)
            $endHire = $this->getValueBySectionHeaderName("End of Employment Date (MM/DD/YYYY)",$rowData,$headers,$sectionEmploymentRange);
            $endHire = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($endHire);
            $endHire = new \DateTime("@$endHire");
            //echo "endHire=".$endHire->format('m/d/Y')."<br>";

            //Type of End of Employment
            $employeeEndType = $this->getValueBySectionHeaderName("Type of End of Employment",$rowData,$headers,$sectionEmploymentRange);
            $employeeEndTypeObject = $this->getObjectByNameTransformerWithoutCreating("EmploymentTerminationType",$employeeEndType,$systemuser);

            $employeeEndReason = $this->getValueBySectionHeaderName("Reason for End of Employment",$rowData,$headers,$sectionEmploymentRange);

            if(
                $dateHire || $employeeTypeObject || $jobDescriptionSummary || $jobDescription ||
                $institutionObject || $endHire || $employeeEndTypeObject || $employeeEndReason
            ) {
                $employmentStatus = new EmploymentStatus($systemuser);
                $user->addEmploymentStatus($employmentStatus);

                $employmentStatus->setHireDate($dateHire);
                $employmentStatus->setEmploymentType($employeeTypeObject);
                $employmentStatus->setJobDescriptionSummary($jobDescriptionSummary);
                $employmentStatus->setJobDescription($jobDescription);
                $employmentStatus->setInstitution($institutionObject);

                $employmentStatus->setTerminationDate($endHire);
                $employmentStatus->setTerminationType($employeeEndTypeObject);
                $employmentStatus->setTerminationReason($employeeEndReason);
            }
            ////////////// EOF Section: Employment Period ////////////////



            ////////////// Section: Administrative Title ////////////////
            $sectionAdministrativeTitle = "Administrative Title";
            $sectionAdministrativeTitleRange = $this->getMergedRangeBySectionName($sectionAdministrativeTitle,$sections,$sheet);
            echo "<br>sectionAdministrativeTitleRange=".$sectionAdministrativeTitleRange."<br>";

            //Title (AdminTitleList)
            $administrativeTitle = $this->getValueBySectionHeaderName("Title",$rowData,$headers,$sectionAdministrativeTitleRange);
            $administrativeTitleObject = $this->getObjectByNameTransformerWithoutCreating("AdminTitleList",$administrativeTitle,$systemuser);
            echo "administrativeTitleObject=".$administrativeTitleObject."<br>";

            $administrativeStart = $this->getValueBySectionHeaderName("Start Date (MM/DD/YYYY)",$rowData,$headers,$sectionAdministrativeTitleRange);
            $administrativeStart = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($administrativeStart);
            $administrativeStart = new \DateTime("@$administrativeStart");
            //echo "administrativeStart=".$administrativeStart->format('m/d/Y')."<br>";

            $administrativeEnd = $this->getValueBySectionHeaderName("End Date (MM/DD/YYYY)",$rowData,$headers,$sectionAdministrativeTitleRange);
            $administrativeEnd = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($administrativeEnd);
            $administrativeEnd = new \DateTime("@$administrativeEnd");
            //echo "administrativeEnd=".$administrativeEnd->format('m/d/Y')."<br>";

            //Institution
            $institutionStr = $this->getValueBySectionHeaderName("Institution",$rowData,$headers,$sectionAdministrativeTitleRange);
            $departmentStr = $this->getValueBySectionHeaderName("Department",$rowData,$headers,$sectionAdministrativeTitleRange);
            $divisionStr = $this->getValueBySectionHeaderName("Division",$rowData,$headers,$sectionAdministrativeTitleRange);
            $serviceStr = $this->getValueBySectionHeaderName("Service",$rowData,$headers,$sectionAdministrativeTitleRange);
            //echo "inst=".$institutionStr."; $departmentStr; $divisionStr; $serviceStr"."<br>";
            $institutionObject = $this->getEntityByInstitutionDepartmentDivisionService($institutionStr,$departmentStr,$divisionStr,$serviceStr);
            echo "institutionObject=".$institutionObject."<br>";

            //Position Type (multiple PositionTypeList)
            $administrativePositionType = $this->getValueBySectionHeaderName("Position Type",$rowData,$headers,$sectionAdministrativeTitleRange);
            echo "multiple administrativePositionType=".$administrativePositionType."<br>";
            $administrativePositionTypeObjects = $this->processMultipleListObjects($administrativePositionType,$systemuser,"PositionTypeList");

            if(
                $administrativeTitleObject || $administrativeStart ||
                $administrativeEnd || $institutionObject || $administrativePositionTypeObjects
            ) {
                $administrativeTitle = new AdministrativeTitle($systemuser);
                $administrativeTitle->setStatus($administrativeTitle::STATUS_VERIFIED);
                $user->addAdministrativeTitle($administrativeTitle);

                $administrativeTitle->setName($administrativeTitleObject);
                $administrativeTitle->setStartDate($administrativeStart);
                $administrativeTitle->setEndDate($administrativeEnd);
                $administrativeTitle->setInstitution($institutionObject);

                //multiple
                foreach( $administrativePositionTypeObjects as $administrativePositionTypeObject ) {
                    $administrativeTitle->addUserPosition($administrativePositionTypeObject);
                    echo "added $administrativePositionTypeObject<br>";
                }

                echo "administrativeTitle=".$administrativeTitle."<br>";
            }
            ////////////// EOF Section: Administrative Title ////////////////



            ////////////// Section: Academic Appointment Title ////////////////
            $sectionAcademicTitle = "Academic Appointment Title";
            $sectionAcademicTitleRange = $this->getMergedRangeBySectionName($sectionAcademicTitle,$sections,$sheet);
            echo "<br>sectionAcademicTitleRange=".$sectionAcademicTitleRange."<br>";

            //Title (AppTitleList)
            $academicTitle = $this->getValueBySectionHeaderName("Title",$rowData,$headers,$sectionAcademicTitleRange);
            $academicTitleObject = $this->getObjectByNameTransformerWithoutCreating("AppTitleList",$academicTitle,$systemuser);
            echo "academicTitleObject=".$academicTitleObject."<br>";

            //Position Track Type (multiple PositionTrackTypeList)
            $academicPositions = $this->getValueBySectionHeaderName("Position Track Type",$rowData,$headers,$sectionAcademicTitleRange);
            //$academicTrackTypeObject = $this->getObjectByNameTransformerWithoutCreating("PositionTrackTypeList",$academicTrackType,$systemuser);
            $academicPositionsObjects = $this->processMultipleListObjects($academicPositions,$systemuser,"PositionTrackTypeList");

            $academicStart = $this->getValueBySectionHeaderName("Start Date (MM/DD/YYYY)",$rowData,$headers,$sectionAcademicTitleRange);
            $academicStart = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($academicStart);
            $academicStart = new \DateTime("@$academicStart");
            //echo "academicStart=".$academicStart->format('m/d/Y')."<br>";

            $academicEnd = $this->getValueBySectionHeaderName("End Date (MM/DD/YYYY)",$rowData,$headers,$sectionAcademicTitleRange);
            $academicEnd = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($academicEnd);
            $academicEnd = new \DateTime("@$academicEnd");
            //echo "academicEnd=".$academicEnd->format('m/d/Y')."<br>";

            //Institution
            $institutionStr = $this->getValueBySectionHeaderName("Institution",$rowData,$headers,$sectionAcademicTitleRange);
            $departmentStr = $this->getValueBySectionHeaderName("Department",$rowData,$headers,$sectionAcademicTitleRange);
            $divisionStr = $this->getValueBySectionHeaderName("Division",$rowData,$headers,$sectionAcademicTitleRange);
            $serviceStr = $this->getValueBySectionHeaderName("Service",$rowData,$headers,$sectionAcademicTitleRange);
            //echo "inst=".$institutionStr."; $departmentStr; $divisionStr; $serviceStr"."<br>";
            $institutionObject = $this->getEntityByInstitutionDepartmentDivisionService($institutionStr,$departmentStr,$divisionStr,$serviceStr);
            echo "institutionObject=".$institutionObject."<br>";

            if(
                $academicTitleObject || $academicPositionsObjects ||
                $academicStart || $academicEnd || $institutionObject
            ) {
                $academicTitle = new AppointmentTitle($systemuser);
                $academicTitle->setStatus($academicTitle::STATUS_VERIFIED);
                $user->addAppointmentTitle($academicTitle);

                $academicTitle->setName($academicTitleObject);
                $academicTitle->setStartDate($academicStart);
                $academicTitle->setEndDate($academicEnd);
                $academicTitle->setInstitution($institutionObject);

                //multiple
                foreach( $academicPositionsObjects as $academicPositionsObject ) {
                    $academicTitle->addPosition($academicPositionsObject);
                    echo "added $academicPositionsObject<br>";
                }

                echo "academicTitle=".$academicTitle."<br>";
            }
            ////////////// EOF Section: Academic Appointment Title ////////////////



            ////////////// Section: Medical Appointment Title ////////////////
            $sectionMedicalTitle = "Medical Appointment Title";
            $sectionMedicalTitleRange = $this->getMergedRangeBySectionName($sectionMedicalTitle,$sections,$sheet);
            echo "<br>sectionMedicalTitleRange=".$sectionMedicalTitleRange."<br>";

            //Title (MedicalTitleList)
            $medicalTitle = $this->getValueBySectionHeaderName("Title",$rowData,$headers,$sectionMedicalTitleRange);
            $medicalTitleObject = $this->getObjectByNameTransformerWithoutCreating("MedicalTitleList",$medicalTitle,$systemuser);
            echo "medicalTitleObject=".$medicalTitleObject."<br>";

            //Specialty (multiple MedicalSpecialties)
            $medicalSpecialties = $this->getValueBySectionHeaderName("Specialty",$rowData,$headers,$sectionMedicalTitleRange);
            //$medicalSpecialtyObject = $this->getObjectByNameTransformerWithoutCreating("MedicalTitleList",$medicalSpecialty,$systemuser);
            $medicalSpecialtyObjects = $this->processMultipleListObjects($medicalSpecialties,$systemuser,"MedicalSpecialties");

            $medicalStart = $this->getValueBySectionHeaderName("Start Date (MM/DD/YYYY)",$rowData,$headers,$sectionMedicalTitleRange);
            $medicalStart = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($medicalStart);
            $medicalStart = new \DateTime("@$medicalStart");
            //echo "medicalStart=".$medicalStart->format('m/d/Y')."<br>";

            $medicalEnd = $this->getValueBySectionHeaderName("End Date (MM/DD/YYYY)",$rowData,$headers,$sectionMedicalTitleRange);
            $medicalEnd = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($medicalEnd);
            $medicalEnd = new \DateTime("@$medicalEnd");
            //echo "medicalEnd=".$medicalEnd->format('m/d/Y')."<br>";

            //Institution
            $institutionStr = $this->getValueBySectionHeaderName("Institution",$rowData,$headers,$sectionMedicalTitleRange);
            $departmentStr = $this->getValueBySectionHeaderName("Department",$rowData,$headers,$sectionMedicalTitleRange);
            $divisionStr = $this->getValueBySectionHeaderName("Division",$rowData,$headers,$sectionMedicalTitleRange);
            $serviceStr = $this->getValueBySectionHeaderName("Service",$rowData,$headers,$sectionMedicalTitleRange);
            //echo "inst=".$institutionStr."; $departmentStr; $divisionStr; $serviceStr"."<br>";
            $institutionObject = $this->getEntityByInstitutionDepartmentDivisionService($institutionStr,$departmentStr,$divisionStr,$serviceStr);
            echo "institutionObject=".$institutionObject."<br>";

            //Position Type (multiple PositionTypeList)
            $medicalPositionTypes = $this->getValueBySectionHeaderName("Position Type",$rowData,$headers,$sectionMedicalTitleRange);
            //$medicalPositionTypeObject = $this->getObjectByNameTransformerWithoutCreating("PositionTypeList",$medicalPositionType,$systemuser);
            $medicalPositionTypeObjects = $this->processMultipleListObjects($medicalPositionTypes,$systemuser,"PositionTypeList");
            //echo "medicalPositionTypeObject=".$medicalPositionTypeObject."<br>";

            if(
                $medicalTitleObject || $medicalSpecialtyObjects ||
                $medicalStart || $medicalEnd || $institutionObject || $medicalPositionTypeObjects
            ) {
                $medicalTitle = new MedicalTitle($systemuser);
                $medicalTitle->setStatus($medicalTitle::STATUS_VERIFIED);
                $user->addMedicalTitle($medicalTitle);

                $medicalTitle->setName($medicalTitleObject);
                $medicalTitle->setStartDate($medicalStart);
                $medicalTitle->setEndDate($medicalEnd);
                $medicalTitle->setInstitution($institutionObject);

                //multiple
                foreach( $medicalSpecialtyObjects as $medicalSpecialtyObject ) {
                    $medicalTitle->addSpecialty($medicalSpecialtyObject);
                    echo "addSpecialty: added $medicalSpecialtyObject<br>";
                }
                foreach( $medicalPositionTypeObjects as $medicalPositionTypeObject ) {
                    $medicalTitle->addUserPosition($medicalPositionTypeObject);
                    echo "addUserPosition: added $medicalPositionTypeObject<br>";
                }

                echo "medicalTitle=".$medicalTitle."<br>";
            }
            ////////////// EOF Section: Medical Appointment Title ////////////////



            ////////////// Section: Location 1 ////////////////
            $this->processLocation("Location 1",$user,$systemuser,$sections,$sheet,$rowData,$headers);
            $this->processLocation("Location 2",$user,$systemuser,$sections,$sheet,$rowData,$headers);
            ////////////// EOF Section: Location 1 ////////////////



            ////////////// Section: Education ////////////////
            $sectionEducation = "Education";
            $sectionEducationRange = $this->getMergedRangeBySectionName($sectionEducation,$sections,$sheet);
            echo "<br>sectionEducationRange=".$sectionEducationRange."<br>";

            //Degree (TrainingDegreeList)
            $degree = $this->getValueBySectionHeaderName("Degree",$rowData,$headers,$sectionEducationRange);
            //                                                  $className,$nameStr,$systemuser
            $degreeObjects = $this->getObjectByNameTransformer("TrainingDegreeList",$degree,$systemuser);

            $appendDegree = $this->getValueBySectionHeaderName("Append degree to name",$rowData,$headers,$sectionEducationRange);

            //Residency Specialty (ResidencySpecialty)
            $residencySpecialty = $this->getValueBySectionHeaderName("Residency Specialty",$rowData,$headers,$sectionEducationRange);
            $residencySpecialtyObjects = $this->getObjectByNameTransformer("ResidencySpecialty",$residencySpecialty,$systemuser);

            //Fellowship Subspecialty (FellowshipSubspecialty)
            $fellowshipSubspecialty = $this->getValueBySectionHeaderName("Fellowship Subspecialty",$rowData,$headers,$sectionEducationRange);
            $fellowshipSubspecialtyObjects = $this->getObjectByNameTransformer("FellowshipSubspecialty",$fellowshipSubspecialty,$systemuser);

            //Educational Institution (Institution)
            $educationalInstitution = $this->getValueBySectionHeaderName("Educational Institution",$rowData,$headers,$sectionEducationRange);
            $educationalInstitutionObjects = $this->getObjectByNameTransformer("Institution",$educationalInstitution,$systemuser);

            //Start Date (MM/DD/YYYY)
            $educationStartEnd = $this->getValueBySectionHeaderName("Start Date (MM/DD/YYYY)",$rowData,$headers,$sectionEducationRange);
            $educationStartEnd = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($educationStartEnd);
            $educationStartEnd = new \DateTime("@$educationStartEnd");
            echo "educationStartEnd=".$educationStartEnd->format('m/d/Y')."<br>";

            //Completion Date (MM/DD/YYYY)
            $educationCompletionEnd = $this->getValueBySectionHeaderName("Completion Date (MM/DD/YYYY)",$rowData,$headers,$sectionEducationRange);
            $educationCompletionEnd = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($educationCompletionEnd);
            $educationCompletionEnd = new \DateTime("@$educationCompletionEnd");
            echo "educationCompletionEnd=".$educationCompletionEnd->format('m/d/Y')."<br>";

            //Completion Reason (CompletionReasonList)
            $completionReason = $this->getValueBySectionHeaderName("Completion Reason",$rowData,$headers,$sectionEducationRange);
            $completionReasonObjects = $this->getObjectByNameTransformerWithoutCreating("CompletionReasonList",$completionReason,$systemuser);

            //Professional Fellowship Title (FellowshipTitleList)
            $professionalFellowshipTitle = $this->getValueBySectionHeaderName("Professional Fellowship Title",$rowData,$headers,$sectionEducationRange);
            $professionalFellowshipTitleObjects = $this->getObjectByNameTransformerWithoutCreating("FellowshipTitleList",$professionalFellowshipTitle,$systemuser);

            $appendProfessionalFellowshipToName = $this->getValueBySectionHeaderName("Append professional fellowship to name",$rowData,$headers,$sectionEducationRange);

            if(
                $degreeObjects || $appendDegree || $residencySpecialtyObjects || $fellowshipSubspecialtyObjects ||
                $educationalInstitutionObjects || $educationStartEnd || $educationCompletionEnd || $completionReasonObjects ||
                $professionalFellowshipTitleObjects || $appendProfessionalFellowshipToName
            ) {
                $training = new Training($systemuser);
                $training->setStatus($training::STATUS_VERIFIED);
                $user->addTraining($training);

                $training->setDegree($degreeObjects);
                $training->setAppendDegreeToName($appendDegree);
                $training->setResidencySpecialty($residencySpecialtyObjects);
                $training->setFellowshipSubspecialty($fellowshipSubspecialtyObjects);
                $training->setInstitution($educationalInstitutionObjects);
                $training->setStartDate($educationStartEnd);
                $training->setCompletionDate($educationCompletionEnd);
                $training->setCompletionReason($completionReasonObjects);
                $training->setFellowshipTitle($professionalFellowshipTitleObjects);
                $training->setAppendFellowshipTitleToName($appendProfessionalFellowshipToName);
            }
            ////////////// EOF Section: Education ////////////////



            ////////////// Section: Research Lab ////////////////
            $sectionResearch = "Research Lab";
            $sectionResearchRange = $this->getMergedRangeBySectionName($sectionResearch,$sections,$sheet);
            echo "<br>sectionResearchRange=".$sectionResearchRange."<br>";

            //Research Lab Title (Institution)
            $researchLabTitle = $this->getValueBySectionHeaderName("Research Lab Title",$rowData,$headers,$sectionResearchRange);
            $researchLabTitleObject = $this->getObjectByNameTransformerWithoutCreating("Institution",$researchLabTitle,$systemuser);

            //Research Lab Other Title (Not Institution)
            $researchLabOtherTitle = $this->getValueBySectionHeaderName("Research Lab Other Title (Not Institution)",$rowData,$headers,$sectionResearchRange);

            //Founded on (MM/DD/YYYY)
            $researchLabFounded = $this->getValueBySectionHeaderName("Founded on (MM/DD/YYYY)",$rowData,$headers,$sectionResearchRange);
            $researchLabFounded = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($researchLabFounded);
            $researchLabFounded = new \DateTime("@$researchLabFounded");
            echo "researchLabFounded=".$researchLabFounded->format('m/d/Y')."<br>";

            //Dissolved on (MM/DD/YYYY)
            $researchLabDissolved = $this->getValueBySectionHeaderName("Dissolved on (MM/DD/YYYY)",$rowData,$headers,$sectionResearchRange);
            $researchLabDissolved = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($researchLabDissolved);
            $researchLabDissolved = new \DateTime("@$researchLabDissolved");
            echo "researchLabDissolved=".$researchLabDissolved->format('m/d/Y')."<br>";

            //Web page link
            $webPagelink = $this->getValueBySectionHeaderName("Web page link",$rowData,$headers,$sectionResearchRange);

            if(
                $researchLabTitleObject || $researchLabOtherTitle ||
                $researchLabFounded || $researchLabDissolved || $webPagelink
            ) {

                //find reseach lab by name $researchLabOtherTitle
                $researchLab = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->findOneByName($researchLabOtherTitle);
                if( !$researchLab ) {
                    $researchLab = new ResearchLab($systemuser);
                    $user->addResearchLab($researchLab);

                    $researchLab->setInstitution($researchLabTitleObject);
                    $researchLab->setName($researchLabOtherTitle);
                    $researchLab->setFoundedDate($researchLabFounded);
                    $researchLab->setDissolvedDate($researchLabDissolved);
                    $researchLab->setWeblink($webPagelink);
                }
                $user->addResearchLab($researchLab);
            }
            ////////////// EOF Section: Research Lab ////////////////


            //check Credentials
            $credentials = $user->getCredentials();
            if( !$credentials ) {
                exit("Credentails object does not exist in a user object");
            }

            ////////////// Section: Identifier ////////////////
            $sectionIdentifier = "Identifier";
            $sectionIdentifierRange = $this->getMergedRangeBySectionName($sectionIdentifier,$sections,$sheet);
            echo "<br>sectionIdentifierRange=".$sectionIdentifierRange."<br>";

            //Identifier Type (IdentifierTypeList)
            $identifierType = $this->getValueBySectionHeaderName("Identifier Type",$rowData,$headers,$sectionIdentifierRange);
            $identifierTypeObject = $this->getObjectByNameTransformerWithoutCreating("IdentifierTypeList",$identifierType,$systemuser);

            $identifierNumber = $this->getValueBySectionHeaderName("Identifier",$rowData,$headers,$sectionIdentifierRange);
            $identifierEnablesAccess = $this->getValueBySectionHeaderName("Identifier enables system/service access",$rowData,$headers,$sectionIdentifierRange);
            $identifierLink = $this->getValueBySectionHeaderName("Link",$rowData,$headers,$sectionIdentifierRange);

            $identifierObject = null;
            if( $identifierTypeObject || $identifierNumber || $identifierEnablesAccess || $identifierLink ){
                $identifierObject = new Identifier();
                $identifierObject->setStatus($identifierObject::STATUS_VERIFIED);
                $credentials->addIdentifier($identifierObject);

                $identifierObject->setKeytype($identifierTypeObject);
                $identifierObject->setField($identifierNumber);
                $identifierObject->setEnableAccess($identifierEnablesAccess);
                $identifierObject->setLink($identifierLink);
            }
            ////////////// EOF Section: Identifier ////////////////



            ////////////// Section: Personal Information ////////////////
            $sectionPersonalInformation = "Personal Information";
            $sectionPersonalInformationRange = $this->getMergedRangeBySectionName($sectionPersonalInformation,$sections,$sheet);
            echo "<br>sectionPersonalInformationRange=".$sectionPersonalInformationRange."<br>";

            //Date of Birth (MM/DD/YYYY)
            $dateOfBirth = $this->getValueBySectionHeaderName("Date of Birth (MM/DD/YYYY)",$rowData,$headers,$sectionPersonalInformationRange);
            $dateOfBirth = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateOfBirth);
            $dateOfBirth = new \DateTime("@$dateOfBirth");
            echo "dateOfBirth=".$dateOfBirth->format('m/d/Y')."<br>";

            //Gender (SexList)
            $gender = $this->getValueBySectionHeaderName("Gender",$rowData,$headers,$sectionPersonalInformationRange);
            $genderObject = $this->getObjectByNameTransformerWithoutCreating("SexList",$gender,$systemuser);

            $socialSecurityNumber = $this->getValueBySectionHeaderName("Social Security Number",$rowData,$headers,$sectionPersonalInformationRange);
            $emergencyContactInformation = $this->getValueBySectionHeaderName("Emergency Contact Information",$rowData,$headers,$sectionPersonalInformationRange);

            $credentials->setDob($dateOfBirth);
            $credentials->setSex($genderObject);
            $credentials->setSsn($socialSecurityNumber);
            $credentials->setEmergencyContactInfo($emergencyContactInformation);
            ////////////// EOF Section: Personal Information ////////////////



            ////////////// Section: Certificate of Qualification ////////////////
            $sectionCertificate = "Certificate of Qualification";
            $sectionCertificateRange = $this->getMergedRangeBySectionName($sectionCertificate,$sections,$sheet);
            echo "<br>sectionCertificateRange=".$sectionCertificateRange."<br>";

            //Certificate of Qualification (COQ) Code
            $coq = $this->getValueBySectionHeaderName("Certificate of Qualification (COQ) Code",$rowData,$headers,$sectionCertificateRange);

            //COQ Serial Number
            $coqSerialNumber = $this->getValueBySectionHeaderName("COQ Serial Number",$rowData,$headers,$sectionCertificateRange);
            echo "coqSerialNumber=".$coqSerialNumber."<br>";

            //COQ Expiration Date (MM/DD/YYYY)
            $coqExpirationDate = $this->getValueBySectionHeaderName("COQ Expiration Date (MM/DD/YYYY)",$rowData,$headers,$sectionCertificateRange);
            $coqExpirationDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($coqExpirationDate);
            $coqExpirationDate = new \DateTime("@$coqExpirationDate");
            echo "coqExpirationDate=".$coqExpirationDate->format('m/d/Y')."<br>";

            $credentials->setCoqCode($coq);
            $credentials->setNumberCOQ($coqSerialNumber);
            $credentials->setCoqExpirationDate($coqExpirationDate);
            ////////////// EOF Section: Certificate of Qualification ////////////////



            ////////////// Section: Clinical Laboratory Improvement Amendments (CLIA) ////////////////
            $sectionClia = "Clinical Laboratory Improvement Amendments (CLIA)";
            $sectionCliaRange = $this->getMergedRangeBySectionName($sectionClia,$sections,$sheet);
            echo "<br>sectionCliaRange=".$sectionCliaRange."<br>";

            //Clinical Laboratory Improvement Amendments (CLIA) Number
            $cliaNumber = $this->getValueBySectionHeaderName("Clinical Laboratory Improvement Amendments (CLIA) Number",$rowData,$headers,$sectionCliaRange);
            echo "cliaNumber=".$cliaNumber."<br>";

            //CLIA Expiration Date (MM/DD/YYYY)
            $cliaExpDate = $this->getValueBySectionHeaderName("CLIA Expiration Date (MM/DD/YYYY)",$rowData,$headers,$sectionCliaRange);
            $cliaExpDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($cliaExpDate);
            $cliaExpDate = new \DateTime("@$cliaExpDate");
            echo "cliaExpDate=".$cliaExpDate->format('m/d/Y')."<br>";

            //NY Permanent Facility Identifier (PFI) Number
            $pfiNumber = $this->getValueBySectionHeaderName("NY Permanent Facility Identifier (PFI) Number",$rowData,$headers,$sectionCliaRange);
            echo "pfiNumber=".$pfiNumber."<br>";

            $credentials->setNumberCLIA($cliaNumber);
            $credentials->setCliaExpirationDate($cliaExpDate);
            $credentials->setNumberPFI($pfiNumber);
            ////////////// EOF Section: Clinical Laboratory Improvement Amendments (CLIA) ////////////////



            ////////////// Section: NYPH Code ////////////////
            $sectionNYPHCode = "NYPH Code";
            $sectionNYPHCodeRange = $this->getMergedRangeBySectionName($sectionNYPHCode,$sections,$sheet);
            echo "<br>sectionNYPHCodeRange=".$sectionNYPHCodeRange."<br>";

            //NYPH Code
            $nyphCode = $this->getValueBySectionHeaderName("NYPH Code",$rowData,$headers,$sectionNYPHCodeRange);
            echo "nyphCode=".$nyphCode."<br>";

            //NYPH Code Start Date (MM/DD/YYYY)
            $nyphCodeStartDate = $this->getValueBySectionHeaderName("NYPH Code Start Date (MM/DD/YYYY)",$rowData,$headers,$sectionNYPHCodeRange);
            $nyphCodeStartDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($nyphCodeStartDate);
            $nyphCodeStartDate = new \DateTime("@$nyphCodeStartDate");
            echo "nyphCodeStartDate=".$nyphCodeStartDate->format('m/d/Y')."<br>";

            //NYPH Code End Date (MM/DD/YYYY)
            $nyphCodeEndDate = $this->getValueBySectionHeaderName("NYPH Code End Date (MM/DD/YYYY)",$rowData,$headers,$sectionNYPHCodeRange);
            $nyphCodeEndDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($nyphCodeEndDate);
            $nyphCodeEndDate = new \DateTime("@$nyphCodeEndDate");
            echo "nyphCodeEndDate=".$nyphCodeEndDate->format('m/d/Y')."<br>";

            if( $nyphCode || $nyphCodeStartDate || $nyphCodeEndDate ) {

                $codeNyphs = $credentials->getCodeNyph();
                if( count($codeNyphs) > 0 ) {
                    $codeNyph = $codeNyphs[0];
                    echo "existing codeNyph <br>";
                } else {
                    $codeNyph = new CodeNYPH();
                    $credentials->addCodeNYPH($codeNyph);
                    echo "new codeNyph <br>";
                }

                $codeNyph->setField($nyphCode);
                $codeNyph->setStartDate($nyphCodeStartDate);
                $codeNyph->setEndDate($nyphCodeEndDate);
            }
            ////////////// EOF Section: NYPH Code ////////////////



            ////////////// Section: Medical License ////////////////
            $sectionMedicalLicense = "Medical License";
            $sectionMedicalLicenseRange = $this->getMergedRangeBySectionName($sectionMedicalLicense,$sections,$sheet);
            echo "<br>sectionMedicalLicenseRange=".$sectionMedicalLicenseRange."<br>";

            //Country (Countries)
            $medicalLicenseCountry = $this->getValueBySectionHeaderName("Country",$rowData,$headers,$sectionMedicalLicenseRange);
            $medicalLicenseCountryObject = $this->getObjectByNameTransformerWithoutCreating("Countries",$medicalLicenseCountry,$systemuser);
            echo "medicalLicenseCountryObject=".$medicalLicenseCountryObject."<br>";

            //State (States)
            $medicalLicenseState = $this->getValueBySectionHeaderName("State",$rowData,$headers,$sectionMedicalLicenseRange);
            $medicalLicenseStateObject = $this->getObjectByNameTransformerWithoutCreating("States",$medicalLicenseState,$systemuser);
            echo "medicalLicenseStateObject=".$medicalLicenseStateObject."<br>";

            //License Number
            $medicalLicenseLicenseNumber = $this->getValueBySectionHeaderName("License Number",$rowData,$headers,$sectionMedicalLicenseRange);
            echo "medicalLicenseLicenseNumber=".$medicalLicenseLicenseNumber."<br>";

            //License Issued Date (MM/DD/YYYY)
            $licenseIssuedDate = $this->getValueBySectionHeaderName("License Issued Date (MM/DD/YYYY)",$rowData,$headers,$sectionMedicalLicenseRange);
            $licenseIssuedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($licenseIssuedDate);
            $licenseIssuedDate = new \DateTime("@$licenseIssuedDate");
            echo "licenseIssuedDate=".$licenseIssuedDate->format('m/d/Y')."<br>";

            //License Expiration Date (MM/DD/YYYY)
            $licenseExpirationDate = $this->getValueBySectionHeaderName("License Expiration Date (MM/DD/YYYY)",$rowData,$headers,$sectionMedicalLicenseRange);
            $licenseExpirationDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($licenseExpirationDate);
            $licenseExpirationDate = new \DateTime("@$licenseExpirationDate");
            echo "licenseExpirationDate=".$licenseExpirationDate->format('m/d/Y')."<br>";

            //Active (MedicalLicenseStatus)
            $licenseActive = $this->getValueBySectionHeaderName("Active",$rowData,$headers,$sectionMedicalLicenseRange);
            $licenseActiveObject = $this->getObjectByNameTransformerWithoutCreating("MedicalLicenseStatus",$licenseActive,$systemuser);
            echo "licenseActiveObject=".$licenseActiveObject."<br>";

            if(
                $medicalLicenseCountryObject || $medicalLicenseStateObject || $medicalLicenseLicenseNumber ||
                $licenseIssuedDate || $licenseExpirationDate || $licenseActiveObject
            ) {

                $stateLicenses = $credentials->getStateLicense();
                if( count($stateLicenses) > 0 ) {
                    $stateLicense = $stateLicenses[0];
                    echo "existing stateLicense <br>";
                } else {
                    $stateLicense = new StateLicense();
                    $credentials->addStateLicense($stateLicense);
                    echo "new stateLicense <br>";
                }

                $stateLicense->setCountry($medicalLicenseCountryObject);
                $stateLicense->setState($medicalLicenseStateObject);
                $stateLicense->setLicenseNumber($medicalLicenseLicenseNumber);
                $stateLicense->setLicenseIssuedDate($licenseIssuedDate);
                $stateLicense->setLicenseExpirationDate($licenseExpirationDate);
                $stateLicense->setActive($licenseActiveObject);
            }
            ////////////// EOF Section: Medical License ////////////////



            ////////////// Section: Board Certification ////////////////
            $sectionBoardCert = "Board Certification";
            $sectionBoardCertRange = $this->getMergedRangeBySectionName($sectionBoardCert,$sections,$sheet);
            echo "<br>sectionBoardCertRange=".$sectionBoardCertRange."<br>";

            //Certifying Board Organization (CertifyingBoardOrganization)
            $certifyingBoardOrganization = $this->getValueBySectionHeaderName("Certifying Board Organization",$rowData,$headers,$sectionBoardCertRange);
            $certifyingBoardOrganizationObject = $this->getObjectByNameTransformerWithoutCreating("CertifyingBoardOrganization",$certifyingBoardOrganization,$systemuser);
            echo "certifyingBoardOrganizationObject=".$certifyingBoardOrganizationObject."<br>";

            //Specialty (BoardCertifiedSpecialties)
            $boardCertSpecialty = $this->getValueBySectionHeaderName("Specialty",$rowData,$headers,$sectionBoardCertRange);
            $boardCertSpecialtyObject = $this->getObjectByNameTransformerWithoutCreating("BoardCertifiedSpecialties",$boardCertSpecialty,$systemuser);
            echo "boardCertSpecialtyObject=".$boardCertSpecialtyObject."<br>";

            //Date Issued (MM/DD/YYYY)
            $boardCertDateIssued = $this->getValueBySectionHeaderName("Date Issued (MM/DD/YYYY)",$rowData,$headers,$sectionBoardCertRange);
            $boardCertDateIssued = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($boardCertDateIssued);
            $boardCertDateIssued = new \DateTime("@$boardCertDateIssued");
            echo "boardCertDateIssued=".$boardCertDateIssued->format('m/d/Y')."<br>";

            //Expiration Date (MM/DD/YYYY)
            $boardCertDateExpiration = $this->getValueBySectionHeaderName("Expiration Date (MM/DD/YYYY)",$rowData,$headers,$sectionBoardCertRange);
            $boardCertDateExpiration = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($boardCertDateExpiration);
            $boardCertDateExpiration = new \DateTime("@$boardCertDateExpiration");
            echo "boardCertDateExpiration=".$boardCertDateExpiration->format('m/d/Y')."<br>";

            //Recertification Date (MM/DD/YYYY)
            $boardCertDateRecertification = $this->getValueBySectionHeaderName("Recertification Date (MM/DD/YYYY)",$rowData,$headers,$sectionBoardCertRange);
            $boardCertDateRecertification = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($boardCertDateRecertification);
            $boardCertDateRecertification = new \DateTime("@$boardCertDateRecertification");
            echo "boardCertDateRecertification=".$boardCertDateRecertification->format('m/d/Y')."<br>";

            if(
                $certifyingBoardOrganizationObject || $boardCertSpecialtyObject ||
                $boardCertDateIssued || $boardCertDateExpiration || $boardCertDateRecertification
            ) {

                $boardCertifications = $credentials->getBoardCertification();
                if( count($boardCertifications) > 0 ) {
                    $boardCertification = $boardCertifications[0];
                    echo "existing boardCertification <br>";
                } else {
                    $boardCertification = new BoardCertification();
                    $credentials->addBoardCertification($boardCertification);
                    echo "new boardCertification <br>";
                }

                $boardCertification->setCertifyingBoardOrganization($certifyingBoardOrganizationObject);
                $boardCertification->setSpecialty($boardCertSpecialtyObject);
                $boardCertification->setIssueDate($boardCertDateIssued);
                $boardCertification->setExpirationDate($boardCertDateExpiration);
                $boardCertification->setRecertificationDate($boardCertDateRecertification);
            }

            ////////////// EOF Section: Board Certification ////////////////

            //exit('1'); //testing

            //echo $username." not found ";
            $this->em->persist($user);
            $this->em->flush();
            $count++;

            echo $count.": added new user $user <br>";

            //**************** create PerSiteSettings for this user **************//
            //TODO: this should be located on scanorder site
            $securityUtil = $this->container->get('order_security_utility');
            $perSiteSettings = $securityUtil->getUserPerSiteSettings($user);
            if( !$perSiteSettings ) {
                $perSiteSettings = new PerSiteSettings($systemuser);
                $perSiteSettings->setUser($user);
            }
//            $params = $this->em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
//            if( count($params) != 1 ) {
//                throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
//            }
//            $param = $params[0];
            //$institution = $param->getAutoAssignInstitution();
            $institution = $userSecUtil->getAutoAssignInstitution();

            $perSiteSettings->addPermittedInstitutionalPHIScope($institution);
            $this->em->persist($perSiteSettings);
            $this->em->flush();
            //**************** EOF create PerSiteSettings for this user **************//

            //record user log create
            $event = "User ".$user." has been created by ".$systemuser."<br>";
            $userSecUtil->createUserEditEvent($this->container->getParameter('employees.sitename'),$event,$systemuser,$user,null,'New user record added');

            //exit('eof user');

        }//for each user

        //exit('exit import users V2');
        //return $count;

        if( $count > 0 ) {
            $resmsg = 'Imported ' . $count . ' new users from Excel.';
        } else {
            $resmsg = 'No new users have been imported.';
        }

        return $resmsg;
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

        if( $separator ) {
            $stringArr = explode($separator,$string);
        } else {
            $stringArr = array($string);
        }

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
    }

    public function processLocation( $sectionTitle, $user, $systemuser, $sections, $sheet, $rowData, $headers ) {
        $sectionRange = $this->getMergedRangeBySectionName($sectionTitle,$sections,$sheet);
        echo "<br>$sectionTitle=$sectionRange<br>";

        //Name
        $name = $this->getValueBySectionHeaderName("Name",$rowData,$headers,$sectionRange);
        echo "name=".$name."<br>";

        //Type (multiple LocationTypeList)
        $types = $this->getValueBySectionHeaderName("Type",$rowData,$headers,$sectionRange);
        $typeObjects = $this->processMultipleListObjects($types,$systemuser,"LocationTypeList");

        $phone = $this->getValueBySectionHeaderName("Phone Number",$rowData,$headers,$sectionRange);
        $pager = $this->getValueBySectionHeaderName("Pager Number",$rowData,$headers,$sectionRange);
        $mobile = $this->getValueBySectionHeaderName("Mobile Number",$rowData,$headers,$sectionRange);
        $intercom = $this->getValueBySectionHeaderName("Intercom",$rowData,$headers,$sectionRange);
        $fax = $this->getValueBySectionHeaderName("Fax",$rowData,$headers,$sectionRange);
        $email = $this->getValueBySectionHeaderName("E-Mail",$rowData,$headers,$sectionRange);

        //Institution
        $institutionStr = $this->getValueBySectionHeaderName("Institution",$rowData,$headers,$sectionRange);
        $departmentStr = $this->getValueBySectionHeaderName("Department",$rowData,$headers,$sectionRange);
        $divisionStr = $this->getValueBySectionHeaderName("Division",$rowData,$headers,$sectionRange);
        $serviceStr = $this->getValueBySectionHeaderName("Service",$rowData,$headers,$sectionRange);
        //echo "inst=".$institutionStr."; $departmentStr; $divisionStr; $serviceStr"."<br>";
        $institutionObject = $this->getEntityByInstitutionDepartmentDivisionService($institutionStr,$departmentStr,$divisionStr,$serviceStr);
        echo "institutionObject=".$institutionObject."<br>";

        //Mailbox (MailboxList)
        $mailbox = $this->getValueBySectionHeaderName("Mailbox",$rowData,$headers,$sectionRange);
        $mailboxObject = $this->getObjectByNameTransformer("MailboxList",$mailbox,$systemuser);
        echo "mailboxObject=".$mailboxObject."<br>";

        //Room Number (RoomList)
        $room = $this->getValueBySectionHeaderName("Room Number",$rowData,$headers,$sectionRange);
        $roomObject = $this->getObjectByNameTransformerWithoutCreating("RoomList",$room,$systemuser);
        echo "roomObject=".$roomObject."<br>";

        //Suite (SuiteList)
        $suite = $this->getValueBySectionHeaderName("Suite",$rowData,$headers,$sectionRange);
        $suiteObject = $this->getObjectByNameTransformerWithoutCreating("SuiteList",$suite,$systemuser);
        echo "suiteObject=".$suiteObject."<br>";

        //Floor (FloorList)
        $floor = $this->getValueBySectionHeaderName("Floor",$rowData,$headers,$sectionRange);
        $floorObject = $this->getObjectByNameTransformerWithoutCreating("FloorList",$floor,$systemuser);
        echo "floorObject=".$floorObject."<br>";

        //Building (BuildingList)
        $building = $this->getValueBySectionHeaderName("Building",$rowData,$headers,$sectionRange);
        $buildingObject = $this->getObjectByNameTransformerWithoutCreating("BuildingList",$building,$systemuser);
        echo "buildingObject=".$buildingObject."<br>";

        //Street Address [Line 1] (GeoLocation->street1)
        $street1 = $this->getValueBySectionHeaderName("Street Address [Line 1]",$rowData,$headers,$sectionRange);
        echo "street1=".$street1."<br>";

        //Street Address [Line 2] (GeoLocation->street2)
        $street2 = $this->getValueBySectionHeaderName("Street Address [Line 2]",$rowData,$headers,$sectionRange);
        echo "street2=".$street2."<br>";

        //City (GeoLocation->CityList)
        $city = $this->getValueBySectionHeaderName("City",$rowData,$headers,$sectionRange);
        $cityObject = $this->getObjectByNameTransformerWithoutCreating("CityList",$city,$systemuser);
        echo "cityObject=".$cityObject."<br>";

        //State (GeoLocation->States)
        $state = $this->getValueBySectionHeaderName("State",$rowData,$headers,$sectionRange);
        $stateObject = $this->getObjectByNameTransformerWithoutCreating("States",$state,$systemuser);
        echo "stateObject=".$stateObject."<br>";

        $zip = $this->getValueBySectionHeaderName("Zip Code",$rowData,$headers,$sectionRange);
        echo "zip=".$zip."<br>";

        //Country (GeoLocation->Countries)
        $country = $this->getValueBySectionHeaderName("Country",$rowData,$headers,$sectionRange);
        $countryObject = $this->getObjectByNameTransformerWithoutCreating("Countries",$country,$systemuser);
        echo "countryObject=".$countryObject."<br>";

        //Associated NYPH Code
        $nyph = $this->getValueBySectionHeaderName("Associated NYPH Code",$rowData,$headers,$sectionRange);
        echo "nyph=".$nyph."<br>";
        $cliaNumber = $this->getValueBySectionHeaderName("CLIA Number",$rowData,$headers,$sectionRange);

        //CLIA Expiration Date (MM/DD/YYYY)
        $cliaExpDate = $this->getValueBySectionHeaderName("CLIA Expiration Date (MM/DD/YYYY)",$rowData,$headers,$sectionRange);
        $cliaExpDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($cliaExpDate);
        $cliaExpDate = new \DateTime("@$cliaExpDate");
        echo "cliaExpDate=".$cliaExpDate->format('m/d/Y')."<br>";

        //PFI Number
        $pfiNumber = $this->getValueBySectionHeaderName("PFI Number",$rowData,$headers,$sectionRange);

        //Comment
        $comment = $this->getValueBySectionHeaderName("Comment",$rowData,$headers,$sectionRange);
        echo "comment=".$comment."<br>";

        if(
            $name || $typeObjects || $phone || $pager || $mobile || $intercom || $fax || $email ||
            $institutionObject || $mailboxObject || $roomObject || $suiteObject || $floorObject ||
            $buildingObject || $street1 || $street2 || $cityObject || $stateObject || $zip || $countryObject ||
            $nyph || $cliaNumber || $cliaExpDate || $pfiNumber
            //|| $comment
        ) {

            //$locations = $user->getLocations();
            //echo "loc count=".count($locations)."<br>";

            $location = new Location($systemuser);
            $location->setStatus($location::STATUS_VERIFIED);
            $user->addLocation($location);

            $location->setName($name);

            foreach( $typeObjects as $typeObject ) {
                $location->addLocationType($typeObject) ;
            }

            $location->setPhone($phone);
            $location->setPager($pager);
            $location->setMobile($mobile);
            $location->setIc($intercom);
            $location->setFax($fax);
            $location->setEmail($email);
            $location->setInstitution($institutionObject);
            $location->setMailbox($mailboxObject);
            $location->setRoom($roomObject);
            $location->setSuite($suiteObject);
            $location->setFloor($floorObject);
            $location->setAssociatedCode($nyph);
            $location->setAssociatedClia($cliaNumber);
            $location->setAssociatedCliaExpDate($cliaExpDate);
            $location->setAssociatedPfi($pfiNumber);
            $location->setComment($comment);

            $location->setBuilding($buildingObject);

            $geo = $location->getGeoLocation();
            if( $geo ) {
                echo "geo exists <br>";
            } else {
                echo "geo does not exists <br>";
                $geo = new GeoLocation();
                $location->setGeoLocation($geo);
            }

            $geo->setStreet1($street1);
            $geo->setStreet2($street2);
            $geo->setCity($cityObject);
            $geo->setState($stateObject);
            $geo->setZip($zip);
            $geo->setCountry($countryObject);
        }

        //exit("testing");
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
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
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

            //************** get pacsvendor group roles and ROLE_SCANORDER_ORDERING_PROVIDER for this user **************//
            //TODO: this should be located on scanorder site
            //TODO: rewrite using pacsvendor's DB not SOAP functions
            $pacsvendorUtil = new PacsvendorUtil();
            echo "username=".$username."<br>";
            $userid = $pacsvendorUtil->getUserIdByUserName($username);
            if( $userid ) {
                echo "userid=".$userid."<br>";
                $pacsvendorRoles = $pacsvendorUtil->getUserGroupMembership($userid);
                $stats = $pacsvendorUtil->setUserPathologyRolesByPacsvendorRoles( $user, $pacsvendorRoles );
            }
            //************** end of  pacsvendor group roles **************//

            $user->setEnabled(true);
            //$user->setLocked(false);
            //$user->setExpired(false);

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
            
//            $params = $this->em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();
//            if( count($params) != 1 ) {
//                throw new \Exception( 'Must have only one parameter object. Found '.count($params).' object(s)' );
//            }
//            $param = $params[0];
//            $institution = $param->getAutoAssignInstitution();
            
            $institution = $userSecUtil->getAutoAssignInstitution();
            
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
        //echo $header.": res=[".$res."]<br>";
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
        $object = $transformer->reverseTransform($nameStr);
        if( !$object ) {
            exit("Error: Not found object [$className] by [$nameStr].");
        }
        return $object;
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