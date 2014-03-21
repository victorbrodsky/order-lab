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

        if( $this->security->isGranted('ROLE_PROCESSOR') ) {

            $response = new RedirectResponse($this->router->generate('incoming-scan-orders',array('filter_search_box[filter]' => 'All Not Filled')));
            $options['event'] = "Successful Login";

        }
        elseif( $this->security->isGranted('ROLE_SUBMITTER') || $this->security->isGranted('ROLE_EXTERNAL_SUBMITTER') || $this->security->isGranted('ROLE_ORDERING_PROVIDER') ) {

            //$referer_url = $request->headers->get('referer');
            //$last = basename(parse_url($referer_url, PHP_URL_PATH));

            //$last_route = $request->getSession()->get('last_route', array('name' => 'scan-order-home'));
            $lastRoute = $request->getSession()->get('last_route');
            //echo "last_route=".$lastRoute."<br>";
            //print_r($request->getSession());
            //print_r($lastRoute);
            //echo "\n count=".count($lastRoute)."\n";
            //$full_url = $request->getSession()->get('full_url');

            //if( $lastRoute && count($lastRoute) > 0 && array_key_exists('name', $lastRoute) ) {
            if( $lastRoute && $lastRoute != '' ) {
                $referer_url = $this->router->generate( $lastRoute );
                //$referer_url = $full_url;
            } else {
                //print_r($lastRoute);
                //exit('last root array is empty, count'.count($lastRoute));
                $referer_url = $this->router->generate('scan-order-home');
            }

            //echo "<br>success: referer_url=".$referer_url."<br>";
            //exit();

//            if( $referer_url == 'login' ) {
//                //exit("gen single_new");
//                $response = new RedirectResponse($this->router->generate('scan-order-home'));
//            } else {
                //exit("use ref url=".$referer_url);
//                $response = new RedirectResponse($referer_url);
//            }

            $response = new RedirectResponse($referer_url);

            $options['event'] = "Successful Login";

        }
        else {

            //echo "user role not ok!";
            //exit();
            $response = new RedirectResponse( $this->router->generate('logout') );

            $options['event'] = "Unsuccessful Login Attempt. Wrong Role: user is not processor or submitter/external/ordering provider submitter";
            
        }

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