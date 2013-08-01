<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\PatientRepository")
 * @ORM\Table(name="patient")
 * @UniqueEntity({"mrn"})
 */
class Patient
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)   
     * @Assert\NotBlank
     */
    protected $mrn;   
    
    /**
     * @ORM\Column(type="string", nullable=true, length=500)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="smallint", nullable=true, length=3)
     */
    protected $age;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=20)
     */
    protected $sex;
    
    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $dob;
    
    /**
     * @ORM\Column(type="text", nullable=true, length=10000)
     */
    protected $clinicalHistory;
    
    /**
     * @ORM\ManyToOne(targetEntity="OrderInfo", inversedBy="patient", cascade={"persist"})
     * @ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id")
     */
    protected $orderinfo; 
    
    /**
     * Patient might have many Specimens or Procedures
     * 
     * @ORM\OneToMany(targetEntity="Specimen", mappedBy="patient")
     */
    protected $specimen;
 

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->specimen = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mrn
     *
     * @param string $mrn
     * @return Patient
     */
    public function setMrn($mrn)
    {
        $this->mrn = $mrn;
    
        return $this;
    }

    /**
     * Get mrn
     *
     * @return string 
     */
    public function getMrn()
    {
        return $this->mrn;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Patient
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set age
     *
     * @param integer $age
     * @return Patient
     */
    public function setAge($age)
    {
        $this->age = $age;
    
        return $this;
    }

    /**
     * Get age
     *
     * @return integer 
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set sex
     *
     * @param string $sex
     * @return Patient
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
    
        return $this;
    }

    /**
     * Get sex
     *
     * @return string 
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set dob
     *
     * @param \DateTime $dob
     * @return Patient
     */
    public function setDob($dob)
    {
        $this->dob = $dob;
    
        return $this;
    }

    /**
     * Get dob
     *
     * @return \DateTime 
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * Set clinicalHistory
     *
     * @param string $clinicalHistory
     * @return Patient
     */
    public function setClinicalHistory($clinicalHistory)
    {
        $this->clinicalHistory = $clinicalHistory;
    
        return $this;
    }

    /**
     * Get clinicalHistory
     *
     * @return string 
     */
    public function getClinicalHistory()
    {
        return $this->clinicalHistory;
    }

    /**
     * Set orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Patient
     */
    public function setOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo = null)
    {
        $this->orderinfo = $orderinfo;
    
        return $this;
    }

    /**
     * Get orderinfo
     *
     * @return \Oleg\OrderformBundle\Entity\OrderInfo 
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }

    /**
     * Add specimen
     *
     * @param \Oleg\OrderformBundle\Entity\Specimen $specimen
     * @return Patient
     */
    public function addSpecimen(\Oleg\OrderformBundle\Entity\Specimen $specimen)
    {
        $specimen->setPatient($this);
        $this->specimen[] = $specimen;   
        return $this;
    }

    /**
     * Remove specimen
     *
     * @param \Oleg\OrderformBundle\Entity\Specimen $specimen
     */
    public function removeSpecimen(\Oleg\OrderformBundle\Entity\Specimen $specimen)
    {
        $this->specimen->removeElement($specimen);
    }

    /**
     * Get specimen
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }
}