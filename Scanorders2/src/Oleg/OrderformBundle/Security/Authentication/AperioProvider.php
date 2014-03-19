<?php

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
    private $providerKey;
    private $timezone;

    public function __construct( UserProviderInterface $userProvider, $serviceContainer, $providerKey = null, $timezone )
    {
        $this->userProvider = $userProvider;
        $this->serviceContainer = $serviceContainer;
        $this->providerKey = $providerKey;
        $this->timezone = $timezone;
    }

    public function authenticate( TokenInterface $token )
    {
        //exit("using Aperio Authentication Provider!!!");

        $aperioUtil = new AperioUtil( $this->timezone );

        $user = $aperioUtil->aperioAuthenticateToken( $token, $this->serviceContainer );

        //echo "token username=".$token->getUsername()."<br>";
        //exit("Aperio Authentication");

        if( $user ) {

            //echo("user exists!");
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
