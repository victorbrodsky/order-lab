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

namespace App\VacReqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


/**
 * @ORM\MappedSuperclass
 */
class VacReqRequestBase
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $numberOfDays;

//    /**
//     * @ORM\Column(type="date", nullable=true)
//     */
//    protected $firstDayBackInOffice;

    /**
     * status: pending, approved, rejected
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $approverComment;



    public function __construct($status='pending') { //='pending'
        $this->setStatus($status);
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
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
    public function getEndDate()
    {
        return $this->endDate;
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
    public function getNumberOfDays()
    {
        return $this->numberOfDays;
    }

    /**
     * @param mixed $numberOfDays
     */
    public function setNumberOfDays($numberOfDays)
    {
        $this->numberOfDays = $numberOfDays;
    }

//    /**
//     * @return mixed
//     */
//    public function getFirstDayBackInOffice()
//    {
//        return $this->firstDayBackInOffice;
//    }
//
//    /**
//     * @param mixed $firstDayBackInOffice
//     */
//    public function setFirstDayBackInOffice($firstDayBackInOffice)
//    {
//        $this->firstDayBackInOffice = $firstDayBackInOffice;
//    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getApproverComment()
    {
        return $this->approverComment;
    }

    /**
     * @param mixed $approverComment
     */
    public function setApproverComment($approverComment)
    {
        $this->approverComment = $approverComment;
    }


    public function getArrayFields() {
        $fieldsArr = array(
            'startDate','endDate','numberOfDays',
            'status','approverComment',
        );
        return $fieldsArr;
    }

}