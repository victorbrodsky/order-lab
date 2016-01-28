<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/7/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\DeidentifierBundle\Security\Authentication;

use Oleg\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

use Oleg\UserdirectoryBundle\Util\UserUtil;


class DeidentifierLoginSuccessHandler extends LoginSuccessHandler {


    public function __construct( $container, SecurityContext $security, $em )
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->security = $security;
        $this->em = $em;
        $this->siteName = $container->getParameter('deidentifier.sitename');
        $this->siteNameStr = 'Deidentifier System';
        $this->roleBanned = 'ROLE_DEIDENTIFICATOR_BANNED';
        $this->roleUser = 'ROLE_DEIDENTIFICATOR_USER';
        $this->roleUnapproved = 'ROLE_DEIDENTIFICATOR_UNAPPROVED';
        $this->firewallName = 'ldap_deidentifier_firewall';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

//        $user = $token->getUser();
//        echo "user ok=".$user."<br>";
//        print_r($user->getRoles());
//        exit("auth ok");

        return parent::onAuthenticationSuccess($request,$token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        //exit("auth failed");
        return parent::onAuthenticationFailure($request,$exception);
    }

}