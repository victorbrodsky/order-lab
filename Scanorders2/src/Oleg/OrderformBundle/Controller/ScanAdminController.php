<?php

namespace Oleg\OrderformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use Oleg\OrderformBundle\Entity\AccessionType;
use Oleg\OrderformBundle\Entity\EncounterType;
use Oleg\OrderformBundle\Entity\FormType;
use Oleg\OrderformBundle\Entity\StainList;
use Oleg\OrderformBundle\Entity\OrganList;
use Oleg\OrderformBundle\Entity\ProcedureList;
use Oleg\OrderformBundle\Entity\Status;
use Oleg\OrderformBundle\Entity\SlideType;
use Oleg\OrderformBundle\Entity\MrnType;
use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Entity\RegionToScan;
use Oleg\OrderformBundle\Entity\SlideDelivery;
use Oleg\OrderformBundle\Entity\ProcessorComments;
use Oleg\OrderformBundle\Entity\Urgency;
use Oleg\OrderformBundle\Entity\ProgressCommentsEventTypeList;

use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Controller\AdminController;
use Oleg\UserdirectoryBundle\Entity\SiteParameters;


/**
 * @Route("/admin")
 */
class ScanAdminController extends AdminController
{
    /**
     * Admin Page
     *
     * @Route("/lists/", name="admin_index")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:index.html.twig")
     */
    public function indexAction()
    {

        $environment = 'dev'; //default

        $em = $this->getDoctrine()->getManager();
        $params = $roles = $em->getRepository('OlegUserdirectoryBundle:SiteParameters')->findAll();

        if( count($params) > 1 ) {
            throw new \Exception( 'Must have only one parameter object. Found '.count($params).'object(s)' );
        }

        if( count($params) == 1 ) {
            $param = $params[0];
            $environment = $param->getEnvironment();
        }

        return $this->render('OlegOrderformBundle:Admin:index.html.twig', array('environment'=>$environment));
    }


    /**
     * Populate DB
     *
     * @Route("/genall", name="generate_all")
     * @Method("GET")
     * @Template()
     */
    public function generateAllAction()
    {

        $max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        $default_time_zone = $this->container->getParameter('default_time_zone');

        $count_acctype = $this->generateAccessionType();
        $count_enctype = $this->generateEncounterType();
        $count_formtype = $this->generateFormType();
        $count_stain = $this->generateStains();
        $count_organ = $this->generateOrgans();
        $count_procedure = $this->generateProcedures();
        $count_status = $this->generateStatuses();
        //$count_pathservice = $this->generatePathServices();
        $count_slidetype = $this->generateSlideType();
        $count_mrntype = $this->generateMrnType();
        $count_SlideDelivery = $this->generateSlideDelivery();
        $count_RegionToScan = $this->generateRegionToScan();
        $count_comments = $this->generateProcessorComments();
        $count_urgency = $this->generateUrgency();
        $count_progressCommentsEventType = $this->generateProgressCommentsEventType();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Generated Tables: '.
            //'Roles='.$count_roles.', '.
            'Accession Types='.$count_acctype.', '.
            'Encounter Types='.$count_enctype.', '.
            'Form Types='.$count_formtype.', '.
            'Stains='.$count_stain.', '.
            'Organs='.$count_organ.', '.
            'Procedures='.$count_procedure.', '.
            'Statuses='.$count_status.', '.
            //'Pathology Services='.$count_pathservice.', '.
            'Slide Types='.$count_slidetype.', '.
            'MRN Types='.$count_mrntype.', '.
            'Slide Delivery='.$count_SlideDelivery.', '.
            'Region To Scan='.$count_RegionToScan.', '.
            'Processor Comments='.$count_comments.', '.
            'Urgency='.$count_urgency.' '.
            'Progress and Comments EventTypes='.$count_progressCommentsEventType.' '.
            ' (Note: -1 means that this table is already exists)'
        );


        ini_set('max_execution_time', $max_exec_time); //set back to the original value

