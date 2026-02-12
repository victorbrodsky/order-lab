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
use Symfony\Component\HttpFoundation\RedirectResponse;
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
//use Symfony\Component\HttpFoundation\RedirectResponse;
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
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;


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
    private $usernametype = null;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        Security $security=null,
        CsrfTokenManagerInterface $csrfTokenManager=null,
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
        //$logger->notice("supports: start");

        //GOOD behavior: only authenticate (i.e. return true) on a specific route
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
        }

        //SAML authentication
        if( $route == 'saml_acs_default' ) {
            //$logger->notice("supports: Yes. saml_acs_default route=".$route);
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
        //exit('authenticate');
        $logger = $this->container->get('logger');
        $logger->notice('authenticate: start');

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
            " DB=[".$connection->getDatabase()."], uri=[".$uri . "]".
            " login attempt username=[$username],".
            " usernametype=[$usernametype], sitename=[$sitename], "

        );

        //$connection = $this->em->getConnection();
        //$currentDb = $connection->getDatabase();
        //$logger->notice('authenticate: currentDb='.$currentDb);
        //exit('before new Passport: usernametype='.$usernametype);

        //SAML case: username=[oli2002@med.cornell.edu_@_saml-sso]
        $route = $request->attributes->get('_route');
        //$logger->notice('authenticate: $route=['.$route."]");


        //Exception for SAML
        //Login page redirect to SamlController->login (/login/{client}/{sitename}?lastroute)
        //SAML login return response to saml_acs_default route and intercepted by this authentication CustomGuardAuthenticator
        if( $route == 'saml_acs_default' ) {
            $logger->notice('authenticate: saml_acs_default: $username=['.$username."]");

            //convert to lower case
            $username = strtolower($username); //username is entered email

            if( $username ) {
                $this->usernametype = 'saml-sso';
                $email = str_replace('_@_saml-sso','',$username);
                //username=[oli2002@med.cornell.edu_@_saml-sso]

                //SAML 2: process acs response
//                $emailArr = explode('@', $email);
//                $domain = $emailArr[1]; //domain=med.cornell.edu

                $authUtil = $this->container->get('authenticator_utility');
                //$logger->notice('authenticate: Before samlAuthenticationByEmail');
                $authenticated = $authUtil->samlAuthenticationByEmail($email);
                //$logger->notice('authenticate: After samlAuthenticationByEmail');
                if( $authenticated ) {
                    $email = str_replace('_@_saml-sso','',$username);
                    $user = $this->em->getRepository(User::class)->findOneUserByUserInfoUseridEmail($email);
                    $logger->notice('authenticate: authenticated $user='.$user->getId());
                    if (!$user) {
                        //$logger->notice('authenticate: $user not found');
                        throw new AuthenticationException('User not found and auto-creation is disabled.');
                    }

                    //Set lastRoute
                    if( $this->sitename && isset($credentials['lastRoute']) && $credentials['lastRoute'] ) {
                        $lastRoute = $credentials['lastRoute'];
                        //$logger->notice('authenticate: set session $lastRoute=['.$lastRoute.']');
                        if( $lastRoute != '/' ) {
                            $authenticationSuccess = $this->container->get($this->sitename . '_authentication_handler');
                            $firewallName = $authenticationSuccess->getFirewallName();
                            $indexLastRoute = '_security.' . $firewallName . '.target_path';
                            //$logger->notice('authenticate: Set lastRoute $lastRoute=' . $lastRoute);
                            $request->getSession()->set($indexLastRoute, $lastRoute);
                        }
                    }

                    //we can use primarypublicuserid (i.e. oli2002) in UserBadge
                    return new SelfValidatingPassport(new UserBadge($email, function () use ($user) {
                        //echo "SelfValidatingPassport OK, user=".$user."<br>";
                        return $user;
                    }));
                } else {
                    $logger->notice('authenticate: SAML authentication failed');
                    throw new AuthenticationException('SAML authentication failed.');
                } //if $authenticated
            } //if($username)
        } //if $route == 'saml_acs_default'

        $logger->notice('authenticate: before new Passport, username='.$credentials['username']);
        //dump($credentials);

