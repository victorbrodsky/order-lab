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
    public function __construct(string $username, $credentials, $usernametype, string $firewallName, array $roles = [])
    {
        $this->credentials = $credentials ?? null;
        $this->username = $username ?? null;
        $this->usernametype = $usernametype ?? null;

        //parent::__construct($user,$firewallName,$roles);
        parent::__construct($roles);

        if ('' === $firewallName) {
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


}