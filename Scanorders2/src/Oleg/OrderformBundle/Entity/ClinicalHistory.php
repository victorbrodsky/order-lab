<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ClinicalHistoryRepository")
 * @ORM\Table(name="clinicalHistory")
 */
class ClinicalHistory
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true, length=10000)
     */
    protected $clinicalHistory;

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="clinicalHistory")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=true)
     */
    protected $patient;

    /**
     * validity - valid or not valid
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $validity;
    
   
    public function getId() {
        return $this->id;
    }

//    public function getName() {
//        return $this->name;
//    }
//
//    public function setId($id) {
//        $this->id = $id;
//    }
//
//    public function setName($name) {
//        $this->name = $name;
//    }

    /**
     * Set part
     *
     * @param \Oleg\OrderformBundle\Entity\Part $part
     * @return Block
     */
    public function setPart(\Oleg\OrderformBundle\Entity\Part $part = null)
    {
        $this->part = $part;
    
        return $this;
    }

    /**
     * Get part
     *
     * @return \Oleg\OrderformBundle\Entity\Part 
     */
    public function getPart()
    {
        return $this->part;
    }

    public function __toString()
    {
        return $this->clinicalHistory;
    }


    /**
     * Set clinicalHistory
     *
     * @param string $clinicalHistory
     * @return ClinicalHistory
     */
    public function setClinicalHistory($clinicalHistory)
    {
        $this->clinicalHistory = $clinicalHistory;
    
        return $this;
    }

    /**
     * Get clinicalHistory
     *
     * @return string 
     */
    public function getClinicalHistory()
    {
        return $this->clinicalHistory;
    }

    /**
     * Set validity
     *
     * @param string $validity
     * @return ClinicalHistory
     */
    public function setValidity($validity)
    {
        $this->validity = $validity;
    
        return $this;
    }

    /**
     * Get validity
     *
     * @return string 
     */
    public function getValidity()
    {
        return $this->validity;
    }

    /**
     * Set patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     * @return ClinicalHistory
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
}