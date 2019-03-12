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

namespace Oleg\UserdirectoryBundle\Services;


use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oleg\UserdirectoryBundle\Util\UserUtil;

class ExceptionListener {

    private $container;
    private $em;
    protected $secTokenStorage;
    protected $secAuthChecker;
    private $logger;

    public function __construct(ContainerInterface $container, $em)
    {
        $this->container = $container;
        $this->em = $em;
        $this->logger = $this->container->get('logger');

        $this->secAuthChecker = $container->get('security.authorization_checker');
        $this->secTokenStorage = $container->get('security.token_storage');
    }


    public function onKernelException(GetResponseForExceptionEvent $event) {

        $userSecUtil = $this->container->get('user_security_utility');
        $user = $this->secTokenStorage->getToken()->getUser();

        $request = $event->getRequest();

        // You get the exception object from the received event
        $exception = $event->getException();

//        $message = sprintf(
//            'Error: %s with code: %s',
//            $exception->getMessage(),
//            $exception->getCode()
//
//        );

        $message = "Error: " . $exception->getMessage() . " with code" . $exception->getCode() .
            "<br>File: ".$exception->getFile() .
            "<br>Line: ".$exception->getLine()
        ;

        if( $request ) {
            $domain = $request->getSchemeAndHttpHost();
        } else {
            $domain = "Unknown Server";
        }

        //echo "<br><br>";
        //echo "file=".$exception->getFile()."<br>";
        //echo "line=".$exception->getLine()."<br>";

        //exit('111');

        if( $userSecUtil->getSiteSettingParameter('emailCriticalError') === true ) {

            if( $request ) {
                $controller = $request->attributes->get('_controller');
                $sitename = $this->getSiteName($controller);
            } else {
                $sitename = "employees";
            }

            //$emails = $userSecUtil->getUserEmailsByRole($sitename,"Platform Administrator");
            //echo "emails: <br>";
            //print_r($emails);

            //2- If the checkbox is checked, Send an email to all users with System Administrator role saying:
            $subject = "Server Critical Error";
            $dateStr = date("m/d/Y \\a\\t H:i:s");
            //exit("dateStr=".$dateStr);
            //On MM/DD/YYYY, at HH:MM:SS the following error has been logged on the [server domain name/C.MED.CORNELL.EDU vs Collage, or IP address etc]: [text of error]
            $msg = "On $dateStr the following error has been logged on the $domain";
            $msg = $msg . ": <br>" . $message;

            //EventLog
            $userSecUtil->createUserEditEvent($sitename,$msg,$user,null,$request,"Critical Error Email Sent");

            //exit('Yes emailCriticalError');
        } else {
            //exit('NO emailCriticalError');
        }

        if( $userSecUtil->getSiteSettingParameter('restartServerErrorCounter') === true ) {
            $msg = $domain . " has been restarted";
            //EventLog
            $userSecUtil->createUserEditEvent($sitename,$msg,$user,null,$request,"Restart Server");
        }

//        // You get the exception object from the received event
//        $exception = $event->getException();
//        $message = sprintf(
//            'My Error says: %s with code: %s',
//            $exception->getMessage(),
//            $exception->getCode()
//        );
//
//        //echo "<br><br>";
//        //var_dump($exception);
//        echo "<br><br>";
//        echo "file=".$exception->getFile()."<br>";
//        echo "line=".$exception->getLine()."<br>";
//        //echo "getStatusCode=".$exception->getStatusCode()."<br>";
//        //echo "getHeaders=".$exception->getHeaders()."<br>";
//
//        // Customize your response object to display the exception details
//        $response = new Response();
//        $response->setContent($message);
//
//        // HttpExceptionInterface is a special type of exception that
//        // holds status code and header details
////        if( $exception instanceof HttpExceptionInterface ) {
////            $response->setStatusCode($exception->getStatusCode());
////            $response->headers->replace($exception->getHeaders());
////        } else {
////            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
////        }
//
//        // sends the modified response object to the event
//        $event->setResponse($response);
    }

    public function getSiteName($controller) {
        if( strpos($controller,'Oleg\UserdirectoryBundle') !== false ) {
            return "employees";
        }
        if( strpos($controller,'Oleg\OrderformBundle') !== false ) {
            return "scan";
        }
        if( strpos($controller,'Oleg\FellAppBundle') !== false ) {
            return "fellapp";
        }
        if( strpos($controller,'Oleg\DeidentifierBundle') !== false ) {
            return "deidentifier";
        }
        if( strpos($controller,'Oleg\VacReqBundle') !== false ) {
            return "vacreq";
        }
        if( strpos($controller,'Oleg\CallLogBundle') !== false ) {
            return "calllog";
        }
        if( strpos($controller,'Oleg\TranslationalResearchBundle') !== false ) {
            return "translationalresearch";
        }

        return null;
    }

} 