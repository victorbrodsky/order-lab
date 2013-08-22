<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\Educational;
use Oleg\OrderformBundle\Form\EducationalType;

/**
 * Educational controller.
 *
 * @Route("/educational")
 */
class EducationalController extends Controller
{

    /**
     * Lists all Educational entities.
     *
     * @Route("/", name="educational")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:Educational')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Educational entity.
     *
     * @Route("/", name="educational_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Educational:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Educational();
        $form = $this->createForm(new EducationalType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('educational_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Educational entity.
     *
     * @Route("/new", name="educational_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Educational();
        $form   = $this->createForm(new EducationalType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Educational entity.
     *
     * @Route("/{id}", name="educational_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Educational')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Educational entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Educational entity.
     *
     * @Route("/{id}/edit", name="educational_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Educational')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Educational entity.');
        }

        $editForm = $this->createForm(new EducationalType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Educational entity.
     *
     * @Route("/{id}", name="educational_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Educational:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Educational')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Educational entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new EducationalType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('educational_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Educational entity.
     *
     * @Route("/{id}", name="educational_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Educational')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Educational entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('educational'));
    }

    /**
     * Creates a form to delete a Educational entity by id.
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
