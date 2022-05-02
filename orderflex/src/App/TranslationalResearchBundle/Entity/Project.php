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

namespace App\TranslationalResearchBundle\Entity;

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
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
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
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
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
     * @ORM\ManyToOne(targetEntity="App\TranslationalResearchBundle\Entity\SpecialtyList", cascade={"persist"})
     */
    private $projectSpecialty;

    /**
     * MessageCategory with subcategory (parent-children hierarchy)
     *
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\MessageCategory", cascade={"persist"})
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
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_principalinvestigator",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="principalinvestigator_id", referencedColumnName="id")}
     * )
     **/
    private $principalInvestigators;

//    /**
//     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User")
//     * @ORM\JoinTable(name="transres_project_principalirbinvestigator",
//     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="principalirbinvestigator_id", referencedColumnName="id")}
//     * )
//     **/
//    private $principalIrbInvestigators;
    /**
     * Principal Investigator listed on the IRB application
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $principalIrbInvestigator;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_coinvestigator",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="coinvestigator_id", referencedColumnName="id")}
     * )
     **/
    private $coInvestigators;

    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_pathologist",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathologist_id", referencedColumnName="id")}
     * )
     **/
    private $pathologists;

    /**
     * Project's "Contact" filed is pre-populated with the current user (Submitter)
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_contact",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id")}
     * )
     **/
    private $contacts;
    
    /**
     * user who will process the billing invoice (who will pay) for this PI's project
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $billingContact;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvalDate;

    /**
     * Date when a Project is submitted to Review
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startReviewDate;

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
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
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
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
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
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
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
     * integer only
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $totalCost;
    
    /**
     * Approved Project Budget
     *
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    private $approvedProjectBudget;

    /**
     * Total including Subsidy
     *
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    private $total;

    /**
     * No Budget Limit
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $noBudgetLimit;

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

    ///////////////// Hide 7 fields (from $budgetSummary to $expectedCompletionDate) ///////////////////
    //Hide fields: $budgetSummary, $hypothesis, $hypothesis, $objective, $numberOfCases, $numberOfCohorts, $expectedResults, $expectedCompletionDate
    //These fields will be replaced by a PDF form included to the project to the Project Documents section
    //$expectedCompletionDate will be set automatically to 1 year after project approval date, after that date the project will change the status to "closed"

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $budgetSummary;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $hypothesis;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $objective;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numberOfCases;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numberOfCohorts;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $expectedResults;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expectedCompletionDate;
    ///////////////// EOF Hide the fields (from $budgetSummary to $expectedCompletionDate) ///////////////////


    //Tissue Request Details
    /**
     * Will this project require tissue procurement/processing:
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $requireTissueProcessing;

    /**
     * Total number of patients:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalNumberOfPatientsProcessing;

    /**
     * Total number of patient cases:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalNumberOfSpecimensProcessing;

    /**
     * Number of blocks per case:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tissueNumberOfBlocksPerCase;

    /**
     * onDelete="CASCADE"
     * Tissue Processing Services: [v] Paraffin Block Processing [v] Fresh/Frozen Tissue Procurement [v] Frozen Tissue Storage
     *
     * @ORM\ManyToMany(targetEntity="TissueProcessingServiceList", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_project_tissueProcessingService",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tissueProcessingService_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $tissueProcessingServices;

    //Archival Specimens
    /**
     * Will this project require archival specimens:
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $requireArchivalProcessing;

    /**
     * Total number of patients
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalNumberOfPatientsArchival;

    /**
     * Total number of patient cases:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalNumberOfSpecimensArchival;

    /**
     * Total number of blocks per case:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalNumberOfBlocksPerCase;

    /**
     * Quantity of slides per block - stained:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantityOfSlidesPerBlockStained;

    /**
     * Quantity of slides per block - unstained:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantityOfSlidesPerBlockUnstained;

    /**
     * Quantity of slides per block - unstained for IHC:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantityOfSlidesPerBlockUnstainedIHC;

    /**
     * Quantity of special stains per block:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantityOfSpecialStainsPerBlock;

    /**
     * Quantity of paraffin sections for RNA/DNA (Tube) per block:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantityOfParaffinSectionsRnaDnaPerBlock;

    /**
     * Quantity of TMA cores for RNA/DNA analysis (Tube) per block:
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantityOfTmaCoresRnaDnaAnalysisPerBlock;

    /**
     * Other Requested Services: [v] Flow Cytometry [v] Immunohistochemistry [v] FISH [v] Tissue Microarray [v] Laser Capture Microdissection
     *
     * @ORM\ManyToMany(targetEntity="OtherRequestedServiceList", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_project_restrictedService",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="restrictedService_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $restrictedServices;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tissueFormComment;

    /**
     * Implicit Expiration Date: the EARLIEST IRB or IACUC date
     * @ORM\Column(type="date", nullable=true)
     */
    private $implicitExpirationDate;

    /**
     * Expected Expiration Date
     * @ORM\Column(type="date", nullable=true)
     */
    private $expectedExpirationDate;
    
    /**
     * Reason for status change or closure:
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    private $reasonForStatusChange;

    /**
     * Upcoming expiration notification state (Counter)
     * @ORM\Column(type="integer", nullable=true)
     */
    private $expirationNotifyCounter;
    /**
     * Upcoming expired notification state (Counter)
     * @ORM\Column(type="integer", nullable=true)
     */
    private $expiredNotifyCounter;
    /**
     * auto-closure (Counter)
     * This automatic status switch should only be done ONCE per project ID + Expiration Date value combination
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $autoClosureCounter;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $stateComment;

    /**
     * Utilize the following price list
     *
     * @ORM\ManyToOne(targetEntity="PriceTypeList")
     */
    private $priceList;

    //NOT USED
    //reminder email: identifier(state), reminderEmailDate
    // for each identifier $state - irb_review, admin_review, committee_review, final_review, irb_missinginfo, admin_missinginfo
