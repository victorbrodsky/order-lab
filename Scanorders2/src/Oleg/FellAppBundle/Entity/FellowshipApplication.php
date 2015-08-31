<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 8/24/15
 * Time: 11:08 AM
 */

namespace Oleg\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\BaseUserAttributes;


/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_fellowshipApplication")
 */
class FellowshipApplication extends BaseUserAttributes {

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User", inversedBy="fellowshipApplications", cascade={"remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\FellowshipSubspecialty", cascade={"persist"})
     */
    private $fellowshipSubspecialty;

    /**
     * This should be the link to WCMC's "Department of Pathology and Laboratory Medicine"
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document")
     * @ORM\JoinTable(name="fellapp_fellApp_coverLetter",
     *      joinColumns={@ORM\JoinColumn(name="fellApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="coverLetter_id", referencedColumnName="id", onDelete="CASCADE", unique=true)}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $coverLetters;


    //Reprimands
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $reprimand;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document")
     * @ORM\JoinTable(name="fellapp_fellApp_reprimandDocument",
     *      joinColumns={@ORM\JoinColumn(name="fellApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="reprimandDocument_id", referencedColumnName="id", onDelete="CASCADE", unique=true)}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $reprimandDocuments;

    //Lawsuits
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $lawsuit;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document")
     * @ORM\JoinTable(name="fellapp_fellApp_lawsuitDocument",
     *      joinColumns={@ORM\JoinColumn(name="fellApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="lawsuitDocument_id", referencedColumnName="id", onDelete="CASCADE", unique=true)}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $lawsuitDocuments;

    /**
     * Accession Number
     * @ORM\OneToMany(targetEntity="Reference", mappedBy="fellapp", cascade={"persist"})
     */
    private $references;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $honors;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $publications;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $memberships;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $signatureName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $signatureDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $interviewScore;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $applicationStatus;

    /**
     * timestamp when google form is opened
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $timestamp;


    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document")
     * @ORM\JoinTable(name="fellapp_fellApp_report",
     *      joinColumns={@ORM\JoinColumn(name="fellApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE", unique=true)}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $reports;


    public function __construct($author=null) {
        parent::__construct($author);

        $this->coverLetters = new ArrayCollection();
        $this->reprimandDocuments = new ArrayCollection();
        $this->lawsuitDocuments = new ArrayCollection();
        $this->references = new ArrayCollection();
        $this->reports = new ArrayCollection();

        $this->setApplicationStatus('active');
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
     * @param mixed $fellowshipSubspecialty
     */
    public function setFellowshipSubspecialty($fellowshipSubspecialty)
    {
        $this->fellowshipSubspecialty = $fellowshipSubspecialty;
    }

    /**
     * @return mixed
     */
    public function getFellowshipSubspecialty()
    {
        return $this->fellowshipSubspecialty;
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
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }



    public function addCoverLetter($item)
    {
        if( $item && !$this->coverLetters->contains($item) ) {
            $this->coverLetters->add($item);
        }
        return $this;
    }
    public function removeCoverLetter($item)
    {
        $this->coverLetters->removeElement($item);
    }
    public function getCoverLetters()
    {
        return $this->coverLetters;
    }

    public function addReprimandDocument($item)
    {
        if( $item && !$this->reprimandDocuments->contains($item) ) {
            $this->reprimandDocuments->add($item);
        }
        return $this;
    }
    public function removeReprimandDocument($item)
    {
        $this->reprimandDocuments->removeElement($item);
    }
    public function getReprimandDocuments()
    {
        return $this->reprimandDocuments;
    }

    public function addLawsuitDocument($item)
    {
        if( $item && !$this->lawsuitDocuments->contains($item) ) {
            $this->lawsuitDocuments->add($item);
        }
        return $this;
    }
    public function removeLawsuitDocument($item)
    {
        $this->lawsuitDocuments->removeElement($item);
    }
    public function getLawsuitDocuments()
    {
        return $this->lawsuitDocuments;
    }

