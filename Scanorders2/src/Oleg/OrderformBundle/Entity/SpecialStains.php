<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\SpecialStainsRepository")
 * @ORM\Table(name="specialStains")
 */
class SpecialStains
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Slide", inversedBy="specialStains")
     * @ORM\JoinColumn(name="slide_id", referencedColumnName="id", nullable=true)
     */
    protected $slide;
   
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
     * Set Slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return Block
     */
    public function setSlide(\Oleg\OrderformBundle\Entity\Slide $slide = null)
    {
        $this->slide = $slide;
    
        return $this;
    }

    /**
     * Get Slide
     *
     * @return \Oleg\OrderformBundle\Entity\Slide
     */
    public function getSlide()
    {
        return $this->slide;
    }

    public function __toString()
    {
        return $this->name;
    }

}