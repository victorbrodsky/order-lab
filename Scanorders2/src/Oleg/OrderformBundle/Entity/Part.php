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
    
    /**
     * @ORM\OneToMany(targetEntity="PartDiagnos", mappedBy="part", cascade={"persist"})
     */
    protected $diagnos;

    /**
     * @ORM\OneToMany(targetEntity="PartPaper", mappedBy="part", cascade={"persist"})
     */
    protected $paper;

    /**
     * @param \Doctrine\Common\Collections\Collection $property
     * @ORM\OneToMany(targetEntity="DiffDiagnoses", mappedBy="part", cascade={"persist"})
     */
    protected $diffDiagnoses;

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
        $this->diffDiagnoses = new ArrayCollection();

        //fields:
        $this->partname = new ArrayCollection();
        $this->sourceOrgan = new ArrayCollection();
        $this->description = new ArrayCollection();
        $this->diagnos = new ArrayCollection();
        $this->paper = new ArrayCollection();

        if( $withfields ) {
            $this->addPartname( new PartPartname($validity) );
            $this->addSourceOrgan( new PartSourceOrgan($validity) );
            $this->addDescription( new PartDescription($validity) );
            $this->addDiagnos( new PartDiagnos($validity) );
            $this->addPaper( new PartPaper($validity) );
            $this->addDiffDiagnoses( new DiffDiagnoses() );
        }
    }

    public function __toString()
    {
        return "Part: id=".$this->id.
        ", partname=".$this->partname->first().
        ", sourceOrgan=".$this->sourceOrgan->first().
        ", description=".$this->description->first().
        ", diagnos=".$this->diagnos[0].
        ", paper=".$this->paper->first().
        ", diffDiagnoses=".$this->diffDiagnoses[0].
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

    public function getDiagnos() {
        return $this->diagnos;
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

    public function setDiagnos($diagnos) {
        $this->diagnos = $diagnos;
    }
    public function addDiagnos($diagnos)
    {
        echo "@@@@@@@@@@@@@@@@@@ add diagnos value=".$diagnos."<br>";
        if( $diagnos ) {
            if( !$this->diagnos->contains($diagnos) ) {
                $diagnos->setPart($this);
                $this->diagnos->add($diagnos);
            }
        }

        return $this;
    }
    public function removeDiagnos($diagnos)
    {
        $this->diagnos->removeElement($diagnos);
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

    public function setDiffDiagnoses($diffDiagnoses) {
        $this->diffDiagnoses = $diffDiagnoses;
    }
    /**
     * Add diffDiagnoses
     *
     * @param \Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses
     * @return Part
     */
    public function addDiffDiagnoses($diffDiagnoses)
    {
        if( $diffDiagnoses != null ) {
            if( !$this->diffDiagnoses->contains($diffDiagnoses) ) {
                $diffDiagnoses->setPart($this);
                $this->diffDiagnoses[] = $diffDiagnoses;
            }
        }
    
        return $this;
    }

    /**
     * Remove diffDiagnoses
     *
     * @param \Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses
     */
    public function removeDiffDiagnoses($diffDiagnoses)
    {
        $this->diffDiagnoses->removeElement($diffDiagnoses);
    }
//    public function removeDiffDiagnos($diffDiagnoses)
//    {
//        $this->removeDiffDiagnoses($diffDiagnoses);
//    }

    public function getDiffDiagnoses() {
        return $this->diffDiagnoses;
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

//    /**
//     * Add diffDiagnoses
//     *
//     * @param \Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses
//     * @return Part
//     */
//    public function addDiffDiagnose(\Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses)
//    {
//        $this->diffDiagnoses[] = $diffDiagnoses;
//
//        return $this;
//    }
//
//    /**
//     * Remove diffDiagnoses
//     *
//     * @param \Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses
//     */
//    public function removeDiffDiagnose(\Oleg\OrderformBundle\Entity\DiffDiagnoses $diffDiagnoses)
//    {
//        $this->diffDiagnoses->removeElement($diffDiagnoses);
//    }
}