<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterType")
 */
class EncounterType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="EncounterType", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="EncounterType", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="EncounterNumber", mappedBy="keytype")
     */
    protected $encounternumber;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->encounternumber = new ArrayCollection();
    }



    public function addEncounternumber(\Oleg\OrderformBundle\Entity\EncounterNumber $encounternumber)
    {
        if( !$this->encounternumber->contains($encounternumber) ) {
            $this->encounternumber->add($encounternumber);
            $encounternumber->setKeytype($this);
        }
        return $this;
    }

    public function removeEncounternumber(\Oleg\OrderformBundle\Entity\EncounterNumber $encounternumber)
    {
        $this->encounternumber->removeElement($encounternumber);
    }

    public function getEncounternumber()
    {
        return $this->encounternumber;
    }


}