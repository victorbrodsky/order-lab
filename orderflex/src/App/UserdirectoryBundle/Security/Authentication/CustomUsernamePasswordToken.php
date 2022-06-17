<?php
/**
 * Created by Oleg Ivanov
 * User: oli2002
 * Date: 6/17/2022
 * Time: 6:01 PM
 */

namespace App\UserdirectoryBundle\Security\Authentication;


use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
//use Symfony\Component\Security\Core\User\UserInterface;


class CustomUsernamePasswordToken extends UsernamePasswordToken
{

    private $credentials;


    public function __construct($user, $credentials, string $firewallName, array $roles = [])
    {
        $this->credentials = $credentials ?? null;

        parent::__construct($user,$firewallName,$roles);
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

}