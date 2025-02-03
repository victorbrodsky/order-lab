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

namespace App\FellAppBundle\Controller;

use App\FellAppBundle\Entity\GoogleFormConfig;
use App\FellAppBundle\Form\GoogleFormConfigType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleFormConfigController extends OrderAbstractController
{

    #[Route(path: '/form-status-and-appearance/edit', name: 'fellapp_google_form_config_edit', methods: ['GET', 'PUT'])]
    #[Route(path: '/form-status-and-appearance/show', name: 'fellapp_google_form_config_show', methods: ['GET', 'PUT'])]
    #[Template('AppFellAppBundle/GoogleFormConfig/google-form-config.html.twig')]
    public function GoogleFormConfigAction(Request $request) {

        if( $this->isGranted('ROLE_FELLAPP_ADMIN') === false ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $routeName = $request->get('_route');

        $cycle = "show";

        if( $routeName == "fellapp_google_form_config_edit" ) {
            $cycle = "edit";
        }

        $configs = $em->getRepository(GoogleFormConfig::class)->findAll();
        if( count($configs) > 0 ) {
            $entity = $configs[0];
        } else {
            $entity = new GoogleFormConfig();
            //throw $this->createNotFoundException('Unable to find Google Fellowship Application Form Configuration');
        }

        $form = $this->createGoogleFormConfigForm($entity,$cycle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            //exit("save");

            $em->persist($entity);
            $em->flush();

            $event = "Google Fellowship Application Form Configuration has been updated by " . $user;
            $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,$request,'Google Form Config Updated');

            return $this->redirect($this->generateUrl('fellapp_google_form_config_show'));
        }

        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $configFileContent = $googlesheetmanagement->getConfigOnGoogleDrive();
        //add new line before ",{"
        //$configFileContent = str_replace("},","},<br>",$configFileContent);

        if( !$configFileContent ) {
                $this->addFlash(
                    'warning',
                    "Fellowship configuration: Google configuration file can not be retrieved from Google Drive." .
                    " Please verify if the 'Full path to the credential authentication JSON file for Google'" .
                    " parameter in the site settings has been provided and exist on the server"
                );
        }

        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'configFileContent' => $configFileContent,
            'cycle' => $cycle,
            'sitename' => $this->getParameter('fellapp.sitename')
        );
    }

    public function createGoogleFormConfigForm($entity, $cycle) {

        if( $cycle == "show" ) {
            $disabled = true;
            $method = "GET";
            //$action = $this->generateUrl('fellapp_update', array('id' => $entity->getId()));
        }

        if( $cycle == "edit" ) {
            $disabled = false;
            $method = "PUT";
            //$action = $this->generateUrl('fellapp_update', array('id' => $entity->getId()));
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $fellTypes = $fellappUtil->getFellowshipTypesByInstitution(true);
        $fellVisaStatus = $fellappUtil->getFellowshipVisaStatuses(true);

        //link to http://127.0.0.1/order/fellowship-applications/fellowship-types-settings
        $fellappTypesListLink = NULL;
        if( $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
            $fellappTypesListUrl = $this->container->get('router')->generate(
                'fellapp_fellowshiptype_settings',
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $fellappTypesListLink = " <a data-toggle='tooltip' title='Fellowship Settings Management' href=".$fellappTypesListUrl."><span class='glyphicon glyphicon-wrench'></span></a>";
        }

        //link to the fellappVisaStatusesLink
        $fellappVisaStatusesListLink = NULL;
        if( $this->isGranted('ROLE_FELLAPP_ADMIN') ) {
            $fellappVisaStatusesListUrl = $this->container->get('router')->generate(
                //'visastatus-list',
                'visastatus-list_fellapp',
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $fellappVisaStatusesListLink = " <a data-toggle='tooltip' title='Fellowship Visa Status Management' href=".$fellappVisaStatusesListUrl."><span class='glyphicon glyphicon-wrench'></span></a>";
        }

        $params = array(
            'cycle' => $cycle,
            'fellTypes' => $fellTypes,
            'fellVisaStatus' => $fellVisaStatus,
            'fellappTypesListLink' => $fellappTypesListLink,
            'fellappVisaStatusesListLink' => $fellappVisaStatusesListLink
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


    #[Route(path: '/google-form-config-update-drive', name: 'fellapp_google_form_config_update_drive', methods: ['GET'])]
    public function GoogleFormConfigUpdateDriveAction(Request $request) {

        if( $this->isGranted('ROLE_FELLAPP_ADMIN') === false ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $configs = $em->getRepository(GoogleFormConfig::class)->findAll();
        if( count($configs) > 0 ) {
            $entity = $configs[0];
        } else {
            //$entity = new GoogleFormConfig();
            throw $this->createNotFoundException('Unable to find Google Fellowship Application Form Configuration');
        }

        //create json file
        $configJson = array();

        $configJson['acceptingSubmissions'] = $entity->getAcceptingSubmission();

        $fellowshipTypes = array();
        foreach($entity->getFellowshipSubspecialties() as $fellowshipType) {
            $name = $fellowshipType->getName();
            $fellowshipTypes[] = array('id'=>$name,'text'=>$name);
        }
        $configJson['fellowshipTypes'] = $fellowshipTypes;

        //applicationFormNote: done
        $configJson['applicationFormNote'] = $entity->getApplicationFormNote();

        //adminEmail: done
        $configJson['adminEmail'] = $entity->getAdminEmail();

        //fellappAdminEmail: done
        $configJson['fellappAdminEmail'] = $entity->getFellappAdminEmail();

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
        //fellowshipVisaStatuses: done
        $fellowshipVisaStatuses = array();
        foreach($entity->getFellowshipVisaStatuses() as $fellowshipVisaStatus) {
            $name = $fellowshipVisaStatus->getName();
            $fellowshipVisaStatuses[] = array('id'=>$name,'text'=>$name);
        }
        $configJson['fellowshipVisaStatuses'] = $fellowshipVisaStatuses;

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

        $configJson['felSpreadsheetFolderId'] = $entity->getFelSpreadsheetFolderId();
        $configJson['felUploadsFolderId'] = $entity->getFelUploadsFolderId();
        $configJson['felTemplateFileId'] = $entity->getFelTemplateFileId();
        $configJson['felBackupTemplateFileId'] = $entity->getFelBackupTemplateFileId();

        $configJson['recSpreadsheetFolderId'] = $entity->getRecSpreadsheetFolderId();
        $configJson['recUploadsFolderId'] = $entity->getRecUploadsFolderId();
        $configJson['recTemplateFileId'] = $entity->getRecTemplateFileId();
        $configJson['recBackupTemplateFileId'] = $entity->getRecBackupTemplateFileId();

        $configJson = json_encode($configJson);

        //echo "<pre>";
        //print_r($configJson);
        //echo "</pre>";

        //get Google service
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            //exit($event);
            $logger->warning("GoogleFormConfigUpdateDriveAction: ".$event);
            return NULL;
        }

        //TODO: use configFileFolderIdFellApp to store unique config file name
//        $configFileFolderIdFellApp = $userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
//        if( !$configFileFolderIdFellApp ) {
//            $logger->warning('Google Drive Folder ID with config file is not defined in Site Parameters. configFileFolderIdFellApp='.$configFileFolderIdFellApp);
//            return NULL;
//        }

        //$configFile = $googlesheetmanagement->findConfigFileInFolder($service, $configFileFolderIdFellApp, "config.json");
        $configFile = $googlesheetmanagement->findConfigFileByName($service, "config-fellapp.json");
        if( $configFile ) {
            $configFile->getId();
        } else {
            exit("Config file not found on Google Drive");
        }

        //replace $request->getSchemeAndHttpHost() with getRealSchemeAndHttpHost($request)
        $userUtil = $this->container->get('user_utility');
        $schemeAndHttpHost = $userUtil->getRealSchemeAndHttpHost($request);

        $newTitle = "config-fellapp.json"; //"config.json";
        $newDescription = "config file for fellowship application generated by " . $user . " from server " . $schemeAndHttpHost;
        //$newMimeType = null; //"application/json";
        $newMimeType = "application/json";
        //$newFileName = "config.json";
        $newRevision = null;

        //$forceUpdate = true;
        $forceUpdate = false;

        //if live
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $forceUpdate || $environment == 'live' ) { //live
            $updatedResArr = $this->updateFileContent($service, $configFile->getId(), $newTitle, $newDescription, $newMimeType, $configJson, $newRevision);
            if( $updatedResArr['error'] === false ) {
                //echo "Config file has been updated <br>";
                $eventMsg = "Fellowship Form Configuration file has been updated on the Google Drive by " . $user;
                $eventType = 'Fellowship Application Config Updated On Google Drive';

                $this->addFlash(
                    'notice',
                    $eventMsg
                );
            } else {
                $eventMsg = "Fellowship Form Configuration file update to Google Drive failed: ".$updatedResArr['msg'];
                $eventType = 'Fellowship Application Config Updated On Google Drive Failed';
                //throw new \Exception( $msg );

                $this->addFlash(
                    'warning',
                    $eventMsg
                );
            }
        } else {
            $eventMsg = "Fellowship Form Configuration file has not been updated because the environment is not 'live' (environment='$environment') on the Google Drive by " . $user;
            $eventType = 'Fellowship Application Config Updated On Google Drive Failed';

            $this->addFlash(
                'warning',
                $eventMsg
            );
        }

        //$this->updateConfigOnGoogleDrive($configJson);

        //exit("update drive");

        $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$eventMsg,$user,$entity,$request,$eventType);

        return $this->redirect($this->generateUrl('fellapp_google_form_config_show'));
    }

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
    function updateFileContentV1($service, $fileId, $newTitle, $newDescription, $newMimeType, $content, $newRevision) {
        try {
            // First retrieve the file from the API.
            $file = $service->files->get($fileId);

            // File's new metadata.
            //$file->setTitle($newTitle);
            $file->setName($newTitle);
            $file->setDescription($newDescription);
            $file->setMimeType($newMimeType);

            // File's new content.
            //$newFileName = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\scanorder\\Scanorders2\\src\App\\FellAppBundle\\Util\\GoogleForm\\"."config.json";
            //$data = file_get_contents($newFileName);
            //print_r($data);
            //$data = $content;

            //https://developers.google.com/drive/api/v2/reference/files/update
            //https://github.com/googleapis/google-api-php-client/issues/468
            //add 'uploadType' => 'multipart'

            $additionalParams = array(
                //'newRevision' => $newRevision,
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
    function updateFileContent($service, $fileId, $newTitle, $newDescription, $newMimeType, $content, $newRevision) {
        try {
            //for update, create a new File, don't get an existing file
            $emptyFile = new \Google_Service_Drive_DriveFile();

            $emptyFile->setName($newTitle);
            $emptyFile->setDescription($newDescription);
            $emptyFile->setMimeType($newMimeType);

            $service->files->update($fileId, $emptyFile, array(
                'data' => $content,
                'mimeType' => $newMimeType, //'application/json', //application/json
                'uploadType' => 'media'
            ));
            return array(
                'error' => false,
                'msg' => ''
            );
        } catch (\Exception $e) {
            //print "An error occurred: " . $e->getMessage();
            return array(
                'error' => true,
                'msg' => $e->getMessage()
            );
        }
    }


}
