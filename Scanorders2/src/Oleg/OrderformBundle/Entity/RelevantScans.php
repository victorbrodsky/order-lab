<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="relevantScans")
 */
class RelevantScans
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
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="Slide", inversedBy="relevantScans")
     * @ORM\JoinColumn(name="slide_id", referencedColumnName="id", nullable=true)
     */
    protected $slide;
   
    public function getId() {
        return $this->id;
    }

    public function getField() {
        return $this->field;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setField($field) {
        $this->field = $field;
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
        return $this->field;
    }

}