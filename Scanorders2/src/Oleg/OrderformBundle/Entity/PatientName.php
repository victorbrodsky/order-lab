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
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $patient;

    /**
     * Last Name
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * First Name
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstName;

    /**
     * Middle Name
     * @ORM\Column(type="string", nullable=true)
     */
    protected $middleName;

    /**
     * Link to the object, the source of field data
     * @ORM\OneToOne(targetEntity="Procedure")
     */
    protected $procedure;

    /**
     * @param mixed $procedure
     */
    public function setProcedure($procedure)
    {
        $this->procedure = $procedure;
    }

    /**
     * @return mixed
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $middleName
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    }

    /**
     * @return mixed
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }


}