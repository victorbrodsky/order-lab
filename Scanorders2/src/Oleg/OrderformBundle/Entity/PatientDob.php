<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


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
     * @ORM\Column(type="date", nullable=true)
     */
    protected $field;


    public function __toString() {
        //return $this->field;
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        return $dateStr = $transformer->transform($this->field);
    }

}