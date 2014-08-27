<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/7/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Security\Authentication;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Resources\config\Constant;


class LoginSuccessHandler implements AuthenticationFailureHandlerInterface, AuthenticationSuccessHandlerInterface {

    private $container;
    private $security;
    private $em;
    private $router;
    private $siteName;

    public function __construct( $container, SecurityContext $security, $em )
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->security = $security;
        $this->em = $em;
        $this->siteName = Constant::SITE_NAME;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

        $response = null;

        $user = $token->getUser();
        $options = array();
        $em = $this->em;
        $userUtil = new UserUtil();
        $secUtil = $this->container->get('user_security_utility');

        echo "employees authentication success: Success. User=".$user.", setCreatedby=".$user->getCreatedby()."<br>";
        //exit;

        if( $this->security->isGranted('ROLE_USERDIRECTORY_BANNED') ) {
            $options = array('event'=>'Banned User Login Attempt');
            $userUtil->setLoginAttempt($request,$this->security,$em,$options);

            return new RedirectResponse( $this->router->generate('access_request_new',array('id'=>$user->getId(),'sitename'=>$this->siteName)) );
        }

        //detect if the user was time logged in by ldap: assign role ROLE_UNAPPROVED_SUBMITTER
        //all users eneterd by ldap must have approved access request
        if( $user->getCreatedby() == 'ldap' && !$secUtil->getUserAccessRequest($user,$this->siteName)  ) {
            //echo "assign role ROLE_UNAPPROVED_SUBMITTER <br>";
            $user->addRole('ROLE_USERDIRECTORY_UNAPPROVED_SUBMITTER');
        }

        if( $this->security->isGranted('ROLE_USERDIRECTORY_UNAPPROVED_SUBMITTER') ) {
            $options = array('event'=>'Unapproved User Login Attempt');
            $userUtil->setLoginAttempt($request,$this->security,$em,$options);

            return new RedirectResponse( $this->router->generate('access_request_new',array('id'=>$user->getId(),'sitename'=>$this->siteName)) );
        }

        $options['event'] = "Successful Login";
        $response = new RedirectResponse($this->router->generate('employees_home'));

        $userUtil->setLoginAttempt($request,$this->security,$em,$options);

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

        $options['event'] = "Bad Credentials";
        $options['serverresponse'] = $exception->getMessage();

        $userUtil->setLoginAttempt($request,$this->security,$em,$options);

        $request->getSession()->set(SecurityContextInterface::AUTHENTICATION_ERROR, $exception);

        $response = new RedirectResponse( $this->router->generate('employees-login') );
        return $response;

    }

}