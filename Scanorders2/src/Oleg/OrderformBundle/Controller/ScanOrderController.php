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

use Oleg\OrderformBundle\Helper\ErrorHelper;

//ScanOrder joins OrderInfo + Scan
/**
 * OrderInfo controller.
 *
 * @Route("/")
 */
class ScanOrderController extends Controller {
   
    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/index", name="show")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        
        //findAll();
        $entities = $em->getRepository('OlegOrderformBundle:OrderInfo')->                   
                    findBy(array(), array('orderdate'=>'desc')); 
               
        return array(
            'entities' => $entities,          
        );
    }
    
    /**
     * Creates a new OrderInfo entity.
     *
     * @Route("/", name="singleorder_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:OrderInfo:new.html.twig")
     */
    public function createAction(Request $request)
    {
        
        //echo "scanorder createAction";
        $entity  = new OrderInfo();
        $form = $this->createForm(new OrderInfoType(), $entity);
        $form->bind($request);
              
        $patient = new Patient();      
        $form_patient = $this->createForm(new PatientType(), $patient);
        $form_patient->bind($request);     
        
        $procedure = new Specimen();
        $form_procedure = $this->createForm(new SpecimenType(), $procedure);
        $form_procedure->bind($request);            
        
//        $errorHelper = new ErrorHelper();
//        $errors = $errorHelper->getErrorMessages($form_patient);
//        echo "<br>patient errors:<br>";
//        print_r($errors); 
//        $errors = $errorHelper->getErrorMessages($form_procedure);
//        echo "<br>procedure errors:<br>";
//        print_r($errors); 
            
        
        if( $form->isValid() && $form_procedure->isValid() ) {
            $em = $this->getDoctrine()->getManager();                            
            
            //procedure/specimen: none
            //$procedure->addProcedure($accession);
            
            //patient: mrn
            if( $patient->getMrn() == "" || $patient->getMrn() == null ) {
                $patient->setMrn('000');
            }
            $patient->addSpecimen($procedure);
   
            //orderinfo: status, type, priority, slideDelivery, returnSlide, provider
            $entity->setStatus("submitted"); 
            $entity->setType("single");   
            $entity->addPatient($patient);
                           
            //$scan_entity->setStatus("submitted");
            //$scan_entity->setOrderinfo($entity); 
            
            //get Accession, Part and Block. Create if they are not exist, or return them if they are exist.
            //process accession. If not exists - create and return new object, if exists - return object          
//            $accession = $scan_entity->getSlide()->getAccession();
//            $accession = $em->getRepository('OlegOrderformBundle:Accession')->processAccession( $accession );                         
//            $scan_entity->getSlide()->setAccession($accession);          
            
//            $part = $scan_entity->getSlide()->getPart();
//            $part->setAccession($accession);
//            $part = $em->getRepository('OlegOrderformBundle:Part')->processPart( $part ); 
//            $scan_entity->getSlide()->setPart($part);         
            
//            $block = $scan_entity->getSlide()->getBlock();
//            $block->setAccession($accession);
//            $block->setPart($part);
//            $block = $em->getRepository('OlegOrderformBundle:Block')->processBlock( $block );                         
//            $scan_entity->getSlide()->setBlock($block);        

            //TODO: i.e. if part's field is updated then add options to detect and update it.
          
            $em->persist($entity);
            $em->persist($patient);
            $em->persist($procedure);
            
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'You successfully submit a scan request! Confirmation email sent!'
            );
            
            return $this->redirect( $this->generateUrl('scanorder_new') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'form_patient'   => $form_patient->createView(),
            'form_procedure'   => $form_procedure->createView(),
        );
    }

    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/", name="scanorder_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ScanOrder:new.html.twig")
     */
    public function newAction()
    {         
        $entity = new OrderInfo();      
        $form   = $this->createForm( new OrderInfoType(), $entity );

        $patient = new Patient();      
        $form_patient   = $this->createForm(new PatientType(), $patient);
        
        $procedure = new Specimen();  //TODO: rename specimen to procedure    
        $form_procedure = $this->createForm(new SpecimenType(), $procedure);
//        
//        $accession = new Accession();      
//        $form_accession   = $this->createForm(new AccessionType(), $accession);
//         
//        $part = new Part();      
//        $form_part   = $this->createForm(new PartType(), $part);
//            
//        $block = new Block();      
//        $form_block   = $this->createForm(new BlockType(), $block);
//        
//        $slide = new Slide();      
//        $form_slide   = $this->createForm(new SlideType(), $slide);
        
        return array(          
            'form' => $form->createView(),
            'form_patient' => $form_patient->createView(),
            'form_procedure' => $form_procedure->createView(),
//            '$form_accession' => $form_accession->createView(),
//            '$form_part' => $form_part->createView(),
//            '$form_block' => $form_block->createView(),
//            '$form_slide' => $form_slide->createView(),
        );
    }

    /**
     * Finds and displays a OrderInfo entity.
     *
     * @Route("/{id}", name="scanorder_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing OrderInfo entity.
     *
     * @Route("/{id}/edit", name="scanorder_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        $editForm = $this->createForm(new OrderInfoType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing OrderInfo entity.
     *
     * @Route("/{id}", name="scanorder_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:OrderInfo:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderInfo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new OrderInfoType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('scanorder_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a OrderInfo entity.
     *
     * @Route("/{id}", name="scanorder_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:OrderInfo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OrderInfo entity.');
            }
            
//            $scan_entities = $em->getRepository('OlegOrderformBundle:Scan')->
//                    findBy(array('scanorder_id'=>$id));
            
//            $scan_entities = $em->getRepository('OlegOrderformBundle:Scan')->findBy(
//                array('scanorder' => $id)            
//            );
            $entity->removeAllChildren();          
            
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('scanorder'));
    }

    /**
     * Creates a form to delete a OrderInfo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
