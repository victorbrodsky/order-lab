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
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="transres_requestCategoryTypeList")
 */
class RequestCategoryTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="RequestCategoryTypeList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="RequestCategoryTypeList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    /**
     * Price of Product or Service
     * External fee - "Fee for one"
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $fee;

    /**
     * Price of Product or Service
     * External fee - "Fee per additional item"
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $feeAdditionalItem;

    /**
     * Default quantity at initial price, 1 - by default
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $initialQuantity;

//    /**
//     * Price of Product or Service
//     * Internal fee - "Internal fee for one"
//     *
//     * @var string
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $internalFee;
//
//    /**
//     * Price of Product or Service
//     * Internal fee - "Internal Fee per additional item"
//     *
//     * @var string
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $internalFeeAdditionalItem;

    /**
     * @ORM\OneToMany(targetEntity="Prices", mappedBy="requestCategoryType", cascade={"persist","remove"})
     */
    private $prices;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $feeUnit;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $section;

    /**
     * ID of Product or Service
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $productId;

    /**
     * Reverse association: Hide this orderable for the work requests that belong to project requests of this type
     *
     * @ORM\ManyToMany(targetEntity="SpecialtyList", inversedBy="requestCategories")
     * @ORM\JoinTable(name="transres_requestcategory_specialty")
     */
    private $projectSpecialties;

//    /**
//     * Default Work Queue (single or multiple? single)
//     *
//     * @ORM\ManyToOne(targetEntity="WorkQueueList")
//     * @ORM\JoinTable(name="transres_requestcategory_workqueue")
//     */
//    private $workQueue;
//    /**
//     * Default Work Queue (one per price section)
//     *
//     * @ORM\ManyToOne(targetEntity="WorkQueueList")
//     * @ORM\JoinColumn(name="workqueue_id", referencedColumnName="id", nullable=true)
//     */
//    private $workQueue;
    /**
     * Default Work Queue (one per price section)
     *
     * @ORM\ManyToMany(targetEntity="App\TranslationalResearchBundle\Entity\WorkQueueList", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_requestcategory_workqueue",
     *      joinColumns={@ORM\JoinColumn(name="requestcategory_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="workqueue_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $workQueues;


    public function __construct($author=null) {

        parent::__construct($author);

        $this->projectSpecialties = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->workQueues = new ArrayCollection();
    }


    /**
     * @return string
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @param string $fee
     */
    public function setFee($fee)
    {
        $this->fee = $fee;
    }

    /**
     * @return string
     */
    public function getFeeAdditionalItem()
    {
        if( !$this->feeAdditionalItem && $this->getFee() ) {
            return $this->getFee();
        }
        return $this->feeAdditionalItem;
    }

    /**
     * @param string $feeAdditionalItem
     */
    public function setFeeAdditionalItem($feeAdditionalItem)
    {
        $this->feeAdditionalItem = $feeAdditionalItem;
    }

    /**
     * @return int
     */
    public function getInitialQuantity()
    {
        if( !$this->initialQuantity ) {
            return 1;
        }
        return $this->initialQuantity;
    }

    /**
     * @param int $initialQuantity
     */
    public function setInitialQuantity($initialQuantity)
    {
        $this->initialQuantity = $initialQuantity;
    }


