<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientlastname",
 *  indexes={
 *      @ORM\Index( name="patientlastname_field_idx", columns={"field"} )
 *  }
 * )
 */
class PatientLastName extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="lastname")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $patient;

    /**
     * Last Name
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;


}