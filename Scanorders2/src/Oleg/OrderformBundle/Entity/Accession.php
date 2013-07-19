<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//Accession is a key for all other tables such as Patient, Case, Part, Block, Slide. 
//All of them have accession object 
/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\AccessionRepository")
 * @ORM\HasLifecycleCallbacks
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
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     */
    protected $accession;
    
    /**
     * Accession create date
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;
    
    /**
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="accession")
     */
    protected $slides;

    public function __construct() {
        $this->slides = new ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getAccession() {
        return $this->accession;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAccession($accession) {
        $this->accession = $accession;
    }
    
    public function getDate() {
        return $this->date;
    }

    /**
    * @ORM\PrePersist
    */
    public function setDate() {
        $this->date = new \DateTime();
    }
    

    /**
     * Add slides
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slides
     * @return Accession
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slides)
    {
        $this->slides[] = $slides;
    
        return $this;
    }

    /**
     * Remove slides
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slides
     */
    public function removeSlide(\Oleg\OrderformBundle\Entity\Slide $slides)
    {
        $this->slides->removeElement($slides);
    }

    /**
     * Get slides
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSlides()
    {
        return $this->slides;
    }
    
    public function __toString()
    {
        return $this->accession;
    }
}