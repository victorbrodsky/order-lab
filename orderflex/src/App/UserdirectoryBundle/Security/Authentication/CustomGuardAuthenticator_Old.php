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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

//use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;


//excluded in Authentication in services.yaml
//Deprecated since Symfony 5.2. Use Passport

class CustomGuardAuthenticator extends AbstractFormLoginAuthenticator #AbstractAuthenticator #AbstractFormLoginAuthenticator {
{
    private $encoder;
    private $container;
    private $em;
    private $security;
    private $csrfTokenManager;
    private $sitename;
    private $userProvider;
    private $passwordToken;

    public function __construct(UserPasswordEncoderInterface $encoder, ContainerInterface $container, EntityManagerInterface $em, Security $security=null, CsrfTokenManagerInterface $csrfTokenManager=null)
    {
        $this->encoder = $encoder;
        $this->container = $container;                //Service Container
        $this->em = $em;                //Entity Manager
        $this->security = $security;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordToken = NULL;
    }

//    public function __construct(UserPasswordHasherInterface $encoder, ContainerInterface $container, EntityManagerInterface $em, Security $security=null, CsrfTokenManagerInterface $csrfTokenManager=null)
//    {
//        $this->encoder = $encoder;
//        $this->container = $container;                //Service Container
//        $this->em = $em;                //Entity Manager
//        $this->security = $security;
//        $this->csrfTokenManager = $csrfTokenManager;
//        $this->passwordToken = NULL;
//    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request) : bool
    {
        // GOOD behavior: only authenticate (i.e. return true) on a specific route
        //return 'employees_login' === $request->attributes->get('_route') && $request->isMethod('POST');

        $route = $request->attributes->get('_route');
        //echo '1 route='.$route."; Method=".$request->getMethod()."<br>";
        //exit('111');

        //No need for auth on main_common_home (list of the systems)
        if( $route == 'main_common_home' ) {
            return false;
        }

        if( $route == 'setserveractive' ) {
            return false;
        }
        if( $route == 'keepalive' ) {
            return false;
        }
        if( $route == 'getmaxidletime' ) {
            return false;
        }
//        if( $route == 'main_maintenance' ) {
//            return false;
//        }

        //No need auth on login page with GET
        if( strpos((string)$route, 'login') !== false ) {
            if( $request->isMethod('POST') ) {
                //exit('true');
                return true;
            }
//            if( $request->isMethod('GET') ) {
//                return false;
//            }
        }

        // if there is already an authenticated user (likely due to the session)
        // then return false and skip authentication: there is no need.
        if( $this->security->getUser() ) {
            //echo 'User authenticated='.$this->security->getUser()."<br>";
            return false;
        }

        return false;
    }

    protected function getLoginUrl() : string
    {
        $url = $this->container->get('router')->generate('directory_login'); //employees_login
        return $url;
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null) : RedirectResponse
    {
        $route = $request->attributes->get('_route');
        //echo '1 route='.$route."; Method=".$request->getMethod()."<br>";
        //echo 'sitename='.$this->sitename."<br>";
        //exit('111');

        $url = NULL;

        if(
            $route == 'setserveractive' ||
            $route == 'keepalive' ||
            $route == 'getmaxidletime'
        ) {
            $url = $this->container->get('router')->generate($route);
        }

        if( !$url && $route == 'main_maintenance' ) {
            $url = $this->container->get('router')->generate('main_maintenance');
        }

        if( !$url ) {
            $sitename = $this->getSiteNameByRoute($route);
            $url = $this->container->get('router')->generate($sitename . '_login');
        }

        return new RedirectResponse($url);
    }

