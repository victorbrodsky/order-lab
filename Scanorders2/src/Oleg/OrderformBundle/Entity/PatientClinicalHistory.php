<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;

//@ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ClinicalHistoryRepository")

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientclinicalHistory")
 */
class PatientClinicalHistory extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="clinicalHistory")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $patient;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $field;

}