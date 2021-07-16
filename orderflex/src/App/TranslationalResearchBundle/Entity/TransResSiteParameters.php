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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity
 * @ORM\Table(name="transres_siteParameters", uniqueConstraints={@ORM\UniqueConstraint(name="siteParameters_unique", columns={"projectSpecialty_id"})})
 * @ORM\HasLifecycleCallbacks
 */
class TransResSiteParameters {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $creator;

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
    private $updateDate;

    /**
     * Hematopathology or AP/CP
     *
     * @ORM\ManyToOne(targetEntity="App\TranslationalResearchBundle\Entity\SpecialtyList", cascade={"persist"})
     * @ORM\JoinColumn(name="projectSpecialty_id", referencedColumnName="id", nullable=true)
     */
    private $projectSpecialty;

    /**
     * invoice header
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresFromHeader;

    /**
     * invoice footer
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresFooter;

    /**
     * Default Invoice Logos
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_transResSiteParameters_transresLogo",
     *      joinColumns={@ORM\JoinColumn(name="transResSiteParameter_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="transresLogo_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $transresLogos;

    /**
     * Email body for notification email when Invoice PDF is sent to PI
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresNotificationEmail;

    /**
     * Email subject for notification email when Invoice PDF is sent to PI
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresNotificationEmailSubject;

    ////////// Invoice reminder email ////////////
    /**
     * Translational Research Unpaid Invoice Reminder Schedule in Months 
     * over due in months (integer), reminder interval in months (integer), max reminder count (integer)
     * 
     * @ORM\Column(type="string", nullable=true)
     */
    private $invoiceReminderSchedule;

    /**
     * Translational Research Reminder Email Subject
     * @ORM\Column(type="text", nullable=true)
     */
    private $invoiceReminderSubject;

    /**
     * Translational Research Unpaid Invoice Reminder Email Body
     * @ORM\Column(type="text", nullable=true)
     */
    private $invoiceReminderBody;

    /**
     * Translational Research Reminder Email - Send From the Following Address
     * @ORM\Column(type="text", nullable=true)
     */
    private $invoiceReminderEmail;
    ////////// EOF Invoice reminder email ////////////

    /**
     * Email body for notification email is being to send to the Request's PI when Request status is changed to "Completed and Notified"
     * @ORM\Column(type="text", nullable=true)
     */
    private $requestCompletedNotifiedEmail;

    /**
     * Email subject for notification email is being to send to to the Request's PI when Request status is changed to "Completed and Notified"
     * @ORM\Column(type="text", nullable=true)
     */
    private $requestCompletedNotifiedEmailSubject;

    /**
     * Invoice's invoiceSalesperson
     *
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="invoiceSalesperson", referencedColumnName="id", nullable=true)
     */
    private $invoiceSalesperson;


    /**
     * Default Accession Type used in the System column in the Work Request handsontable
     *
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\AccessionType")
     */
    private $accessionType;


