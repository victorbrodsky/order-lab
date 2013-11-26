<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="slidetype")
 */
class SlideType
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=500)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="slidetype")
     */
    protected $slide;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     */
    protected $creator;

    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime")
     * @Assert\NotBlank
     */
    protected $createdate;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return SlideType
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return SlideType
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set creator
     *
     * @param string $creator
     * @return SlideType
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    
        return $this;
    }

    /**
     * Get creator
     *
     * @return string 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set createdate
     *
     * @param \DateTime $createdate
     * @return SlideType
     */
    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;
    
        return $this;
    }

    /**
     * Get createdate
     *
     * @return \DateTime 
     */
    public function getCreatedate()
    {
        return $this->createdate;
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

    public function __toString()
    {
        $res = $this->name;
        return $res;
    }
}