    /**
     * @param mixed $lawsuit
     */
    public function setLawsuit($lawsuit)
    {
        $this->lawsuit = $lawsuit;
    }

    /**
     * @return mixed
     */
    public function getLawsuit()
    {
        return $this->lawsuit;
    }

    /**
     * @param mixed $reprimand
     */
    public function setReprimand($reprimand)
    {
        $this->reprimand = $reprimand;
    }

    /**
     * @return mixed
     */
    public function getReprimand()
    {
        return $this->reprimand;
    }

    public function addReference($item)
    {
        if( $item && !$this->references->contains($item) ) {
            $this->references->add($item);
            $item->setFellapp($this);
        }
        return $this;
    }
    public function removeReference($item)
    {
        $this->references->removeElement($item);
    }
    public function getReferences()
    {
        return $this->references;
    }


    public function addReport($item)
    {
        if( $item && !$this->reports->contains($item) ) {
            $this->reports->add($item);
        }
        return $this;
    }
    public function removeReport($item)
    {
        $this->reports->removeElement($item);
    }
    public function getReports()
    {
        return $this->reports;
    }


    /**
     * @param mixed $honors
     */
    public function setHonors($honors)
    {
        $this->honors = $honors;
    }

    /**
     * @return mixed
     */
    public function getHonors()
    {
        return $this->honors;
    }

    /**
     * @param mixed $memberships
     */
    public function setMemberships($memberships)
    {
        $this->memberships = $memberships;
    }

    /**
     * @return mixed
     */
    public function getMemberships()
    {
        return $this->memberships;
    }

    /**
     * @param mixed $publications
     */
    public function setPublications($publications)
    {
        $this->publications = $publications;
    }

    /**
     * @return mixed
     */
    public function getPublications()
    {
        return $this->publications;
    }

    /**
     * @param mixed $signatureName
     */
    public function setSignatureName($signatureName)
    {
        $this->signatureName = $signatureName;
    }

    /**
     * @return mixed
     */
    public function getSignatureName()
    {
        return $this->signatureName;
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
     * @param mixed $interviewScore
     */
    public function setInterviewScore($interviewScore)
    {
        $this->interviewScore = $interviewScore;
    }

    /**
     * @return mixed
     */
    public function getInterviewScore()
    {
        return $this->interviewScore;
    }

    /**
     * @param mixed $applicationStatus
     */
    public function setApplicationStatus($applicationStatus)
    {
        $this->applicationStatus = $applicationStatus;
    }

    /**
     * @return mixed
     */
    public function getApplicationStatus()
    {
        return $this->applicationStatus;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }


    public function clearReports() {
        $this->reports->clear();
    }

    public function getRecentReport() {
        return $this->getReports()->last();
    }

    public function getRecentCoverLetter() {
        return $this->getCoverLetters()->last();
    }

    public function getRecentCv() {
        $recentCv = $this->getUser()->getCredentials()->getOneRecentCv();
        return $recentCv->getDocuments()->last();
    }

    public function getRecentExaminationScores() {
        $recentExamination = $this->getUser()->getCredentials()->getOneRecentExamination();
        return $recentExamination->getScores();
    }

    public function getRecentReprimand() {
        if( count($this->getReprimandDocuments()) > 0 ) {
            return $this->getReprimandDocuments()->last();
        } else {
            return null;
        }
    }

    public function getRecentLegalExplanation() {
        if( count($this->getLawsuitDocuments()) > 0 ) {
            return $this->getLawsuitDocuments()->last();
        } else {
            return null;
        }
    }

    //interface methods
    public function addDocument($item)
    {
        $this->addCoverLetter($item);
        return $this;
    }
    public function removeDocument($item)
    {
        $this->removeCoverLetter($item);
    }
    public function getDocuments()
    {
        return $this->getCoverLetters();
    }



    public function __toString() {
        return "FellowshipApplication";
    }

} 