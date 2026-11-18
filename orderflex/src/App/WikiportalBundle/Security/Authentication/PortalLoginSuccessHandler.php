<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\WikiportalBundle\Security\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;


class PortalLoginSuccessHandler extends LoginSuccessHandler {


    public function __construct( ContainerInterface $container, EntityManagerInterface $em, Security $security )
    {
        parent::__construct($container,$em,$security);

        $this->siteName = $container->getParameter('wikiportal.sitename');
        $this->siteNameStr = 'Portal';
        $this->roleBanned = 'ROLE_WIKIPORTAL_BANNED';
        $this->roleUser = 'ROLE_WIKIPORTAL_USER';
        $this->roleUnapproved = 'ROLE_WIKIPORTAL_UNAPPROVED';
        $this->firewallName = 'ldap_wikiportal_firewall';
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

?>
