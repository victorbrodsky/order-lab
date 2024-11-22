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
 * User: Oleg Ivanov
 * Date: 3/4/15
 * Time: 12:23 PM
 */

namespace App\UserdirectoryBundle\Security\Authentication;


//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;
//use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
//use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Saml\Util\SamlConfigProvider;
use App\UserdirectoryBundle\Entity\User;
use OneLogin\Saml2\Auth;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
//use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
//use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

//use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
//use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;


class CustomGuardAuthenticator extends AbstractAuthenticator
{
    //private $encoder;
    private $container;
    private $em;
    private $security;
    private $csrfTokenManager;
    private $sitename;
    private $userProvider;
    private $passwordToken;
    //private $credentials;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        Security $security=null,
        CsrfTokenManagerInterface $csrfTokenManager=null
    )
    {
        //$this->encoder = $encoder;
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
    public function supports(Request $request) : bool
    {
        //$logger = $this->container->get('logger');

        // GOOD behavior: only authenticate (i.e. return true) on a specific route
        //return 'employees_login' === $request->attributes->get('_route') && $request->isMethod('POST');

        $route = $request->attributes->get('_route');
        //$logger->notice("supports: route=".$route);
        //echo '1 route='.$route."; Method=".$request->getMethod()."<br>";
        //exit('exit support');

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
                //exit('supports: true');
                //$logger->notice("supports: Yes. route=".$route);
                return true;
            }
//            if( $request->isMethod('GET') ) {
//                return false;
//            }
        }

        //SAML authentication
        if( $route == 'saml_acs_default' ) {
            return true;
        }

        // if there is already an authenticated user (likely due to the session)
        // then return false and skip authentication: there is no need.
        if( $this->security->getUser() ) {
            //echo 'User authenticated='.$this->security->getUser()."<br>";
            //$logger->notice("supports: Not. User exists");
            return false;
        }

        //$logger->notice("supports: Not. EOF");
        return false;
    }

    //Authentication Diagram: https://symfony.com/doc/6.0/security.html#authentication-events
    /**
     * Create a passport for the current request.
     *
     * The passport contains the user, credentials and any additional information
     * that has to be checked by the Symfony Security system. For example, a login
     * form authenticator will probably return a passport containing the user, the
     * presented password and the CSRF token value.
     *
     * You may throw any AuthenticationException in this method in case of error (e.g.
     * a UserNotFoundException when the user cannot be found).
     *
     * @throws AuthenticationException
     */
    public function authenticate(Request $request) : Passport
    {
        //dump($request->request);
        //exit('authenticate');
        $logger = $this->container->get('logger');

        ///////// Switch DB according to the locale ////////
        //Switch DB must be done on every request: MultiDbConnectionWrapper->__construct
        //https://stackoverflow.com/questions/53151669/symfony-change-database-dynamically
        //https://stackoverflow.com/questions/65902878/dynamic-doctrine-database-connection
//        if( 0 ) {
//            $locale = $request->getLocale();
//            if( $locale == 'c/lmh/pathology' ) {
//                $connection = $this->em->getConnection();
//                $dbName = 'Tenant2';
//                //echo "set connection=".$dbName.'<br>';
//                $connection->selectDatabase($dbName);
//                //echo 'authenticate dbName='.$connection->getDatabase()."<br>";
//                //exit('dbName='.$connection->getDatabase());
//            }
////            $locale = $request->getLocale();
////            $session = $request->getSession();
////            $sessionLocale = $session->get('locale');
////            echo "locale=" . $locale . ', sessionLocale=' . $sessionLocale . "<br>";
////            $connection = $this->em->getConnection();
////            //$connection = $this->connection;
////            //$params = $connection->getParams();
////            $connection->selectDatabase('Tenant2');
////            //dump($params);
////            //exit('111');
//        }

        //$userSecUtil = $this->container->get('user_security_utility');
        //$userSecUtil->switchDb();
        ///////// EOF Switch DB according to the locale ////////

        $credentials = $this->getCredentials($request);

        //dump($credentials);
        //exit('111');

        $username = "N/A";
        if( isset($credentials['username']) ) {
            $username = $credentials['username'];
        }
        $usernametype = "N/A";
        if( isset($credentials['usernametype']) ) {
            $usernametype = $credentials['usernametype'];
        }
        $sitename = "N/A";
        if( isset($credentials['sitename']) ) {
            $sitename = $credentials['sitename'];
        }

        $connection = $this->em->getConnection();
        $uri = $request->getUri();
        $logger->notice(
            "authenticate: ".
            " DB=".$connection->getDatabase().", uri=".$uri .
            " login attempt username=[$username],".
            " usernametype=[$usernametype], sitename=[$sitename], "

        );

        //$connection = $this->em->getConnection();
        //$currentDb = $connection->getDatabase();
        //$logger->notice('authenticate: currentDb='.$currentDb);
        //exit('before new Passport: usernametype='.$usernametype);

//        //SAML 1: redirect login request to 'saml_login'
//        if( 1 && $usernametype == 'saml-sso' ) {
//            $route = $request->attributes->get('_route');
//            if( strpos((string)$route, 'login') !== false ) {
//                if( $request->isMethod('POST') ) {
//                    return new RedirectResponse( $this->router->generate('saml_login',array('client'=>)) );
//                }
//            }
//        }

        $route = $request->attributes->get('_route');
        $logger->notice('authenticate: $route=['.$route."]");
        //if( $route == 'saml_acs_default' ) {
        //    exit('authenticate: saml_acs_default');
        //}

        if( $route == 'saml_acs_default' ) {
            $logger->notice('authenticate: saml_acs_default: $username=['.$username."]");

            //TODO: For SAML, login form is not used and all credentials are empty
            if( 0 && $username == 'N/A' ) {
                dump($request);
                $samlResponse = $request->getPayload()->get('SAMLResponse');
                $relayState = $request->getPayload()->get('RelayState');
                echo 'relayState='.$relayState."<br>";
                dump($samlResponse);

                //$relayState: http://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu/employees
                if( str_contains($relayState,'/login/')) {
                    //$client = (string) substr($somestring, strrpos("/$somestring", '/'));
                    $parts = explode('/', $relayState);
                    $sitename = array_pop($parts);
                    $client = array_pop($parts);

                    $credentials['username'] = $client;
                    $this->sitename = $sitename;
                }
                
                exit('after dump request');
            }

        //if( 0 && $usernametype == 'saml-sso' ) {
            //$authUtil = $this->container->get('authenticator_utility');
            //username = username=oli2002l_@_local-user
            //$user = $authUtil->findUserByUsername($username);
            //$userManager = $this->container->get('user_manager');
            //$user = $userManager->findUserByEmail($usernametype);
            //$user = $this->getAuthUser($credentials);
            //$username = str_replace('_@_saml-sso','',$username);
            //convert to lower case
            $username = strtolower($username); //username is entered email
            //echo 'before new Passport: username='.$username."<br>";
            //exit('samlAuthentication');

//            $authenticationSuccess = $this->container->get($this->sitename.'_authentication_handler');
//            $firewallName = $authenticationSuccess->getFirewallName();
//
//            //testing: https://view.online/c/wcm/pathology/directory/event-log/
//            //http://127.0.0.1/translational-research/request/fee-schedule
//            //https://view.online/c/wcm/pathology/translational-research/request/fee-schedule
//            //$firewallName = 'ldap_employees_firewall';
//            $indexLastRoute = '_security.'.$firewallName.'.target_path';
//            $lastRoute = $request->getSession()->get($indexLastRoute);
//            //replace http to https
//            $protocol = 'https';
////            if( isset($_SERVER['HTTPS']) ) {
////                $protocol = 'https';
////            }
////            else {
////                $protocol = 'http';
////            }
////            echo 'authenticate: protocol='.$protocol."<br>";
//            $lastRoute = str_replace('http',$protocol,$lastRoute);
//            //echo 'authenticate: lastRoute='.$lastRoute."<br>";
//            $logger->notice('authenticate: saml_acs_default: $lastRoute=['.$lastRoute."]");

            //$this->sitename
            //echo 'authenticate: sitename='.$this->sitename."<br>";
            //dump($request);
            //exit('saml');

            if( $username ) {
                $email = str_replace('_@_saml-sso','',$username);
                //username=[oli2002@med.cornell.edu_@_saml-sso]

//                //SAML 1: redirect login request to 'saml_login'
//                $route = $request->attributes->get('_route');
//                if( strpos((string)$route, 'login') !== false ) {
//                    if( $request->isMethod('POST') ) {
//                        return new RedirectResponse( $this->container->get('router')->generate('saml_login',array('client'=>$email)) );
//                    }
//                }

//                $relayState = $request->getPayload()->get('RelayState');
                $samlResponse = $request->getPayload()->get('SAMLResponse');
//                echo 'relayState='.$relayState."<br>";
                //dump($samlResponse);
//                exit('111 saml');

                //SAML 2: process acs response
                $emailArr = explode('@', $email);
                $domain = $emailArr[1]; //domain=med.cornell.edu
                $authUtil = $this->container->get('authenticator_utility');
                //$authUtil->samlAuthenticationByDomain($domain,$lastRoute);

                $logger->notice('authenticate: Before samlAuthenticationStayByDomain');
                $authenticated = $authUtil->samlAuthenticationStayByDomain($domain);
                $logger->notice('authenticate: After samlAuthenticationStayByDomain');
                if( $authenticated ) {
                    $email = str_replace('_@_saml-sso','',$username);
                    $user = $this->em->getRepository(User::class)->findOneUserByUserInfoUseridEmail($email);
                    $logger->notice('authenticate: $user='.$user->getId());
                    if (!$user) {
                        $logger->notice('authenticate: $user not found');
                        throw new AuthenticationException('User not found and auto-creation is disabled.');
                    }
                    //we can use primarypublicuserid (i.e. oli2002) in UserBadge
                    return new SelfValidatingPassport(new UserBadge($email, function () use ($user) {
                        //echo "SelfValidatingPassport OK, user=".$user."<br>";
                        return $user;
                    }));
                } else {
                    $logger->notice('authenticate: SAML authentication failed');
                    throw new AuthenticationException('SAML authentication failed.');
                }
            }

            if( 0 ) {
                $user = $this->em->getRepository(User::class)->findOneUserByUserInfoUseridEmail($username);
                if ($user && $user->getSingleEmail()) {
                    //$router = $this->container->get('router');
                    //$response = new RedirectResponse($router->generate('saml_login', array('client' => $user->getSingleEmail())));
                    //dump($response);
                    //exit('samlAuthentication');
                    //return $response;

                    $authUtil = $this->container->get('authenticator_utility');
                    $authUtil->samlAuthenticationByEmail($user);
                }
            }
            //exit('after saml-sso: user='.$user);
        }

        return new Passport(
            new UserBadge($credentials['username']),
            new CustomCredentials(
                // If this function returns anything else than `true`, the credentials are marked as invalid.
                function( $credentials ) {
                    //exit('new Passport: CustomCredentials');
                    //return true;
                    //$logger = $this->container->get('logger');
                   // $logger->notice('authenticate: new CustomCredentials. Before getAuthUser');
                    //return true; //$user->getApiToken() === $credentials;
                    $user = $this->getAuthUser($credentials);
                    if( $user ) {
                        //if user exists here then it's already authenticated
                        //return true; //this enough

                        //As a final check if getUserIdentifier is equal to 'username' (i.e. oli2002_@_ldap-user)
                        //exit($user->getUserIdentifier()."?=".$credentials['username']);
                        return $user->getUserIdentifier() === $credentials['username'];
                    }
                    return false;
                },
                // The custom credentials
                $credentials
            )
        );


//        $user = $this->getAuthUser($credentials);
//        if( $user ) {
//            return new SelfValidatingPassport(new UserBadge($credentials['username']));
//
//            return new Passport(
//                new UserBadge($credentials['username']),
//                new CustomCredentials(
//                    // If this function returns anything else than `true`, the credentials
//                    // are marked as invalid.
//                    // The $credentials parameter is equal to the next argument of this class
//                    function ($credentials, UserInterface $user) {
//                        return true; //$user->getApiToken() === $credentials;
//                    },
//                    // The custom credentials
//                    $credentials
//            ));
//
//        } else {
//            throw new CustomUserMessageAuthenticationException('Authentication failed');
//        }

    }


    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request) : mixed
    {

        //dump($request->request);

        $username = $request->request->get('_username');
        $usernametype = $request->request->get('_usernametype');
        $sitename = $request->request->get('_sitename');

        //testing: use clean username without _@_local-user saml-sso
        if( 0 ) {
            //Original: username=[oli2002@med.cornell.edu_@_saml-sso], usernametype=[saml-sso], sitename=[employees]
            $usernameArr = explode('_@_', $username);
            $username = $usernameArr[0];

            //oli2002@med.cornell.edu => oli2002
            $usernameArr = explode('@', $username);
            $username = $usernameArr[0];
            //$username = $username."_@_local-user";
            //oli2002_@_ldap-user
            $username = $username."_@_ldap-user";
            $username = 'oli2002_@_saml-sso';
        }

        if( !$username ) {
            //dump($request);
            //$samlResponse = $request->getPayload()->get('SAMLResponse');
            $relayState = $request->getPayload()->get('RelayState');
            //echo 'relayState='.$relayState."<br>";
            //dump($samlResponse);

            //$relayState: http://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu/employees
            if( str_contains($relayState,'/login/')) {
                //$client = (string) substr($somestring, strrpos("/$somestring", '/'));
                $parts = explode('/', $relayState);
                //dump($parts);
                $sitenameUrl = array_pop($parts);
                //echo "sitename=".$sitenameUrl."<br>";
                $client = array_pop($parts);
                //echo "client=".$client."<br>";
                $username = $client;

                if( !$sitename ) {
                    $sitename = $sitenameUrl;
                }
            }
        }
        //exit('after dump request: $username='.$username);

        //$usernametype = 'saml-sso'; //testing

        $credentials = [
            'username' => $username, //$request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'usernametype' => $usernametype, //$request->request->get('_usernametype'),
            'sitename' => $sitename, //$request->request->get('_sitename'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $this->sitename = $credentials['sitename'];

        //dump($credentials);
        //exit('111');

        return $credentials;
    }

    //getUser is replaced by checkCredentials: it authenticate the user and set passwordToken if success,
    // if LDAP user exists in LDAP but not in the system => authenticate and create LDAP user
    public function getAuthUser($credentials) : mixed
    {
        $logger = $this->container->get('logger');
        $logger->notice("getAuthUser: Start");

        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        //Request $request, $username, $password, $providerKey
        $request = null;
        $username = $credentials['username'];
        $password = $credentials['password'];
        $usernametype = $credentials['usernametype'];

        //_security.<your providerKey>.target_path (e.g. _security.main.target_path if the name of your firewall is main)
        $providerKey = 'ldap_employees_firewall';
        //public function __construct(UserInterface $user, string $firewallName, array $roles = [])
//        $unauthenticatedToken = new UsernamePasswordToken(
//            $username,
//            $password,
//            $providerKey
//        );

        $unauthenticatedToken = new CustomUsernamePasswordToken(
            $username,      //username
            $password,
            $usernametype,
            $providerKey
        );

        $usernamePasswordToken = $this->authenticateToken($unauthenticatedToken,$providerKey);
        if( $usernamePasswordToken ) {
            $this->passwordToken = $usernamePasswordToken;
            $user = $usernamePasswordToken->getUser();
            $logger->notice("getAuthUser: User=".$user);
            //exit('return user='.$user);
            return $user;
        }

        $logger->notice("getAuthUser: User not found");
        $this->passwordToken = NULL;
        return NULL;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey=null) : Response
    {
        $authenticationSuccess = $this->container->get($this->sitename.'_authentication_handler');
        return $authenticationSuccess->onAuthenticationSuccess($request,$token);
    }
    
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) : Response
    {
        $authenticationSuccess = $this->container->get('employees_authentication_handler');
        return $authenticationSuccess->onAuthenticationFailure($request,$exception);
    }

    //public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    public function authenticateToken($token, $providerKey)
    {
        //echo "CustomGuardAuthenticator: username=".$token->getUsername()."<br>"; //", pwd=".$token->getCredentials()
        //exit();

        $userSecUtil = $this->container->get('user_security_utility');
        $logger = $this->container->get('logger');

        if( $token->getCredentials() ) {
            //ok
        } else {
            //$logger = $this->container->get('logger');
            $logger->error("authenticate Token: no credentials");
            throw new AuthenticationException('Invalid username or password');
        }

        //$authUtil = new AuthUtil($this->container,$this->em);
        $authUtil = $this->container->get('authenticator_utility');

        $user = null;

        //auth type: ldap-user, local-user, external
        $usernamePrefix = $userSecUtil->getUsernamePrefix($token->getUsername());

        $logger->notice("authenticateToken: usernamePrefix=[$usernamePrefix]");

        //Default user type is 'local-user'
        if( !$usernamePrefix ) {
            $usernamePrefix = 'local-user';
            $token->setUser($token->getUsername()."_@_".$usernamePrefix);
        }

        if( $token->getUsernametype() === 'saml-sso' ) {
            $usernamePrefix = $token->getUsernametype();
        }

        //exit("usernamePrefix=".$usernamePrefix);
        $logger->notice("authenticateToken: 2 usernamePrefix=[$usernamePrefix]");

        //usernametype can be used instead of $usernamePrefix
        //switch( $token->getUsernametype() )
        switch( $usernamePrefix )
        {

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

            case "saml-sso":
                //////////////////////////////////////////////////////////////////////
                //                       5) SAML/SSO authentication                        //
                //////////////////////////////////////////////////////////////////////
//                if( $usernametype && $usernametype == 'saml-sso' ) {
//                    return $this->redirect( $this->generateUrl('saml_login', {'client': }) );
//                }
                $user = $authUtil->samlAuthentication($token);
                break;

            default:
                throw new AuthenticationException('Invalid username or password');

        }

        if ($user) {
            $connection = $this->em->getConnection();
            $logger->notice("authenticateToken: user found by token".", DB=".$connection->getDatabase());
            $this->resetFailedAttemptCounter($user);
            return $this->getUsernamePasswordToken($user, $providerKey);
        }

        //exit('all failed');
        throw new AuthenticationException('Invalid username or password');
    }

    public function getUsernamePasswordToken($user,$providerKey) {
        //exit('getUsernamePasswordToken '.$user);
        return new UsernamePasswordToken(
            $user,
            //NULL,   //$user->getPassword(),
            $providerKey,
            $user->getRoles()
        );
    }

    public function resetFailedAttemptCounter($user) {
        $user->resetFailedAttemptCounter(); //no need to flush. User will be updated by auth.
    }

} 