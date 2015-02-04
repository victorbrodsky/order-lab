<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterName")
 */
class EncounterName extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="name", cascade={"persist"})
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $encounter;

     /**
     * @ORM\ManyToOne(targetEntity="EncounterList", inversedBy="encountername", cascade={"persist"})
     * @ORM\JoinColumn(name="encounterlist_id", referencedColumnName="id", nullable=true)
     */
    protected $field;

}