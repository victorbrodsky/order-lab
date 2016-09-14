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



//    public function setField($field=null)
//    {
//        //echo "setField old=".$this->field."; new=".$field->format('Y-m-d')."<br>";
//        if( $field != $this->field ) {
//            $class = new \ReflectionClass($this);
//            $className = $class->getShortName();
//            $this->changeArr[$className]['field']['old'] = $this->field;
//            $this->changeArr[$className]['field']['new'] = $field;
//        }
//
//        $this->field = $field;
//    }


    public function __toString() {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($this->field);
        return $dateStr;
    }

}