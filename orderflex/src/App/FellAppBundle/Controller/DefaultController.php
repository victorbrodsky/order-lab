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



use App\FellAppBundle\Entity\GlobalFellowshipSpecialty;
use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document
use App\FellAppBundle\Entity\FellowshipApplication;
use App\FellAppBundle\Entity\Reference;
use App\UserdirectoryBundle\Controller\OrderAbstractController;

use App\UserdirectoryBundle\Entity\FellowshipSubspecialty;
use App\UserdirectoryBundle\Entity\Institution;
use App\UserdirectoryBundle\Entity\Roles;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpClient\HttpClient;

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
    #[Route(path: '/thanks-for-downloading/{id}/{sitename}', name: 'fellapp_thankfordownloading', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Default/thanksfordownloading.html.twig')]
    public function thankfordownloadingAction(Request $request, $id, $sitename) {
        return array(
            'fileid' => $id,
            'sitename' => $sitename
        );
    }


    #[Route(path: '/about', name: 'fellapp_about_page')]
    #[Template('AppUserdirectoryBundle/Default/about.html.twig')]
    public function aboutAction( Request $request ) {

        //$this->tokenStorage->setToken(null);
        //$security->logout(false);
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


//        //Testing google drive
//        $userSecUtil = $this->container->get('user_security_utility');
//        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
//        $service = $googlesheetmanagement->getGoogleService();
//        $folderIdFellAppId = $userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
//        if( !$folderIdFellAppId ) {
//            exit('Google Drive Folder ID is not defined in Site Parameters. configFileFolderIdFellApp='.$folderIdFellAppId);
//        }
//
//        //find folder by name
//        $letterSpreadsheetFolder = $googlesheetmanagement->findOneRecLetterSpreadsheetFolder($service,$folderIdFellAppId);
//        echo "letterSpreadsheetFolder=".$letterSpreadsheetFolder->getId()."<br>";
//        $files = $googlesheetmanagement->retrieveFilesByFolderId($letterSpreadsheetFolder->getId(),$service);
//        echo "files count=".count($files)."<br>";
//
//        $letterFolder = $googlesheetmanagement->findOneRecLetterUploadFolder($service,$folderIdFellAppId);
//        $files = $googlesheetmanagement->retrieveFilesByFolderId($letterFolder->getId(),$service);
//        echo "rec letter files count=".count($files)."<br>";
//
//        exit('111');

//        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
//        $service = $googlesheetmanagement->authenticationP12Key();
//        if( !$service ) {
//            $event = "Google API service failed!";
//            exit($event);
//            //$logger->warning("getConfigOnGoogleDrive: ".$event);
//            //return NULL;
//        }
//        exit('111');

//        $em = $this->getDoctrine()->getManager();
//        $primaryPublicUserId = 'administrator';
//        $localUserType = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findOneByAbbreviation('local-user');
//        $administrators = $em->getRepository('AppUserdirectoryBundle:User')->findBy(
//            array(
//                'primaryPublicUserId' => $primaryPublicUserId,
//                'keytype' => $localUserType->getId()
//            )
//        );
//
//        if( $administrators && count($administrators) == 1 ) {
//            $administrator = $administrators[0];
//        } else {
//            $administrator = NULL;
//        }
//
//        $userManager = $this->container->get('user_manager');
//        $userManager->updateUser($administrator);
//
//        $encoder = $this->container->get('security.password_encoder');
//        $encodedPassword = $encoder->encodePassword($administrator, "1234567890");
//        echo 'testing4 $encodedPassword=['.$encodedPassword.']<br>';
//        //$encodedPassword = '$argon2id$v=19$m=65536,t=4,p=1$JEfUey9jtD13oVS833lFPw$/5GrEbDABSdwnVKGyODzPsLlJ+CDwUv9ZtpM6FSa0AE';
//        $encodedPassword = strval($encodedPassword);
//        $encodedPassword = (string)$encodedPassword;
//
//        $administrator->addRole('ROLE_PLATFORM_ADMIN');
//        //$administrator->setPassword((string)$encodedPassword);
//        $em->persist($administrator);
//        //$em->flush($administrator);
//        $em->flush();
//        exit('111');

//        //testing
//        //1648736222ID1hPlhzbLA_YEsosPrw3uKgL0fe1IgyAUt1rxCg3R3dF4
//        $inputFileName = "/opt/order-lab/orderflex/public/Uploaded/fellapp/Spreadsheets/1648736222ID1hPlhzbLA_YEsosPrw3uKgL0fe1IgyAUt1rxCg3R3dF4";
//        $inputFileName = "/opt/order-lab/orderflex/public/Uploaded/fellapp/Spreadsheets/1648736219ID1-L_TCY1vrhXyl4KBEZ_x7g-iC_CoKQbcjnvdjgdVR-o.edu_Ali_Mahmoud_2021-05-23_20_21_18";
//        echo "inputFileName=".$inputFileName."<br>";
//
//        //inputFileName=/opt/order-lab/orderflex/public/Uploaded/fellapp/Spreadsheets/1648736219ID1-L_TCY1vrhXyl4KBEZ_x7g-iC_CoKQbcjnvdjgdVR-o.edu_First_Lastname_2021-05-23_20_21_18
//        $extension = pathinfo($inputFileName,PATHINFO_EXTENSION);
//        echo "extension=".$extension."<br>";
//        if( $extension || strlen($extension) > 7 ) {
//            //$inputFileType = 'Xlsx'; //'Csv'; //'Xlsx';
//
//            //$objReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
//            //$objReader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
//            $objReader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
//
//            //$objReader->setReadDataOnly(true);
//            //$objPHPExcel = $objReader->load($inputFileType);
//
//            //return false; //testing: skip
//        } else {
//            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
//            echo "inputFileType=".$inputFileType."<br>";
//            //exit('111');
//            $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
//        }
//
//        //$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
//        $objPHPExcel = $objReader->load($inputFileName);
//
//        dump($objPHPExcel);
//        exit('111');

        //$fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');
        //$fellappImportPopulateUtil->getFileInfofromGoogleDriveTesting();

//        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
//        $service = $googlesheetmanagement->getGoogleService();
//        $folderId = "1gapiVoGBGzOZ5frPcBiXjRSC8Wbz6H8l"; //$userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
//        $fileName = "wcmpath_29be771f19e9fc0ab21f874990e29ab7038d3fba_2021-07-09-14-15-59_Shabna.doc";
//        $file = $googlesheetmanagement->findOneFolderByFolderNameAndParentFolder($service, $folderId, $fileName);
//        if( $file ) {
//            dump($file);
//        }
//        exit('111');

//        //wcmpath_dbbd7f57b9f1f175496505ad42bdbd902a40249c_2022-05-06-14-32-52_Geszte is windows doc file => error
//        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
//        $service = $googlesheetmanagement->getGoogleService();
//        $configFileFolderIdFellApp = '1gapiVoGBGzOZ5frPcBiXjRSC8Wbz6H8l';
//        $fileName = 'wcmpath_dbbd7f57b9f1f175496505ad42bdbd902a40249c_2022-05-06-14-32-52_Geszte';
//        //$fileName = 'config.json';
//        $file = $googlesheetmanagement->findConfigFileInFolder($service,$configFileFolderIdFellApp,$fileName);
//
//        $response = $googlesheetmanagement->downloadGeneralFile($service,$file);
//        echo "getWebContentLink=".$response->getWebContentLink()."<br>";
//        dump($response);
//
//        $response = $googlesheetmanagement->downloadFile($service,$file);
//
//        dump($response);
//        exit('111');

//        //fellapp interview and feedback count
//        $fellappUtil = $this->container->get('fellapp_util');
//        $yearRange = '2021';
//        $fellapps = $fellappUtil->getFellAppByStatusAndYear(null,null,$yearRange);
//        $totalInterviews = 0;
//        $totalFeedbacks = 0;
//        foreach($fellapps as $fellapp) {
//            $interviews = $fellapp->getInterviews();
//            echo $fellapp->getId().": ".$fellapp->getStartDate()->format('Y-m-d')."-".$fellapp->getEndDate()->format('Y-m-d')."; interviews=".count($interviews)."<br>";
//            $totalInterviews = $totalInterviews + count($interviews);
//            foreach($interviews as $interview) {
//                if( $interview->isEmpty() === true ) {
//                    $totalFeedbacks = $totalFeedbacks + 1;
//                }
//            }
//        }
//        echo "fellapps=".count($fellapps)."; interviews=".$totalInterviews."; totalFeedbacks=".$totalFeedbacks."<br>";
//        exit('111');
        /////////// EOF testing ///////////

//        //test rec letter import doc format
//        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
//        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
//        $service = $googlesheetmanagement->getGoogleService();
//        $fileId = "17PwcM0qPAAz8KcitIBayMzTj6XW8GSsu"; //"1ohvKGunEsvSowwpozfjvjtyesN0iUeF2"; //Word
//        $fileId = "1Bkz0jkDWn8ymagMf6EPZQZ2Nyf18kaPXI2aqKm_eX-U"; //"1is-0L26e_W76hL-UfAuuZEEo8p9ycnwnn02hZ9lzFek"; //PDF
//        $fileId = "1fd-vjpmQKdVXDiAhEzcP-5fFDZEl2kKW67nrRrtfcWg"; //"17inHCzyZNyZ98E_ZngUjkUKWNp3D2J8Ri2TZWR5Oi1k"; //Google Docs
//        $fileId = "1NwCFOUZ6oTyiehtSzPuxuddsnxbqgPeUCn516eEW05o"; //"1beJAujYBEwPdi3RI7YAb4a8NcrBj5l0vhY6Zsa01Ohg"; //Google Sheets
//        $fileId = "1imVshtA63nsr5oQOyW3cWXzXV_zhjHtyCwTKgjR8MAM"; //Image
//
//        $file = $googlesheetmanagement->getFileById($fileId);
//        $mimeType = $file->getMimeType();
//        //echo "mimeType=$mimeType <br>";
//
//        //$content = $googlesheetmanagement->downloadGeneralFileGoogleDoc($service,$file,$fileId);
//        $content = $googlesheetmanagement->downloadFile($service,$file);
//        //$content = $googlesheetmanagement->downloadFileOrig($service,$file);
//
////        header('Content-Type: ' . $mimeType);
////        header('Expires: 0');
////        header('Cache-Control: must-revalidate');
////        header('Pragma: public');
////        echo $content;
////        exit();
//
//        dump($content);
//        exit('111');

//        $googlesheetmanagementv2 = $this->container->get('fellapp_googlesheetmanagement_v2');
//        if(1) {
//            $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
//            $fileContentsCount = $googlesheetmanagement->testFileDownload();
//            dump($fileContentsCount);
//            exit('111');
//            $configFileContent = $googlesheetmanagement->getConfigOnGoogleDrive();
//            //$fileName = 'test.json';
//            $fileName = 'config.json';
//            //$fileName = 'config_live.json';
//            //$configFileContent = $googlesheetmanagementv2->getConfigOnGoogleDrive($fileName);
//            $configFileContent = json_decode($configFileContent, true);
//            dump($configFileContent);
//            exit('111');
//        }
//        if(0) {
//            //$fileContentsCount = $googlesheetmanagementv2->testFileDownload();
//            //$fileContentsCount = $googlesheetmanagementv2->getConfigOnGoogleDrive();
//            $files = $googlesheetmanagementv2->searchFiles();
//        }
//        if(0) {
//            $service = $googlesheetmanagementv2->getService();
//            $files = $googlesheetmanagementv2->retrieveAllFiles($service);
//            dump($files);
//        }
//        if(0) {
//            $service = $googlesheetmanagementv2->getService();
//            //$fileId = "1maBuBYjB_xEiQi8lqtNDzUhQwEDrFi_o";
//            //$fileId = "1mzVYbtdN72PPEqJ0qlWwon6-ca9epH8iP86mjjpSjLw";
//            //$fileId = "0B0PyCK-oDTOEc3RhcnRlcl9maWxl";
//            //$fileId = "1NwCFOUZ6oTyiehtSzPuxuddsnxbqgPeUCn516eEW05o";
//            $files = $googlesheetmanagementv2->testFiles($service);
//            dump($files); //5 files
//        }
//        //exit('files');

        //$fellappUtil = $this->container->get('fellapp_util');
        //$felBackupTemplateFileId = $fellappUtil->getUpdateDateGoogleDriveFile('felBackupTemplateFileId');
        //echo "felBackupTemplateFileId=$felBackupTemplateFileId <br>";
//        $modifiedDate = $fellappUtil->getUpdateDateBackupFellAppTemplate();
//        echo "modifiedDate=$modifiedDate <br>";
        //exit("felBackupTemplateFileId=".$felBackupTemplateFileId);

        //$em = $this->getDoctrine()->getManager();
        //$wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
        //echo "$wcmc=$wcmc <br>";
        //exit('111');

//        //fellapp_download
//        $applicationId = 1507;
//        $connectionChannel = 'https';
//        $context = $this->container->get('router')->getContext();
//        $context->setHost('localhost');
//        $context->setScheme($connectionChannel);
//
//        $router = $this->container->get('router');
//        $pageUrl = $router->generate(
//            'fellapp_download',
//            array(
//                'id' => $applicationId
//            ),
//            UrlGeneratorInterface::ABSOLUTE_URL
//        );
//        echo "pageurl=". $pageUrl . "<br>";
//
//        $userTenantUtil = $this->container->get('user_tenant_utility');
//        $tenantUrlBase = $userTenantUtil->getTenantUrlBase();
//        echo "tenantUrlBase=". $tenantUrlBase . "<br>";

//        $em = $this->getDoctrine()->getManager();
//        $roleName = "ROLE_FELLAPP_COORDINATOR_SURGICALPATHOLOGY";
//        $role = $em->getRepository(Roles::class)->findOneByName($roleName);
//        $permissions = $role->getPermissions();
//        echo "<br>####################<br>";
//        foreach($permissions as $permission) {
//            echo "Permission object. ID=".$permission->getId()."<br>";
//        }
//        $permission = $permissions[0];
//        $permMsg =  "2 createOrEnableFellAppRole: $roleName: permission count=".count($permissions).", testing.<br>".
//            "permission: ID=".$permission->getId().
//            ", PermissionList: getPermission()->getId=".$permission->getPermission()->getId().
//            ", <br>PermissionList: getPermission()->getName=".$permission->getPermission()->getName()."<br>";
//        if( $permission->getPermission()->getPermissionObjectList() ) {
//            $permMsg = $permMsg . "<br> PermissionObjectList: object ID=".$permission->getPermission()->getPermissionObjectList()->getId().
//                ", PermissionObjectList: object name=".$permission->getPermission()->getPermissionObjectList()->getName()."<br>".
//                ", PermissionObjectList: action name=".$permission->getPermission()->getPermissionActionList()->getName()."<br>";
//        } else {
//            $permMsg = $permMsg . " <br> PermissionObjectList does not exists!!!!!!! <br>";
//        }
//        echo $permMsg;
//        $fellappUtil = $this->container->get('fellapp_util');
//        $fellapp = $this->getDoctrine()->getRepository(FellowshipApplication::class)->find(1684);
//        //$res = $fellapp->getAllFellowshipSpecialty();
//        $res = $fellappUtil->sendWithdrawnNotificationEmail($fellapp, $reasonText='test reason', $previousStatusStr='active');
//        echo 'res='.$res."<br>";
//        //echo "inst=".$institution = $fellapp->getInstitution()."<br>";
//        exit('fellapp default controller');

        //$userServiceUtil = $this->container->get('user_service_utility');
        //$url = $userServiceUtil->getLinkToListIdByClassNameAndSpecificName('SiteList','fellowship-applications');
        //echo "url=$url <br>";

//        $userSecUtil = $this->container->get('user_security_utility');
//        $fellappRecLetterUrl1 = $userSecUtil->getSiteSettingParameter('fellappRecLetterUrl',$this->getParameter('fellapp.sitename'));
//        echo '$fellappRecLetterUrl1='.$fellappRecLetterUrl1."<br>";
//        $fellappUtil = $this->container->get('fellapp_util');
//        $fellappRecLetterUrl2 = $fellappUtil->getFellappRecommendationFormLink();
//        echo '$fellappRecLetterUrl2='.$fellappRecLetterUrl2."<br>";
//        if( $fellappRecLetterUrl1 === $fellappRecLetterUrl2 ) {
//            echo "match ok <br>";
//        } else {
//            echo "match notok <br>";
//        }
//        exit('$fellappRecLetterUrl1');
//        $fellapp = $this->getDoctrine()->getRepository(FellowshipApplication::class)->find(1778);
//        $examinations = $fellapp->getExaminations();
//        echo "examinations=".count($examinations)."<br>";
//        foreach($examinations as $examination) {
//            echo "examination usmle score docs=".count($examination->getScores())."<br>";
//        }
//        exit('exit default FellowshipApplication');

        ///////////
//        $entity = $this->getDoctrine()->getRepository(FellowshipApplication::class)->find(1676);
//        $fellappUtil = $this->container->get('fellapp_util');
//        $holderNamespace = "App\\UserdirectoryBundle\\Entity";
//        $holderName = "FormNode";
//        $holderId = $fellappUtil->getFellappParentFormNodeId();
//        $entityNamespace = "App\\FellAppBundle\\Entity";
//        $entityName = "FellowshipApplication";
//        $entityId = $entity->getId();
//        $testing = false;
//        $params = [
//            'cycle'           => 'show',
//            'holderNamespace' => $holderNamespace ?? null,
//            'holderName'      => $holderName ?? null,
//            'holderId'        => $holderId,
//            'entityNamespace' => $entityNamespace ?? null,
//            'entityName'      => $entityName ?? null,
//            'entityId'        => $entityId ?? null,
//            'testing'         => $testing ?? false,
//        ];
//
//        $screeningQuestionsArray = $fellappUtil->getFellAppFormNodeHtml(null, $params); //return array
//        dump($screeningQuestionsArray);
//        exit('111');
        /////////

        return array('sitename'=>$this->getParameter('fellapp.sitename'));
    }


