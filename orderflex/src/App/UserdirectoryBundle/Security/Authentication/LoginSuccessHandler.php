<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/7/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Security\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

//use App\UserdirectoryBundle\Util\UserUtil;


class LoginSuccessHandler implements AuthenticationFailureHandlerInterface, AuthenticationSuccessHandlerInterface {

    protected $container;
    //protected $secTokenStorage;
    //protected $secAuth;
    protected $security;
    protected $em;
    protected $router;
    protected $siteName;
    protected $siteNameStr;
    protected $roleBanned;
    protected $roleUser;
    protected $roleUnapproved;
    protected $firewallName;

    public function __construct( ContainerInterface $container, EntityManagerInterface $em, Security $security )
    {
        $this->container = $container;
        $this->router = $container->get('router');
        //$this->secAuth = $container->get('security.authorization_checker');
        //$this->secTokenStorage = $container->get('security.token_storage');
        $this->security = $security;
        $this->em = $em;

        $this->siteName = $container->getParameter('employees.sitename');
        $this->siteNameStr = 'Employee Directory';
        $this->roleBanned = 'ROLE_USERDIRECTORY_BANNED';
        $this->roleUser = 'ROLE_USERDIRECTORY_OBSERVER';
        $this->roleUnapproved = 'ROLE_USERDIRECTORY_UNAPPROVED';
        $this->firewallName = 'ldap_employees_firewall';
    }

