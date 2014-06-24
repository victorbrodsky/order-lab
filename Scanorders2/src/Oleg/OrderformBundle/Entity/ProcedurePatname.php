<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="procedurePatname")
 */
class ProcedurePatname extends ProcedureArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="patname", cascade={"persist"})
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $procedure;

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