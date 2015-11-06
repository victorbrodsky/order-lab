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
                'abbreviation' => array('wcmc-cwid')
            ),
            array('orderinlist' => 'ASC')
        );

        if( count($usernametypes) == 0 ) {
            $usernametypes = array();
            $option = array('abbreviation'=>'wcmc-cwid', 'name'=>'WCMC CWID');
            $usernametypes[] = $option;
        }

        $formArr['usernametypes'] = $usernametypes;

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
        }
        if( $routename == "fellapp_setloginvisit" ) {
            $options['sitename'] = $this->container->getParameter('fellapp.sitename');
        }

        $options['eventtype'] = "Login Page Visit";
        $options['event'] = "Employee Directory login page visit";
        $options['serverresponse'] = "";
        $userUtil->setLoginAttempt($request,$this->get('security.context'),$em,$options);

        $response = new Response();
        $response->setContent('OK');
        return $response;
    }



    //////////////// Idle Time Out - Common Functions ////////////////////

    /**
     * @Route("/common/keepalive", name="keepalive")
     * @Method("GET")
     */
    public function keepAliveAction( Request $request )
    {
        //echo "keep Alive Action! <br>";

        $response = new Response();

        $userUtil = new UserUtil();
        $res = $userUtil->getMaxIdleTimeAndMaintenance($this->getDoctrine()->getManager(),$this->get('security.context'),$this->container);
        $maxIdleTime = $res['maxIdleTime'];
        $maintenance = $res['maintenance'];

        /////////////////// check if maintenance is on ////////////////////
        if( $maintenance ) {
            $maxIdleTime = 0;
        }
        //echo '$maxIdleTime='.$maxIdleTime."<br>";
        ////////////////////////////////////////////////////////////////////

        if( $maxIdleTime > 0 ) {

            $session = $request->getSession();
            
            //Don't use getLastUsed(). But it is the same until page is closed.
            //$lapse = time() - $session->getMetadataBag()->getLastUsed();
            
            //get lapse from the lastRequest in session
            $lapse = time() - $session->get('lastRequest');
            $session->set('lastRequest',time());

            //created=2015-11-06T19:50:36Z<br>OK
            //echo "created=".gmdate("Y-m-d H:i:s", $session->getMetadataBag()->getCreated())."<br>";
            //$msg = "'lapse=".$lapse.", max idle time=".$maxIdleTime."'";
            //echo "console.log(".$msg.")";
            //echo $msg;
            //$this->logoutUser($event);
            //exit();

            if( $lapse > $maxIdleTime ) {               
                $response->setContent('over lapse = '.($lapse-$maxIdleTime));
            } else {
                $response->setContent('OK');
            }

        } else {
            $response->setContent('NOTOK');
        }

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
        $maxIdleTime = $res['maxIdleTime'];
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
//     * @Route("/logout", name="logout")
//     * @Template()
//     */
//    public function logoutAction()
//    {
//        //echo "logout Action! <br>";
//        //exit();
//
//        $this->get('security.context')->setToken(null);
//        $this->get('request')->getSession()->invalidate();
//        return $this->redirect($this->generateUrl('login'));
//    }

}

?>
