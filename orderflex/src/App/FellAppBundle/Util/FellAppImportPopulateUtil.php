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
 * Date: 8/20/15
 * Time: 4:21 PM
 */

namespace App\FellAppBundle\Util;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use App\FellAppBundle\Entity\DataFile;
use App\FellAppBundle\Entity\Interview;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Entity\BoardCertification;
use App\UserdirectoryBundle\Entity\Citizenship;
use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\UserdirectoryBundle\Entity\Examination;
use App\FellAppBundle\Entity\FellowshipApplication;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\JobTitleList;
use App\UserdirectoryBundle\Entity\Location;
use App\FellAppBundle\Entity\Reference;
use App\UserdirectoryBundle\Entity\StateLicense;
use App\UserdirectoryBundle\Entity\Training;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use App\UserdirectoryBundle\Util\EmailUtil;
use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//$fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

class FellAppImportPopulateUtil {

    protected $em;
    protected $container;

    protected $uploadDir;
    protected $systemEmail;


    public function __construct( $em, $container ) {

        $this->em = $em;
        $this->container = $container;

        $this->uploadDir = 'Uploaded';

        $userutil = new UserUtil();
        $this->systemEmail = $userutil->getSiteSetting($this->em,'siteEmail');
    }



    //1)  Import sheets from Google Drive Folder
    //1a)   import all sheets from Google Drive folder
    //1b)   add successefull downloaded sheets to DataFile DB object with status "active"
    //
    //2)  Populate applications from DataFile DB object
    //2a)   for each sheet with not "completed" status in DataFile:
    //      populate application by populateSingleFellApp($sheet) (this function add report generation to queue)
    //2b)   if populateSingleFellApp($sheet) return true => set sheet DataFile status to "completed"
    //
    //3)  Delete successfully imported sheets and uploads from Google Drive if deleteImportedAplicationsFellApp is true
    //3a)   foreach "completed" sheet in DataFile:
    //3b)   delete sheet and uploads from Google drive
    //3c)   delete sheet object from DataFile
    //3d)   unlink sheet file from folder
    //
    //4)  Process backup sheet on Google Drive
    public function processFellAppFromGoogleDrive() {

        //1) Import sheets from Google Drive Folder
        $filesGoogleDrive = $this->importSheetsFromGoogleDriveFolder();

        //2) Populate applications from DataFile DB object
        $populatedCount = $this->populateApplicationsFromDataFile();

        //3) Delete old sheet and uploads from Google Drive if deleteOldAplicationsFellApp is true
        $deletedSheetCount = $this->deleteSuccessfullyImportedApplications();

        //4)  Process backup sheet on Google Drive
        $populatedBackupApplications = $this->processBackupFellAppFromGoogleDrive();

        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $generatedReport = $fellappRepGen->tryRun(); //run hard run report generation

        //exit('eof processFellAppFromGoogleDrive');

        $result = "Finish processing Fellowship Application on Google Drive and on server.<br>".
            "filesGoogleDrive=".count($filesGoogleDrive).", populatedCount=".$populatedCount.
            ", deletedSheetCount=".$deletedSheetCount.", populatedBackupApplications=".$populatedBackupApplications.
            ", First generated report in queue=".$generatedReport;

        $logger = $this->container->get('logger');
        $logger->notice($result);

        //create eventlog for this cron job event. It will be used later on to display in "Last successful import:
        if(1) {
            //Event Logger with event type "Import of Fellowship Applications Spreadsheet". It will be used to get lastImportTimestamps
            $userSecUtil = $this->container->get('user_security_utility');
            $systemUser = $userSecUtil->findSystemUser();
            $eventTypeStr = "Import of Fellowship Applications Spreadsheet";
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $result, $systemUser, null, null, $eventTypeStr);
        }

