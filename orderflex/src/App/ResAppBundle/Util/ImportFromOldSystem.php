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



use App\UserdirectoryBundle\Entity\ResidencyTrackList; //process.py script: replaced namespace by ::class: added use line for classname=ResidencyTrackList


use App\ResAppBundle\Entity\ResAppRank; //process.py script: replaced namespace by ::class: added use line for classname=ResAppRank


use App\ResAppBundle\Entity\LanguageProficiency; //process.py script: replaced namespace by ::class: added use line for classname=LanguageProficiency


use App\UserdirectoryBundle\Entity\EmploymentType; //process.py script: replaced namespace by ::class: added use line for classname=EmploymentType


use App\ResAppBundle\Entity\ResAppStatus; //process.py script: replaced namespace by ::class: added use line for classname=ResAppStatus


use App\ResAppBundle\Entity\PostSophList; //process.py script: replaced namespace by ::class: added use line for classname=PostSophList


use App\UserdirectoryBundle\Entity\TrainingTypeList; //process.py script: replaced namespace by ::class: added use line for classname=TrainingTypeList


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\ResAppBundle\Entity\Interview;
use App\ResAppBundle\Entity\ResidencyApplication;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\UserdirectoryBundle\Entity\Examination;
use App\UserdirectoryBundle\Entity\Training;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;

//PRA_APPLICANT_INFO - application
//PRA_APPLICANT_CV_INFO - document
//PRA_APPLICANT_UPDATE_CV_INFO - document 2
//PRA_ENROLLMENT_INFO - enrollment
//PRA_EVALUATION_FORM_INFO - evaluation
//PRA_FACULTY_RESIDENT_INFO - evaluator

class ImportFromOldSystem {

    private $em;
    private $container;

    private $path = NULL;   //"../../../../../ResidencyImport";    //"C:\Users\ch3\Documents\MyDocs\WCMC\Residency";
    private $uploadPath = NULL;
    private $enrolmentYearArr = array();
    private $residencySpecialtyArr = array();
    private $documentErasType = NULL;
    private $usersArr = array();

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->em = $em;
        $this->container = $container;

