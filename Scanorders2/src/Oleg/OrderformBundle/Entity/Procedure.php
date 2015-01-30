<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Procedure (use 'procedures', because 'procedure' causes problems (reserved?))
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ProcedureRepository")
 * @ORM\Table(name="scan_procedures")
 */
class Procedure extends ObjectAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="ProcedureName", mappedBy="procedure", cascade={"persist"})
     */
    protected $name;

    /**
     * Encounter Number
     * @ORM\OneToMany(targetEntity="ProcedureEncounter", mappedBy="procedure", cascade={"persist"})
     */
    protected $encounter;
    
    /**
     * parent
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="procedure")
     * @ORM\JoinColumn(name="patient", referencedColumnName="id")
     */
    protected $patient; 
    
    /**
     * Procedure might have many Accession (children)
     * 
     * @ORM\OneToMany(targetEntity="Accession", mappedBy="procedure")
     */
    protected $accession;
    
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="procedure")
     **/
    protected $orderinfo;


    //Patient's info: age, name, sex
    /**
     * @ORM\OneToMany(targetEntity="ProcedureEncounterDate", mappedBy="procedure", cascade={"persist"})
     */
    protected $encounterDate;

    /**
     * @ORM\OneToMany(targetEntity="ProcedurePatsuffix", mappedBy="procedure", cascade={"persist"})
     */
    protected $patsuffix;

    /**
     * @ORM\OneToMany(targetEntity="ProcedurePatlastname", mappedBy="procedure", cascade={"persist"})
     */
    protected $patlastname;

    /**
     * @ORM\OneToMany(targetEntity="ProcedurePatfirstname", mappedBy="procedure", cascade={"persist"})
     */
    protected $patfirstname;

    /**
     * @ORM\OneToMany(targetEntity="ProcedurePatmiddlename", mappedBy="procedure", cascade={"persist"})
     */
    protected $patmiddlename;

    /**
     * @ORM\OneToMany(targetEntity="ProcedurePatsex", mappedBy="procedure", cascade={"persist"})
     */
    protected $patsex;

    /**
     * @ORM\OneToMany(targetEntity="ProcedurePatage", mappedBy="procedure", cascade={"persist"})
     */
    protected $patage;

    /**
     * @ORM\OneToMany(targetEntity="ProcedurePathistory", mappedBy="procedure", cascade={"persist"})
     */
    protected $pathistory;


    ///////////////// additional extra fields not shown on scan order /////////////////
    /**
     * @ORM\OneToMany(targetEntity="ProcedureLocation", mappedBy="procedure", cascade={"persist"})
     */
    private $location;

    /**
     * @ORM\OneToMany(targetEntity="ProcedureEncounterorder", mappedBy="procedure", cascade={"persist"})
     */
    private $encounterorder;

    /**
     * @ORM\OneToMany(targetEntity="ProcedureProcedureorder", mappedBy="procedure", cascade={"persist"})
     */
    private $procedureorder;

    /**
     * @ORM\OneToMany(targetEntity="ProcedureInpatientinfo", mappedBy="procedure", cascade={"persist"})
     */
    private $inpatientinfo;

    ///////////////// EOF additional extra fields not shown on scan order /////////////////


    public function __construct( $withfields=false, $status='invalid', $provider=null, $source=null ) {
        parent::__construct($status,$provider,$source);
        $this->accession = new ArrayCollection();

        //fields:
        $this->name = new ArrayCollection();
        $this->encounter = new ArrayCollection();
        $this->encounterDate = new ArrayCollection();

        $this->patsuffix = new ArrayCollection();
        $this->patlastname = new ArrayCollection();
        $this->patmiddlename = new ArrayCollection();
        $this->patfirstname = new ArrayCollection();

        $this->patsex = new ArrayCollection();
        $this->patage = new ArrayCollection();

        $this->pathistory = new ArrayCollection();

        //extra
        $this->location = new ArrayCollection();
        $this->encounterorder = new ArrayCollection();
        $this->procedureorder = new ArrayCollection();
        $this->inpatientinfo = new ArrayCollection();

        if( $withfields ) {
            $this->addName( new ProcedureName($status,$provider,$source) );
            $this->addEncounter( new ProcedureEncounter($status,$provider,$source) );
            $this->addEncounterDate( new ProcedureEncounterDate($status,$provider,$source) );
            $this->addPatsuffix( new ProcedurePatsuffix($status,$provider,$source) );
            $this->addPatlastname( new ProcedurePatlastname($status,$provider,$source) );
            $this->addPatfirstname( new ProcedurePatfirstname($status,$provider,$source) );
            $this->addPatmiddlename( new ProcedurePatmiddlename($status,$provider,$source) );
            $this->addPatsex( new ProcedurePatsex($status,$provider,$source) );
            $this->addPatage( new ProcedurePatage($status,$provider,$source) );
            $this->addPathistory( new ProcedurePathistory($status,$provider,$source) );

            //testing data structure
            $this->addExtraFields($status,$provider,$source);
        }
    }

    public function makeDependClone() {
        $this->name = $this->cloneDepend($this->name,$this);
        $this->encounter = $this->cloneDepend($this->encounter,$this);
        $this->encounterDate = $this->cloneDepend($this->encounterDate,$this);
        $this->patsuffix = $this->cloneDepend($this->patsuffix,$this);
        $this->patlastname = $this->cloneDepend($this->patlastname,$this);
        $this->patfirstname = $this->cloneDepend($this->patfirstname,$this);
        $this->patmiddlename = $this->cloneDepend($this->patmiddlename,$this);
        $this->patsex = $this->cloneDepend($this->patsex,$this);
        $this->patage = $this->cloneDepend($this->patage,$this);
        $this->pathistory = $this->cloneDepend($this->pathistory,$this);

        //extra fields
        $this->location = $this->cloneDepend($this->location,$this);
        $this->encounterorder = $this->cloneDepend($this->encounterorder,$this);
        $this->procedureorder = $this->cloneDepend($this->procedureorder,$this);
        $this->inpatientinfo = $this->cloneDepend($this->inpatientinfo,$this);
    }

    /**
     * @param mixed $encounterDate
     */
    public function setEncounterDate($encounterDate)
    {
        $this->encounterDate = $encounterDate;
    }
    /**
     * @return mixed
     */
    public function getEncounterDate()
    {
        return $this->encounterDate;
    }
    public function addEncounterDate($encounterDate)
    {
        if( $encounterDate == null ) {
            $encounterDate = new ProcedureEncounterDate();
        }

        if( !$this->encounterDate->contains($encounterDate) ) {
            $encounterDate->setProcedure($this);
            $this->encounterDate->add($encounterDate);
        }

        return $this;
    }
    public function removeEncounterDate($encounterDate)
    {
        $this->encounterDate->removeElement($encounterDate);
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
            $patage = new ProcedurePatage();
        }

        if( !$this->patage->contains($patage) ) {
            $patage->setProcedure($this);
            $this->patage->add($patage);
        }

        return $this;
    }
    public function removePatage($patage)
    {
        $this->patage->removeElement($patage);
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
            $pathistory = new ProcedurePathistory();
        }

        if( !$this->pathistory->contains($pathistory) ) {
            $pathistory->setProcedure($this);
            $this->pathistory->add($pathistory);
        }

        return $this;
    }
    public function removePathistory($pathistory)
    {
        $this->pathistory->removeElement($pathistory);
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
            $patsuffix = new ProcedurePatsuffix();
        }

        if( !$this->patsuffix->contains($patsuffix) ) {
            $patsuffix->setProcedure($this);
            $this->patsuffix->add($patsuffix);
        }

        return $this;
    }
    public function removePatsuffix($patsuffix)
    {
        $this->patsuffix->removeElement($patsuffix);
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
            $patlastname = new ProcedurePatlastname();
        }

        if( !$this->patlastname->contains($patlastname) ) {
            $patlastname->setProcedure($this);
            $this->patlastname->add($patlastname);
        }

        return $this;
    }
    public function removePatlastname($patlastname)
    {
        $this->patlastname->removeElement($patlastname);
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
            $patfirstname = new ProcedurePatfirstname();
        }

        if( !$this->patfirstname->contains($patfirstname) ) {
            $patfirstname->setProcedure($this);
            $this->patfirstname->add($patfirstname);
        }

        return $this;
    }
    public function removePatfirstname($patfirstname)
    {
        $this->patfirstname->removeElement($patfirstname);
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
            $patmiddlename = new ProcedurePatmiddlename();
        }

        if( !$this->patmiddlename->contains($patmiddlename) ) {
            $patmiddlename->setProcedure($this);
            $this->patmiddlename->add($patmiddlename);
        }

        return $this;
    }
    public function removePatmiddlename($patmiddlename)
    {
        $this->patmiddlename->removeElement($patmiddlename);
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
            $patsex = new ProcedurePatsex();
        }

        if( !$this->patsex->contains($patsex) ) {
            $patsex->setProcedure($this);
            $this->patsex->add($patsex);
        }

        return $this;
    }
    public function removePatsex($patsex)
    {
        $this->patsex->removeElement($patsex);
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
            $name = new ProcedureName();
        }

        if( !$this->name->contains($name) ) {
            $name->setProcedure($this);
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

    //Encounter
    public function getEncounter() {
        //echo "ecounter count=".count($this->encounter)."<br>";
        return $this->encounter;
    }

    public function setEncounter($encounter) {
        $this->encounter = $encounter;
    }

    public function addEncounter($encounter)
    {
        //echo "encounter add: id=".$encounter->getId().", name=".$encounter->getField()."<br>";
        if( $encounter ) {
            if( !$this->encounter->contains($encounter) ) {
                $encounter->setProcedure($this);
                $this->encounter->add($encounter);
            }
        }

        return $this;
    }

    public function removeEncounter($encounter)
    {
        $this->encounter->removeElement($encounter);
    }

    public function clearEncounter()
    {
        $this->encounter->clear();
    }

    /**
     * Add accession
     *
     * @param \Oleg\OrderformBundle\Entity\Accession $accession
     * @return Procedure
     */
    public function addAccession(\Oleg\OrderformBundle\Entity\Accession $accession)
    {
        if( !$this->accession->contains($accession) ) {
            $accession->setProcedure($this);
            $this->accession[] = $accession;
        }
    
        return $this;
    }

    /**
     * Remove accession
     *
     * @param \Oleg\OrderformBundle\Entity\Accession $accession
     */
    public function removeAccession(\Oleg\OrderformBundle\Entity\Accession $accession)
    {
        $this->accession->removeElement($accession);
    }

    /**
     * Get accession
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccession()
    {
        return $this->accession;
    }
    public function setAccession(\Doctrine\Common\Collections\ArrayCollection $accession)
    {
        $this->accession = $accession;
    }

    /**
     * Set patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     * @return Procedure
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

    public function clearAccession(){
        $this->accession->clear();
    }



    ///////////////////////// Extra fields /////////////////////////
    public function addExtraFields($status,$provider,$source) {
        $this->addLocation( new ProcedureLocation($status,$provider,$source) );
        $this->addEncounterorder( new ProcedureEncounterorder($status,$provider,$source) );
        $this->addProcedureorder( new ProcedureProcedureorder($status,$provider,$source) );
        $this->addInpatientinfo( new ProcedureInpatientinfo($status,$provider,$source) );

    }

    public function getLocation()
    {
        return $this->location;
    }
    public function addLocation($location)
    {
        if( $location && !$this->location->contains($location) ) {
            $this->location->add($location);
            $location->setProcedure($this);
        }

        return $this;
    }
    public function removeLocation($location)
    {
        $this->location->removeElement($location);
    }

    public function getEncounterorder()
    {
        return $this->encounterorder;
    }
    public function addEncounterorder($encounterorder)
    {
        if( $encounterorder && !$this->encounterorder->contains($encounterorder) ) {
            $this->encounterorder->add($encounterorder);
            $encounterorder->setProcedure($this);
        }

        return $this;
    }
    public function removeEncounterorder($encounterorder)
    {
        $this->encounterorder->removeElement($encounterorder);
    }

    public function getProcedureorder()
    {
        return $this->procedureorder;
    }
    public function addProcedureorder($procedureorder)
    {
        if( $procedureorder && !$this->procedureorder->contains($procedureorder) ) {
            $this->procedureorder->add($procedureorder);
            $procedureorder->setProcedure($this);
        }

        return $this;
    }
    public function removeProcedureorder($procedureorder)
    {
        $this->procedureorder->removeElement($procedureorder);
    }

    public function getInpatientinfo()
    {
        return $this->inpatientinfo;
    }
    public function addInpatientinfo($inpatientinfo)
    {
        if( $inpatientinfo && !$this->inpatientinfo->contains($inpatientinfo) ) {
            $this->inpatientinfo->add($inpatientinfo);
            $inpatientinfo->setProcedure($this);
        }

        return $this;
    }
    public function removeInpatientinfo($inpatientinfo)
    {
        $this->inpatientinfo->removeElement($inpatientinfo);
    }
    ///////////////////////// EOF Extra fields /////////////////////////


    public function __toString() {

        $procNames = "";
        foreach( $this->getName() as $name ) {
            $procNames = $procNames . " name=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $patlastname = "";
        foreach( $this->getpatlastname() as $name ) {
            $patlastname = $patlastname . " patlastname=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().") ";
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

        return 'Procedure: id=' . $this->id . ", patientFirstName=".$this->getPatient()->getFirstname()->first().
            ", patlastname=" . $patlastname . ", patage=" . $patAge . ", patsex=".$patSex.", Clinical History=".$hist.
            ", procedureNameCount=" . count($this->getName()) . " => Names=".$procNames.
            ", encounterCount=" . count($this->encounter) .
            ": encounter->first=" . $this->encounter->first() .
            ", parentId=".$this->getParent()->getId().
            "; linked accessionCount=".count($this->accession).":".$this->accession->first();
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
        return $this->getAccession();
    }

    public function addChildren($child) {
        $this->addAccession($child);
    }

    public function removeChildren($child) {
        $this->removeAccession($child);
    }

    public function setChildren($children) {
        $this->setAccession($children);
    }
    
    //don't use 'get' because later repo functions relay on "get" keyword
    public function obtainKeyField() {
        return $this->getEncounter();
    }

    public function obtainKeyFieldName() {
        return "encounter";
    }

    public function createKeyField() {
        $this->addEncounter( new ProcedureEncounter() );
        return $this->obtainKeyField();
    }

    public function getArrayFields() {
        $fieldsArr = array(
            'Encounter','Name','EncounterDate','Patsuffix','Patlastname','Patfirstname','Patmiddlename','Patage','Patsex','Pathistory',
            //extra fields
            'Location', 'Encounterorder', 'Procedureorder', 'Inpatientinfo'
        );
        return $fieldsArr;
    }

}