<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Procedure (use 'procedures', because 'procedure' causes problems (reserved?))
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ProcedureRepository")
 * @ORM\Table(name="procedures")
 */
class Procedure extends OrderAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="ProcedureName", mappedBy="procedure", cascade={"persist"})
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="ProcedureEncounter", mappedBy="procedure", cascade={"persist"})
     */
    protected $encounter;
    
    /**
     * parent
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="procedure")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id")
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
//    /**
//     * @ORM\Column(type="datetime", nullable=true)
//     */
//    protected $encounterDate;
    /**
     * @ORM\OneToMany(targetEntity="ProcedureEncounterDate", mappedBy="procedure", cascade={"persist"})
     */
    protected $encounterDate;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $patname;
    /**
     * @ORM\OneToMany(targetEntity="ProcedurePatname", mappedBy="procedure", cascade={"persist"})
     */
    protected $patname;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $patsex;
    /**
     * @ORM\OneToMany(targetEntity="ProcedurePatsex", mappedBy="procedure", cascade={"persist"})
     */
    protected $patsex;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $patage;
    /**
     * @ORM\OneToMany(targetEntity="ProcedurePatage", mappedBy="procedure", cascade={"persist"})
     */
    protected $patage;

//    /**
//     * @ORM\Column(type="text", nullable=true)
//     */
//    protected $pathistory;
    /**
     * @ORM\OneToMany(targetEntity="ProcedurePathistory", mappedBy="procedure", cascade={"persist"})
     */
    protected $pathistory;


    public function __construct( $withfields=false, $status='invalid', $provider=null, $source=null ) {
        parent::__construct($status,$provider);
        $this->accession = new ArrayCollection();

        //fields:
        $this->name = new ArrayCollection();
        $this->encounter = new ArrayCollection();

        $this->encounterDate = new ArrayCollection();
        $this->patname = new ArrayCollection();
        $this->patsex = new ArrayCollection();
        $this->patage = new ArrayCollection();
        $this->pathistory = new ArrayCollection();

        if( $withfields ) {
            $this->addName( new ProcedureName($status,$provider,$source) );
            $this->addEncounter( new ProcedureEncounter($status,$provider,$source) );

            $this->addEncounterDate( new ProcedureEncounterDate($status,$provider,$source) );
            $this->addPatname( new ProcedurePatname($status,$provider,$source) );
            $this->addPatsex( new ProcedurePatsex($status,$provider,$source) );
            $this->addPatage( new ProcedurePatage($status,$provider,$source) );
            $this->addPathistory( new ProcedurePathistory($status,$provider,$source) );
        }
    }

    public function makeDependClone() {
        $this->name = $this->cloneDepend($this->name,$this);
        $this->encounter = $this->cloneDepend($this->encounter,$this);

        $this->encounterDate = $this->cloneDepend($this->encounterDate,$this);
        $this->patname = $this->cloneDepend($this->patname,$this);
        $this->patsex = $this->cloneDepend($this->patsex,$this);
        $this->patage = $this->cloneDepend($this->patage,$this);
        $this->pathistory = $this->cloneDepend($this->pathistory,$this);
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

    /**
     * @param mixed $patname
     */
    public function setPatname($patname)
    {
        $this->patname = $patname;
    }
    /**
     * @return mixed
     */
    public function getPatname()
    {
        return $this->patname;
    }
    public function addPatname($patname)
    {
        if( $patname == null ) {
            $patname = new ProcedurePatname();
        }

        if( !$this->patname->contains($patname) ) {
            $patname->setProcedure($this);
            $this->patname->add($patname);
        }

        return $this;
    }
    public function removePatname($patname)
    {
        $this->patname->removeElement($patname);
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



    public function __toString() {

        $procNames = "";
        foreach( $this->getName() as $name ) {
            $procNames = $procNames . " name=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().") ";
        }

        $patName = "";
        foreach( $this->getPatname() as $name ) {
            $patName = $patName . " patname=". $name. " (provider=".$name->getProvider().", status=".$name->getStatus().") ";
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

        return 'Procedure: id=' . $this->id . ", patientName=".$this->getPatient()->getName()->first().
            ", patname=" . $patName . ", patage=" . $patAge . ", patsex=".$patSex.", Clinical History=".$hist.
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
        $fieldsArr = array('Encounter','Name','EncounterDate','Patname','Patage','Patsex','Pathistory');
        return $fieldsArr;
    }

}