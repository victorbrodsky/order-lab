<?php
namespace Oleg\UserdirectoryBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

use Oleg\UserdirectoryBundle\Util\UserUtil;

class SecurityController extends Controller
{

//    /**
//     * @Route("/login_check", name="employees_login_check")
//     * @Method("POST")
//     * @Template("OlegUserdirectoryBundle:Security:login.html.twig")
//     */
//    public function loginCheckAction( Request $request )
//    {
//
//        $username = $request->get('_username');
//        $password = $request->get('_password');
//
//        echo "username=".$username.", password=".$password."<br>";
//
//        exit("my login check!");
//    }

    /**
     * @Route("/login", name="employees_login")
     *
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {

        $routename = $request->get('_route');
        //echo "routename=".$routename."<br>";

        if( $routename == "employees_login" ) {
            $sitename = $this->container->getParameter('employees.sitename');
        }
        if( $routename == "fellapp_login" ) {
            $sitename = $this->container->getParameter('fellapp.sitename');
        }
        if( $routename == "deidentifier_login" ) {
            $sitename = $this->container->getParameter('deidentifier.sitename');
        }
        if( $routename == "scan_login" ) {
            $sitename = $this->container->getParameter('scan.sitename');
        }

        //$sitename = $this->container->getParameter('employees.sitename');
        $formArr = $this->loginPage($sitename);

        if( $formArr == null ) {
            return $this->redirect( $this->generateUrl('main_common_home') );
            //return $this->redirect( $this->generateUrl($sitename.'_home') );
        }

        $em = $this->getDoctrine()->getManager();
        $usernametypes = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findBy(
            array(
                'type' => array('default', 'user-added'),
                'abbreviation' => array('wcmc-cwid','local-user')
            ),
            array('orderinlist' => 'ASC')
        );

        if( count($usernametypes) == 0 ) {
            $usernametypes = array();
            $option = array('abbreviation'=>'wcmc-cwid', 'name'=>'WCMC CWID');
            $usernametypes[] = $option;
            $option_localuser = array('abbreviation'=>'local-user', 'name'=>'Local User');
            $usernametypes[] = $option_localuser;
        }

        $formArr['usernametypes'] = $usernametypes;

        ///////////// read cookies /////////////
        $cookieKeytype = $request->cookies->get('userOrderSuccessCookiesKeytype');
        if( $cookieKeytype ) {
            $formArr['user_type'] = $cookieKeytype;
            //echo "cookieKeytype=".$cookieKeytype."<br>";
        }

        $cookieUsername = $request->cookies->get('userOrderSuccessCookiesUsername');
        if( $cookieUsername ) {
            $formArr['last_username'] = $cookieUsername;
            //echo "cookieUsername=".$cookieUsername."<br>";
        }
        ///////////// EOF read cookies /////////////


        return $this->render(
            'OlegUserdirectoryBundle:Security:login.html.twig',
            $formArr
        );
    }

    public function loginPage($sitename) {

        if(
            $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return null;
        }

        $request = $this->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if( $request->attributes->has(SecurityContext::AUTHENTICATION_ERROR) ) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

//        $em = $this->getDoctrine()->getManager();
//        $usernametypes = $em->getRepository('OlegUserdirectoryBundle:UsernameType')->findBy( array('type' => array('default', 'user-added')), array('orderinlist' => 'ASC') );
//
//        if( !$usernametypes || count($usernametypes) == 0 ) {
//            $usernametypes = array();
//            $option = array('abbreviation'=>'wcmc-cwid', 'name'=>'WCMC CWID');
//            $usernametypes[] = $option;
//        }

        //get original username entered by a user in login form
        $lastUsername = $session->get(SecurityContext::LAST_USERNAME);
        $lastUsernameArr = explode("_@_", $lastUsername);
        $lastUsername = $lastUsernameArr[0];

        $formArr = array(
//                            'usernametypes' => $usernametypes,
                            'last_username' => $lastUsername,   // last username entered by the user
                            'error'         => $error,
                            'sitename'     => $sitename
                        );

        return $formArr;
    }



    /**
     * @Route("/idlelogout", name="employees_idlelogout")
     * @Route("/idlelogout/{flag}", name="employees_idlelogout-saveorder")
     *
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        $routename = $request->get('_route');
        if( $routename == "employees_idlelogout" ) {
            $sitename = $this->container->getParameter('employees.sitename');
        }
        if( $routename == "fellapp_idlelogout" ) {
            $sitename = $this->container->getParameter('fellapp.sitename');
        }
        if( $routename == "deidentifier_idlelogout" ) {
            $sitename = $this->container->getParameter('deidentifier.sitename');
        }
        if( $routename == "scan_idlelogout" ) {
            $sitename = $this->container->getParameter('scan.sitename');
        }

        $userSecUtil = $this->get('user_security_utility');
        return $userSecUtil->idleLogout( $request, $sitename, $flag );
    }

    /**
     * @Route("/setloginvisit/", name="employees_setloginvisit")
     *
     * @Method("GET")
     */
    public function setAjaxLoginVisit( Request $request )
    {
        //echo "height=".$request->get('display_width').", width=".$request->get('display_height')." ";
        $options = array();
        $em = $this->getDoctrine()->getManager();
        $userUtil = new UserUtil();

        $routename = $request->get('_route');
        if( $routename == "employees_setloginvisit" ) {
            $options['sitename'] = $this->container->getParameter('employees.sitename');
            $options['event'] = "Employee Directory login page visit";
        }
        if( $routename == "fellapp_setloginvisit" ) {
            $options['sitename'] = $this->container->getParameter('fellapp.sitename');
            $options['event'] = "Fellowship Applications login page visit";
        }
        if( $routename == "deidentifier_setloginvisit" ) {
            $options['sitename'] = $this->container->getParameter('deidentifier.sitename');
            $options['event'] = "Deidentifier System login page visit";
        }
        if( $routename == "scan_setloginvisit" ) {
            //scan uses its own setLoginAttempt
            $options['sitename'] = $this->container->getParameter('scan.sitename');
            $options['event'] = "Scan Order login page visit";
        }


        $options['eventtype'] = "Login Page Visit";
        $options['serverresponse'] = "";

        //"Login Page Visit" - Object is Site name
        //echo "sitename=".$options['sitename']."<br>";
        $siteObject = $em->getRepository('OlegUserdirectoryBundle:SiteList')->findOneByAbbreviation($options['sitename']);
        //echo "siteObject=".$siteObject."<br>";
        //exit();
        if( $siteObject ) {
            $options['eventEntity'] = $siteObject;
        }

        $userUtil->setLoginAttempt($request,$this->get('security.context'),$em,$options);

        $response = new Response();
        $response->setContent('OK');
        return $response;
    }



