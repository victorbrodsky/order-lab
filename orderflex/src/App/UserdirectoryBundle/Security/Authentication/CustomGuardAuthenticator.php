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
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

//use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;


class CustomGuardAuthenticator extends AbstractFormLoginAuthenticator {

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

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        //return $request->headers->has('X-AUTH-TOKEN');
        //return $request->attributes->get('_route') === 'employees_login' && $request->isMethod('POST');

//        $route = $request->attributes->get('_route');
//        if( strpos($route, 'login') !== false && $request->isMethod('POST') ) {
//            return true;
//        }
//        return false;

        //dump($this->security->getUser());
        //exit('111');

//        if( $this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') ) {
//            return false;
//        }
//        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_USER') ) {
//            return true;
//        }

        //exit('supports: route='.$request->attributes->get('_route')."; method=".$request->isMethod('POST'));
        // GOOD behavior: only authenticate (i.e. return true) on a specific route
        //return 'employees_login' === $request->attributes->get('_route') && $request->isMethod('POST');

        $route = $request->attributes->get('_route');
        //echo '1 route='.$route."; Method=".$request->getMethod()."<br>";
        //exit('111');

        //No need for auth on main_common_home (list of the systems)
        if( $route == 'main_common_home' ) {
            return false;
        }

        //No need auth on login page with GET
        if( strpos($route, 'login') !== false ) {
            if( $request->isMethod('POST') ) {
                return true;
            }
            if( $request->isMethod('GET') ) {
                return false;
            }
        }

        // if there is already an authenticated user (likely due to the session)
        // then return false and skip authentication: there is no need.
        if( $this->security->getUser() ) {
            //echo 'User authenticated='.$this->security->getUser()."<br>";
            return false;
        }

        return false;
        //return true;

//        if( $route == 'employees_home' ) {
//            return false;
//        }
//        if( $route == 'fellapp_home' ) {
//            return false;
//        }
//        if( $route == 'vacreq_home' ) {
//            return false;
//        }
//        if( strpos($route, '_home') !== false ) {
//            return false;
//        }

//        if( $route == 'login' ) {
//            return true;
//        }

//        if( $route == 'login' && $request->isMethod('POST') ) {
//            return true;
//        }
//        if( $route == 'fellapp_login' && $request->isMethod('POST') ) {
//            return true;
//        }
//        if( $route == 'vacreq_login' && $request->isMethod('POST') ) {
//            return true;
//        }
//        if( strpos($route, 'login') !== false ) {
//            echo '2 route='.$route."; Method=".$request->getMethod()."<br>";
//            //exit('222');
//            //return true;
//            if( $request->isMethod('POST') ) {
//                return true;
//            }
//            return false;
//        }
//        if( strpos($route, 'login') !== false ) {
//            return true;
//        }

