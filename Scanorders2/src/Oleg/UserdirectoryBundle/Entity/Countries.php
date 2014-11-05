<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_countries")
 */
class Countries extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="Countries", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Countries", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
    }


    /**
     * Add synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Countries $synonyms
     * @return Countries
     */
    public function addSynonym(\Oleg\UserdirectoryBundle\Entity\Countries $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Countries $synonyms
     */
    public function removeSynonym(\Oleg\UserdirectoryBundle\Entity\Countries $synonyms)
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
     * @param mixed $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

}