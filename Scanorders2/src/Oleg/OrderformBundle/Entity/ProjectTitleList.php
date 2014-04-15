<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ProjectTitleList")
 */
class ProjectTitleList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ProjectTitleList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ProjectTitleList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="Research", mappedBy="projectTitle")
     */
    protected $research;

    //list of set titles belongs to this project title.
    /**
     * @ORM\OneToMany(targetEntity="SetTitleList", mappedBy="projectTitle", cascade={"persist"})
     */
    protected $setTitles;


    public function __construct() {
        $this->research = new ArrayCollection();
        $this->setTitles = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
    }

    public function addResearch(\Oleg\OrderformBundle\Entity\Research $research)
    {
        if( !$this->research->contains($research) ) {
            $this->research->add($research);
        }
        return $this;
    }

    public function removeResearch(\Oleg\OrderformBundle\Entity\Research $research)
    {
        $this->research->removeElement($research);
    }

    public function getResearch()
    {
        return $this->research;
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\ProjectTitleList $synonyms
     * @return ProjectTitleList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\ProjectTitleList $synonyms)
    {
        $this->synonyms->add($synonyms);

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\ProjectTitleList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\ProjectTitleList $synonyms)
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


    public function addSetTitles(\Oleg\OrderformBundle\Entity\SetTitleList $setTitle)
    {
        if( !$this->setTitles->contains($setTitle) ) {
            $this->setTitles->add($setTitle);
            //$setTitle->setProjectTitle($this);
        }
        return $this;
    }

    public function removeSetTitles(\Oleg\OrderformBundle\Entity\SetTitleList $setTitle)
    {
        $this->setTitles->removeElement($setTitle);
    }

    /**
     * @return mixed
     */
    public function getSetTitles()
    {
        return $this->setTitles;
    }

    public function setSetTitles( $settitle )
    {
        if( $settitle ) {
            $this->addSetTitles($settitle);
            //$settitle->setProjectTitle($this);
        } else {
            $this->setTitles = new ArrayCollection();
        }
        return $this;
    }



}