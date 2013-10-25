<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="patientdob")
 */
class PatientDob extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="dob")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=true)
     */
    protected $patient;

    /**
     * @ORM\Column(type="date")
     */
    protected $field;


}