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
        //exit("using Aperio Authentication Provider!!!");

        $aperioUtil = new AperioUtil();

        $user = $aperioUtil->aperioAuthenticateToken( $token, $this->serviceContainer, $this->em );

        //echo "token username=".$token->getUsername()."<br>";

        if( $user !== null ) {
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