        return $result;
    }

    //1)  Import sheets from Google Drive
    //1a)   import all sheets from Google Drive folder
    //1b)   add successefull downloaded sheets to DataFile DB object with status "active"
    public function importSheetsFromGoogleDriveFolder() {

        if( !$this->checkIfFellappAllowed("Import from Google Drive") ) {
            return null;
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        //get Google service
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            $logger->warning($event);
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Error');
            $this->sendEmailToSystemEmail($event, $event);
            return null;
        }

        //echo "service ok <br>";

        $folderIdFellApp = $userSecUtil->getSiteSettingParameter('folderIdFellApp');
        if( !$folderIdFellApp ) {
            $logger->warning('Google Drive Folder ID is not defined in Site Parameters. sourceFolderIdFellApp='.$folderIdFellApp);
        }

        //get all files in google folder
        $filesGoogleDrive = $this->processFilesInFolder($folderIdFellApp,$service,"Fellowship Application Spreadsheet");

        $logger->notice("Processed " . count($filesGoogleDrive) . " files with applicant data from Google Drive");

        return $filesGoogleDrive;
    }

    //2)  Populate applications from DataFile DB object
    //2a)   for each sheet with not "completed" status in DataFile:
    //      populate application by populateSingleFellApp($sheet) (this function add report generation to queue)
    //2b)   if populateSingleFellApp($sheet) return true => set sheet DataFile status to "completed"
    public function populateApplicationsFromDataFile() {

        $logger = $this->container->get('logger');

        if( !$this->checkIfFellappAllowed("Populate not completed applications") ) {
            $logger->warning("Not Allowed to populate not completed applications.");
            return null;
        }

        //get not completed DataFile
        $repository = $this->em->getRepository('AppFellAppBundle:DataFile');
        $dql =  $repository->createQueryBuilder("datafile");
        $dql->select('datafile');
        $dql->leftJoin("datafile.fellapp", "fellapp");
        $dql->where("datafile.status != :completeStatus OR fellapp.id IS NULL");

        $query = $this->em->createQuery($dql);

        $query->setParameter("completeStatus","completed");

        $datafiles = $query->getResult();

        $logger->notice("Start populating " . count($datafiles) . " data files (not populated applications) on the server.");

        $populatedCount = 0;

        foreach( $datafiles as $datafile ) {

            $populatedFellowshipApplications = $this->populateSingleFellApp( $datafile->getDocument() );
            $count = count($populatedFellowshipApplications);

            if( $count > 0 ) {
                //this method process a sheet with a single application => $populatedFellowshipApplications has only one element
                $populatedFellowshipApplication = $populatedFellowshipApplications[0];
                if( $populatedFellowshipApplication ) {
                    $logger->notice("Completing population of the FellApp ID " . $populatedFellowshipApplication->getID() . " data file ID " . $datafile->getId() . " on the server.");

                    $datafile->setFellapp($populatedFellowshipApplication);
                    $datafile->setStatus("completed");
                    $this->em->flush($datafile);

                    //$logger->notice("Status changed to 'completed' for data file ID ".$datafile->getId());

                    $populatedCount = $populatedCount + $count;
                } else {
                    $logger->warning("Error populating data file ID ".$datafile->getId());
                }
            }

        }

        $event = "Populated Applications from DataFile: populatedCount=" . $populatedCount;
        $logger->notice($event);

        return $populatedCount;
    }

    //3)  Delete successfully imported sheets and uploads from Google Drive if deleteImportedAplicationsFellApp is true
    //3a)   foreach "completed" sheet in DataFile:
    //3b)   delete sheet and uploads from Google drive
    //3c)   delete sheet object from DataFile
    //3d)   unlink sheet file from folder
    public function deleteSuccessfullyImportedApplications() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        $deleteImportedAplicationsFellApp = $userSecUtil->getSiteSettingParameter('deleteImportedAplicationsFellApp');
        if( !$deleteImportedAplicationsFellApp ) {
            $logger->warning("deleteImportedAplicationsFellApp parameter is nor defined or is set to false");
            return false;
        }

        //get completed DataFile
        $repository = $this->em->getRepository('AppFellAppBundle:DataFile');
        $dql =  $repository->createQueryBuilder("datafile");
        $dql->select('datafile');
        $dql->leftJoin("datafile.fellapp", "fellapp");
        $dql->where("datafile.status = :completeStatus AND fellapp.id IS NOT NULL");

        $query = $this->em->createQuery($dql);

        $query->setParameter("completeStatus","completed");

        $datafiles = $query->getResult();

        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();
        if( !$service ) {
            $event = "Google API service failed!";
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            return null;
        }

        $deletedSheetCount = 0;

        foreach( $datafiles as $datafile ) {

            //get Google Drive file id
            $document = $datafile->getDocument();

            $fellowshipApplication = $datafile->getFellapp();

            if( !$document ) {
                $logger->error("Document does not exists in DataFile object with ID=".$datafile->getId());
                continue;
            }

            $fileId = $document->getUniqueid();

            //delete all rows and associated files from Google Drive
            $deletedRows = $googlesheetmanagement->deleteAllRowsWithUploads($fileId);

            //delete file from Google Drive
            if( $deletedRows > 0 ) {
                $fileDeleted = $googlesheetmanagement->deleteFile($service, $fileId);

                if( !$fileDeleted ) {
                    $logger->error("Delete file from Google Drive failed! fileId=".$fileId);
                    continue;
                }
            }

            //remove (unlink) file from server
            $documentPath = $this->container->get('kernel')->getRootDir() . '/../web/' . $document->getUploadDirectory().'/'.$document->getUniquename();
            if( is_file($documentPath) ) {

                unlink($documentPath);
                $logger->notice("File deleted from server: path=".$documentPath);

                //delete datafile
                $datafileId = $datafile->getId();
                $this->em->remove($datafile);
                $this->em->flush($datafile);
                $logger->notice("DataFile object deleted from DB: datafileId=".$datafileId);

                //delete document from Document DB
                $documentId = $document->getId();
                $this->em->remove($document);
                $this->em->flush($document);
                $logger->notice("File deleted from DB and server: documentId=".$documentId);

                $deletedSheetCount++;
            } else {
                $logger->error("File does not exist on server! path=".$documentPath);
            }

        } //foreach datafile

        if( $deletedSheetCount > 0 ) {
            //eventlog
            $systemUser = $userSecUtil->findSystemUser();
            $event = "Successfully deleted imported sheets and uploads from Google Drive and from server: deletedSheetCount=".$deletedSheetCount;
            $eventTypeStr = "Deleted Fellowship Application From Google Drive";
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$fellowshipApplication,null,$eventTypeStr);
        }

        return $deletedSheetCount;
    }

    //4)  Process backup sheet on Google Drive
    public function processBackupFellAppFromGoogleDrive() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        $backupFileIdFellApp = $userSecUtil->getSiteSettingParameter('backupFileIdFellApp');
        if( !$backupFileIdFellApp ) {
            $logger->error("Import is not proceed because the backupFileIdFellApp parameter is not set.");
            return 0;
        }

        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();
        if( !$service ) {
            $event = "Google API service failed!";
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            return 0;
        }

        //1) get backup file on GoogleDrive
        $backupFile = $service->files->get($backupFileIdFellApp);
        $modifiedDate = $backupFile->getModifiedDate(); //datetime

        $intervalDays = 0;

        //get interval
        if( $modifiedDate ) {
            //echo "modifiedDate=".$modifiedDate."<br>";
            //$logger->notice("modifiedDate=".$modifiedDate);

            $datetimeNow = new \DateTime();
            //$datetimeNow->modify('+9 day'); //testing
            $datetimeModified = new \DateTime($modifiedDate);
            $intervalDays = $datetimeNow->diff($datetimeModified)->days;
        }

        //echo "intervalDays=".$intervalDays."<br>";
        //don't process backup file if interval is more than 1 day (process if interval is less then 1 day - recently modified backup)
        if( $intervalDays > 1 ) {
            //exit('dont process backup');
            $logger->notice("Do not process backup: $modifiedDate=[$modifiedDate]; intervalDays=[$intervalDays]");
            return 0;
        }
        //exit('process backup');
        $logger->notice("Process backup file modified on ".$modifiedDate);

        //download backup file to server and link it to Document DB
        $backupDb = $this->processSingleFile($backupFileIdFellApp, $service, 'Fellowship Application Backup Spreadsheet');

        $populatedBackupApplications = $this->populateSingleFellApp($backupDb, true);

        return count($populatedBackupApplications);
    }



    /**
     * Download files belonging to a folder. $folderId='0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E'
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param String $folderId ID of the folder to print files from.
     */
    public function processFilesInFolder( $folderId, $service, $documentType="Fellowship Application Spreadsheet" ) {

        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $files = $googlesheetmanagement->retrieveFilesByFolderId($folderId,$service);
        //echo "files count=".count($files)."<br>";

        foreach( $files as $file ) {
            //echo 'File Id: ' . $file->getId() . "<br>";
            $this->processSingleFile( $file->getId(), $service, $documentType );
        }

        return $files; //google drive files
    }


    //Download file from Google Drive to server and link it to a new Document DB
    public function processSingleFile( $fileId, $service, $documentType ) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $systemUser = $userSecUtil->findSystemUser();

        //$path = $this->uploadDir.'/Spreadsheets';
        $spreadsheetsPathFellApp = $userSecUtil->getSiteSettingParameter('spreadsheetsPathFellApp');
        if( !$spreadsheetsPathFellApp ) {
            $spreadsheetsPathFellApp = 'Spreadsheets';
            $logger->warning('spreadsheetsPathFellApp is not defined in Site Parameters; spreadsheetsPathFellApp='.$spreadsheetsPathFellApp);
        }
        $path = $this->uploadDir.'/'.$spreadsheetsPathFellApp;

        //download file
        $fileDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $fileId, $documentType, $path);

        $dataFile = null;

        if( $fileDb ) {
            $this->em->flush($fileDb);
            if( $documentType != "Fellowship Application Backup Spreadsheet" ) {
                $dataFile = $this->addFileToDataFileDB($fileDb);
            }
            if( $dataFile ) {
                $event = $documentType . " file has been successful downloaded to the server with id=" . $fileDb->getId() . ", title=" . $fileDb->getUniquename();
                $logger->notice($event);
            } else {
                //$logger->warning($documentType." dataFile has not been added (already exists) for fileId=$fileId; fileDb Id=".$fileDb->getId(). ", title=" . $fileDb->getUniquename());
            }
        } else {
            $event = $documentType . " download failed!";
            $logger->warning($event);
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Error');
            $this->sendEmailToSystemEmail($event, $event);
        }

        if( $dataFile ) {
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $event, $systemUser, null, null, 'Import of ' . $documentType);
        }

        return $fileDb;
    }

    //return newly created DataFile object
    public function addFileToDataFileDB( $document ) {

        $logger = $this->container->get('logger');

        $dataFile = $this->em->getRepository('AppFellAppBundle:DataFile')->findOneByDocument($document->getId());
        if( $dataFile ) {
            //$event = "DataFile already exists with document ID=".$document->getId();
            //$logger->notice($event);
            return null;
        }

        //create new
        $dataFile = new DataFile($document);
        $this->em->persist($dataFile);
        $this->em->flush($dataFile);

        return $dataFile;
    }



    public function checkIfFellappAllowed( $action="Action" ) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        $allowPopulateFellApp = $userSecUtil->getSiteSettingParameter('AllowPopulateFellApp');
        if( !$allowPopulateFellApp ) {
            $logger->warning($action." is not proceed because the AllowPopulateFellApp parameter is set to false.");
            return false;
        }

        $maintenance = $userSecUtil->getSiteSettingParameter('maintenance');
        if( $maintenance ) {
            $logger->warning($action." is not proceed because the server is on the  maintenance.");
            return false;
        }

        return true;
    }



    //2) populate a single fellowship application from spreadsheet to DB (using uploaded files from Google Drive)
    public function populateSingleFellApp( $document, $deleteSourceRow=false ) {

        $logger = $this->container->get('logger');
        //$userSecUtil = $this->container->get('user_security_utility');

        if( !$this->checkIfFellappAllowed("Populate Single Application") ) {
            $logger->warning("populate Single FellApp: Not Allowed to populate Single Application");
            return null;
        }

        //echo "fellapp populate Spreadsheet <br>";

        if( !$document ) {
            $logger->error("Document is not provided.");
            return null;
        }

        //2a) get spreadsheet path
//        $inputFileName = $document->getServerPath();    //'Uploaded/fellapp/Spreadsheets/Pathology Fellowships Application Form (Responses).xlsx';
//        $logger->notice("Population a single application sheet with filename=".$inputFileName);
//        if( $path ) {
//            $inputFileName = $path . "/" . $inputFileName;
//        }
        //2b) populate applicants
        $populatedFellowshipApplications = $this->populateSpreadsheet($document,$deleteSourceRow);

//        if( $populatedCount && $populatedCount > 0 ) {
//            //set applicantData from 'active' to 'populated'
//        } else {
//            //set applicantData from active to 'failed'
//        }
//        $userSecUtil = $this->container->get('user_security_utility');
//        $systemUser = $userSecUtil->findSystemUser();
//        foreach( $populatedFellowshipApplications as $fellowshipApplication ) {
//            $event = "Populated Fellowship Application for ".$fellowshipApplication->getUser()." (Application ID ".$fellowshipApplication->getId().") from Spreadsheets to DB.";
//            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$fellowshipApplication,null,'Import of Fellowship Application data to DB');
//        }

        //call tryRun() asynchronous
        $cmd = 'php ../bin/console fellapp:generatereportrun --env=prod';
        //$fellappRepGen = $this->container->get('fellapp_reportgenerator');
        //$fellappRepGen->cmdRunAsync($cmd);
        $userServiceUtil = $this->container->get('user_service_utility');
        $userServiceUtil->execInBackground($cmd);

        return $populatedFellowshipApplications;
    }


    /////////////// populate methods /////////////////
    public function populateSpreadsheet( $document, $deleteSourceRow=false ) {

        //echo "inputFileName=".$inputFileName."<br>";
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');

        ini_set('max_execution_time', 3000); //30000 seconds = 50 minutes
        //ini_set('memory_limit', '512M');

        $service = $googlesheetmanagement->getGoogleService();
        if( !$service ) {
            $event = "Google API service failed!";
            $logger->error($event);
            $this->sendEmailToSystemEmail($event, $event);
            return false;
        }

        $inputFileName = $document->getServerPath();    //'Uploaded/fellapp/Spreadsheets/Pathology Fellowships Application Form (Responses).xlsx';
        $logger->notice("Population a single application sheet with filename=".$inputFileName);

        //if ruuning from cron path must be: $path = getcwd() . "/web";
        //$inputFileName = $path . "/" . $inputFileName;
        $inputFileName = realpath($this->container->get('kernel')->getRootDir() . "/../web/" . $inputFileName);
        if( !file_exists($inputFileName) ) {
            $logger->error("Source sheet does not exists with filename=".$inputFileName);
            return false;
        }

        //$logger->notice("Getting source sheet with filename=".$inputFileName);

        try {
            //$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
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

        //$logger->notice("Successfully obtained sheet with filename=".$inputFileName);

        //$uploadPath = $this->uploadDir.'/FellowshipApplicantUploads';
        $applicantsUploadPathFellApp = $userSecUtil->getSiteSettingParameter('applicantsUploadPathFellApp');
        if( !$applicantsUploadPathFellApp ) {
            $applicantsUploadPathFellApp = "FellowshipApplicantUploads";
            $logger->warning('applicantsUploadPathFellApp is not defined in Site Parameters. Use default "'.$applicantsUploadPathFellApp.'" folder.');
        }
        $uploadPath = $this->uploadDir.'/'.$applicantsUploadPathFellApp;

        //$logger->notice("Destination upload path=".$uploadPath);
        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        //var_dump($sheetData);

        $em = $this->em;
        $default_time_zone = $this->container->getParameter('default_time_zone');
        $emailUtil = $this->container->get('user_mailer_utility');

        $userkeytype = $userSecUtil->getUsernameType('local-user');
        if( !$userkeytype ) {
            throw new EntityNotFoundException('Unable to find local user keytype');
        }

        $employmentType = $em->getRepository('AppUserdirectoryBundle:EmploymentType')->findOneByName("Pathology Fellowship Applicant");
        if( !$employmentType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Pathology Fellowship Applicant");
        }
        $presentLocationType = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Present Address");
        if( !$presentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Present Address");
        }
        $permanentLocationType = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Permanent Address");
        if( !$permanentLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Permanent Address");
        }
        $workLocationType = $em->getRepository('AppUserdirectoryBundle:LocationTypeList')->findOneByName("Work Address");
        if( !$workLocationType ) {
            throw new EntityNotFoundException('Unable to find entity by name='."Work Address");
        }

        $activeStatus = $em->getRepository('AppFellAppBundle:FellAppStatus')->findOneByName("active");
        if( !$activeStatus ) {
            throw new EntityNotFoundException('Unable to find entity by name='."active");
        }


        ////////////// add system user /////////////////
        $systemUser = $userSecUtil->findSystemUser();
        ////////////// end of add system user /////////////////

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

        $populatedFellowshipApplications = new ArrayCollection();

        //for each user in excel
        for( $row = 3; $row <= $highestRow; $row++ ){

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            //$logger->notice(print_r($rowData[0]));
            //print_r($rowData[0]);
            //echo "<pre>";
            //print_r($headers[0]);
            //echo "</pre>";

            //echo "<pre>";
            //print_r($rowData[0]);
            //echo "</pre>";
            //exit('exit');

            //$googleFormId = $rowData[0][0];
            $googleFormId = $this->getValueByHeaderName('ID',$rowData,$headers);
            $email = $this->getValueByHeaderName('email', $rowData, $headers);
            $lastName = $this->getValueByHeaderName('lastName', $rowData, $headers);
            $firstName = $this->getValueByHeaderName('firstName', $rowData, $headers);

            if( !$googleFormId ) {
                //echo $row.": skip ID is null <br>";
                //$logger->warning($row.': Skip this fell application, because googleFormId does not exists. rowData='.$rowData.'; headers='.implode(";",$headers[0]));
                $logger->warning('Skip this fell application, because googleFormId does not exists');
                $logger->warning(implode("; ", $rowData[0]));
                continue; //skip this fell application, because googleFormId does not exists
            }

            //ID=".$googleFormId
            //subject for error email
            //Failed to import a received fellowship application - will automatically attempt to re-import in X hours
            $subjectError = "Failed to import a received fellowship application - will automatically attempt to re-import (ID=$googleFormId)";

            ////////////////// validate spreadsheet /////////////////////////
            $errorMsgArr = array();
            $fellowshipType = $this->getValueByHeaderName('fellowshipType', $rowData, $headers);
            if( !$fellowshipType ) {
                $errorMsgArr[] = "Fellowship Type is null";
            }
            $ref1 = $this->createFellAppReference($em,$systemUser,'recommendation1',$rowData,$headers,true);
            if( !$ref1 ) {
                $errorMsgArr[] = "Reference1 is null";
            }
            $ref2 = $this->createFellAppReference($em,$systemUser,'recommendation2',$rowData,$headers,true);
            if( !$ref2 ) {
                $errorMsgArr[] = "Reference2 is null";
            }
            $ref3 = $this->createFellAppReference($em,$systemUser,'recommendation3',$rowData,$headers,true);
            if( !$ref3 ) {
                $errorMsgArr[] = "Reference3 is null";
            }

            if( !$lastName ) {
                $errorMsgArr[] = "Applicant last name is null";
            }
            if( !$firstName ) {
                $errorMsgArr[] = "Applicant first name is null";
            }

            if( !$email ) {
                $errorMsgArr[] = "Applicant email is null";
            }

            $signatureName = $this->getValueByHeaderName('signatureName',$rowData,$headers);
            if( !$signatureName ) {
                $errorMsgArr[] = "Signature is null";
            }
            $signatureDate = $this->getValueByHeaderName('signatureDate',$rowData,$headers);
            if( !$signatureDate ) {
                $errorMsgArr[] = "Signature Date is null";
            }
            $trainingPeriodStart = $this->getValueByHeaderName('trainingPeriodStart',$rowData,$headers);
            if( !$trainingPeriodStart ) {
                $errorMsgArr[] = "Start Date is null";
            }
            $trainingPeriodEnd = $this->getValueByHeaderName('trainingPeriodEnd',$rowData,$headers);
            if( !$trainingPeriodEnd ) {
                $errorMsgArr[] = "End Date is null";
            }

            //getFellowshipSubspecialty
            //if( !$fellowshipApplication->getFellowshipSubspecialty() ) { //getSignatureName() - not reliable - some applicants managed to submit the form without signature
            if( count($errorMsgArr) > 0 ) {

                //delete erroneous spreadsheet from filesystem and $document from DB
                if( file_exists($inputFileName) ) {
                    //$logger->error("Source sheet does not exists with filename=".$inputFileName);
                    //remove from DB
                    $em->remove($document);
                    $em->flush();
                    //delete file
                    unlink($inputFileName); // or die("Couldn't delete erroneous spreadsheet inputFileName=[".$inputFileName."]");
                    $logger->error("Erroneous spreadsheet deleted from server: $inputFileName=".$inputFileName);
                }

                $event = "First spreadsheet validation error:".
                    " Empty required fields after trying to populate the Fellowship Application with Google Applicant ID=[" . $googleFormId . "]" .
                    ": " . implode("; ",$errorMsgArr);

                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Fellowship Application Creation Failed');
                $logger->error($event);

                //send email
                $sendErrorEmail = true;
                //$sendErrorEmail = false;
                if( $sendErrorEmail ) {
                    $userSecUtil = $this->container->get('user_security_utility');
                    $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
                    $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
                    if (!$emails) {
                        $emails = $ccs;
                        $ccs = null;
                    }
                    $emailUtil->sendEmail($emails, $subjectError, $event, $ccs);
                    $this->sendEmailToSystemEmail($subjectError, $event);
                }

                continue; //skip this fell application, because getFellowshipSubspecialty is null => something is wrong
            }
            ////////////////// EOF validate spreadsheet ////////////////////////


            //exit('exit');

            try {

                //            //reopen em after DBALException
                //            if( !$em->isOpen() ) {
                //                echo 'em is closed; ID=' . $googleFormId."<br>";
                //                $em = $em->create(
                //                $em->getConnection(), $em->getConfiguration());
                //                $this->em = $em;
                //                // reset the EM and all aias
                ////                $container = $this->container;
                ////                $container->set('doctrine.orm.entity_manager', null);
                ////                $container->set('doctrine.orm.default_entity_manager', null);
                ////                // get a fresh EM
                ////                $em = $this->container->getDoctrine()->getManager();
                ////                $this->em = $em;
                //            }


                //            if( !$em->isOpen() ) {
                //                exit('em is still closed; ID=' . $googleFormId);
                //            }

                //echo "row=".$row.": id=".$googleFormId."<br>";

                $fellowshipApplicationDb = $em->getRepository('AppFellAppBundle:FellowshipApplication')->findOneByGoogleFormId($googleFormId);
                if( $fellowshipApplicationDb ) {
                    //$logger->notice('Skip this fell application, because it already exists in DB. googleFormId='.$googleFormId);
                    continue; //skip this fell application, because it already exists in DB
                }

                //$email = $this->getValueByHeaderName('email', $rowData, $headers);
                //$lastName = $this->getValueByHeaderName('lastName', $rowData, $headers);
                //$firstName = $this->getValueByHeaderName('firstName', $rowData, $headers);
                $middleName = $this->getValueByHeaderName('middleName', $rowData, $headers);

//                $logger->notice('Start populating fell application (googleFormId=['.$googleFormId.']'.' with email='.$email.', firstName='.$firstName.', lastname='.$lastName);
//                if( !$email ) {
//                    $logger->warning("Error populating fellapp googleFormId=$googleFormId: email is null");
//                    $logger->warning(implode("; ", $rowData[0]));
//                }

                $lastNameCap = $this->capitalizeIfNotAllCapital($lastName);
                $firstNameCap = $this->capitalizeIfNotAllCapital($firstName);
                //$middleNameCap = $this->capitalizeIfNotAllCapital($middleName);

                $lastNameCap = preg_replace('/\s+/', '_', $lastNameCap);
                $firstNameCap = preg_replace('/\s+/', '_', $firstNameCap);

                //Last Name + First Name + Email
                $username = $lastNameCap . "_" . $firstNameCap . "_" . $email;

                $displayName = $firstName . " " . $lastName;
                if ($middleName) {
                    $displayName = $firstName . " " . $middleName . " " . $lastName;
                }

                //testing !!! TODO: remove it!!!
                //echo "email=$email, googleFormId=$googleFormId <br>";
                //exit('111');
                //continue;

                //create logger which must be deleted on successefull creation of application
                $eventAttempt = "Attempt of creating Fellowship Applicant " . $displayName . " with unique Google Applicant ID=" . $googleFormId;
                $eventLogAttempt = $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $eventAttempt, $systemUser, null, null, 'Fellowship Application Creation Failed');


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


                    $user->setEmail($email);
                    $user->setEmailCanonical($email);

                    $user->setFirstName($firstName);
                    $user->setLastName($lastName);
                    $user->setMiddleName($middleName);
                    $user->setDisplayName($displayName);
                    $user->setPassword("");
                    $user->setCreatedby('googleapi');
                    $user->getPreferences()->setTimezone($default_time_zone);
                    $user->setLocked(true);

                    //Pathology Fellowship Applicant in EmploymentStatus
                    $employmentStatus = new EmploymentStatus($systemUser);
                    $employmentStatus->setEmploymentType($employmentType);
                    $user->addEmploymentStatus($employmentStatus);
                }

                //create new Fellowship Applicantion
                $fellowshipApplication = new FellowshipApplication($systemUser);
                //if( !$fellowshipApplication ) {
                //    $fellowshipApplication = new FellowshipApplication($systemUser);
                //}

                $fellowshipApplication->setAppStatus($activeStatus);
                $fellowshipApplication->setGoogleFormId($googleFormId);

                $user->addFellowshipApplication($fellowshipApplication);
                //if( $fellowshipApplication && !$user->getFellowshipApplications()->contains($fellowshipApplication) ) {
                //    $user->addFellowshipApplication($fellowshipApplication);
                //}

                //timestamp
                $fellowshipApplication->setTimestamp($this->transformDatestrToDate($this->getValueByHeaderName('timestamp', $rowData, $headers)));

                //fellowshipType
                $fellowshipType = $this->getValueByHeaderName('fellowshipType', $rowData, $headers);
                if ($fellowshipType) {
                    //$logger->notice("fellowshipType=[".$fellowshipType."]");
                    $fellowshipType = trim($fellowshipType);
                    $fellowshipType = $this->capitalizeIfNotAllCapital($fellowshipType);
                    $transformer = new GenericTreeTransformer($em, $systemUser, 'FellowshipSubspecialty');
                    $fellowshipTypeEntity = $transformer->reverseTransform($fellowshipType);
                    $fellowshipApplication->setFellowshipSubspecialty($fellowshipTypeEntity);
                }

                //////////////////////// assign local institution from SiteParameters ////////////////////////
                $instPathologyFellowshipProgram = null;
                $localInstitutionFellApp = $userSecUtil->getSiteSettingParameter('localInstitutionFellApp');

                if( strpos($localInstitutionFellApp, " (") !== false ) {
                    //Case 1: get string from SiteParameters - "Pathology Fellowship Programs (WCMC)"
                    $localInstitutionFellAppArr = explode(" (", $localInstitutionFellApp);
                    if (count($localInstitutionFellAppArr) == 2 && $localInstitutionFellAppArr[0] != "" && $localInstitutionFellAppArr[1] != "") {
                        $localInst = trim($localInstitutionFellAppArr[0]); //"Pathology Fellowship Programs"
                        $rootInst = trim($localInstitutionFellAppArr[1]);  //"(WCMC)"
                        $rootInst = str_replace("(", "", $rootInst);
                        $rootInst = str_replace(")", "", $rootInst);
                        //$logger->warning('rootInst='.$rootInst.'; localInst='.$localInst);
                        $wcmc = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation($rootInst);
                        if( !$wcmc ) {
                            $wcmc = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName($rootInst);
                            if( !$wcmc ) {
                                throw new EntityNotFoundException('Unable to find Institution by name=' . $rootInst);
                            }
                        }
                        $instPathologyFellowshipProgram = $em->getRepository('AppUserdirectoryBundle:Institution')->findNodeByNameAndRoot($wcmc->getId(), $localInst);
                        if( !$instPathologyFellowshipProgram ) {
                            throw new EntityNotFoundException('Unable to find Institution by name=' . $localInst);
                        }
                    }
                } else {
                    //Case 2: get string from SiteParameters - "WCM" or "Weill Cornell Medical College"
                    $instPathologyFellowshipProgram = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByAbbreviation($localInstitutionFellApp);
                    if( !$instPathologyFellowshipProgram ) {
                        $instPathologyFellowshipProgram = $em->getRepository('AppUserdirectoryBundle:Institution')->findOneByName($localInstitutionFellApp);
                    }
                }

                if( $instPathologyFellowshipProgram ) {
                    $fellowshipApplication->setInstitution($instPathologyFellowshipProgram);
                } else {
                    $logger->warning('Local Institution for Import Application is not set or invalid; localInstitutionFellApp='.$localInstitutionFellApp);
                }
                //////////////////////// EOF assign local institution from SiteParameters ////////////////////////


                //trainingPeriodStart
                $fellowshipApplication->setStartDate($this->transformDatestrToDate($this->getValueByHeaderName('trainingPeriodStart',$rowData,$headers)));

                //trainingPeriodEnd
                $fellowshipApplication->setEndDate($this->transformDatestrToDate($this->getValueByHeaderName('trainingPeriodEnd',$rowData,$headers)));

                //uploadedPhotoUrl
                $uploadedPhotoUrl = $this->getValueByHeaderName('uploadedPhotoUrl',$rowData,$headers);
                $uploadedPhotoId = $this->getFileIdByUrl( $uploadedPhotoUrl );
                if( $uploadedPhotoId ) {
                    $uploadedPhotoDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedPhotoId, 'Fellowship Photo', $uploadPath);
                    if( !$uploadedPhotoDb ) {
                        throw new IOException('Unable to download file to server: uploadedPhotoUrl='.$uploadedPhotoUrl.', fileDB='.$uploadedPhotoDb);
                    }
                    //$user->setAvatar($uploadedPhotoDb); //set this file as Avatar
                    $fellowshipApplication->addAvatar($uploadedPhotoDb);
                }

                //uploadedCVUrl
                $uploadedCVUrl = $this->getValueByHeaderName('uploadedCVUrl',$rowData,$headers);
                $uploadedCVUrlId = $this->getFileIdByUrl( $uploadedCVUrl );
                if( $uploadedCVUrlId ) {
                    $uploadedCVUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedCVUrlId, 'Fellowship CV', $uploadPath);
                    if( !$uploadedCVUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedCVUrl='.$uploadedCVUrl.', fileDB='.$uploadedCVUrlDb);
                    }
                    $fellowshipApplication->addCv($uploadedCVUrlDb);
                }

                //uploadedCoverLetterUrl
                $uploadedCoverLetterUrl = $this->getValueByHeaderName('uploadedCoverLetterUrl',$rowData,$headers);
                $uploadedCoverLetterUrlId = $this->getFileIdByUrl( $uploadedCoverLetterUrl );
                if( $uploadedCoverLetterUrlId ) {
                    $uploadedCoverLetterUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedCoverLetterUrlId, 'Fellowship Cover Letter', $uploadPath);
                    if( !$uploadedCoverLetterUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedCoverLetterUrl='.$uploadedCoverLetterUrl.', fileDB='.$uploadedCoverLetterUrlDb);
                    }
                    $fellowshipApplication->addCoverLetter($uploadedCoverLetterUrlDb);
                }

                $examination = new Examination($systemUser);
                //$user->getCredentials()->addExamination($examination);
                $fellowshipApplication->addExamination($examination);
                //uploadedUSMLEScoresUrl
                $uploadedUSMLEScoresUrl = $this->getValueByHeaderName('uploadedUSMLEScoresUrl',$rowData,$headers);
                $uploadedUSMLEScoresUrlId = $this->getFileIdByUrl( $uploadedUSMLEScoresUrl );
                if( $uploadedUSMLEScoresUrlId ) {
                    $uploadedUSMLEScoresUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedUSMLEScoresUrlId, 'Fellowship USMLE Scores', $uploadPath);
                    if( !$uploadedUSMLEScoresUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedUSMLEScoresUrl='.$uploadedUSMLEScoresUrl.', fileDB='.$uploadedUSMLEScoresUrlDb);
                    }
                    $examination->addScore($uploadedUSMLEScoresUrlDb);
                }

                //presentAddress
                $presentLocation = new Location($systemUser);
                $presentLocation->setName('Fellowship Applicant Present Address');
                $presentLocation->addLocationType($presentLocationType);
                $geoLocation = $this->createGeoLocation($em,$systemUser,'presentAddress',$rowData,$headers);
                if( $geoLocation ) {
                    $presentLocation->setGeoLocation($geoLocation);
                }
                $user->addLocation($presentLocation);
                $fellowshipApplication->addLocation($presentLocation);

                //telephoneHome
                //telephoneMobile
                //telephoneFax
                $presentLocation->setPhone($this->getValueByHeaderName('telephoneHome',$rowData,$headers)."");
                $presentLocation->setMobile($this->getValueByHeaderName('telephoneMobile',$rowData,$headers)."");
                $presentLocation->setFax($this->getValueByHeaderName('telephoneFax',$rowData,$headers)."");

                //permanentAddress
                $permanentLocation = new Location($systemUser);
                $permanentLocation->setName('Fellowship Applicant Permanent Address');
                $permanentLocation->addLocationType($permanentLocationType);
                $geoLocation = $this->createGeoLocation($em,$systemUser,'permanentAddress',$rowData,$headers);
                if( $geoLocation ) {
                    $permanentLocation->setGeoLocation($geoLocation);
                }
                $user->addLocation($permanentLocation);
                $fellowshipApplication->addLocation($permanentLocation);

                //telephoneWork
                $telephoneWork = $this->getValueByHeaderName('telephoneWork',$rowData,$headers);
                if( $telephoneWork ) {
                    $workLocation = new Location($systemUser);
                    $workLocation->setName('Fellowship Applicant Work Address');
                    $workLocation->addLocationType($workLocationType);
                    $workLocation->setPhone($telephoneWork."");
                    $user->addLocation($workLocation);
                    $fellowshipApplication->addLocation($workLocation);
                }


                $citizenship = new Citizenship($systemUser);
                //$user->getCredentials()->addCitizenship($citizenship);
                $fellowshipApplication->addCitizenship($citizenship);
                //visaStatus
                $citizenship->setVisa($this->getValueByHeaderName('visaStatus',$rowData,$headers));
                //citizenshipCountry
                $citizenshipCountry = $this->getValueByHeaderName('citizenshipCountry',$rowData,$headers);
                if( $citizenshipCountry ) {
                    $citizenshipCountry = trim($citizenshipCountry);
                    $transformer = new GenericTreeTransformer($em, $systemUser, 'Countries');
                    $citizenshipCountryEntity = $transformer->reverseTransform($citizenshipCountry);
                    $citizenship->setCountry($citizenshipCountryEntity);
                }

                //DOB: oleg_userdirectorybundle_user_credentials_dob
                $dobDate = $this->transformDatestrToDate($this->getValueByHeaderName('dateOfBirth',$rowData,$headers));
                $fellowshipApplication->getUser()->getCredentials()->setDob($dobDate);

                //undergraduate
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"undergraduateSchool",$rowData,$headers,1);

                //graduate
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"graduateSchool",$rowData,$headers,2);

                //medical
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"medicalSchool",$rowData,$headers,3);

                //residency: residencyStart	residencyEnd	residencyName	residencyArea
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"residency",$rowData,$headers,4);

                //gme1: gme1Start, gme1End, gme1Name, gme1Area => Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"gme1",$rowData,$headers,5);

                //gme2: gme2Start, gme2End, gme2Name, gme2Area => Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"gme2",$rowData,$headers,6);

                //otherExperience1Start	otherExperience1End	otherExperience1Name=>Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"otherExperience1",$rowData,$headers,7);

                //otherExperience2Start	otherExperience2End	otherExperience2Name=>Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"otherExperience2",$rowData,$headers,8);

                //otherExperience3Start	otherExperience3End	otherExperience3Name=>Major
                $this->createFellAppTraining($em,$fellowshipApplication,$systemUser,"otherExperience3",$rowData,$headers,9);

                //USMLEStep1DatePassed	USMLEStep1Score
                $examination->setUSMLEStep1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep1DatePassed',$rowData,$headers)));
                $examination->setUSMLEStep1Score($this->getValueByHeaderName('USMLEStep1Score',$rowData,$headers));
                $examination->setUSMLEStep1Percentile($this->getValueByHeaderName('USMLEStep1Percentile',$rowData,$headers));

                //USMLEStep2CKDatePassed	USMLEStep2CKScore	USMLEStep2CSDatePassed	USMLEStep2CSScore
                $examination->setUSMLEStep2CKDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CKDatePassed',$rowData,$headers)));
                $examination->setUSMLEStep2CKScore($this->getValueByHeaderName('USMLEStep2CKScore',$rowData,$headers));
                $examination->setUSMLEStep2CKPercentile($this->getValueByHeaderName('USMLEStep2CKPercentile',$rowData,$headers));
                $examination->setUSMLEStep2CSDatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep2CSDatePassed',$rowData,$headers)));
                $examination->setUSMLEStep2CSScore($this->getValueByHeaderName('USMLEStep2CSScore',$rowData,$headers));
                $examination->setUSMLEStep2CSPercentile($this->getValueByHeaderName('USMLEStep2CSPercentile',$rowData,$headers));

                //USMLEStep3DatePassed	USMLEStep3Score
                $examination->setUSMLEStep3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('USMLEStep3DatePassed',$rowData,$headers)));
                $examination->setUSMLEStep3Score($this->getValueByHeaderName('USMLEStep3Score',$rowData,$headers));
                $examination->setUSMLEStep3Percentile($this->getValueByHeaderName('USMLEStep3Percentile',$rowData,$headers));

                //ECFMGCertificate
                $ECFMGCertificateStr = $this->getValueByHeaderName('ECFMGCertificate',$rowData,$headers);
                $ECFMGCertificate = false;
                if( $ECFMGCertificateStr == 'Yes' ) {
                    $ECFMGCertificate = true;
                }
                $examination->setECFMGCertificate($ECFMGCertificate);

                //ECFMGCertificateNumber	ECFMGCertificateDate
                $examination->setECFMGCertificateNumber($this->getValueByHeaderName('ECFMGCertificateNumber',$rowData,$headers));
                $examination->setECFMGCertificateDate($this->transformDatestrToDate($this->getValueByHeaderName('ECFMGCertificateDate',$rowData,$headers)));

                //COMLEXLevel1DatePassed	COMLEXLevel1Score	COMLEXLevel2DatePassed	COMLEXLevel2Score	COMLEXLevel3DatePassed	COMLEXLevel3Score
                $examination->setCOMLEXLevel1Score($this->getValueByHeaderName('COMLEXLevel1Score',$rowData,$headers));
                $examination->setCOMLEXLevel1Percentile($this->getValueByHeaderName('COMLEXLevel1Percentile',$rowData,$headers));
                $examination->setCOMLEXLevel1DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel1DatePassed',$rowData,$headers)));
                $examination->setCOMLEXLevel2Score($this->getValueByHeaderName('COMLEXLevel2Score',$rowData,$headers));
                $examination->setCOMLEXLevel2Percentile($this->getValueByHeaderName('COMLEXLevel2Percentile',$rowData,$headers));
                $examination->setCOMLEXLevel2DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel2DatePassed',$rowData,$headers)));
                $examination->setCOMLEXLevel3Score($this->getValueByHeaderName('COMLEXLevel3Score',$rowData,$headers));
                $examination->setCOMLEXLevel3Percentile($this->getValueByHeaderName('COMLEXLevel3Percentile',$rowData,$headers));
                $examination->setCOMLEXLevel3DatePassed($this->transformDatestrToDate($this->getValueByHeaderName('COMLEXLevel3DatePassed',$rowData,$headers)));

                //medicalLicensure1Country	medicalLicensure1State	medicalLicensure1DateIssued	medicalLicensure1Number	medicalLicensure1Active
                $this->createFellAppMedicalLicense($em,$fellowshipApplication,$systemUser,"medicalLicensure1",$rowData,$headers);

                //medicalLicensure2
                $this->createFellAppMedicalLicense($em,$fellowshipApplication,$systemUser,"medicalLicensure2",$rowData,$headers);

                //suspendedLicensure
                $fellowshipApplication->setReprimand($this->getValueByHeaderName('suspendedLicensure',$rowData,$headers));
                //uploadedReprimandExplanationUrl
                $uploadedReprimandExplanationUrl = $this->getValueByHeaderName('uploadedReprimandExplanationUrl',$rowData,$headers);
                $uploadedReprimandExplanationUrlId = $this->getFileIdByUrl( $uploadedReprimandExplanationUrl );
                if( $uploadedReprimandExplanationUrlId ) {
                    $uploadedReprimandExplanationUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedReprimandExplanationUrlId, 'Fellowship Reprimand', $uploadPath);
                    if( !$uploadedReprimandExplanationUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedReprimandExplanationUrl='.$uploadedReprimandExplanationUrl.', fileID='.$uploadedReprimandExplanationUrlDb->getId());
                    }
                    $fellowshipApplication->addReprimandDocument($uploadedReprimandExplanationUrlDb);
                }

                //legalSuit
                $fellowshipApplication->setLawsuit($this->getValueByHeaderName('legalSuit',$rowData,$headers));
                //uploadedLegalExplanationUrl
                $uploadedLegalExplanationUrl = $this->getValueByHeaderName('uploadedLegalExplanationUrl',$rowData,$headers);
                $uploadedLegalExplanationUrlId = $this->getFileIdByUrl( $uploadedLegalExplanationUrl );
                if( $uploadedLegalExplanationUrlId ) {
                    $uploadedLegalExplanationUrlDb = $googlesheetmanagement->downloadFileToServer($systemUser, $service, $uploadedLegalExplanationUrlId, 'Fellowship Legal Suit', $uploadPath);
                    if( !$uploadedLegalExplanationUrlDb ) {
                        throw new IOException('Unable to download file to server: uploadedLegalExplanationUrl='.$uploadedLegalExplanationUrl.', fileID='.$uploadedLegalExplanationUrlDb->getId());
                    }
                    $fellowshipApplication->addReprimandDocument($uploadedLegalExplanationUrlDb);
                }

                //boardCertification1Board	boardCertification1Area	boardCertification1Date
                $this->createFellAppBoardCertification($em,$fellowshipApplication,$systemUser,"boardCertification1",$rowData,$headers);
                //boardCertification2
                $this->createFellAppBoardCertification($em,$fellowshipApplication,$systemUser,"boardCertification2",$rowData,$headers);
                //boardCertification3
                $this->createFellAppBoardCertification($em,$fellowshipApplication,$systemUser,"boardCertification3",$rowData,$headers);

                //recommendation1Name	recommendation1Title	recommendation1Institution	recommendation1AddressStreet1	recommendation1AddressStreet2	recommendation1AddressCity	recommendation1AddressState	recommendation1AddressZip	recommendation1AddressCountry
                $ref1 = $this->createFellAppReference($em,$systemUser,'recommendation1',$rowData,$headers);
                if( $ref1 ) {
                    $fellowshipApplication->addReference($ref1);
                }
                $ref2 = $this->createFellAppReference($em,$systemUser,'recommendation2',$rowData,$headers);
                if( $ref2 ) {
                    $fellowshipApplication->addReference($ref2);
                }
                $ref3 = $this->createFellAppReference($em,$systemUser,'recommendation3',$rowData,$headers);
                if( $ref3 ) {
                    $fellowshipApplication->addReference($ref3);
                }
                $ref4 = $this->createFellAppReference($em,$systemUser,'recommendation4',$rowData,$headers);
                if( $ref4 ) {
                    $fellowshipApplication->addReference($ref4);
                }

                //honors
                $fellowshipApplication->setHonors($this->getValueByHeaderName('honors',$rowData,$headers));
                //publications
                $fellowshipApplication->setPublications($this->getValueByHeaderName('publications',$rowData,$headers));
                //memberships
                $fellowshipApplication->setMemberships($this->getValueByHeaderName('memberships',$rowData,$headers));

                //signatureName
                $fellowshipApplication->setSignatureName($this->getValueByHeaderName('signatureName',$rowData,$headers));
                //signatureDate
                $signatureDate = $this->transformDatestrToDate($this->getValueByHeaderName('signatureDate',$rowData,$headers));
                $fellowshipApplication->setSignatureDate($signatureDate);

                //////////////////// second validate the application //////////////////////
                $errorMsgArr = array();
                if( !$fellowshipApplication->getFellowshipSubspecialty() ) {
                    $errorMsgArr[] = "Fellowship Type is null";
                }
                if( count($fellowshipApplication->getReferences()) == 0 ) {
                    $errorMsgArr[] = "References are null";
                }
                if( !$displayName ) {
                    $errorMsgArr[] = "Applicant name is null";
                }
                if( !$fellowshipApplication->getSignatureName() ) {
                    $errorMsgArr[] = "Signature is null";
                }
                if( !$fellowshipApplication->getSignatureDate() ) {
                    $errorMsgArr[] = "Signature Date is null";
                }
                if( !$fellowshipApplication->getStartDate() ) {
                    $errorMsgArr[] = "Start Date is null";
                }
                if( !$fellowshipApplication->getEndDate() ) {
                    $errorMsgArr[] = "End Date is null";
                }

                //This condition (count($errorMsgArr) > 0) should never happen theoretically, because the first validation should catch the erroneous spreadsheet
                //if( !$fellowshipApplication->getFellowshipSubspecialty() ) { //getSignatureName() - not reliable - some applicants managed to submit the form without signature
                if( count($errorMsgArr) > 0 ) {

                    //delete erroneous spreadsheet from filesystem and $document from DB
                    if( 0 && file_exists($inputFileName) ) {
                        //$logger->error("Source sheet does not exists with filename=".$inputFileName);
                        //remove from DB
                        $em->remove($document);
                        $em->flush($document);
                        //delete file
                        unlink($inputFileName); // or die("Couldn't delete erroneous spreadsheet inputFileName=[".$inputFileName."]");
                        $logger->error("Erroneous spreadsheet deleted from server: $inputFileName=".$inputFileName);
                    }

                    $event = "Second spreadsheet validation error:".
                        " (Applicant=[" . $displayName . "], Application ID=[" . $fellowshipApplication->getId() . "])" .
                        " Empty required fields after trying to populate the Fellowship Application with Google Applicant ID=[" . $googleFormId . "]" .
                        ": " . implode("; ",$errorMsgArr);

                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,null,null,'Fellowship Application Creation Failed');
                    $logger->error($event);

                    //send email
                    //$sendErrorEmail = true;
                    $sendErrorEmail = false;
                    if( $sendErrorEmail ) {
                        $userSecUtil = $this->container->get('user_security_utility');
                        $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Administrator");
                        $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'), "Platform Administrator");
                        if (!$emails) {
                            $emails = $ccs;
                            $ccs = null;
                        }
                        $emailUtil->sendEmail($emails, $subjectError, $event, $ccs);
                        $this->sendEmailToSystemEmail($subjectError, $event);
                    }

                    continue; //skip this fell application, because getFellowshipSubspecialty is null => something is wrong
                }
                //////////////////// EOF second validate the application //////////////////////

                //exit('end applicant');

                $em->persist($user);
                $em->flush();

                //everything looks fine => remove creation attempt log
                $em->remove($eventLogAttempt);
                $em->flush();

                $event = "Populated fellowship applicant " . $displayName . "; Application ID " . $fellowshipApplication->getId();
                $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$fellowshipApplication,null,'Fellowship Application Created');

                //add application pdf generation to queue
                $fellappRepGen = $this->container->get('fellapp_reportgenerator');
                $fellappRepGen->addFellAppReportToQueue( $fellowshipApplication->getId() );

                $logger->notice($event);

                //send confirmation email to this applicant for prod server
                $environment = $userSecUtil->getSiteSettingParameter('environment');
                if( $environment == "live" ) {
                    //send confirmation email to this applicant
                    $confirmationEmailFellApp = $userSecUtil->getSiteSettingParameter('confirmationEmailFellApp');
                    $confirmationSubjectFellApp = $userSecUtil->getSiteSettingParameter('confirmationSubjectFellApp');
                    $confirmationBodyFellApp = $userSecUtil->getSiteSettingParameter('confirmationBodyFellApp');
                    //$logger->notice("Before Send confirmation email to " . $email . " from " . $confirmationEmailFellApp);
                    if ($email && $confirmationEmailFellApp && $confirmationSubjectFellApp && $confirmationBodyFellApp) {
                        $logger->notice("Send confirmation email (fellowship application " . $fellowshipApplication->getId() . " populated in DB) to the applicant email " . $email . " from " . $confirmationEmailFellApp);
                        $emailUtil->sendEmail($email, $confirmationSubjectFellApp, $confirmationBodyFellApp, null, $confirmationEmailFellApp);
                    } else {
                        $logger->error("ERROR: confirmation email has not been sent (fellowship application " . $fellowshipApplication->getId() . " populated in DB) to the applicant email " . $email . " from " . $confirmationEmailFellApp);

                    }
                    
                }//if live

                if( $environment == "live" ) {
                    //send confirmation email to the corresponding Fellowship director and coordinator
                    $fellappUtil = $this->container->get('fellapp_util');
                    $fellappUtil->sendConfirmationEmailsOnApplicationPopulation( $fellowshipApplication, $user );
                }

                //create reference hash ID. Must run after fellowship is in DB and has IDs
                $fellappRecLetterUtil->generateFellappRecLetterId($fellowshipApplication,true);
                if( $environment == "live" ) {
                    // send invitation email to upload recommendation letter to references
                    $fellappRecLetterUtil->sendInvitationEmailsToReferences($fellowshipApplication,true);
                }
                
                //delete: imported rows from the sheet on Google Drive and associated uploaded files from the Google Drive.
                if( $deleteSourceRow ) {

                    $userSecUtil = $this->container->get('user_security_utility');
                    $deleteImportedAplicationsFellApp = $userSecUtil->getSiteSettingParameter('deleteImportedAplicationsFellApp');
                    if( $deleteImportedAplicationsFellApp ) {

                        $backupFileIdFellApp = $userSecUtil->getSiteSettingParameter('backupFileIdFellApp');
                        if( $backupFileIdFellApp ) {
                            $googleSheetManagement = $this->container->get('fellapp_googlesheetmanagement');
                            $rowId = $fellowshipApplication->getGoogleFormId();

                            $worksheet = $googleSheetManagement->getSheetByFileId($backupFileIdFellApp);

                            $deletedRows = $googleSheetManagement->deleteImportedApplicationAndUploadsFromGoogleDrive($worksheet, $rowId);

                            if( $deletedRows ) {
                                $event = "Fellowship Application (and all uploaded files) with Google Applicant ID=".$googleFormId." Application ID " . $fellowshipApplication->getId() . " has been successful deleted from Google Drive";
                                $eventTypeStr = "Deleted Fellowship Application Backup From Google Drive";
                            } else {
                                $event = "Error: Fellowship Application with Google Applicant ID=".$googleFormId." Application ID " . $fellowshipApplication->getId() . "failed to delete from Google Drive";
                                $eventTypeStr = "Failed Deleted Fellowship Application Backup From Google Drive";
                            }
                            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$fellowshipApplication,null,$eventTypeStr);
                            $logger->notice($event);

                        }//if

                    }

                }
