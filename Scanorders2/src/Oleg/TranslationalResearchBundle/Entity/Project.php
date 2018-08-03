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

namespace Oleg\TranslationalResearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="transres_project")
 * @ORM\HasLifecycleCallbacks
 */
class Project {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="exportId", type="integer", nullable=true)
     */
    private $exportId;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updateUser", referencedColumnName="id", nullable=true)
     */
    private $updateUser;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $importDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * Institutional PHI Scope: users with the same Institutional PHI Scope can view the data of this order
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $oid;

    /**
     * Hematopathology or AP/CP
     *
     * @ORM\ManyToOne(targetEntity="Oleg\TranslationalResearchBundle\Entity\SpecialtyList", cascade={"persist"})
     */
    private $projectSpecialty;

    /**
     * MessageCategory with subcategory (parent-children hierarchy)
     *
     * @ORM\ManyToOne(targetEntity="Oleg\OrderformBundle\Entity\MessageCategory", cascade={"persist"})
     */
    private $messageCategory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $version;

//    /**
//     * @ORM\OneToMany(targetEntity="FormVersion", mappedBy="message", cascade={"persist","remove"})
//     */
//    private $formVersions;

    /**
     * State of the project (state machine variable)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $state;

    // Project fields
    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_principalinvestigator",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="principalinvestigator_id", referencedColumnName="id")}
     * )
     **/
    private $principalInvestigators;

