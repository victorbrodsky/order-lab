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


use App\UserdirectoryBundle\Security\Authentication\AuthUtil;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Security\Authentication\CustomUsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
//use Symfony\Component\Security\Core\Security;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpFoundation\Session\Session;
//use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends OrderAbstractController
{

//    /**
//     * @Route("/login_check", name="employees_login_check", methods={"POST"})
//     * @Template("AppUserdirectoryBundle/Security/login.html.twig")
//     */
//    public function loginCheckAction( Request $request )
//    {
//
//        $username = $request->get('_username');
//        $password = $request->get('_password');
//
//        echo "username=".$username.", password=".$password."<br>";
//
//        //exit("my login check!");
//    }

//    /**
//     * @Route("/login_check", name="dummy_login_check")
//     * @Route("/directory/login_check", name="employees_dummy_login_check")
//     * @Route("/call-log-book/login_check", name="calllog_dummy_login_check")
//     */
//    public function loginCheckAction( Request $request )
//    {
//        //Reroute to home page
//        //exit("Reroute to home page");
//        return $this->redirect( $this->generateUrl('main_common_home') );
//    }

    /**
     * //Method("GET")
     *
     * @Route("/directory/login", name="directory_login")
     * @Route("/login", name="employees_login")
     *
     * @Template()
     */
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
        $formArr = $this->loginPage($sitename,$authenticationUtils);

        if( $formArr == null ) {
            return $this->redirect( $this->generateUrl('main_common_home') );
            //return $this->redirect( $this->generateUrl($sitename.'_home') );
        }

        $em = $this->getDoctrine()->getManager();
        $usernametypes = $em->getRepository('AppUserdirectoryBundle:UsernameType')->findBy(
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

        return $this->render(
            'AppUserdirectoryBundle/Security/login.html.twig',
            $formArr
        );

    }

    public function loginPage($sitename,$authenticationUtils) {

        if(
            $this->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return null;
        }

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
        $siteObject = $em->getRepository('AppUserdirectoryBundle:SiteList')->findOneByAbbreviation($sitename);
        if( $siteObject ) {
            $logos = $siteObject->getDocuments();
            if( count($logos) > 0 ) {
                $logo = $logos->first();
                //$packingSlipLogoFileName = $transresRequestUtil->getDefaultFile("transresPackingSlipLogos",null,$transresRequest);
                $logoPath = $logo->getAbsoluteUploadFullPath();
            }
        }

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
                            'logoHeight' => 80,
                            'logoWidth' => 300
                        );

        return $formArr;
    }

//    public function getMessageToUsers() {
//        return null;
//    }



    /**
     * @Route("/idle-log-out", name="employees_idlelogout")
     * @Route("/idle-log-out/{flag}", name="employees_idlelogout-saveorder")
     *
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
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

        $userSecUtil = $this->container->get('user_security_utility');
        return $userSecUtil->idleLogout( $request, $sitename, $flag );
    }

    /**
     * @Route("/setloginvisit/", name="employees_setloginvisit", methods={"GET"})
     */
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
        $siteObject = $em->getRepository('AppUserdirectoryBundle:SiteList')->findOneByAbbreviation($options['sitename']);
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



    //////////////// Idle Time Out - Common Functions ////////////////////

    /**
     * Check the server every 30 min (maxIdleTime) if the server timeout is ok ($lapse > $maxIdleTime).
     * If not, the server returns NOTOK flag and js open a dialog modal to continue.
     *
     * @Route("/common/keepalive", name="keepalive", methods={"GET"}, options={"expose"=true})
     */
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
     * @Route("/common/setserveractive", name="setserveractive", methods={"GET"}, options={"expose"=true})
     */
    public function setServerActiveAction( Request $request )
    {
        //echo "keep Alive Action! <br>";
        $response = new Response();

        $session = $request->getSession();            
        $session->set('lastRequest',time());

        //$logger = $this->container->get('logger');
        //$logger->notice("setServerActiveAction: reset lastRequest");
    
        $response->setContent('OK');
        return $response;
    }



    /**
     * @Route("/common/getmaxidletime", name="getmaxidletime", methods={"GET"})
     */
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
    //////////////// EOF Idle Time Out ////////////////////


    /**
     * @Route("/no-permission", name="employees-nopermission", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Security/nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        $empty = $request->get('empty');

        return array(
            'sitename' => $this->getParameter('employees.sitename'),
            'empty' => $empty
        );
    }


//    /**
//     * @Route("/logout", name="employees_logout")
//     * @Template()
//     */
//    public function logoutAction( Request $request )
//    {
//        echo "logout Action! <br>";
//        //exit();
//
//        $this->container->get('security.token_storage')->setToken(null);
//        //$this->container->get('request')->getSession()->invalidate();
//
//
//        $routename = $request->get('_route');
//        //echo "routename=".$routename."<br>";
//
//        return $this->redirect($this->generateUrl($sitename.'_login'));
//    }


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


    /**
     * Use for ajax authentication on web page (i.e. CallLog "Finalize and Sign")
     *
     * @Route("/authenticate-user/", name="employees_authenticate_user", methods={"GET","POST"}, options={"expose"=true})
     */
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

        //$token = new UsernamePasswordToken($username, $password, $providerKey);
        $token = new CustomUsernamePasswordToken($username, $password, $providerKey);

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

}

?>
