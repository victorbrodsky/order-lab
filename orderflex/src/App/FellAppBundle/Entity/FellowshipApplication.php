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

namespace App\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Entity\BaseUserAttributes;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

#[ORM\Table(name: 'fellapp_fellowshipapplication')]
#[ORM\Entity]
class FellowshipApplication extends BaseUserAttributes {

    #[ORM\Column(type: 'string', nullable: true)]
    private $googleFormId;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User', inversedBy: 'fellowshipApplications', cascade: ['remove'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $user;

    #[ORM\Column(type: 'date', nullable: true)]
    private $startDate;

    #[ORM\Column(type: 'date', nullable: true)]
    private $endDate;

    //add fellowship specialty: add-fellowship-application-type -> $subspecialtyType->setInstitution($pathology);
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\FellowshipSubspecialty', cascade: ['persist'])]
    private $fellowshipSubspecialty;

    #[ORM\ManyToOne(targetEntity: GlobalFellowshipSpecialty::class, cascade: ['persist'])]
    private $globalFellowshipSpecialty;

    /**
     * This should be the link to WCMC's "Department of Pathology and Laboratory Medicine"
     */
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    private $institution;

    #[ORM\JoinTable(name: 'fellapp_fellapp_coverletter')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'coverletter_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $coverLetters;

    #[ORM\JoinTable(name: 'fellapp_fellapp_cv')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'cv_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $cvs;


    //Reprimands
    #[ORM\Column(type: 'string', nullable: true)]
    private $reprimand;

    #[ORM\JoinTable(name: 'fellapp_fellapp_reprimanddocument')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'reprimanddocument_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $reprimandDocuments;

    //Lawsuits
    #[ORM\Column(type: 'string', nullable: true)]
    private $lawsuit;

    #[ORM\JoinTable(name: 'fellapp_fellapp_lawsuitdocument')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'lawsuitdocument_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $lawsuitDocuments;

    #[ORM\OneToMany(targetEntity: 'Reference', mappedBy: 'fellapp', cascade: ['persist'])]
    private $references;


    #[ORM\Column(type: 'text', nullable: true)]
    private $honors;

    #[ORM\Column(type: 'text', nullable: true)]
    private $publications;

    #[ORM\Column(type: 'text', nullable: true)]
    private $memberships;

    #[ORM\Column(type: 'string', nullable: true)]
    private $signatureName;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $signatureDate;

    //Notes and comments
    #[ORM\Column(type: 'text', nullable: true)]
    private $notes;

    #[ORM\Column(type: 'string', nullable: true)]
    private $interviewScore;

//    /**
    //     * @ORM\Column(type="string", nullable=true)
    //     */
    //    private $applicationStatus;
    /**
     * application status as a list
     */
    #[ORM\ManyToOne(targetEntity: 'FellAppStatus')]
    private $appStatus;

    /**
     * timestamp when google form is opened
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $timestamp;


    #[ORM\JoinTable(name: 'fellapp_fellapp_report')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $reports;

    /**
     * Application PDF without attached documents: Will be automatically generated if left empty
     **/
    #[ORM\JoinTable(name: 'fellapp_fellapp_formreport')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'formreport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $formReports;

    /**
     * Manually Uploaded Application PDF without attachments
     **/
    #[ORM\JoinTable(name: 'fellapp_fellapp_manualreports')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'manualreport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $manualReports;

    #[ORM\JoinTable(name: 'fellapp_fellapp_oldreport')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'oldreport_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $oldReports;

    /**
     * Other Documents
     **/
    #[ORM\JoinTable(name: 'fellapp_fellapp_document')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $documents;

    /**
     * Itinerarys
     **/
    #[ORM\JoinTable(name: 'fellapp_fellapp_itinerary')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'itinerary_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $itinerarys;

    #[ORM\Column(type: 'date', nullable: true)]
    private $interviewDate;

    #[ORM\OneToMany(targetEntity: 'Interview', mappedBy: 'fellapp', cascade: ['persist', 'remove'])]
    private $interviews;

    #[ORM\JoinTable(name: 'fellapp_fellapp_observer')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'observer_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    private $observers;

    #[ORM\OneToOne(targetEntity: 'Rank', inversedBy: 'fellapp', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'rank_id', referencedColumnName: 'id', nullable: true)]
    private $rank;


    /////////// user objects /////////////
    //    /**
    //     * @ORM\ManyToMany(targetEntity="EmploymentStatus")
    //     * @ORM\JoinTable(name="fellapp_fellowshipapplication_employmentstatus",
    //     *      joinColumns={@ORM\JoinColumn(name="fellowshipapplication_id", referencedColumnName="id")},
    //     *      inverseJoinColumns={@ORM\JoinColumn(name="employmentstatus_id", referencedColumnName="id")}
    //     *      )
    //     **/
    //    private $employmentStatuses;
    /**
     * Other Documents
     **/
    #[ORM\JoinTable(name: 'fellapp_fellapp_avatar')]
    #[ORM\JoinColumn(name: 'fellapp_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'avatar_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $avatars;

