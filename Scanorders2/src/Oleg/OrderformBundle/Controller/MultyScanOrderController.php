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
     * @Template("OlegOrderformBundle:ScanOrder:newmulty.html.twig")
     */
    public function multyCreateAction(Request $request)
    { 
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            //throw new AccessDeniedException();
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        echo " controller multy<br>";

        $entity  = new OrderInfo();
        $form = $this->createForm(new OrderInfoType(true), $entity);  
//        $form->bind($request);
        $form->handleRequest($request);

        echo "Before validation main entity:<br>";
        echo $entity;
        echo " valid?: <br>";
        
        if( $form->isValid() ) {
            
            $em = $this->getDoctrine()->getManager();                            
                       
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->processEntity( $entity, "multy" );                   
           
            echo "before loop:<br>";
            echo $entity;
            $count = 0;
            foreach( $entity->getPatient() as $patient ) {
                
                echo $patient;              
                echo " before_process ";
                $patient_processed = $em->getRepository('OlegOrderformBundle:Patient')->processEntity( $patient );  //$entity->getPatient()[$count] );           
                //remove old and attach new patient. This requires only for multyple order with data_prototype                                                            
                $entity->removePatient( $patient ); 
                //$patient_processed->addOrderinfo($entity);
                $entity->addPatient($patient_processed);
                echo " after_process ";
                echo $patient_processed;

                foreach( $patient->getSpecimen() as $specimen ) {
                    $entity->removePatient( $patient_processed );
                    $specimen_processed = $em->getRepository('OlegOrderformBundle:Specimen')->processEntity( $specimen );
                    //$specimen_processed->setPatient($patient_processed);
                    //$em->persist($specimen_processed->getPatient());

                    $patient_processed->removeSpecimen( $specimen );
                    $patient_processed->addSpecimen( $specimen_processed );
                    $entity->addPatient($patient_processed);

                    echo "specimen: <br>";
                    echo $entity;
                    echo " end of specimen <br>";

                }

                //$entity->addPatient($patient);
                echo " after processing: <br>";
                echo $entity; 
                
            } //foreach
                  
            echo "<br>End of loop<br>";
            echo $entity;
            //exit();
            
//            $count = 0;
//            foreach( $entity->getPatient() as $patient ) {              
//                $entity->removePatient($entity->getPatient()[$count++]);             
//            }
//            echo "<br>after removal directly<br>";
//            echo $entity; 
            
            $em->persist($entity);         
            $em->flush($entity);

            $this->get('session')->getFlashBag()->add(
                'notice',
                'You successfully submit a scan request! Confirmation email sent!'
            );
            
            return $this->redirect( $this->generateUrl('multy_new') );
        }
        
        
        return array(
            'entity' => $entity,
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

        $procedure2 = new Specimen();
        $patient->addSpeciman($procedure2);

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
