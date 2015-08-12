<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_examination")
 */
class Examination
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
    private $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="Credentials", inversedBy="examinations")
     * @ORM\JoinColumn(name="credentials_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $credentials;


    /**
     * @ORM\ManyToMany(targetEntity="Document")
     * @ORM\JoinTable(name="user_examination_score",
     *      joinColumns={@ORM\JoinColumn(name="examination_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="score_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $scores;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $USMLEStep1Score;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $USMLEStep1DatePassed;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $USMLEStep2CKScore;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $USMLEStep2CKDatePassed;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $USMLEStep2CSScore;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $USMLEStep2CSDatePassed;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $USMLEStep3Score;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $USMLEStep3DatePassed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $ECFMGCertificate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ECFMGCertificateNumber;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $ECFMGCertificateDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $COMLEXLevel1Score;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $COMLEXLevel1DatePassed;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $COMLEXLevel2DatePassed;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $COMLEXLevel2Score;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $COMLEXLevel3Score;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $COMLEXLevel3DatePassed;


    public function __construct( $user ) {
        $this->setCreatedBy($user);
        $this->setCreationDate( new \DateTime());

        $this->scores = new ArrayCollection();
    }

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return mixed
     */
    public function getCredentials()
    {
        return $this->credentials;
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
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function addScore($item)
    {
        if( $item && !$this->scores->contains($item) ) {
            $this->scores->add($item);
        }
        return $this;
    }
    public function removeScore($item)
    {
        $this->scores->removeElement($item);
    }
    public function getScores()
    {
        return $this->scores;
    }

    /**
     * @param mixed $USMLEStep1DatePassed
     */
    public function setUSMLEStep1DatePassed($USMLEStep1DatePassed)
    {
        $this->USMLEStep1DatePassed = $USMLEStep1DatePassed;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep1DatePassed()
    {
        return $this->USMLEStep1DatePassed;
    }

    /**
     * @param mixed $USMLEStep1Score
     */
    public function setUSMLEStep1Score($USMLEStep1Score)
    {
        $this->USMLEStep1Score = $USMLEStep1Score;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep1Score()
    {
        return $this->USMLEStep1Score;
    }

    /**
     * @param mixed $USMLEStep2CKDatePassed
     */
    public function setUSMLEStep2CKDatePassed($USMLEStep2CKDatePassed)
    {
        $this->USMLEStep2CKDatePassed = $USMLEStep2CKDatePassed;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CKDatePassed()
    {
        return $this->USMLEStep2CKDatePassed;
    }

    /**
     * @param mixed $USMLEStep2CKScore
     */
    public function setUSMLEStep2CKScore($USMLEStep2CKScore)
    {
        $this->USMLEStep2CKScore = $USMLEStep2CKScore;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CKScore()
    {
        return $this->USMLEStep2CKScore;
    }

    /**
     * @param mixed $USMLEStep2CSDatePassed
     */
    public function setUSMLEStep2CSDatePassed($USMLEStep2CSDatePassed)
    {
        $this->USMLEStep2CSDatePassed = $USMLEStep2CSDatePassed;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CSDatePassed()
    {
        return $this->USMLEStep2CSDatePassed;
    }

    /**
     * @param mixed $USMLEStep2CSScore
     */
    public function setUSMLEStep2CSScore($USMLEStep2CSScore)
    {
        $this->USMLEStep2CSScore = $USMLEStep2CSScore;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CSScore()
    {
        return $this->USMLEStep2CSScore;
    }

    /**
     * @param mixed $USMLEStep3DatePassed
     */
    public function setUSMLEStep3DatePassed($USMLEStep3DatePassed)
    {
        $this->USMLEStep3DatePassed = $USMLEStep3DatePassed;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep3DatePassed()
    {
        return $this->USMLEStep3DatePassed;
    }

    /**
     * @param mixed $USMLEStep3Score
     */
    public function setUSMLEStep3Score($USMLEStep3Score)
    {
        $this->USMLEStep3Score = $USMLEStep3Score;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep3Score()
    {
        return $this->USMLEStep3Score;
    }

    /**
     * @param mixed $ECFMGCertificateDate
     */
    public function setECFMGCertificateDate($ECFMGCertificateDate)
    {
        $this->ECFMGCertificateDate = $ECFMGCertificateDate;
    }

    /**
     * @return mixed
     */
    public function getECFMGCertificateDate()
    {
        return $this->ECFMGCertificateDate;
    }

    /**
     * @param mixed $ECFMGCertificateNumber
     */
    public function setECFMGCertificateNumber($ECFMGCertificateNumber)
    {
        $this->ECFMGCertificateNumber = $ECFMGCertificateNumber;
    }

    /**
     * @return mixed
     */
    public function getECFMGCertificateNumber()
    {
        return $this->ECFMGCertificateNumber;
    }

    /**
     * @param mixed $ECFMGCertificate
     */
    public function setECFMGCertificate($ECFMGCertificate)
    {
        $this->ECFMGCertificate = $ECFMGCertificate;
    }

    /**
     * @return mixed
     */
    public function getECFMGCertificate()
    {
        return $this->ECFMGCertificate;
    }

    /**
     * @param mixed $COMLEXLevel1DatePassed
     */
    public function setCOMLEXLevel1DatePassed($COMLEXLevel1DatePassed)
    {
        $this->COMLEXLevel1DatePassed = $COMLEXLevel1DatePassed;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel1DatePassed()
    {
        return $this->COMLEXLevel1DatePassed;
    }

    /**
     * @param mixed $COMLEXLevel1Score
     */
    public function setCOMLEXLevel1Score($COMLEXLevel1Score)
    {
        $this->COMLEXLevel1Score = $COMLEXLevel1Score;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel1Score()
    {
        return $this->COMLEXLevel1Score;
    }

    /**
     * @param mixed $COMLEXLevel2DatePassed
     */
    public function setCOMLEXLevel2DatePassed($COMLEXLevel2DatePassed)
    {
        $this->COMLEXLevel2DatePassed = $COMLEXLevel2DatePassed;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel2DatePassed()
    {
        return $this->COMLEXLevel2DatePassed;
    }

    /**
     * @param mixed $COMLEXLevel2Score
     */
    public function setCOMLEXLevel2Score($COMLEXLevel2Score)
    {
        $this->COMLEXLevel2Score = $COMLEXLevel2Score;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel2Score()
    {
        return $this->COMLEXLevel2Score;
    }

    /**
     * @param mixed $COMLEXLevel3DatePassed
     */
    public function setCOMLEXLevel3DatePassed($COMLEXLevel3DatePassed)
    {
        $this->COMLEXLevel3DatePassed = $COMLEXLevel3DatePassed;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel3DatePassed()
    {
        return $this->COMLEXLevel3DatePassed;
    }

    /**
     * @param mixed $COMLEXLevel3Score
     */
    public function setCOMLEXLevel3Score($COMLEXLevel3Score)
    {
        $this->COMLEXLevel3Score = $COMLEXLevel3Score;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel3Score()
    {
        return $this->COMLEXLevel3Score;
    }






    public function __toString() {
        return "Examination";
    }

}