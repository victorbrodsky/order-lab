<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//(repositoryClass="Oleg\OrderformBundle\Repository\StainListRepository")
/**
 * @ORM\Entity
 * @ORM\Table(name="stainlist")
 */
class StainList
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
     * @ORM\OneToMany(targetEntity="StainList", mappedBy="original")
     **/
    private $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="StainList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    private $original;

    /**
     * @ORM\OneToMany(targetEntity="Stain", mappedBy="field")
     */
    protected $stain;

    /**
     * @ORM\OneToMany(targetEntity="SpecialStains", mappedBy="stain")
     */
    protected $specialstain;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->stain = new ArrayCollection();
        $this->specialstain = new ArrayCollection();
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
     * @return StainList
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
     * @return StainList
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
     * Set createdate
     *
     * @param \DateTime $createdate
     * @return StainList
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
     * Set creator
     *
     * @param string $creator
     * @return StainList
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
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\StainList $synonyms
     * @return StainList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\StainList $synonyms)
    {
        $this->synonyms[] = $synonyms;
    
        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\StainList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\StainList $synonyms)
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
     * @param \Oleg\OrderformBundle\Entity\StainList $original
     * @return StainList
     */
    public function setOriginal(\Oleg\OrderformBundle\Entity\StainList $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    /**
     * Get original
     *
     * @return \Oleg\OrderformBundle\Entity\StainList 
     */
    public function getOriginal()
    {
        return $this->original;
    }

    public function __toString()
    {
        //$res = "id=".$this->id.", name=".$this->name.", synonymCount=".count($this->synonyms);
        $res = $this->name;
        return $res;
    }


    


    /**
     * Add stain
     *
     * @param \Oleg\OrderformBundle\Entity\Stain $stain
     * @return StainList
     */
    public function addStain(\Oleg\OrderformBundle\Entity\Stain $stain)
    {
        if( !$this->stain->contains($stain) ) {
            $this->stain->add($stain);
        }
    
        return $this;
    }

    /**
     * Remove stain
     *
     * @param \Oleg\OrderformBundle\Entity\Stain $stain
     */
    public function removeStain(\Oleg\OrderformBundle\Entity\Stain $stain)
    {
        $this->stain->removeElement($stain);
    }

    /**
     * Get stain
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStain()
    {
        return $this->stain;
    }


    public function addSpecialstain(\Oleg\OrderformBundle\Entity\Specialstain $specialstain)
    {
        if( !$this->specialstain->contains($specialstain) ) {
            $this->specialstain->add($specialstain);
        }

        return $this;
    }

    public function removeSpecialstain(\Oleg\OrderformBundle\Entity\Specialstain $specialstain)
    {
        $this->specialstain->removeElement($specialstain);
    }

    public function getSpecialstain()
    {
        return $this->specialstain;
    }
}