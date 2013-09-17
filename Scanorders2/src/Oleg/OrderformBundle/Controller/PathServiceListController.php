<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\PathServiceList;
use Oleg\OrderformBundle\Form\PathServiceListType;

/**
 * PathServiceList controller.
 *
 * @Route("/pathservicelist")
 */
class PathServiceListController extends Controller
{

    /**
     * Lists all PathServiceList entities.
     *
     * @Route("/", name="pathservicelist")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:PathServiceList')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new PathServiceList entity.
     *
     * @Route("/", name="pathservicelist_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:PathServiceList:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new PathServiceList();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('pathservicelist_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a PathServiceList entity.
    *
    * @param PathServiceList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(PathServiceList $entity)
    {
        $form = $this->createForm(new PathServiceListType(), $entity, array(
            'action' => $this->generateUrl('pathservicelist_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new PathServiceList entity.
     *
     * @Route("/new", name="pathservicelist_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new PathServiceList();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a PathServiceList entity.
     *
     * @Route("/{id}", name="pathservicelist_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:PathServiceList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PathServiceList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing PathServiceList entity.
     *
     * @Route("/{id}/edit", name="pathservicelist_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:PathServiceList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PathServiceList entity.');
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
    * Creates a form to edit a PathServiceList entity.
    *
    * @param PathServiceList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(PathServiceList $entity)
    {
        $form = $this->createForm(new PathServiceListType(), $entity, array(
            'action' => $this->generateUrl('pathservicelist_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing PathServiceList entity.
     *
     * @Route("/{id}", name="pathservicelist_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:PathServiceList:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:PathServiceList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PathServiceList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('pathservicelist_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a PathServiceList entity.
     *
     * @Route("/{id}", name="pathservicelist_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:PathServiceList')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find PathServiceList entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('pathservicelist'));
    }

    /**
     * Creates a form to delete a PathServiceList entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pathservicelist_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
