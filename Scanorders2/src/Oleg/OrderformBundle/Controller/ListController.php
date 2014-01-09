<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

//use Oleg\OrderformBundle\Entity\MrnType;
use Oleg\OrderformBundle\Form\GenericListType;

/**
 * Common list controller
 */
class ListController extends Controller
{

    /**
     * Lists all entities.
     *
     * @Route("/mrntype/", name="mrntype")
     * @Route("/accessiontype/", name="accessiontype")
     * @Route("/stainlist/", name="stainlist")
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:index.html.twig")
     */
    public function indexAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->container->get('request');
        $type = $request->get('_route');
        //echo "type=".$type; //mrntype

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('OlegOrderformBundle:'.$type)->findAll();

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
     * @Method("POST")
     * @Template("OlegOrderformBundle:ListForm:new.html.twig")
     */
    public function createAction(Request $request)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

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

        //$formClass = "Oleg\\OrderformBundle\\Form\\".$className."Type";
        //$newForm = new $formClass($className);

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
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:new.html.twig")
     */
    public function newAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $type = $pieces[0];
        echo "type=".$type."<br>";

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
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:show.html.twig")
     */
    public function showAction($id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

        $request = $this->container->get('request');
        $routeName = $request->get('_route');
        $pieces = explode("_", $routeName);
        $type = $pieces[0];

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:'.$type)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find '.$type.' entity.');
        }

        $deleteForm = $this->createDeleteForm($id,$type);

        return array(
            'entity'      => $entity,
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
     * @Method("GET")
     * @Template("OlegOrderformBundle:ListForm:edit.html.twig")
     */
    public function editAction($id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

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
    private function createEditForm($entity)
    {

        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();

        //$formClass = "Oleg\\OrderformBundle\\Form\\".$className."Type";
        //$newForm = new $formClass();

        $options = array();
        if( method_exists($entity,'getOriginal') ) {
            $options['original'] = true;
        }

        $newForm = new GenericListType($className, $options);

        $create_path = strtolower($className);

        $form = $this->createForm($newForm, $entity, array(
            'action' => $this->generateUrl($create_path.'_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing entity.
     *
     * @Route("/mrntype/{id}", name="mrntype_update")
     * @Route("/accessiontype/{id}", name="accessiontype_update")
     * @Route("/stainlist/{id}", name="stainlist_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:ListForm:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

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

            return $this->redirect($this->generateUrl($type.'_edit', array('id' => $id)));
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
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            return $this->render('OlegOrderformBundle:Security:login.html.twig');
        }

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
