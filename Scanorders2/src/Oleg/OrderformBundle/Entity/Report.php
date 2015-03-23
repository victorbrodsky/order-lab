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




    //Outside Report Reference Representation (PDF) (actually a set of 5 fields - links to the Images table where Autopsy Images are)
    /**
     * ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\DocumentContainer", cascade={"persist","remove"})
     **/
    private $documentContainer;

    //Outside Report Pathologist(s) - Signing Pathologist(s): [Select2, can add new value, separate list in List Manager]
    //Should be able to create a new user
    //Pathologist names are entered. Use a wrapper because report can have multiple Pathologists
    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper")
     * @ORM\JoinTable(name="scan_reports_signingPathologists",
     *      joinColumns={@ORM\JoinColumn(name="report_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathologist_id", referencedColumnName="id")}
     *      )
     **/
    private $signingPathologists;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper")
     * @ORM\JoinTable(name="scan_reports_consultedPathologists",
     *      joinColumns={@ORM\JoinColumn(name="report_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathologist_id", referencedColumnName="id")}
     *      )
     **/
    private $consultedPathologists;

    //Outside Report Issued On (Date & Time)
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $issuedDate;

    //Outside Report Received On (Date & TIme)
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $receivedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $signatureDate;


    /**
     * @ORM\ManyToOne(targetEntity="ReportType", cascade={"persist"})
     */
    private $reportType;

    //report full text must be associated with document image.
    //So, report full text is array of comments in documentContainer (documentContainer->comments(DocumentComment)->comment)


    public function __construct() {
        $this->signingPathologists = new ArrayCollection();
        $this->consultedPathologists = new ArrayCollection();
    }




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
     * @param mixed $documentContainer
     */
    public function setDocumentContainer($documentContainer)
    {
        $this->documentContainer = $documentContainer;
    }

    /**
     * @return mixed
     */
    public function getDocumentContainer()
    {
        return $this->documentContainer;
    }



    public function getSigningPathologists()
    {
        return $this->signingPathologists;
    }
    public function addSigningPathologist($item)
    {
        if( $item && !$this->signingPathologists->contains($item) ) {
            $this->signingPathologists->add($item);
        }
        return $this;
    }
    public function removeSigningPathologist($item)
    {
        $this->signingPathologists->removeElement($item);
    }

    public function getConsultedPathologists()
    {
        return $this->consultedPathologists;
    }
    public function addConsultedPathologist($item)
    {
        if( $item && !$this->consultedPathologists->contains($item) ) {
            $this->consultedPathologists->add($item);
        }
        return $this;
    }
    public function removeConsultedPathologist($item)
    {
        $this->consultedPathologists->removeElement($item);
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





}