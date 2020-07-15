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

use App\FellAppBundle\Entity\FellowshipApplication;
use App\FellAppBundle\Entity\Reference;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends OrderAbstractController
{

//    /**
//     * @Route("/hello/{name}")
//     * @Template()
//     */
//    public function indexAction($name)
//    {
//        return array('name' => $name);
//    }


    /**
     * @Route("/thanks-for-downloading/{id}/{sitename}", name="fellapp_thankfordownloading", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Default/thanksfordownloading.html.twig")
     */
    public function thankfordownloadingAction(Request $request, $id, $sitename) {
        return array(
            'fileid' => $id,
            'sitename' => $sitename
        );
    }


    /**
     * @Route("/about", name="fellapp_about_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array('sitename'=>$this->getParameter('fellapp.sitename'));
    }



    /**
     * 127.0.0.1/order/fellowship-applications/test_google_file
     *
     * @Route("/test_google_file", name="fellapp_test_google_file")
     */
    public function testGoogleFileAction( Request $request ) {

        //$fellappRecLetterUtil = $this->get('fellapp_rec_letter_util');
        //$result2 = $fellappRecLetterUtil->processFellRecLetterFromGoogleDrive();
        //echo $result2."<br>";

        //exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        $emailUtil->testEmailWithAttachments();
        exit("EOF testEmailWithAttachments");

        //test 1) sendRefLetterReceivedNotificationEmail
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        $fellapp = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find(1414); //8-testing, 1414-collage, 1439-live
        $references = $fellapp->getReferences();
        $reference = $references->first();
        $letters = $reference->getDocuments();
        $uploadedLetterDb = $letters->first();
        $res = $fellappRecLetterUtil->sendRefLetterReceivedNotificationEmail($fellapp,$uploadedLetterDb);

        $fellappType = $fellapp->getFellowshipSubspecialty();
        echo "ID=".$fellapp->getId().", fellappType=".$fellappType.": res=".$res."<br>";

        exit("end of sendRefLetterReceivedNotificationEmail test");

        
        //test 2)
        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

        $inputFileName = "Uploaded/fellapp/Spreadsheets/test-fellapp3";

        $applications = $fellappImportPopulateUtil->populateSpreadsheet($inputFileName);

        exit("end of fellapp test");
    }

    //generateRecLetterId
    /**
     * @Route("/generate-rec-letter-id", name="fellapp_rec_letter_id")
     */
    public function generateRecLetterIdAction( Request $request ) {

        //testing checkAndSendCompleteEmail
        //$fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        //$fellapp = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find(8);
        //$fellappRecLetterUtil->checkAndSendCompleteEmail($fellapp);

        //testing checkReferenceAlreadyHasLetter
        //$fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        //$fellapp = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find(1414); //8-test,1414-collage
        //$reference = $fellapp->getReferences()->first();
        //$fellappRecLetterUtil->checkReferenceAlreadyHasLetter($fellapp,$reference);

        exit("not allowed. one time run method.");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');

        $repository = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication');
        $dql =  $repository->createQueryBuilder("fellapp");
        $dql->select('fellapp');
        $dql->leftJoin("fellapp.references", "references");
        $dql->where("references.recLetterHashId IS NULL");
        $dql->orderBy("fellapp.id","DESC");
        $query = $em->createQuery($dql);
        $fellapps = $query->getResult();
        echo "fellapps count=".count($fellapps)."<br>";

        foreach($fellapps as $fellapp) {
            $references = $fellapp->getReferences($fellapp);

            foreach($references as $reference) {
                $hash = $fellappRecLetterUtil->generateRecLetterId($fellapp,$reference,$request);
                if( $hash ) {
                    $reference->setRecLetterHashId($hash);
                    $em->flush($reference);
                    echo $fellapp->getId()." (".$reference->getId()."): added hash=".$hash."<br>";
                }
            }

        }

        exit("end of generateRecLetterIdAction");
    }

    /**
     * @Route("/confirmation/{id}", name="fellapp_simple_confirmation")
     * @Template("AppFellAppBundle/Default/simple-confirmation.html.twig")
     */
    public function confirmationAction( Request $request, FellowshipApplication $fellapp ) {

        return array(
            'entity' => $fellapp
        );
    }

    /**
     * http://127.0.0.1/order/fellowship-applications/generate-thumbnails
     * 
     * @Route("/generate-thumbnails", name="fellapp_generate_thumbnails")
     */
    public function generateThumbnailsAction( Request $request ) {

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();

        //get spreadsheets older than X year
        $repository = $em->getRepository('AppUserdirectoryBundle:Document');
        $dql =  $repository->createQueryBuilder("document");
        $dql->select('document');
        $dql->leftJoin('document.type','documentType');

        //$dql->where("documentType.name = 'Fellowship Photo'");
        $dql->where("documentType.name = 'Fellowship Photo' OR documentType.name = 'Avatar Image'");

        $query = $em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";

        $documents = $query->getResult();
        echo "doc count=".count($documents)."<br>";

        $counter = 0;
        foreach($documents as $document) {
            $dest = $userServiceUtil->generateTwoThumbnails($document);
            if( $dest ) {
                echo $document->getId() . ": dest=" . $dest . "<br>";
                $counter++;
            }
            //break;
        }

        exit("end of fellapp thumbnails, counter=$counter");
    }



    /**
     * http://127.0.0.1/order/index_dev.php/fellowship-applications/test-google
     *
     * @Route("/test-google", name="fellapp_test_google")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function testGoogleAction( Request $request ) {

        exit("not allowed");
        
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');

        $service = $googlesheetmanagement->getGoogleService();

        if( !$service ) {
            $event = "Google API service failed!";
            exit($event);
        }

        $fileId = '1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ'; //1EEZ85D4sNeffSLb35_72qi8TdjD9nLyJ json
        //$fileId = '1OM2G8yI_gmSMG-KP1liBG611-CJO0GXEY5wCn_4CZWY'; //excel
        $fileId = '16yxO0PhBel3UXSnBVfIUknA0DamhFUIs6KZG7i2l2aU'; //excel //SYLK file
        //$fileId = '1HrAOhG6d-kfv1KVSRNmK8po7LkbBwsuK'; //pdf

        $file = $service->files->get($fileId);
        echo "file ID=".$file->getId()."<br>";
        $content = $googlesheetmanagement->downloadFile($service, $file, 'Fellowship Application Spreadsheet');
        dump($content);
        exit($content);
//
//        $response = $service->files->export($fileId, 'text/csv', array(
//            'alt' => 'media'));
//        $content = $response->getBody()->getContents();
//
//        exit($content);

        $configFileFolderIdFellApp = $userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
        if( !$configFileFolderIdFellApp ) {
            exit('Google Drive Folder ID with config file is not defined in Site Parameters. configFileFolderIdFellApp='.$configFileFolderIdFellApp);
            return NULL;
        }

        $file = $googlesheetmanagement->findConfigFileInFolder($service, $configFileFolderIdFellApp, "config.json");
        if( !$file ) {
            exit("Config file 'config.json' not found in $configFileFolderIdFellApp");
        }

        //$contentConfigFile = $googlesheetmanagement->downloadGeneralFile($service, $file);

        $contentConfigFile = $googlesheetmanagement->downloadSimpleFile($service, $file, null);

        exit($contentConfigFile);

        exit('EOF testGoogleAction');
    }
    /**
     * Download a file's content.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param File $file Drive File instance.
     * @return String The file's content if successful, null otherwise.
     */
    function downloadSimpleFile($service, $file) {
        $downloadUrl = $file->getDownloadUrl();
        if ($downloadUrl) {
            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
            if ($httpRequest->getResponseHttpCode() == 200) {
                return $httpRequest->getResponseBody();
            } else {
                // An error occurred.
                //return $httpRequest->getResponseBody();
                return null;
            }
        } else {
            // The file doesn't have any content stored on Drive.
            return null;
        }
    }

    /**
     * Download a file's content.
     *
     * @param Google_Servie_Drive $service Drive API service instance.
     * @param Google_Servie_Drive_DriveFile $file Drive File instance.
     * @param String $type Document type string.
     * @return String The file's content if successful, null otherwise.
     */
    function downloadFile($service, $file, $type=null) {

        exit('not allowed');

        /// testing ///
        $fileId = $file->getId();
        //$content = $service->files->get($fileId, array('alt' => 'media' ));
        //$content = $service->files->get($fileId);
        //modelData
        //dump($content['modelData']);
        //echo "modelData=".$content['modelData']."<br>";
        //echo "content=".$content."<br>";
        //dump($content);
        //$link = $content['exportLinks'];
        //$exportLinks = $link['text/csv'];
        //echo "exportLinks=".$exportLinks."<br>";
        //$request = new \Google_Http_Request($exportLinks, 'GET', null, null);
        //$httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
        //return $httpRequest->getResponseBody();
        //dump($httpRequest->getResponseBody());

        //https://www.googleapis.com/drive/v3/files/fileId/export
        //https://www.googleapis.com/drive/v2/files/16yxO0PhBel3UXSnBVfIUknA0DamhFUIs6KZG7i2l2aU/export?mimeType=text/csv
        //https://docs.google.com/document/d/16yxO0PhBel3UXSnBVfIUknA0DamhFUIs6KZG7i2l2aU/export?format=pdf
        //https://docs.google.com/spreadsheets/d/16yxO0PhBel3UXSnBVfIUknA0DamhFUIs6KZG7i2l2aU/export?format=csv

        //exit('333');
        //return $content;
        /// EOF testing ///

        $logger = $this->container->get('logger');
        if( $type && ($type == 'Fellowship Application Spreadsheet' || $type == 'Fellowship Application Backup Spreadsheet' || $type == 'Fellowship Recommendation Letter Spreadsheet') ) {

            //dump($file->getExportLinks());
            //$downloadUrl = $file->getExportLinks()['text/csv'];
            $downloadUrl = $file->getAlternateLink();
            echo "AlternateLink=".$downloadUrl."<br>";

            $downloadUrl = $file->getEmbedLink();
            echo "EmbedLink=".$downloadUrl."<br>";

            //$exportLinks = $file->getExportLinks();
            //$downloadUrl = $exportLinks['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            //$downloadUrl = $exportLinks['application/vnd.google-apps.spreadsheet'];
            //$downloadUrl = $exportLinks['text/csv'];

            $downloadUrl = 'https://docs.google.com/spreadsheets/d/16yxO0PhBel3UXSnBVfIUknA0DamhFUIs6KZG7i2l2aU/export?format=csv';
            //                https://docs.google.com/spreadsheets/export?id=16yxO0PhBel3UXSnBVfIUknA0DamhFUIs6KZG7i2l2aU&exportFormat=csv

            $downloadUrl = 'https://www.googleapis.com/drive/v3/files/'.$fileId.'/export?mimeType=text/csv';

        } else {
            $downloadUrl = $file->getDownloadUrl();
        }
        echo "downloadUrl=".$downloadUrl."<br>";
        if ($downloadUrl) {
            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
            //$request = new \Google_Http_Request($downloadUrl);
            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
            echo "res code=".$httpRequest->getResponseHttpCode()."<br>";
            if ($httpRequest->getResponseHttpCode() == 200) {
                //$logger->notice("download file: response=".$httpRequest->getResponseHttpCode()."; file id=".$file->getId()."; type=".$type);
                //$logger->notice("getResponseBody=".$httpRequest->getResponseBody());
                return $httpRequest->getResponseBody();
            } else {
                // An error occurred.
                //TODO: test why: 307 Temporary Redirect: The document has moved here
                //https://github.com/googleapis/google-api-php-client/issues/102
                //https://stackoverflow.com/questions/39340374/php-google-drive-api-http-response
                return $httpRequest->getResponseBody(); //testing
                //exit("Error download file: invalid response =".$httpRequest->getResponseHttpCode());
                $logger->error("Error download file: invalid response =".$httpRequest->getResponseHttpCode());
                return null;
            }
        } else {
            // The file doesn't have any content stored on Drive.
            $logger->error("Error download file: downloadUrl is null=".$downloadUrl);
            return null;
        }
    }


    /**
     * http://127.0.0.1/order/fellowship-applications/test-latest-reference-letter/2810
     *
     * @Route("/test-latest-reference-letter/{id}", name="fellapp_test-latest-reference-letter")
     */
    public function testLatestReferenceLetterAction( Request $request, Reference $reference ) {

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');

        $fellapp = $reference->getFellapp();

        $testing = true;
        $fellappRecLetterUtil->checkReferenceAlreadyHasLetter($fellapp,$reference,$testing);

        exit("end of testLatestReferenceLetterAction");
    }
}
