<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 3/18/14
 * Time: 5:45 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class AperioToken extends AbstractToken {

    private $sessionId;
    private $username;
    private $credentials;

    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
    }

    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }



}