    public function getFirewallName() {
        return $this->firewallName;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) : Response
    {
        //exit('onAuthenticationSuccess');
        //testing
        //return new RedirectResponse($this->router->generate('employees_initial_configuration'));

        $response = null;

        $user = $token->getUser();

        if( $user && $user instanceof UserInterface ) {
            $username = $user->getUserIdentifier(); //getUsername();
        } else {
            $username = $user."";
        }
        //$username = $user."";
        //exit('$username='.$username);

        $options = array();
        //$em = $this->em;
        //$userUtil = new UserUtil();
        $secUtil = $this->container->get('user_security_utility');

        //I should be redirected to the URL I was trying to visit after login.
        $indexLastRoute = '_security.'.$this->firewallName.'.target_path';
        $lastRoute = $request->getSession()->get($indexLastRoute);
        //echo "lastRoute=".$lastRoute."<br>";
        //exit('111');

        $options['sitename'] = $this->siteName;

        //echo "userdirectory: employees authentication success: Success. User=".$user.", setCreatedby=".$user->getCreatedby()."<br>";
        //exit('111');

        //echo "roleBanned=".$this->roleBanned."<br>";
        //echo "siteName=".$this->siteName."<br>";

        $session = $request->getSession();

        //Set session locale on login
        //$session->set('create-custom-db', null);
        //$session->remove('create-custom-db');

        //$locale = $request->getLocale();
        //$session->set('locale',$locale);
        //echo "locale=".$locale."<br>";
        //exit('111');
        //$this->setConnectionParameters($session,$locale);

        //$res = UserUtil::getMaxIdleTimeAndMaintenance($em,$this->security,$this->container);
        $res = $secUtil->getMaxIdleTimeAndMaintenance();

        //check for maintenance
        $maintenance = $res['maintenance'];
        if( $maintenance ) {
            return new RedirectResponse( $this->router->generate('main_maintenance') );
        }

        ////////////////// set session variables //////////////////
        //set max idle time maxIdleTime
        $maxIdleTime = $res['maxIdleTime'];
        $session->set('maxIdleTime',$maxIdleTime);
        
        //set site email
        //$siteEmail = UserUtil::getSiteSetting($em,'siteEmail');
        $siteEmail = $secUtil->getSiteSettingParameter('siteEmail');
        $session->set('siteEmail',$siteEmail);

        //set original site name
        $session->set('sitename',$this->siteName);

        $session->set('logintype','normal');
        $logintype = $session->get('logintype');
        $logger = $this->container->get('logger');
        $logger->notice("onAuthenticationSuccess: logintype=".$logintype);
        ///////////////// EOF set session variables /////////////////

        if( $this->security->isGranted($this->roleBanned) ) {
            $options['eventtype'] = 'Banned User Login Attempt';
            $options['event'] = 'Banned user login attempt to '.$this->siteNameStr.' site. Username='.$username;
//            UserUtil::setLoginAttempt($request,$this->security,$em,$options);
            $secUtil->setLoginAttempt($request,$options);
            //exit('banned user');
            return new RedirectResponse( $this->router->generate($this->siteName.'_access_request_new') );
        }

        //detect if the user was first time logged in by ldap: assign role UNAPPROVED user
        //all users must have at least an OBSERVER role
//        if( !$this->security->isGranted($this->roleUser)  ) {
//            //echo "assign role UNAPPROVED user <br>";
//            //exit('UNAPPROVED user');
//            $user->addRole($this->roleUnapproved);
//        }
        $this->checkBasicRole($user,$lastRoute);
        //echo "lastRoute=".$lastRoute."<br>";exit();

        if( $this->security->isGranted($this->roleUnapproved) ) {
            $options['eventtype'] = 'Unapproved User Login Attempt';
            $options['event'] = 'Unapproved user login attempt with role '.$this->roleUnapproved.' to '.$this->siteNameStr.' site. Username='.$username;
            //UserUtil::setLoginAttempt($request,$this->security,$em,$options);
            $secUtil->setLoginAttempt($request,$options);
            //exit('Unapproved user');
            return new RedirectResponse( $this->router->generate($this->siteName.'_access_request_new') );
        }

        $user->setLastLogin(new \DateTime());
        $user->setLastActivity(new \DateTime());
        $user->setLastLoggedUrl($lastRoute);

        //exit('user ok');
        $options['eventtype'] = "Successful Login";
        $options['event'] = 'Successful login to '.$this->siteNameStr.' site. Username='.$username;

        //UserUtil::setLoginAttempt($request,$this->security,$em,$options);
        $secUtil->setLoginAttempt($request,$options);

        //Initial Configuration Completed
        $userSecUtil = $this->container->get('user_security_utility');
        $initialConfigurationCompleted = $userSecUtil->getSiteSettingParameter('initialConfigurationCompleted');
        if( !$initialConfigurationCompleted ) {
            if( strtolower($user->getPrimaryPublicUserId()) == "administrator" ) {
                return new RedirectResponse($this->router->generate('employees_initial_configuration'));
            }
        }

        //Issue #381: redirect non-processor users to the previously requested page before authentication

        //$response = new RedirectResponse($this->router->generate($this->siteName.'_home'));
        //return $response;

        $loginpos = strpos((string)$lastRoute, '/login');
        $nopermpos = strpos((string)$lastRoute, '/no-permission');
        $nocheck = strpos((string)$lastRoute, '/check/');
        $keepalive = strpos((string)$lastRoute, '/keepalive');
        $idlelogout = strpos((string)$lastRoute, '/idle-log-out');
        $common = strpos((string)$lastRoute, '/common/');
        //$newproject = strpos((string)$lastRoute, '/project/new/');

        $filedownload = strpos((string)$lastRoute, '/file-download');
        if( $filedownload ) {
            $lastRouteArr = explode("/", $lastRoute);
            $fileid = $lastRouteArr[count($lastRouteArr)-1];
            $referer_url = $this->router->generate($this->siteName.'_thankfordownloading',array('id'=>$fileid,'sitename'=>$this->siteName));
            $response = new RedirectResponse($referer_url);
            //exit('thankfordownloading');
            return $response;
        }

        //echo "keepalive=".$keepalive."<br>";
        //echo "lastRoute=".$lastRoute."<br>";exit();

        if( 
            $lastRoute && $lastRoute != '' && 
            $loginpos === false && $nopermpos === false && 
            $nocheck === false && $keepalive === false && 
            $idlelogout === false && $common === false
        ) {
            $referer_url = $lastRoute;
        } else {
            $referer_url = $this->router->generate($this->siteName.'_home');
//            echo "onAuthenticationSuccess tenantprefix=".$request->get('tenantprefix')."<br>";
//            $parameters = array(
//                'tenantprefix' => $request->get('tenantprefix')
//            );
//            $referer_url = $this->router->generate($this->siteName.'_home',$parameters);
        }

        //echo("referer_url=".$referer_url);
        //exit();

        //Add redirect o verify page if "Only allow log in if the primary mobile number is verified and ask to verify" is yes
        $userSecUtil = $this->container->get('user_security_utility');
        if( $userSecUtil->isRequireMobilePhoneToLogin($this->siteName) ) {
            $userInfo = $user->getUserInfo();
            $mobilePhoneVerified = $userInfo->getPreferredMobilePhoneVerified();
            $phoneNumber = $userInfo->getPreferredMobilePhone();
            //if( $phoneNumber && !$mobilePhoneVerified ) {
            if( !$mobilePhoneVerified ) {
                $session->set('originalRouteOnLogin',$lastRoute);
                $verify_url = $this->router->generate('employees_verify_mobile_phone', array('siteName'=>$this->siteName,'phoneNumber'=>$phoneNumber));
                $response = new RedirectResponse($verify_url);
                return $response;
            }
        }

        $response = new RedirectResponse($referer_url);

        ///////////// set cookies /////////////
        //$cookieKeytype = $request->cookies->get('userOrderSuccessCookiesKeytype');
        //if( !$cookieKeytype ) {
        //    $response->headers->setCookie(new Cookie('userOrderSuccessCookiesKeytype', $user->getKeytype().""));
        //}
        //$response->headers->setCookie($cookie);
        $lifetime = time() + (86400 * 30); //in seconds. 86400 => 1 day. 86400 * 30 => 1 month
        $response->headers->setCookie(new Cookie('userOrderSuccessCookiesKeytype', $user->getKeytype()."", $lifetime));
        $response->headers->setCookie(new Cookie('userOrderSuccessCookiesUsername', $user->getPrimaryPublicUserId(), $lifetime));
        ///////////// EOF set cookies /////////////

        return $response;

    }

