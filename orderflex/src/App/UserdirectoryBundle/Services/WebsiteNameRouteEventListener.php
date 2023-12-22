<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 12/1/2023
 * Time: 10:20 AM
 */

namespace App\UserdirectoryBundle\Services;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContextAwareInterface;


//https://github.com/symfony/symfony/blob/6.4/src/Symfony/Component/HttpKernel/EventListener/LocaleListener.php

//NOT USED. NOt enabled in services.yaml
//For multitenancy, it is possible to use route's prefix instead of _locale
//Purpose of this class: Replace request's context {tenantprefix} with user's tenant id
class WebsiteNameRouteEventListener implements EventSubscriberInterface {

    private $router;

    public function __construct(RequestContextAwareInterface $router = null) {
        $this->router = $router;
        //dump($router);
        //exit('111');
    }

    public function onKernelResponse(ResponseEvent $event) {
        $request = $event->getRequest();
        //dump($request);
        //exit('onKernelResponse');
        $this->setWebsiteName($request);
    }

    public function onKernelRequest(RequestEvent $event) {
        $request = $event->getRequest();
        //dump($request);
        //exit('onKernelRequest');
        $uri = $this->setWebsiteName($request);
        //$uri = $request->getUri();
//        echo "uri=".$uri."<br>";
//        if( $uri ) {
//            exit('111');
//            $response = new RedirectResponse($uri);
//            $event->setResponse($response);
//        }
    }

    public static function getSubscribedEvents() {
        return array(
            // must be registered after the Router to have access to the _locale
            KernelEvents::REQUEST => array(array('onKernelRequest', 16)),
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }

    private function setWebsiteName(Request $request) {
        //return;
        if( null !== $this->router ) {

            //$url = $request->getRequestUri();
            //echo "url=".$url."<br>";

            $uri = $request->getUri();
            //echo "uri=".$uri."<br>";

            //$routeName = $request->get('_route');
            //echo "routeName=".$routeName."<br>";

            //remove tenantid
            //$uri = str_replace('c/wcm/pathology','',$uri);

            //dump($request);
            //exit('setWebsiteName');

//            $response = new Response();
//            $response->headers->set("Location","www.example.com");
//            return $response;

            //echo "NEW CODE IN ACTION";die();
            //$this->router->getContext()->setParameter('tenantprefix', $request->attributes->get("tenantprefix"));

            $tenantprefix = $this->router->getContext()->getParameter('tenantprefix');
            //echo 'current tenantprefix='.$tenantprefix."<br>";

            if( !$tenantprefix ) {
                $tenantprefix = 'c/lmh/pathology/';
                $tenantprefix = 'c/wcm/pathology/';
                $tenantprefix = 'pathology';
                //echo "set parameter tenantprefix=" . $tenantprefix . "<br>";
                $this->router->getContext()->setParameter('tenantprefix', $tenantprefix);

                $tenantprefix = $this->router->getContext()->getParameter('tenantprefix');
                //echo 'after tenantprefix='.$tenantprefix."<br>";

                //$url = $this->container->get('router')->generate($maintenanceRoute);
                //$response = new RedirectResponse($uri);
                //$event->setResponse($response);

                return $uri;
            }
        }

        return null;
    }

}