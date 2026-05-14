<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\CtpSporeBundle\Security\Authentication;

use App\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class SporeLoginSuccessHandler extends LoginSuccessHandler {

    public function __construct( ContainerInterface $container, EntityManagerInterface $em, Security $security )
    {
        parent::__construct($container,$em,$security);

        $this->siteName = $container->getParameter('spore.sitename');
        $this->siteNameStr = 'Prostate Cancer Research Data Explorer';
        $this->roleBanned = 'ROLE_SPORE_BANNED';
        $this->roleUser = 'ROLE_SPORE_USER';
        $this->roleUnapproved = 'ROLE_SPORE_UNAPPROVED';
        $this->firewallName = 'ldap_spore_firewall';
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
