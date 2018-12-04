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


//https://pathology.weill.cornell.edu/research/translational-research-services
//https://pathology.weill.cornell.edu/research/translational-research-services/fee-schedule

/**
 * @ORM\Entity
 * @ORM\Table(name="transres_request")
 * @ORM\HasLifecycleCallbacks
 */
class TransResRequest {

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
     * @ORM\Column(type="integer", nullable=true)
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
    private $updateDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $completedDate;

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
     * Progress State of the request (state machine variable)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $progressState;

    /**
     * Billing State of the request (state machine variable)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $billingState;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $progressApprovalDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $billingApprovalDate;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="requests", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $project;

//    /**
//     * @ORM\OneToMany(targetEntity="Invoice", inversedBy="transresRequests")
//     * @ORM\JoinTable(name="transres_request_invoice")
//     */
//    private $invoices;
    /**
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="transresRequest", cascade={"persist","remove"})
     */
    private $invoices;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_request_principalinvestigator",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="principalinvestigator_id", referencedColumnName="id")}
     * )
     **/
    private $principalInvestigators;

    //////////////// fields /////////////////////////
    /**
     * fundedAccountNumber: this field can override the project's fundedAccountNumber
     * @ORM\Column(type="string", nullable=true)
     */
    private $fundedAccountNumber;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $supportStartDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $supportEndDate;

    /**
     * Billing contact is populated from Project's $billingContact
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="contact", referencedColumnName="id", nullable=true)
     */
    private $contact;

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="transresRequest", cascade={"persist","remove"})
     */
    private $products;

    //////////////// EOF fields /////////////////////////

    /**
     * @ORM\OneToMany(targetEntity="DataResult", mappedBy="transresRequest", cascade={"persist","remove"})
     */
    private $dataResults;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Request Documents
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_request_document",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $documents;

    /**
     * Packing Slip PDFs
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_request_packingSlipPdf",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="packingSlipPdf_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $packingSlipPdfs;

    /**
     * Old Packing Slip PDFs
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_request_oldPackingSlipPdf",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="oldPackingSlipPdf_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $oldPackingSlipPdfs;

    /**
     * Reference antibody
     *
     * @ORM\ManyToMany(targetEntity="Oleg\TranslationalResearchBundle\Entity\AntibodyList", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_request_antibody",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="antibody_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $antibodyReferences;

    /**
     * Translational Research Work Request Business Purposes
     *
     * @ORM\ManyToMany(targetEntity="Oleg\TranslationalResearchBundle\Entity\BusinessPurposeList", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_request_businessPurpose",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="businessPurpose_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $businessPurposes;

    
    public function __construct($user=null) {
        $this->setSubmitter($user);
        //$this->setState('draft');
        $this->setCreateDate(new \DateTime());

        $this->invoices = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->principalInvestigators = new ArrayCollection();
        $this->dataResults = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->packingSlipPdfs = new ArrayCollection();
        $this->oldPackingSlipPdfs = new ArrayCollection();
        $this->antibodyReferences = new ArrayCollection();
        $this->businessPurposes = new ArrayCollection();
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
     * @return \DateTime
     */
    public function getCompletedDate()
    {
        return $this->completedDate;
    }

    /**
     * @param \DateTime $completedDate
     */
    public function setCompletedDate($completedDate)
    {
        $this->completedDate = $completedDate;
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
    public function getOid()
    {
        //return $this->oid;

        if( $this->oid ) {
            if( $this->getExportId() ) {
                //return $this->oid;
                return $this->getProject()->getOid(false) . "-REQ" . $this->getId() . " (".$this->getProject()->getExportId()."-RS".$this->getExportId().")";
            } else {
                return $this->generateOid();
            }
        } else {
            return $this->getId();
        }
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
    public function getProgressState()
    {
        return $this->progressState;
    }

    /**
     * @param mixed $progressState
     */
    public function setProgressState($progressState)
    {
        $this->progressState = $progressState;

        if( $progressState == "completedNotified" ) {
            $this->setCompletedDate(new \DateTime());
        }
//        if( $progressState == "completed" || $progressState == "completedNotified" ) {
//            $this->setCompletedDate(new \DateTime());
//        }
    }

    /**
     * @return mixed
     */
    public function getBillingState()
    {
        return $this->billingState;
    }

    /**
     * @param mixed $billingState
     */
    public function setBillingState($billingState)
    {
        $this->billingState = $billingState;
    }

    /**
     * @return mixed
     */
    public function getProgressApprovalDate()
    {
        return $this->progressApprovalDate;
    }

    /**
     * @param mixed $progressApprovalDate
     */
    public function setProgressApprovalDate($progressApprovalDate)
    {
        $this->progressApprovalDate = $progressApprovalDate;
    }

    /**
     * @return mixed
     */
    public function getBillingApprovalDate()
    {
        return $this->billingApprovalDate;
    }

    /**
     * @param mixed $billingApprovalDate
     */
    public function setBillingApprovalDate($billingApprovalDate)
    {
        $this->billingApprovalDate = $billingApprovalDate;
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project)
    {
        $this->project = $project;
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

    public function getInvoices()
    {
        return $this->invoices;
    }
    public function addInvoice($item)
    {
        if( $item && !$this->invoices->contains($item) ) {
            $this->invoices->add($item);
            $item->setTransresRequest($this);
        }
        return $this;
    }
    public function removeInvoice($item)
    {
        $this->invoices->removeElement($item);
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

    //////////////// fields /////////////////////////
    public function getProducts()
    {
        return $this->products;
    }
    public function addProduct($item)
    {
        if( $item && !$this->products->contains($item) ) {
            $this->products->add($item);
            $item->setTransresRequest($this);
        }
        return $this;
    }
    public function removeProduct($item)
    {
        $this->products->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getSupportStartDate()
    {
        return $this->supportStartDate;
    }

    /**
     * @param mixed $supportStartDate
     */
    public function setSupportStartDate($supportStartDate)
    {
        $this->supportStartDate = $supportStartDate;
    }

    /**
     * @return mixed
     */
    public function getSupportEndDate()
    {
        return $this->supportEndDate;
    }

    /**
     * @param mixed $supportEndDate
     */
    public function setSupportEndDate($supportEndDate)
    {
        $this->supportEndDate = $supportEndDate;
    }

    /**
     * @return mixed
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param mixed $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    public function getDataResults()
    {
        return $this->dataResults;
    }
    public function addDataResult($item)
    {
        if( $item && !$this->dataResults->contains($item) ) {
            $this->dataResults->add($item);
            $item->setTransresRequest($this);
        }
        return $this;
    }
    public function removeDataResult($item)
    {
        $this->dataResults->removeElement($item);
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
     * @return mixed
     */
    public function getPackingSlipPdfs()
    {
        return $this->packingSlipPdfs;
    }
    public function addPackingSlipPdf($item)
    {
        if( $item && !$this->packingSlipPdfs->contains($item) ) {
            $this->packingSlipPdfs->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removePackingSlipPdf($item)
    {
        $this->packingSlipPdfs->removeElement($item);
        $item->clearUseObject();
    }

    /**
     * @return mixed
     */
    public function getOldPackingSlipPdfs()
    {
        return $this->oldPackingSlipPdfs;
    }
    public function addOldPackingSlipPdf($item)
    {
        if( $item && !$this->oldPackingSlipPdfs->contains($item) ) {
            $this->oldPackingSlipPdfs->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeOldPackingSlipPdf($item)
    {
        $this->oldPackingSlipPdfs->removeElement($item);
        $item->clearUseObject();
    }



    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getAntibodyReferences()
    {
        return $this->antibodyReferences;
    }
    public function addAntibodyReference($item)
    {
        if( $item && !$this->antibodyReferences->contains($item) ) {
            $this->antibodyReferences->add($item);
        }
        return $this;
    }
    public function removeAntibodyReference($item)
    {
        $this->antibodyReferences->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getBusinessPurposes()
    {
        return $this->businessPurposes;
    }
    public function addBusinessPurpose($item)
    {
        if( $item && !$this->businessPurposes->contains($item) ) {
            $this->businessPurposes->add($item);
        }
        return $this;
    }
    public function removeBusinessPurpose($item)
    {
        $this->businessPurposes->removeElement($item);
    }
    //////////////// EOF fields /////////////////////////


    public function getProjectSpecialty() {
        $project = $this->getProject();
        return $project->getProjectSpecialty();
    }

    /**
     * projectOid + "-RED-" + ID; Example: "HP8-REQ1" or "APCP7-REQ1"
     * @param string $oid
     */
    public function generateOid()
    {
        $oid = $this->getProject()->getOid(false) . "-REQ" . $this->getId();
        //echo "oid=$oid <br>";
        $this->setOid($oid);
        return $oid;
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

    public function getEntityName() {
        return "Request";
    }

    public function getDisplayName() {
        return "Work Request";
    }

    public function __toString() {
        return "Request id=".$this->getId();
    }
}