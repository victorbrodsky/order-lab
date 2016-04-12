<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/11/2016
 * Time: 11:35 AM
 */

namespace Oleg\VacReqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="vacreq_business")
 */
class VacReqRequestBusiness extends VacReqRequestBase
{

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $expenses;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $paidByOutsideOrganization;

//    /**
//     * @ORM\OneToOne(targetEntity="VacReqRequest", mappedBy="requestBusiness")
//     */
//    private $request;



    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getExpenses()
    {
        return $this->expenses;
    }

    /**
     * @param mixed $expenses
     */
    public function setExpenses($expenses)
    {
        $this->expenses = $expenses;
    }

    /**
     * @return mixed
     */
    public function getPaidByOutsideOrganization()
    {
        return $this->paidByOutsideOrganization;
    }

    /**
     * @param mixed $paidByOutsideOrganization
     */
    public function setPaidByOutsideOrganization($paidByOutsideOrganization)
    {
        $this->paidByOutsideOrganization = $paidByOutsideOrganization;
    }

//    /**
//     * @return mixed
//     */
//    public function getRequest()
//    {
//        return $this->request;
//    }
//
//    /**
//     * @param mixed $request
//     */
//    public function setRequest($request)
//    {
//        $this->request = $request;
//    }





}