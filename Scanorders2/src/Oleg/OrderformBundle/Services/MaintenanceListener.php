<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 8/8/14
 * Time: 4:20 PM
 */

namespace Oleg\OrderformBundle\Services;


use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\HttpFoundation\RedirectResponse;

class MaintenanceListener {

    private $container;
    //private $sc;

//    public function __construct( ContainerInterface $container, SecurityContext $sc )
//    {
//        $this->container = $container;
//        $this->sc = $sc;
//    }

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        //$this->sc = $sc;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

        //return;

        //$user = $this->sc->getToken()->getUser();
        //echo "user=".$user."<br>";

        //$maintenanceUntil = $this->container->hasParameter('maintenanceenddate') ? $this->container->getParameter('maintenanceenddate') : false;
        $maintenance = $this->container->hasParameter('maintenance') ? $this->container->getParameter('maintenance') : false;

        //$maintenanceLoginMsg = $this->container->hasParameter('maintenanceloginmsg') ? $this->container->getParameter('maintenanceloginmsg') : false;
        //$maintenanceLogoutMsg = $this->container->hasParameter('maintenancelogoutmsg') ? $this->container->getParameter('maintenancelogoutmsg') : false;

        //echo "maintenance=".$maintenance."<br>";
        //echo "maintenanceUntil=".$maintenanceUntil."<br>";
        //echo "maintenanceLoginMsg=".$maintenanceLoginMsg."<br>";
        //echo "maintenancelogoutmsg=".$maintenanceLogoutMsg."<br>";

        $debug = in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));

        //echo "route=".$event->getRequest()->get('_route')."<b>";

        //if( 0 ) {
        if( $maintenance && !$debug ) {
        //if( $maintenance ) {

            $route = 'maintenance_scanorder';

            //echo "route=".$event->getRequest()->get('_route')."<br>";
            //echo "url=".$url."<br>";
            //echo "route=".$route."<br>";

            if( $route === $event->getRequest()->get('_route') ) {
                return;
            } else {
                //$url = $this->router->generate("main_common_home");
                $url = $this->container->get('router')->generate('maintenance_scanorder');
                $response = new RedirectResponse($url);
                $event->setResponse($response);
            }

//            $engine = $this->container->get('templating');
//
//            //return $engine->render( 'OlegOrderformBundle:Maintenance:maintenance.html.twig', array('maintenanceUntil'=>$maintenanceUntil) );
//            //return $engine->render( 'OlegOrderformBundle:Maintenance:maintenance.html.twig', array('maintenanceUntil'=>$maintenanceUntil) );
            //$content = $engine->render( '::maintenance.html.twig', array('maintenanceloginmsg'=>$maintenanceLoginMsg) );
//            //$content = $engine->render('OlegOrderformBundle:Maintenance:maintenance.html.twig', array('maintenanceUntil'=>$maintenanceUntil));
//
//            $url = $this->router->generate($route);
//            $response = new RedirectResponse($url);
//            $event->setResponse($response);
//
//            $event->setResponse(new Response($content, 503));
//            //$event->setResponse(new Response($content));
//            $event->stopPropagation();
        }

    }

} 