<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

//* @UniqueEntity({"accession","partname"})

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\PartRepository")
 * @ORM\Table(name="part")
 */
class Part extends OrderAbstract
{

    /**
     * Part belongs to exactly one Accession => Part has only one Accession
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="part")
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", nullable=true)
     */
    protected $accession;

    /**
     * @ORM\OneToMany(targetEntity="PartPartname", mappedBy="part", cascade={"persist"})
     */
    protected $partname;
    
    //*********************************************// 
    // optional fields
    //*********************************************//     

    /**
     * @ORM\OneToMany(targetEntity="PartSourceOrgan", mappedBy="part", cascade={"persist"})
     */
    protected $sourceOrgan;

    /**
     * @ORM\OneToMany(targetEntity="PartDescription", mappedBy="part", cascade={"persist"})
     */
    protected $description;

    //diagnosis: disident (diagnoses causes the problem as reserved word)
    /**
     * @ORM\OneToMany(targetEntity="PartDisident", mappedBy="part", cascade={"persist"})
     */
    protected $disident;

    /**
     * @ORM\OneToMany(targetEntity="PartPaper", mappedBy="part", cascade={"persist"})
     */
    protected $paper;

    /**
     * @ORM\OneToMany(targetEntity="PartDiffDisident", mappedBy="part", cascade={"persist"})
     */
    protected $diffDisident;

    /////////////////////// Type of Disease TODO: make it as separate object? /////////////////////
    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $diseaseType;

    /**
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $origin;

    /**
     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="partprimary", cascade={"persist"})
     * @ORM\JoinColumn(name="primaryorgan_id", referencedColumnName="id", nullable=true)
     */
    protected $primaryOrgan;
    //////////////////////////////////// EOF Type of Disease /////////////////////////////////////

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
    
    public function __construct( $withfields=false, $validity=0 ) {
        $this->block = new ArrayCollection();
        $this->orderinfo = new ArrayCollection();
        $this->diffDisident = new ArrayCollection();

        //fields:
        $this->partname = new ArrayCollection();
        $this->sourceOrgan = new ArrayCollection();
        $this->description = new ArrayCollection();
        $this->disident = new ArrayCollection();
        $this->paper = new ArrayCollection();

        if( $withfields ) {
            $this->addPartname( new PartPartname($validity) );
            $this->addSourceOrgan( new PartSourceOrgan($validity) );
            $this->addDescription( new PartDescription($validity) );
            $this->addDisident( new PartDisident($validity) );
            $this->addPaper( new PartPaper($validity) );
            $this->addDiffDisident( new PartDiffDisident() );
        }
    }

    public function __toString()
    {
        return "Part: id=".$this->id.
        ", partname=".$this->partname->first().
        ", sourceOrgan=".$this->sourceOrgan->first().
        ", description=".$this->description->first().
        ", disident=".$this->disident->first().
        ", paper=".$this->paper->first().
        ", diffDisident=".$this->diffDisident->first().
        ", blockCount=".count($this->block)."<br>";
    }
    
    public function getId() {
        return $this->id;
    }

    public function getAccession() {
        return $this->accession;
    }

    public function getPartname() {
        return $this->partname;
    }

    public function getSourceOrgan() {
        return $this->sourceOrgan;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDiseaseType() {
        return $this->diseaseType;
    }

    public function setAccession(\Oleg\OrderformBundle\Entity\Accession $accession = null) {
        $this->accession = $accession;
        return $this;
    }

    public function setPartname($partname) {
        $this->partname = $partname;
    }
    public function addPartname($partname)
    {
        //echo "@@@@@@@@@@@@@@@@@@ add Partname value=".$partname."<br>";
        if( $partname ) {
            if( !$this->partname->contains($partname) ) {
                $partname->setPart($this);
                $this->partname->add($partname);
            }
        }

        return $this;
    }
    public function removePartname($partname)
    {
        $this->partname->removeElement($partname);
    }


    public function setSourceOrgan($sourceOrgan) {
        $this->sourceOrgan = $sourceOrgan;
    }
    public function addSourceOrgan($sourceOrgan)
    {
        echo "@@@@@@@@@@@@@@@@@@ add sourceOrgan value=".$sourceOrgan."<br>";
        if( $sourceOrgan ) {
            if( !$this->sourceOrgan->contains($sourceOrgan) ) {
                $sourceOrgan->setPart($this);
                $this->sourceOrgan->add($sourceOrgan);
            }
        }

        return $this;
    }
    public function removeSourceOrgan($sourceOrgan)
    {
        $this->sourceOrgan->removeElement($sourceOrgan);
    }

    public function setDescription($description) {
        $this->description = $description;
    }
    public function addDescription($description)
    {
        echo "@@@@@@@@@@@@@@@@@@ add Description value=".$description."<br>";
        if( $description ) {
            if( !$this->description->contains($description) ) {
                $description->setPart($this);
                $this->description->add($description);
            }
        }

        return $this;
    }
    public function removeDescription($description)
    {
        $this->description->removeElement($description);
    }


    /**
     * @param mixed $primaryOrgan
     */
    public function setPrimaryOrgan($primaryOrgan)
    {
        $this->primaryOrgan = $primaryOrgan;
    }

    /**
     * @return mixed
     */
    public function getPrimaryOrgan()
    {
        return $this->primaryOrgan;
    }
//    public function addPrimaryOrgan($primaryOrgan)
//    {
//        if( $primaryOrgan ) {
//            if( !$this->primaryOrgan->contains($primaryOrgan) ) {
//                $primaryOrgan->setPart($this);
//                $this->primaryOrgan->add($primaryOrgan);
//            }
//        }
//
//        return $this;
//    }
//    public function removePrimaryOrgan($primaryOrgan)
//    {
//        $this->primaryOrgan->removeElement($primaryOrgan);
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

    public function setDiffDisident($diffDisident) {
        $this->diffDisident = $diffDisident;
    }
    public function adddiffDisident($diffDisident)
    {
        echo "@@@@@@@@@@@@@@@@@@ add diffDisident value=".$diffDisident."<br>";
        if( $diffDisident != null ) {
            if( !$this->diffDisident->contains($diffDisident) ) {
                $diffDisident->setPart($this);
                $this->diffDisident[] = $diffDisident;
            }
        }
    
        return $this;
    }
    public function removeDiffDisident($diffDisident)
    {
        $this->diffDisident->removeElement($diffDisident);
    }
    public function getDiffDisident() {
        return $this->diffDisident;
    }

    /**
     * Add paper
     *
     * @param \Oleg\OrderformBundle\Entity\PartPaper $paper
     * @return Part
     */
    public function addPaper($paper)
    {
        if( $paper != null ) {
            if( !$this->paper->contains($paper) ) {
                $paper->setPart($this);
                $this->paper[] = $paper;
            }
        }
    
        return $this;
    }
    /**
     * Remove paper
     *
     * @param \Oleg\OrderformBundle\Entity\PartPaper $paper
     */
    public function removePaper($paper)
    {
        $this->paper->removeElement($paper);
    }

    /**
     * Get paper
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPaper()
    {
        return $this->paper;
    }

    public function getDisident() {
        return $this->disident;
    }
    public function setDisident($disident) {
        $this->disident = $disident;
    }
    public function addDisident($disident)
    {
        echo "@@@@@@@@@@@@@@@@@@ add disident value=".$disident."<br>";
        if( $disident ) {
            if( !$this->disident->contains($disident) ) {
                $disident->setPart($this);
                $this->disident->add($disident);
            }
        }
        return $this;
    }
    public function removeDisident($disident)
    {
        $this->disident->removeElement($disident);
    }


}