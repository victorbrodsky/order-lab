<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\BlockRepository")
 * @ORM\Table(name="block")
 */
class Block
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    //Block belongs to exactly one Accession => Block has only one Accession
    /**
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="block")
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", nullable=true)    
     */
    //protected $accession;

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="block")
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", nullable=true)    
     */
    protected $part;
    
    /**
     * One Block has Many slides
     * Accession might have many slide s
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="block", cascade={"persist"})
     */
    protected $slide;
    
    /**
     * Name is a letter (A,B ...)
     * @ORM\Column(type="string", length=3)
     * @Assert\NotBlank   
     */
    protected $name;  
    
    
    public function __construct() {
        $this->slide = new ArrayCollection();
    }
   
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
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
    
//    public function setSlide($slide){
//        $this->slide = $slide;
//        foreach( $slide as $slide_single ) {
//            $slide_single->setBlock($this);
//        }
//    }

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

    public function __toString()
    {
        //return "Block: id=".$this->id.", name".$this->name."<br>";
        $slide_info = "(";
        $count = 0;
        foreach( $this->slide as $slide ) {
            $slide_info .= $count.":" . $slide. "; ";
            $count++;
        }
        $slide_info .= ")";
        return "Block: id=".$this->id.", name".$this->name.", slideCount=".count($this->slide)." (".$slide_info.")<br>";
    }
}