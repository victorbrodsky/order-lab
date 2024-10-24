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
use Symfony\Component\Security\Http\Event\LogoutEvent;
//use Symfony\Component\Security\Http\Event\TokenDeauthenticatedEvent;

//https://stackoverflow.com/questions/60848727/how-to-listen-to-the-log-out-event-to-record-the-event-on-the-database
//https://php.tutorialink.com/symfony-how-to-return-all-logged-in-active-users/
//https://symfony.com/doc/current/security.html#logging-out

class LogoutEventSubscriber implements EventSubscriberInterface
{

    protected $container;

    public function __construct( ContainerInterface $container ) {
        $this->container = $container;
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


    //LogoutEvent
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = NULL;
        if( $event->getToken() ) {
            $user = $event->getToken()->getUser();
        }

        //$user = $event->getToken()->getUser();
        //exit("logout user=".$user);
        //$this->updateUserLastLogin($user);
        //$vacreqUtil = $this->container->get('vacreq_util');

        //EventLog
        $request = $event->getRequest();
        $eventStr = "User $user manually logged out";
        $eventType = "User Manually Logged Out";
        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent(
            $this->container->getParameter('employees.sitename'),   //$sitename
            $eventStr,                                              //$event (Event description)
            $user,                                                  //$user
            $user,                                                  //$subjectEntities
            $request,                                               //$request
            $eventType                                              //$action (Event Type)
        );
    }
}