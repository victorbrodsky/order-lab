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

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\EncounterRepository")
 * @ORM\Table(name="scan_encounter")
 */
class Encounter extends ObjectAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="EncounterName", mappedBy="encounter", cascade={"persist"})
     */
    protected $name;

    /**
     * Encounter Number
     * @ORM\OneToMany(targetEntity="EncounterNumber", mappedBy="encounter", cascade={"persist"})
     */
    protected $number;
    
    /**
     * parent
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="encounter")
     * @ORM\JoinColumn(name="patient", referencedColumnName="id")
     */
    protected $patient; 
    
    /**
     * Encounter might have many Procedures (children)
     * 
     * @ORM\OneToMany(targetEntity="Procedure", mappedBy="encounter")
     */
    protected $procedure;
    
    /**
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="encounter")
     **/
    protected $message;


    //Patient's info: age, name, sex, date, history
    /**
     * @ORM\OneToMany(targetEntity="EncounterDate", mappedBy="encounter", cascade={"persist"})
     */
    protected $date;

    /**
     * @ORM\OneToMany(targetEntity="EncounterPatsuffix", mappedBy="encounter", cascade={"persist"})
     */
    protected $patsuffix;

    /**
     * @ORM\OneToMany(targetEntity="EncounterPatlastname", mappedBy="encounter", cascade={"persist"})
     */
    protected $patlastname;

    /**
     * @ORM\OneToMany(targetEntity="EncounterPatfirstname", mappedBy="encounter", cascade={"persist"})
     */
    protected $patfirstname;

    /**
     * @ORM\OneToMany(targetEntity="EncounterPatmiddlename", mappedBy="encounter", cascade={"persist"})
     */
    protected $patmiddlename;

    /**
     * @ORM\OneToMany(targetEntity="EncounterPatsex", mappedBy="encounter", cascade={"persist"})
     */
    protected $patsex;

    /**
     * @ORM\OneToMany(targetEntity="EncounterPatage", mappedBy="encounter", cascade={"persist"})
     */
    protected $patage;

    /**
     * @ORM\OneToMany(targetEntity="EncounterPathistory", mappedBy="encounter", cascade={"persist"})
     */
    protected $pathistory;

    /**
     * @ORM\OneToMany(targetEntity="EncounterReferringProvider", mappedBy="encounter", cascade={"persist"})
     */
    protected $referringProviders;

    /**
     * @ORM\OneToMany(targetEntity="EncounterAttendingPhysician", mappedBy="encounter", cascade={"persist"})
     */
    protected $attendingPhysicians;

    /**
     * @ORM\OneToMany(targetEntity="EncounterInfoType", mappedBy="encounter", cascade={"persist"})
     */
    protected $encounterInfoTypes;

    /**
     * TODO: make it the same as patlastname?
     * unmapped patientDob
     */
    private $patientDob;

    ///////////////// additional extra fields not shown on scan order /////////////////
    /**
     * Encounter location
     * @ORM\OneToMany(targetEntity="EncounterLocation", mappedBy="encounter", cascade={"persist"})
     */
    private $location;

