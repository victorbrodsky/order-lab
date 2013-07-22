<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\Specimen;
use Oleg\OrderformBundle\Form\SpecimenType;

/**
 * Specimen controller.
 *
 * @Route("/specimen")
 */
class SpecimenController extends Controller
{

    /**
     * Lists all Specimen entities.
     *
     * @Route("/", name="specimen")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:Specimen')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Specimen entity.
     *
     * @Route("/", name="specimen_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Specimen:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Specimen();
        $form = $this->createForm(new SpecimenType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('specimen_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Specimen entity.
     *
     * @Route("/new", name="specimen_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Specimen();
        $form   = $this->createForm(new SpecimenType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Specimen entity.
     *
     * @Route("/{id}", name="specimen_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Specimen')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Specimen entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Specimen entity.
     *
     * @Route("/{id}/edit", name="specimen_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Specimen')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Specimen entity.');
        }

        $editForm = $this->createForm(new SpecimenType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Specimen entity.
     *
     * @Route("/{id}", name="specimen_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Specimen:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Specimen')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Specimen entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new SpecimenType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('specimen_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Specimen entity.
     *
     * @Route("/{id}", name="specimen_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Specimen')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Specimen entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('specimen'));
    }

    /**
     * Creates a form to delete a Specimen entity by id.
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
