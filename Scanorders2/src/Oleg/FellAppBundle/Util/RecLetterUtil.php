<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/1/2019
 * Time: 11:31 AM
 */

namespace Oleg\FellAppBundle\Util;


use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RecLetterUtil {

    protected $em;
    protected $container;
    protected $uploadDir;

    public function __construct( $em, $container ) {
        $this->em = $em;
        $this->container = $container;
        $this->uploadDir = 'Uploaded';
    }

    public function generateFellappRecLetterId( $fellapp ) {
        $references = $fellapp->getReferences($fellapp);

        foreach($references as $reference) {
            $hash = $this->generateRecLetterId($fellapp,$reference);
            if( $hash ) {
                $reference->setRecLetterHashId($hash);
                //echo $fellapp->getId()." (".$reference->getId()."): added hash=".$hash."<br>";
            }
        }
    }

    //Recommendation Letter Salted Script Hash ID
    public function generateRecLetterId( $fellapp, $reference, $request=null, $count=0 ) {

        $userSecUtil = $this->container->get('user_security_utility');

        //$str = "pepperstr";

        $salt = $userSecUtil->getSiteSettingParameter('recLetterSaltFellApp');
        if( !$salt ) {
            $salt = 'pepper';
        }

        //Generate "Recommendation Letter Salted Scrypt Hash ID":
        // Live Server URL from Site Settings +
        if( !$request ) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
        }
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

        $str = $url . $institutionId . $typeId . $fellappId . $timestampStr . $referenceId . $referenceEmail . $salt . $count;

        //use if (hash_equals($knownString, $userInput)) to compare two hash (or php password_verify)
        //$hash = md5($str);
        //$hash = sha1($str);
        $hash = hash("sha1",$str); //sha1
        //$hash = password_hash($str,PASSWORD_DEFAULT);
        //echo "Hash=".$hash."<br>";

        //check for uniqueness
        if( $hash ) {
            $references = $this->em->getRepository('OlegFellAppBundle:Reference')->findByRecLetterHashId($hash);
            if( count($references) > 0 ) {
                $count = $count + 1;
                $hash = $this->generateRecLetterId( $fellapp, $reference, $request, $count );
            }
        }

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

        $result = "Finish processing Fellowship Recommendation Letters on Google Drive and on server.<br>".
            "filesGoogleDrive=".count($filesGoogleDrive).", populatedCount=".$populatedCount.
            ", deletedSheetCount=".$deletedSheetCount.", populatedBackupApplications=".count($populatedBackupApplications)
            //.", First generated report in queue=".$generatedReport
        ;

        $logger = $this->container->get('logger');
        $logger->notice($result);

        //Event Logger with event type "Import of Fellowship Applications Spreadsheet". It will be used to get lastImportTimestamps
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();
        $eventTypeStr = "Import of Fellowship Recommendation Letters";
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
        $importedLetters = array();
        $documentType = "Fellowship Recommendation Letter";
        $path = 'Uploaded'.'/'.'fellapp/RecommendationLetters/RecommendationLetterUploads';
        foreach( $files as $file ) {
            echo 'File Id: ' . $file->getId() . "; title=" . $file->getTitle() . "<br>";
            //Download file from Google Drive to the server without creating document entity
            $googlesheetmanagement->printFile($service, $file->getId());

            $documentDb = $this->processSingleLetter($service,$file,$documentType,$path);
            if( $documentDb ) {
                $importedLetters[] = $documentDb;
            }
        }

        exit("Exit importLetterFromGoogleDriveFolder");

        return $importedLetters;
    }
    public function processSingleLetter( $service, $file, $documentType, $path ) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $emailUtil = $this->container->get('user_mailer_utility');
        $systemUser = $userSecUtil->findSystemUser();

        $testing = true;

