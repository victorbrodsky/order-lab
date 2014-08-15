<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="codeNYPH")
 */
class CodeNYPH extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="CodeNYPH", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="CodeNYPH", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    public function __construct() {
        $this->synonyms = new ArrayCollection();
    }


    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\CodeNYPH $synonyms
     * @return CodeNYPH
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\CodeNYPH $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\CodeNYPH $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\CodeNYPH $synonyms)
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