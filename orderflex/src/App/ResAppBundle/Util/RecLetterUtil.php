<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/1/2019
 * Time: 11:31 AM
 */

namespace App\ResAppBundle\Util;



use App\ResAppBundle\Entity\ResidencyApplication; //process.py script: replaced namespace by ::class: added use line for classname=ResidencyApplication


use App\ResAppBundle\Entity\Reference; //process.py script: replaced namespace by ::class: added use line for classname=Reference


use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document


use App\ResAppBundle\Entity\ResAppStatus; //process.py script: replaced namespace by ::class: added use line for classname=ResAppStatus
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

class RecLetterUtil {

    protected $em;
    protected $container;
    protected $security;
    protected $uploadDir;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;
        $this->uploadDir = 'Uploaded';
    }

    //Generate hash ID only once when application is created.
    //This hash ID will be used to auto attach recommendation letter to the reference's application.
    public function generateResappRecLetterId( $resapp, $flush=false ) {
        $logger = $this->container->get('logger');
        foreach($resapp->getReferences() as $reference) {
            $hash = $this->generateRecLetterId($resapp,$reference);
            if( $hash ) {
                $reference->setRecLetterHashId($hash);
                $logger->notice($resapp->getId()." (".$reference->getId()."): added hash=".$hash);
                if( $flush ) {
                    $this->em->flush($reference);
                    $logger->notice($resapp->getId()." (".$reference->getId()."): flushed with an added hash=".$hash);
                }
                //echo $resapp->getId()." (".$reference->getId()."): added hash=".$hash."<br>";
            }
        }
    }

    //Recommendation Letter Salted Script Hash ID
    public function generateRecLetterId( $resapp, $reference, $request=null, $count=0 ) {
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //$str = "pepperstr";

        $salt = $userSecUtil->getSiteSettingParameter('recLetterSaltResApp');
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
            //$url = $request->getSchemeAndHttpHost();
            //replace $request->getSchemeAndHttpHost() with getRealSchemeAndHttpHost($request)
            $userUtil = $this->container->get('user_utility');
            $url = $userUtil->getRealSchemeAndHttpHost($request);
        }
        if( !$url ) {
            $url = $userSecUtil->getSiteSettingParameter('environment');
        }

        // Organizational Group of the received application +
        $institution = $resapp->getInstitution();
        if( $institution ) {
            $institutionId = $institution->getId();
        } else {
            $institutionId = NULL;
        }

        // Residency Type of the Application +
        $type = $resapp->getResidencyTrack();
        if( $type ) {
            $typeId = $type->getId();
        } else {
            $typeId = NULL;
        }

        // Application ID +
        $resappId = $resapp->getId();
        if( !$resappId ) {
            $resappId = microtime(true);
        }

        // Application Timestamp +
        $timestamp = $resapp->getTimestamp();
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

        $str = $url . $institutionId . $typeId . $resappId . $timestampStr . $referenceId . $referenceEmail . $salt . $count;

        //use if (hash_equals($knownString, $userInput)) to compare two hash (or php password_verify)
        //$hash = md5($str);
        //$hash = sha1($str);
        $hash = hash("sha1",$str); //sha1
        //$hash = password_hash($str,PASSWORD_DEFAULT);
        //echo "Hash=".$hash."<br>";
        $logger->notice($resappId."(".$referenceId.", count=".$count."): Generated hash=".$hash);

        //check for uniqueness
        if( $hash ) {
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:Reference'] by [Reference::class]
            $references = $this->em->getRepository(Reference::class)->findByRecLetterHashId($hash);
            if( count($references) > 0 ) {
                $count = $count + 1;
                $hash = $this->generateRecLetterId( $resapp, $reference, $request, $count );
            }
        }

        return $hash;
    }

    public function inviteSingleReferenceToSubmitLetter( $reference, $resapp=null, $flush=true ) {

        $emailUtil = $this->container->get('user_mailer_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment != 'live' ) {
            $msg = "Server is not live: invitation email for residency application will not be send to reference ".$reference->getFullName();
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$resapp);
            $res = array(
                "res" => false,
                "msg" => $msg
            );
            return $res;
        }

        $sendEmailUploadLetterResApp = $userSecUtil->getSiteSettingParameter('sendEmailUploadLetterResApp');
        if( !$sendEmailUploadLetterResApp ) {
            $msg = "Automatically send invitation emails to upload recommendation letters is set to NO: invitation email will not be send to reference ".$reference->getFullName();
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$resapp);
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
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$resapp);
            $res = array(
                "res" => false,
                "msg" => $msg
            );
            return $res;
        }

        if(1) { //Generate hash ID here if empty. (It must be pre-generated before?)
            if( !$reference->getRecLetterHashId() ) {
                $resappRecLetterUtil = $this->container->get('resapp_rec_letter_util');
                $hash = $resappRecLetterUtil->generateRecLetterId($resapp, $reference);
                if ($hash) {
                    $reference->setRecLetterHashId($hash);
                    $this->em->flush($reference);
                    //echo $resapp->getId()." (".$reference->getId()."): added hash=".$hash."<br>";
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
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$resapp);
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
            $this->sendLetterEventLog($msg,"No Reference Invitation Email",$resapp);
            $res = array(
                "res" => false,
                "msg" => $msg
            );
            return $res;
        }

        $referenceFullName = $reference->getFullName();


        $logger->notice("Invite reference $referenceFullName to send invitation letter");

        if( !$resapp ) {
            $resapp = $reference->getResapp();
        }

        $resappType = $resapp->getResidencyTrack();
        if( $resappType ) {
            $resappTypeStr = $resappType->getName();
        } else {
            $resappTypeStr = null;
        }

        $startDate = $resapp->getStartDate();
        if( $startDate ) {
            $startDateStr = $startDate->format('Y');
        } else {
            $startDateStr = NULL;
        }

        $geoLocation = $reference->getGeoLocation();
        $applicantFullName = $resapp->getApplicantFullName();

        $applicant = $resapp->getUser();

        $senderEmail = $userSecUtil->getSiteSettingParameter('confirmationEmailResApp',$this->getParameter('resapp.sitename'));

        //$localInstitutionResApp = $userSecUtil->getSiteSettingParameter('localInstitutionResApp'); //Pathology Residency Programs (WCMC)
        $localInstitutionResApp = "Weill Cornell Medical College / New York Presbyterian Hospital";

        $identificationUploadLetterResApp = $userSecUtil->getSiteSettingParameter('identificationUploadLetterResApp',$this->container->getParameter('resapp.sitename')); //55555
        if( !$identificationUploadLetterResApp ) {
            $identificationUploadLetterResApp = "55555";
        }

        //testing
        //$resapp = new ResidencyApplication();
        //$reference = new Reference();
        //$geoLocation = new GeoLocation();

        $refInst = $reference->getInstitution();
        if( $refInst ) {
            $refInstStr = $refInst->getName();
        } else {
            $refInstStr = NULL;
        }

        //get upload form link with parameters
        //http://wcmc.pathologysystems.org/residency-application-reference-letter-upload
        //?
        //Reference-Letter-ID=0000000110c8357966576df46f3b802ca897deb7ad18b12f1c24ecff6386ebd9
        //&Applicant-First-Name=John
        //&Applicant-Last-Name=Smith
        //&Applicant-E-Mail=john@smith.com
        //&Residency-Type=Cytopathology
        //&Residency-Start-Date=07-01-2018
        //&Residency-End-Date=07-01-2019
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
        $uploadFormLink = "http://wcmc.pathologysystems.org/residency-application-reference-letter-upload/?";
        $uploadFormLink = $uploadFormLink . "Reference-Letter-ID=" . $reference->getRecLetterHashId();
        $uploadFormLink = $uploadFormLink . "&Identification=" . $identificationUploadLetterResApp;
        $uploadFormLink = $uploadFormLink . "&Applicant-First-Name=" . $applicant->getFirstName();
        $uploadFormLink = $uploadFormLink . "&Applicant-Last-Name=" . $applicant->getLastName();
        $uploadFormLink = $uploadFormLink . "&Applicant-E-Mail=" . $applicant->getSingleEmail();
        $uploadFormLink = $uploadFormLink . "&Residency-Type=" . $resapp->getResidencyTrack()->getName();
        $uploadFormLink = $uploadFormLink . "&Residency-Start-Date=" . $resapp->getStartDate()->format("m/d/Y");
        $uploadFormLink = $uploadFormLink . "&Residency-End-Date=" . $resapp->getEndDate()->format("m/d/Y");
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
        // as a reference in their ResidencyType residency application.
        // Please submit your recommendation letter to Weill Cornell Medical College / New York Presbyterian Hospital.
        $subject = $applicantFullName . " has listed you " . $referenceFullName
            . " as a reference in their ".$resappTypeStr." residency application."
            . " Please submit your recommendation letter to $localInstitutionResApp."
        ;

        //check the degree of the recommendation letter author; if it equals "MD", "md", "PhD", "m.d.", "Ph.D", "Ph.D.", or "MD/PhD", insert "Dr. "
        $degreeStr = "";
        $degreeReference = strtolower($reference->getDegree());
        if(
            strpos((string)$degreeReference, 'md') !== false
            || strpos((string)$degreeReference, 'm.d.') !== false
            || strpos((string)$degreeReference, 'phd') !== false
            || strpos((string)$degreeReference, 'ph.d') !== false
            || strpos((string)$degreeReference, 'dr.') !== false
        ) {
            $degreeStr = "Dr. ";
        }

        $body =
            "Dear ".$degreeStr."$referenceFullName,"
            . "<br><br>"
            . "$applicantFullName has applied to the $resappTypeStr residency at $localInstitutionResApp"
            . " for the year $startDateStr and listed you as a reference."
            . "<br>"
            . "We review complete applications as they are received and your timely submission of your recommendation letter will increase"
            . " " . $applicantFullName . "'s chances of being accepted."
            . "<br>" . "Please use the link below to submit your recommendation letter as soon as possible:"
            . "<br><br>" . $uploadFormLink
            . "<br><br>" . "If you have any issues with submitting your letter, please contact"
            . " Jessica Misner (our residency program coordinator) at jep2018@med.cornell.edu for alternative methods of submitting your recommendation letter."
            . "<br><br>" . "If you believe you have received this email in error please let Jessica Misner know."
            . "<br><br><br>" . "Sincerely,"
            . "<br><br>" . "Jessica Misner"
            . "<br>" . "Residency Program Coordinator"
            . "<br>" . "Weill Cornell Medicine Pathology and Laboratory Medicine"
            . "<br>" . "1300 York Avenue, Room C-302"
            . "<br>" . "New York, NY 10065  "
            . "<br>" . "T 212.746.6464"
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
//            $this->container->getParameter('resapp.sitename'), //$sitename
//            $eventMsg,                                          //$event message
//            $user,                                              //user
//            $resapp,                                           //$subjectEntities
//            null,                                               //$request
//            "Reference Invitation Email"                        //$action
//        );
        $this->sendLetterEventLog($eventMsg,"Reference Invitation Email",$resapp);

        $res = array(
            "res" => true,
            "msg" => $msg
        );

        return $res;
    }

    public function processResRecLetterFromGoogleDrive() {
        //1) Import sheets from Google Drive Folder
        $filesGoogleDrive = $this->importSheetsFromGoogleDriveFolder();

        //2) Import recommendation letter from Google Drive Folder
        $filesGoogleDrive = $this->importLetterFromGoogleDriveFolder();

        //2) Populate applications from DataFile DB object
        $populatedCount = $this->populateApplicationsFromDataFile();

        //3) Delete old sheet and uploads from Google Drive if deleteOldAplicationsResApp is true
        $deletedSheetCount = $this->deleteSuccessfullyImportedApplications();

        //4)  Process backup sheet on Google Drive
        $populatedBackupApplications = $this->processBackupResAppFromGoogleDrive();

        //$resappRepGen = $this->container->get('resapp_reportgenerator');
        //$generatedReport = $resappRepGen->tryRun(); //run hard run report generation

        //exit('eof processResAppFromGoogleDrive');

        $filesGoogleDriveCount = "N/A";
        if( $filesGoogleDrive ) {
            $filesGoogleDriveCount = count($filesGoogleDrive);
        }

        $populatedBackupApplicationsCount = "N/A";
        if( $populatedBackupApplications ) {
            $populatedBackupApplicationsCount = count($populatedBackupApplications);
        }

        $result = "Finish processing Residency Recommendation Letters on Google Drive and on server.<br>".
            "filesGoogleDrive=".$filesGoogleDriveCount.", populatedCount=".$populatedCount.
            ", deletedSheetCount=".$deletedSheetCount.", populatedBackupApplications=".$populatedBackupApplicationsCount
            //.", First generated report in queue=".$generatedReport
        ;

        $logger = $this->container->get('logger');
        $logger->notice($result);

        if(0) { //do not create event log every time on import attempt
            //Event Logger with event type "Import of Residency Applications Spreadsheet". It will be used to get lastImportTimestamps
            $userSecUtil = $this->container->get('user_security_utility');
            $systemUser = $userSecUtil->findSystemUser();
            $eventTypeStr = "Import of Residency Recommendation Letters";
            $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'), $result, $systemUser, null, null, $eventTypeStr);
        }

        return $result;
    }

    //1)  Import sheets from Google Drive
    //1a)   import all sheets from Google Drive folder
    //1b)   add successefull downloaded sheets to DataFile DB object with status "active"
    public function importSheetsFromGoogleDriveFolder() {

        $resappImportPopulateUtil = $this->container->get('resapp_importpopulate_util');
        $logger = $this->container->get('logger');
        $logger->notice("Start importing spreadsheet with reference letter info from Google Drive");

        if( !$resappImportPopulateUtil->checkIfResappAllowed("Import from Google Drive") ) {
            //exit("can't import");
            //return null;
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        //get Google service
        $googlesheetmanagement = $this->container->get('resapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            $logger->warning($event);
            $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'),$event,$systemUser,null,null,'Error');
            $this->sendEmailToSystemEmail($event, $event);
            return null;
        }

        //echo "service ok <br>";

        $folderIdResAppId = $userSecUtil->getSiteSettingParameter('configFileFolderIdResApp',$this->container->getParameter('resapp.sitename'));
        if( !$folderIdResAppId ) {
            $logger->warning('Google Drive Folder ID is not defined in Site Parameters. configFileFolderIdResApp='.$folderIdResAppId);
        }

        //find folder by name
        $letterSpreadsheetFolder = $googlesheetmanagement->findOneRecLetterSpreadsheetFolder($service,$folderIdResAppId);
        //echo "letterSpreadsheetFolder: Title=".$letterSpreadsheetFolder->getTitle()."; ID=".$letterSpreadsheetFolder->getId()."<br>";
        
        //exit("exit importSheetsFromGoogleDriveFolder");

        //get all files in google folder
        $googlesheetmanagement = $this->container->get('resapp_googlesheetmanagement');
        $files = $googlesheetmanagement->retrieveFilesByFolderId($letterSpreadsheetFolder->getId(),$service);
        //echo "files count=".count($files)."<br>";

        //Download files to the server
        $documentType = "Residency Recommendation Letter Spreadsheet";
        $path = 'Uploaded'.'/'.'resapp/RecommendationLetters/Spreadsheets';
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

        //$root = $this->container->get('kernel')->getRootDir();
        //$fullpath = $root . '/../public/'.$path;
        $fullpath = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $path;
        $target_file = $fullpath . DIRECTORY_SEPARATOR . $file->getTitle() . $fileExtStr;

        //check if file already exists by file path
        if( file_exists($target_file) ) {
            //echo "File already exists <br>";
            return NULL;
        }

        $googlesheetmanagement = $this->container->get('resapp_googlesheetmanagement');
        $response = $googlesheetmanagement->downloadFile($service,$file,$documentType);
        if( !$response ) {
            throw new IOException('Error Rec Letter file response is empty: file id='.$file->getId());
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
        $resappImportPopulateUtil = $this->container->get('resapp_importpopulate_util');
        $logger = $this->container->get('logger');
        $logger->notice("Start importing reference letter info from Google Drive");

        if( !$resappImportPopulateUtil->checkIfResappAllowed("Import from Google Drive") ) {
            //exit("can't import");
            //return null;
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        //get Google service
        $googlesheetmanagement = $this->container->get('resapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            $logger->warning($event);
            $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'),$event,$systemUser,null,null,'Error');
            $this->sendEmailToSystemEmail($event, $event);
            return null;
        }

        //echo "service ok <br>";

        $folderIdResAppId = $userSecUtil->getSiteSettingParameter('configFileFolderIdResApp');
        if( !$folderIdResAppId ) {
            $logger->warning('Google Drive Folder ID is not defined in Site Parameters. configFileFolderIdResApp='.$folderIdResAppId);
        }

        //find folder by name
        $letterFolder = $googlesheetmanagement->findOneRecLetterUploadFolder($service,$folderIdResAppId);
        //echo "letterFolder: Title=".$letterFolder->getTitle()."; ID=".$letterFolder->getId()."<br>";
        //$logger->notice("Getting reference letters from folder ID=".$letterFolder->getId());

        //get all files in google folder
        $googlesheetmanagement = $this->container->get('resapp_googlesheetmanagement');
        $files = $googlesheetmanagement->retrieveFilesByFolderId($letterFolder->getId(),$service);
        //echo "files count=".count($files)."<br>";
        //$logger->notice("Found ".count($files)." reference letters from folder ID=".$letterFolder->getId());

        //Download files to the server
        $importedLetters = array();
        $documentType = "Residency Recommendation Letter";
        $path = 'Uploaded'.'/'.'resapp/RecommendationLetters/RecommendationLetterUploads';
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
        $googlesheetmanagement = $this->container->get('resapp_googlesheetmanagement');
        $emailUtil = $this->container->get('user_mailer_utility');
        $systemUser = $userSecUtil->findSystemUser();
        //$environment = $userSecUtil->getSiteSettingParameter('environment');

        $testing = false;
        //$testing = true;

//        //test
//        $subject = "More than one recommendation letter received from "."RefName"." in support of
//                "."ApplicantName"."'s application ID#"."ResappId"." for the "."ResType"." StartDate residency";
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
//        $emails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('resapp.sitename'),"Administrator");
//        $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('resapp.sitename'),"Platform Administrator");
//        if( !$emails ) {
//            $emails = $ccs;
//            $ccs = null;
//        }
//        $emailUtil->sendEmail( $emails, $subject, $body, $ccs );
//        //test

        $filesize = $file->getFileSize();
        if( $filesize == 0 ) {
            $logger->error("Error processing reference letter with title=".$file->getTitle().": size is zero, filesize=".$filesize);
            //Send email to admin

            //send email
            $msg = "Error processing reference letter with title ".$file->getTitle().": reference file is empty";
            //$userSecUtil->sendEmailToSystemEmail($msg, $msg);

            /////////////////
//            $adminEmails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('resapp.sitename'),"Administrator");
//            $platformAdminEmails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('resapp.sitename'),"Platform Administrator");
//            if( $platformAdminEmails ) {
//                $adminEmails = array_merge($adminEmails,$platformAdminEmails);
//            }
//            $adminEmails = array_unique($adminEmails);
//            $emailUtil->sendEmail( $adminEmails, $msg, $msg );
            /////////////////

            //eventlog
            $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'), $msg, $systemUser, null, null, "No Recommendation Letters");

            return NULL;
        }

        //check if file already exists by file id
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $documentDb = $this->em->getRepository(Document::class)->findOneByUniqueid($file->getId());
        if( $documentDb && $documentType != 'Residency Application Backup Spreadsheet' ) {
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
        //TODO: use $file->getCreatedTime for creation date? (https://developers.google.com/drive/api/v3/reference/files#createdTime)
        $uploadedLetterDb = $googlesheetmanagement->downloadFileToServer($systemUser,$service,$file->getId(),$documentType,$path);
        if( !$uploadedLetterDb ) {
            throw new IOException('Unable to download file to server: fileID='.$uploadedLetterDb->getId());
        }

        //$residencyApplication->addReprimandDocument($uploadedLegalExplanationUrlDb);

        //ID_datetime_name.ext: 55555_0000000110c8357966576df46f3b802ca897deb7ad18b12f1c24ecff6386ebd9_2019-04-03-13-13-17_Cat-Wa.jpg
        $letterDatetimeStr = null;
        $letterArr = explode("_",$file->getTitle());
        //echo "letterArr count=".count($letterArr)."<br>";
        if( count($letterArr) >= 3 ) {
            $instituteIdentification = $letterArr[0];
            $refId = $letterArr[1];
            $letterDatetimeStr = $letterArr[2];
            //$name = $letterArr[3];
        } else {
            return NULL;
        }

        if( $testing ) {
            $instituteIdentification = "55555";
            $refId = "340d08a7c8037b62e5e0e36b1119486f2dd00540";
            $letterDatetimeStr = "2019-04-03-13-13-17";
            //$name = "filenameee";
        }

        //TODO: use $file->getCreatedTime for creation date? (https://developers.google.com/drive/api/v3/reference/files#createdTime)
        //Use wcmpath_4924679d8f452cfe52d3cdfe_2019-07-17-09-37-59.pdf
        //2019-07-17-09-37-59 : year-month-day-hour-minute-second
        if( $letterDatetimeStr ) {
            $letterDateTime = date_create_from_format('Y-m-d-H-i-s', $letterDatetimeStr);
            $uploadedLetterDb->setExternalCreatedate($letterDateTime);
            $this->em->flush($uploadedLetterDb);
        }

        //10d: compare instituteIdentification with site settings instituteIdentification
        $identificationUploadLetterResApp = $userSecUtil->getSiteSettingParameter('identificationUploadLetterResApp'); //i.e. 55555
        $logger->notice("compare: $identificationUploadLetterResApp ?= $instituteIdentification");
        if( $instituteIdentification && $identificationUploadLetterResApp ) {
            if ($identificationUploadLetterResApp != $instituteIdentification) {
                //send email
                $msg = "Residency identification string in the letter file name ($instituteIdentification) does not match with the site settings ($identificationUploadLetterResApp)";
                $userSecUtil->sendEmailToSystemEmail($msg, $msg);
                //eventlog
                $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'), $msg, $systemUser, null, null, "No Recommendation Letters");
                return NULL;
            }
        }

        //find application and reference by reference ID
        //echo "search by ref ID=".$refId."<br>";
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:Reference'] by [Reference::class]
        $references = $this->em->getRepository(Reference::class)->findByRecLetterHashId($refId);
        //echo "references count=".count($references)."<br>";

        //not found
        if( count($references) == 0 ) {
            //send email (on live server only)
            $msg = "No residency references found by letter ID=" . $refId;
            $environment = $userSecUtil->getSiteSettingParameter('environment');
            if( $environment == 'live' ) { //send email (on live server only)
                $userSecUtil->sendEmailToSystemEmail($msg, $msg);
            }
            //eventlog
            $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'),$msg,$systemUser,null,null,"No Recommendation Letters");
            return NULL;
        }

        //can't be more than 1
        if( count($references) > 1 ) {
            //send email
            $msg = "Error: Multiple " . count($references) . " residency references found by letter ID=".$refId;
            $userSecUtil->sendEmailToSystemEmail($msg,$msg);
            //eventlog
            $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'),$msg,$systemUser,null,null,"Multiple Recommendation Letters");
            return NULL;
        }

        //Good: only one reference corresponds to the hash Id
        if( count($references) == 1 ) {
            $reference = $references[0];
            $resapp = $reference->getResapp();
            $newLetter = true;

            //add this letter to this reference
            //if letter already exists, use identical($fileOne, $fileTwo) to see if the letters are identical.
            //If identical don't add to the reference object
            if(1) {
                $letters = $reference->getDocuments();
                if (count($letters) > 0) {
                    $uploadedLetterDbPath = $uploadedLetterDb->getServerPath();
                    $fileTwoHash = hash_file('md5', $uploadedLetterDbPath);
                    //loop over all existing letter and compare
                    foreach ($letters as $thisLetter) {
                        $thisLetterPath = $thisLetter->getServerPath();
                        $identical = $this->checkIfFilesIdentical($thisLetterPath,$uploadedLetterDbPath,$fileTwoHash);
                        if( $identical ) {
                            $newLetter = false;
                            break;
                        }
                    }
                }
            }

            if( $newLetter ) {
                $reference->addDocument($uploadedLetterDb);
                $this->em->flush($reference);

                $this->sendRefLetterReceivedNotificationEmail($resapp,$uploadedLetterDb);

                $this->checkReferenceAlreadyHasLetter($resapp,$reference);

                $this->checkAndSendCompleteEmail($resapp);

                //update application PDF:
                $resappRepGen = $this->container->get('resapp_reportgenerator');
                //async generation
                $resappRepGen->addResAppReportToQueue( $resapp->getId(), 'overwrite' );
                //sync generation
                //$res = $resappRepGen->generateResAppReport( $resapp->getId() );
                //echo "filename=".$res['filename']."<br>";
                return $uploadedLetterDb;
            } else {
                $msg = "New letter ".$file->getTitle()." is identical to already existed letters for the application ID ".$resapp->getId();
                $logger->notice($msg);
                $userSecUtil->sendEmailToSystemEmail($msg,$msg);
                //eventlog
                $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'),$msg,$systemUser,null,null,"Multiple Recommendation Letters");
                return NULL;
            }

            return $uploadedLetterDb;
        } //if count($references) == 1


        return NULL;
    }

    public function getGoogleFileCreationDatetime($service, $fileId) {
        $file = null;
        try {
            $file = $service->files->get($fileId);
        } catch (\Exception $e) {
            throw new IOException('Google API: Unable to get file by file id='.$fileId.". An error occurred: " . $e->getMessage());
        }

        $fileId = $file->getId();
        //echo "fileId=".$fileId."<br>";

        $fileTitle = $file->getTitle();
        echo "fileTitle=".$fileTitle.": ";

        $createdTime = null;
        //$createdTime = $file->getCreatedTime();

        if( $createdTime ) {
            echo "getCreatedTime(): createdTime=".$createdTime."<br>";
        } else {

            if(1) {
                $letterDatetimeStr = null;
                $letterArr = explode("_", $fileTitle);
                //echo "letterArr count=".count($letterArr)."<br>";
                if (count($letterArr) >= 3) {
                    // $instituteIdentification = $letterArr[0];
                    //$refId = $letterArr[1];
                    $letterDatetimeStr = $letterArr[2];
                    //$name = $letterArr[3];
                } else {
                    return NULL;
                }
            }

            //$letterDatetimeStr = "2019-04-03-13-13-17";

            if( $letterDatetimeStr ) {
                $letterDateTime = date_create_from_format('Y-m-d-H-i-s', $letterDatetimeStr);
                echo "getTitle(): createdTime=".$letterDateTime->format("Y-m-d H:i:s")."<br>";
            } else {
                echo "getTitle(): $letterDatetimeStr is NULL<br>";
            }

        }

        return null;
    }

    //check if this reference already has a letter
    public function checkReferenceAlreadyHasLetter($resapp,$reference) {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        //$resapp = $reference->getResapp();
        $applicant = $resapp->getUser();
        $applicantName = "Unknown Applicant";
        if( $applicant ) {
            $applicantName = $applicant->getUsernameOptimal();
        }
        $startDate = $resapp->getStartDate();
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
                .$applicantName."'s application ".$resapp->getId()." for the ".$resapp->getResidencyTrack()." $startDateStr residency";

            //use download datetime as letter datetime
            $latestLetterId = null;
            $latestLetter = $letters->last();
            if( $latestLetter ) {
                $latestLetterId = $latestLetter->getId();
                $latestLetterCreatedDate = $latestLetter->getExternalOrDbCreatedate();
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
                .$applicantName."'s application ".$resapp->getId()." for the ".$resapp->getResidencyTrack()." $startDateStr residency.";
            $body = $body . " The latest document was received on ".$latestLetterTimeStr.".";
            $body = $body . "<br><br>" . "Please review these letters of recommendation and delete any duplicates or erroneously added documents.";

            //You can review the letter 1 here: LINKtoLETTER1. You can review the letter 2 here: LINKtoLETTER2. You can review the letter 3 here: LINKtoLETTER3.
            $reviewLetterArr = array();

            //You can review the latest letter submitted on MM/DD/YYYY at HH/MM here: https://localhost/residency-applications/file-download/XXXXX
            if( $latestLetter ) {
                $latestLetterCreatedDateStr = "submitted on " . $latestLetterTimeStr;

                $latestLetterLink = $router->generate(
                    'resapp_file_download',
                    array('id' => $latestLetter->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $latestLetterLink = '<a href="'.$latestLetterLink.'">'.$latestLetterLink.'</a>';
                $reviewLetterArr[] = "You can review the latest letter " . $latestLetterCreatedDateStr . " here: " . $latestLetterLink . "<br>";
            }

            $counter = 1;
            foreach($letters as $letter) {
                if( $latestLetterId != $letter->getId() ) {
                    $letterLink = $router->generate(
                        'resapp_file_download',
                        array('id' => $letter->getId()),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $letterLink = '<a href="' . $letterLink . '">' . $letterLink . '</a>';
                    $letterCreatedDate = $letter->getExternalOrDbCreatedate();
                    if ($letterCreatedDate) {
                        $letterCreatedDateStr = "submitted on " . $letterCreatedDate->format('m/d/Y \a\t H:i');
                    } else {
                        $letterCreatedDateStr = $counter;
                    }
                    $reviewLetterArr[] = "You can review the letter " . $letterCreatedDateStr . " here: " . $letterLink;
                    $counter++;
                }
            }

            $body = $body . "<br><br>" . implode("<br>",$reviewLetterArr);

            //You can review the entire application here: LINKtoAPPLICATION.
            $resappLink = $router->generate(
                'resapp_show',
                array('id' => $resapp->getId()),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $logger->notice("resappLink=".$resappLink);
            $resappLink = '<a href="'.$resappLink.'">'.$resappLink.'</a>';
            $body = $body . "<br><br>" . "You can review the entire application here: ".$resappLink;

            //get emails: coordinators and directors
            $resappUtil = $this->container->get('resapp_util');
            $directorEmails = $resappUtil->getDirectorsOfResAppEmails($resapp);
            $coordinatorEmails = $resappUtil->getCoordinatorsOfResAppEmails($resapp);
            $emails = array_unique(array_merge ($coordinatorEmails, $directorEmails));

            //get CCs
            $ccAdminEmails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('resapp.sitename'),"Administrator");
            $ccPlatformAdminEmails = $userSecUtil->getUserEmailsByRole($this->container->getParameter('resapp.sitename'),"Platform Administrator");
            if( $ccPlatformAdminEmails ) {
                $ccAdminEmails = array_merge($ccAdminEmails,$ccPlatformAdminEmails);
            }
            $ccAdminEmails = array_unique($ccAdminEmails);

            if( !$emails || count($emails) == 0 ) {
                $emails = $ccAdminEmails;
                $ccAdminEmails = null;
            }

            $emailUtil->sendEmail( $emails, $subject, $body, $ccAdminEmails );

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

    public function processBackupResAppFromGoogleDrive() {
        return array();
    }

    public function sendRefLetterReceivedNotificationEmail($resapp,$uploadedLetterDb) {

        if( !$uploadedLetterDb ) {
            return "Reference letter is null";
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');
        $resappUtil = $this->container->get('resapp_util');
        $systemUser = $userSecUtil->findSystemUser();

        $applicant = $resapp->getUser();
        if( $applicant ) {
            $applicantName = $applicant->getUsernameOptimal();
        } else {
            return false;
        }

        $startDate = $resapp->getStartDate();
        $startDateStr = null;
        if( $startDate ) {
            $startDateStr = $startDate->format('m/d/Y');
        } else {
            return false;
        }

        $router = $userSecUtil->getRequestContextRouter();

        $letterLink = $router->generate(
            'resapp_file_download',
            array('id' => $uploadedLetterDb->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $letterLink = '<a href="'.$letterLink.'">'.$letterLink.'</a>';

        $subject =
            "A new recommendation letter has been received for "
            . $applicantName . "'s application ID#" . $resapp->getId()
            . " for the " . $resapp->getResidencyTrack() . " " . $startDateStr
        ;

        $resappLink = $router->generate(
            'resapp_show',
            array('id' => $resapp->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $resappLink = '<a href="'.$resappLink.'">'.$resappLink.'</a>';

        $body = $subject . " residency."
            . "<br><br>"."You can review this recommendation letter (attached) here:"
            . "<br>".$letterLink
            . "<br><br>"."You can review the entire application here:"
            . "<br>".$resappLink
        ;

        $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('resapp.sitename'),"Administrator");
        $coordinatorEmails = $resappUtil->getCoordinatorsOfResAppEmails($resapp);
        //echo "coordinatorEmails=".implode("; ",$coordinatorEmails)."<br>";
        $directorEmails = $resappUtil->getDirectorsOfResAppEmails($resapp);
        //echo "directorEmails=".implode("; ",$directorEmails)."<br>";
        $coordinatorDirectorEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));

        //add ref letter as an attachment
        $attachmentPath = null;
        if( $uploadedLetterDb ) {
            //$attachmentPath = $uploadedLetterDb->getAbsoluteUploadFullPath();
            $attachmentPath = $uploadedLetterDb->getAttachmentEmailPath(); //test is implemented
            //echo "attachmentPath=[$attachmentPath]<br>";
        }

        if( 1 ) {
            $emailUtil->sendEmail(
                $coordinatorDirectorEmails,
                $subject,
                $body,
                $ccs,
                null,
                $attachmentPath
            );
        }

        $body = $body . "<br>" . "Emails: " . implode("; ",$coordinatorDirectorEmails) . "<br>" . "CCs: " . implode("; ", $ccs);

        //eventlog
        $userSecUtil->createUserEditEvent($this->container->getParameter('resapp.sitename'),$body,$systemUser,$resapp,null,"Recommendation Letter Received");

        return $body;
    }

    public function checkAndSendCompleteEmail($resapp) {
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');

        $applicant = $resapp->getUser();
        $applicantName = "Unknown Applicant";
        if( $applicant ) {
            $applicantName = $applicant->getUsernameOptimal();
        } else {
            return false;
        }

        $startDate = $resapp->getStartDate();
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
        // (ResidencyType) Program Coordinator(s) and Program Director(s)

        //12d1- Every time a new recommendation letter is successfully imported,
        // check if the current status of the application is either "Active" or "Priority" AND the quantity of the letters based
        // ONLY on the status of the checked "[ ] Recommendation Letter Received" boxes in that application is more than 2 (3 or more),
        // and for these applications automatically change their status to "Complete".

        //Check for the presence of the letter or checkbox

        $allHasLetter = true;
        $refCounter = 0;
        $reviewLetterLinkArr = array();
        foreach( $resapp->getReferences() as $thisReference ) {
            $existingLetters = $thisReference->getDocuments();
            if( count($existingLetters) > 0 || $thisReference->getRecLetterReceived() ) {
                $refCounter++;
                //YLINKtoLETTER1.
                $existingLetter = $existingLetters->last(); //$existingLetters[0];
                $letterLink = $router->generate(
                    'resapp_file_download',
                    array('id' => $existingLetter->getId()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $letterLink = '<a href="'.$letterLink.'">'.$letterLink.'</a>';
                $reviewLetterLinkArr[] = $letterLink;
            } else {
                $allHasLetter = false;
            }
        }

        ///////// send an email to both the corresponding (ResidencyType) Program Coordinator(s) and Program Director(s) ///////////
        if( $allHasLetter ) {

            //if status is not "Complete"
            $originalStatus = $resapp->getAppStatus();
            $originalStatusStr = NULL;
            if( $originalStatus ) {
                $originalStatusStr = $originalStatus->getAction();
            }

//            if( $originalStatusStr && $originalStatusStr != "Complete" ) {
//                $statusStr = 'The application status has been changed from "'.$originalStatusStr.'" to "Complete".';
//            } else {
//                $statusStr = 'The application status has been changed to "Complete".';
//            }

            //set Status to "Complete"
            if( $originalStatusStr != "Complete" ) {
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResAppStatus'] by [ResAppStatus::class]
                $completeStatus = $this->em->getRepository(ResAppStatus::class)->findOneByName("complete");
                if (!$completeStatus) {
                    throw new EntityNotFoundException('Unable to find ResAppStatus by name=' . "complete");
                }
                $resapp->setAppStatus($completeStatus);
                $this->em->flush($resapp);
            }

            //Subject: ApplicantFirstName ApplicantLastName's ResidencyType ResidencyYear residency application is now complete!
            //$subject = $applicantName . "'s " . $resapp->getResidencyTrack() . " " . $startDateStr . " residency application is now complete!";

            //"Subject="3 recommendation letters have now been received for ApplicantFirstName ApplicantsLastName's application ID#XXX for the ResidencyType ResidencyYear"
            $subject =
                $refCounter . " recommendation letters have now been received for "
                . $applicantName . "'s application ID#" . $resapp->getId()
                . " for the " . $resapp->getResidencyTrack() . " " . $startDateStr
            ;

            //Body: We have received all X reference letters in support of
            // ApplicantFirstName ApplicantLastName's ResidencyType ResidencyYear residency application.
            // The application status has been changed from "OLDSTATUS" to "Complete".
            // You can review the recommendation letters here:
            // LINKtoLETTER 1, LINKtoLETTER 2, LINKtoLETTER 3.
            // You can review the entire application here: LINKtoAPPLICATION.

            $resappLink = $router->generate(
                'resapp_show',
                array('id' => $resapp->getId()),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $resappLink = '<a href="'.$resappLink.'">'.$resappLink.'</a>';

//            $body = "We have received all $refCounter reference letters in support of " . $applicantName . "'s "
//                . $resapp->getResidencyTrack() . " " . $startDateStr . " residency application."
//                . "<br>".$statusStr
//                . "<br><br>"."You can review the recommendation letters here:"
//                . "<br>".implode("<br>",$reviewLetterLinkArr)
//                . "<br><br>"."You can review the entire application here:"
//                . "<br>".$resappLink
//            ;

            //Body: 3 of [N] recommendation letters have now been received for [ApplicantFirstName] [ApplicantsLastName]'s
            // application [ID#XXX] for the [ResidencyType] [ResidencyYear] residency.
            // The application in PDF format is attached for your review.

            $body = $subject . " residency."
                //. " The application in PDF format is attached for your review." //PDF will not be ready by this time when email is sent.
                . "<br><br>"."You can review the entire application here:"
                . "<br>".$resappLink
            ;

            //echo "send email: <br>subject=".$subject."<br><br>body=".$body."<br>";

            $resappUtil = $this->container->get('resapp_util');
            $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('resapp.sitename'),"Administrator");
            $coordinatorEmails = $resappUtil->getCoordinatorsOfResAppEmails($resapp);
            $directorEmails = $resappUtil->getDirectorsOfResAppEmails($resapp);
            $coordinatorDirectorEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));
            $emailUtil->sendEmail($coordinatorDirectorEmails,$subject,$body,$ccs);
        } else {
            //echo "No allHasLetter. refCounter=$refCounter <br>";
        }
        ///////// EOF send an email to both the corresponding (ResidencyType) Program Coordinator(s) and Program Director(s) ///////////

        return true;
    }

    //NOT USED in resapp: There are no reference letters to upload for resapp. It was derived from fellapp
    //send invitation email to upload recommendation letter to references
    public function sendInvitationEmailsToReferences( $resapp, $flush=false ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $emailUtil = $this->container->get('user_mailer_utility');
        //$userServiceUtil = $this->container->get('user_service_utility');
        $resappUtil = $this->container->get('resapp_util');

        $sendEmailUploadLetterResApp = $userSecUtil->getSiteSettingParameter('sendEmailUploadLetterResApp');
        //$sendEmailUploadLetterResApp = true;
        if( $sendEmailUploadLetterResApp ) {

            //check for duplicates or if one of the reference email is missing
            //1) check for missing email
            $missingEmail = false;
            foreach($resapp->getReferences() as $reference) {
                if( !$reference->getEmail() ) {
                    $missingEmail = true;
                }
            }

            //2) check for duplicates (same ResidencyType, same ResidencyYear, with the same ApplicantEmail )
            $applicant = $resapp->getUser();
            if( $applicant ) {
                $applicantEmail = $applicant->getSingleEmail();
            } else {
                $applicantEmail = null;
                $errorMsg = "Logical Error: Applicantion ID#".$resapp->getId()." does not have applicant email";
                $this->sendEmailToSystemEmail($errorMsg, $errorMsg);
                return false;
            }

            $duplicates = false;
        //process.py script: replaced namespace by ::class: ['AppResAppBundle:ResidencyApplication'] by [ResidencyApplication::class]
            $repository = $this->em->getRepository(ResidencyApplication::class);
            $dql = $repository->createQueryBuilder("resapp");
            $dql->select('resapp');
            $dql->leftJoin("resapp.residencyTrack", "residencyTrack");
            $dql->leftJoin("resapp.user", "user");
            $dql->where("residencyTrack.id = :residencyTrackId");
            $dql->andWhere("user.email = :applicantEmail");
            $dql->andWhere("resapp.id != :resappId");

            //startDate
            $startDate = $resapp->getStartDate(); //Residency Start Year
            $startDateStr = $startDate->format('Y');
            //$startDate = $startDateStr."-01-01";
            //$endDate = $startDateStr."-12-31";

            //TODO: test start year (NOTUSED for resapp)
            $startEndDates = $resappUtil->getAcademicYearStartEndDates($startDateStr);
            $startDate = $startEndDates['startDate'];
            $endDate = $startEndDates['endDate'];
            //echo "startDate=".$startDate.", endDate=".$endDate."<br>";

            //$startEndDates = $resappUtil->getResAppAcademicYearStartEndDates($startDateStr);
            //$startDate = $startEndDates['Residency Start Date'];
            //$endDate = $startEndDates['Residency End Date'];
            //echo "startDate=".$startDate.", endDate=".$endDate."<br>";
            //exit('111');

            $dql->andWhere("resapp.startDate BETWEEN '" . $startDate . "'" . " AND " . "'" . $endDate . "'" );

            $query = $dql->getQuery();

            $query->setParameters(array(
                "residencyTrackId" => $resapp->getResidencyTrack()->getId(),
                "applicantEmail" => $applicantEmail,
                "resappId" => $resapp->getId()
            ));

            $duplicateResapps = $query->getResult();
            if( count($duplicateResapps) > 0 ) {
                $duplicates = true;
            }

            if( $duplicates || $missingEmail ) {
                //email to the Program Coordinator
                $resappId = $resapp->getId();
                $resappUtil = $this->container->get('resapp_util');
                $ccs = $userSecUtil->getUserEmailsByRole($this->container->getParameter('resapp.sitename'),"Administrator");
                $coordinatorEmails = $resappUtil->getCoordinatorsOfResAppEmails($resapp);
                $directorEmails = $resappUtil->getDirectorsOfResAppEmails($resapp);
                $coordinatorDirectorEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));

                if ($missingEmail) {
                    //No reference letter upload invitations sent automatically for residency application
                    // ID XXX since it does not have 3 reference letter author emails.
                    // Please invite reference letter authors manually for this application
                    // using the Action button if desired.
                    $subject = "No reference letter upload invitations sent automatically for residency application ID $resappId";
                    $body = $subject
                        ." since it does not have ".count($resapp->getReferences())." reference letter author emails."
                        ." Please invite reference letter authors manually for this application using the Action button if desired.";

                    $emailUtil->sendEmail($coordinatorDirectorEmails,$subject,$body,$ccs);

                    $this->sendLetterEventLog($body,"No Reference Invitation Email",$resapp);

                    return false;
                }

                if ($duplicates) {
                    $duplicatesInfos = array();
                    foreach($duplicateResapps as $duplicateResapp) {
                        $duplicatesInfos[] = $duplicateResapp->getId();
                    }
                    //No reference letter upload invitations sent automatically for residency application
                    // ID XXX since it appears to be a duplicate of application ID YYY.
                    // Please invite reference letter authors manually for this application
                    // using the Action button if desired.
                    $subject = "No reference letter upload invitations sent automatically for residency application ID $resappId";
                    $body = $subject
                        ." since it appears to be a duplicate of application(s) ".implode(", ",$duplicatesInfos)."."
                        ." Please invite reference letter authors manually for this application using the Action button if desired.";

                    $emailUtil->sendEmail($coordinatorDirectorEmails,$subject,$body,$ccs);

                    $this->sendLetterEventLog($body,"No Reference Invitation Email",$resapp);

                    return false;
                }
            } //if( $duplicates || $missingEmail )

            //send invitation email to references to submit letters
            $resArr = array();
            foreach ($resapp->getReferences() as $reference) {
                if( count($reference->getDocuments()) == 0 ) {
                    //send invitation email
                    //echo $resapp->getId().": send invitation email for reference ID=".$reference->getId()."<br>";
                    $resArr[] = $this->inviteSingleReferenceToSubmitLetter($reference,$resapp,$flush);
                }
            }

            return $resArr;
        }//if sendEmailUploadLetterResApp

        return false;
    }

    public function sendLetterEventLog($msg,$eventType,$resapp) {
        $userSecUtil = $this->container->get('user_security_utility');

        $user = $this->security->getUser();
        if( !$user ) {
            $user = $userSecUtil->findSystemUser();
        }

        $userSecUtil->createUserEditEvent(
            $this->container->getParameter('resapp.sitename'), //$sitename
            $msg,                                               //$event message
            $user,                                              //user
            $resapp,                                           //$subjectEntities
            null,                                               //$request
            $eventType                                          //$action
        );
    }

    //check files in case of multiple letters and do not send "More than one " email if the letters are identical
    public function checkIfFilesIdentical($fileOne, $fileTwo, $fileTwoHash=null) {
        $identical = $this->identicalFilesByBites($fileOne,$fileTwo); //first level check by bites
        if( $identical ) {
            //additional, more precise check by using the hash
            if( !$fileTwoHash ) {
                $fileTwoHash = hash_file('md5', $fileTwo);
            }
            $fileOneHash = hash_file('md5', $fileOne);
            if( $fileOneHash == $fileTwoHash ) {
                return true; //identical
            } else {
                return false; //different
            }
        } else {
            return false;
        }
        return false;
    }
    /**
     * Check if two files are identical.
     *
     * If you just need to find out if two files are identical, comparing file
     * hashes can be inefficient, especially on large files.  There's no
     * reason to read two whole files and do all the math if the
     * second byte of each file is different.  If you don't need to
     * store the hash value for later use, there may not be a need to
     * calculate the hash value just to compare files.This can be much faster.
     *
     * @link http://www.php.net/manual/en/function.md5-file.php#94494
     *
     * @param string $fileOne
     * @param string $fileTwo
     * @return boolean
     */
    public function identicalFilesByBites($fileOne, $fileTwo)
    {
        if (filetype($fileOne) !== filetype($fileTwo)) return false;
        if (filesize($fileOne) !== filesize($fileTwo)) return false;

        if (! $fp1 = fopen($fileOne, 'rb')) return false;

        if (! $fp2 = fopen($fileTwo, 'rb'))
        {
            fclose($fp1);
            return false;
        }

        $same = true;

        while (! feof($fp1) and ! feof($fp2))
            if (fread($fp1, 4096) !== fread($fp2, 4096))
            {
                $same = false;
                break;
            }

        if (feof($fp1) !== feof($fp2)) $same = false;

        fclose($fp1);
        fclose($fp2);

        return $same;
    }

}