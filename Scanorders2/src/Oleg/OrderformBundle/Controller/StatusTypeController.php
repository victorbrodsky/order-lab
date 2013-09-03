<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\StatusType;
use Oleg\OrderformBundle\Form\StatusTypeType;

/**
 * StatusType controller.
 *
 * @Route("/statustype")
 */
class StatusTypeController extends Controller
{

    /**
     * Lists all StatusType entities.
     *
     * @Route("/", name="statustype")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:StatusType')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new StatusType entity.
     *
     * @Route("/", name="statustype_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:StatusType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new StatusType();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('statustype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a StatusType entity.
    *
    * @param StatusType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(StatusType $entity)
    {
        $form = $this->createForm(new StatusTypeType(), $entity, array(
            'action' => $this->generateUrl('statustype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new StatusType entity.
     *
     * @Route("/new", name="statustype_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new StatusType();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a StatusType entity.
     *
     * @Route("/{id}", name="statustype_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:StatusType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StatusType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing StatusType entity.
     *
     * @Route("/{id}/edit", name="statustype_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:StatusType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StatusType entity.');
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
    * Creates a form to edit a StatusType entity.
    *
    * @param StatusType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(StatusType $entity)
    {
        $form = $this->createForm(new StatusTypeType(), $entity, array(
            'action' => $this->generateUrl('statustype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing StatusType entity.
     *
     * @Route("/{id}", name="statustype_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:StatusType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:StatusType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StatusType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('statustype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a StatusType entity.
     *
     * @Route("/{id}", name="statustype_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:StatusType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find StatusType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('statustype'));
    }

    /**
     * Creates a form to delete a StatusType entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('statustype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
