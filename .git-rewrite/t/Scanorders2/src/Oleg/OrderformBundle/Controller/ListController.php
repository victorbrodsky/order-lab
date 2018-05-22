<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oleg\OrderformBundle\Entity\ReturnSlideTo;;

use Oleg\OrderformBundle\Form\GenericListType;


/**
 * Common list controller
 * @Route("/admin")
 */
class ListController extends Controller
{

    /**
     * Lists all entities.
     *
     * @Route("/mrntype/", name="mrntype")
     * @Route("/accessiontype/", name="accessiontype")
     * @Route("/stainlist/", name="stainlist")
     * @Route("/organlist/", name="organlist")
     * @Route("/procedurelist/", name="procedurelist")
     * @Route("/pathservicelist/", name="pathservicelist")
     * @Route("/slidetype/", name="slidetype")
     * @Route("/formtype/", name="formtype")
     * @Route("/status/", name="status")
     * @Route("/roles/", name="roles")
     * @Route("/returnslideto/", name="returnslideto")
     * @Route("/slidedelivery/", name="slidedelivery")
     * @Route("/regiontoscan/", name="regiontoscan")
     * @Route("/lists/scan-order-processor-comments/", name="processorcomments")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:index.html.twig")
     */
    public function indexAction()
    {

        $request = $this->container->get('request');
        $type = $request->get('_route');

        $em = $this->getDoctrine()->getManager();

        //echo "type=".$type; //mrntype

        //$entities = $em->getRepository('OlegOrderformBundle:'.$type)->findAll();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:'.$type);
        $dql =  $repository->createQueryBuilder("ent");
        $dql->select('ent');
        $dql->innerJoin("ent.creator", "creator");
        //$dql->orderBy("ent.createdate","DESC");

        $limit = 30;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        return array(
            'entities' => $entities,
            'type' => $type
        );
    }

    /**
     * Creates a new entity.
     *
     * @Route("/mrntype/", name="mrntype_create")
     * @Route("/accessiontype/", name="accessiontype_create")
     * @Route("/stainlist/", name="stainlist_create")
     * @Route("/organlist/", name="organlist_create")
     * @Route("/procedurelist/", name="procedurelist_create")
     * @Route("/pathservicelist/", name="pathservicelist_create")
     * @Route("/slidetype/", name="slidetype_create")
     * @Route("/formtype/", name="formtype_create")
     * @Route("/status/", name="status_create")
     * @Route("/roles/", name="roles_create")
     * @Route("/returnslideto/", name="returnslideto_create")
     * @Route("/slidedelivery/", name="slidedelivery_create")
     * @Route("/regiontoscan/", name="regiontoscan_create")
     * @Route("/lists/scan-order-processor-comments/", name="processorcomments_create")
     * @Method("POST")
     * @Template("OlegOrderformBundle:ListForm:new.html.twig")
     */
    public function createAction(Request $request)
    {

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        //echo "routeName=".$routeName; //mrntype

        $pieces = explode("_", $routeName);
        $type = $pieces[0];
        //echo "type=".$type."<br>";

        $entityClass = "Oleg\\OrderformBundle\\Entity\\".$type;

        $entity = new $entityClass();

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl($type.'_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'type' => $type
        );
    }

