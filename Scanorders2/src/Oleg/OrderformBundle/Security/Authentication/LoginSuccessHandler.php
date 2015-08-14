<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/7/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Authentication;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\UserdirectoryBundle\Util\UserUtil;


class LoginSuccessHandler implements AuthenticationFailureHandlerInterface, AuthenticationSuccessHandlerInterface {

    private $container;
    private $security;
    private $em;
    private $router;
    private $siteName;
    protected $firewallName;

    public function __construct( $container, SecurityContext $security, $em )
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->security = $security;
        $this->em = $em;
        $this->siteName = $container->getParameter('scan.sitename');
        $this->firewallName = 'aperio_ldap_firewall';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

        $response = null;

        $user = $token->getUser();
        $options = array();
        $em = $this->em;
        $userUtil = new UserUtil();
        //$secUtil = $this->container->get('user_security_utility');

        $options['sitename'] = $this->siteName;

        //echo "onAuthenticationSuccess: Success. User=".$user.", setCreatedby=".$user->getCreatedby()."<br>";
        //exit;

        if( $this->security->isGranted('ROLE_SCANORDER_BANNED') ) {
            $options['eventtype'] = 'Banned User Login Attempt';
            $options['event'] = 'Banned user login attempt to Scan Order site';
            $userUtil->setLoginAttempt($request,$this->security,$em,$options);

            return new RedirectResponse( $this->router->generate($this->siteName.'_access_request_new') );
        }

        //detect if the user was first time logged in by ldap: assign role ROLE_SCANORDER_UNAPPROVED_SUBMITTER
        //If the user does not have lowest role => assign unapproved role to trigger access request
        if( $this->security->isGranted('ROLE_SCANORDER_SUBMITTER') === false ) {
            //echo "assign role ROLE_SCANORDER_UNAPPROVED_SUBMITTER <br>";
            $user->addRole('ROLE_SCANORDER_UNAPPROVED_SUBMITTER');
        }

        if( $this->security->isGranted('ROLE_SCANORDER_UNAPPROVED_SUBMITTER') ) {

            $options['eventtype'] = 'Unapproved User Login Attempt';
            $options['event'] = 'Unapproved user login attempt to Scan Order site';
            $userUtil->setLoginAttempt($request,$this->security,$em,$options);

            //exit("onAuthenticationSuccess: Success: redirect to _access_request_new");

            return new RedirectResponse( $this->router->generate($this->siteName.'_access_request_new') );
        }
        //exit('ok');

        $options['eventtype'] = "Successful Login";
        $options['event'] = 'Successful login to Scan Order site';

        $userUtil->setLoginAttempt($request,$this->security,$em,$options);

        //Issue #381: redirect non-processor users to the previously requested page before authentication

        //$response = new RedirectResponse($this->router->generate($this->siteName.'_scan_home'));
        //return $response;

        //I should be redirected to the URL I was trying to visit after login.
        $indexLastRoute = '_security.'.$this->firewallName.'.target_path';
        $lastRoute = $request->getSession()->get($indexLastRoute);
        //exit("lastRoute=".$lastRoute."<br>");

        $loginpos = strpos($lastRoute, '/login');
        $nopermpos = strpos($lastRoute, '/no-permission');
        $nocheck = strpos($lastRoute, '/check/');
        $keepalive = strpos($lastRoute, '/keepalive');
        $idlelogout = strpos($lastRoute, '/idlelogout');

        if( $lastRoute && $lastRoute != '' && $loginpos === false && $nopermpos === false && $nocheck === false && $keepalive === false && $idlelogout === false ) {
            $referer_url = $lastRoute;
        } else {
            $referer_url = $this->router->generate($this->siteName.'_home');
        }

        //echo("referer_url=".$referer_url);

        $response = new RedirectResponse($referer_url);
        return $response;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        //error_log('You are out!');
        //echo "user is not ok!. Exception=<br>".$exception."<br>";
        //exit("user is not ok!");

        $options = array();
        $em = $this->em;
        $userUtil = new UserUtil();

        $options['sitename'] = $this->siteName;
        $options['eventtype'] = "Bad Credentials";
        $options['event'] = 'Bad credentials provided on login for Scan Order site';
        $options['serverresponse'] = $exception->getMessage();

        $userUtil->setLoginAttempt($request,$this->security,$em,$options);

        $request->getSession()->set(SecurityContextInterface::AUTHENTICATION_ERROR, $exception);

        $response = new RedirectResponse( $this->router->generate($this->siteName.'_login') );
        return $response;

    }

}