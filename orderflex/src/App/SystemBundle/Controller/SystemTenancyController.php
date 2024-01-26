<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/25/2024
 * Time: 12:35 PM
 */

namespace App\SystemBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\SiteParameters;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Attribute\Template;


class SystemTenancyController extends OrderAbstractController
{

    // /system/
    //#[Route(path: '/', name: 'system-home')]
    //#[Template('AppSystemBundle/home/system-home.html.twig')]
    public function systemHomeAction(Request $request)
    {
        //exit("systemHomeAction");
        $em = $this->getDoctrine()->getManager('systemdb');
        $users = $em->getRepository(User::class)->findAll();
        echo "users=".count($users)."<br>";
        //exit("111");
        if (count($users) == 0) {
            $this->initialUsers();
        }

        $params = $em->getRepository(SiteParameters::class)->findAll();
        echo "system params=".count($params)."<br>";
        if( count($params) == 0 ) {
            $params = new SiteParameters();
        } else {
            $params = $siteParameters[0];
        }

        $title = "System";
        $msg = "Administrator user has been created";

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
            $adminemail = "email@example.com";
            $default_time_zone = $this->getParameter('default_time_zone');

            $localUserType = $userSecUtil->getUsernameType($usernamePrefix);
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
                //$em->persist($systemuser);
                //$em->flush();
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
                //$em->persist($administrator);
                //$em->flush();
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

    #[Route(path: '/manager/', name: 'system-manager')]
    #[Route(path: '/manager/https', name: 'system-manager-https')]
    public function systemManagerAction(Request $request)
    {
        exit("systemManagerAction");
        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $users = $roles = $em->getRepository(User::class)->findAll();
        $logger->notice('firstTimeLoginGenerationAction: users='.count($users));

        if (count($users) == 0) {

            //1) get systemuser
            //$userSecUtil = new UserSecurityUtil($em, null);
            $userSecUtil = $this->container->get('user_security_utility');
            $systemuser = $userSecUtil->findSystemUser();

            //$this->generateSitenameList($systemuser);

            if (!$systemuser) {

                $logger->notice('Start generate system user');
                $default_time_zone = null;
                $usernamePrefix = "local-user";

                //$userUtil = new UserUtil();
                $userUtil = $this->container->get('user_utility');
                $userUtil->generateUsernameTypes(null, false);
                //$userkeytype = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findOneByAbbreviation("local-user");

                $this->generateSitenameList(null);

                $userSecUtil = $this->container->get('user_security_utility');
                $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

                //echo "userkeytype=".$userkeytype."; ID=".$userkeytype->getId()."<br>";

                $systemuser = $userUtil->createSystemUser($userkeytype,$default_time_zone);

                //echo "0 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>";

                //set unique username
                //$usernameUnique = $systemuser->createUniqueUsername();
                //$systemuser->setUsername($usernameUnique);
                //$systemuser->setUsernameCanonical($usernameUnique);

                //exit("1 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>");

                //$systemuser->setUsername("system_@_local-user");
                //$systemuser->setUsernameCanonical("system_@_local-user");

                //$encoder = $this->container->get('security.password_encoder');
                //$encoded = $encoder->encodePassword($systemuser, "systemuserpass");
                $authUtil = $this->container->get('authenticator_utility');
                $encoded = $authUtil->getEncodedPassword($systemuser,"systemuserpass");

                $systemuser->setPassword($encoded);
                $systemuser->setLocked(false);

                $em->persist($systemuser);
                $em->flush();

                $logger->notice('Finished generate system user: '.$systemuser);
                //exit("system user created");
            }

            $adminRes = $this->generateAdministratorAction(true);
            $logger->notice('Finished generate AdministratorAction. adminRes='.$adminRes);
            //exit($adminRes);

            //TODO: $channel
            //if( $channel && $channel == "https" ) {
            if( $request->get('_route') == "first-time-login-generation-init-https" ) {
                //set channel in SiteParameters to https
                $entities = $em->getRepository(SiteParameters::class)->findAll();
                if (count($entities) != 1) {
                    $userServiceUtil = $this->container->get('user_service_utility');
                    $userServiceUtil->generateSiteParameters();
                    $entities = $em->getRepository(SiteParameters::class)->findAll();
                }
                if (count($entities) != 1) {
                    exit('Must have only one parameter object. Found ' . count($entities) . ' object(s)');
                    //throw new \Exception( 'Must have only one parameter object. Found '.count($entities).' object(s)' );
                }
                $entity = $entities[0];
                $entity->setConnectionChannel("https");
                //$em->flush($entity);
                $em->flush();
            }

            $logger->notice('Start updateApplication');
            $updateres = $this->updateApplication();

            $adminRes = $adminRes . " <br> " .$updateres;

            $logger->notice('Finished initialization. adminRes='.$adminRes);

        } else {
            //$adminRes = 'Admin user already exists';
            //$adminRes = "System has been initialized successfully.";
            $adminRes = 'Admin user has been successfully created.';
            //exit('users already exists');
            $logger->notice('Finished initialization. users already exists');
        }


        $this->addFlash(
            'notice',
            $adminRes
        );

//        //make sure sitesettings is initialized
//        $siteParams = $em->getRepository(SiteParameters::class)->findAll();
//        if( count($siteParams) != 1 ) {
//            $userServiceUtil = $this->container->get('user_service_utility');
//            $userServiceUtil->generateSiteParameters();
//        }

        return $this->redirect($this->generateUrl('employees_home'));
    }
    public function generateSitenameList($user=null) {

        $em = $this->getDoctrine()->getManager();

        $elements = $this->getSiteList();
        $description = "System site to initialize the System DB and multinenatcy management";

        $count = 10;
        foreach( $elements as $name => $abbreviation ) {

            $entity = $em->getRepository(SiteList::class)->findOneByName($name);
            if( $entity ) {

                if( !$entity->getDescription() ) {
                    if( $description ) {
                        $entity->setDescription($description);
                        $em->flush();
                    }
                }

                continue;
            }

            $entity = new SiteList();
            $this->setDefaultList($entity,$count,$user,$name);

            $entity->setAbbreviation($abbreviation);

            if( isset($description) ) {
                $entity->setDescription($description);
            }

            $em->persist($entity);
            $em->flush();

            $count = $count + 10;

        } //foreach

        return round($count/10);

    }
    public function getSiteList() {
        $elements = array(
            'system' => 'system',
        );
        return $elements;
    }


    /**
     * run: http://localhost/order/directory/admin/first-time-login-generation-init/
     * run: http://localhost/order/directory/admin/first-time-login-generation-init/https
     */
    //#[Route(path: '/first-time-login-generation-init/', name: 'first-time-login-generation-init')]
    //#[Route(path: '/first-time-login-generation-init/https', name: 'first-time-login-generation-init-https')]
    public function firstTimeLoginGenerationAction(Request $request)
    {
        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();
        $users = $roles = $em->getRepository(User::class)->findAll();
        $logger->notice('firstTimeLoginGenerationAction: users='.count($users));

        if (count($users) == 0) {

            //1) get systemuser
            //$userSecUtil = new UserSecurityUtil($em, null);
            $userSecUtil = $this->container->get('user_security_utility');
            $systemuser = $userSecUtil->findSystemUser();

            //$this->generateSitenameList($systemuser);

            if (!$systemuser) {

                $logger->notice('Start generate system user');
                $default_time_zone = null;
                $usernamePrefix = "local-user";

                //$userUtil = new UserUtil();
                $userUtil = $this->container->get('user_utility');
                $userUtil->generateUsernameTypes(null, false);
                //$userkeytype = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findOneByAbbreviation("local-user");

                $this->generateSitenameList(null);

                $userSecUtil = $this->container->get('user_security_utility');
                $userkeytype = $userSecUtil->getUsernameType($usernamePrefix);

                //echo "userkeytype=".$userkeytype."; ID=".$userkeytype->getId()."<br>";

                $systemuser = $userUtil->createSystemUser($userkeytype,$default_time_zone);

                //echo "0 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>";

                //set unique username
                //$usernameUnique = $systemuser->createUniqueUsername();
                //$systemuser->setUsername($usernameUnique);
                //$systemuser->setUsernameCanonical($usernameUnique);

                //exit("1 systemuser=".$systemuser."; username=".$systemuser->getUsername()."; ID=".$systemuser->getId()."<br>");

                //$systemuser->setUsername("system_@_local-user");
                //$systemuser->setUsernameCanonical("system_@_local-user");

                //$encoder = $this->container->get('security.password_encoder');
                //$encoded = $encoder->encodePassword($systemuser, "systemuserpass");
                $authUtil = $this->container->get('authenticator_utility');
                $encoded = $authUtil->getEncodedPassword($systemuser,"systemuserpass");

                $systemuser->setPassword($encoded);
                $systemuser->setLocked(false);

                $em->persist($systemuser);
                $em->flush();

                $logger->notice('Finished generate system user: '.$systemuser);
                //exit("system user created");
            }

            $adminRes = $this->generateAdministratorAction(true);
            $logger->notice('Finished generate AdministratorAction. adminRes='.$adminRes);
            //exit($adminRes);

            //TODO: $channel
            //if( $channel && $channel == "https" ) {
            if( $request->get('_route') == "first-time-login-generation-init-https" ) {
                //set channel in SiteParameters to https
                $entities = $em->getRepository(SiteParameters::class)->findAll();
                if (count($entities) != 1) {
                    $userServiceUtil = $this->container->get('user_service_utility');
                    $userServiceUtil->generateSiteParameters();
                    $entities = $em->getRepository(SiteParameters::class)->findAll();
                }
                if (count($entities) != 1) {
                    exit('Must have only one parameter object. Found ' . count($entities) . ' object(s)');
                    //throw new \Exception( 'Must have only one parameter object. Found '.count($entities).' object(s)' );
                }
                $entity = $entities[0];
                $entity->setConnectionChannel("https");
                //$em->flush($entity);
                $em->flush();
            }

            $logger->notice('Start updateApplication');
            $updateres = $this->updateApplication();

            $adminRes = $adminRes . " <br> " .$updateres;

            $logger->notice('Finished initialization. adminRes='.$adminRes);

        } else {
            //$adminRes = 'Admin user already exists';
            //$adminRes = "System has been initialized successfully.";
            $adminRes = 'Admin user has been successfully created.';
            //exit('users already exists');
            $logger->notice('Finished initialization. users already exists');
        }


        $this->addFlash(
            'notice',
            $adminRes
        );

//        //make sure sitesettings is initialized
//        $siteParams = $em->getRepository(SiteParameters::class)->findAll();
//        if( count($siteParams) != 1 ) {
//            $userServiceUtil = $this->container->get('user_service_utility');
//            $userServiceUtil->generateSiteParameters();
//        }

        return $this->redirect($this->generateUrl('system_special_home'));
    }

}