//    /**
//     * @ORM\ManyToMany(targetEntity="ReminderEmail", cascade={"persist","remove"})
//     * @ORM\JoinTable(name="transres_project_reminderEmail",
//     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="reminderEmail_id", referencedColumnName="id", onDelete="CASCADE")}
//     *      )
//     * @ORM\OrderBy({"createdate" = "ASC"})
//     **/
//    private $reminderEmails;

    //////////////////// Project Closure/Reactivation ////////////////////
    //view page: show if not empty
    //edit page: always show
    //"Reason for project closure"
    //"Reason for project reactivation"
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $closureReason;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $reactivationReason;
    //edit page: show only to Platform Admin/Deputy Platform Admin (not TRP Admin)
    //"Target Status" - select of possible project statuses
    //"Target Status Requester" - select of users
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $targetState;
    /**
     * Target Status Requestor
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $targetStateRequester;
    //////////////////// EOF Project Closure/Reactivation ////////////////////



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

        $this->tissueProcessingServices = new ArrayCollection();
        $this->restrictedServices = new ArrayCollection();

        //$this->reminderEmails = new ArrayCollection();

        //$this->formVersions = new ArrayCollection();

        $this->setSubmitter($user);
        $this->addContact($user);
        $this->setState('draft');
        $this->setCreateDate(new \DateTime());

        //$this->setRequireTissueProcessing("Yes");
        //$this->setRequireArchivalProcessing("Yes");
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

    //@ORM\PreUpdate
    /**
     * @param \DateTime
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

        if( $state == "irb_review" ) { //&& !$this->getStartReviewDate()
            $this->setStartReviewDate(new \DateTime());
        }
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

//        $description = $this->description;
//        if( strpos((string)$description, 'Previously used in the project fields (currently hidden)') === false ) {
//            $mergeInfo = $this->mergeHiddenFields();
//            if( $mergeInfo ) {
//                $newline = "\n";
//                //$newline = "<br>";
//                $description = $description . $newline.$newline.
//                    "-------------------------------------------------- " .
//                    $newline .
//                    "Previously used in the project fields (currently hidden):" .
//                    $newline . $mergeInfo;
//            }
//        }
//        return $description;
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
        //return $this->totalCost;
        return $this->strToDecimal($this->totalCost);
    }

    /**
     * @param mixed $totalCost
     */
    public function setTotalCost($totalCost)
    {
        $totalCost = $this->strToDecimal($totalCost);
        $this->totalCost = $totalCost;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getNoBudgetLimit()
    {
        return $this->noBudgetLimit;
    }

    /**
     * @param mixed $noBudgetLimit
     */
    public function setNoBudgetLimit($noBudgetLimit)
    {
        $this->noBudgetLimit = $noBudgetLimit;
    }

    /**
     * @return mixed
     */
    public function getApprovedProjectBudget()
    {
        return $this->strToDecimal($this->approvedProjectBudget);
        //return $this->approvedProjectBudget;
    }

    /**
     * @param mixed $approvedProjectBudget
     */
    public function setApprovedProjectBudget($approvedProjectBudget)
    {
        $approvedProjectBudget = $this->strToDecimal($approvedProjectBudget);
        $this->approvedProjectBudget = $approvedProjectBudget;
    }

    //Used on the submitting a new project
    //approvedProjectBudget and projectType are not visible/editable on new page
    //Auto populate if these fields have not been edited (fields are NULL)
    public function autoPopulateApprovedProjectBudget()
    {
        //For all existing "Funded" projects:
        // approvedProjectBudget = NULL;
        // noBudgetLimit = true;

        if( $this->getFunded() ) {
            //Funded
            //For “Funded” project requests,
            // preset the “No Budget Limit” to checked by default,
            // AND do not populate “Approved Budget” from Estimated costs
            if( $this->getNoBudgetLimit() === NULL ) {
                $this->setNoBudgetLimit(true);
            }

            if( $this->getApprovedProjectBudget() === NULL ) { //not required, added for uniformity
                $this->setApprovedProjectBudget(NULL);
            }
            //echo $this->getId().": funded: noBudgetLimit=".$this->getNoBudgetLimitYesNo().", budget=".$this->getApprovedProjectBudget()." <br>";
        } else {
            //Non-Funded
            //For “Non-Funded” project requests,
            // preset the “No Budget limit” to Unchecked,
            // and DO populate the “approved budget” with a valid value from Estimated costs
            if( $this->getNoBudgetLimit() === NULL ) {
                $this->setNoBudgetLimit(false);
            }

            if( $this->getApprovedProjectBudget() === NULL ) {
                $totalCost = $this->getTotalCost();
                if ($totalCost) {
                    $totalCost = $this->strToDecimal($totalCost);
                    $this->setApprovedProjectBudget($totalCost);
                }
            }

            //echo $this->getId().": un-funded: noBudgetLimit=".$this->getNoBudgetLimitYesNo().", budget=".$this->getApprovedProjectBudget()." <br>";
        }

//        if( $this->approvedProjectBudget === NULL ) {
//            $totalCost = $this->getTotalCost();
//            if( $totalCost ) {
//                $this->setApprovedProjectBudget($totalCost);
//            }
//        }
    }
    public function getNoBudgetLimitYesNo() {
        if( $this->getNoBudgetLimit() === true ) {
            return "Yes";
        }
        if( $this->getNoBudgetLimit() === false ) {
            return "No";
        }
        return NULL;
    }

    public function toDecimal($number) {
//        if( !$number ) {
//            return $number;
//        }

        //return $this->strToDecimal($number);
        return number_format((float)$number, 2, '.', '');
    }
    public function toMoney($number) {
        return number_format((float)$number, 2, '.', ',');
    }
    //$1,160.98 => 1160.98
    public function strToDecimal($str) {
        //$str = "-$1,160.98"; //testing
        //$str = "$-1,160.98"; //testing
        //$str = "-$-1 ,160"; //testing
        //echo "str=$str<br>";
        if( $str ) {
            //$str = $this->toInt($str);
            $str = $this->getAmount($str);
            return number_format((float)$str, 2, '.', '');
        }

        return NULL;
    }
    //https://stackoverflow.com/questions/5139793/unformat-money-when-parsing-in-php
    function toInt($str)
    {
        return preg_replace("/([^0-9\\.])/i", "", $str);
    }
    //https://stackoverflow.com/questions/5139793/unformat-money-when-parsing-in-php
    public function getAmount($money)
    {
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

        $separatorsCountToBeErased = strlen((string)$cleanString) - strlen((string)$onlyNumbersString) - 1;

        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);

        return (float) str_replace(',', '.', $removedThousandSeparator);
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
    public function getStartReviewDate()
    {
        return $this->startReviewDate;
    }

    /**
     * @param mixed $startReviewDate
     */
    public function setStartReviewDate($startReviewDate)
    {
        $this->startReviewDate = $startReviewDate;
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
        $this->calculateAndSetImplicitExpirationDate();
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

    public function getAdminReviews( $filterByType=false )
    {
        $adminReviews = $this->adminReviews;

        if( $filterByType === true ) {

            $funded = $this->getFunded();
            $newAdminReviews = new ArrayCollection();
            //'funded', 'non-funded', 'all'/NULL/''


            foreach($adminReviews as $adminReview) {
                if( $this->isAdminReviewerByType($adminReview) ) {
                    $newAdminReviews->add($adminReview);
                }
            }

            return $newAdminReviews;
        }// if $filterByType

        return $adminReviews;
    }
    public function isAdminReviewerByType( $adminReview ) {

        if( $adminReview instanceof AdminReview ) {
            //continue
        } else {
            return true; //admin if $review is not AdminReview (???)
        }

        $funded = $this->getFunded();
        $reviewProjectType = $adminReview->getReviewProjectType();

        if( $reviewProjectType == 'all' || !$reviewProjectType ) {
            return true;
        }

        if( $funded ) {
            if( $reviewProjectType == 'funded' ) {
                return true;
            }
        } else {
            if( $reviewProjectType == 'non-funded' ) {
                return true;
            }
        }

        return false;
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
        $this->calculateAndSetImplicitExpirationDate();
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

    /**
     * @return mixed
     */
    public function getImplicitExpirationDate()
    {
        return $this->implicitExpirationDate;
    }

    /**
     * @param mixed $implicitExpirationDate
     */
    public function setImplicitExpirationDate($implicitExpirationDate)
    {
        $this->implicitExpirationDate = $implicitExpirationDate;
    }

    /**
     * @param mixed $implicitExpirationDate
     */
    public function calculateAndSetImplicitExpirationDate()
    {
        $earliestDate = null;
        $irb = false;
        $iacuc = false;
        $irbExpDate = $this->getIrbExpirationDate();
        $iacucExpDate = $this->getIacucExpirationDate();

        $irbApproval = $this->getExemptIrbApproval();
        if( !$irbApproval || ($irbApproval && $irbApproval->getName() == "Not Exempt") ) {
            //echo "irb true <br>";
            $irb = true;
        }
        $iacucApproval = $this->getExemptIACUCApproval();
        if( !$iacucApproval || ($iacucApproval && $iacucApproval->getName() == "Not Exempt") ) {
            //echo "iacuc true <br>";
            $iacuc = true;
        }

        if( $irb && $irbExpDate ) {
            $earliestDate = $irbExpDate;
        }
        if( $iacuc && $iacucExpDate ) {
            $earliestDate = $iacucExpDate;
        }
        if( $irb && $iacuc && $irbExpDate && $iacucExpDate ) {
            //get the EARLIEST date and copy to $implicitExpirationDate
            if( $iacucExpDate < $irbExpDate ) {
                if( $iacuc ) {
                    $earliestDate = $iacucExpDate;
                }
            } else {
                if( $irb ) {
                    $earliestDate = $irbExpDate;
                }
            }
        }

        if( $earliestDate ) {
            //echo "earliestDate=".$earliestDate->format('Y-m-d')."<br>";
            //exit("Changed");
            $this->setImplicitExpirationDate($earliestDate);
        }
        //exit("Not changed");

        return $earliestDate;
    }

    /**
     * @return mixed
     */
    public function getExpectedExpirationDate()
    {
        return $this->expectedExpirationDate;
    }

    /**
     * @param mixed $expectedExpirationDate
     */
    public function setExpectedExpirationDate($expectedExpirationDate)
    {

//        //update expiration date only once
//        if( $this->getExpectedExpirationDate() ) {
//            return false;
//        }

        //notification should only be sent once for a given combination of project id and Expiration date:
        //if expectedExpirationDate is updated => reset expired/expiring notify counter
        if( $expectedExpirationDate != $this->expectedExpirationDate ) {

            if( $this->getExpirationNotifyCounter() ) {
                $this->setExpirationNotifyCounter(0);
            }

            if( $this->getExpiredNotifyCounter() ) {
                $this->setExpiredNotifyCounter(0);
            }

            if( $this->getAutoClosureCounter() ) {
                $this->setAutoClosureCounter(0);
            }

            //Same for auto-closed counter:
            // This automatic status switch should only be done ONCE
            // per project ID + Expiration Date value combination,
            // meaning, if this Cron Job sets the project request to status “Closed”,
            // but the admin user sets it back to another status (not “Closed”) WITHOUT changing the Expiration date,
            // the system should not try to change it again

            //exit("updated exp date");
        }

        $this->expectedExpirationDate = $expectedExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getReasonForStatusChange()
    {
        return $this->reasonForStatusChange;
    }

    /**
     * @param mixed $reasonForStatusChange
     */
    public function setReasonForStatusChange($reasonForStatusChange)
    {
        $this->reasonForStatusChange = $reasonForStatusChange;
    }

    /**
     * @return mixed
     */
    public function getHypothesis()
    {
        return $this->hypothesis;
    }

    /**
     * @param mixed $hypothesis
     */
    public function setHypothesis($hypothesis)
    {
        $this->hypothesis = $hypothesis;
    }

    /**
     * @return mixed
     */
    public function getObjective()
    {
        return $this->objective;
    }

    /**
     * @param mixed $objective
     */
    public function setObjective($objective)
    {
        $this->objective = $objective;
    }

    /**
     * @return mixed
     */
    public function getNumberOfCases()
    {
        return $this->numberOfCases;
    }

    /**
     * @param mixed $numberOfCases
     */
    public function setNumberOfCases($numberOfCases)
    {
        $this->numberOfCases = $numberOfCases;
    }

    /**
     * @return mixed
     */
    public function getNumberOfCohorts()
    {
        return $this->numberOfCohorts;
    }

    /**
     * @param mixed $numberOfCohorts
     */
    public function setNumberOfCohorts($numberOfCohorts)
    {
        $this->numberOfCohorts = $numberOfCohorts;
    }

    /**
     * @return mixed
     */
    public function getExpectedResults()
    {
        return $this->expectedResults;
    }

    /**
     * @param mixed $expectedResults
     */
    public function setExpectedResults($expectedResults)
    {
        $this->expectedResults = $expectedResults;
    }

    /**
     * @return mixed
     */
    public function getExpectedCompletionDate()
    {
        return $this->expectedCompletionDate;
    }

    /**
     * @param mixed $expectedCompletionDate
     */
    public function setExpectedCompletionDate($expectedCompletionDate)
    {
        $this->expectedCompletionDate = $expectedCompletionDate;
    }


    public function getTissueProcessingServices()
    {
        return $this->tissueProcessingServices;
    }
    public function addTissueProcessingService($item)
    {
        if( $item && !$this->tissueProcessingServices->contains($item) ) {
            $this->tissueProcessingServices->add($item);
            //$item->setProject($this);
        }
        return $this;
    }
    public function removeTissueProcessingService($item)
    {
        $this->tissueProcessingServices->removeElement($item);
    }

    public function getRestrictedServices()
    {
        return $this->restrictedServices;
    }
    public function addRestrictedService($item)
    {
        if( $item && !$this->restrictedServices->contains($item) ) {
            $this->restrictedServices->add($item);
            //$item->setProject($this);
        }
        return $this;
    }
    public function removeRestrictedService($item)
    {
        $this->restrictedServices->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getRequireTissueProcessing()
    {
        return $this->requireTissueProcessing;
    }

    /**
     * @param mixed $requireTissueProcessing
     */
    public function setRequireTissueProcessing($requireTissueProcessing)
    {
        $this->requireTissueProcessing = $requireTissueProcessing;
    }

    /**
     * @return mixed
     */
    public function getTotalNumberOfPatientsProcessing()
    {
        return $this->totalNumberOfPatientsProcessing;
    }

    /**
     * @param mixed $totalNumberOfPatientsProcessing
     */
    public function setTotalNumberOfPatientsProcessing($totalNumberOfPatientsProcessing)
    {
        $this->totalNumberOfPatientsProcessing = $totalNumberOfPatientsProcessing;
    }

    /**
     * @return mixed
     */
    public function getTotalNumberOfSpecimensProcessing()
    {
        return $this->totalNumberOfSpecimensProcessing;
    }

    /**
     * @param mixed $totalNumberOfSpecimensProcessing
     */
    public function setTotalNumberOfSpecimensProcessing($totalNumberOfSpecimensProcessing)
    {
        $this->totalNumberOfSpecimensProcessing = $totalNumberOfSpecimensProcessing;
    }

    /**
     * @return mixed
     */
    public function getRequireArchivalProcessing()
    {
        return $this->requireArchivalProcessing;
    }

    /**
     * @param mixed $requireArchivalProcessing
     */
    public function setRequireArchivalProcessing($requireArchivalProcessing)
    {
        $this->requireArchivalProcessing = $requireArchivalProcessing;
    }

    /**
     * @return mixed
     */
    public function getTotalNumberOfPatientsArchival()
    {
        return $this->totalNumberOfPatientsArchival;
    }

    /**
     * @param mixed $totalNumberOfPatientsArchival
     */
    public function setTotalNumberOfPatientsArchival($totalNumberOfPatientsArchival)
    {
        $this->totalNumberOfPatientsArchival = $totalNumberOfPatientsArchival;
    }

    /**
     * @return mixed
     */
    public function getTotalNumberOfSpecimensArchival()
    {
        return $this->totalNumberOfSpecimensArchival;
    }

    /**
     * @param mixed $totalNumberOfSpecimensArchival
     */
    public function setTotalNumberOfSpecimensArchival($totalNumberOfSpecimensArchival)
    {
        $this->totalNumberOfSpecimensArchival = $totalNumberOfSpecimensArchival;
    }

    /**
     * @return mixed
     */
    public function getTotalNumberOfBlocksPerCase()
    {
        return $this->totalNumberOfBlocksPerCase;
    }

    /**
     * @param mixed $totalNumberOfBlocksPerCase
     */
    public function setTotalNumberOfBlocksPerCase($totalNumberOfBlocksPerCase)
    {
        $this->totalNumberOfBlocksPerCase = $totalNumberOfBlocksPerCase;
    }

    /**
     * @return mixed
     */
    public function getQuantityOfSlidesPerBlockStained()
    {
        return $this->quantityOfSlidesPerBlockStained;
    }

    /**
     * @param mixed $quantityOfSlidesPerBlockStained
     */
    public function setQuantityOfSlidesPerBlockStained($quantityOfSlidesPerBlockStained)
    {
        $this->quantityOfSlidesPerBlockStained = $quantityOfSlidesPerBlockStained;
    }

    /**
     * @return mixed
     */
    public function getQuantityOfSlidesPerBlockUnstained()
    {
        return $this->quantityOfSlidesPerBlockUnstained;
    }

    /**
     * @param mixed $quantityOfSlidesPerBlockUnstained
     */
    public function setQuantityOfSlidesPerBlockUnstained($quantityOfSlidesPerBlockUnstained)
    {
        $this->quantityOfSlidesPerBlockUnstained = $quantityOfSlidesPerBlockUnstained;
    }

    /**
     * @return mixed
     */
    public function getQuantityOfSlidesPerBlockUnstainedIHC()
    {
        return $this->quantityOfSlidesPerBlockUnstainedIHC;
    }

    /**
     * @param mixed $quantityOfSlidesPerBlockUnstainedIHC
     */
    public function setQuantityOfSlidesPerBlockUnstainedIHC($quantityOfSlidesPerBlockUnstainedIHC)
    {
        $this->quantityOfSlidesPerBlockUnstainedIHC = $quantityOfSlidesPerBlockUnstainedIHC;
    }

    /**
     * @return mixed
     */
    public function getQuantityOfSpecialStainsPerBlock()
    {
        return $this->quantityOfSpecialStainsPerBlock;
    }

    /**
     * @param mixed $quantityOfSpecialStainsPerBlock
     */
    public function setQuantityOfSpecialStainsPerBlock($quantityOfSpecialStainsPerBlock)
    {
        $this->quantityOfSpecialStainsPerBlock = $quantityOfSpecialStainsPerBlock;
    }

    /**
     * @return mixed
     */
    public function getQuantityOfParaffinSectionsRnaDnaPerBlock()
    {
        return $this->quantityOfParaffinSectionsRnaDnaPerBlock;
    }

    /**
     * @param mixed $quantityOfParaffinSectionsRnaDnaPerBlock
     */
    public function setQuantityOfParaffinSectionsRnaDnaPerBlock($quantityOfParaffinSectionsRnaDnaPerBlock)
    {
        $this->quantityOfParaffinSectionsRnaDnaPerBlock = $quantityOfParaffinSectionsRnaDnaPerBlock;
    }

    /**
     * @return mixed
     */
    public function getQuantityOfTmaCoresRnaDnaAnalysisPerBlock()
    {
        return $this->quantityOfTmaCoresRnaDnaAnalysisPerBlock;
    }

    /**
     * @param mixed $quantityOfTmaCoresRnaDnaAnalysisPerBlock
     */
    public function setQuantityOfTmaCoresRnaDnaAnalysisPerBlock($quantityOfTmaCoresRnaDnaAnalysisPerBlock)
    {
        $this->quantityOfTmaCoresRnaDnaAnalysisPerBlock = $quantityOfTmaCoresRnaDnaAnalysisPerBlock;
    }

    /**
     * @return mixed
     */
    public function getTissueFormComment()
    {
        return $this->tissueFormComment;
    }

    /**
     * @param mixed $tissueFormComment
     */
    public function setTissueFormComment($tissueFormComment)
    {
        $this->tissueFormComment = $tissueFormComment;
    }

    /**
     * @return mixed
     */
    public function getTissueNumberOfBlocksPerCase()
    {
        return $this->tissueNumberOfBlocksPerCase;
    }

    /**
     * @param mixed $tissueNumberOfBlocksPerCase
     */
    public function setTissueNumberOfBlocksPerCase($tissueNumberOfBlocksPerCase)
    {
        $this->tissueNumberOfBlocksPerCase = $tissueNumberOfBlocksPerCase;
    }

    /**
     * @return mixed
     */
    public function getStateComment()
    {
        return $this->stateComment;
    }

    /**
     * @param mixed $stateComment
     */
    public function setStateComment($stateComment)
    {
        $this->stateComment = $stateComment;
    }

    /**
     * @return mixed
     */
    public function getClosureReason()
    {
        return $this->closureReason;
    }

    /**
     * @param mixed $closureReason
     */
    public function setClosureReason($closureReason)
    {
        $this->closureReason = $closureReason;
    }
    /**
     * @param mixed $closureReason
     */
    public function updateClosureReason($closureReason,$user)
    {
        $oldClosureReason = $this->getClosureReason();

//        if( $oldClosureReason ) {
//            $oldClosureReason = $oldClosureReason . "\n\n";
//        }

        //user name on MM/DD/YYYY at HH:MM: This project is expired
        $date = new \DateTime();
        $closureReason = $user . " on " . $date->format('m/d/Y \a\t H:i') . ": " . $closureReason . "." . "\n\n";
        $updatedClosureReason = $closureReason . $oldClosureReason;

        $this->setClosureReason($updatedClosureReason);

        return $updatedClosureReason;
    }

    /**
     * @return mixed
     */
    public function getReactivationReason()
    {
        return $this->reactivationReason;
    }
    /**
     * @param mixed $reactivationReason
     */
    public function setReactivationReason($reactivationReason)
    {
        $this->reactivationReason = $reactivationReason;
    }
    public function updateReactivationReason($reactivationReason,$user)
    {
        $oldReactivationReason = $this->getReactivationReason();

        //user name on MM/DD/YYYY at HH:MM: This project is expired
        $date = new \DateTime();
        $reactivationReason = $user . " on " . $date->format('m/d/Y \a\t H:i') . ": " . $reactivationReason . "." . "\n\n";
        $updatedReactivationReason = $reactivationReason . $oldReactivationReason;

        $this->setReactivationReason($updatedReactivationReason);

        return $updatedReactivationReason;
    }

    public function getTitleAndReason() {
        $title = $this->getTitle();
        $title = "'".$title."'.";

        $state = $this->getState();

        if( $state == "closed" ) {
            $reason = $this->getClosureReason();
            if( $reason ) {
                $closedReason = "Closed as per " . $reason;
                $title = $title . " " . $closedReason;
            }
        }

        return $title;
    }

    /**
     * @return mixed
     */
    public function getTargetState()
    {
        return $this->targetState;
    }

    /**
     * @param mixed $targetState
     */
    public function setTargetState($targetState)
    {
        $this->targetState = $targetState;
    }

    /**
     * @return mixed
     */
    public function getTargetStateRequester()
    {
        return $this->targetStateRequester;
    }

    /**
     * @param mixed $targetStateRequester
     */
    public function setTargetStateRequester($targetStateRequester)
    {
        $this->targetStateRequester = $targetStateRequester;
    }

    /**
     * @return mixed
     */
    public function getExpirationNotifyCounter()
    {
        return $this->expirationNotifyCounter;
    }

    /**
     * @param mixed $expirationNotifyCounter
     */
    public function setExpirationNotifyCounter($expirationNotifyCounter)
    {
        $this->expirationNotifyCounter = $expirationNotifyCounter;
    }

    /**
     * @return mixed
     */
    public function getExpiredNotifyCounter()
    {
        return $this->expiredNotifyCounter;
    }

    /**
     * @param mixed $expiredNotifyCounter
     */
    public function setExpiredNotifyCounter($expiredNotifyCounter)
    {
        $this->expiredNotifyCounter = $expiredNotifyCounter;
    }

    /**
     * @return mixed
     */
    public function getAutoClosureCounter()
    {
        return $this->autoClosureCounter;
    }

    /**
     * @param mixed $autoClosureCounter
     */
    public function setAutoClosureCounter($autoClosureCounter)
    {
        $this->autoClosureCounter = $autoClosureCounter;
    }

    public function incrementExpirationNotifyCounter() {
        $counter = $this->getExpirationNotifyCounter();
        if( !$counter ) {
            $counter = 0;
        }
        $counter = $counter + 1;
        $this->setExpirationNotifyCounter($counter);
        return $counter;
    }
    public function incrementExpiredNotifyCounter() {
        $counter = $this->getExpiredNotifyCounter();
        if( !$counter ) {
            $counter = 0;
        }
        $counter = $counter + 1;
        $this->setExpiredNotifyCounter($counter);
        return $counter;
    }
    public function incrementAutoClosureCounter() {
        $counter = $this->getAutoClosureCounter();
        if( !$counter ) {
            $counter = 0;
        }
        $counter = $counter + 1;
        $this->setAutoClosureCounter($counter);
        return $counter;
    }
    
    public function getExpirationNotifyCounterStr() {
        if( $this->getExpirationNotifyCounter() ) {
            return "Yes";
        }
        return "No";
    }
    public function getExpiredNotifyCounterStr() {
        if( $this->getExpiredNotifyCounter() ) {
            return "Yes";
        }
        return "No";
    }
    public function getAutoClosureCounterStr() {
        if( $this->getAutoClosureCounter() ) {
            return "Yes";
        }
        return "No";
    }

    /**
     * @return mixed
     */
    public function getPriceList()
    {
        return $this->priceList;
    }

    /**
     * @param mixed $priceList
     */
    public function setPriceList($priceList)
    {
        $this->priceList = $priceList;
    }

    public function getPriceListAbbreviationPostfix() {
        $priceList = $this->getPriceList();
        if( $priceList ) {
            return "-".$priceList->getAbbreviation();
        }
        return NULL;
    }
    


//    public function getReminderEmails()
//    {
//        return $this->reminderEmails;
//    }
//    public function addReminderEmail($item)
//    {
//        if( $item && !$this->reminderEmails->contains($item) ) {
//            $this->reminderEmails->add($item);
//        }
//        return $this;
//    }
//    public function removeReminderEmail($item)
//    {
//        $this->reminderEmails->removeElement($item);
//    }

    //Return only unique users
    public function getAllPrincipalInvestigators() {
        $allPis = new ArrayCollection();
        $pis = $this->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi && !$allPis->contains($pi) ) {
                //if( $irbPi ) {
                $allPis->add($pi);
            }
        }
        $irbPi = $this->getPrincipalIrbInvestigator();
        if( $irbPi && !$allPis->contains($irbPi) ) {
            //if( $irbPi ) {
            $allPis->add($irbPi);
        }
        return $allPis;
    }
    //NOT USED (TO DELETE). Might return repeating users
    public function getAllPrincipalInvestigators_ORIG() {
        $pis = $this->getPrincipalInvestigators();
        $irbPi = $this->getPrincipalIrbInvestigator();
        if( $irbPi ) {
            $pis->add($irbPi);
        }
        return $pis;
    }

    public function isFunded() {
        if( $this->getFunded() ) {
            return "Funded";
        }
        return "Non-funded";  //"Not-Funded";
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
     * "HPID" or "APCPID" or "COVID"
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
            if( $projectSpecialtyAbbreviation == "covid19" ) {
                //$projectSpecialtyAbbreviation = "HEMEPATH";
                //use ShortName for this
                $projectSpecialtyAbbreviation = "COVID";
            }
            $projectSpecialtyAbbreviation = str_replace("-","",$projectSpecialtyAbbreviation);
            $projectSpecialtyStr = strtoupper($projectSpecialtyAbbreviation);
        }
        $oid = $projectSpecialtyStr . $this->getId();
        //echo "oid=$oid <br>";
        $this->setOid($oid);
    }

    //Project request APCP1 'Project Title' submitted by FirstName LastName on MM/DD/YYYY
    public function getProjectInfoName() {
        $title = $this->getTitle();
        $createDateStr = null;
        if( $this->getCreateDate() ) {
            $createDateStr = " on " . $this->getCreateDate()->format('m/d/Y');
        }
        return "Project request " . $this->getOid() . " '$title' submitted by ".$this->getSubmitter()->getUsernameOptimal() . $createDateStr; //. " at ".$this->getCreateDate()->format('H:i:s')
    }

    public function getProjectIdTitle() {
        $title = $this->getTitle();
        //$createDateStr = null;
        //if( $this->getCreateDate() ) {
        //    $createDateStr = " on " . $this->getCreateDate()->format('m/d/Y');
        //}
        //return $this->getOid() . " '$title' submitted by ".$this->getSubmitter()->getUsernameOptimal();
        return $this->getOid() . " '$title'";
    }

    public function getIrbIacucNumber($delimeter=" ") {
        //A- If there is only an IRB number: show the IRB number as you do now
        //B- If there is only an IACUC number: show the IACUC number in parentheses (IACUC Number)
        //C- If there are both an IRB number and an IACUC number, show IRB Number followed by an IACUC number in parenthesis: IRB Number (IACUC Number)

//        if( $this->getIrbNumber() && !$this->getIacucNumber() ) {
//            return $this->getIrbNumber();
//        }
//
//        if( !$this->getIrbNumber() && $this->getIacucNumber() ) {
//            return "(".$this->getIacucNumber() . ")";
//        }
//
//        if( $this->getIrbNumber() && $this->getIacucNumber() ) {
//            return $this->getIrbNumber() . $delimeter . "(".$this->getIacucNumber().")";
//        }

        $resultArr = array();

        $irb = false;
        $iacuc = false;

        $irbApproval = $this->getExemptIrbApproval();
        if( !$irbApproval || ($irbApproval && $irbApproval->getName() == "Not Exempt") ) {
            //echo $this->getId().": irb true <br>";
            $irb = true;
        }
        $iacucApproval = $this->getExemptIACUCApproval();
        if( !$iacucApproval || ($iacucApproval && $iacucApproval->getName() == "Not Exempt") ) {
            //echo $this->getId().": iacuc true <br>";
            $iacuc = true;
        }

        if( $irb && $this->getIrbNumber() ) {
            $resultArr[] = $this->getIrbNumber();
        }
        if( $iacuc && $this->getIacucNumber() ) {
            $resultArr[] = "(".$this->getIacucNumber().")";
        }

        if( count($resultArr) > 0 ) {
            return implode($delimeter,$resultArr);
        }

        return null;
    }

    //"IRB with PI FirstName LastName expires on MM/DD/YYYY."
    public function getIrbInfo($humanName=null) {
        $info = "No provided IRB number";

        if( $this->getIrbNumber() ) {
            if( !$humanName ) {
                $humanName = "IRB";
            }
            $info = $humanName."# ".$this->getIrbNumber();
        }

        if( $this->getPrincipalIrbInvestigator() ) {
            $info = $info . " with PI " . $this->getPrincipalIrbInvestigator()->getUsernameOptimal();
        } else {
            $pis = $this->getPrincipalInvestigators();
            if( count($pis) > 0 ) {
                $piArr = array();
                foreach($pis as $pi) {
                    $piArr[] = $pi->getUsernameOptimal();
                }
                $info = $info . " with PI(s) " . implode(", ",$piArr);
            }
        }

        //if( $this->getIrbExpirationDate() ) {
        //    $info = $info . ", expires on " . $this->getIrbExpirationDate()->format('m/d/Y');
        //}

        //$info = '<div class="well">'.$info.'</div>';
        $info = '<p class="text-primary">'.$info.'</p>';

        return $info;
    }

    //used by select2. Limit by 15 chars
    public function getProjectInfoNameChoice() {
        return $this->getProjectInfoLimited(false);
    }
    public function getProjectInfoNameWithPIsChoice() {
        //return $this->getOid();// . " " . $this->getTitle();
        return $this->getProjectInfoLimited(true);
    }
    public function getProjectInfoLimited($withpis=true) {
        //return $this->getOid(); //testing
        //$info = $this->getProjectInfoName();
        //$info = $this->getOid() . " submitted on ".$this->getCreateDate()->format('m/d/Y'); //. " at ".$this->getCreateDate()->format('H:i:s')
        //$info = $this->getOid() . ", submitted on " . $this->getCreateDate()->format('m/d/Y');
        $info = $this->getOid();

        $title = $this->getTitle();
        if( $title ) {
            $limit = 20;
            if( strlen((string)$title) > $limit ) {
                $title = substr((string)$title, 0, $limit) . '...';
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

        //if( $this->getCreateDate() ) {
        //    $info = $info . ", " . $this->getCreateDate()->format('m/d/Y');
        //}

        $limit = 70;
        if( strlen((string)$info) > $limit ) {
            $info = substr((string)$info, 0, $limit) . '...';
        }

        return $info;
    }
    public function getPiStr() {
        $piStr = "unknown PI";
        $piArr = array();
        foreach( $this->getPrincipalInvestigators() as $pi ) {
            $piArr[] = $pi->getUsernameShortest();
        }
        if( count($piArr) > 0 ) {
            $piStr = implode(", ", $piArr);
        }
        return $piStr;
    }

    public function mergeHiddenFields() {

        $mergeInfo = NULL;
        //$separator = "\n";
        $separator = "<br>";

        if( $this->getBudgetSummary() ) {
            if( $mergeInfo ) {
                $mergeInfo = $mergeInfo . $separator;
            }
            $mergeInfo = $mergeInfo . "Budget Summary: " . $this->getBudgetSummary();
        }

        if( $this->getHypothesis() ) {
            if( $mergeInfo ) {
                $mergeInfo = $mergeInfo . $separator;
            }
            $mergeInfo = $mergeInfo . "Hypothesis: " . $this->getHypothesis();
        }

        if( $this->getObjective() ) {
            if( $mergeInfo ) {
                $mergeInfo = $mergeInfo . $separator;
            }
            $mergeInfo = $mergeInfo . "Objective: " . $this->getObjective();
        }

        if( $this->getExpectedResults() ) {
            if( $mergeInfo ) {
                $mergeInfo = $mergeInfo . $separator;
            }
            $mergeInfo = $mergeInfo . "Expected Results: " . $this->getExpectedResults();
        }

        if( $this->getNumberOfCases() ) {
            if( $mergeInfo ) {
                $mergeInfo = $mergeInfo . $separator;
            }
            $mergeInfo = $mergeInfo . "Number of Cases: " . $this->getNumberOfCases();
        }

        if( $this->getNumberOfCohorts() ) {
            if( $mergeInfo ) {
                $mergeInfo = $mergeInfo . $separator;
            }
            $mergeInfo = $mergeInfo . "Number of Cohorts: " . $this->getNumberOfCohorts();
        }

        if( $this->getExpectedCompletionDate() ) {
            if( $mergeInfo ) {
                $mergeInfo = $mergeInfo . $separator;
            }
            $mergeInfo = $mergeInfo . "Expected Completion Date: " . $this->getExpectedCompletionDate()->format('m/d/Y');;
        }

        return $mergeInfo;
    }

    //get Issued Invoices
    //Used in project index.php
    //Used in Dashboard
    public function getInvoicesInfosByProject($admin=true) {
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $invoicesInfos = array();
        $count = 0;         //total number of latest invoices
        $total = 0.00;
        $paid = 0.00;
        $due = 0.00;
        $subsidy = 0.00;
        $countRequest = 0;
        $grandTotal = 0.00;
        $grandTotalWithoutInvoices = 0.00; //amount of work requests without invoices including subsidy
        $sumTotal = 0.00;

        $paidCount = 0;             //total number of latest invoices "Paid in Full", "Paid Partially"
        $paidAmount = 0;            //amount of "Paid in Full", "Paid Partially"
        $outstandingCount = 0;      //total number of latest invoices "Unpaid/Issued", "Paid Partially"
        $outstandingAmount = 0;     //amount "issued-unpaid", "partially paid"

        foreach($this->getRequests() as $request) {

            //check if $request progressState != draft, canceled
            $progressState = $request->getProgressState();
            //check if $request billingState != draft, canceled
            $billingState = $request->getBillingState();

            $skip = false;
            if( $progressState == 'draft' || $progressState == 'canceled' ) {
                $skip = true;
            }
            if( $billingState == 'draft' || $billingState == 'canceled' ) {
                $skip = true;
            }

            if( $skip ) {
                continue;
            }

            //$res = $transresRequestUtil->getInvoicesInfosByRequest($request);
            $res = $request->getInvoicesInfosByRequest($admin);
            if( $res['count'] > 0 ) {
                $count = $count + $res['count'];                //invoice count
                $total = $total + $res['total'];                //invoice total (Charge)
                $paid = $paid + $res['paid'];                   //invoice paid
                $due = $due + $res['due'];                      //invoice due
                $subsidy = $subsidy + $res['subsidy'];          //invoice subsidy
                $grandTotal = $grandTotal + $res['grandTotal']; //Project Value
                $sumTotal = $sumTotal + $res['sumTotal'];       //Project Total including subsidy (Paid+Due+Positive Subsidy)

                $paidCount = $paidCount + $res['paidCount'];
                $paidAmount = $paidAmount + $res['paidAmount'];
                $outstandingCount = $outstandingCount + $res['outstandingCount'];
                $outstandingAmount = $outstandingAmount + $res['outstandingAmount'];

            } else {
                //No invoice. Use work request value instead.
                $subTotal = $request->getTransResRequestSubTotal();
                //$count++;
                $requestSubsidy = $request->calculateSubsidyByRequest();
                //$grandTotal = $grandTotal + $res['grandTotal']; //$grandTotal = $total + $subsidy;
                $grandTotal = $grandTotal + $subTotal + $requestSubsidy; //"Value" in the project list
                $grandTotalWithoutInvoices = $grandTotalWithoutInvoices + $subTotal + $requestSubsidy;
            }

            $countRequest++;
        }
        //echo $project->getOid().": countRequest=$countRequest: ";

        $grandTotal = $this->toDecimal($grandTotal);
        $grandTotalWithoutInvoices = $this->toDecimal($grandTotalWithoutInvoices);

        if( $count > 0 ) {
            $total = $this->toDecimal($total);
            $paid = $this->toDecimal($paid);
            $due = $this->toDecimal($due);
            $subsidy = $this->toDecimal($subsidy);
            $sumTotal = $this->toDecimal($sumTotal);

            $paidAmount = $this->toDecimal($paidAmount);
            $outstandingAmount = $this->toDecimal($outstandingAmount);
        } else {
            //echo "total=$total<br>";
            $total = NULL;
            $paid = NULL;
            $due = NULL;
            $subsidy = NULL;
            $sumTotal = NULL;
            $paidAmount = NULL;
            $outstandingAmount = NULL;
        }
        //echo "total=$total<br>";

        $invoicesInfos['count'] = $count;
        $invoicesInfos['total'] = $total; //charge
        $invoicesInfos['paid'] = $paid;
        $invoicesInfos['due'] = $due;
        $invoicesInfos['subsidy'] = $subsidy;
        $invoicesInfos['grandTotal'] = $grandTotal; //grand total including subsidy
        $invoicesInfos['grandTotalWithoutInvoices'] = $grandTotalWithoutInvoices; //amount of work requests without invoices including subsidy
        $invoicesInfos['sumTotal'] = $sumTotal;

        $invoicesInfos['paidCount'] = $paidCount;
        $invoicesInfos['paidAmount'] = $paidAmount;
        $invoicesInfos['outstandingCount'] = $outstandingCount;
        $invoicesInfos['outstandingAmount'] = $outstandingAmount;

        return $invoicesInfos;
    }

    public function getRemainingBudget( $total=NULL ) {

        //return NULL; //testing

        $remainingBudget = NULL; //"No Info";

        if( $this->getFunded() ) {
            return $remainingBudget;
        }

        if( $this->getNoBudgetLimit() === true ) {
            return $remainingBudget;
        }

        if( $total === NULL ) {
            $total = $this->getTotal();
        }

        $approvedProjectBudget = $this->getApprovedProjectBudget();

        if( $approvedProjectBudget === NULL ) {
            //null
        } else {
            if( $total ) {
                $remainingBudget = $this->toDecimal($approvedProjectBudget) - $this->toDecimal($total);
            } else {
                $remainingBudget = $this->toDecimal($approvedProjectBudget);
            }
        }

        //echo $this->getId().": approvedProjectBudget=$approvedProjectBudget, total=$total, remainingBudget=$remainingBudget<br>";
        return $remainingBudget;
    }

    public function updateProjectTotal()
    {
        $invoicesInfos = $this->getInvoicesInfosByProject();
        $total = $invoicesInfos['grandTotal'];
        if( $total !== NULL ) {
            //exit("total=".$total);
            $this->setTotal($total);
        }

        return $total;
    }

    public function getAdminUserReviewers($filterByType=false) {
        $adminReviews = $this->getAdminReviews($filterByType);
        if( count($adminReviews) == 0 ) {
            return array();
        }
        $admins = array();
        foreach($adminReviews as $adminReview) {
            $admin = $adminReview->getReviewer();
            if( $admin ) {
                $admins[] = $admin;
            }
            $adminDelegate = $adminReview->getReviewerDelegate();
            if( $adminDelegate ) {
                $admins[] = $adminDelegate;
            }
        }
        return $admins;
    }
    
    public function getExpectedExpirationDateStr() {
        $expDateStr = NULL;
        $expDate = $this->getExpectedExpirationDate();
        if( $expDate ) {
            $expDateStr = $expDate->format('m/d/Y'); //same format as in the project form
        }
        return $expDateStr;
    }

    public function getEntityName() {
        return "Project";
    }

    public function getDisplayName() {
        return "project request";
    }

    public function __toString() {
        return "Project id=[".$this->getId()."]";
    }
}