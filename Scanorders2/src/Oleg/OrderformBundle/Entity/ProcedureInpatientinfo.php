<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedureInpatientinfo")
 */
class ProcedureInpatientinfo extends ProcedureArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="inpatientinfo")
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", nullable=true)
     */
    protected $procedure;

    


}