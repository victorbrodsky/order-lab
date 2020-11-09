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
 * Date: 8/24/15
 * Time: 11:08 AM
 */

namespace App\ResAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Entity\BaseUserAttributes;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity
 * @ORM\Table(name="resapp_residencyapplication")
 */
class ResidencyApplication extends BaseUserAttributes {

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $googleFormId;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User", inversedBy="residencyApplications", cascade={"remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $user;

    /**
     * Residency Start Year
     * Usually: $startDate = $applicationSeasonStartDate + 1 year
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * Application Season Start Year
     * the year that was imported from the old site: enrolment dates in 2019 => 2019-2020
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $applicationSeasonStartDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $applicationSeasonEndDate;
    
    /**
     * Residency Specialties (ResidencySpecialty)
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\ResidencySpecialty", cascade={"persist"})
     */
    private $residencySubspecialty;

    /**
     * Residency Type (ResidencyTrackList)
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\ResidencyTrackList", cascade={"persist"})
     */
    private $residencyTrack;

    /**
     * This should be the link to WCMC's "Department of Pathology and Laboratory Medicine"
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;

    /**
     * Use Cover Letter DB for ERAS (Electronic Residency Application Service) files. Only the latest ERAS file will be used in the application's PDF
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_coverLetter",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="coverLetter_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $coverLetters;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_cv",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="cv_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $cvs;


    //Reprimands
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $reprimand;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_reprimandDocument",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="reprimandDocument_id", referencedColumnName="id", onDelete="CASCADE")}
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
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_lawsuitDocument",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="lawsuitDocument_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $lawsuitDocuments;

    /**
     * @ORM\OneToMany(targetEntity="Reference", mappedBy="resapp", cascade={"persist"})
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

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $applicationStatus;

    /**
     * application status as a list
     * @ORM\ManyToOne(targetEntity="ResAppStatus")
     */
    private $appStatus;

    /**
     * CSV=>Applicant Applied Date, Handsontable=>Application Receipt Date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $timestamp;


    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_report",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $reports;

    /**
     * Application PDF without attached documents: Will be automatically generated if left empty
     * 
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_formReport",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="formReport_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $formReports;

    /**
     * Manually Uploaded Application PDF without attachments
     * 
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resapp_manualreports",
     *      joinColumns={@ORM\JoinColumn(name="resapp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="manualreport_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $manualReports;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_oldReport",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="oldReport_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $oldReports;

    /**
     * Other Documents
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_document",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $documents;

    /**
     * Itinerarys
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_itinerary",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="itinerary_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $itinerarys;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $interviewDate;

    /**
     * @ORM\OneToMany(targetEntity="Interview", mappedBy="resapp", cascade={"persist","remove"})
     */
    private $interviews;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="resapp_resApp_observer",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="observer_id", referencedColumnName="id")}
     *      )
     **/
    private $observers;

    /**
     * @ORM\OneToOne(targetEntity="Rank", inversedBy="resapp", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="rank_id", referencedColumnName="id", nullable=true)
     **/
    private $rank;


    /////////// user objects /////////////
