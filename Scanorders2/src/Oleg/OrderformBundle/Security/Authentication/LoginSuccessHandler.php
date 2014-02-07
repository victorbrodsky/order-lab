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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Oleg\OrderformBundle\Security\Authentication\AperioAuthenticator;
use Oleg\OrderformBundle\Helper\UserUtil;

class LoginSuccessHandler extends AperioAuthenticator implements AuthenticationFailureHandlerInterface, AuthenticationSuccessHandlerInterface {

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

        $user = $token->getUser();
        $options = array();
        $em = $this->em;
        $userUtil = new UserUtil();

//        echo "Success. User=".$user.", width=".$width."<br>";
//        exit();

        if( $this->security->isGranted('ROLE_UNAPPROVED_SUBMITTER') ) {
            //redirect to "Welcome to the Scan Order system! Would you like to receive access to this site?"

            $options = array('event'=>'Unapproved User Login Attempt');
            $userUtil->setLoginAttempt($request,$this->security,$em,$options);

            return new RedirectResponse( $this->router->generate('access_request_new',array('id'=>$user->getId())) );
        }

        if( $this->security->isGranted('ROLE_PROCESSOR') ) {

            $response = new RedirectResponse($this->router->generate('adminindex',array('filter_search_box[filter]' => 'All Not Filled')));
            $options['event'] = "Successful Login as Processor";

        }
        elseif( $this->security->isGranted('ROLE_SUBMITTER') || $this->security->isGranted('ROLE_EXTERNAL_SUBMITTER') ) {

            $referer_url = $request->headers->get('referer');
            $last = basename(parse_url($referer_url, PHP_URL_PATH));
            //echo "user role ok! referer_url=".$referer_url.", last=".$last."<br>";
            //exit();
            if( $last == 'login' ) {
                //exit("gen single_new");
                $response = new RedirectResponse($this->router->generate('single_new'));
            } else {
                //exit("use ref url=".$referer_url);
                $response = new RedirectResponse($referer_url);
            }

            $options['event'] = "Successful Login";

        }
        else {

            //echo "user role not ok!";
            //exit();
            $response = new RedirectResponse( $this->router->generate('logout') );

            $options['event'] = "Unsuccessful Login Attempt. Wrong Role: user is not processor or submitter/external submitter";
            
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

        $response = new RedirectResponse( $this->router->generate('logout') );
        return $response;

    }

}