        $projectRoot = $this->container->get('kernel')->getProjectDir();
        $this->path = $projectRoot . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."ResidencyImport"; //Place 'ResidencyImport' to the same folder as 'order-lab'
//        if( file_exists($this->path) ) {
//            //echo $row.": The file exists: $inputFilePath <br>";
//        } else {
//            exit("Source folder does not exist. path=[".$this->path."]<br>");
//        }

//        $userSecUtil = $this->container->get('user_security_utility');
//        $resappuploadpath = $userSecUtil->getSiteSettingParameter('resappuploadpath'); //resapp/documents
//        $path = 'Uploaded'.DIRECTORY_SEPARATOR.$resappuploadpath;
//        $this->uploadPath = $path;  //'Uploaded'.DIRECTORY_SEPARATOR.$resappuploadpath.DIRECTORY_SEPARATOR;
    }

    //http://127.0.0.1/order/index_dev.php/residency-applications/import-from-old-system-interview
    public function importApplicationsFilesInterview($max) {

        if( file_exists($this->path) ) {
            //echo $row.": The file exists: $inputFilePath <br>";
        } else {
            exit("importApplicationsFilesInterview: Source folder does not exist. path=[".$this->path."]<br>");
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        set_time_limit(720); //12 min

        $em = $this->em;
        //$default_time_zone = $this->container->getParameter('default_time_zone');

        $this->getFacultyResident(false);
        //dump($this->usersArr);
        //exit('EOF importApplicationsFilesInterview');

        try {
            //$inputFileName = $this->path . "/DB_file1/" . "PRA_APPLICANT_CV_INFO.csv";
            $inputFileName = $this->path . "/DB/"."PRA_EVALUATION_FORM_INFO.csv";
            //$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            //Use depreciated PHPExcel, because PhpOffice does not read correctly rows of the google spreadsheets
            //$inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            //$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            //$objPHPExcel = $objReader->load($inputFileName);

            //migrate PHPExcel=>PhpOffice: All users must migrate to its direct successor PhpSpreadsheet, or another alternative.
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);

        } catch(\Exception $e) {
            $event = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            throw new IOException($event);
        }

        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResAppRank'] by [ResAppRank::class]
        $ranks = $em->getRepository(ResAppRank::class)->findAll();
        $ranksArr = array();
        foreach($ranks as $rank) {
            $ranksArr[$rank->getValue()] = $rank;
        }
        dump($ranksArr);
        //exit('111');

//        $statusComplete = $this->em->getRepository('AppResAppBundle:ResAppStatus')->findOneByName('complete');
//        if( !$statusComplete ) {
//            exit("ResAppStatus not found by langProficiency=complete");
//        }

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

        //$testing = true;
        $testing = false;

        $count = 0;
        $processingCount = 0;

        //for each user in excel
        for( $row = 2; $row <= $highestRow; $row++ ) {

            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //EVAL_FORM_ID
            //FACULTY_RESIDENT_ID
            //APPLICANT_ID
            //DATE_INTERVIEW
            //ACADEMIC_RANK         (Academic Performance / Accomplishments)
            //PERSONALITY_RANK      (Interpersonal / Communication Skills)
            //RES_POTENTIAL_RANK    (Attitude / Work Ethic / Team Player)
            //TOTAL_RANKS
            //LANG_PROFICIENCY
            //COMMENTS
            //COMPLETE_EVAL
            //DATE_CREATED
            //WHEN_ACCESS_EVAL_FORM
            //OVERALL_FIT
            //MIN_RANK
            //MAX_RANK

            $evalFormId = $this->getValueByHeaderName('EVAL_FORM_ID', $rowData, $headers);
            $facultyResidentId = $this->getValueByHeaderName('FACULTY_RESIDENT_ID', $rowData, $headers);
            $applicantId = $this->getValueByHeaderName('APPLICANT_ID', $rowData, $headers);
            $dateInterview = $this->getValueByHeaderName('DATE_INTERVIEW', $rowData, $headers);

            $academicRank = $this->getValueByHeaderName('ACADEMIC_RANK', $rowData, $headers);
            $personalityRank = $this->getValueByHeaderName('PERSONALITY_RANK', $rowData, $headers);
            $potentialRank = $this->getValueByHeaderName('RES_POTENTIAL_RANK', $rowData, $headers);
            $totalRank = $this->getValueByHeaderName('TOTAL_RANKS', $rowData, $headers);
            $langProficiency = $this->getValueByHeaderName('LANG_PROFICIENCY', $rowData, $headers);
            $comment = $this->getValueByHeaderName('COMMENTS', $rowData, $headers);
            $overallFit = $this->getValueByHeaderName('OVERALL_FIT', $rowData, $headers); //No field in Interviewer

            $minRank = $this->getValueByHeaderName('MIN_RANK', $rowData, $headers);
            $maxRank = $this->getValueByHeaderName('MAX_RANK', $rowData, $headers);

            $completeEval = $this->getValueByHeaderName('COMPLETE_EVAL', $rowData, $headers);
            $dateCreated = $this->getValueByHeaderName('DATE_CREATED', $rowData, $headers);
            $whenAccessEvalForm = $this->getValueByHeaderName('WHEN_ACCESS_EVAL_FORM', $rowData, $headers);

//            if( $applicantId == '14' ) {
//                //ok
//            } else {
//                echo "Skip Interview applicantId=$applicantId <br>";
//                continue;
//            }

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResidencyApplication'] by [ResidencyApplication::class]
            $residencyApplicationDb = $em->getRepository(ResidencyApplication::class)->findOneByGoogleFormId($applicantId);

            if( !$residencyApplicationDb ) {
                $errorMsg = $row.": Skip ResidencyApplication not found by id=$applicantId";
                echo $errorMsg."<br>";
                $logger->notice($errorMsg);
                continue;
            }

            $interviewDb = $this->getExistingInterview($residencyApplicationDb,$facultyResidentId);
            if( $interviewDb ) {
                $errorMsg = $row.": Skip Existing Evaluation id=$evalFormId: residencyApplicationDb=$residencyApplicationDb, facultyResidentId=$facultyResidentId";
                echo $errorMsg."<br>";
                $logger->notice($errorMsg);
                continue;
            }

            //Check if user exists for non-empty review
            if( $academicRank || $personalityRank || $potentialRank || $totalRank || $langProficiency || $comment ) {
                $facultyResidentArr = $this->usersArr[$facultyResidentId];
                $interviewer = $facultyResidentArr['user'];
                $facultyResidentLastName = $facultyResidentArr['LAST_NAME']; //'LAST_NAME' => $LAST_NAME,
                $facultyResidentFirstName = $facultyResidentArr['FIRST_NAME'];//'FIRST_NAME' => $FIRST_NAME,
                $facultyResidentPhone = $facultyResidentArr['PHONE'];//'PHONE' => $PHONE,
                $facultyResidentEmail = $facultyResidentArr['EMAIL'];//'EMAIL' => $EMAIL,

                if( !$interviewer ) {
                    echo $count.": academicScore=$academicRank: No user exists: FirstName=$facultyResidentFirstName, LastName=$facultyResidentLastName, email=$facultyResidentEmail, phone$facultyResidentPhone<br>";
                    $count++;
                } else {

                    echo $row.": Evaluation id=$evalFormId (applicant=".$residencyApplicationDb->getUser()."), facultyResident=".$interviewer."<br>";

                    $interview = new Interview();
                    $interview->setInterviewer($interviewer);
                    $interview->setLocation($interviewer->getMainLocation());

                    //echo "dateInterview=$dateInterview <br>";
                    $dateInterviewStr = $this->transformDatestrToDate($dateInterview);
                    //exit("dateInterviewStr=".$dateInterviewStr->format('Y-m-d H:i:s'));
                    //echo "dateInterview=[$dateInterview]=>[".$dateInterviewStr->format('Y-m-d H:i:s')."]<br>";
                    $interview->setInterviewDate($dateInterviewStr);

                    //$academicRank = number_format($academicRank, 1);
                    //$academicRankEntity = $ranksArr[$academicRank];
                    $academicRankEntity = $this->convertToRank($academicRank,$ranksArr);
                    if( $academicRankEntity ) {
                        $interview->setAcademicRank($academicRankEntity); //ResAppRank
                    }

                    //$personalityRank = number_format($personalityRank, 1);
                    //$personalityRankEntity = $ranksArr[$personalityRank];
                    $personalityRankEntity = $this->convertToRank($personalityRank,$ranksArr);
                    if( $personalityRankEntity ) {
                        $interview->setPersonalityRank($personalityRankEntity); //ResAppRank
                    }

                    //$potentialRank = number_format($potentialRank, 1);
                    //$potentialRankEntity = $ranksArr[$potentialRank];
                    $potentialRankEntity = $this->convertToRank($potentialRank,$ranksArr);
                    if( $potentialRankEntity ) {
                        $interview->setPotentialRank($potentialRankEntity); //ResAppRank
                    }

                    //$totalRankDecimal = number_format($totalRank, 2);
                    if( $totalRank > 9 ) {
                        //$totalRank = $academicRank + $personalityRank + $potentialRank;
                        $totalRank = 9;
                        //echo "TotalRank=$totalRank <br>";
                    }
                    $totalRank = number_format($totalRank, 1);
                    $interview->setTotalRank($totalRank);

                    //LanguageProficiency
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:LanguageProficiency'] by [LanguageProficiency::class]
                    $langProficiencyEntity = $this->em->getRepository(LanguageProficiency::class)->findOneByName($langProficiency);
//                    if( !$langProficiencyEntity ) {
//                        exit("LanguageProficiency not found by langProficiency=$langProficiency");
//                    }
                    //echo "langProficiency=$langProficiency => $langProficiencyEntity <br>";
                    //exit('111');
                    if( $langProficiencyEntity ) {
                        $interview->setLanguageProficiency($langProficiencyEntity);
                    }

                    $interview->setComment($comment);

                    $residencyApplicationDb->addInterview($interview);

                    //calculate total Interview Score (interviewScore) for all existing interviews
                    $this->calculateScore($residencyApplicationDb);

                    //TODO: ResidencyApplication -> Rank? : NotUsed

                    //$completeEval of the Interview
                    //$residencyApplicationDb->setAppStatus($statusComplete);

                    $em->persist($interview);
                    $em->flush();

                    //exit('EOF 0Interview applicantId='.$applicantId);
                }
            } //if( $academicRank

            $processingCount++;

            //exit('EOF 1Interview applicantId='.$applicantId);
        } //for

        return "Imported evaluations: count=".$processingCount;
    }
    public function getExistingInterview( $residencyApplicationDb, $facultyResidentId ) {

        $facultyResidentArr = $this->usersArr[$facultyResidentId];
        $interviewer = $facultyResidentArr['user'];
        if( !$interviewer ) {
            exit("Interviewer not found by facultyResidentId=$facultyResidentId");
        }

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:Interview'] by [Interview::class]
        $repository = $this->em->getRepository(Interview::class);
        $dql = $repository->createQueryBuilder('interview');
        $dql->leftJoin("interview.resapp","resapp");
        $dql->leftJoin("interview.interviewer","interviewer");

        $dql->where("resapp.id = :resappId AND interviewer.id = :interviewerId");

        $query = $dql->getQuery();

        $query->setParameters( array(
            'resappId' => $residencyApplicationDb->getId(),
            'interviewerId' => $interviewer->getId(),
        ));

        $interviews = $query->getResult();

        if( count($interviews) > 0 ) {
            return true;
        }

        return false;
    }
    public function convertToRank($str,$ranksArr) {
        if( (int)$str > 3 ) {
            $str = '3';
        }
        $decimal = number_format($str, 1);
        //echo "[$str]=>[$decimal] <br>";
        if( array_key_exists($decimal, $ranksArr) === false ) {
            echo "Warning: Score not valid [$str]=>[$decimal] <br>";
            return NULL;
            //exit("Rank not valid [$str]=>[$decimal]");
        }
        $rankEntity = $ranksArr[$decimal];
        if( !$rankEntity ) {
            exit("Score not found by str=$str, decimal=$decimal");
        }
        return $rankEntity;
    }
    public function calculateScore($entity) {
        $count = 0;
        $score = 0;
        foreach( $entity->getInterviews() as $interview ) {
            $totalRank = $interview->getTotalRank();
            if( $totalRank ) {
                $score = $score + $totalRank;
                $count++;
            }
        }
        if( $count > 0 ) {
            $score = $score/$count;
            $score = round($score,1);
        }

        $entity->setInterviewScore($score);
    }


    public function importApplicationsFiles( $max, $dataFileName, $dataFileFolder, $fileTypeName ) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        set_time_limit(720); //12 min

        $em = $this->em;
        //$default_time_zone = $this->container->getParameter('default_time_zone');

        if( file_exists($this->path) ) {
            //echo $row.": The file exists: $inputFilePath <br>";
        } else {
            exit("importApplicationsFiles: Source folder does not exist. path=[".$this->path."]<br>");
        }

        try {
            //$inputFileName = $this->path . "/DB_file1/" . "PRA_APPLICANT_CV_INFO.csv";
            $inputFileName = $this->path . DIRECTORY_SEPARATOR . $dataFileName;
            //$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            //Use depreciated PHPExcel, because PhpOffice does not read correctly rows of the google spreadsheets
            //$inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            //$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            //$objPHPExcel = $objReader->load($inputFileName);

            //migrate PHPExcel=>PhpOffice: All users must migrate to its direct successor PhpSpreadsheet, or another alternative.
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(\Exception $e) {
            $event = 'Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage();
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            throw new IOException($event);
        }

        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        //Document Type
        $transformer = new GenericTreeTransformer($this->em, $systemUser, "DocumentTypeList", "UserdirectoryBundle");
        $documentType = "Residency ERAS Document";
        $documentErasTypeObject = $transformer->reverseTransform($documentType);
        if( !$documentErasTypeObject ) {
            exit("Document Type can not be found/created by name 'Residency ERAS Document'");
        }
        $this->documentErasType = $documentErasTypeObject;

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

        //$testing = true;
        $testing = false;

        $count = 0;
        $processingCount = 0;

        //for each user in excel
        for( $row = 2; $row <= $highestRow; $row++ ) {

            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $id = $this->getValueByHeaderName('APPLICANT_ID', $rowData, $headers);

            //FILE_NAME	FILE_TYPE	FILE_SIZE	IMAGE

            $fileOriginalName = $this->getValueByHeaderName('FILE_NAME', $rowData, $headers);

            $fileType = $this->getValueByHeaderName('FILE_TYPE', $rowData, $headers);

            $fileSize = $this->getValueByHeaderName('FILE_SIZE', $rowData, $headers);

            //C:\Users\ch3\Documents\MyDocs\WCMC\Residency\DB2\files\PRA_APPLICANT_CV_INFO.csv-1.data
            $imagePath = $this->getValueByHeaderName('IMAGE', $rowData, $headers);

            $processingCount++;
            if( $max && $processingCount > (int)$max ) {
                exit('end of processing '.$max.' applications'); //.($processingCount+1).">=".$max
            }

            //echo $row.": fileOriginalName=$fileOriginalName (ID $id) <br>";

            //get file name
            $fileName = basename($imagePath);
            //echo "fileName1=".$fileName."<br>";

            if( strpos((string)$fileName, ":") !== false ) {
                //C:\Users\ccc\Documents\MyDocs\WCMC\Residency\DB2\files\PRA_APPLICANT_CV_INFO.csv-1.data
                //echo "Get basename from fileName=$fileName <br>";
                //$pathinfoArr = pathinfo($fileName);
                //$fileName = $pathinfoArr['basename'];
                //Get filename from path
                $pathArr = explode("\\", $fileName);
                $fileName = end($pathArr);
            }
            //echo "fileName2=".$fileName."<br>";

            //get file path
            $inputFilePath = $this->path . DIRECTORY_SEPARATOR . $dataFileFolder . DIRECTORY_SEPARATOR. "files" . DIRECTORY_SEPARATOR . $fileName;
            //echo "inputFilePath=".$inputFilePath."<br>";

            if( file_exists($inputFilePath) ) {
                //echo $row.": The file exists: $inputFilePath <br>";
            } else {
                exit($row.": The file does not exist: $inputFilePath <br>");
            }

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResidencyApplication'] by [ResidencyApplication::class]
            $residencyApplicationDb = $em->getRepository(ResidencyApplication::class)->findOneByGoogleFormId($id);

            //Modify files
            if( 0 ) {
                if ($residencyApplicationDb) {

                    //Move documents from 'documents' to 'CoverLetter' and change type to 'Residency ERAS Document'
                    $modified = false;
                    foreach($residencyApplicationDb->getDocuments() as $document) {
                        $document->setType($this->documentErasType);
                        $residencyApplicationDb->removeDocument($document);
                        $residencyApplicationDb->addCoverLetter($document);
                        $modified = true;
                    }

                    if ($modified) {
                        $em->flush();

                        $msg = "Document ".$document." moved for ".$residencyApplicationDb->getApplicantFullName();
                        echo $msg."<br>";
                        $logger->notice($msg);
                    }

                    $logger->notice('Skip this residency application, because it already exists in DB. googleFormId=' . $id);
                    echo 'Skip this residency application, because it already exists in DB. googleFormId=' . $id . "<br>";

                    //exit("EOF $firstName $lastName (ID $id)");
                    continue; //skip this fell application, because it already exists in DB
                }
            }


            if( !$residencyApplicationDb ) {
                $errorMsg = $dataFileName.": Skip ResidencyApplication not found by id=$id";
                echo $errorMsg."<br>";
                $logger->notice($errorMsg);
                continue;
            }

            //create Document and attach to $residencyApplicationDb
            //$fileTypeName = 'ERAS1';
            $document = $this->attachDocument($residencyApplicationDb,$inputFilePath,$fileOriginalName,$fileType,$fileTypeName,$systemUser);

            if( $document ) {
                $em->persist($document);
                $em->flush();
                $count++;
                echo $row.": Created file $fileName for ResidencyApplication ID#".$residencyApplicationDb->getId().", ".$residencyApplicationDb->getApplicantFullName()." with id=$id <br>";
            } else {
                echo $row.": File $fileName not created for ResidencyApplication ID#".$residencyApplicationDb->getId().", ".$residencyApplicationDb->getApplicantFullName()." with id=$id <br>";
            }

            //exit("EOF $fileTypeName");
        }

        return "Imported $fileTypeName files: count=".$count;
    }
    public function attachDocument( $residencyApplicationDb, $inputFilePath, $fileOriginalName, $fileType, $fileTypeName, $author ) {
        $document = NULL;

        $fileExtStr = NULL;
        if( $fileType == "pdf" ) {
            $fileExtStr = "pdf";
        }
        if( $fileType == "doc" ) {
            $fileExtStr = "doc";
        }
        if( !$fileExtStr ) {
            exit("Unknown file type ".$fileType);
        }

        //create unique file name
        //$currentDatetime = new \DateTime();
        //$currentDatetimeTimestamp = $currentDatetime->getTimestamp();
        //$fileUniqueName = $currentDatetimeTimestamp.'ID'.$residencyApplicationDb->getId().".".$fileExtStr;

        $fileUniqueName = 'imported-'.$fileTypeName.'-'.'ID'.$residencyApplicationDb->getId().".".$fileExtStr;

        $inputFileSize = filesize($inputFilePath);
        //echo "inputFileSize=".$inputFileSize."<br>";
        if( !$inputFileSize ) {
            exit("Invalid file size=".$inputFileSize);
        }

        //$documentType = "Residency Application Document";
        //$uploadPath = 'Uploaded'.DIRECTORY_SEPARATOR.'resapp'.DIRECTORY_SEPARATOR.'documents'.DIRECTORY_SEPARATOR;

        //check if file already exists by file id
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $documentDb = $this->em->getRepository(Document::class)->findOneByUniqueid($fileUniqueName);
        if( $documentDb ) {
            echo "Document already exists with uniqueid=".$fileUniqueName."; Application Id=".$residencyApplicationDb->getId();
            //$logger->notice($event);
            return null;
        }

        $fileOriginalName = basename($fileOriginalName);
        //echo "fileOriginalName=".$fileOriginalName."<br>";

        //copy file to resapp folder
        //$destinationFolder = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $this->uploadPath;

        $userSecUtil = $this->container->get('user_security_utility');
        $resappuploadpath = $userSecUtil->getSiteSettingParameter('resappuploadpath'); //resapp/documents
        $uploadPath = 'Uploaded'.DIRECTORY_SEPARATOR.$resappuploadpath;

        $destinationFolder = realpath($uploadPath);
        //echo "destinationFolder=".$destinationFolder."<br>";
        if( !file_exists($destinationFolder) ) {
            //echo "Create destination folder <br>";
            mkdir($destinationFolder, 0700, true);
            chmod($destinationFolder, 0700);
        }
        $destinationFilePath = $destinationFolder . DIRECTORY_SEPARATOR . $fileUniqueName;
        if( !file_exists($destinationFilePath) ) {
            if( !copy($inputFilePath, $destinationFilePath ) ) {
                //echo "failed to copy $filePath...\n<br>";
                $errorMsg = "Residency Application document $inputFilePath - Failed to copy to destination folder; filePath=".$destinationFilePath;
                exit($errorMsg);
            }
        }

        $document = new Document($author);
        $document->setUniqueid($fileUniqueName);
        $document->setUniquename($fileUniqueName);
        $document->setUploadDirectory($uploadPath);
        $document->setSize($inputFileSize);

        $document->setCleanOriginalname($fileOriginalName);

        //$transformer = new GenericTreeTransformer($this->em, $author, "DocumentTypeList", "UserdirectoryBundle");
        //$documentType = "Residency Application Document";
        //$documentTypeObject = $transformer->reverseTransform($documentType);
        //echo "documentTypeObject ID=".$documentTypeObject->getId()."<br>";
        $document->setType($this->documentErasType);

        //$residencyApplicationDb->addDocument($document);
        $residencyApplicationDb->addCoverLetter($document);

        return $document;
    }




    public function importApplications($max=NULL) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        set_time_limit(720); //12 min

        //$projectRoot = $this->container->get('kernel')->getProjectDir();
        //exit("projectRoot=$projectRoot");

        $em = $this->em;
        $default_time_zone = $this->container->getParameter('default_time_zone');

        //$res = "Import Residency Applications";

        $this->getResidencySpecialties();
        dump($this->residencySpecialtyArr);

        $this->getEnrolmentYear();
        dump($this->enrolmentYearArr);

        //exit('111');

        if( file_exists($this->path) ) {
            //echo $row.": The file exists: $inputFilePath <br>";
        } else {
            exit("importApplications: Source folder does not exist. path=[".$this->path."]<br>");
        }

        try {
            $inputFileName = $this->path . "/DB/" . "PRA_APPLICANT_INFO.csv";
            //$inputFileName = "../../../../../../ResidencyImport" . "/DB/"."PRA_APPLICANT_INFO.csv";
            //$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            //Use depreciated PHPExcel, because PhpOffice does not read correctly rows of the google spreadsheets
            //$inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            //$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            //$objPHPExcel = $objReader->load($inputFileName);

            //migrate PHPExcel=>PhpOffice: All users must migrate to its direct successor PhpSpreadsheet, or another alternative.
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(\Exception $e) {
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

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
        $employmentType = $em->getRepository(EmploymentType::class)->findOneByName("Pathology Residency Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Residency Applicant");
        }

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResAppStatus'] by [ResAppStatus::class]
        $activeStatus = $em->getRepository(ResAppStatus::class)->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."active");
        }
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResAppStatus'] by [ResAppStatus::class]
        $archiveStatus = $em->getRepository(ResAppStatus::class)->findOneByName("archive");
        if( !$archiveStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."archive");
        }

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:PostSophList'] by [PostSophList::class]
        $postSophPathologyEntity = $em->getRepository(PostSophList::class)->findOneByName("Pathology");
        if( !$postSophPathologyEntity ) {
            throw new EntityNotFoundException('Unable to find PostSophList entity by name='."Pathology");
        }
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:PostSophList'] by [PostSophList::class]
        $postSophNoneEntity = $em->getRepository(PostSophList::class)->findOneByName("None");
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

        //$testing = true;
        $testing = false;

        //$residencyApplications = new ArrayCollection();
        $count = 0;
        $processingCount = 0;

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
//            if( $id != '2493' ) {
//                echo 'Skip this residency application with googleFormId='.$id."<br>";
//                continue;
//            }

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


            $processingCount++;
            if( $max && $processingCount > (int)$max ) {
                exit('end of processing '.$max.' applications'); //.($processingCount+1).">=".$max
            }

            echo $row.": $firstName $lastName (ID $id) <br>";

            //Convert: ACTIVED	AOA	COUPLES	MD_PHD	POSTSOPH DO
            if( $activeD."" == '1' ) {
                $activeD = true;
            } else {
                $activeD = false;
            }

            if( $aoa."" == '1' ) {
                $aoa = true;
            } else {
                $aoa = false;
            }

            if( $couples."" == '1' ) {
                $couples = true;
            } else {
                $couples = false;
            }

            if( $postSoph."" == '1' ) {
                $postSoph = true;
            } else {
                $postSoph = false;
            }

            if( $mdPhd."" == '1' ) {
                $mdPhd = true;
            } else {
                $mdPhd = false;
            }
            if( $do."" == '1' ) {
                $do = true;
            } else {
                $do = false;
            }
            if( $md."" == '1' ) {
                $md = true;
            } else {
                $md = false;
            }

        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResidencyApplication'] by [ResidencyApplication::class]
            $residencyApplicationDb = $em->getRepository(ResidencyApplication::class)->findOneByGoogleFormId($id);

            //Modify applications
            if( 0 ) {
                if ($residencyApplicationDb) {

                    $modified = false;

                    echo "Start modify $firstName $lastName (ID $id) Medschool=$medSchool <br>";
                    //$this->modifyTraining($residencyApplicationDb,$systemUser,$mdPhd,$do,$md);
                    $training = $this->setResAppTraining($residencyApplicationDb, $systemUser, $medSchool, $graduateDate, $mdPhd, $do, $md);
                    if ($training) {
                        $em->persist($training);
                        $modified = true;
                    }

                    //AOA, Couples
                    if ($residencyApplicationDb->getAoa() != $aoa) {
                        $residencyApplicationDb->setAoa($aoa);
                        $modified = true;
                    }
                    if ($residencyApplicationDb->getCouple() != $couples) {
                        $residencyApplicationDb->setCouple($couples);
                        $modified = true;
                    }

                    if ($modified) {
                        $em->flush();
                    }

                    $logger->notice('Skip this residency application, because it already exists in DB. googleFormId=' . $id);
                    echo 'Skip this residency application, because it already exists in DB. googleFormId=' . $id . "<br>";

                    //exit("EOF $firstName $lastName (ID $id)");
                    continue; //skip this fell application, because it already exists in DB
                }
            }

            if( $residencyApplicationDb ) {
                $logger->notice('Skip this residency application, because it already exists in DB. googleFormId='.$id);
                echo 'Skip this residency application, because it already exists in DB. googleFormId='.$id."<br>";
                continue; //skip this fell application, because it already exists in DB
            }
            
            //exit('Testing');

            //echo $row.": $firstName $lastName (ID $id) <br>";

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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $user = $em->getRepository(User::class)->findOneByPrimaryPublicUserId($username);

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
                $residencyType = trim((string)$residencyType);
                //$residencyType = $this->capitalizeIfNotAllCapital($residencyType);
                $residencyType = strtoupper($residencyType);
                //$transformer = new GenericTreeTransformer($em, $systemUser, 'ResidencySpecialty');
                //$residencyTypeEntity = $transformer->reverseTransform($residencyType);
                $residencyTypeEntity = $this->residencySpecialtyArr[$residencyType];
                $residencyApplication->setResidencyTrack($residencyTypeEntity);
            }

            $this->setResAppTraining($residencyApplication,$systemUser,$medSchool,$graduateDate,$mdPhd,$do,$md);

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

            //DELETE applicant, just set actived=0
            if( $activeD ) {
                $residencyApplication->setAppStatus($activeStatus);
            } else {
                $residencyApplication->setAppStatus($archiveStatus);
            }

            $residencyApplication->setAoa($aoa);
            $residencyApplication->setCouple($couples);

            //Post-Sophomore Fellowship in Pathology/No
            if( $postSoph ) {
                $residencyApplication->setPostSoph($postSophPathologyEntity);
            } else {
                $residencyApplication->setPostSoph($postSophNoneEntity);
            }

            //exit('end applicant');
            $event = "Populated residency applicant " . $displayName . "; Application ID " . $residencyApplication->getId();

            if( !$testing ) {
                $em->persist($user);
                $em->flush();
                $logger->notice($event);
                $count++;
            }

            echo "$event <br>";

            echo "###################### <br>";

