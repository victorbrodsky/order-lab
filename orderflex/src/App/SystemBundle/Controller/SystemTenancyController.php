<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/25/2024
 * Time: 12:35 PM
 */

namespace App\SystemBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\AuthServerNetworkList;
use App\UserdirectoryBundle\Entity\HostedGroupHolder;
use App\UserdirectoryBundle\Entity\HostedUserGroupList;
use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Entity\UsernameType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Attribute\Template;

//NOT USED
class SystemTenancyController extends OrderAbstractController
{

    // /system/home/
    //#[Route(path: '/', name: 'system-home')]
    //#[Template('AppSystemBundle/home/system-home.html.twig')]
    public function systemHomeAction(Request $request)
    {
        //exit("systemHomeAction");
        $em = $this->getDoctrine()->getManager('systemdb');
        $users = $em->getRepository(User::class)->findAll();
        echo "users=".count($users)."<br>";
        //exit("111");

        $msg = '';
        if (count($users) == 0) {
            $this->initialUsers();
            $msg = $msg . "Administrator user has been created<br>";
        }

        $params = $em->getRepository(SiteParameters::class)->findAll();
        echo "system params=".count($params)."<br>";
        if( count($params) == 0 ) {
            $this->generateSiteParameters();
            $msg = $msg . "Site Parameters for System DB has been created<br>";
        }

        $title = "System";

        $params = array(
            'title' => $title,
            'msg' => $msg
        );
        return $this->render('AppSystemBundle/home/system-home.html.twig', $params);
    }

    public function systemInitAction(Request $request) {
        $em = $this->getDoctrine()->getManager('systemdb');
        $users = $em->getRepository(User::class)->findAll();
        if (count($users) == 0) {
            $this->initialUsers();
        }
        return $this->redirect($this->generateUrl('system_special_home'));
    }

    #[Route(path: '/initial-settings', name: 'system_initial_settings', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Default/home.html.twig')]
    public function initialSettingsAction() {
        exit("initialSettingsAction");
        $em = $this->getDoctrine()->getManager('systemdb');
        $users = $em->getRepository(User::class)->findAll();
        echo "users=".count($users)."<br>";
        exit("111");
        if( count($users) == 0 ) {
            $this->initialUsers();
        }

//        $params = $em->getRepository(SiteParameters::class)->findAll();
//        echo "system params=".count($params)."<br>";
//        if( count($params) == 0 ) {
//            $params = new SiteParameters();
//        } else {
//            $params = $siteParameters[0];
//        }

        exit("initialSettingsAction");
    }

