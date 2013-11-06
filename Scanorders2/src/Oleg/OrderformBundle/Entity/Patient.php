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
class Patient extends OrderAbstract implements JsonSerializable
{
    
//    /**
//     * @ORM\Id
//     * @ORM\Column(type="integer")
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="PatientMrn", mappedBy="patient", cascade={"persist"})
     */
    protected $mrn;

    /**
     * @ORM\OneToMany(targetEntity="PatientName", mappedBy="patient", cascade={"persist"})
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="PatientAge", mappedBy="patient", cascade={"persist"})
     */
    protected $age;

    /**
     * @ORM\OneToMany(targetEntity="PatientSex", mappedBy="patient", cascade={"persist"})
     */
    protected $sex;

    /**
     * @ORM\OneToMany(targetEntity="PatientDob", mappedBy="patient", cascade={"persist"})
     */
    protected $dob;

    /**
     * @param \Doctrine\Common\Collections\Collection $property
     * @ORM\OneToMany(targetEntity="PatientClinicalHistory", mappedBy="patient", cascade={"persist"})
     */
    protected $clinicalHistory;
        
//    /**
//     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="patient")
//     **/
//    protected $orderinfo;
    
    //, cascade={"persist"}
    /**
     * Patient might have many Procedures or Procedures
     * 
     * @ORM\OneToMany(targetEntity="Procedure", mappedBy="patient")
     */
    protected $procedure;

//    /**
//     * status: use to indicate if the patient with this mrn is reserved only but not submitted
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $status;

//    /**
//     * @var \DateTime
//     * @ORM\Column(type="datetime", nullable=true)
//     *
//     */
//    private $creationdate;
    
    /**
     * Constructor
     */
    public function __construct( $withfields=false, $validity=0 )
    {
        //$this->orderinfo = new \Doctrine\Common\Collections\ArrayCollection();
        parent::__construct();
        $this->procedure = new \Doctrine\Common\Collections\ArrayCollection();

        //fields:
        $this->mrn = new \Doctrine\Common\Collections\ArrayCollection();
        $this->name = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sex = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dob = new \Doctrine\Common\Collections\ArrayCollection();
        $this->age = new \Doctrine\Common\Collections\ArrayCollection();
        $this->clinicalHistory = new \Doctrine\Common\Collections\ArrayCollection();

        if( $withfields ) {
            $this->addMrn( new PatientMrn($validity) );
            $this->addName( new PatientName($validity) );
            $this->addSex( new PatientSex($validity) );
            $this->addDob( new PatientDob($validity) );
            $this->addAge( new PatientAge($validity) );
            $this->addClinicalHistory( new PatientClinicalHistory($validity) );
        }

    }
    
    /**
     * Get id
     *
     * @return integer 
     */
