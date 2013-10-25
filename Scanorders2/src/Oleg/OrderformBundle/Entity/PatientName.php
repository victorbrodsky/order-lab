<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="patientname")
 */
class PatientName extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="name")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=true)
     */
    protected $patient;

    /**
     * @ORM\Column(type="string")
     */
    protected $field;

}