//    //generateRecLetterId
    //    /**
    //     * @Route("/generate-rec-letter-id", name="fellapp_rec_letter_id")
    //     */
    //    public function generateRecLetterIdAction( Request $request ) {
    //
    //        //testing checkAndSendCompleteEmail
    //        //$fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
    //        //$fellapp = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find(8);
    //        //$fellappRecLetterUtil->checkAndSendCompleteEmail($fellapp);
    //
    //        //testing checkReferenceAlreadyHasLetter
    //        //$fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
    //        //$fellapp = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find(1414); //8-test,1414-collage
    //        //$reference = $fellapp->getReferences()->first();
    //        //$fellappRecLetterUtil->checkReferenceAlreadyHasLetter($fellapp,$reference);
    //
    //        exit("not allowed. one time run method.");
    //
    //        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
    //            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
    //        }
    //
    //        $em = $this->getDoctrine()->getManager();
    //        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
    //
    //        $repository = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication');
    //        $dql =  $repository->createQueryBuilder("fellapp");
    //        $dql->select('fellapp');
    //        $dql->leftJoin("fellapp.references", "references");
    //        $dql->where("references.recLetterHashId IS NULL");
    //        $dql->orderBy("fellapp.id","DESC");
    //        $query = $em->createQuery($dql);
    //        $fellapps = $query->getResult();
    //        echo "fellapps count=".count($fellapps)."<br>";
    //
    //        foreach($fellapps as $fellapp) {
    //            $references = $fellapp->getReferences($fellapp);
    //
    //            foreach($references as $reference) {
    //                $hash = $fellappRecLetterUtil->generateRecLetterId($fellapp,$reference,$request);
    //                if( $hash ) {
    //                    $reference->setRecLetterHashId($hash);
    //                    $em->flush($reference);
    //                    echo $fellapp->getId()." (".$reference->getId()."): added hash=".$hash."<br>";
    //                }
    //            }
    //
    //        }
    //
    //        exit("end of generateRecLetterIdAction");
    //    }
    #[Route(path: '/confirmation/{id}', name: 'fellapp_simple_confirmation')]
    #[Template('AppFellAppBundle/Default/simple-confirmation.html.twig')]
    public function confirmationAction( Request $request, FellowshipApplication $fellapp ) {

        return array(
            'entity' => $fellapp
        );
    }

    /**
     * http://127.0.0.1/order/fellowship-applications/generate-thumbnails
     */
    #[Route(path: '/generate-thumbnails', name: 'fellapp_generate_thumbnails')]
    public function generateThumbnailsAction( Request $request ) {

        exit("not allowed: generateThumbnailsAction");

        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('fellapp.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();

        //get spreadsheets older than X year
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $repository = $em->getRepository(Document::class);
        $dql =  $repository->createQueryBuilder("document");
        $dql->select('document');
        $dql->leftJoin('document.type','documentType');

        //$dql->where("documentType.name = 'Fellowship Photo'");
        $dql->where("documentType.name = 'Fellowship Photo' OR documentType.name = 'Avatar Image'");

        $query = $dql->getQuery();

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





    //Keep only list of specialties according to getFellowshipTypesStrArr (WCM) and getFellowshipTypesWahsuStrArr (Washu)
    //http://127.0.0.1/fellowship-applications/update-fellowship-types
    #[Route(path: '/update-fellowship-types', name: 'fellapp_update_fellowship_types')]
    public function updateGlobalFellowshipTypesAction( Request $request )
    {
        exit("not allowed: updateGlobalFellowshipTypesAction");
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('fellapp.sitename') . '-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        $fellowshipSubspecialtyArr = [
            "Breast Pathology",
            "Gastrointestinal Pathology",
            "Genitourinary Pathology",
            "Gynecologic Pathology",
            "Renal Pathology",
            "Clinical Informatics",
        ];

        ////// get WashU pathology //////
        $washU = $em->getRepository(Institution::class)->findOneByAbbreviation("WashU");
        if( !$washU ) {
            exit('generateGlobalFellowshipSpecialtiesWahsu: No Institution: "WashU"');
        }
        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution',
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );
        $washUPathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
            "Pathology and Immunology",
            $washU,
            $mapper
        );
        if( !$washUPathology ) {
            exit('generateGlobalFellowshipSpecialtiesWahsu: No Institution: "Pathology and Immunology"');
        }
        echo "washUPathology=$washUPathology, ID=".$washUPathology->getId()." <br>";
        ////// EOF get WashU pathology //////

        $cytopathology = $em->getRepository(FellowshipSubspecialty::class)->findOneByName("Cytopathology");
        if( !$cytopathology ) {
            exit("FellowshipSubspecialty not found with name Cytopathology");
        }
        $globalCytopathology = $em->getRepository(GlobalFellowshipSpecialty::class)->findOneByName("Cytopathology");
        if( !$globalCytopathology ) {
            exit("GlobalFellowshipSpecialty not found with name Cytopathology");
        }

        $counter = 0;
        $counterGlobal = 0;

        $testing = true;
        //$testing = false;

        foreach( $fellowshipSubspecialtyArr as $fellappSpecialtyStr ) {
            //1) Find all fellowship applications with deleted fellappSpecialty
            //2) Find and replace deleted fellappSpecialty with other (i.e. Cytopathology)
            //3) Remove deleted fellappSpecialty
            $fellappSpecialtyStr = trim($fellappSpecialtyStr);
            echo "<br>### [$fellappSpecialtyStr] ###";

            /////////// Remove FellowshipSubspecialty ///////////////
            //1)
            //$fellappSubspecialty = $em->getRepository(FellowshipSubspecialty::class)->findOneByName(trim($fellappSpecialtyStr));
//            $fellappSubspecialty = $em->getRepository(FellowshipSubspecialty::class)
//                ->findBy([
//                    //'institution' => $washUPathology,
//                    'name'        => $fellappSpecialtyStr,
//                ]);
            $repo = $em->getRepository(FellowshipSubspecialty::class);
            $qb = $repo->createQueryBuilder('s')
                ->where('LOWER(s.name) = LOWER(:name)')
                //->andWhere('LOWER(s.institution) = LOWER(:institution)')
                ->setParameter('name', $fellappSpecialtyStr);
            $fellappSubspecialties = $qb->getQuery()->getResult();
            echo "<br>Found local count=".count($fellappSubspecialties)."<br>";

//            $globalFellappSpecialty = null;
//            if( count($fellappSubspecialties) == 1 ) {
//                $fellappSubspecialty = $fellappSubspecialties[0];
//            } else {
//                echo "<br>!!! Not found FellowshipSubspecialty by name=[$fellappSpecialtyStr] <br>";
//            }

            foreach($fellappSubspecialties as $fellappSubspecialty) {
                if ($fellappSubspecialty) {
                    echo "*** Found FellowshipSubspecialty [$fellappSubspecialty]<br>";
                    //2) Find fellowship applications FellowshipApplication
                    $fellapps = $em->getRepository(FellowshipApplication::class)
                        ->findBy([
                            'fellowshipSubspecialty' => $fellappSubspecialty,
                            //'institution'            => $washUPathology,
                        ]);
                    echo "fellapps=" . count($fellapps) . ": fellappSubspecialty=[$fellappSubspecialty]" . "<br>";
                    foreach ($fellapps as $fellapp) {
                        $fellapp->setFellowshipSubspecialty($cytopathology);
                        echo "Update fellapp ID=" . $fellapp->getId() . "<br>";
                    }
                    //Remove from Roles
                    $roles = $em->getRepository(Roles::class)->findBy([
                        'fellowshipSubspecialty' => $fellappSubspecialty,
                    ]);
                    echo "$fellappSpecialtyStr roles=" . count($roles) . "<br>";
                    foreach ($roles as $role) {
                        echo "Update role $fellappSpecialtyStr from role $role<br>";
                        $role->setFellowshipSubspecialty($cytopathology);
                    }
                    //3) Remove deleted fellappSpecialty
                    echo "***Remove FellowshipSubspecialty " . $fellappSubspecialty->getNameInstitution() . ",ID=" . $fellappSubspecialty->getId() . "<br>";
                    if (!$testing) {
                        //$em->remove($fellappSubspecialty);
                        //$em->flush();
                    }
                    $counter++;
                } else {
                    //exit("FellowshipSubspecialty not found with name $fellappSpecialtyStr");
                    echo "FellowshipSubspecialty not found with name [$fellappSpecialtyStr]" . "<br>";
                }
            }

            //////////// Remove GlobalFellowshipSpecialty //////////////
            //1)
            //$globalFellappSpecialty = $em->getRepository(GlobalFellowshipSpecialty::class)->findOneByName($fellappSpecialtyStr);
            $globalFellappSpecialties = $em->getRepository(GlobalFellowshipSpecialty::class)
                ->findBy([
                    //'institution' => $washUPathology,
                    'name'        => $fellappSpecialtyStr,
                ]);
            echo "Found global count=".count($globalFellappSpecialties)."<br>";
//            $repo = $em->getRepository(GlobalFellowshipSpecialty::class);
//            $qb = $repo->createQueryBuilder('s')
//                ->where('LOWER(s.name) = LOWER(:name)')
//                //->andWhere('LOWER(s.institution) = LOWER(:institution)')
//                ->setParameter('name', $fellappSpecialtyStr);
//            $globalFellappSpecialties = $qb->getQuery()->getResult();
//            $globalFellappSpecialty = null;
//            if( count($globalFellappSpecialties) == 1 ) {
//                $globalFellappSpecialty = $globalFellappSpecialties[0];
//            } else {
//                echo "<br>!!! Not found GlobalFellowshipSpecialty by name=[$fellappSpecialtyStr] <br>";
//            }
            foreach($globalFellappSpecialties as $globalFellappSpecialty) {
                if ($globalFellappSpecialty) {
                    echo "*** Found GlobalFellowshipSpecialty $globalFellappSpecialty<br>";
                    //2)
                    $globalFellapps = $em->getRepository(FellowshipApplication::class)
                        ->findBy([
                            'globalFellowshipSpecialty' => $globalFellappSpecialty,
                            //'institution'               => $washUPathology,
                        ]);
                    echo "fellapps=" . count($globalFellapps) . ": globalFellappSpecialty=$globalFellappSpecialty" . "<br>";
                    foreach ($globalFellapps as $globalFellapp) {
                        $globalFellapp->setGlobalFellowshipSpecialty($globalCytopathology);
                        echo "Update globalFellapp ID=" . $globalFellapp->getId() . "<br>";
                    }
                    //3) Remove deleted $globalFellappSpecialty
                    echo "***Remove GlobalFellowshipSpecialty " . $globalFellappSpecialty->getNameInstitution() . ",ID=" . $globalFellappSpecialty->getId() . "<br>";
                    if (!$testing) {
                        //$em->remove($globalFellappSpecialty);
                        //$em->flush();
                    }
                    $counterGlobal++;
                } else {
                    //exit("GlobalFellowshipSpecialty not found with name $fellappSpecialtyStr");
                    echo "GlobalFellowshipSpecialty not found with name $fellappSpecialtyStr" . "<br>";
                }
            }
        }

        exit("<br><br>end of updateGlobalFellowshipTypesAction, counter=$counter, counterGlobal=$counterGlobal");
    }
    //Keep only list of specialties according to getFellowshipTypesStrArr (WCM) or getFellowshipTypesWahsuStrArr (Washu)
    //http://127.0.0.1/fellowship-applications/filter-fellowship-types
    #[Route(path: '/filter-fellowship-types', name: 'fellapp_filter_fellowship_types')]
    public function filterFellowshipTypesAction( Request $request )
    {
        exit("not allowed: filterFellowshipTypesAction");
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('fellapp.sitename') . '-nopermission'));
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $em = $this->getDoctrine()->getManager();

        //get oleg_fellappbundle_fellappsiteparameter_localInstitution
        $userSecUtil = $this->container->get('user_security_utility');
        $localInstitutionName = $userSecUtil->getSiteSettingParameter('localInstitution', $this->getParameter('fellapp.sitename'));
        if( !$localInstitutionName ) {
            echo "localInstitution is not set => skip generation of the FellowshipSubspecialty <br>";
            return 0;
        }
        if( strtoupper($localInstitutionName) == 'WCM' ) {
            $fellowshipSubspecialtyArr = $fellappUtil->getFellowshipTypesStrArr(); //WCM generateAllFellowshipSubspecialties
        }
        if( strtoupper($localInstitutionName) == 'WASHU' ) {
            $fellowshipSubspecialtyArr = $fellappUtil->getFellowshipTypesWahsuStrArr(); //WASHU generateAllFellowshipSubspecialties
        }


        $cytopathology = $em->getRepository(FellowshipSubspecialty::class)->findOneByName("Cytopathology");
        if( !$cytopathology ) {
            exit("FellowshipSubspecialty not found with name Cytopathology");
        }

        //1) Find all fellowship fellappSpecialty
        $fellowshipSubspecialties = $em->getRepository(FellowshipSubspecialty::class)->findAll();

        $counter = 0;

        $testing = true;
        //$testing = false;

        foreach( $fellowshipSubspecialties as $fellappSubspecialty ) {

            //if not in $fellowshipSubspecialtyArr => remove $fellowshipSubspecialty
            $fellappSubspecialtyName = $fellappSubspecialty->getName();
            $fellappSpecialtyStr = trim($fellappSubspecialtyName);
            echo "<br>### [$fellappSpecialtyStr] ###";
            if( in_array($fellappSpecialtyStr, $fellowshipSubspecialtyArr, true) ) {
                // it's in the array -> skip
                continue;
            }

            //1) find all FellowshipApplication with $fellowshipSubspecialty
            //2) Find and replace deleted $fellowshipSubspecialty with other (i.e. Cytopathology)
            //3) Remove deleted $fellowshipSubspecialty

            if ($fellappSubspecialty) {
                echo "*** Found FellowshipSubspecialty [$fellappSubspecialty]<br>";
                //2) Find fellowship applications FellowshipApplication
                $fellapps = $em->getRepository(FellowshipApplication::class)
                    ->findBy([
                        'fellowshipSubspecialty' => $fellappSubspecialty,
                        //'institution'            => $washUPathology,
                    ]);
                echo "fellapps=" . count($fellapps) . ": fellappSubspecialty=[$fellappSubspecialty]" . "<br>";
                foreach ($fellapps as $fellapp) {
                    $fellapp->setFellowshipSubspecialty($cytopathology);
                    echo "Update fellapp ID=" . $fellapp->getId() . "<br>";
                }
                //Remove from Roles
                $roles = $em->getRepository(Roles::class)->findBy([
                    'fellowshipSubspecialty' => $fellappSubspecialty,
                ]);
                echo "$fellappSpecialtyStr roles=" . count($roles) . "<br>";
                foreach ($roles as $role) {
                    echo "Update role $fellappSpecialtyStr from role $role<br>";
                    $role->setFellowshipSubspecialty($cytopathology);
                }
                //3) Remove deleted fellappSpecialty
                echo "***Remove FellowshipSubspecialty " . $fellappSubspecialty->getNameInstitution() . ",ID=" . $fellappSubspecialty->getId() . "<br>";
                if( 1 ) {
                    $em->remove($fellappSubspecialty);
                    $em->flush();
                }
                $counter++;
            } else {
                //exit("FellowshipSubspecialty not found with name $fellappSpecialtyStr");
                echo "FellowshipSubspecialty not found with name [$fellappSpecialtyStr]" . "<br>";
            }
        }//foreach

        exit("<br><br>end of filterFellowshipTypesAction, counter=$counter");
    }

    //Keep only list of specialties according to getFellowshipTypesStrArr (WCM)
    //http://127.0.0.1/fellowship-applications/update-wcm-fellowship-types
    #[Route(path: '/update-wcm-fellowship-types', name: 'fellapp_update_wcm_fellowship_types')]
    public function updateWCMGlobalFellowshipTypesAction( Request $request ) {
        exit("not allowed: updateWCMGlobalFellowshipTypesAction");
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('fellapp.sitename') . '-nopermission'));
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $em = $this->getDoctrine()->getManager();

        //Keep only specialties defined in getFellowshipTypesStrArr for WCM
        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution',
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );

        $wcmc = $em->getRepository(Institution::class)->findOneByAbbreviation("WCM");
        $wcmPathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
            "Pathology and Laboratory Medicine",
            $wcmc,
            $mapper
        );

        $globalCytopathology = $em->getRepository(GlobalFellowshipSpecialty::class)->findOneByName("Cytopathology");
        if( !$globalCytopathology ) {
            exit("GlobalFellowshipSpecialty not found with name Cytopathology");
        }

        //1) Get WCM types
        $wcmFellTypes = $fellappUtil->getFellowshipTypesStrArr();
        echo "wcmFellTypes=".count($wcmFellTypes)."<br>";

        //1) Get all existing specialties for WCM
        $globalFellTypes = $fellappUtil->getGlobalFellowshipTypesByInstitution($wcmPathology,$asArray=false);
        echo "globalFellTypes=".count($globalFellTypes)."<br>";

        $counter = 0;

        foreach($globalFellTypes as $globalFellType) {
            $name = $globalFellType->getName();
            echo "<br>globalFellType=".$globalFellType->getNameInstitution().", name=".$name."<br>";

            if( in_array(strtolower($name), array_map('strtolower', $wcmFellTypes)) ) {
                //in array
                continue;
            }

            //not in wcm array => remove
            echo "### [$name] - not in wcm array => remove <br>";

            $globalFellapps = $em->getRepository(FellowshipApplication::class)
                ->findBy([
                    'globalFellowshipSpecialty' => $globalFellType,
                    'institution'               => $wcmPathology,
                ]);
            echo "fellapps=" . count($globalFellapps) . ": globalFellType=".$globalFellType->getName()."<br>";
            foreach ($globalFellapps as $globalFellapp) {
                $globalFellapp->setGlobalFellowshipSpecialty($globalCytopathology);
                echo "Update globalFellapp ID=" . $globalFellapp->getId() . "<br>";
            }
            //3) Remove deleted $globalFellappSpecialty
            echo "***Remove GlobalFellowshipSpecialty " . $globalFellType->getNameInstitution() . ",ID=" . $globalFellType->getId() . "<br>";
            if( 1 ) {
                $em->remove($globalFellType);
                $em->flush();
            }
            $counter++;
        }

        exit("<br><br>end of updateWCMGlobalFellowshipTypesAction, removed counter=$counter");
    }

    //Keep only list of specialties according to getFellowshipTypesWahsuStrArr (Washu)
    //http://127.0.0.1/fellowship-applications/update-washu-fellowship-types
    #[Route(path: '/update-washu-fellowship-types', name: 'fellapp_update_washu_fellowship_types')]
    public function updateWashuGlobalFellowshipTypesAction( Request $request ) {
        exit("not allowed: updateWashuGlobalFellowshipTypesAction");
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('fellapp.sitename') . '-nopermission'));
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $em = $this->getDoctrine()->getManager();

        //Keep only specialties defined in getFellowshipTypesStrArr for WCM
        $washU = $em->getRepository(Institution::class)->findOneByAbbreviation("WashU");
        if( !$washU ) {
            exit('generateGlobalFellowshipSpecialtiesWahsu: No Institution: "WashU"');
        }
        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution',
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );
        $washUPathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
            "Pathology and Immunology",
            $washU,
            $mapper
        );
        if( !$washUPathology ) {
            exit('generateGlobalFellowshipSpecialtiesWahsu: No Institution: "Pathology and Immunology"');
        }
        echo "washUPathology=$washUPathology, ID=".$washUPathology->getId()." <br>";

        $globalCytopathology = $em->getRepository(GlobalFellowshipSpecialty::class)->findOneByName("Cytopathology");
        if( !$globalCytopathology ) {
            exit("GlobalFellowshipSpecialty not found with name Cytopathology");
        }

        //1) Get WCM types
        $washuFellTypes = $fellappUtil->getFellowshipTypesWahsuStrArr();
        echo "washuFellTypes=".count($washuFellTypes)."<br>";

        //1) Get all existing specialties for WashU
        $globalFellTypes = $fellappUtil->getGlobalFellowshipTypesByInstitution($washUPathology,$asArray=false);
        echo "globalFellTypes=".count($globalFellTypes)."<br>";

        $counter = 0;

        foreach($globalFellTypes as $globalFellType) {
            $name = $globalFellType->getName();
            echo "<br>globalFellType=".$globalFellType->getNameInstitution().", name=".$name."<br>";

            if( in_array(strtolower($name), array_map('strtolower', $washuFellTypes)) ) {
                //in array
                echo "Skip ".strtolower($name)."<br>";
                continue;
            }

            //not in wcm array => remove
            echo "### [$name] - not in washu array => remove <br>";

            $globalFellapps = $em->getRepository(FellowshipApplication::class)
                ->findBy([
                    'globalFellowshipSpecialty' => $globalFellType,
                    'institution'               => $washUPathology,
                ]);
            echo "fellapps=" . count($globalFellapps) . ": globalFellType=".$globalFellType->getName()."<br>";
            foreach ($globalFellapps as $globalFellapp) {
                $globalFellapp->setGlobalFellowshipSpecialty($globalCytopathology);
                echo "Update globalFellapp ID=" . $globalFellapp->getId() . "<br>";
            }
            //3) Remove deleted $globalFellappSpecialty
            echo "***Remove GlobalFellowshipSpecialty " . $globalFellType->getNameInstitution() . ",ID=" . $globalFellType->getId() . "<br>";
            if( 1 ) {
                $em->remove($globalFellType);
                $em->flush();
            }
            $counter++;
        }

        exit("<br><br>end of updateWashuGlobalFellowshipTypesAction, removed counter=$counter");
    }

    //http://127.0.0.1/fellowship-applications/populate-fellapp-users
    #[Route(path: '/populate-fellapp-users', name: 'fellapp_fellapp_users')]
    public function populateFellappUsersAction( Request $request )
    {
        exit("not allowed: populateFellappUsersAction");
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('fellapp.sitename') . '-nopermission'));
        }

