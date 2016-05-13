<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/11/2016
 * Time: 11:35 AM
 */

namespace Oleg\VacReqBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="vacreq_userCarryOver")
 */
class VacReqUserCarryOver
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $user;


    /**
     * @ORM\OneToMany(targetEntity="VacReqCarryOver", mappedBy="userCarryOver", cascade={"persist","remove"})
     **/
    private $carryOvers;




    public function __construct($user) {
        $this->carryOvers = new ArrayCollection();
        $this->setUser($user);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
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
    public function getCarryOvers()
    {
        return $this->carryOvers;
    }
    public function addCarryOver($item)
    {
        if( $item && !$this->carryOvers->contains($item) ) {
            $this->carryOvers->add($item);
            $item->setUserCarryOver($this);
        }
        return $this;
    }
    public function removeCarryOver($item)
    {
        $this->carryOvers->removeElement($item);
    }

    public function getCarryOverByYear( $year ) {
        foreach( $this->getCarryOvers() as $carryOver ) {
            if( $carryOver->getYear() == $year ) {
                return $carryOver;
            }
        }
        return null;
    }


    public function __toString()
    {
        return "VacReqCarryOver: Id=".$this->getId()."<br>";
    }
}