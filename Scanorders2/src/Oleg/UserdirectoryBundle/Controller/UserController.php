<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

use Doctrine\Common\Collections\ArrayCollection;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Form\UserType;
use Oleg\UserdirectoryBundle\Entity\AdministrativeTitle;
use Oleg\UserdirectoryBundle\Entity\AppointmentTitle;



class UserController extends Controller
{

    /**
     * @Route("/user-directory", name="listusers")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:users.html.twig")
     */
    public function indexUserAction()
    {
        return $this->indexUser();
    }
    public function indexUser() {
        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect($this->generateUrl('scan-order-nopermission'));
        }

        //$userManager = $this->container->get('fos_user.user_manager');
        //$users = $userManager->findUsers();

        $rolesArr = $this->getUserRoles();

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
        $dql->leftJoin("administrativeTitles.institution", "administrativeInstitution");
        $dql->leftJoin("administrativeTitles.service", "administrativeService");
        //$dql->leftJoin("user.institutions", "institutions");
        //$dql->where("user.appliedforaccess = 'active'");
        $dql->orderBy("user.id","ASC");

        $limit = 1000;
        $em = $this->getDoctrine()->getManager();
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
     * @Route("/users/new", name="new_user")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:register.html.twig")
     */
    public function newUserAction()
    {
        //return $this->showUserAction(0);
        $em = $this->getDoctrine()->getManager();

        $entity = new User();

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('create',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')), $entity);

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'edit_user',
        );
    }


    /**
     * @Route("/users/new", name="create_user")
     * @Method("POST")
     * @Template("OlegUserdirectoryBundle:Profile:register.html.twig")
     */
    public function createUserAction( Request $request )
    {
        return createUser($request);
    }
    public function createUser($request) {
        //return $this->showUserAction(0);
        $em = $this->getDoctrine()->getManager();

        $entity = new User();

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('create',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')), $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('listusers'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'edit_user',
        );
    }