//        Coordinators
//        Naomi Rattler, burr@wustl.edu
//        Molly Newport, nmolly@wustl.edu
//        Kim Green, greenkd@wustl.edu

        ////// get WashU pathology //////
        $em = $this->getDoctrine()->getManager();
        $washU = $em->getRepository(Institution::class)->findOneByAbbreviation("WashU");
        if( !$washU ) {
            exit('generateGlobalFellowshipSpecialtiesWahsu: No Institution: "WashU"');
        }
        $mapper = array(
            'prefix' => 'App',
            'bundleName' => 'UserdirectoryBundle',
            'className' => 'Institution',
            'fullClassName' => "App\\UserdirectoryBundle\\Entity\\Institution",
            'entityNamespace' => "App\\UserdirectoryBundle\\Entity"
        );
        $washUPathology = $em->getRepository(Institution::class)->findByChildnameAndParent(
            "Pathology and Immunology",
            $washU,
            $mapper
        );
        if( !$washUPathology ) {
            exit('generateGlobalFellowshipSpecialtiesWahsu: No Institution: "Pathology and Immunology"');
        }
        echo "washUPathology=$washUPathology, ID=".$washUPathology->getId()." <br>";
        ////// EOF get WashU pathology //////

        //$inputFileName = 'C:\Users\cinav\Documents\WCMC\Users\ImportFellappUsers.csv';
        $projectRoot = $this->container->get('kernel')->getProjectDir(); // /srv/order-lab-tenantappdemo/orderflex
        echo '$projectRoot='.$projectRoot.'<br>';
        $inputFileName = $projectRoot . '/src/App/FellAppBundle/Util/ImportFellappUsers.csv';

        $userGenerator = $this->container->get('user_generator');
        $res = $userGenerator->generateSimpleUsersExcel($inputFileName,$washUPathology);

        exit($res);
    }

    //http://127.0.0.1/fellowship-applications/set-show-specialties
    #[Route(path: '/set-show-specialties', name: 'fellapp_set_show_specialties')]
    public function setFellowshipSpecialtiesShowAction( Request $request )
    {
        //exit("not allowed: setFellowshipSpecialtiesShowAction");
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('fellapp.sitename') . '-nopermission'));
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $em = $this->getDoctrine()->getManager();

        $fellowshipSpecialties = $fellappUtil->getValidFellowshipTypes($asEntities=true);
        echo "Valid fellowshipSpecialties=".count($fellowshipSpecialties)."<br>";
        foreach($fellowshipSpecialties as $fellowshipSpecialty) {
            if( $fellowshipSpecialty->getShowOption() === null ) {
                $fellowshipSpecialty->setShowOption(true);
                echo "setShowOption ID=" . $fellowshipSpecialty->getName() . "<br>";
            }
        }

        $globalFellTypes = $fellappUtil->getGlobalFellowshipTypesByInstitution($institution=null,$asArray=false);
        echo "Valid globalFellTypes=".count($globalFellTypes)."<br>";
        foreach($globalFellTypes as $globalFellType) {
            if( $globalFellType->getShowOption() === null ) {
                $globalFellType->setShowOption(true);
                echo "Global setShowOption ID=" . $globalFellType->getName() . "<br>";
            }
        }

        $em->flush();
        exit("setFellowshipSpecialtiesShowAction");
    }
}
