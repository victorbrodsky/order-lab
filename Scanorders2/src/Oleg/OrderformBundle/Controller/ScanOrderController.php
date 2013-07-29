<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\OrderInfo;
use Oleg\OrderformBundle\Form\OrderInfoType;
use Oleg\OrderformBundle\Entity\Scan;
use Oleg\OrderformBundle\Form\ScanType;
use Oleg\OrderformBundle\Entity\Block;

//ScanOrder joins OrderInfo + Scan
/**
 * OrderInfo controller.
 *
 * @Route("/scanorder")
 */
class ScanOrderController extends Controller {
   
    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/", name="scanorder")
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
     * @Route("/", name="scanorder_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:OrderInfo:new.html.twig")
     */
    public function createAction(Request $request)
    {
        //echo "scanorder createAction";
        $entity  = new OrderInfo();
        $form = $this->createForm(new OrderInfoType(), $entity);
        $form->bind($request);
        
        $scan_entity = new Scan();
        $scan_form = $this->createForm(new ScanType(), $scan_entity);
        $scan_form->bind($request);
        
        if( $form->isValid() && $scan_form->isValid() ) {
            $em = $this->getDoctrine()->getManager();                  
                      
            $entity->setStatus("submitted");            
                      
            $scan_entity->setStatus("submitted");
            $scan_entity->setOrderinfo($entity); 
            
            //get Accession, Part and Block. Create if they are not exist, or return them if they are exist.
            //process accession. If not exists - create and return new object, if exists - return object          
            $accession = $scan_entity->getSlide()->getAccession();
            $accession = $em->getRepository('OlegOrderformBundle:Accession')->processAccession( $accession );                         
            $scan_entity->getSlide()->setAccession($accession);          
            
            $part = $scan_entity->getSlide()->getPart();
            $part->setAccession($accession);
            $part = $em->getRepository('OlegOrderformBundle:Part')->processPart( $part ); 
            $scan_entity->getSlide()->setPart($part);         
            
            $block = $scan_entity->getSlide()->getBlock();
            $block->setAccession($accession);
            $block->setPart($part);
            $block = $em->getRepository('OlegOrderformBundle:Block')->processBlock( $block );                         
            $scan_entity->getSlide()->setBlock($block);        

            //TODO: i.e. if part's field is updated then add options to detect and update it.
            
            $em->persist($entity);       
            $em->persist($scan_entity);           
            
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'You successfully submit a scan request! Confirmation email sent!'
            );
            
            return $this->redirect( $this->generateUrl('scanorder') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'form_scan'   => $scan_form->createView(),
        );
    }

    /**
     * Displays a form to create a new OrderInfo + Scan entities.
     *
     * @Route("/new", name="scanorder_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ScanOrder:new.html.twig")
     */
    public function newAction()
    {         
        $entity = new OrderInfo();      
        $form   = $this->createForm(new OrderInfoType(), $entity);

        $scan_entity = new Scan();      
        $form_scan   = $this->createForm(new ScanType(), $scan_entity);
        
        return array(          
            'form' => $form->createView(),
            'form_scan' => $form_scan->createView(),
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
