<?php

namespace Oleg\OrderformBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

//include_once 'DatabaseRoutines.php';

class WebserviceUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {                  
        // make a call to your webservice here     
        //$userData = ADB_Authenticate($username, $password);                       
        // pretend it returns an array on success, false if there is no user    
        $password = null;
        $salt = null;     
        $roles = array();//array('ROLE_USER');
        return new WebserviceUser($username, $password, $salt, $roles);

        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Oleg\OrderformBundle\Security\User\WebserviceUser';
    }
}

?>
