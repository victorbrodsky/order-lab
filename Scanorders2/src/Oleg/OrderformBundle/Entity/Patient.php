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
     * Patient's Clinical Summary
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
    public function __construct( $withfields=false, $status='invalid', $provider=null, $source = null )
    {
        parent::__construct($status,$provider);
        $this->procedure = new ArrayCollection();

        //fields:
        $this->mrn = new ArrayCollection();
        $this->name = new ArrayCollection();
        $this->sex = new ArrayCollection();
        $this->dob = new ArrayCollection();
        $this->age = new ArrayCollection();
        $this->clinicalHistory = new ArrayCollection();

        if( $withfields ) {
            $this->addMrn( new PatientMrn($status,$provider,$source) );
            $this->addDob( new PatientDob($status,$provider,$source) );
            $this->addClinicalHistory( new PatientClinicalHistory($status,$provider,$source) );
            $this->addName( new PatientName($status,$provider,$source) );
            $this->addSex( new PatientSex($status,$provider,$source) );
            $this->addAge( new PatientAge($status,$provider,$source) );
        }

    }

    public function makeDependClone() {
        $this->mrn = $this->cloneDepend($this->mrn,$this);
        $this->name = $this->cloneDepend($this->name,$this);
        $this->sex = $this->cloneDepend($this->sex,$this);
        $this->dob = $this->cloneDepend($this->dob,$this);
        $this->age = $this->cloneDepend($this->age,$this);
        $this->clinicalHistory = $this->cloneDepend($this->clinicalHistory,$this);
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
                //echo "Adding MRN = ".$mrn->getField()."<br>";
                //echo "Adding keytype=".$mrn->getKeytype()."<br>";
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
            $procedure->setPatient($this);
            $this->procedure->add($procedure);
        }

        return $this;
    }

    /**
     * Remove procedure
     *
     * @param \Oleg\OrderformBundle\Entity\Procedure $procedure
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
        if( $clinicalHistory == null ) {
            $clinicalHistory = new PatientClinicalHistory();
        }

        if( !$this->clinicalHistory->contains($clinicalHistory) ) {
            $clinicalHistory->setPatient($this);
            $this->clinicalHistory->add($clinicalHistory);
        }

        return $this;
    }


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

        //echo "Patient add name: name=".$name."<br>";

        if( $name == null ) {
            $name = new PatientName();
        }

        if( !$this->name->contains($name) && !$this->hasSimpleField($name,"getName") ) {
            $name->setPatient($this);
            $this->name->add($name);
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
     * @param \Oleg\OrderformBundle\Entity\PatientAge $age
     * @return Patient
     */
    public function addAge($age)
    {
        if( $age == null ) {
            $age = new PatientAge();
        }

        if( !$this->age->contains($age) && !$this->hasSimpleField($age,"getAge") ) {
            $age->setPatient($this);
            $this->age->add($age);
        }

        return $this;
    }

    /**
     * Remove age
     *
     * @param \Oleg\OrderformBundle\Entity\PatientAge $age
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
        if( $sex == null ) {
            $sex = new PatientSex();
        }

        if( !$this->sex->contains($sex) && !$this->hasSimpleField($sex,"getSex") ) {
            $sex->setPatient($this);
            $this->sex->add($sex);
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
        if( $dob == null ) {
            $dob = new PatientDob();
        }

        if( !$this->dob->contains($dob) ) {
            $dob->setPatient($this);
            $this->dob->add($dob);
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
        $mrns = ", mrnCount=".count($this->mrn).": ";
        foreach( $this->mrn as $mrn ) {
            $mrns = $mrns . $mrn->getField().",".$mrn->getKeytype()."(".$mrn->getStatus().",id=".$mrn->getId().")";
        }

        $orders = ", orderinfosCount=".count($this->getOrderinfo()).": ";
        foreach( $this->getOrderinfo() as $order ) {
            $orders = $orders . "id=".$order->getId().", oid=".$order->getOid();
        }

        $names = ", nameCount=".count($this->name).": ";
        foreach( $this->name as $name ) {
            $names = $names . $name->getField()." (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $sexs = ", sexCount=".count($this->sex).": ";
        foreach( $this->sex as $name ) {
            $sexs = $sexs . $name->getField()." (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $ages = ", ageCount=".count($this->age).": ";
        foreach( $this->age as $name ) {
            $ages = $ages . $name->getField()." (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        return "Patient: id=".$this->id.
        ", mrn=".$this->mrn->first().", mrnID=".$this->mrn->first()->getId().
        //", name=".$this->name->first().", nameID=".$this->name->first()->getId().
        ", names=".$names.
        ", sexs=".$sexs.
        ", ages=".$ages.
        //", age=".$this->age->first().", nameID=".$this->age->first()->getId().
        ", status=".$this->status.
        ", procedureCount=".count($this->procedure).
        //", firstprocedureID=".$this->procedure->first()->getId().
        ", orderinfo=".$orders.
        $mrns."<br>";
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

    public function setChildren($children) {
        $this->setProcedure($children);
    }

    //if simple field already exists. Compare by field name
    public function hasSimpleField( $field, $getMethod ) {

        foreach( $this->$getMethod() as $obj ) {
            if( $obj->getField()."" == $field->getField()."" ) {
                //echo $getMethod.":field exists = ".$field."<br>";
                return true;
            } else {
                //echo $getMethod.":does not exists = ".$field."<br>";
                return false;
            }
        }
        //echo $getMethod.":no loop: field does not = ".$field."<br>";
        return false;
    }

    //don't use 'get' because later repo functions relay on "get" keyword
    public function obtainKeyField() {
        return $this->getMrn();
    }

    public function obtainValidDob() {
        foreach( $this->getDob() as $dob ) {
            if( $dob->getStatus() == 'valid' ) {
                return $dob;
            }
        }
        return null;
    }

//    public function obtainExtraKey() {
//        $extra = array();
//        $extra['keytype'] = $this->getMrn()->getKeytype()->getId();
//        return $extra;
//    }

    public function obtainKeyFieldName() {
        return "mrn";
    }

    public function createKeyField() {
        $this->addMrn( new PatientMrn() );
        return $this->obtainKeyField();
    }

    public function obtainArrayFieldNames() {
        return array('Age','ClinicalHistory');
    }

    public function getArrayFields() {
        $fieldsArr = array('Mrn','Name','Sex','Dob','Age','ClinicalHistory');
        return $fieldsArr;
    }


}