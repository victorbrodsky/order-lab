<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\CtpSporeBundle\Controller;

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

class SporeSecurityController extends SecurityController
{
    #[Route(path: '/login', name: 'ctpspore_login')]
    public function loginAction( Request $request, AuthenticationUtils $authenticationUtils ) {
        return parent::loginAction($request,$authenticationUtils);
    }
    public function getLoginTwig() {
        return 'AppCtpBundle/Security/login.html.twig';
    }

    #[Route(path: '/setloginvisit/', name: 'ctpspore_setloginvisit', methods: ['GET'])]
    public function setAjaxLoginVisit( Request $request )
    {
        return parent::setAjaxLoginVisit($request);
    }

    #[Route(path: '/no-permission', name: 'ctpspore-nopermission', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Security/nopermission.html.twig')]
    public function actionNoPermission( Request $request )
    {
        $empty = $request->get('empty');
        return array(
            'sitename' => $this->getParameter('spore.sitename'),
            'empty' => $empty
        );
    }

    #[Route(path: '/idle-log-out', name: 'ctpspore_idlelogout')]
    #[Route(path: '/idle-log-out/{flag}', name: 'ctpspore_idlelogout-saveorder')]
    public function idlelogoutAction( Request $request, $flag = null )
    {
        return parent::idlelogoutAction($request,$flag);
    }
}
