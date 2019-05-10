<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/1/2019
 * Time: 11:31 AM
 */

namespace Oleg\FellAppBundle\Util;


use Oleg\UserdirectoryBundle\Entity\User;
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

    //Generate hash ID only once when application is created.
    //This hash ID will be used to auto attach recommendation letter to the reference's application.
    public function generateFellappRecLetterId( $fellapp, $flush=false ) {
        $logger = $this->container->get('logger');
        foreach($fellapp->getReferences() as $reference) {
            $hash = $this->generateRecLetterId($fellapp,$reference);
            if( $hash ) {
                $reference->setRecLetterHashId($hash);
                $logger->notice($fellapp->getId()." (".$reference->getId()."): added hash=".$hash);
                if( $flush ) {
                    $this->em->flush($reference);
                    $logger->notice($fellapp->getId()." (".$reference->getId()."): flushed with an added hash=".$hash);
                }
                //echo $fellapp->getId()." (".$reference->getId()."): added hash=".$hash."<br>";
            }
        }
    }

    //Recommendation Letter Salted Script Hash ID
    public function generateRecLetterId( $fellapp, $reference, $request=null, $count=0 ) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //$str = "pepperstr";

        $salt = $userSecUtil->getSiteSettingParameter('recLetterSaltFellApp');
        if( !$salt ) {
            $salt = 'pepper';
        }

        //Generate "Recommendation Letter Salted Scrypt Hash ID":
        // Live Server URL from Site Settings +
        $url = NULL;
        if( !$request ) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
        }
        if( $request ) {
            $url = $request->getSchemeAndHttpHost();
        }
        if( !$url ) {
            $url = $userSecUtil->getSiteSettingParameter('environment');
        }

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
        if( !$fellappId ) {
            $fellappId = microtime(true);
        }

        // Application Timestamp +
        $timestamp = $fellapp->getTimestamp();
        if( $timestamp ) {
            $timestampStr = $timestamp->format("m-d-Y H:i:s");
        } else {
            $timestampStr = NULL;
        }

        // Reference ID +
        $referenceId = $reference->getId();
        if( !$reference ) {
            $referenceId = microtime(true);
        }

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
        $logger->notice($fellappId."(".$referenceId.", count=".$count."): Generated hash=".$hash);

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

    public function inviteSingleReferenceToSubmitLetter( $reference, $fellapp=null, $flush=true ) {

        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment != "live" ) {
            $msg = "Server is not live: invitation email will not be send to reference ".$reference->getFullName();
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$fellapp);
            $res = array(
                "res" => false,
                "msg" => $msg
            );
            return $res;
        }

        $sendEmailUploadLetterFellApp = $userSecUtil->getSiteSettingParameter('sendEmailUploadLetterFellApp');
        if( !$sendEmailUploadLetterFellApp ) {
            $msg = "Automatically send invitation emails to upload recommendation letters is set to NO: invitation email will not be send to reference ".$reference->getFullName();
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$fellapp);
            $res = array(
                "res" => false,
                "msg" => $msg
            );
            return $res;
        }

        //do not invite if letter already received
        if( count($reference->getDocuments()) > 0 ) {
//            $this->container->get('session')->getFlashBag()->add(
//                'warning',
//                "Recommendation letter has already been received for reference ".$reference->getFullName()
//            );
            $msg = "Recommendation letter has already been received from reference ".$reference->getFullName();
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$fellapp);
            $res = array(
                "res" => false,
                "msg" => $msg
            );
            return $res;
        }

        if(1) { //Generate hash ID here if empty. (It must be pre-generated before?)
            if( !$reference->getRecLetterHashId() ) {
                $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
                $hash = $fellappRecLetterUtil->generateRecLetterId($fellapp, $reference);
                if ($hash) {
                    $reference->setRecLetterHashId($hash);
                    $this->em->flush($reference);
                    //echo $fellapp->getId()." (".$reference->getId()."): added hash=".$hash."<br>";
//                    $this->container->get('session')->getFlashBag()->add(
//                        'warning',
//                        "Reference Letter Hash ID has been re-generated for " . $reference->getFullName()
//                    );
                }
                $hash = NULL;
            }
        }
        if( !$reference->getRecLetterHashId() ) {
            $msg = "Error sending invitation email: Reference Letter Hash ID has not been generated for ".$reference->getFullName();
            $logger->error($msg);
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$fellapp);
            $res = array(
                "res" => false,
                "msg" => $msg
            );
            return $res;
        }

        $email = $reference->getEmail();
        if( !$email ) {
//            $this->container->get('session')->getFlashBag()->add(
//                'warning',
//                "Email is not specified for reference ".$reference->getFullName()
//            );
//            return false;
            $msg = "Email is not specified for reference ".$reference->getFullName();
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$fellapp);
            $res = array(
                "res" => false,
                "msg" => $msg
            );
            return $res;
        }

        $referenceFullName = $reference->getFullName();


        $logger->notice("Invite reference $referenceFullName to send invitation letter");

        if( !$fellapp ) {
            $fellapp = $reference->getFellapp();
        }

        $fellappType = $fellapp->getFellowshipSubspecialty();
        if( $fellappType ) {
            $fellappTypeStr = $fellappType->getName();
        } else {
            $fellappTypeStr = null;
        }

        $startDate = $fellapp->getStartDate();
        if( $startDate ) {
            $startDateStr = $startDate->format('Y');
        } else {
            $startDateStr = NULL;
        }

        $geoLocation = $reference->getGeoLocation();
        $applicantFullName = $fellapp->getApplicantFullName();

        $applicant = $fellapp->getUser();

        $senderEmail = $userSecUtil->getSiteSettingParameter('confirmationEmailFellApp');

        //$localInstitutionFellApp = $userSecUtil->getSiteSettingParameter('localInstitutionFellApp'); //Pathology Fellowship Programs (WCMC)
        $localInstitutionFellApp = "Weill Cornell Medical College / New York Presbyterian Hospital";

        $identificationUploadLetterFellApp = $userSecUtil->getSiteSettingParameter('identificationUploadLetterFellApp'); //55555
        if( !$identificationUploadLetterFellApp ) {
            $identificationUploadLetterFellApp = "55555";
        }

        //testing
        //$fellapp = new FellowshipApplication();
        //$reference = new Reference();
        //$geoLocation = new GeoLocation();

        $refInst = $reference->getInstitution();
        if( $refInst ) {
            $refInstStr = $refInst->getName();
        } else {
            $refInstStr = NULL;
        }

        //get upload form link with parameters
        //http://wcmc.pathologysystems.org/fellowship-application-reference-letter-upload
        //?
        //Reference-Letter-ID=0000000110c8357966576df46f3b802ca897deb7ad18b12f1c24ecff6386ebd9
        //&Applicant-First-Name=John
        //&Applicant-Last-Name=Smith
        //&Applicant-E-Mail=john@smith.com
        //&Fellowship-Type=Cytopathology
        //&Fellowship-Start-Date=07-01-2018
        //&Fellowship-End-Date=07-01-2019
        //&Reference-First-Name=Joe
        //&Reference-Last-Name=Doe
        //&Reference-Degree=Doctor
        //&Reference-Title=Professor
        //&Reference-Institution=McGill
        //&Reference-Phone=123-345-6789
        //&Reference-EMail=refemail@email.com
        //&Reference-Street1=5th%20Avenue
        //&Reference-Street2=App%20B
        //&Reference-City=NYC
        //&Reference-State=New%20York
        //&Reference-Zip=12345
        //&Reference-Country=USA
        $uploadFormLink = "http://wcmc.pathologysystems.org/fellowship-application-reference-letter-upload/?";
        $uploadFormLink = $uploadFormLink . "Reference-Letter-ID=" . $reference->getRecLetterHashId();
        $uploadFormLink = $uploadFormLink . "&Identification=" . $identificationUploadLetterFellApp;
        $uploadFormLink = $uploadFormLink . "&Applicant-First-Name=" . $applicant->getFirstName();
        $uploadFormLink = $uploadFormLink . "&Applicant-Last-Name=" . $applicant->getLastName();
        $uploadFormLink = $uploadFormLink . "&Applicant-E-Mail=" . $applicant->getSingleEmail();
        $uploadFormLink = $uploadFormLink . "&Fellowship-Type=" . $fellapp->getFellowshipSubspecialty()->getName();
        $uploadFormLink = $uploadFormLink . "&Fellowship-Start-Date=" . $fellapp->getStartDate()->format("m/d/Y");
        $uploadFormLink = $uploadFormLink . "&Fellowship-End-Date=" . $fellapp->getEndDate()->format("m/d/Y");
        $uploadFormLink = $uploadFormLink . "&Reference-First-Name=" . $reference->getFirstName();
        $uploadFormLink = $uploadFormLink . "&Reference-Last-Name=" . $reference->getName();
        $uploadFormLink = $uploadFormLink . "&Reference-Degree=" . $reference->getDegree();
        $uploadFormLink = $uploadFormLink . "&Reference-Title=" . $reference->getTitle();
        $uploadFormLink = $uploadFormLink . "&Reference-Institution=" . $refInstStr;
        $uploadFormLink = $uploadFormLink . "&Reference-Phone=" . $reference->getPhone();
        $uploadFormLink = $uploadFormLink . "&Reference-EMail=" . $reference->getEmail();
        if( $geoLocation ) {

            $state = $geoLocation->getState();
            if( $state ) {
                $stateStr = $state->getName();
            } else {
                $stateStr = NULL;
            }

            $uploadFormLink = $uploadFormLink . "&Reference-Street1=" . $geoLocation->getStreet1();
            $uploadFormLink = $uploadFormLink . "&Reference-Street2=" . $geoLocation->getStreet2();
            $uploadFormLink = $uploadFormLink . "&Reference-City=" . $geoLocation->getCity();
            $uploadFormLink = $uploadFormLink . "&Reference-State=" . $stateStr;
            $uploadFormLink = $uploadFormLink . "&Reference-Zip=" . $geoLocation->getZip();
            $uploadFormLink = $uploadFormLink . "&Reference-Country=" . $geoLocation->getCountry();
        }

        $uploadFormLink = '<a href="'.$uploadFormLink.'">'.$uploadFormLink.'</a>';

        //ApplicantFirstName ApplicantLastName has listed you ReferenceFirstName ReferenceLastName
        // as a reference in their FellowshipType fellowship application.
        // Please submit your recommendation letter to Weill Cornell Medical College / New York Presbyterian Hospital.
        $subject = $applicantFullName . " has listed you " . $referenceFullName
            . " as a reference in their ".$fellappTypeStr." fellowship application."
            . " Please submit your recommendation letter to $localInstitutionFellApp."
        ;

        //check the degree of the recommendation letter author; if it equals "MD", "md", "PhD", "m.d.", "Ph.D", "Ph.D.", or "MD/PhD", insert "Dr. "
        $degreeStr = "";
        $degreeReference = strtolower($reference->getDegree());
        if(
            strpos($degreeReference, 'md') !== false
            || strpos($degreeReference, 'm.d.') !== false
            || strpos($degreeReference, 'phd') !== false
            || strpos($degreeReference, 'ph.d') !== false
            || strpos($degreeReference, 'dr.') !== false
        ) {
            $degreeStr = "Dr. ";
        }

        $body =
            "Dear ".$degreeStr."$referenceFullName,"
            . "<br><br>"
            . "$applicantFullName has applied to the $fellappTypeStr fellowship at $localInstitutionFellApp"
            . " for the year $startDateStr and listed you as a reference."
            . "<br>"
            . "We review complete applications as they are received and your timely submission of your recommendation letter will increase"
            . " " . $applicantFullName . "'s chances of being accepted."
            . "<br>" . "Please use the link below to submit your recommendation letter as soon as possible:"
            . "<br><br>" . $uploadFormLink
            . "<br><br>" . "If you have any issues with submitting your letter, please contact"
            . " Elizabeth Hammerschmidt (our fellowship program coordinator) at eah2006@med.cornell.edu for alternative methods of submitting your recommendation letter."
            . "<br><br>" . "If you believe you have received this email in error please let Elizabeth Hammerschmidt know."
            . "<br><br><br>" . "Sincerely,"
            . "<br><br>" . "Elizabeth Hammerschmidt"
            . "<br>" . "Fellowship Program Coordinator"
            . "<br>" . "Weill Cornell Medicine Pathology and Laboratory Medicine"
            . "<br>" . "1300 York Avenue, Room C-302"
            . "<br>" . "New York, NY 10065  "
            . "<br>" . "T 212.746.7365"
            . "<br>" . "F 212.746.8192"
        ;

        $emailUtil->sendEmail(
            $email,
            $subject,
            $body,
            $senderEmail, //$cc
            $senderEmail
        );

        //increment counter
        $counter = $reference->getInvitationSentEmailCounter();
        if( !$counter ) {
            $counter = 0;
        }
        $counter = $counter + 1;
        $reference->setInvitationSentEmailCounter($counter);
        if( $flush ) {
            $this->em->flush($reference);
        }

