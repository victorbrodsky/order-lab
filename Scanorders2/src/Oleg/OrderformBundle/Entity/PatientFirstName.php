<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientfirstname",
 *  indexes={
 *      @ORM\Index( name="patientfirstname_field_idx", columns={"field"} )
 *  }
 * )
 */
class PatientFirstName extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="firstname")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $patient;

    /**
     * Last Name
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;



}