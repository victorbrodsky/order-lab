<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedureLocation")
 */
class ProcedureLocation extends ProcedureArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="location")
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", nullable=true)
     */
    protected $procedure;


    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
     */
    protected $field;



}