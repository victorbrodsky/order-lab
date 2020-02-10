<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\OrderformBundle\Entity\Status;
use App\OrderformBundle\Form\StatusType;

/**
 * Status controller.
 *
 * @Route("/status")
 */
class StatusController extends AbstractController
{

//    /**
//     * Lists all Status entities.
//     *
//     * @Route("/", name="status")
//     * @Method("GET")
//     * @Template()
//     */
//    public function indexAction()
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entities = $em->getRepository('AppOrderformBundle:Status')->findAll();
//
//        return array(
//            'entities' => $entities,
//        );
//    }
//    /**
//     * Creates a new Status entity.
//     *
//     * @Route("/", name="status_create")
//     * @Method("POST")
//     * @Template("AppOrderformBundle/Status/new.html.twig")
//     */
//    public function createAction(Request $request)
//    {
//        $entity = new Status();
//        $form = $this->createCreateForm($entity);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($entity);
//            $em->flush();
//
//            return $this->redirect($this->generateUrl('status_show', array('id' => $entity->getId())));
//        }
//
//        return array(
//            'entity' => $entity,
//            'form'   => $form->createView(),
//        );
//    }
//
//    /**
//    * Creates a form to create a Status entity.
//    *
//    * @param Status $entity The entity
//    *
//    * @return \Symfony\Component\Form\Form The form
//    */
//    private function createCreateForm(Status $entity)
//    {
//        $form = $this->createForm(new StatusType(), $entity, array(
//            'action' => $this->generateUrl('status_create'),
//            'method' => 'POST',
//        ));
//
//        $form->add('submit', 'submit', array('label' => 'Create'));
//
//        return $form;
//    }
//
//    /**
//     * Displays a form to create a new Status entity.
//     *
//     * @Route("/new", name="status_new")
//     * @Method("GET")
//     * @Template()
//     */
//    public function newAction()
//    {
//        $entity = new Status();
//        $form   = $this->createCreateForm($entity);
//
//        return array(
//            'entity' => $entity,
//            'form'   => $form->createView(),
//        );
//    }
//
//    /**
//     * Finds and displays a Status entity.
//     *
//     * @Route("/{id}", name="status_show")
//     * @Method("GET")
//     * @Template()
//     */
//    public function showAction($id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('AppOrderformBundle:Status')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Status entity.');
//        }
//
//        $deleteForm = $this->createDeleteForm($id);
//
//        return array(
//            'entity'      => $entity,
//            'delete_form' => $deleteForm->createView(),
//        );
//    }
//
//    /**
//     * Displays a form to edit an existing Status entity.
//     *
//     * @Route("/{id}/edit", name="status_edit")
//     * @Method("GET")
//     * @Template()
//     */
//    public function editAction($id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('AppOrderformBundle:Status')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Status entity.');
//        }
//
//        $editForm = $this->createEditForm($entity);
//        $deleteForm = $this->createDeleteForm($id);
//
//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        );
//    }
//
//    /**
//    * Creates a form to edit a Status entity.
//    *
//    * @param Status $entity The entity
//    *
//    * @return \Symfony\Component\Form\Form The form
//    */
//    private function createEditForm(Status $entity)
//    {
//        $form = $this->createForm(new StatusType(), $entity, array(
//            'action' => $this->generateUrl('status_update', array('id' => $entity->getId())),
//            'method' => 'PUT',
//        ));
//
//        $form->add('submit', 'submit', array('label' => 'Update'));
//
//        return $form;
//    }
//    /**
//     * Edits an existing Status entity.
//     *
//     * @Route("/{id}", name="status_update")
//     * @Method("PUT")
//     * @Template("AppOrderformBundle/Status/edit.html.twig")
//     */
//    public function updateAction(Request $request, $id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('AppOrderformBundle:Status')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find Status entity.');
//        }
//
//        $deleteForm = $this->createDeleteForm($id);
//        $editForm = $this->createEditForm($entity);
//        $editForm->handleRequest($request);
//
//        if ($editForm->isValid()) {
//            $em->flush();
//
//            return $this->redirect($this->generateUrl('status_edit', array('id' => $id)));
//        }
//
//        return array(
//            'entity'      => $entity,
//            'edit_form'   => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        );
//    }
//    /**
//     * Deletes a Status entity.
//     *
//     * @Route("/{id}", name="status_delete")
//     * @Method("DELETE")
//     */
//    public function deleteAction(Request $request, $id)
//    {
//        $form = $this->createDeleteForm($id);
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $entity = $em->getRepository('AppOrderformBundle:Status')->find($id);
//
//            if (!$entity) {
//                throw $this->createNotFoundException('Unable to find Status entity.');
//            }
//
//            $em->remove($entity);
//            $em->flush();
//        }
//
//        return $this->redirect($this->generateUrl('status'));
//    }
//
//    /**
//     * Creates a form to delete a Status entity by id.
//     *
//     * @param mixed $id The entity id
//     *
//     * @return \Symfony\Component\Form\Form The form
//     */
//    private function createDeleteForm($id)
//    {
//        return $this->createFormBuilder()
//            ->setAction($this->generateUrl('status_delete', array('id' => $id)))
//            ->setMethod('DELETE')
//            ->add('submit', 'submit', array('label' => 'Delete'))
//            ->getForm()
//        ;
//    }
}
