<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="encountertype")
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
     * @ORM\OneToMany(targetEntity="ProcedureEncounter", mappedBy="keytype")
     */
    protected $procedureencounter;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->procedureencounter = new ArrayCollection();
    }



    public function addProcedureencounter(\Oleg\OrderformBundle\Entity\ProcedureEncounter $procedureencounter)
    {
        if( !$this->procedureencounter->contains($procedureencounter) ) {
            $this->procedureencounter->add($procedureencounter);
        }
        return $this;
    }

    public function removeProcedureencounter(\Oleg\OrderformBundle\Entity\ProcedureEncounter $procedureencounter)
    {
        $this->procedureencounter->removeElement($procedureencounter);
    }

    public function getProcedureencounter()
    {
        return $this->procedureencounter;
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
     * @param mixed $synonyms
     */
    public function setSynonyms($synonyms)
    {
        $this->synonyms = $synonyms;
    }

    /**
     * @return mixed
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }


}