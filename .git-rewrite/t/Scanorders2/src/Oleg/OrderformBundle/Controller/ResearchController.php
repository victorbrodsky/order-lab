<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\Research;
use Oleg\OrderformBundle\Form\ResearchType;

/**
 * Research controller.
 *
 * @Route("/research")
 */
class ResearchController extends Controller
{

    /**
     * Lists all Research entities.
     *
     * @Route("/", name="research")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:Research')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Research entity.
     *
     * @Route("/", name="research_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Research:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Research();
        $form = $this->createForm(new ResearchType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('research_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Research entity.
     *
     * @Route("/new", name="research_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Research();
        $form   = $this->createForm(new ResearchType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Research entity.
     *
     * @Route("/{id}", name="research_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Research')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Research entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Research entity.
     *
     * @Route("/{id}/edit", name="research_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Research')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Research entity.');
        }

        $editForm = $this->createForm(new ResearchType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Research entity.
     *
     * @Route("/{id}", name="research_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Research:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Research')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Research entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ResearchType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('research_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Research entity.
     *
     * @Route("/{id}", name="research_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Research')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Research entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('research'));
    }

    /**
     * Creates a form to delete a Research entity by id.
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
