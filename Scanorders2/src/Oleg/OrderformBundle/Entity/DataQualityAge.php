<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="dataquality_age")
 */
class DataQualityAge extends DataQuality
{

    /**
     * @ORM\ManyToOne(targetEntity="OrderInfo")
     * @ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $orderinfo;

    /**
     * @ORM\ManyToOne(targetEntity="Procedure")
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $procedure;

    /**
     * @ORM\ManyToOne(targetEntity="Patient")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $patient;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $procedureage;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $patientage;




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
     * @param mixed $patient
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
    }

    /**
     * @return mixed
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param mixed $patientage
     */
    public function setPatientage($patientage)
    {
        $this->patientage = $patientage;
    }

    /**
     * @return mixed
     */
    public function getPatientage()
    {
        return $this->patientage;
    }

    /**
     * @param mixed $procedure
     */
    public function setProcedure($procedure)
    {
        $this->procedure = $procedure;
    }

    /**
     * @return mixed
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * @param mixed $procedureage
     */
    public function setProcedureage($procedureage)
    {
        $this->procedureage = $procedureage;
    }

    /**
     * @return mixed
     */
    public function getProcedureage()
    {
        return $this->procedureage;
    }





}