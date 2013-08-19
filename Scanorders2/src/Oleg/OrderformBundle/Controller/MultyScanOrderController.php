<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;
use Oleg\OrderformBundle\Entity\Patient;
use Oleg\OrderformBundle\Form\PatientType;
use Oleg\OrderformBundle\Entity\Specimen;
use Oleg\OrderformBundle\Form\SpecimenType;
use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Form\AccessionType;
use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Form\PartType;
use Oleg\OrderformBundle\Entity\Block;
use Oleg\OrderformBundle\Form\BlockType;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Form\SlideType;

use Oleg\OrderformBundle\Form\SlideMultiType;

use Oleg\OrderformBundle\Helper\ErrorHelper;

//ScanOrder joins OrderInfo + Scan
/**
 * OrderInfo controller.
 *
 * @Route("/multy")
 */
class MultyScanOrderController extends Controller {
   
    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/index", name="multyIndex")
     * @Method("GET")
     * @Template("OlegOrderformBundle:MultyScanOrder:index.html.twig")
     */
    public function multyIndexAction() {
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            //throw new AccessDeniedException();
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();
        
        //findAll();
        $entities = $em->getRepository('OlegOrderformBundle:OrderInfo')->                   
                    findBy(array(), array('orderdate'=>'desc')); 
       
        //$slides = $em->getRepository('OlegOrderformBundle:Slide')->findAll();
        
        return array(
            'entities' => $entities,  
            //'slides' => $slides
        );
    }
     
    /**
     * Creates a new OrderInfo entity.
     *
     * @Route("/new", name="multy_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:MultyScanOrder:new.html.twig")
     */
    public function multyCreateAction(Request $request)
    { 
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        //echo " controller multy<br>";

        $entity  = new OrderInfo();
        $form = $this->createForm(new OrderInfoType(true), $entity);  
//        $form->bind($request);
        $form->handleRequest($request);

        if(1) {
            $errorHelper = new ErrorHelper();
            $errors = $errorHelper->getErrorMessages($form);
            echo "<br>form errors:<br>";
            print_r($errors);           
        }
        
        echo "Before validation main entity:<br>";

//        if( $form->isValid() ) {
        if( 1 ) {
            
            $em = $this->getDoctrine()->getManager();                            
                       
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processEntity( $entity, "multy" );

            echo "Before loop:<br>";
            //echo $entity;

            foreach( $entity->getPatient() as $patient ) {
                if( !$patient->getId() ) {
                    $entity->removePatient( $patient );
                    $patient = $em->getRepository('OlegOrderformBundle:Patient')->processEntity( $patient );
                    $entity->addPatient($patient);
                }

                //Procedure
                foreach( $patient->getSpecimen() as $specimen ) {
                    if( !$specimen->getId() ) {
                        $patient->removeSpecimen( $specimen );
                        $specimen = $em->getRepository('OlegOrderformBundle:Specimen')->processEntity( $specimen, $specimen->getAccession() );
                        $patient->addSpecimen($specimen);
                    }

                    //Accession
                    foreach( $specimen->getAccession() as $accession ) {
                        if( !$accession->getId() ) {
                            $specimen->removeAccession( $accession );
                            $accession = $em->getRepository('OlegOrderformBundle:Accession')->processEntity( $accession );
                            $specimen->addAccession($accession);
                        }

                        //Part
                        foreach( $accession->getPart() as $part ) {
                            if( !$part->getId() ) {
                                $accession->removePart( $part );
                                $part = $em->getRepository('OlegOrderformBundle:Part')->processEntity( $part, $accession );
                                $accession->addPart($part);
                            }
                            //Block
                            foreach( $part->getBlock() as $block ) {
                                if( !$block->getId() ) {
                                    $part->removeBlock( $block );
                                    $block = $em->getRepository('OlegOrderformBundle:Block')->processEntity( $block, $part );
                                    $part->addBlock($block);
                                }

                                //Slide
                                foreach( $block->getSlide() as $slide ) {
                                    if( !$slide->getId() ) {
                                        $block->removeSlide( $slide );
                                        $slide = $em->getRepository('OlegOrderformBundle:Slide')->processEntity( $slide );
                                        $slide->setOrderInfo($entity);
                                        $slide->setAccession($accession);
                                        $slide->setPart($part);
                                        //$slide->setBlock($block);
                                        $block->addSlide($slide);
                                    }
                                } //slide

                            } //block

                        } //part

                    } //accession

                } //procedure

            } //patient

            echo "<br>End of loop<br>";
            //echo $entity;
            //exit();

            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'You successfully submit a scan request! Confirmation email sent!'
            );
            
            return $this->redirect( $this->generateUrl('multy_new') );
        }
        
        
        return array(           
            'form'   => $form->createView()
        );    
    }    
    
    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/new", name="multy_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:MultyScanOrder:new.html.twig")
     */
    public function newMultyAction()
    {

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            //throw new AccessDeniedException();
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $entity = new OrderInfo();
        $username = $this->get('security.context')->getToken()->getUser();
        $entity->setProvider($username);

        $patient = new Patient();
        $entity->addPatient($patient);

        //$patient2 = new Patient();
        //$entity->addPatient($patient2);

        $procedure = new Specimen();
        $patient->addSpeciman($procedure);

        //$procedure2 = new Specimen();
        //$patient->addSpeciman($procedure2);

        $accession = new Accession();
        $procedure->addAccession($accession);

        $part = new Part();
        $accession->addPart($part);

        $block = new Block();
        $part->addBlock($block);

        $slide = new Slide();
        $block->addSlide($slide);

        //$slide2 = new Slide();
        //$block->addSlide($slide2);

        $form   = $this->createForm( new OrderInfoType(true), $entity );
        
        return array(          
            'form' => $form->createView(),          
        );
    }
    
    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/table", name="table")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Order:table.html.twig")
     */
    public function tableAction()
    {     
        return array(          
            //'form' => $form->createView(),          
        ); 
    }
 
}
