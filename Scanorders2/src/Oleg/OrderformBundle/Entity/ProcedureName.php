<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedurename")
 */
class ProcedureName extends ProcedureArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="name", cascade={"persist"})
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $procedure;

//     /**
//     * @ORM\ManyToOne(targetEntity="ProcedureList", inversedBy="procedurename", cascade={"persist"})
//     * @ORM\JoinColumn(name="procedurelist_id", referencedColumnName="id", nullable=true)
//     */

    /**
     * @ORM\ManyToOne(targetEntity="ProcedureList", cascade={"persist"})
     * @ORM\JoinColumn(name="procedurelist_id", referencedColumnName="id", nullable=true)
     */
    protected $field;

}