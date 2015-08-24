<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/7/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\FellAppBundle\Security\Authentication;

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


class FellAppLoginSuccessHandler extends LoginSuccessHandler {

//    protected $container;
//    protected $security;
//    protected $em;
//    protected $router;
//    protected $siteName;
//    protected $siteNameStr;
//    protected $roleBanned;
//    protected $roleUser;
//    protected $roleUnapproved;

    public function __construct( $container, SecurityContext $security, $em )
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->security = $security;
        $this->em = $em;
        $this->siteName = $container->getParameter('fellapp.sitename');
        $this->siteNameStr = 'Fellowship Applications';
        $this->roleBanned = 'ROLE_FELLAPP_BANNED';
        $this->roleUser = 'ROLE_FELLAPP_USER';
        $this->roleUnapproved = 'ROLE_FELLAPP_UNAPPROVED';
        $this->firewallName = 'ldap_fellapp_firewall';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        return parent::onAuthenticationSuccess($request,$token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return parent::onAuthenticationFailure($request,$exception);
    }

}