//    /**
//     * @ORM\ManyToMany(targetEntity="EmploymentStatus")
//     * @ORM\JoinTable(name="resapp_residencyApplication_employmentStatus",
//     *      joinColumns={@ORM\JoinColumn(name="residencyApplication_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="employmentStatus_id", referencedColumnName="id")}
//     *      )
//     **/
//    private $employmentStatuses;

    /**
     * Other Documents
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_resApp_avatar",
     *      joinColumns={@ORM\JoinColumn(name="resApp_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="avatar_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $avatars;

    /**
     * //"completionDate" = "DESC", "orderinlist" = "ASC"
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Training", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_residencyApplication_training",
     *      joinColumns={@ORM\JoinColumn(name="residencyApplication_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="training_id", referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"orderinlist" = "ASC"})
     **/
    private $trainings;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Examination", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_residencyApplication_examination",
     *      joinColumns={@ORM\JoinColumn(name="residencyApplication_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="examination_id", referencedColumnName="id")}
     *      )
     **/
    private $examinations;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Location", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_residencyApplication_location",
     *      joinColumns={@ORM\JoinColumn(name="residencyApplication_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id")}
     *      )
     **/
    private $locations;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Citizenship", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_residencyApplication_citizenship",
     *      joinColumns={@ORM\JoinColumn(name="residencyApplication_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="citizenship_id", referencedColumnName="id")}
     *      )
     **/
    private $citizenships;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\StateLicense", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_residencyApplication_stateLicense",
     *      joinColumns={@ORM\JoinColumn(name="residencyApplication_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="stateLicense_id", referencedColumnName="id")}
     *      )
     **/
    private $stateLicenses;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\BoardCertification", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_residencyApplication_boardCertification",
     *      joinColumns={@ORM\JoinColumn(name="residencyApplication_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="boardCertification_id", referencedColumnName="id")}
     *      )
     **/
    private $boardCertifications;

    /////////// EOF user objects /////////////

    //Additional fields (citizenship and visa status (similar to the fellowship site) is under citizenship object)
    /**
     * Ethnicity: yes/no or Yes/No/Unknown
     * Black or African American, Hispanic or Latino, American Indian or Alaska Native, Native Hawaiian and other Pacific Islander, Unknown
     * Visible only to Admin and Coordinator
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $ethnicity;

    /**
     * number of 1st author publications
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstPublications;

    /**
     * number of all publications
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $allPublications;

    /**
     * AOA (Yes/No): boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $aoa;

    /**
     * Couples match (Yes/No): boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $couple;

    /**
     * Post-Sophomore Fellowship: [Pathology / None]
     * 
     * @ORM\ManyToOne(targetEntity="PostSophList", cascade={"persist","remove"})
     */
    private $postSoph;

    /**
     * AAMC ID
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $aamcId;

    /**
     * ERAS Applicant ID (Unique application ID)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $erasApplicantId;

    //Residency types: AP/EXP CP/EXP +Experimental Pathology


    public function __construct($author=null) {
        parent::__construct($author);

        $this->cvs = new ArrayCollection();
        $this->coverLetters = new ArrayCollection();
        $this->reprimandDocuments = new ArrayCollection();
        $this->lawsuitDocuments = new ArrayCollection();
        $this->references = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->formReports = new ArrayCollection();
        $this->manualReports = new ArrayCollection();
        $this->oldReports = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->itinerarys = new ArrayCollection();
        $this->interviews = new ArrayCollection();
        $this->observers = new ArrayCollection();

        //$this->employmentStatuses = new ArrayCollection();
        $this->trainings = new ArrayCollection();
        $this->avatars = new ArrayCollection();
        $this->examinations = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->citizenships = new ArrayCollection();
        $this->stateLicenses = new ArrayCollection();
        $this->boardCertifications = new ArrayCollection();

    }




    //////////////// user object //////////////////////
//    public function addEmploymentStatus($item)
//    {
//        if( $item && !$this->employmentStatuses->contains($item) ) {
//            $this->employmentStatuses->add($item);
//        }
//        return $this;
//    }
//    public function removeEmploymentStatus($item)
//    {
//        $this->employmentStatuses->removeElement($item);
//    }
//    public function getEmploymentStatuses()
//    {
//        return $this->employmentStatuses;
//    }

    public function addTraining($item)
    {
        if( $item && !$this->trainings->contains($item) ) {
            $this->trainings->add($item);
        }
        return $this;
    }
    public function removeTraining($item)
    {
        $this->trainings->removeElement($item);
    }
    public function getTrainings()
    {
        return $this->trainings;
    }


    public function addAvatar($item)
    {
        if( $item && !$this->avatars->contains($item) ) {
            $this->avatars->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeAvatar($item)
    {
        $this->avatars->removeElement($item);
        $item->clearUseObject();
    }
    public function getAvatars()
    {
        return $this->avatars;
    }


    public function addExamination($item)
    {
        if( $item && !$this->examinations->contains($item) ) {
            $this->examinations->add($item);
        }
        return $this;
    }
    public function removeExamination($item)
    {
        $this->examinations->removeElement($item);
    }
    public function getExaminations()
    {
        return $this->examinations;
    }

    public function addLocation($location)
    {
        if( $location && !$this->locations->contains($location) ) {
            $this->locations->add($location);
        }

        return $this;
    }
    public function removeLocation($locations)
    {
        $this->locations->removeElement($locations);
    }
    public function getLocations()
    {
        return $this->locations;
    }

    public function addCitizenship($item)
    {
        if( $item && !$this->citizenships->contains($item) ) {
            $this->citizenships->add($item);
        }
        return $this;
    }
    public function removeCitizenship($item)
    {
        $this->citizenships->removeElement($item);
    }
    public function getCitizenships()
    {
        return $this->citizenships;
    }

    public function getStateLicenses()
    {
        return $this->stateLicenses;
    }
    public function addStateLicense($item)
    {
        if( $item && !$this->stateLicenses->contains($item) ) {
            $this->stateLicenses->add($item);
        }

    }
    public function removeStateLicense($item)
    {
        $this->stateLicenses->removeElement($item);
    }

    public function getBoardCertifications()
    {
        return $this->boardCertifications;
    }
    public function addBoardCertification($item)
    {
        if( $item && !$this->boardCertifications->contains($item) ) {
            $this->boardCertifications->add($item);
        }

    }
    public function removeBoardCertification($item)
    {
        $this->boardCertifications->removeElement($item);
    }

    //////////////// EOF user object //////////////////////




    /**
     * @param mixed $googleFormId
     */
    public function setGoogleFormId($googleFormId)
    {
        $this->googleFormId = $googleFormId;
    }

    /**
     * @return mixed
     */
    public function getGoogleFormId()
    {
        return $this->googleFormId;
    }


    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Expected Graduation Date: startDate + ResidencyTrackList->duration - 1 day
     *
     * @return mixed
     */
    public function getEndDate()
    {
        $startDate = $this->getStartDate();
        $residencyTrack = $this->getResidencyTrack();
        if( $startDate && $residencyTrack && $duration=$residencyTrack->getDuration() ) {
            $expectedEndDate = clone $startDate;
            $expectedEndDate->modify('+'.$duration.' year');
            $expectedEndDate->modify('-1 day');
            return $expectedEndDate;
        }

        return $this->endDate;
    }

    /**
     * @param mixed $residencySubspecialty
     */
    public function setResidencySubspecialty($residencySubspecialty)
    {
        $this->residencySubspecialty = $residencySubspecialty;
    }

    /**
     * @return mixed
     */
    public function getResidencySubspecialty()
    {
        return $this->residencySubspecialty;
    }
    
    /**
     * @param mixed $residencyTrack
     */
    public function setResidencyTrack($residencyTrack)
    {
        $this->residencyTrack = $residencyTrack;
    }

    /**
     * @return mixed
     */
    public function getResidencyTrack()
    {
        return $this->residencyTrack;
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
     * @return mixed
     */
    public function getApplicationSeasonStartDate()
    {
        return $this->applicationSeasonStartDate;
    }

    /**
     * @param mixed $applicationSeasonStartDate
     */
    public function setApplicationSeasonStartDate($applicationSeasonStartDate)
    {
        $this->applicationSeasonStartDate = $applicationSeasonStartDate;
    }

    /**
     * @return mixed
     */
    public function getApplicationSeasonEndDate()
    {
        return $this->applicationSeasonEndDate;
    }

    /**
     * @param mixed $applicationSeasonEndDate
     */
    public function setApplicationSeasonEndDate($applicationSeasonEndDate)
    {
        $this->applicationSeasonEndDate = $applicationSeasonEndDate;
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



    public function getCvs()
    {
        return $this->cvs;
    }
    public function addCv($item)
    {
        if( $item && !$this->cvs->contains($item) ) {
            $this->cvs->add($item);
            $item->createUseObject($this);
        }

    }
    public function removeCv($item)
    {
        $this->cvs->removeElement($item);
        $item->clearUseObject();
    }

    public function addCoverLetter($item)
    {
        if( $item && !$this->coverLetters->contains($item) ) {
            $this->coverLetters->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeCoverLetter($item)
    {
        $this->coverLetters->removeElement($item);
        $item->clearUseObject();
    }
    public function getCoverLetters()
    {
        return $this->coverLetters;
    }

    public function addReprimandDocument($item)
    {
        if( $item && !$this->reprimandDocuments->contains($item) ) {
            $this->reprimandDocuments->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeReprimandDocument($item)
    {
        $this->reprimandDocuments->removeElement($item);
        $item->clearUseObject();
    }
    public function getReprimandDocuments()
    {
        return $this->reprimandDocuments;
    }

    public function addLawsuitDocument($item)
    {
        if( $item && !$this->lawsuitDocuments->contains($item) ) {
            $this->lawsuitDocuments->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeLawsuitDocument($item)
    {
        $this->lawsuitDocuments->removeElement($item);
        $item->clearUseObject();
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
            $item->setResapp($this);
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
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeReport($item)
    {
        $this->reports->removeElement($item);
        $item->clearUseObject();
    }
    public function getReports()
    {
        return $this->reports;
    }

    public function addFormReport($item)
    {
        if( $item && !$this->formReports->contains($item) ) {
            $this->formReports->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeFormReport($item)
    {
        $this->formReports->removeElement($item);
        $item->clearUseObject();
    }
    public function getFormReports()
    {
        return $this->formReports;
    }

    public function addManualReport($item)
    {
        if( $item && !$this->manualReports->contains($item) ) {
            $this->manualReports->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeManualReport($item)
    {
        $this->manualReports->removeElement($item);
        $item->clearUseObject();
    }
    public function getManualReports()
    {
        return $this->manualReports;
    }

    public function addOldReport($item)
    {
        if( $item && !$this->oldReports->contains($item) ) {
            $this->oldReports->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeOldReport($item)
    {
        $this->oldReports->removeElement($item);
        $item->clearUseObject();
    }
    public function getOldReports()
    {
        return $this->oldReports;
    }


    public function addItinerary($item)
    {
        if( $item && !$this->itinerarys->contains($item) ) {
            $this->itinerarys->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeItinerary($item)
    {
        $this->itinerarys->removeElement($item);
        $item->clearUseObject();
    }
    public function getItinerarys()
    {
        return $this->itinerarys;
    }


    public function addInterview($item)
    {
        if( $item && !$this->interviews->contains($item) ) {
            $this->interviews->add($item);
            $item->setResapp($this);
        }
        return $this;
    }
    public function removeInterview($item)
    {
        $this->interviews->removeElement($item);
    }
    public function getInterviews()
    {
        return $this->interviews;
    }


    public function addObserver($item)
    {
        if( $item && !$this->observers->contains($item) ) {
            $this->observers->add($item);
        }
        return $this;
    }
    public function removeObserver($item)
    {
        $this->observers->removeElement($item);
    }
    public function getObservers()
    {
        return $this->observers;
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

//    /**
//     * @param mixed $applicationStatus
//     */
//    public function setApplicationStatus($applicationStatus)
//    {
//        $this->applicationStatus = $applicationStatus;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getApplicationStatus()
//    {
//        return $this->applicationStatus;
//    }

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

    public function addDocument($item)
    {
        if( $item && !$this->documents->contains($item) ) {
            $this->documents->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeDocument($item)
    {
        $this->documents->removeElement($item);
        $item->clearUseObject();
    }
    public function getDocuments()
    {
        return $this->documents;
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
     * @param mixed $appStatus
     */
    public function setAppStatus($appStatus)
    {
        $this->appStatus = $appStatus;
    }

    /**
     * @return mixed
     */
    public function getAppStatus()
    {
        return $this->appStatus;
    }

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * @return mixed
     */
    public function getEthnicity()
    {
        return $this->ethnicity;
    }

    /**
     * @param mixed $ethnicity
     */
    public function setEthnicity($ethnicity)
    {
        $this->ethnicity = $ethnicity;
    }

    /**
     * @return mixed
     */
    public function getFirstPublications()
    {
        return $this->firstPublications;
    }

    /**
     * @param mixed $firstPublications
     */
    public function setFirstPublications($firstPublications)
    {
        $this->firstPublications = $firstPublications;
    }

    /**
     * @return mixed
     */
    public function getAllPublications()
    {
        return $this->allPublications;
    }

    /**
     * @param mixed $allPublications
     */
    public function setAllPublications($allPublications)
    {
        $this->allPublications = $allPublications;
    }

    /**
     * @return mixed
     */
    public function getAoa()
    {
        return $this->aoa;
    }

    /**
     * @param mixed $aoa
     */
    public function setAoa($aoa)
    {
        $this->aoa = $aoa;
    }

    /**
     * @return mixed
     */
    public function getCouple()
    {
        return $this->couple;
    }

    /**
     * @param mixed $couple
     */
    public function setCouple($couple)
    {
        $this->couple = $couple;
    }

    /**
     * @return mixed
     */
    public function getPostSoph()
    {
        return $this->postSoph;
    }

    /**
     * @param mixed $postSoph
     */
    public function setPostSoph($postSoph)
    {
        $this->postSoph = $postSoph;
    }

    /**
     * @return mixed
     */
    public function getAamcId()
    {
        return $this->aamcId;
    }

    /**
     * @param mixed $aamcId
     */
    public function setAamcId($aamcId)
    {
        $this->aamcId = $aamcId;
    }

    /**
     * @return mixed
     */
    public function getErasApplicantId()
    {
        return $this->erasApplicantId;
    }

    /**
     * @param mixed $erasApplicantId
     */
    public function setErasApplicantId($erasApplicantId)
    {
        $this->erasApplicantId = $erasApplicantId;
    }


    


    public function clearReports() {
        $this->reports->clear();
    }

    public function getRecentReport() {
        if( count($this->getReports()) > 0 ) {
            return $this->getReports()->last();
        } else {
            return null;
        }
    }
    public function getTheMostRecentReport() {
        if( count($this->getReports()) > 0 ) {
            return $this->getReports()->last();
        } else {
            if( count($this->getOldReports()) > 0 ) {
                return $this->getOldReports()->last();
            } else {
                return null;
            }
        }
    }

    public function getRecentCoverLetter() {
        if( count($this->getCoverLetters()) > 0 ) {
            return $this->getCoverLetters()->last();
        } else {
            return null;
        }
    }

    public function getRecentCv() {
        if( count($this->getCvs()) > 0 ) {
            return $this->getCvs()->last();
        } else {
            return null;
        }
    }

    public function getRecentAvatar() {
        if( count($this->getAvatars()) > 0 ) {
            return $this->getAvatars()->last();
        } else {
            return null;
        }
    }

//    public function getRecentExaminationScores() {
//        $recentExamination = $this->getUser()->getCredentials()->getOneRecentExamination();
//        return $recentExamination->getScores();
//    }
    public function getExaminationScores() {
        $scores = new ArrayCollection();
        foreach( $this->getExaminations() as $examination ) {
            foreach( $examination->getScores() as $score ) {
                if( $score && !$scores->contains($score) ) {
                    $scores->add($score);
                }
            }
        }
        return $scores;
    }

    public function getReferenceLetters() {
        $refletters = new ArrayCollection();
        foreach( $this->getReferences() as $reference ) {
            foreach( $reference->getDocuments() as $refletter ) {
                if( $refletter && !$refletters->contains($refletter) ) {
                    $refletters->add($refletter);
                }
            }
        }
        return $refletters;
    }
    public function getRecentReferenceLetters() {
        $refletters = new ArrayCollection();
        foreach( $this->getReferences() as $reference ) {
            $refletter = $reference->getRecentReferenceLetter();
            if( $refletter && !$refletters->contains($refletter) ) {
                $refletters->add($refletter);
            }
        }
        return $refletters;
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

    public function getRecentItinerary() {
        if( count($this->getItinerarys()) > 0 ) {
            return $this->getItinerarys()->last();
        } else {
            return null;
        }
    }

    //get recent "Application PDF without attachmed documents" - formReports
    public function getRecentFormReports() {
        if( count($this->getReports()) > 0 ) {
            return $this->getReports()->last();
        } else {
            return null;
        }
    }

    public function getInterviewIdByUser( $interviewer ) {
        $interview = $this->getInterviewByUser($interviewer);
        if( $interview ) {
            return $interview->getId();
        }
        return null;
    }
    public function getInterviewByUser( $interviewer ) {
        $interview = null;

        $items = $this->getInterviews();

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("interviewer", $interviewer))
            //->orderBy(array("creationDate" => Criteria::DESC))
        ;
        $itemsFiltered = $items->matching($criteria);

        if( count($itemsFiltered) > 0 ) {
            $itemFiltered = $itemsFiltered->first();
            if( $itemFiltered && $itemFiltered->getId() ) {
                $interview = $itemFiltered;
            }
        }

        return $interview;
    }
    
    //$trainingTypeName: Medical, Residency, GME
    public function getDegreeByTrainingTypeName( $trainingTypeName ) {
        $degree = "";
        
        $items = $this->getTrainings();
        
        foreach( $items as $item ) {
            if( $item->getTrainingType() && $item->getTrainingType()->getName() == $trainingTypeName ) {
                $degree = $item->getDegree().""; 
                break;
            }
        }               
 
        return $degree;
    }
    
    //$trainingTypeName: Medical, Residency, GME
    public function getSchoolByTrainingTypeName( $trainingTypeName, $withGeoLocation=false, $withResidencySpecialty=false ) {
        $schoolName = "";

        foreach( $this->getTrainings() as $item ) {
            //echo "training=".$item->getId()."; Institution=".$item->getInstitution()."<br>";
            if( $item->getTrainingType() && $item->getTrainingType()->getName() == $trainingTypeName ) {

                $schoolName = "";

                //AP, CP, AP/CP, other or Area of Training
                if( $withResidencySpecialty && $item->getResidencyTrack() ) {
                    $schoolName = $schoolName . $item->getResidencyTrack();
                }
                if( $withResidencySpecialty && $item->getMajors() ) {
                    $majorArr = array();
                    foreach( $item->getMajors() as $major ) {
                        $majorArr[] = $major."";
                    }
                    if( $schoolName && count($majorArr)>0 ) {
                        $schoolName = $schoolName . "; ";
                    }
                    $schoolName = $schoolName . implode(", ",$majorArr);
                }

                //Institution
                if( $item->getInstitution() ) {
                    $separator = "";
                    if( $schoolName ) {
                        $separator = "<br>";
                    }
                    $schoolName = $schoolName . $separator . $this->capitalizeMultiIfNotAllCapital($item->getInstitution()) . "";
                }

                //Finish Date
                if( $item->getCompletionDate() ) {
                    $transformer = new DateTimeToStringTransformer(null,null,'Y');                            
                    $schoolName = $schoolName . ", " . $transformer->transform($item->getCompletionDate());
                }

                //City, Country Abbreviation such as USA (Full name of country if abbreviation not available)
                if( $withGeoLocation && $item->getGeoLocation() ) {
                    $locationStr = $item->getGeoLocation()->getFullGeoLocation();
                    if( $locationStr ) {
                        $schoolName = $schoolName . "<br>" . $locationStr;
                    }
                }

                //echo "schoolName=$schoolName <br>";

                break;
            }
        }                    
 
        return $schoolName;
    }
    function capitalizeMultiIfNotAllCapital($s) {
        $sArr = explode(' ',$s);
        $resArr = array();
        foreach( $sArr as $str ) {
            $resArr[] = $this->capitalizeIfNotAllCapital($str);
        }
        return implode(' ',$resArr);
    }
//    function capitalizeIfNotAllCapital($s) {
//        if( strlen(preg_replace('![^A-Z]+!', '', $s)) == strlen($s) ) {
//            $s = ucfirst(strtolower($s));
//        }
//        return $s;
//    }
    public function capitalizeIfNotAllCapital($s) {
        if( !$s ) {
            return $s;
        }
        $convert = false;
        //check if all UPPER
        if( strtoupper($s) == $s ) {
            $convert = true;
        }
        //check if all lower
        if( strtolower($s) == $s ) {
            $convert = true;
        }
        if( $convert ) {
            return ucwords( strtolower($s) );
        }
        return $s;
    }
    
    public function getAllReferences() {
        $references = "";
        
        $items = $this->getReferences();
        
        $refNameArr = array();
        
        foreach( $items as $item ) {
            if( $item->getName() ) {
                $refNameArr[] = $item->getName();
            }
        }                    
 
        return implode("; ",$refNameArr);
    }
    
    
    public function getUsmleArr() {
        $usmleArr = array();
        
        foreach( $this->getExaminations() as $examination ) {
            
            if( 
                !$examination->getUSMLEStep1Score() &&
                !$examination->getUSMLEStep2CKScore() &&
                !$examination->getUSMLEStep3Score()    
            ) {
                continue;
            }
            
            if( $examination->getUSMLEStep1Score() ) {
                $usmleArr[] = $examination->getUSMLEStep1Score();
            } else {
                $usmleArr[] = "-";
            }
            if( $examination->getUSMLEStep2CKScore() ) {
                $usmleArr[] = $examination->getUSMLEStep2CKScore();
            } else {
                $usmleArr[] = "-";
            }
            if( $examination->getUSMLEStep3Score() ) {
                $usmleArr[] = $examination->getUSMLEStep3Score();
            } else {
                $usmleArr[] = "-";
            }
        }
        //print_r($usmleArr);
        
        return $usmleArr;
    }
    
    public function getComlexArr() {
        $comlexArr = array();
        
        foreach( $this->getExaminations() as $examination ) {
            
            if( 
                !$examination->getCOMLEXLevel1Score() &&
                !$examination->getCOMLEXLevel2Score() &&
                !$examination->getCOMLEXLevel3Score()    
            ) {
                continue;
            }
            
            if( $examination->getCOMLEXLevel1Score() ) {
                $comlexArr[] = $examination->getCOMLEXLevel1Score();
            } else {
                $comlexArr[] = "-";
            }
            if( $examination->getCOMLEXLevel2Score() ) {
                $comlexArr[] = $examination->getCOMLEXLevel2Score();
            } else {
                $comlexArr[] = "-";
            }
            if( $examination->getCOMLEXLevel3Score() ) {
                $comlexArr[] = $examination->getCOMLEXLevel3Score();
            } else {
                $comlexArr[] = "-";
            }
        }
        //print_r($comlexArr);
        
        return $comlexArr;
    }
    
    public function daysAfterInterviewDate() {
        $interviewDate = $this->getInterviewDate();
        if( $interviewDate ) {
            $now = new \DateTime();      
            $diff = $interviewDate->diff($now)->format("%R%a");
        } else {
            $diff = null;
        }
        //echo "diff=",$diff."<br>";
        return $diff;
    }

//    //interface methods
//    public function addDocument($item)
//    {
//        $this->addCoverLetter($item);
//        return $this;
//    }
//    public function removeDocument($item)
//    {
//        $this->removeCoverLetter($item);
//    }
//    public function getDocuments()
//    {
//        return $this->getCoverLetters();
//    }


    //School Name, Finish Date
    //City, Country Abbreviation such as USA (Full name of country if not available)
    public function getMedicalSchoolDescription() {
        $resArr = array();
        $name = $this->getSchoolByTrainingTypeName("Medical",true);
        $resArr[] = $name;
        return implode(", ",$resArr);
    }

    //AP, CP, AP/CP, other
    //Residency Institution, Finish Date
    //City, Country Abbreviation such as USA (Full name of country if abbreviation not available)
    public function getResidencyDescription() {
        $resArr = array();
        $name = $this->getSchoolByTrainingTypeName("Residency",true,true);
        $resArr[] = $name;
        return implode(", ",$resArr);
    }

    public function getInfo() {
        $info = $this->getId();

        $subjectUser = $this->getUser();
        if( $subjectUser ) {
            $info = $info . " " . $subjectUser->getFirstNameUppercase() . " " . $subjectUser->getLastNameUppercase();
        }

        return $info;
    }

    public function getApplicantFullName() {
        $subjectUser = $this->getUser();
        if( $subjectUser ) {
            return $subjectUser->getFirstNameUppercase() . " " . $subjectUser->getLastNameUppercase();
        }
        return "Unknown Applicant (Application ID " . $this->getId() . ")";
    }

    public function getFullId() {
        $fullId = NULL;
        $fullIdArr = array();
        if( $this->getGoogleFormId() ) {
            //return $this->getId() . '<br>(Old ID:' . $this->getGoogleFormId() . ')';
            $fullIdArr[] = "Old ID: " . $this->getGoogleFormId();
        }
        if( $this->getErasApplicantId() ) {
            $fullIdArr[] = "ERAS ID: " . $this->getErasApplicantId();
        }
        if( $this->getAamcId() ) {
            $fullIdArr[] = "AAMC ID: " . $this->getAamcId();
        }

        if( count($fullIdArr) > 0 ) {
            $fullId = "<br>" . implode("<br>",$fullIdArr);
        }

        return $this->getId() . $fullId;
    }

    public function autoSetRecLetterReceived()
    {
        foreach($this->getReferences() as $reference) {
            $reference->autoSetRecLetterReceived();
        }
    }

    public function hasAllCheckmarks() {
        foreach($this->getReferences() as $reference) {
            if( !$reference->getRecLetterReceived() ) {
                return false;
            }
        }
        return true;
    }

    public function hasAllCheckmarksOrReferenceLetters() {
        foreach($this->getReferences() as $reference) {
            if( !$reference->hasReferenceLetter() ) {
                return false;
            }
        }
        return true;
    }

    //Get Itinerary note for applications before migration to a new server in June 2020.
    //In old server Itinerary had been included in ERAS file.
    public function getItineraryNote() {
        //Itinerary (Only the last Itinerary will be added to the Complete Application PDF)
        //Itinerary (Only the last Itinerary will be added to the Complete Application PDF; For applicants who started in 2020 or earlier, the itinerary may be in the ERAS PDF)

        //$startDate = $this->getStartDate();
        //$startDate2020 = new \DateTime('2020-01-01');
        //if( $startDate &&  $startDate2020 < $startDate ) { //“Start Year” = “2020” or less

        //Migrated applications have googleFormId
        if( $this->getGoogleFormId() ) {
            $note = "Itinerary (Only the last Itinerary will be added to the Complete Application PDF; For applicants who started in 2020 or earlier, the itinerary may be in the ERAS PDF)";
        } else {
            $note = "Itinerary (Only the last Itinerary will be added to the Complete Application PDF)";
        }

        return $note;
    }

    public function getCalculatedAverageFit() {
        $count = 0;
        $countNa = 0;
        $totalFit = 0;
        foreach( $this->getInterviews() as $interview ) {
            $fit = $interview->getFitForProgram();
            if( $fit ) {
                $fitValue = $fit->getAbbreviation();

                //“; One ‘Do Not Rank' feedback received.” or “; More than one 'Do Not Rank’ feedback received”
                if( $fitValue == 4 ) {
                    $countNa++;
                }

                $totalFit = $totalFit + $fitValue;
                $count++;
            }
        }
        if( $count > 0 ) {
            $totalFit = $totalFit/$count;
            $totalFit = round($totalFit,2);
        }

        //A-1, B-2, C-3, “Do not rank”-4
        if( $totalFit == 0 ) {
            $totalFit = "N/A";
        }
        if( $totalFit == 1 ) {
            $totalFit = "A";
        }

        //1 - 2
        if( $totalFit > 1 && $totalFit < 1.5 ) {
            $totalFit = "A-";
        }
        if( $totalFit >= 1.5 && $totalFit < 2 ) {
            $totalFit = "B+";
        }

        if( $totalFit == 2 ) {
            $totalFit = "B";
        }

        //2 - 3
        if( $totalFit > 2 && $totalFit < 2.5 ) {
            $totalFit = "B-";
        }
        if( $totalFit >= 2.5 && $totalFit < 3 ) {
            $totalFit = "C+";
        }

        if( $totalFit == 3 ) {
            $totalFit = "C";
        }

        //3 - 4
        if( $totalFit > 3 && $totalFit < 3.5 ) { //3.5
            $totalFit = "C-";
        }
        if( $totalFit >= 3.5 && $totalFit < 4 ) {
            $totalFit = "D+";
        }

        if( $totalFit == 4 ) {
            $totalFit = "D (Do not rank)";
        }

        //“; One ‘Do Not Rank' feedback received.” or “; More than one 'Do Not Rank’ feedback received”
        if( $countNa > 0 ) {
            if( $countNa == 1 ) {
                $totalFit = $totalFit . "; One 'Do Not Rank' feedback received";
            }
            if( $countNa > 1 ) {
                $totalFit = $totalFit . "; More than one 'Do Not Rank' feedback received";
            }
        }

        //$entity->setInterviewScore($score);
        return $totalFit;
    }

    public function __toString() {
        return "ResidencyApplication";
    }

} 