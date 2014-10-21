<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedureList",
 *  indexes={
 *      @ORM\Index( name="procedure_name_idx", columns={"name"} )
 *  }
 * )
 */
class ProcedureList extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="ProcedureList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ProcedureList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="ProcedureName", mappedBy="field")
     */
    protected $procedure;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->procedure = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\ProcedureList $synonyms
     * @return ProcedureList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\ProcedureList $synonyms)
    {
        $this->synonyms->add($synonyms);
    
        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\ProcedureList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\ProcedureList $synonyms)
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
     * @param \Oleg\OrderformBundle\Entity\ProcedureList $original
     * @return ProcedureList
     */
    public function setOriginal(\Oleg\OrderformBundle\Entity\ProcedureList $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    /**
     * Get original
     *
     * @return \Oleg\OrderformBundle\Entity\ProcedureList 
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Add procedure
     *
     * @param \Oleg\OrderformBundle\Entity\Procedure $procedure
     * @return ProcedureList
     */
    public function addProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {
        $this->procedure->add($procedure);
    
        return $this;
    }

    /**
     * Remove procedure
     *
     * @param \Oleg\OrderformBundle\Entity\Procedure $procedure
     */
    public function removeProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {
        $this->procedure->removeElement($procedure);
    }

    /**
     * Get procedure
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProcedure()
    {
        return $this->procedure;
    }


}