<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="slideReturnRequest")
 */
class SlideReturnRequest extends OrderAbstract {

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @var string
     * @ORM\Column(name="returnSlide", type="string", nullable=true)
     * @Assert\NotBlank
     */
    protected $returnSlide;

    /**
     * @var string
     * @ORM\Column(name="urgency", type="string", nullable=true)
     * @Assert\NotBlank
     */
    protected $urgency;

    /**
     * @ORM\ManyToMany(targetEntity="Slide")
     * @ORM\JoinTable(name="returnrequest_slide",
     *      joinColumns={@ORM\JoinColumn(name="slideReturnRequest_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="slide_id", referencedColumnName="id")}
     * )
     */
    private $slide;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slide = new ArrayCollection();
    }

    /**
     * Set returnSlide
     *
     * @param string $returnSlide
     * @return SlideReturnRequest
     */
    public function setReturnSlide($returnSlide)
    {
        $this->returnSlide = $returnSlide;

        return $this;
    }

    /**
     * Get returnSlide
     *
     * @return string
     */
    public function getReturnSlide()
    {
        return $this->returnSlide;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * Add slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return SlideReturnRequest
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slide->contains($slide) ) {
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
     * @return SlideReturnRequest
     */
    public function getSlide()
    {
        return $this->slide;
    }

    /**
     * Get slide
     * @param Doctrine\Common\Collections\Collection
     * @return SlideReturnRequest
     */
    public function setSlide( $slides)
    {
        $this->slide = $slides;
        return $this;
    }

    /**
     * @param string $urgency
     */
    public function setUrgency($urgency)
    {
        $this->urgency = $urgency;
    }

    /**
     * @return string
     */
    public function getUrgency()
    {
        return $this->urgency;
    }



}