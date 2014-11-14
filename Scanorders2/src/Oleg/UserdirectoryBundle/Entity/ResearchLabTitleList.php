<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_researchLabTitleList")
 */
class ResearchLabTitleList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ResearchLabTitleList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ResearchLabTitleList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    /**
     * @ORM\OneToMany(targetEntity="ResearchLab", mappedBy="researchLabTitle")
     **/
    private $researchlab;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->researchlab = new ArrayCollection();
    }



   


    /**
     * @return mixed
     */
    public function getResearchlab()
    {
        return $this->researchlab;
    }
    public function addResearchlab(\Oleg\UserdirectoryBundle\Entity\ResearchLab $researchlab)
    {
        if( !$this->researchlab->contains($researchlab) ) {
            $this->researchlab->add($researchlab);
            $researchlab->setResearchLabTitle($this);
        }

        return $this;
    }
    public function removeResearchlab(\Oleg\UserdirectoryBundle\Entity\ResearchLab $researchlab)
    {
        $this->researchlab->removeElement($researchlab);
    }



}