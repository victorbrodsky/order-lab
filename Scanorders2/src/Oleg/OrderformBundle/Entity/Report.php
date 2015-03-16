<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_report")
 */
class Report {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="OrderInfo", mappedBy="report")
     **/
    private $orderinfo;


//    //Outside Report Source
//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
//     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true)
//     */
//    private $source;

//    //Outside Report Type
//    /**
//     * @ORM\ManyToOne(targetEntity="OutsideReportTypeList", cascade={"persist"})
//     * @ORM\JoinColumn(name="outsideReportType", referencedColumnName="id")
//     */
//    private $outsideReportType;

//    //Outside Report Text: [plain text multi-line field]
//    /**
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $outsideReportText;

    //Outside Report Reference Representation (PDF) (actually a set of 5 fields - links to the Images table where Autopsy Images are)
    //TODO: 5 fields ???

    //Outside Report Pathologist(s): [Select2, can add new value, separate list in List Manager]
    //TODO: create a new user

//    //Outside Report Issued On (Date & Time)
//    /**
//     * @ORM\Column(type="datetime", nullable=true)
//     */
//    private $issuedonDate;
//    /**
//     * @ORM\Column(type="time", nullable=true)
//     */
//    private $issuedonTime;


//    //Outside Report Received On (Date & TIme)
//    /**
//     * @ORM\Column(type="datetime", nullable=true)
//     */
//    private $receivedonDate;
//    /**
//     * @ORM\Column(type="time", nullable=true)
//     */
//    private $receivedonTime;

    //Attach "Progress & Comments" page to the Outside Report Order
    //TODO: implement this

//    //Outside Report Location
//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
//     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
//     */
//    private $location;




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
     * @param mixed $orderinfo
     */
    public function setOrderinfo($orderinfo)
    {
        $this->orderinfo = $orderinfo;
    }

    /**
     * @return mixed
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }







}