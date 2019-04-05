<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/1/2019
 * Time: 11:31 AM
 */

namespace Oleg\FellAppBundle\Util;


use Symfony\Component\Filesystem\Exception\IOException;

class RecLetterUtil {

    protected $em;
    protected $container;
    protected $uploadDir;

    public function __construct( $em, $container ) {
        $this->em = $em;
        $this->container = $container;
        $this->uploadDir = 'Uploaded';
    }

    //Recommendation Letter Salted Script Hash ID
    public function generateRecLetterId( $fellapp, $reference, $request ) {

        $userSecUtil = $this->container->get('user_security_utility');

        $str = "pepperstr";

        $salt = $userSecUtil->getSiteSettingParameter('recLetterSaltFellApp');
        if( !$salt ) {
            $salt = 'pepper';
        }

        //Generate "Recommendation Letter Salted Scrypt Hash ID":
        // Live Server URL from Site Settings +
        $url = $request->getSchemeAndHttpHost();

        // Organizational Group of the received application +
        $institution = $fellapp->getInstitution();
        if( $institution ) {
            $institutionId = $institution->getId();
        } else {
            $institutionId = NULL;
        }

        // Fellowship Type of the Application +
        $type = $fellapp->getFellowshipSubspecialty();
        if( $type ) {
            $typeId = $type->getId();
        } else {
            $typeId = NULL;
        }

        // Application ID +
        $fellappId = $fellapp->getId();

        // Application Timestamp +
        $timestamp = $fellapp->getTimestamp();
        if( $timestamp ) {
            $timestampStr = $timestamp->format("m-d-Y H:i:s");
        } else {
            $timestampStr = NULL;
        }

        // Reference ID +
        $referenceId = $reference->getId();

        // Reference Email +
        $referenceEmail = $reference->getEmail();

        // "Recommendation Letter Salt"
        //$salt

        $str = $url . $institutionId . $typeId . $fellappId . $timestampStr . $referenceId . $referenceEmail . $salt;

        //use if (hash_equals($knownString, $userInput)) to compare two hash (or php password_verify)
        //$hash = md5($str);
        //$hash = sha1($str);
        $hash = hash("sha1",$str); //sha1
        //$hash = password_hash($str,PASSWORD_DEFAULT);

        //echo "Hash=".$hash."<br>";

        return $hash;
    }

    public function processFellRecLetterFromGoogleDrive() {
        //1) Import sheets from Google Drive Folder
        $filesGoogleDrive = $this->importSheetsFromGoogleDriveFolder();

        //2) Import recommendation letter from Google Drive Folder
        $filesGoogleDrive = $this->importLetterFromGoogleDriveFolder();

        //2) Populate applications from DataFile DB object
        $populatedCount = $this->populateApplicationsFromDataFile();

        //3) Delete old sheet and uploads from Google Drive if deleteOldAplicationsFellApp is true
        $deletedSheetCount = $this->deleteSuccessfullyImportedApplications();

        //4)  Process backup sheet on Google Drive
        $populatedBackupApplications = $this->processBackupFellAppFromGoogleDrive();

        //$fellappRepGen = $this->container->get('fellapp_reportgenerator');
        //$generatedReport = $fellappRepGen->tryRun(); //run hard run report generation

        //exit('eof processFellAppFromGoogleDrive');

        $result = "Finish processing Fellowship Application on Google Drive and on server.<br>".
            "filesGoogleDrive=".count($filesGoogleDrive).", populatedCount=".$populatedCount.
            ", deletedSheetCount=".$deletedSheetCount.", populatedBackupApplications=".count($populatedBackupApplications)
            //.", First generated report in queue=".$generatedReport
        ;

        $logger = $this->container->get('logger');
        $logger->notice($result);

        //Event Logger with event type "Import of Fellowship Applications Spreadsheet". It will be used to get lastImportTimestamps
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $eventTypeStr = "Import of Fellowship Recommendation Letters Spreadsheet";
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$result,$systemUser,null,null,$eventTypeStr);

