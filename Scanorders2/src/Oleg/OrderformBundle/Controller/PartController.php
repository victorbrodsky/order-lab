<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\Part;
use Oleg\OrderformBundle\Form\PartType;

/**
 * Part controller.
 *
 * @Route("/part")
 */
class PartController extends Controller
{

    /**
     * Lists all Part entities.
     *
     * @Route("/", name="part")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:Part')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Part entity.
     *
     * @Route("/", name="part_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Part:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Part();
        $form = $this->createForm(new PartType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('part_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Part entity.
     *
     * @Route("/new", name="part_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Part();
        $form   = $this->createForm(new PartType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Part entity.
     *
     * @Route("/{id}", name="part_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Part')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Part entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Part entity.
     *
     * @Route("/{id}/edit", name="part_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Part')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Part entity.');
        }

        $editForm = $this->createForm(new PartType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Part entity.
     *
     * @Route("/{id}", name="part_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Part:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Part')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Part entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new PartType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('part_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Part entity.
     *
     * @Route("/{id}", name="part_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Part')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Part entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('part'));
    }

    /**
     * Creates a form to delete a Part entity by id.
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
