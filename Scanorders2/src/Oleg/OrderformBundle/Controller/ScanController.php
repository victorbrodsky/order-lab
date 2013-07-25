<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\Scan;
use Oleg\OrderformBundle\Entity\Slide;
use Oleg\OrderformBundle\Form\ScanType;
use Oleg\OrderformBundle\Helper\FormHelper;

/**
 * Scan controller.
 *
 * @Route("/scan")
 */
class ScanController extends Controller
{

    /**
     * Lists all Scan entities.
     *
     * @Route("/", name="scan")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:Scan')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Scan entity.
     *
     * @Route("/", name="scan_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Scan:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Scan();
        $form = $this->createForm(new ScanType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('scan_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Scan entity.
     *
     * @Route("/new", name="scan_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $helper = new FormHelper();
        $entity = new Scan();
        
        //$slide= new Slide(); 
        //$entity->setSlide($slide);
                
        $entity->setMag( key($helper->getMags()) );       
        
        $form   = $this->createForm(new ScanType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Scan entity.
     *
     * @Route("/{id}", name="scan_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Scan')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Scan entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Scan entity.
     *
     * @Route("/{id}/edit", name="scan_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Scan')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Scan entity.');
        }

        $editForm = $this->createForm(new ScanType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Scan entity.
     *
     * @Route("/{id}", name="scan_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Scan:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Scan')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Scan entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ScanType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('scan_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Scan entity.
     *
     * @Route("/{id}", name="scan_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Scan')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Scan entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('scan'));
    }

    /**
     * Creates a form to delete a Scan entity by id.
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
