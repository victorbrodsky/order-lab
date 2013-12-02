<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\MrnType;
use Oleg\OrderformBundle\Form\MrnTypeType;

/**
 * MrnType controller.
 *
 * @Route("/mrntype")
 */
class MrnTypeController extends Controller
{

    /**
     * Lists all MrnType entities.
     *
     * @Route("/", name="mrntype")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:MrnType')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new MrnType entity.
     *
     * @Route("/", name="mrntype_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:MrnType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new MrnType();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('mrntype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a MrnType entity.
    *
    * @param MrnType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(MrnType $entity)
    {
        $form = $this->createForm(new MrnTypeType(), $entity, array(
            'action' => $this->generateUrl('mrntype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new MrnType entity.
     *
     * @Route("/new", name="mrntype_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new MrnType();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a MrnType entity.
     *
     * @Route("/{id}", name="mrntype_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:MrnType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MrnType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing MrnType entity.
     *
     * @Route("/{id}/edit", name="mrntype_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:MrnType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MrnType entity.');
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
    * Creates a form to edit a MrnType entity.
    *
    * @param MrnType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(MrnType $entity)
    {
        $form = $this->createForm(new MrnTypeType(), $entity, array(
            'action' => $this->generateUrl('mrntype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing MrnType entity.
     *
     * @Route("/{id}", name="mrntype_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:MrnType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:MrnType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MrnType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('mrntype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a MrnType entity.
     *
     * @Route("/{id}", name="mrntype_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:MrnType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find MrnType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('mrntype'));
    }

    /**
     * Creates a form to delete a MrnType entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('mrntype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
