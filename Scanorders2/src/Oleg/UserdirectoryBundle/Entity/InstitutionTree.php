<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//TODO: remove this entity? Why do I need it? For logger?
/**
 * @ORM\Entity
 * @ORM\Table(name="user_institutionTree")
 */
class InstitutionTree
{

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Logger", inversedBy="institutionTrees")
     * @ORM\JoinColumn(name="logger_id", referencedColumnName="id")
     **/
    private $logger;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Institution",cascade={"persist"})
     */
    private $institution;

//    /**
//     * @ORM\ManyToOne(targetEntity="Department",cascade={"persist"})
//     */
//    private $department;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="Division",cascade={"persist"})
//     */
//    private $division;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="Service",cascade={"persist"})
//     */
//    private $service;


    function __construct($type=null)
    {
        $this->type = $type;
    }



//    /**
//     * @param mixed $department
//     */
//    public function setDepartment($department)
//    {
//        $this->department = $department;
//
//        //set parent
//        $this->setInstitution($department->getParent());
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDepartment()
//    {
//        return $this->department;
//    }
//
//    /**
//     * @param mixed $division
//     */
//    public function setDivision($division)
//    {
//        $this->division = $division;
//
//        //set parent
//        $this->setDepartment($division->getParent());
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDivision()
//    {
//        return $this->division;
//    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

//    /**
//     * @param mixed $service
//     */
//    public function setService($service)
//    {
//        $this->service = $service;
//
//        //set parent
//        $this->setDivision($service->getParent());
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getService()
//    {
//        return $this->service;
//    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }




}