<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedureType")
 */
class ProcedureType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ProcedureType", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ProcedureType", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="ProcedureNumber", mappedBy="keytype")
     */
    protected $procedurenumber;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->procedurenumber = new ArrayCollection();
    }



    public function addProcedurenumber(\Oleg\OrderformBundle\Entity\ProcedureNumber $procedurenumber)
    {
        if( !$this->procedurenumber->contains($procedurenumber) ) {
            $this->procedurenumber->add($procedurenumber);
            $procedurenumber->setKeytype($this);
        }
        return $this;
    }

    public function removeProcedurenumber(\Oleg\OrderformBundle\Entity\ProcedureNumber $procedurenumber)
    {
        $this->procedurenumber->removeElement($procedurenumber);
    }

    public function getProcedurenumber()
    {
        return $this->procedurenumber;
    }


}