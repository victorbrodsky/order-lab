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

namespace Oleg\OrderformBundle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Oleg\OrderformBundle\Security\Util\AperioUtil;


class AperioProvider implements AuthenticationProviderInterface {

    private $userProvider;
    private $serviceContainer;
    private $em;
    private $providerKey;
    private $timezone;

    public function __construct( UserProviderInterface $userProvider, $serviceContainer, $em, $providerKey = null, $timezone )
    {
        //echo("constractor Aperio Authentication Provider <br>");
        $this->userProvider = $userProvider;
        $this->serviceContainer = $serviceContainer;
        $this->em = $em;
        $this->providerKey = $providerKey;
        $this->timezone = $timezone;
    }

    //it is called only if the user does not exist in User table
    public function authenticate( TokenInterface $token )
    {
        echo "using Aperio Authentication Provider!!!<br>";
        //exit('aperio auth');

        $aperioUtil = new AperioUtil();

        $user = $aperioUtil->aperioAuthenticateToken( $token, $this->serviceContainer, $this->em );

        //echo "token username=".$token->getUsername()."<br>";

        if( $user !== null ) {
            echo "Aprerio user ok! <br>";
            return new UsernamePasswordToken($user, null, $this->providerKey, $user->getRoles());
        }

        //exit("Aperio Authentication failed!!!");
        throw new AuthenticationException('The Aperio authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof UsernamePasswordToken && $token->getProviderKey() === $this->providerKey;
    }

}
