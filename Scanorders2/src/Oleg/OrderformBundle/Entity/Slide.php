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
    // first step fields 
    //*******************************//
    
    //Slide belongs to exactly one Accession => 
    //Slide has only one Accession, Accession might have many Slides (1..n)
    //Note: Unique slide accession number is combination of Accession+Part+Block (S12-99997 A2)
    /**
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="slide", cascade={"persist"})
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    protected $accession;
    
    /**
     * Slide belongs to exactly one OrderInfo => Slide has only one OrderInfo
     * @ORM\ManyToOne(targetEntity="OrderInfo", inversedBy="slide", cascade={"persist"})
     * @ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    protected $orderinfo;
    
    //add manytoone for block and part?
    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="slide", cascade={"persist"})
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    protected $part;
    
    /**
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="slide", cascade={"persist"})
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    protected $block;
    

    /**
     * @ORM\Column(type="string", nullable=true, length=100)   
     */
    protected $stain;   
    
    /**
     * @ORM\Column(type="string", length=100)
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
       

    /**
     * Set accession
     *
     * @param \Oleg\OrderformBundle\Entity\Accession $accession
     * @return Slide
     */
    public function setAccession(\Oleg\OrderformBundle\Entity\Accession $accession = null)
    {
        $this->accession = $accession;
    
        return $this;
    }

    /**
     * Get accession
     *
     * @return \Oleg\OrderformBundle\Entity\Accession 
     */
    public function getAccession()
    {
        return $this->accession;
    }
    
    public function getOrderinfo() {
        return $this->orderinfo;
    }

    public function setOrderinfo( \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo = null ) {
        $this->orderinfo = $orderinfo;
    }

    /**
     * Set part
     *
     * @param \Oleg\OrderformBundle\Entity\Part $part
     * @return Slide
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

    /**
     * Set block
     *
     * @param \Oleg\OrderformBundle\Entity\Block $block
     * @return Slide
     */
    public function setBlock(\Oleg\OrderformBundle\Entity\Block $block = null)
    {
        $this->block = $block;
    
        return $this;
    }

    /**
     * Get block
     *
     * @return \Oleg\OrderformBundle\Entity\Block 
     */
    public function getBlock()
    {
        return $this->block;
    }
    
    public function __toString() {
        return "id=".$this->getId().", mag=".$this->getMag().", accession=".$this->getAccession(); 
    }
}