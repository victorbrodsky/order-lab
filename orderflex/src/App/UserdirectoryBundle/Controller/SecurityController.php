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

namespace App\UserdirectoryBundle\Controller;



use App\Saml\Entity\SamlConfig;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Entity\UsernameType; //process.py script: replaced namespace by ::class: added use line for classname=UsernameType

//use App\UserdirectoryBundle\Security\Authentication\AuthUtil;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\SiteList;
use App\UserdirectoryBundle\Repository\UserRepository;
use App\UserdirectoryBundle\Security\Authentication\CustomUsernamePasswordToken;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
//use Symfony\Bundle\SecurityBundle\Security;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpFoundation\Session\Session;
//use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends OrderAbstractController
{

    //https://stackoverflow.com/questions/76895321/enable-symfony-6-3-login-via-username-and-via-email-at-the-same-time

    //Add 2 cases:
    //1) SAML login type => user enters the user name => user exists => user domain has SAML => redirect to SAML
    //2) user enters the user email BEFORE ever changing authentication to SAML/SSO
    // => user exists => user domain has SAML => redirect to SAML
    #[Route(path: '/directory/user_check_custom', name: 'employees_user_check_custom', methods: ["POST"], options: ['expose' => true])]
    public function userCheckCustomAction( Request $request )
    {
        $em = $this->getDoctrine()->getManager();

        //dump($request);
        $username = $request->get('_username');
        //$sitename = $request->get('_sitename');
        //$lastroute = $request->get('_lastroute');

        //echo "username=".$username.', sitename='.$sitename.', lastroute='.$lastroute."<br>";

        //exit("my login check!");

        $user = NULL;
        $userEmail = NULL;
        $useSaml = false;

        if( !$useSaml ) {
            //1) check user by username as user->'primarypublicuserid' (i.e. johndoe)
            $users = $em->getRepository(User::class)->findBy(array('primaryPublicUserId' => $username));
            if (count($users) > 0) {
                $user = $users[0];
            }
            if ($user) {
                $userEmail = $user->getSingleEmail();
                //exit($user->getId().": userEmail=".$userEmail);
                if( $userEmail ) {
                    //get CWID from email. Only proceed if CWID is the same as $username to prevent
                    //situations when username 'testadmin' has oli2002@med.cornell.edu email
                    $cwid = $this->getCwidFromEmail($userEmail);
                    if( $cwid == $username ) {
                        //check if domain has SAML config
                        //$samlConfigProviderUtil = $this->container->get('saml_config_provider_util');
                        //$config = $samlConfigProviderUtil->getConfig($userEmail);
                        $config = $em->getRepository(SamlConfig::class)->findByClient($userEmail);
                        if ($config) {
                            $useSaml = true;
                        }
                    }
                }
            }
        }

        if( !$useSaml ) {
            //2) check user by username as email: user->userinfo->'emailcanonical' (i.e. johndoe@yahoo.com)
            //Warning we might have multiple users with the same email, therefore, findOneUserByUserInfoUseridEmail is safer
            //$user = $em->getRepository(User::class)->findOneUserByEmail($username);
            $user = $em->getRepository(User::class)->findOneUserByUserInfoUseridEmail($username);
            if ($user) {
                $userEmail = $user->getSingleEmail();
                //exit($user->getId().": userEmail=".$userEmail);
                if ($userEmail) {
                    $cwid1 = $this->getCwidFromEmail($userEmail);
                    $cwid2 = $this->getCwidFromEmail($username);
                    if( $cwid1 == $cwid2 ) {
                        //check if domain has SAML config
                        $config = $em->getRepository(SamlConfig::class)->findByClient($userEmail);
                        if ($config) {
                            $useSaml = true;
                        }
                    }
                }
            }
        }

        $output = array();
        $output['usesaml'] = $useSaml;
        $output['useremail'] = $userEmail;

        $response = new Response();
        $response->setContent(json_encode($output));
        return $response;
    }
    public function getCwidFromEmail( $userEmail ) {
        $cwid = NULL;
        $domainArr = explode('@', $userEmail);
        if( count($domainArr) > 0 ) {
            $cwid = $domainArr[0];
        }
        return $cwid;
    }

//    //[Template("AppUserdirectoryBundle/Security/login.html.twig")]
//    #[Route(path: '/directory/login_check_custom', name: 'employees_login_check_custom', methods: ["GET","POST"], options: ['expose' => true])]
//    public function loginCheckCustomAction( Request $request )
//    {
//
//        //dump($request);
//        //$username = $request->get('_username');
//        //$password = $request->get('_password');
//
//        $username = $request->query->get('_username');
//        $password = $request->query->get('_password');
//        $sitename = $request->query->get('_sitename');
//        $lastroute = $request->query->get('_lastroute');
//
//        echo "username=".$username.", password=".$password.', sitename='.$sitename.', lastroute='.$lastroute."<br>";
//
//        exit("my login check!");
//    }

//    /**
//     * @Route("/login_check", name="login_check")
//     * @Route("/directory/login_check", name="employees_dummy_login_check")
//     * @Route("/call-log-book/login_check", name="calllog_dummy_login_check")
//     */
//    public function loginCheckAction( Request $request )
//    {
//        //Reroute to home page
//        exit("Reroute to home page");
//        return $this->redirect( $this->generateUrl('main_common_home') );
//    }

    #[Route(path: '/directory/login', name: 'directory_login')]
    #[Route(path: '/login', name: 'employees_login')]
    public function loginAction( Request $request, AuthenticationUtils $authenticationUtils ) {
        //exit('user: loginAction');
        $userSecUtil = $this->container->get('user_security_utility');

        $routename = $request->get('_route');
        //echo "routename=".$routename."<br>";

        //default
        $sitename = $this->getParameter('employees.sitename');

        if( $routename == "employees_login" ) {
            $sitename = $this->getParameter('employees.sitename');
        }
        if( $routename == "fellapp_login" ) {
            $sitename = $this->getParameter('fellapp.sitename');
        }
        if( $routename == "resapp_login" ) {
            $sitename = $this->getParameter('resapp.sitename');
        }
        if( $routename == "deidentifier_login" ) {
            $sitename = $this->getParameter('deidentifier.sitename');
        }
        if( $routename == "scan_login" ) {
            $sitename = $this->getParameter('scan.sitename');
        }
        if( $routename == "vacreq_login" ) {
            $sitename = $this->getParameter('vacreq.sitename');
        }
        if( $routename == "calllog_login" ) {
            $sitename = $this->getParameter('calllog.sitename');
        }
        if( $routename == "crn_login" ) {
            $sitename = $this->getParameter('crn.sitename');
        }
        if( $routename == "translationalresearch_login" ) {
            $sitename = $this->getParameter('translationalresearch.sitename');
        }
        if( $routename == "dashboard_login" ) {
            $sitename = $this->getParameter('dashboard.sitename');
        }
        //exit('sitename='.$sitename);

        /////////////// set browser info ///////////////
        //$request = $this->container->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();
        $userServiceUtil = $this->container->get('user_service_utility');
        $browserInfo = $userServiceUtil->browserCheck();
        $session->set('browserWarningInfo', $browserInfo);
        /////////////// EOF set browser info ///////////////

        //$sitename = $this->getParameter('employees.sitename');
        $formArr = $this->loginPage($request,$sitename,$authenticationUtils);

        if( $formArr == null ) {
            //exit('111');
            return $this->redirect( $this->generateUrl('main_common_home') );
            //return $this->redirect( $this->generateUrl($sitename.'_home') );
        }

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:UsernameType'] by [UsernameType::class]
        $usernametypes = $em->getRepository(UsernameType::class)->findBy(
            array(
                'type' => array('default', 'user-added'),
                //'abbreviation' => array('ldap-user','local-user')
            ),
            array('orderinlist' => 'ASC')
        );

        if( count($usernametypes) == 0 ) {
            $usernametypes = array();
            //$option = array('abbreviation'=>'ldap-user', 'name'=>'WCM CWID');
            //$usernametypes[] = $option;
            $option_localuser = array('abbreviation'=>'local-user', 'name'=>'Local User');
            $usernametypes[] = $option_localuser;
        }

        $formArr['usernametypes'] = $usernametypes;

        ///////////// read cookies /////////////
        $formArr['user_type'] = null;
        $cookieKeytype = $request->cookies->get('userOrderSuccessCookiesKeytype');
        if( $cookieKeytype ) {
            $formArr['user_type'] = $cookieKeytype;
            //echo "cookieKeytype=".$cookieKeytype."<br>";
        } else {
            //set default
            $defaultPrimaryPublicUserIdType = $userSecUtil->getSiteSettingParameter('defaultPrimaryPublicUserIdType');
            if( $defaultPrimaryPublicUserIdType && is_object($defaultPrimaryPublicUserIdType) ) {
                $formArr['user_type'] = $defaultPrimaryPublicUserIdType->getName();
            }
        }

        $cookieUsername = $request->cookies->get('userOrderSuccessCookiesUsername');
        if( $cookieUsername ) {
            $formArr['last_username'] = $cookieUsername;
            //echo "cookieUsername=".$cookieUsername."<br>";
        }
        ///////////// EOF read cookies /////////////

        //check if SAML enabled
        $samlenabled = 0;
        //get enabled configs. Alternatively, we can check if SAML login type enabled (UsernameType has name 'SAML/SSO').
        //$config = $em->getRepository(SamlConfig::class)->findAnyEnabledOne();
        //if( $config ) {
        //    $samlenabled = 1;
        //}
        //Alternatively, check if SAML login type enabled (UsernameType has name 'SAML/SSO')
        $samlKeytypes = $em->getRepository(UsernameType::class)->findBy(
            array('type' => array('default','user-added'), 'name' => 'SAML/SSO'),
            array('orderinlist' => 'ASC')
        );
        if( count($samlKeytypes) > 0 ) {
            $samlenabled = 1;
        }
        $formArr['samlenabled'] = $samlenabled;
        //echo "samlenabled=$samlenabled <br>"; //dev
        
        //not live warning
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        //echo "environment=$environment <br>"; //dev
        $formArr['environment'] = $environment;
        $formArr['inputStyle'] = "";
        if( $environment != 'live' ) {
            $request->getSession()->getFlashBag()->add(
                'pnotify-error',
                "THIS IS A TEST SERVER. USE ONLY FOR TESTING !!!"
            );
            $formArr['inputStyle'] = "background-color:#FF5050;";
        }

//        $request->getSession()->getFlashBag()->add(
//            'notice',
//            "Test message"
//        );

        return $this->render(
            'AppUserdirectoryBundle/Security/login.html.twig',
            $formArr
        );

    }

//    #[Route(path: '/single/login', name: 'base_single_login')]
//    public function loginSingleAction( Request $request, AuthenticationUtils $authenticationUtils ) {
//        //exit('user: employees_single_login');
//        $uri = $request->getUri();
//        echo "uri=".$uri."<br>";
//
//        $referer = $request->headers->get('referer');
//        echo "referer=".$referer."<br>";
//
//        exit('111');
//    }

    public function loginPage($request,$sitename,$authenticationUtils) {

        if(
            $this->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return null;
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $em = $this->getDoctrine()->getManager();

        //$helper = $this->container->get('security.authentication_utils');
        //$authenticationUtils = $this->container->get('security.authentication_utils');

        //Symfony < 2.6 deprecated methods
        //$request = $this->container->get('request_stack')->getCurrentRequest();
        //$session = $request->getSession();

        // get the login error if there is one
//        if( $request->attributes->has(Security::AUTHENTICATION_ERROR) ) {
//            $error = $request->attributes->get(
//                Security::AUTHENTICATION_ERROR
//            );
//        } else {
//            $error = $session->get(Security::AUTHENTICATION_ERROR);
//            $session->remove(Security::AUTHENTICATION_ERROR);
//        }

        //get error
        //$error = $helper->getLastAuthenticationError();
        $error = $authenticationUtils->getLastAuthenticationError();

        //get original username entered by a user in login form
        //$lastUsername = $helper->getLastUsername();
        $lastUsername = $authenticationUtils->getLastUsername();

        $lastUsernameArr = explode("_@_", $lastUsername);
        $lastUsername = $lastUsernameArr[0];

        $logoPath = null;
        $siteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
        if( $siteObject ) {
            $logos = $siteObject->getDocuments();
            if( count($logos) > 0 ) {
                $logo = $logos->first();
                //$packingSlipLogoFileName = $transresRequestUtil->getDefaultFile("transresPackingSlipLogos",null,$transresRequest);
                //$logoPath = $logo->getAbsoluteUploadFullPath();
                $logoPath = $userServiceUtil->getDocumentAbsoluteUrl($logo);
            }
        }

        ///// Get last rout for SAML /////
        //$authenticationSuccess = $this->container->get($sitename.'_authentication_handler');
        //ldap_translationalresearch_firewall
        //$logger = $this->container->get('logger');
        $firewallName = 'ldap_'.$sitename.'_firewall';
        $indexLastRoute = '_security.'.$firewallName.'.target_path';
        $lastRoute = $request->getSession()->get($indexLastRoute);
        //$logger->notice('1 loginPage: $lastRoute=['.$lastRoute."]");
        //replace http to https
        $protocol = 'https'; //TODO: looks like we need a real scheme parameter in site settings (case of haproxy)
//            if( isset($_SERVER['HTTPS']) ) {
//                $protocol = 'https';
//            }
//            else {
//                $protocol = 'http';
//            }
//            echo 'authenticate: protocol='.$protocol."<br>";
        $lastRoute = str_replace('http',$protocol,$lastRoute);
        //echo 'authenticate: lastRoute='.$lastRoute."<br>";
        //$logger->notice('2 loginPage: $lastRoute=['.$lastRoute."]");
        ///// EOF Get last rout /////

        //$messageToUsers = $this->getMessageToUsers();
        //$messageToUsers = null;

        $noteOnLoginPage = null;
        if( $siteObject ) {
            $noteOnLoginPage = $siteObject->getNoteOnLoginPage();
        }

        $formArr = array(
                            'last_username' => $lastUsername,   // last username entered by the user
                            'error'         => $error,
                            'sitename'     => $sitename,
                            'logo'  => $logoPath,
                            'messageToUsers' => $noteOnLoginPage,
                            'lastRoute' => $lastRoute,
                            'logoHeight' => 80,
                            'logoWidth' => 300
                        );

        return $formArr;
    }

//    public function getMessageToUsers() {
    //        return null;
    //    }
    //    /**
    //     * NOT USED
    //     * 127.0.0.1/order/index_dev.php/directory/logout
    //     *
    //     * @Route("/logout", name="employees_logout")
    //     * @Template()
    //     */
    //    public function logoutAction( Request $request )
    //    {
    //        //exit('idlelogoutAction');
    //        $routename = $request->get('_route');
    //
    //        //default
    //        $sitename = $this->getParameter('employees.sitename');
    //
    //        if( $routename == "employees_idlelogout" ) {
    //            $sitename = $this->getParameter('employees.sitename');
    //        }
    //        if( $routename == "fellapp_idlelogout" ) {
    //            $sitename = $this->getParameter('fellapp.sitename');
    //        }
    //        if( $routename == "resapp_idlelogout" ) {
    //            $sitename = $this->getParameter('resapp.sitename');
    //        }
    //        if( $routename == "deidentifier_idlelogout" ) {
    //            $sitename = $this->getParameter('deidentifier.sitename');
    //        }
    //        if( $routename == "scan_idlelogout" ) {
    //            $sitename = $this->getParameter('scan.sitename');
    //        }
    //        if( $routename == "vacreq_idlelogout" ) {
    //            $sitename = $this->getParameter('vacreq.sitename');
    //        }
    //        if( $routename == "calllog_idlelogout" ) {
    //            $sitename = $this->getParameter('calllog.sitename');
    //        }
    //        if( $routename == "crn_idlelogout" ) {
    //            $sitename = $this->getParameter('crn.sitename');
    //        }
    //        if( $routename == "translationalresearch_idlelogout" ) {
    //            $sitename = $this->getParameter('translationalresearch.sitename');
    //        }
    //        if( $routename == "dashboard_idlelogout" ) {
    //            $sitename = $this->getParameter('dashboard.sitename');
    //        }
    //
    //        $userSecUtil = $this->container->get('user_security_utility');
    //        return $userSecUtil->userLogout( $request, $sitename );
    //    }
    /**
     * 127.0.0.1/order/index_dev.php/directory/idle-log-out
     *
     *
     */
    #[Route(path: '/idle-log-out', name: 'employees_idlelogout', options: ['expose' => true])]
    #[Route(path: '/idle-log-out/{flag}', name: 'employees_idlelogout-saveorder', options: ['expose' => true])]
    public function idlelogoutAction( Request $request, $flag = null )
    {
        //exit('idlelogoutAction');
        $routename = $request->get('_route');

        //default
        $sitename = $this->getParameter('employees.sitename');

        if( $routename == "employees_idlelogout" ) {
            $sitename = $this->getParameter('employees.sitename');
        }
        if( $routename == "fellapp_idlelogout" ) {
            $sitename = $this->getParameter('fellapp.sitename');
        }
        if( $routename == "resapp_idlelogout" ) {
            $sitename = $this->getParameter('resapp.sitename');
        }
        if( $routename == "deidentifier_idlelogout" ) {
            $sitename = $this->getParameter('deidentifier.sitename');
        }
        if( $routename == "scan_idlelogout" ) {
            $sitename = $this->getParameter('scan.sitename');
        }
        if( $routename == "vacreq_idlelogout" ) {
            $sitename = $this->getParameter('vacreq.sitename');
        }
        if( $routename == "calllog_idlelogout" ) {
            $sitename = $this->getParameter('calllog.sitename');
        }
        if( $routename == "crn_idlelogout" ) {
            $sitename = $this->getParameter('crn.sitename');
        }
        if( $routename == "translationalresearch_idlelogout" ) {
            $sitename = $this->getParameter('translationalresearch.sitename');
        }
        if( $routename == "dashboard_idlelogout" ) {
            $sitename = $this->getParameter('dashboard.sitename');
        }

//        $request->getSession()->getFlashBag()->add(
//            'notice',
//            "Test message"
//        );
        //exit('111');

        $userSecUtil = $this->container->get('user_security_utility');
        return $userSecUtil->idleLogout( $request, $sitename, $flag );
    }
    //Used by Routing.generate in idleTimeoutClass.prototype.checkIdleTimeout.
    //idle log out with refereal url, from this url we can get the sitename and then redirect properly to the same system
    #[Route(path: '/idle-log-out-ref/{url}', name: 'employees_idlelogout_ref', options: ['expose' => true])]
    public function idlelogoutRefAction( Request $request, $url = null )
    {
        //exit('idlelogoutAction');
        //$routename = $request->get('_route');

        //default
        $sitename = $this->getParameter('employees.sitename');

        if( $url ) {
            //_index_dev.php_c_lmh_pathology_directory_ => /index_dev.php/c/lmh/pathology/directory/
            $url = str_replace("_","/",$url);

            if (strpos((string)$url, "/translational-research/") !== false) {
                $sitename = $this->getParameter('translationalresearch.sitename');
            }
            if (strpos((string)$url, "/directory/") !== false) {
                $sitename = $this->getParameter('employees.sitename');
            }
            if (strpos((string)$url, "/fellowship-applications/") !== false) {
                $sitename = $this->getParameter('fellapp.sitename');
            }
            if (strpos((string)$url, "/residency-applications/") !== false) {
                $sitename = $this->getParameter('resapp.sitename');
            }
            if (strpos((string)$url, "/call-log-book/") !== false) {
                $sitename = $this->getParameter('calllog.sitename');
            }
            if (strpos((string)$url, "/critical-result-notifications/") !== false) {
                $sitename = $this->getParameter('crn.sitename');
            }
            if (strpos((string)$url, "/time-away-request/") !== false) {
                $sitename = $this->getParameter('vacreq.sitename');
            }
            if (strpos((string)$url, "/scan/") !== false) {
                $sitename = $this->getParameter('scan.sitename');
            }
            if (strpos((string)$url, "/deidentifier/") !== false) {
                $sitename = $this->getParameter('deidentifier.sitename');
            }
            if (strpos((string)$url, "/dashboard/") !== false) {
                $sitename = $this->getParameter('dashboard.sitename');
            }
        }

        $userSecUtil = $this->container->get('user_security_utility');
        return $userSecUtil->idleLogout( $request, $sitename );
    }

    /**
     * sessionKeepAliveUrl for user-idleTimeouts.js
     */
    #[Route(path: '/common/setserveractive/{url}', name: 'setserveractive', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function setServerActiveAction( Request $request, $url=null )
    {
        //echo "keep Alive Action! <br>";
        $user = $this->getUser();
        if( !$user ) {
            $response = new Response();
            $response->setContent('OK');
            return $response;
        }

        ////////// setLastLoggedUrl //////////////
        //We might want to remove this setLastLoggedUrl to record user's activities
        //because it's an auto passive ping to the system, not a user interaction
        $user->setLastActivity(new \DateTime());
        //reconstruct url -order-index_dev.php-directory- to http://127.0.0.1/order/index_dev.php/directory/
        $url = str_replace("_","/",$url);
        $url = $request->getSchemeAndHttpHost().$url;
        //echo "url=".$url."<br>";

        // get current url from request
        $user->setLastLoggedUrl($url); //user's field lastLoggedUrl
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        ////////// EOF setLastLoggedUrl //////////////

        $response = new Response();
        $response->setContent('OK');
        return $response;
    }

    #[Route(path: '/common/getmaxidletime', name: 'getmaxidletime', methods: ['GET'], options: ['expose' => true])]
    public function getmaxidletimeAction( Request $request )
    {

        $userSecUtil = $this->container->get('user_security_utility');
        //$userUtil = new UserUtil();
        //$maxIdleTime = $userUtil->getMaxIdleTime($this->getDoctrine()->getManager());

        //$userUtil = new UserUtil();
        //$res = $userUtil->getMaxIdleTimeAndMaintenance($this->getDoctrine()->getManager(),$this->container->get('security.authorization_checker'),$this->container);
        $res = $userSecUtil->getMaxIdleTimeAndMaintenance();
        $maxIdleTime = $res['maxIdleTime']; //in seconds
        $maintenance = $res['maintenance'];

        if( $maintenance ) {
            $maxIdleTime = 0; //2min
        }

        $output = array(
            'maxIdleTime' => $maxIdleTime,
            'maintenance' => $maintenance
        );

        $response = new Response();
        //$response->setContent($res);
        $response->setContent(json_encode($output));

        return $response;
    }

    #[Route(path: '/no-permission', name: 'employees-nopermission', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Security/nopermission.html.twig')]
    public function actionNoPermission( Request $request )
    {
        $empty = $request->get('empty');

        return array(
            'sitename' => $this->getParameter('employees.sitename'),
            'empty' => $empty
        );
    }

    /**
     * Use for ajax authentication on web page (i.e. CallLog "Finalize and Sign")
     */
    #[Route(path: '/authenticate-user/', name: 'employees_authenticate_user', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function authenticateUserAction( Request $request )
    {

        if( false === $this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        ///////// temp testing ///////////
        //$response = new Response();
        //$response->setContent("OK");
        //return $response;
        ///////// EOF temp testing ///////////

        //$em = $this->getDoctrine()->getManager();
        //$user = $this->getUser(); //oli2002_@_local-user
        $user = $this->getUser();

        $res = "NOTOK";

        $password = $request->get('token');
        //echo "password=".$password."<br>";

        //create token
        $providerKey = 'ldap_employees_firewall'; //'ldap_fellapp_firewall'; //firewall name, or here, anything
        $username = $user->getUsername();
        $usernametype = NULL;

        //$token = new UsernamePasswordToken($username, $password, $providerKey);
        $token = new CustomUsernamePasswordToken($username, $password, $providerKey, $usernametype);

        //$authUtil = new AuthUtil($this->container,$em);
        $authUtil = $this->container->get('authenticator_utility');

        $authUSer = $authUtil->authenticateUserToken($user,$token);

        if( $authUSer ) {
            $res = "OK";
        }

        //echo "res=".$res."<br>";
        $response = new Response();
        $response->setContent($res);
        return $response;
    }

    #[Route(path: '/currently-logged-in-users/', name: 'employees_currently_logged_in_users', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/loggedin_users.html.twig')]
    public function currentlyLoggedInUsersAction( Request $request )
    {

        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect( $this->generateUrl("employees-nopermission") );
        }

        $userSecUtil = $this->container->get('user_security_utility');
        //$em = $this->getDoctrine()->getManager();
        //echo "sitename=".$this->siteName."<br>";

        $users = $userSecUtil->getLoggedInUsers($request);

        return array(
            'users' => $users,
            'sitename' => 'employees'
        );
    }

    /**
     * //potentially might be used by login page
     */
    #[Route(path: '/setloginvisit/', name: 'employees_setloginvisit', methods: ['GET'])]
    public function setAjaxLoginVisit( Request $request )
    {
        //echo "height=".$request->get('display_width').", width=".$request->get('display_height')." ";
        $options = array();
        $em = $this->getDoctrine()->getManager();
        //$userUtil = new UserUtil();

        $routename = $request->get('_route');
        if( $routename == "employees_setloginvisit" ) {
            $options['sitename'] = $this->getParameter('employees.sitename');
            $options['event'] = "Employee Directory login page visit";
        }
        if( $routename == "fellapp_setloginvisit" ) {
            $options['sitename'] = $this->getParameter('fellapp.sitename');
            $options['event'] = "Fellowship Applications login page visit";
        }
        if( $routename == "resapp_setloginvisit" ) {
            $options['sitename'] = $this->getParameter('resapp.sitename');
            $options['event'] = "Residency Applications login page visit";
        }
        if( $routename == "deidentifier_setloginvisit" ) {
            $options['sitename'] = $this->getParameter('deidentifier.sitename');
            $options['event'] = "Deidentifier System login page visit";
        }
        if( $routename == "scan_setloginvisit" ) {
            //scan uses its own setLoginAttempt
            $options['sitename'] = $this->getParameter('scan.sitename');
            $options['event'] = "Scan Order login page visit";
        }
        if( $routename == "vacreq_setloginvisit" ) {
            $options['sitename'] = $this->getParameter('vacreq.sitename');
            $options['event'] = "Vacation Request login page visit";
        }
        if( $routename == "calllog_setloginvisit" ) {
            $options['sitename'] = $this->getParameter('calllog.sitename');
            $options['event'] = "Call Log Book login page visit";
        }
        if( $routename == "crn_setloginvisit" ) {
            $options['sitename'] = $this->getParameter('crn.sitename');
            $options['event'] = "Critical Result Notification login page visit";
        }
        if( $routename == "translationalresearch_setloginvisit" ) {
            $options['sitename'] = $this->getParameter('translationalresearch.sitename');
            $options['event'] = "Translational Research login page visit";
        }
        if( $routename == "dashboard_setloginvisit" ) {
            $options['sitename'] = $this->getParameter('dashboard.sitename');
            $options['event'] = "Dashboard login page visit";
        }


        $options['eventtype'] = "Login Page Visit";
        $options['serverresponse'] = "";

        //"Login Page Visit" - Object is Site name
        //echo "sitename=".$options['sitename']."<br>";
        $siteObject = $em->getRepository(SiteList::class)->findOneByAbbreviation($options['sitename']);
        //echo "siteObject=".$siteObject."<br>";
        //exit();
        if( $siteObject ) {
            $options['eventEntity'] = $siteObject;
        }

        //$userUtil->setLoginAttempt($request,$this->container->get('security.token_storage'),$em,$options);
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->setLoginAttempt($request,$options);

        $response = new Response();
        $response->setContent('OK');
        return $response;
    }



    //////////////// Idle Time Out - Common Functions (NOT USED ANYMORE) ////////////////////
    /**
     * //NOT USED
     *
     * Check the server every 30 min (maxIdleTime) if the server timeout is ok ($lapse > $maxIdleTime).
     * If not, the server returns NOTOK flag and js open a dialog modal to continue.
     */
    #[Route(path: '/common/keepalive', name: 'keepalive', methods: ['GET'], options: ['expose' => true])]
    public function keepAliveAction( Request $request )
    {
        //echo "keep Alive Action! <br>";

        $response = new Response();
        
        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');

        //$userUtil = new UserUtil();
        //$res = $userUtil->getMaxIdleTimeAndMaintenance($this->getDoctrine()->getManager(),$this->container->get('security.authorization_checker'),$this->container);
        $res = $userSecUtil->getMaxIdleTimeAndMaintenance();
        $maxIdleTime = $res['maxIdleTime']+5; //in seconds; add some seconds as a safety delay.
        $maintenance = $res['maintenance'];

        /////////////////// check if maintenance is on ////////////////////
        if( $maintenance ) {
            $response->setContent('NOTOK: maxIdleTime='.$maxIdleTime);
            return $response;
        }
        //echo '$maxIdleTime='.$maxIdleTime."<br>";
        ////////////////////////////////////////////////////////////////////
        $session = $request->getSession();

        //Don't use getLastUsed(). But it is the same until page is closed.
        //$lapse = time() - $session->getMetadataBag()->getLastUsed();

        //get lapse from the lastRequest in session
        $lastRequest = $session->get('lastRequest');
        //echo "lastRequest=".gmdate("Y-m-d H:i:s",$lastRequest)."<br>";
        //echo "pingCheck=".$session->get('pingCheck')."<br>";

        if( !$lastRequest ) {
            $logger->notice("keepAliveAction: lastRequest is not set! Set lastRequest to ".time());
            $session->set('lastRequest',time());
            $lastRequest = $session->get('lastRequest');
        }

        //echo "time=".time()."; lastRequest=".$lastRequest."<br>";
        $lapse = time() - $lastRequest;

        //update lastRequest
        //$session->set('lastRequest',time());

        //created=2015-11-06T19:50:36Z<br>OK
        //echo "created=".gmdate("Y-m-d H:i:s", $session->getMetadataBag()->getCreated())."<br>";
        //$msg = "'lapse=".$lapse.", max idle time=".$maxIdleTime."'";
        //echo "console.log(".$msg.")";
        //echo $msg;
        //$this->logoutUser($event);
        //exit();

        $msg = "keepAliveAction: lapse=".$lapse." ?= "."maxIdleTime=".$maxIdleTime;
        //$logger->notice($msg);

        if( $lapse > $maxIdleTime ) {
            $overlapseMsg = 'over lapse = '.($lapse-$maxIdleTime) . "seconds.";
            $logger->notice("keepAliveAction: ".$overlapseMsg);
            //echo $overlapseMsg."<br>";
            $response->setContent('show_idletimeout_modal: '.$overlapseMsg);
        } else {
            //echo "OK<br>";
            $logger->notice("keepAliveAction: no overlapse: return OK");
            $response->setContent($msg); //'OK'
        }

        return $response;
    }
    
    /**
     * //NOT USED
     */
    #[Route(path: '/common/setserveractive_ORIG', name: 'setserveractive_ORIG', methods: ['GET'], options: ['expose' => true])]
    public function setServerActiveAction_ORIG( Request $request )
    {
        //echo "keep Alive Action! <br>";
        $response = new Response();

        $session = $request->getSession();            
        $session->set('lastRequest',time());

        //$url = $request->query->get('url');

        //dump($session);
        //exit('111');

        //$logger = $this->container->get('logger');
        //$logger->notice("setServerActiveAction: session id=".$session->getId()."; url=".$url);
    
        $response->setContent('OK');
        return $response;
    }
    //////////////// EOF Idle Time Out (NOT USED ANYMORE) ////////////////////


    //Custom logout
    //[Route(path: '/logout', name: 'employees_logout')]
    public function logoutTestAction( Request $request, Security $security, TokenStorageInterface $tokenStorage )
    {
        echo "logout Action! <br>";
        exit();

        //$this->container->get('security.token_storage')->setToken(null);
        //$this->container->get('request')->getSession()->invalidate();

//        $session = $request->getSession();
//        // you can also disable the csrf logout
//        $security->logout(false);
//        return $this->redirect($this->generateUrl($sitename.'_login'));
    }


//    /**
//     * @Route("/access-request-logout/", name="employees_accreq_logout")
//     * @Template()
//     */
//    public function accreqLogoutAction( Request $request )
//    {
//        //echo "logout Action! <br>";
//        //exit();
//
//
//        $this->container->get('security.token_storage')->setToken(null);
//        //$this->container->get('request')->getSession()->invalidate();
//
//        return $this->accreqLogout($request,$this->getParameter('employees.sitename'));
//    }
//
//    public function accreqLogout($request,$sitename) {
//        $this->container->get('security.token_storage')->setToken(null);
//        return $this->redirect($this->generateUrl($sitename.'_login'));
//    }




}

?>
