<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterDate")
 */
class EncounterDate extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="date", cascade={"persist"})
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $encounter;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $field;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $time;



    public function __toString() {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        return $dateStr = $transformer->transform($this->field);
    }



    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }




}