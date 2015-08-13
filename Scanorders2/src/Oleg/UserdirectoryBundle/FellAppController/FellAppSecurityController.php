<?php
namespace Oleg\UserdirectoryBundle\FellAppController;


use Oleg\UserdirectoryBundle\Controller\SecurityController;
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

class FellAppSecurityController extends SecurityController
{

    /**
     * @Route("/login", name="fellapp_login")
     *
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {
        return parent::loginAction($request);
    }


    /**
     * @Route("/setloginvisit/", name="fellapp_setloginvisit")
     *
     * @Method("GET")
     */
    public function setAjaxLoginVisit( Request $request )
    {
        return parent::setAjaxLoginVisit($request);
    }


    /**
     * @Route("/no-permission", name="fellapp-nopermission")
     * @Method("GET")
     * @Template("OlegUserdirectoryBundle:Security:nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        return parent::actionNoPermission($request);
    }



    /**
     * @Route("/idlelogout", name="fellapp_idlelogout")
     * @Route("/idlelogout/{flag}", name="fellapp_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        return parent::idlelogoutAction($request,$flag);
//        $userSecUtil = $this->get('user_security_utility');
//        $sitename = $this->container->getParameter('fellapp.sitename');
//        return $userSecUtil->idleLogout( $request, $sitename, $flag );
    }





//    /**
//     * @Route("/setloginvisit/", name="fellapp_setloginvisit")
//     * @Method("GET")
//     */
//    public function setAjaxLoginVisit( Request $request )
//    {
//        //echo "height=".$request->get('display_width').", width=".$request->get('display_height')." ";
//        $options = array();
//        $em = $this->getDoctrine()->getManager();
//        $userUtil = new UserUtil();
//        $options['sitename'] = $this->container->getParameter('scan.sitename');
//        $options['eventtype'] = "Login Page Visit";
//        $options['event'] = "Scan Order login page visit";
//        $options['serverresponse'] = "";
//        $userUtil->setLoginAttempt($request,$this->get('security.context'),$em,$options);
//
//        $response = new Response();
//        $response->setContent('OK');
//        return $response;
//    }



}

?>
