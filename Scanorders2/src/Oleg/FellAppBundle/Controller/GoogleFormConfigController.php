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

namespace Oleg\FellAppBundle\Controller;

use Oleg\FellAppBundle\Entity\GoogleFormConfig;
use Oleg\FellAppBundle\Form\GoogleFormConfigType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class GoogleFormConfigController extends Controller
{

    /**
     * @Route("/google-form-config/edit", name="fellapp_google_form_config_edit")
     * @Route("/google-form-config/show", name="fellapp_google_form_config_show")
     * @Template("OlegFellAppBundle:GoogleFormConfig:google-form-config.html.twig")
     * @Method({"GET", "PUT"})
     */
    public function GoogleFormConfigAction(Request $request) {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') === false ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $routeName = $request->get('_route');

        $cycle = "show";

        if( $routeName == "fellapp_google_form_config_edit" ) {
            $cycle = "edit";
        }

        $configs = $em->getRepository("OlegFellAppBundle:GoogleFormConfig")->findAll();
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
            $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$user,$entity,$request,'Google Form Config Updated');

            return $this->redirect($this->generateUrl('fellapp_google_form_config_show'));
        }

        $configFile = $this->getConfigOnGoogleDrive();

        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'cycle' => $cycle,
            'sitename' => $this->container->getParameter('fellapp.sitename')
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

        $fellappUtil = $this->get('fellapp_util');
        $fellTypes = $fellappUtil->getFellowshipTypesByInstitution(true);

        $params = array(
            'cycle' => $cycle,
            'fellTypes' => $fellTypes
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


    /**
     * @Route("/google-form-config-update-drive", name="fellapp_google_form_config_update_drive")
     * @Method({"GET"})
     */
    public function GoogleFormConfigUpdateDriveAction(Request $request) {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') === false ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $configs = $em->getRepository("OlegFellAppBundle:GoogleFormConfig")->findAll();
        if( count($configs) > 0 ) {
            $entity = $configs[0];
        } else {
            //$entity = new GoogleFormConfig();
            throw $this->createNotFoundException('Unable to find Google Fellowship Application Form Configuration');
        }

        exit("update drive");


        $event = "Fellowship Form Configuration has been updated on the Google by " . $user;
        $userSecUtil->createUserEditEvent($this->container->getParameter('fellapp.sitename'),$event,$user,$entity,$request,'Google Form Config Drive Updated');

        return $this->redirect($this->generateUrl('fellapp_google_form_config_show'));

    }

    public function updateConfigOnGoogleDrive() {

    }

    //1)  Import sheets from Google Drive
    //1a)   import all sheets from Google Drive folder
    //1b)   add successefull downloaded sheets to DataFile DB object with status "active"
    public function getConfigOnGoogleDrive() {

        if( $this->get('security.authorization_checker')->isGranted('ROLE_FELLAPP_ADMIN') === false ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $systemUser = $userSecUtil->findSystemUser();

        //get Google service
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            exit($event);
        }

        //echo "service ok <br>";

        //https://drive.google.com/file/d/1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ/view?usp=sharing
//        $fileId = "1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ"; //config.json
//        //$fileId = "0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M"; //FellowshipApplication
//        $file = null;
//        try {
//            $file = $service->files->get($fileId);
//            exit("fileId=".$file->getId()."; title=".$file->getTitle());
//        } catch (Exception $e) {
//            throw new IOException('Google API: Unable to get file by file id='.$fileId.". An error occurred: " . $e->getMessage());
//        }

        $folderIdFellApp = $userSecUtil->getSiteSettingParameter('folderIdFellApp');
        if( !$folderIdFellApp ) {
            $logger->warning('Google Drive Folder ID is not defined in Site Parameters. sourceFolderIdFellApp='.$folderIdFellApp);
        }
        $folderIdFellApp = "0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M";
        echo "folder ID=".$folderIdFellApp."<br>";


        $this->findConfigFileInFolder($service,$folderIdFellApp,"config.json");
        exit('111');

        //get all files in google folder
        //ID=0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M
        //$parameters = array('q' => "'".$folderIdFellApp."' in parents and trashed=false and name contains 'config.json'");
        //$parameters = array('q' => "'".$folderIdFellApp."' in parents and trashed=false");
        $parameters = array('q' => "'".$folderIdFellApp."' in parents and trashed=false and title='config.json'");
        $files = $service->files->listFiles($parameters);

        foreach($files->getItems() as $file) {
            echo "file=".$file->getId()."<br>";
            echo "File Title=" . $file->getTitle()."<br>";
        }


        return $file;
    }

    /**
     * Print files belonging to a folder.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param String $folderId ID of the folder to print files from.
     */
    function findConfigFileInFolder($service, $folderId, $fileName) {
        $pageToken = NULL;

        do {
            try {

                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }

                //$parameters = array();
                //$parameters = array('q' => "trashed=false and title='config.json'");
                //$children = $service->children->listChildren($folderId, $parameters);
                $parameters = array('q' => "'".$folderId."' in parents and trashed=false and title='".$fileName."'");
                $files = $service->files->listFiles($parameters);

                foreach ($files->getItems() as $child) {
                    echo "File ID=" . $child->getId()."<br>";
                    echo "File Title=" . $child->getTitle()."<br>";
                }
                $pageToken = $files->getNextPageToken();
            } catch (Exception $e) {
                print "An error occurred: " . $e->getMessage();
                $pageToken = NULL;
            }
        } while ($pageToken);
    }


}
