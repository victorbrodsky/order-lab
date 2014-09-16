<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="location")
 */
class Location extends BaseUserAttributes
{

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $pager;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $mobile;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fax;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $room;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $street;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $zip;

    /**
     * Associated NYPH Code
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $associatedCode;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="assistant", referencedColumnName="id")
     */
    private $assistant;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="locations")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", options={"default" = 1}, nullable=true)
     */
    private $removable;


    public function __construct() {
        $this->setRemovable(true);
        parent::__construct();
    }

    /**
     * @param mixed $assistant
     */
    public function setAssistant($assistant)
    {
        $this->assistant = $assistant;
    }

    /**
     * @return mixed
     */
    public function getAssistant()
    {
        return $this->assistant;
    }

    /**
     * @param mixed $associatedCode
     */
    public function setAssociatedCode($associatedCode)
    {
        $this->associatedCode = $associatedCode;
    }

    /**
     * @return mixed
     */
    public function getAssociatedCode()
    {
        return $this->associatedCode;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return mixed
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $pager
     */
    public function setPager($pager)
    {
        $this->pager = $pager;
    }

    /**
     * @return mixed
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $room
     */
    public function setRoom($room)
    {
        $this->room = $room;
    }

    /**
     * @return mixed
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
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
     * @param mixed $removable
     */
    public function setRemovable($removable)
    {
        $this->removable = $removable;
    }

    /**
     * @return mixed
     */
    public function getRemovable()
    {
        return $this->removable;
    }





}