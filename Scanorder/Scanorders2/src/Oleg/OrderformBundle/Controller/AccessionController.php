<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\Accession;
use Oleg\OrderformBundle\Form\AccessionType;

/**
 * Accession controller.
 *
 * @Route("/accession")
 */
class AccessionController extends Controller
{

    /**
     * Lists all Accession entities.
     *
     * @Route("/", name="accession")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:Accession')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Accession entity.
     *
     * @Route("/", name="accession_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:Accession:new_orig.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Accession();
        $form = $this->createForm(new AccessionType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('accession_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Accession entity.
     *
     * @Route("/new", name="accession_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Accession();
        $form   = $this->createForm(new AccessionType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Accession entity.
     *
     * @Route("/{id}", name="accession_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Accession')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Accession entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Accession entity.
     *
     * @Route("/{id}/edit", name="accession_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Accession')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Accession entity.');
        }

        $editForm = $this->createForm(new AccessionType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Accession entity.
     *
     * @Route("/{id}", name="accession_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Accession:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:Accession')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Accession entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new AccessionType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('accession_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Accession entity.
     *
     * @Route("/{id}", name="accession_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:Accession')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Accession entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('accession'));
    }

    /**
     * Creates a form to delete a Accession entity by id.
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