//                $deleteImportedAplicationsFellApp = $userUtil->getSiteSetting($this->em,'deleteImportedAplicationsFellApp');
//                if( $deleteImportedAplicationsFellApp ) {
//                    $googleSheetManagement = $this->container->get('fellapp_googlesheetmanagement');
//                    $res = $googleSheetManagement->deleteImportedApplicationAndUploadsFromGoogleDrive($fellowshipApplication->getGoogleFormId());
//                    if( $res ) {
//                        $event = "Fellowship Application (and all uploaded files) with Google Applicant ID=".$googleFormId." Application ID " . $fellowshipApplication->getId() . " has been successful deleted from Google Drive";
//                        $eventTypeStr = "Deleted Fellowship Application From Google Drive";
//                    } else {
//                        $event = "Error: Fellowship Application with Google Applicant ID=".$googleFormId." Application ID " . $fellowshipApplication->getId() . "failed to delete from Google Drive";
//                        $eventTypeStr = "Failed Deleted Fellowship Application From Google Drive";
//                    }
//                    $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$systemUser,$fellowshipApplication,null,$eventTypeStr);
//                    $logger->error($event);
//                }

                //$count++;
                if( $fellowshipApplication && !$populatedFellowshipApplications->contains($fellowshipApplication) ) {
                    $populatedFellowshipApplications->add($fellowshipApplication);
                }

                //exit( 'Test: end of fellowship applicant id='.$fellowshipApplication->getId() );

            } catch( \Doctrine\DBAL\DBALException $e ) {
                //} catch( \Exception $e ) {

                //        //reopen em after DBALException
                //        if( !$em->isOpen() ) {
                //            echo 'em is closed; ID=' . $googleFormId."<br>";
                //            $em = $em->create( $em->getConnection(), $em->getConfiguration() );
                //            $this->em = $em;
                //            // reset the EM and all aias
                ////                $container = $this->container;
                ////                $container->set('doctrine.orm.entity_manager', null);
                ////                $container->set('doctrine.orm.default_entity_manager', null);
                ////                // get a fresh EM
                ////                $em = $this->container->getDoctrine()->getManager();
                ////                $this->em = $em;
                //        }

                //email
                //$emails = "oli2002@med.cornell.edu";
                //$userutil = new UserUtil();
                //$emails = $userutil->getSiteSetting($this->em,'siteEmail');
                $event = "Error creating fellowship applicant with unique Google Applicant ID=".$googleFormId."; Exception=".$e->getMessage();
                //$emailUtil->sendEmail( $emails, $subjectError, $event );
                $this->sendEmailToSystemEmail($subjectError, $event);

                //logger
                $logger->error($event);

                //flash
                $this->container->get('session')->getFlashBag()->add(
                    'warning',
                    $event
                );
            } //try/catch


        } //for


        //echo "count=".$count."<br>";
        //exit('end populate');

        return $populatedFellowshipApplications;
    }

    public function createFellAppReference($em,$author,$typeStr,$rowData,$headers,$testOnly=false) {

        //recommendation1Name	recommendation1Title	recommendation1Institution	recommendation1AddressStreet1
        //recommendation1AddressStreet2	recommendation1AddressCity	recommendation1AddressState	recommendation1AddressZip	recommendation1AddressCountry

        $recommendationFirstName = $this->getValueByHeaderName($typeStr."FirstName",$rowData,$headers);
        $recommendationLastName = $this->getValueByHeaderName($typeStr."LastName",$rowData,$headers);

        //echo "recommendationFirstName=".$recommendationFirstName."<br>";
        //echo "recommendationLastName=".$recommendationLastName."<br>";

        if( !$recommendationFirstName && !$recommendationLastName ) {
            //echo "no ref<br>";
            return null;
        }

        if( $testOnly ) {
            return true;
        }

        $reference = new Reference($author);

        //recommendation1FirstName
        $reference->setFirstName($recommendationFirstName);

        //recommendation1LastName
        $reference->setName($recommendationLastName);

        //recommendation1Degree
        $recommendationDegree = $this->getValueByHeaderName($typeStr."Degree",$rowData,$headers);
        if( $recommendationDegree ) {
            $reference->setDegree($recommendationDegree);
        }

        //recommendation1Title
        $recommendationTitle = $this->getValueByHeaderName($typeStr."Title",$rowData,$headers);
        if( $recommendationTitle ) {
            $reference->setTitle($recommendationTitle);
        }

        //recommendation1Email
        $recommendationEmail = $this->getValueByHeaderName($typeStr."Email",$rowData,$headers);
        if( $recommendationEmail ) {
            $reference->setEmail($recommendationEmail);
        }

        //recommendation1Phone
        $recommendationPhone = $this->getValueByHeaderName($typeStr."Phone",$rowData,$headers);
        if( $recommendationPhone ) {
            $reference->setPhone($recommendationPhone);
        }

        $instStr = $this->getValueByHeaderName($typeStr."Institution",$rowData,$headers);
        if( $instStr ) {
            $params = array('type'=>'Educational');
            $instStr = trim($instStr);
            $instStr = $this->capitalizeIfNotAllCapital($instStr);
            $transformer = new GenericTreeTransformer($em, $author, 'Institution', null, $params);
            $instEntity = $transformer->reverseTransform($instStr);
            $reference->setInstitution($instEntity);
        }

        $geoLocation = $this->createGeoLocation($em,$author,$typeStr."Address",$rowData,$headers);
        if( $geoLocation ) {
            $reference->setGeoLocation($geoLocation);
        }

//        //generate hash ID
//        $this->generateRecLetterId($reference);

        return $reference;
    }

    public function createGeoLocation($em,$author,$typeStr,$rowData,$headers) {

        $geoLocationStreet1 = $this->getValueByHeaderName($typeStr.'Street1',$rowData,$headers);
        $geoLocationStreet2 = $this->getValueByHeaderName($typeStr.'Street2',$rowData,$headers);
        //echo "geoLocationStreet1=".$geoLocationStreet1."<br>";
        //echo "geoLocationStreet2=".$geoLocationStreet2."<br>";

        if( !$geoLocationStreet1 && !$geoLocationStreet2 ) {
            //echo "no geoLocation<br>";
            return null;
        }

        $geoLocation = new GeoLocation();
        //popuilate geoLocation
        $geoLocation->setStreet1($this->getValueByHeaderName($typeStr.'Street1',$rowData,$headers));
        $geoLocation->setStreet2($this->getValueByHeaderName($typeStr.'Street2',$rowData,$headers));
        $geoLocation->setZip($this->getValueByHeaderName($typeStr.'Zip',$rowData,$headers));
        //presentAddressCity
        $presentAddressCity = $this->getValueByHeaderName($typeStr.'City',$rowData,$headers);
        if( $presentAddressCity ) {
            $presentAddressCity = trim($presentAddressCity);
            $transformer = new GenericTreeTransformer($em, $author, 'CityList');
            $presentAddressCityEntity = $transformer->reverseTransform($presentAddressCity);
            $geoLocation->setCity($presentAddressCityEntity);
        }
        //presentAddressState
        $presentAddressState = $this->getValueByHeaderName($typeStr.'State',$rowData,$headers);
        if( $presentAddressState ) {
            $presentAddressState = trim($presentAddressState);
            $transformer = new GenericTreeTransformer($em, $author, 'States');
            $presentAddressStateEntity = $transformer->reverseTransform($presentAddressState);
            $geoLocation->setState($presentAddressStateEntity);
        }
        //presentAddressCountry
        $presentAddressCountry = $this->getValueByHeaderName($typeStr.'Country',$rowData,$headers);
        if( $presentAddressCountry ) {
            $presentAddressCountry = trim($presentAddressCountry);
            $transformer = new GenericTreeTransformer($em, $author, 'Countries');
            $presentAddressCountryEntity = $transformer->reverseTransform($presentAddressCountry);
            $geoLocation->setCountry($presentAddressCountryEntity);
        }

        return $geoLocation;
    }

    public function transformDatestrToDate($datestr) {

        $userSecUtil = $this->container->get('user_security_utility');
        return $userSecUtil->transformDatestrToDateWithSiteEventLog($datestr,$this->container->getParameter('fellapp.sitename'));

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
//            //$this->sendEmailToSystemEmail("Bad format of datetime string", $msg);
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
//            $this->sendEmailToSystemEmail("Bad format of datetime string", $msg);
//        }
//
//        return $date;
    }
