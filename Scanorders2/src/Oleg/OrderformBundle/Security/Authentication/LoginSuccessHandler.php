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
use Oleg\OrderformBundle\Helper\UserUtil;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


class LoginSuccessHandler implements AuthenticationFailureHandlerInterface, AuthenticationSuccessHandlerInterface {

    private $router;
    private $security;
    private $em;

    public function __construct( Router $router, SecurityContext $security, $em )
    {
        $this->router = $router;
        $this->security = $security;
        $this->em = $em;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

        $response = null;

        $user = $token->getUser();
        $options = array();
        $em = $this->em;
        $userUtil = new UserUtil();

        //echo "onAuthenticationSuccess: Success. User=".$user.", setCreatedby=".$user->getCreatedby()."<br>";
        //exit();

        if( $this->security->isGranted('ROLE_BANNED') ) {
            $options = array('event'=>'Banned User Login Attempt');
            $userUtil->setLoginAttempt($request,$this->security,$em,$options);

            return new RedirectResponse( $this->router->generate('access_request_new',array('id'=>$user->getId())) );
        }

        if( $this->security->isGranted('ROLE_UNAPPROVED_SUBMITTER') ) {
            $options = array('event'=>'Unapproved User Login Attempt');
            $userUtil->setLoginAttempt($request,$this->security,$em,$options);

            return new RedirectResponse( $this->router->generate('access_request_new',array('id'=>$user->getId())) );
        }

        if( $user->hasRole('ROLE_PROCESSOR') ) {

            $response = new RedirectResponse($this->router->generate('incoming-scan-orders',array('filter_search_box[filter]' => 'All Not Filled')));
            $options['event'] = "Successful Login";

        }
        elseif( $this->security->isGranted('ROLE_SUBMITTER') || $this->security->isGranted('ROLE_EXTERNAL_SUBMITTER') || $this->security->isGranted('ROLE_ORDERING_PROVIDER') ) {

            //$referer_url1 = $request->headers->get('referer');
            //echo("referer_url1=".$referer_url1."<br>");
            //$last = basename(parse_url($referer_url, PHP_URL_PATH));

            $indexLastRoute = '_security.aperio_ldap_firewall.target_path';   //'last_route';
            $lastRoute = $request->getSession()->get($indexLastRoute);
            //echo("lastRoute=".$lastRoute."<br>");

            $loginpos = strpos($lastRoute, '/login');
            $nopermpos = strpos($lastRoute, '/nopermission');

            //echo "nopermpos=".$nopermpos."<br>";
            //echo "loginpos=".$loginpos."<br>";

            if( $lastRoute && $lastRoute != '' && $lastRoute && $loginpos === false && $nopermpos === false ) {
                //$referer_url = $this->router->generate( $lastRoute );
                $referer_url = $lastRoute;
            } else {
                $referer_url = $this->router->generate('scan-order-home');
            }

            //echo("referer_url=".$referer_url);
            //exit('not processor');

            $response = new RedirectResponse($referer_url);

            $options['event'] = "Successful Login";

        }
        else {

            //echo "user role not ok!";
            //exit();
            $response = new RedirectResponse( $this->router->generate('logout') );

            $options['event'] = "Unsuccessful Login Attempt. Wrong Role: user is not processor or submitter/external/ordering provider submitter";
            
        }

//        $lastRouteArr = $request->getSession()->get('last_route_arr');
//        echo "<br>lastRouteArr:<br>";
//        print_r($lastRouteArr);
//        $request->getSession()->set('last_route_arr', array());
//        echo "Session:<br>";
//        print_r($request->getSession());
//        exit("<br>");

        $userUtil->setLoginAttempt($request,$this->security,$em,$options);

        return $response;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        //error_log('You are out!');
        //echo "user is not ok!. Exception=<br>".$exception."<br>";
        //exit();

        $options = array();
        $em = $this->em;
        $userUtil = new UserUtil();

        $options['event'] = "Bad Credentials";
        $options['serverresponse'] = $exception->getMessage();

        $userUtil->setLoginAttempt($request,$this->security,$em,$options);

        $request->getSession()->set(SecurityContextInterface::AUTHENTICATION_ERROR, $exception);

        $response = new RedirectResponse( $this->router->generate('login') );
        return $response;

    }

}