    //Packing Slip
    /**
     * Default Packing Slip Logos
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_transResSiteParameters_transresPackingSlipLogo",
     *      joinColumns={@ORM\JoinColumn(name="transResSiteParameter_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="transresPackingSlipLogo_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $transresPackingSlipLogos;

    /**
     * Packing Slip
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresPackingSlipTitle;

    /**
     * Department of Pathology and Laboratory Medicine
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresPackingSlipHeadline1;

    /**
     * Translational Research Program
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresPackingSlipHeadline2;

    /**
     * Blue (HTML color value)
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresPackingSlipHeadlineColor;

    /**
     * Red (HTML Color Value)
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresPackingSlipHighlightedColor;

    /**
     * Comment for Request
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresPackingSlipSubHeading1;

    /**
     * List of Deliverables
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresPackingSlipSubHeading2;

    /**
     * Please contact us for more information about this slip.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresPackingSlipFooter1;

    /**
     * Translational Research Program * 1300 York Ave., F512, New York, NY 10065 * Tel (212) 746-62255
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transresPackingSlipFooter2;

    /**
     * Barcode size
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $barcodeSize;

    /**
     * Packing Slip font size
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $transresPackingSlipFontSize;

    //Project:
    /**
     * The answers you provide must reflect what has been requested in the approved IRB and the approved tissue request form.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $specimenDetailsComment;

    /**
     * 'NYP/WCM' Pathologist(s) Involved
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $institutionName;

    /**
     * from email address: trp-admin@med.cornell.edu
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $fromEmail;

    /**
     * Add trp@med.cornell.edu to site settings and use it for Cc for Work Request status change to "Completed" and "Completed and Notified"
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $notifyEmail;

    /**
     * Show TRP Message to Users
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $showMessageToUsers;

    /**
     * TRP Message to Users
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $messageToUsers;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $humanTissueFormNote;

    //Disable/Enable new project
    /**
     * a) Enable/Disable the display of each button (project category) on the “New Project Request page: https://view.med.cornell.edu/translational-research/project/new
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enableNewProjectOnSelector;
    /**
     * b) [Separately] Enable/Disable the display of each “New Project Request” link in the top Navbar
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enableNewProjectOnNavbar;
    /**
     * c) [Separately] Enable/Disable access to each “New Project Request” page URL (this is for users who might bookmark this page and try to return to it)
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enableNewProjectAccessPage;

    /**
     * Translational Research Email Notification Asking To Contact With Concerns:
     * Please review the deliverables and comments (if any), and if you have any concerns,
     * contact the Translational Research group by emailing User Name (email)...
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $emailNoteConcern;


    ////////// Project reminder ////////////
    //4 delay fields for review + 2 delay fields for missinginfo + 2 subject, body for review + 2 subject, body for missinginfo
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $projectReminderDelay_irb_review;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $projectReminderDelay_admin_review;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $projectReminderDelay_committee_review;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $projectReminderDelay_final_review;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $projectReminderDelay_irb_missinginfo;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $projectReminderDelay_admin_missinginfo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $projectReminderSubject_review;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $projectReminderBody_review;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $projectReminderSubject_missinginfo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $projectReminderBody_missinginfo;
    ////////// EOF Project reminder email ////////////


    ////////////// Pending Work Requests reminder email //////////////
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $pendingRequestReminderDelay;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pendingRequestReminderSubject;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pendingRequestReminderBody;
    ////////////// EOF Pending Work Requests reminder email //////////////

    ////////////// Completed Work Requests reminder email //////////////
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $completedRequestReminderDelay;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $completedRequestReminderSubject;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $completedRequestReminderBody;
    ////////////// EOF Completed Work Requests reminder email //////////////

    ////////////// Completed and Notified Work Requests without issued invoice reminder email //////////////
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $completedNoInvoiceRequestReminderDelay;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $completedNoInvoiceRequestReminderSubject;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $completedNoInvoiceRequestReminderBody;
    ////////////// EOF Completed Work Requests reminder email //////////////

    /**
     * showRemittance section
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $showRemittance;

    /**
     * Update parent Project Request’s Fund Number when New Work request’s number is submitted
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $updateProjectFundNumber;

    /**
     * Intake form
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_transResSiteParameters_transresIntakeForm",
     *      joinColumns={@ORM\JoinColumn(name="transResSiteParameter_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="transresIntakeForm_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $transresIntakeForms;

    ////////////// Budget Related Parameters /////////////////////
    /**
     * Over budget notification from: [default to trpadminMailingListEmail]: trp-admin@med.cornell.edu
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $overBudgetFromEmail;

    /**
     * Over budget notification subject:
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $overBudgetSubject;

    /**
     * Over budget notification body
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $overBudgetBody;

    /**
     * Send over budget notifications: “yes/no” (default to “yes”)
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $overBudgetSendEmail;

    //Approved Budget (6 fields)
    /**
     * Send ‘approved project budget’ update notifications: (yes/no, default to “yes)
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $approvedBudgetSendEmail;

    /**
     * Approved budget amount update notification from: [default to trpadminMailingListEmail]: trp-admin@med.cornell.edu
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $approvedBudgetFromEmail;

    /**
     * Approved budget amount update notification email subject
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $approvedBudgetSubject;

    /**
     * Approved budget update notification email body
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $approvedBudgetBody;

    /**
     * Approved budget limit removal notification email subject
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $budgetLimitRemovalSubject;
    
    /**
     * Approved budget limit removal notification email body
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $budgetLimitRemovalBody;

    /**
     * Base the notification regarding exceeding the budget on whether the following value exceeds the project’s budget:
     * [Total (Charge and Subsidy) / Charge (without Subsidy)]
     * Set to “Charge (without Subsidy)” by default.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $overBudgetCalculation;
    ////////////// EOF Budget Related Parameters /////////////////////

    ///////////// Project expirations ///////////////
    /**
     * Default duration of a project request before expiration in months (leave blank for no notification): [12] - default to 12
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $projectExprDuration;

    /**
     * Default number of months in advance of the project request expiration date when the automatic notification requesting a
     * progress report should be sent (leave blank to never send this notification): [6] - default to 6
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $projectExprDurationEmail;

    /**
     * Default number of days after the project request expiration date when the project request status should be set to 'Closed'
     * (leave blank to never auto-close): [90] - default to 90
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $projectExprDurationChangeStatus;

    /**
     * Apply project request expiration notification rule to this project request type: [Yes/No] and default to “Yes” by default
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $projectExprApply;

    /**
     * Apply project request auto-closure after expiration rule to this project request type: [Yes/No] and default to “Yes” by default
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $projectExprApplyChangeStatus;

    //8 fields
    /**
     * 1) Automatically send a reminder email to submit project progress report for expiring projects: [Yes/No] (Default to yes)
     * Similar to "Apply project request expiration notification rule to this project request type"?
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sendExpriringProjectEmail;

    /**
     * 2) Project Request Upcoming Expiration Notification E-Mail sent from: [email address], default to trp-admin’s
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $expiringProjectEmailFrom;

    /**
     * 3) Project Request Upcoming Expiration Notification E-Mail Subject: “[TRP] Please submit a progress report for PROJECT-ID”
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $expiringProjectEmailSubject;

    /**
     * 4) Project Request Upcoming Expiration Notification E-Mail Body:
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $expiringProjectEmailBody;

    /**
     * 5) Automatically send a reminder email to the [TRP] system administrator for expired projects: [Yes/No] (Default to yes)
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sendExpiredProjectEmail;

    /**
     * 6) Project Request Expiration Notification E-Mail sent from: [email address], default to trp-admin’s
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $expiredProjectEmailFrom;

    /**
     * 7) Project Request Expiration Notification E-Mail Subject: “[TRP] Project PROJECT-ID has reached its expiration date”
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $expiredProjectEmailSubject;

    /**
     * 8) Project Request Expiration Notification E-Mail Body:
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $expiredProjectEmailBody;

    ///////////// EOF Project expirations ///////////////

    public function __construct($user=null) {
        $this->setCreator($user);
        $this->setCreateDate(new \DateTime());

        $this->transresLogos = new ArrayCollection();
        $this->transresPackingSlipLogos = new ArrayCollection();
        $this->transresIntakeForms = new ArrayCollection();

        $this->setEnableNewProjectAccessPage(true);
        $this->setEnableNewProjectOnNavbar(true);
        $this->setEnableNewProjectOnSelector(true);

        $this->setOverBudgetSendEmail(true);
        $this->setApprovedBudgetSendEmail(true);
    }



    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
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
     * @return mixed
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param mixed $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return \DateTime
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
    public function getTransresFromHeader()
    {
        return $this->transresFromHeader;
    }

    /**
     * @param mixed $transresFromHeader
     */
    public function setTransresFromHeader($transresFromHeader)
    {
        $this->transresFromHeader = $transresFromHeader;
    }

