<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\UserdirectoryBundle\Util\LargeFileDownloader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * @Route("/about", name="translationalresearch_about_page")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array('sitename'=>$this->container->getParameter('translationalresearch.sitename'));
    }


//    /**
//     * @Route("/", name="translationalresearch_home")
//     * @Template("OlegTranslationalResearchBundle:Default:index.html.twig")
//     * @Method("GET")
//     */
//    public function indexAction( Request $request ) {
//
//        if( false == $this->get('security.context')->isGranted('ROLE_TRANSRES_USER') ){
//            //exit('deidentifier: no permission');
//            return $this->redirect( $this->generateUrl('translationalresearch-nopermission') );
//        }
//
//        return $this->redirect( $this->generateUrl('translationalresearch_project_index') );
//
////        return array(
////            'title' => "Translational Research"
////        );
//    }


    /**
     * @Route("/download/humanTissueForm", name="translationalresearch_download_humanTissueForm")
     */
    public function downloadHumanTissueFormAction( Request $request ) {

        $originalname = "human_tissue_request_form.pdf";
        $abspath = "";

        $abspath = "bundles\\olegtranslationalresearch\\downloads\\".$originalname;

        $size = null;//$document->getSize();

        $downloader = new LargeFileDownloader();
        $downloader->downloadLargeFile($abspath, $originalname, $size);

        exit;
    }


    /**
     * http://localhost/order/translational-research/import-old-data/0
     * 1) Disable comments first in the FosCommentListener - $disable = true
     * 2) Make sure the Admin and default AP-CP reviewers are set correctly
     * 3) Run Steps 1, 2, 3 and 4
     * 4) Run Step 5
     * 
     * @Route("/import-old-data/{startRow}", name="translationalresearch_import_old_data")
     * @Method({"GET"})
     */
    public function importOldDataAction(Request $request, $startRow=null) {

        if( !$this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //exit('not allowed');
        set_time_limit(10800); //18000 seconds => 5 hours
        ini_set('memory_limit', '7168M');

        $em = $this->getDoctrine()->getManager();
        echo "startRow=".$startRow."<br>";

        $resProject = null;
        $resAdminComments = null;

        $resArr = array();

        $importUtil = $this->get('transres_import');

        //Step 1: import projects and admin Comments from TRF_PROJECT_INFO
        if(0) {
            //import projects
            $resArr[] = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'project_adminComments');
        }
        if(0) {
            //import projects
            $resProject = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'project');
            //import admin comments
            //$resAdminComments = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'adminComments');

            $resArr[] = $resProject . "<br>========= EOF TRF_PROJECT_INFO ===========<br>" . $resAdminComments;
        }
        if(0) {
            //edit project
            $resArr[] = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'project_edit');
        }

        //Step 2: import projects and admin Comments from TRF_DRAFT_PROJECT
        if(0) {
            //import projects
            $resArr[] = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT_INFO.xlsx', 'project_adminComments');
        }
        if(0) {
            //import projects
            $resProject = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT.xlsx', 'project');
            //import admin comments
            $resAdminComments = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT.xlsx', 'adminComments');

            $resArr[] = $resProject . "<br>========= EOF TRF_DRAFT_PROJECT ===========<br>" . $resAdminComments;
        }
        if(0) {
            //edit project
            $resArr[] = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT.xlsx', 'project_edit');
        }

        //Step 3: import Committee Comments from TRF_COMMITTEE_REV
        //Committee comments
        if(0) {
            $resArr[] = $importUtil->importCommitteeComments($request, 'TRF_COMMITTEE_REVIEW_INFO.xlsx');
        }

        //Step 4: import Committee Comments from TRF_COMMENTS_RESP
        if(0) {
            $resArr[] = $importUtil->importCommitteeComments2($request, 'TRF_COMMENTS_RESPONSE_INFO.xlsx');
        }

        //Step 5: import working requests (~14k ~10 hours)
        if(1) {
            //use only 1000 per time
//            $startRow = 2;
//            $endRow = $startRow + 1000;
//            echo "Start: $startRow, end: $endRow <br>";
//            $time_start = microtime(true);
//            $resCount = $importUtil->importWorkRequests($request, 'TRF_REQUESTED_2.xlsx', $startRow, $endRow);
//            $time_end = microtime(true);
//
//            //dividing with 60 will give the execution time in minutes otherwise seconds
//            $execution_time = ($time_end - $time_start)/60;
//            //execution time of the script
//            //echo '<b>Total Execution Time:</b> '.$execution_time.' Mins <br>';
//            echo '<b>Total Execution Time:</b> '.number_format((float) $execution_time, 2).' Mins <br>';

            if( !$startRow ) {
                $startRow = 2;
            }

            //$filename = 'TRF_REQUESTED_SERVICE_INFO.xlsx';
            $filename = 'TRF_REQUESTED_SERVICE_INFO_1.xlsx';

            //check
            //$this->importRequests($request,$filename,$startRow,null);

            $this->importRequests($request,$filename,$startRow,1000);

            //$this->importRequests($request,$filename,3000);
            //$this->importRequests($request,$filename,2,1000);
            //$this->importRequests($request,$filename,5000);

        }

        //6) Update Request from "UpdatedReqStatus.xlsx": Price, Status, Comment
        if(0) {
            $filename = "UpdatedReqStatus.xlsx";
            $resArr[] = $importUtil->updateRequests($request,$filename);
        }

        ///////////////////// AUX Functions ///////////////////////
        //edit requests without oid
        if(0) {
            $repository = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
            $dql =  $repository->createQueryBuilder("request");
            $dql->select('request');

            $dql->leftJoin('request.principalInvestigators','principalInvestigators');

            $dql->andWhere("request.oid IS NULL");
            //$dql->andWhere("project.oid IS NULL");
            //$dql->andWhere("principalInvestigators.id IS NULL");

            $query = $dql->getQuery();

            $requests = $query->getResult();
            echo "requests count=".count($requests)."<br>";

            foreach($requests as $transresRequest) {
                $transresRequest->generateOid();
                $em->flush($transresRequest);
            }
        }

        //add missing request's comment
        if(0) {
            $filename = 'TRF_REQUESTED_1.xlsx';
            $startRow = 2;//3908;
            $endRow = null;//3927;
            $resCount = $importUtil->editWorkRequests($request, $filename, $startRow, $endRow);
        }
        ///////////////////// EOF AUX Functions ///////////////////////

        $res = implode("<br><br>",$resArr);

        exit('Imported result: '.$res);

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Imported result: '.$res
        );
        
        return $this->redirectToRoute('translationalresearch_home');
    }

    public function importRequests( $request, $filename, $startRow, $increment=1000 ) {
        set_time_limit(10800); //18000 seconds => 5 hours
        ini_set('memory_limit', '7168M');

        $importUtil = $this->get('transres_import');
        //use only 500 per time
        //$startRow = 2;
        if( $increment ) {
            $endRow = $startRow + $increment;
        } else {
            $endRow = null;
        }
        echo "Start: $startRow, end: $endRow <br>";
        $time_start = microtime(true);
        $resCount = $importUtil->importWorkRequests($request, $filename, $startRow, $endRow);
        $time_end = microtime(true);

        //dividing with 60 will give the execution time in minutes otherwise seconds
        $execution_time = ($time_end - $time_start)/60;
        //execution time of the script
        //echo '<b>Total Execution Time:</b> '.$execution_time.' Mins <br>';
        echo '<b>Imported '.$resCount.' requests; Total Execution Time:</b> '.number_format((float) $execution_time, 2).' Mins <br>';
    }


    /**
     * http://localhost/order/translational-research/barcode-demo
     *
     * @Route("/barcode-demo", name="translationalresearch_barcode-demo")
     * @Template("OlegTranslationalResearchBundle:Request:barcodedemo.html.twig")
     */
    public function barcodeDemoAction( Request $request ) {
        return array();
    }

    /**
     * generateAntibodyList and setAntibodyListProperties
     * run: http://localhost/order/translational-research/generate-antibody-list/ihc_antibody_mssql.sql
     * @Route("/generate-antibody-list/{filename}", name="translationalresearch_generate_antibody_list")
     */
    public function generateAntibodyListAction(Request $request, $filename) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        //exit("generateAntibodyList: ".$filename);

        $importUtil = $this->get('transres_import');
        $res = $importUtil->createAntibodyList($filename);
        //exit("generateAntibodyListAction: Finished with res=".$res);

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            "Antibody list imported result ($filename): <br>".$res
        );

        //exit("res=".$res);
        return $this->redirectToRoute('employees_siteparameters');
    }
    /**
     * Load Antibody list into Platform List Manager
     * run: http://localhost/order/translational-research/set-properties-antibody-list/
     * @Route("/set-properties-antibody-list/", name="translationalresearch_set_properties_antibody_list")
     */
    public function setPropertiesAntibodyListAction() {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        $importUtil = $this->get('transres_import');
        $res = $importUtil->setAntibodyListProperties();

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Antibody set properties result: '.$res
        );

        //exit("res=".$res);
        return $this->redirectToRoute('employees_siteparameters');
    }

    

}
