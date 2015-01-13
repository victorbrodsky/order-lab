<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Security\Util;



use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Entity\Logger;

class UserSecurityUtil {

    protected $em;
    protected $sc;
    protected $container;

    public function __construct( $em, $sc, $container ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
    }

    public function isCurrentUser( $id ) {

        $user = $this->sc->getToken()->getUser();

        $entity = $this->em->getRepository('OlegUserdirectoryBundle:User')->find($id);

        if( $entity && $entity->getId() === $user->getId() ) {
            return true;
        }

        return false;
    }


    //used by login success handler to get user has access request
    public function getUserAccessRequest($user,$sitename) {
        $accessRequest = $this->em->getRepository('OlegUserdirectoryBundle:AccessRequest')->findOneBy(
            array('user' => $user, 'siteName' => $sitename)
        );

        return $accessRequest;
    }

    public function getUserAccessRequestsByStatus($sitename, $status) {
        $accessRequests = $this->em->getRepository('OlegUserdirectoryBundle:AccessRequest')->findBy(
            array('siteName' => $sitename, 'status' => $status)
        );

        return $accessRequests;
    }


    //check for the role in security context and in the user DB
    public function hasGlobalUserRole( $role, $user=null ) {

        if( false === $this->sc->isGranted('IS_AUTHENTICATED_FULLY') )
            return false;

        if( $this->sc->isGranted($role) )
            return true;

        //get user from DB?

        if( $user == null )
            $user = $this->sc->getToken()->getUser();

//        if( $this->sc->isGranted('IS_AUTHENTICATED_ANONYMOUSLY') )
//            return false;

        if( !is_object($user) ) {
            //echo "user is not object: return false <br>";
            //exit();
            return false;
        } else {
            //echo "user is object <br>";
        }
        //exit();

        if( $user && $user->hasRole($role) ) {
            return true;
        }

        //echo "no role=".$role." => return false <br>";
        //exit();

        return false;
    }


    function idleLogout( $request, $sitename, $flag = null ) {

        $userUtil = new UserUtil();
        $res = $userUtil->getMaxIdleTimeAndMaintenance($this->em,$this->sc,$this->container);
        $maxIdleTime = $res['maxIdleTime'];
        $maintenance = $res['maintenance'];

        if( $maintenance ) {

            $msg = $userUtil->getSiteSetting($this->em,'MaintenancelogoutmsgWithDate');

        } else {

            if( $flag && $flag == 'saveorder' ) {
                $msg = 'You have been logged out after '.($maxIdleTime/60).' minutes of inactivity. You can find the order you have been working on in the list of your orders once you log back in.';
            } else {
                $msg = 'You have been logged out after '.($maxIdleTime/60).' minutes of inactivity.';
            }

        }

        $this->container->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        $this->container->get('security.context')->setToken(null);
        //$this->get('request')->getSession()->invalidate();


        //return $this->redirect($this->generateUrl('login'));

        return new RedirectResponse( $this->container->get('router')->generate($sitename.'_login') );

    }

    function constructEventLog( $sitename, $user, $request ) {

        $logger = new Logger($sitename);
        $logger->setUser($user);
        $logger->setRoles($user->getRoles());
        $logger->setUsername($user."");
        $logger->setUseragent($_SERVER['HTTP_USER_AGENT']);

        if( $request ) {
            $logger->setIp($request->getClientIp());
            $logger->setWidth($request->get('display_width'));
            $logger->setHeight($request->get('display_height'));
        }

        return $logger;
    }

//    public function getDefaultUserKeytypeSafe() {
//        $userUtil = new UserUtil();
//        $userkeytype = $userUtil->getDefaultUsernameType($this->em);
//        if( $userkeytype == null ) {
//            //generate user keytypes
//            $userUtil->generateUsernameTypes($this->em,null);
//            $userkeytype = $userUtil->getDefaultUsernameType($this->em);
//        }
//        return $userkeytype;
//    }
    public function getDefaultUsernameType() {
        $userUtil = new UserUtil();
        $userkeytype = $userUtil->getDefaultUsernameType($this->em);
        return $userkeytype;
    }


    public function getUsernameType($abbreviation=null) {
        $userkeytype = null;
        if( $abbreviation ) {
            $userkeytype = $this->em->getRepository('OlegUserdirectoryBundle:UsernameType')->findOneBy(
                array(
                    'type' => array('default', 'user-added'),
                    'abbreviation' => array($abbreviation)
                ),
                array('orderinlist' => 'ASC')
            );

            return $userkeytype;
        } else {
            $userkeytypes = $this->em->getRepository('OlegUserdirectoryBundle:UsernameType')->findBy(
                array('type' => array('default', 'user-added')),
                array('orderinlist' => 'ASC')
            );

            //echo "userkeytypes=".$userkeytypes."<br>";
            //print_r($userkeytypes);
            if( $userkeytypes && count($userkeytypes) > 0 ) {
                $userkeytype = $userkeytypes[0];
            }
            return $userkeytypes;
        }
    }

    public function createCleanUsername($username) {
        $user = new User();
        return $user->createCleanUsername($username);
    }

    public function getUsernamePrefix($username) {
        $user = new User();
        return $user->getUsernamePrefix($username);
    }

    public function usernameIsValid($username) {
        $user = new User();
        return $user->usernameIsValid($username);
    }


    public function getUserEmailsByRole($sitename,$userRole) {

        if( $userRole == "Platform Administrator" ) {

            $role = "ROLE_PLATFORM_DEPUTY_ADMIN";

        } else if( $userRole == "Administrator" ) {

            if( $sitename == $this->container->getParameter('scan.sitename') ) {
                $role = "ROLE_SCANORDER_ADMIN";
            }

            if( $sitename == $this->container->getParameter('employees.sitename') ) {
                $role = "ROLE_USERDIRECTORY_ADMIN";
            }

        } else {
            return null;
        }

        $users = $this->findByRole($role);

        //echo "user count=".count($users)."<br>";

        $emails = array();
        if( $users && count($users) > 0 ) {

            foreach( $users as $user ) {
                $emails[] = $user->getEmail();
            }

        }
        //print_r($emails);

        return implode(", ", $emails);
    }

    public function findByRole($role) {
        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('OlegUserdirectoryBundle:User', 'u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"' . $role . '"%');
        return $qb->getQuery()->getResult();
    }

    public function findSystemUser() {

        $systemusers = $this->em->getRepository('OlegUserdirectoryBundle:User')->findBy(
            array(
                'keytype' => NULL,
                'primaryPublicUserId' => 'system'
            )
        );

        if( !$systemusers || count($systemusers) == 0  ) {
            return null;
        }

        $systemuser = $systemusers[0];

        return $systemuser;
    }


    public function createUserEditEvent($sitename,$event,$user,$subjectEntity,$request,$action='User Updated') {
        $eventLog = $this->constructEventLog($sitename,$user,$request);
        $eventLog->setEvent($event);

        //set Event Type
        $em = $this->em;
        $eventtype = $em->getRepository('OlegUserdirectoryBundle:EventTypeList')->findOneByName($action);
        $eventLog->setEventType($eventtype);

        //get classname, entity name and id of subject entity
        $class = new \ReflectionClass($subjectEntity);
        $className = $class->getShortName();
        $classNamespace = $class->getNamespaceName();

        //set classname, entity name and id of subject entity
        $eventLog->setEntityNamespace($classNamespace);
        $eventLog->setEntityName($className);
        $eventLog->setEntityId($subjectEntity->getId());

        $em->persist($eventLog);
        $em->flush();
    }

}