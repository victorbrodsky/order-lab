<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\RegulatorytBundle\Security\Authentication;

use App\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class RegulatorytLoginSuccessHandler extends LoginSuccessHandler {

    public function __construct( ContainerInterface $container, EntityManagerInterface $em, Security $security )
    {
        parent::__construct($container,$em,$security);

        $this->siteName = $container->getParameter('regulatoryt.sitename');
        $this->siteNameStr = 'Regulatory Templates';
        $this->roleBanned = 'ROLE_REGULATORYT_BANNED';
        $this->roleUser = 'ROLE_REGULATORYT_USER';
        $this->roleUnapproved = 'ROLE_REGULATORYT_UNAPPROVED';
        $this->firewallName = 'ldap_regulatoryt_firewall';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) : Response
    {
        return parent::onAuthenticationSuccess($request,$token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) : Response
    {
        return parent::onAuthenticationFailure($request,$exception);
    }
}
