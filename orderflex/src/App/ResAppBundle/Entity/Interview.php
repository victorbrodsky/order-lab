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
 * User: DevServer
 * Date: 9/22/15
 * Time: 12:34 PM
 */

namespace App\ResAppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity
 * @ORM\Table(name="resapp_interview")
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
     * @ORM\ManyToOne(targetEntity="ResidencyApplication", inversedBy="interviews")
     * @ORM\JoinColumn(name="resapp_id", referencedColumnName="id", nullable=true)
     */
    private $resapp;

    /**
     * actual submitter of the scores
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="submitter_id", referencedColumnName="id")
     */
    private $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
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
     * @ORM\ManyToOne(targetEntity="ResAppRank")
     * @ORM\JoinColumn(name="academicRank_id", referencedColumnName="id", nullable=true)
     */
    private $academicRank;

    /**
     * @ORM\ManyToOne(targetEntity="ResAppRank")
     * @ORM\JoinColumn(name="personalityRank_id", referencedColumnName="id", nullable=true)
     */
    private $personalityRank;

    /**
     * @ORM\ManyToOne(targetEntity="ResAppRank")
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
     * @ORM\ManyToOne(targetEntity="LanguageProficiency")
     * @ORM\JoinColumn(name="languageProficiency_id", referencedColumnName="id", nullable=true)
     */
    private $languageProficiency;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
     */
    private $location;

    /**
     * Fit for residency program
     *
     * @ORM\ManyToOne(targetEntity="ResAppFitForProgram")
     * @ORM\JoinColumn(name="fitForProgram_id", referencedColumnName="id", nullable=true)
     */
    private $fitForProgram;




    /**
     * @param mixed $resapp
     */
    public function setResapp($resapp)
    {
        $this->resapp = $resapp;
    }

    /**
     * @return mixed
     */
    public function getResapp()
    {
        return $this->resapp;
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

    /**
     * @return mixed
     */
    public function getFitForProgram()
    {
        return $this->fitForProgram;
    }

    /**
     * @param mixed $fitForProgram
     */
    public function setFitForProgram($fitForProgram)
    {
        $this->fitForProgram = $fitForProgram;
    }

    /**
     * @return mixed
     */
    public function getSubmitter()
    {
        return $this->submitter;
    }

    /**
     * @param mixed $submitter
     */
    public function setSubmitter($submitter)
    {
        $this->submitter = $submitter;
    }
    
    


    public function __toString() {
        $res = "Interview";
        if( $this->getInterviewer() ) {
            $res = $res . " Interviewer: ".$this->getInterviewer();
        }
        return $res;
    }

    public function getInterviewDateStr()
    {
        $interviewDateStr = "";
        $interviewDate = $this->getInterviewDate();
        $resapp = $this->getResapp();
        if( $resapp ) {
            $resappInterviewDate = $resapp->getInterviewDate();
            //$resappInterviewDate->setTimezone(new DateTimeZone("UTC"));
            //$interviewDateStr = ", interview date " . $resappInterviewDate->format('m/d/Y');
            if( $resappInterviewDate && $interviewDate && $resappInterviewDate != $interviewDate ) {
                $interviewDateStr = ", general interview date " . $resappInterviewDate->format('m/d/Y') .
                    ", your interview date " . $interviewDate->format('m/d/Y');
            }
        } else {
            if( $interviewDate ) {
                $interviewDateStr = ", interview date " . $interviewDate->format('m/d/Y');
            }
        }

        return $interviewDateStr;
    }

    public function getInterviewerInfo() {
        $res = "Unknown Interviewer";
        if( $this->getInterviewer() ) {
            //$res = $this->getInterviewer()->getUsernameOptimal();
            $res = $this->getInterviewer()."";
        }
        return $res;
    }

    public function isEmpty() {
        if( $this->getTotalRank() ) {
            return false;
        }

        return true;
    }
    public function formStatus() {
        //return NULL;
        //$submitted = " (submitted)";
        $submitted = '<span class="glyphicon glyphicon-ok" style="color:#50C878"></span>&nbsp;';
        if( !$this->isEmpty() ) {
            return $submitted;
        }
        return NULL;
    }
} 