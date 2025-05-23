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

#[ORM\Table(name: 'transres_invoiceItem')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class InvoiceItem {

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
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

    #[ORM\ManyToOne(targetEntity: 'Invoice', inversedBy: 'invoiceItems')]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'id')]
    private $invoice;

    #[ORM\ManyToOne(targetEntity: 'Product')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $product;

    //////////// Invoice fields ///////////////////
    /**
     * QTY for the first item (initial quantity)
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $quantity;

    //TODO: add initialQuantity?
    /**
     * QTY for additional items (additional quantity)
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $additionalQuantity;

    /**
     * Item Code (i.e. TRP-1003)
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $itemCode;

    /**
     * Description
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    /**
     * unitPrice based on $fee
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $unitPrice;

    /**
     * Additional unitPrice based on $feeAdditionalItem
     */
    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $additionalUnitPrice;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2, nullable: true)]
    private $total;
    //////////// EOF Invoice fields ///////////////////


    public function __construct($user=null) {
        $this->setSubmitter($user);
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

    /**
     * @param \DateTime $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getAdditionalQuantity()
    {
        return $this->additionalQuantity;
    }

    /**
     * @param mixed $additionalQuantity
     */
    public function setAdditionalQuantity($additionalQuantity)
    {
        $this->additionalQuantity = $additionalQuantity;
    }

    /**
     * @return mixed
     */
    public function getItemCode()
    {
        return $this->itemCode;
    }

    /**
     * @param mixed $itemCode
     */
    public function setItemCode($itemCode)
    {
        $this->itemCode = $itemCode;
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
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param mixed $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        //fix SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type numeric: "" with code0
        //if value is "" convert it to NULL
        if( $unitPrice ) {
            $unitPrice = trim((string)$unitPrice);
            if( !$unitPrice ) {
                $unitPrice = NULL;
            }
        } else {
            //if unitPrice is ""
            $unitPrice = NULL;
        }

        $this->unitPrice = $unitPrice;
    }

    /**
     * @return mixed
     */
    public function getAdditionalUnitPrice()
    {
        if( !$this->additionalUnitPrice && $this->getUnitPrice() ) {
            return $this->getUnitPrice();
        }
        
        return $this->additionalUnitPrice;
    }

    /**
     * @param mixed $additionalUnitPrice
     */
    public function setAdditionalUnitPrice($additionalUnitPrice)
    {
        //fix SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type numeric: "" with code0
        //if value is "" convert it to NULL
        //echo "1set  AdditionalUnitPrice=".$additionalUnitPrice."<br>";
        if( $additionalUnitPrice ) {
            $additionalUnitPrice = trim((string)$additionalUnitPrice);
            if( !$additionalUnitPrice ) {
                $additionalUnitPrice = NULL;
            }
        } else {
            //if additionalUnitPrice is ""
            $additionalUnitPrice = NULL;
        }
        //echo "2set  AdditionalUnitPrice=".$additionalUnitPrice."<br>";
        $this->additionalUnitPrice = $additionalUnitPrice;
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
        //fix SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type numeric: "" with code0
        //if value is "" convert it to NULL
        if( $total ) {
            $total = trim((string)$total);
            if( !$total ) {
                $total = NULL;
            }
        } else {
            //if total is ""
            $total = NULL;
        }

        $this->total = $total;
    }
    
    public function getTotalQuantity() {
        $initialQuantity = $this->getQuantity();
        $additionalQuantity = $this->getAdditionalQuantity();
        $totalQuantity = intval($initialQuantity) + intval($additionalQuantity);
        //echo "totalQuantity=".$totalQuantity."<br>";
        return $totalQuantity;
    }

    public function hasSecondRaw() {
        $secondRaw = false;
        $unitPrice = $this->toDecimal($this->getUnitPrice());
        $additionalUnitPrice = $this->toDecimal($this->getAdditionalUnitPrice());

        $quantity = $this->getQuantity();
        $additionalQuantity = $this->getAdditionalQuantity();

//        if( $quantity > 1 ) {
//            if( $unitPrice != $additionalUnitPrice ) {
//                $secondRaw = true;
//            }
//        }

        //echo "$quantity, $additionalQuantity; $unitPrice, $additionalUnitPrice <br>";
        //if( $additionalQuantity > $quantity ) {
        if( $additionalQuantity > 0 ) {
            if( $unitPrice != $additionalUnitPrice ) {
                $secondRaw = true;
            }
        }
        //$secondRaw = true;

        return $secondRaw;
    }
    public function toDecimal($number) {
        return number_format((float)$number, 2, '.', '');
    }

    //Initial total1
    public function getTotal1() {
        $unitPrice = $this->toDecimal($this->getUnitPrice());
        $quantity = $this->getQuantity();
        $total1 = $unitPrice*$quantity;
        return $this->toDecimal($total1);
    }

    //Additional total2
    public function getTotal2() {
        $additionalUnitPrice = $this->toDecimal($this->getAdditionalUnitPrice());
        $additionalQuantity = $this->getAdditionalQuantity();
        $total2 = $additionalUnitPrice*$additionalQuantity;
        return $this->toDecimal($total2);
    }

    public function getProductId() {
        $product = $this->getProduct();
        if( $product ) {
            $category = $product->getCategory();
            if( $category ) {
                $productId = $category->getProductId();
                return $productId."";
            }
        }
        return NULL;
    }

    //Used only in getInvoiceItemInfoHtml (get invoice items for PDF view)
    public function getItemCodeWithPriceListAbbreviation() {
        $itemCode = $this->getItemCode();

        //do not append "-i" for products without fee schedule (custom entered itemcode on the invoice item)
        $product = $this->getProduct();
        if( $product ) {
            $category = $product->getCategory();
            if( !$category ) {
                //if category is NULL => return itemCode without $priceListAbbreviationPostfix
                return $itemCode;
            }
        }

        if( $itemCode ) {
            $invoice = $this->getInvoice();
            if ($invoice) {
                $transresRequest = $invoice->getTransresRequest();
                if ($transresRequest) {
                    $project = $transresRequest->getProject();
                    if ($project) {
                        $priceListAbbreviationPostfix = $project->getPriceListAbbreviationPostfix(); //"-i"
                        $itemCode = $itemCode . $priceListAbbreviationPostfix;
                    }
                }
            }
        }

        return $itemCode;
    }

    //pull in “Completed” orderable prices into the Subtotal
    public function isOrderableStatus( $status='Completed' ) {
        $product = $this->getProduct();
        if( $product ) {
            $orderableStatus = $product->getOrderableStatus();
            if( $orderableStatus && $orderableStatus == $status ) {
                return true;
            }
        }
        return false;
    }

    public function __toString() {
        return "InvoiceItem ".$this->getId().": description=".$this->getDescription().
        "; itemCode=".$this->getItemCode()."; quantity=".$this->getQuantity().
        "; additionalQuantity=".$this->getAdditionalQuantity()."; product=[".$this->getProduct()."]";
    }

}