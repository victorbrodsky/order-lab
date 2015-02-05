<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_dataquality_age")
 */
class DataQualityAge extends DataQuality
{

    /**
     * @ORM\ManyToOne(targetEntity="OrderInfo")
     * @ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $orderinfo;

    /**
     * @ORM\ManyToOne(targetEntity="Encounter")
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $encounter;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $encounterage;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $encounterdate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $patientdob;




    /**
     * @param mixed $orderinfo
     */
    public function setOrderinfo($orderinfo)
    {
        $this->orderinfo = $orderinfo;
    }

    /**
     * @return mixed
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }

    /**
     * @param mixed $encounter
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
    }

    /**
     * @return mixed
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * @param mixed $encounterage
     */
    public function setEncounterage($encounterage)
    {
        $this->encounterage = $encounterage;
    }

    /**
     * @return mixed
     */
    public function getEncounterage()
    {
        return $this->encounterage;
    }

    /**
     * @param mixed $encounterdate
     */
    public function setEncounterdate($encounterdate)
    {
        $this->encounterdate = $encounterdate;
    }

    /**
     * @return mixed
     */
    public function getEncounterdate()
    {
        return $this->encounterdate;
    }

    /**
     * @param mixed $patientdob
     */
    public function setPatientdob($patientdob)
    {
        $this->patientdob = $patientdob;
    }

    /**
     * @return mixed
     */
    public function getPatientdob()
    {
        return $this->patientdob;
    }


}