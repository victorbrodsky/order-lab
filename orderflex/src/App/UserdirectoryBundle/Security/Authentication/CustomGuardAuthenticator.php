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

namespace App\UserdirectoryBundle\Security\Authentication;


//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;
//use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
//use Symfony\Component\Security\Core\Exception\AuthenticationException;
//use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
//use Symfony\Component\Security\Core\User\UserInterface;
//use Symfony\Component\Security\Core\User\UserProviderInterface;
//use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Security;


class CustomGuardAuthenticator extends AbstractGuardAuthenticator {

    private $encoder;
    private $container;
    private $em;
    private $security;

    public function __construct(UserPasswordEncoderInterface $encoder, ContainerInterface $container, EntityManagerInterface $em, Security $security=null)
    {
        $this->encoder = $encoder;
        $this->container = $container;                //Service Container
        $this->em = $em;                //Entity Manager
        $this->security = $security;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        //return $request->headers->has('X-AUTH-TOKEN');

        // if there is already an authenticated user (likely due to the session)
        // then return false and skip authentication: there is no need.
//        if( $this->security->getUser() ) {
//            return false;
//        }
        //exit('111');
        // GOOD behavior: only authenticate (i.e. return true) on a specific route
        return 'employees_login' === $request->attributes->get('_route') && $request->isMethod('POST');

        // the user is not logged in, so the authenticator should continue
        //return true;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        //dump($request);

        $credentials = [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'usernametype' => $request->request->get('_usernametype'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        //dump($credentials);

//        $request->getSession()->set(
//            Security::LAST_USERNAME,
//            $credentials['email']
//        );

        exit('111');

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $username = $credentials['username'];
        echo "username=$username<br>";

        if (null === $username) {
            return;
        }

        //exit("before findUserByUsername");
        return $this->findUserByUsername($username);

        // if a User object, checkCredentials() is called
        //return $this->em->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;

        $token = $credentials['token'];
        //exit("before checkCredentials");
        $this->authenticateToken($token,null,null);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        exit("before onAuthenticationFailure");
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return true;
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

        //Default user type is 'local-user'
        if( !$usernamePrefix ) {
            $usernamePrefix = 'local-user';
            $token->setUser($token->getUsername()."_@_".$usernamePrefix);
        }

        //exit("usernamePrefix=".$usernamePrefix);

        switch( $usernamePrefix ) {

            //case "wcmc-cwid": //use for auth transition. Remove after transition.
            case "ldap-user":
                //////////////////////////////////////////////////////////////////////
                //                       3) ldap authentication                     //
                //////////////////////////////////////////////////////////////////////
                $user = $authUtil->LdapAuthentication($token, $userProvider, $ldapType = 1);

                if( !$user && $userSecUtil->getSiteSettingParameter('ldapAll') ) {
                    $user = $authUtil->LdapAuthentication($token, $userProvider, $ldapType = 2);
                }

                if( !$user ) {
                    //Try to use user's credential authentication under Credentials->Identifiers-> identifier type "Local User"
                    //This identifier must have status "Verified by Administrator" and checked "Identifier enables system/service access" checkbox
                    $user = $authUtil->simpleIdentifierAuthetication($token);
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

            case "local2-user":
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