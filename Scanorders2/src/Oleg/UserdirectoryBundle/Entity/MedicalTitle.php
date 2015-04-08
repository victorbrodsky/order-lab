<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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




    function __construct($author=null)
    {
        parent::__construct($author);

        $this->specialties = new ArrayCollection();
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




    public function __toString() {
        return "Medical Appointment Title";
    }
}