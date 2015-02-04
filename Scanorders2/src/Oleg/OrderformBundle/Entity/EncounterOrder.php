<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterOrder")
 */
class EncounterOrder extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="order")
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", nullable=true)
     */
    protected $encounter;


    /**
     * @ORM\ManyToOne(targetEntity="GeneralOrder")
     * @ORM\JoinColumn(name="generalorder_id", referencedColumnName="id", nullable=true)
     */
    protected $field;



}