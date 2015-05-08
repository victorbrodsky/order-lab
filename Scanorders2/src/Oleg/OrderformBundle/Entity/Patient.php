<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//UniqueEntity({"mrn"})

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\PatientRepository")
 * @ORM\Table(name="scan_patient")
 * @ORM\HasLifecycleCallbacks
 */
class Patient extends ObjectAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PatientMrn", mappedBy="patient", cascade={"persist"})
     */
    private $mrn;

    /**
     * @ORM\OneToMany(targetEntity="PatientSuffix", mappedBy="patient", cascade={"persist"})
     */
    private $suffix;

    /**
     * @ORM\OneToMany(targetEntity="PatientLastName", mappedBy="patient", cascade={"persist"})
     */
    private $lastname;

    /**
     * @ORM\OneToMany(targetEntity="PatientFirstName", mappedBy="patient", cascade={"persist"})
     */
    private $firstname;

    /**
     * @ORM\OneToMany(targetEntity="PatientMiddleName", mappedBy="patient", cascade={"persist"})
     */
    private $middlename;

    /**
     * @ORM\OneToMany(targetEntity="PatientSex", mappedBy="patient", cascade={"persist"})
     */
    private $sex;

    /**
     * @ORM\OneToMany(targetEntity="PatientDob", mappedBy="patient", cascade={"persist"})
     */
    private $dob;

    //@ORM\OrderBy({"creationdate" = "DESC", "id" = "DESC"})
    /**
     * Patient's Clinical Summary
     * @param \Doctrine\Common\Collections\Collection $property
     * @ORM\OneToMany(targetEntity="PatientClinicalHistory", mappedBy="patient", cascade={"persist"})
     */
    private $clinicalHistory;
        
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="patient", cascade={"persist"})
     **/
    protected $orderinfo;

    /**
     * Patient might have many encounters (children)
     *
     * @ORM\OneToMany(targetEntity="Encounter", mappedBy="patient")
     */
    private $encounter;



    ///////////////// additional extra fields not shown on scan order /////////////////
    /**
     * @ORM\OneToMany(targetEntity="PatientRace", mappedBy="patient", cascade={"persist"})
     */
    private $race;

    /**
     * @ORM\OneToMany(targetEntity="PatientDeceased", mappedBy="patient", cascade={"persist"})
     */
    private $deceased;

    /**
     * @ORM\OneToMany(targetEntity="PatientContactinfo", mappedBy="patient", cascade={"persist"})
     */
    private $contactinfo;

    /**
     * Hierarchy Tree
     * @ORM\OneToMany(targetEntity="PatientType", mappedBy="patient", cascade={"persist"})
     */
    private $type;
    ///////////////// EOF additional extra fields not shown on scan order /////////////////



    
    /**
     * Constructor
     */
    public function __construct( $withfields=false, $status='invalid', $provider=null, $sourcesystem = null )
    {
        parent::__construct($status,$provider,$sourcesystem);
        $this->encounter = new ArrayCollection();

        //fields:
        $this->mrn = new ArrayCollection();
        $this->suffix = new ArrayCollection();
        $this->lastname = new ArrayCollection();
        $this->firstname = new ArrayCollection();
        $this->middlename = new ArrayCollection();
        $this->sex = new ArrayCollection();
        $this->dob = new ArrayCollection();
        //$this->age = new ArrayCollection();
        $this->clinicalHistory = new ArrayCollection();

        //extra
        $this->race = new ArrayCollection();
        $this->deceased = new ArrayCollection();
        $this->contactinfo = new ArrayCollection();
        $this->type = new ArrayCollection();

        if( $withfields ) {
            $this->addMrn( new PatientMrn($status,$provider,$sourcesystem) );
            $this->addDob( new PatientDob($status,$provider,$sourcesystem) );
            $this->addClinicalHistory( new PatientClinicalHistory($status,$provider,$sourcesystem) );

            //$this->addLastname( new PatientLastname($status,$provider,$sourcesystem) );
            //$this->addFirstname( new PatientFirstname($status,$provider,$sourcesystem) );
            //$this->addMiddlename( new PatientMiddlename($status,$provider,$sourcesystem) );
            //$this->addSex( new PatientSex($status,$provider,$sourcesystem) );
            //$this->addAge( new PatientAge($status,$provider,$sourcesystem) );

            //testing data structure
            //$this->addExtraFields($status,$provider,$sourcesystem);
        }

    }

    public function makeDependClone() {
        $this->mrn = $this->cloneDepend($this->mrn,$this);
        $this->suffix = $this->cloneDepend($this->suffix,$this);
        $this->lastname = $this->cloneDepend($this->lastname,$this);
        $this->firstname = $this->cloneDepend($this->firstname,$this);
        $this->middlename = $this->cloneDepend($this->middlename,$this);
        $this->sex = $this->cloneDepend($this->sex,$this);
        $this->dob = $this->cloneDepend($this->dob,$this);
        //$this->age = $this->cloneDepend($this->age,$this);
        $this->clinicalHistory = $this->cloneDepend($this->clinicalHistory,$this);

        //extra fields
        $this->race = $this->cloneDepend($this->race,$this);
        $this->deceased = $this->cloneDepend($this->deceased,$this);
        $this->contactinfo = $this->cloneDepend($this->contactinfo,$this);
        $this->type = $this->cloneDepend($this->type,$this);

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
            return $years . " yo";
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
     * Add encounter
     *
     * @param \Oleg\OrderformBundle\Entity\Encounter $Encounter
     * @return Patient
     */
    public function addEncounter(\Oleg\OrderformBundle\Entity\Encounter $encounter)
    {
        //echo "add encounter: ".$encounter."<br>";
        if( !$this->encounter->contains($encounter) ) {
            $this->encounter->add($encounter);
            $encounter->setPatient($this);
        }

        return $this;
    }
    /**
     * Remove encounter
     *
     * @param \Oleg\OrderformBundle\Entity\Encounter $encounter
     */
    public function removeEncounter(\Oleg\OrderformBundle\Entity\Encounter $encounter)
    {
        $this->encounter->removeElement($encounter);
    }
    /**
     * Get encounter
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEncounter()
    {
        return $this->encounter;
    }
    public function setEncounter(\Doctrine\Common\Collections\ArrayCollection $encounter)
    {
        $this->encounter = $encounter;
    }
    public function clearEncounter(){
        $this->encounter->clear();
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


    public function addSuffix($suffix)
    {
        if( !$this->suffix->contains($suffix) && !$this->hasSimpleField($suffix,"getSuffix") ) {
            $suffix->setPatient($this);
            $this->suffix->add($suffix);
        }
        return $this;
    }
    public function removeSuffix($suffix)
    {
        $this->suffix->removeElement($suffix);
    }
    public function getSuffix()
    {
        return $this->suffix;
    }


    /**
     * Add lastname
     *
     * @param \Oleg\OrderformBundle\Entity\PatientLastname $lastname
     * @return Patient
     */
    public function addLastname($lastname)
    {

        //echo "Patient add lastname: lastname=".$lastname.", id=".$lastname->getId().", status=".$lastname->getStatus()."<br>";

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


    ///////////////////////// Extra fields /////////////////////////
    public function addExtraFields($status,$provider,$source) {
        $this->addRace( new PatientRace($status,$provider,$source) );
        $this->addDeceased( new PatientDeceased($status,$provider,$source) );
        $this->addContactinfo( new PatientContactinfo($status,$provider,$source) );
        $this->addType( new PatientType($status,$provider,$source) );
    }

    public function getRace()
    {
        return $this->race;
    }
    public function addRace($race)
    {
        if( $race && !$this->race->contains($race) ) {
            $this->race->add($race);
            $race->setPatient($this);
        }

        return $this;
    }
    public function removeRace($race)
    {
        $this->race->removeElement($race);
    }


    public function getDeceased()
    {
        return $this->deceased;
    }
    public function addDeceased($deceased)
    {
        if( $deceased && !$this->deceased->contains($deceased) ) {
            $this->deceased->add($deceased);
            $deceased->setPatient($this);
        }

        return $this;
    }
    public function removeDeceased($deceased)
    {
        $this->deceased->removeElement($deceased);
    }

    public function getContactinfo()
    {
        return $this->contactinfo;
    }
    public function addContactinfo($contactinfo)
    {
        if( $contactinfo && !$this->contactinfo->contains($contactinfo) ) {
            $this->contactinfo->add($contactinfo);
            $contactinfo->setPatient($this);
        }

        return $this;
    }
    public function removeContactinfo($contactinfo)
    {
        $this->contactinfo->removeElement($contactinfo);
    }

    public function getType()
    {
        return $this->type;
    }
    public function addType($item)
    {
        if( $item && !$this->type->contains($item) ) {
            $this->type->add($item);
        }
        return $this;
    }
    public function removeType($item)
    {
        $this->type->removeElement($item);
    }
    ///////////////////////// Extra fields /////////////////////////





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

        $firstnames = ", firstnameCount=".count($this->firstname).": ";
        foreach( $this->firstname as $firstname ) {
            $firstnames = $firstnames . $firstname->getField()." (provider=".$firstname->getProvider().", status=".$firstname->getStatus().") ";
        }

        $lastnames = ", lastnameCount=".count($this->lastname).": ";
        foreach( $this->lastname as $lastname ) {
            $lastnames = $lastnames . $lastname->getField()." (provider=".$lastname->getProvider().", status=".$lastname->getStatus().") ";
        }

        $sexs = ", sexCount=".count($this->sex).": ";
        foreach( $this->sex as $name ) {
            $sexs = $sexs . $name->getField()." (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $clinhists = ", clinicalHistoryCount=".count($this->clinicalHistory).": ";
        foreach( $this->clinicalHistory as $name ) {
            $clinhists = $clinhists . "value=".$name->getField()." (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $dobs = " dobCount=".count($this->dob).": ";
        foreach( $this->dob as $name ) {
            $dobs = $dobs . "value=".$name." (provider=".$name->getProvider().", status=".$name->getStatus().") ";
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
        ", dobs:".$dobs.
        ", clinhists=".$clinhists.
        ", firstnames=".$firstnames.
        ", lastnames=".$lastnames.
        ", sexs=".$sexs.
//        ", ages=".$ages.
        //", age=".$this->age->first().", nameID=".$this->age->first()->getId().
        ", status=".$this->status.
        ", encounterCount=".count($this->encounter).
        //", firstencounterID=".$this->encounter->first()->getId().
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
        return $this->getEncounter();
    }

    public function addChildren($child) {
        $this->addEncounter($child);
    }

    public function removeChildren($child) {
        $this->removeEncounter($child);
    }

    public function setChildren($children) {
        $this->setEncounter($children);
    }

    //object info for blue strip
    public function obtainFullObjectName() {

        $fullNameArr = array();

        $fullPatientName = $this->getFullPatientName();
        if( $fullPatientName && $fullPatientName != "" ) {
            //echo "fullPatientName=".$fullPatientName."<br>";
            $fullNameArr[] = $fullPatientName;
        }

        $calculateAge = $this->calculateAge();
        if( $calculateAge && $calculateAge != "" ) {
            $dob = $this->obtainValidField('dob');
            $fullNameArr[] = "(DOB: " . $dob . "), " . $calculateAge;
        }

        $sex = $this->obtainValidField('sex');
        if( $sex && $sex != "" ) {
            $fullNameArr[] = $sex."";
        }

        //mrn
//        $mrn = $this->obtainValidField('mrn');
//        if( $mrn ) {
//            if( $mrn && $mrn->getKeytype() ) {
//                $fullNameArr[] = $mrn->getKeytype()->getOptimalName() . ": " . $mrn->getField();
//            } else {
//                $fullNameArr[] = $mrn->getField();
//            }
//        }
        $mrnStr = $this->obtainFullValidKeyName();
        if( $mrnStr ) {
            $fullNameArr[] = $mrnStr;
        }

        $fullName = implode(", ",$fullNameArr);

        return $fullName;
    }

    //soShow MRN type "shortest name" (abbreviation, if not available, then short, if empty, then full name)
    //before each MRN value, separated by a colon and a space (example: NYH MRN: 123456)
    public function obtainFullValidKeyName() {
        $keyStr = "";

        //mrn
        $mrn = $this->obtainValidField('mrn');
        if( $mrn ) {
            $keyStr = $mrn->obtainOptimalName();
        }

        return $keyStr;
    }

    public function getFullPatientName() {

        $patientFullNameValid = "";
        $patientFullNameAlias = "";

        $patientFullNameValidArr = $this->patientName('valid');
        $patientFullNameAliasArr = $this->patientName('alias');

        if( $patientFullNameValidArr && count($patientFullNameValidArr) > 0 ) {
            $patientFullNameValid = implode("; ",$patientFullNameValidArr);
        }
        if( $patientFullNameAliasArr && count($patientFullNameAliasArr) > 0 ) {
            $patientFullNameAlias = implode("; ",$patientFullNameAliasArr);
        }

        $patientFullName = $patientFullNameValid;

        if( $patientFullNameAlias ) {
            $patientFullName = $patientFullNameValid . " (" . $patientFullNameAlias . ")";
        }

        return $patientFullName ;
    }


    public function patientName($status) {

        $patientFullNameArr = array();

        if( !$this->getId() || $this->getId() == "" ) {
            return "";
        }

        $firstNameArr = $this->obtainStatusFieldArray('firstname', $status);
        $middleNameArr = $this->obtainStatusFieldArray('middlename', $status);
        $lastNameArr = $this->obtainStatusFieldArray('lastname', $status);
        $suffixArr = $this->obtainStatusFieldArray('suffix', $status);

        //echo "count firstnameArr=".count($firstNameArr)."<br>";
        //echo "count middleNameArr=".count($middleNameArr)."<br>";
        //echo "count lastNameArr=".count($lastNameArr)."<br>";
        //echo "count suffixArr=".count($suffixArr)."<br>";

        $firstNameArrOrder = array();
        $middleNameArrOrder = array();
        $lastNameArrOrder = array();
        $suffixArrOrder = array();

        //get order id array
        $orderArr = array();

        //rearange by orderid as key
        $resArr = $this->rearangeNameArrByOrder($orderArr,$firstNameArr,$firstNameArrOrder);
        $orderArr = $resArr['orderArr'];
        $firstNameArrOrder = $resArr['destArr'];

        $resArr = $this->rearangeNameArrByOrder($orderArr,$middleNameArr,$middleNameArrOrder,array('<i>','</i>'));
        $orderArr = $resArr['orderArr'];
        $middleNameArrOrder = $resArr['destArr'];

        $resArr = $this->rearangeNameArrByOrder($orderArr,$lastNameArr,$lastNameArrOrder,array('<b>','</b>'));
        $orderArr = $resArr['orderArr'];
        $lastNameArrOrder = $resArr['destArr'];

        $resArr = $this->rearangeNameArrByOrder($orderArr,$suffixArr,$suffixArrOrder);
        $orderArr = $resArr['orderArr'];
        $suffixArrOrder = $resArr['destArr'];

        //echo "count orderArr=".count($orderArr)."<br>";

        foreach( $orderArr as $orderId ) {

            //echo "orderId=".$orderId."<br>";

            $patientFullName = "";

            $patientFullName = $this->patientPartialName($patientFullName,$firstNameArrOrder,$orderId,"No First Name Provided");
            $patientFullName = $this->patientPartialName($patientFullName,$middleNameArrOrder,$orderId,"No Middle Name Provided");
            $patientFullName = $this->patientPartialName($patientFullName,$lastNameArrOrder,$orderId,"No Last Name Provided");
            $patientFullName = $this->patientPartialName($patientFullName,$suffixArrOrder,$orderId,"");

            if( $patientFullName != "" ) {
                $patientFullNameArr[] = $patientFullName;
            }

        } //foreach order key

        //echo "count patientFullNameArr=".count($patientFullNameArr)."<br>";

        return $patientFullNameArr;
    }

    public function rearangeNameArrByOrder( $orderArr, $sourceArr, $destArr, $htmlTags = null ) {
        $resArr = array();
        foreach( $sourceArr as $name ) {
            $orderId = $name->getOrderinfo()->getId();
            //echo "orderId=".$orderId."<br>";
            if( !in_array($orderId,$orderArr) ) {
                //echo "!!!!!!!!!add orderId=".$orderId."<br>";
                if( $name."" != "" ) {
                    $orderArr[] = $orderId;
                }
            }
            if( $name."" != "" ) {
                //$status = "[".$name->getStatus()."]";
                $nameStr = $name."";
                if( $htmlTags && count($htmlTags) == 2 ) {
                    $nameStr = $htmlTags[0] . $nameStr . $htmlTags[1];
                }
                $destArr[$orderId] = $nameStr;
            }
        }
        $resArr['orderArr'] = $orderArr;
        $resArr['sourceArr'] = $sourceArr;
        $resArr['destArr'] = $destArr;
        return $resArr;
    }

    public function patientPartialName($patientFullName,$sourceArr,$orderId,$defaultName) {
        if( array_key_exists($orderId, $sourceArr) ) {
            if( $sourceArr[$orderId] != "" ) {
                if( $patientFullName != '' ) {
                    $patientFullName .= ', ';
                }
                $patientFullName .= $sourceArr[$orderId];
            }
        } else {
            if( $patientFullName != '' && $defaultName != '' ) {
                $patientFullName .= ', ';
            }
            if( $defaultName != '' ) {
                $patientFullName .= $defaultName;
            }
        }
        return $patientFullName;
    }


    //if simple field already exists. Compare by field name. This is to prevent creating similar fields
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

    public function obtainNoprovidedKeyPrefix() {
        return $name = "NOMRNPROVIDED";
    }

    public function createKeyField() {
        $this->addMrn( new PatientMrn() );
        return $this->obtainKeyField();
    }

    public function obtainArrayFieldNames() {
        return array('ClinicalHistory');
    }

    public function getArrayFields() {
        $fieldsArr = array(
            'Mrn','Suffix','Lastname','Firstname','Middlename','Sex','Dob','ClinicalHistory',
            //extra fields
            'Race','Deceased','Contactinfo'
        );
        return $fieldsArr;
    }

    //obtain only valid, not empty clinical histories
    public function obtainAllValidNotEmptyClinicalHistories()
    {
        $res = new ArrayCollection();
        $clinHists = $this->getClinicalHistory();
        foreach( $clinHists as $clinHist ) {

            if( $clinHist->getField() != "" && $clinHist->getStatus() == 'valid' ) {
                $res->add($clinHist);
            }
        }
        return $res;
    }

}