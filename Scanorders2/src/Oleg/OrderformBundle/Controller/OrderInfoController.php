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
use Oleg\OrderformBundle\Helper\FormHelper;

/**
 * OrderInfo controller.
 *
 * @Route("/orderinfo")
 */
class OrderInfoController extends Controller {

    /**
     * Lists all OrderInfo entities.
     *
     * @Route("/", name="orderinfo")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:OrderInfo')->findAll();
        
        //echo "count=".count($entities);
        //$scan = $entities->getScan()->getSlide();
        //$scan = $entities[0]->getScan();
        //echo "scan mag=".$scan->getMag();
        //$slide = $scan->getSlide();
        return array(
            'entities' => $entities,
            //'num_slides' => count($slide)
        );
    }
    /**
     * Creates a new OrderInfo entity.
     *
     * @Route("/", name="orderinfo_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:OrderInfo:new.html.twig")
     */
    public function createAction(Request $request)
    {
        //echo "orderinfo createAction";
        $entity  = new OrderInfo();
        $form = $this->createForm(new OrderInfoType(), $entity);
        $form->bind($request);
        
        $scan_entity = new Scan();
        $scan_form = $this->createForm(new ScanType(), $scan_entity);
        $scan_form->bind($request);

        if( $form->isValid() && $scan_form->isValid() ) {
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($entity);       
            $em->persist($scan_entity);
            
            $entity->setStatus("submitted");
                    
            $scan_entity->setOrderinfo($entity);
            
            $em->flush();

            return $this->redirect($this->generateUrl('orderinfo_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new OrderInfo entity.
     *
     * @Route("/new", name="orderinfo_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $helper = new FormHelper();
        
        $entity = new OrderInfo();      
        $form   = $this->createForm(new OrderInfoType(), $entity);

        $scan_entity = new Scan();
        $scan_entity->setMag( key($helper->getMags()) );
        //$entity->addScan($scan_entity);
        $form_scan   = $this->createForm(new ScanType(), $scan_entity);
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'form_scan'   => $form_scan->createView(),
        );
    }

    /**
     * Finds and displays a OrderInfo entity.
     *
     * @Route("/{id}", name="orderinfo_show")
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
     * @Route("/{id}/edit", name="orderinfo_edit")
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
     * @Route("/{id}", name="orderinfo_update")
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

            return $this->redirect($this->generateUrl('orderinfo_edit', array('id' => $id)));
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
     * @Route("/{id}", name="orderinfo_delete")
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

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('orderinfo'));
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
