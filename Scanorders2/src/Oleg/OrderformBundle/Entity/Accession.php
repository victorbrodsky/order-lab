<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
//@UniqueEntity({"accession"})
         
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
     * Accession string i.e. S12-99998. Must be unique.
     * @ORM\Column(type="string", length=100, unique=true)
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
    
    ///////////////////////////////////////////
    
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
     * Accession might have many slides
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="accession", cascade={"persist"})
     */
    protected $slide;
      
    public function __construct() {
        $this->part = new ArrayCollection(); 
        $this->slide = new ArrayCollection();
    }
      
    public function __toString()
    {
//        $part_info = "(";
//        $count = 0;
//        foreach( $this->part as $part ) {
//            //$patient_info .= 'id='.$patient->getId().", mrn=".$patient->getMrn(). "; ";
//            $part_info .= $count.":" . $part. "; ";
//            $count++;
//        }
//        $part_info .= ")";
//        return "Accession: id=".$this->id.", accession#".$this->accession.", partCount=".count($this->part)." (".$part_info.")<br>";
        return "Accession: id=".$this->id.", accession#".$this->accession.", partCount=".count($this->part)."<br>";
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
        if( !$this->part->contains($part) ) {
            $part->setAccession($this);
            $this->part[] = $part;
        }

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
    public function setPart(\Doctrine\Common\Collections\ArrayCollection $part)
    {
        $this->part = $part;
    }


    /**
     * Add slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return Accession
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slide->contains($slide) ) {
            $slide->setAccession($this);
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

    public function clearPart(){
        foreach( $this->part as $thispart ) {
            $this->removePart($thispart);
        }
    }

}