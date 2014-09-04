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

    /**
     * @Route("/login", name="employees_login")
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
            'OlegUserdirectoryBundle:Security:login.html.twig',
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
        $res = $userUtil->getMaxIdleTimeAndMaintenance($this->getDoctrine()->getManager());
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
            //$this->session->start();
            $lapse = time() - $session->getMetadataBag()->getLastUsed();

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
     * @Route("/idlelogout", name="employees_idlelogout")
     * @Route("/idlelogout/{flag}", name="employees_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        $userSecUtil = $this->get('user_security_utility');
        $sitename = $this->container->getParameter('employees.sitename');
        return $userSecUtil->idleLogout( $request, $sitename, $flag );
    }

    /**
     * @Route("/getmaxidletime/", name="getmaxidletime")
     * @Method("GET")
     */
    public function getmaxidletimeAction( Request $request )
    {

        //$userUtil = new UserUtil();
        //$maxIdleTime = $userUtil->getMaxIdleTime($this->getDoctrine()->getManager());

        $userUtil = new UserUtil();
        $res = $userUtil->getMaxIdleTimeAndMaintenance($this->getDoctrine()->getManager());
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