//    /**
//     * Encounter order
//     * @ORM\OneToMany(targetEntity="EncounterOrder", mappedBy="encounter", cascade={"persist"})
//     */
//    private $order;

    /**
     * @ORM\OneToMany(targetEntity="EncounterInpatientinfo", mappedBy="encounter", cascade={"persist"})
     */
    private $inpatientinfo;

    /**
     * @ORM\ManyToOne(targetEntity="EncounterStatusList")
     */
    private $encounterStatus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $version;
    ///////////////// EOF additional extra fields not shown on scan order /////////////////


    public function __construct( $withfields=false, $status='invalid', $provider=null, $source=null ) {
        parent::__construct($status,$provider,$source);
        $this->procedure = new ArrayCollection();

        //fields:
        $this->name = new ArrayCollection();
        $this->number = new ArrayCollection();
        $this->date = new ArrayCollection();

        $this->patsuffix = new ArrayCollection();
        $this->patlastname = new ArrayCollection();
        $this->patmiddlename = new ArrayCollection();
        $this->patfirstname = new ArrayCollection();

        $this->patsex = new ArrayCollection();
        $this->patage = new ArrayCollection();
        $this->encounterInfoTypes = new ArrayCollection();

        $this->pathistory = new ArrayCollection();
        $this->referringProviders = new ArrayCollection();
        $this->attendingPhysicians = new ArrayCollection();

        //extra
        $this->location = new ArrayCollection();
        //$this->order = new ArrayCollection();
        $this->inpatientinfo = new ArrayCollection();

        if( $withfields ) {
            $this->addName( new EncounterName($status,$provider,$source) );
            $this->addNumber( new EncounterNumber($status,$provider,$source) );
            $this->addDate( new EncounterDate($status,$provider,$source) );
            $this->addPatsuffix( new EncounterPatsuffix($status,$provider,$source) );
            $this->addPatlastname( new EncounterPatlastname($status,$provider,$source) );
            $this->addPatfirstname( new EncounterPatfirstname($status,$provider,$source) );
            $this->addPatmiddlename( new EncounterPatmiddlename($status,$provider,$source) );
            $this->addPatsex( new EncounterPatsex($status,$provider,$source) );
            $this->addPatage( new EncounterPatage($status,$provider,$source) );
            $this->addPathistory( new EncounterPathistory($status,$provider,$source) );
            $this->addEncounterInfoType( new EncounterInfoType($status,$provider,$source) );

            //testing data structure
            //$this->addExtraFields($status,$provider,$source);
        }
    }

    public function makeDependClone() {
        $this->name = $this->cloneDepend($this->name,$this);
        $this->number = $this->cloneDepend($this->number,$this);
        $this->date = $this->cloneDepend($this->date,$this);
        $this->patsuffix = $this->cloneDepend($this->patsuffix,$this);
        $this->patlastname = $this->cloneDepend($this->patlastname,$this);
        $this->patfirstname = $this->cloneDepend($this->patfirstname,$this);
        $this->patmiddlename = $this->cloneDepend($this->patmiddlename,$this);
        $this->patsex = $this->cloneDepend($this->patsex,$this);
        $this->patage = $this->cloneDepend($this->patage,$this);
        $this->pathistory = $this->cloneDepend($this->pathistory,$this);
        $this->referringProviders = $this->cloneDepend($this->referringProviders,$this);
        $this->attendingPhysicians = $this->cloneDepend($this->attendingPhysicians,$this);
        $this->encounterInfoTypes = $this->cloneDepend($this->encounterInfoTypes,$this);

        //extra fields
        $this->location = $this->cloneDepend($this->location,$this);
        //$this->order = $this->cloneDepend($this->order,$this);
        $this->inpatientinfo = $this->cloneDepend($this->inpatientinfo,$this);
    }



    /**
     * @return mixed
     */
    public function getPatientDob()
    {
        return $this->patientDob;
    }

    /**
     * @param mixed $patientDob
     */
    public function setPatientDob($patientDob)
    {
        $this->patientDob = $patientDob;
    }



    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }
    public function addDate($date)
    {
        if( $date == null ) {
            $date = new EncounterDate();
        }

        if( !$this->date->contains($date) ) {
            $this->date->add($date);
            $date->setEncounter($this);
            $this->setArrayFieldObjectChange('date','add',$date);
        }

        return $this;
    }
    public function removeDate($date)
    {
        $this->date->removeElement($date);
    }

    /**
     * @param mixed $patage
     */
    public function setPatage($patage)
    {
        $this->patage = $patage;
    }
    /**
     * @return mixed
     */
    public function getPatage()
    {
        return $this->patage;
    }
    public function addPatage($patage)
    {
        if( $patage == null ) {
            $patage = new EncounterPatage();
        }

        if( !$this->patage->contains($patage) ) {
            $patage->setEncounter($this);
            $this->patage->add($patage);
            $this->setArrayFieldObjectChange('patage','add',$patage);
        }

        return $this;
    }
    public function removePatage($patage)
    {
        $this->patage->removeElement($patage);
        $this->setArrayFieldObjectChange('patage','remove',$patage);
    }

    /**
     * @param mixed $pathistory
     */
    public function setPathistory($pathistory)
    {
        $this->pathistory = $pathistory;
    }
    /**
     * @return mixed
     */
    public function getPathistory()
    {
        return $this->pathistory;
    }
    public function addPathistory($pathistory)
    {
        if( $pathistory == null ) {
            $pathistory = new EncounterPathistory();
        }

        if( !$this->pathistory->contains($pathistory) ) {
            $pathistory->setEncounter($this);
            $this->pathistory->add($pathistory);
            $this->setArrayFieldObjectChange('pathistory','add',$pathistory);
        }

        return $this;
    }
    public function removePathistory($pathistory)
    {
        $this->pathistory->removeElement($pathistory);
        $this->setArrayFieldObjectChange('pathistory','remove',$pathistory);
    }


    public function setPatsuffix($patsuffix)
    {
        $this->patsuffix = $patsuffix;
    }
    public function getPatsuffix()
    {
        return $this->patsuffix;
    }
    public function addPatsuffix($patsuffix)
    {
        if( $patsuffix == null ) {
            $patsuffix = new EncounterPatsuffix();
        }

        if( !$this->patsuffix->contains($patsuffix) ) {
            $patsuffix->setEncounter($this);
            $this->patsuffix->add($patsuffix);
            $this->setArrayFieldObjectChange('patsuffix','add',$patsuffix);
        }

        return $this;
    }
    public function removePatsuffix($patsuffix)
    {
        $this->patsuffix->removeElement($patsuffix);
        $this->setArrayFieldObjectChange('patsuffix','remove',$patsuffix);
    }



    /**
     * @param mixed $patlastname
     */
    public function setPatlastname($patlastname)
    {
        $this->patlastname = $patlastname;
    }
    /**
     * @return mixed
     */
    public function getPatlastname()
    {
        return $this->patlastname;
    }
    public function addPatlastname($patlastname)
    {
        if( $patlastname == null ) {
            $patlastname = new EncounterPatlastname();
        }

        if( !$this->patlastname->contains($patlastname) ) {
            $patlastname->setEncounter($this);
            $this->patlastname->add($patlastname);
            $this->setArrayFieldObjectChange('patlastname','add',$patlastname);
        }

        return $this;
    }
    public function removePatlastname($patlastname)
    {
        $this->patlastname->removeElement($patlastname);
        $this->setArrayFieldObjectChange('patlastname','remove',$patlastname);
    }


    /**
     * @param mixed $patfirstname
     */
    public function setPatfirstname($patfirstname)
    {
        $this->patfirstname = $patfirstname;
    }
    /**
     * @return mixed
     */
    public function getPatfirstname()
    {
        return $this->patfirstname;
    }
    public function addPatfirstname($patfirstname)
    {
        if( $patfirstname == null ) {
            $patfirstname = new EncounterPatfirstname();
        }

        if( !$this->patfirstname->contains($patfirstname) ) {
            $patfirstname->setEncounter($this);
            $this->patfirstname->add($patfirstname);
            $this->setArrayFieldObjectChange('patfirstname','add',$patfirstname);
        }

        return $this;
    }
    public function removePatfirstname($patfirstname)
    {
        $this->patfirstname->removeElement($patfirstname);
        $this->setArrayFieldObjectChange('patfirstname','remove',$patfirstname);
    }

    /**
     * @param mixed $patmiddlename
     */
    public function setPatmiddlename($patmiddlename)
    {
        $this->patmiddlename = $patmiddlename;
    }
    /**
     * @return mixed
     */
    public function getPatmiddlename()
    {
        return $this->patmiddlename;
    }
    public function addPatmiddlename($patmiddlename)
    {
        if( $patmiddlename == null ) {
            $patmiddlename = new EncounterPatmiddlename();
        }

        if( !$this->patmiddlename->contains($patmiddlename) ) {
            $patmiddlename->setEncounter($this);
            $this->patmiddlename->add($patmiddlename);
            $this->setArrayFieldObjectChange('patmiddlename','add',$patmiddlename);
        }

        return $this;
    }
    public function removePatmiddlename($patmiddlename)
    {
        $this->patmiddlename->removeElement($patmiddlename);
        $this->setArrayFieldObjectChange('patmiddlename','remove',$patmiddlename);
    }


    /**
     * @param mixed $patsex
     */
    public function setPatsex($patsex)
    {
        $this->patsex = $patsex;
    }
    /**
     * @return mixed
     */
    public function getPatsex()
    {
        return $this->patsex;
    }
    public function addPatsex($patsex)
    {
        if( $patsex == null ) {
            $patsex = new EncounterPatsex();
        }

        if( !$this->patsex->contains($patsex) ) {
            $patsex->setEncounter($this);
            $this->patsex->add($patsex);
            $this->setArrayFieldObjectChange('patsex','add',$patsex);
        }

        return $this;
    }
    public function removePatsex($patsex)
    {
        $this->patsex->removeElement($patsex);
        $this->setArrayFieldObjectChange('patsex','remove',$patsex);
    }

    /**
     * @return mixed
     */
    public function getEncounterInfoTypes()
    {
        return $this->encounterInfoTypes;
    }
    public function addEncounterInfoType($item)
    {
        if( $item == null ) {
            $item = new EncounterInfoType();
        }

        if( !$this->encounterInfoTypes->contains($item) ) {
            $item->setEncounter($this);
            $this->encounterInfoTypes->add($item);
            $this->setArrayFieldObjectChange('encounterInfoTypes','add',$item);
        }

        return $this;
    }
    public function removeEncounterInfoType($item)
    {
        $this->encounterInfoTypes->removeElement($item);
        $this->setArrayFieldObjectChange('encounterInfoTypes','remove',$item);
    }

    //Name
    public function getName() {
        return $this->name;
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function addName($name)
    {
        if( $name == null ) {
            $name = new EncounterName();
        }

        if( !$this->name->contains($name) ) {
            $name->setEncounter($this);
            $this->name->add($name);
        }

        return $this;
    }
    public function removeName($name)
    {
        $this->name->removeElement($name);
    }

    public function clearName()
    {
        $this->name->clear();
    }

    //Encounter Number
    public function getNumber() {
        return $this->number;
    }
    public function setNumber($number) {
        $this->number = $number;
    }
    public function addNumber($number)
    {
        if( $number ) {
            if( !$this->number->contains($number) ) {
                $this->number->add($number);
                $number->setEncounter($this);
            }
        }

        return $this;
    }
    public function removeNumber($number)
    {
        $this->number->removeElement($number);
    }

    public function clearNumber()
    {
        $this->number->clear();
    }

    /**
     * Add procedure
     *
     * @param \Oleg\OrderformBundle\Entity\Procedure $procedure
     * @return Encounter
     */
    public function addProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {
        if( !$this->procedure->contains($procedure) ) {
            $this->procedure->add($procedure);
            $procedure->setEncounter($this);
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
     * Set patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     * @return Encounter
     */
    public function setPatient(\Oleg\OrderformBundle\Entity\Patient $patient = null)
    {
        $this->patient = $patient;
    
        return $this;
    }
    /**
     * Get patient
     *
     * @return \Oleg\OrderformBundle\Entity\Patient 
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @return mixed
     */
    public function getReferringProviders()
    {
        return $this->referringProviders;
    }
    public function addReferringProvider($item)
    {
        if( $item && !$this->referringProviders->contains($item) ) {
            $item->setEncounter($this);
            $this->referringProviders->add($item);
            //$this->setArrayFieldObjectChange('referringProviders','add',$item);
        }
        return $this;
    }
    public function removeReferringProvider($item)
    {
        $this->referringProviders->removeElement($item);
        //$this->setArrayFieldObjectChange('referringProviders','remove',$item);
    }

    /**
     * @return mixed
     */
    public function getAttendingPhysicians()
    {
        return $this->attendingPhysicians;
    }
    public function addAttendingPhysician($item)
    {
        if( $item && !$this->attendingPhysicians->contains($item) ) {
            $item->setEncounter($this);
            $this->attendingPhysicians->add($item);
            //$this->setArrayFieldObjectChange('attendingPhysicians','add',$item);
        }
        return $this;
    }
    public function removeAttendingPhysician($item)
    {
        $this->attendingPhysicians->removeElement($item);
        //$this->setArrayFieldObjectChange('attendingPhysicians','remove',$item);
    }

    ///////////////////////// Extra fields /////////////////////////
    public function addExtraFields($status,$provider,$source) {
        $this->addLocation( new EncounterLocation($status,$provider,$source) );
        //$this->addOrder( new EncounterOrder($status,$provider,$source) );
        $this->addInpatientinfo( new EncounterInpatientinfo($status,$provider,$source) );

    }

    public function getLocation()
    {
        return $this->location;
    }
    public function addLocation($location)
    {
        if( $location && !$this->location->contains($location) ) {
            $this->location->add($location);
            $location->setEncounter($this);
        }

        return $this;
    }
    public function removeLocation($location)
    {
        $this->location->removeElement($location);
    }

    /**
     * @return mixed
     */
    public function getEncounterStatus()
    {
        return $this->encounterStatus;
    }

    /**
     * @param mixed $encounterStatus
     */
    public function setEncounterStatus($encounterStatus)
    {
        $this->encounterStatus = $encounterStatus;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }




//    public function getOrder()
//    {
//        return $this->order;
//    }
//    public function addOrder($order)
//    {
//        if( $order && !$this->order->contains($order) ) {
//            $this->order->add($order);
//            $order->setEncounter($this);
//        }
//
//        return $this;
//    }
//    public function removeOrder($order)
//    {
//        $this->order->removeElement($order);
//    }

    public function getInpatientinfo()
    {
        return $this->inpatientinfo;
    }
    public function addInpatientinfo($inpatientinfo)
    {
        if( $inpatientinfo && !$this->inpatientinfo->contains($inpatientinfo) ) {
            $this->inpatientinfo->add($inpatientinfo);
            $inpatientinfo->setEncounter($this);
        }

        return $this;
    }
    public function removeInpatientinfo($inpatientinfo)
    {
        $this->inpatientinfo->removeElement($inpatientinfo);
    }
    ///////////////////////// EOF Extra fields /////////////////////////

    public function obtainEncounterNames() {
        $exists = false;
        $patfirstname = "First Name:";
        foreach( $this->getpatfirstname() as $name ) {
            if( $name."" ) {
                $patfirstname = $patfirstname . $name . " (" . $name->getStatus() . ")";
                $exists = true;
            }
        }

        $patlastname = "Last Name:";
        foreach( $this->getpatlastname() as $name ) {
            if( $name."" ) {
                $patlastname = $patlastname . $name . " (" . $name->getStatus() . ") ";
                $exists = true;
            }
        }

        if( !$exists ) {
            return null;
        }

        $creationDateStr = "";
        if( $this->getCreationdate() ) {
            $creationDateStr = " created on ".$this->getCreationdate()->format('m/d/Y');
        }

        return "Encounter".$creationDateStr.": ".$patlastname . " " . $patfirstname;
    }

    public function __toString() {

        $encNames = "";
        foreach( $this->getName() as $name ) {
            $encNames = $encNames . " name=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $patientFirstName = "";
        if( $this->getPatient() && $this->getPatient()->getFirstname() ) {
            $patientFirstName = $this->getPatient()->getFirstname()->first();
        }

        $patfirstname = "";
        foreach( $this->getpatfirstname() as $name ) {
            $patfirstname = $patfirstname . " patfirstname=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().", alias=".$name->getAlias().") ";
        }

        $patlastname = "";
        foreach( $this->getpatlastname() as $name ) {
            $patlastname = $patlastname . " patlastname=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().", alias=".$name->getAlias().") ";
        }

        $patAge = "";
        foreach( $this->getPatage() as $name ) {
            $patAge = $patAge . " patage=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $patSex = "";
        foreach( $this->getPatsex() as $name ) {
            $patSex = $patSex . " patsex=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $hist = "";
        foreach( $this->getPathistory() as $name ) {
            $hist = $hist . " pathist=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $parentId = "";
        if( $this->getParent() ) {
            $parentId = $this->getParent()->getId();
        }

        return 'Encounter: id=' . $this->id . ", patientFirstName=".$patientFirstName.
            ", patfirstname=" . $patfirstname .
            ", patlastname=" . $patlastname .
            ", patage=" . $patAge . ", patsex=".$patSex.", Clinical History=".$hist.
            ", encounterNameCount=" . count($this->getName()) . " => Names=".$encNames.
            ", encounterCount=" . count($this->number) .
            ": encounter->first=" . $this->number->first() .
            ", parentId=".$parentId.
            "; linked procedureCount=".count($this->procedure).":".$this->procedure->first();
    }


    //parent, children, key field methods
    public function setParent($parent)
    {
        $this->setPatient($parent);
        return $this;
    }

    public function getParent()
    {
        return $this->getPatient();
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
    
    //don't use 'get' because later repo functions relay on "get" keyword
    //object info for blue strip
    public function obtainFullObjectName() {

        $fullNameArr = array();

        //date
        $dateStr = "";
        $date = $this->obtainValidField('date');
        if( $date && $date != "" ) {
            $dateStr = $date." ";
        }

        //number
        $key = $this->obtainValidField('number');
        if( $key ) {
            if( $key->getKeytype() ) {
                $fullNameArr[] = $dateStr . $key->getKeytype()->getOptimalName() . ": " . $key->getField();
            } else {
                $fullNameArr[] = $dateStr . $key->getField();
            }
        }

        $fullName = implode(", ",$fullNameArr);

        //testing
        //$fullName = $fullName . "[ID# ".$this->getId()."]";

        return $fullName;
    }

    public function obtainEncounterNumber() {
        $number = null;
        $key = $this->obtainValidField('number');
        if( $key ) {
            if( $key->getKeytype() ) {
                $number = $key->getField() . " (". $key->getKeytype()->getOptimalName() . ")";
            } else {
                $number = $key->getField();
            }
        }
        return $number;
    }

    public function obtainEncounterNumberOnlyAndDate() {
        $number = null;
        $key = $this->obtainValidField('number');
        if( $key ) {
            $number = $key->getField();
            //PreviousEncounterID (MM/DD/YYYY HH:MM:SS)
            $number = $number . " (" . $this->getCreationdate()->format("m/d/Y H:i:s") . ")";
        }
        return $number;
    }

    //[EncounterLocation'sName] / [EncounterLocation'sPhoneNumber]
    public function obtainLocationInfo() {
        $infoArr = array();
        //[tracker][spots][0][currentLocation][name]
        if( !$this->getTracker() ) {
            return "";
        }
        foreach( $this->getTracker()->getSpots() as $spot ) {
            if( $spot->getCurrentLocation() ) {
                $info = $spot->getCurrentLocation()->getName();
                if( $spot->getCurrentLocation()->getPhone() ) {
                    $info = $info . " / " . $spot->getCurrentLocation()->getPhone();
                }
                if( $spot->getCurrentLocation()->getInstitution() ) {
                    $instName = $spot->getCurrentLocation()->getInstitution()->getAbbreviation();
                    if( !$instName ) {
                        $instName = $spot->getCurrentLocation()->getInstitution()->getShortName();
                    }
                    if( !$instName ) {
                        $instName = $spot->getCurrentLocation()->getInstitution()->getName();
                    }
                    $info = $info . " (" . $instName . ")";
                }
                $infoArr[] = $info;
            }
        }

        return implode("; ",$infoArr);
    }
    public function obtainTrackerSpotsLocations() {
        $locationArr = array();
        //[tracker][spots][0][currentLocation][name]
        if( !$this->getTracker() ) {
            return $locationArr;
        }
        foreach( $this->getTracker()->getSpots() as $spot ) {
            $location = $spot->getCurrentLocation();
            if( $location ) {
                $locationArr[] = $location;
            }
        }

        return $locationArr;
    }
    public function obtainTrackerSpotsLocationId() {
        $locations = $this->obtainTrackerSpotsLocations();
        if( count($locations) > 0 ) {
            $location = $locations[0];
            if( $location->getId() ) {
                return $location->getId();
            }
        }
        return null;
    }

    //[ReferringProvider] ([Specialty], [Phone Number]/[ReferringProviderEmail])
    public function obtainReferringProviderInfo() {
        $infoArr = array();
        //referringProviders_0_referringProviderSpecialty
        foreach( $this->getReferringProviders() as $refProvider ) {

            $info = "";

            if( $refProvider->getField() ) {
                $info = $info . $refProvider->getField()->getFullName();
            }

            //([Specialty], [Phone Number]/[ReferringProviderEmail])
            $addInfoArr = array();
            if( $refProvider->getReferringProviderSpecialty() ) {
                $addInfoArr[] = $refProvider->getReferringProviderSpecialty();
            }

            //[Phone Number]
            $contactInfo = "";
            //[Phone Number]
            if( $refProvider->getReferringProviderPhone() ) {
                $contactInfo .= $refProvider->getReferringProviderPhone();
            }
            //[ReferringProviderEmail]
            if( $refProvider->getReferringProviderEmail() ) {
                if( $contactInfo ) {
                    $contactInfo = $contactInfo . "/";
                }
                $contactInfo .= $refProvider->getReferringProviderEmail();
            }
            if( $contactInfo ) {
                $addInfoArr[] = $contactInfo;
            }

            //([Specialty], [Phone Number]/[ReferringProviderEmail])
            $addInfo = "";
            if( count($addInfoArr) > 0 ) {
                $addInfo = "(" . implode(", ",$addInfoArr) . ")";
            }

            //Oleg Ivanov - username (Blood Bank Personnel, [Phone Number]/[ReferringProviderEmail])
            if( $addInfo ) {
                $info = $info . " " . $addInfo;
            }

            $infoArr[] = $info;
        }

        return implode("; ",$infoArr);
    }

    //AttendingPhysician (i.e. firstname lastname - cwid)
    public function obtainAttendingPhysicianInfo() {
        $infoArr = array();
        //referringProviders_0_referringProviderSpecialty
        foreach( $this->getAttendingPhysicians() as $physician ) {

            $info = "";

            if( $physician->getField() ) {
                $info = $info . $physician->getField()->getFullName();
            }

            $infoArr[] = $info;
        }

        return implode("; ",$infoArr);
    }

    public function hasPatientInfo() {
        if( $this->getPatfirstname()->first()."" )
            return true;
        if( $this->getPatlastname()->first()."" )
            return true;
        if( $this->getPatmiddlename()->first()."" )
            return true;
        if( $this->getPatsuffix()->first()."" )
            return true;
        if( $this->getPatsex()->first()."" )
            return true;

        return false;
    }

    public function obtainKeyField() {
        return $this->getNumber();
    }

    public function obtainKeyFieldName() {
        return "number";
    }

    public function createKeyField() {
        $this->addNumber( new EncounterNumber() );
        return $this->obtainKeyField();
    }

    public function obtainNoprovidedKeyPrefix() {
        return $name = "AUTOGENERATEDENCOUNTERID";
    }

    public function getArrayFields() {
        $fieldsArr = array(
            'Name','Number','Date','Patsuffix','Patlastname','Patfirstname','Patmiddlename','Patage','Patsex','Pathistory','encounterInfoTypes',
            //extra fields
            'Location', 'Inpatientinfo' //'Order'
        );
        return $fieldsArr;
    }

}