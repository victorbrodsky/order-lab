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

    /**
     * fundedAccountNumber: this field can override the project's fundedAccountNumber
     * @ORM\Column(type="string", nullable=true)
     */
    private $fundedAccountNumber;

    /**
     * @ORM\ManyToMany(targetEntity="Invoice", inversedBy="transresRequests")
     * @ORM\JoinTable(name="transres_request_invoice")
     */
    private $invoices;


    public function __construct($user=null) {
        $this->setSubmitter($user);
        //$this->setState('draft');
        $this->setCreateDate(new \DateTime());
        $this->invoices = new ArrayCollection();
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
            return $this->oid;
        } else {
            return $this->generateOid();
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
        }
        return $this;
    }
    public function removeInvoice($item)
    {
        $this->invoices->removeElement($item);
    }


    /**
     * projectOid + "-RED-" + ID; Example: "HEMEPATH-8-REQ-1"
     * @param string $oid
     */
    public function generateOid()
    {
        $oid = $this->getProject()->getOid() . "-REQ-" . $this->getId();
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


    public function __toString() {
        return "Request id=".$this->getId();
    }
}