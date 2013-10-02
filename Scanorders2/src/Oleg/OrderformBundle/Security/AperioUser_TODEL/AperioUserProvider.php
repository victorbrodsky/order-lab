<?php

namespace Oleg\OrderformBundle\Security\AperioUser;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Oleg\OrderformBundle\Security\Authentication\Provider\AperioProvider;

/**
 * Provides users from Ldap
 */
class AperioUserProvider implements UserProviderInterface
{
    private $serviceContainer;

//    public function __construct(LdapDriverInterface $driver, $serviceContainer)
//    {
//        $this->serviceContainer = $serviceContainer;
//    }


    public function setServiceContainer($serviceContainer) {
        $this->serviceContainer = $serviceContainer;
    }


    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        echo "Aperio load user by Username ... <br>";
//        $aperioProvider = new AperioProvider('',$this->serviceContainer);

        $request = $this->serviceContainer->get('request');

        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        //exit("password=".$password);

//        $token = new AperioUserToken();
//        $token->username = $username;
//        $token->digest = $password;

        $userManager = $this->serviceContainer->get('fos_user.user_manager');   //TODO: remove it
        $user = $userManager->createUser();

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return true;
    }

}
