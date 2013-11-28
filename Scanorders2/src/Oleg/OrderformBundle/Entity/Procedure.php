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

    public function __construct( $withfields=false, $validity=0 ) {
        parent::__construct();
        $this->accession = new ArrayCollection();

        //fields:
        $this->name = new ArrayCollection();
        $this->encounter = new ArrayCollection();

        if( $withfields ) {
            $this->addName( new ProcedureName($validity) );
            $this->addEncounter( new ProcedureEncounter($validity) );
        }
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
        if( $name ) {
            if( !$this->name->contains($name) ) {
                $name->setProcedure($this);
                $this->name->add($name);
            }
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
        return $this->encounter;
    }

    public function setEncounter($encounter) {
        $this->encounter = $encounter;
    }

    public function addEncounter($encounter)
    {
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

    public function obtainKeyField() {
        return $this->getEncounter();
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

    public function getChildren()
    {
        return $this->getAccession();
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

    //parent
    public function setParent($parent)
    {
        $this->setPatient($parent);
        return $this;
    }

    public function getParent()
    {
        return $this->getPatient();
    }

    public function __toString() {
        return 'Procedure: id=' . $this->id . ", patientName=".$this->getPatient()->getName()->first().", encounterCount=" . count($this->encounter->first()) . ": encounter->first=" . $this->encounter->first() . "; accessionCount=".count($this->accession).":".$this->accession->first()."<br>";
    }

}