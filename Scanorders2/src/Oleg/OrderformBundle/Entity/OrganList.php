<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="organlist")
 */
class OrganList
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
     * @ORM\OneToMany(targetEntity="OrganList", mappedBy="original")
     **/
    private $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    private $original;

    /**
     * @ORM\OneToMany(targetEntity="PartSourceOrgan", mappedBy="field")
     */
    protected $part;

//    /**
//     * @ORM\OneToMany(targetEntity="Part", mappedBy="primaryOrgan")
//     */
//    protected $partprimary;

    /**
     * @ORM\OneToMany(targetEntity="PartDiseaseType", mappedBy="primaryOrgan")
     */
    protected $partprimary;


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

    /**
     * Set name
     *
     * @param string $name
     * @return OrganList
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
     * @return OrganList
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
     * @return OrganList
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
     * @return OrganList
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
     * @param \Oleg\OrderformBundle\Entity\OrganList $synonyms
     * @return OrganList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\OrganList $synonyms)
    {
        $this->synonyms[] = $synonyms;
    
        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\OrganList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\OrganList $synonyms)
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
     * @param \Oleg\OrderformBundle\Entity\OrganList $original
     * @return OrganList
     */
    public function setOriginal(\Oleg\OrderformBundle\Entity\OrganList $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    /**
     * Get original
     *
     * @return \Oleg\OrderformBundle\Entity\OrganList 
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
     * Add part
     *
     * @param \Oleg\OrderformBundle\Entity\OrganList $part
     * @return OrganList
     */
    public function addPart(\Oleg\OrderformBundle\Entity\OrganList $part)
    {
        $this->part[] = $part;
    
        return $this;
    }

    /**
     * Remove part
     *
     * @param \Oleg\OrderformBundle\Entity\OrganList $part
     */
    public function removePart(\Oleg\OrderformBundle\Entity\OrganList $part)
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
}