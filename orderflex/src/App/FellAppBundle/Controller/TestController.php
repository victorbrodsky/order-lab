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


//Use for testing:
//http://127.0.0.1/order/index_dev.php/fellowship-applications/test/test-google
//http://127.0.0.1/order/index_dev.php/fellowship-applications/test/google-drive
//http://127.0.0.1/order/index_dev.php/fellowship-applications/test/test-latest-reference-letter/2810 (17 - dev)
//http://127.0.0.1/order/index_dev.php/fellowship-applications/test/google-file
//http://127.0.0.1/order/index_dev.php/fellowship-applications/test/verify-import
//http://127.0.0.1/order/index_dev.php/fellowship-applications/test/populate-fellapp
#[Route(path: '/test')]
class TestController extends OrderAbstractController
{

    /**
     * http://127.0.0.1/order/index_dev.php/fellowship-applications/test/google-drive
     */
    #[Route(path: '/google-drive', name: 'fellapp_test_google-drive')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function googleDriveAction( Request $request ) {

        /////////// testing ///////////
//        $fellappUtil = $this->container->get('fellapp_util');
//        $em = $this->getDoctrine()->getManager();
//        $str = "[[DIRECTOR]] - program director";
//        $fellappIdArr = array(1574,1565,1576);
//        foreach( $fellappIdArr as $fellappId ) {
//            $fellapp = $em->getRepository('AppFellAppBundle:FellowshipApplication')->find($fellappId);
//            if ($fellapp) {
//                $str = $fellappUtil->siteSettingsConstantReplace($str,$fellapp);
//                echo $fellappId.": str=" . $str . "<br>";
//                $directorsStr = $fellappUtil->getProgramDirectorStr($fellapp->getFellowshipSubspecialty(), $str);
//                echo $fellappId.": directorsStr=" . $directorsStr . "<br>";
//                echo "###########<br>";
//            }
//        }

//        $fellappUtil = $this->container->get('fellapp_util');
//        $currentYear = $fellappUtil->getDefaultAcademicStartYear();
//        $currentYear = $currentYear + 2;
//        $fellowshipDbApplications = $fellappUtil->getFellAppByStatusAndYear(null,null,$currentYear);
//        echo "fellowshipDbApplications count=".count($fellowshipDbApplications)."<br>";

//        $testvar = null;
//        echo "testvar=[$testvar]<br>";
//        $testvar2 = trim((string)$testvar);
//        echo "testvar2=[$testvar2]<br>";
//        exit();

        //Testing google drive
        $userSecUtil = $this->container->get('user_security_utility');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();

//        $folderIdFellAppId = $userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
//        if( !$folderIdFellAppId ) {
//            exit('Google Drive Folder ID is not defined in Site Parameters. configFileFolderIdFellApp='.$folderIdFellAppId);
//        }
        //$recSpreadsheetFolderId = $userSecUtil->getSiteSettingParameter('recSpreadsheetFolderId');
        $recSpreadsheetFolderId = $googlesheetmanagement->getGoogleConfigParameter('recSpreadsheetFolderId');
        if( !$recSpreadsheetFolderId ) {
            exit('Google Drive Folder ID is not defined in Site Parameters. recSpreadsheetFolderId='.$recSpreadsheetFolderId);
        }

        //find folder by name
        //$letterSpreadsheetFolder = $googlesheetmanagement->findOneRecLetterSpreadsheetFolder($service,$folderIdFellAppId);
        //echo "letterSpreadsheetFolder=".$letterSpreadsheetFolder->getId()."<br>";
        $files = $googlesheetmanagement->retrieveFilesByFolderId($recSpreadsheetFolderId,$service);
        echo "files count=".count($files)."<br>";


        //$recUploadsFolderId = $userSecUtil->getSiteSettingParameter('recUploadsFolderId');
        $recUploadsFolderId = $googlesheetmanagement->getGoogleConfigParameter('recUploadsFolderId');
        if( !$recUploadsFolderId ) {
            exit('Google Drive Folder ID is not defined in Site Parameters. recUploadsFolderId='.$recUploadsFolderId);
        }

        $letterFolder = $googlesheetmanagement->findOneRecLetterUploadFolder($service,$recUploadsFolderId);
        $files = $googlesheetmanagement->retrieveFilesByFolderId($letterFolder->getId(),$service);
        echo "rec letter files count=".count($files)."<br>";

        exit('111');
        
        /////////// EOF testing ///////////

        return array('sitename'=>$this->getParameter('fellapp.sitename'));
    }

    /**
     * http://127.0.0.1/order/index_dev.php/fellowship-applications/test/test-google
     */
    #[Route(path: '/test-google', name: 'fellapp_test_test_google')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function testGoogleAction( Request $request ) {

        //exit("not allowed");

        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
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

