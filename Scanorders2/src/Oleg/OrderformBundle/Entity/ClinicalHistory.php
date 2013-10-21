<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ClinicalHistoryRepository")
 * @ORM\Table(name="clinicalHistory")
 * @ORM\HasLifecycleCallbacks
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
     * @ORM\Column(type="text", length=10000)
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

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     *
     */
    protected $creationdate;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $creator;

    public function getId() {
        return $this->id;
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

    /**
     * @ORM\PrePersist
     */
    public function setCreationdate()
    {
        $this->creationdate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }





}