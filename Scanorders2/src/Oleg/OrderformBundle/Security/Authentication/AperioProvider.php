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

class AperioProvider extends UserAuthenticationProvider {
//class AperioProvider implements AuthenticationProviderInterface {

    private $userProvider;
    //private $encoderFactory;
    private $serviceContainer;

    public function __construct( UserProviderInterface $userProvider, $serviceContainer )
    {
        $this->userProvider = $userProvider;
        //$this->encoderFactory     = $encoderFactory;
        $this->serviceContainer = $serviceContainer;
    }

    public function authenticate( TokenInterface $token )
    {
        //exit("using Aperio Authentication Provider!!!");

        $aperioUtil = new AperioUtil();

        $user = $aperioUtil->aperioAuthenticateToken( $token, $this->serviceContainer );

        //exit("Aperio Authentication");

        if( $user ) {

            //echo("user exists!");

            //TODO: get the firewall name: $this->container->getParameter('fos_user.firewall_name');
            $providerKey = "aperio_ldap_firewall";
            return new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());

            $authenticatedToken = new AperioToken($user->getRoles());
            $authenticatedToken->setUser($user);
            $authenticatedToken->setAuthenticated(true);

            return $authenticatedToken;

            //return $user;
        }

        //exit("Aperio Authentication failed!!!");
        throw new AuthenticationException('The Aperio authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof UsernamePasswordToken;
    }

    protected function retrieveUser($username, UsernamePasswordToken $token) {
        //exit("Aperio Authentication: retrieveUser");
        return null;
    }

    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token) {
        //exit("Aperio Authentication: checkAuthentication");
        //return false;
    }
}