    public function initialUsers() {
        //exit("initialUsers");
        echo "initialUsers <br>";
        $userUtil = $this->container->get('user_utility');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager('systemdb');
        $users = $em->getRepository(User::class)->findAll();
        echo "users=".count($users)."<br>";
        if (count($users) == 0) {

            $usernamePrefix = "local-user";
            $adminemail = "";//"email@example.com";
            $default_time_zone = $this->getParameter('default_time_zone');

            $this->generateUsernameTypes();

            $localUserType = $this->getUsernameType($usernamePrefix);
            echo "localUserType=$localUserType <br>";
            if( !$localUserType ) {
                exit('$userkeytype is null');
            }

            $systemusers = $em->getRepository(User::class)->findBy(
                array(
                    'primaryPublicUserId' => 'system',
                    'keytype' => $localUserType->getId()
                )
            );
            if( count($systemusers) == 0 ) {
                $systemuser = new User();
                $systemuser->setKeytype($localUserType);
                $systemuser->setPrimaryPublicUserId('system');
                $systemuser->setUsername('system');
                $systemuser->setUsernameCanonical('system');
                $systemuser->setEmail($adminemail);
                $systemuser->setEmailCanonical($adminemail);
                $systemuser->setPassword("");
                $systemuser->setCreatedby('system');
                $systemuser->addRole('ROLE_SYSTEM_DEPUTY_ADMIN');
                $systemuser->getPreferences()->setTimezone($default_time_zone);
                $systemuser->setEnabled(false); //can not login
                $em->persist($systemuser);
                $em->flush();
            }


            $administrators = $em->getRepository(User::class)->findBy(
                array(
                    'primaryPublicUserId' => 'administrator',
                    'keytype' => $localUserType->getId()
                )
            );
            if( count($administrators) == 0 ) {
                $administrator = new User();
                $administrator->setKeytype($localUserType);
                $administrator->setPrimaryPublicUserId('administrator');
                $administrator->setUsername('system');
                $administrator->setUsernameCanonical('system');
                $administrator->setEmail($adminemail);
                $administrator->setEmailCanonical($adminemail);
                $administrator->setCreatedby('system');
                $administrator->addRole('ROLE_SYSTEM_DEPUTY_ADMIN');
                $administrator->getPreferences()->setTimezone($default_time_zone);
                $administrator->setEnabled(true);

                $authUtil = $this->container->get('authenticator_utility');
                $encodedPassword = $authUtil->getEncodedPassword($administrator, "1234567890");
                $administrator->setPassword($encodedPassword);
                $em->persist($administrator);
                $em->flush();
            }
        }

//        $params = $em->getRepository(SiteParameters::class)->findAll();
//        echo "system params=".count($params)."<br>";
//        if( count($params) == 0 ) {
//            $params = new SiteParameters();
//        } else {
//            $params = $siteParameters[0];
//        }
        //exit("initialUsers");
    }
    public function generateSiteParameters() {

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager('systemdb');

        $entities = $em->getRepository(SiteParameters::class)->findAll();

        if( count($entities) > 0 ) {
            $logger->notice("Exit generateSiteParameters: SiteParameters has been already generated.");
            return -1;
        }

        $logger->notice("SystemTenancyController: Start generating SiteParameters");

        //Use only params used in setparameters.php
        $types = array(
            "connectionChannel" => "http",

            "maxIdleTime" => "60",
            //"environment" => "dev",
            "siteEmail" => "", //"email@email.com",
            //"loginInstruction" => 'Please use your <a href="https://its.weill.cornell.edu/services/accounts-and-access/center-wide-id">CWID</a> to log in.',
            //"remoteAccessUrl" => "https://its.weill.cornell.edu/services/wifi-networks/remote-access",
            //"enableAutoAssignmentInstitutionalScope" => true,

            "smtpServerAddress" => "smtp.gmail.com",
            //"mailerPort" => "587",
            //"mailerTransport" => "smtp",
            //"mailerAuthMode" => "login",
            //"mailerUseSecureConnection" => "tls",
            //"mailerUser" => null,
            //"mailerPassword" => null,
            //"mailerSpool" => false,
            //"mailerFlushQueueFrequency" => 15, //minuts
            //"mailerDeliveryAddresses" => null,

            "institutionurl" => "http://www.cornell.edu/",
            "institutionname" => "Cornell University",
            "subinstitutionurl" => "http://weill.cornell.edu",
            "subinstitutionname" => "Weill Cornell Medicine",
            "departmenturl" => "http://www.cornellpathology.com",
            "departmentname" => "Pathology and Laboratory Medicine Department",
            "showCopyrightOnFooter" => true,

            "initialConfigurationCompleted" => false,

            "maintenance" => false,
            "maintenancelogoutmsg" =>   'The scheduled maintenance of this software has begun.'.
                ' The administrators are planning to return this site to a fully functional state on or before [[datetime]].',
            "maintenanceloginmsg" =>    'The scheduled maintenance of this software has begun.'.
                ' The administrators are planning to return this site to a fully functional state on or before [[datetime]].',

            //"externalMonitorUrl" => "https://view.med.cornell.edu/",
            //"monitorScript" => "python webmonitor.py -l 'https://view.med.cornell.edu/' -h 'smtp.med.cornell.edu' -s 'oli2002@med.cornell.edu' -r 'oli2002@med.cornell.edu'",

            //uploads
            "avataruploadpath" => "directory/avatars",
            "employeesuploadpath" => "directory/documents",
            "scanuploadpath" => "scan-order/documents",
            "fellappuploadpath" => "fellapp/documents",
            "resappuploadpath" => "resapp/documents",
            "vacrequploadpath" => "directory/vacreq",
            "transresuploadpath" => "transres/documents",
            "callloguploadpath" => "calllog/documents",
            "crnuploadpath" => "crn/documents",

            "mainHomeTitle" => "Welcome to the O R D E R platform!",
            "listManagerTitle" => "List Manager",
            "eventLogTitle" => "Event Log",
            "siteSettingsTitle" => "Site Settings",

            ////////////////////////// LDAP notice messages /////////////////////////
            "noticeAttemptingPasswordResetLDAP" => "The password for your [[CWID]] can only be changed or reset by visiting the enterprise password management page or by calling the help desk at ‭1 (212) 746-4878‬.",
            //"noticeUseCwidLogin" => "Please use your CWID to log in",
            "noticeSignUpNoCwid" => "Sign up for an account if you have no CWID",
            //"noticeHasLdapAccount" => 'Do you (the person for whom the account is being requested) have a <a href=\"https://its.weill.cornell.edu/services/accounts-and-access/center-wide-id\">CWID</a> username?',
            "noticeHasLdapAccount" => "Do you (the person for whom the account is being requested) have an existing institutional user name?",
            //"noticeLdapName" => "Active Directory (LDAP)",
            "noticeLdapName" => "Existing institutional user name in Active Directory or LDAP",
            ////////////////////////// EOF LDAP notice messages /////////////////////////
            ////////////////////// EOF Third-Party Software //////////////////
        );

        $siteParameters = $em->getRepository(SiteParameters::class)->findAll();

        if( count($siteParameters) == 0 ) {
            $params = new SiteParameters();
        } else {
            $params = $siteParameters[0];
        }

        $count = 0;
        foreach( $types as $key => $value ) {
            $method = "set".$key;
            $params->$method( $value );
            $count = $count + 10;
            $logger->notice("SystemTenancyController: generateSiteParameters setter: $method");
        }

        $em->persist($params);
        $em->flush();

        $this->createAuthServerNetworkList($params);

        $logger->notice("Finished System's generateSiteParameters: count=".$count/10);

        return round($count/10);
    }

