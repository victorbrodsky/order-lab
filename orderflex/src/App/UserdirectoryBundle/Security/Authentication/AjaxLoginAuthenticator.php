<?php
/**
 * Created by PhpStorm.
 * User: cinav
 * Date: 2/12/2026
 * Time: 4:47 PM
 */

namespace App\UserdirectoryBundle\Security\Authentication;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

//class AjaxLoginAuthenticator extends AbstractAuthenticator
class AjaxLoginAuthenticator extends CustomGuardAuthenticator
{
//    private $container;
//    private $csrfTokenManager;
//
//    public function __construct(
//        ContainerInterface $container,
//        CsrfTokenManagerInterface $csrfTokenManager=null,
//    )
//    {
//        $this->container = $container;
//        $this->csrfTokenManager = $csrfTokenManager;
//    }

    //public function supports(Request $request): ?bool
    public function supports(Request $request) : bool
    {
        // Trigger only for AJAX login endpoint
        return $request->isMethod('POST')
            && $request->attributes->get('_route') === 'employees_ajax_login';
    }

    public function authenticate(Request $request): Passport
    {
        $logger = $this->container->get('logger');
        $logger->notice('AjaxLoginAuthenticator authenticate: start');
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;
        $logger->notice("AjaxLoginAuthenticator authenticate: username=$username, password=$password");

        if (!$username || !$password) {
            throw new AuthenticationException('Missing credentials');
        }

        $credentials = $this->getCredentials($request);
//        $credentials = [
//            'username' => $username,
//            'password' => $password,
//            'usernametype' => 'local-user',
//            'sitename' => 'employees',
//            'csrf_token' => $request->request->get('_csrf_token'),
//            'lastRoute' => ''
//        ];

//        return new Passport(
//            new UserBadge($username),
//            new PasswordCredentials($password)
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
    }

    public function getCredentials(Request $request) : mixed
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;
        $credentials = [
            'username' => $username,
            'password' => $password,
            'usernametype' => 'local-user',
            'sitename' => 'employees',
            'csrf_token' => $request->request->get('_csrf_token'),
            'lastRoute' => ''
        ];
    }

//    //getUser is replaced by checkCredentials: it authenticate the user and set passwordToken if success,
//    // if LDAP user exists in LDAP but not in the system => authenticate and create LDAP user
//    public function getAuthUser($credentials) : mixed
//    {
//        $logger = $this->container->get('logger');
//        $logger->notice("getAuthUser: Start");
//
//        //dump($credentials);
//        //exit('getAuthUser');
//        //$logger->notice("getAuthUser: credentials['csrf_token']=".$credentials['csrf_token']);
//        //echo "credentials['csrf_token']=".$credentials['csrf_token']."<br>";
//
//        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
//        if (!$this->csrfTokenManager->isTokenValid($token)) {
//            throw new InvalidCsrfTokenException();
//        }
//
//        //Request $request, $username, $password, $providerKey
//        $request = null;
//        $username = $credentials['username'];
//        $password = $credentials['password']; //plain password
//        $usernametype = $credentials['usernametype'];
//
//        //_security.<your providerKey>.target_path (e.g. _security.main.target_path if the name of your firewall is main)
//        $providerKey = 'ldap_employees_firewall';
//        //public function __construct(UserInterface $user, string $firewallName, array $roles = [])
//    //        $unauthenticatedToken = new UsernamePasswordToken(
//    //            $username,
//    //            $password,
//    //            $providerKey
//    //        );
//
//        $logger->notice("getAuthUser: before CustomUsernamePasswordToken: username=$username, usernametype=$usernametype");
//
//        $unauthenticatedToken = new CustomUsernamePasswordToken(
//            $username,      //username
//            $password,
//            $usernametype,
//            $providerKey
//        );
//
//        $usernamePasswordToken = $this->authenticateToken($unauthenticatedToken,$providerKey);
//        if( $usernamePasswordToken ) {
//            $this->passwordToken = $usernamePasswordToken;
//            $user = $usernamePasswordToken->getUser();
//            $logger->notice("getAuthUser: User=".$user);
//            //exit('return user='.$user);
//            return $user;
//        }
//
//        $logger->notice("getAuthUser: User not found");
//        $this->passwordToken = NULL;
//        return NULL;
//    }


//    public function onAuthenticationSuccess(
//        Request $request,
//        TokenInterface $token,
//        string $firewallName
//    ): ?JsonResponse

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey=null) : ?Response
    {
        return new JsonResponse([
            'authenticated' => true
        ]);
    }

//    public function onAuthenticationFailure(
//        Request $request,
//        AuthenticationException $exception
//    ): ?JsonResponse
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) : ?Response
    {
        return new JsonResponse([
            'authenticated' => false,
            'error' => $exception->getMessage()
        ], 401);
    }



}
