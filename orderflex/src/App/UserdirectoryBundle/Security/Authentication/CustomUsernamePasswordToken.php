<?php
/**
 * Created by Oleg Ivanov
 * User: oli2002
 * Date: 6/17/2022
 * Time: 6:01 PM
 */

namespace App\UserdirectoryBundle\Security\Authentication;


//use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class CustomUsernamePasswordToken extends AbstractToken //UsernamePasswordToken
{

    private $firewallName;
    private $username;
    private $credentials;
    private $usernametype;


    //UserInterface $user,
    public function __construct(
        string $username,
        $credentials,
        $usernametype,
        string $firewallName,
        array $roles = []
    )
    {
        $this->credentials = $credentials ?? null;
        $this->username = $username ?? null;
        $this->usernametype = $usernametype ?? null;

        //parent::__construct($user,$firewallName,$roles);
        parent::__construct($roles);

        if( '' === $firewallName ) {
            throw new \InvalidArgumentException('$firewallName must not be empty.');
        }

        //$this->setUser($user);
        $this->firewallName = $firewallName;
    }


    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        parent::eraseCredentials();

        $this->credentials = null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername( $username )
    {
        return $this->username = $username;
    }

    /**
     * @return null
     */
    public function getUsernametype()
    {
        return $this->usernametype;
    }

    /**
     * @param null $usernametype
     */
    public function setUsernametype($usernametype)
    {
        $this->usernametype = $usernametype;
    }

    //overwrite to avoid error :
    //Original exception message=Symfony\Component\Security\Core\Authentication\Token\AbstractToken::setUser():
    // Argument #1 ($user) must be of type Symfony\Component\Security\Core\User\UserInterface, null given
    // called in /usr/local/bin/order-lab-tenantappdemo/orderflex/vendor/symfony/security-http/Firewall/ContextListener.php
    // on line 209" at AbstractToken.php line 59
    //Trigger: user's addRole or removeRole calls refreshUser()
    //https://stackoverflow.com/questions/59301420/authentication-problem-user-must-be-an-instanceof-userinterface
    //As soon as a user group (ROLES) was changed, the error occurred because the session user could not be refreshed.
    //In order to allow a role change without the user being logged out or receiving an error message,
    // a separate isEqualTo() method must be integrated in your User entity.
    // See: https://symfony.com/doc/current/security/user_provider.html#comparing-users-manually-with-equatableinterface
    //https://stackoverflow.com/questions/59879834/security-downsides-of-using-equatableuserinterface
    public function setUser(UserInterface $user=null): void
    {
        if( $user ) {
            $this->user = $user;
        }
    }

}