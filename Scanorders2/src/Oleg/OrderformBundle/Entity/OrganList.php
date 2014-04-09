<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="organlist")
 */
class OrganList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="OrganList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

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