    /**
     * //"completionDate" = "DESC", "orderinlist" = "ASC"
     **/
    #[ORM\JoinTable(name: 'fellapp_fellowshipapplication_training')]
    #[ORM\JoinColumn(name: 'fellowshipapplication_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'training_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Training', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['orderinlist' => 'ASC'])]
    private $trainings;

    #[ORM\JoinTable(name: 'fellapp_fellowshipapplication_examination')]
    #[ORM\JoinColumn(name: 'fellowshipapplication_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'examination_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Examination', cascade: ['persist', 'remove'])]
    private $examinations;

    #[ORM\JoinTable(name: 'fellapp_fellowshipapplication_location')]
    #[ORM\JoinColumn(name: 'fellowshipapplication_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'location_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Location', cascade: ['persist', 'remove'])]
    private $locations;

    #[ORM\JoinTable(name: 'fellapp_fellowshipapplication_citizenship')]
    #[ORM\JoinColumn(name: 'fellowshipapplication_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'citizenship_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Citizenship', cascade: ['persist', 'remove'])]
    private $citizenships;

    #[ORM\JoinTable(name: 'fellapp_fellowshipapplication_statelicense')]
    #[ORM\JoinColumn(name: 'fellowshipapplication_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'statelicense_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\StateLicense', cascade: ['persist', 'remove'])]
    private $stateLicenses;

    #[ORM\JoinTable(name: 'fellapp_fellowshipapplication_boardcertification')]
    #[ORM\JoinColumn(name: 'fellowshipapplication_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'boardcertification_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\BoardCertification', cascade: ['persist', 'remove'])]
    private $boardCertifications;

    /////////// EOF user objects /////////////




    public function __construct($author=null) {
        parent::__construct($author); //use only in $this->setAuthor($author);

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
//        if( !$fellowshipSubspecialty ) {
//            $this->fellowshipSubspecialty = $fellowshipSubspecialty;
//        }
    }

    /**
     * @return mixed
     */
    public function getFellowshipSubspecialty()
    {
        return $this->fellowshipSubspecialty;

//        $fellappType = $this->fellowshipSubspecialty;
//        if( !$fellappType ) {
//            $fellappType = $this->getGlobalFellowshipSpecialty();
//        }
//        return $fellappType;
    }

    /**
     * @return mixed
     */
    public function getGlobalFellowshipSpecialty()
    {
        return $this->globalFellowshipSpecialty;
    }

