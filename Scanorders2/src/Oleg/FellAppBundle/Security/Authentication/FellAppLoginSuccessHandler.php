<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/7/13
 * Time: 11:24 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\FellAppBundle\Security\Authentication;

use Oleg\UserdirectoryBundle\Security\Authentication\LoginSuccessHandler;
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


class FellAppLoginSuccessHandler extends LoginSuccessHandler {

//    protected $container;
//    protected $security;
//    protected $em;
//    protected $router;
//    protected $siteName;
//    protected $siteNameStr;
//    protected $roleBanned;
//    protected $roleUser;
//    protected $roleUnapproved;

    public function __construct( $container, SecurityContext $security, $em )
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->security = $security;
        $this->em = $em;
        $this->siteName = $container->getParameter('fellapp.sitename');
        $this->siteNameStr = 'Fellowship Applications';
        $this->roleBanned = 'ROLE_FELLAPP_BANNED';
        $this->roleUser = 'ROLE_FELLAPP_USER';
        $this->roleUnapproved = 'ROLE_FELLAPP_UNAPPROVED';
        $this->firewallName = 'ldap_fellapp_firewall';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        $redirectResponse = parent::onAuthenticationSuccess($request,$token);

        $url = $redirectResponse->getTargetUrl();
        //echo "url=".$url."<br>";

        //If "Fellowship Interviewer" is the highest role of the user logging into the fellowship site,
        // automatically redirect them to the /my-interviewees page after login
        // UNLESS they came to the site via a link from an email to evaluate a specific candidate already.
        if( $url == "/order/fellowship-applications/" && $url != "/order/fellowship-applications/interview-evaluation/") {
            //exit('redirect to fellapp_myinterviewees');
            //TODO: check "Fellowship Interviewer" is the highest role into the fellowship site
            if( $this->isFellowshipInterviewerHighestRole($token->getUser()) ) {
                $redirectResponse->setTargetUrl("/order/fellowship-applications/my-interviewees/");
            }
        }

        return $redirectResponse;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return parent::onAuthenticationFailure($request,$exception);
    }

    //check "Fellowship Interviewer" is the highest role into the fellowship site
    public function isFellowshipInterviewerHighestRole($user) {

        //1) check if the user has role "Fellowship Interviewer" with permission: object="Interview", action="create"
        $fellappRoles = $this->em->getRepository('OlegUserdirectoryBundle:User')->findUserRolesByObjectAction( $user, "Interview", "create" );

        $level = 0;
        foreach( $fellappRoles as $fellappRole ) {
            if( $fellappRole->getLevel() > $level ) {
                $level = $fellappRole->getLevel();
            }
        }

        //2) check if the "Fellowship Interviewer" level is the highest among all roles
        //echo "level1=".$level."<br>";
        //exit("level2=".$this->getHighestRoleLevel($user));
        if( $level >= $this->getHighestRoleLevel($user) ) {
            return true;
        }

        return false;
    }

    public function getHighestRoleLevel($user) {
        $level = 0;
        foreach( $user->getRoles() as $roleName ) {
            $role = $this->em->getRepository('OlegUserdirectoryBundle:Roles')->findOneByName($roleName);
            //echo "role=".$role."<br>";
            if( $role && $role->getLevel() > $level ) {
                $level = $role->getLevel();
            }
        }

        return $level;
    }
}