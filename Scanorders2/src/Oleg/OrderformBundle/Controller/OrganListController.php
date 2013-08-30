<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\OrganList;
use Oleg\OrderformBundle\Form\OrganListType;

/**
 * OrganList controller.
 *
 * @Route("/organlist")
 */
class OrganListController extends Controller
{

    /**
     * Lists all OrganList entities.
     *
     * @Route("/", name="organlist")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:OrganList')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new OrganList entity.
     *
     * @Route("/", name="organlist_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:OrganList:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new OrganList();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('organlist_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a OrganList entity.
    *
    * @param OrganList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(OrganList $entity)
    {
        $form = $this->createForm(new OrganListType(), $entity, array(
            'action' => $this->generateUrl('organlist_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OrganList entity.
     *
     * @Route("/new", name="organlist_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new OrganList();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a OrganList entity.
     *
     * @Route("/{id}", name="organlist_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrganList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrganList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing OrganList entity.
     *
     * @Route("/{id}/edit", name="organlist_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrganList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrganList entity.');
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
    * Creates a form to edit a OrganList entity.
    *
    * @param OrganList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OrganList $entity)
    {
        $form = $this->createForm(new OrganListType(), $entity, array(
            'action' => $this->generateUrl('organlist_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OrganList entity.
     *
     * @Route("/{id}", name="organlist_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:OrganList:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:OrganList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrganList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('organlist_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a OrganList entity.
     *
     * @Route("/{id}", name="organlist_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:OrganList')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OrganList entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('organlist'));
    }

    /**
     * Creates a form to delete a OrganList entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('organlist_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
