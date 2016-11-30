<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterInfoType")
 */
class EncounterInfoType extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="encounterInfoTypes", cascade={"persist"})
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $encounter;

    /**
     * @ORM\ManyToOne(targetEntity="EncounterInfoTypeList", cascade={"persist"})
     * @ORM\JoinColumn(name="encounterInfoType_id", referencedColumnName="id", nullable=true)
     */
    protected $field;

}