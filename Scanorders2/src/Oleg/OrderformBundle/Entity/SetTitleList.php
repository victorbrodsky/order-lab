<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="SetTitleList")
 */
class SetTitleList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="SetTitleList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="SetTitleList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="Research", inversedBy="setTitles", cascade={"persist"})
     * @ORM\JoinColumn(name="research_id", referencedColumnName="id", nullable=true)
     */
    protected $research;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\SetTitleList $synonyms
     * @return SetTitleList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\SetTitleList $synonyms)
    {
        $this->synonyms->add($synonyms);

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\SetTitleList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\SetTitleList $synonyms)
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

    /**
     * @param mixed $research
     */
    public function setResearch($research)
    {
        $this->research = $research;
    }

    /**
     * @return mixed
     */
    public function getResearch()
    {
        return $this->research;
    }




}