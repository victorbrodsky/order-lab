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

    /**
     * Name is a letter (A,B ...)
     * @ORM\Column(type="string", length=3)
     * @Assert\NotBlank
     */
    protected $name;


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

    
    public function __construct() {
        $this->slide = new ArrayCollection();
        $this->orderinfo = new ArrayCollection();
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
        return "Block: id=".$this->id.", name=".$this->name.", slideCount=".count($this->slide)." (".$slide_info.")<br>";
    }

    /**
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Block
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
}