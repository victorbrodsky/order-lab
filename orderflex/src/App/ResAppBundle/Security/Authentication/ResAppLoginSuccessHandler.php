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

namespace App\ResAppBundle\Security\Authentication;

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


class ResAppLoginSuccessHandler extends LoginSuccessHandler {

    public function __construct( ContainerInterface $container, EntityManagerInterface $em )
    {
        parent::__construct($container,$em);

        $this->siteName = $container->getParameter('resapp.sitename');
        $this->siteNameStr = 'Residency Applications';
        $this->roleBanned = 'ROLE_RESAPP_BANNED';
        $this->roleUser = 'ROLE_RESAPP_USER';
        $this->roleUnapproved = 'ROLE_RESAPP_UNAPPROVED';
        $this->firewallName = 'ldap_resapp_firewall';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        $redirectResponse = parent::onAuthenticationSuccess($request,$token);

        if( $this->secAuth->isGranted('ROLE_RESAPP_ADMIN') ) {
            return $redirectResponse;
        }

        $url = $redirectResponse->getTargetUrl();
        //echo "url=".$url."<br>";

        //If "Residency Interviewer" is the highest role of the user logging into the residency site,
        // automatically redirect them to the /my-interviewees page after login
        // UNLESS they came to the site via a link from an email to evaluate a specific candidate already.
        //$subdomain = "/order";
        $subdomain = "";
        if( $url == $subdomain."/residency-applications/" &&
            $url != $subdomain."/residency-applications/interview-evaluation/" &&
            $url != $subdomain."/residency-applications/application-evaluation/"
        ) {

            if( $this->isResidencyInterviewerHighestRole($token->getUser()) ) {
                $redirectResponse->setTargetUrl($subdomain."/residency-applications/my-interviewees/");
            }

        }

        return $redirectResponse;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return parent::onAuthenticationFailure($request,$exception);
    }

    //check "Residency Interviewer" is the highest role into the residency site
    public function isResidencyInterviewerHighestRole($user) {

        //1) check if the user has role "Residency Interviewer" with permission: object="Interview", action="create"
        $resappRoles = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserRolesByObjectAction( $user, "Interview", "create" );

        $level = 0;
        foreach( $resappRoles as $resappRole ) {
            if( $resappRole->getLevel() > $level ) {
                $level = $resappRole->getLevel();
            }
        }

        //2) check if the "Residency Interviewer" level is the highest among all roles
        //echo "level1=".$level."<br>";
        //exit("level2=".$this->getHighestRoleLevel($user));
        if( $level >= $this->getHighestRoleLevel($user) ) {
            return true;
        }

        return false;
    }

    public function getHighestRoleLevel($user) {
        $level = 0;
        foreach( $user->getRoles() as $roleName ) {
            $role = $this->em->getRepository('AppUserdirectoryBundle:Roles')->findOneByName($roleName);
            //echo "role=".$role."<br>";
            if( $role && $role->getLevel() > $level ) {
                $level = $role->getLevel();
            }
        }

        return $level;
    }
}