    public function getSiteNameByRoute($route) : string
    {
        //sitename is the first string before '_';
        //$sitenameArr = explode('_',$route);
        //return $sitenameArr[0];

        //echo "route=$route <br>";
        //exit('111');

        if( strpos((string)$route,'translationalresearch') !== false ) {
            return "translationalresearch";
        }
        if( strpos((string)$route,'vacreq') !== false ) {
            return "vacreq";
        }
        if( strpos((string)$route,'calllog') !== false ) {
            return "calllog";
        }
        if( strpos((string)$route,'crn') !== false ) {
            return "crn";
        }

        if( strpos((string)$route,'fellapp') !== false ) {
            return "fellapp";
        }
        if( strpos((string)$route,'resapp') !== false ) {
            return "resapp";
        }

        if( strpos((string)$route,'employees') !== false ) {
            return "employees";
        }

        if( strpos((string)$route,'user') !== false ) {
            return "employees";
        }

        if( strpos((string)$route,'deidentifier') !== false ) {
            return "deidentifier";
        }
        if( strpos((string)$route,'scan') !== false ) {
            return "scan";
        }
        if( strpos((string)$route,'dashboard') !== false ) {
            return "dashboard";
        }

        //get first element before '_'
        if( strpos((string)$route,'_') !== false ) {
            $routeArr = explode('_',$route);
            if( count($routeArr) > 0 ) {
                return $routeArr[0];
            }
        }

        return "employees";
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request) : mixed
    {

        //dump($request->request);

        $credentials = [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'usernametype' => $request->request->get('_usernametype'),
            'sitename' => $request->request->get('_sitename'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $this->sitename = $credentials['sitename'];

        //dump($credentials);
        //exit('111');

        return $credentials;
    }

    //getUser is replaced by checkCredentials: it authenticate the user and set passwordToken if success,
    // if LDAP user exists in LDAP but not in the system => authenticate and create LDAP user
    public function getUser($credentials, UserProviderInterface $userProvider) : mixed
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        //Request $request, $username, $password, $providerKey
        $request = null;
        $username = $credentials['username'];
        $password = $credentials['password'];

        //_security.<your providerKey>.target_path (e.g. _security.main.target_path if the name of your firewall is main)
        $providerKey = 'ldap_employees_firewall';
        $unauthenticatedToken = new UsernamePasswordToken(
            $username,
            $password,
            $providerKey
        );

        $usernamePasswordToken = $this->authenticateToken($unauthenticatedToken,$providerKey);
        if( $usernamePasswordToken ) {
            $this->passwordToken = $usernamePasswordToken;
            $user = $usernamePasswordToken->getUser();
            return $user;
        }

        $this->passwordToken = NULL;
        return NULL;
    }
//    public function getUserOrig($credentials, UserProviderInterface $userProvider)
//    {
//        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
//        if( !$this->csrfTokenManager->isTokenValid($token) ) {
//            throw new InvalidCsrfTokenException();
//        }
//
//        $this->userProvider = $userProvider;
//        $username = $credentials['username'];
//
//        if( null === $username ) {
//            return false;
//        }
//
//        $authUtil = $this->container->get('authenticator_utility');
//        $user = $authUtil->findUserByUsername($username);
//
//        return $user;
//    }

    //Just check if passwordToken is set by getUser()
    public function checkCredentials($credentials, UserInterface $user) : bool
    {
        if( $this->passwordToken ) {
            return true;
        }

        return false;
    }
//    public function checkCredentialsOrig($credentials, UserInterface $user)
//    {
//        //Request $request, $username, $password, $providerKey
//        $request = null;
//        $username = $credentials['username'];
//        $password = $credentials['password'];
//        $providerKey = 'ldap_employees_firewall'; //_security.<your providerKey>.target_path (e.g. _security.main.target_path if the name of your firewall is main)
//        $unauthenticatedToken = new UsernamePasswordToken(
//            $username,
//            $password,
//            $providerKey
//        );
//        $usernamePasswordToken = $this->authenticateToken($unauthenticatedToken,$providerKey);
//        if( $usernamePasswordToken ) {
//            return true;
//        }
//        return false;
//    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) : Response
    {
        $authenticationSuccess = $this->container->get($this->sitename.'_authentication_handler');
        return $authenticationSuccess->onAuthenticationSuccess($request,$token);
    }
    
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) : Response
    {
        $authenticationSuccess = $this->container->get('employees_authentication_handler');
        return $authenticationSuccess->onAuthenticationFailure($request,$exception);
    }

    public function supportsRememberMe() : bool
    {
        return true;
    }



    //public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    public function authenticateToken($token, $providerKey)
    {
        //echo "CustomGuardAuthenticator: username=".$token->getUsername()."<br>"; //", pwd=".$token->getCredentials()
        //exit();

        $userSecUtil = $this->container->get('user_security_utility');

        if( $token->getCredentials() ) {
            //ok
        } else {
            $logger = $this->container->get('logger');
            $logger->error("authenticate Token: no credentials");
            throw new AuthenticationException('Invalid username or password');
        }

        //$authUtil = new AuthUtil($this->container,$this->em);
        $authUtil = $this->container->get('authenticator_utility');

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
                $user = $authUtil->LdapAuthentication($token, $ldapType = 1);

                if( !$user && $userSecUtil->getSiteSettingParameter('ldapAll') ) {
                    $user = $authUtil->LdapAuthentication($token, $ldapType = 2);
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
                $user = $authUtil->LdapAuthentication($token, $ldapType = 2);

                if( !$user && $userSecUtil->getSiteSettingParameter('ldapAll') ) {
                    $user = $authUtil->LdapAuthentication($token, $ldapType = 1);
                }
                ////////////////////EOF ldap authentication ////////////////////
                break;

            case "local-user":
                //////////////////////////////////////////////////////////////////////
                //                       1) local authentication                   //
                //////////////////////////////////////////////////////////////////////
                $user = $authUtil->LocalAuthentication($token);
                ////////////////////EOF first local authentication //////////////////
                break;


            case "external":
                //////////////////////////////////////////////////////////////////////
                //                       2) pacsvendor authentication                   //
                //////////////////////////////////////////////////////////////////////
                $user = $authUtil->PacsvendorAuthentication($token);
                ////////////////////EOF pacsvendor authentication //////////////////
                break;

            case "local2-user":
                //////////////////////////////////////////////////////////////////////
                //                       4) External IDs                            //
                //////////////////////////////////////////////////////////////////////
                $user = $authUtil->identifierAuthentication($token);
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
//    public function createToken(PassportInterface $passport, string $firewallName): TokenInterface
//    {
//        return new UsernamePasswordToken($passport->getUser(), $passport->getToken(), $providerKey);
//        //return new CustomOauthToken($passport->getUser(), $passport->getAttribute('scope'));
//    }



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