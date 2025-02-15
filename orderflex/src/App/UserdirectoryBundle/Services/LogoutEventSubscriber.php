<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 7/28/2022
 * Time: 4:21 PM
 */

namespace App\UserdirectoryBundle\Services;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\KernelEvents;
//use Symfony\Component\Security\Http\Event\TokenDeauthenticatedEvent;

//https://stackoverflow.com/questions/60848727/how-to-listen-to-the-log-out-event-to-record-the-event-on-the-database
//https://php.tutorialink.com/symfony-how-to-return-all-logged-in-active-users/
//https://symfony.com/doc/current/security.html#logging-out
//priority: https://stackoverflow.com/questions/62815732/symfony-redirect-user-before-logout-event

class LogoutEventSubscriber implements EventSubscriberInterface
{

    protected $container;
    protected $requestStack;
    protected $security;

    public function __construct( ContainerInterface $container, RequestStack $requestStack, Security $security ) {
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    //TokenDeauthenticatedEvent
//    public static function getSubscribedEvents(): array
//    {
//        return [
//            TokenDeauthenticatedEvent::class => 'onDeauthenticated',
//        ];
//    }
//
//    //Testing
//    public function onDeauthenticated(TokenDeauthenticatedEvent $event): void
//    {
//        $user = $event->getOriginalToken()->getUser();
//        exit("de auth user=".$user);
//        $this->updateUserLastLogin($user);
//    }


    //There is an optional attribute for the kernel.event_listener tag called priority,
    // which is a positive or negative integer that defaults to 0 and it controls
    // the order in which listeners are executed (the higher the number,
    // the earlier a listener is executed). This is useful when you need
    // to guarantee that one listener is executed before another.
    // The priorities of the internal Symfony listeners usually
    // range from -256 to 256 but your own listeners can use any positive or negative integer.

    //LogoutEvent
    public static function getSubscribedEvents(): array
    {
        //priority:
        //-1 - session does not exist
        //0 - session does not exist
        //1 - session exists
        return [
            LogoutEvent::class => 'onLogout'
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $logger = $this->container->get('logger');
        $logger->notice("onLogout");

        $user = NULL;
        if( $event->getToken() ) {
            $user = $event->getToken()->getUser();
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $request = $event->getRequest();
        $samlLogoutStr = "";

        $sitename = $this->getSitename($request);
        //$logger->notice("onLogout: sitename=$sitename");

        //In order to keep session onLogout, set firewall logout: invalidate_session: false then $session->invalidate();
        $session = $request->getSession();
        //dump($session);
        //exit('logout');

        $logintype = $session->get('logintype');
        //$logger = $this->container->get('logger');
        $logger->notice("onLogout: logintype=".$logintype);

        if( $logintype === 'saml-sso' ) {
            $samlLogoutStr = ", with SAML logout";
        }

        //EventLog
        //$request = $event->getRequest();
        $eventStr = "User $user manually logged out".$samlLogoutStr;
        $eventType = "User Manually Logged Out";
        $userSecUtil->createUserEditEvent(
            $sitename,                             //$sitename
            $eventStr,                             //$event (Event description)
            $user,                                 //$user
            $user,                                 //$subjectEntities
            $request,                              //$request
            $eventType                             //$action (Event Type)
        );

        //invalidate_session manually
        $session->invalidate(); //auto loggout flashbag message not display
        $userSecUtil->userLogout($sitename);

        //samlLogout will redirect by $auth->logout(); to $sitename homepage
        $userSecUtil->samlLogout($user,$logintype,$sitename);
    }

    public function getSitename( $request ) {
        //default
        $sitename = $this->container->getParameter('employees.sitename');

        $routename = $request->get('_route');

        //$logger = $this->container->get('logger');
        //$logger->notice("onLogout: getSitename: routename=$routename"); //translationalresearch_logout

        if( $routename == "employees_logout" ) {
            $sitename = $this->container->getParameter('employees.sitename');
        }
        if( $routename == "fellapp_logout" ) {
            $sitename = $this->container->getParameter('fellapp.sitename');
        }
        if( $routename == "resapp_logout" ) {
            $sitename = $this->container->getParameter('resapp.sitename');
        }
        if( $routename == "deidentifier_logout" ) {
            $sitename = $this->container->getParameter('deidentifier.sitename');
        }
        if( $routename == "scan_logout" ) {
            $sitename = $this->container->getParameter('scan.sitename');
        }
        if( $routename == "vacreq_logout" ) {
            $sitename = $this->container->getParameter('vacreq.sitename');
        }
        if( $routename == "calllog_logout" ) {
            $sitename = $this->container->getParameter('calllog.sitename');
        }
        if( $routename == "crn_logout" ) {
            $sitename = $this->container->getParameter('crn.sitename');
        }
        if( $routename == "translationalresearch_logout" ) {
            $sitename = $this->container->getParameter('translationalresearch.sitename');
        }
        if( $routename == "dashboard_logout" ) {
            $sitename = $this->container->getParameter('dashboard.sitename');
        }

        return $sitename;
    }

//    public function onSamlLogout(ResponseEvent $event): void
//    {
//        //if( !$event->isMasterRequest() ) {
//        //    return;
//        //}
//
//        $logger = $this->container->get('logger');
//        $logger->notice("onSamlLogout");
//
//        $request = $event->getRequest();
//
//        $pathInfo = $request->getPathInfo();
//        $logger->notice("onSamlLogout: pathInfo=".$pathInfo);
//        //$logger->notice("onSamlLogout: 2 pathInfo=".substr($request->getPathInfo(), 0, 4)."");
//
////        if( '/logout' !== substr($request->getPathInfo(), 0, 4) ) {
////            return;
////        }
//
//        if( !str_contains($pathInfo, '/logout') ) {
//            return;
//        }
//
////        $user = NULL;
////        if( $event->getToken() ) {
////            $user = $event->getToken()->getUser();
////        }
//        $user = $this->security->getUser();
//
//        $userSecUtil = $this->container->get('user_security_utility');
//
//        //$user = $event->getToken()->getUser();
//        //exit("logout user=".$user);
//        //$this->updateUserLastLogin($user);
//        //$vacreqUtil = $this->container->get('vacreq_util');
//
//        //Saml logout: TODO: session is empty
//        //$request = $event->getRequest();
//        $session = $this->requestStack->getSession();
//        //dump($session);
//        //exit('logout');
//        $logintype = $session->get('logintype');
//        $logger = $this->container->get('logger');
//        $logger->notice("onSamlLogout: logintype=".$logintype);
//        //dump($session);
//        //exit('onLogout');
//        $samlLogoutStr = "";
//        $samlLogout = $userSecUtil->samlLogout($user);
//        if( $samlLogout ) {
//            $samlLogoutStr = ", with SAML logout";
//        }
//    }

}