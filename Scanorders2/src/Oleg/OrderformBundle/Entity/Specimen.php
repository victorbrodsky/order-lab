<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Specimen or Case or Procedure
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\SpecimenRepository")
 * @ORM\Table(name="specimen")
 */
class Specimen
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="proceduretype", type="string", nullable=true, length=300)   
     */
    protected $proceduretype;   
    
    /**
     * Link to a paper or abstract file
     * @ORM\Column(name="paper", type="string", nullable=true, length=300)
     */
    protected $paper;   
    
    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="specimen")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id")
     */
    protected $patient; 
    
    /**
     * Specimen might have many Accession
     * 
     * @ORM\OneToMany(targetEntity="Accession", mappedBy="specimen")
     */
    protected $accession;

    public function __construct() {
        $this->accession = new ArrayCollection();
    }   

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getProceduretype() {
        return $this->proceduretype;
    }

    public function setProceduretype($proceduretype) {
        $this->proceduretype = $proceduretype;
    }

    /**
     * Set paper
     *
     * @param string $paper
     * @return Specimen
     */
    public function setPaper($paper)
    {
        $this->paper = $paper;
    
        return $this;
    }

    /**
     * Get paper
     *
     * @return string 
     */
    public function getPaper()
    {
        return $this->paper;
    }

    /**
     * Add accession
     *
     * @param \Oleg\OrderformBundle\Entity\Accession $accession
     * @return Specimen
     */
    public function addAccession(\Oleg\OrderformBundle\Entity\Accession $accession)
    {
        if( !$this->accession->contains($accession) ) {
            $accession->setSpecimen($this);
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
     * @return Specimen
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
        foreach( $this->accession as $thisaccession ) {
            $this->removeAccession($thisaccession);
        }
    }

    public function __toString() {
        $acc_info = "(";
        $count = 0;
        foreach( $this->accession as $accession ) {
            //$patient_info .= 'id='.$patient->getId().", mrn=".$patient->getMrn(). "; ";
            $acc_info .= $count.":" . $accession. "; ";
            $count++;
        }
        $acc_info .= ")";

        return 'Procedure: (ID=' . $this->getId() . ',type=' . $this->getProceduretype()." Accession count=".count($this->getAccession())." (".$acc_info.")<br>";
    }

}