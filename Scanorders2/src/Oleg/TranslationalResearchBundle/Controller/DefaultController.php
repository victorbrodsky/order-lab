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

        exit('not allowed');
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
        if(0) {
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
            $filename = "Updated_All_REQUESTS_08202018.xlsx";
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
     * run: http://127.0.0.1/order/translational-research/generate-antibody-list/ihc_antibody_mssql.sql
     * run: http://127.0.0.1/order/translational-research/generate-antibody-list/ihc_antibody_mssql_2.sql
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

    /**
     * Update or Insert AntibodyList
     * run: http://127.0.0.1/order/translational-research/update-insert-antibody-list
     * @Route("/update-insert-antibody-list", name="translationalresearch_update_insert_antibody_list")
     */
    public function updateInsertAntibodyListAction(Request $request) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        //exit("generateAntibodyList: ".$filename);

        $importUtil = $this->get('transres_import');

        $filename = "IHC_antibody-11_16Nov2018.csv";
        $res = $importUtil->updateInsertAntibodyList($filename);
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
     * Sync ID for AntibodyList
     * run: http://127.0.0.1/order/translational-research/sync-id-antibody-list
     * @Route("/sync-id-antibody-list", name="translationalresearch_sync_id_antibody_list")
     */
    public function syncIdAntibodyListAction(Request $request) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        //exit("generateAntibodyList: ".$filename);

        $importUtil = $this->get('transres_import');
        $res = $importUtil->syncIdAntibodyList();

//        $em = $this->getDoctrine()->getManager();
//
//        $antibodies = $this->em->getRepository('OlegTranslationalResearchBundle:AntibodyList')->findAll();
//        foreach( $antibodies as $antibody ) {
//            $exportId = $antibody->getExportId();
//            if( $exportId && $exportId != $antibody->getId() ) {
//                //Explicitly set Id with Doctrine when using “AUTO” strategy
//                $metadata = $this->em->getClassMetaData(get_class($antibody));
//                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
//                $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
//            }
//        }

        //Flash
        $this->get('session')->getFlashBag()->add(
            'notice',
            "Antibody sync id result: <br>".$res
        );

        //exit("res=".$res);
        return $this->redirectToRoute('employees_siteparameters');
    }

    /**
     * http://127.0.0.1/order/translational-research/update-projects-implicit-date
     *
     * @Route("/update-projects-implicit-date", name="translationalresearch_update_projects_implicit_date")
     */
    public function updateProjectsImplicitDateAction( Request $request ) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        exit("updateProjectsImplicitDateAction: Not allowed");

        $count = $this->updateCommentObject($request);
        exit("<br>End of update comment's object: ".$count);

        $count = $this->updateInvoicePaidDue($request);
        exit("End of update invoice's paid and due: ".$count);

        $em = $this->getDoctrine()->getManager();
        $projects = $em->getRepository('OlegTranslationalResearchBundle:Project')->findAll();

        //$project = $em->getRepository('OlegTranslationalResearchBundle:Project')->find(3294);
        //$projects = array($project);

        //$batchSize = 20;
        $i = 0;

        foreach($projects as $project) {
            $implicitExpDate = $project->calculateAndSetImplicitExpirationDate();
            if($implicitExpDate) {
                $i++;
                //echo "update implicitExpDate=" . $implicitExpDate->format('Y-m-d') . "<br>";
                $em->flush($project);
            }
            //$em->persist($project);
//            if (($i % $batchSize) === 0) {
//                $em->flush($project);
//                $em->clear(); // Detaches all objects from Doctrine!
//            }
//            $i++;
        }
        //$em->flush(); //Persist objects that did not make up an entire batch
        //$em->clear();

        exit("End of update project's implicit dates: ".$i);
    }

//    /**
//     * http://127.0.0.1/order/translational-research/update-invoice-paid-due
//     *
//     * @Route("/update-invoice-paid-due", name="translationalresearch_update_invoice_paid_due")
//     */
    public function updateInvoicePaidDue( Request $request ) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        exit("updateInvoicePaidDue: Not allowed");

        $em = $this->getDoctrine()->getManager();
        $invoices = $em->getRepository('OlegTranslationalResearchBundle:Invoice')->findByStatus('Paid in Full');
        echo "Found invoices=".count($invoices)."<br>";

        $i = 1;

        foreach($invoices as $invoice) {
            if ($invoice->getStatus() == "Paid in Full") {
                if( $invoice->getDue() && !$invoice->getPaid() ) {
                    echo $i.": Original: total=".$invoice->getTotal()."; paid=".$invoice->getPaid()."; due=".$invoice->getDue()."<br>";
                    $invoice->setPaid($invoice->getTotal());
                    $invoice->setDue(NULL);
                    $em->flush($invoice);
                    echo $i.": Updated: total=".$invoice->getTotal()."; paid=".$invoice->getPaid()."; due=".$invoice->getDue()."<br><br>";
                    $i++;
                }
            }
        }

        //exit("End of update project's implicit dates: ".$i);
        return $i-1;
    }

    public function updateCommentObject(Request $request) {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('employees.sitename').'-order-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('OlegUserdirectoryBundle:FosComment');
        $dql =  $repository->createQueryBuilder("foscomment");
        $dql->select('foscomment');

        $dql->where("foscomment.entityName IS NULL");

        $query = $em->createQuery($dql);

        $comments = $query->getResult();
        echo "comments count=".count($comments)."<br>";

        $batchSize = 20;
        $count = 0;

        foreach($comments as $comment) {
            if( !$comment->getEntityId() || !$comment->getEntityName() || !$comment->getEntityNamespace() ) {

                $commentId = $comment->getThread();
                //echo "commentId=".$commentId."<br>";
                //get request ID from $commentId 'transres-Request-13541-billing'
                $commentIdArr = explode("-", $commentId);
                if( count($commentIdArr) >= 3 ) {
                    $threadEntityName = $commentIdArr[1];
                    $requestId = $commentIdArr[2];
                    if( $threadEntityName && $requestId ) {

                        if( $threadEntityName == "Request" ) {
                            $threadEntityName = "TransResRequest";
                        }

                        $entity = $em->getRepository('OlegTranslationalResearchBundle:'.$threadEntityName)->find($requestId);
                        if( $entity ) {
                            $comment->setObject($entity);
                            echo $comment->getEntityId()." ";

                            $em->flush($comment);

//                            if (($count % $batchSize) === 0) {
//                                $em->flush();
//                                $em->clear(); // Detaches all objects from Doctrine!
//                            }

                            $count++;
                        }
                    }
                }

            }
        }

        $em->flush(); //Persist objects that did not make up an entire batch
        $em->clear();

        return $count;
    }


}
