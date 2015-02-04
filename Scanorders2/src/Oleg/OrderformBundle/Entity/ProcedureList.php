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
    protected $procedurename;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->procedurename = new ArrayCollection();
    }   

    /**
     * Add procedurename
     *
     * @param \Oleg\OrderformBundle\Entity\ProcedureName $procedurename
     * @return ProcedureList
     */
    public function addProcedurename(\Oleg\OrderformBundle\Entity\ProcedureName $procedurename)
    {
        if( $procedurename && !$this->procedurename->contains($procedurename) ) {
            $this->procedurename->add($procedurename);
            $procedurename->setField($this);
        }
    
        return $this;
    }

    /**
     * Remove procedurename
     *
     * @param \Oleg\OrderformBundle\Entity\ProcedureName $procedurename
     */
    public function removeProcedurename(\Oleg\OrderformBundle\Entity\ProcedureName $procedurename)
    {
        $this->procedurename->removeElement($procedurename);
    }

    /**
     * Get procedurename
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProcedurename()
    {
        return $this->procedurename;
    }


}