//    public function getId()
//    {
//        return $this->id;
//    }

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

    public function addMrn($mrn)
    {
        if( $mrn ) {
            if( !$this->mrn->contains($mrn) ) {
                $mrn->setPatient($this);
                $this->mrn->add($mrn);
            }
        }

        return $this;
    }

    public function removeMrn($mrn)
    {
        $this->mrn->removeElement($mrn);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Patient
     */
    public function setName($name)
    {
        echo "set name ";
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
     * Add Procedure
     *
     * @param \Oleg\OrderformBundle\Entity\Procedure $Procedure
     * @return Patient
     */
    public function addProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {
        if( !$this->procedure->contains($procedure) ) {
            $procedure->setPatient($this);
            $this->procedure->add($procedure);
        }

        return $this;
    }
    public function addSpeciman(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {
        $this->addProcedure($procedure);
        return $this;
    }

    /**
     * Remove procedure
     *
     * @param \Oleg\OrderformBundle\Entity\procedure $procedure
     */
    public function removeProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {
        $this->procedure->removeElement($procedure);
    }
    public function removeSpeciman(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {
        $this->removeProcedure($procedure);
    }

    /**
     * Get procedure
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProcedure()
    {
        return $this->procedure;
    }
    
    public function setProcedure(\Doctrine\Common\Collections\ArrayCollection $procedure)
    {
        $this->procedure = $procedure;
    }

    public function clearProcedure(){
        foreach( $this->procedure as $thisprocedure ) {
            $this->removeProcedure($thisprocedure);
        }
    }

//    /**
//     * Add orderinfo
//     *
//     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
//     * @return Patient
//     */
//    public function addOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo=null)
//    {
//        if( !$this->orderinfo->contains($orderinfo) ) {
//            $this->orderinfo->add($orderinfo);
//        }
//    }
//
//    /**
//     * Remove orderinfo
//     *
//     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
//     */
//    public function removeOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
//    {
//        $this->orderinfo->removeElement($orderinfo);
//    }
//
//    /**
//     * Get orderinfo
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getOrderinfo()
//    {
//        return $this->orderinfo;
//    }
//
//    public function __toString(){
//
//        $mrnStr  = "";
//        foreach($this->mrn as $mrn) {
//            $mrnStr = $mrnStr." ".$mrn;
//        }
//        $nameStr  = "";
//        foreach($this->name as $name) {
//            $nameStr = $nameStr." ".$name;
//        }
//        $ageStr  = "";
//        foreach($this->age as $age) {
//            $ageStr = $ageStr." ".$age;
//        }
//        return "Patient: id=".$this->id.", mrnArr=".$mrnStr.", nameArr=".$nameStr.", ageArr=".$ageStr."<br>";
//    }
    

    /**
     * Add clinicalHistory
     *
     * @param \Oleg\OrderformBundle\Entity\PatientClinicalHistory $clinicalHistory
     * @return Patient
     */
    public function addClinicalHistory($clinicalHistory)
    {
        if( $clinicalHistory ) {
            if( !$this->clinicalHistory->contains($clinicalHistory) ) {
//            if( !$this->isExisted($this->clinicalHistory,$clinicalHistory) ) {
                $clinicalHistory->setPatient($this);
                $this->clinicalHistory->add($clinicalHistory);
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
     * @param \Oleg\OrderformBundle\Entity\PatientClinicalHistory $clinicalHistory
     */
    public function removeClinicalHistory($clinicalHistory)
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

//    /**
//     * @ORM\PrePersist
//     */
//    public function setCreationdate()
//    {
//        $this->creationdate = new \DateTime();;
//    }
//
//    /**
//     * @return \DateTime
//     */
//    public function getCreationdate()
//    {
//        return $this->creationdate;
//    }

//    /**
//     * @param mixed $status
//     */
//    public function setStatus($status)
//    {
//        $this->status = $status;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getStatus()
//    {
//        return $this->status;
//    }



    public function jsonSerialize()
    {
        return array(
            'name' => $this->name,
            'id'=> $this->id,
            'age'=> $this->age,
            'sex'=> $this->sex,
        );
    }

    /**
     * Add name
     *
     * @param \Oleg\OrderformBundle\Entity\PatientName $name
     * @return Patient
     */
    public function addName($name)
    {
        if( $name ) {
            if( !$this->name->contains($name) ) {
                $name->setPatient($this);
                $this->name->add($name);
            }
        }
    
        return $this;
    }

    /**
     * Remove name
     *
     * @param \Oleg\OrderformBundle\Entity\PatientName $name
     */
    public function removeName($name)
    {
        $this->name->removeElement($name);
    }

    /**
     * Add age
     *
     * @param \Oleg\OrderformBundle\Entity\PatientName $age
     * @return Patient
     */
    public function addAge($age)
    {
        if( $age ) {
            if( !$this->age->contains($age) ) {
                $age->setPatient($this);
                $this->age->add($age);
            }
        }

        return $this;
    }

    /**
     * Remove age
     *
     * @param \Oleg\OrderformBundle\Entity\PatientName $age
     */
    public function removeAge($age)
    {
        $this->age->removeElement($age);
    }

    /**
     * Add sex
     *
     * @param \Oleg\OrderformBundle\Entity\PatientSex $sex
     * @return Patient
     */
    public function addSex($sex)
    {
        if( $sex ) {
            if( !$this->sex->contains($sex) ) {
                $sex->setPatient($this);
                $this->sex->add($sex);
            }
        }
        return $this;
    }

    /**
     * Remove sex
     *
     * @param \Oleg\OrderformBundle\Entity\PatientSex $sex
     */
    public function removeSex($sex)
    {
        $this->sex->removeElement($sex);
    }

    /**
     * Add dob
     *
     * @param \Oleg\OrderformBundle\Entity\PatientDob $dob
     * @return Patient
     */
    public function addDob($dob)
    {
        if( $dob ) {
            if( !$this->dob->contains($dob) ) {
                $dob->setPatient($this);
                $this->dob->add($dob);
            }
        }
    
        return $this;
    }

    /**
     * Remove dob
     *
     * @param \Oleg\OrderformBundle\Entity\PatientDob $dob
     */
    public function removeDob($dob)
    {
        $this->dob->removeElement($dob);
    }


//    public static function expose()
//    {
//        return get_class_vars(__CLASS__);
//    }
}