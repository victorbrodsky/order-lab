<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Oleg\OrderformBundle\Entity\StainList;
use Oleg\OrderformBundle\Entity\OrganList;
use Oleg\OrderformBundle\Entity\ProcedureList;
use Oleg\OrderformBundle\Entity\PathServiceList;
use Oleg\OrderformBundle\Entity\StatusType;
use Oleg\OrderformBundle\Entity\StatusGroup;
use Oleg\OrderformBundle\Entity\Status;
use Oleg\OrderformBundle\Entity\SlideType;
use Oleg\OrderformBundle\Entity\MrnType;
use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Entity\User;
use Oleg\OrderformBundle\Form\UserType;
use Oleg\OrderformBundle\Helper\UserUtil;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * StainList controller.
 *
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

//        if( false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }

        $count_stain = $this->generateStains();
        $count_organ = $this->generateOrgans();
        $count_procedure = $this->generateProcedures();
        $count_statustype = $this->generateStatusType();
        $count_statusgroup = $this->generateStatusGroups();
        $count_status = $this->generateStatuses();
        $count_pathservice = $this->generatePathServices();
        $count_slidetype = $this->generateSlideType();
        $count_mrntype = $this->generateMrnType();
        $userutil = new UserUtil();
        $count_users = $userutil->generateUsersExcel($this->getDoctrine()->getManager());


        $this->get('session')->getFlashBag()->add(
            'notice',
            'Generated Tables: '.
            'Stains='.$count_stain.', '.
            'Organs='.$count_organ.', '.
            'Procedures='.$count_procedure.', '.
            'Status Types='.$count_statustype.', '.
            'Status Groups='.$count_statusgroup.', '.
            'Statuses='.$count_status.', '.
            'Pathology Services='.$count_pathservice.', '.
            'Slide Types='.$count_slidetype.', '.
            'Mrn Types='.$count_mrntype.', '.
            'Users='.$count_users.
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

//        $helper = new FormHelper();
//        $organs = $helper->getProcedure();
//
//        $username = $this->get('security.context')->getToken()->getUser();
//
//        $count = 0;
//        foreach( $organs as $organ ) {
//            $list = new ProcedureList();
//            $list->setCreator( $username );
//            $list->setCreatedate( new \DateTime() );
//            $list->setName( $organ );
//            $list->setType('default');
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($list);
//            $em->flush();
//            $count++;
//        }
//
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            'Created '.$count. ' procedure records'
//        );
//
//        return $this->redirect($this->generateUrl('procedurelist'));

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

//        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }

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

//        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }

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

        //if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
        //    return $this->render('OlegOrderformBundle:Security:login.html.twig');
        //}

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

        $count = 0;
        foreach( $stains as $stain ) {
            $stainList = new StainList();
            $stainList->setCreator( $username );
            $stainList->setCreatedate( new \DateTime() );
            $stainList->setName( trim($stain) );
            $stainList->setType('default');

            $em = $this->getDoctrine()->getManager();
            $em->persist($stainList);
            $em->flush();
            $count++;
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

        $count = 0;
        foreach( $organs as $organ ) {
            $list = new OrganList();
            $list->setCreator( $username );
            $list->setCreatedate( new \DateTime() );
            $list->setName( trim($organ) );
            $list->setType('default');

            $em = $this->getDoctrine()->getManager();
            $em->persist($list);
            $em->flush();
            $count++;
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

        $count = 0;
        foreach( $procedures as $procedure ) {
            $list = new ProcedureList();
            $list->setCreator( $username );
            $list->setCreatedate( new \DateTime() );
            $list->setName( trim($procedure) );
            $list->setType('default');

            $em = $this->getDoctrine()->getManager();
            $em->persist($list);
            $em->flush();
            $count++;
        }

        return $count;
    }

    public function generateStatusType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:StatusType')->findAll();

        if( $entities ) {

            return -1;
        }

        $type1 = new StatusType();
        $type1->setName("Regular");
        $em->persist($type1);

        $type2 = new StatusType();
        $type2->setName("Filled");
        $em->persist($type2);

        $type3 = new StatusType();
        $type3->setName("On Hold");
        $em->persist($type3);

        $em->flush();

        return 3;
    }

    public function generateStatusGroups() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:StatusGroup')->findAll();

        if( $entities ) {

            return -1;
        }

        $group1 = new StatusGroup();
        $group1->setName("User");
        $em->persist($group1);

        $group2 = new StatusGroup();
        $group2->setName("Admin");
        $em->persist($group2);

        $em->flush();

        return 2;
    }


    public function generateStatuses() {

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

        $count = 0;
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

            $status->setType( $em->getRepository('OlegOrderformBundle:StatusType')->findOneByName('Regular') );
            $status->setGroup( $em->getRepository('OlegOrderformBundle:StatusGroup')->findOneByName('User') );

            //Filled
            if( strpos($statusStr,'Filled') !== false ) {
                $status->setName($statusStr);
                $status->setAction($statusStr);
                $status->setType( $em->getRepository('OlegOrderformBundle:StatusType')->findOneByName('Filled') );
                $status->setGroup( $em->getRepository('OlegOrderformBundle:StatusGroup')->findOneByName('Admin') );
            }

            //On Hold
            if( strpos($statusStr,'On Hold') !== false ) {
                $status->setName($statusStr);
                $status->setAction($statusStr);
                $status->setType( $em->getRepository('OlegOrderformBundle:StatusType')->findOneByName('On Hold') );
                $status->setGroup( $em->getRepository('OlegOrderformBundle:StatusGroup')->findOneByName('Admin') );
            }

            $em->persist($status);
            $em->flush();

            $count++;

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

        $count = 0;
        foreach( $services as $service ) {

            $pathlogyServices = explode("/",$service);

            foreach( $pathlogyServices as $pathlogyService ) {

                $pathlogyServiceEntity  = $em->getRepository('OlegOrderformBundle:PathServiceList')->findOneByName($pathlogyService);

                if( $pathlogyServiceEntity ) {
                    //
                } else {
                    //echo " ".$pathlogyService.", ";
                    $list = new PathServiceList();
                    $list->setCreator( $username );
                    $list->setCreatedate( new \DateTime() );
                    $list->setName( trim($pathlogyService) );
                    $list->setType('default');

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($list);
                    $em->flush();
                    $count++;
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

        $count = 0;
        foreach( $types as $type ) {
            $slideType = new SlideType();
            $slideType->setCreator( $username );
            $slideType->setCreatedate( new \DateTime() );
            $slideType->setName( trim($type) );

            if( $type == "TMA" ) {
                $slideType->setType('TMA');
            } else {
                $slideType->setType('default');
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($slideType);
            $em->flush();
            $count++;
        }

        return $count;
    }

    public function generateMrnType() {

        $helper = new FormHelper();
        $types = $helper->getMrnType();

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:MrnType')->findAll();

        if( $entities ) {

            return -1;
        }

        $count = 0;
        foreach( $types as $type ) {
            $mrnType = new MrnType();
            $mrnType->setCreator( $username );
            $mrnType->setCreatedate( new \DateTime() );
            $mrnType->setName( trim($type) );
            $mrnType->setType('default');
            $em = $this->getDoctrine()->getManager();
            $em->persist($mrnType);
            $em->flush();
            $count++;
        }

        return $count;
    }

}
