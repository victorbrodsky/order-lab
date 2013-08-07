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
     * @Template()
     */
    public function multyIndexAction() {
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
      
    //////////// test slide multi
    /**
     * Creates a new OrderInfo entity.
     *
     * @Route("/multy", name="multy_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:ScanOrder:newmulty.html.twig")
     */
    public function multyCreateAction(Request $request)
    { 
        echo " controller multy1";
//        exit();
        //echo "scanorder createAction";
        $entity  = new OrderInfo();
        echo " new";
        $form = $this->createForm(new SlideMultiType(), $entity);
        echo " form";
        $form->bind($request);
        
//        $patient  = new Patient();
//        $form_patient = $this->createForm(new PatientType(), $patient);
//        $form_patient->bind($request);
        echo " controller multy2";
        //exit();
        if( $form->isValid() ) {
            
            $em = $this->getDoctrine()->getManager();                            
            
            $entity->setStatus("submitted"); 
            $entity->setType("single");             
            //procedure/specimen: none
            //$procedure->addProcedure($accession);
            foreach( $entity->getPatient() as $patient ) { 
                $patient = $em->getRepository('OlegOrderformBundle:Patient')->processEntity( $patient ); 
                $entity->addPatient($patient);
                $patient->addOrderInfo($entity);
                echo " pat mrn=".$patient->getMrn();
                $em->persist($patient); 
                //exit();
            }
            
            
            echo "<br>111";
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
            'entity' => $entity,
            'form'   => $form->createView()
        );    
    }    
    
    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/new", name="multy_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ScanOrder:newmulty.html.twig")
     */
    public function newMultyAction()
    {         
        $entity = new OrderInfo();      
        $form   = $this->createForm( new SlideMultiType(), $entity );  
        
        return array(          
            'form' => $form->createView(),          
        );
    }
    
 
}
