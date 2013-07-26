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
     * @ORM\Column(type="string", nullable=true, length=200)
     */
    protected $barcode;
    
    /**
     * @ORM\OneToOne(
     *      targetEntity="Stain", 
     *      inversedBy="slide", 
     *      cascade={"persist"}, 
     *      orphanRemoval=true
     * )
     * @ORM\JoinColumn(
     *      name="stain_id", 
     *      referencedColumnName="id",
     *      onDelete="CASCADE"
     * ) 
     * @Assert\NotBlank   
     */
    protected $stain;
        
    /**
     * @ORM\OneToOne(targetEntity="Scan", inversedBy="slide", cascade={"persist"})
     * @ORM\JoinColumn(name="scan_id", referencedColumnName="id", nullable=true)    
     */
    protected $scan;
    
    /**
     * Constructor
     */
//    public function __construct()
//    {
//        $this->scan = new \Doctrine\Common\Collections\ArrayCollection();
//    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
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

    /**
     * Set stain
     *
     * @param \Oleg\OrderformBundle\Entity\Stain $stain
     * @return Slide
     */
    public function setStain(\Oleg\OrderformBundle\Entity\Stain $stain = null)
    {
        $this->stain = $stain;
    
        return $this;
    }

    /**
     * Get stain
     *
     * @return \Oleg\OrderformBundle\Entity\Stain 
     */
    public function getStain()
    {
        return $this->stain;
    }
    
     public function __toString() {
        return "id=".$this->getId().", accession=".$this->getAccession(); 
    }
    

    /**
     * Set scan
     *
     * @param \Oleg\OrderformBundle\Entity\Scan $scan
     * @return Slide
     */
    public function setScan(\Oleg\OrderformBundle\Entity\Scan $scan = null)
    {
        $this->scan = $scan;
    
        return $this;
    }

    /**
     * Get scan
     *
     * @return \Oleg\OrderformBundle\Entity\Scan 
     */
    public function getScan()
    {
        return $this->scan;
    }

    /**
     * Set barcode
     *
     * @param string $barcode
     * @return Slide
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    
        return $this;
    }

    /**
     * Get barcode
     *
     * @return string 
     */
    public function getBarcode()
    {
        return $this->barcode;
    }
}