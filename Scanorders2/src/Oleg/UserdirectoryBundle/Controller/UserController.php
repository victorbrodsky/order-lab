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
use Oleg\UserdirectoryBundle\Entity\StateLicense;
use Oleg\UserdirectoryBundle\Entity\BoardCertification;
use Oleg\UserdirectoryBundle\Entity\CodeNYPH;



class UserController extends Controller
{

    /**
     * @Route("/user-directory", name="employees_listusers")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:users.html.twig")
     */
    public function indexUserAction()
    {
        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect($this->generateUrl('scan-order-nopermission'));
        }

        return $this->indexUser();
    }
    public function indexUser() {

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

//    /**
//     * @Route("/users/new", name="new_user")
//     * @Method("GET")
//     * @Template("OlegUserdirectoryBundle:Profile:register.html.twig")
//     */
//    public function newUserAction()
//    {
//        $entity = new User();
//
//        //Roles
//        $rolesArr = $this->getUserRoles();
//
//        $form = $this->createForm(new UserType('create',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')), $entity);
//
//        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
//        return array(
//            'entity' => $entity,
//            'form' => $form->createView(),
//            'cicle' => 'edit_user',
//        );
//    }


//    /**
//     * @Route("/users/new", name="create_user")
//     * @Method("POST")
//     * @Template("OlegUserdirectoryBundle:Profile:register.html.twig")
//     */
//    public function createUserAction( Request $request )
//    {
//        return createUser($request);
//    }
//    public function createUser($request) {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = new User();
//
//        //Roles
//        $rolesArr = $this->getUserRoles();
//
//        $form = $this->createForm(new UserType('create',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_SCANORDER_ADMIN')), $entity);
//
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em->persist($entity);
//            $em->flush();
//            return $this->redirect($this->generateUrl('listusers'));
//        }
//
//        return array(
//            'entity' => $entity,
//            'form' => $form->createView(),
//            'cicle' => 'edit_user',
//        );
//    }



    /**
     * @Route("/users/{id}", name="employees_showuser", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function showUserAction($id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        return $this->showUser($id,$this->container->getParameter('employees.sitename'));
    }
    public function showUser($id, $sitename=null) {
        $em = $this->getDoctrine()->getManager();

        //echo "id=".$id."<br>";

        if( $id == 0 || $id == '' || $id == '' ) {
            $entity = new User();
        } else {
            $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);
        }

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $this->addEmptyCollections($entity);

        $this->addHookFields($entity);

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('show',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_ADMIN')), $entity, array('disabled' => true));

//        if (!is_object($user) || !$user instanceof UserInterface) {
//            throw new AccessDeniedException('This user does not have access to this section.');
//        }

        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'show_user',
            'user_id' => $id,
            'sitename' => $sitename
        );
    }

    /**
     * @Route("/edit-user-profile/{id}", name="employees_user_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function editUserAction($id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        return $this->editUser($id, $this->container->getParameter('employees.sitename'));
    }

    public function editUser($id,$sitename=null) {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $this->addEmptyCollections($entity);

        $this->addHookFields($entity);

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('edit',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_ADMIN')), $entity, array(
            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
//        $form->add('submit', 'submit', array('label' => 'Update','attr' => array('class' => 'btn btn-warning')));

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cicle' => 'edit_user',
            'user_id' => $id,
            'sitename' => $sitename
        );
    }

    //create empty collections
    public function addEmptyCollections($entity) {

        if( count($entity->getAdministrativeTitles()) == 0 ) {
            $administrativeTitle = new AdministrativeTitle();
            $entity->addAdministrativeTitle($administrativeTitle);
        }

        if( count($entity->getAppointmentTitles()) == 0 ) {
            $appointmentTitle = new AppointmentTitle();
            $entity->addAppointmentTitle($appointmentTitle);
            //echo "app added, type=".$appointmentTitle->getType()."<br>";
        }

        if( count($entity->getCredentials()->getStateLicense()) == 0 ) {
            $entity->getCredentials()->addStateLicense( new StateLicense() );
        }

        if( count($entity->getCredentials()->getBoardCertification()) == 0 ) {
            $entity->getCredentials()->addBoardCertification( new BoardCertification() );
        }

        //if( count($entity->getCredentials()->getCodeNYPH()) == 0 ) {
            //$entity->getCredentials()->addCodeNYPH( new CodeNYPH() );
        //}

    }



    public function addHookFields($user) {
        //empty
    }

    /**
     * @Route("/edit-user-profile/{id}", name="employees_user_update")
     * @Method("PUT")
     * @Template("OlegUserdirectoryBundle:Profile:edit_user.html.twig")
     */
    public function updateUserAction(Request $request, $id)
    {
        $secUtil = $this->get('user_security_utility');
        if( !$secUtil->isCurrentUser($id) && false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        return $this->updateUser( $request, $id, $this->container->getParameter('employees.sitename') );
    }
    public function updateUser(Request $request, $id, $sitename)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

//        $entity->setPreferredPhone('111222333');
//        $uow = $em->getUnitOfWork();
//        $uow->computeChangeSets(); // do not compute changes if inside a listener
//        $changeset = $uow->getEntityChangeSet($entity);
//        print_r($changeset);
//        exit();

        //$oldEntity = clone $entity;
        //$oldUserArr = get_object_vars($oldEntity);

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
        //echo "count=".count($originalAdminTitles)."<br>";

        //Roles
        $rolesArr = $this->getUserRoles();

        $form = $this->createForm(new UserType('edit',$entity,$rolesArr,$this->get('security.context')->isGranted('ROLE_ADMIN')), $entity, array(
            'action' => $this->generateUrl($sitename.'_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        //$form->add('submit', 'submit', array('label' => 'Update'));

        $form->handleRequest($request);


        if( $form->isValid() ) {

            $this->removeCollection($entity,$originalAdminTitles,'getAdministrativeTitles');
            $this->removeCollection($entity,$originalAppTitles,'getAppointmentTitles');
            $this->removeLocationCollection($entity,$originalLocations,'getLocations');

            //$this->setEventLogChanges($entity,$request);

            //$em->persist($entity);
            $em->flush();

            //redirect only if this was called by the same controller class
            if( $sitename == $this->container->getParameter('employees.sitename') ) {
                return $this->redirect($this->generateUrl($sitename.'_showuser', array('id' => $id)));
            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'cicle' => 'edit_user',
            'user_id' => $id,
            'sitename' => $sitename
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

    public function removeLocationCollection($entity,$originalArr,$getMethod) {
        $em = $this->getDoctrine()->getManager();

        foreach( $originalArr as $title ) {

            //check if location is not home and main
            if( $title->getRemovable() == false ) {
                continue;
            }

            //echo "title=".$title->getName().", id=".$title->getId()."<br>";
            $em->persist($title);
            if( false === $entity->$getMethod()->contains($title) ) {
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
     * Generate users from excel
     *
     * @Route("/users/generate", name="generate_users")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Admin:users.html.twig")
     */
    public function generateUsersAction()
    {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
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
        return $this->redirect($this->generateUrl('employees_listusers'));
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



    /**
     * @Route("/lockunlock/change/{id}/{status}", name="employees_lockunlock_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function lockUnlockChangeAction($id, $status) {

        if (false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR')) {
            return $this->redirect( $this->generateUrl('scan-order-nopermission') );
        }

        $this->lockUnlock($id, $status);

        return $this->redirect($this->generateUrl($this->container->getParameter('employees.sitename').'_listusers'));
    }

    public function lockUnlock($id, $status) {

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        if( $status == "lock" ) {
            $user->setLocked(true);
        }

        if( $status == "unlock" ) {
            $user->setLocked(false);
        }

        $em->persist($user);
        $em->flush();

    }



    //Log user changes as in Issue #360
    //TODO: separate event log for scan and user. User log should record all changes in user: subjectUser, Author, field, old value, new value.
    public function setEventLogChanges($entity,$request) {
        
        $em = $this->getDoctrine()->getManager();

        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener

        $userSecUtil = $this->get('user_security_utility');
        $eventLog = $userSecUtil->constractEventLog($entity,$request);
        $user = $this->get('security.context')->getToken()->getUser();
        $event = "User information of ".$entity." has been changed by ".$user.":"."<br>";
        $eventLog->setEvent($event);

        //log simple fields
        $changeset = $uow->getEntityChangeSet($entity);
        $eventLog = $this->addToEventLog( $eventLog, $entity, $changeset );

        //log credentials
        $changeset = $uow->getEntityChangeSet($entity->getCredentials());
        $eventLog = $this->addToEventLog( $eventLog, $entity, $changeset );

        echo "event=".$eventLog->getEvent()."<br>";

        //exit();

        $em->persist($eventLog);
        $em->flush();
    }

    public function addToEventLog( $eventLog, $subjectUser, $changeset ) {

        //process $changeset: author, subjectuser, oldvalue, newvalue
        $changeArr = array();
        foreach( $changeset as $key => $value ) {
            $changeArr[] = $key.": "."old value=".$value[0].", new value=".$value[1];
        }
        $event = implode("<br>", $changeArr);

        //echo "event=".$event."<br>";

        $eventLog->addEvent( $event );

        return $eventLog;
    }

}