    /**
     * @return mixed
     */
    public function getTransresFooter()
    {
        return $this->transresFooter;
    }

    /**
     * @param mixed $transresFooter
     */
    public function setTransresFooter($transresFooter)
    {
        $this->transresFooter = $transresFooter;
    }

    public function addTransresLogo($item)
    {
        if( $item && !$this->transresLogos->contains($item) ) {
            $this->transresLogos->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeTransresLogo($item)
    {
        $this->transresLogos->removeElement($item);
        $item->clearUseObject();
    }
    public function getTransresLogos()
    {
        return $this->transresLogos;
    }

    /**
     * @return mixed
     */
    public function getTransresNotificationEmail()
    {
        return $this->transresNotificationEmail;
    }

    /**
     * @param mixed $transresNotificationEmail
     */
    public function setTransresNotificationEmail($transresNotificationEmail)
    {
        $this->transresNotificationEmail = $transresNotificationEmail;
    }

    /**
     * @return mixed
     */
    public function getTransresNotificationEmailSubject()
    {
        return $this->transresNotificationEmailSubject;
    }

    /**
     * @param mixed $transresNotificationEmailSubject
     */
    public function setTransresNotificationEmailSubject($transresNotificationEmailSubject)
    {
        $this->transresNotificationEmailSubject = $transresNotificationEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getRequestCompletedNotifiedEmail()
    {
        return $this->requestCompletedNotifiedEmail;
    }

    /**
     * @param mixed $requestCompletedNotifiedEmail
     */
    public function setRequestCompletedNotifiedEmail($requestCompletedNotifiedEmail)
    {
        $this->requestCompletedNotifiedEmail = $requestCompletedNotifiedEmail;
    }

    /**
     * @return mixed
     */
    public function getRequestCompletedNotifiedEmailSubject()
    {
        return $this->requestCompletedNotifiedEmailSubject;
    }

    /**
     * @param mixed $requestCompletedNotifiedEmailSubject
     */
    public function setRequestCompletedNotifiedEmailSubject($requestCompletedNotifiedEmailSubject)
    {
        $this->requestCompletedNotifiedEmailSubject = $requestCompletedNotifiedEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getInvoiceSalesperson()
    {
        return $this->invoiceSalesperson;
    }

    /**
     * @param mixed $invoiceSalesperson
     */
    public function setInvoiceSalesperson($invoiceSalesperson)
    {
        $this->invoiceSalesperson = $invoiceSalesperson;
    }

    /**
     * @return mixed
     */
    public function getAccessionType()
    {
        return $this->accessionType;
    }

    /**
     * @param mixed $accessionType
     */
    public function setAccessionType($accessionType)
    {
        $this->accessionType = $accessionType;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipLogos()
    {
        return $this->transresPackingSlipLogos;
    }
    public function addTransresPackingSlipLogo($item)
    {
        if( $item && !$this->transresPackingSlipLogos->contains($item) ) {
            $this->transresPackingSlipLogos->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeTransresPackingSlipLogo($item)
    {
        $this->transresPackingSlipLogos->removeElement($item);
        $item->clearUseObject();
    }

    /**
     * @return mixed
     */
    public function getTransresIntakeForms()
    {
        return $this->transresIntakeForms;
    }
    public function addTransresIntakeForm($item)
    {
        if( $item && !$this->transresIntakeForms->contains($item) ) {
            $this->transresIntakeForms->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeTransresIntakeForm($item)
    {
        $this->transresIntakeForms->removeElement($item);
        $item->clearUseObject();
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipTitle()
    {
        return $this->transresPackingSlipTitle;
    }

    /**
     * @param mixed $transresPackingSlipTitle
     */
    public function setTransresPackingSlipTitle($transresPackingSlipTitle)
    {
        $this->transresPackingSlipTitle = $transresPackingSlipTitle;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipHeadline1()
    {
        return $this->transresPackingSlipHeadline1;
    }

    /**
     * @param mixed $transresPackingSlipHeadline1
     */
    public function setTransresPackingSlipHeadline1($transresPackingSlipHeadline1)
    {
        $this->transresPackingSlipHeadline1 = $transresPackingSlipHeadline1;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipHeadline2()
    {
        return $this->transresPackingSlipHeadline2;
    }

    /**
     * @param mixed $transresPackingSlipHeadline2
     */
    public function setTransresPackingSlipHeadline2($transresPackingSlipHeadline2)
    {
        $this->transresPackingSlipHeadline2 = $transresPackingSlipHeadline2;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipHeadlineColor()
    {
        return $this->transresPackingSlipHeadlineColor;
    }

    /**
     * @param mixed $transresPackingSlipHeadlineColor
     */
    public function setTransresPackingSlipHeadlineColor($transresPackingSlipHeadlineColor)
    {
        $this->transresPackingSlipHeadlineColor = $transresPackingSlipHeadlineColor;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipHighlightedColor()
    {
        return $this->transresPackingSlipHighlightedColor;
    }

    /**
     * @param mixed $transresPackingSlipHighlightedColor
     */
    public function setTransresPackingSlipHighlightedColor($transresPackingSlipHighlightedColor)
    {
        $this->transresPackingSlipHighlightedColor = $transresPackingSlipHighlightedColor;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipSubHeading1()
    {
        return $this->transresPackingSlipSubHeading1;
    }

    /**
     * @param mixed $transresPackingSlipSubHeading1
     */
    public function setTransresPackingSlipSubHeading1($transresPackingSlipSubHeading1)
    {
        $this->transresPackingSlipSubHeading1 = $transresPackingSlipSubHeading1;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipSubHeading2()
    {
        return $this->transresPackingSlipSubHeading2;
    }

    /**
     * @param mixed $transresPackingSlipSubHeading2
     */
    public function setTransresPackingSlipSubHeading2($transresPackingSlipSubHeading2)
    {
        $this->transresPackingSlipSubHeading2 = $transresPackingSlipSubHeading2;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipFooter1()
    {
        return $this->transresPackingSlipFooter1;
    }

    /**
     * @param mixed $transresPackingSlipFooter1
     */
    public function setTransresPackingSlipFooter1($transresPackingSlipFooter1)
    {
        $this->transresPackingSlipFooter1 = $transresPackingSlipFooter1;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipFooter2()
    {
        return $this->transresPackingSlipFooter2;
    }

    /**
     * @param mixed $transresPackingSlipFooter2
     */
    public function setTransresPackingSlipFooter2($transresPackingSlipFooter2)
    {
        $this->transresPackingSlipFooter2 = $transresPackingSlipFooter2;
    }

    /**
     * @return mixed
     */
    public function getBarcodeSize()
    {
        return $this->barcodeSize;
    }

    /**
     * @param mixed $barcodeSize
     */
    public function setBarcodeSize($barcodeSize)
    {
        $this->barcodeSize = $barcodeSize;
    }

    /**
     * @return mixed
     */
    public function getTransresPackingSlipFontSize()
    {
        return $this->transresPackingSlipFontSize;
    }

    /**
     * @param mixed $transresPackingSlipFontSize
     */
    public function setTransresPackingSlipFontSize($transresPackingSlipFontSize)
    {
        $this->transresPackingSlipFontSize = $transresPackingSlipFontSize;
    }

    /**
     * @return mixed
     */
    public function getSpecimenDetailsComment()
    {
        return $this->specimenDetailsComment;
    }

    /**
     * @param mixed $specimenDetailsComment
     */
    public function setSpecimenDetailsComment($specimenDetailsComment)
    {
        $this->specimenDetailsComment = $specimenDetailsComment;
    }

    /**
     * @return mixed
     */
    public function getInstitutionName()
    {
        return $this->institutionName;
    }

    /**
     * @param mixed $institutionName
     */
    public function setInstitutionName($institutionName)
    {
        $this->institutionName = $institutionName;
    }

    /**
     * @return mixed
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * @param mixed $fromEmail
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
    }

    /**
     * @return mixed
     */
    public function getNotifyEmail()
    {
        return $this->notifyEmail;
    }

    /**
     * @param mixed $notifyEmail
     */
    public function setNotifyEmail($notifyEmail)
    {
        $this->notifyEmail = $notifyEmail;
    }

    /**
     * @return mixed
     */
    public function getEmailNoteConcern()
    {
        return $this->emailNoteConcern;
    }

    /**
     * @param mixed $emailNoteConcern
     */
    public function setEmailNoteConcern($emailNoteConcern)
    {
        $this->emailNoteConcern = $emailNoteConcern;
    }

    /**
     * @return mixed
     */
    public function getInvoiceReminderSchedule()
    {
        return $this->invoiceReminderSchedule;
    }

    /**
     * @param mixed $invoiceReminderSchedule
     */
    public function setInvoiceReminderSchedule($invoiceReminderSchedule)
    {
        $this->invoiceReminderSchedule = $invoiceReminderSchedule;
    }

    /**
     * @return mixed
     */
    public function getInvoiceReminderSubject()
    {
        return $this->invoiceReminderSubject;
    }

    /**
     * @param mixed $invoiceReminderSubject
     */
    public function setInvoiceReminderSubject($invoiceReminderSubject)
    {
        $this->invoiceReminderSubject = $invoiceReminderSubject;
    }

    /**
     * @return mixed
     */
    public function getInvoiceReminderBody()
    {
        return $this->invoiceReminderBody;
    }

    /**
     * @param mixed $invoiceReminderBody
     */
    public function setInvoiceReminderBody($invoiceReminderBody)
    {
        $this->invoiceReminderBody = $invoiceReminderBody;
    }

    /**
     * @return mixed
     */
    public function getInvoiceReminderEmail()
    {
        return $this->invoiceReminderEmail;
    }

    /**
     * @param mixed $invoiceReminderEmail
     */
    public function setInvoiceReminderEmail($invoiceReminderEmail)
    {
        $this->invoiceReminderEmail = $invoiceReminderEmail;
    }

    
    /**
     * @return mixed
     */
    public function getProjectReminderDelayIrbReview()
    {
        return $this->projectReminderDelay_irb_review;
    }

    /**
     * @param mixed $projectReminderDelay_irb_review
     */
    public function setProjectReminderDelayIrbReview($projectReminderDelay_irb_review)
    {
        $this->projectReminderDelay_irb_review = $projectReminderDelay_irb_review;
    }

    /**
     * @return mixed
     */
    public function getProjectReminderDelayAdminReview()
    {
        return $this->projectReminderDelay_admin_review;
    }

    /**
     * @param mixed $projectReminderDelay_admin_review
     */
    public function setProjectReminderDelayAdminReview($projectReminderDelay_admin_review)
    {
        $this->projectReminderDelay_admin_review = $projectReminderDelay_admin_review;
    }

    /**
     * @return mixed
     */
    public function getProjectReminderDelayCommitteeReview()
    {
        return $this->projectReminderDelay_committee_review;
    }

    /**
     * @param mixed $projectReminderDelay_committee_review
     */
    public function setProjectReminderDelayCommitteeReview($projectReminderDelay_committee_review)
    {
        $this->projectReminderDelay_committee_review = $projectReminderDelay_committee_review;
    }

    /**
     * @return mixed
     */
    public function getProjectReminderDelayFinalReview()
    {
        return $this->projectReminderDelay_final_review;
    }

    /**
     * @param mixed $projectReminderDelay_final_review
     */
    public function setProjectReminderDelayFinalReview($projectReminderDelay_final_review)
    {
        $this->projectReminderDelay_final_review = $projectReminderDelay_final_review;
    }

    /**
     * @return mixed
     */
    public function getProjectReminderDelayIrbMissinginfo()
    {
        return $this->projectReminderDelay_irb_missinginfo;
    }

    /**
     * @param mixed $projectReminderDelay_irb_missinginfo
     */
    public function setProjectReminderDelayIrbMissinginfo($projectReminderDelay_irb_missinginfo)
    {
        $this->projectReminderDelay_irb_missinginfo = $projectReminderDelay_irb_missinginfo;
    }

    /**
     * @return mixed
     */
    public function getProjectReminderDelayAdminMissinginfo()
    {
        return $this->projectReminderDelay_admin_missinginfo;
    }

    /**
     * @param mixed $projectReminderDelay_admin_missinginfo
     */
    public function setProjectReminderDelayAdminMissinginfo($projectReminderDelay_admin_missinginfo)
    {
        $this->projectReminderDelay_admin_missinginfo = $projectReminderDelay_admin_missinginfo;
    }

    /**
     * @return mixed
     */
    public function getProjectReminderSubjectReview()
    {
        return $this->projectReminderSubject_review;
    }

    /**
     * @param mixed $projectReminderSubject_review
     */
    public function setProjectReminderSubjectReview($projectReminderSubject_review)
    {
        $this->projectReminderSubject_review = $projectReminderSubject_review;
    }

    /**
     * @return mixed
     */
    public function getProjectReminderBodyReview()
    {
        return $this->projectReminderBody_review;
    }

    /**
     * @param mixed $projectReminderBody_review
     */
    public function setProjectReminderBodyReview($projectReminderBody_review)
    {
        $this->projectReminderBody_review = $projectReminderBody_review;
    }

    /**
     * @return mixed
     */
    public function getProjectReminderSubjectMissinginfo()
    {
        return $this->projectReminderSubject_missinginfo;
    }

    /**
     * @param mixed $projectReminderSubject_missinginfo
     */
    public function setProjectReminderSubjectMissinginfo($projectReminderSubject_missinginfo)
    {
        $this->projectReminderSubject_missinginfo = $projectReminderSubject_missinginfo;
    }

    /**
     * @return mixed
     */
    public function getProjectReminderBodyMissinginfo()
    {
        return $this->projectReminderBody_missinginfo;
    }

    /**
     * @param mixed $projectReminderBody_missinginfo
     */
    public function setProjectReminderBodyMissinginfo($projectReminderBody_missinginfo)
    {
        $this->projectReminderBody_missinginfo = $projectReminderBody_missinginfo;
    }

    /**
     * @return mixed
     */
    public function getPendingRequestReminderDelay()
    {
        return $this->pendingRequestReminderDelay;
    }

    /**
     * @param mixed $pendingRequestReminderDelay
     */
    public function setPendingRequestReminderDelay($pendingRequestReminderDelay)
    {
        $this->pendingRequestReminderDelay = $pendingRequestReminderDelay;
    }

    /**
     * @return mixed
     */
    public function getPendingRequestReminderSubject()
    {
        return $this->pendingRequestReminderSubject;
    }

    /**
     * @param mixed $pendingRequestReminderSubject
     */
    public function setPendingRequestReminderSubject($pendingRequestReminderSubject)
    {
        $this->pendingRequestReminderSubject = $pendingRequestReminderSubject;
    }

    /**
     * @return mixed
     */
    public function getPendingRequestReminderBody()
    {
        return $this->pendingRequestReminderBody;
    }

    /**
     * @param mixed $pendingRequestReminderBody
     */
    public function setPendingRequestReminderBody($pendingRequestReminderBody)
    {
        $this->pendingRequestReminderBody = $pendingRequestReminderBody;
    }

    /**
     * @return mixed
     */
    public function getCompletedRequestReminderDelay()
    {
        return $this->completedRequestReminderDelay;
    }

    /**
     * @param mixed $completedRequestReminderDelay
     */
    public function setCompletedRequestReminderDelay($completedRequestReminderDelay)
    {
        $this->completedRequestReminderDelay = $completedRequestReminderDelay;
    }

    /**
     * @return mixed
     */
    public function getCompletedRequestReminderSubject()
    {
        return $this->completedRequestReminderSubject;
    }

    /**
     * @param mixed $completedRequestReminderSubject
     */
    public function setCompletedRequestReminderSubject($completedRequestReminderSubject)
    {
        $this->completedRequestReminderSubject = $completedRequestReminderSubject;
    }

    /**
     * @return mixed
     */
    public function getCompletedRequestReminderBody()
    {
        return $this->completedRequestReminderBody;
    }

    /**
     * @param mixed $completedRequestReminderBody
     */
    public function setCompletedRequestReminderBody($completedRequestReminderBody)
    {
        $this->completedRequestReminderBody = $completedRequestReminderBody;
    }

    /**
     * @return mixed
     */
    public function getCompletedNoInvoiceRequestReminderDelay()
    {
        return $this->completedNoInvoiceRequestReminderDelay;
    }

    /**
     * @param mixed $completedNoInvoiceRequestReminderDelay
     */
    public function setCompletedNoInvoiceRequestReminderDelay($completedNoInvoiceRequestReminderDelay)
    {
        $this->completedNoInvoiceRequestReminderDelay = $completedNoInvoiceRequestReminderDelay;
    }

    /**
     * @return mixed
     */
    public function getCompletedNoInvoiceRequestReminderSubject()
    {
        return $this->completedNoInvoiceRequestReminderSubject;
    }

    /**
     * @param mixed $completedNoInvoiceRequestReminderSubject
     */
    public function setCompletedNoInvoiceRequestReminderSubject($completedNoInvoiceRequestReminderSubject)
    {
        $this->completedNoInvoiceRequestReminderSubject = $completedNoInvoiceRequestReminderSubject;
    }

    /**
     * @return mixed
     */
    public function getCompletedNoInvoiceRequestReminderBody()
    {
        return $this->completedNoInvoiceRequestReminderBody;
    }

    /**
     * @param mixed $completedNoInvoiceRequestReminderBody
     */
    public function setCompletedNoInvoiceRequestReminderBody($completedNoInvoiceRequestReminderBody)
    {
        $this->completedNoInvoiceRequestReminderBody = $completedNoInvoiceRequestReminderBody;
    }

    /**
     * @return mixed
     */
    public function getShowMessageToUsers()
    {
        return $this->showMessageToUsers;
    }

    /**
     * @param mixed $showMessageToUsers
     */
    public function setShowMessageToUsers($showMessageToUsers)
    {
        $this->showMessageToUsers = $showMessageToUsers;
    }

    /**
     * @return mixed
     */
    public function getMessageToUsers()
    {
        return $this->messageToUsers;
    }

    /**
     * @param mixed $messageToUsers
     */
    public function setMessageToUsers($messageToUsers)
    {
        $this->messageToUsers = $messageToUsers;
    }

    /**
     * @return mixed
     */
    public function getEnableNewProjectOnSelector()
    {
        return $this->enableNewProjectOnSelector;
    }

    /**
     * @param mixed $enableNewProjectOnSelector
     */
    public function setEnableNewProjectOnSelector($enableNewProjectOnSelector)
    {
        $this->enableNewProjectOnSelector = $enableNewProjectOnSelector;
    }

    /**
     * @return mixed
     */
    public function getEnableNewProjectOnNavbar()
    {
        return $this->enableNewProjectOnNavbar;
    }

    /**
     * @param mixed $enableNewProjectOnNavbar
     */
    public function setEnableNewProjectOnNavbar($enableNewProjectOnNavbar)
    {
        $this->enableNewProjectOnNavbar = $enableNewProjectOnNavbar;
    }

    /**
     * @return mixed
     */
    public function getEnableNewProjectAccessPage()
    {
        return $this->enableNewProjectAccessPage;
    }

    /**
     * @param mixed $enableNewProjectAccessPage
     */
    public function setEnableNewProjectAccessPage($enableNewProjectAccessPage)
    {
        $this->enableNewProjectAccessPage = $enableNewProjectAccessPage;
    }

    /**
     * @return mixed
     */
    public function getHumanTissueFormNote()
    {
        return $this->humanTissueFormNote;
    }

    /**
     * @param mixed $humanTissueFormNote
     */
    public function setHumanTissueFormNote($humanTissueFormNote)
    {
        $this->humanTissueFormNote = $humanTissueFormNote;
    }

    /**
     * @return mixed
     */
    public function getShowRemittance()
    {
        return $this->showRemittance;
    }

    /**
     * @param mixed $showRemittance
     */
    public function setShowRemittance($showRemittance)
    {
        $this->showRemittance = $showRemittance;
    }

    /**
     * @return mixed
     */
    public function getUpdateProjectFundNumber()
    {
        return $this->updateProjectFundNumber;
    }

    /**
     * @param mixed $updateProjectFundNumber
     */
    public function setUpdateProjectFundNumber($updateProjectFundNumber)
    {
        $this->updateProjectFundNumber = $updateProjectFundNumber;
    }

    /**
     * @return mixed
     */
    public function getOverBudgetFromEmail()
    {
        return $this->overBudgetFromEmail;
    }

    /**
     * @param mixed $overBudgetFromEmail
     */
    public function setOverBudgetFromEmail($overBudgetFromEmail)
    {
        $this->overBudgetFromEmail = $overBudgetFromEmail;
    }

    /**
     * @return mixed
     */
    public function getOverBudgetSubject()
    {
        return $this->overBudgetSubject;
    }

    /**
     * @param mixed $overBudgetSubject
     */
    public function setOverBudgetSubject($overBudgetSubject)
    {
        $this->overBudgetSubject = $overBudgetSubject;
    }

    /**
     * @return mixed
     */
    public function getOverBudgetBody()
    {
        return $this->overBudgetBody;
    }

    /**
     * @param mixed $overBudgetBody
     */
    public function setOverBudgetBody($overBudgetBody)
    {
        $this->overBudgetBody = $overBudgetBody;
    }

    /**
     * @return mixed
     */
    public function getOverBudgetSendEmail()
    {
        return $this->overBudgetSendEmail;
    }

    /**
     * @param mixed $overBudgetSendEmail
     */
    public function setOverBudgetSendEmail($overBudgetSendEmail)
    {
        $this->overBudgetSendEmail = $overBudgetSendEmail;
    }

    /**
     * @return mixed
     */
    public function getApprovedBudgetSendEmail()
    {
        return $this->approvedBudgetSendEmail;
    }

    /**
     * @param mixed $approvedBudgetSendEmail
     */
    public function setApprovedBudgetSendEmail($approvedBudgetSendEmail)
    {
        $this->approvedBudgetSendEmail = $approvedBudgetSendEmail;
    }

    /**
     * @return mixed
     */
    public function getApprovedBudgetFromEmail()
    {
        return $this->approvedBudgetFromEmail;
    }

    /**
     * @param mixed $approvedBudgetFromEmail
     */
    public function setApprovedBudgetFromEmail($approvedBudgetFromEmail)
    {
        $this->approvedBudgetFromEmail = $approvedBudgetFromEmail;
    }

    /**
     * @return mixed
     */
    public function getApprovedBudgetSubject()
    {
        return $this->approvedBudgetSubject;
    }

    /**
     * @param mixed $approvedBudgetSubject
     */
    public function setApprovedBudgetSubject($approvedBudgetSubject)
    {
        $this->approvedBudgetSubject = $approvedBudgetSubject;
    }

    /**
     * @return mixed
     */
    public function getApprovedBudgetBody()
    {
        return $this->approvedBudgetBody;
    }

    /**
     * @param mixed $approvedBudgetBody
     */
    public function setApprovedBudgetBody($approvedBudgetBody)
    {
        $this->approvedBudgetBody = $approvedBudgetBody;
    }

    /**
     * @return mixed
     */
    public function getBudgetLimitRemovalSubject()
    {
        return $this->budgetLimitRemovalSubject;
    }

    /**
     * @param mixed $budgetLimitRemovalSubject
     */
    public function setBudgetLimitRemovalSubject($budgetLimitRemovalSubject)
    {
        $this->budgetLimitRemovalSubject = $budgetLimitRemovalSubject;
    }

    /**
     * @return mixed
     */
    public function getBudgetLimitRemovalBody()
    {
        return $this->budgetLimitRemovalBody;
    }

    /**
     * @param mixed $budgetLimitRemovalBody
     */
    public function setBudgetLimitRemovalBody($budgetLimitRemovalBody)
    {
        $this->budgetLimitRemovalBody = $budgetLimitRemovalBody;
    }

    /**
     * @return mixed
     */
    public function getOverBudgetCalculation()
    {
        return $this->overBudgetCalculation;
    }

    /**
     * @param mixed $overBudgetCalculation
     */
    public function setOverBudgetCalculation($overBudgetCalculation)
    {
        $this->overBudgetCalculation = $overBudgetCalculation;
    }

    /**
     * @return mixed
     */
    public function getProjectExprDuration()
    {
        return $this->projectExprDuration;
    }

    /**
     * @param mixed $projectExprDuration
     */
    public function setProjectExprDuration($projectExprDuration)
    {
        $this->projectExprDuration = $projectExprDuration;
    }

    /**
     * @return mixed
     */
    public function getProjectExprDurationEmail()
    {
        return $this->projectExprDurationEmail;
    }

    /**
     * @param mixed $projectExprDurationEmail
     */
    public function setProjectExprDurationEmail($projectExprDurationEmail)
    {
        $this->projectExprDurationEmail = $projectExprDurationEmail;
    }

    /**
     * @return mixed
     */
    public function getProjectExprDurationChangeStatus()
    {
        return $this->projectExprDurationChangeStatus;
    }

    /**
     * @param mixed $projectExprDurationChangeStatus
     */
    public function setProjectExprDurationChangeStatus($projectExprDurationChangeStatus)
    {
        $this->projectExprDurationChangeStatus = $projectExprDurationChangeStatus;
    }

    /**
     * @return mixed
     */
    public function getProjectExprApply()
    {
        return $this->projectExprApply;
    }

    /**
     * @param mixed $projectExprApply
     */
    public function setProjectExprApply($projectExprApply)
    {
        $this->projectExprApply = $projectExprApply;
    }

    /**
     * @return mixed
     */
    public function getProjectExprApplyChangeStatus()
    {
        return $this->projectExprApplyChangeStatus;
    }

    /**
     * @param mixed $projectExprApplyChangeStatus
     */
    public function setProjectExprApplyChangeStatus($projectExprApplyChangeStatus)
    {
        $this->projectExprApplyChangeStatus = $projectExprApplyChangeStatus;
    }

    /**
     * @return mixed
     */
    public function getSendExpriringProjectEmail()
    {
        return $this->sendExpriringProjectEmail;
    }

    /**
     * @param mixed $sendExpriringProjectEmail
     */
    public function setSendExpriringProjectEmail($sendExpriringProjectEmail)
    {
        $this->sendExpriringProjectEmail = $sendExpriringProjectEmail;
    }

    /**
     * @return mixed
     */
    public function getExpiringProjectEmailFrom()
    {
        return $this->expiringProjectEmailFrom;
    }

    /**
     * @param mixed $expiringProjectEmailFrom
     */
    public function setExpiringProjectEmailFrom($expiringProjectEmailFrom)
    {
        $this->expiringProjectEmailFrom = $expiringProjectEmailFrom;
    }

    /**
     * @return mixed
     */
    public function getExpiringProjectEmailSubject()
    {
        return $this->expiringProjectEmailSubject;
    }

    /**
     * @param mixed $expiringProjectEmailSubject
     */
    public function setExpiringProjectEmailSubject($expiringProjectEmailSubject)
    {
        $this->expiringProjectEmailSubject = $expiringProjectEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getExpiringProjectEmailBody()
    {
        return $this->expiringProjectEmailBody;
    }

    /**
     * @param mixed $expiringProjectEmailBody
     */
    public function setExpiringProjectEmailBody($expiringProjectEmailBody)
    {
        $this->expiringProjectEmailBody = $expiringProjectEmailBody;
    }

    /**
     * @return mixed
     */
    public function getSendExpiredProjectEmail()
    {
        return $this->sendExpiredProjectEmail;
    }

    /**
     * @param mixed $sendExpiredProjectEmail
     */
    public function setSendExpiredProjectEmail($sendExpiredProjectEmail)
    {
        $this->sendExpiredProjectEmail = $sendExpiredProjectEmail;
    }

    /**
     * @return mixed
     */
    public function getExpiredProjectEmailFrom()
    {
        return $this->expiredProjectEmailFrom;
    }

    /**
     * @param mixed $expiredProjectEmailFrom
     */
    public function setExpiredProjectEmailFrom($expiredProjectEmailFrom)
    {
        $this->expiredProjectEmailFrom = $expiredProjectEmailFrom;
    }

    /**
     * @return mixed
     */
    public function getExpiredProjectEmailSubject()
    {
        return $this->expiredProjectEmailSubject;
    }

    /**
     * @param mixed $expiredProjectEmailSubject
     */
    public function setExpiredProjectEmailSubject($expiredProjectEmailSubject)
    {
        $this->expiredProjectEmailSubject = $expiredProjectEmailSubject;
    }

    /**
     * @return mixed
     */
    public function getExpiredProjectEmailBody()
    {
        return $this->expiredProjectEmailBody;
    }

    /**
     * @param mixed $expiredProjectEmailBody
     */
    public function setExpiredProjectEmailBody($expiredProjectEmailBody)
    {
        $this->expiredProjectEmailBody = $expiredProjectEmailBody;
    }

    


    public function __toString(){
        if( $this->getProjectSpecialty() ) {
            $name = $this->getProjectSpecialty()->getName();
        } else {
            $name = "Default";
        }
        return "Site Parameters for ".$name;
    }




}