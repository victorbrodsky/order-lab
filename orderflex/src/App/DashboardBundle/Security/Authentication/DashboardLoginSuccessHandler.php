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

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/8/2021
 * Time: 12:12 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\DashboardBundle\Security\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;


class DashboardLoginSuccessHandler extends LoginSuccessHandler {

    public function __construct( ContainerInterface $container, EntityManagerInterface $em )
    {
        parent::__construct($container,$em);

        $this->siteName = $container->getParameter('dashboard.sitename');
        $this->siteNameStr = 'Dashboards';
        $this->roleBanned = 'ROLE_DASHBOARD_BANNED';
        $this->roleUser = 'ROLE_DASHBOARD_USER';
        $this->roleUnapproved = 'ROLE_DASHBOARD_UNAPPROVED';
        $this->firewallName = 'ldap_dashboard_firewall';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        return parent::onAuthenticationSuccess($request,$token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return parent::onAuthenticationFailure($request,$exception);
    }

}