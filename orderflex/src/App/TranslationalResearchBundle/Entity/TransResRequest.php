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
     * serve as submitted date when button "Complete Submission" ('saveAsComplete') clicked
     *
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
     * "Saved as Draft on" timestamp field
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $savedAsDraftDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $completedDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $completedBy;

    /**
     * completedDate is set by script
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $completedDateSet;

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
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User")
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
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
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
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
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
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
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
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
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
     * @ORM\ManyToMany(targetEntity="App\TranslationalResearchBundle\Entity\AntibodyList", cascade={"persist","remove"})
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
     * @ORM\ManyToMany(targetEntity="App\TranslationalResearchBundle\Entity\BusinessPurposeList", cascade={"persist","remove"})
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

        //Every time a new work request is saved as draft for the first time, save the timestamp in both “Submitted on” field AND “Saved as Draft on” field
        $nowDate = new \DateTime();
        $this->setCreateDate($nowDate);
        $this->setSavedAsDraftDate($nowDate);

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

    /**
     * @return mixed
     */
    public function getSavedAsDraftDate()
    {
        return $this->savedAsDraftDate;
    }

    /**
     * @param mixed $savedAsDraftDate
     */
    public function setSavedAsDraftDate($savedAsDraftDate)
    {
        $this->savedAsDraftDate = $savedAsDraftDate;
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
    public function getCompletedBy()
    {
        return $this->completedBy;
    }

    /**
     * @param mixed $completedBy
     */
    public function setCompletedBy($completedBy)
    {
        $this->completedBy = $completedBy;
    }

    /**
     * @return mixed
     */
    public function getCompletedDateSet()
    {
        return $this->completedDateSet;
    }

    /**
     * @param mixed $completedDateSet
     */
    public function setCompletedDateSet($completedDateSet)
    {
        $this->completedDateSet = $completedDateSet;
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

        if( $progressState == "completedNotified" ) { // && !$this->getCompletedDate()
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
    public function getLatestInvoice() {
        $invoices = $this->getInvoices();
        if( count($invoices) > 0 ) {
            return $invoices[0];
        }
        return null;
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
        if( $project ) {
            return $project->getProjectSpecialty();
        }
        return NULL;
    }

    public function getPriceList() {
        $project = $this->getProject();
        if( $project ) {
            return $project->getPriceList();
        }
        return NULL;
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

    //get Issued Invoices
    public function getInvoicesInfosByRequest($admin=true) {

        //$transresRequest = $this->getTransresRequest();

        $invoicesInfos = array();
        $count = 0;
        $total = 0.00;
        $paid = 0.00;
        $due = 0.00;
        $subsidy = 0.00;
        $grandTotal = 0.00;
        $sumTotal = 0.00;       //real financial total: //Paid+Due+Positive Subsidy

        //check if progressState != draft, canceled
        $progressState = $this->getProgressState();
        //check if billingState != draft, canceled
        $billingState = $this->getBillingState();

        $skip = false;
        if( $progressState == 'draft' || $progressState == 'canceled' ) {
            $skip = true;
        }
        if( $billingState == 'draft' || $billingState == 'canceled' ) {
            $skip = true;
        }

        if( $skip == false ) {
            foreach ($this->getInvoices() as $invoice) {
                if ($invoice->getLatestVersion() && $invoice->getStatus() != 'Canceled') {
                    $count++;

                    $invoiceTotal = $invoice->getTotal();
                    $invoicePaid = $invoice->getPaid();
                    $invoiceDue = $invoice->getDue();
                    $invoiceSubsidy = $this->getInvoiceSubsidy($invoice, $admin);

                    $total = $total + $invoiceTotal;
                    $paid = $paid + $invoicePaid;
                    //echo "paid=$paid <br>";
                    $due = $due + $invoiceDue;

                    //$subsidy = $subsidy + $invoice->getSubsidy();
                    $subsidy = $subsidy + $invoiceSubsidy;

                    $grandTotal = $grandTotal + $total + $invoiceSubsidy;

                    if( $invoiceSubsidy > 0 ) {
                        //sum of the “Paid”, “Due” and “Subsidy”
                        $sumTotal = $sumTotal + $invoicePaid + $invoiceDue + $invoiceSubsidy; //real financial total: //Paid+Due+Positive Subsidy
                    } else {
                        //when the Subsidy is negative - sum of only “Paid” and “Due” columns (not the Subsidy).
                        $sumTotal = $sumTotal + $invoicePaid + $invoiceDue; //Paid+Due
                    }

                }//if invoice latest
            }//foreach invoice
        }//$skip == false

        //echo "total=$total<br>";
        //echo "paid=$paid<br>";

        if( $count > 0 ) {
            $total = $this->toDecimal($total);
            $paid = $this->toDecimal($paid);
            $due = $this->toDecimal($due);
            $subsidy = $this->toDecimal($subsidy);
            $grandTotal = $this->toDecimal($grandTotal);    //Value: total + subsidy (?)
            $sumTotal = $this->toDecimal($sumTotal);
        } else {
            $total = null;
            $paid = null;
            $due = null;
            $subsidy = null;
            $grandTotal = null;
            $sumTotal = null;
        }

        //echo "paid=$paid<br>";

        $invoicesInfos['count'] = $count;
        $invoicesInfos['total'] = $total;
        $invoicesInfos['paid'] = $paid;
        $invoicesInfos['due'] = $due;
        $invoicesInfos['subsidy'] = $subsidy;
        $invoicesInfos['grandTotal'] = $grandTotal;
        $invoicesInfos['sumTotal'] = $sumTotal;

        return $invoicesInfos;
    }

    //Calculate subsidy based only on the work request's products.
    //If invoice is edited manually (products added or removed, price changed, discount applied), subsidy will not be changed.
    //Used only in getSubsidyInfo($invoice) and 
    public function calculateSubsidyByRequest() {
        $priceList = $this->getPriceList();
        $subsidy = 0;

        foreach( $this->getProducts() as $product ) {

            //$quantity = $product->getQuantity();
            //echo "quantity=$quantity <br>";

            //default fee
            $quantitiesArr = $product->calculateQuantities(NULL);
            $initialQuantity = $quantitiesArr['initialQuantity'];
            $additionalQuantity = $quantitiesArr['additionalQuantity'];
            $initialFee = $quantitiesArr['initialFee'];
            $additionalFee = $quantitiesArr['additionalFee'];

            $totalDefault = $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);

            //special fee
            $quantitiesArr = $product->calculateQuantities($priceList);
            $initialQuantity = $quantitiesArr['initialQuantity'];
            $additionalQuantity = $quantitiesArr['additionalQuantity'];
            $initialFee = $quantitiesArr['initialFee'];
            $additionalFee = $quantitiesArr['additionalFee'];

            $totalSpecial = $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);

            if( $totalDefault && $totalSpecial && $totalDefault != $totalSpecial ) {
                //echo "totalDefault=$totalDefault totalSpecial=$totalSpecial <br>";
                $diff = $this->toDecimal($totalDefault - $totalSpecial);

                //subsidy can be negative. Show negative subsidy only to admin
                $subsidy = $subsidy + $diff;
            }

        }

        $subsidy = $this->toDecimal($subsidy);
        //echo "res subsidy=$subsidy <br>";

        return $subsidy;
    }
    
    public function getInvoiceSubsidy( $invoice, $admin=true ) {
        $subsidy = $invoice->getSubsidy();

        $showSubsidy = false;

        if( $subsidy > 0 ) {
            $showSubsidy = true;
        } else {
            //negative subsidy: additional check if admin or technician
//            $transresRequestUtil = $this->container->get('transres_request_util');
//            if( $transresRequestUtil->isUserHasInvoicePermission($invoice, "update") ) {
//                $showSubsidy = true;
//            }
            if( $admin ) {
                $showSubsidy = true;
            }
        }

        if( $showSubsidy ) {
            $subsidy = $this->toDecimal($subsidy);
        } else {
            $subsidy = 0.00;
        }

        return $subsidy;
    }

    //Unbilled work request total amount
    //Used to calculate fee on request list and dashboard (used to be getTransResRequestFeeHtml)
    public function getTransResRequestSubTotal() {
        $subTotal = 0;

        $priceList = $this->getPriceList();

        foreach($this->getProducts() as $product) {
            $quantitiesArr = $product->calculateQuantities($priceList);
            $initialQuantity = $quantitiesArr['initialQuantity'];
            $additionalQuantity = $quantitiesArr['additionalQuantity'];
            $initialFee = $quantitiesArr['initialFee'];
            $additionalFee = $quantitiesArr['additionalFee'];

            //echo "units=$units; fee=$fee <br>";
            if( $initialFee && $initialQuantity ) {
                //$subTotal = $subTotal + ($units * intval($fee));
                //$subTotal = $subTotal + $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$initialQuantity,$units);
                $subTotal = $subTotal + $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
            }
        }

        return $subTotal;
    }

    public function calculateDefaultTotalByRequest() {
        $totalDefault = 0;

        foreach( $this->getProducts() as $product ) {

            //default
            $quantitiesArr = $product->calculateQuantities(NULL);
            $initialQuantity = $quantitiesArr['initialQuantity'];
            $additionalQuantity = $quantitiesArr['additionalQuantity'];
            $initialFee = $quantitiesArr['initialFee'];
            $additionalFee = $quantitiesArr['additionalFee'];
            $totalDefault = $totalDefault + $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
        }

        $totalDefault = $this->toDecimal($totalDefault);

        return $totalDefault;
    }

//    public function calculateDefaultTotalByInvoice() {
//        return $this->calculateDefaultTotalByRequest();
//    }
    
    public function getTotalFeesByQuantity($fee,$feeAdditionalItem,$initialQuantity,$quantity) {
        $quantity = intval($quantity);
        //$fee = intval($fee);
        $fee = $this->toDecimal($fee);
        if( $feeAdditionalItem ) {
            //$feeAdditionalItem = intval($feeAdditionalItem);
            $feeAdditionalItem = $this->toDecimal($feeAdditionalItem);
        } else {
            $feeAdditionalItem = $fee;
        }

        $initialTotal = $this->toDecimal($initialQuantity * $fee);
        $additionalTotal = $this->toDecimal($quantity * $feeAdditionalItem);

        $total = $initialTotal + $additionalTotal;

        if ($total > 0) {
            $total = $this->toDecimal($total);
        }
        return $total;
    }

//    /**
//     * Create new: update project's grandTotal ("Total" in the project list)
//     *
//     * @ORM\PostPersist
//     */
//    public function updatePostPersistProjectTotal()
//    {
//        //exit("request->PostPersist->updateProjectTotal");
//        $this->updateProjectTotal();
//    }
    /**
     * postUpdate - The postUpdate event occurs after the database update operations to entity data. It is not called for a DQL UPDATE statement.
     * update project's total ("Total" in the project list)
     *
     * @ORM\PostUpdate
     */
    public function updatePostUpdateProjectTotal()
    {
        //exit("request->PostUpdate->updateProjectTotal");
        $this->updateProjectTotal();
    }
    public function updateProjectTotal()
    {
        $total = NULL;

        //check if progressState != draft, canceled
        $progressState = $this->getProgressState();
        //check if billingState != draft, canceled
        $billingState = $this->getBillingState();

        $skip = false;
        if( $progressState == 'draft' || $progressState == 'canceled' ) {
            $skip = true;
        }
        if( $billingState == 'draft' || $billingState == 'canceled' ) {
            $skip = true;
        }
        if( $skip ) {
            return $total;
        }

//        $project = $this->getProject();
//        $invoicesInfos = $project->getInvoicesInfosByProject();
//        $total = $invoicesInfos['grandTotal'];
//        if( $total !== NULL ) {
//            //exit("total=".$total);
//            $project->setTotal($total);
//        }
        $project = $this->getProject();
        if( $project ) {
            $project->updateProjectTotal();
        }

        return $total;
    }

    public function toDecimal($number) {
//        if( !$number ) {
//            return $number;
//        }
        return number_format((float)$number, 2, '.', '');
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