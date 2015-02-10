<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterPatfirstname")
 */
class EncounterPatfirstname extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="patfirstname", cascade={"persist"})
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $encounter;

    /**
     * Last Name
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $alias;





    /**
     * @param mixed $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }






}