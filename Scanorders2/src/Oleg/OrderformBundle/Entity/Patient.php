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
 * @ORM\HasLifecycleCallbacks
 */
class Patient extends OrderAbstract
{

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
        
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="patient")
     **/
    protected $orderinfo;
    
    //, cascade={"persist"}
    /**
     * Patient might have many Procedures or Procedures (children)
     * 
     * @ORM\OneToMany(targetEntity="Procedure", mappedBy="patient")
     */
    protected $procedure;
    
    /**
     * Constructor
     */
    public function __construct( $withfields=false, $validity=0 )
    {
        parent::__construct();
        $this->procedure = new ArrayCollection();

        //fields:
        $this->mrn = new ArrayCollection();
        $this->name = new ArrayCollection();
        $this->sex = new ArrayCollection();
        $this->dob = new ArrayCollection();
        $this->age = new ArrayCollection();
        $this->clinicalHistory = new ArrayCollection();

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

    public function clearMrn()
    {
        $this->mrn->clear();
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
     * Add Procedure
     *
     * @param \Oleg\OrderformBundle\Entity\Procedure $Procedure
     * @return Patient
     */
    public function addProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {
        //echo "add procedure: ".$procedure."<br>";
        if( !$this->procedure->contains($procedure) ) {
//        if( !$this->containsChild($procedure) ) {
            $procedure->setPatient($this);
            $this->procedure->add($procedure);
        }

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
        $this->procedure->clear();
    }

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

    public function __toString()
    {
        return "Patient: id=".$this->id.
        ", mrn=".$this->mrn->first().
        ", name=".$this->name->first().
        ", orderinfo=".count($this->orderinfo)."<br>";
    }



    //parent, children, key field methods
    public function setParent($parent) {
        return null; //no parent for patient
    }

    public function getParent() {
        return null; //no parent for patient
    }

    public function getChildren() {
        return $this->getProcedure();
    }

    public function addChildren($child) {
        $this->addProcedure($child);
    }

    public function removeChildren($child) {
        $this->removeProcedure($child);
    }

    //don't use 'get' because later repo functions relay on "get" keyword
    public function obtainKeyField() {
        return $this->getMrn();
    }

    public function obtainExtraKey() {
        $extra = array();
        $extra['mrntype'] = $this->getMrn()->getMrntype()->getId();
        return $extra;
    }

    public function obtainKeyFieldName() {
        return "mrn";
    }

    public function createKeyField() {
        $this->addMrn( new PatientMrn(1) );
        return $this->obtainKeyField();
    }

    //TODO: not used. Remove it later.
    //check if procedure-accession is exists
    //$entity - procedure
    public function containsChild($entity) {
        //echo $entity;
//        if( count($entity->getChildren()) != 1 ) {
//            throw $this->createNotFoundException( 'This Object must have only one child. Number of children=' . count($entity->getChildren()) );
//        }
        //echo "procedure count=".count($this->procedure)."<br>";
        foreach( $this->procedure as $procedure ) {
            $acc1 = $entity->getChildren()->first()->getValidKeyfield();
            $acc2 = $procedure->getChildren()->first()->getValidKeyfield();
            //echo "compare: ".$acc1."?=".$acc2."<br>";
            if( $acc1."" == $acc2."" ) {
                echo "exists!!! <br>";
                return true;
            }
        }
        echo "not exists!!! <br>";
        return false;
    }


}