<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oleg\OrderformBundle\Entity\UserRequest;
use Oleg\OrderformBundle\Form\UserRequestType;

/**
 * UserRequest controller.
 *
 * @Route("/accountrequest")
 */
class UserRequestController extends Controller
{

    /**
     * Lists all UserRequest entities.
     *
     * @Route("/", name="accountrequest")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:UserRequest')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new UserRequest entity.
     *
     * @Route("/", name="accountrequest_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:UserRequest:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new UserRequest();
        $form = $this->createForm(new UserRequestType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->processEntity( $entity );
            
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'You successfully submit a request for an Aperio eSlide Manager account!'
            );

            //return $this->redirect($this->generateUrl('scanorder_new', array('id' => $entity->getId())));
            return $this->redirect( $this->generateUrl('login') );
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new UserRequest entity.
     *
     * @Route("/new", name="accountrequest_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new UserRequest();
        $form   = $this->createForm(new UserRequestType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'security' => 'false'
        );
    }

    /**
     * Finds and displays a UserRequest entity.
     *
     * @Route("/{id}", name="accountrequest_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing UserRequest entity.
     *
     * @Route("/{id}/edit", name="accountrequest_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $editForm = $this->createForm(new UserRequestType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing UserRequest entity.
     *
     * @Route("/{id}", name="accountrequest_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:UserRequest:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new UserRequestType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {

            $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->processEntity( $entity );

            $em->persist($entity);
            $em->flush();

            return $this->redirect( $this->generateUrl('multy_new') );

            return $this->redirect($this->generateUrl('accountrequest_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a UserRequest entity.
     *
     * @Route("/{id}", name="accountrequest_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UserRequest entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('accountrequest'));
    }

    /**
     * Creates a form to delete a UserRequest entity by id.
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
    
    
    /**
     * @Route("/{id}/{status}/status", name="accountrequest_status")
     * @Method("GET")
     * @Template("OlegOrderformBundle:UserRequest:index.html.twig")
     */
    public function statusAction($id, $status)
    {
        
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:UserRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserRequest entity.');
        }
      
        $entity->setStatus($status);
        $em->persist($entity);
        $em->flush();
        
        
        return $this->redirect($this->generateUrl('accountrequest'));
            
    }
    
}
