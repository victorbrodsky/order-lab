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
     * Start academic year. For example, (2015-2016) $year=2015
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