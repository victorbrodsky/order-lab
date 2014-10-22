<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\MappedSuperclass
 */
class BaseTitle extends BaseUserAttributes
{

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * Primary, Secondary
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $priority;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $effort;

    /**
     * @ORM\ManyToOne(targetEntity="Institution",cascade={"persist"})
     */
    protected $institution;

    /**
     * @ORM\ManyToOne(targetEntity="Department",cascade={"persist"})
     */
    protected $department;

    /**
     * @ORM\ManyToOne(targetEntity="Division",cascade={"persist"})
     */
    protected $division;

    /**
     * @ORM\ManyToOne(targetEntity="Service",cascade={"persist"})
     */
    protected $service;

//    /**
//     * @ORM\ManyToOne(targetEntity="User")
//     * @ORM\JoinColumn(name="boss", referencedColumnName="id")
//     */
//    protected $boss;
//    /**
//     * @ORM\ManyToMany(targetEntity="User")
//     * @ORM\JoinTable(name="user_user_boss",
//     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="boss_id", referencedColumnName="id")}
//     * )
//     **/
//    protected $boss;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $pgystart;

    /**
     * @var \DateTime
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pgylevel;



    function __construct($author=null)
    {
        parent::__construct($author);
    }


    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param mixed $division
     */
    public function setDivision($division)
    {
        $this->division = $division;
    }

    /**
     * @return mixed
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $effort
     */
    public function setEffort($effort)
    {
        $this->effort = $effort;
    }

    /**
     * @return mixed
     */
    public function getEffort()
    {
        return $this->effort;
    }

    /**
     * @param \DateTime $pgylevel
     */
    public function setPgylevel($pgylevel)
    {
        $this->pgylevel = $pgylevel;
    }

    /**
     * @return \DateTime
     */
    public function getPgylevel()
    {
        return $this->pgylevel;
    }

    /**
     * @param \DateTime $pgystart
     */
    public function setPgystart($pgystart)
    {
        $this->pgystart = $pgystart;
    }

    /**
     * @return \DateTime
     */
    public function getPgystart()
    {
        return $this->pgystart;
    }

}