    public function generateAuthServerNetworkList($systemuser) {
        $em = $this->getDoctrine()->getManager('systemdb');

        $types = array(
            "Intranet (Solo)",
            "Intranet (Tandem)",
            "Internet (Solo) ",
            "Internet (Tandem)",
            "Internet (Hub)"
        );

        $count = 10;
        foreach( $types as $name ) {

            $listEntity = $em->getRepository(AuthServerNetworkList::class)->findOneByName($name);
            if( $listEntity ) {
                continue;
            }

            $listEntity = new AuthServerNetworkList();
            $this->setDefaultList($listEntity,$count,$systemuser,$name);

            $em->persist($listEntity);
            $em->flush();

            $count = $count + 10;
        }

        return round($count/10);
    }
    public function createAuthServerNetworkList( $params ) {
        $em = $this->getDoctrine()->getManager('systemdb');

        $systemuser = null;
        $systemusers = $em->getRepository(User::class)->findBy(
            array(
                'primaryPublicUserId' => 'system',
                'keytype' => $localUserType->getId()
            )
        );
        if( count($systemusers) > 0 ) {
            $systemuser = $systemusers[0];
        }

        $this->generateAuthServerNetworkList($systemuser);

        $authServerNetwork = $em->getRepository(AuthServerNetworkList::class)->findOneByName("Internet (Hub)");
        $params->setAuthServerNetwork($authServerNetwork);
        $em->flush();

        //HostedGroupHolder
        $hostedGroupHolder = new HostedGroupHolder($systemuser);
        $hostedGroupHolder->setServerNetwork($authServerNetwork);

        //set:
//        private $databaseHost;
//        private $databasePort;
//        private $databaseName;
//        private $databaseUser;
//        private $databasePassword;
//        private $systemDb;
//        private $enabled;

        //set HostedUserGroupList
        $hostedUserGroup = new HostedUserGroupList($systemuser);
        $hostedGroupHolder->setHostedUserGroup($hostedUserGroup);


        $authServerNetwork->addHostedGroupHolder($hostedGroupHolder);
    }

