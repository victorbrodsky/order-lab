<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_tracking")
 */
class Tracking {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     */
    private $status;

    /**
     * Order source location
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
     */
    private $pickLocation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     */
    private $pickDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $pickfromPerson;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
     */
    private $dropLocation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     */
    private $dropDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $droptoPerson;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;








    /**
     * The person who carried on delivery
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $person;

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \DateTime $dropDate
     */
    public function setDropDate($dropDate)
    {
        $this->dropDate = $dropDate;
    }

    /**
     * @return \DateTime
     */
    public function getDropDate()
    {
        return $this->dropDate;
    }

    /**
     * @param mixed $dropLocation
     */
    public function setDropLocation($dropLocation)
    {
        $this->dropLocation = $dropLocation;
    }

    /**
     * @return mixed
     */
    public function getDropLocation()
    {
        return $this->dropLocation;
    }

    /**
     * @param mixed $droptoPerson
     */
    public function setDroptoPerson($droptoPerson)
    {
        $this->droptoPerson = $droptoPerson;
    }

    /**
     * @return mixed
     */
    public function getDroptoPerson()
    {
        return $this->droptoPerson;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
    }

    /**
     * @return mixed
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param \DateTime $pickDate
     */
    public function setPickDate($pickDate)
    {
        $this->pickDate = $pickDate;
    }

    /**
     * @return \DateTime
     */
    public function getPickDate()
    {
        return $this->pickDate;
    }

    /**
     * @param mixed $pickLocation
     */
    public function setPickLocation($pickLocation)
    {
        $this->pickLocation = $pickLocation;
    }

    /**
     * @return mixed
     */
    public function getPickLocation()
    {
        return $this->pickLocation;
    }

    /**
     * @param mixed $pickfromPerson
     */
    public function setPickfromPerson($pickfromPerson)
    {
        $this->pickfromPerson = $pickfromPerson;
    }

    /**
     * @return mixed
     */
    public function getPickfromPerson()
    {
        return $this->pickfromPerson;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }


}