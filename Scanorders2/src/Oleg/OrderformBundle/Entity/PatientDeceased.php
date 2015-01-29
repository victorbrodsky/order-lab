<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientDeceased")
 */
class PatientDeceased extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="race")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=true)
     */
    protected $patient;


    /**
     * @ORM\Column(type="boolean")
     */
    private $deceased;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $deathdate;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $deathtime;




    /**
     * @param mixed $deathdate
     */
    public function setDeathdate($deathdate)
    {
        $this->deathdate = $deathdate;
    }

    /**
     * @return mixed
     */
    public function getDeathdate()
    {
        return $this->deathdate;
    }

    /**
     * @param mixed $deathtime
     */
    public function setDeathtime($deathtime)
    {
        $this->deathtime = $deathtime;
    }

    /**
     * @return mixed
     */
    public function getDeathtime()
    {
        return $this->deathtime;
    }

    /**
     * @param mixed $deceased
     */
    public function setDeceased($deceased)
    {
        $this->deceased = $deceased;
    }

    /**
     * @return mixed
     */
    public function getDeceased()
    {
        return $this->deceased;
    }



}