//    /**
//     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
//     * @ORM\JoinTable(name="transres_project_principalirbinvestigator",
//     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="principalirbinvestigator_id", referencedColumnName="id")}
//     * )
//     **/
//    private $principalIrbInvestigators;
    /**
     * Principal Investigator listed on the IRB application
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $principalIrbInvestigator;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_coinvestigator",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="coinvestigator_id", referencedColumnName="id")}
     * )
     **/
    private $coInvestigators;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_pathologist",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathologist_id", referencedColumnName="id")}
     * )
     **/
    private $pathologists;

    /**
     * Project's "Contact" filed is pre-populated with the current user (Submitter)
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_contact",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id")}
     * )
     **/
    private $contacts;
    
    /**
     * user who will process the billing invoice (who will pay) for this PI's project
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $billingContact;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvalDate;

    //IrbReviews (one-to-many, but only one review is valid)
    /**
     * @ORM\OneToMany(targetEntity="IrbReview", mappedBy="project", cascade={"persist","remove"})
     */
    private $irbReviews;

    //AdminReviews (one-to-many, but only one review is valid)
    /**
     * @ORM\OneToMany(targetEntity="AdminReview", mappedBy="project", cascade={"persist","remove"})
     */
    private $adminReviews;

    //CommitteeReviews (one-to-many)
    /**
     * @ORM\OneToMany(targetEntity="CommitteeReview", mappedBy="project", cascade={"persist","remove"})
     */
    private $committeeReviews;

    //FinalReviews (one-to-many, but only one review is valid)
    /**
     * @ORM\OneToMany(targetEntity="FinalReview", mappedBy="project", cascade={"persist","remove"})
     */
    private $finalReviews;

    /**
     * Project Documents
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_project_document",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $documents;

    /**
     * IRB Approval Letter
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_project_irbApprovalLetter",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="irbApprovalLetters_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $irbApprovalLetters;

    /**
     * Human Tissue Form
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_project_humanTissueForm",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="humanTissueForm_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $humanTissueForms;

    /**
     * @ORM\OneToMany(targetEntity="TransResRequest", mappedBy="project", cascade={"persist"})
     */
    private $requests;

    /**
     * Will this project involve human tissue?
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $involveHumanTissue;

    /////////// Project fields /////////////
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $title;

    /**
     * IRB Expiration Date: copied from the project's formnode field on create and update
     * @ORM\Column(type="date", nullable=true)
     */
    private $irbExpirationDate;

    /**
     * fundedAccountNumber: copied from the project's formnode field on create and update
     * @ORM\Column(type="string", nullable=true)
     */
    private $fundedAccountNumber;

    //added later
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $irbNumber;

    /**
     * @ORM\ManyToOne(targetEntity="ProjectTypeList")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $projectType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $funded;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $budgetSummary;

    /**
     * integer only
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $totalCost;

    /////////// EOF Project fields /////////////

    /**
     * Is this project exempt from IRB approval?
     *
     * @ORM\ManyToOne(targetEntity="IrbApprovalTypeList")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $exemptIrbApproval;

    /**
     * Is this project exempt from IACUC approval?
     *
     * @ORM\ManyToOne(targetEntity="IrbApprovalTypeList")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $exemptIACUCApproval;

    /**
     * IACUC Expiration Date
     * @ORM\Column(type="date", nullable=true)
     */
    private $iacucExpirationDate;

    //added later
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $iacucNumber;


    public function __construct($user=null) {

        $this->principalInvestigators = new ArrayCollection();
        //$this->principalIrbInvestigators = new ArrayCollection();
        $this->coInvestigators = new ArrayCollection();
        $this->pathologists = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        //$this->billingContacts = new ArrayCollection();

        $this->irbReviews = new ArrayCollection();
        $this->adminReviews = new ArrayCollection();
        $this->committeeReviews = new ArrayCollection();
        $this->finalReviews = new ArrayCollection();

        $this->documents = new ArrayCollection();
        $this->irbApprovalLetters = new ArrayCollection();
        $this->humanTissueForms = new ArrayCollection();

        $this->requests = new ArrayCollection();

        //$this->formVersions = new ArrayCollection();

        $this->setSubmitter($user);
        $this->addContact($user);
        $this->setState('draft');
        $this->setCreateDate(new \DateTime());
    }



    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getExportId()
    {
        return $this->exportId;
    }

    /**
     * @param mixed $exportId
     */
    public function setExportId($exportId)
    {
        $this->exportId = $exportId;
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


    /**
     * @return mixed
     */
    public function getUpdateUser()
    {
        return $this->updateUser;
    }

    /**
     * @param mixed $updateUser
     */
    public function setUpdateUser($updateUser)
    {
        $this->updateUser = $updateUser;
    }

    /**
     * @return DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param DateTime $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return \DateTime
     */
    public function getImportDate()
    {
        return $this->importDate;
    }

    /**
     * @param \DateTime $importDate
     */
    public function setImportDate($importDate)
    {
        $this->importDate = $importDate;
    }

    /**
     * @return DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdateDate()
    {
        $this->updateDate = new \DateTime();
    }
//    public function setUpdateDate( $date=null )
//    {
//        if( $date ) {
//            $this->updateDate = $date;
//        } else {
//            $this->updateDate = new \DateTime();
//        }
//    }


    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getIrbNumber()
    {
        return $this->irbNumber;
    }

    /**
     * @param mixed $irbNumber
     */
    public function setIrbNumber($irbNumber)
    {
        $this->irbNumber = $irbNumber;
    }

    /**
     * @return mixed
     */
    public function getProjectType()
    {
        return $this->projectType;
    }

    /**
     * @param mixed $projectType
     */
    public function setProjectType($projectType)
    {
        $this->projectType = $projectType;
    }

    /**
     * @return mixed
     */
    public function getFunded()
    {
        return $this->funded;
    }

    /**
     * @param mixed $funded
     */
    public function setFunded($funded)
    {
        $this->funded = $funded;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getBudgetSummary()
    {
        return $this->budgetSummary;
    }

    /**
     * @param mixed $budgetSummary
     */
    public function setBudgetSummary($budgetSummary)
    {
        $this->budgetSummary = $budgetSummary;
    }

    /**
     * @return mixed
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @param mixed $totalCost
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
    }


    public function getPrincipalInvestigators()
    {
        return $this->principalInvestigators;
    }
    public function addPrincipalInvestigator($item)
    {
        if( $item && !$this->principalInvestigators->contains($item) ) {
            $this->principalInvestigators->add($item);
        }
        return $this;
    }
    public function removePrincipalInvestigator($item)
    {
        $this->principalInvestigators->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getPrincipalIrbInvestigator()
    {
        return $this->principalIrbInvestigator;
    }

    /**
     * @param mixed $principalIrbInvestigator
     */
    public function setPrincipalIrbInvestigator($principalIrbInvestigator)
    {
        $this->principalIrbInvestigator = $principalIrbInvestigator;
    }
    public function getPrincipalIrbInvestigators()
    {
        $principalIrbInvestigators = new ArrayCollection();
        $principalIrbInvestigators->add($this->principalIrbInvestigator);
        return $principalIrbInvestigators;
    }

//    public function getPrincipalIrbInvestigators()
//    {
//        return $this->principalIrbInvestigators;
//    }
//    public function addPrincipalIrbInvestigator($item)
//    {
//        if( $item && !$this->principalIrbInvestigators->contains($item) ) {
//            $this->principalIrbInvestigators->add($item);
//        }
//        return $this;
//    }
//    public function removePrincipalIrbInvestigator($item)
//    {
//        $this->principalIrbInvestigators->removeElement($item);
//    }


    public function getCoInvestigators()
    {
        return $this->coInvestigators;
    }
    public function addCoInvestigator($item)
    {
        if( $item && !$this->coInvestigators->contains($item) ) {
            $this->coInvestigators->add($item);
        }
        return $this;
    }
    public function removeCoInvestigator($item)
    {
        $this->coInvestigators->removeElement($item);
    }

    public function getPathologists()
    {
        return $this->pathologists;
    }
    public function addPathologist($item)
    {
        if( $item && !$this->pathologists->contains($item) ) {
            $this->pathologists->add($item);
        }
        return $this;
    }
    public function removePathologist($item)
    {
        $this->pathologists->removeElement($item);
    }

    public function getContacts()
    {
        return $this->contacts;
    }
    public function addContact($item)
    {
        if( $item && !$this->contacts->contains($item) ) {
            $this->contacts->add($item);
        }
        return $this;
    }
    public function removeContact($item)
    {
        $this->contacts->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getBillingContact()
    {
        return $this->billingContact;
    }
    public function getBillingContacts()
    {
        //return array($this->billingContact);
        $billingContacts = new ArrayCollection();
        $billingContacts->add($this->billingContact);
        return $billingContacts;
    }
    /**
     * @param mixed $billingContact
     */
    public function setBillingContact($billingContact)
    {
        $this->billingContact = $billingContact;
    }

    //billingContacts
//    public function getBillingContacts()
//    {
//        return $this->billingContacts;
//    }
//    public function addBillingContact($item)
//    {
//        if( $item && !$this->billingContacts->contains($item) ) {
//            $this->billingContacts->add($item);
//        }
//        return $this;
//    }
//    public function removeBillingContact($item)
//    {
//        $this->billingContacts->removeElement($item);
//    }

    /**
     * @return mixed
     */
    public function getApprovalDate()
    {
        return $this->approvalDate;
    }

    /**
     * @param mixed $approvalDate
     */
    public function setApprovalDate($approvalDate)
    {
        $this->approvalDate = $approvalDate;
    }

    /**
     * @return mixed
     */
    public function getIrbExpirationDate()
    {
        return $this->irbExpirationDate;
    }

    /**
     * @param mixed $irbExpirationDate
     */
    public function setIrbExpirationDate($irbExpirationDate)
    {
        $this->irbExpirationDate = $irbExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getFundedAccountNumber()
    {
        return $this->fundedAccountNumber;
    }

    /**
     * @param mixed $fundedAccountNumber
     */
    public function setFundedAccountNumber($fundedAccountNumber)
    {
        $this->fundedAccountNumber = $fundedAccountNumber;
    }

    /**
     * @return mixed
     */
    public function getInvolveHumanTissue()
    {
        return $this->involveHumanTissue;
    }

    /**
     * @param mixed $involveHumanTissue
     */
    public function setInvolveHumanTissue($involveHumanTissue)
    {
        $this->involveHumanTissue = $involveHumanTissue;
    }


    public function getIrbReviews()
    {
        return $this->irbReviews;
    }
    public function addIrbReview($item)
    {
        if( $item && !$this->irbReviews->contains($item) ) {
            $this->irbReviews->add($item);
            $item->setProject($this);
        }
        return $this;
    }
    public function removeIrbReview($item)
    {
        $this->irbReviews->removeElement($item);
    }

    public function getCommitteeReviews()
    {
        return $this->committeeReviews;
    }
    public function addCommitteeReview($item)
    {
        if( $item && !$this->committeeReviews->contains($item) ) {
            $this->committeeReviews->add($item);
            $item->setProject($this);
        }
        return $this;
    }
    public function removeCommitteeReview($item)
    {
        $this->committeeReviews->removeElement($item);
    }

    public function getFinalReviews()
    {
        return $this->finalReviews;
    }
    public function addFinalReview($item)
    {
        if( $item && !$this->finalReviews->contains($item) ) {
            $this->finalReviews->add($item);
            $item->setProject($this);
        }
        return $this;
    }
    public function removeFinalReview($item)
    {
        $this->finalReviews->removeElement($item);
    }

    public function getAdminReviews()
    {
        return $this->adminReviews;
    }
    public function addAdminReview($item)
    {
        if( $item && !$this->adminReviews->contains($item) ) {
            $this->adminReviews->add($item);
            $item->setProject($this);
        }
        return $this;
    }
    public function removeAdminReview($item)
    {
        $this->adminReviews->removeElement($item);
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

    public function addIrbApprovalLetter($item)
    {
        if( $item && !$this->irbApprovalLetters->contains($item) ) {
            $this->irbApprovalLetters->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeIrbApprovalLetter($item)
    {
        $this->irbApprovalLetters->removeElement($item);
        $item->clearUseObject();
    }
    public function getIrbApprovalLetters()
    {
        return $this->irbApprovalLetters;
    }

    public function addHumanTissueForm($item)
    {
        if( $item && !$this->humanTissueForms->contains($item) ) {
            $this->humanTissueForms->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeHumanTissueForm($item)
    {
        $this->humanTissueForms->removeElement($item);
        $item->clearUseObject();
    }
    public function getHumanTissueForms()
    {
        return $this->humanTissueForms;
    }

    public function getRequests()
    {
        return $this->requests;
    }
    public function addRequest($item)
    {
        if( $item && !$this->requests->contains($item) ) {
            $this->requests->add($item);
            $item->setProject($this);
        }
        return $this;
    }
    public function removeRequest($item)
    {
        $this->requests->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getMessageCategory()
    {
        return $this->messageCategory;
    }

    /**
     * @param mixed $messageCategory
     */
    public function setMessageCategory($messageCategory)
    {
        $this->messageCategory = $messageCategory;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return string
     */
    public function getOid($withExportId=true)
    {
        if( $this->getExportId() && $withExportId ) {
            return $this->oid . " (".$this->getExportId().")";
        }
        return $this->oid;
    }

    /**
     * @param string $oid
     */
    public function setOid($oid)
    {
        $this->oid = $oid;
    }

    /**
     * @return mixed
     */
    public function getProjectSpecialty()
    {
        return $this->projectSpecialty;
    }

    /**
     * @param mixed $projectSpecialty
     */
    public function setProjectSpecialty($projectSpecialty)
    {
        $this->projectSpecialty = $projectSpecialty;
    }

    /**
     * @return mixed
     */
    public function getExemptIrbApproval()
    {
        return $this->exemptIrbApproval;
    }

    /**
     * @param mixed $exemptIrbApproval
     */
    public function setExemptIrbApproval($exemptIrbApproval)
    {
        $this->exemptIrbApproval = $exemptIrbApproval;
    }

    /**
     * @return mixed
     */
    public function getExemptIACUCApproval()
    {
        return $this->exemptIACUCApproval;
    }

    /**
     * @param mixed $exemptIACUCApproval
     */
    public function setExemptIACUCApproval($exemptIACUCApproval)
    {
        $this->exemptIACUCApproval = $exemptIACUCApproval;
    }

    /**
     * @return mixed
     */
    public function getIacucExpirationDate()
    {
        return $this->iacucExpirationDate;
    }

    /**
     * @param mixed $iacucExpirationDate
     */
    public function setIacucExpirationDate($iacucExpirationDate)
    {
        $this->iacucExpirationDate = $iacucExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getIacucNumber()
    {
        return $this->iacucNumber;
    }

    /**
     * @param mixed $iacucNumber
     */
    public function setIacucNumber($iacucNumber)
    {
        $this->iacucNumber = $iacucNumber;
    }


    //show the name of the form (from the form hierarchy) that was used to generate this submitted message.
    // Make sure to save this form ID of the form linked from the Message Type at the time of message submission
    public function getMessageTitleStr()
    {
        $title = "";
        if( $this->getMessageCategory() ) {
            $title = $this->getMessageCategory()->getNodeNameWithParent() . " (ID " . $this->getMessageCategory()->getId() . ")";
        }

        return $title;
    }

    public function isEditable() {
        return true;
    }


    /**
     * "HPID" or "APCPID"
     * @param string $oid
     */
    public function generateOid()
    {
        $projectSpecialty = $this->getProjectSpecialty();
        if( $projectSpecialty ) {
            $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();
            if( $projectSpecialtyAbbreviation == "hematopathology" ) {
                //$projectSpecialtyAbbreviation = "HEMEPATH";
                $projectSpecialtyAbbreviation = "HP";
            }
            $projectSpecialtyAbbreviation = str_replace("-","",$projectSpecialtyAbbreviation);
            $projectSpecialtyStr = strtoupper($projectSpecialtyAbbreviation);
        }
        $oid = $projectSpecialtyStr . $this->getId();
        //echo "oid=$oid <br>";
        $this->setOid($oid);
    }

    //Project ID - Project Title - Submitted by FirstName LastName on MM/DD/YYYY at HH:MM
    public function getProjectInfoName() {
        $createDateStr = null;
        if( $this->getCreateDate() ) {
            $createDateStr = " on " . $this->getCreateDate()->format('m/d/Y');
        }
        return "Project ID " . $this->getOid() . " - Submitted by ".$this->getSubmitter()->getUsernameOptimal() . $createDateStr; //. " at ".$this->getCreateDate()->format('H:i:s')
    }

    //used by select2. Limit by 15 chars
    public function getProjectInfoNameChoice() {
        return $this->getProjectInfoLimited(false);
    }
    public function getProjectInfoNameWithPIChoice() {
        return $this->getProjectInfoLimited(true);
    }
    public function getProjectInfoLimited($withpis=true) {
        //$info = $this->getProjectInfoName();
        //$info = $this->getOid() . " submitted on ".$this->getCreateDate()->format('m/d/Y'); //. " at ".$this->getCreateDate()->format('H:i:s')
        //$info = $this->getOid() . ", submitted on " . $this->getCreateDate()->format('m/d/Y');
        $info = $this->getOid();

        $title = $this->getTitle();
        if( $title ) {
            $limit = 20;
            if( strlen($title) > $limit ) {
                $title = substr($title, 0, $limit) . '...';
            }
            $info = $info . ", " . $title;
        }

        //This PI's info will add number of queries equal to the number of existing projects
        if($withpis) {
            $pis = $this->getPrincipalInvestigators();
            if (count($pis) > 0) {
                $pi = $pis[0];
                $piStr = ", PI " . $pi->getUsernameShortest();
                $info = $info . $piStr;
            }
        }

        if( $this->getCreateDate() ) {
            $info = $info . ", " . $this->getCreateDate()->format('m/d/Y');
        }

        $limit = 70;
        if( strlen($info) > $limit ) {
            $info = substr($info, 0, $limit) . '...';
        }

        return $info;
    }

    public function getEntityName() {
        return "Project";
    }

    public function __toString() {
        return "Project id=[".$this->getId()."]";
    }
}