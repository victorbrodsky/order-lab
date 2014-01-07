<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="patientmrn")
 */
class PatientMrn extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="mrn", cascade={"persist"})
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $patient;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="MrnType", inversedBy="patientmrn", cascade={"persist"})
     * @ORM\JoinColumn(name="mrntype_id", referencedColumnName="id", nullable=true)
     */
    protected $mrntype;

    /**
     * @param mixed $mrntype
     */
    public function setMrntype($mrntype)
    {
        $this->mrntype = $mrntype;
    }

    /**
     * @return mixed
     */
    public function getMrntype()
    {
        return $this->mrntype;
    }


    public function obtainExtraKey()
    {
        $extra = array();
        $extra['mrntype'] = $this->getMrntype()->getId();
        return $extra;
    }

    public function setExtra($extraEntity)
    {
        $this->setMrntype($extraEntity);
    }

}