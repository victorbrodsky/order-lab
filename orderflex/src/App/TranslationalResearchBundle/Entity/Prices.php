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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/5/16
 * Time: 5:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\TranslationalResearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="transres_prices")
 */
class Prices
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="RequestCategoryTypeList", inversedBy="prices")
     * @ORM\JoinColumn(name="requestCategoryType_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $requestCategoryType;


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
    
    /**
     * Utilize the following price list
     *
     * @ORM\ManyToOne(targetEntity="PriceTypeList")
     */
    private $priceList;

    /**
     * Work Queue (one per price section)
     *
     * @ORM\ManyToMany(targetEntity="App\TranslationalResearchBundle\Entity\WorkQueueList", cascade={"persist","remove"})
     * @ORM\JoinTable(name="transres_price_workqueue",
     *      joinColumns={@ORM\JoinColumn(name="price_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="workqueue_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "DESC"})
     **/
    private $workQueues;




    public function __construct() {
        $this->workQueues = new ArrayCollection();
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getRequestCategoryType()
    {
        return $this->requestCategoryType;
    }

    /**
     * @param mixed $requestCategoryType
     */
    public function setRequestCategoryType($requestCategoryType)
    {
        $this->requestCategoryType = $requestCategoryType;
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
        if( !$this->feeAdditionalItem && $this->fee ) {
            return $this->fee;
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

    public function getPriceInfo( $allowedPriceListIds=NULL )
    {
        //getPriceList => PriceTypeList
        if( $allowedPriceListIds ) {
           if( is_array($allowedPriceListIds) ) {
               $priceTypeList = $this->getPriceList();
               if( $priceTypeList ) {
                   $priceTypeListId = $priceTypeList->getId();
                   //echo "priceTypeListId=$priceTypeListId ?= " . implode(",",$allowedPriceListIds)."<br>";
                   if( in_array($priceTypeListId, $allowedPriceListIds) ) {
                       return $this->getPriceList().": ".$this->getFee()." (".$this->getFeeAdditionalItem().")";
                   }
               }
           }
        } else {
            return $this->getPriceList().": ".$this->getFee()." (".$this->getFeeAdditionalItem().")";
        }
        
        return NULL;
    }


    public function __toString() {
        $res = "Price ID " . $this->getId();

        $res = $res . ", priceList=".$this->getPriceList().", fee=".$this->getFee().", feeAdditionalItem=".$this->getFeeAdditionalItem().", initialQuantity=".$this->getInitialQuantity();
        
        return $res;
    }
}