//        $this->container->get('session')->getFlashBag()->add(
//            'notice',
//            "Invitation email has been sent to ".$reference->getFullName()
//        );

        $msg = "Invitation email to submit a letter of recommendation has been sent to ".$reference->getFullName() . " (".$email.")";

        //eventlog
        $eventMsg = $msg . "<br><br> Subject:<br>". $subject . "<br><br>Body:<br>" . $body;
//        $user = NULL;
//        if( $this->container->get('security.token_storage')->getToken() ) {
//            $user = $this->container->get('security.token_storage')->getToken()->getUser();
//        }
//        if( $user instanceof User) {
//            //User OK - do nothing
//        } else {
//            $user = $userSecUtil->findSystemUser();
//        }
//        if( !$user ) {
//            $user = $userSecUtil->findSystemUser();
//        }
//        $userSecUtil->createUserEditEvent(
//            $this->container->getParameter('fellapp.sitename'), //$sitename
//            $eventMsg,                                          //$event message
//            $user,                                              //user
//            $fellapp,                                           //$subjectEntities
//            null,                                               //$request
//            "Reference Invitation Email"                        //$action
//        );
        $this->sendLetterEventLog($eventMsg,"Reference Invitation Email",$fellapp);

        $res = array(
            "res" => true,
            "msg" => $msg
        );

        return $res;
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

        if(0) { //do not create event log every time on import attempt
            //Event Logger with event type "Import of Fellowship Applications Spreadsheet". It will be used to get lastImportTimestamps
            $userSecUtil = $this->container->get('user_security_utility');
            $systemUser = $userSecUtil->findSystemUser();
            $eventTypeStr = "Import of Fellowship Recommendation Letters";
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'), $result, $systemUser, null, null, $eventTypeStr);
        }

        return $result;
    }

    //1)  Import sheets from Google Drive
    //1a)   import all sheets from Google Drive folder
    //1b)   add successefull downloaded sheets to DataFile DB object with status "active"
    public function importSheetsFromGoogleDriveFolder() {

        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        $logger = $this->container->get('logger');
        $logger->notice("Start importing spreadsheet with reference letter info from Google Drive");

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
        //echo "letterSpreadsheetFolder: Title=".$letterSpreadsheetFolder->getTitle()."; ID=".$letterSpreadsheetFolder->getId()."<br>";
        
        //exit("exit importSheetsFromGoogleDriveFolder");

        //get all files in google folder
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $files = $googlesheetmanagement->retrieveFilesByFolderId($letterSpreadsheetFolder->getId(),$service);
        //echo "files count=".count($files)."<br>";

        //Download files to the server
        $documentType = "Fellowship Recommendation Letter Spreadsheet";
        $path = 'Uploaded'.'/'.'fellapp/RecommendationLetters/Spreadsheets';
        foreach( $files as $file ) {
            //echo 'File Id: ' . $file->getId() . "; title=" . $file->getTitle() . "<br>";
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
            //echo "File already exists <br>";
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
        $logger = $this->container->get('logger');
        $logger->notice("Start importing reference letter info from Google Drive");

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
        //echo "letterFolder: Title=".$letterFolder->getTitle()."; ID=".$letterFolder->getId()."<br>";

        //get all files in google folder
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $files = $googlesheetmanagement->retrieveFilesByFolderId($letterFolder->getId(),$service);
        //echo "files count=".count($files)."<br>";

        //Download files to the server
        $importedLetters = array();
        $documentType = "Fellowship Recommendation Letter";
        $path = 'Uploaded'.'/'.'fellapp/RecommendationLetters/RecommendationLetterUploads';
        foreach( $files as $file ) {
            //echo 'File Id: ' . $file->getId() . "; title=" . $file->getTitle() . "<br>";
            //Download file from Google Drive to the server without creating document entity
            //$googlesheetmanagement->printFile($service, $file->getId());
            $documentDb = $this->processSingleLetter($service,$file,$documentType,$path);
            if( $documentDb ) {
                $importedLetters[] = $documentDb;
            }
        }

        //exit("Exit importLetterFromGoogleDriveFolder");

        return $importedLetters;
    }
    public function processSingleLetter( $service, $file, $documentType, $path ) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        //$emailUtil = $this->container->get('user_mailer_utility');
        $systemUser = $userSecUtil->findSystemUser();
        //$environment = $userSecUtil->getSiteSettingParameter('environment');

        $testing = false;
        //$testing = true;

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
            //echo "letter already exists with document ID=".$documentDb->getId()."<br>";
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

        //ID_datetime_name.ext: 55555_0000000110c8357966576df46f3b802ca897deb7ad18b12f1c24ecff6386ebd9_2019-04-03-13-13-17_Cat-Wa.jpg
        $letterArr = explode("_",$file->getTitle());
        //echo "letterArr count=".count($letterArr)."<br>";
        if( count($letterArr) == 4 ) {
            $instituteIdentification = $letterArr[0];
            $refId = $letterArr[1];
            //$latestLetterDatetime = $letterArr[2];
            //$name = $letterArr[3];
        } else {
            return NULL;
        }

        if( $testing ) {
            $instituteIdentification = "55555";
            $refId = "340d08a7c8037b62e5e0e36b1119486f2dd00540";
            //$latestLetterDatetime = "2019-04-03-13-13-17";
            //$name = "filenameee";
        }

        //10d: compare instituteIdentification with site settings instituteIdentification
        $identificationUploadLetterFellApp = $userSecUtil->getSiteSettingParameter('identificationUploadLetterFellApp'); //i.e. 55555
        $logger->notice("compare: $identificationUploadLetterFellApp ?= $instituteIdentification");
        if( $identificationUploadLetterFellApp != $instituteIdentification ) {
            //send email
            $msg = "Fellowship identification string in the letter file name ($instituteIdentification) does not match with the site settings ($identificationUploadLetterFellApp)";
            $userSecUtil->sendEmailToSystemEmail($msg,$msg);
            //eventlog
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$msg,$systemUser,null,null,"No Recommendation Letters");
            return NULL;
        }

        //find application and reference by reference ID
        //echo "search by ref ID=".$refId."<br>";
        $references = $this->em->getRepository('OlegFellAppBundle:Reference')->findByRecLetterHashId($refId);
        //echo "references count=".count($references)."<br>";

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

            //add this letter to this reference
            $reference->addDocument($uploadedLetterDb);

            $this->em->flush($reference);

            $this->checkReferenceAlreadyHasLetter($fellapp,$reference);

            $this->checkAndSendCompleteEmail($fellapp);

            //TODO: update application PDF:
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            //async generation
            $fellappRepGen->addFellAppReportToQueue( $fellapp->getId(), 'overwrite' );
            //sync generation
            //$res = $fellappRepGen->generateFellAppReport( $fellapp->getId() );

            //echo "filename=".$res['filename']."<br>";

            return $uploadedLetterDb;
        } //if count($references) == 1


        return NULL;
    }

    //check if this reference already has a letter
    public function checkReferenceAlreadyHasLetter($fellapp,$reference,$latestLetterDatetime=null) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        //$fellapp = $reference->getFellapp();
        $applicant = $fellapp->getUser();
        $applicantName = "Unknown Applicant";
        if( $applicant ) {
            $applicantName = $applicant->getUsernameOptimal();
        }
        $startDate = $fellapp->getStartDate();
        $startDateStr = null;
        if( $startDate ) {
            $startDateStr = $startDate->format('Y');
        }

        //check if this reference already has a letter
        $letters = $reference->getDocuments();

        //echo "letters count=".count($letters)."<br>";
        //This check is done after the letter has been added, therefore check if number of letters more than 1
        if( count($letters) > 1 ) {

            $router = $userSecUtil->getRequestContextRouter();

            $subject = "More than one recommendation letter received from ".$reference->getFullName()." in support of "
                .$applicantName."'s application ".$fellapp->getId()." for the ".$fellapp->getFellowshipSubspecialty()." $startDateStr fellowship";

            //TODO: get CreatedTime. Not in file's metadata.
            //$latestLetterTime = $file->getCreatedTime();
            //use $datetime from the filename
//            $latestLetterTimeStr = NULL;
//            if( $latestLetterDatetime ) {
//                //2019-04-03-13-13-17
//                $timeArr = explode("-",$latestLetterDatetime);
//                if( count($timeArr) == 6 ) {
//                    //m/d/Y H:i
//                    $latestLetterTimeStr = $timeArr[1]."/".$timeArr[2]."/".$timeArr[0]. " at " . $timeArr[3].":".$timeArr[4];
//                }
//            }
//            if( !$latestLetterTimeStr ) {
//                $latestLetterTime = new \DateTime();
//                $latestLetterTimeStr = $latestLetterTime->format("m/d/Y H:i");
//            }

            $latestLetter = $letters->last();
            if( $latestLetter ) {
                $latestLetterCreatedDate = $latestLetter->getCreatedate();
                if ($latestLetterCreatedDate) {
                    //$latestLetterCreatedDateStr = "submitted on " . $latestLetterCreatedDate->format('m/d/Y \a\t H:i');
                    $latestLetterTimeStr = $latestLetterCreatedDate->format('m/d/Y \a\t H:i');
                } else {
                    //$latestLetterCreatedDateStr = "";
                    $nowDateTime = new \DateTime();
                    $latestLetterTimeStr = $nowDateTime->format("m/d/Y H:i");
                }
            }

            $body = "More than one recommendation letter has been received from ".$reference->getFullName()." in support of "
                .$applicantName."'s application ".$fellapp->getId()." for the ".$fellapp->getFellowshipSubspecialty()." $startDateStr fellowship.";
            $body = $body . " The latest document was received on ".$latestLetterTimeStr.".";
            $body = $body . "<br><br>" . "Please review these letters of recommendation and delete any duplicates or erroneously added documents.";

            //You can review the letter 1 here: LINKtoLETTER1. You can review the letter 2 here: LINKtoLETTER2. You can review the letter 3 here: LINKtoLETTER3.
            $reviewLetterArr = array();

            //You can review the latest letter submitted on MM/DD/YYYY at HH/MM here: https://localhost/fellowship-applications/file-download/XXXXX
            //$latestLetter = $letters->last();
            if( $latestLetter ) {
                //$latestLetterCreatedDate = $latestLetter->getCreatedate();
                //if( $latestLetterCreatedDate ) {
                //    $latestLetterCreatedDateStr = "submitted on " . $latestLetterTimeStr;   //$latestLetterCreatedDate->format('m/d/Y \a\t H:i');
                //} else {
                //    $latestLetterCreatedDateStr = "";
                //}
                $latestLetterCreatedDateStr = "submitted on " . $latestLetterTimeStr;

                $latestLetterLink = $router->generate(
                    'fellapp_file_download',
                    array('id' => $latestLetter->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $latestLetterLink = '<a href="'.$latestLetterLink.'">'.$latestLetterLink.'</a>';
                $reviewLetterArr[] = "You can review the latest letter " . $latestLetterCreatedDateStr . " here: " . $latestLetterLink . "<br>";
            }

            $counter = 1;
            foreach($letters as $letter) {
                $letterLink = $router->generate(
                    'fellapp_file_download',
                    array('id' => $letter->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $letterLink = '<a href="'.$letterLink.'">'.$letterLink.'</a>';
                $letterCreatedDate = $letter->getCreatedate();
                if( $letterCreatedDate ) {
                    $letterCreatedDateStr = "submitted on ".$letterCreatedDate->format('m/d/Y \a\t H:i');
                } else {
                    $letterCreatedDateStr = $counter;
                }
                $reviewLetterArr[] = "You can review the letter ".$letterCreatedDateStr." here: " . $letterLink;
                $counter++;
            }

            $body = $body . "<br><br>" . implode("<br>",$reviewLetterArr);

            //You can review the entire application here: LINKtoAPPLICATION.
            $fellappLink = $router->generate(
                'fellapp_show',
                array('id' => $fellapp->getId()),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $logger->notice("fellappLink=".$fellappLink);
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

            //echo "Email sent: $subject <br><br><br> $body <br>";

        } //if count($letters) > 0

        return count($letters);
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

    public function checkAndSendCompleteEmail($fellapp) {
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $applicant = $fellapp->getUser();
        $applicantName = "Unknown Applicant";
        if( $applicant ) {
            $applicantName = $applicant->getUsernameOptimal();
        } else {
            return false;
        }

        $startDate = $fellapp->getStartDate();
        $startDateStr = null;
        if( $startDate ) {
            $startDateStr = $startDate->format('m/d/Y');
        } else {
            return false;
        }

        $router = $userSecUtil->getRequestContextRouter();

        //16- Add a check to the letter import mechanism at the end:
        // if the application now has all 3 of 3 letters uploaded as a
        // result of the import that just occurred, set the status of the application
        // to "Complete" and send an email to both the corresponding
        // (FellowshipType) Program Coordinator(s) and Program Director(s)

        //12d1- Every time a new recommendation letter is successfully imported,
        // check if the current status of the application is either "Active" or "Priority" AND the quantity of the letters based
        // ONLY on the status of the checked "[ ] Recommendation Letter Received" boxes in that application is more than 2 (3 or more),
        // and for these applications automatically change their status to "Complete".

        //Check for the presence of the letter or checkbox

        $allHasLetter = true;
        $refCounter = 0;
        $reviewLetterLinkArr = array();
        foreach( $fellapp->getReferences() as $thisReference ) {
            $existingLetters = $thisReference->getDocuments();
            if( count($existingLetters) > 0 || $thisReference->getRecLetterReceived() ) {
                $refCounter++;
                //YLINKtoLETTER1.
                $existingLetter = $existingLetters->last(); //$existingLetters[0];
                $letterLink = $router->generate(
                    'fellapp_file_download',
                    array('id' => $existingLetter->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $letterLink = '<a href="'.$letterLink.'">'.$letterLink.'</a>';
                $reviewLetterLinkArr[] = $letterLink;
            } else {
                $allHasLetter = false;
            }
        }

        ///////// send an email to both the corresponding (FellowshipType) Program Coordinator(s) and Program Director(s) ///////////
        if( $allHasLetter ) {

            //if status is not "Complete"
            $originalStatus = $fellapp->getAppStatus();
            $originalStatusStr = NULL;
            if( $originalStatus ) {
                $originalStatusStr = $originalStatus->getAction();
            }
            if( $originalStatusStr && $originalStatusStr != "Complete" ) {
                $statusStr = 'The application status has been changed from "'.$originalStatusStr.'" to "Complete".';
            } else {
                $statusStr = 'The application status has been changed to "Complete".';
            }

            //set Status to "Complete"
            if( $originalStatusStr != "Complete" ) {
                $completeStatus = $this->em->getRepository('OlegFellAppBundle:FellAppStatus')->findOneByName("complete");
                if (!$completeStatus) {
                    throw new EntityNotFoundException('Unable to find FellAppStatus by name=' . "complete");
                }
                $fellapp->setAppStatus($completeStatus);
                $this->em->flush($fellapp);
            }

            //Subject: ApplicantFirstName ApplicantLastName's FellowshipType FellowshipYear fellowship application is now complete!
            //$subject = $applicantName . "'s " . $fellapp->getFellowshipSubspecialty() . " " . $startDateStr . " fellowship application is now complete!";

            //"Subject="3 recommendation letters have now been received for ApplicantFirstName ApplicantsLastName's application ID#XXX for the FellowshipType FellowshipYear"
            $subject =
                $refCounter . " recommendation letters have now been received for "
                . $applicantName . "'s application ID#" . $fellapp->getId()
                . " for the " . $fellapp->getFellowshipSubspecialty() . " " . $startDateStr
            ;

            //Body: We have received all X reference letters in support of
            // ApplicantFirstName ApplicantLastName's FellowshipType FellowshipYear fellowship application.
            // The application status has been changed from "OLDSTATUS" to "Complete".
            // You can review the recommendation letters here:
            // LINKtoLETTER 1, LINKtoLETTER 2, LINKtoLETTER 3.
            // You can review the entire application here: LINKtoAPPLICATION.

            $fellappLink = $router->generate(
                'fellapp_show',
                array('id' => $fellapp->getId()),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $fellappLink = '<a href="'.$fellappLink.'">'.$fellappLink.'</a>';

//            $body = "We have received all $refCounter reference letters in support of " . $applicantName . "'s "
//                . $fellapp->getFellowshipSubspecialty() . " " . $startDateStr . " fellowship application."
//                . "<br>".$statusStr
//                . "<br><br>"."You can review the recommendation letters here:"
//                . "<br>".implode("<br>",$reviewLetterLinkArr)
//                . "<br><br>"."You can review the entire application here:"
//                . "<br>".$fellappLink
//            ;

            //Body: 3 of [N] recommendation letters have now been received for [ApplicantFirstName] [ApplicantsLastName]'s
            // application [ID#XXX] for the [FellowshipType] [FellowshipYear] fellowship.
            // The application in PDF format is attached for your review.

            $body = $subject . " fellowship."
                //. " The application in PDF format is attached for your review." //PDF will not be ready by this time when email is sent.
                . "<br><br>"."You can review the entire application here:"
                . "<br>".$fellappLink
            ;

            //echo "send email: <br>subject=".$subject."<br><br>body=".$body."<br>";

            $fellappUtil = $this->container->get('fellapp_util');
            $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'),"Administrator");
            $coordinatorEmails = $fellappUtil->getCoordinatorsOfFellAppEmails($fellapp);
            $directorEmails = $fellappUtil->getCoordinatorsOfFellAppEmails($fellapp);
            $coordinatorDirectorEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));
            $emailUtil->sendEmail($coordinatorDirectorEmails,$subject,$body,$ccs);
        } else {
            //echo "No allHasLetter. refCounter=$refCounter <br>";
        }
        ///////// EOF send an email to both the corresponding (FellowshipType) Program Coordinator(s) and Program Director(s) ///////////

        return true;
    }

    //send invitation email to upload recommendation letter to references
    public function sendInvitationEmailsToReferences( $fellapp, $flush=false ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $sendEmailUploadLetterFellApp = $userSecUtil->getSiteSettingParameter('sendEmailUploadLetterFellApp');
        if( $sendEmailUploadLetterFellApp ) {

            //check for duplicates or if one of the reference email is missing
            //1) check for missing email
            $missingEmail = false;
            foreach($fellapp->getReferences() as $reference) {
                if( !$reference->getEmail() ) {
                    $missingEmail = true;
                }
            }

            //2) check for duplicates (same FellowshipType, same FellowshipYear, with the same ApplicantEmail )
            $applicant = $fellapp->getUser();
            if( $applicant ) {
                $applicantEmail = $applicant->getSingleEmail();
            } else {
                $applicantEmail = null;
                $errorMsg = "Logical Error: Applicantion ID#".$fellapp->getId()." does not have applicant email";
                $this->sendEmailToSystemEmail($errorMsg, $errorMsg);
                return false;
            }

            $duplicates = false;
            $repository = $this->em->getRepository('OlegFellAppBundle:FellowshipApplication');
            $dql = $repository->createQueryBuilder("fellapp");
            $dql->select('fellapp');
            $dql->leftJoin("fellapp.fellowshipSubspecialty", "fellowshipSubspecialty");
            $dql->leftJoin("fellapp.user", "user");
            $dql->where("fellowshipSubspecialty.id = :fellowshipSubspecialtyId");
            $dql->andWhere("user.email = :applicantEmail");
            $dql->andWhere("fellapp.id != :fellappId");

            //startDate
            $startDate = $fellapp->getStartDate();
            $startDateStr = $startDate->format('Y');
            $bottomDate = $startDateStr."-01-01";
            $topDate = $startDateStr."-12-31";
            $dql->andWhere("fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" );

            $query = $this->em->createQuery($dql);

            $query->setParameters(array(
                "fellowshipSubspecialtyId" => $fellapp->getFellowshipSubspecialty()->getId(),
                "applicantEmail" => $applicantEmail,
                "fellappId" => $fellapp->getId()
            ));

            $duplicateFellapps = $query->getResult();
            if( count($duplicateFellapps) > 0 ) {
                $duplicates = true;
            }

            if( $duplicates || $missingEmail ) {
                //email to the Program Coordinator
                $fellappId = $fellapp->getId();
                $fellappUtil = $this->container->get('fellapp_util');
                $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('fellapp.sitename'),"Administrator");
                $coordinatorEmails = $fellappUtil->getCoordinatorsOfFellAppEmails($fellapp);
                $directorEmails = $fellappUtil->getCoordinatorsOfFellAppEmails($fellapp);
                $coordinatorDirectorEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));

                if ($missingEmail) {
                    //No reference letter upload invitations sent automatically for fellowship application
                    // ID XXX since it does not have 3 reference letter author emails.
                    // Please invite reference letter authors manually for this application
                    // using the Action button if desired.
                    $subject = "No reference letter upload invitations sent automatically for fellowship application ID $fellappId";
                    $body = $subject
                        ." since it does not have ".count($fellapp->getReferences())." reference letter author emails."
                        ." Please invite reference letter authors manually for this application using the Action button if desired.";

                    $emailUtil->sendEmail($coordinatorDirectorEmails,$subject,$body,$ccs);

                    $this->sendLetterEventLog($body,"No Reference Invitation Email",$fellapp);

                    return false;
                }

                if ($duplicates) {
                    $duplicatesInfos = array();
                    foreach($duplicateFellapps as $duplicateFellapp) {
                        $duplicatesInfos[] = $duplicateFellapp->getId();
                    }
                    //No reference letter upload invitations sent automatically for fellowship application
                    // ID XXX since it appears to be a duplicate of application ID YYY.
                    // Please invite reference letter authors manually for this application
                    // using the Action button if desired.
                    $subject = "No reference letter upload invitations sent automatically for fellowship application ID $fellappId";
                    $body = $subject
                        ." since it appears to be a duplicate of application(s) ".implode(", ",$duplicatesInfos)."."
                        ." Please invite reference letter authors manually for this application using the Action button if desired.";

                    $emailUtil->sendEmail($coordinatorDirectorEmails,$subject,$body,$ccs);

                    $this->sendLetterEventLog($body,"No Reference Invitation Email",$fellapp);

                    return false;
                }
            } //if( $duplicates || $missingEmail )

            //send invitation email to references to submit letters
            $resArr = array();
            foreach ($fellapp->getReferences() as $reference) {
                if( count($reference->getDocuments()) == 0 ) {
                    //send invitation email
                    //echo $fellapp->getId().": send invitation email for reference ID=".$reference->getId()."<br>";
                    $resArr[] = $this->inviteSingleReferenceToSubmitLetter($reference,$fellapp,$flush);
                }
            }

            return $resArr;
        }//if sendEmailUploadLetterFellApp

        return false;
    }

    public function sendLetterEventLog($msg,$eventType,$fellapp) {
        $userSecUtil = $this->container->get('user_security_utility');

        $user = NULL;
        if( $this->container->get('security.token_storage')->getToken() ) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
        }
        if( $user instanceof User) {
            //User OK - do nothing
        } else {
            $user = $userSecUtil->findSystemUser();
        }
        if( !$user ) {
            $user = $userSecUtil->findSystemUser();
        }

        $userSecUtil->createUserEditEvent(
            $this->container->getParameter('fellapp.sitename'), //$sitename
            $msg,                                               //$event message
            $user,                                              //user
            $fellapp,                                           //$subjectEntities
            null,                                               //$request
            $eventType                                          //$action
        );
    }

}