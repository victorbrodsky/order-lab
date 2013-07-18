<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\SlideRepository")
 * @ORM\Table(name="slide")
 */
class Slide
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    
    //*******************************// 
    // fillable fields 
    //*******************************//
    
    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     */
    protected $accession;

    /**
     * @ORM\Column(type="string", nullable=true, length=100)   
     */
    protected $stain;   
    
    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $mag;
    
    /**
     * @ORM\Column(type="text", nullable=true, length=10000)
     */
    protected $diagnosis; 
    
    
    //*********************************************// 
    // second part of the form (optional) 
    //*********************************************//                
    
    /**
     * @ORM\Column(type="text", nullable=true, length=10000)
     */
    protected $microscopicdescr;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $specialstain;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $relevantscan;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $scanregion;
     
    /**
     * @ORM\Column(type="text", nullable=true, length=10000)    
     */
    protected $note;
    
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getAccession() {
        return $this->accession;
    }

    public function setAccession($accession) {
        $this->accession = $accession;
    }

    public function getStain() {
        return $this->stain;
    }

    public function setStain($stain) {
        $this->stain = $stain;
    }

    public function getMag() {
        return $this->mag;
    }

    public function setMag($mag) {
        $this->mag = $mag;
    }

    public function getDiagnosis() {
        return $this->diagnosis;
    }

    public function setDiagnosis($diagnosis) {
        $this->diagnosis = $diagnosis;
    }

    public function getMicroscopicdescr() {
        return $this->microscopicdescr;
    }

    public function setMicroscopicdescr($microscopicdescr) {
        $this->microscopicdescr = $microscopicdescr;
    }

    public function getSpecialstain() {
        return $this->specialstain;
    }

    public function setSpecialstain($specialstain) {
        $this->specialstain = $specialstain;
    }

    public function getRelevantscan() {
        return $this->relevantscan;
    }

    public function setRelevantscan($relevantscan) {
        $this->relevantscan = $relevantscan;
    }

    public function getScanregion() {
        return $this->scanregion;
    }

    public function setScanregion($scanregion) {
        $this->scanregion = $scanregion;
    }

    public function getNote() {
        return $this->note;
    }

    public function setNote($note) {
        $this->note = $note;
    }
       
}

?>
