<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 3/4/15
 * Time: 12:23 PM
 */

namespace Oleg\UserdirectoryBundle\Security\Authentication;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class CustomAuthenticator implements SimpleFormAuthenticatorInterface {

    private $encoder;
    private $sc;
    private $em;

    public function __construct(UserPasswordEncoderInterface $encoder,$sc,$em)
    {
        $this->encoder = $encoder;
        $this->sc = $sc;                //Service Container
        $this->em = $em;                //Entity Manager
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        echo "CustomAuthenticator: username=".$token->getUsername()."<br>"; //", pwd=".$token->getCredentials()
        //exit();

        $authUtil = new AuthUtil($this->sc,$this->em);

        //$userSecUtil = $this->sc->get('user_security_utility');
        //$usernamePrefix = $userSecUtil->getUsernamePrefix($token->getUsername());

        ///////////////////// first aperio authentication /////////////////////
        $user = $authUtil->AperioAuthentication($token, $userProvider);

        if( $user ) {
            return $this->getUsernamePasswordToken($user,$providerKey);
        }


        ///////////////////// second ldap authentication /////////////////////
        $user = $authUtil->LdapAuthentication($token, $userProvider);

        if( $user ) {
            return $this->getUsernamePasswordToken($user,$providerKey);
        }

        //exit('all failed');

        throw new AuthenticationException('Invalid username or password');
    }



    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken
        && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }



    public function getUsernamePasswordToken($user,$providerKey) {
        return new UsernamePasswordToken(
            $user,
            NULL,   //$user->getPassword(),
            $providerKey,
            $user->getRoles()
        );
    }

} 