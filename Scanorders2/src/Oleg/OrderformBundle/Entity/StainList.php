<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="stainlist")
 */
class StainList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="StainList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="StainList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="Stain", mappedBy="field")
     */
    protected $stain;

    /**
     * @ORM\OneToMany(targetEntity="BlockSpecialStains", mappedBy="staintype")
     */
    protected $specialstain;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->stain = new ArrayCollection();
        $this->specialstain = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\StainList $synonyms
     * @return StainList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\StainList $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }
    
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

//    public function setSynonyms(\Oleg\OrderformBundle\Entity\StainList $synonyms = null) {
//        $newsynonyms = new ArrayCollection();
//        if( $synonyms ) {
//            $newsynonyms->add($synonyms);
//            $this->synonyms = $newsynonyms;
//        } else {
//            $this->synonyms = $newsynonyms;
//        }
//        return $this;
//    }

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


    public function addSpecialstain(\Oleg\OrderformBundle\Entity\BlockSpecialStains $specialstain)
    {
        if( !$this->specialstain->contains($specialstain) ) {
            $this->specialstain->add($specialstain);
        }

        return $this;
    }

    public function removeSpecialstain(\Oleg\OrderformBundle\Entity\BlockSpecialStains $specialstain)
    {
        $this->specialstain->removeElement($specialstain);
    }

    public function getSpecialstain()
    {
        return $this->specialstain;
    }
}