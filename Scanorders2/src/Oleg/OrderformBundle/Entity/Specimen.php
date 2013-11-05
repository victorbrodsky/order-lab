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
     * @ORM\ManyToOne(targetEntity="ProcedureList", inversedBy="specimen", cascade={"persist"})
     * @ORM\JoinColumn(name="procedurelist_id", referencedColumnName="id", nullable=true)
     */
    protected $proceduretype;
    
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
    
    
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="specimen")
     **/
    protected $orderinfo; 

    public function __construct() {
        $this->accession = new ArrayCollection();
        $this->orderinfo = new ArrayCollection();
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
        return 'Procedure: id=' . $this->getId() . "<br>";
    }


    /**
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Specimen
     */
    public function addOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
    {    
        if( !$this->orderinfo->contains($orderinfo) ) {
            $this->orderinfo->add($orderinfo);
        }   
    }

    /**
     * Remove orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     */
    public function removeOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
    {
        $this->orderinfo->removeElement($orderinfo);
    }

    /**
     * Get orderinfo
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }
}