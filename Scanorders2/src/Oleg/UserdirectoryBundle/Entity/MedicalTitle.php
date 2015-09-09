<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_medicalTitle")
 */
class MedicalTitle extends BaseTitle
{

    /**
     * @ORM\ManyToOne(targetEntity="MedicalTitleList")
     * @ORM\JoinColumn(name="name", referencedColumnName="id", nullable=true)
     **/
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="medicalTitles")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToMany(targetEntity="MedicalSpecialties")
     * @ORM\JoinTable(name="user_medicaltitle_medicalspeciality",
     *      joinColumns={@ORM\JoinColumn(name="medicaltitle_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="medicalspeciality_id", referencedColumnName="id")}
     * )
     **/
    protected $specialties;



    /**
     * @ORM\ManyToMany(targetEntity="PositionTypeList")
     * @ORM\JoinTable(name="user_medicalTitle_userPosition",
     *      joinColumns={@ORM\JoinColumn(name="medicalTitle_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userPosition_id", referencedColumnName="id")}
     * )
     **/
    private $userPositions;



    function __construct($author=null)
    {
        parent::__construct($author);

        $this->specialties = new ArrayCollection();

        $this->userPositions = new ArrayCollection();
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


    public function getSpecialties()
    {
        return $this->specialties;
    }
    public function addSpecialty($specialty)
    {
        if( $specialty && !$this->specialties->contains($specialty) ) {
            $this->specialties->add($specialty);
        }
        return $this;
    }
    public function removeSpecialty($specialty)
    {
        $this->specialties->removeElement($specialty);
    }

    public function addUserPosition($item)
    {
        if( !$this->userPositions->contains($item) ) {
            $this->userPositions->add($item);
        }
        return $this;
    }
    public function removeUserPosition($item)
    {
        $this->userPositions->removeElement($item);
    }
    public function getUserPositions()
    {
        return $this->userPositions;
    }


    public function __toString() {
        return "Medical Appointment Title";
    }
}