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
class Patient extends ObjectAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PatientMrn", mappedBy="patient", cascade={"persist"})
     */
    protected $mrn;

    /**
     * @ORM\OneToMany(targetEntity="PatientLastName", mappedBy="patient", cascade={"persist"})
     */
    protected $lastname;

    /**
     * @ORM\OneToMany(targetEntity="PatientFirstName", mappedBy="patient", cascade={"persist"})
     */
    protected $firstname;

    /**
     * @ORM\OneToMany(targetEntity="PatientMiddleName", mappedBy="patient", cascade={"persist"})
     */
    protected $middlename;

//    /**
//     * @ORM\OneToMany(targetEntity="PatientAge", mappedBy="patient", cascade={"persist"})
//     */
//    protected $age;

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
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="patients")
     * @ORM\JoinColumn(name="institution", referencedColumnName="id")
     */
    protected $institution;
    
    /**
     * Constructor
     */
    public function __construct( $withfields=false, $status='invalid', $provider=null, $source = null )
    {
        parent::__construct($status,$provider);
        $this->procedure = new ArrayCollection();

        //fields:
        $this->mrn = new ArrayCollection();
        $this->lastname = new ArrayCollection();
        $this->firstname = new ArrayCollection();
        $this->middlename = new ArrayCollection();
        $this->sex = new ArrayCollection();
        $this->dob = new ArrayCollection();
        //$this->age = new ArrayCollection();
        $this->clinicalHistory = new ArrayCollection();

        if( $withfields ) {
            $this->addMrn( new PatientMrn($status,$provider,$source) );
            $this->addDob( new PatientDob($status,$provider,$source) );
            $this->addClinicalHistory( new PatientClinicalHistory($status,$provider,$source) );
            //$this->addLastname( new PatientLastname($status,$provider,$source) );
            //$this->addFirstname( new PatientFirstname($status,$provider,$source) );
            //$this->addMiddlename( new PatientMiddlename($status,$provider,$source) );
            //$this->addSex( new PatientSex($status,$provider,$source) );
            //$this->addAge( new PatientAge($status,$provider,$source) );
        }

    }

    public function makeDependClone() {
        $this->mrn = $this->cloneDepend($this->mrn,$this);
        $this->lastname = $this->cloneDepend($this->lastname,$this);
        $this->firstname = $this->cloneDepend($this->firstname,$this);
        $this->middlename = $this->cloneDepend($this->middlename,$this);
        $this->sex = $this->cloneDepend($this->sex,$this);
        $this->dob = $this->cloneDepend($this->dob,$this);
        //$this->age = $this->cloneDepend($this->age,$this);
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

//    /**
//     * Set age
//     *
//     * @param integer $age
//     * @return Patient
//     */
//    public function setAge($age)
//    {
//        $this->age = $age;
//
//        return $this;
//    }
//
//    /**
//     * Get age
//     *
//     * @return integer
//     */
//    public function getAge()
//    {
//        return $this->age;
//    }

    public function calculateAgeInt() {
        $age = null;
        $dob = $this->obtainValidField('dob');

        $years = 0;

        if( $dob != null ) {
            $date = new \DateTime($dob);
            $now = new \DateTime();
            $interval = $now->diff($date);

            $years = $interval->format('%y');
        }

        if( $years < 0 )
            $years = 0;

        return $years;
    }

    //calculate age based on the dob and current date
    public function calculateAge() {
        $age = null;
        $dob = $this->obtainValidField('dob');

        $years = 0;
        $months = 0;
        $days = 0;
        $daysFull = 0;

        if( $dob != null ) {
            $date = new \DateTime($dob);
            $now = new \DateTime();
            $interval = $now->diff($date);

            $years = $interval->format('%y');
            $months = $interval->format('%m');
            $days = $interval->format('%d');
            //$fullMonths = ($years * 12) + $months;
            $daysFull = $interval->days;
            //echo "years=".$years.", months=".$months.", days=".$days.", fullMonths=".$fullMonths.", daysFull=".$daysFull."<br>";
        }

        //If the age is less than 1 day, show the age as "less than 1 day".
        if( $daysFull > 0 && $years < 1 && $months < 1 && $days < 1 ) {
            return "less than 1 day";
        }

        //If the age is less than 1 month, show the age in days and show the word "day(s)"; for example: "16 day(s)"
        if( $daysFull > 0 && $years < 1 && $months < 1 ) {
            return $days . " day(s)";
        }

        //If the age is less than 1 year, give the age in months and show the word "month(s)"; for example: "3 month(s)".
        if( $daysFull > 0 && $years < 1 ) {
            return $months . " month(s)";
        }

        //If the age is less than 1 year, give the age in months and show the word "month(s)"; for example: "3 month(s)".
        if( $daysFull > 0 && $years > 0 ) {
            return $years;
        }

        return "";
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
     * Add lastname
     *
     * @param \Oleg\OrderformBundle\Entity\PatientLastname $lastname
     * @return Patient
     */
    public function addLastname($lastname)
    {

        //echo "Patient add lastname: lastname=".$lastname."<br>";

//        if( $lastname == null ) {
//            $lastname = new PatientLastname();
//        }

        if( !$this->lastname->contains($lastname) && !$this->hasSimpleField($lastname,"getLastname") ) {
            $lastname->setPatient($this);
            $this->lastname->add($lastname);
        }

        return $this;
    }

    /**
     * Remove lastname
     *
     * @param \Oleg\OrderformBundle\Entity\PatientLastname $lastname
     */
    public function removeLastname($lastname)
    {
        $this->lastname->removeElement($lastname);
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return Patient
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }


    /**
     * Add firstname
     *
     * @param \Oleg\OrderformBundle\Entity\PatientFirstname $firstname
     * @return Patient
     */
    public function addFirstname($firstname)
    {

        //echo "Patient add firstname: firstname=".$firstname."<br>";

//        if( $firstname == null ) {
//            $firstname = new PatientFirstname();
//        }

        if( !$this->firstname->contains($firstname) && !$this->hasSimpleField($firstname,"getFirstname") ) {
            $firstname->setPatient($this);
            $this->firstname->add($firstname);
        }

        return $this;
    }

    /**
     * Remove firstname
     *
     * @param \Oleg\OrderformBundle\Entity\PatientFirstname $firstname
     */
    public function removeFirstname($firstname)
    {
        $this->firstname->removeElement($firstname);
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Patient
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }


    /**
     * Add middlename
     *
     * @param \Oleg\OrderformBundle\Entity\PatientMiddlename $middlename
     * @return Patient
     */
    public function addMiddlename($middlename)
    {

        //echo "Patient add middlename: middlename=".$middlename."<br>";

//        if( $middlename == null ) {
//            $middlename = new PatientMiddlename();
//        }

        if( !$this->middlename->contains($middlename) && !$this->hasSimpleField($middlename,"getMiddlename") ) {
            $middlename->setPatient($this);
            $this->middlename->add($middlename);
        }

        return $this;
    }

    /**
     * Remove middlename
     *
     * @param \Oleg\OrderformBundle\Entity\PatientMiddlename $middlename
     */
    public function removeMiddlename($middlename)
    {
        $this->middlename->removeElement($middlename);
    }

    /**
     * Set middlename
     *
     * @param string $middlename
     * @return Patient
     */
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;

        return $this;
    }

    /**
     * Get middlename
     *
     * @return string
     */
    public function getMiddlename()
    {
        return $this->middlename;
    }


//    /**
//     * Add age
//     *
//     * @param \Oleg\OrderformBundle\Entity\PatientAge $age
//     * @return Patient
//     */
//    public function addAge($age)
//    {
////        if( $age == null ) {
////            $age = new PatientAge();
////        }
//
//        if( !$this->age->contains($age) && !$this->hasSimpleField($age,"getAge") ) {
//            $age->setPatient($this);
//            $this->age->add($age);
//        }
//
//        return $this;
//    }
//
//    /**
//     * Remove age
//     *
//     * @param \Oleg\OrderformBundle\Entity\PatientAge $age
//     */
//    public function removeAge($age)
//    {
//        $this->age->removeElement($age);
//    }

    /**
     * Add sex
     *
     * @param \Oleg\OrderformBundle\Entity\PatientSex $sex
     * @return Patient
     */
    public function addSex($sex)
    {
//        if( $sex == null ) {
//            $sex = new PatientSex();
//        }

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

        $lastnames = ", lastnameCount=".count($this->lastname).": ";
        foreach( $this->lastname as $lastname ) {
            $lastnames = $lastnames . $lastname->getField()." (provider=".$lastname->getProvider().", status=".$lastname->getStatus().") ";
        }

        $sexs = ", sexCount=".count($this->sex).": ";
        foreach( $this->sex as $name ) {
            $sexs = $sexs . $name->getField()." (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

//        $ages = ", ageCount=".count($this->age).": ";
//        foreach( $this->age as $name ) {
//            $ages = $ages . $name->getField()." (provider=".$name->getProvider().", status=".$name->getStatus().") ";
//        }

        $mrnId = "N/A";
        if( $this->mrn->first() ) {
            $mrnId = $this->mrn->first()->getId();
        }

        return "Patient: id=".$this->id.
        ", mrn=".$this->mrn->first().", mrnID=".$mrnId.
        ", mrnCount=".count($this->mrn).
        ", lastnames=".$lastnames.
        ", sexs=".$sexs.
//        ", ages=".$ages.
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

    public function getFullPatientName() {
        $patientFullName = "";

        //echo "lastname=".implode(",",$this->getLastname())."<br>";

        if( $this->getLastname() && $this->getLastname()->first() && $this->getLastname()->first()->getField() ) {
            $patientFullName .= '<b>'.$this->getLastname()->first()->getField().'</b>';
        } else {
            $patientFullName .= "No Last Name Provided";
        }

        if( $this->getFirstname() && $this->getFirstname()->first() && $this->getFirstname()->first()->getField() ) {
            if( $patientFullName != '' ) {
                $patientFullName .= ', ';
            }
            $patientFullName .= $this->getFirstname()->first()->getField();
        } else {
            if( $patientFullName != '' ) {
                $patientFullName .= ', ';
            }
            $patientFullName .= "No First Name Provided";
        }

        if( $this->getMiddlename() && $this->getMiddlename()->first() && $this->getMiddlename()->first()->getField() ) {
            if( $patientFullName != '' ) {
                $patientFullName .= ' ';
            }
            $patientFullName .= '<i>'.$this->getMiddlename()->first()->getField().'</i>';
        }

        return $patientFullName;
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

//    public function obtainValidDob() {
//        foreach( $this->getDob() as $dob ) {
//            if( $dob->getStatus() == 'valid' ) {
//                return $dob;
//            }
//        }
//        return null;
//    }

//    public function obtainExtraKey() {
//        $extra = array();
//        $extra['keytype'] = $this->getMrn()->getKeytype()->getId();
//        return $extra;
//    }

    public function obtainOneValidObjectPatient() {
        //mrn
        $mrn = $this->obtainValidField('mrn');
        $this->mrn->clear();
        $this->addMrn($mrn);

        //dob
        $dob = $this->obtainValidField('dob');
        $this->dob->clear();
        $this->addDob($dob);

        //clinical history
        $clinicalHistory = $this->obtainValidField('clinicalHistory');
        $this->clinicalHistory->clear();
        $this->addClinicalHistory($clinicalHistory);
    }

    public function obtainKeyFieldName() {
        return "mrn";
    }

    public function createKeyField() {
        $this->addMrn( new PatientMrn() );
        return $this->obtainKeyField();
    }

    public function obtainArrayFieldNames() {
        return array('ClinicalHistory');
    }

    public function getArrayFields() {
        $fieldsArr = array('Mrn','Lastname','Firstname','Middlename','Sex','Dob','ClinicalHistory');
        return $fieldsArr;
    }


}