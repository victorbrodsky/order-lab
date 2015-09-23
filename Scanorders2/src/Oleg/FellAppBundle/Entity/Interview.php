<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 9/22/15
 * Time: 12:34 PM
 */

namespace Oleg\FellAppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_interview")
 */
class Interview {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="FellowshipApplication", inversedBy="interviews", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="fellapp_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $fellapp;


    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="interviewer_id", referencedColumnName="id", nullable=true)
     */
    private $interviewer;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $interviewDate;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $startTime;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $endTime;

    /**
     * @ORM\ManyToOne(targetEntity="FellAppRank", cascade={"persist"})
     * @ORM\JoinColumn(name="academicRank_id", referencedColumnName="id", nullable=true)
     */
    private $academicRank;

    /**
     * @ORM\ManyToOne(targetEntity="FellAppRank", cascade={"persist"})
     * @ORM\JoinColumn(name="personalityRank_id", referencedColumnName="id", nullable=true)
     */
    private $personalityRank;

    /**
     * @ORM\ManyToOne(targetEntity="FellAppRank", cascade={"persist"})
     * @ORM\JoinColumn(name="potentialRank_id", referencedColumnName="id", nullable=true)
     */
    private $potentialRank;

    /**
     * @ORM\Column(name="totalRank", type="decimal", precision=2, scale=1, nullable=true)
     */
    private $totalRank;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;


    /**
     * @ORM\ManyToOne(targetEntity="LanguageProficiency", cascade={"persist"})
     * @ORM\JoinColumn(name="languageProficiency_id", referencedColumnName="id", nullable=true)
     */
    private $languageProficiency;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location", cascade={"persist"})
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
     */
    private $location;





    /**
     * @param mixed $fellapp
     */
    public function setFellapp($fellapp)
    {
        $this->fellapp = $fellapp;
    }

    /**
     * @return mixed
     */
    public function getFellapp()
    {
        return $this->fellapp;
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
     * @param mixed $interviewer
     */
    public function setInterviewer($interviewer)
    {
        $this->interviewer = $interviewer;
    }

    /**
     * @return mixed
     */
    public function getInterviewer()
    {
        return $this->interviewer;
    }

    /**
     * @param mixed $academicRank
     */
    public function setAcademicRank($academicRank)
    {
        $this->academicRank = $academicRank;
    }

    /**
     * @return mixed
     */
    public function getAcademicRank()
    {
        return $this->academicRank;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $interviewDate
     */
    public function setInterviewDate($interviewDate)
    {
        $this->interviewDate = $interviewDate;
    }

    /**
     * @return mixed
     */
    public function getInterviewDate()
    {
        return $this->interviewDate;
    }

    /**
     * @param mixed $languageProficiency
     */
    public function setLanguageProficiency($languageProficiency)
    {
        $this->languageProficiency = $languageProficiency;
    }

    /**
     * @return mixed
     */
    public function getLanguageProficiency()
    {
        return $this->languageProficiency;
    }

    /**
     * @param mixed $personalityRank
     */
    public function setPersonalityRank($personalityRank)
    {
        $this->personalityRank = $personalityRank;
    }

    /**
     * @return mixed
     */
    public function getPersonalityRank()
    {
        return $this->personalityRank;
    }

    /**
     * @param mixed $potentialRank
     */
    public function setPotentialRank($potentialRank)
    {
        $this->potentialRank = $potentialRank;
    }

    /**
     * @return mixed
     */
    public function getPotentialRank()
    {
        return $this->potentialRank;
    }

    /**
     * @param mixed $totalRank
     */
    public function setTotalRank($totalRank)
    {
        $this->totalRank = $totalRank;
    }

    /**
     * @return mixed
     */
    public function getTotalRank()
    {
        return $this->totalRank;
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

    /**
     * @param mixed $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param mixed $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }





} 