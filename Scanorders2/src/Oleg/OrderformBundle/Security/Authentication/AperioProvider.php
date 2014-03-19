<?php

namespace Oleg\OrderformBundle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;
use Oleg\OrderformBundle\Security\Authentication\AperioToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

use Oleg\OrderformBundle\Security\Util\AperioUtil;

//class AperioProvider extends UserAuthenticationProvider {
class AperioProvider implements AuthenticationProviderInterface {

    private $userProvider;
    private $serviceContainer;
    private $providerKey;

    public function __construct( UserProviderInterface $userProvider, $serviceContainer, $providerKey = null )
    {
        $this->userProvider = $userProvider;
        $this->serviceContainer = $serviceContainer;
        $this->providerKey = $providerKey;
    }

    public function authenticate( TokenInterface $token )
    {
        //exit("using Aperio Authentication Provider!!!");

        $aperioUtil = new AperioUtil();

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
        //return $token instanceof UsernamePasswordToken;
        return $token instanceof UsernamePasswordToken && $token->getProviderKey() === $this->providerKey;
    }

//    protected function retrieveUser($username, UsernamePasswordToken $token) {
//        //exit("Aperio Authentication: retrieveUser");
//        return null;
//    }
//
//    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token) {
//        //exit("Aperio Authentication: checkAuthentication");
//        //return false;
//    }
}