    /**
     * @param mixed $globalFellowshipSpecialty
     */
    public function setGlobalFellowshipSpecialty($globalFellowshipSpecialty)
    {
        $this->globalFellowshipSpecialty = $globalFellowshipSpecialty;
//        if( !$globalFellowshipSpecialty ) {
//            $this->globalFellowshipSpecialty = $globalFellowshipSpecialty;
//        }
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
            $item->setFellapp($this);
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
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
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

    public function getEcfmgDocs() {
        $ecfmgDocs = new ArrayCollection();
        foreach( $this->getExaminations() as $examination ) {
            foreach( $examination->getEcfmgDocs() as $ecfmgDoc ) {
                if( $ecfmgDoc && !$ecfmgDocs->contains($ecfmgDoc) ) {
                    $ecfmgDocs->add($ecfmgDoc);
                }
            }
        }
        return $ecfmgDocs;
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
    
    //$trainingTypeName: Medical, Residency, GME, Post-Residency Fellowship
    public function getSchoolByTrainingTypeName(
        $trainingTypeName,
        $withGeoLocation=false,
        $withResidencySpecialty=false,
        $separator='<br>',
        $withAt=true
    ) {
        $schoolName = "";

        foreach( $this->getTrainings() as $item ) {
            if( $item->getTrainingType() && $item->getTrainingType()->getName() == $trainingTypeName ) {

                $schoolName = "";

                //AP, CP, AP/CP, other or Area of Training
                if( $withResidencySpecialty && $item->getResidencySpecialty() ) {
                    //$schoolName = $schoolName . $item->getResidencySpecialty();
                    if( $withAt ) {
                        $schoolName = $item->getResidencySpecialty() . " at " . $schoolName;
                    } else {
                        $schoolName = $item->getResidencySpecialty() . " " . $schoolName;
                    }
                }
                if( $withResidencySpecialty && $item->getMajors() ) {
                    $majorArr = array();
                    foreach( $item->getMajors() as $major ) {
                        $major = strtolower($major);
                        $major = ucwords($major);
                        $majorArr[] = $major."";
                    }
                    if( $schoolName && count($majorArr)>0 ) {
                        $schoolName = $schoolName . "; ";
                    }
                    //$schoolName = $schoolName . implode(", ",$majorArr);
                    $majorStr = implode(", ",$majorArr);
                    if( trim($majorStr) ) {
                        if( $withAt ) {
                            $schoolName = trim($majorStr) . " at " . $schoolName;
                        } else {
                            $schoolName = trim($majorStr) . " " . $schoolName;
                        }
                    }
                }

                //Institution
                if( $item->getInstitution() ) {
                    //$separator = "";
                    if( !$schoolName ) {
                        $separator = "";
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
//        if( strlen(preg_replace('![^A-Z]+!', '', $s)) == strlen((string)$s) ) {
//            $s = ucfirst(strtolower($s));
//        }
//        return $s;
//    }
    public function capitalizeIfNotAllCapital($s) {
        if( !$s ) {
            return $s;
        }

        // Only apply if length > 4
        if (strlen($s) <= 4) {
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
        return $s."";
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
                !$examination->getUSMLEStep1Score(true) &&
                !$examination->getUSMLEStep2CKScore(true) &&
                !$examination->getUSMLEStep3Score(true)    
            ) {
                continue;
            }
            
            if( $examination->getUSMLEStep1Score(true) ) {
                $usmleArr[] = $examination->getUSMLEStep1Score(true);
            } else {
                $usmleArr[] = "-";
            }
            if( $examination->getUSMLEStep2CKScore(true) ) {
                $usmleArr[] = $examination->getUSMLEStep2CKScore(true);
            } else {
                $usmleArr[] = "-";
            }
            if( $examination->getUSMLEStep3Score(true) ) {
                $usmleArr[] = $examination->getUSMLEStep3Score(true);
            } else {
                $usmleArr[] = "-";
            }
        }
        //print_r($usmleArr);
        
        return $usmleArr;
    }
//    public function getUsmleSum() {
//        $scoreSum = 0;
//        foreach($this->getUsmleArr() as $score) {
//            if( $score && $score != '-' ) {
//                $score = intval($score);
//                if( $score > 0 && $score <= 300 ) {
//                    $scoreSum = $scoreSum + $score;
//                }
//            }
//        }
//        return $scoreSum;
//    }
    public function getUsmleAverage() {
        $scoreAverage = NULL;
        $scoreSum = 0;
        $counter = 0;
        foreach($this->getUsmleArr() as $score) {
            if( $score && $score != '-' && is_numeric($score) ) {
                $score = intval($score);
                //if( $score > 0 && $score <= 300 ) {
                //if( $this->isUsmleValid($score) ) {
                    $scoreSum = $scoreSum + $score;
                    $counter++;
                //}
            }
        }
        if( $counter > 0 ) {
            $scoreAverage = round($scoreSum / $counter);
        }
        return $scoreAverage;
    }

    public function getAllUsmleArr() {

        $examination = NULL;
        $usmleArr = array();

        //we should have only 1 examination per applicant
        $examinations = $this->getExaminations();
        if( count($examinations) > 0 ) {
            $examination = $examinations[0];
        }

        if( !$examination ) {
            return $usmleArr;
        }

        $score1 = NULL;
        $score2 = NULL;
        $score3 = NULL;

        //if( $this->isUsmleValid($examination->getUSMLEStep1Score(true)) ) {
            $score1 = $examination->getUSMLEStep1Score(true);
        //}
        //if( $this->isUsmleValid($examination->getUSMLEStep2CKScore(true)) ) {
            $score2 = $examination->getUSMLEStep2CKScore(true);
        //}
        //if( $this->isUsmleValid($examination->getUSMLEStep3Score(true)) ) {
            $score3 = $examination->getUSMLEStep3Score(true);
        //}

        if(
            !$score1 &&
            !$score2 &&
            !$score3
        ) {
            return $usmleArr;
        }

        $usmleArr[1] = $score1;
        $usmleArr[2] = $score2;
        $usmleArr[3] = $score3;

        return $usmleArr;
    }
    
    public function getComlexArr() {
        $comlexArr = array();
        
        foreach( $this->getExaminations() as $examination ) {
            
            if( 
                !$examination->getCOMLEXLevel1Score(true) &&
                !$examination->getCOMLEXLevel2Score(true) &&
                !$examination->getCOMLEXLevel3Score(true)
            ) {
                continue;
            }
            
            if( $examination->getCOMLEXLevel1Score(true) ) {
                $comlexArr[] = $examination->getCOMLEXLevel1Score(true);
            } else {
                $comlexArr[] = "-";
            }
            if( $examination->getCOMLEXLevel2Score(true) ) {
                $comlexArr[] = $examination->getCOMLEXLevel2Score(true);
            } else {
                $comlexArr[] = "-";
            }
            if( $examination->getCOMLEXLevel3Score(true) ) {
                $comlexArr[] = $examination->getCOMLEXLevel3Score(true);
            } else {
                $comlexArr[] = "-";
            }
        }
        //print_r($comlexArr);
        
        return $comlexArr;
    }

    public function getAllComlexArr() {

        $examination = NULL;
        $comlexArr = array();

        //we should have only 1 examination per applicant
        $examinations = $this->getExaminations();
        if( count($examinations) > 0 ) {
            $examination = $examinations[0];
        }

        if( !$examination ) {
            return $comlexArr;
        }

        $score1 = NULL;
        $score2 = NULL;
        $score3 = NULL;

        //if( $this->isComlexValid($examination->getCOMLEXLevel1Score(true)) ) {
            $score1 = $examination->getCOMLEXLevel1Score(true);
        //}
        //if( $this->isComlexValid($examination->getCOMLEXLevel2Score(true)) ) {
            $score2 = $examination->getCOMLEXLevel2Score(true);
            //echo "getAllComlexArr score2=$score2<br>";
        //}
        //if( $this->isComlexValid($examination->getCOMLEXLevel3Score(true)) ) {
            $score3 = $examination->getCOMLEXLevel3Score(true);
        //}

        if(
            !$score1 &&
            !$score2 &&
            !$score3
        ) {
            return $comlexArr;
        }

        $comlexArr[1] = $score1;
        $comlexArr[2] = $score2;
        $comlexArr[3] = $score3;

        return $comlexArr;
    }

//    public function isComlexValid( $score ) {
//        //upper limit of 1000 and a lower limit of 8
//        if( is_numeric($score) && $score !== NULL && $score >= 8 && $score <= 1000 )
//        {
//            return true;
//        }
//        return false;
//    }

//    public function isUsmleValid( $score ) {
//        //https://en.wikipedia.org/wiki/USMLE_Step_1
//        //Prior to January 2022, Step 1 scoring is a three-digit score, theoretically ranging from 1 to 300
//        if( is_numeric($score) && $score !== NULL && $score >= 1 && $score <= 300 ) {
//            return true;
//        }
//        return false;
//    }
    
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
    public function getMedicalSchoolDescription($withAt=false) {
        $resArr = array();
        //$name = $this->getSchoolByTrainingTypeName("Medical",true);
        $name = $this->getSchoolByTrainingTypeName("Medical",true,false,'<br>',$withAt);
        $resArr[] = $name;
        return implode(", ",$resArr);
    }

    //AP, CP, AP/CP, other
    //Residency Institution, Finish Date
    //City, Country Abbreviation such as USA (Full name of country if abbreviation not available)
    public function getResidencyDescription($withAt=false) {
        $resArr = array();
        $name = $this->getSchoolByTrainingTypeName("Residency",true,true,'<br>',$withAt);
        $resArr[] = $name;
        return implode(", ",$resArr);
    }

    //Area of training
    //GME Institution, Finish Date
    //City, Country Abbreviation such as USA (Full name of country if abbreviation not available)
    public function getPostResidencyFellowshipDescription($withAt=false) {
        $resArr = array();
        $name = $this->getSchoolByTrainingTypeName("Post-Residency Fellowship",true,true,'<br>',$withAt);
        $resArr[] = $name;
        $description = implode(", ",$resArr);
        return $description;
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

    public function getStartYear() {
        $startDate = $this->getStartDate();
        if( $startDate ) {
            return $startDate->format('Y');
        }
        return NULL;
    }

    public function isInterviewed() {
        //definition of the not interviewed applications
        //interviewed means she sets the interview date and then sends interview evaluation emails.
        //The simplest answer if "not interviewed" would be any applicant that if all those are true:
        // (a) was never set to the “Interviewee” status AND
        // (b) does not have any interview feedback AND
        // (c) does not have an interview date field value AND

        // (a) was never set to the “Interviewee” status
        $fellAppStatusEntity = $this->getAppStatus();
        if( $fellAppStatusEntity ) {
            if( $fellAppStatusEntity->getName() == 'interviewee' ) {
                return true;
            }
            if( $fellAppStatusEntity->getName() == 'priorityinterviewee' ) {
                return true;
            }
        }

        // (b) does not have any interview feedback
        // (c) does not have an interview date field value
        foreach( $this->getInterviews() as $interview ) {

            // (b) does not have any interview feedback
            if( $interview->isEmpty() === false ) {
                return true;
            }

            if( $interview->getComment() ) {
                return true;
            }

            // (c) does not have an interview date field value
            //getInterviewDate
            if( $interview->getInterviewDate() ) {
                return true;
            }

        }

        return false;
    }

    public function __toString() {
        return "FellowshipApplication";
    }

} 