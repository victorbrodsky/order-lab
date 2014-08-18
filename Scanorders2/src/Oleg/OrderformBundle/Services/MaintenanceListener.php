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
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Util\UserUtil;

class MaintenanceListener {

    private $container;
    private $em;
    private $sc;

    public function __construct(ContainerInterface $container, $em, SecurityContext $sc)
    {
        $this->container = $container;
        $this->em = $em;
        $this->sc = $sc;
    }


    public function onKernelRequest(GetResponseEvent $event)
    {

        if( HttpKernelInterface::MASTER_REQUEST != $event->getRequestType() ) {
            return;
        }

        $controller = $event->getRequest()->attributes->get('_controller');
        //echo "controller=".$controller."<br>";
        if( strpos($controller,'Oleg\OrderformBundle') !== false ) {
            // fire custom event e.g. My.db.lookup
            //echo "OlegOrderformBundle controller! <br>";
        } else {
            //echo "other controller! <br>";
            return;
        }

        $maintenanceRoute = 'maintenance_scanorder';
        $scanRoute = 'main_common_home';

        $debug = in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));

        //echo "route=".$event->getRequest()->get('_route')."<b>";

        /////////////// maintanance from DB. Container parameter will be updated only after cleaning the cache //////////////
        $userUtil = new UserUtil();
        $maintenance = $userUtil->getSiteSetting($this->em,'maintenance');
        if( $maintenance == -1 ) {
            return;
        }
        //echo "maintenanceDb=".$maintenanceDb."<br>";
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        if( !$maintenance ) {
            if( $maintenanceRoute === $event->getRequest()->get('_route') ) {
                $url = $this->container->get('router')->generate('logout');
                $response = new RedirectResponse($url);
                $event->setResponse($response);
            }
        }

        //if( 0 ) {
        //if( $maintenance && !$debug && $maintenanceDb ) {
        if( $maintenance && !$debug ) {
        //if( $maintenance ) {

            //echo "route=".$event->getRequest()->get('_route')."<br>";
            //echo "url=".$url."<br>";
            //echo "route=".$route."<br>";

            if( $this->sc->isGranted('IS_AUTHENTICATED_FULLY') ){
                //don't kick out already logged in users
//                $maintenanceMsg = $userUtil->getSiteSetting($this->em,'maintenance');
//                $this->container->get('session')->getFlashBag()->add(
//                    'notice',
//                    'Maintanance!'
//                );
                return;
            }

            if( $maintenanceRoute === $event->getRequest()->get('_route') || $scanRoute === $event->getRequest()->get('_route') ) {
                return;
            } else {
                $url = $this->container->get('router')->generate($maintenanceRoute);
                $response = new RedirectResponse($url);
                $event->setResponse($response);
            }

//            $engine = $this->container->get('templating');
//            $content = $engine->render('OlegOrderformBundle:Default:maintenance.html.twig', array('param'=>null));
//            $event->setResponse(new Response($content, 503));
//            $event->stopPropagation();
        }

    }

} 