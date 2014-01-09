<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="slidetype")
 */
class SlideType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="slidetype")
     */
    protected $slide;

    /**
     * @ORM\OneToMany(targetEntity="SlideType", mappedBy="original")
     **/
    private $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="SlideType", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    private $original;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slide = new \Doctrine\Common\Collections\ArrayCollection();
        $this->synonyms = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return SlideType
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

    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\SlideType $synonyms
     * @return SlideType
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\SlideType $synonyms)
    {
        $this->synonyms[] = $synonyms;
    
        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\SlideType $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\SlideType $synonyms)
    {
        $this->synonyms->removeElement($synonyms);
    }

    /**
     * Get synonyms
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * Set original
     *
     * @param \Oleg\OrderformBundle\Entity\SlideType $original
     * @return SlideType
     */
    public function setOriginal(\Oleg\OrderformBundle\Entity\SlideType $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    /**
     * Get original
     *
     * @return \Oleg\OrderformBundle\Entity\SlideType 
     */
    public function getOriginal()
    {
        return $this->original;
    }

}