        // the user is not logged in, so the authenticator should continue
        //return true;
        //return false;
    }

    protected function getLoginUrl()
    {
        //exit('getLoginUrl');
        $url = $this->container->get('router')->generate('directory_login'); //employees_login
        return $url;
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $route = $request->attributes->get('_route');
        //echo '1 route='.$route."; Method=".$request->getMethod()."<br>";
        //echo 'sitename='.$this->sitename."<br>";
        //exit('111');

        $sitename = $this->getSiteName($route);

        $url = $this->container->get('router')->generate($sitename.'_login');

        return new RedirectResponse($url);
    }

    public function getSiteName($route) {

        //sitename is the first string before '_';
        $sitenameArr = explode('_',$route);

        return $sitenameArr[0];
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        //exit('getCredentials');
        //dump($request);

        $credentials = [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'usernametype' => $request->request->get('_usernametype'),
            'sitename' => $request->request->get('_sitename'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $this->sitename = $credentials['sitename'];

        //dump($credentials);

//        $request->getSession()->set(
//            Security::LAST_USERNAME,
//            $credentials['email']
//        );

        //exit('getCredentials');

        return $credentials;
    }

    public function getUserOrig($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if( !$this->csrfTokenManager->isTokenValid($token) ) {
            throw new InvalidCsrfTokenException();
        }

        $this->userProvider = $userProvider;

        $username = $credentials['username'];
        //echo "username=$username<br>";

        if( null === $username ) {
            return false;
        }

        //$logger = $this->container->get('logger');
        $authUtil = $this->container->get('authenticator_utility');

        //exit("before findUserByUsername");
        $user = $authUtil->findUserByUsername($username);

        if( !$user ) {
            //exit("findUserByUsername: no user found by username=".$username);
            //check if user existed in LDAP => create a new user
            //$user = $authUtil->createNewLdapUser($username);
            //$user = $authUtil->getUserInLdap($username);

            //Only for LDAP user: create user if not exists in DB and credentials are correct
            //If user has Use LdapAuthenticationByUsernamePassword($username, $password, $ldapType=1)
            $userSecUtil = $this->container->get('user_security_utility');
            //$usernameClean = $userSecUtil->createCleanUsername($username);
            $usernamePrefix = $userSecUtil->getUsernamePrefix($username);
            if( $usernamePrefix == "ldap-user" || $usernamePrefix == "ldap2-user" ) {
                $password = $credentials['password'];
                $user = $authUtil->LdapAuthenticationByUsernamePassword($username, $password, $ldapType=1);
            }
        }

        return $user;

        // if a User object, checkCredentials() is called
        //return $this->em->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
    }
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
//        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
//        if (!$this->csrfTokenManager->isTokenValid($token)) {
//            throw new InvalidCsrfTokenException();
//        }

        //Request $request, $username, $password, $providerKey
        $request = null;
        $username = $credentials['username'];
        $password = $credentials['password'];

        //_security.<your providerKey>.target_path (e.g. _security.main.target_path if the name of your firewall is main)
        $providerKey = 'ldap_employees_firewall';
        //$token = $this->createToken($request, $username, $password, $providerKey);
        //$token =  new UsernamePasswordToken($username, $password, $providerKey);
        $unauthenticatedToken = new UsernamePasswordToken(
            $username,
            $password,
            $providerKey
        );

        //exit("before checkCredentials");
        //TokenInterface $token, UserProviderInterface $userProvider, $providerKey
        //$userProvider = $this->userProvider;
        $usernamePasswordToken = $this->authenticateToken($unauthenticatedToken,null,$providerKey);
        if( $usernamePasswordToken ) {
            $this->passwordToken = $usernamePasswordToken;
            $user = $usernamePasswordToken->getUser();
            return $user;
        }

        $this->passwordToken = NULL;
        return NULL;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if( $this->passwordToken ) {
            return true;
        }

        return false;
    }

    public function checkCredentialsOrig($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        //return true;

        //$token = $credentials['token'];

        //Request $request, $username, $password, $providerKey
        $request = null;
        $username = $credentials['username'];
        $password = $credentials['password'];

        //_security.<your providerKey>.target_path (e.g. _security.main.target_path if the name of your firewall is main)
        $providerKey = 'ldap_employees_firewall';
        //$token = $this->createToken($request, $username, $password, $providerKey);
        //$token =  new UsernamePasswordToken($username, $password, $providerKey);
        $unauthenticatedToken = new UsernamePasswordToken(
            $username,
            $password,
            $providerKey
        );

        //exit("before checkCredentials");
        //TokenInterface $token, UserProviderInterface $userProvider, $providerKey
        //$userProvider = $this->userProvider;
        $usernamePasswordToken = $this->authenticateToken($unauthenticatedToken,null,$providerKey);
        if( $usernamePasswordToken ) {
            return true;
        }
        return false;

        //$this->LdapAuthenticationStr($username, $password, $ldapType=1)
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {

//        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
//            return new RedirectResponse($targetPath);
//        }
//        // For example : return new RedirectResponse($this->router->generate('some_route'));
//        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);

        //exit("onAuthenticationSuccess");
        // on success, let the request continue
        //return null;

        //$credentials = $this->getCredentials($request);

        //employees_authentication_handler
        //fellapp_authentication_handler
        //exit("sitename=".$this->sitename);
        $authenticationSuccess = $this->container->get($this->sitename.'_authentication_handler');

        //onAuthenticationSuccess(Request $request, TokenInterface $token)
        return $authenticationSuccess->onAuthenticationSuccess($request,$token);

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        //exit("onAuthenticationFailure");

//        $data = [
//            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
//
//            // or to translate this message
//            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
//        ];
//
//        return new JsonResponse($data, Response::HTTP_FORBIDDEN);


        //$credentials = $this->getCredentials($request);

        //employees_authentication_handler
        $authenticationSuccess = $this->container->get('employees_authentication_handler');

        return $authenticationSuccess->onAuthenticationFailure($request,$exception);

    }

    public function supportsRememberMe()
    {
        return true;
    }



    //public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    public function authenticateToken($token, $userProvider, $providerKey)
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

//    public function getLoginUrl() {
//        exit('222');
//        return $this->router->generate('directory_testlogin');
//    }

} 