//        //test
//        $subject = "More than one recommendation letter received from "."RefName"." in support of
//                "."ApplicantName"."'s application ID#"."FellappId"." for the "."FellType"." StartDate fellowship";
//
//        //TODO: get CreatedTime. Not in file's metadata.
//        //$latestLetterTime = $file->getCreatedTime();
//        //$latestLetterTime = $file->get('createdTime');
//        $latestLetterTime = new \DateTime();
//        if( $latestLetterTime ) {
//            $latestLetterTimeStr = $latestLetterTime->format("m/d/Y H:i");
//        }
//        $body = $subject . " The latest document was received on ".$latestLetterTimeStr;
//
//        //$userSecUtil->sendEmailToSystemEmail($subject,$body);
//        $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'),"Administrator");
//        $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'),"Platform Administrator");
//        if( !$emails ) {
//            $emails = $ccs;
//            $ccs = null;
//        }
//        $emailUtil->sendEmail( $emails, $subject, $body, $ccs );
//        //test

        //check if file already exists by file id
        $documentDb = $this->em->getRepository('OlegUserdirectoryBundle:Document')->findOneByUniqueid($file->getId());
        if( $documentDb && $documentType != 'Fellowship Application Backup Spreadsheet' ) {
            echo "letter already exists with document ID=".$documentDb->getId()."<br>";
            //$logger = $this->container->get('logger');
            //$event = "Document already exists with uniqueid=".$file->getId();
            //$logger->warning($event);
            if( !$testing ) {
                return $documentDb;
            }
            //return $documentDb;
        }

        //download file to the server and create Document object in DB
        $uploadedLetterDb = $googlesheetmanagement->downloadFileToServer($systemUser,$service,$file->getId(),$documentType,$path);
        if( !$uploadedLetterDb ) {
            throw new IOException('Unable to download file to server: fileID='.$uploadedLetterDb->getId());
        }
        //$fellowshipApplication->addReprimandDocument($uploadedLegalExplanationUrlDb);

        //ID_datetime_name.ext: 0000000110c8357966576df46f3b802ca897deb7ad18b12f1c24ecff6386ebd9_2019-04-03-13-13-17_Cat-Wa.jpg
        $letterArr = explode("_",$file->getTitle());
        echo "letterArr count=".count($letterArr)."<br>";
        if( count($letterArr) == 3 ) {
            $refId = $letterArr[0];
            $datetime = $letterArr[1];
            $name = $letterArr[2];
        } else {
            return NULL;
        }

        if( $testing ) {
            $refId = "340d08a7c8037b62e5e0e36b1119486f2dd00540";
            $datetime = "2019-04-03-13-13-17";
            $name = "filenameee";
        }

        //find application and reference by reference ID
        echo "search by ref ID=".$refId."<br>";
        $references = $this->em->getRepository('OlegFellAppBundle:Reference')->findByRecLetterHashId($refId);
        echo "references count=".count($references)."<br>";

        //not found
        if( count($references) == 0 ) {
            //send email
            $msg = "No fellowship references found by letter ID=".$refId;
            $userSecUtil->sendEmailToSystemEmail($msg,$msg);
            //eventlog
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$msg,$systemUser,null,null,"No Recommendation Letters");
            return NULL;
        }

        //can't be more than 1
        if( count($references) > 1 ) {
            //send email
            $msg = "Error: Multiple " . count($references) . " fellowship references found by letter ID=".$refId;
            $userSecUtil->sendEmailToSystemEmail($msg,$msg);
            //eventlog
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$msg,$systemUser,null,null,"Multiple Recommendation Letters");
            return NULL;
        }

        //Good: only one reference corresponds to the hash Id
        if( count($references) == 1 ) {
            $reference = $references[0];
            $fellapp = $reference->getFellapp(); 
            $applicant = $fellapp->getUser();
            $applicantName = "Unknown Applicant";
            if( $applicant ) {
                $applicantName = $applicant->getUsernameOptimal();
            }
            $startDate = $fellapp->getStartDate();
            $startDateStr = null;
            if( $startDate ) {
                $startDateStr = $startDate->format('m/d/Y');
            }

            //check if this reference already has a letter
            $letters = $reference->getDocuments();
            echo "letters count=".count($letters)."<br>";
            if( count($letters) > 0 ) {
                $subject = "More than one recommendation letter received from ".$reference->getFullName()." in support of 
                ".$applicantName."'s application ID#".$fellapp->getId()." for the ".$fellapp->getFellowshipSubspecialty()." $startDateStr fellowship";

                //TODO: get CreatedTime. Not in file's metadata.
                //$latestLetterTime = $file->getCreatedTime();
                //use $datetime from the filename
                $latestLetterTimeStr = NULL;
                if( $datetime ) {
                    //2019-04-03-13-13-17
                    $timeArr = explode("-",$datetime);
                    if( count($timeArr) == 6 ) {
                        //m/d/Y H:i
                        $latestLetterTimeStr = $timeArr[1]."/".$timeArr[2]."/".$timeArr[0]. " at " . $timeArr[3].":".$timeArr[4];
                    }
                }
                if( !$latestLetterTimeStr ) {
                    $latestLetterTime = new \DateTime();
                    $latestLetterTimeStr = $latestLetterTime->format("m/d/Y H:i");
                }
                $body = $subject . " The latest document was received on ".$latestLetterTimeStr;
                $body = $body . "<br><br>" . "Please review these letters of recommendation and delete any duplicates or erroneously added documents.";

                //You can review the letter 1 here: LINKtoLETTER1. You can review the letter 2 here: LINKtoLETTER2. You can review the letter 3 here: LINKtoLETTER3.
                $reviewLetterArr = array();
                $counter = 1;
                foreach($letters as $letter) {
                    $letterLink = $this->container->get('router')->generate(
                        'fellapp_file_download',
                        array('id' => $letter->getId()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $letterLink = '<a href="'.$letterLink.'">'.$letterLink.'</a>';
                    $reviewLetterArr[] = "You can review the letter $counter here: " . $letterLink;
                    $counter++;
                }
                $body = $body . "<br><br>" . implode("<br>",$reviewLetterArr);

                //You can review the entire application here: LINKtoAPPLICATION.
                $fellappLink = $this->container->get('router')->generate(
                    'fellapp_show',
                    array('id' => $fellapp->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $fellappLink = '<a href="'.$fellappLink.'">'.$fellappLink.'</a>';
                $body = $body . "<br><br>" . "You can review the entire application here: ".$fellappLink;

                //$userSecUtil->sendEmailToSystemEmail($subject,$body);
                $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'),"Administrator");
                $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'),"Platform Administrator");
                if( !$emails ) {
                    $emails = $ccs;
                    $ccs = null;
                }
                $emailUtil->sendEmail( $emails, $subject, $body, $ccs );
            } //if count($letters) > 0

            //add this letter to this reference

            $reference->addDocument($uploadedLetterDb);
            $this->em->flush($reference);

            //TODO: update application PDF:
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            //async generation
            //$fellappRepGen->addFellAppReportToQueue( $fellapp->getId(), 'overwrite' );
            //sync generation
            $res = $fellappRepGen->generateFellAppReport( $fellapp->getId() );

            echo "filename=".$res['filename']."<br>";

            return $uploadedLetterDb;
        } //if count($references) == 1


        return NULL;
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