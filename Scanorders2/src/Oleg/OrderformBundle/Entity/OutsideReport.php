<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_outsideReport")
 */
class OutsideReport {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    //Outside Report Order contains: Lab Order ID Source, Lab Order ID
    /**
     * @ORM\ManyToOne(targetEntity="GeneralOrder")
     * @ORM\JoinColumn(name="generalorder_id", referencedColumnName="id", nullable=true)
     */
    private $order;

    //Outside Report Source
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true)
     */
    private $source;

    //Outside Report Type
    /**
     * @ORM\ManyToOne(targetEntity="OutsideReportTypeList", cascade={"persist"})
     * @ORM\JoinColumn(name="outsideReportType", referencedColumnName="id")
     */
    private $outsideReportType;

    //Outside Report Text: [plain text multi-line field]
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $outsideReportText;

    //Outside Report Reference Representation (PDF) (actually a set of 5 fields - links to the Images table where Autopsy Images are)
    //TODO: 5 fields ???

    //Outside Report Pathologist(s): [Select2, can add new value, separate list in List Manager]
    //TODO: create a new user

    //Outside Report Issued On (Date & Time)
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $issuedonDate;
    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $issuedonTime;


    //Outside Report Received On (Date & TIme)
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $receivedonDate;
    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $receivedonTime;

    //Attach "Progress & Comments" page to the Outside Report Order
    //TODO: implement this

    //Outside Report Location
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
     */
    private $location;




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
     * @param mixed $issuedonDate
     */
    public function setIssuedonDate($issuedonDate)
    {
        $this->issuedonDate = $issuedonDate;
    }

    /**
     * @return mixed
     */
    public function getIssuedonDate()
    {
        return $this->issuedonDate;
    }

    /**
     * @param mixed $issuedonTime
     */
    public function setIssuedonTime($issuedonTime)
    {
        $this->issuedonTime = $issuedonTime;
    }

    /**
     * @return mixed
     */
    public function getIssuedonTime()
    {
        return $this->issuedonTime;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $outsideReportText
     */
    public function setOutsideReportText($outsideReportText)
    {
        $this->outsideReportText = $outsideReportText;
    }

    /**
     * @return mixed
     */
    public function getOutsideReportText()
    {
        return $this->outsideReportText;
    }

    /**
     * @param mixed $outsideReportType
     */
    public function setOutsideReportType($outsideReportType)
    {
        $this->outsideReportType = $outsideReportType;
    }

    /**
     * @return mixed
     */
    public function getOutsideReportType()
    {
        return $this->outsideReportType;
    }

    /**
     * @param mixed $receivedonDate
     */
    public function setReceivedonDate($receivedonDate)
    {
        $this->receivedonDate = $receivedonDate;
    }

    /**
     * @return mixed
     */
    public function getReceivedonDate()
    {
        return $this->receivedonDate;
    }

    /**
     * @param mixed $receivedonTime
     */
    public function setReceivedonTime($receivedonTime)
    {
        $this->receivedonTime = $receivedonTime;
    }

    /**
     * @return mixed
     */
    public function getReceivedonTime()
    {
        return $this->receivedonTime;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }





}