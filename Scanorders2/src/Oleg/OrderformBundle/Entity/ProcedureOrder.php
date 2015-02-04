<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedureOrder")
 */
class ProcedureOrder extends ProcedureArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="order")
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", nullable=true)
     */
    protected $procedure;


    /**
     * @ORM\ManyToOne(targetEntity="GeneralOrder")
     * @ORM\JoinColumn(name="generalorder_id", referencedColumnName="id", nullable=true)
     */
    protected $field;



}