    public function generateUsernameTypes($user=null) {
        $em = $this->getDoctrine()->getManager('systemdb');
        $userSecUtil = $this->container->get('user_security_utility');
        $user = null;
        $entities = $em->getRepository(UsernameType::class)->findAll();

        if( $entities ) {
            return -1;
        }

        $elements = array(
            'ldap-user' => 'Active Directory (LDAP)',
            'ldap2-user' => 'Active Directory (LDAP) 2',
            'external'=>'External Authentication',
            'local-user'=>'Local User'
        );

        $count = 1;
        foreach( $elements as $key=>$value ) {

            $entity = new UsernameType();
            //$userSecUtil->setDefaultList($entity,$count,$user,null);

            $entity->setOrderinlist( $count );
            $entity->setCreator( $user );
            $entity->setCreatedate( new \DateTime() );
            $entity->setType('user-added');

            $entity->setName( trim((string)$value) );
            $entity->setAbbreviation( trim((string)$key) );

            $user->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);
    }
    public function getUsernameType($abbreviation=null) {
        $em = $this->getDoctrine()->getManager('systemdb');
        $userkeytype = null;
        if( $abbreviation ) {
            $userkeytype = $em->getRepository(UsernameType::class)->findOneBy(
                array(
                    'type' => array('default', 'user-added'),
                    'abbreviation' => array($abbreviation)
                ),
                array('orderinlist' => 'ASC')
            );

            return $userkeytype;
        } else {
            $userkeytypes = $em->getRepository(UsernameType::class)->findBy(
                array('type' => array('default', 'user-added')),
                array('orderinlist' => 'ASC')
            );

            //echo "userkeytypes=".$userkeytypes."<br>";
            //print_r($userkeytypes);
            if( $userkeytypes && count($userkeytypes) > 0 ) {
                $userkeytype = $userkeytypes[0];
                return $userkeytype;
            }
        }
        return null;
    }

//    #[Route(path: '/manager/', name: 'system-manager')]
//    #[Route(path: '/manager/https', name: 'system-manager-https')]
//    public function systemManagerAction(Request $request)
//    {
//        exit("systemManagerAction");
//        $logger = $this->container->get('logger');
//        $em = $this->getDoctrine()->getManager();
//        $users = $roles = $em->getRepository(User::class)->findAll();
//        $logger->notice('firstTimeLoginGenerationAction: users='.count($users));
//
//        if (count($users) == 0) {
//
//            //1) get systemuser
//            //$userSecUtil = new UserSecurityUtil($em, null);
//            $userSecUtil = $this->container->get('user_security_utility');
//            $systemuser = $userSecUtil->findSystemUser();
//
//            //$this->generateSitenameList($systemuser);
//
//            if (!$systemuser) {
//
//                $logger->notice('Start generate system user');
//                $default_time_zone = null;
//                $usernamePrefix = "local-user";
//
//                //$userUtil = new UserUtil();
//                $userUtil = $this->container->get('user_utility');
//                $userUtil->generateUsernameTypes(null, false);
//                //$userkeytype = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findOneByAbbreviation("local-user");
//
//                $this->generateSitenameList(null);
//
//                $userSecUtil = $this->container->get('user_security_utility');
//                $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);
//
//                //echo "userkeytype=".$userkeytype."; ID=".$userkeytype->getId()."<br>";
//
//                $systemuser = $userUtil->createSystemUser($userkeytype,$default_time_zone);
//
//                //echo "0 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>";
//
//                //set unique username
//                //$usernameUnique = $systemuser->createUniqueUsername();
//                //$systemuser->setUsername($usernameUnique);
//                //$systemuser->setUsernameCanonical($usernameUnique);
//
//                //exit("1 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>");
//
//                //$systemuser->setUsername("system_@_local-user");
//                //$systemuser->setUsernameCanonical("system_@_local-user");
//
//                //$encoder = $this->container->get('security.password_encoder');
//                //$encoded = $encoder->encodePassword($systemuser, "systemuserpass");
//                $authUtil = $this->container->get('authenticator_utility');
//                $encoded = $authUtil->getEncodedPassword($systemuser,"systemuserpass");
//
//                $systemuser->setPassword($encoded);
//                $systemuser->setLocked(false);
//
//                $em->persist($systemuser);
//                $em->flush();
//
//                $logger->notice('Finished generate system user: '.$systemuser);
//                //exit("system user created");
//            }
//
//            $adminRes = $this->generateAdministratorAction(true);
//            $logger->notice('Finished generate AdministratorAction. adminRes='.$adminRes);
//            //exit($adminRes);
//
//            //TODO: $channel
//            //if( $channel && $channel == "https" ) {
//            if( $request->get('_route') == "first-time-login-generation-init-https" ) {
//                //set channel in SiteParameters to https
//                $entities = $em->getRepository(SiteParameters::class)->findAll();
//                if (count($entities) != 1) {
//                    $userServiceUtil = $this->container->get('user_service_utility');
//                    $userServiceUtil->generateSiteParameters();
//                    $entities = $em->getRepository(SiteParameters::class)->findAll();
//                }
//                if (count($entities) != 1) {
//                    exit('Must have only one parameter object. Found ' . count($entities) . ' object(s)');
//                    //throw new \Exception( 'Must have only one parameter object. Found '.count($entities).' object(s)' );
//                }
//                $entity = $entities[0];
//                $entity->setConnectionChannel("https");
//                //$em->flush($entity);
//                $em->flush();
//            }
//
//            $logger->notice('Start updateApplication');
//            $updateres = $this->updateApplication();
//
//            $adminRes = $adminRes . " <br> " .$updateres;
//
//            $logger->notice('Finished initialization. adminRes='.$adminRes);
//
//        } else {
//            //$adminRes = 'Admin user already exists';
//            //$adminRes = "System has been initialized successfully.";
//            $adminRes = 'Admin user has been successfully created.';
//            //exit('users already exists');
//            $logger->notice('Finished initialization. users already exists');
//        }
//
//
//        $this->addFlash(
//            'notice',
//            $adminRes
//        );
//
////        //make sure sitesettings is initialized
////        $siteParams = $em->getRepository(SiteParameters::class)->findAll();
////        if( count($siteParams) != 1 ) {
////            $userServiceUtil = $this->container->get('user_service_utility');
////            $userServiceUtil->generateSiteParameters();
////        }
//
//        return $this->redirect($this->generateUrl('employees_home'));
//    }
//    public function generateSitenameList($user=null) {
//
//        $em = $this->getDoctrine()->getManager();
//
//        $elements = $this->getSiteList();
//        $description = "System site to initialize the System DB and multinenatcy management";
//
//        $count = 10;
//        foreach( $elements as $name => $abbreviation ) {
//
//            $entity = $em->getRepository(SiteList::class)->findOneByName($name);
//            if( $entity ) {
//
//                if( !$entity->getDescription() ) {
//                    if( $description ) {
//                        $entity->setDescription($description);
//                        $em->flush();
//                    }
//                }
//
//                continue;
//            }
//
//            $entity = new SiteList();
//            $this->setDefaultList($entity,$count,$user,$name);
//
//            $entity->setAbbreviation($abbreviation);
//
//            if( isset($description) ) {
//                $entity->setDescription($description);
//            }
//
//            $em->persist($entity);
//            $em->flush();
//
//            $count = $count + 10;
//
//        } //foreach
//
//        return round($count/10);
//
//    }
//    public function getSiteList() {
//        $elements = array(
//            'system' => 'system',
//        );
//        return $elements;
//    }


//    /**
//     * run: http://localhost/order/directory/admin/first-time-login-generation-init/
//     * run: http://localhost/order/directory/admin/first-time-login-generation-init/https
//     */
//    //#[Route(path: '/first-time-login-generation-init/', name: 'first-time-login-generation-init')]
//    //#[Route(path: '/first-time-login-generation-init/https', name: 'first-time-login-generation-init-https')]
//    public function firstTimeLoginGenerationAction(Request $request)
//    {
//        $logger = $this->container->get('logger');
//        $em = $this->getDoctrine()->getManager();
//        $users = $roles = $em->getRepository(User::class)->findAll();
//        $logger->notice('firstTimeLoginGenerationAction: users='.count($users));
//
//        if (count($users) == 0) {
//
//            //1) get systemuser
//            //$userSecUtil = new UserSecurityUtil($em, null);
//            $userSecUtil = $this->container->get('user_security_utility');
//            $systemuser = $userSecUtil->findSystemUser();
//
//            //$this->generateSitenameList($systemuser);
//
//            if (!$systemuser) {
//
//                $logger->notice('Start generate system user');
//                $default_time_zone = null;
//                $usernamePrefix = "local-user";
//
//                //$userUtil = new UserUtil();
//                $userUtil = $this->container->get('user_utility');
//                $userUtil->generateUsernameTypes(null, false);
//                //$userkeytype = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findOneByAbbreviation("local-user");
//
//                $this->generateSitenameList(null);
//
//                $userSecUtil = $this->container->get('user_security_utility');
//                $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);
//
//                //echo "userkeytype=".$userkeytype."; ID=".$userkeytype->getId()."<br>";
//
//                $systemuser = $userUtil->createSystemUser($userkeytype,$default_time_zone);
//
//                //echo "0 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>";
//
//                //set unique username
//                //$usernameUnique = $systemuser->createUniqueUsername();
//                //$systemuser->setUsername($usernameUnique);
//                //$systemuser->setUsernameCanonical($usernameUnique);
//
//                //exit("1 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>");
//
//                //$systemuser->setUsername("system_@_local-user");
//                //$systemuser->setUsernameCanonical("system_@_local-user");
//
//                //$encoder = $this->container->get('security.password_encoder');
//                //$encoded = $encoder->encodePassword($systemuser, "systemuserpass");
//                $authUtil = $this->container->get('authenticator_utility');
//                $encoded = $authUtil->getEncodedPassword($systemuser,"systemuserpass");
//
//                $systemuser->setPassword($encoded);
//                $systemuser->setLocked(false);
//
//                $em->persist($systemuser);
//                $em->flush();
//
//                $logger->notice('Finished generate system user: '.$systemuser);
//                //exit("system user created");
//            }
//
//            $adminRes = $this->generateAdministratorAction(true);
//            $logger->notice('Finished generate AdministratorAction. adminRes='.$adminRes);
//            //exit($adminRes);
//
//            //TODO: $channel
//            //if( $channel && $channel == "https" ) {
//            if( $request->get('_route') == "first-time-login-generation-init-https" ) {
//                //set channel in SiteParameters to https
//                $entities = $em->getRepository(SiteParameters::class)->findAll();
//                if (count($entities) != 1) {
//                    $userServiceUtil = $this->container->get('user_service_utility');
//                    $userServiceUtil->generateSiteParameters();
//                    $entities = $em->getRepository(SiteParameters::class)->findAll();
//                }
//                if (count($entities) != 1) {
//                    exit('Must have only one parameter object. Found ' . count($entities) . ' object(s)');
//                    //throw new \Exception( 'Must have only one parameter object. Found '.count($entities).' object(s)' );
//                }
//                $entity = $entities[0];
//                $entity->setConnectionChannel("https");
//                //$em->flush($entity);
//                $em->flush();
//            }
//
//            $logger->notice('Start updateApplication');
//            $updateres = $this->updateApplication();
//
//            $adminRes = $adminRes . " <br> " .$updateres;
//
//            $logger->notice('Finished initialization. adminRes='.$adminRes);
//
//        } else {
//            //$adminRes = 'Admin user already exists';
//            //$adminRes = "System has been initialized successfully.";
//            $adminRes = 'Admin user has been successfully created.';
//            //exit('users already exists');
//            $logger->notice('Finished initialization. users already exists');
//        }
//
//
//        $this->addFlash(
//            'notice',
//            $adminRes
//        );
//
////        //make sure sitesettings is initialized
////        $siteParams = $em->getRepository(SiteParameters::class)->findAll();
////        if( count($siteParams) != 1 ) {
////            $userServiceUtil = $this->container->get('user_service_utility');
////            $userServiceUtil->generateSiteParameters();
////        }
//
//        return $this->redirect($this->generateUrl('system_special_home'));
//    }

}