    /**
     * @Route("/users/{id}", name="showuser", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function showUserAction($id)
    {
        return $this->showUser($id);
    }
    public function showUser($id, $prefix=null) {
        $em = $this->getDoctrine()->getManager();

        $secUtil = $this->get('user_security_utility');

        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }
        //echo "id=".$id."<br>";

        if( $id == 0 || $id == '' || $id == '' ) {
            $entity = new User();
        } else {
            $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
        }

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $this->addHookFields($entity);

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('show',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')), $entity, array('disabled' => true));

//        if (!is_object($user) || !$user instanceof UserInterface) {
//            throw new AccessDeniedException('This user does not have access to this section.');
//        }

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'show_user',
            'user_id' => $id,
            'prefix' => $prefix
        );
    }

    /**
     * @Route("/edit-user-profile/{id}", name="user_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction($id)
    {
        return $this->editUser($id);
    }
    public function editUser($id,$prefix=null) {
        $em = $this->getDoctrine()->getManager();

        $secUtil = $this->get('user_security_utility');

        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

//        if( count($entity->getAdministrativeTitles()) == 0 ) {
//            $administrativeTitle = new AdministrativeTitle(true);
//            $entity->addAdministrativeTitle($administrativeTitle);
//        }
//
//        if( count($entity->getAppointmentTitles()) == 0 ) {
//            $appointmentTitle = new AppointmentTitle(true);
//            $entity->addAppointmentTitle($appointmentTitle);
//        }

        $this->addHookFields($entity);

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('edit',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
//        $form->add('submit', 'submit', array('label' => 'Update','attr' => array('class' => 'btn btn-warning')));

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'edit_user',
            'user_id' => $id,
            'prefix' => $prefix
        );
    }

    public function addHookFields($user) {
        //empty
    }

    /**
     * @Route("/users/{id}", name="user_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        return $this->updateUser($request,$id);
    }
    public function updateUser(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $secUtil = $this->get('user_security_utility');

        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        // Create an ArrayCollection of the current Tag objects in the database
        $originalAdminTitles = new ArrayCollection();
        foreach( $entity->getAdministrativeTitles() as $title) {
            $originalAdminTitles->add($title);
        }

        $originalAppTitles = new ArrayCollection();
        foreach( $entity->getAppointmentTitles() as $title) {
            $originalAppTitles->add($title);
        }

        $originalLocations = new ArrayCollection();
        foreach( $entity->getLocations() as $loc) {
            $originalLocations->add($loc);
        }
        echo "count=".count($originalAdminTitles)."<br>";

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('edit',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->add('submit', 'submit', array('label' => 'Update'));

        $form->handleRequest($request);

        if( $form->isValid() ) {

            $this->removeCollection($entity,$originalAdminTitles,'getAdministrativeTitles');
            $this->removeCollection($entity,$originalAppTitles,'getAppointmentTitles');
            $this->removeCollection($entity,$originalLocations,'getLocations');

            //$em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('showuser', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'cicle' => 'edit_user',
            'user_id' => $id
        );
    }


    public function removeCollection($entity,$originalArr,$getMethod) {
        $em = $this->getDoctrine()->getManager();

        foreach( $originalArr as $title ) {
            //echo "title=".$title->getName().", id=".$title->getId()."<br>";
            $em->persist($title);
            if( false === $entity->$getMethod()->contains($title) ) {
                //echo "removed title=".$title->getName()."<br>";
                // remove the Task from the Tag
                //$tag->getAdministrativeTitles()->removeElement($task);
                // if it was a many-to-one relationship, remove the relationship like this
                //$title->setUser(null);
                //$em->persist($title);
                // if you wanted to delete the Tag entirely, you can also do that
                $em->remove($title);
                $em->flush();
            }
        }
        //exit();
    }


//    /**
//     * @Route("/new_user1", name="new_user1")
//     * @Method("GET")
//     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
//     */
//    public function registerAction(Request $request)
//    {
//        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
//        $formFactory = $this->container->get('fos_user.registration.form.factory');
//        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
//        $userManager = $this->container->get('fos_user.user_manager');
//        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
//        $dispatcher = $this->container->get('event_dispatcher');
//
//        $user = $userManager->createUser();
//        $user->setEnabled(true);
//
//        $event = new GetResponseUserEvent($user, $request);
//        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
//
//        if (null !== $event->getResponse()) {
//            return $event->getResponse();
//        }
//
//        $form = $formFactory->createForm();
//        $form->setData($user);
//
//        if ('POST' === $request->getMethod()) {
//            $form->bind($request);
//
//            if ($form->isValid()) {
//                $event = new FormEvent($form, $request);
//                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
//
//                $userManager->updateUser($user);
//
//                if (null === $response = $event->getResponse()) {
//                    $url = $this->container->get('router')->generate('fos_user_registration_confirmed');
//                    $response = new RedirectResponse($url);
//                }
//
//                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
//
//                return $response;
//            }
//        }
//
//        return $this->container->get('templating')->renderResponse('OlegUserdirectoryBundle:Profile:register.html.twig', array(
//            'form' => $form->createView(),
//        ));
//    }




    /**
     * @Route("/users/generate", name="generate_users")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:users.html.twig")
     */
    public function generateUsersAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You do not have permission to visit this page'
            );
            return $this->redirect($this->generateUrl('scan-order-nopermission'));
        }

        $default_time_zone = $this->container->getParameter('default_time_zone');

        $userutil = new UserUtil();
        $usersCount = $userutil->generateUsersExcel($this->getDoctrine()->getManager(),$default_time_zone);

        //exit();
        return $this->redirect($this->generateUrl('listusers'));
    }

    public function getUserRoles() {
        $rolesArr = array();
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository('OlegUserdirectoryBundle:Roles')->findAll();
        foreach( $roles as $role ) {
            $rolesArr[$role->getName()] = $role->getAlias();
        }
        return $rolesArr;
    }


//    public function getUserChiefServices() {
//        $servArr = array();
//        $em = $this->getDoctrine()->getManager();
//        $services = $em->getRepository('OlegUserdirectoryBundle:Roles')->findAll();
//        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
//            foreach( $services as $service ) {
//                $servArr[$service->getName()] = $service->getAlias();
//            }
//        }
//        return $servArr;
//    }


}
