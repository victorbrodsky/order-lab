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
 * Date: 8/28/15
 * Time: 9:27 AM
 */

namespace App\ResAppBundle\Util;

use App\ResAppBundle\Entity\ResidencyApplication;
use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\UserdirectoryBundle\Entity\Examination;
use App\UserdirectoryBundle\Entity\Training;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportFromOldSystem {

    private $em;
    private $container;

    private $path = "C:\Users\ch3\Documents\MyDocs\WCMC\Residency\DB";
    private $enrolmentYearArr = array();
    private $residencySpecialtyArr = array();

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->em = $em;
        $this->container = $container;
    }

    //PRA_APPLICANT_INFO - application
    //PRA_APPLICANT_CV_INFO - document
    //PRA_APPLICANT_UPDATE_CV_INFO - document 2
    //PRA_ENROLLMENT_INFO - enrollment
    //PRA_EVALUATION_FORM_INFO - evaluation
    //PRA_FACULTY_RESIDENT_INFO - evaluator




    public function importApplications() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        $em = $this->em;
        $default_time_zone = $this->container->getParameter('default_time_zone');

        $res = "import Residency Applications";

        $this->getResidencySpecialties();
        dump($this->residencySpecialtyArr);

        $this->getEnrolmentYear();
        dump($this->enrolmentYearArr);

        //exit('111');

        try {
            $inputFileName = $this->path . "/"."PRA_APPLICANT_INFO.csv";
            //$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            //Use depreciated PHPExcel, because PhpOffice does not read correctly rows of the google spreadsheets
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);

            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            $event = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            throw new IOException($event);
        }

        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        $userkeytype = $userSecUtil->getUsernameType('local-user');
        if( !$userkeytype ) {
            throw new EntityNotFoundException('Unable to find local user keytype');
        }

        $employmentType = $em->getRepository('AppUserdirectoryBundle:EmploymentType')->findOneByName("Pathology Residency Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Residency Applicant");
        }

        $activeStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."active");
        }
        $archiveStatus = $em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName("archive");
        if( !$archiveStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."archive");
        }

        $postSophPathologyEntity = $em->getRepository('AppResAppBundle:PostSophList')->findOneByName("Pathology");
        if( !$postSophPathologyEntity ) {
            throw new EntityNotFoundException('Unable to find PostSophList entity by name='."Pathology");
        }
        $postSophNoneEntity = $em->getRepository('AppResAppBundle:PostSophList')->findOneByName("None");
        if( !$postSophNoneEntity ) {
            throw new EntityNotFoundException('Unable to find PostSophList entity by name='."None");
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        echo "rows=$highestRow columns=$highestColumn <br>";
        //$logger->notice("rows=$highestRow columns=$highestColumn");

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);
        //print_r($headers);

        $testing = true;
        $testing = false;

        $residencyApplications = new ArrayCollection();

        //for each user in excel
        for( $row = 2; $row <= $highestRow; $row++ ){

            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //APPLICANT_ID	LAST_NAME	FIRST_NAME
            //AP_CP	MED_SCHOOL	DATE_GRADUATE
            //INTERVIEW_DATE	DATE_CREATED
            //ENROLLMENT_ID	ACTIVED	AOA	COUPLES
            //MD_PHD	POSTSOPH	DO
            //USMLE_STEP1	USMLE_STEP2	USMLE_STEP3
            //MD

            $id = $this->getValueByHeaderName('APPLICANT_ID', $rowData, $headers);

            //testing
            if( $id != '2697' ) {
                echo 'Skip this residency application with googleFormId='.$id."<br>";
                continue;
            }

            $residencyApplicationDb = $em->getRepository('AppResAppBundle:ResidencyApplication')->findOneByGoogleFormId($id);
            if( $residencyApplicationDb ) {
                $logger->notice('Skip this residency application, because it already exists in DB. googleFormId='.$id);
                echo 'Skip this residency application, because it already exists in DB. googleFormId='.$id."<br>";
                continue; //skip this fell application, because it already exists in DB
            }

            $lastName = $this->getValueByHeaderName('LAST_NAME', $rowData, $headers);
            $firstName = $this->getValueByHeaderName('FIRST_NAME', $rowData, $headers);

            //AP, CP, AP/CP, AP/EXP, CP/EXP
            $residencyType = $this->getValueByHeaderName('AP_CP', $rowData, $headers);

            //Training
            $medSchool = $this->getValueByHeaderName('MED_SCHOOL', $rowData, $headers);
            $graduateDate = $this->getValueByHeaderName('DATE_GRADUATE', $rowData, $headers); //Training->completionDate
            
            $interviewDate = $this->getValueByHeaderName('INTERVIEW_DATE', $rowData, $headers); //interviewDate
            $createDate = $this->getValueByHeaderName('DATE_CREATED', $rowData, $headers);
            $enrolmentId = $this->getValueByHeaderName('ENROLLMENT_ID', $rowData, $headers);
            $activeD = $this->getValueByHeaderName('ACTIVED', $rowData, $headers); //?
            $aoa = $this->getValueByHeaderName('AOA', $rowData, $headers);
            $couples = $this->getValueByHeaderName('COUPLES', $rowData, $headers);

            //Post-Sophomore Fellowship in Pathology/No
            $postSoph = $this->getValueByHeaderName('POSTSOPH', $rowData, $headers);

            //Training
            $mdPhd = $this->getValueByHeaderName('MD_PHD', $rowData, $headers);
            $do = $this->getValueByHeaderName('DO', $rowData, $headers);
            $md = $this->getValueByHeaderName('MD', $rowData, $headers);

            //Examination: $examination->getUSMLEStep1Score();
            $usmleStep1 = $this->getValueByHeaderName('USMLE_STEP1', $rowData, $headers);
            $usmleStep2 = $this->getValueByHeaderName('USMLE_STEP2', $rowData, $headers);
            $usmleStep3 = $this->getValueByHeaderName('USMLE_STEP3', $rowData, $headers);

            echo $row.": $firstName $lastName (ID $id) <br>";

            $lastNameCap = $this->capitalizeIfNotAllCapital($lastName);
            $firstNameCap = $this->capitalizeIfNotAllCapital($firstName);

            $lastNameCap = preg_replace('/\s+/', '_', $lastNameCap);
            $firstNameCap = preg_replace('/\s+/', '_', $firstNameCap);

            //Last Name + First Name + Email
            $username = $lastNameCap . "_" . $firstNameCap . "_" . $id;

            $displayName = $firstName . " " . $lastName;

            //create logger which must be deleted on successefull creation of application
            //$eventAttempt = "Attempt of creating Residency Applicant " . $displayName . " with unique applicant ID=" . $id;
            //$eventLogAttempt = $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'), $eventAttempt, $systemUser, null, null, 'Residency Application Creation Failed');


            //check if the user already exists in DB by $googleFormId
            $user = $em->getRepository('AppUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($username);

            if (!$user) {
                //create excel user
                $addobjects = false;
                $user = new User($addobjects);
                $user->setKeytype($userkeytype);
                $user->setPrimaryPublicUserId($username);

                //set unique username
                $usernameUnique = $user->createUniqueUsername();
                $user->setUsername($usernameUnique);
                $user->setUsernameCanonical($usernameUnique);


                //$user->setEmail($email);
                //$user->setEmailCanonical($email);

                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                //$user->setMiddleName($middleName);
                $user->setDisplayName($displayName);
                $user->setPassword("");
                $user->setCreatedby('resapp_migration');
                $user->getPreferences()->setTimezone($default_time_zone);
                $user->setLocked(true);

                //Pathology Residency Applicant in EmploymentStatus
                $employmentStatus = new EmploymentStatus($systemUser);
                $employmentStatus->setEmploymentType($employmentType);
                $user->addEmploymentStatus($employmentStatus);
            }

            $residencyApplication = new ResidencyApplication($systemUser);

            $residencyApplication->setAppStatus($activeStatus);

            $user->addResidencyApplication($residencyApplication);

            //////////////// populate fields ////////////////////

            if( $id ) {
                $residencyApplication->setGoogleFormId($id);
            }

            if( $enrolmentId ) {
                $enrolmentStartYear = $this->enrolmentYearArr[$enrolmentId];
                //echo "enrolmentStartYear=$enrolmentStartYear <br>";

                $enrolmentEndYear = (int)$enrolmentStartYear+1;
                //echo "enrolmentEndYear=$enrolmentEndYear <br>";
                //echo "enrolment=$enrolmentStartYear-$enrolmentEndYear <br>";

                //trainingPeriodStart
                $enrolmentStartYear = $enrolmentStartYear."-07-01";
                $startDate = $this->transformDatestrToDate($enrolmentStartYear);
                //echo "startDate ($enrolmentStartYear)=".$startDate->format('Y-m-d H:i:s')."<br>";
                $residencyApplication->setStartDate($startDate);
                //trainingPeriodEnd
                $enrolmentEndYear = $enrolmentEndYear."-06-30";
                $endDate = $this->transformDatestrToDate($enrolmentEndYear);
                //echo "endDate ($enrolmentEndYear)=".$endDate->format('Y-m-d H:i:s')."<br>";
                $residencyApplication->setEndDate($endDate);
            }

            //fellowshipType
            if( $residencyType ) {
                //$logger->notice("fellowshipType=[".$fellowshipType."]");
                $residencyType = trim($residencyType);
                //$residencyType = $this->capitalizeIfNotAllCapital($residencyType);
                $residencyType = strtoupper($residencyType);
                //$transformer = new GenericTreeTransformer($em, $systemUser, 'ResidencySpecialty');
                //$residencyTypeEntity = $transformer->reverseTransform($residencyType);
                $residencyTypeEntity = $this->residencySpecialtyArr[$residencyType];
                $residencyApplication->setResidencySubspecialty($residencyTypeEntity);
            }

            $this->createResAppTraining($residencyApplication,$systemUser,$medSchool,$graduateDate,$mdPhd,$do,$md);

            //USMLE scores: $usmleStep1, $usmleStep2, $usmleStep3
            $examination = new Examination($systemUser);
            if( $usmleStep1 ) {
                $examination->setUSMLEStep1Score($usmleStep1);
            }
            if( $usmleStep2 ) {
                $examination->setUSMLEStep2CKScore($usmleStep2);
            }
            if( $usmleStep3 ) {
                $examination->setUSMLEStep3Score($usmleStep3);
            }
            $residencyApplication->addExamination($examination);

            if( $interviewDate ) {
                $residencyApplication->setInterviewDate($this->transformDatestrToDate($interviewDate));
            }

            if( $createDate ) {
                $residencyApplication->setTimestamp($this->transformDatestrToDate($createDate));
            }

            //$activeD = $this->getValueByHeaderName('ACTIVED', $rowData, $headers); //?
            //DELETE applicant, just set actived=0
            if( $activeD ) {
                $residencyApplication->setAppStatus($activeStatus);
            } else {
                $residencyApplication->setAppStatus($archiveStatus);
            }

            if( $aoa ) {
                $residencyApplication->setAoa($aoa);
            }

            if( $couples == '1' ) {
                $couples = true;
            } else {
                $couples = false;
            }
            $residencyApplication->setCouple($couples);

            //Post-Sophomore Fellowship in Pathology/No
            if( $postSoph == '1' ) {
                $residencyApplication->setPostSoph($postSophPathologyEntity);
            } else {
                $residencyApplication->setPostSoph($postSophNoneEntity);
            }

            //exit('end applicant');

            if( !$testing ) {
                $em->persist($user);
                $em->flush();
            }

            $event = "Populated residency applicant " . $displayName . "; Application ID " . $residencyApplication->getId();
            //$logger->notice($event);
            echo "$event <br>";

            echo "###################### <br>";

            exit('end application');

        } //for



        return $res;
    }

    public function createResAppTraining($residencyApplication,$author,$medSchool,$graduateDate,$mdPhd,$do,$md) {
        $em = $this->em;

        $training = new Training($author);

        $training->setOrderinlist(1);

        $trainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('Medical');
        $training->setTrainingType($trainingType);

        $residencyApplication->addTraining($training);
        $residencyApplication->getUser()->addTraining($training);

        if ($mdPhd) {
            $schoolDegree = trim($mdPhd);
            $transformer = new GenericTreeTransformer($em, $author, 'TrainingDegreeList');
            $schoolDegreeEntity = $transformer->reverseTransform($schoolDegree);
            $training->setDegree($schoolDegreeEntity);
        }
        if ($do) {
            $schoolDegree = trim($do);
            $transformer = new GenericTreeTransformer($em, $author, 'TrainingDegreeList');
            $schoolDegreeEntity = $transformer->reverseTransform($schoolDegree);
            $training->setDegree($schoolDegreeEntity);
        }
        if ($md) {
            $schoolDegree = trim($md);
            $transformer = new GenericTreeTransformer($em, $author, 'TrainingDegreeList');
            $schoolDegreeEntity = $transformer->reverseTransform($schoolDegree);
            $training->setDegree($schoolDegreeEntity);
        }

        if( $medSchool ) {
            $params = array('type'=>'Educational');
            $medSchool = trim($medSchool);
            $schoolName = $this->capitalizeIfNotAllCapital($medSchool);
            $transformer = new GenericTreeTransformer($em, $author, 'Institution', null, $params);
            $schoolNameEntity = $transformer->reverseTransform($schoolName);
            $training->setInstitution($schoolNameEntity);
        }

        if( $graduateDate ) {
            $training->setCompletionDate($this->transformDatestrToDate($graduateDate));
        }

    }

    public function transformDatestrToDate($datestr) {
        $userSecUtil = $this->container->get('user_security_utility');
        return $userSecUtil->transformDatestrToDateWithSiteEventLog($datestr,$this->container->getParameter('resapp.sitename'));
    }

    public function getEnrolmentYear() {

        $logger = $this->container->get('logger');

        $inputFileName = $this->path . "/"."PRA_ENROLLMENT_INFO.csv";

        try {
            //$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            //Use depreciated PHPExcel, because PhpOffice does not read correctly rows of the google spreadsheets
            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);

            $objPHPExcel = $objReader->load($inputFileName);
        } catch(Exception $e) {
            $event = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            throw new IOException($event);
        }


        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        //echo "rows=$highestRow columns=$highestColumn <br>";
        //$logger->notice("rows=$highestRow columns=$highestColumn");

        $headers = $rowData = $sheet->rangeToArray('A' . 1 . ':' . $highestColumn . 1,
            NULL,
            TRUE,
            FALSE);
        //print_r($headers);

        $enrolmentYearArr = array();

        //for each user in excel
        for( $row = 2; $row <= $highestRow; $row++ ){

            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $enrolmentId = $this->getValueByHeaderName('ENROLLMENT_ID', $rowData, $headers);
            $startYear = $this->getValueByHeaderName('START_YEAR_ENROLLMENT', $rowData, $headers);
            //$endYear = $this->getValueByHeaderName('END_YEAR_ENROLLMENT', $rowData, $headers);

            //echo $row.": $firstName $lastName (ID $id) <br>";

            $startYear = (int)$startYear;

            $enrolmentYearArr[$enrolmentId] = $startYear;
        }

        $this->enrolmentYearArr = $enrolmentYearArr;
        //return $enrolmentYearArr;
    }

    public function getResidencySpecialties() {

        $residencySpecialtyStrArr = array('AP','CP','AP/CP','AP/EXP','CP/EXP');

        $wcmc = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation("WCM");
        if( !$wcmc ) {
            exit('generateDefaultOrgGroupSiteParameters: No Institution: "WCM"');
        }

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution'
        );
        $pathologyInstitution = $this->em->getRepository('AppUserdirectoryBundle:Institution')->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );
        $pathologyInstitutionId = $pathologyInstitution->getId();

        foreach($residencySpecialtyStrArr as $residencySpecialtyStr) {
            $residencySpecialtyEntity = $this->em->getRepository('AppUserdirectoryBundle:ResidencySpecialty')->findOneByName($residencySpecialtyStr);

            $repository = $this->em->getRepository('AppUserdirectoryBundle:ResidencySpecialty');
            $dql =  $repository->createQueryBuilder("list");
            $dql->select('list');
            $dql->leftJoin("list.institution", "institution");
            $dql->where("list.name = :name AND institution.id = :institutionId");

            $query = $this->em->createQuery($dql);
            $query->setParameters(
                array(
                    'name' => $residencySpecialtyStr,
                    'institutionId' => $pathologyInstitutionId
                )
            );

            $residencySpecialtyEntity = NULL;
            $residencySpecialties = $query->getResult();
            if( count($residencySpecialties) > 0 ) {
                $residencySpecialtyEntity = $residencySpecialties[0];
            }

            if( !$residencySpecialtyEntity ) {
                throw new EntityNotFoundException('Unable to find ResidencySpecialty entity by name='.$residencySpecialtyStr);
            }

            $residencySpecialtyArr[$residencySpecialtyStr] = $residencySpecialtyEntity;
        }

        $this->residencySpecialtyArr = $residencySpecialtyArr;
    }

    public function getValueByHeaderName($header, $row, $headers) {

        $res = null;

        if( !$header ) {
            return $res;
        }

        //echo "header=".$header."<br>";
        //print_r($headers);
        //print_r($row[0]);

        $key = array_search($header, $headers[0]);
        //echo "key=".$key."<br>";

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

    public function capitalizeIfNotAllCapital($s) {
        if( !$s ) {
            return $s;
        }
        $convert = false;
        //check if all UPPER
        if( strtoupper($s) == $s ) {
            $convert = true;
        }
        //check if all lower
        if( strtolower($s) == $s ) {
            $convert = true;
        }
        if( $convert ) {
            return ucwords( strtolower($s) );
        }
        return $s;
    }
} 