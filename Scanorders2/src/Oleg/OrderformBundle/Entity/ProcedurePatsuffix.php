<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedurePatsuffix")
 */
class ProcedurePatsuffix extends ProcedureArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="patsuffix", cascade={"persist"})
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $procedure;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

}