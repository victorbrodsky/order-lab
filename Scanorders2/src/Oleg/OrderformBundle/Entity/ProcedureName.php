<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\AccessionArrayFieldAbstract;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ArrayFieldAbstractRepository")
 * @ORM\Table(name="procedurename")
 */
class ProcedureName extends ProcedureArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="procedure", cascade={"persist"})
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $procedure;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $field;

     /**
     * @ORM\ManyToOne(targetEntity="ProcedureList", inversedBy="procedure", cascade={"persist"})
     * @ORM\JoinColumn(name="procedurelist_id", referencedColumnName="id", nullable=true)
     */
    protected $field;

}