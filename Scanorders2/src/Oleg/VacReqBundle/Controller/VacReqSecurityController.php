<?php
namespace Oleg\VacReqBundle\Controller;


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

class VacReqSecurityController extends SecurityController
{

    /**
     * @Route("/login", name="vacreq_login")
     *
     * @Method("GET")
     * @Template()
     */
    public function loginAction( Request $request ) {
        //exit('vacreq: loginAction');
        return parent::loginAction($request);
    }


    /**
     * @Route("/setloginvisit/", name="vacreq_setloginvisit")
     *
     * @Method("GET")
     */
    public function setAjaxLoginVisit( Request $request )
    {
        //exit('vacreq: setAjaxLoginVisit');
        return parent::setAjaxLoginVisit($request);
    }


    /**
     * @Route("/no-permission", name="vacreq-nopermission")
     * @Method("GET")
     * @Template("VacReqBundle:Security:nopermission.html.twig")
     */
    public function actionNoPermission( Request $request )
    {
        //exit('vacreq: actionNoPermission');
        //return parent::actionNoPermission($request);
        return array(
            //'returnpage' => '',
        );
    }


    /**
     * @Route("/idlelogout", name="vacreq_idlelogout")
     * @Route("/idlelogout/{flag}", name="vacreq_idlelogout-saveorder")
     * @Template()
     */
    public function idlelogoutAction( Request $request, $flag = null )
    {
        //exit('vacreq: idlelogoutAction');
        return parent::idlelogoutAction($request,$flag);
    }


}

?>
