<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;

use Oleg\OrderformBundle\Entity\AccessionType;
use Oleg\OrderformBundle\Entity\FormType;
use Oleg\OrderformBundle\Entity\StainList;
use Oleg\OrderformBundle\Entity\OrganList;
use Oleg\OrderformBundle\Entity\ProcedureList;
use Oleg\OrderformBundle\Entity\PathServiceList;
use Oleg\OrderformBundle\Entity\Status;
use Oleg\OrderformBundle\Entity\SlideType;
use Oleg\OrderformBundle\Entity\MrnType;
use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Helper\UserUtil;
use Oleg\OrderformBundle\Entity\Roles;
use Oleg\OrderformBundle\Entity\ReturnSlideTo;

use Symfony\Component\HttpFoundation\Session\Session;
//use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * Admin Page
     *
     * @Route("/", name="admin_index")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:index.html.twig")
     */
    public function indexAction()
    {
        return $this->render('OlegOrderformBundle:Admin:index.html.twig');
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

        $count_roles = $this->generateRoles();
        $count_acctype = $this->generateAccessionType();
        $count_formtype = $this->generateFormType();
        $count_stain = $this->generateStains();
        $count_organ = $this->generateOrgans();
        $count_procedure = $this->generateProcedures();
        $count_status = $this->generateStatuses();
        $count_pathservice = $this->generatePathServices();
        $count_slidetype = $this->generateSlideType();
        $count_mrntype = $this->generateMrnType();
        $count_returnslide = $this->generateReturnSlideTo();
        $userutil = new UserUtil();
        //$count_users = $userutil->generateUsersExcel($this->getDoctrine()->getManager());


        $this->get('session')->getFlashBag()->add(
            'notice',
            'Generated Tables: '.
            'Roles='.$count_roles.', '.
            'Accession Types='.$count_acctype.', '.
            'Form Types='.$count_formtype.', '.
            'Stains='.$count_stain.', '.
            'Organs='.$count_organ.', '.
            'Procedures='.$count_procedure.', '.
            'Statuses='.$count_status.', '.
            'Pathology Services='.$count_pathservice.', '.
            'Slide Types='.$count_slidetype.', '.
            'Mrn Types='.$count_mrntype.', '.
            'Return Slide To='.$count_returnslide.', '.
            //'Users='.$count_users.
            ' (Note: -1 means that this table is already exists)'
        );

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


    /**
     * Populate DB
     *
     * @Route("/genpathservice", name="generate_pathservice")
     * @Method("GET")
     * @Template()
     */
    public function generatePathServiceAction()
    {

        $count = $this->generatePathServices();
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
            $stainList->setOrderinlist( $count );
            $stainList->setCreator( $username );
            $stainList->setCreatedate( new \DateTime() );
            $stainList->setName( trim($stain) );
            $stainList->setType('default');

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
            $list->setOrderinlist( $count );
            $list->setCreator( $username );
            $list->setCreatedate( new \DateTime() );
            $list->setName( trim($organ) );
            $list->setType('default');

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
            $list->setOrderinlist( $count );
            $list->setCreator( $username );
            $list->setCreatedate( new \DateTime() );
            $list->setName( trim($procedure) );
            $list->setType('default');

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
            "Submitted", "Not Submitted", "Canceled by Submitter", "Canceled by Processor", "Amended", "Superseded",
            "On Hold: Slides Received", "On Hold: Awaiting Slides",
            "Filled: Scanned", "Filled: Not Scanned", "Filled: Some Scanned", "Filled: Scanned & Returned",
            "Filled: Not Scanned & Returned", "Filled: Some Scanned & Returned",
        );

        $count = 1;

        foreach( $statuses as $statusStr ) {

            $status = new Status();

            //Regular
            switch( $statusStr )
            {

                case "Submitted":
                    $status->setName("Submitted");
                    $status->setAction("Submit");
                    break;
                case "Not Submitted":
                    $status->setName("Not Submitted");
                    $status->setAction("On Hold");
                    break;
                case "Canceled by Submitter":
                    $status->setName("Canceled by Submitter");
                    $status->setAction("Cancel");
                    break;
                case "Canceled by Processor":
                    $status->setName("Canceled by Processor");
                    $status->setAction("Cancel");
                    break;
                case "Amended":
                    $status->setName("Amended");
                    $status->setAction("Amend");
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

            $status->setOrderinlist( $count );
            $status->setCreator( $username );
            $status->setCreatedate( new \DateTime() );
            $status->setType('default');

            $em->persist($status);
            $em->flush();

            $count = $count + 10;
        } //foreach

        return $count;
    }

    public function generatePathServices() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:PathServiceList')->findAll();

        if( $entities ) {
            return -1;
        }

        $helper = new FormHelper();
        $services = $helper->getPathologyService();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $services as $service ) {

            $pathlogyServices = explode("/",$service);

            foreach( $pathlogyServices as $pathlogyService ) {

                $pathlogyServiceEntity  = $em->getRepository('OlegOrderformBundle:PathServiceList')->findOneByName($pathlogyService);

                if( $pathlogyServiceEntity ) {
                    //
                } else {
                    //echo " ".$pathlogyService.", ";
                    $list = new PathServiceList();
                    $list->setOrderinlist( $count );
                    $list->setCreator( $username );
                    $list->setCreatedate( new \DateTime() );
                    $list->setName( trim($pathlogyService) );
                    $list->setType('default');

                    $em->persist($list);
                    $em->flush();

                    $count = $count + 10;
                }

            }
            //echo "<br>";
        }

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
            $slideType->setOrderinlist( $count );
            $slideType->setCreator( $username );
            $slideType->setCreatedate( new \DateTime() );
            $slideType->setName( trim($type) );

            if( $type == "TMA" ) {
                $slideType->setType('TMA');
            } else {
                $slideType->setType('default');
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
            $mrnType->setOrderinlist( $count );
            $mrnType->setCreator( $username );
            $mrnType->setCreatedate( new \DateTime() );
            $mrnType->setName( trim($type) );
            $mrnType->setType('default');
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
            'One Slide Scan Order',
            'Clinical Multi-Slide Scan Order',
            'Educational Multi-Slide Scan Order',
            'Research Multi-Slide Scan Order'
        );

        $count = 1;
        foreach( $types as $type ) {
            $formType = new FormType();
            $formType->setOrderinlist( $count );
            $formType->setCreator( $username );
            $formType->setCreatedate( new \DateTime() );
            $formType->setName( trim($type) );
            $formType->setType('default');
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
            "Existing Auto-generated Accession Number"
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $types as $type ) {

            $accType = new AccessionType();
            $accType->setOrderinlist( $count );
            $accType->setCreator( $username );
            $accType->setCreatedate( new \DateTime() );
            $accType->setName( trim($type) );

            if( $type == "TMA Slide" ) {
                $accType->setType('TMA');
            } else {
                $accType->setType('default');
            }

            $em->persist($accType);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return $count;
    }
   

    public function generateRoles() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:Roles')->findAll();

        if( $entities ) {
            return -1;
        }

        //Note: fos user has role ROLE_SUPER_ADMIN

        $types = array(
            "ROLE_ADMIN" => "Admin",
            "ROLE_PROCESSOR" => "Processor",

            "ROLE_DATA_QUALITY_ASSURANCE_SPECIALIST" => "Data Quality Assurance Specialist",

            //"ROLE_USER" => "User", //this role must be always assigned to the authenticated user. Required by fos user bundle.

            "ROLE_SUBMITTER" => "Submitter",
            "ROLE_ORDERING_PROVIDER" => "Ordering Provider",

            "ROLE_PATHOLOGY_RESIDENT" => "Pathology Resident",
            "ROLE_PATHOLOGY_FELLOW" => "Pathology Fellow",
            "ROLE_PATHOLOGY_FACULTY" => "Pathology Faculty",

            //"ROLE_BANNED_USER" => "Banned User",  //not required since we have locked
            "ROLE_EXTERNAL_SUBMITTER" => "External Submitter",
            "ROLE_EXTERNAL_ORDERING_PROVIDER" => "External Ordering Provider",

            "ROLE_UNAPPROVED_SUBMITTER" => "Unapproved Submitter"
        );

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 1;
        foreach( $types as $role => $alias ) {

            $entity = new Roles();
            $entity->setOrderinlist( $count );
            $entity->setCreator( $username );
            $entity->setCreatedate( new \DateTime() );
            $entity->setName( trim($role) );
            $entity->setAlias( trim($alias) );

            $entity->setType('default');

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return $count;
    }


    public function generateReturnSlideTo() {

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:ReturnSlideTo')->findAll();

        if( $entities ) {
            return -1;
        }

        $types = array(
            'Filing Room', 'Me'
        );

        $count = 1;
        foreach( $types as $type ) {

            $mrnType = new ReturnSlideTo();
            $mrnType->setOrderinlist( $count );
            $mrnType->setCreator( $username );
            $mrnType->setCreatedate( new \DateTime() );
            $mrnType->setName( trim($type) );
            $mrnType->setType('default');
            $em->persist($mrnType);
            $em->flush();

            $count = $count + 10;
        }

        return $count;
    }


}
