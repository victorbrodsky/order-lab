<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\CtpRegulatorytBundle\Controller;

use App\UserdirectoryBundle\Controller\SecurityController;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use App\UserdirectoryBundle\Util\UserUtil;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegulatorytSecurityController extends SecurityController
{
    #[Route(path: '/login', name: 'regulatoryt_login')]
    public function loginAction( Request $request, AuthenticationUtils $authenticationUtils ) {
        return parent::loginAction($request,$authenticationUtils);
    }
    public function getLoginTwig() {
        return 'AppCtpBundle/Security/login.html.twig';
    }

    #[Route(path: '/setloginvisit/', name: 'regulatoryt_setloginvisit', methods: ['GET'])]
    public function setAjaxLoginVisit( Request $request )
    {
        return parent::setAjaxLoginVisit($request);
    }

    #[Route(path: '/no-permission', name: 'regulatoryt-nopermission', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Security/nopermission.html.twig')]
    public function actionNoPermission( Request $request )
    {
        $empty = $request->get('empty');
        return array(
            'sitename' => $this->getParameter('regulatoryt.sitename'),
            'empty' => $empty
        );
    }

    #[Route(path: '/idle-log-out', name: 'regulatoryt_idlelogout')]
    #[Route(path: '/idle-log-out/{flag}', name: 'regulatoryt_idlelogout-saveorder')]
    public function idlelogoutAction( Request $request, $flag = null )
    {
        return parent::idlelogoutAction($request,$flag);
    }
}