    //////////////// Idle Time Out - Common Functions ////////////////////

    /**
     * Check the server every 30 min (maxIdleTime) if the server timeout is ok ($lapse > $maxIdleTime).
     * If not, the server returns NOTOK flag and js open a dialog modal to continue.
     *
     * @Route("/common/keepalive", name="keepalive")
     * @Method("GET")
     */
    public function keepAliveAction( Request $request )
    {
        //echo "keep Alive Action! <br>";

        $response = new Response();
        
        $logger = $this->container->get('logger');

        $userUtil = new UserUtil();
        $res = $userUtil->getMaxIdleTimeAndMaintenance($this->getDoctrine()->getManager(),$this->get('security.context'),$this->container);
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
            //$logger->notice("keepAliveAction: lastRequest is not set! Set lastRequest to ".time());
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

        //$logger->notice("keepAliveAction: lapse=".$lapse." > "."maxIdleTime=".$maxIdleTime);

        if( $lapse > $maxIdleTime ) {
            $overlapseMsg = 'over lapse = '.($lapse-$maxIdleTime) . "seconds.";
            //$logger->notice("keepAliveAction: ".$overlapseMsg);
            //echo $overlapseMsg."<br>";
            $response->setContent($overlapseMsg);
        } else {
            //echo "OK<br>";
            $response->setContent('OK');
        }

        return $response;
    }
    
