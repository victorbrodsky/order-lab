<?php

namespace Oleg\OrderformBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Oleg\OrderformBundle\Entity\PathService;
use Oleg\OrderformBundle\Form\UserType;
use Oleg\OrderformBundle\Helper\UserUtil;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * StainList controller.
 *
 * @Route("/user")
 */
class UserController extends Controller
{

    /**
     * @Route("/listusers", name="listusers")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:users.html.twig")
     */
    public function indexUserAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You do not have permission to visit this page'
            );
            return $this->redirect($this->generateUrl('index'));
        }

        //$userManager = $this->container->get('fos_user.user_manager');
        //$users = $userManager->findUsers();

        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('OlegOrderformBundle:Roles')->findAll();
        $rolesArr = array();
        if( $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        }

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin("user.pathologyServices", "pathologyServices");
        //$dql->where("user.appliedforaccess = 'active'");
        $dql->orderBy("user.id","ASC");
        //$dql->orderBy("pathologyServices.name","DESC");   //test many-to-many sorting

        $limit = 30;
        $query = $em->createQuery($dql);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1), /*page number*/
            $limit/*limit per page*/
        );

        return array(
            'entities' => $pagination,
            'roles' => $rolesArr
        );
    }

    /**
     * @Route("/show_user/{id}", name="showuser", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function showUserAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);
        //$ps = new PathService();
        //$entity->addPathologyServices($ps);

        //Roles
        $roles = $em->getRepository('OlegOrderformBundle:Roles')->findAll();
        $rolesArr = array();
        if( $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        }

        $form = $this->createForm(new UserType('show',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_ADMIN')), $entity, array('disabled' => true));

//        if (!is_object($user) || !$user instanceof UserInterface) {
//            throw new AccessDeniedException('This user does not have access to this section.');
//        }

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'show_user',
            'user_id' => $id
        );
    }

    /**
     * @Route("/edit_user/{id}", name="user_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);

        //Roles
        $roles = $em->getRepository('OlegOrderformBundle:Roles')->findAll();
        $rolesArr = array();
        if( $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        }

        $form = $this->createForm(new UserType('edit',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_ADMIN')), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
//        $form->add('submit', 'submit', array('label' => 'Update','attr' => array('class' => 'btn btn-warning')));

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'edit_user',
            'user_id' => $id
        );
    }

    /**
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //Roles
        $roles = $em->getRepository('OlegOrderformBundle:Roles')->findAll();
        $rolesArr = array();
        if( $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            foreach( $roles as $role ) {
                $rolesArr[$role->getName()] = $role->getAlias();
            }
        }

        $form = $this->createForm(new UserType('edit',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_ADMIN')), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->add('submit', 'submit', array('label' => 'Update'));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('showuser', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'cicle' => 'edit_user',
            'user_id' => $id
//            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * @Route("/genusers", name="generate_users")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:users.html.twig")
     */
    public function generateUsersAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You do not have permission to visit this page'
            );
            return $this->redirect($this->generateUrl('index'));
        }

        $userutil = new UserUtil();
        $usersCount = $userutil->generateUsersExcel($this->getDoctrine()->getManager());

        //exit();
        return $this->redirect($this->generateUrl('listusers'));
    }




}
