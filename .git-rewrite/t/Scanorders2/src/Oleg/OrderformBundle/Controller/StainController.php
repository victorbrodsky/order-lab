<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\Stain;
use Oleg\OrderformBundle\Form\StainType;

/**
 * Stain controller.
 *
 * @Route("/stain")
 */
class StainController extends Controller
{

    /**
     * Lists all Stain entities.
     *
     * @Route("/", name="stain")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:Stain')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Stain entity.
     *
     * @Route("/", name="stain_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Stain:new_orig.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Stain();
        $form = $this->createForm(new StainType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('stain_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Stain entity.
     *
     * @Route("/new", name="stain_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Stain();
        $form   = $this->createForm(new StainType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Stain entity.
     *
     * @Route("/{id}", name="stain_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Stain')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Stain entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Stain entity.
     *
     * @Route("/{id}/edit", name="stain_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Stain')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Stain entity.');
        }

        $editForm = $this->createForm(new StainType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Stain entity.
     *
     * @Route("/{id}", name="stain_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Stain:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Stain')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Stain entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new StainType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('stain_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Stain entity.
     *
     * @Route("/{id}", name="stain_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Stain')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Stain entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('stain'));
    }

    /**
     * Creates a form to delete a Stain entity by id.
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
