<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oleg\OrderformBundle\Entity\ArrayFieldAbstract;

/**
 * @ORM\MappedSuperclass
 */
abstract class PatientArrayFieldAbstract extends ArrayFieldAbstract {


    /**
     * @ORM\ManyToOne(targetEntity="Patient",cascade={"persist"})
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=true)
     */
    protected $patient;

    /**
     * @ORM\Column(type="string")
     */
    protected $field;

    /**
     * Set patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     * @return PatientArrayFieldAbstract
     */
    public function setPatient(\Oleg\OrderformBundle\Entity\Patient $patient = null)
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * Get patient
     *
     * @return \Oleg\OrderformBundle\Entity\Patient
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }


}