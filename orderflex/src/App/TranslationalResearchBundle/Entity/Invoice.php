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
 * User: ch3
 * Date: 11/21/2017
 * Time: 12:10 PM
 */

namespace App\TranslationalResearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Table(name: 'transres_invoice')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Invoice {

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $submitter;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'updateUser', referencedColumnName: 'id', nullable: true)]
    private $updateUser;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $createDate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updateDate;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $oid;

//    /**
    //     * @ORM\ManyToOne(targetEntity="TransResRequest", mappedBy="invoices")
    //     */
    //    private $transresRequests;
    #[ORM\ManyToOne(targetEntity: 'TransResRequest', inversedBy: 'invoices')]
    #[ORM\JoinColumn(name: 'transresRequest_id', referencedColumnName: 'id')]
    private $transresRequest;

    /**
     * The same as OID (remove it?)
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $invoiceNumber;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $dueDate;

    /**
     * Is not the same as Request's contact (Billing Contact). Pre-populated from default salesperson setting
     */
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'salesperson', referencedColumnName: 'id', nullable: true)]
    private $salesperson;

//    /**
    //     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\User")
    //     * @ORM\JoinTable(name="transres_invoice_principalinvestigator",
    //     *      joinColumns={@ORM\JoinColumn(name="invoice_id", referencedColumnName="id")},
    //     *      inverseJoinColumns={@ORM\JoinColumn(name="principalinvestigator_id", referencedColumnName="id")}
    //     * )
    //     **/
    //    private $principalInvestigators;
    /**
     * Pre-Populated by Request's contact (Billing Contact: "Bill To")
     */
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'principalInvestigator', referencedColumnName: 'id', nullable: true)]
    private $principalInvestigator;

    /**
     * Billing contact (from PI side) is populated from Request's  billing contact ($contact)
     */
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'billingContact', referencedColumnName: 'id', nullable: true)]
    private $billingContact;

    /**
     * Invoice status
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $status;
    
    /**
     * Generated Invoices
     **/
    #[ORM\JoinTable(name: 'transres_invoice_document')]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'DESC'])]
    private $documents;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $invoiceFrom;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $invoiceTo;

    /**
     * Make check payable & mail to: Weill Cornell Medicine, 1300 York Ave, C302/Box69, New York, NY 10065 (Attn: John Dow)
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $footer;

    /**
     * Tel: (212) 111-1111 Fax: (212) 111-1111 Email: email@med.cornell.edu
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $footer2;

    /**
     * Detach and return with payment
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $footer3;

    /**
     * Discount numeric
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $discountNumeric;

    /**
     * Discount numeric
     */
    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    private $discountPercent;

    /**
     * Administrative Fee
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $administrativeFee;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $subTotal;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $total;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $paid;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $due;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $subsidy;

    #[ORM\OneToMany(targetEntity: 'InvoiceItem', mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private $invoiceItems;

//    /**
    //     * @ORM\OneToMany(targetEntity="InvoiceAddItem", mappedBy="invoice", cascade={"persist","remove"})
    //     */
    //    private $invoiceAddItems;
    #[ORM\Column(type: 'integer', nullable: true)]
    private $version;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $latestVersion;

    /**
     * fundedAccountNumber - pre-populated from request's $fundedAccountNumber
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $fundedAccountNumber;

    /**
     * irbNumber - pre-populated from project's $irbNumber
     * NOT USED; project's getIrbIacucNumber() is used to show IRB(IACUC) number in the invoice's list, invoice show and PDF)
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $irbNumber;

//    /**
    //     * iacucNumber - pre-populated from project's $iacucNumber
    //     * @ORM\Column(type="string", nullable=true)
    //     */
    //    private $iacucNumber;
    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $issuedDate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $paidDate;

    /**
     * Invoice Last Reminder Sent Date
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $invoiceLastReminderSentDate;

    /**
     * Count of the invoice reminder emails
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $invoiceReminderCount;
    


    public function __construct($user=null) {
        $this->setSubmitter($user);
        $this->setCreateDate(new \DateTime());
        $this->setVersion(1);

        $this->invoiceItems = new ArrayCollection();
        $this->documents = new ArrayCollection();
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
    public function getLatestVersion()
    {
        return $this->latestVersion;
    }

    /**
     * @param mixed $latestVersion
     */
    public function setLatestVersion($latestVersion)
    {
        $this->latestVersion = $latestVersion;
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
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
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

    #[ORM\PreUpdate]
    public function setUpdateDate()
    {
        $this->updateDate = new \DateTime();
    }

    /**
     * @return string
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * Invoice number
     * @param string $oid
     */
    public function setOid($oid)
    {
        $this->oid = $oid;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * @param string $invoiceNumber
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    public function reSetDueDate()
    {
        //pre-populate dueDate +30 days
        $dueDateStr = date('Y-m-d', strtotime("+30 days"));
        $dueDate = new \DateTime($dueDateStr);
        $this->setDueDate($dueDate);
    }

    /**
     * @return mixed
     */
    public function getSubsidy()
    {
        return $this->subsidy;
    }

    /**
     * @param mixed $subsidy
     */
    public function setSubsidy($subsidy)
    {
        $this->subsidy = $subsidy;
    }

    /**
     * @return mixed
     */
    public function getSalesperson()
    {
        return $this->salesperson;
    }

    /**
     * @param mixed $salesperson
     */
    public function setSalesperson($salesperson)
    {
        $this->salesperson = $salesperson;
    }

    /**
     * @return mixed
     */
    public function getPrincipalInvestigator()
    {
        return $this->principalInvestigator;
    }

    /**
     * @param mixed $principalInvestigator
     */
    public function setPrincipalInvestigator($principalInvestigator)
    {
        $this->principalInvestigator = $principalInvestigator;
    }

    /**
     * @return mixed
     */
    public function getBillingContact()
    {
        return $this->billingContact;
    }

    /**
     * @param mixed $billingContact
     */
    public function setBillingContact($billingContact)
    {
        $this->billingContact = $billingContact;
    }

//    public function getPrincipalInvestigators()
//    {
//        return $this->principalInvestigators;
//    }
//    public function addPrincipalInvestigator($item)
//    {
//        if( $item && !$this->principalInvestigators->contains($item) ) {
//            $this->principalInvestigators->add($item);
//        }
//        return $this;
//    }
//    public function removePrincipalInvestigator($item)
//    {
//        $this->principalInvestigators->removeElement($item);
//    }


    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        if( $status == "Unpaid/Issued" ) {
            $this->setIssuedDate(new \DateTime());
        }
    }

    /**
     * @return string
     */
    public function getInvoiceFrom()
    {
        return $this->invoiceFrom;
    }

    /**
     * @param string $invoiceFrom
     */
    public function setInvoiceFrom($invoiceFrom)
    {
        $this->invoiceFrom = $invoiceFrom;
    }

    /**
     * @return string
     */
    public function getInvoiceTo()
    {
        return $this->invoiceTo;
    }

    /**
     * @param string $invoiceTo
     */
    public function setInvoiceTo($invoiceTo)
    {
        $this->invoiceTo = $invoiceTo;
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param string $footer
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
    }

    /**
     * @return string
     */
    public function getFooter2()
    {
        return $this->footer2;
    }

    /**
     * @param string $footer2
     */
    public function setFooter2($footer2)
    {
        $this->footer2 = $footer2;
    }

    /**
     * @return string
     */
    public function getFooter3()
    {
        return $this->footer3;
    }

    /**
     * @param string $footer3
     */
    public function setFooter3($footer3)
    {
        $this->footer3 = $footer3;
    }

    /**
     * @return mixed
     */
    public function getDiscountNumeric()
    {
        return $this->discountNumeric;
    }

    /**
     * @param mixed $discountNumeric
     */
    public function setDiscountNumeric($discountNumeric)
    {
        $this->discountNumeric = $discountNumeric;
    }

    /**
     * @return mixed
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * @param mixed $discountPercent
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;
    }

    /**
     * @return mixed
     */
    public function getAdministrativeFee()
    {
        return $this->administrativeFee;
    }

    /**
     * @param mixed $administrativeFee
     */
    public function setAdministrativeFee($administrativeFee)
    {
        $this->administrativeFee = $administrativeFee;
    }

    /**
     * @return mixed
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @param mixed $subTotal
     */
    public function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;
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
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * @param mixed $paid
     */
    public function setPaid($paid)
    {
        if( $this->paid != $paid ) {
            //exit("change paid date");
            if( $paid ) {
                $this->setPaidDate(new \DateTime());
            } else {
                $this->setPaidDate(NULL);
            }
        }
        $this->paid = $paid;
    }

    /**
     * @return mixed
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * @param mixed $due
     */
    public function setDue($due)
    {
        $this->due = $due;
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
    public function getIrbNumber()
    {
        $irbNumber = $this->irbNumber;
        if( $irbNumber ) {
            return $irbNumber;
        } else {
            return $this->getTransresRequest()->getProject()->getIrbNumber();
        }
        return $this->irbNumber;
    }
    /**
     * @param mixed $irbNumber
     */
    public function setIrbNumber($irbNumber)
    {
        $this->irbNumber = $irbNumber;
    }
    public function getProjectIrbIacucNumber()
    {
        return $this->getTransresRequest()->getProject()->getIrbIacucNumber();
    }

    /**
     * @return mixed
     */
    public function getTransresRequest()
    {
        return $this->transresRequest;
    }

    /**
     * @param mixed $transresRequest
     */
    public function setTransresRequest($transresRequest)
    {
        $this->transresRequest = $transresRequest;
    }

    public function getInvoiceItems()
    {
        return $this->invoiceItems;
    }
    public function addInvoiceItem($item)
    {
        if( $item && !$this->invoiceItems->contains($item) ) {
            $this->invoiceItems->add($item);
            $item->setInvoice($this);
        }
        return $this;
    }
    public function removeInvoiceItem($item)
    {
        $this->invoiceItems->removeElement($item);
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
     * @return \DateTime
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
    }

    /**
     * @param \DateTime $issuedDate
     */
    public function setIssuedDate($issuedDate)
    {
        $this->issuedDate = $issuedDate;
    }

    /**
     * @return \DateTime
     */
    public function getPaidDate()
    {
        return $this->paidDate;
    }

    /**
     * @param \DateTime $paidDate
     */
    public function setPaidDate($paidDate)
    {
        $this->paidDate = $paidDate;
    }

    /**
     * @return mixed
     */
    public function getInvoiceLastReminderSentDate()
    {
        return $this->invoiceLastReminderSentDate;
    }

    /**
     * @param mixed $invoiceLastReminderSentDate
     */
    public function setInvoiceLastReminderSentDate($invoiceLastReminderSentDate)
    {
        $this->invoiceLastReminderSentDate = $invoiceLastReminderSentDate;
    }

    /**
     * @return mixed
     */
    public function getInvoiceReminderCount()
    {
        return $this->invoiceReminderCount;
    }

    /**
     * @param mixed $invoiceReminderCount
     */
    public function setInvoiceReminderCount($invoiceReminderCount)
    {
        $this->invoiceReminderCount = $invoiceReminderCount;
    }


//    public function getInvoiceAddItems()
//    {
//        return $this->invoiceAddItems;
//    }
//    public function addInvoiceAddItem($item)
//    {
//        if( $item && !$this->invoiceAddItems->contains($item) ) {
//            $this->invoiceAddItems->add($item);
//        }
//        return $this;
//    }
//    public function removeInvoiceAddItem($item)
//    {
//        $this->invoiceAddItems->removeElement($item);
//    }

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
    //"createdate" = "DESC" => the most recent is the first one
    public function getRecentPDF() {
        if( count($this->getDocuments()) > 0 ) {
            return $this->getDocuments()->first();
        } else {
            return null;
        }
    }

    public function generateOid($transresRequest)
    {
        $transresRequestOid = $transresRequest->getOid();
        $oid = $transresRequestOid . "-V" . $this->getVersion();
        $this->setOid($oid);
        return $oid;
    }

    public function getProjectSpecialty() {
        $transresRequest = $this->getTransresRequest();
        $project = $transresRequest->getProject();
        return $project->getProjectSpecialty();
    }

    public function getPriceListAbbreviation() {
        $transresRequest = $this->getTransresRequest();
        $priceList = $transresRequest->getPriceList();
        if( $priceList ) {
            return $priceList->getAbbreviation();
        }
        return NULL;
    }

    public function getSerializeStr() {
        //$str = serialize($this);

        $paidDateStr = "";
        if( $this->getPaidDate() ) {
            $paidDateStr = $this->getPaidDate()->format('m/d/Y');
        }

        $str =
            "Status=".$this->getStatus().";<br>".
            "PI=".$this->getPrincipalInvestigator().";<br>".
            "Salesperson=".$this->getSalesperson().";<br>".
            "To=".$this->getInvoiceTo().";<br>".
            "Subtotal($)=".$this->toDecimal($this->getSubTotal())."; ".
            "Discount($)=".$this->toDecimal($this->getDiscountNumeric())."; ".
            "Discount(%)=".$this->getDiscountPercent()."; ".
            "Administrative Fee($)=".$this->getAdministrativeFee()."; ".
            "Total($)=".$this->toDecimal($this->getTotal())."; ".
            "Paid($)=".$this->toDecimal($this->getPaid())."; ".
            "Balance Due($)=".$this->toDecimal($this->getDue()).";<br>".
            "Paid Date=".$paidDateStr.";<br>".
            "Comment=".$this->getComment().";";

        return $str;
    }
    public function toDecimal($number) {
//        if( !$number ) {
//            return $number;
//        }
        return number_format((float)$number, 2, '.', '');
    }

    public function getCleanOid() {
        $oid = $this->getOid();
        $oid = str_replace(" ","-",$oid);
        $oid = str_replace("(","-",$oid);
        $oid = str_replace(")","-",$oid);
        return $oid;
    }

    public function getDueAndDaysStr() {
        $dueDate = $this->getDueDate();
        if( $dueDate ) {
            $now = new \DateTime();
            $diff = $now->diff($dueDate);
            $days = $diff->format("%a");
            if ($days) {
                return $dueDate->format('m/d/Y') . " (" . $days . " days ago" . ")";
            } else {
                return $dueDate->format('m/d/Y') . " (" . "just now" . ")";
            }
        } else {
            return null;
        }
    }

//    public function getGrandTotal() {
    //        $total = $this->getTotal();
    //        $subsidy = $this->getSubsidy();
    //        $grandTotal = (float)$total + (float)$subsidy;
    //        $grandTotal = $this->toDecimal($grandTotal);
    //        return $grandTotal;
    //    }
    //    /**
    //     * Create new: update project's grandTotal ("Total" in the project list)
    //     *
    //     * @ORM\PostPersist
    //     */
    //    public function updatePostPersistProjectTotal()
    //    {
    //        //exit("invoice->PostPersist->updateProjectTotal");
    //        $this->updateProjectTotal();
    //    }
    /**
     * postUpdate - The postUpdate event occurs after the database update operations to entity data. It is not called for a DQL UPDATE statement.
     * update project's total ("Total" in the project list)
     */
    #[ORM\PostUpdate]
    public function updatePostUpdateProjectTotal()
    {
        //exit("invoice->PostUpdate->updateProjectTotal");
        $this->updateProjectTotal();
    }
    public function updateProjectTotal()
    {
        $total = NULL;

        if( $this->getStatus() == 'Canceled' ) {
            return $total;
        }

        $request = $this->getTransresRequest();
        if( $request ) {
            $total = $request->updateProjectTotal();
        }

        return $total;
    }

    //calculate total of all items with ItemCode with fee schedule (product) and without ItemCode or fee schedule
    public function calculateSubTotal() {

        $transresRequest = $this->getTransresRequest();
        $priceList = $transresRequest->getPriceList();

        $invoiceItems = $this->getInvoiceItems();

        $subTotal = 0;

        foreach( $invoiceItems as $invoiceItem ) {
            $product = $invoiceItem->getProduct();

            if( $product ) {
                $productRes = $product->calculateQuantities($priceList);
                $initialQuantity = $productRes['initialQuantity'];
                $additionalQuantity = $productRes['additionalQuantity'];
                $initialFee = $productRes['initialFee'];
                $additionalFee = $productRes['additionalFee'];

                if( $initialFee && $initialQuantity ) {
                    $subTotal = $subTotal + $transresRequest->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
                }

                //echo "product subTotal=$subTotal<br>";

            } else {
                $total1 = $invoiceItem->getTotal1();
                $total2 = $invoiceItem->getTotal2();
                $subTotal = $subTotal + $total1 + $total2;

                //echo "other subTotal=$subTotal<br>";
            }
        }

        $subTotal = $this->toDecimal($subTotal);

        return $subTotal;
    }

    //calculate total with discounts, administrative fee
    public function calculateTotal() {
        $total = 0;
        $discount = 0;
        $discountNumeric = $this->getDiscountNumeric();
        $discountPercent = $this->getDiscountPercent();
        $administrativeFee = $this->getAdministrativeFee();
        $subTotal = $this->getSubTotal();
        
        if( $subTotal ) {
            if( $discountNumeric ) {
                $discount = (float)$discountNumeric;
            }
            if( $discountPercent ) {
                $discount = (float)$subTotal * ((float)$discountPercent/100);
            }
        }

        $total = (float)$subTotal - (float)$discount;

        if( $administrativeFee ) {
            $total = (float)$total + (float)$administrativeFee;
        }

        return $total;
    }

    public function getWorkRequestSubmitter() {
        $transresRequest = $this->getTransresRequest();
        if( $transresRequest ) {
            $submitter = $transresRequest->getSubmitter();
            if( $submitter ) {
                return $submitter.""; //->getUsernameShortest();
            }
        }
    }
    
    public function __toString() {
        return "".$this->getId();
    }
}