        return $result;
    }

    //1)  Import sheets from Google Drive
    //1a)   import all sheets from Google Drive folder
    //1b)   add successefull downloaded sheets to DataFile DB object with status "active"
    public function importSheetsFromGoogleDriveFolder() {

        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

        if( !$fellappImportPopulateUtil->checkIfFellappAllowed("Import from Google Drive") ) {
            //exit("can't import");
            //return null;
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

        $folderIdFellAppId = $userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
        if( !$folderIdFellAppId ) {
            $logger->warning('Google Drive Folder ID is not defined in Site Parameters. configFileFolderIdFellApp='.$folderIdFellAppId);
        }

        //find folder by name
        $letterSpreadsheetFolder = $googlesheetmanagement->findOneRecLetterSpreadsheetFolder($service,$folderIdFellAppId);
        echo "letterSpreadsheetFolder: Title=".$letterSpreadsheetFolder->getTitle()."; ID=".$letterSpreadsheetFolder->getId()."<br>";
        
        //exit("exit importSheetsFromGoogleDriveFolder");

        //get all files in google folder
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $files = $googlesheetmanagement->retrieveFilesByFolderId($letterSpreadsheetFolder->getId(),$service);
        echo "files count=".count($files)."<br>";

        //Download files to the server
        $documentType = "Fellowship Recommendation Letter Spreadsheet";
        $path = 'Uploaded'.'/'.'fellapp/RecommendationLetters/Spreadsheets';
        foreach( $files as $file ) {
            echo 'File Id: ' . $file->getId() . "; title=" . $file->getTitle() . "<br>";
            //Download file from Google Drive to the server without creating document entity
            //$this->processSingleFile( $file->getId(), $service, $documentType );
            //$googlesheetmanagement->printFile($service, $file->getId());
            $this->downloadSpeadsheetFileToServer($service,$file,$documentType,$path);
        }

        return $files; //google drive files

        //$logger->notice("Processed " . count($filesGoogleDrive) . " files with applicant data from Google Drive");

        //return $filesGoogleDrive;
    }
    //copy spreadsheet to the server. Keep the original file name (title).
    public function downloadSpeadsheetFileToServer($service, $file, $documentType, $path) {
        if( !$file ) {
            return NULL;
        }

        $fileExt = pathinfo($file->getTitle(), PATHINFO_EXTENSION);
        if( !$fileExt ) {
            if( $file->getMimeType() == "application/vnd.google-apps.spreadsheet" ) {
                $fileExt = "csv";
            }
        }
        $fileExtStr = "";
        if( $fileExt ) {
            $fileExtStr = ".".$fileExt;
        }

        $root = $this->container->get('kernel')->getRootDir();
        $fullpath = $root . '/../web/'.$path;
        $target_file = $fullpath . "/" . $file->getTitle() . $fileExtStr;

        //check if file already exists by file path
        if( file_exists($target_file) ) {
            echo "File already exists <br>";
            return NULL;
        }

        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $response = $googlesheetmanagement->downloadFile($service,$file,$documentType);
        if( !$response ) {
            throw new IOException('Error file response is empty: file id='.$file->getId());
        }

        if( !file_exists($fullpath) ) {
            // 0600 - Read/write/execute for owner, nothing for everybody else
            mkdir($fullpath, 0700, true);
            chmod($fullpath, 0700);
        }

        file_put_contents($target_file, $response);

        return $target_file;
    }

    public function importLetterFromGoogleDriveFolder() {
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

        if( !$fellappImportPopulateUtil->checkIfFellappAllowed("Import from Google Drive") ) {
            //exit("can't import");
            //return null;
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

        $folderIdFellAppId = $userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
        if( !$folderIdFellAppId ) {
            $logger->warning('Google Drive Folder ID is not defined in Site Parameters. configFileFolderIdFellApp='.$folderIdFellAppId);
        }

        //find folder by name
        $letterFolder = $googlesheetmanagement->findOneRecLetterUploadFolder($service,$folderIdFellAppId);
        echo "letterFolder: Title=".$letterFolder->getTitle()."; ID=".$letterFolder->getId()."<br>";

        //get all files in google folder
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $files = $googlesheetmanagement->retrieveFilesByFolderId($letterFolder->getId(),$service);
        echo "files count=".count($files)."<br>";

        //Download files to the server
        $documentType = "Fellowship Recommendation Letter Spreadsheet";
        $path = 'Uploaded'.'/'.'fellapp/RecommendationLetters/Uploads';
        foreach( $files as $file ) {
            echo 'File Id: ' . $file->getId() . "; title=" . $file->getTitle() . "<br>";
            //Download file from Google Drive to the server without creating document entity
            //$this->processSingleFile( $file->getId(), $service, $documentType );
            //$googlesheetmanagement->printFile($service, $file->getId());
            //$this->downloadSpeadsheetFileToServer($service,$file,$documentType,$path);
        }
    }

    public function populateApplicationsFromDataFile() {
        return 0;
    }

    public function deleteSuccessfullyImportedApplications() {
        return 0;
    }

    public function processBackupFellAppFromGoogleDrive() {
        return array();
    }

    

}