        return $this->redirect($this->generateUrl('admin_index'));
    }


    /**
     * Populate DB
     *
     * @Route("/genstain", name="generate_stain")
     * @Method("GET")
     * @Template()
     */
    public function generateStainAction()
    {

        $count = $this->generateStains();
        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' stain records'
            );

            return $this->redirect($this->generateUrl('stainlist'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

    }


    /**
     * Populate DB
     *
     * @Route("/genorgan", name="generate_organ")
     * @Method("GET")
     * @Template()
     */
    public function generateOrganAction()
    {

        $count = $this->generateOrgans();

        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' organ records'
            );

            return $this->redirect($this->generateUrl('organlist'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

    }



    /**
     * Populate DB
     *
     * @Route("/genprocedure", name="generate_procedure")
     * @Method("GET")
     * @Template()
     */
    public function generateProcedureAction()
    {

//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('OlegOrderformBundle:ProcedureList')->findAll();

        $count = $this->generateProcedures();

        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' procedure records'
            );

            return $this->redirect($this->generateUrl('procedurelist'));
        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

    }


//    /**
//     * Populate DB
//     *
//     * @Route("/genpathservice", name="generate_pathservice")
//     * @Method("GET")
//     * @Template()
//     */
//    public function generatePathServiceAction()
//    {
//
//        $count = $this->generatePathServices();
//        if( $count >= 0 ) {
//
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                'Created '.$count. ' stain records'
//            );
//
//            return $this->redirect($this->generateUrl('stainlist'));
//
//        } else {
//
//            $this->get('session')->getFlashBag()->add(
//                'notice',
//                'This table is already exists!'
//            );
//
//            return $this->redirect($this->generateUrl('admin_index'));
//        }
//
//    }

    /**
     * Populate DB
     *
     * @Route("/genslidetype", name="generate_slidetype")
     * @Method("GET")
     * @Template()
     */
    public function generateSlideTypeAction()
    {

        $count = $this->generateSlideType();
        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' slide types records'
            );

            return $this->redirect($this->generateUrl('slidetype'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

    }

    /**
     * Populate DB
     *
     * @Route("/genmrntype", name="generate_mrntype")
     * @Method("GET")
     * @Template()
     */
    public function generateMrnTypeAction()
    {

        $count = $this->generateMrnType();
        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' mrn type records'
            );

            return $this->redirect($this->generateUrl('mrntype'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

    }


//////////////////////////////////////////////////////////////////////////////


    //return -1 if failed
    //return number of generated records
    public function generateStains() {

        $helper = new FormHelper();
        $stains = $helper->getStains();

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:StainList')->findAll();

        if( $entities ) {

            return -1;
        }

        $count = 1;
        foreach( $stains as $stain ) {
            $stainList = new StainList();
            $this->setDefaultList($stainList,$count,$username,$stain);

            $em->persist($stainList);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }

    public function generateOrgans() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:OrganList')->findAll();

        if( $entities ) {

            return -1;
        }

        $helper = new FormHelper();
        $organs = $helper->getSourceOrgan();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $organs as $organ ) {

            $list = new OrganList();
            $this->setDefaultList($list,$count,$username,$organ);

            $em->persist($list);
            $em->flush();

            $count = $count + 10;
        }


        return $count;
    }

    public function generateProcedures() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:ProcedureList')->findAll();

        if( $entities ) {

           return -1;
        }

        $helper = new FormHelper();
        $procedures = $helper->getProcedure();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $procedures as $procedure ) {

            $list = new ProcedureList();
            $this->setDefaultList($list,$count,$username,$procedure);

            $em->persist($list);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }

    public function generateStatuses() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:Status')->findAll();

        if( $entities ) {
            return -1;
        }

        $statuses = array(
            "Not Submitted", "Submitted", "Amended",
            "Superseded", "Canceled by Submitter", "Canceled by Processor",
            "On Hold: Awaiting Slides", "On Hold: Slides Received",
            "Filled: Scanned", "Filled: Some Scanned", "Filled: Not Scanned",
            "Filled: Scanned & Returned", "Filled: Some Scanned & Returned", "Filled: Not Scanned & Returned"
        );

        $count = 1;

        foreach( $statuses as $statusStr ) {

            $status = new Status();
            $this->setDefaultList($status,$count,$username,null);

            //Regular
            switch( $statusStr )
            {

                case "Not Submitted":
                    $status->setName("Not Submitted");
                    $status->setAction("On Hold");
                    break;
                case "Submitted":
                    $status->setName("Submitted");
                    $status->setAction("Submit");
                    break;
                case "Amended":
                    $status->setName("Amended");
                    $status->setAction("Amend");
                    break;
                case "Canceled by Submitter":
                    $status->setName("Canceled by Submitter");
                    $status->setAction("Cancel");
                    break;
                case "Canceled by Processor":
                    $status->setName("Canceled by Processor");
                    $status->setAction("Cancel");
                    break;

                case "Superseded":
                    $status->setName("Superseded");
                    $status->setAction("Supersede");
                    break;
                default:
                    break;
            }

            //Filled
            if( strpos($statusStr,'Filled') !== false ) {
                $status->setName($statusStr);
                $status->setAction($statusStr);
            }

            //On Hold
            if( strpos($statusStr,'On Hold') !== false ) {
                $status->setName($statusStr);
                $status->setAction($statusStr);
            }

            $em->persist($status);
            $em->flush();

            $count = $count + 10;
        } //foreach

        return $count;
    }

    public function generateSlideType() {

        $helper = new FormHelper();
        $types = $helper->getSlideType();

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:SlideType')->findAll();

        if( $entities ) {

            return -1;
        }

        $count = 1;
        foreach( $types as $type ) {

            $slideType = new SlideType();
            $this->setDefaultList($slideType,$count,$username,$type);

            if( $type == "TMA" ) {
                $slideType->setType('TMA');
            }

            $em->persist($slideType);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }

    public function generateMrnType() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:MrnType')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'New York Hospital MRN',
            'Epic Ambulatory Enterprise ID Number',
            'Weill Medical College IDX System MRN',
            'Enterprise Master Patient Index',
            'Uptown Hospital ID',
            'NYH Health Quest Corporate Person Index',
            'New York Downtown Hospital',
            'De-Identified NYH Tissue Bank Research Patient ID',
            'De-Identified Personal Educational Slide Set Patient ID',
            'De-Identified Personal Research Project Patient ID',
            'California Tumor Registry Patient ID',
            'Specify Another Patient ID Issuer',
            'Auto-generated MRN',
            'Existing Auto-generated MRN'
        );

        $count = 1;
        foreach( $types as $type ) {

            $mrnType = new MrnType();
            $this->setDefaultList($mrnType,$count,$username,$type);

            $em->persist($mrnType);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }

    public function generateFormType() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:FormType')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'One-Slide Scan Order',
            'Multi-Slide Scan Order',
            'Table-View Scan Order',
            'Slide Return Request'
        );

        $count = 1;
        foreach( $types as $type ) {
            $formType = new FormType();
            $this->setDefaultList($formType,$count,$username,$type);

            $em->persist($formType);
            $em->flush();
            $count = $count + 10;
        } //foreach

        return $count;
    }


    public function generateAccessionType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:AccessionType')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'NYH CoPath Anatomic Pathology Accession Number',
            'De-Identified NYH Tissue Bank Research Specimen ID',
            'De-Identified Personal Educational Slide Set Specimen ID',
            'De-Identified Personal Research Project Specimen ID',
            'California Tumor Registry Specimen ID',
            'Specify Another Specimen ID Issuer',
            'TMA Slide',
            'Auto-generated Accession Number',
            'Existing Auto-generated Accession Number'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $types as $type ) {

            $accType = new AccessionType();
            $this->setDefaultList($accType,$count,$username,$type);

            if( $type == "TMA Slide" ) {
                $accType->setType('TMA');
            }

            $em->persist($accType);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return $count;
    }


    public function generateEncounterType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:EncounterType')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Auto-generated Encounter Number',
            'Existing Auto-generated Encounter Number'
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $types as $type ) {

            $encType = new EncounterType();
            $this->setDefaultList($encType,$count,$username,$type);

            $em->persist($encType);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return $count;
    }



    public function generateSlideDelivery() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:SlideDelivery')->findAll();

        if( $entities ) {
            return -1;
        }

        $userutil = new UserUtil();
        $adminemail = $userutil->getSiteSetting($em,'siteEmail');

        $types = array(
            "I'll give slides to Melody - ST1015E (212) 746-2993",
            "I have given slides to Melody already",
            "I will drop the slides off at C-458A (212) 746-6406",
            "I have handed the slides to Liza already",
            "I will write S on the slide & submit as a consult",
            "I will write S4 on the slide & submit as a consult",
            "I will email ".$adminemail." about it",
            "Please e-mail me to set the time & pick up slides",
        );

        $count = 1;
        $rescount = 0;
        foreach( $types as $type ) {

            $listEntity = new SlideDelivery();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
            $rescount++;
        }

        return $rescount;
    }


    public function generateRegionToScan() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:RegionToScan')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Entire Slide",
            "Any one of the levels",
            "Region circled by marker"
        );

        $count = 1;
        foreach( $types as $type ) {

            $listEntity = new RegionToScan();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }


    public function generateProcessorComments() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:ProcessorComments')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            "Slide(s) damaged and can not be scanned",
            "Slide(s) returned before being scanned",
            "Slide(s) could not be scanned due to focusing issues"
        );

        $count = 1;
        foreach( $types as $type ) {

            $listEntity = new ProcessorComments();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }


    public function generateUrgency() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:Urgency')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'As soon as possible', 'Urgently (the patient is waiting in my office)'
        );

        $count = 1;
        foreach( $types as $type ) {

            $listEntity = new Urgency();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }

    public function generateProgressCommentsEventType() {
        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:ProgressCommentsEventTypeList')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Initial Order Submission',
            'Amended Order Submission',
            'Auto-saved at the time of auto-logout',
            'Status Changed',
            'Data Reviewed',
            'Progress & Comments Viewed',
            'Comment Added',
            'Initial Slide Return Request Submission',
            'Slide Return Request Status Changed',
            'Slide Return Request Comment Added'
        );

        $count = 1;
        foreach( $types as $type ) {

            $listEntity = new ProgressCommentsEventTypeList();
            $this->setDefaultList($listEntity,$count,$username,$type);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }


}
