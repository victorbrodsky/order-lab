<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: oli2002
 * Date: 8/8/14
 * Time: 4:20 PM
 */

namespace App\UserdirectoryBundle\Services;


//use Symfony\Component\HttpKernel\Event\GetResponseEvent;
//use Doctrine\ORM\EntityManager;
use App\UserdirectoryBundle\Entity\SiteList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\SecurityBundle\Security;


class MaintenanceListener {

    private $container;
    private $em;
    private $security;
    private $logger;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, Security $security)
    {
        $this->container = $container;
        $this->em = $em;
        $this->logger = $this->container->get('logger');
        $this->security = $security;
    }


    public function onKernelRequest(RequestEvent $event)
    {

//        if( $this->security->isGranted('IS_AUTHENTICATED_FULLY') ) {
//            exit('auth users');
//        } else {
//            exit('not auth users');
//        }

//        if( HttpKernelInterface::MASTER_REQUEST != $event->getRequestType() ) {
//            return;
//        }

//        ///////// Testing tenantprefix /////////
//        $tenantprefix = $this->container->getParameter('tenantprefix');
//        //echo "current tenantprefix=".$tenantprefix."<br>";
//        //$tenantprefix = 'c/lmh/pathology/';
//        $tenantprefix = 'pathology';
//        //$this->container->setParameter('tenantprefix', $tenantprefix);
//        $this->container->get('router')->getContext()->setParameter('tenantprefix', $tenantprefix);
//        ///////// EOF Testing tenantprefix /////////

        //Symfony\Component\HttpKernel\Event\KernelEvent::isMasterRequest()" is deprecated, use "isMainRequest()" instead.
        if( !$event->isMainRequest() ) {
            //exit('1');
            return;
        }

//        ///////// Testing tenantprefix: {tenantprefix} in config /////////
//        $tenantprefix = $this->container->getParameter('tenantprefix');
//        //echo "current tenantprefix=".$tenantprefix."<br>";
//        //$tenantprefix = 'c/lmh/pathology/';
//        $tenantprefix = 'pathology';
//        //$this->container->setParameter('tenantprefix', $tenantprefix);
//        $this->container->get('router')->getContext()->setParameter('tenantprefix', $tenantprefix);
//        ///////// EOF Testing tenantprefix /////////


        $userSecUtil = $this->container->get('user_security_utility');

        $controller = $event->getRequest()->attributes->get('_controller');
        //echo "controller=".$controller."<br>";

        //get route name
        $request = $event->getRequest();
        //$routeName = $request->get('_route');
        $uri = $request->getUri();
        //echo "uri=".$uri."<br>";
        //exit('1');

        //site check accessibility
        if(
            strpos((string)$uri, '/common') === false &&
            strpos((string)$uri, '/util') === false &&
            strpos((string)$uri, '/check/') === false &&
            strpos((string)$uri, '/admin/') === false
        ) {
            $sitename = $this->getSiteName($controller);

//            if( $userSecUtil->isSiteAccessible($sitename) ) {
//                echo $sitename.": site accessible <br>";
//            } else {
//                echo $sitename.": site is not accessible <br>";
//            }

            if( $sitename && $userSecUtil->isSiteAccessible($sitename) === false ) {
                $siteObject = $this->em->getRepository(SiteList::class)->findOneByAbbreviation($sitename);
                //echo $sitename.": ".$siteObject." <br>";
                if( $siteObject ) {
                    $systemEmail = $userSecUtil->getSiteSettingParameter('siteEmail');
                    //exit('not accessible');

                    $session = $request->getSession(); //$this->container->get('session');
                    $session->getFlashBag()->add(
                        'warning',
                        $siteObject->getSiteName() . " site is not currently accessible. If you have any questions, please contact $systemEmail."
                    );

                    $url = $this->container->get('router')->generate('main_common_home');
                    $response = new RedirectResponse($url);
                    $event->setResponse($response);
                }
            }
        }


        if( 
                strpos((string)$controller,'App\UserdirectoryBundle') !== false || 
                strpos((string)$controller,'App\OrderformBundle') !== false ||
                strpos((string)$controller,'App\FellAppBundle') !== false ||
                strpos((string)$controller,'App\ResAppBundle') !== false ||
                strpos((string)$controller,'App\DeidentifierBundle') !== false ||
                strpos((string)$controller,'App\VacReqBundle') !== false ||
                strpos((string)$controller,'App\CallLogBundle') !== false ||
                strpos((string)$controller,'App\CrnBundle') !== false ||
                strpos((string)$controller,'App\TranslationalResearchBundle') !== false ||
                strpos((string)$controller,'App\DashboardBundle') !== false
        ) {
            // fire custom event e.g. My.db.lookup
            //echo "Sites controller! <br>";
        } else {
            //echo "other controller! <br>";
            return;
        }

        if( $event->getRequest()->get('_route') == "first-time-login-generation-init" ||
            $event->getRequest()->get('_route') == "first-time-login-generation-init-https"
        ) {
            return;
        }

        $maintenanceRoute = 'main_maintenance';
        $scanRoute = 'main_common_home';

        //echo "route=".$event->getRequest()->get('_route')."<b>";

        /////////////// maintanance from DB. Container parameter will be updated only after cleaning the cache //////////////
        //$maintenance = $this->userUtil->getSiteSetting($this->em,'maintenance');
        $maintenance = $userSecUtil->getSiteSettingParameter('maintenance');

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

        $debug = in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'));

        //if( 0 ) {
        //if( $maintenance && !$debug && $maintenanceDb ) {
        if( $maintenance && !$debug ) {
        //if( $maintenance ) {

            //echo "route=".$event->getRequest()->get('_route')."<br>";
            //echo "urlLogout=".$urlLogout."<br>";
            //echo "route=".$route."<br>";
            //echo "token=".$this->secTokenStorage->getToken()."<br>";
            //exit('maintenance mode');

//            if( null === $this->secTokenStorage->getToken() ) {
//                //exit('token not set');
//            } else {

//                if(
//                    //$this->secAuthChecker->isGranted('IS_AUTHENTICATED_FULLY')
//                    $this->security->isGranted('IS_AUTHENTICATED_FULLY')
//                ) {
//                    //don't kick out already logged in users
//                    //exit('do not kick out already logged in users');
//                    return;
//                }

                if(
                    //$this->secAuthChecker->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')
                    $this->security->isGranted('IS_AUTHENTICATED_FULLY')
                ) {
                    //don't kick out already logged in users
                    //exit('do not kick out already logged in users');
                    return;
                }

                //exit('token set');
//            }

//            if( strpos((string)$event->getRequest()->get('_route'),'login_check') !== false ) {
            if( strpos((string)$event->getRequest()->get('_route'),'login') !== false && $event->getRequest()->isMethod('POST') ) {
                //exit('login check');
                $url = $this->container->get('router')->generate($maintenanceRoute);
                $response = new RedirectResponse($url);
                $event->setResponse($response);
                return;
            }

            if( strpos((string)$event->getRequest()->get('_route'),'_login') !== false || strpos((string)$event->getRequest()->get('_route'),'_logout') !== false ) {
                //exit('login or logout page. route='.$event->getRequest()->get('_route'));
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

    public function getSiteName($controller) {
        if( strpos((string)$controller,'App\UserdirectoryBundle') !== false ) {
            return "employees";
        }
        if( strpos((string)$controller,'App\OrderformBundle') !== false ) {
            return "scan";
        }
        if( strpos((string)$controller,'App\FellAppBundle') !== false ) {
            return "fellapp";
        }
        if( strpos((string)$controller,'App\ResAppBundle') !== false ) {
            return "resapp";
        }
        if( strpos((string)$controller,'App\DeidentifierBundle') !== false ) {
            return "deidentifier";
        }
        if( strpos((string)$controller,'App\VacReqBundle') !== false ) {
            return "vacreq";
        }
        if( strpos((string)$controller,'App\CallLogBundle') !== false ) {
            return "calllog";
        }
        if( strpos((string)$controller,'App\CrnBundle') !== false ) {
            return "crn";
        }
        if( strpos((string)$controller,'App\TranslationalResearchBundle') !== false ) {
            return "translationalresearch";
        }
        if( strpos((string)$controller,'App\DashboardBundle') !== false ) {
            return "dashboard";
        }

        return null;
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