    /**
     * Not used anymore; Replaced by keepAliveAction
     * @Route("/common/isserveractive", name="isserveractive")
     * @Method("GET")
     */
//    public function isServerActiveAction( Request $request )
//    {
//        //echo "keep Alive Action! <br>";
//
//        $response = new Response();
//
//        $logger = $this->container->get('logger');
//
//        $userUtil = new UserUtil();
//        $res = $userUtil->getMaxIdleTimeAndMaintenance($this->getDoctrine()->getManager(),$this->get('security.context'),$this->container);
//        $maxIdleTime = $res['maxIdleTime'];   //in seconds
//        $maintenance = $res['maintenance'];
//
//        //$maxIdleTime = 55;  //(60000-5000)/1000;
//
//        if( $maintenance ) {
//            $response->setContent(json_encode('NOTOK'));
//            return $response;
//        }
//
//        $session = $request->getSession();
//
//        $lastRequest = $session->get('lastRequest');
//        //echo "lastRequest=".gmdate("Y-m-d H:i:s",$lastRequest)."<br>";
//        //echo "pingCheck=".$session->get('pingCheck')."<br>";
//
//        if( !$lastRequest ) {
//            //echo "init set lastRequest=".gmdate("Y-m-d H:i:s",time())."<br>";
//            $logger->notice("isServerActiveAction: set lastRequest to ".time());
//            $session->set('lastRequest',time());
//            $lastRequest = $session->get('lastRequest');
//        }
//
//        //echo "time=".time()."; lastRequest=".$lastRequest."<br>";
//        $lapse = time() - $lastRequest; //time() in seconds
//
//        //echo "lapse=".$lapse."; maxIdleTime=".$maxIdleTime."<br>";
//
//        if( $lapse > $maxIdleTime ) {
//            $overlapseMsg = 'over lapse = '.($lapse-$maxIdleTime);
//            $response->setContent(json_encode($overlapseMsg));
//        } else {
//            $response->setContent(json_encode('OK'));
//        }
//
//        return $response;
//    }
    
    /**
     *
     *
     * @Route("/common/setserveractive", name="setserveractive")
     * @Method("GET")
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
     * @Route("/common/getmaxidletime", name="getmaxidletime")
     * @Method("GET")
     */
    public function getmaxidletimeAction( Request $request )
    {

        //$userUtil = new UserUtil();
        //$maxIdleTime = $userUtil->getMaxIdleTime($this->getDoctrine()->getManager());

        $userUtil = new UserUtil();
        $res = $userUtil->getMaxIdleTimeAndMaintenance($this->getDoctrine()->getManager(),$this->get('security.context'),$this->container);
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
     * @Route("/no-permission", name="employees-nopermission")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Security:nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        return array(
            //'returnpage' => '',
        );
    }


//    /**
//     * @Route("/logout", name="employees_logout")
//     * @Template()
//     */
//    public function logoutAction( Request $request )
//    {
//        echo "logout Action! <br>";
//        exit();
//
//        $this->get('security.context')->setToken(null);
//        //$this->get('request')->getSession()->invalidate();
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
//        $this->get('security.context')->setToken(null);
//        //$this->get('request')->getSession()->invalidate();
//
//        return $this->accreqLogout($request,$this->container->getParameter('employees.sitename'));
//    }
//
//    public function accreqLogout($request,$sitename) {
//        $this->get('security.context')->setToken(null);
//        return $this->redirect($this->generateUrl($sitename.'_login'));
//    }

}

?>
