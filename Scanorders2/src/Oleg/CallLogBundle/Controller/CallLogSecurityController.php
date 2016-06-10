<?php
namespace Oleg\CallLogBundle\Controller;


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

class CallLogSecurityController extends SecurityController
{

    /**
     * @Route("/login", name="calllog_login")
     *
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {
        //exit('calllog: loginAction');
        return parent::loginAction($request);
    }


    /**
     * @Route("/setloginvisit/", name="calllog_setloginvisit")
     *
     * @Method("GET")
     */
    public function setAjaxLoginVisit( Request $request )
    {
        //exit('calllog: setAjaxLoginVisit');
        return parent::setAjaxLoginVisit($request);
    }


    /**
     * @Route("/no-permission", name="calllog-nopermission")
     * @Method("GET")
     * @Template("OlegCallLogBundle:Security:nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        //exit('calllog: actionNoPermission');
        //return parent::actionNoPermission($request);
        return array(
            //'returnpage' => '',
        );
    }


    /**
     * @Route("/idlelogout", name="calllog_idlelogout")
     * @Route("/idlelogout/{flag}", name="calllog_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        //exit('calllog: idlelogoutAction');
        return parent::idlelogoutAction($request,$flag);
    }


}

?>