//        return new Passport(
//            //new UserBadge($credentials['username']),
//            new UserBadge($credentials['username'], function (string $username) use ($credentials) {
//                // Now you can access the full $credentials array inside
//                return $this->getAuthUser($credentials);
//            }),
//            new CustomCredentials(
//                // If this function returns anything else than `true`, the credentials are marked as invalid.
//                function( $credentials ) {
//                    $user = $this->getAuthUser($credentials);
//                    if( $user ) {
//                        //if user exists here then it's already authenticated
//                        //return true; //this enough
//
//                        //As a final check if getUserIdentifier is equal to 'username' (i.e. oli2002_@_ldap-user)
//                        //exit($user->getUserIdentifier()."?=".$credentials['username']);
//                        //return true;
//                        return $user->getUserIdentifier() === $credentials['username'];
//                    }
//                    return false;
//                },
//                // The custom credentials
//                $credentials
//            )
//        );

        return new Passport(
            new UserBadge($credentials['username'], function (string $username) use ($credentials) {
                // Load or create the user from LDAP
                return $this->getAuthUser($credentials);
            }),
            new CustomCredentials(
                function ($credentials, $user) {
                    // $user is already resolved by UserBadge loader
                    return $user && $user->getUserIdentifier() === $credentials['username'];
                },
                $credentials
            )
        );

    } //authenticate

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request) : mixed
    {
        //dump($request->request);
        //$logger = $this->container->get('logger');
        $username = $request->request->get('_username');
        $usernametype = $request->request->get('_usernametype');
        $sitename = $request->request->get('_sitename');
        $lastRoute = NULL;

        $route = $request->attributes->get('_route');

        //Exception for SAML auth
        if( !$username && $route == 'saml_acs_default' ) {
            //dump( $request->getPayload() );
            //$samlResponse = $request->getPayload()->get('SAMLResponse');
            $relayState = $request->getPayload()->get('RelayState');
            //$logger->notice("getCredentials: relayState=".$relayState);
            //$relayState = oli2002@med.cornell.edu_#_https://view.online/c/wcm/pathology/directory/
            //oli2002@med.cornell.edu__employees__https://view.online/c/wcm/pathology/directory/user/new
            //echo 'relayState='.$relayState."<br>";
            //dump($request);
            //dump($samlResponse);
           // exit('111');

            $useEmailLastRoute = true;
            //$useEmailLastRoute = false;

            if( $useEmailLastRoute ) {
                //$deliemeter = "_#_";
                //$deliemeter = "__";
                $deliemeter = "_**_";
                $relayStateParts = explode($deliemeter, $relayState);
                $username = $relayStateParts[0];
                $sitename = $relayStateParts[1];
                $lastRoute = $relayStateParts[2];
            } else {
                //This condition is not used anymore
                //$relayState: http://view.online/c/wcm/pathology/saml/login/oli2002@med.cornell.edu/employees
                if (str_contains($relayState, '/login/')) {
                    //$client = (string) substr($somestring, strrpos("/$somestring", '/'));
                    $parts = explode('/', $relayState);
                    //dump($parts);
                    $sitenameUrl = array_pop($parts);
                    //echo "sitename=".$sitenameUrl."<br>";
                    $client = array_pop($parts); //Pop the element off the end of array
                    //echo "client=".$client."<br>";
                    $username = $client;

                    if (!$sitename) {
                        $sitename = $sitenameUrl;
                    }
                }
            }//if $useEmailLastRoute
        }
        //exit('after dump request: $username='.$username);

        //$usernametype = 'saml-sso'; //testing

        $credentials = [
            'username' => $username, //$request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'usernametype' => $usernametype, //$request->request->get('_usernametype'),
            'sitename' => $sitename, //$request->request->get('_sitename'),
            'csrf_token' => $request->request->get('_csrf_token'),
            'lastRoute' => $lastRoute
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

        //dump($credentials);
        //exit('getAuthUser');
        //$logger->notice("getAuthUser: credentials['csrf_token']=".$credentials['csrf_token']);
        //echo "credentials['csrf_token']=".$credentials['csrf_token']."<br>";

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

        $logger->notice("getAuthUser: before CustomUsernamePasswordToken: username=$username, usernametype=$usernametype, password=$password");

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
        //Set usernametype for SAML authentication and logout to determine is SAML logout required
        $this->container->get('logger')->notice("CustomGuardAuthentication: onAuthenticationSuccess: this usernametype=".$this->usernametype);
        $token->setAttributes(array('usernametype' => $this->usernametype));
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

        if( $token->getUsername() ) {
            //ok
        } else {
            $logger->error("authenticate Token: no username");
            throw new AuthenticationException('Username is not provided');
        }

        //$authUtil = new AuthUtil($this->container,$this->em);
        $authUtil = $this->container->get('authenticator_utility');
        $ldapAuthUtil = $this->container->get('ldap_authenticator_utility'); //LdapAuthUtil

        $user = null;

        //auth type: ldap-user, local-user, external
        $usernamePrefix = $userSecUtil->getUsernamePrefix($token->getUsername());

        $logger->notice("authenticateToken: usernamePrefix=[$usernamePrefix]");

        //Default user type is 'local-user'
        if( !$usernamePrefix ) {
            $usernamePrefix = 'local-user';
            $logger->notice("authenticate Token: before setUsername to [".$token->getUsername()."_@_".$usernamePrefix."]");
            //$token->setUser($token->getUsername()."_@_".$usernamePrefix);
            $token->setUsername($token->getUsername()."_@_".$usernamePrefix);
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
                $user = $ldapAuthUtil->LdapAuthentication($token, $ldapType = 1);
                //exit("test exit authenticateToken: $usernamePrefix: user=".$user);

                if( !$user && $userSecUtil->getSiteSettingParameter('ldapAll') ) {
                    $user = $ldapAuthUtil->LdapAuthentication($token, $ldapType = 2);
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
                $user = $ldapAuthUtil->LdapAuthentication($token, $ldapType = 2);

                if( !$user && $userSecUtil->getSiteSettingParameter('ldapAll') ) {
                    $user = $ldapAuthUtil->LdapAuthentication($token, $ldapType = 1);
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
            $connection = $this->em->getConnection();
            $logger->notice("authenticateToken: user found by token".", DB=".$connection->getDatabase().", user=".$user);
            $this->resetFailedAttemptCounter($user);
            return $this->getUsernamePasswordToken($user, $providerKey);
        }

        //exit('all failed');
        throw new AuthenticationException('Invalid username or password');
    }

    public function getUsernamePasswordToken(UserInterface $user,$providerKey) {

        $logger = $this->container->get('logger');
        $logger->notice("getUsernamePasswordToken: start");

        if( !$user ) {
            //exit('User is not defined. Invalid username or password');
            throw new AuthenticationException('User is not defined. Invalid username or password');
            //return NULL;
        }

        if( $user instanceof UserInterface ) {
            //ok
        } else {
            //exit('User is not UserInterface. Invalid username or password');
            throw new AuthenticationException('User is not UserInterface. Invalid username or password');
        }

        $logger->notice("getUsernamePasswordToken: before UsernamePasswordToken");
        //exit('get UsernamePasswordToken '.$user);
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