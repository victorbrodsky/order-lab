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

            $roles = array("ROLE_PLATFORM_ADMIN","ROLE_PLATFORM_DEPUTY_ADMIN");

        } else if( $userRole == "Administrator" ) {

            if( $sitename == $this->container->getParameter('scan.sitename') ) {
                $roles = array("ROLE_SCANORDER_ADMIN");
            }

            if( $sitename == $this->container->getParameter('employees.sitename') ) {
                $roles = array("ROLE_USERDIRECTORY_ADMIN");
            }

        } else {
            return null;
        }

        $users = $this->findByRoles($roles);

        //echo "user count=".count($users)."<br>";

        $emails = array();
        if( $users && count($users) > 0 ) {

            foreach( $users as $user ) {
                //echo "user=".$user."<br>";
                if( $user->getEmail() ) {
                    $emails[] = $user->getEmail();
                }
            }

        }
        //print_r($emails);

        return implode(", ", $emails);
    }

    public function findByRoles($roles) {

        $whereArr = array();
        foreach($roles as $role) {
            $whereArr[] = 'u.roles LIKE '."'%\"" . $role . "\"%'";
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('u')
            ->from('OlegUserdirectoryBundle:User', 'u')
            ->where( implode(' OR ',$whereArr) );

        //echo "query=".$qb."<br>";

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

    //add type to tree entity if exists
    public function addDefaultType($entity,$params) {
        $fullClassName = new \ReflectionClass($entity);
        $className = $fullClassName->getShortName();

        //add institutional type
        if( $className == "Institution" ) {
            if( array_key_exists('type',$params) && $params['type'] ) {
                $type = $this->em->getRepository('OlegUserdirectoryBundle:InstitutionType')->findOneByName($params['type']);
                $entity->addType($type);
            }
        }

        return $entity;
    }

    public function getDefaultSourceSystem() {
        $defaultSourceSystemName = 'Scan Order';
        $source = $this->em->getRepository('OlegUserdirectoryBundle:SourceSystemList')->findOneByName($defaultSourceSystemName);
        if( !$source ) {
            if( $this->container ) {
                $logger = $this->container->get('logger');
                $logger->warning('Warning (Not Found): Default Source System with name '.$defaultSourceSystemName);
            }
        }
        //echo "source=".$source."<br>";
        return $source;
    }


    //username - full username including user type ie svc_aperio_spectrum_@_wcmc-cwid
    public function constractNewUser($username) {

        $serviceContainer = $this->container;
        $em = $this->em;
        $userManager = $serviceContainer->get('fos_user.user_manager');
        $userSecUtil = $serviceContainer->get('user_security_utility');

        $usernamePrefix = $userSecUtil->getUsernamePrefix($username);
        $usernameClean = $userSecUtil->createCleanUsername($username);

        $default_time_zone = $serviceContainer->getParameter('default_time_zone');

        $user = $userManager->createUser();

        //////////////////////////////// get usertype ////////////////////////////////
        $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);
        //echo "keytype=".$userkeytype."<br>";

        //first time login when DB is clean
        if( !$userkeytype ) {
            $userUtil = new UserUtil();
            $count_usernameTypeList = $userUtil->generateUsernameTypes($this->em);
            $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);
        }

        if( !$userkeytype ) {
            throw new \Exception('User keytype is empty for prefix '.$usernamePrefix);
        }
        //////////////////////////////// EOF get usertype ////////////////////////////////

        $user->setKeytype($userkeytype);
        $user->setPrimaryPublicUserId($usernameClean);
        $user->setUniqueUsername();

        $user->setEnabled(true);
        $user->getPreferences()->setTimezone($default_time_zone);

        //add default locations
        $userUtil = new UserUtil();
        $userUtil->addDefaultLocations($user,null,$em,$serviceContainer);

        $user->setPassword("");

        //$userManager->updateUser($user);

        return $user;
    }


    //$name is entered by a user username. $name can be a guessed username
    //Use primaryPublicUserId as cwid
    public function getUserByUserstr( $name ) {

        //echo "get cwid name=".$name."<br>";

        $user = null;
        $cwid = null;

        //get cwid
        $strArr = explode(" ",$name);

        if( count($strArr) > 0 ) {
            $cwid = $strArr[0];
        }

        //1) try first part
        if( $cwid ) {
            //echo "cwid=".$cwid."<br>";
            $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($cwid);
        }

        //2) try full name
        if( !$user ) {
            $user = $this->em->getRepository('OlegUserdirectoryBundle:User')->findOneByPrimaryPublicUserId($name);
        }

        //3) try full name
        if( !$user ) {

            $query = $this->em->createQueryBuilder()
                ->from('OlegUserdirectoryBundle:User', 'user')
                ->select("user")
                ->leftJoin("user.infos", "infos")
                ->where("infos.email=:name OR infos.displayName=:name")
                ->setParameters( array(
                    'name' => $name
                ));

            $users = $query->getQuery()->getResult();

            if( count($users) > 0 ) {
                $user = $users->first();
            }

        }

        return $user;
    }

}