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

namespace App\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\MappedSuperclass
 */
abstract class ReportBase {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $signatureDate;

    /**
     * Report Issued On (Date & Time)
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $issuedDate;

    /**
     * Report Received On (Date & TIme)
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $receivedDate;

    /**
     * required by https://bitbucket.org/weillcornellpathology/scanorder/issue/389/new-field-arrays-was-20-in-issue-387; empty list and not used.
     * @ORM\ManyToOne(targetEntity="ReportType", cascade={"persist"})
     */
    protected $reportType;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $processedDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     */
    protected $processedByUser;


//    public function __construct() {
//        $this->signingPathologists = new ArrayCollection();
//        $this->consultedPathologists = new ArrayCollection();
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
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $issuedDate
     */
    public function setIssuedDate($issuedDate)
    {
        $this->issuedDate = $issuedDate;
    }

    /**
     * @return mixed
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
    }

    /**
     * @param mixed $receivedDate
     */
    public function setReceivedDate($receivedDate)
    {
        $this->receivedDate = $receivedDate;
    }

    /**
     * @return mixed
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    /**
     * @param mixed $signatureDate
     */
    public function setSignatureDate($signatureDate)
    {
        $this->signatureDate = $signatureDate;
    }

    /**
     * @return mixed
     */
    public function getSignatureDate()
    {
        return $this->signatureDate;
    }


    /**
     * @param mixed $reportType
     */
    public function setReportType($reportType)
    {
        $this->reportType = $reportType;
    }

    /**
     * @return mixed
     */
    public function getReportType()
    {
        return $this->reportType;
    }

    /**
     * @param mixed $processedDate
     */
    public function setProcessedDate($processedDate)
    {
        $this->processedDate = $processedDate;
    }

    /**
     * @return mixed
     */
    public function getProcessedDate()
    {
        return $this->processedDate;
    }

    /**
     * @param mixed $processedByUser
     */
    public function setProcessedByUser($processedByUser)
    {
        $this->processedByUser = $processedByUser;
    }

    /**
     * @return mixed
     */
    public function getProcessedByUser()
    {
        return $this->processedByUser;
    }




}