//            if( $max && $count >= $max ) {
//                exit('end of import count='.$count);
//            }

            //exit('end application');

        } //for



        return "Imported residency applications. count=$count <br>";
    }

    public function setResAppTraining($residencyApplication,$author,$medSchool,$graduateDate,$mdPhd,$do,$md) {
        $em = $this->em;

        $training = NULL;
        $user = $residencyApplication->getUser();

        $trainings = $residencyApplication->getTrainings();
        if( count($trainings) > 0 ) {
            $training = $trainings[0];
        }

        $removedTrainingArr = array();
        //remove existing training
        foreach( $residencyApplication->getTrainings() as $thisTraining ) {
            if( $training && $training->getId() != $thisTraining->getId() ) {
                $residencyApplication->removeTraining($thisTraining);
                //$em->remove($thisTraining);
                $removedTrainingArr[$thisTraining->getId()] = $thisTraining;
            }
        }
        foreach( $user->getTrainings() as $thisTraining ) {
            if( $training && $training->getId() != $thisTraining->getId() ) {
                $user->removeTraining($thisTraining);
                //$em->remove($thisTraining);
                $removedTrainingArr[$thisTraining->getId()] = $thisTraining;
            }
        }

        foreach($removedTrainingArr as $removedTraining) {
            $em->remove($removedTraining);
        }

        if( !$training ) {
            $training = new Training($author);
            $training->setOrderinlist(1);
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:TrainingTypeList'] by [TrainingTypeList::class]
        $trainingType = $em->getRepository(TrainingTypeList::class)->findOneByName('Medical');
        if( !$trainingType ) {
            exit("TrainingTypeList not found by name=Medical");
        }
        $training->setTrainingType($trainingType);

        $residencyApplication->addTraining($training);
        $user->addTraining($training);

        $schoolDegree = NULL;

        if ($mdPhd) {
            $schoolDegree = "MD/PhD";
            $this->setTrainingDegree($training,$schoolDegree,$author);
        }
        if ($do) {
            $schoolDegree = "DO";
            $this->setTrainingDegree($training,$schoolDegree,$author);
        }
        if ($md) {
            $schoolDegree = "MD";
            $this->setTrainingDegree($training,$schoolDegree,$author);
        }

        if( !$schoolDegree ) {
            $training->setDegree(NULL);
        }

        if( $medSchool ) {
            $params = array('type'=>'Educational');
            $medSchool = trim((string)$medSchool);
            //$medSchool = $this->capitalizeIfNotAllCapital($medSchool);
            $transformer = new GenericTreeTransformer($em, $author, 'Institution', null, $params);
            $schoolNameEntity = $transformer->reverseTransform($medSchool);
            $training->setInstitution($schoolNameEntity);
        }

        if( $graduateDate ) {
            $training->setCompletionDate($this->transformDatestrToDate($graduateDate));
        }

        return $training;
    }
    public function setTrainingDegree($training,$schoolDegree,$author) {
        $transformer = new GenericTreeTransformer($this->em, $author, 'TrainingDegreeList');
        $schoolDegreeEntity = $transformer->reverseTransform($schoolDegree);
        $training->setDegree($schoolDegreeEntity);
    }
    
    public function getDegreeMapping( $degreeValue ) {

        if( !$degreeValue ) {
            return $degreeValue;
        }

        $schoolDegree = NULL;

        if( $degreeValue == "M.D./Ph.D." ) {
            $schoolDegree = "MD/PhD";
        }
        if ( $degreeValue == "D.O." ) {
            $schoolDegree = "DO";
        }
        if ( $degreeValue == "M.D." ) {
            $schoolDegree = "MD";
        }
        if ( $degreeValue == "B.MED" ) {
            $schoolDegree = "BMed";
        }
        if ( $degreeValue == "M.B.B.Ch." ) {
            $schoolDegree = "MBBch";
        }
        if ( $degreeValue == "M.B." ) {
            $schoolDegree = "MB";
        }
        if ( $degreeValue == "M.B.,B.S." ) {
            $schoolDegree = "MBBS";
        }
        if ( $degreeValue == "M.B.Ch.B." ) {
            $schoolDegree = "MBChB";
        }
        if ( $degreeValue == "M.B.Ch.B." ) {
            $schoolDegree = "MBChB";
        }
        if ( $degreeValue == "M.C." ) {
            $schoolDegree = "MC";
        }
        if ( $degreeValue == "M.CH.ORTHO" ) {
            $schoolDegree = "MCH/ORTHO";
        }
        if ( $degreeValue == "M.D./M.P.H." ) {
            $schoolDegree = "MD/MPH";
        }
        if ( $degreeValue == "F.P.C." ) {
            $schoolDegree = $degreeValue;
        }
        if ( $degreeValue == "M.D./Other" ) {
            $schoolDegree = "MD/Other";
        }
        if ( $degreeValue == "M.D./M.B.A." ) {
            $schoolDegree = "MD/MBA";
        }
        if ( $degreeValue == "M.B.B.Ch.B" ) {
            $schoolDegree = "MBBChB";
        }
        if ( $degreeValue == "M.D.,C.M." ) {
            $schoolDegree = "MD/CM";
        }
        if ( $degreeValue == "M.Med." ) {
            $schoolDegree = "MMed";
        }
        if ( $degreeValue == "M.S./M.D." ) {
            $schoolDegree = "MS/MD";
        }
        if ( $degreeValue == "DO/PhD" ) {
            $schoolDegree = "DO/PhD";
        }
        if ( $degreeValue == "D.M.D." ) {
            $schoolDegree = "DMD";
        }
        if ( $degreeValue == "M.Surg." ) {
            $schoolDegree = "M/Surg";
        }
        if ( $degreeValue == "B.S./M.D." ) {
            $schoolDegree = "BS/MD";
        }

        if( $schoolDegree ) {
            //echo "Found: [$degreeValue] => [$schoolDegree] <br>";
        } else {
            //Most of the time our internal degree does not have '.'
            //$schoolDegree = str_replace('.','',$schoolDegree);
            //exit("Uknown degreeValue=[$degreeValue]");
            $schoolDegree = $degreeValue;
        }

        return $schoolDegree;
    }
    public function getCitizenshipMapping( $countryCitizenshipValue ) {

        if( !$countryCitizenshipValue ) {
            return $countryCitizenshipValue;
        }

        $countryCitizenshipStr = NULL;

        if( $countryCitizenshipValue == "U.S. Citizen" ) {
            $countryCitizenshipStr = "United States";
        }

        if( !$countryCitizenshipStr ) {
            $countryCitizenshipStr = $countryCitizenshipValue;
        }

        //don’t lose data
        return $countryCitizenshipStr;
        
//        if( strpos((string)$countryCitizenshipValue, 'Foreign') !== false ) {
//            $countryCitizenshipStr = $countryCitizenshipValue;
//        }
//        if ( $countryCitizenshipValue == "Foreign National Currently in the U.S. with Valid Visa Status" ) {
//            $countryCitizenshipStr = $countryCitizenshipValue;
//        }
//        if ( $countryCitizenshipValue == "Permanent Resident (Green Card Holder)" ) {
//            $countryCitizenshipStr = $countryCitizenshipValue;
//        }
//        if ( $countryCitizenshipValue == "Pending Application for Permanent Resident" ) {
//            $countryCitizenshipStr = $countryCitizenshipValue;
//        }
//        if ( $countryCitizenshipValue == "Refugee/Asylum/Displaced Person" ) {
//            $countryCitizenshipStr = $countryCitizenshipValue;
//        }
//        if ( $countryCitizenshipValue == "Conditional Permanent Resident" ) {
//            $countryCitizenshipStr = $countryCitizenshipValue;
//        }
//
//        if( $countryCitizenshipStr ) {
//            //echo "Found: [$countryCitizenshipValue] => [$countryCitizenshipStr] <br>";
//        } else {
//            //echo "Not Found: [$countryCitizenshipValue] <br>";
//            //exit("Unknown countryCitizenshipValue=[$countryCitizenshipValue]");
//            $countryCitizenshipStr = $countryCitizenshipValue;
//        }
//
//        return $countryCitizenshipStr;
    }
    public function getVisaMapping( $visaValue ) {

        //don’t lose data
        return $visaValue;

//        if( !$visaValue ) {
//            return $visaValue;
//        }
//        $defaultVisaStr = "Other-please contact the program coordinator";
//        $visaStr = NULL;
//
//        if( $visaValue == "J-1 - Visa for exchange visitor" ) {
//            $visaStr = "J-1 visa";
//        }
//        if( $visaValue == "H-1B - Specialty occupation, DoD worker, etcetera" ) {
//            $visaStr = "H-1B visa";
//        }
//        if( $visaValue == "Employment Authorization Document (EAD)" ) {
//            $visaStr = "EAD";
//        }
//        if( $visaValue == "Citizen, Legal Permanent Resident, Refugee, Asylee" ) {
//            $visaStr = "N/A (US Citizenship)";
//        }
//
//        if( $visaValue == "E-2 - Treaty investor, spouse and children (Employment Authorization Document - EAD)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "F-1 - Academic Student (Employment Authorization Document - Optional Practical Training - OPT)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "J-2 - Spouse or child of J-1 (Employment Authorization Document - EAD)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "TN - NAFTA trade visa for Canadians and Mexicans" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "H-4 - Spouse or child of H-1, H-2, H-3" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "Other" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "O-1 - Extraordinary ability in sciences, arts, education, business or athletics" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "L2- Dependent of Intra-Company Transferee (Employment Authorization Document - EAD)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "Adjustment of Status applicant (Green Card application) (EAD)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "J-2 - Spouse or child of J-1 (EAD)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "L-2 - Dependent of Intra-Company Transferee (EAD)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "F-1 - Academic Student (EAD, OPT)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "H-4 - Spouse or child of H-1, H-2, H-3 (EAD)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//        if( $visaValue == "E-2 - Treaty investor, spouse and children (EAD)" ) {
//            $visaStr = $defaultVisaStr;
//        }
//
//        if( $visaStr ) {
//            echo "Found: [$visaValue] => [$visaStr] <br>";
//        } else {
//            //$visaStr = "Other-please contact the program coordinator";
//            //echo "Not Found: [$visaValue] <br>";
//            exit("Unknown visaValue=[$visaValue]");
//            $visaStr = $visaValue;
//        }
//
//        return $visaStr;
    }
    

    public function transformDatestrToDate($datestr) {
        $userSecUtil = $this->container->get('user_security_utility');
        return $userSecUtil->transformDatestrToDateWithSiteEventLog($datestr,$this->container->getParameter('resapp.sitename'));
    }

    public function getEnrolmentYear() {

        $logger = $this->container->get('logger');

        if( file_exists($this->path) ) {
            //echo $row.": The file exists: $inputFilePath <br>";
        } else {
            exit("getEnrolmentYear: Source folder does not exist. path=[".$this->path."]<br>");
        }

        $inputFileName = $this->path . "/DB/"."PRA_ENROLLMENT_INFO.csv";

        try {
            //$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            //Use depreciated PHPExcel, because PhpOffice does not read correctly rows of the google spreadsheets
            //$inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            //$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            //$objPHPExcel = $objReader->load($inputFileName);

            //migrate PHPExcel=>PhpOffice: All users must migrate to its direct successor PhpSpreadsheet, or another alternative.
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(\Exception $e) {
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

    //TODO: ResidencySpecialty -> ResidencyTrackList
    public function getResidencySpecialties() {

        $residencySpecialtyStrArr = array('AP','CP','AP/CP','AP/EXP','CP/EXP');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $wcmc = $this->em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
        if( !$wcmc ) {
            exit('generateDefaultOrgGroupSiteParameters: No Institution: "WCM"');
        }

        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution',
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
        $pathologyInstitution = $this->em->getRepository(Institution::class)->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );
        $pathologyInstitutionId = $pathologyInstitution->getId();

        foreach($residencySpecialtyStrArr as $residencySpecialtyStr) {
            //$residencySpecialtyEntity = $this->em->getRepository('AppUserdirectoryBundle:ResidencySpecialty')->findOneByName($residencySpecialtyStr);

            //$repository = $this->em->getRepository('AppUserdirectoryBundle:ResidencySpecialty');
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResidencyTrackList'] by [ResidencyTrackList::class]
            $repository = $this->em->getRepository(ResidencyTrackList::class);
            $dql =  $repository->createQueryBuilder("list");
            $dql->select('list');
            $dql->leftJoin("list.institution", "institution");
            $dql->where("list.name = :name AND institution.id = :institutionId");

            $query = $dql->getQuery();
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
                exit('Unable to find ResidencyTrackList entity by name='.$residencySpecialtyStr);
                //throw new EntityNotFoundException('Unable to find ResidencySpecialty entity by name='.$residencySpecialtyStr);
            }

            $residencySpecialtyArr[$residencySpecialtyStr] = $residencySpecialtyEntity;
        }

        $this->residencySpecialtyArr = $residencySpecialtyArr;
    }

    public function getFacultyResident($allowCreate=false) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $authUtil = $this->container->get('authenticator_utility');

        if( file_exists($this->path) ) {
            //echo $row.": The file exists: $inputFilePath <br>";
        } else {
            exit("getFacultyResident: Source folder does not exist. path=[".$this->path."]<br>");
        }

        $inputFileName = $this->path . "/DB/"."PRA_FACULTY_RESIDENT_INFO.csv";

        try {
            //$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

            //Use depreciated PHPExcel, because PhpOffice does not read correctly rows of the google spreadsheets
            //$inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
            //$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            //$objPHPExcel = $objReader->load($inputFileName);

            //migrate PHPExcel=>PhpOffice: All users must migrate to its direct successor PhpSpreadsheet, or another alternative.
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch(\Exception $e) {
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

        $default_time_zone = $this->container->getParameter('default_time_zone');

        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

        $localUserkeytype = $userSecUtil->getUsernameType('local-user');
        if( !$localUserkeytype ) {
            throw new EntityNotFoundException('Unable to find local user keytype');
        }

        $ldapUserkeytype = $userSecUtil->getUsernameType('ldap-user');
        if( !$ldapUserkeytype ) {
            throw new EntityNotFoundException('Unable to find ldap-user user keytype');
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
        $employmentType = $this->em->getRepository(EmploymentType::class)->findOneByName("Full Time");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Full Time");
        }

        $yestardayDate = new \DateTime();
        $yestardayDate = $yestardayDate->add(\DateInterval::createFromDateString('yesterday'));

        $usersArr = array();

        $count = 0;
        $notFoundUserCount = 0;

        //for each user in excel
        for( $row = 2; $row <= $highestRow; $row++ ){

            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //FACULTY_RESIDENT_ID
            //LAST_NAME
            //FIRST_NAME
            //PHONE
            //EMAIL
            //DATE_CREATED
            //ACTIVED
            //ROLE

            $FACULTY_RESIDENT_ID = $this->getValueByHeaderName('FACULTY_RESIDENT_ID', $rowData, $headers);
            $LAST_NAME = $this->getValueByHeaderName('LAST_NAME', $rowData, $headers);
            $FIRST_NAME = $this->getValueByHeaderName('FIRST_NAME', $rowData, $headers);
            $PHONE = $this->getValueByHeaderName('PHONE', $rowData, $headers);
            $EMAIL = $this->getValueByHeaderName('EMAIL', $rowData, $headers);
            $DATE_CREATED = $this->getValueByHeaderName('DATE_CREATED', $rowData, $headers);
            $ACTIVED = $this->getValueByHeaderName('ACTIVED', $rowData, $headers);
            $ROLE = $this->getValueByHeaderName('ROLE', $rowData, $headers);
            $mobilenumber = NULL;

            $emailArr = explode("@",$EMAIL); //email@med.cornell.edu
            if( count($emailArr) > 0 ) {
                $cwid = $emailArr[0];
            }
            if( !$cwid ) {
                exit("No CWID found by email=".$EMAIL);
            }

            $cwid = $this->canonicalize($cwid);

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $user = $this->em->getRepository(User::class)->findOneByPrimaryPublicUserId($cwid);

            if( !$user ) {
                $emailCanonical = $this->canonicalize($EMAIL);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                $user = $this->em->getRepository(User::class)->findOneByEmailCanonical($emailCanonical);
            }

            if( !$user ) {
                $notFoundUserCount++;
                $errorMsg = $notFoundUserCount.": No user found by cwid=".$cwid." (firstName=$FIRST_NAME, lastName=$LAST_NAME)";
                echo $errorMsg."<br>";

                if( $allowCreate ) {

                    //Create new interviewer user
                    $lastNameCap = $this->capitalizeIfNotAllCapital($LAST_NAME);
                    $firstNameCap = $this->capitalizeIfNotAllCapital($FIRST_NAME);

                    //$lastNameCap = preg_replace('/\s+/', '_', $lastNameCap);
                    //$firstNameCap = preg_replace('/\s+/', '_', $firstNameCap);
                    //Last Name + First Name + Email
                    //$username = $lastNameCap . "_" . $firstNameCap;

                    //$authUtil = new AuthUtil($this->container,$em);
                    $searchRes = $authUtil->searchLdap($cwid,1,false);
                    //echo "1 searchRes=".$searchRes."<br>";
                    if( $searchRes == NULL || count($searchRes) == 0 ) {
                        $searchRes = $authUtil->searchLdap($cwid,2,false);
                    }
                    //echo "2 searchRes=".$searchRes."<br>";
                    if( $searchRes == NULL || count($searchRes) == 0 ) {
                        $userkeytype = $localUserkeytype;
                        $disabledUser = true;
                    } else {
                        echo "### ldap user=".$cwid."###<br>";
                        //exit('111');
                        $disabledUser = false;
                        $userkeytype = $ldapUserkeytype;

                        if( array_key_exists('telephonenumber', $searchRes) ) {
                            $ldapPhone = $searchRes['telephoneNumber'];
                            $ldapPhone = trim((string)$ldapPhone);
                            if( $PHONE != $ldapPhone ) {
                                $PHONE = $ldapPhone;
                            }
                        }
                        if( array_key_exists('mobile', $searchRes) ) {
                            $mobilenumber = $searchRes['mobile'];
                        }

                        if (array_key_exists('mail', $searchRes)) {
                            $ldapEmail = $searchRes['mail'];
                            $ldapEmail = trim((string)$ldapEmail);
                            if( $EMAIL != $ldapEmail ) {
                                $EMAIL = $ldapEmail;
                            }
                        }
                    }

                    $displayName = $FIRST_NAME . " " . $LAST_NAME;

                    //create excel user
                    $addobjects = false;
                    $user = new User($addobjects);
                    $user->setKeytype($userkeytype);
                    $user->setPrimaryPublicUserId($cwid);
                    $user->setAuthor($systemUser);


                    //set unique username
                    $usernameUnique = $user->createUniqueUsername();
                    $user->setUsername($usernameUnique);
                    $user->setUsernameCanonical($usernameUnique);

                    $user->setEmail($EMAIL);
                    $user->setEmailCanonical($EMAIL);

                    $user->setFirstName($FIRST_NAME);
                    $user->setLastName($LAST_NAME);
                    //$user->setMiddleName($middleName);
                    $user->setDisplayName($displayName);
                    $user->setPassword("");
                    $user->setCreatedby('resapp_migration');
                    $user->getPreferences()->setTimezone($default_time_zone);

                    //Pathology Residency Applicant in EmploymentStatus
                    $employmentStatus = new EmploymentStatus($systemUser);
                    $employmentStatus->setEmploymentType($employmentType);

                    if( $disabledUser ) {
                        $user->setEnabled(false);
                        $employmentStatus->setTerminationDate($yestardayDate);
                    }

                    $user->addEmploymentStatus($employmentStatus);

                    $user->setPreferredPhone($PHONE);

                    if( $mobilenumber ) {
                        $user->setPreferredMobilePhone($mobilenumber);
                    }

                    $this->em->persist($user);
                    $this->em->flush();
                    //exit('EOF create new user='.$user);

                } else { //if( $allowCreate ) {

                    //exit("EOF get Faculty Resident: ".$errorMsg);

                } //else( $allowCreate ) {

            } //if user

            $usersArr[$FACULTY_RESIDENT_ID] = array(
                'user' => $user,
                'cwid' => $cwid,
                'LAST_NAME' => $LAST_NAME,
                'FIRST_NAME' => $FIRST_NAME,
                'PHONE' => $PHONE,
                'EMAIL' => $EMAIL,
                'DATE_CREATED' => $DATE_CREATED,
                'ROLE' => $ROLE
            );

            $count++;
        }

        echo "Total count $count, notFoundUserCount=$notFoundUserCount <br>";
        //exit('EOF');
        $this->usersArr = $usersArr;
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

        if( $res ) {
            $res = trim((string)$res);
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

    /**
     * {@inheritdoc}
     */
    public function canonicalize($string)
    {
        if (null === $string) {
            return;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }



} 