    /**
    * Creates a form to create an entity.
    * @param $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm($entity)
    {
        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();

        $options = array();
        if( method_exists($entity,'getOriginal') ) {
            $options['original'] = true;
        }

        $newForm = new GenericListType($className, $options);

        $create_path = strtolower($className);
        //echo "create_path=".$create_path;

        $form = $this->createForm($newForm, $entity, array(
            'action' => $this->generateUrl($create_path.'_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/mrntype/new", name="mrntype_new")
     * @Route("/accessiontype/new", name="accessiontype_new")
     * @Route("/stainlist/new", name="stainlist_new")
     * @Route("/organlist/new", name="organlist_new")
     * @Route("/procedurelist/new", name="procedurelist_new")
     * @Route("/pathservicelist/new", name="pathservicelist_new")
     * @Route("/slidetype/new", name="slidetype_new")
     * @Route("/formtype/new", name="formtype_new")
     * @Route("/status/new", name="status_new")
     * @Route("/roles/new", name="roles_new")
     * @Route("/returnslideto/new", name="returnslideto_new")
     * @Route("/slidedelivery/new", name="slidedelivery_new")
     * @Route("/regiontoscan/new", name="regiontoscan_new")
     * @Route("/lists/scan-order-processor-comments/new", name="processorcomments_new")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:new.html.twig")
     */
    public function newAction()
    {

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $type = $pieces[0];
        //echo "type=".$type."<br>";

        $entityClass = "Oleg\\OrderformBundle\\Entity\\".$type;

        $entity = new $entityClass();

        $user = $this->get('security.context')->getToken()->getUser();
        $entity->setCreatedate(new \DateTime());
        $entity->setType('user-added');
        $entity->setCreator($user);

        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'type' => $type
        );
    }

    /**
     * Finds and displays a entity.
     *
     * @Route("/mrntype/{id}", name="mrntype_show")
     * @Route("/accessiontype/{id}", name="accessiontype_show")
     * @Route("/stainlist/{id}", name="stainlist_show")
     * @Route("/organlist/{id}", name="organlist_show")
     * @Route("/procedurelist/{id}", name="procedurelist_show")
     * @Route("/pathservicelist/{id}", name="pathservicelist_show")
     * @Route("/slidetype/{id}", name="slidetype_show")
     * @Route("/formtype/{id}", name="formtype_show")
     * @Route("/status/{id}", name="status_show")
     * @Route("/roles/{id}", name="roles_show")
     * @Route("/returnslideto/{id}", name="returnslideto_show")
     * @Route("/slidedelivery/{id}", name="slidedelivery_show")
     * @Route("/regiontoscan/{id}", name="regiontoscan_show")
     * @Route("/lists/scan-order-processor-comments/{id}", name="processorcomments_show")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:show.html.twig")
     */
    public function showAction($id)
    {

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $type = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);
        $form = $this->createEditForm($entity,true);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$type.' entity.');
        }

        $deleteForm = $this->createDeleteForm($id,$type);

        return array(
            'entity'      => $entity,
            'edit_form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'type' => $type
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/mrntype/{id}/edit", name="mrntype_edit")
     * @Route("/accessiontype/{id}/edit", name="accessiontype_edit")
     * @Route("/stainlist/{id}/edit", name="stainlist_edit")
     * @Route("/organlist/{id}/edit", name="organlist_edit")
     * @Route("/procedurelist/{id}/edit", name="procedurelist_edit")
     * @Route("/pathservicelist/{id}/edit", name="pathservicelist_edit")
     * @Route("/slidetype/{id}/edit", name="slidetype_edit")
     * @Route("/formtype/{id}/edit", name="formtype_edit")
     * @Route("/status/{id}/edit", name="status_edit")
     * @Route("/roles/{id}/edit", name="roles_edit")
     * @Route("/returnslideto/{id}/edit", name="returnslideto_edit")
     * @Route("/slidedelivery/{id}/edit", name="slidedelivery_edit")
     * @Route("/regiontoscan/{id}/edit", name="regiontoscan_edit")
     * @Route("/lists/scan-order-processor-comments/{id}/edit", name="processorcomments_edit")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:edit.html.twig")
     */
    public function editAction($id)
    {

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $type = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$type.' entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id,$type);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'type' => $type
        );
    }

    /**
    * Creates a form to edit an entity.
    * @param $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm($entity,$disabled=false)
    {

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();

        $options = array();
        if( method_exists($entity,'getOriginal') ) {
            $options['original'] = true;
        }

        $newForm = new GenericListType($className, $options);

        $create_path = strtolower($className);

        $form = $this->createForm($newForm, $entity, array(
            'action' => $this->generateUrl($create_path.'_show', array('id' => $entity->getId())),
            'method' => 'PUT',
            'disabled' => $disabled
        ));

        if( !$disabled ) {
            $form->add('submit', 'submit', array('label' => 'Update'));
        }

        return $form;
    }
    /**
     * Edits an existing entity.
     *
     * @Route("/mrntype/{id}", name="mrntype_update")
     * @Route("/accessiontype/{id}", name="accessiontype_update")
     * @Route("/stainlist/{id}", name="stainlist_update")
     * @Route("/organlist/{id}", name="organlist_update")
     * @Route("/procedurelist/{id}", name="procedurelist_update")
     * @Route("/pathservicelist/{id}", name="pathservicelist_update")
     * @Route("/slidetype/{id}", name="slidetype_update")
     * @Route("/formtype/{id}", name="formtype_update")
     * @Route("/status/{id}", name="status_update")
     * @Route("/roles/{id}", name="roles_update")
     * @Route("/returnslideto/{id}", name="returnslideto_update")
     * @Route("/slidedelivery/{id}", name="slidedelivery_update")
     * @Route("/regiontoscan/{id}", name="regiontoscan_update")
     * @Route("/lists/scan-order-processor-comments/{id}", name="processorcomments_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:ListForm:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {

        //$request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $type = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$type.' entity.');
        }

        $deleteForm = $this->createDeleteForm($id,$type);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl($type.'_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'type' => $type
        );
    }
    /**
     * Deletes a entity.
     *
     * @Route("/mrntype/{id}", name="mrntype_delete")
     * @Route("/accessiontype/{id}", name="accessiontype_delete")
     * @Route("/stainlist/{id}", name="stainlist_delete")
     * @Route("/organlist/{id}", name="organlist_delete")
     * @Route("/procedurelist/{id}", name="procedurelist_delete")
     * @Route("/pathservicelist/{id}", name="pathservicelist_delete")
     * @Route("/slidetype/{id}", name="slidetype_delete")
     * @Route("/formtype/{id}", name="formtype_delete")
     * @Route("/status/{id}", name="status_delete")
     * @Route("/roles/{id}", name="roles_delete")
     * @Route("/returnslideto/{id}", name="returnslideto_delete")
     * @Route("/slidedelivery/{id}", name="slidedelivery_delete")
     * @Route("/regiontoscan/{id}", name="regiontoscan_delete")
     * @Route("/lists/scan-order-processor-comments/{id}", name="processorcomments_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {

        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $type = $pieces[0];

        $form = $this->createDeleteForm($id,$type);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find '.$type.' entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl($type));
    }

    /**
     * Creates a form to delete a entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id,$type)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($type.'_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
