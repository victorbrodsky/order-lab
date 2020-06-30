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

use App\ResAppBundle\Entity\ResidencyApplication;
use App\ResAppBundle\Util\ImportFromOldSystem;
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
     * @Route("/thanks-for-downloading/{id}/{sitename}", name="resapp_thankfordownloading", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Default/thanksfordownloading.html.twig")
     */
    public function thankfordownloadingAction(Request $request, $id, $sitename) {
        return array(
            'fileid' => $id,
            'sitename' => $sitename
        );
    }


    /**
     * @Route("/about", name="resapp_about_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array('sitename'=>$this->getParameter('resapp.sitename'));
    }



    /**
     * 127.0.0.1/order/residency-applications/test_google_file
     *
     * @Route("/test_google_file", name="resapp_test_google_file")
     */
    public function testGoogleFileAction( Request $request ) {

        //$resappRecLetterUtil = $this->get('resapp_rec_letter_util');
        //$result2 = $resappRecLetterUtil->processResRecLetterFromGoogleDrive();
        //echo $result2."<br>";

        //exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        $emailUtil->testEmailWithAttachments();
        exit("EOF testEmailWithAttachments");

        //test 1) sendRefLetterReceivedNotificationEmail
        $resappRecLetterUtil = $this->container->get('resapp_rec_letter_util');
        $resapp = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication')->find(1414); //8-testing, 1414-collage, 1439-live
        $references = $resapp->getReferences();
        $reference = $references->first();
        $letters = $reference->getDocuments();
        $uploadedLetterDb = $letters->first();
        $res = $resappRecLetterUtil->sendRefLetterReceivedNotificationEmail($resapp,$uploadedLetterDb);

        $resappType = $resapp->getResidencySubspecialty();
        echo "ID=".$resapp->getId().", resappType=".$resappType.": res=".$res."<br>";

        exit("end of sendRefLetterReceivedNotificationEmail test");

        
        //test 2)
        $resappImportPopulateUtil = $this->container->get('resapp_importpopulate_util');

        $inputFileName = "Uploaded/resapp/Spreadsheets/test-resapp3";

        $applications = $resappImportPopulateUtil->populateSpreadsheet($inputFileName);

        exit("end of resapp test");
    }

    //generateRecLetterId
    /**
     * @Route("/generate-rec-letter-id", name="resapp_rec_letter_id")
     */
    public function generateRecLetterIdAction( Request $request ) {

        //testing checkAndSendCompleteEmail
        //$resappRecLetterUtil = $this->container->get('resapp_rec_letter_util');
        //$resapp = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication')->find(8);
        //$resappRecLetterUtil->checkAndSendCompleteEmail($resapp);

        //testing checkReferenceAlreadyHasLetter
        //$resappRecLetterUtil = $this->container->get('resapp_rec_letter_util');
        //$resapp = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication')->find(1414); //8-test,1414-collage
        //$reference = $resapp->getReferences()->first();
        //$resappRecLetterUtil->checkReferenceAlreadyHasLetter($resapp,$reference);

        exit("not allowed. one time run method.");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $resappRecLetterUtil = $this->container->get('resapp_rec_letter_util');

        $repository = $this->getDoctrine()->getRepository('AppResAppBundle:ResidencyApplication');
        $dql =  $repository->createQueryBuilder("resapp");
        $dql->select('resapp');
        $dql->leftJoin("resapp.references", "references");
        $dql->where("references.recLetterHashId IS NULL");
        $dql->orderBy("resapp.id","DESC");
        $query = $em->createQuery($dql);
        $resapps = $query->getResult();
        echo "resapps count=".count($resapps)."<br>";

        foreach($resapps as $resapp) {
            $references = $resapp->getReferences($resapp);

            foreach($references as $reference) {
                $hash = $resappRecLetterUtil->generateRecLetterId($resapp,$reference,$request);
                if( $hash ) {
                    $reference->setRecLetterHashId($hash);
                    $em->flush($reference);
                    echo $resapp->getId()." (".$reference->getId()."): added hash=".$hash."<br>";
                }
            }

        }

        exit("end of generateRecLetterIdAction");
    }

    /**
     * @Route("/confirmation/{id}", name="resapp_simple_confirmation")
     * @Template("AppResAppBundle/Default/simple-confirmation.html.twig")
     */
    public function confirmationAction( Request $request, ResidencyApplication $resapp ) {

        return array(
            'entity' => $resapp
        );
    }

    /**
     * http://127.0.0.1/order/residency-applications/generate-thumbnails
     * 
     * @Route("/generate-thumbnails", name="resapp_generate_thumbnails")
     */
    public function generateThumbnailsAction( Request $request ) {

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        $userServiceUtil = $this->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();

        //get spreadsheets older than X year
        $repository = $em->getRepository('AppUserdirectoryBundle:Document');
        $dql =  $repository->createQueryBuilder("document");
        $dql->select('document');
        $dql->leftJoin('document.type','documentType');

        //$dql->where("documentType.name = 'Residency Photo'");
        $dql->where("documentType.name = 'Residency Photo' OR documentType.name = 'Avatar Image'");

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

        exit("end of resapp thumbnails, counter=$counter");
    }

    /**
     * @Route("/import-from-old-system/{max}", name="resapp_import_from_old_system")
     */
    public function importFromOldSystemAction( Request $request, ImportFromOldSystem $importFromOldSystemUtil, $max=NULL ) {

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        //$importFromOldSystemUtil = $this->container->get('resapp_rec_letter_util');

        $res = $importFromOldSystemUtil->importApplications($max);
        
        exit($res);
    }
    /**
     * @Route("/import-from-old-system-files1/{max}", name="resapp_import_from_old_system_files1")
     */
    public function importFromOldSystem2Action( Request $request, ImportFromOldSystem $importFromOldSystemUtil, $max=NULL ) {

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        //$importFromOldSystemUtil = $this->container->get('resapp_rec_letter_util');

        $dataFileFolder = "DB_file1";
        $dataFileName = $dataFileFolder . DIRECTORY_SEPARATOR . "PRA_APPLICANT_CV_INFO.csv";
        $fileTypeName = 'ERAS1';
        $res = $importFromOldSystemUtil->importApplicationsFiles($max,$dataFileName,$dataFileFolder,$fileTypeName);

        exit($res);
    }
    /**
     * @Route("/import-from-old-system-files2/{max}", name="resapp_import_from_old_system_files2")
     */
    public function importFromOldSystem3Action( Request $request, ImportFromOldSystem $importFromOldSystemUtil, $max=NULL ) {

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        //$importFromOldSystemUtil = $this->container->get('resapp_rec_letter_util');

        $dataFileFolder = "DB_file2";
        $dataFileName = $dataFileFolder . DIRECTORY_SEPARATOR . "PRA_APPLICANT_UPDATE_CV_INFO.csv";
        $fileTypeName = 'ERAS2';
        $res = $importFromOldSystemUtil->importApplicationsFiles($max,$dataFileName,$dataFileFolder,$fileTypeName);

        exit($res);
    }

    /**
     * http://127.0.0.1/order/index_dev.php/residency-applications/import-from-old-system-interviewers
     *
     * @Route("/import-from-old-system-interviewers", name="resapp_import_from_old_system_interviewers")
     */
    public function importFromOldSystemInterviewersAction( Request $request, ImportFromOldSystem $importFromOldSystemUtil ) {

        //exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        //$importFromOldSystemUtil = $this->container->get('resapp_rec_letter_util');

        $allowCreate = true;
        $allowCreate = false;
        $res = $importFromOldSystemUtil->getFacultyResident($allowCreate);

        exit($res);
    }
    /**
     * http://127.0.0.1/order/index_dev.php/residency-applications/import-from-old-system-interview
     * 
     * @Route("/import-from-old-system-interview/{max}", name="resapp_import_from_old_system_interview")
     */
    public function importFromOldSystem4Action( Request $request, ImportFromOldSystem $importFromOldSystemUtil, $max=NULL ) {

        //exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        //$importFromOldSystemUtil = $this->container->get('resapp_rec_letter_util');

        $res = $importFromOldSystemUtil->importApplicationsFilesInterview($max);

        exit($res);
    }
}
