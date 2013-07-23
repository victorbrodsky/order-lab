<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\PartRepository")
 * @ORM\Table(name="part")
 */
class Part
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
   
    /**
     * Part belongs to exactly one Accession => Part has only one Accession
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="part")
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    protected $accession;

    /**
     * Name is a letter
     * @ORM\Column(type="string", length=1)   
     */
    protected $name;  
    
    //*********************************************// 
    // optional fields
    //*********************************************//     
    
    /**
     * @ORM\Column(type="string", nullable=true, length=100)   
     */
    protected $sourceOrgan;   
    
    /**
     * @ORM\Column(type="string", nullable=true, length=10000)
     */
    protected $description;
    
    /**
     * @ORM\Column(type="text", nullable=true, length=10000)
     */
    protected $diagnosis; 
             
    
    /**
     * @ORM\Column(type="text", nullable=true, length=10000)
     */
    protected $diffDiagnosis;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $diseaseType; 
    
    
    public function getId() {
        return $this->id;
    }

    public function getAccession() {
        return $this->accession;
    }

    public function getName() {
        return $this->name;
    }

    public function getSourceOrgan() {
        return $this->sourceOrgan;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDiagnosis() {
        return $this->diagnosis;
    }

    public function getDiffDiagnosis() {
        return $this->diffDiagnosis;
    }

    public function getDiseaseType() {
        return $this->diseaseType;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAccession($accession) {
        $this->accession = $accession;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setSourceOrgan($sourceOrgan) {
        $this->sourceOrgan = $sourceOrgan;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setDiagnosis($diagnosis) {
        $this->diagnosis = $diagnosis;
    }

    public function setDiffDiagnosis($diffDiagnosis) {
        $this->diffDiagnosis = $diffDiagnosis;
    }

    public function setDiseaseType($diseaseType) {
        $this->diseaseType = $diseaseType;
    }


}