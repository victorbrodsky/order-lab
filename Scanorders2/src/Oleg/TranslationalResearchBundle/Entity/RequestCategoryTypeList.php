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

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

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
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $fee;

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
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    public function getFeeUnitStr() {
        if( $this->getFeeUnit() == "Project-specific" ) {
            return $this->getFeeUnit();
        } else {
            return "per " . ucwords($this->getFeeUnit());
        }
    }

    public function getOptimalAbbreviationName() {
        return $this->getProductId() . " (" .$this->getSection() . ") - " . $this->getName() . ": $" . $this->getFee() . "/" . $this->getFeeUnit();
    }

    public function getProductIdAndName() {
        return $this->getProductId() . " " . $this->getName();
    }

    public function getShortInfo() {
        return $this->getProductId() . " (" .$this->getSection() . ")";
    }
}
