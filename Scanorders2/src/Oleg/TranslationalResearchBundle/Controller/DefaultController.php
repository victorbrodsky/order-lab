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
     * @Route("/import-old-data/", name="translationalresearch_import_old_data")
     * @Method({"GET"})
     */
    public function importOldDataAction(Request $request) {

        if( !$this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        set_time_limit(0);
        ini_set('memory_limit', '2048M');

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
            //$resProject = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'project');
            //import admin comments
            $resAdminComments = $importUtil->importOldData($request, 'TRF_PROJECT_INFO.xlsx', 'adminComments');

            $resArr[] = $resProject . "<br>========= EOF TRF_PROJECT_INFO ===========<br>" . $resAdminComments;
        }

        //Step 2: import projects and admin Comments from TRF_DRAFT_PROJECT
        if(0) {
            //import projects
            $resArr[] = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT.xlsx', 'project_adminComments');
        }
        if(0) {
            //import projects
            $resProject = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT.xlsx', 'project');
            //import admin comments
            $resAdminComments = $importUtil->importOldData($request, 'TRF_DRAFT_PROJECT.xlsx', 'adminComments');

            $resArr[] = $resProject . "<br>========= EOF TRF_DRAFT_PROJECT ===========<br>" . $resAdminComments;
        }

        //Step 3: import Committee Comments from TRF_COMMITTEE_REV
        //Committee comments
        if(0) {
            $resArr[] = $importUtil->importCommitteeComments($request, 'TRF_COMMITTEE_REV.xlsx');
        }

        //Step 4: import Committee Comments from TRF_COMMENTS_RESP
        if(0) {
            $resArr[] = $importUtil->importCommitteeComments2($request, 'TRF_COMMENTS_RESP.xlsx');
        }

        if(1) {
            $resArr[] = $importUtil->importWorkRequests($request, 'TRF_REQUESTED_SER.xlsx');
        }

        $res = implode("<br><br>",$resArr);

        exit('Imported result: '.$res);

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Imported result: '.$res
        );
        
        return $this->redirectToRoute('translationalresearch_home');
    }


}
