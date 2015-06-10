<?php

namespace Oleg\OrderformBundle\Entity;

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
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
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