//    function valid_date($date) {
//        return (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date));
//    }

    public function createFellAppBoardCertification($em,$fellowshipApplication,$author,$typeStr,$rowData,$headers) {

        $boardCertificationIssueDate = $this->getValueByHeaderName($typeStr.'Date',$rowData,$headers);
        if( !$boardCertificationIssueDate ) {
            return null;
        }

        $boardCertification = new BoardCertification($author);
        $fellowshipApplication->addBoardCertification($boardCertification);
        $fellowshipApplication->getUser()->getCredentials()->addBoardCertification($boardCertification);

        //boardCertification1Board
        $boardCertificationBoard = $this->getValueByHeaderName($typeStr.'Board',$rowData,$headers);
        if( $boardCertificationBoard ) {
            $boardCertificationBoard = trim($boardCertificationBoard);
            $transformer = new GenericTreeTransformer($em, $author, 'CertifyingBoardOrganization');
            $CertifyingBoardOrganizationEntity = $transformer->reverseTransform($boardCertificationBoard);
            $boardCertification->setCertifyingBoardOrganization($CertifyingBoardOrganizationEntity);
        }

        //boardCertification1Area => BoardCertifiedSpecialties
        $boardCertificationArea = $this->getValueByHeaderName($typeStr.'Area',$rowData,$headers);
        if( $boardCertificationArea ) {
            $boardCertificationArea = trim($boardCertificationArea);
            $transformer = new GenericTreeTransformer($em, $author, 'BoardCertifiedSpecialties');
            $boardCertificationAreaEntity = $transformer->reverseTransform($boardCertificationArea);
            $boardCertification->setSpecialty($boardCertificationAreaEntity);
        }

        //boardCertification1Date
        $boardCertification->setIssueDate($this->transformDatestrToDate($boardCertificationIssueDate));

        return $boardCertification;
    }

    public function createFellAppMedicalLicense($em,$fellowshipApplication,$author,$typeStr,$rowData,$headers) {

        //medicalLicensure1Country	medicalLicensure1State	medicalLicensure1DateIssued	medicalLicensure1Number	medicalLicensure1Active

        $licenseNumber = $this->getValueByHeaderName($typeStr.'Number',$rowData,$headers);
        $licenseIssuedDate = $this->getValueByHeaderName($typeStr.'DateIssued',$rowData,$headers);

        if( !$licenseNumber && !$licenseIssuedDate ) {
            return null;
        }

        $license = new StateLicense($author);
        $fellowshipApplication->addStateLicense($license);
        $fellowshipApplication->getUser()->getCredentials()->addStateLicense($license);

        //medicalLicensure1DateIssued
        $license->setLicenseIssuedDate($this->transformDatestrToDate($licenseIssuedDate));

        //medicalLicensure1Active
        $medicalLicensureActive = $this->getValueByHeaderName($typeStr.'Active',$rowData,$headers);
        if( $medicalLicensureActive ) {
            $transformer = new GenericTreeTransformer($em, $author, 'MedicalLicenseStatus');
            $medicalLicensureActiveEntity = $transformer->reverseTransform($medicalLicensureActive);
            $license->setActive($medicalLicensureActiveEntity);
        }

        //medicalLicensure1Country
        $medicalLicensureCountry = $this->getValueByHeaderName($typeStr.'Country',$rowData,$headers);
        if( $medicalLicensureCountry ) {
            $medicalLicensureCountry = trim($medicalLicensureCountry);
            $transformer = new GenericTreeTransformer($em, $author, 'Countries');
            $medicalLicensureCountryEntity = $transformer->reverseTransform($medicalLicensureCountry);
            //echo "MedCountry=".$medicalLicensureCountryEntity.", ID+".$medicalLicensureCountryEntity->getId()."<br>";
            $license->setCountry($medicalLicensureCountryEntity);
        }

        //medicalLicensure1State
        $medicalLicensureState = $this->getValueByHeaderName($typeStr.'State',$rowData,$headers);
        if( $medicalLicensureState ) {
            $medicalLicensureState = trim($medicalLicensureState);
            $transformer = new GenericTreeTransformer($em, $author, 'States');
            $medicalLicensureStateEntity = $transformer->reverseTransform($medicalLicensureState);
            //echo "MedState=".$medicalLicensureStateEntity."<br>";
            $license->setState($medicalLicensureStateEntity);
        }

        //medicalLicensure1Number
        $license->setLicenseNumber($licenseNumber);

        return $license;
    }

    public function createFellAppTraining($em,$fellowshipApplication,$author,$typeStr,$rowData,$headers,$orderinlist) {

        //Start
        $trainingStart = $this->getValueByHeaderName($typeStr.'Start',$rowData,$headers);
        //End
        $trainingEnd = $this->getValueByHeaderName($typeStr.'End',$rowData,$headers);

        if( !$trainingStart && !$trainingEnd ) {
            return null;
        }

        $training = new Training($author);
        $training->setOrderinlist($orderinlist);
        $fellowshipApplication->addTraining($training);
        $fellowshipApplication->getUser()->addTraining($training);

        //set TrainingType
        if( $typeStr == 'undergraduateSchool' ) {
            $trainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('Undergraduate');
            $training->setTrainingType($trainingType);
        }
        if( $typeStr == 'graduateSchool' ) {
            $trainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('Graduate');
            $training->setTrainingType($trainingType);
        }
        if( strpos($typeStr,'medical') !== false ) {
            $trainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('Medical');
            $training->setTrainingType($trainingType);
        }
        if( strpos($typeStr,'residency') !== false ) {
            $trainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('Residency');
            $training->setTrainingType($trainingType);
        }
        if( strpos($typeStr,'gme1') !== false ) {
            //Post-Residency Fellowship
            $trainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('Post-Residency Fellowship');
            $training->setTrainingType($trainingType);
        }
        if( strpos($typeStr,'gme2') !== false ) {
            $trainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('GME');
            $training->setTrainingType($trainingType);
        }
        if( strpos($typeStr,'other') !== false ) {
            $trainingType = $em->getRepository('AppUserdirectoryBundle:TrainingTypeList')->findOneByName('Other');
            $training->setTrainingType($trainingType);
        }

        $majorMatchString = $typeStr.'Major';
        $nameMatchString = $typeStr.'Name';

        if( strpos($typeStr,'otherExperience') !== false ) {
            //otherExperience1Name => jobTitle
            $nameMatchString = null;
            $majorMatchString = null;
            $jobTitle = $this->getValueByHeaderName($typeStr.'Name',$rowData,$headers);
            $jobTitle = trim($jobTitle);
            $transformer = new GenericTreeTransformer($em, $author, 'JobTitleList');
            $jobTitleEntity = $transformer->reverseTransform($jobTitle);
            $training->setJobTitle($jobTitleEntity);
        }

        if( strpos($typeStr,'gme') !== false ) {
            //gme1Start	gme1End	gme1Name gme1Area
            //exception for Area: gmeArea => Major
            $majorMatchString = $typeStr.'Area';
        }

        if( strpos($typeStr,'residency') !== false ) {
            //residencyStart	residencyEnd	residencyName	residencyArea
            //residencyArea => ResidencySpecialty
            $residencyArea = $this->getValueByHeaderName('residencyArea',$rowData,$headers);
            $transformer = new GenericTreeTransformer($em, $author, 'ResidencySpecialty');
            $residencyArea = trim($residencyArea);
            $residencyAreaEntity = $transformer->reverseTransform($residencyArea);
            $training->setResidencySpecialty($residencyAreaEntity);
        }

        //Start
        $training->setStartDate($this->transformDatestrToDate($this->getValueByHeaderName($typeStr.'Start',$rowData,$headers)));

        //End
        $training->setCompletionDate($this->transformDatestrToDate($this->getValueByHeaderName($typeStr.'End',$rowData,$headers)));

        //City, Country, State
        $city = $this->getValueByHeaderName($typeStr.'City',$rowData,$headers);
        $country = $this->getValueByHeaderName($typeStr.'Country',$rowData,$headers);
        $state = $this->getValueByHeaderName($typeStr.'State',$rowData,$headers);

        if( $city || $country || $state ) {
            $trainingGeo = new GeoLocation();
            $training->setGeoLocation($trainingGeo);

            if( $city ) {
                $city = trim($city);
                $transformer = new GenericTreeTransformer($em, $author, 'CityList');
                $cityEntity = $transformer->reverseTransform($city);
                $trainingGeo->setCity($cityEntity);
            }

            if( $country ) {
                $country = trim($country);
                $transformer = new GenericTreeTransformer($em, $author, 'Countries');
                $countryEntity = $transformer->reverseTransform($country);
                $trainingGeo->setCountry($countryEntity);
            }

            if( $state ) {
                $state = trim($state);
                $transformer = new GenericTreeTransformer($em, $author, 'States');
                $stateEntity = $transformer->reverseTransform($state);
                $trainingGeo->setState($stateEntity);
            }
        }

        //Name
        $schoolName = $this->getValueByHeaderName($nameMatchString,$rowData,$headers);
        if( $schoolName ) {
            $params = array('type'=>'Educational');
            $schoolName = trim($schoolName);
            $schoolName = $this->capitalizeIfNotAllCapital($schoolName);
            $transformer = new GenericTreeTransformer($em, $author, 'Institution', null, $params);
            $schoolNameEntity = $transformer->reverseTransform($schoolName);
            $training->setInstitution($schoolNameEntity);
        }

        //Description
        $schoolDescription = $this->getValueByHeaderName($typeStr.'Description',$rowData,$headers);
        if( $schoolDescription ) {
            $schoolDescription = trim($schoolDescription);
            $training->setDescription($schoolDescription);
        }

        //Major
        $schoolMajor = $this->getValueByHeaderName($majorMatchString,$rowData,$headers);
        if( $schoolMajor ) {
            $schoolMajor = trim($schoolMajor);
            $transformer = new GenericTreeTransformer($em, $author, 'MajorTrainingList');
            $schoolMajorEntity = $transformer->reverseTransform($schoolMajor);
            $training->addMajor($schoolMajorEntity);
        }

        //Degree
        $schoolDegree = $this->getValueByHeaderName($typeStr.'Degree',$rowData,$headers);
        if( $schoolDegree ) {
            $schoolDegree = trim($schoolDegree);
            $transformer = new GenericTreeTransformer($em, $author, 'TrainingDegreeList');
            $schoolDegreeEntity = $transformer->reverseTransform($schoolDegree);
            $training->setDegree($schoolDegreeEntity);
        }

        return $training;
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


    //parse url and get file id
    public function getFileIdByUrl( $url ) {
        if( !$url ) {
            return null;
        }
        //https://drive.google.com/a/pathologysystems.org/file/d/0B2FwyaXvFk1eSDQ0MkJKSjhLN1U/view?usp=drivesdk
        $urlArr = explode("/d/", $url);
        $urlSecond = $urlArr[1];
        $urlSecondArr = explode("/", $urlSecond);
        $fileId = $urlSecondArr[0];
        return $fileId;
    }

//    function capitalizeIfNotAllCapital($s) {
//        if( strlen(preg_replace('![^A-Z]+!', '', $s)) == strlen($s) ) {
//            $s = ucfirst(strtolower($s));
//        }
//        return $s;
//    }
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

    public function sendEmailToSystemEmail($subject, $message) {
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->sendEmailToSystemEmail($subject, $message);
    }

    ////////////////////////////////////// EOF Populate FellApp ///////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////









    //Automatically delete downloaded application spreadsheets that are older than [X] year(s)
    // X - yearsOldAplicationsFellApp
    public function deleteOldSheetFellApp() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //deleteOldAplicationsFellApp
        $deleteOldAplicationsFellApp = $userSecUtil->getSiteSettingParameter('deleteOldAplicationsFellApp');
        if( !$deleteOldAplicationsFellApp ) {
            $logger->notice('deleteOldAplicationsFellApp is FALSE or not defined in Site Parameters. deleteOldAplicationsFellApp='.$deleteOldAplicationsFellApp);
            return false;
        }

        $yearsOldAplicationsFellApp = $userSecUtil->getSiteSettingParameter('yearsOldAplicationsFellApp');
        if( !$yearsOldAplicationsFellApp ) {
            $logger->warning('yearsOldAplicationsFellApp is not defined in Site Parameters. yearsOldAplicationsFellApp='.$yearsOldAplicationsFellApp);
            return false;
        }

        //delete old sheets
        $days = $yearsOldAplicationsFellApp * 365;
        $result = $userSecUtil->deleteOrphanFiles( $days, 'Fellowship Application Spreadsheet', 'only' );

        return $result;
    }



} 