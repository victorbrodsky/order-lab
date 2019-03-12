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

        //$this->userUtil = new UserUtil();
    }


    public function onKernelException(GetResponseForExceptionEvent $event) {

        // You get the exception object from the received event
        $exception = $event->getException();
        $message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        //echo "<br><br>";
        //var_dump($exception);
        echo "<br><br>";
        echo "file=".$exception->getFile()."<br>";
        echo "line=".$exception->getLine()."<br>";
        //echo "getStatusCode=".$exception->getStatusCode()."<br>";
        //echo "getHeaders=".$exception->getHeaders()."<br>";

        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($message);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if( $exception instanceof HttpExceptionInterface ) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // sends the modified response object to the event
        $event->setResponse($response);
    }

} 