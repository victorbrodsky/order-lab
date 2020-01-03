<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\OrderformBundle\Repository\PartRepository")
 * @ORM\Table(name="scan_part")
 */
class Part extends ObjectAbstract
{

    /**
     * Part belongs to exactly one Accession => Part has only one Accession
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="part")
     * @ORM\JoinColumn(name="accession", referencedColumnName="id", nullable=true)
     */
    protected $accession;

    /**
     * @ORM\OneToMany(targetEntity="PartPartname", mappedBy="part", cascade={"persist"})
     */
    protected $partname;

    /**
     * @ORM\OneToMany(targetEntity="PartParttitle", mappedBy="part", cascade={"persist"})
     */
    protected $parttitle;
    
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
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="part")
     **/
    protected $message;

    /**
     * For some slides, the slide can be attached to the Part directly, without block
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="part")
     */
    protected $slide;

    
    public function __construct( $withfields=false, $status='invalid', $provider=null, $source=null ) {
        parent::__construct($status,$provider,$source);
        $this->block = new ArrayCollection();
        $this->slide = new ArrayCollection();

        //fields:
        $this->partname = new ArrayCollection();
        $this->parttitle = new ArrayCollection();
        $this->sourceOrgan = new ArrayCollection();
        $this->description = new ArrayCollection();
        $this->disident = new ArrayCollection();
        $this->diffDisident = new ArrayCollection();
        $this->diseaseType = new ArrayCollection();
        $this->paper = new ArrayCollection();

        if( $withfields ) {
            $this->addPartname( new PartPartname($status,$provider,$source) );
            $this->addParttitle( new PartParttitle($status,$provider,$source) );
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
        $this->parttitle = $this->cloneDepend($this->parttitle,$this);
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

        $partnameId = "N/A";
        if( $this->partname->first() ) {
            $partnameId = $this->partname->first()->getId();
        }

        $parentId = "N/A";
        if( $this->getAccession() ) {
            $parentId = $this->getAccession()->getId();
        }

        return "Part: id=".$this->id.
        ", accessionId=".$parentId.
        ", partnameCount=".count($this->partname).", partnameId=".$partnameId.
        ", sourceOrgan=".$this->sourceOrgan->first().
        ", description=".$this->description->first().
        ", disident=".$this->disident->first().
        ", paper=".$this->paper->first().
        ", diffDisident=".$this->diffDisident->first().
        ", blockCount=".count($this->block).
        ", message=".count($this->message).
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

    public function setAccession(\App\OrderformBundle\Entity\Accession $accession = null) {
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

    public function getParttitle()
    {
        return $this->parttitle;
    }
    public function addParttitle($item)
    {
        if( $item && !$this->parttitle->contains($item) ) {
            $this->parttitle->add($item);
            $item->setPart($this);
        }
        return $this;
    }
    public function removeParttitle($item)
    {
        $this->parttitle->removeElement($item);
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
     * @param \App\OrderformBundle\Entity\Block $block
     * @return Part
     */
    public function addBlock(\App\OrderformBundle\Entity\Block $block)
    {
        //echo "block count1=".count($this->getBlock())."<br>";
        if( !$this->block->contains($block) ) {
        //if( !$this->childAlreadyExist($block) ) {
            //echo "add block name=".$block->getBlockname()->first()."<br>";
            $block->setPart($this);
            $this->block->add($block);
        } else {
            //echo "Exist!!!!!!!!!!!!!!!!!!!! block name=".$block->getBlockname()->first()."<br>";
        }
        //echo "block count2=".count($this->getBlock())."<br><br>";
        return $this;
    }

    /**
     * Remove block
     *
     * @param \App\OrderformBundle\Entity\Block $block
     */
    public function removeBlock(\App\OrderformBundle\Entity\Block $block)
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
        //echo "adding DiffDisident=".$diffDisident."<br>";
        if( $diffDisident == null ) {
            //echo "skip DiffDisident=null<br>";
            //$diffDisident = new PartDiffDisident();
            return $this;
        }
        if( !$this->diffDisident->contains($diffDisident) ) {
            $this->diffDisident->add($diffDisident);
            if( $diffDisident ) {
                $diffDisident->setPart($this);
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

    public function cleanEmptyArrayFields() {
        //DiffDisident
        //echo $this;
        //echo "DiffDisident count1=".count($this->diffDisident)."<br>";
        //exit();
        foreach( $this->diffDisident as $field ) {
            //echo "clean field=".$field."<br>";
            if( $field->getField() == "" && count($this->diffDisident) > 1 ) {
                $this->removeDiffDisident($field);
            } else {
                //echo "keep diffDisident =".$field."<br>";
            }
        }
        //echo "DiffDisident count2=".count($this->diffDisident)."<br>";
    }

    //create and add empty array fields: documents, diffDisident
    public function createEmptyArrayFields() {

        if( count($this->getPaper()) == 0 ) {
            $this->addPaper( new PartPaper('valid',$this->getProvider(),$this->getSource()) );
        }

        if( count($this->getDiffDisident()) == 0 ) {
            $this->addDiffDisident( new PartDiffDisident('valid',$this->getProvider(),$this->getSource()) );
        }
    }

    /**
     * Add paper
     *
     * @param \App\OrderformBundle\Entity\PartPaper $paper
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
     * @param \App\OrderformBundle\Entity\PartPaper $paper
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
     * @param \App\OrderformBundle\Entity\Slide $slide
     * @return Part
     */
    public function addSlide(\App\OrderformBundle\Entity\Slide $slide)
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
     * @param \App\OrderformBundle\Entity\Slide $slide
     */
    public function removeSlide(\App\OrderformBundle\Entity\Slide $slide)
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

    //object info for blue strip
    public function obtainFullObjectName() {

        $fullNameArr = array();

        //partname
        $partname = $this->obtainValidField('partname');
        if( $partname ) {
            $fullNameArr[] = $partname->getField()."";
        }

        //sourceOrgan
        $sourceOrgan = $this->obtainValidField('sourceOrgan');
        if( $sourceOrgan && $sourceOrgan != "" ) {
            $fullNameArr[] = $sourceOrgan;
        }

        $fullName = implode(": ",$fullNameArr);

        return $fullName;
    }

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
        $fieldsArr = array('Partname','Parttitle','SourceOrgan','Description','Disident','Paper','DiffDisident','DiseaseType');
        return $fieldsArr;
    }

//    public function obtainArrayFieldNames() {
//        return array();
//    }

}