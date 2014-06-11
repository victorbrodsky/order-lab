<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\BlockRepository")
 * @ORM\Table(name="block")
 */
class Block extends ObjectAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="BlockBlockname", mappedBy="block", cascade={"persist"})
     */
    protected $blockname;

    /**
     * @ORM\OneToMany(targetEntity="BlockSectionsource", mappedBy="block", cascade={"persist"})
     */
    protected $sectionsource;


    //////////////  OBJECTS /////////////

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="block")
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", nullable=true)
     */
    protected $part;
    
    //cascade={"persist"}
    /**
     * One Block has Many slides
     * Accession might have many slide s
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="block")
     */
    protected $slide;
    
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="block")
     **/
    protected $orderinfo;

    /**
     * @param \Doctrine\Common\Collections\Collection $property
     * @ORM\OneToMany(targetEntity="BlockSpecialStains", mappedBy="block", cascade={"persist"})
     */
    protected $specialStains;

    
    public function __construct( $withfields=false, $status='invalid', $provider=null, $source=null ) {
        parent::__construct($status,$provider);
        $this->slide = new ArrayCollection();
        $this->specialStains = new ArrayCollection();

        //fields:
        $this->blockname = new ArrayCollection();
        $this->sectionsource = new ArrayCollection();

        if( $withfields ) {
            $this->addBlockname( new BlockBlockname($status,$provider,$source) );
            $this->addSectionsource( new BlockSectionsource($status,$provider,$source) );
            $this->addSpecialStain( new BlockSpecialStains($status,$provider,$source) );
        }
    }

    public function makeDependClone() {
        $this->blockname = $this->cloneDepend($this->blockname,$this);
        $this->sectionsource = $this->cloneDepend($this->sectionsource,$this);
        $this->specialStains = $this->cloneDepend($this->specialStains,$this);
    }


    public function getBlockname() {
        return $this->blockname;
    }

    public function setBlockname($blockname) {
        $this->blockname = $blockname;
    }

    public function addBlockname($blockname)
    {
        if( $blockname ) {
            if( !$this->blockname->contains($blockname) ) {
                $blockname->setBlock($this);
                $this->blockname->add($blockname);
            }
        }

        return $this;
    }
    public function removeBlockname($blockname)
    {
        $this->blockname->removeElement($blockname);
    }
    public function clearBlockname()
    {
        $this->blockname->clear();
    }


    public function getSectionsource() {
        return $this->sectionsource;
    }

    public function setSectionsource($sectionsource) {
        $this->sectionsource = $sectionsource;
    }

    public function addSectionsource($sectionsource)
    {
        if( $sectionsource == null ) {
            $sectionsource = new BlockSectionsource();
        }
        if( !$this->sectionsource->contains($sectionsource) ) {
            $sectionsource->setBlock($this);
            $this->sectionsource->add($sectionsource);
        }

        return $this;
    }

    public function removeSectionsource($sectionsource)
    {
        $this->sectionsource->removeElement($sectionsource);
    }


    /**
     * Add slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return Block
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slide->contains($slide) ) {
            $slide->setBlock($this);
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

    /**
     * Set part
     *
     * @param \Oleg\OrderformBundle\Entity\Part $part
     * @return Block
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

    public function addSpecialStain( $specialStain )
    {
        if( $specialStain != null ) {
            if( !$this->specialStains->contains($specialStain) ) {
                $this->specialStains->add($specialStain);
                $specialStain->setBlock($this);
                $specialStain->setProvider($this->getProvider());
            }
        }
        return $this;
    }
    public function addSpecialStains( $specialStain ) {
        $this->addSpecialStain( $specialStain );
        return $this;
    }

    public function removeSpecialStain($specialStain)
    {
        $this->specialStains->removeElement($specialStain);
    }
    public function removeSpecialStains($specialStain)
    {
        $this->removeSpecialStain($specialStain);
    }

    public function getSpecialStains()
    {
        return $this->specialStains;
    }

    public function __toString()
    {
        $parentId = null;
        if( $this->getParent() )
            $parentId = $this->getParent()->getId();

        $sectionStr = "{";
        foreach( $this->sectionsource as $section ) {
            $sectionStr .= $section."(".$section->getStatus()."),";
        }
        $sectionStr .= "}";

        $nameStr = "{";
        foreach( $this->blockname as $name ) {
            $nameStr .= $name."(".$name->getStatus()."),";
        }
        $nameStr .= "}";

        return "Block: id=".$this->getId().
        ", blocknames=".$nameStr.
        ", section=".$sectionStr.
        ", status=".$this->status.
        ", parentId=".$parentId.
        "<br>";
    }


    //parent, children, key field methods
    public function setParent($parent) {
        $this->setPart($parent);
        return $this;
    }

    public function getParent() {
        return $this->getPart();
    }

    public function getChildren() {
        return $this->getSlide();
    }

    public function addChildren($child) {
        $this->addSlide($child);
    }

    public function removeChildren($child) {
        $this->removeSlide($child);
    }

    public function setChildren($children) {
        $this->setSlide($children);
    }
    
    //don't use 'get' because later repo functions relay on "get" keyword
    public function obtainKeyField() {
        return $this->getBlockname();
    }

    public function obtainKeyFieldName() {
        return "blockname";
    }

    public function createKeyField() {
        $this->addBlockname( new BlockBlockname() );
        return $this->obtainKeyField();
    }

    public function getArrayFields() {
        $fieldsArr = array('Blockname','Sectionsource','Specialstains');
        return $fieldsArr;
    }

}