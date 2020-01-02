<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/11/2016
 * Time: 11:35 AM
 */

namespace Oleg\VacReqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

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
     * @ORM\Column(type="string", nullable=true)
     */
    private $expenses;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $paidByOutsideOrganization;




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


    public function getArrayFields() {
        $fieldsArr = parent::getArrayFields();
        $fieldsArr[] = 'expenses';
        $fieldsArr[] = 'paidByOutsideOrganization';
        $fieldsArr[] = 'description';
        return $fieldsArr;
    }


    public function __toString()
    {
        //$break = "\r\n";
        $break = "<br>";
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');

        $res = "### Business Travel Request ###".$break;
        $res .= "Status: ".$this->getStatus().$break;
        $res .= "Business Travel - First Day Away: ".$transformer->transform($this->getStartDate()).$break;
        $res .= "Business Travel - Last Day Away: ".$transformer->transform($this->getEndDate()).$break;
        $res .= "Number of Work Days Off-site: ".$this->getNumberOfDays().$break;
        //$res .= "First Day Back in Office: ".$transformer->transform($this->getFirstDayBackInOffice()).$break;

        if( $this->getPaidByOutsideOrganization() ) {
            $paidOutside = "yes";
        } else {
            $paidOutside = "no";
        }
        $res .= "Paid by Outside Organization: ".$paidOutside.$break;

        $res .= "Estimated Expenses: ".$this->getExpenses().$break;

        $res .= "Description: ".$this->getDescription().$break;

        if( $this->getApproverComment() ) {
            $res .= "Approver Comment: ".$this->getApproverComment().$break;
        }

        return $res;
    }

}