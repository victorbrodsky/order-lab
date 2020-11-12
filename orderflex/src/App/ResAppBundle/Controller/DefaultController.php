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

//        $fits = $this->getDoctrine()->getRepository('AppResAppBundle:ResAppFitForProgram')->findAll();
//        foreach($fits as $fit) {
//            echo "fit=$fit <br>";
//        }

//        //testing extract pdf as array of keys
//        $resappPdfUtil = $this->get('resapp_pdfutil');
//        //$keyFieldArr = $resappPdfUtil->getKeyFieldArr();
//        //dump($keyFieldArr);
//        $path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\ResidencyImport\\Test1\\StevenAdams_Original_MY_ERAS_APPLICATION_2020-10-23-124716_4470ce61-5d2c-4b8d-b163-547adc95123d.pdf";
//        $keysArr = $resappPdfUtil->extractPdfText($path,false);
//        dump($keysArr);
//        $path = "C:\\Users\\ch3\\Documents\\MyDocs\\WCMC\\ORDER\\ResidencyImport\\Test1\\BarbatiZ_13018609_f2d01751-e7f9-41f6-97b5-da8289db6137.pdf"; //custom
//        $keysArr = $resappPdfUtil->extractPdfText($path,false);
//        dump($keysArr);
//        exit('111');

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

        $resappType = $resapp->getResidencyTrack();
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

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        //$importFromOldSystemUtil = $this->container->get('resapp_rec_letter_util');

        $allowCreate = true;
        //$allowCreate = false;
        $res = $importFromOldSystemUtil->getFacultyResident($allowCreate);

        exit($res);
    }
    /**
     * http://127.0.0.1/order/index_dev.php/residency-applications/import-from-old-system-interview
     * 
     * @Route("/import-from-old-system-interview/{max}", name="resapp_import_from_old_system_interview")
     */
    public function importFromOldSystem4Action( Request $request, ImportFromOldSystem $importFromOldSystemUtil, $max=NULL ) {

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        //$importFromOldSystemUtil = $this->container->get('resapp_rec_letter_util');

        $res = $importFromOldSystemUtil->importApplicationsFilesInterview($max);

        exit($res);
    }


    /**
     * http://127.0.0.1/order/index_dev.php/residency-applications/update-application-season-start-date
     *
     * @Route("/update-application-season-start-date", name="resapp_update_application_season_start_date")
     */
    public function updateApplicationSeasonStartDateAction( Request $request ) {

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        ini_set('max_execution_time', 1800); //1800 seconds = 30 minutes;

        $em = $this->getDoctrine()->getManager();

        //get spreadsheets older than X year
        $repository = $em->getRepository('AppResAppBundle:ResidencyApplication');
        $dql =  $repository->createQueryBuilder("application");
        $dql->select('application');

        //$dql->where("documentType.name = 'Residency Photo'");
        //$dql->where("application.applicationSeasonStartDate IS NULL");
        $dql->where("application.applicationSeasonEndDate IS NULL");

        $dql->orderBy("application.id","ASC");

        $query = $em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";

        $applications = $query->getResult();
        echo "applications count=".count($applications)."<br>";

        $counter = 0;

        foreach($applications as $application) {

            $counter++;

            $startDate = $application->getStartDate();
            $endDate = $application->getEndDate();

            echo $counter." (ID=".$application->getId().", ExternalID=".$application->getGoogleFormId()."): Date1=".
                $startDate->format('Y-m-d')."~".$endDate->format('Y-m-d')." => ";

            if( !$startDate ) {
                exit("No start date");
            }
            if( !$endDate ) {
                exit("No end date");
            }

            //Copy the values for all residency applications from “Start Year” to “Application Season Start Year”
            $application->setApplicationSeasonStartDate( $application->getStartDate() );
            $application->setApplicationSeasonEndDate( $application->getEndDate() );

            //$em->flush();

            //Set Start/End date +1 year
            $startDate2 = $application->getStartDate();
            $startDatePlusOne = clone $startDate2;

            $endDate2 = $application->getEndDate();
            $endDatePlusOne = clone $endDate2;

            //Usually: $startDate = $applicationSeasonStartDate + 1 year
            $startDatePlusOne->modify('+1 year');
            $endDatePlusOne->modify('+1 year');
            echo "Date2=".$startDatePlusOne->format('Y-m-d').", ".$endDatePlusOne->format('Y-m-d')."<br>";
            $application->setStartDate($startDatePlusOne);
            $application->setEndDate($endDatePlusOne);

            //$em->flush();

            //exit("EOF");
        }

        $res = "Update application season start date: counter=$counter";

        exit($res);
    }

    /**
     * http://127.0.0.1/order/index_dev.php/residency-applications/update-application-residency-track
     *
     * @Route("/update-application-residency-track", name="resapp_update_application_residency_track")
     */
    public function updateApplicationResidencyTrackAction( Request $request ) {

        exit("not allowed");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('resapp.sitename').'-nopermission') );
        }

        ini_set('max_execution_time', 1800); //1800 seconds = 30 minutes;

        $em = $this->getDoctrine()->getManager();

        //get spreadsheets older than X year
        $repository = $em->getRepository('AppResAppBundle:ResidencyApplication');
        $dql =  $repository->createQueryBuilder("application");
        $dql->select('application');

        $dql->where("application.residencyTrack IS NULL");

        $dql->orderBy("application.id","ASC");

        $query = $em->createQuery($dql);

        //echo "query=".$query->getSql()."<br>";

        $applications = $query->getResult();
        echo "applications count=".count($applications)."<br>";

        $counter = 0;

        foreach($applications as $application) {

            $counter++;

            $residencySubspecialty = $application->getResidencySubspecialty();

            echo $counter." (ID=".$application->getId().", ExternalID=".$application->getGoogleFormId()."): residencySubspecialty=". $residencySubspecialty."";

            if( !$residencySubspecialty ) {
                exit("No residencySubspecialty");
            }

            //convert residencySubspecialty to residencyTrack
            $name = NULL;
            switch ($residencySubspecialty) {
                case "Pathology AP/EXP":
                    $name = "AP/EXP";
                    break;
                case "Pathology CP/EXP":
                    $name = "CP/EXP";
                    break;
                case "AP/CP":
                    $name = "AP/CP";
                    break;
                case "CP":
                    $name = "CP";
                    break;
                case "AP":
                    $name = "AP";
                    break;
                default:
                    exit("No Residency Track name found");
            }

            if( !$name ) {
                exit("No Residency Track name is NULL");
            }

            $residencyTrack = $em->getRepository("AppUserdirectoryBundle:ResidencyTrackList")->findOneByName($name);
            if( !$residencyTrack ) {
                exit("Residency Track entity not found by name=[".$name."]");
            }

            echo " => residencyTrack=".$residencyTrack->getName()."<br>";

            $application->setResidencyTrack( $residencyTrack );

            $em->flush();

            //exit("EOF");
        }

        $res = "Update application Residency Track: counter=$counter";

        exit($res);
    }
}
