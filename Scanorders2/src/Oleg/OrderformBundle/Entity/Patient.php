<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use JsonSerializable;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//UniqueEntity({"mrn"})

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\PatientRepository")
 * @ORM\Table(name="patient")
 * @ORM\HasLifecycleCallbacks
 */
class Patient implements JsonSerializable
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
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
//    /**
//     * @param \Doctrine\Common\Collections\Collection $property
//     * @ORM\OneToMany(targetEntity="Age", mappedBy="patient", cascade={"persist"})
//     */
//    protected $age;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=20)
     */
    protected $sex;
    
    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $dob;

    /**
     * @param \Doctrine\Common\Collections\Collection $property
     * @ORM\OneToMany(targetEntity="ClinicalHistory", mappedBy="patient", cascade={"persist"})
     */
    protected $clinicalHistory;
        
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
     * status: use to indicate if the patient with this mrn is reserved only but not submitted
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     *
     */
    private $creationdate;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderinfo = new \Doctrine\Common\Collections\ArrayCollection();
        $this->specimen = new \Doctrine\Common\Collections\ArrayCollection();
        $this->clinicalHistory = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add specimen
     *
     * @param \Oleg\OrderformBundle\Entity\Specimen $specimen
     * @return Patient
     */
    public function addSpecimen(\Oleg\OrderformBundle\Entity\Specimen $specimen)
    {
        if( !$this->specimen->contains($specimen) ) {
            $specimen->setPatient($this);
            $this->specimen->add($specimen);
        }

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
    
    public function setSpecimen(\Doctrine\Common\Collections\ArrayCollection $specimen)
    {
        $this->specimen = $specimen;
    }

    public function clearSpecimen(){
        foreach( $this->specimen as $thisspecimen ) {
            $this->removeSpecimen($thisspecimen);
        }
    }

    /**
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Patient
     */
    public function addOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
    {
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
    

    /**
     * Add clinicalHistory
     *
     * @param \Oleg\OrderformBundle\Entity\ClinicalHistory $clinicalHistory
     * @return Patient
     */
    public function addClinicalHistory($clinicalHistory)
    {
        if( $clinicalHistory != null ) {
            if( !$this->clinicalHistory->contains($clinicalHistory) ) {
//            if( !$this->isExisted($this->clinicalHistory,$clinicalHistory) ) {
                $clinicalHistory->setPatient($this);
                $this->clinicalHistory[] = $clinicalHistory;
            }
        }

        return $this;
    }

//    public function isExisted( $clinicalHistories, $clinicalHistory ) {
//        foreach( $clinicalHistories as $thisHist ) {
//            if( $thisHist->getClinicalHistory() == $clinicalHistory->getClinicalHistory() ) {
//                return true;
//            }
//        }
//        return false;
//    }

    /**
     * Remove clinicalHistory
     *
     * @param \Oleg\OrderformBundle\Entity\ClinicalHistory $clinicalHistory
     */
    public function removeClinicalHistory(\Oleg\OrderformBundle\Entity\ClinicalHistory $clinicalHistory)
    {
        $this->clinicalHistory->removeElement($clinicalHistory);
    }

    /**
     * Get clinicalHistory
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClinicalHistory()
    {
        return $this->clinicalHistory;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreationdate()
    {
        $this->creationdate = new \DateTime();;
    }

    /**
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
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



    public function jsonSerialize()
    {
        return array(
            'name' => $this->name,
            'id'=> $this->id,
            'age'=> $this->age,
            'sex'=> $this->sex,
        );
    }
}