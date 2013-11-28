<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="partList")
 */
class PartList
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
     * @ORM\OneToMany(targetEntity="PartList", mappedBy="original")
     **/
    private $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PartList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    private $original;

    //orphanRemoval=true
    /**
     * @ORM\OneToMany(targetEntity="PartPartname", mappedBy="field", orphanRemoval=true)
     */
    protected $part;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->part = new ArrayCollection();
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

    public function addSynonym(\Oleg\OrderformBundle\Entity\PartPartname $synonyms)
    {
        $this->synonyms->add($synonyms);
    
        return $this;
    }

    public function removeSynonym(\Oleg\OrderformBundle\Entity\PartPartname $synonyms)
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


    public function setOriginal(\Oleg\OrderformBundle\Entity\PartPartname $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    public function getOriginal()
    {
        return $this->original;
    }

    public function __toString()
    {
        //$res = "id=".$this->id.", name=".$this->name.", synonymCount=".count($this->synonyms);
        $res = $this->name."";
        return $res;
    }


    public function addPart(\Oleg\OrderformBundle\Entity\PartPartname $part)
    {
        $this->part->add($part);
    
        return $this;
    }

    public function removePart(\Oleg\OrderformBundle\Entity\PartPartname $part)
    {
        $this->part->removeElement($part);
    }

    public function getPart()
    {
        return $this->part;
    }
}