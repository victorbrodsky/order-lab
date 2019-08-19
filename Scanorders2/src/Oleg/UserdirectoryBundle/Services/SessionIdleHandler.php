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

namespace Oleg\UserdirectoryBundle\Services;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

//use Oleg\UserdirectoryBundle\Util\UserUtil;

//This handle will (independently from JS) verify if max idle time out is reached and logout user on the first page redirect or reload

class SessionIdleHandler
{

    protected $container;
    protected $session;
    protected $router;
    protected $maxIdleTime;
    protected $em;

    public function __construct($container, SessionInterface $session, RouterInterface $router, $em )
    {
        $this->container = $container;
        $this->session = $session;
        $this->router = $router;
        $this->em = $em;

        //getMaxIdleTime
        //$userUtil = new UserUtil();
        //$this->maxIdleTime = $userUtil->getMaxIdleTime($this->em);
        $userSecUtil = $this->container->get('user_security_utility');
        $this->maxIdleTime = $userSecUtil->getMaxIdleTime();
        //echo "maxIdleTime=".$this->maxIdleTime."<br>";
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

        if( HttpKernelInterface::MASTER_REQUEST != $event->getRequestType() ) {
            return;
        }

        //*************** set url for redirection ***************//
        $dontSetRedirect = $this->setSessionLastRoute( $event );
        if( $dontSetRedirect > 0 ) {
            return;
        }
        //*************** end of set url for redirection ***************//

        if( $this->maxIdleTime > 0 ) {

            $this->session->start();
            
        if( 0 ) {
                //Don't use getLastUsed(). But it is the same until page is closed.
                $lapse = time() - $this->session->getMetadataBag()->getLastUsed();

                //$msg = "'lapse=".$lapse.", max idle time=".$this->maxIdleTime."'";
                //echo $msg;
                //exit();

        } else {
                //set lastRequest timestamp $this->getUser()->getAttribute('lastRequest');
                $lastRequest = $this->session->get('lastRequest');
                //echo "Handler: lastRequest=".gmdate("Y-m-d H:i:s",$lastRequest)."<br>";
                //echo "Handler: pingCheck=".$this->session->get('pingCheck')."<br>";
                if( !$lastRequest ) {
                    $logger = $this->container->get('logger');
                    $logger->notice("onKernelRequest: set lastRequest to ".time());
                    $this->session->set('lastRequest',time());
                    //$this->session->set('pingCheck','Yes!');
                }

                $lapse = time() - $this->session->get('lastRequest');
                $this->session->set('lastRequest',time());
        }

            if ($lapse > $this->maxIdleTime) {

                $event->setResponse(new RedirectResponse($this->router->generate('logout'))); //idlelogout

            }
        }
    }


    //http://www.fractalizer.ru/frpost_658/symfony2-how-redirect-user-to-a-previous-page-correctly/
    public function setSessionLastRoute( $event ) {

        $dontSetRedirect = 0;

        /** @var \Symfony\Component\HttpFoundation\Request $request  */
        $request = $event->getRequest();
        /** @var \Symfony\Component\HttpFoundation\Session $session  */
        $session = $request->getSession();

        $routeParams = $this->router->match($request->getPathInfo());
        //print_r($routeParams);

        $fullUrl = $_SERVER['REQUEST_URI'];

        $routeName = $routeParams['_route'];
        //echo "<br> kernel routeName=".$routeName."<br>"; exit();

        if( $routeName[0] == '_' ) {
            $dontSetRedirect++;
        }
        //unset($routeParams['_route']);

        $routeData = array('name' => $routeName, 'params' => $routeParams);

        //Skipping duplicates, logins and logout
        $thisRoute = $session->get('this_route', array());

        $pos = strpos( $routeName, "scan-order" );
        if( $pos === false ) {
            //$dontSetRedirect++;
        }

        if(
            //$thisRoute == $routeData['name'] ||
            //$routeName == 'login' ||
            //$routeName == 'scan-nopermission' ||
            //$routeName == 'scan_setloginvisit' ||
            //$routeName == 'employees_setloginvisit' ||
            strpos( $routeName, "login" ) === false ||    
            strpos( $routeName, "_setloginvisit" ) === false ||
            //$routeName == 'logout' ||
            $routeName == 'getmaxidletime' ||
            $routeName == 'isserveractive' ||
            $routeName == 'setserveractive' ||
            $routeName == '_wdt' ||
            $routeName == 'keepalive' ||
            $routeName == 'idlelogout' ||
            strpos( $routeName, "logout" ) === false
            
        ) {
            $dontSetRedirect++;
        }

        $idlelogout = strpos($routeName, 'idlelogout');
        if( $idlelogout ) {
            $dontSetRedirect++;
        }

        if( $dontSetRedirect == 0 ) {
            if( $session->get('last_route_arr') && count($session->get('last_route_arr')) > 0 ) {
                $routeNameArr = $session->get('last_route_arr');
            } else {
                $routeNameArr = array();
                $session->set('last_route_arr',$routeNameArr);
            }
            $target_path = $session->get('_security.external_ldap_firewall.target_path');
            $routeNameArr[] =  $routeName;
            $session->set('last_route', $routeName);
            $session->set('this_route', $routeData);
            $session->set('full_url', $fullUrl);
            $session->set('last_route_arr', $routeNameArr);
            $session->set('target_path', $target_path);
            //echo "set session rout=".$routeName."<br>";
        } else {
            //$session->set('target_path', null);
        }
//        echo "<br> kernel routeName=".$routeName."<br>";
//        $referer = $request->headers->get('referer');
//        echo "referer=".$referer."<br>";
//        print_r($session);
//        exit();
        
        return $dontSetRedirect;
    }


}