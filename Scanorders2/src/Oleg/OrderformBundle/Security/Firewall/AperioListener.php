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
 * Date: 3/18/14
 * Time: 5:59 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

//THIS CLASS NOT USED ANYMORE.
class AperioListener { //implements ListenerInterface  {

    protected $container;
    protected $authenticationManager;
    protected $providerKey;

    public function __construct( $container, AuthenticationManagerInterface $authenticationManager, $providerKey = null)
    {
        $this->$container = $container;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
    }

    public function handle(GetResponseEvent $event)
    {

        //exit("using handle Aperio Listener");

        //use default handle
        return;

        $request = $event->getRequest();
        $username = $request->get('_username');
        $password = $request->get('_password');
        //exit( "username=".$username.", password=".$password."<br>" );

        if( !$username || $username == "" ) {
            //exit( "username=".$username.", password=".$password."<br>" );
            return;
        }

        $unauthenticatedToken = new UsernamePasswordToken(
            $username,
            $password,
            $this->providerKey
        );

        try {

            $authToken = $this->authenticationManager->authenticate($unauthenticatedToken);

            $this->container->get('security.token_storage')->setToken($authToken);

            return;

        } catch (AuthenticationException $failed) {

            //exit('aperio auth error');

            // To deny the authentication clear the token. This will redirect to the login page.
            // Make sure to only clear your token, not those of other authentication listeners.
            $unauthenticatedToken = $this->container->get('security.token_storage')->getToken();
            if( $unauthenticatedToken instanceof AperioToken ) {//&& $this->providerKey === $unauthenticatedToken->getProviderKey()) {
                $this->container->get('security.token_storage')->setToken(null);
            }

        }

        // By default deny authorization
        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }


}