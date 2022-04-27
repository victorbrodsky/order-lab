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

        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();
        $folderId = "1gapiVoGBGzOZ5frPcBiXjRSC8Wbz6H8l"; //$userSecUtil->getSiteSettingParameter('configFileFolderIdFellApp');
        $fileName = "wcmpath_29be771f19e9fc0ab21f874990e29ab7038d3fba_2021-07-09-14-15-59_Shabna.doc";
        $file = $googlesheetmanagement->findOneFolderByFolderNameAndParentFolder($service, $folderId, $fileName);
        if( $file ) {
            dump($file);
        }
        exit('111');
        
        /////////// EOF testing ///////////

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
//        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
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

        exit("not allowed: generateThumbnailsAction");

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

}
