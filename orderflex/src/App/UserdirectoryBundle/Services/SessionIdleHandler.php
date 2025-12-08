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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 2/7/14
 * Time: 3:03 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

//This handle will (independently from JS) verify if max idle time out is reached and logout user on the first page redirect or reload

class SessionIdleHandler
{

    protected $container;
    protected $router;
    protected $maxIdleTime;
    protected $em;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, RouterInterface $router )
    {
        $this->container = $container;
        $this->router = $router;
        $this->em = $em;

        $userSecUtil = $this->container->get('user_security_utility');
        $this->maxIdleTime = $userSecUtil->getMaxIdleTime();
    }

    public function onKernelRequest(RequestEvent $event)
    {
        //echo "maxIdleTime=".$this->maxIdleTime."<br>";exit('111');

//        if( HttpKernelInterface::MASTER_REQUEST != $event->getRequestType() ) {
//            return;
//        }
        if (!$event->isMainRequest()) {
            // don't do anything if it's not the main request
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        //$this->maxIdleTime = 3;//sec testing

        if( $this->maxIdleTime > 0 ) {

            $session->start();

            $lapse = time() - $session->getMetadataBag()->getLastUsed();

            if ($lapse > $this->maxIdleTime) {
                //exit("$lapse > ".$this->maxIdleTime);

                //Added for autologin: Save current URL so we can send user back here after login
                //$currentUrl = $request->getSchemeAndHttpHost() . $request->getRequestUri();
                //$session->set('idle_last_route', $currentUrl);

                $event->setResponse(new RedirectResponse($this->router->generate('employees_idlelogout')));
                //$event->setResponse(new RedirectResponse($this->router->generate('logout'))); //idlelogout
                //$event->setResponse(new RedirectResponse($this->router->generate('employees_login')));
            }
        }
    }




//    //NOT USED
//    public function onKernelRequest_ORIG(RequestEvent $event)
//    {
//        //echo "maxIdleTime=".$this->maxIdleTime."<br>";exit('111');
//        $request = $event->getRequest();
//        $session = $request->getSession();
//
//        if( HttpKernelInterface::MASTER_REQUEST != $event->getRequestType() ) {
//            return;
//        }
//
//        //*************** set url for redirection ***************//
//        $dontSetRedirect = $this->setSessionLastRoute( $event );
//        if( $dontSetRedirect > 0 ) {
//            //exit('$dontSetRedirect');
//            return;
//        }
//        //*************** end of set url for redirection ***************//
//
//        if( $this->maxIdleTime > 0 ) {
//
//            $session->start();
//
//            if( 0 ) {
//                    //Don't use getLastUsed(). But it is the same until page is closed.
//                    $lapse = time() - $session->getMetadataBag()->getLastUsed();
//
//                    //$msg = "'lapse=".$lapse.", max idle time=".$this->maxIdleTime."'";
//                    //echo $msg;
//                    //exit();
//
//            } else {
//                    //set lastRequest timestamp $this->getUser()->getAttribute('lastRequest');
//                    $lastRequest = $session->get('lastRequest');
//                    //echo "Handler: lastRequest=".gmdate("Y-m-d H:i:s",$lastRequest)."<br>";
//                    //echo "Handler: pingCheck=".$session->get('pingCheck')."<br>";
//                    //exit('111');
//                    if( !$lastRequest ) {
//                        $logger = $this->container->get('logger');
//                        $logger->notice("onKernelRequest: set lastRequest to ".time());
//                        $session->set('lastRequest',time());
//                        //$session->set('pingCheck','Yes!');
//                    }
//
//                    $lapse = time() - $session->get('lastRequest');
//                    $session->set('lastRequest',time());
//            }
//
//            if ($lapse > $this->maxIdleTime) {
//
//                $event->setResponse(new RedirectResponse($this->router->generate('logout'))); //idlelogout
//
//            }
//        }
//    }
//    //NOT USED
//    //http://www.fractalizer.ru/frpost_658/symfony2-how-redirect-user-to-a-previous-page-correctly/
//    public function setSessionLastRoute( $event ) {
//
//        $dontSetRedirect = 0;
//
//        /** @var \Symfony\Component\HttpFoundation\Request $request  */
//        $request = $event->getRequest();
//        /** @var \Symfony\Component\HttpFoundation\Session $session  */
//        $session = $request->getSession();
//
//        $routeParams = $this->router->match($request->getPathInfo());
//        //print_r($routeParams);
//
//        $fullUrl = $_SERVER['REQUEST_URI'];
//
//        $routeName = $routeParams['_route'];
//        //echo "<br> kernel routeName=".$routeName."<br>";
//        //exit();
//
////        if( $routeName[0] == '_' ) {
////            echo "dontSetRedirect: routeName _<br>";
////            $dontSetRedirect++;
////        }
//        //unset($routeParams['_route']);
//
//        $routeData = array('name' => $routeName, 'params' => $routeParams);
//
//        //Skipping duplicates, logins and logout
//        //$thisRoute = $session->get('this_route', array());
//
////        $pos = strpos((string)$routeName, "scan-order" );
////        if( $pos === false ) {
////            //$dontSetRedirect++;
////        }
//
//        if(
//            strpos((string)$routeName, "login" ) === false ||
//            strpos((string)$routeName, "_setloginvisit" ) === false ||
//            //$routeName == 'logout' ||
//            $routeName == 'getmaxidletime' ||
//            $routeName == 'isserveractive' ||
//            $routeName == 'setserveractive' ||
//            $routeName == '_wdt' ||
//            $routeName == 'keepalive' ||
//            $routeName == 'idlelogout' ||
//            strpos((string)$routeName, "logout" ) === false
//
//        ) {
//            $dontSetRedirect++;
//            //echo "dontSetRedirect: 1<br>";
//        }
//
//        $idlelogout = strpos((string)$routeName, 'idlelogout');
//        if( $idlelogout ) {
//            $dontSetRedirect++;
//        }
//
//        if( $dontSetRedirect == 0 ) {
//            if( $session->get('last_route_arr') && count($session->get('last_route_arr')) > 0 ) {
//                $routeNameArr = $session->get('last_route_arr');
//            } else {
//                $routeNameArr = array();
//                $session->set('last_route_arr',$routeNameArr);
//            }
//            $target_path = $session->get('_security.external_ldap_firewall.target_path');
//            $routeNameArr[] =  $routeName;
//            $session->set('last_route', $routeName);
//            $session->set('this_route', $routeData);
//            $session->set('full_url', $fullUrl);
//            $session->set('last_route_arr', $routeNameArr);
//            $session->set('target_path', $target_path);
//            //echo "set session rout=".$routeName."<br>";
//        } else {
//            //$session->set('target_path', null);
//        }
////        echo "<br> kernel routeName=".$routeName."<br>";
////        $referer = $request->headers->get('referer');
////        echo "referer=".$referer."<br>";
////        print_r($session);
////        exit();
//
//        return $dontSetRedirect;
//    }


}