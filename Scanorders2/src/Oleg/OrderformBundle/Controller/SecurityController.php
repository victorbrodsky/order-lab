<?php
namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

//use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Oleg\OrderformBundle\Helper\SessionIdleHandler;
use Oleg\OrderformBundle\Helper\UserUtil;

class SecurityController extends Controller
{

    /**
     * @Route("/login", name="login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {

        if(
            $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
        ){
            return $this->redirect( $this->generateUrl('scan-order-home') );
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

        return $this->render(
            'OlegOrderformBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            )
        );

    }


    //////////////// Idle Time Out ////////////////////

    /**
     * @Route("/keepalive/", name="keepalive")
     * @Method("GET")
     */
    public function keepAliveAction( Request $request )
    {
        //echo "keep Alive Action! <br>";

        $response = new Response();

        $userUtil = new UserUtil();
        $maxIdleTime = $userUtil->getMaxIdleTime($this->getDoctrine()->getManager());

        if( $maxIdleTime > 0 ) {

            $session = $request->getSession();
            //$this->session->start();
            $lapse = time() - $session->getMetadataBag()->getLastUsed();

            $msg = "'lapse=".$lapse.", max idle time=".$maxIdleTime."'";
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
     * @Route("/idlelogout", name="idlelogout")
     * @Route("/idlelogout/{flag}", name="idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        //echo "logout Action! <br>";
        //exit();

        $userUtil = new UserUtil();
        $maxIdleTime = $userUtil->getMaxIdleTime($this->getDoctrine()->getManager());

//        if( !$flag || $flag == '' ) {
//            //$request = $this->get('request');
//            $flag = trim( $request->get('opt') );
//        }

        if( $flag && $flag == 'saveorder' ) {
            $msg = 'You have been logged out after '.($maxIdleTime/60).' minutes of inactivity. You can find the order you have been working on in the list of your orders once you log back in.';
        } else {
            $msg = 'You have been logged out after '.($maxIdleTime/60).' minutes of inactivity.';
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        $this->get('security.context')->setToken(null);
        //$this->get('request')->getSession()->invalidate();
        return $this->redirect($this->generateUrl('login'));
    }

    /**
     * @Route("/getmaxidletime/", name="getmaxidletime")
     * @Method("GET")
     */
    public function getmaxidletimeAction( Request $request )
    {

        $userUtil = new UserUtil();
        $maxIdleTime = $userUtil->getMaxIdleTime($this->getDoctrine()->getManager());

        $response = new Response();
        $response->setContent($maxIdleTime);

        return $response;
    }

    /**
     * @Route("/setloginvisit/", name="setloginvisit")
     * @Method("GET")
     */
    public function setAjaxLoginVisit( Request $request )
    {
        //echo "height=".$request->get('display_width').", width=".$request->get('display_height')." ";
        $options = array();
        $em = $this->getDoctrine()->getManager();
        $userUtil = new UserUtil();
        $options['event'] = "Login Page Visit";
        $options['serverresponse'] = "";
        $userUtil->setLoginAttempt($request,$this->get('security.context'),$em,$options);

        $response = new Response();
        $response->setContent('OK');
        return $response;
    }


    /**
     * @Route("/scan-order/no-permission", name="scan-order-nopermission")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Security:nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        return array(
            //'returnpage' => '',
        );
    }

   
//    /**
//     * @Route("/login_check", name="login_check")
//     * @Method("POST")
//     * @Template("OlegOrderformBundle:ScanOrder:new_orig.html.twig")
//     */
//    public function loginCheckAction( Request $request )
//    {
//        //exit("my login check!");
//    }


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
