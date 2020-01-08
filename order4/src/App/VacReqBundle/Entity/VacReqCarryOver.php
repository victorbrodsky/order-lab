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

namespace App\VacReqBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="vacreq_carryOver")
 */
class VacReqCarryOver
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
     * @ORM\ManyToOne(targetEntity="VacReqUserCarryOver", inversedBy="carryOvers", cascade={"persist"})
     * @ORM\JoinColumn(name="userCarryOver_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $userCarryOver;


    /**
     * Start academic year of the destination year range. For example, (2015-2016) $year=2015
     * Days carry over to the this (destination) academic year
     * @ORM\Column(type="string", nullable=true)
     */
    private $year;

    /**
     * Carry over days to $year from previous year
     * @ORM\Column(type="integer", nullable=true)
     */
    private $days;




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
    public function getUserCarryOver()
    {
        return $this->userCarryOver;
    }

    /**
     * @param mixed $userCarryOver
     */
    public function setUserCarryOver($userCarryOver)
    {
        $this->userCarryOver = $userCarryOver;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param mixed $days
     */
    public function setDays($days)
    {
        $this->days = $days;
    }

    public function getYearRange() {
        $endYear = (int)$this->getYear() + 1;
        //echo "endYear=".$endYear."<br>";
        $yearRange = $this->getYear() . "-" . $endYear;
        return $yearRange;
    }



    public function __toString()
    {
        return "VacReqCarryOver: user=".$this->getUserCarryOver()->getUser().": year=".$this->getYear()."; days=".$this->getDays()."<br>";
    }

}