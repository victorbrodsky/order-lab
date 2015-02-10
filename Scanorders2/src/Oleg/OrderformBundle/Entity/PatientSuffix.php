<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientSuffix")
 */
class PatientSuffix extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="suffix")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $patient;

    /**
     * Suffix
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;


}