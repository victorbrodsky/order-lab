<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientdob")
 */
class PatientDob extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="dob")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $patient;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $field;



    public function setField($field=null)
    {
        $this->setFieldChangeArray("field",$this->formatDataToString($this->field),$this->formatDataToString($field));
        $this->field = $field;
    }


    public function __toString() {
        return $this->formatDataToString($this->field);
    }

    public function formatDataToString($data) {
        $transformer = new DateTimeToStringTransformer(null,null,'Y-m-d');
        $dateStr = $transformer->transform($data);
        return $dateStr;
    }

}