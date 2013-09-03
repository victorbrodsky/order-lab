<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\StatusGroup;
use Oleg\OrderformBundle\Form\StatusGroupType;

/**
 * StatusGroup controller.
 *
 * @Route("/statusgroup")
 */
class StatusGroupController extends Controller
{

    /**
     * Lists all StatusGroup entities.
     *
     * @Route("/", name="statusgroup")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:StatusGroup')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new StatusGroup entity.
     *
     * @Route("/", name="statusgroup_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:StatusGroup:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new StatusGroup();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('statusgroup_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a StatusGroup entity.
    *
    * @param StatusGroup $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(StatusGroup $entity)
    {
        $form = $this->createForm(new StatusGroupType(), $entity, array(
            'action' => $this->generateUrl('statusgroup_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new StatusGroup entity.
     *
     * @Route("/new", name="statusgroup_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new StatusGroup();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a StatusGroup entity.
     *
     * @Route("/{id}", name="statusgroup_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:StatusGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StatusGroup entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing StatusGroup entity.
     *
     * @Route("/{id}/edit", name="statusgroup_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:StatusGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StatusGroup entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a StatusGroup entity.
    *
    * @param StatusGroup $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(StatusGroup $entity)
    {
        $form = $this->createForm(new StatusGroupType(), $entity, array(
            'action' => $this->generateUrl('statusgroup_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing StatusGroup entity.
     *
     * @Route("/{id}", name="statusgroup_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:StatusGroup:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:StatusGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StatusGroup entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('statusgroup_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a StatusGroup entity.
     *
     * @Route("/{id}", name="statusgroup_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:StatusGroup')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find StatusGroup entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('statusgroup'));
    }

    /**
     * Creates a form to delete a StatusGroup entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('statusgroup_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
