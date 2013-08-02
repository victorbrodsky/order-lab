<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\PartRepository")
 * @ORM\Table(name="part")
 * @UniqueEntity({"accession","name"})
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
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", nullable=true)
     */
    protected $accession;

    /**
     * Name is a letter
     * @ORM\Column(type="string", length=3) 
     * @Assert\NotBlank  
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
    
    /**
     * One Part has Many slides
     * Accession might have many slide s
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="part", cascade={"persist"})
     */
    //protected $slide;
    
    /**
     * One Part has Many blocks
     * Accession might have many slide s
     * @ORM\OneToMany(targetEntity="Block", mappedBy="part")
     */
    protected $block;
    
    public function __construct() {
        //$this->slide = new ArrayCollection();
        $this->block = new ArrayCollection();
    }
    
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



    /**
     * Add slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return Part
     */
//    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
//    {
//        $this->slide[] = $slide;
//    
//        return $this;
//    }

    /**
     * Remove slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     */
//    public function removeSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
//    {
//        $this->slide->removeElement($slide);
//    }

    /**
     * Get slide
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
//    public function getSlide()
//    {
//        return $this->slide;
//    }

    /**
     * Add block
     *
     * @param \Oleg\OrderformBundle\Entity\Block $block
     * @return Part
     */
    public function addBlock(\Oleg\OrderformBundle\Entity\Block $block)
    {
        $block->setPart($this);
        $this->block[] = $block;
    
        return $this;
    }

    /**
     * Remove block
     *
     * @param \Oleg\OrderformBundle\Entity\Block $block
     */
    public function removeBlock(\Oleg\OrderformBundle\Entity\Block $block)
    {
        $this->block->removeElement($block);
    }

    /**
     * Get block
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBlock()
    {
        return $this->block;
    }
    
    public function __toString() {
        return $this->name;
    }
    
}