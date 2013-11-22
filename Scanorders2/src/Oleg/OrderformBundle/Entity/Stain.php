<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\StainRepository")
 * @ORM\Table(name="stain")
 */
class Stain extends SlideArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Slide", inversedBy="stain")
     * @ORM\JoinColumn(name="slide_id", referencedColumnName="id")
     */
    protected $slide;

    /**
     * @ORM\ManyToOne(targetEntity="StainList", inversedBy="stain", cascade={"persist"})
     * @ORM\JoinColumn(name="stainlist_id", referencedColumnName="id", nullable=true)
     */
    protected $field;

    /**
     * @ORM\Column(type="string", nullable=true, length=200)
     */
    protected $stainer;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;

    /**
     * Set stainer
     *
     * @param string $stainer
     * @return Stain
     */
    public function setStainer($stainer)
    {
        $this->stainer = $stainer;
    
        return $this;
    }

    /**
     * Get stainer
     *
     * @return string 
     */
    public function getStainer()
    {
        return $this->stainer;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Stain
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
    
}