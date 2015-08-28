<?php
/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 8/8/14
 * Time: 4:20 PM
 */

namespace Oleg\UserdirectoryBundle\Services;


use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Util\UserUtil;

class MaintenanceListener {

    private $container;
    private $em;
    private $sc;
    private $logger;

    public function __construct(ContainerInterface $container, $em, SecurityContext $sc)
    {
        $this->container = $container;
        $this->em = $em;
        $this->sc = $sc;
        $this->logger = $this->container->get('logger');
    }


    public function onKernelRequest(GetResponseEvent $event)
    {

//        if( HttpKernelInterface::MASTER_REQUEST != $event->getRequestType() ) {
//            return;
//        }

        if( !$event->isMasterRequest() ) {
            return;
        }

        $controller = $event->getRequest()->attributes->get('_controller');
        //echo "controller=".$controller."<br>";
        if( strpos($controller,'Oleg\UserdirectoryBundle') !== false || strpos($controller,'Oleg\OrderformBundle') !== false ) {
            // fire custom event e.g. My.db.lookup
            //echo "Sites controller! <br>";
        } else {
            //echo "other controller! <br>";
            return;
        }

        $maintenanceRoute = 'main_maintenance';
        $scanRoute = 'main_common_home';

        $debug = in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));

        //echo "route=".$event->getRequest()->get('_route')."<b>";

        /////////////// maintanance from DB. Container parameter will be updated only after cleaning the cache //////////////
        $userUtil = new UserUtil();
        $maintenance = $userUtil->getSiteSetting($this->em,'maintenance');

        //echo "maint list =".$maintenance."<br>";

        if( $maintenance === -1 ) {
            //site settings are not exist
            return;
        }

        //echo "maintenance=".$maintenance."<br>";
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//        if( !$maintenance ) {
//            //exit('no maint');
//            if( $maintenanceRoute === $event->getRequest()->get('_route') ) {
//                $urlLogout = $this->container->get('router')->generate('logout');
//                $response = new RedirectResponse($urlLogout);
//                $event->setResponse($response);
//            }
//        }

        //if( 0 ) {
        //if( $maintenance && !$debug && $maintenanceDb ) {
        if( $maintenance && !$debug ) {
        //if( $maintenance ) {

            //echo "route=".$event->getRequest()->get('_route')."<br>";
            //echo "urlLogout=".$urlLogout."<br>";
            //echo "route=".$route."<br>";
            //echo "token=".$this->sc->getToken()."<br>";
            //exit('maintenance mode');

            if( null === $this->sc->getToken() ) {
                //exit('token not set');
            } else {

                if( $this->sc->isGranted('IS_AUTHENTICATED_FULLY') ) {
                    //don't kick out already logged in users
                    //exit('do not kick out already logged in users');
                    return;
                }

                if( $this->sc->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
                    //don't kick out already logged in users
                    //exit('do not kick out already logged in users');
                    return;
                }

                //exit('token set');
            }

            if( strpos($event->getRequest()->get('_route'),'login_check') !== false ) {
                //exit('login check');
                $url = $this->container->get('router')->generate($maintenanceRoute);
                $response = new RedirectResponse($url);
                $event->setResponse($response);
                return;
            }

            if( strpos($event->getRequest()->get('_route'),'_login') !== false || strpos($event->getRequest()->get('_route'),'_logout') !== false ) {
                return;
            }

            if( $maintenanceRoute === $event->getRequest()->get('_route') || $scanRoute === $event->getRequest()->get('_route') ) {
                //exit('maint route');
                return;
            }

            //exit('2');
            $url = $this->container->get('router')->generate($maintenanceRoute);
            $response = new RedirectResponse($url);
            $event->setResponse($response);


        }

    }


//    //perform heavy jobs
//    public function onKernelTerminate(PostResponseEvent $event) {
//
//        $request = $event->getRequest();
//        $routeName = $request->get('_route');
//
//        //echo 'Kernel Terminate: route=' . $routeName . "<br>";
//
//        $this->logger->debug('Kernel Terminate: route=' . $routeName);
//
//        //generate fellapp report
//        if( $routeName === "fellapp_update" ) {
//            $this->updateReport($request);
//            return;
//        }
//
//        //employees_file_delete
//    }


//    public function updateReport($request) {
//        $id = $request->get('id');
//        //$id = $response->getContent();    //->get('id');
//
//        $this->logger->notice('fellapp id='.$id);
//
//        //update report
//        $fellappUtil = $this->container->get('fellapp_util');
//        $fellappUtil->addFellAppReportToQueue( $id );
//    }

} 