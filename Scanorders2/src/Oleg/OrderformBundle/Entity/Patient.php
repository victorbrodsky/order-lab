<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//UniqueEntity({"mrn"})

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\PatientRepository")
 * @ORM\Table(name="patient")
 * 
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
    
    //, cascade={"persist"}
    /**
     * ORM\ManyToOne(targetEntity="OrderInfo", inversedBy="patient")
     * ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id")
     */
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="patient")
     **/
    protected $orderinfo; 
    
    //, cascade={"persist"}
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
        $this->orderinfo = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function addSpeciman(\Oleg\OrderformBundle\Entity\Specimen $specimen)
    {
        $this->addSpecimen($specimen);
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
    public function removeSpeciman(\Oleg\OrderformBundle\Entity\Specimen $specimen)
    {
        $this->removeSpecimen($specimen);
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
    
//    public function setSpecimen(\Oleg\OrderformBundle\Entity\Specimen $specimen)
//    {
//        $this->specimen = $specimen;
//    }

    /**
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Patient
     */
    public function addOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
    {
//        $this->orderinfo[] = $orderinfo;
//        return $this;
        
        if( !$this->orderinfo->contains($orderinfo) ) {
            $this->orderinfo->add($orderinfo);
        }    
    }

    /**
     * Remove orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     */
    public function removeOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
    {
        $this->orderinfo->removeElement($orderinfo);
    }

    /**
     * Get orderinfo
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }
    
    public function __toString(){

        $specimen_info = "(";
        $count = 0;
        foreach( $this->specimen as $specimen ) {
            //$patient_info .= 'id='.$patient->getId().", mrn=".$patient->getMrn(). "; ";
            $specimen_info .= $count.":" . $specimen. "; ";
            $count++;
        }
        $specimen_info .= ")";

        return "Patient: id=".$this->id.", mrn=".$this->mrn.", orderinfoCount=".count($this->orderinfo).", specimenCount=".count($this->specimen)." (".$specimen_info.")<br>";
    }
    
}