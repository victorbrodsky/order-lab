<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/1/2019
 * Time: 11:31 AM
 */

namespace Oleg\FellAppBundle\Util;


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

        if( !$this->checkIfFellappAllowed("Import from Google Drive") ) {
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
        $folderName = "Responses";
        $folder = $googlesheetmanagement->findOneFolderByFolderNameAndParentFolder($service,$folderIdFellAppId,$folderName);
        echo "Folder=".$folder->getTitle()."; ID=".$folder->getId()."<br>";

        $folderName = "RecommendationLetters";
        $folder = $googlesheetmanagement->findOneRecLetterUploadFolder($service,$folderIdFellAppId,$folderName);

        //foreach($folders as $folder) {
            echo "Folder=".$folder->getTitle()."; ID=".$folder->getId()."<br>";
        //}
        exit("exit importSheetsFromGoogleDriveFolder");

        //get all files in google folder
        //$filesGoogleDrive = $this->processFilesInFolder($folderIdFellApp,$service);

        //$logger->notice("Processed " . count($filesGoogleDrive) . " files with applicant data from Google Drive");

        //return $filesGoogleDrive;
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
    

}