<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\PartRepository")
 * @ORM\Table(name="part")
 * @UniqueEntity({"accession","partname"})
 */
class Part extends OrderAbstract
{
    
//    /**
//     * @ORM\Id
//     * @ORM\Column(type="integer")
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    protected $id;
   
    /**
     * Part belongs to exactly one Accession => Part has only one Accession
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="part")
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", nullable=true)
     */
    protected $accession;

//    /**
//     * Name is a letter
//     * @ORM\Column(type="string", length=3)
//     * @Assert\NotBlank
//     */
//    protected $name;
    /**
     * @ORM\OneToMany(targetEntity="PartName", mappedBy="part", cascade={"persist"})
     */
    protected $partname;
    
    //*********************************************// 
    // optional fields
    //*********************************************//     

//    /**
//     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="part", cascade={"persist"})
//     * @ORM\JoinColumn(name="organlist_id", referencedColumnName="id", nullable=true)
//     */
//    protected $sourceOrgan;
    /**
     * @ORM\OneToMany(targetEntity="PartSourceOrgan", mappedBy="part", cascade={"persist"})
     */
    protected $sourceOrgan;

    /**
     * @ORM\OneToMany(targetEntity="PartDescription", mappedBy="part", cascade={"persist"})
     */
    protected $description;
    
//    /**
//     * @ORM\Column(type="text", nullable=true, length=10000)
//     */
    /**
     * @ORM\OneToMany(targetEntity="PartDiagnosis", mappedBy="part", cascade={"persist"})
     */
    protected $diagnosis;

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
        //$this->paper = new ArrayCollection();
        $this->block = new ArrayCollection();
        $this->orderinfo = new ArrayCollection();
        $this->diffDiagnoses = new ArrayCollection();

        //fields:
        $this->partname = new ArrayCollection();
        $this->sourceOrgan = new ArrayCollection();
        //$this->primaryOrgan = new ArrayCollection();
        $this->description = new ArrayCollection();
        $this->diagnosis = new ArrayCollection();
        $this->paper = new ArrayCollection();

        if( $withfields ) {
            $this->addPartname( new PartName($validity) );
            $this->addSourceOrgan( new PartSourceOrgan($validity) );
            $this->addDescription( new PartDescription($validity) );
            $this->addDiagnosis( new PartDiagnosis($validity) );
            $this->addPaper( new PartPaper($validity) );
            $this->addDiffDiagnoses( new DiffDiagnoses() );
        }
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

    public function getDiagnosis() {
        return $this->diagnosis;
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

    public function setDiagnosis($diagnosis) {
        $this->diagnosis = $diagnosis;
    }
    public function addDiagnosis($diagnosis)
    {
        if( $diagnosis ) {
            if( !$this->diagnosis->contains($diagnosis) ) {
                $diagnosis->setPart($this);
                $this->diagnosis->add($diagnosis);
            }
        }

        return $this;
    }
    public function removeDiagnosis($diagnosis)
    {
        $this->diagnosis->removeElement($diagnosis);
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
        return "Part: id=".$this->id.", partname=".$this->partname.", blockCount=".count($this->block)."<br>";
    }
    

//    /**
//     * Add orderinfo
//     *
//     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
//     * @return Part
//     */
//    public function addOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
//    {
//        if( !$this->orderinfo->contains($orderinfo) ) {
//            $this->orderinfo->add($orderinfo);
//        }
//    }
//
//    /**
//     * Remove orderinfo
//     *
//     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
//     */
//    public function removeOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
//    {
//        $this->orderinfo->removeElement($orderinfo);
//    }

//    /**
//     * Get orderinfo
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getOrderinfo()
//    {
//        return $this->orderinfo;
//    }


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