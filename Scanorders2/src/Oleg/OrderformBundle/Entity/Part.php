<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\Validator\Constraints as Assert;
//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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

    /**
     * @ORM\OneToMany(targetEntity="PartDiseaseType", mappedBy="part", cascade={"persist"})
     */
    protected $diseaseType;

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

    /**
     * For some slides, the slide can be attached to the Part directly, without block
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="part")
     */
    protected $slide;
    
    public function __construct( $withfields=false, $status='invalid', $provider=null, $source=null ) {
        parent::__construct($status,$provider);
        $this->block = new ArrayCollection();
        $this->slide = new ArrayCollection();

        //fields:
        $this->partname = new ArrayCollection();
        $this->sourceOrgan = new ArrayCollection();
        $this->description = new ArrayCollection();
        $this->disident = new ArrayCollection();
        $this->paper = new ArrayCollection();
        $this->diffDisident = new ArrayCollection();
        $this->diseaseType = new ArrayCollection();

        if( $withfields ) {
            $this->addPartname( new PartPartname($status,$provider,$source) );
            $this->addSourceOrgan( new PartSourceOrgan($status,$provider,$source) );
            $this->addDescription( new PartDescription($status,$provider,$source) );
            $this->addDisident( new PartDisident($status,$provider,$source) );
            $this->addPaper( new PartPaper($status,$provider,$source) );
            $this->addDiffDisident( new PartDiffDisident($status,$provider,$source) );
            $this->addDiseaseType( new PartDiseaseType($status,$provider,$source) );
        }
    }

    public function makeDependClone() {
        $this->partname = $this->cloneDepend($this->partname,$this);
        $this->sourceOrgan = $this->cloneDepend($this->sourceOrgan,$this);
        $this->description = $this->cloneDepend($this->description,$this);
        $this->disident = $this->cloneDepend($this->disident,$this);
        $this->paper = $this->cloneDepend($this->paper,$this);
        $this->diffDisident = $this->cloneDepend($this->diffDisident,$this);
        $this->diseaseType = $this->cloneDepend($this->diseaseType,$this);
    }

    public function __toString()
    {
        $partnameStr = ", partnameCount=".count($this->getPartname()).":";
        foreach( $this->getPartname() as $partname ) {
            $partnameStr = $partnameStr . $partname . "(". $partname->getStatus() . ", id=". $partname->getId().") ";
        }

        return "Part: id=".$this->id.
        ", accessionId=".$this->getAccession()->getId().
        ", partnameCount=".count($this->partname).", partnameId=".$this->partname->first()->getId().
        ", sourceOrgan=".$this->sourceOrgan->first().
        ", description=".$this->description->first().
        ", disident=".$this->disident->first().
        ", paper=".$this->paper->first().
        ", diffDisident=".$this->diffDisident->first().
        ", blockCount=".count($this->block).
        ", parentId=".$this->getParent()->getId().
        ", orderinfo=".count($this->orderinfo).
        $partnameStr."<br>";
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
    public function setDiseaseType($diseaseType) {
        $this->diseaseType = $diseaseType;
    }
    public function addDiseaseType($diseaseType)
    {
        if( $diseaseType == null ) {
            $diseaseType = new PartDiseaseType();
        }

        if( !$this->diseaseType->contains($diseaseType) ) {
            $diseaseType->setPart($this);
            $this->diseaseType->add($diseaseType);
        }

        return $this;
    }
    public function removeDiseaseType($diseaseType)
    {
        $this->diseaseType->removeElement($diseaseType);
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
    public function clearPartname()
    {
        $this->partname->clear();
    }


    public function setSourceOrgan($sourceOrgan) {
        $this->sourceOrgan = $sourceOrgan;
    }
    public function addSourceOrgan($sourceOrgan)
    {
        if( $sourceOrgan == null ) {
            $sourceOrgan = new PartSourceOrgan();
        }

        if( !$this->sourceOrgan->contains($sourceOrgan) ) {
            $sourceOrgan->setPart($this);
            $this->sourceOrgan->add($sourceOrgan);
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
        if( $description == null ) {
            $description = new PartDescription();
        }

        if( !$this->description->contains($description) ) {
            $description->setPart($this);
            $this->description->add($description);
        }

        return $this;
    }

    public function removeDescription($description)
    {
        $this->description->removeElement($description);
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
            $this->block->add($block);
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

    public function clearBlock() {
        $this->block->clear();
    }

    public function setDiffDisident($diffDisident) {
        $this->diffDisident = $diffDisident;
    }
    public function adddiffDisident($diffDisident)
    {
        if( $diffDisident == null ) {
            $diffDisident = new PartDiffDisident();
        }
        if( !$this->diffDisident->contains($diffDisident) ) {
            $this->diffDisident->add($diffDisident);
            $diffDisident->setPart($this);
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
        if( $paper == null ) {
            $paper = new PartPaper();
        }

        if( !$this->paper->contains($paper) ) {
            $this->paper->add($paper);
            $paper->setPart($this);
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
        if( $disident == null ) {
            $disident = new PartDisident();
        }

        if( !$this->disident->contains($disident) ) {
            $disident->setPart($this);
            $this->disident->add($disident);
        }

        return $this;
    }
    public function removeDisident($disident)
    {
        $this->disident->removeElement($disident);
    }

    /**
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return Part
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slide->contains($slide) ) {
            $slide->setPart($this);
            $this->slide->add($slide);
        }

        return $this;
    }

    /**
     * Remove slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     */
    public function removeSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        $this->slide->removeElement($slide);
    }

    /**
     * Get slide
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSlide()
    {
        return $this->slide;
    }

    public function clearSlide(){
        $this->slide->clear();
    }

    public function setSlide( $slide ){
        $this->slide = $slide;
    }

    //parent, children, key field methods
    public function setParent($parent) {
        $this->setAccession($parent);
        return $this;
    }

    public function getParent() {
        return $this->getAccession();
    }

    public function getChildren() {
        //echo "Part block count=".count($this->getBlock())."<br>";
        //echo "Part slide count=".count($this->getSlide())."<br>";

        $children = new ArrayCollection();

        foreach( $this->getBlock() as $block ) {
            $children->add($block);
        }

        foreach( $this->getSlide() as $slide ) {
            $children->add($slide);
        }

        return $children;
    }

    public function addChildren($child) {
        $childClass = new \ReflectionClass($child);
        $childClassName = $childClass->getShortName();
        if( $childClassName == "Block" ) {
            //echo "add  Block <br>";
            $this->addBlock($child);
        } else
        if( $childClassName == "Slide") {
            //echo "add  Slide <br>";
            $this->addSlide($child);
        } else {
            throw new \Exception('Part can not add object of the class ' . $childClassName );
        }
    }

    public function removeChildren($child) {
        $childClass = new \ReflectionClass($child);
        $childClassName = $childClass->getShortName();
        if( $childClassName == "Block" ) {
            //echo "remove  Block <br>";
            $this->removeBlock($child);
        } else
        if( $childClassName == "Slide") {
            //echo "remove  Slide <br>";
            $this->removeSlide($child);
        } else {
            throw new \Exception('Part can not remove object of the class ' . $childClassName );
        }
    }
    
    public function setChildren($children) {
        $childClass = new \ReflectionClass($children->first());
        $childClassName = $childClass->getShortName();
        if( $childClassName == "Block" ) {
            //echo "add  Block <br>";
            $this->setBlock($children);
        } else
        if( $childClassName == "Slide") {
            //echo "add  Slide <br>";
            $this->setSlide($children);
        } else {
            throw new \Exception('Part can not set object of the class ' . $childClassName );
        }
    }

    //don't use 'get' because later repo functions relay on "get" keyword
    public function obtainKeyField() {
        return $this->getPartname();
    }

    public function obtainKeyFieldName() {
        return "partname";
    }

    public function createKeyField() {
        $this->addPartname( new PartPartname() );
        return $this->obtainKeyField();
    }

    public function getArrayFields() {
        $fieldsArr = array('Partname','SourceOrgan','Description','Disident','Paper','DiffDisident','DiseaseType');
        return $fieldsArr;
    }

//    public function obtainArrayFieldNames() {
//        return array();
//    }

}