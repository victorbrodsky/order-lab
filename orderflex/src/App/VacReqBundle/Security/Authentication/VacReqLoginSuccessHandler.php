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

namespace App\VacReqBundle\Security\Authentication;

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



class VacReqLoginSuccessHandler extends LoginSuccessHandler {


    public function __construct( ContainerInterface $container, EntityManagerInterface $em )
    {
        parent::__construct($container,$em);

        $this->siteName = $container->getParameter('vacreq.sitename');
        $this->siteNameStr = 'Vacation Request System';
        $this->roleBanned = 'ROLE_VACREQ_BANNED';
        $this->roleUser = 'ROLE_VACREQ_USER';
        $this->roleUnapproved = 'ROLE_VACREQ_UNAPPROVED';
        $this->firewallName = 'ldap_vacreq_firewall';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        //return parent::onAuthenticationSuccess($request,$token);

        $redirectResponse = parent::onAuthenticationSuccess($request,$token);

        if( $this->secAuth->isGranted("ROLE_VACREQ_ADMIN") ) {
            return $redirectResponse;
        }

        $url = $redirectResponse->getTargetUrl();
        //echo "url=".$url."<br>";

        $em = $this->em;
        $user = $token->getUser();

        //check other user's vacreq roles
        //$user, $sitename, $rolePartialName, $institutionId=null
        $institutionId = null;
        $roles = $em->getRepository('AppUserdirectoryBundle:User')->
            findUserRolesBySiteAndPartialRoleName($user,'vacreq',"ROLE_VACREQ",$institutionId);
        //echo "roles count=".count($roles)."<br>";

        foreach( $roles as $role ) {
            $roleStr = $role."";
            $findStr = "_OBSERVER_";
            //echo "roleStr = ".$roleStr."; findStr=".$findStr."<br>";
            if( strpos($roleStr,$findStr) === false ) {
                //echo "The string $findStr was not found in the string $roleStr <br>";
                return $redirectResponse;
            } else {
                //echo "this is observer role!<br>";
            }
        }

        //if this is the only role the user has on the Vacation Request Site, be instantly redirected to the Away Calendar page
        if( $url != "/order/vacation-request/away-calendar/" ) {
            $redirectResponse->setTargetUrl("/order/vacation-request/away-calendar/");
        }

        return $redirectResponse;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return parent::onAuthenticationFailure($request,$exception);
    }

}