    public function checkBasicRole($user,$targetUrl=null) : void
    {
        if( !$this->security->isGranted($this->roleUser)  ) {
            //echo "assign role UNAPPROVED user <br>";
            //exit('UNAPPROVED user');
            $user->addRole($this->roleUnapproved);
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) : Response
    {
        //error_log('You are out!');
        //echo "user is not ok!. Exception=<br>".$exception."<br>";
        //exit("user is not ok!");
        //throw new \Exception( 'user is not ok!' );

        $secUtil = $this->container->get('user_security_utility');

        $options = array();
        //$em = $this->em;
        //$userUtil = new UserUtil();

        $options['sitename'] = $this->siteName;
        $options['eventtype'] = "Bad Credentials";
        $options['event'] = 'Bad credentials provided on login for '.$this->siteNameStr.' site';
        $options['serverresponse'] = $exception->getMessage();

        //testing
        //UserUtil::setLoginAttempt($request,$this->security,$em,$options);
        $secUtil->setLoginAttempt($request,$options);

        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        //$request->getSession()->set('locale',null);
        //$session->remove('locale');
        //$request->getSession()->set('create-custom-db', null);
        //$request->getSession()->remove('create-custom-db');
        //$this->clearConnectionParameters($request->getSession());

        $response = new RedirectResponse( $this->router->generate($this->siteName.'_login') );
        return $response;

    }

//    //NOT USED
//    public function setConnectionParameters( $session, $locale ) {
//        $session->set('locale',$locale);
//
////        $params = getConnectionParams($locale);
////        $params['driver'] = $this->container->getParameter('database_driver');
////        $params['host'] = $this->container->getParameter($urlSlug.'-databaseHost');
////        $params['port'] = $this->container->getParameter($urlSlug.'-databasePort');
////        $params['dbname'] = $this->container->getParameter($urlSlug.'-databaseName');
////        $params['user'] = $this->container->getParameter($urlSlug.'-databaseUser');
////        $params['password'] = $this->container->getParameter($urlSlug.'-databasePassword');
//
////        $session->set('driver',$params['driver']);
////        $session->set('host',$params['host']);
////        $session->set('port',$params['port']);
////        $session->set('dbname',$params['dbname']);
////        $session->set('user',$params['user']);
////        $session->set('password',$params['password']);
//    }
//    //NOT USED
//    public function clearConnectionParameters( $session ) {
//        $session->set('locale',null);
////        $session->set('driver',null);
////        $session->set('host',null);
////        $session->set('port',null);
////        $session->set('dbname',null);
////        $session->set('user',null);
////        $session->set('password',null);
//    }

}