<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/22/14
 * Time: 10:19 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_userWrapper")
 */
class UserWrapper {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $userStr;

    /**
     * User object
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;





    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $userStr
     */
    public function setUserStr($userStr)
    {
        $this->userStr = $userStr;
    }

    /**
     * @return mixed
     */
    public function getUserStr()
    {
        return $this->userStr;
    }

    public function __toString() {
        return $this->getUser()." ".$this->getUserStr();
    }

    //get user id or user string
    //used for transformer
    public function getEntity() {
        if( $this->getUser() && $this->getUser()->getId() ) {
            return $this->getUser()->getId();
        }
        if( $this->getUserStr() ) {
            return $this->getUserStr();
        }
        return null;
    }

}