<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\AccessionType;
use Oleg\OrderformBundle\Form\AccessionTypeType;

/**
 * AccessionType controller.
 *
 * @Route("/accessiontype")
 */
class AccessionTypeController extends Controller
{

    /**
     * Lists all AccessionType entities.
     *
     * @Route("/", name="accessiontype")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:AccessionType')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new AccessionType entity.
     *
     * @Route("/", name="accessiontype_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:AccessionType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new AccessionType();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('accessiontype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a AccessionType entity.
    *
    * @param AccessionType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(AccessionType $entity)
    {
        $form = $this->createForm(new AccessionTypeType(), $entity, array(
            'action' => $this->generateUrl('accessiontype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new AccessionType entity.
     *
     * @Route("/new", name="accessiontype_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new AccessionType();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a AccessionType entity.
     *
     * @Route("/{id}", name="accessiontype_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:AccessionType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AccessionType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing AccessionType entity.
     *
     * @Route("/{id}/edit", name="accessiontype_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:AccessionType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AccessionType entity.');
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
    * Creates a form to edit a AccessionType entity.
    *
    * @param AccessionType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AccessionType $entity)
    {
        $form = $this->createForm(new AccessionTypeType(), $entity, array(
            'action' => $this->generateUrl('accessiontype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing AccessionType entity.
     *
     * @Route("/{id}", name="accessiontype_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:AccessionType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:AccessionType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AccessionType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('accessiontype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a AccessionType entity.
     *
     * @Route("/{id}", name="accessiontype_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:AccessionType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AccessionType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('accessiontype'));
    }

    /**
     * Creates a form to delete a AccessionType entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('accessiontype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