//    /**
//     * @return string
//     */
//    public function getInternalFee()
//    {
//        return $this->internalFee;
//    }
//
//    /**
//     * @param string $internalFee
//     */
//    public function setInternalFee($internalFee)
//    {
//        $this->internalFee = $internalFee;
//    }
//
//    /**
//     * @return string
//     */
//    public function getInternalFeeAdditionalItem()
//    {
//        return $this->internalFeeAdditionalItem;
//    }
//
//    /**
//     * @param string $internalFeeAdditionalItem
//     */
//    public function setInternalFeeAdditionalItem($internalFeeAdditionalItem)
//    {
//        $this->internalFeeAdditionalItem = $internalFeeAdditionalItem;
//    }

    /**
     * @return string
     */
    public function getFeeUnit()
    {
        return $this->feeUnit;
    }

    /**
     * @param string $feeUnit
     */
    public function setFeeUnit($feeUnit)
    {
        $this->feeUnit = $feeUnit;
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param string $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return string
     */
    public function getProductId($priceList=NULL)
    {
        if( $priceList ) {
            $priceListAbbreviation = $priceList->getAbbreviation();
            return $this->productId.'-'.$priceListAbbreviation;
        }

        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    public function getWorkQueues()
    {
        return $this->workQueues;
    }
    public function addWorkQueue( $item )
    {
        if( !$this->workQueues->contains($item) ) {
            $this->workQueues->add($item);
        }

        return $this;
    }
    public function removeWorkQueue($item)
    {
        if( $this->workQueues->contains($item) ) {
            $this->workQueues->removeElement($item);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProjectSpecialties()
    {
        return $this->projectSpecialties;
    }
    public function addProjectSpecialty( $item )
    {
        if( !$this->projectSpecialties->contains($item) ) {
            $this->projectSpecialties->add($item);
            //$item->addRequestCategory($this);
        }

        return $this;
    }
    public function removeProjectSpecialty($item)
    {
        if( $this->projectSpecialties->contains($item) ) {
            $this->projectSpecialties->removeElement($item);
            //$item->removeRequestCategory($this);
        }

        return $this;
    }
    public function clearProjectSpecialties() {
        $this->projectSpecialties->clear();
    }
    public function getProjectSpecialtiesStr() {
        $specialtyStr = "";
        foreach($this->getProjectSpecialties() as $specialty) {
            $specialtyStr = $specialtyStr . $specialty->getAbbreviation() . " ";
        }
        return $specialtyStr;
    }

    public function getPrices()
    {
        return $this->prices;
    }
    public function addPrice( $item )
    {
        if( !$this->prices->contains($item) ) {
            $this->prices->add($item);
            $item->setRequestCategoryType($this);
        }

        return $this;
    }
    public function removePrice($item)
    {
        if( $this->prices->contains($item) ) {
            $this->prices->removeElement($item);
            //$item->setRequestCategoryType(NULL);
        }

        return $this;
    }

    public function getPrice($priceList=NULL) {
        if( $priceList ) {
            foreach( $this->getPrices() as $price ) {
                $thisPriceList = $price->getPriceList();
                if( $thisPriceList ) {
                    if( $thisPriceList->getId() == $priceList->getId() ) {
                        return $price;
                    }
                }
            }
        }
        return NULL;
    }
    public function getPriceInitialQuantity($priceList=NULL) {
        $price = $this->getPrice($priceList);
        if( $price ) {
            $initialQuantity = $price->getInitialQuantity();
            if( $initialQuantity ) {
                return $initialQuantity;
            }
        }
        return $this->getInitialQuantity();
    }
    public function getPriceFee($priceList=NULL) {
        $price = $this->getPrice($priceList);
        if( $price ) {
            $fee = $price->getFee();
            if( $fee ) {
                return $fee;
            }
        }
        return $this->getFee();
    }
    public function getPriceFeeAdditionalItem($priceList=NULL) {
        $price = $this->getPrice($priceList);
        if( $price ) {
            $feeAdditionalItem = $price->getFeeAdditionalItem();
            //echo "price FeeAdditionalItem=".$feeAdditionalItem."<br>";
            if( $feeAdditionalItem ) {
                //echo "return price FeeAdditionalItem=".$feeAdditionalItem."<br>";
                return $feeAdditionalItem;
            }
        }
        return $this->getFeeAdditionalItem();
    }
    public function getFeeStr($priceList=NULL) {
        $res = NULL;
        $price = $this->getPrice($priceList);
        //echo $priceList.": price=$price <br>";
        if( $price ) {
            $fee = $price->getFee();
            if( !$fee ) {
                $fee = $this->getFee();
            }
            $feeAdditionalItem = $price->getFeeAdditionalItem();
            if( !$feeAdditionalItem ) {
                $feeAdditionalItem = $this->getFeeAdditionalItem();
            }

            $res = "$".$fee;

            if( $feeAdditionalItem && $fee != $feeAdditionalItem ) {
                $res = $res . " ($" .  $feeAdditionalItem . " per additional item)";
            }

//            if( $priceList ) {
//                $res = $res . "[" . $priceList . "]";
//            }
        }

        if( !$res ) {
            $fee = $this->getFee();
            $feeAdditionalItem = $this->getFeeAdditionalItem();
            //$res = "$".$fee . " ($" .  $feeAdditionalItem . " per additional item)";
            $res = "$".$fee;

            if( $feeAdditionalItem && $fee != $feeAdditionalItem ) {
                $res = $res . " ($" .  $feeAdditionalItem . " per additional item)";
            }
        }
        //echo $priceList.": res=$res <br>";

        return $res;
    }

    public function getFeeUnitStr() {
        if( $this->getFeeUnit() == "Project-specific" ) {
            return $this->getFeeUnit();
        } else {
            return "per " . ucwords($this->getFeeUnit());
        }
    }

    public function getOptimalAbbreviationName( $priceList=NULL ) {
        //return $this->getProductId() . " (" .$this->getSection() . ") - " . $this->getName() . ": $" . $this->getFee() . "/" . $this->getFeeUnit();
        return $this->getProductId($priceList) . " (" .$this->getSection() . ") - " . $this->getName() . ": " . $this->getFeeStr($priceList) . "/" . $this->getFeeUnit();
    }

    public function getProductIdAndName() {
        return $this->getProductId() . " " . $this->getName();
    }

    public function getShortInfo($request=NULL) {
        $priceList = NULL;
        if( $request ) {
            $priceList = $request->getPriceList();
        }
        return $this->getProductId($priceList) . " (" .$this->getSection() . ")";
    }

    public function getSpecificPricesInfo() {
        $specificPriceInfo = "";
        $specificPriceArr = array();
        foreach($this->getPrices() as $specificPrice) {
            $specificPriceArr[] = $specificPrice->getPriceInfo();
        }

        if( count($specificPriceArr) > 0 ) {
            $specificPriceInfo = implode("<br>",$specificPriceArr);
        }

        return $specificPriceInfo;
    }
}