//        $configFileFolderIdFellApp = $userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
//        if( !$configFileFolderIdFellApp ) {
//            exit('Google Drive Folder ID with config file is not defined in Site Parameters. configFileFolderIdFellApp='.$configFileFolderIdFellApp);
//            return NULL;
//        }
//        $file = $googlesheetmanagement->findConfigFileInFolder($service, $configFileFolderIdFellApp, "config.json");

        $file = $googlesheetmanagement->findConfigFileByName($service, "config-fellapp.json");
        if( !$file ) {
            exit("Config file 'config-fellapp.json' not found by name");
        }

        //$contentConfigFile = $googlesheetmanagement->downloadGeneralFile($service, $file);

        $contentConfigFile = $googlesheetmanagement->downloadSimpleFile($service, $file, null);

        exit($contentConfigFile);

        exit('EOF testGoogleAction');
    }


    /**
     * NOT USED
     *
     * http://127.0.0.1/order/index_dev.php/fellowship-applications/test/google-file
     */
    #[Route(path: '/google-file', name: 'fellapp_test_google-file')]
    public function googleFileAction( Request $request ) {

        //$fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        //$result2 = $fellappRecLetterUtil->processFellRecLetterFromGoogleDrive();
        //echo $result2."<br>";

        //exit("not allowed: googleFileAction");

        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment && $environment == 'live' ) {
            exit("googleFileAction: not allowed for live server");
        }

        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        //$emailUtil = $this->container->get('user_mailer_utility');
        //$emailUtil->testEmailWithAttachments();
        //exit("EOF testEmailWithAttachments");

        if( $environment && $environment == 'dev' ) {
            $fellapId = 1503;
        }
        if( $environment && $environment == 'test' ) {
            $fellapId = 1414;
        }

        //test 1) sendRefLetterReceivedNotificationEmail
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $fellapp = $this->getDoctrine()->getRepository(FellowshipApplication::class)->find($fellapId); //8-testing, 1414-collage, 1439-live
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




    /**
     * Test if one reference has more than one ref letters. Not significant test
     *
     * http://127.0.0.1/order/index_dev.php/fellowship-applications/test/test-latest-reference-letter/17 //2810
     */
    #[Route(path: '/test-latest-reference-letter/{id}', name: 'fellapp_test_test-latest-reference-letter')]
    public function testLatestReferenceLetterAction( Request $request, Reference $reference ) {

        //exit("not allowed");

        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment && $environment == 'live' ) {
            exit("testLatestReferenceLetterAction: not allowed for live server");
        }

        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');

        $fellapp = $reference->getFellapp();

        $testing = true;
        $letterCount = $fellappRecLetterUtil->checkReferenceAlreadyHasLetter($fellapp,$reference,$testing);
        echo "letterCount=$letterCount <br>";

        exit("end of testLatestReferenceLetterAction");
    }


    /**
     * http://127.0.0.1/order/index_dev.php/fellowship-applications/test/verify-import
     */
    #[Route(path: '/verify-import', name: 'fellapp_test_verify-import')]
    public function testVerifyImportAction( Request $request ) {

        //exit("not allowed");

        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment && $environment == 'live' ) {
            exit("testVerifyImportAction: not allowed for live server");
        }

        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        $res = $fellappImportPopulateUtil->verifyImport();

        echo "res=$res <br>";

        exit("end of testVerifyImportAction");
    }


    /**
     * http://127.0.0.1/order/index_dev.php/fellowship-applications/test/populate-fellapp
     */
    #[Route(path: '/populate-fellapp', name: 'fellapp_test_populate-fellapp')]
    public function testPopulateFellAppAction( Request $request ) {

        //exit("not allowed");

        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment && $environment == 'live' ) {
            exit("testPopulateFellAppAction: not allowed for live server");
        }

        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        //////////// test PhpOffice ////////////
        if(0) {
            $filename = "1647382892ID1GzihEJuCbtWQoroRZbrt-k38s9AVcu9yRUS-GTP5gR4.com_Ashour_Salam_2021-05-19_21_34_54"; //good
            $filename = "1647382888ID1-L_TCY1vrhXyl4KBEZ_x7g-iC_CoKQbcjnvdjgdVR-o.edu_Ali_Mahmoud_2021-05-23_20_21_18"; //bad
            //$filename = "good.xls";
            //$filename = "bad.xls";
            $inputFileName = "C:/Users/ch3/Documents/MyDocs/WCMC/ORDER/order-lab/orderflex/public/Uploaded/fellapp/Spreadsheets/";
            $inputFileName = $inputFileName . $filename;
            echo "Getting source sheet with filename=" . $inputFileName . "<br>";
            //migrate PHPExcel=>PhpOffice: All users must migrate to its direct successor PhpSpreadsheet, or another alternative.
            //$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
            //https://phpspreadsheet.readthedocs.io/en/latest/topics/migration-from-PHPExcel/
            $inputFileType = "Csv";
            //$inputFileType = "Xls";
            echo "inputFileType=$inputFileType <br>";
            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
            exit("testing PhpOffice");
        }
        //////////// EOF test PhpOffice ////////////


        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

        $testing=true;
        $limit=1;

        $testing=false;
        $limit=null;

        $result = $fellappImportPopulateUtil->processFellAppFromGoogleDrive($testing,$limit);

        //$logger->notice("Cron job processing FellApp from Google Drive finished with result=".$result);

        echo "res=$result <br>";

        exit("end of testPopulateFellAppAction");
    }
}
