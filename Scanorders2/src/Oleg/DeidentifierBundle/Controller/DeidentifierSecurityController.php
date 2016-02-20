<?php
namespace Oleg\DeidentifierBundle\Controller;


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

class DeidentifierSecurityController extends SecurityController
{

    /**
     * @Route("/login", name="deidentifier_login")
     *
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {
        return parent::loginAction($request);
    }


    /**
     * @Route("/setloginvisit/", name="deidentifier_setloginvisit")
     *
     * @Method("GET")
     */
    public function setAjaxLoginVisit( Request $request )
    {
        return parent::setAjaxLoginVisit($request);
    }


    /**
     * @Route("/no-permission", name="deidentifier-nopermission")
     * @Method("GET")
     * @Template("DeidentifierBundle:Security:nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        //return parent::actionNoPermission($request);
        return array(
            //'returnpage' => '',
        );
    }



    /**
     * @Route("/idlelogout", name="deidentifier_idlelogout")
     * @Route("/idlelogout/{flag}", name="deidentifier_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        return parent::idlelogoutAction($request,$flag);
    }


//    /**
//     * @Route("/access-request-logout/", name="deidentifier_accreq_logout")
//     * @Template()
//     */
//    public function accreqLogoutAction( Request $request )
//    {
//        //echo "logout Action! <br>";
//        //exit();
//        //return parent::accreqLogoutAction($request);
//
//        return $this->accreqLogout($request,$this->container->getParameter('deidentifier.sitename'));
//    }



}

?>
