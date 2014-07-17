<?php

namespace Oleg\OrderformBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

use Oleg\OrderformBundle\Entity\PathService;
use Oleg\OrderformBundle\Form\UserType;
use Oleg\OrderformBundle\Helper\UserUtil;
use Symfony\Component\HttpFoundation\Session\Session;

use Oleg\OrderformBundle\Entity\User;
use Oleg\OrderformBundle\Security\Util\SecurityUtil;
use Oleg\OrderformBundle\Security\Util\AperioUtil;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;


class UserController extends Controller
{

    /**
     * @Route("/user-directory", name="listusers")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:users.html.twig")
     */
    public function indexUserAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            return $this->redirect($this->generateUrl('scan-order-nopermission'));
        }

        //$userManager = $this->container->get('fos_user.user_manager');
        //$users = $userManager->findUsers();

        $rolesArr = $this->getUserRoles();

        $repository = $this->getDoctrine()->getRepository('OlegOrderformBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin("user.pathologyServices", "pathologyServices");
        $dql->leftJoin("user.institution", "institutions");
        //$dql->where("user.appliedforaccess = 'active'");
        $dql->orderBy("user.id","ASC");
        //$dql->orderBy("pathologyServices.name","DESC");   //test many-to-many sorting

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
     * @Template("OlegOrderformBundle:Profile:register.html.twig")
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
     * @Template("OlegOrderformBundle:Profile:register.html.twig")
     */
    public function createUserAction( Request $request )
    {
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
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function showUserAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $secUtil = new SecurityUtil($em,$this->get('security.context'),$this->get('session'));

        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }
        //echo "id=".$id."<br>";

        if( $id == 0 || $id == '' || $id == '' ) {
            $entity = new User();
        } else {
            $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);
        }

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        //$ps = new PathService();
        //$entity->addPathologyServices($ps);

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
            'user_id' => $id
        );
    }

    /**
     * @Route("/edit-user-profile/{id}", name="user_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $secUtil = new SecurityUtil($em,$this->get('security.context'),$this->get('session'));

        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

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
            'user_id' => $id
        );
    }

    /**
     * @Route("/users/{id}", name="user_update")
     * @Method("PUT")
     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $secUtil = new SecurityUtil($em,$this->get('security.context'),$this->get('session'));

        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('edit',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        $form->add('submit', 'submit', array('label' => 'Update'));

        $form->handleRequest($request);

        if( count($entity->getInstitution()) == 0 && $entity->getUsername() != 'system' ) {
            $instLink = '<a href="'.$this->generateUrl('institutions-list').'">add the new institution name directly.</a>';
            $error = new FormError("Please add at least one institution. If you do not see your institution listed, please inform the System Administrator or ".$instLink);
            $form->get('institution')->addError($error);
        }

        if( $form->isValid() ) {
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


//    /**
//     * @Route("/new_user1", name="new_user1")
//     * @Method("GET")
//     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
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
//        return $this->container->get('templating')->renderResponse('OlegOrderformBundle:Profile:register.html.twig', array(
//            'form' => $form->createView(),
//        ));
//    }




    /**
     * @Route("/users/generate", name="generate_users")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:users.html.twig")
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
        $roles = $em->getRepository('OlegOrderformBundle:Roles')->findAll();
        foreach( $roles as $role ) {
            $rolesArr[$role->getName()] = $role->getAlias();
        }
        return $rolesArr;
    }


    /**
     * @Route("/admin/load-roles-from-aperio", name="load-roles-from-aperio")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:load-roles-from-aperio.html.twig")
     */
    public function loadRolesFromAperioAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You do not have permission to visit this page'
            );
            return $this->redirect($this->generateUrl('scan-order-nopermission'));
        }

        $notfoundusers = array();
        $results = array();
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('OlegOrderformBundle:User')->findAll();

        //echo "count=".count($users)."<br>";

        foreach( $users as $user ) {

            //************** get Aperio group roles and ROLE_SCANORDER_ORDERING_PROVIDER for this user **************//
            $aperioUtil = new AperioUtil();

            $username = $user->getUsername()."";

            //echo "username=".$username. " => ";

            $userid = $aperioUtil->getUserIdByUserName($username);

            //echo "userid=".$userid." => ";

            if( !$userid || $userid == '' ) {

                $userArr = array();
                $userArr['user'] = $user;
                //$userArr['stats'] = $stats;
                $notfoundusers[] = $userArr;

            } else {
                $aperioRoles = $aperioUtil->getUserGroupMembership($userid);

                $addedRoles = $aperioUtil->setUserPathologyRolesByAperioRoles( $user, $aperioRoles );

                if( count($addedRoles) == 0 ) {

                    $stats = 'No changes';

                } else {

                    $stats = 'Added roles of ';
                    $count = 1;
                    foreach( $addedRoles as $addedRole ) {
                        //echo "role=(".$addedRole.") ";
                        $stats = $stats . $addedRole;
                        if( count($addedRoles) > $count ) {
                            $stats = $stats . ', ';
                        }
                        $count++;
                    }

                    $em->persist($user);
                    $em->flush();
                }

                //$url = $this->generateUrl('showuser', array('id' => $user->getId()) );
                //$userLink = '<a href="'.$url.'">'.$user.'</a>';
                $userArr = array();
                $userArr['user'] = $user;
                $userArr['stats'] = $stats;
                $results[] = $userArr;

            }

//            //echo "username=(".$username.")\n";
//            //echo "userid=(".$userid.")\n";
//            if( $username == 'oli2002' ) {
//                print_r($aperioRoles);
//                //exit('aperio util');
//            }
            //************** end of  Aperio group roles **************//

        }

//        $this->get('session')->getFlashBag()->add(
//            'status-changed',
//            $stats
//        );

        return array(
            'results' => $results,
            'notfoundusers' => $notfoundusers
        );

        //return $this->redirect($this->generateUrl('admin_index'));
    }

//    public function getUserChiefServices() {
//        $servArr = array();
//        $em = $this->getDoctrine()->getManager();
//        $services = $em->getRepository('OlegOrderformBundle:Roles')->findAll();
//        if( $this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN') ) {
//            foreach( $services as $service ) {
//                $servArr[$service->getName()] = $service->getAlias();
//            }
//        }
//        return $servArr;
//    }


}
