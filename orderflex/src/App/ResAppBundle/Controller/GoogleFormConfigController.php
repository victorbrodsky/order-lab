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

namespace App\ResAppBundle\Controller;

use App\ResAppBundle\Entity\GoogleFormConfig;
use App\ResAppBundle\Form\GoogleFormConfigType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleFormConfigController extends OrderAbstractController
{

    #[Route(path: '/form-status-and-appearance/edit', name: 'resapp_google_form_config_edit', methods: ['GET', 'PUT'])]
    #[Route(path: '/form-status-and-appearance/show', name: 'resapp_google_form_config_show', methods: ['GET', 'PUT'])]
    #[Template('AppResAppBundle/GoogleFormConfig/google-form-config.html.twig')]
    public function GoogleFormConfigAction(Request $request) {

        if( $this->isGranted('ROLE_RESAPP_ADMIN') === false ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $routeName = $request->get('_route');

        $cycle = "show";

        if( $routeName == "resapp_google_form_config_edit" ) {
            $cycle = "edit";
        }

        //$configs = $em->getRepository("AppResAppBundle:GoogleFormConfig")->findAll();
        $configs = $em->getRepository(GoogleFormConfig::class)->findAll();
        if( count($configs) > 0 ) {
            $entity = $configs[0];
        } else {
            $entity = new GoogleFormConfig();
            //throw $this->createNotFoundException('Unable to find Google Residency Application Form Configuration');
        }

        $form = $this->createGoogleFormConfigForm($entity,$cycle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            //exit("save");

            $em->persist($entity);
            $em->flush();

            $event = "Google Residency Application Form Configuration has been updated by " . $user;
            $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'),$event,$user,$entity,$request,'Google Form Config Updated');

            return $this->redirect($this->generateUrl('resapp_google_form_config_show'));
        }

        $googlesheetmanagement = $this->container->get('resapp_googlesheetmanagement');
        $configFileContent = $googlesheetmanagement->getConfigOnGoogleDrive();
        //add new line before ",{"
        //$configFileContent = str_replace("},","},<br>",$configFileContent);

        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'configFileContent' => $configFileContent,
            'cycle' => $cycle,
            'sitename' => $this->getParameter('resapp.sitename')
        );
    }

    public function createGoogleFormConfigForm($entity, $cycle) {

        if( $cycle == "show" ) {
            $disabled = true;
            $method = "GET";
            //$action = $this->generateUrl('resapp_update', array('id' => $entity->getId()));
        }

        if( $cycle == "edit" ) {
            $disabled = false;
            $method = "PUT";
            //$action = $this->generateUrl('resapp_update', array('id' => $entity->getId()));
        }

        $resappUtil = $this->container->get('resapp_util');
        $resTypes = $resappUtil->getResidencyTypesByInstitution(true);
        $resVisaStatus = $resappUtil->getResidencyVisaStatuses(true);

        //link to http://127.0.0.1/order/residency-applications/residency-types-settings
        $resappTypesListLink = NULL;
        if( $this->isGranted('ROLE_RESAPP_ADMIN') ) {
            $resappTypesListUrl = $this->container->get('router')->generate(
                'resapp_residencytype_settings',
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $resappTypesListLink = " <a data-toggle='tooltip' title='Residency Settings Management' href=".$resappTypesListUrl."><span class='glyphicon glyphicon-wrench'></span></a>";
        }

        //link to the resappVisaStatusesLink
        $resappVisaStatusesListLink = NULL;
        if( $this->isGranted('ROLE_RESAPP_ADMIN') ) {
            $resappVisaStatusesListUrl = $this->container->get('router')->generate(
                //'visastatus-list',
                'visastatus-list_resapp',
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $resappVisaStatusesListLink = " <a data-toggle='tooltip' title='Residency Visa Status Management' href=".$resappVisaStatusesListUrl."><span class='glyphicon glyphicon-wrench'></span></a>";
        }

        $params = array(
            'cycle' => $cycle,
            'resTypes' => $resTypes,
            'resVisaStatus' => $resVisaStatus,
            'resappTypesListLink' => $resappTypesListLink,
            'resappVisaStatusesListLink' => $resappVisaStatusesListLink
        );

        $form = $this->createForm(
        //new InterviewType($params),
            GoogleFormConfigType::class,
            $entity,
            array(
                'form_custom_value' => $params,
                'disabled' => $disabled,
                'method' => $method,
                //'action' => $action
            )
        );

        return $form;
    }


    #[Route(path: '/google-form-config-update-drive', name: 'resapp_google_form_config_update_drive', methods: ['GET'])]
    public function GoogleFormConfigUpdateDriveAction(Request $request) {

        if( $this->isGranted('ROLE_RESAPP_ADMIN') === false ) {
            return $this->redirect( $this->generateUrl('resapp-nopermission') );
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $configFileName = "residency-config.json";

        //$configs = $em->getRepository("AppResAppBundle:GoogleFormConfig")->findAll();
        $configs = $em->getRepository(GoogleFormConfig::class)->findAll();
        if( count($configs) > 0 ) {
            $entity = $configs[0];
        } else {
            //$entity = new GoogleFormConfig();
            throw $this->createNotFoundException('Unable to find Google Residency Application Form Configuration');
        }

        //create json file
        $configJson = array();

        $configJson['acceptingSubmissions'] = $entity->getAcceptingSubmission();

        $residencyTypes = array();
        foreach($entity->getResidencySubspecialties() as $residencyType) {
            $name = $residencyType->getName();
            $residencyTypes[] = array('id'=>$name,'text'=>$name);
        }
        $configJson['residencyTypes'] = $residencyTypes;

        //applicationFormNote: done
        $configJson['applicationFormNote'] = $entity->getApplicationFormNote();

        //adminEmail: done
        $configJson['adminEmail'] = $entity->getAdminEmail();

        //resappAdminEmail: done
        $configJson['resappAdminEmail'] = $entity->getResappAdminEmail();

        //exceptionAccount: done
        $configJson['exceptionAccount'] = $entity->getExceptionAccount();

        //submissionConfirmation: done
        $configJson['submissionConfirmation'] = $entity->getSubmissionConfirmation();

        //letterAcceptingSubmission: done
        $configJson['letterAcceptingSubmission'] = $entity->getLetterAcceptingSubmission();

        //letterError: done
        $configJson['letterError'] = $entity->getLetterError();

        //letterExceptionAccount: done
        $configJson['letterExceptionAccount'] = $entity->getLetterExceptionAccount();

        //2
        //residencyVisaStatuses: done
        $residencyVisaStatuses = array();
        foreach($entity->getResidencyVisaStatuses() as $residencyVisaStatus) {
            $name = $residencyVisaStatus->getName();
            $residencyVisaStatuses[] = array('id'=>$name,'text'=>$name);
        }
        $configJson['residencyVisaStatuses'] = $residencyVisaStatuses;

        //visaNote: done
        $configJson['visaNote'] = $entity->getVisaNote();

        //otherExperienceNote: done
        $configJson['otherExperienceNote'] = $entity->getOtherExperienceNote();

        //nationalBoardNote: done
        $configJson['nationalBoardNote'] = $entity->getNationalBoardNote();

        //medicalLicenseNote:
        $configJson['medicalLicenseNote'] = $entity->getMedicalLicenseNote();

        //boardCertificationNote: done
        $configJson['boardCertificationNote'] = $entity->getBoardCertificationNote();

        //referenceLetterNote: done
        $configJson['referenceLetterNote'] = $entity->getReferenceLetterNote();

        //signatureStatement:
        $configJson['signatureStatement'] = $entity->getSignatureStatement();



        $configJson = json_encode($configJson);

        //echo "<pre>";
        //print_r($configJson);
        //echo "</pre>";

        //get Google service
        $googlesheetmanagement = $this->container->get('resapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            //exit($event);
            $logger->warning("GoogleFormConfigUpdateDriveAction: deleteRowInListFeed: ".$event);
            return NULL;
        }

        $configFileFolderIdResApp = $userSecUtil->getSiteSettingParameter('configFileFolderIdResApp');
        if( !$configFileFolderIdResApp ) {
            $logger->warning('Google Drive Folder ID with config file is not defined in Site Parameters. configFileFolderIdResApp='.$configFileFolderIdResApp);
            return NULL;
        }

        $configFile = $googlesheetmanagement->findConfigFileInFolder($service, $configFileFolderIdResApp, $configFileName);
        if( $configFile ) {
            $configFile->getId();
        } else {
            exit("Config file not found on Google Drive");
        }

        //replace $request->getSchemeAndHttpHost() with getRealSchemeAndHttpHost($request)
        $userUtil = $this->container->get('user_utility');
        $schemeAndHttpHost = $userUtil->getRealSchemeAndHttpHost($request);

        $newDescription = "config file for residency application generated by " . $user . " from server " . $schemeAndHttpHost;
        $newMimeType = null; //"application/json";
        //$newFileName = $configFileName;
        $newRevision = null;

        //if live
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment == 'live' ) { //live
            $updatedFile = $this->updateFileContent($service, $configFile->getId(), $configFileName, $newDescription, $newMimeType, $configJson, $newRevision);
            if( $updatedFile ) {
                //echo "Config file has been updated <br>";
                $eventMsg = "Residency Form Configuration file has been updated on the Google Drive by " . $user;
                $eventType = 'Residency Application Config Updated On Google Drive';

                $this->addFlash(
                    'notice',
                    $eventMsg
                );
            } else {
                $eventMsg = "Residency Form Configuration file update to Google Drive failed";
                $eventType = 'Residency Application Config Updated On Google Drive Failed';
                //throw new \Exception( $msg );

                $this->addFlash(
                    'warning',
                    $eventMsg
                );
            }
        } else {
            $eventMsg = "Residency Form Configuration file has not been updated because the environment is not 'live' (environment='$environment') on the Google Drive by " . $user;
            $eventType = 'Residency Application Config Updated On Google Drive Failed';

            $this->addFlash(
                'warning',
                $eventMsg
            );
        }

        //$this->updateConfigOnGoogleDrive($configJson);

        //exit("update drive");

        $userSecUtil->createUserEditEvent($this->getParameter('resapp.sitename'),$eventMsg,$user,$entity,$request,$eventType);

        return $this->redirect($this->generateUrl('resapp_google_form_config_show'));
    }

//    //Generate residency-config.json file and upload the file to the Google Drive
//    public function updateConfigOnGoogleDrive($configEntity) {
//        if( $this->isGranted('ROLE_RESAPP_ADMIN') === false ) {
//            //return $this->redirect( $this->generateUrl('resapp-nopermission') );
//            return NULL;
//        }
//
//        $logger = $this->container->get('logger');
//        $userSecUtil = $this->container->get('user_security_utility');
//
//        $configFileFolderIdResApp = $userSecUtil->getSiteSettingParameter('configFileFolderIdResApp');
//        if( !$configFileFolderIdResApp ) {
//            $logger->warning('Google Drive Folder ID with config file is not defined in Site Parameters. configFileFolderIdResApp='.$configFileFolderIdResApp);
//            return NULL;
//        }
//
//
//        return false;
//    }
    /**
     * Update an existing file's metadata and content.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param string $fileId ID of the file to update.
     * @param string $newTitle New title for the file.
     * @param string $newDescription New description for the file.
     * @param string $newMimeType New MIME type for the file.
     * @param string $content New content to upload.
     * @param bool $newRevision Whether or not to create a new revision for this file.
     * @return Google_Servie_Drive_DriveFile The updated file. NULL is returned if
     *     an API error occurred.
     */
    function updateFileContent($service, $fileId, $newTitle, $newDescription, $newMimeType, $content, $newRevision) {
        try {
            // First retrieve the file from the API.
            $file = $service->files->get($fileId);

            // File's new metadata.
            $file->setTitle($newTitle);
            $file->setDescription($newDescription);
            $file->setMimeType($newMimeType);

            // File's new content.
            //$newFileName = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\App\\ResAppBundle\\Util\\GoogleForm\\"."residency-config.json";
            //$data = file_get_contents($newFileName);
            //print_r($data);
            //$data = $content;

            //https://developers.google.com/drive/api/v2/reference/files/update
            //https://github.com/googleapis/google-api-php-client/issues/468
            //add 'uploadType' => 'multipart'

            $additionalParams = array(
                'newRevision' => $newRevision,
                'data' => $content,
                'mimeType' => $newMimeType,
                'uploadType' => 'media'
            );

            // Send the request to the API.
            $updatedFile = $service->files->update($fileId, $file, $additionalParams);
            return $updatedFile;
        } catch (\Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
    }

//    //1)  Import sheets from Google Drive
//    //1a)   import all sheets from Google Drive folder
//    //1b)   add successefull downloaded sheets to DataFile DB object with status "active"
//    public function getConfigOnGoogleDrive() {
//
//        if( $this->isGranted('ROLE_RESAPP_ADMIN') === false ) {
//            //return $this->redirect( $this->generateUrl('resapp-nopermission') );
//            return NULL;
//        }
//
//        $logger = $this->container->get('logger');
//        $userSecUtil = $this->container->get('user_security_utility');
//        //$systemUser = $userSecUtil->findSystemUser();
//
//        //get Google service
//        $googlesheetmanagement = $this->container->get('resapp_googlesheetmanagement');
//        $service = $googlesheetmanagement->getGoogleService();
//
//        if( !$service ) {
//            $event = "Google API service failed!";
//            exit($event);
//        }
//
//        //echo "service ok <br>";
//
//        //https://drive.google.com/file/d/1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ/view?usp=sharing
////        $fileId = "1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ"; //residency-config.json
////        //$fileId = "0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M"; //ResidencyApplication
////        $file = null;
////        try {
////            $file = $service->files->get($fileId);
////            exit("fileId=".$file->getId()."; title=".$file->getTitle());
////        } catch (Exception $e) {
////            throw new IOException('Google API: Unable to get file by file id='.$fileId.". An error occurred: " . $e->getMessage());
////        }
//
//        $configFileFolderIdResApp = $userSecUtil->getSiteSettingParameter('configFileFolderIdResApp');
//        if( !$configFileFolderIdResApp ) {
//            $logger->warning('Google Drive Folder ID with config file is not defined in Site Parameters. configFileFolderIdResApp='.$configFileFolderIdResApp);
//            return NULL;
//        }
//        //$folderIdResApp = "0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M";
//        //echo "folder ID=".$configFileFolderIdResApp."<br>";
//
//        if( 1 ) {
//            $configFile = $this->findConfigFileInFolder($service, $configFileFolderIdResApp, "residency-config.json");
//            $contentConfigFile = $this->downloadFile($service, $configFile);
//
//            //$contentConfigFile = str_replace(",",", ",$contentConfigFile);
////            //echo $content;
////            echo "<pre>";
////            print_r($contentConfigFile);
////            echo "</pre>";
//
//            return $contentConfigFile;
//
////            $response = new Response();
////            $response->headers->set('Content-Type', 'application/json');
////            $response->setContent(json_encode($content));
//            //echo $response;
//
//            //exit();
//
//            //return $configFile;
//            //exit('111');
//        } else {
//            //get all files in google folder
//            //ID=0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M
//            //$parameters = array('q' => "'".$configFileFolderIdResApp."' in parents and trashed=false and name contains 'residency-config.json'");
//            //$parameters = array('q' => "'".$configFileFolderIdResApp."' in parents and trashed=false");
//            $parameters = array('q' => "'" . $configFileFolderIdResApp . "' in parents and trashed=false and title='residency-config.json'");
//            $files = $service->files->listFiles($parameters);
//
//            foreach ($files->getItems() as $file) {
//                echo "file=" . $file->getId() . "<br>";
//                echo "File Title=" . $file->getTitle() . "<br>";
//            }
//
//            return $file;
//        }
//
//
//        return NULL;
//    }
//
//    /**
//     * @param Google_Service_Drive $service Drive API service instance.
//     * @param String $folderId ID of the folder to print files from.
//     * @param String $fileName Name (Title) of the config file to find.
//     */
//    function findConfigFileInFolder($service, $folderId, $fileName) {
//        $pageToken = NULL;
//
//        do {
//            try {
//
//                if ($pageToken) {
//                    $parameters['pageToken'] = $pageToken;
//                }
//
//                //$parameters = array();
//                //$parameters = array('q' => "trashed=false and title='residency-config.json'");
//                //$children = $service->children->listChildren($folderId, $parameters);
//                $parameters = array('q' => "'".$folderId."' in parents and trashed=false and title='".$fileName."'");
//                $files = $service->files->listFiles($parameters);
//
//                foreach ($files->getItems() as $file) {
//                    //echo "File ID=" . $file->getId()."<br>";
//                    //echo "File Title=" . $file->getTitle()."<br>";
//
//                    return $file;
//                }
//                $pageToken = $files->getNextPageToken();
//            } catch (Exception $e) {
//                print "An error occurred: " . $e->getMessage();
//                $pageToken = NULL;
//            }
//        } while ($pageToken);
//
//        return NULL;
//    }
//
//    /**
//     * Download a file's content.
//     *
//     * @param Google_Service_Drive $service Drive API service instance.
//     * @param File $file Drive File instance.
//     * @return String The file's content if successful, null otherwise.
//     */
//    function downloadFile($service, $file) {
//        $downloadUrl = $file->getDownloadUrl();
//        if ($downloadUrl) {
//            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
//            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
//            if ($httpRequest->getResponseHttpCode() == 200) {
//                return $httpRequest->getResponseBody();
//            } else {
//                // An error occurred.
//                return null;
//            }
//        } else {
//            // The file doesn't have any content stored on Drive.
//            return null;
//        }
//    }


}
