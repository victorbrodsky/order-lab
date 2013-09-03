<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\ProcedureList;
use Oleg\OrderformBundle\Form\ProcedureListType;

/**
 * ProcedureList controller.
 *
 * @Route("/procedurelist")
 */
class ProcedureListController extends Controller
{

    /**
     * Lists all ProcedureList entities.
     *
     * @Route("/", name="procedurelist")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:ProcedureList')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new ProcedureList entity.
     *
     * @Route("/", name="procedurelist_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:ProcedureList:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ProcedureList();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('procedurelist_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a ProcedureList entity.
    *
    * @param ProcedureList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(ProcedureList $entity)
    {
        $form = $this->createForm(new ProcedureListType(), $entity, array(
            'action' => $this->generateUrl('procedurelist_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ProcedureList entity.
     *
     * @Route("/new", name="procedurelist_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ProcedureList();
        $form   = $this->createCreateForm($entity);
        //$form = $this->createForm(new ProcedureListType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a ProcedureList entity.
     *
     * @Route("/{id}", name="procedurelist_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:ProcedureList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProcedureList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ProcedureList entity.
     *
     * @Route("/{id}/edit", name="procedurelist_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:ProcedureList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProcedureList entity.');
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
    * Creates a form to edit a ProcedureList entity.
    *
    * @param ProcedureList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ProcedureList $entity)
    {
        $form = $this->createForm(new ProcedureListType(), $entity, array(
            'action' => $this->generateUrl('procedurelist_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ProcedureList entity.
     *
     * @Route("/{id}", name="procedurelist_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:ProcedureList:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:ProcedureList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProcedureList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('procedurelist_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a ProcedureList entity.
     *
     * @Route("/{id}", name="procedurelist_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:ProcedureList')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProcedureList entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('procedurelist'));
    }

    /**
     * Creates a form to delete a ProcedureList entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('procedurelist_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
