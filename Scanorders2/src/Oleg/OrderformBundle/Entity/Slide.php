<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

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

    /**
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="slide")
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id")
     */
    protected $block;
    
    /**
     * Keep info about accession, so we can get quickly how many slides in this accession
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="slide")
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    //protected $accession;  
    
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
     * @ORM\Column(type="string", nullable=true, length=200)
     */
    protected $barcode;
    
//    /**
//     * @ORM\OneToOne(
//     *      targetEntity="Stain",
//     *      cascade={"persist"},
//     *      orphanRemoval=true
//     * )
//     * @ORM\JoinColumn(
//     *      name="stain_id",
//     *      referencedColumnName="id",
//     *      onDelete="CASCADE"
//     * )
//     * @Assert\NotBlank
//     */
//    protected $stain;
    
//    /**
//     * @ORM\OneToOne(
//     *      targetEntity="Scan",
//     *      cascade={"persist"},
//     *      orphanRemoval=true
//     * )
//     * @ORM\JoinColumn(
//     *      name="scan_id",
//     *      referencedColumnName="id",
//     *      onDelete="CASCADE"
//     * )
//     * @Assert\NotBlank
//     */
//    protected $scan;

    /**
     * @ORM\OneToMany(targetEntity="Scan", mappedBy="slide")
     */
    protected $scan;

    /**
     * @ORM\OneToMany(targetEntity="Stain", mappedBy="slide")
     */
    protected $stain;
    
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="slide")
     **/
    protected $orderinfo; 
    
    public function __construct()
    {
        $this->orderinfo = new ArrayCollection();
        $this->scan = new ArrayCollection();
        $this->stain = new ArrayCollection();
    }
    
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

//    /**
//     * Set stain
//     *
//     * @param \Oleg\OrderformBundle\Entity\Stain $stain
//     * @return Slide
//     */
//    public function setStain(\Oleg\OrderformBundle\Entity\Stain $stain = null)
//    {
//        $this->stain = $stain;
//
//        return $this;
//    }
//
//    /**
//     * Get stain
//     *
//     * @return \Oleg\OrderformBundle\Entity\Stain
//     */
//    public function getStain()
//    {
//        return $this->stain;
//    }
//  
//    /**
//     * Set scan
//     *
//     * @param \Oleg\OrderformBundle\Entity\Scan $scan
//     * @return Slide
//     */
//    public function setScan(\Oleg\OrderformBundle\Entity\Scan $scan = null)
//    {
//        $this->scan = $scan;
//
//        return $this;
//    }
//
//    /**
//     * Get scan
//     *
//     * @return \Oleg\OrderformBundle\Entity\Scan
//     */
//    public function getScan()
//    {
//        return $this->scan;
//    }

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

    /**
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Slide
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
     * Get orderinfos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }


    /**
     * Add scan
     *
     * @param \Oleg\OrderformBundle\Entity\Scan $scan
     * @return Slide
     */
    public function addScan(\Oleg\OrderformBundle\Entity\Scan $scan)
    {
        if( !$this->scan->contains($scan) ) {
            $scan->setSlide($this);
            $this->scan[] = $scan;
        }
    
        return $this;
    }

    /**
     * Remove scan
     *
     * @param \Oleg\OrderformBundle\Entity\Scan $scan
     */
    public function removeScan(\Oleg\OrderformBundle\Entity\Scan $scan)
    {
        $this->scan->removeElement($scan);
    }

    /**
     * Get scan
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getScan()
    {
        return $this->scan;
    }

    /**
     * Add stain
     *
     * @param \Oleg\OrderformBundle\Entity\Stain $stain
     * @return Slide
     */
    public function addStain(\Oleg\OrderformBundle\Entity\Stain $stain)
    {
        if( !$this->stain->contains($stain) ) {
            $stain->setSlide($this);
            $this->stain[] = $stain;
        }
    
        return $this;
    }

    /**
     * Remove stain
     *
     * @param \Oleg\OrderformBundle\Entity\Stain $stain
     */
    public function removeStain(\Oleg\OrderformBundle\Entity\Stain $stain)
    {
        $this->stain->removeElement($stain);
    }

    /**
     * Get stain
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStain()
    {
        return $this->stain;
    }
    
    
    public function __toString() {
        return "Slide: id=".$this->getId().", diagnosis=".$this->getDiagnosis()."<br>";
    }
}