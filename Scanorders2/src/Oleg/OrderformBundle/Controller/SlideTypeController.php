<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\SlideType;
use Oleg\OrderformBundle\Form\SlideTypeType;

/**
 * SlideType controller.
 *
 * @Route("/slidetype")
 */
class SlideTypeController extends Controller
{

    /**
     * Lists all SlideType entities.
     *
     * @Route("/", name="slidetype")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:SlideType')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new SlideType entity.
     *
     * @Route("/", name="slidetype_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:SlideType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new SlideType();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('slidetype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a SlideType entity.
    *
    * @param SlideType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(SlideType $entity)
    {
        $form = $this->createForm(new SlideTypeType(), $entity, array(
            'action' => $this->generateUrl('slidetype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new SlideType entity.
     *
     * @Route("/new", name="slidetype_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new SlideType();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a SlideType entity.
     *
     * @Route("/{id}", name="slidetype_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:SlideType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SlideType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing SlideType entity.
     *
     * @Route("/{id}/edit", name="slidetype_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:SlideType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SlideType entity.');
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
    * Creates a form to edit a SlideType entity.
    *
    * @param SlideType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(SlideType $entity)
    {
        $form = $this->createForm(new SlideTypeType(), $entity, array(
            'action' => $this->generateUrl('slidetype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing SlideType entity.
     *
     * @Route("/{id}", name="slidetype_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:SlideType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:SlideType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SlideType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('slidetype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a SlideType entity.
     *
     * @Route("/{id}", name="slidetype_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:SlideType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SlideType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('slidetype'));
    }

    /**
     * Creates a form to delete a SlideType entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('slidetype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
