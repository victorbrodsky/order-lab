<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

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


    public function getCarryOverInfo() {
        $infoArr = array();
        foreach( $this->getCarryOvers() as $carryOver ) {
            $days = $carryOver->getDays();
            if( !$days ) {
                $days = "0";
            }
            $infoArr[] = $carryOver->getYear().": ".$days." days";
        }
        return implode("<br>",$infoArr);
    }


    public function __toString()
    {
        return "VacReqCarryOver: Id=".$this->getId()."<br>";
    }
}