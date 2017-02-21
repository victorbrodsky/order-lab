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

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\SlideRepository")
 * @ORM\Table(name="scan_slide")
 */
class Slide extends ObjectAbstract
{

    //*******************************// 
    // first step fields 
    //*******************************//

    /**
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="slide")
     * @ORM\JoinColumn(name="block", referencedColumnName="id")
     */
    protected $block;

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="slide")
     * @ORM\JoinColumn(name="part", referencedColumnName="id")
     */
    protected $part;
    
    //*********************************************// 
    // second part of the form (optional) 
    //*********************************************//                
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $microscopicdescr;

    /**
     * @param \Doctrine\Common\Collections\Collection $property
     * @ORM\OneToMany(targetEntity="RelevantScans", mappedBy="slide", cascade={"persist"})
     */
    protected $relevantScans;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $barcode;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\ManyToOne(targetEntity="SlideType", cascade={"persist"})
     * @ORM\JoinColumn(name="slidetype", referencedColumnName="id", nullable=true)
     */
    protected $slidetype;

    /**
     * @ORM\OneToMany(targetEntity="Imaging", mappedBy="slide", cascade={"persist"})
     */
    protected $scan;

    /**
     * @ORM\OneToMany(targetEntity="Stain", mappedBy="slide", cascade={"persist"})
     */
    protected $stain;
    
    /**
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="slide")
     **/
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="Educational", inversedBy="slides", cascade={"persist"})
     * @ORM\JoinColumn(name="educational", referencedColumnName="id", nullable=true)
     */
    protected $educational;

    /**
     * @ORM\ManyToOne(targetEntity="Research", inversedBy="slides", cascade={"persist"})
     * @ORM\JoinColumn(name="research", referencedColumnName="id", nullable=true)
     */
    protected $research;

    /**
     * Sequence in table form scan order
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sequence;


    
    public function __construct( $withfields=false, $status='valid', $provider=null, $source=null )
    {
        parent::__construct($status,$provider,$source);
        $this->scan = new ArrayCollection();
        $this->stain = new ArrayCollection();
        $this->relevantScans = new ArrayCollection();

        if( $withfields ) {
            $this->addRelevantScan( new RelevantScans($status,$provider,$source) );
            $this->addScan( new Imaging($status,$provider,$source) );
            $this->addStain( new Stain($status,$provider,$source) );
        }
    }

    public function makeDependClone() {
        $this->scan = $this->cloneDepend($this->scan,$this);
        $this->stain = $this->cloneDepend($this->stain,$this);
        $this->relevantScans = $this->cloneDepend($this->relevantScans,$this);
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getMicroscopicdescr() {
        return $this->microscopicdescr;
    }

    public function setMicroscopicdescr($microscopicdescr) {
        $this->microscopicdescr = $microscopicdescr;
    }

    public function getRelevantscan() {
        return $this->relevantScans;
    }

    public function setRelevantscan($relevantscan) {
        $this->relevantScans = $relevantscan;
    }

    /**
     * Set block
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
     */
    public function getBlock()
    {
        return $this->block;
    }

    public function setPart(\Oleg\OrderformBundle\Entity\Part $part = null)
    {
        $this->part = $part;

        return $this;
    }
    public function getPart()
    {
        return $this->part;
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


    public function addScan($scan)
    {
        if( !$this->scan->contains($scan) ) {
            $scan->setSlide($this);
            $this->scan->add($scan);
        }
        return $this;
    }
    public function removeScan($scan)
    {
        $this->scan->removeElement($scan);
    }
    public function getScan()
    {
        return $this->scan;
    }
    public function clearScan()
    {
        foreach( $this->getScan() as $scan ) {
            $this->removeScan($scan);
            $scan->setSlide(null);
        }
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
            $this->stain->add($stain);
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
        $stain = "";
        $mag = "";
        if( count($this->getStain()) > 0 && count($this->getScan())>0 ) {
            $mag = $this->getScan()->first()->getMagnification();
            $stain = $this->getStain()->first()->getField();
        }

        return "Slide: id=".$this->getId().", title=".$this->getTitle().", slidetype=".$this->getSlidetype().
                ", provider=".$this->getProvider().
                ", source=".$this->getSource().
                ", parentId=".$this->getParent()->getId().
                ", message count=".count($this->getMessage()).", first message:=".$this->getMessage()->first().
                ", scan count=".count($this->getScan()).", firstscanid=".$this->getScan()->first()->getId().
                ", stain count=".count($this->getStain()).", firststainid=".$this->getStain()->first()->getId().
                ", stain=".$stain.", mag=".$mag.
                ", relScansCount=".count($this->getRelevantScans()).":".$this->getRelevantScans()->first()."<br>";
    }

    /**
     * Add relevantScans
     *
     * @param \Oleg\OrderformBundle\Entity\RelevantScans $relevantScans
     * @return Slide
     */
    public function addRelevantScan( $relevantScans )
    {

        if( $relevantScans == null ) {
            $relevantScans = new RelevantScans();
        }

        if( !$this->relevantScans->contains($relevantScans) ) {
            $this->relevantScans->add($relevantScans);
            $relevantScans->setSlide($this);
            $relevantScans->setProvider($this->getProvider());
        }

        return $this;
    }

    /**
     * Remove relevantScans
     *
     * @param \Oleg\OrderformBundle\Entity\RelevantScans $relevantScans
     */
    public function removeRelevantScan(\Oleg\OrderformBundle\Entity\RelevantScans $relevantScans)
    {
        $this->relevantScans->removeElement($relevantScans);
    }

    /**
     * Get relevantScans
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelevantScans()
    {
        return $this->relevantScans;
    }

    /**
     * @param mixed $slidetype
     */
    public function setSlidetype($slidetype)
    {
        $this->slidetype = $slidetype;
    }

    /**
     * @return mixed
     */
    public function getSlidetype()
    {
        return $this->slidetype;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $educational
     */
    public function setEducational($educational)
    {
        $this->educational = $educational;
    }

    /**
     * @return mixed
     */
    public function getEducational()
    {
        return $this->educational;
    }

    /**
     * @param mixed $research
     */
    public function setResearch($research)
    {
        $this->research = $research;
    }

    /**
     * @return mixed
     */
    public function getResearch()
    {
        return $this->research;
    }

    /**
     * @param mixed $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * @return mixed
     */
    public function getSequence()
    {
        return $this->sequence;
    }




    public function cleanEmptyArrayFields() {
        //relevantScans
        //echo "relevantScans count1=".count($this->relevantScans)."<br>";
        foreach( $this->relevantScans as $field ) {
            if( $field->getField() == "" && count($this->relevantScans) > 1 ) {
                $this->removeRelevantScan($field);
                //$field->setSlide(NULL);
            } else {
                //echo "keep relevantScans =".$field."<br>";
            }
        }
        //echo "relevantScans count2=".count($this->relevantScans)."<br>";
        //exit();
    }

    public function getChildren() {
        return $this->getScan();
    }

    public function addChildren($child) {
        $this->addScan($child);
    }

    public function removeChildren($child) {
        $this->removeScan($child);
    }

    public function setChildren($children) {
        $this->setScan($children);
    }

    public function setScan( $scan ){
        $this->scan = $scan;
    }

    public function obtainKeyField() {
        return null;
    }
    
    //parent, children, key field methods
    public function setParent($parent) {
        $parentClass = new \ReflectionClass($parent);
        $parentClassName = $parentClass->getShortName();
        if( $parentClassName == "Block" ) {
            //echo "set  Block <br>";
            $this->setBlock($parent);
            $this->setPart(NULL);
        } else
        if( $parentClassName == "Part") {
            //echo "set  Part <br>";
            $this->setPart($parent);
            $this->setBlock(NULL);
        } else {
            throw new \Exception('Parent can not be set of the class ' . $parentClassName );
        }
        return $this;
    }

    public function getParent() {
        if( $this->getBlock() ) {
            return $this->getBlock();
        } else if( $this->getPart() ) {
            return $this->getPart();
        } else {
            throw new \Exception( 'Slide does not have parent; slide id='.$this->id );
        }
    }

    public function obtainPatient() {
        $parent = $this->getParent();
        $parentClass = new \ReflectionClass($parent);
        $parentClassName = $parentClass->getShortName();
        if( $parentClassName == "Block" ) {
                        //block    part        acc           proc        encounter   patient
            $patient = $parent->getParent()->getParent()->getParent()->getParent()->getParent();
        } else
        if( $parentClassName == "Part") {
                        //part     acc         proc        encounter     patient
            $patient = $parent->getParent()->getParent()->getParent()->getParent();
        } else {
            throw new \Exception('Parent can not be set of the class ' . $parentClassName );
        }
        return $patient;
    }

    public function obtainAccession() {
        $parent = $this->getParent();
        $parentClass = new \ReflectionClass($parent);
        $parentClassName = $parentClass->getShortName();
        if( $parentClassName == "Block" ) {
                        //block     part        acc
            $accession = $parent->getParent()->getParent();
        } else
            if( $parentClassName == "Part") {
                            //part       acc
                $accession = $parent->getParent();
            } else {
                throw new \Exception('Accession can not be set of the class ' . $parentClassName );
            }

        return $accession;
    }

    public function obtainPart() {
        $parent = $this->getParent();
        $parentClass = new \ReflectionClass($parent);
        $parentClassName = $parentClass->getShortName();
        if( $parentClassName == "Block" ) {
                    //block     part
            $part = $parent->getParent();
        } else
            if( $parentClassName == "Part") {
                $part = $parent;
            } else {
                throw new \Exception('Accession can not be set of the class ' . $parentClassName );
            }

        return $part;
    }

    public function obtainBlock() {
        $parent = $this->getParent();
        $parentClass = new \ReflectionClass($parent);
        $parentClassName = $parentClass->getShortName();
        if( $parentClassName == "Block" ) {
            return $parent;
        }
        return null;
    }


    //object info for blue strip
    public function obtainFullObjectName() {

        $fullNameArr = array();

        //stain
        $name = $this->obtainValidField('stain');
        //echo "name=".$name."<br>";
        if( $name && $name != "" ) {
            //pull the abbreviation for the Stain Name. Only iff abbreviation is not available, then pull the Full name (no short names).
            if( $name->getField()->getAbbreviation() ) {
                $fullNameArr[] = $name->getField()->getAbbreviation()."";
            } else {
                $fullNameArr[] = $name->getField()->getName()."";
            }
        }

        //title
        $title = $this->getTitle();
        if( $title ) {
            $fullNameArr[] = $title."";
        }

        //slidetype
        $slidetype = $this->getSlidetype();
        if( $slidetype && $slidetype."" != "" && $slidetype."" != "Permanent Section") {
            $fullNameArr[] = $slidetype;
        }

        $fullName = implode(", ",$fullNameArr);

        return $fullName;
    }

}