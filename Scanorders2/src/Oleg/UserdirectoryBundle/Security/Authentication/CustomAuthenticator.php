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
 * Created by PhpStorm.
 * User: DevServer
 * Date: 3/4/15
 * Time: 12:23 PM
 */

namespace Oleg\UserdirectoryBundle\Security\Authentication;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class CustomAuthenticator implements SimpleFormAuthenticatorInterface {

    private $encoder;
    private $container;
    private $em;

    public function __construct(UserPasswordEncoderInterface $encoder,$container,$em)
    {
        $this->encoder = $encoder;
        $this->container = $container;                //Service Container
        $this->em = $em;                //Entity Manager
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        //echo "CustomAuthenticator: username=".$token->getUsername()."<br>"; //", pwd=".$token->getCredentials()
        //exit();

        $userSecUtil = $this->container->get('user_security_utility');

        if( $token->getCredentials() ) {
            //ok
        } else {
            $logger = $this->container->get('logger');
            $logger->error("authenticate Token: no credentials");
            throw new AuthenticationException('Invalid username or password');
        }

        $authUtil = new AuthUtil($this->container,$this->em);

        $user = null;

        //auth type: ldap-user, local-user, external
        $usernamePrefix = $userSecUtil->getUsernamePrefix($token->getUsername());

        switch( $usernamePrefix ) {
            case "local-user":
                //////////////////////////////////////////////////////////////////////
                //                       1) local authentication                   //
                //////////////////////////////////////////////////////////////////////
                $user = $authUtil->LocalAuthentication($token, $userProvider);
                ////////////////////EOF first local authentication //////////////////
                break;


            case "external":
                //////////////////////////////////////////////////////////////////////
                //                       2) pacsvendor authentication                   //
                //////////////////////////////////////////////////////////////////////
                $user = $authUtil->PacsvendorAuthentication($token, $userProvider);
                ////////////////////EOF pacsvendor authentication //////////////////
                break;


            case "wcmc-cwid": //use for auth transition. Remove after transition.
            case "ldap-user":
                //////////////////////////////////////////////////////////////////////
                //                       3) ldap authentication                     //
                //////////////////////////////////////////////////////////////////////
                $user = $authUtil->LdapAuthentication($token, $userProvider, $ldapType = 1);

                if( !$user && $userSecUtil->getSiteSettingParameter('ldapAll') ) {
                    $user = $authUtil->LdapAuthentication($token, $userProvider, $ldapType = 2);
                }
                ////////////////////EOF ldap authentication ////////////////////
                break;

            case "ldap2-user":
                //////////////////////////////////////////////////////////////////////
                //                       3) ldap authentication                     //
                //////////////////////////////////////////////////////////////////////
                $user = $authUtil->LdapAuthentication($token, $userProvider, $ldapType = 2);

                if( !$user && $userSecUtil->getSiteSettingParameter('ldapAll') ) {
                    $user = $authUtil->LdapAuthentication($token, $userProvider, $ldapType = 1);
                }
                ////////////////////EOF ldap authentication ////////////////////
                break;


            case "local-user":
                //////////////////////////////////////////////////////////////////////
                //                       4) External IDs                            //
                //////////////////////////////////////////////////////////////////////
                $user = $authUtil->identifierAuthentication($token, $userProvider);
                ////////////////////EOF External IDs authentication //////////////////
                break;

            default:
                throw new AuthenticationException('Invalid username or password');

        }

        if ($user) {
            $this->resetFailedAttemptCounter($user);
            return $this->getUsernamePasswordToken($user, $providerKey);
        }

        //exit('all failed');
        throw new AuthenticationException('Invalid username or password');
    }



    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken
        && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }



    public function getUsernamePasswordToken($user,$providerKey) {
        return new UsernamePasswordToken(
            $user,
            NULL,   //$user->getPassword(),
            $providerKey,
            $user->getRoles()
        );
    }

    public function resetFailedAttemptCounter($user) {
        $user->resetFailedAttemptCounter(); //no need to flush. User will be updated by auth.
    }


} 