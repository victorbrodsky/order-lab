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

namespace App\FellAppBundle\Entity;

use App\UserdirectoryBundle\Entity\ListAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\Validator\Constraints as Assert;


//Similar to FellowshipSubspecialty, but used only on the /apply page
#[ORM\Table(name: 'fellapp_globalspecialty')]
#[ORM\Entity]
class GlobalFellowshipSpecialty extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'GlobalFellowshipSpecialty', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'GlobalFellowshipSpecialty', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;


//    //ResidencySpecialty - parent ($trainingTrack)
//    #[ORM\ManyToOne(targetEntity: 'ResidencySpecialty', inversedBy: 'children')]
//    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
//    private $trainingTrack; //$parent;

    //One institution can have many fellowship specialty. Institution is like a parent
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    #[ORM\JoinColumn(name: 'institution_id', referencedColumnName: 'id', nullable: true)]
    private $institution;

    #[ORM\JoinTable(name: 'fellapp_globalspecialty_coordinator')]
    #[ORM\JoinColumn(name: 'fellowshipSubspecialty_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'coordinator_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    private $coordinators;

    #[ORM\JoinTable(name: 'fellapp_globalspecialty_director')]
    #[ORM\JoinColumn(name: 'fellowshipSubspecialty_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'director_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    private $directors;

    #[ORM\JoinTable(name: 'fellapp_globalspecialty_interviewer')]
    #[ORM\JoinColumn(name: 'fellowshipSubspecialty_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'interviewer_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    private $interviewers;

    /*
     * Run by separate cron job, run once a day:
     * If dates are empty - nothing changed.
     * If $seasonYearStart not null -> check if current date = $seasonYearStart => enable $acceptingApplication
     * If $seasonYearEnd not null -> check if current date = $seasonYearEnd => disable $acceptingApplication
     * */
    /**
     * Application season start date
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $seasonYearStart;

    /**
     * Application season end date
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $seasonYearEnd;

    //API key expected in URL to enable remote connection: [l4kn5lk2nl23iron2i3n2l3inl23kn4o2i3j42fowiefw940]
    #[ORM\Column(type: 'string', nullable: true)]
    private $apiConnectionKey;

    #[ORM\Column(type: 'string', nullable: true)]
    private $apiHashConnectionKey;

    //Show an additional section with screening questions on the Fellowship Application page
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $screeningQuestions;

    #[ORM\ManyToOne(targetEntity: 'ExpectedDegreeList')]
    #[ORM\JoinColumn(name: 'expecteddegree_id', referencedColumnName: 'id', nullable: true)]
    private $expectedDegree;

    //Notice to display at the top of Additional Text Attachment Section (leave blank for none):
    #[ORM\Column(type: 'text', nullable: true)]
    private $noticeAttachment;

    /* Added to json field to the FormNode with name="Fellowship Screening Questions Form"
    {
        "Expected answers": {
            "1": "Yes",
            "2": "[[Any except]]: No",
            "3": "[[Any]]",
            "4": "[[Checked]]"
        }
    }
    */
    //Message to the submitter who did not supply expected answers to the screening questions:
    // "Based on your answers you are not eligible to apply to the selected program."
    #[ORM\Column(type: 'text', nullable: true)]
    private $screeningMessage;

    //Show as an available option on the fellowship application form (“Apply” page)
    //show it on apply page (on HUB server). Show yellow well if opened by url ?specialty[]=1
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $showOption;
    
    //per fellowship specialty: Send email invitations to recommendation letter writers with a link to upload their recommendation letter after successful application import
    //Override the site settings: Automatically send invitation emails to upload recommendation letters (sendEmailUploadLetterFellApp)
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $sendEmailUploadLetterFellApp;

    //Fellowship Duration (in months): [12]
    #[ORM\Column(type: 'integer', nullable: true)]
    private $duration;

    /*
     * NOT USED. Use $seasonYearStart instead
     * Run by separate cron job, run once a day:
     * If dates are empty - nothing changed.
     * If start date -> check if current date = $submissionStart => enable $acceptingApplication
     * If end date -> check if current date = $submissionStart => disable $acceptingApplication
     *
     * $submissionStart is similar to $seasonYearStart
     * */
    //Fellowship Application Submission Period Default Start Date: [MM/DD]
    /**
     * Fellowship Application Submission Period Default Start Date: [MM/DD]
     */
    //NOT USED. Use $seasonYearStart instead
    #[ORM\Column(type: 'date', nullable: true)]
    private $submissionStart;

    //Fellowship Application Submission Period Default End Date: [MM/DD]
    /**
     * NOT USED. Use $seasonYearEnd instead
     * Fellowship Application Submission Period Default End Date: [MM/DD]
     */
    //NOT USED. Use $seasonYearEnd instead
    #[ORM\Column(type: 'date', nullable: true)]
    private $submissionEnd;

    //Currently Accepting Applications: [checkmark]
    /*
     * Currently Accepting Applications: [checkmark] field should over-ride the default
     * "Submission Period Default Start Date / Submission Period Default End Date" field values -
     * if the value is NOT checked - do not accept applications (for example the program was already filled).
     * If the value IS checked and is outside of the submission date range, STILL accept applications.
     * This way this check mark can be checked or unchecked for specific programs at any time.
     */
    /*
     * Show specialty on the list, but on select show yellow well saying '...not accept...'
     * AND disable 'Submit Applicant' and show tooltip with the same message
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $acceptingApplication;


    
    public function __construct()
    {
        $this->coordinators = new ArrayCollection();
        $this->directors = new ArrayCollection();
        $this->interviewers = new ArrayCollection();
        $this->setShowOption(true);
        $this->setSendEmailUploadLetterFellApp(false);
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

    public function addCoordinator($item)
    {
        if( $item && !$this->coordinators->contains($item) ) {
            $this->coordinators->add($item);
        }
        return $this;
    }
    public function removeCoordinator($item)
    {
        $this->coordinators->removeElement($item);
    }
    public function getCoordinators()
    {
        return $this->coordinators;
    }

    public function addDirector($item)
    {
        if( $item && !$this->directors->contains($item) ) {
            $this->directors->add($item);
        }
        return $this;
    }
    public function removeDirector($item)
    {
        $this->directors->removeElement($item);
    }
    public function getDirectors()
    {
        return $this->directors;
    }

    public function addInterviewer($item)
    {
        if( $item && !$this->interviewers->contains($item) ) {
            $this->interviewers->add($item);
        }
        return $this;
    }
    public function removeInterviewer($item)
    {
        $this->interviewers->removeElement($item);
    }
    public function getInterviewers()
    {
        return $this->interviewers;
    }

    /**
     * @return mixed
     */
    public function getSeasonYearStart()
    {
        return $this->seasonYearStart;
    }

    /**
     * @param mixed $seasonYearStart
     */
    public function setSeasonYearStart($seasonYearStart)
    {
        $this->seasonYearStart = $seasonYearStart;
    }

    /**
     * @return mixed
     */
    public function getSeasonYearEnd()
    {
        return $this->seasonYearEnd;
    }

    /**
     * @param mixed $seasonYearEnd
     */
    public function setSeasonYearEnd($seasonYearEnd)
    {
        $this->seasonYearEnd = $seasonYearEnd;
    }

    /**
     * @return mixed
     */
    public function getApiConnectionKey()
    {
        return $this->apiConnectionKey;
    }

    /**
     * @param mixed $apiConnectionKey
     */
    public function setApiConnectionKey($apiConnectionKey)
    {
        $this->apiConnectionKey = $apiConnectionKey;
    }

    /**
     * @return mixed
     */
    public function getApiHashConnectionKey()
    {
        return $this->apiHashConnectionKey;
    }

    /**
     * @param mixed $apiHashConnectionKey
     */
    public function setApiHashConnectionKey($apiHashConnectionKey)
    {
        $this->apiHashConnectionKey = $apiHashConnectionKey;
    }

    /**
     * @return mixed
     */
    public function getScreeningQuestions()
    {
        return $this->screeningQuestions;
    }

    /**
     * @param mixed $screeningQuestions
     */
    public function setScreeningQuestions($screeningQuestions)
    {
        $this->screeningQuestions = $screeningQuestions;
    }

    /**
     * @return mixed
     */
    public function getExpectedDegree()
    {
        return $this->expectedDegree;
    }

    /**
     * @param mixed $expectedDegree
     */
    public function setExpectedDegree($expectedDegree)
    {
        $this->expectedDegree = $expectedDegree;
    }

    /**
     * @return mixed
     */
    public function getNoticeAttachment()
    {
        return $this->noticeAttachment;
    }

    /**
     * @param mixed $noticeAttachment
     */
    public function setNoticeAttachment($noticeAttachment)
    {
        $this->noticeAttachment = $noticeAttachment;
    }

    /**
     * @return mixed
     */
    public function getScreeningMessage()
    {
        return $this->screeningMessage;
    }

    /**
     * @param mixed $screeningMessage
     */
    public function setScreeningMessage($screeningMessage)
    {
        $this->screeningMessage = $screeningMessage;
    }

    /**
     * @return mixed
     */
    public function getShowOption()
    {
        return $this->showOption;
    }

    /**
     * @param mixed $showOption
     */
    public function setShowOption($showOption)
    {
        $this->showOption = $showOption;
    }

    /**
     * @return mixed
     */
    public function getSendEmailUploadLetterFellApp()
    {
        return $this->sendEmailUploadLetterFellApp;
    }

    /**
     * @param mixed $sendEmailUploadLetterFellApp
     */
    public function setSendEmailUploadLetterFellApp($sendEmailUploadLetterFellApp)
    {
        $this->sendEmailUploadLetterFellApp = $sendEmailUploadLetterFellApp;
    }

    


    

    //Clinical Informatics (WCM => Pathology)" becomes
    //"WCM Department of Pathology and Laboratory Medicine - Clinical Informatics
    public function getNameInstitution() {
        $name = $this->getName();
        $institution = null;
        if( $this->getInstitution() ) {
            $institution = $this->getInstitution()->getTreeRootAbbreviationChildName(' ');
            return $institution . " - " . $name;
        }
        return $name;
    }

    public function __toString() {
        return $this->getNameInstitution();
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getSubmissionStart()
    {
        return $this->submissionStart;
    }

    /**
     * @param mixed $submissionStart
     */
    public function setSubmissionStart($submissionStart)
    {
        $this->submissionStart = $submissionStart;
    }

    /**
     * @return mixed
     */
    public function getSubmissionEnd()
    {
        return $this->submissionEnd;
    }

    /**
     * @param mixed $submissionEnd
     */
    public function setSubmissionEnd($submissionEnd)
    {
        $this->submissionEnd = $submissionEnd;
    }

    /**
     * @return mixed
     */
    public function getAcceptingApplication()
    {
        return $this->acceptingApplication;
    }

    /**
     * @param mixed $acceptingApplication
     */
    public function setAcceptingApplication($acceptingApplication)
    {
        $this->acceptingApplication = $acceptingApplication;
    }

}