<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_employmentStatus")
 */
class EmploymentStatus extends BaseUserAttributes
{

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $hireDate;

    //Employee Type
    /**
     * @ORM\ManyToOne(targetEntity="EmploymentType")
     * @ORM\JoinColumn(name="employmentType_id", referencedColumnName="id", nullable=true)
     **/
    private $employmentType;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $terminationDate;

    /**
     * @ORM\ManyToOne(targetEntity="EmploymentTerminationType")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", nullable=true)
     **/
    private $terminationType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $terminationReason;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="employmentStatus")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;



    public function __construct($author) {
        parent::__construct($author);
        $this->setType(self::TYPE_PRIVATE);
        $this->setStatus(self::STATUS_VERIFIED);
    }

    /**
     * @param mixed $hireDate
     */
    public function setHireDate($hireDate)
    {
        $this->hireDate = $hireDate;
    }

    /**
     * @return mixed
     */
    public function getHireDate()
    {
        return $this->hireDate;
    }

    /**
     * @param mixed $terminationDate
     */
    public function setTerminationDate($terminationDate)
    {
        $this->terminationDate = $terminationDate;
    }

    /**
     * @return mixed
     */
    public function getTerminationDate()
    {
        return $this->terminationDate;
    }

    /**
     * @param mixed $terminationReason
     */
    public function setTerminationReason($terminationReason)
    {
        $this->terminationReason = $terminationReason;
    }

    /**
     * @return mixed
     */
    public function getTerminationReason()
    {
        return $this->terminationReason;
    }

    /**
     * @param mixed $terminationType
     */
    public function setTerminationType($terminationType)
    {
        $this->terminationType = $terminationType;
    }

    /**
     * @return mixed
     */
    public function getTerminationType()
    {
        return $this->terminationType;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $employmentType
     */
    public function setEmploymentType($employmentType)
    {
        $this->employmentType = $employmentType;
    }

    /**
     * @return mixed
     */
    public function getEmploymentType()
    {
        return $this->employmentType;
    }




    public function __toString() {
        return "Employment Status";
    }


}