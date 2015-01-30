<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedureEncounterorder")
 */
class ProcedureEncounterorder extends ProcedureArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="encounterorder")
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", nullable=true)
     */
    protected $procedure;

    //Encounter Order Source
    //$source field - already exists in base abstract class

    //Encounter Order ID
    //$orderinfo - already exists in base abstract class


}