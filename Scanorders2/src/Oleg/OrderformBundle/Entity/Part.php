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
     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="part", cascade={"persist"})
     * @ORM\JoinColumn(name="organlist_id", referencedColumnName="id", nullable=true)
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
             
    
//    /**
//     * @ORM\Column(type="text", nullable=true, length=10000)
//     */
//    protected $diffDiagnoses;
    /**
     * @param \Doctrine\Common\Collections\Collection $property
     * @ORM\OneToMany(targetEntity="DiffDiagnoses", mappedBy="part", cascade={"persist"})
     */
    protected $diffDiagnoses;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $diseaseType;

    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $origin;

    /**
     * One Part has Many blocks
     * @param \Doctrine\Common\Collections\Collection $property
     * @ORM\OneToMany(targetEntity="Block", mappedBy="part")
     */
    protected $block;


    
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="part")
     **/
    protected $orderinfo; 
    
    public function __construct() {
        //$this->slide = new ArrayCollection();
        $this->block = new ArrayCollection();
        $this->orderinfo = new ArrayCollection();
        $this->diffDiagnoses = new ArrayCollection();
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

//    public function setDiffDiagnoses($diffDiagnoses) {
//        $this->diffDiagnoses = $diffDiagnoses;
//    }

    public function setDiseaseType($diseaseType) {
        $this->diseaseType = $diseaseType;
    }

    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Add block
     *
     * @param \Oleg\OrderformBundle\Entity\Block $block
     * @return Part
     */
    public function addBlock(\Oleg\OrderformBundle\Entity\Block $block)
    {
        if( !$this->block->contains($block) ) {
            $block->setPart($this);
            $this->block[] = $block;
        }

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
    public function setBlock(\Doctrine\Common\Collections\ArrayCollection $block)
    {
        $this->block = $block;
    }


    public function clearSlide(){
        foreach( $this->slide as $thisslide ) {
            $this->removeSlide($thisslide);
        }
    }

    public function clearBlock(){
        foreach( $this->block as $thisblock ) {
            $this->removeBlock($thisblock);
        }
    }
    
    

    public function __toString()
    {
//        $block_info = "(";
//        $count = 0;
//        foreach( $this->block as $block ) {
//            //$patient_info .= 'id='.$patient->getId().", mrn=".$patient->getMrn(). "; ";
//            $block_info .= $count.":" . $block. "; ";
//            $count++;
//        }
//        $block_info .= ")";
//        return "Part: id=".$this->id.", name=".$this->name.", blockCount=".count($this->block)." (".$block_info.")<br>";
        return "Part: id=".$this->id.", name=".$this->name.", blockCount=".count($this->block)."<br>";
    }
    

    /**
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Part
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


    /**
     * Add diffDiagnoses
     *
     * @param \Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses
     * @return Part
     */
    public function addDiffDiagnoses(\Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses)
    {
        if( !$this->diffDiagnoses->contains($diffDiagnoses) ) {
            $diffDiagnoses->setPart($this);
            $this->diffDiagnoses[] = $diffDiagnoses;
        }
    
        return $this;
    }
    //public function addDiffDiagnos(\Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses)
    public function addDiffDiagnos($diffDiagnoses)
    {
        if( $diffDiagnoses != null ) {
            $this->addDiffDiagnoses($diffDiagnoses);
        }
         
        return $this;
    }

    /**
     * Remove diffDiagnoses
     *
     * @param \Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses
     */
    public function removeDiffDiagnoses(\Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses)
    {
        $this->diffDiagnoses->removeElement($diffDiagnoses);
    }
    public function removeDiffDiagnos(\Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses)
    {
        $this->removeDiffDiagnoses($diffDiagnoses);
    }

    public function getDiffDiagnoses() {
        return $this->diffDiagnoses;
    }

}