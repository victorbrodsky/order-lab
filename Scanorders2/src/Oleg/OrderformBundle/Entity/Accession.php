<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//Accession is a key for all other tables such as Patient, Case, Part, Block, Slide (?) 
//All of them have accession object (?)
/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\AccessionRepository")
 * @ORM\Table(name="accession")
 */
class Accession {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * Accession string i.e. S12-99998
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     */
    protected $accession;
    
    /**
     * Accession create date
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    protected $date;
    
    //Accession belongs to exactly one Specimen => Accession has only one Specimen
    /**
     * @ORM\ManyToOne(targetEntity="Specimen", inversedBy="accession")
     * @ORM\JoinColumn(name="specimen_id", referencedColumnName="id")
     */
    protected $specimen;
    
    /**
     * Accession might have many parts
     * @ORM\OneToMany(targetEntity="Part", mappedBy="accession")
     */
    protected $part;
    
     /**
     * Accession might have many parts
     * @ORM\OneToMany(targetEntity="Block", mappedBy="accession")
     */
    protected $block;
    
     /**
     * Accession might have many slide s
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="accession")
     */
    protected $slide;

    public function __construct() {
        $this->part = new ArrayCollection();
        $this->block = new ArrayCollection();
        $this->slide = new ArrayCollection();
    }
    
    
    
    public function __toString()
    {
        return $this->accession;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set accession
     *
     * @param string $accession
     * @return Accession
     */
    public function setAccession($accession)
    {
        $this->accession = $accession;
    
        return $this;
    }

    /**
     * Get accession
     *
     * @return string 
     */
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Accession
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set specimen
     *
     * @param \Oleg\OrderformBundle\Entity\Specimen $specimen
     * @return Accession
     */
    public function setSpecimen(\Oleg\OrderformBundle\Entity\Specimen $specimen = null)
    {
        $this->specimen = $specimen;
    
        return $this;
    }

    /**
     * Get specimen
     *
     * @return \Oleg\OrderformBundle\Entity\Specimen 
     */
    public function getSpecimen()
    {
        return $this->specimen;
    }

    /**
     * Add part
     *
     * @param \Oleg\OrderformBundle\Entity\Part $part
     * @return Accession
     */
    public function addPart(\Oleg\OrderformBundle\Entity\Part $part)
    {
        $this->part[] = $part;
    
        return $this;
    }

    /**
     * Remove part
     *
     * @param \Oleg\OrderformBundle\Entity\Part $part
     */
    public function removePart(\Oleg\OrderformBundle\Entity\Part $part)
    {
        $this->part->removeElement($part);
    }

    /**
     * Get part
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * Add block
     *
     * @param \Oleg\OrderformBundle\Entity\Block $block
     * @return Accession
     */
    public function addBlock(\Oleg\OrderformBundle\Entity\Block $block)
    {
        $this->block[] = $block;
    
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

    /**
     * Add slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return Accession
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        $this->slide[] = $slide;
    
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
}