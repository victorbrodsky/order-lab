<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\BlockRepository")
 * @ORM\Table(name="block")
 */
class Block extends OrderAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="BlockBlockname", mappedBy="block", cascade={"persist"})
     */
    protected $blockname;


    //////////////  OBJECTS /////////////

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="block")
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
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

    
    public function __construct( $withfields=false, $validity=0 ) {
        $this->slide = new ArrayCollection();
        $this->orderinfo = new ArrayCollection();

        //fields:
        $this->blockname = new ArrayCollection();

        if( $withfields ) {
            $this->addBlockname( new BlockBlockname($validity) );
        }
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
            $this->slide[] = $slide;
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
        foreach( $this->slide as $thisslide ) {
            $this->removeSlide($thisslide);
        }
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

    public function setParent($parent)
    {
        $this->setPart($parent);
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

    public function __toString()
    {
        //return "Block: id=".$this->id.", name".$this->name."<br>";
//        $slide_info = "(";
//        $count = 0;
//        foreach( $this->slide as $slide ) {
//            $slide_info .= $count.":" . $slide. "; ";
//            $count++;
//        }
//        $slide_info .= ")";
//        return "Block: id=".$this->id.", name=".$this->name.", slideCount=".count($this->slide)." (".$slide_info.")<br>";
        return "Block: id=".$this->id.", blockname=".$this->blockname->first()."<br>";
    }

}