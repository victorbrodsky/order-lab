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
 * Date: 10/7/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace App\CrnBundle\Security\Authentication;

use App\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;



class CrnLoginSuccessHandler extends LoginSuccessHandler {


    public function __construct( ContainerInterface $container, EntityManagerInterface $em, Security $security )
    {
        parent::__construct($container,$em,$security);

        $this->siteName = $container->getParameter('crn.sitename');
        $this->siteNameStr = 'Critical Result Notification';
        $this->roleBanned = 'ROLE_CRN_BANNED';
        $this->roleUser = 'ROLE_CRN_USER';
        $this->roleUnapproved = 'ROLE_CRN_UNAPPROVED';
        $this->firewallName = 'ldap_crn_firewall';
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