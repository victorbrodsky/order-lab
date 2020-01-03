<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\UserdirectoryBundle\Entity\GeoLocation;
use App\UserdirectoryBundle\Entity\Location;
use App\UserdirectoryBundle\Entity\Spot;
use App\UserdirectoryBundle\Entity\Tracker;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//UniqueEntity({"mrn"})

/**
 * @ORM\Entity(repositoryClass="App\OrderformBundle\Repository\PatientRepository")
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
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="patient", cascade={"persist"})
     **/
    protected $message;

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

//    /**
//     * @ORM\OneToMany(targetEntity="PatientContactinfo", mappedBy="patient", cascade={"persist"})
//     */
//    private $contactinfo;

    /**
     * Hierarchy Tree
     * @ORM\OneToMany(targetEntity="PatientType", mappedBy="patient", cascade={"persist"})
     */
    private $type;
    ///////////////// EOF additional extra fields not shown on scan order /////////////////

//    /**
//     * Master Merge Record
//     * @ORM\Column(type="boolean", nullable=true)
//     */
//    private $masterMergeRecord;
    /**
     * Master Merge Record
     * @ORM\OneToMany(targetEntity="PatientMasterMergeRecord", mappedBy="patient", cascade={"persist"})
     */
    private $masterMergeRecord;

    /**
     * @ORM\ManyToOne(targetEntity="PatientRecordStatusList")
     */
    private $patientRecordStatus;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\LifeFormList")
     **/
    private $lifeForm;


    /////TODO: Add these fields to SinglePatient to vedit the patient demographic page
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phoneCanonical;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $emailCanonical;



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
        //$this->contactinfo = new ArrayCollection();
        $this->type = new ArrayCollection();

        if( $withfields ) {
            $this->addMrn( new PatientMrn($status,$provider,$sourcesystem) );
            $this->addDob( new PatientDob($status,$provider,$sourcesystem) );
            $this->addClinicalHistory( new PatientClinicalHistory($status,$provider,$sourcesystem) );

            //TODO: add tracker
            //$this->addContactinfoByTypeAndName($provider,$sourcesystem);

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
        //$this->contactinfo = $this->cloneDepend($this->contactinfo,$this);
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
                $this->setArrayFieldObjectChange('mrn','add',$mrn);
            }
        }

        return $this;
    }

    public function removeMrn($mrn)
    {
        $this->mrn->removeElement($mrn);
        $this->setArrayFieldObjectChange('mrn','remove',$mrn);
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
    public function calculateAge( $now=null ) {

        if( !$now ) {
            $now = new \DateTime();
        }
        //echo "nowdate=".$now->format('Y-m-d');

        $age = null;
        $dob = $this->obtainValidField('dob');
        //echo "dob=".$dob."<br>";

        $years = 0;
        $months = 0;
        $days = 0;
        $daysFull = 0;

        if( $dob != null && $dob."" ) {
            $date = new \DateTime($dob);
            //$now = new \DateTime();
            $interval = $now->diff($date);

            $years = $interval->format('%y');
            $months = $interval->format('%m');
            $days = $interval->format('%d');
            //$fullMonths = ($years * 12) + $months;
            $daysFull = $interval->days;
            //echo "years=".$years.", months=".$months.", days=".$days.", daysFull=".$daysFull."<br>";
        }

        //If the age is less than 1 day, show the age as "less than 1 day".
        if( $daysFull > 0 && $years < 1 && $months < 1 && $days < 1 ) {
            return "less than 1 day";
        }

        //If the age is less than 1 month, show the age in days and show the word "day(s)"; for example: "16 day(s)"
        if( $daysFull > 0 && $years < 1 && $months < 1 ) {
            return $days . " d.o.";  //" day(s)";
        }

        //If the age is less than 1 year, give the age in months and show the word "month(s)"; for example: "3 month(s)".
        if( $daysFull > 0 && $years < 1 ) {
            return $months . " m.o.";    //" month(s)";
        }

        //If the age is less than 1 year, give the age in months and show the word "month(s)"; for example: "3 month(s)".
        if( $daysFull > 0 && $years > 0 ) {
            return $years . " y.o.";
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
     * @param \App\OrderformBundle\Entity\Encounter $Encounter
     * @return Patient
     */
    public function addEncounter(\App\OrderformBundle\Entity\Encounter $encounter)
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
     * @param \App\OrderformBundle\Entity\Encounter $encounter
     */
    public function removeEncounter(\App\OrderformBundle\Entity\Encounter $encounter)
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
     * @param \App\OrderformBundle\Entity\PatientClinicalHistory $clinicalHistory
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
     * @param \App\OrderformBundle\Entity\PatientClinicalHistory $clinicalHistory
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


    public function addSuffix($suffix,$force=false)
    {
        if( $force || ( $this->notEmpty($suffix) && !$this->suffix->contains($suffix) && !$this->hasSimpleField($suffix,"getSuffix") ) ) {
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
     * @param \App\OrderformBundle\Entity\PatientLastname $lastname
     * @return Patient
     */
    public function addLastname($lastname,$force=false)
    {

        //echo "Patient add lastname: lastname=".$lastname.", id=".$lastname->getId().", status=".$lastname->getStatus()."<br>";

//        if( $lastname == null ) {
//            $lastname = new PatientLastname();
//        }

        if( $force || ( $this->notEmpty($lastname) && !$this->lastname->contains($lastname) && !$this->hasSimpleField($lastname,"getLastname") ) ) {
            //echo "adding lastname=".$lastname."<br>";
            $lastname->setPatient($this);
            $this->lastname->add($lastname);
        } else {
            //echo "NO adding lastname=".$lastname."<br>";
        }

        return $this;
    }

    /**
     * Remove lastname
     *
     * @param \App\OrderformBundle\Entity\PatientLastname $lastname
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
     * @param \App\OrderformBundle\Entity\PatientFirstname $firstname
     * @return Patient
     */
    public function addFirstname($firstname,$force=false)
    {

        //echo "Patient add firstname: firstname=".$firstname."<br>";

//        if( $firstname == null ) {
//            $firstname = new PatientFirstname();
//        }

        if( $force || ( $this->notEmpty($firstname) && !$this->firstname->contains($firstname) && !$this->hasSimpleField($firstname,"getFirstname") ) ) {
            $firstname->setPatient($this);
            $this->firstname->add($firstname);
        }

        return $this;
    }

    /**
     * Remove firstname
     *
     * @param \App\OrderformBundle\Entity\PatientFirstname $firstname
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
     * @param \App\OrderformBundle\Entity\PatientMiddlename $middlename
     * @return Patient
     */
    public function addMiddlename($middlename,$force=false)
    {

        //echo "Patient add middlename: middlename=".$middlename."<br>";

//        if( $middlename == null ) {
//            $middlename = new PatientMiddlename();
//        }

        if( $force || ($this->notEmpty($middlename) && !$this->middlename->contains($middlename) && !$this->hasSimpleField($middlename,"getMiddlename") ) ) {
            $middlename->setPatient($this);
            $this->middlename->add($middlename);
        }

        return $this;
    }

    /**
     * Remove middlename
     *
     * @param \App\OrderformBundle\Entity\PatientMiddlename $middlename
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


    /**
     * Add sex
     *
     * @param \App\OrderformBundle\Entity\PatientSex $sex
     * @param boolean $force
     * @return Patient
     */
    public function addSex($sex,$force=false)
    {
        if( $force || ($this->notEmpty($sex) && !$this->sex->contains($sex) && !$this->hasSimpleField($sex,"getSex")) ) {
            $sex->setPatient($this);
            $this->sex->add($sex);
        }

        return $this;
    }

    /**
     * Remove sex
     *
     * @param \App\OrderformBundle\Entity\PatientSex $sex
     */
    public function removeSex($sex)
    {
        $this->sex->removeElement($sex);
    }

    /**
     * Add dob
     *
     * @param \App\OrderformBundle\Entity\PatientDob $dob
     * @return Patient
     */
    public function addDob($dob)
    {
        //exit("add dob: ".$dob);
        if( $dob == null ) {
            $dob = new PatientDob();
        }

        if( !$this->dob->contains($dob) ) {
            $dob->setPatient($this);
            $this->dob->add($dob);
            $this->setArrayFieldObjectChange('dob','add',$dob);
        }
    
        return $this;
    }

    /**
     * Remove dob
     *
     * @param \App\OrderformBundle\Entity\PatientDob $dob
     */
    public function removeDob($dob)
    {
        //$this->changeObjectArr['dob']['remove']['field'] = $dob->getField()->format('Y-m-d')."";
        //$this->changeObjectArr['dob']['remove']['status'] = $dob->getStatus()."";
        //$this->changeObjectArr['dob']['remove']['provider'] = $dob->getProvider()."";
        $this->setArrayFieldObjectChange('dob','remove',$dob);
        $this->dob->removeElement($dob);
    }

    /**
     * @return mixed
     */
    public function getMasterMergeRecord()
    {
        return $this->masterMergeRecord;
    }

    /**
     * @param mixed $masterMergeRecord
     */
    public function addMasterMergeRecord( $masterMergeRecord )
    {
        if( $masterMergeRecord && !$this->masterMergeRecord->contains($masterMergeRecord) ) {
            $this->masterMergeRecord->add($masterMergeRecord);
            $masterMergeRecord->setPatient($this);
        }

        return $this;
    }

    public function isMasterMergeRecord( $status='valid' )
    {
        foreach( $this->getMasterMergeRecord() as $masterMergeRecord ) {
            if( $masterMergeRecord->getStatus() == $status && $masterMergeRecord->getField() == true ) {
                return true;
            }
        }
        return false;
    }
    public function invalidateMasterMergeRecord( $status='invalid' )
    {
        foreach( $this->getMasterMergeRecord() as $masterMergeRecord ) {
            $masterMergeRecord->setStatus($status);
            $masterMergeRecord->setField(false);
        }
    }

    /**
     * @return mixed
     */
    public function getPatientRecordStatus()
    {
        return $this->patientRecordStatus;
    }

    /**
     * @param mixed $patientRecordStatus
     */
    public function setPatientRecordStatus($patientRecordStatus)
    {
        $this->patientRecordStatus = $patientRecordStatus;
    }

    /**
     * @return mixed
     */
    public function getLifeForm()
    {
        return $this->lifeForm;
    }

    /**
     * @param mixed $lifeForm
     */
    public function setLifeForm($lifeForm)
    {
        $this->lifeForm = $lifeForm;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        $this->setPhoneCanonical($phone);
    }

    /**
     * @return mixed
     */
    public function getPhoneCanonical()
    {
        return $this->phoneCanonical;
    }

    /**
     * “+1 (234) 567-8901” becomes “12345678901”
     *
     * @param mixed $phoneCanonical
     */
    public function setPhoneCanonical($phoneCanonical)
    {
        if( $phoneCanonical ) {
            $phoneCanonical = $this->obtainPhoneCanonical($phoneCanonical);
        }

        $this->phoneCanonical = $phoneCanonical;
    }
    public function obtainPhoneCanonical($phone) {
        //echo "original phone=".$phoneCanonical."<br>";
        $phoneCanonical = str_replace(' ', '', $phone); // Replaces all spaces with hyphens.
        $phoneCanonical = preg_replace('/[^0-9]/', '', $phoneCanonical); // Removes special chars.
        //exit("phoneCanonical=".$phoneCanonical);
        return $phoneCanonical;
    }


    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;

        $this->setEmailCanonical($email);
    }

    /**
     * @return mixed
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * @param mixed $emailCanonical
     */
    public function setEmailCanonical($emailCanonical)
    {
        if( $emailCanonical ) {
            $emailCanonical = strtolower($emailCanonical);
            $this->emailCanonical = $emailCanonical;
        }
    }





    ///////////////////////// Extra fields /////////////////////////
    public function addExtraFields($status,$provider,$source) {
        $this->addRace( new PatientRace($status,$provider,$source) );
        $this->addDeceased( new PatientDeceased($status,$provider,$source) );
        $this->addType( new PatientType($status,$provider,$source) );
    }

//    public function addContactinfoByTypeAndName($user,$system,$locationType=null,$locationName=null,$spotEntity=null,$withdummyfields=false,$em=null) {
//        $patientLocation = new Location($user);
//
//        if( $locationType ) {
//            $patientLocation->addLocationType($locationType);
//        }
//
//        $patientLocation->setName($locationName);
//        $patientLocation->setStatus(1);
//        $patientLocation->setRemovable(1);
//
//        $geoLocation = new GeoLocation();
//        $patientLocation->setGeoLocation($geoLocation);
//
//        if( $withdummyfields ) {
//            $patientLocation->setEmail("dummyemail@myemail.com");
//            $patientLocation->setPhone("(212) 123-4567");
//            //$geoLocation = new GeoLocation();
//            $geoLocation->setStreet1("100");
//            $geoLocation->setStreet2("Broadway");
//            $geoLocation->setZip("10001");
//            //$patientLocation->setGeoLocation($geoLocation);
//
//            if( $em ) {
//                $city = $em->getRepository('AppUserdirectoryBundle:CityList')->findOneByName('New York');
//                $geoLocation->setCity($city);
//
//                $country = $em->getRepository('AppUserdirectoryBundle:Countries')->findOneByName('United States');
//                $geoLocation->setCountry($country);
//            }
//        }
//
//        $tracker = $this->getTracker();
//        if( !$tracker) {
//            $tracker = new Tracker();
//            $this->setTracker($tracker);
//        }
//
//        if( !$spotEntity ) {
//            $spotEntity = new Spot($user,$system);
//        }
//        $spotEntity->setCurrentLocation($patientLocation);
//        $spotEntity->setCreation(new \DateTime());
//        $spotEntity->setSpottedOn(new \DateTime());
//
//        $tracker->addSpot($spotEntity);
//    }

    //$locationTypeStr = "Patient's Primary Contact Information"
    public function obtainPatientContactinfo($locationTypeStr) {

        $locationArr = array();

        if( $this->getTracker() ) {
            foreach ($this->getTracker()->getSpots() as $spot) {
                $currentLocation = $spot->getCurrentLocation();
                if( $currentLocation->hasLocationTypeName($locationTypeStr) ) {

                    $emailStr = "";
                    if( $currentLocation->getEmail() ) {
                        $emailStr = '<a href="mailto:'.$currentLocation->getPhone().'" target="_top">'.$currentLocation->getPhone().'</a> ';
                    }

                    $phoneStr = "";
                    if( $currentLocation->getPhone() ) {
                        $phoneStr = '<a href="tel:'.$currentLocation->getPhone().'" target="_top">'.$currentLocation->getPhone().'</a> ';
                    }

                    $locationArr[] = $emailStr.$phoneStr.$currentLocation->getLocationFullBuildingName();

                }

            }
        }

        return implode("<br>",$locationArr);
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

//    public function getContactinfo()
//    {
//        return $this->contactinfo;
//    }
//    public function addContactinfo($contactinfo)
//    {
//        if( $contactinfo && !$this->contactinfo->contains($contactinfo) ) {
//            $this->contactinfo->add($contactinfo);
//            $contactinfo->setPatient($this);
//        }
//
//        return $this;
//    }
//    public function removeContactinfo($contactinfo)
//    {
//        $this->contactinfo->removeElement($contactinfo);
//    }

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

        $orders = ", messagesCount=".count($this->getMessage()).": ";
        foreach( $this->getMessage() as $order ) {
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
        ", message=".$orders.
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

        if( count($fullNameArr) == 0 ) {
            $fullNameArr[] = "No Name";
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

    public function getFullPatientName($htmlTags=true) {

        $patientFullNameValid = "";
        $patientFullNameAlias = "";

        $patientFullNameValidArr = $this->patientName('valid',$htmlTags);
        $patientFullNameAliasArr = $this->patientName('alias',$htmlTags);

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

    //Lastname, Firstname, Middlename
    public function patientName($status,$htmlTags=true) {

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

        if( $htmlTags ) {
            $htmlTagsMiddleArr = array('<i>','</i>');
        } else {
            $htmlTagsMiddleArr = null;
        }
        $resArr = $this->rearangeNameArrByOrder($orderArr,$middleNameArr,$middleNameArrOrder,$htmlTagsMiddleArr);
        $orderArr = $resArr['orderArr'];
        $middleNameArrOrder = $resArr['destArr'];


        if( $htmlTags ) {
            $htmlTagsLastArr = array('<b>','</b>');
        } else {
            $htmlTagsLastArr = null;
        }
        $resArr = $this->rearangeNameArrByOrder($orderArr,$lastNameArr,$lastNameArrOrder,$htmlTagsLastArr);
        $orderArr = $resArr['orderArr'];
        $lastNameArrOrder = $resArr['destArr'];

        $resArr = $this->rearangeNameArrByOrder($orderArr,$suffixArr,$suffixArrOrder,null);
        $orderArr = $resArr['orderArr'];
        $suffixArrOrder = $resArr['destArr'];

        //echo "count orderArr=".count($orderArr)."<br>";

        foreach( $orderArr as $orderId ) {

            //echo "orderId=".$orderId."<br>";

            $patientFullName = "";

            //order is important: Lastname, Firstname, Middlename
            $patientFullName = $this->patientPartialName($patientFullName,$lastNameArrOrder,$orderId,"No Last Name Provided");
            $patientFullName = $this->patientPartialName($patientFullName,$firstNameArrOrder,$orderId,"No First Name Provided");
            $patientFullName = $this->patientPartialName($patientFullName,$middleNameArrOrder,$orderId,""); //"No Middle Name Provided"

            $patientFullName = $this->patientPartialName($patientFullName,$suffixArrOrder,$orderId,"");

            if( $patientFullName != "" ) {
                $patientFullNameArr[] = $patientFullName;
            }

        } //foreach order key

        //echo "count patientFullNameArr=".count($patientFullNameArr)."<br>";

        return $patientFullNameArr;
    }

    public function rearangeNameArrByOrder( $orderArr, $sourceArr, $destArr, $htmlTags=null ) {
        $resArr = array();
        foreach( $sourceArr as $name ) {
            if( $name->getMessage() ) {
                //echo "no message <br>";
                //continue; //is it correct: if there is no message
                $orderId = $name->getMessage()->getId();
            } else {
                $orderId = 0;
            }
            //$orderId = $name->getMessage()->getId();
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

    //obtain only one encounter's the most recent value (i.e. last name: $fieldname='patlastname') with $status
    public function obtainSingleEncounterValues( $fieldnameArr, $status ) {

        //echo "<br>PATIENTID=".$this->getId()."<br>";
        $resArr = array();

        //foreach( $this->getEncounter() as $encounter ) {

            //foreach( $fieldnameArr as $fieldname) {
        foreach( $fieldnameArr as $fieldname) {

            //echo "fieldname=".$fieldname." => encounterCount=".count($this->getEncounter())."<br>";
            $mostRecentFieldEntity = null;

            foreach( $this->getEncounter() as $encounter ) {

                $getMethod = "get".$fieldname;

                //$mostRecentFieldEntity = null;

                //for each encounter's fieldname array (i.e. patlastname)
                foreach( $encounter->$getMethod() as $fieldEntity ) {
                    //echo "fieldEntity=".$fieldEntity."; status=".$fieldEntity->getStatus()."; date=".$fieldEntity->getCreationdate()->format('Y-m-d H:i')."<br>";

                    if( !$mostRecentFieldEntity ) {
                        if( $fieldEntity->getStatus() == $status && $fieldEntity->getField() ) {
                            $mostRecentFieldEntity = $fieldEntity;
                        }
                        continue;
                    }

                    $mostRecentFieldnameDate = $mostRecentFieldEntity->getCreationdate();
                    $currentFieldnameDate = $fieldEntity->getCreationdate();

                    if( !$mostRecentFieldnameDate || !$currentFieldnameDate ) {
                        continue;
                    }

                    if( $fieldEntity->getStatus() == $status && $currentFieldnameDate > $mostRecentFieldnameDate && $fieldEntity->getField() ) {
                        $mostRecentFieldEntity = $fieldEntity;
                    }

                }//$encounter->$getMethod()

                //$resArr[$fieldname] = $mostRecentFieldEntity;

            }//foreach fieldnameArr

            //echo "mostRecentFieldEntity=".$mostRecentFieldEntity."<br>";
            $resArr[$fieldname] = $mostRecentFieldEntity;

        }//foreach encounter

        return $resArr;
    }

    //if simple field already exists. Compare by field name. This is to prevent creating similar fields
    public function hasSimpleField( $field, $getMethod ) {

        foreach( $this->$getMethod() as $obj ) {
            //echo "this field=".$obj."<br>";
            //echo "compare ".$getMethod." (".$obj->getField().") ?= (".$field->getField().")<br>";
            if( $obj->getField()."" == $field->getField()."" ) {
                //echo $getMethod.":field exists = ".$field."<br>";
                return $obj;
            } else {
                //echo $getMethod.":does not exists = ".$field."<br>";
                //return false;
            }
        }
        //echo $getMethod.":no loop: field does not = ".$field."<br>";
        return false;
    }

    public function notEmpty( $fieldObject ) {
        if( $fieldObject && $fieldObject->getField() ) {
            return true;
        } else {
            return false;
        }
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

    //Not Used!!!
    //Danger: it will overwrite the patient with the valid values
//    public function obtainOneValidObjectPatient() {
//        //mrn
//        $mrn = $this->obtainValidField('mrn');
//        $this->mrn->clear();
//        $this->addMrn($mrn);
//
//        //dob
//        $dob = $this->obtainValidField('dob');
//        $this->dob->clear();
//        $this->addDob($dob);
//
//        //clinical history
//        $clinicalHistory = $this->obtainValidField('clinicalHistory');
//        $this->clinicalHistory->clear();
//        $this->addClinicalHistory($clinicalHistory);
//    }



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
            'Race','Deceased'
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

    //return latest Merge MRN, if not exists, return NULL
    public function obtainMergeMrn( $status=null ) {
        $latestMergeMrn = null;
        foreach( $this->getMrn() as $mrn ) {
            $compare = true;
            if( $status ) {
                if( $status != $mrn->getStatus() ) {
                    $compare = false;
                }
            }
            if( $compare && $mrn->getKeytype()->getName() == "Merge ID" ) {
                if( !$latestMergeMrn ) {
                    $latestMergeMrn = $mrn;
                    continue;
                }
                if( $mrn->getCreationdate() > $latestMergeMrn->getCreationdate() ) {
                    $latestMergeMrn = $mrn;
                }
            }
        }
        return $latestMergeMrn;
    }
    public function hasOnlyOneMergeMrn( $status=null ) {
        if( count($this->obtainMergeMrnArr($status)) == 1 ) {
            return true;
        } else {
            return false;
        }
    }
    //return Merge MRN array
    public function obtainMergeMrnArr( $status=null ) {
        $mergeMrnArr = array();
        foreach( $this->getMrn() as $mrn ) {
            $compare = true;
            if( $status ) {
                if( $status != $mrn->getStatus() ) {
                    $compare = false;
                }
            }
            if( $compare && $mrn->getKeytype() && $mrn->getKeytype()->getName() == "Merge ID" ) {
                $mergeMrnArr[] = $mrn;
            }
        }
        return $mergeMrnArr;
    }
    public function obtainMergeMrnById( $mrnId, $status=null ) {
        foreach( $this->obtainMergeMrnArr($status) as $mrn ) {
            $compare = true;
            if( $status ) {
                if( $status != $mrn->getStatus() ) {
                    $compare = false;
                }
            }
            if( $compare && $mrn->getKeytype()->getName() == "Merge ID" && $mrn->getField() == $mrnId ) {
                return $mrn;
            }
        }
        return null;
    }

    public function obtainMergeInfo($separator=", ") {
        $mergedMrnArr = $this->obtainMergeMrnArr("valid");
        $resArr = array();
        foreach( $mergedMrnArr as $mergedMrn ) {
            $resArr[] = $mergedMrn->getField()." merged by " . $mergedMrn->getProvider() . " on " . $mergedMrn->getCreationdate()->format('m/d/Y');
        }
        if( $separator ) {
            return implode($separator, $resArr);
        } else {
            return $resArr;
        }
    }
    public function obtainMergeInfoArr() {
        $mergedMrnArr = $this->obtainMergeMrnArr("valid");
        $resArr = array();
        foreach( $mergedMrnArr as $mergedMrn ) {
            //$resArr[] = "Merge ID ".$mergedMrn->getField().", merged by " . $mergedMrn->getProvider() . " on " . $mergedMrn->getCreationdate()->format('m/d/Y');
            $mergeArr = array(
                "mergeId" => $mergedMrn->getField(),
                "mergeDetails" => $mergedMrn->getField() . " merged by " . $mergedMrn->getProvider() . " on " . $mergedMrn->getCreationdate()->format('m/d/Y')
            );
            $resArr[] = $mergeArr;
        }
        return $resArr;
    }

    //overwrite obtainStatusField method in ObjectAbstract object
    //get only one field with $status belongs to order with id $orderid
    //if status is null, get the first field belongs to the given order id
    public function obtainStatusField( $fieldname, $status, $orderid=null ) {

        if( $fieldname == "mrn" ) {

            $res = null;

            $resArr = $this->obtainStatusFieldArray($fieldname, $status, $orderid);

            if( count($resArr) == 1 ) {
                $res = $resArr[0];
            }

            //if multiple found, get the latest one (with the latest timestamp getCreationdate) except Merge ID
            if( count($resArr) > 1 ) {

                $latestField = null;
                foreach( $resArr as $field ) {

                    //ignore MERGE ID mrn
                    if( $field->getKeytype()->getName() == "Merge ID" ) {
                        //echo " >>ignore mergeid<< ";
                        continue;
                    }

                    if( !$latestField ) {
                        //echo " >>update null mrn ".$field->getField()."<< ";
                        $latestField = $field;
                        continue;
                    }

                    if( $field->getCreationdate() > $latestField->getCreationdate() ) {
                        //echo " >>update latest mrn ".$field->getField()."<< ";
                        $latestField = $field;
                    }

                }
                $res = $latestField;
            }

            //echo "res=".$res."<br>|||||||";
            return $res;
        }

        return parent::obtainStatusField( $fieldname, $status, $orderid );
    }

    //11/29/1980 | F | 36 y.o. | New York Hospital MRN: 1?
    public function obtainPatientInfoTitle( $status='valid', $now=null, $htmlTags=true ) {

        if( !$now ) {
            $now = new \DateTime();
        }

        $fieldnameArr = array('patlastname','patfirstname','patmiddlename','patsex');
        $fieldnameResArr = $this->obtainSingleEncounterValues($fieldnameArr,$status);

        $patientInfoArr = array();

        $fullName = $this->getFullPatientName($htmlTags);
        if( $fullName ) {
            //echo "fullName <br>";
            $patientInfoArr[] = $fullName;
        }

        $dobRes = $this->obtainStatusField('dob', $status);
        if( $dobRes && $dobRes."" ) {
            //echo "dobRes=$dobRes <br>";
            $patientInfoArr[] = "DOB: ".$dobRes."";
        }

        $sexRes = $fieldnameResArr['patsex'];
        if( $sexRes ) {
            //echo "sexRes=$sexRes <br>";
            $patientInfoArr[] = $sexRes."";
        }

        $age = $this->calculateAge($now)."";
        if( $age ) {
            //echo "age=$age <br>";
            $patientInfoArr[] = $age."";
        }

        $mrnRes = $this->obtainStatusField('mrn', $status);
        if( $mrnRes ) {
            //$mrntype = $mrnRes->getKeytype()->getId();
            //$mrntypeObject = $this->convertAutoGeneratedMrntype($mrntype,true);
            //$mrntype = $mrntypeObject->getId();
            if( $mrnRes->getKeytype() ) {
                $mrntypeStr = $mrnRes->getKeytype()->getOptimalName().": ";
            } else {
                $mrntypeStr = "";
            }

            $patientInfoArr[] = $mrntypeStr.$mrnRes->getField();
        }

        $patientInfo = implode(" | ",$patientInfoArr);

        return $patientInfo;
    }

    //last name of LLL, first name of FFFF, and a MM/DD/YYYY date of birth
    public function obtainPatientInfoShort( $status='valid' ) {

        $patientInfoArr = array();

        $fullName = $this->getFullPatientName();
        if( $fullName ) {
            //echo "fullName <br>";
            $patientInfoArr[] = $fullName;
        } else {
            $patientInfoArr[] = "No Name Provided";
        }

        $dobRes = $this->obtainStatusField('dob', $status);
        if( $dobRes && $dobRes."" ) {
            //echo "dobRes=$dobRes <br>";
            $patientInfoArr[] = $dobRes." date of birth";
        }

        if( count($patientInfoArr) == 0 ) {
            $patientInfoArr[] = "ID# ".$this->getId();
        }

        $patientInfo = implode(", ",$patientInfoArr);

        return $patientInfo;
    }

    //CallLog: PatientLastName, Patient FirstName (DOB: MM/DD/YY, [Gender], [MRN Type(short name)]: [MRN])
    public function obtainPatientInfoSimple( $status='valid' ) {

        $fieldnameArr = array('patlastname','patfirstname','patmiddlename','patsex');
        $fieldnameResArr = $this->obtainSingleEncounterValues($fieldnameArr,$status);

        $patientInfo = "";

        //PatientLastName, Patient FirstName
        $fullPatientName = $this->getFullPatientName();
        if( $fullPatientName && $fullPatientName != "" ) {
            $patientInfo = $fullPatientName;
        } else {
            $patientInfo = "No Name Provided";
        }

        //(DOB: MM/DD/YY, [Gender], [MRN Type(short name)]: [MRN])
        $patientAddInfoArr = array();

        //DOB: MM/DD/YY
        $dob = $this->obtainValidField('dob');
        if( $dob ) {
            $patientAddInfoArr[] = $dob."";
        }

        //[Gender]
        $sex = $this->obtainValidField('sex');
        if( $sex && $sex != "" ) {
            $patientAddInfoArr[] = $sex."";
        } else {
            $sexRes = $fieldnameResArr['patsex'];
            if( $sexRes ) {
                //echo "sexRes=$sexRes <br>";
                $patientAddInfoArr[] = $sexRes."";
            }
        }

        //[MRN Type(short name)]: [MRN]
        $mrnRes = $this->obtainStatusField('mrn', $status);
        if( $mrnRes ) {
            $mrnKeyType = $mrnRes->getKeytype();
            if( $mrnKeyType ) {
                $mrntypeStr = $mrnKeyType->getOptimalName();
                $patientAddInfoArr[] = $mrntypeStr . ": " . $mrnRes->getField();
            } else {
                $patientAddInfoArr[] = $mrnRes->getField();
            }
        }

        $patientAddInfo = implode(", ",$patientAddInfoArr);

        $patientInfo = $patientInfo . " (". $patientAddInfo . ")";

        return $patientInfo;
    }


    public function obtainPatientLatestEncounterLocation() {

        $encounters = $this->getEncounter();
        if( count($encounters) > 0 ) {
            $encounter = $encounters->last();
            return $encounter->obtainLocationInfo();
        }

        return null;

        ///////////////////////////////
//        $locationArr = array();
//        foreach( $this->getEncounter() as $encounter ) {
//            $locationInfo = $encounter->obtainLocationInfo();
//            $locationArr[] = $locationInfo;
//        }
//
//        return implode(", ",$locationArr);
    }
}