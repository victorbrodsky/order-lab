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
 * Date: 4/11/2016
 * Time: 11:35 AM
 */


namespace App\VacReqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


/**
 * @ORM\Entity
 * @ORM\Table(name="vacreq_floating")
 */
class VacReqFloatingVacation extends VacReqRequestBase
{

    /**
     * floating day type [Juneteenth]
     *
     * @ORM\ManyToOne(targetEntity="VacReqFloatingTypeList")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $floatingType;

    /**
     * I have worked or plan to work on [Juneteenth]
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $work;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $floatingDay;






    /**
     * @return mixed
     */
    public function getFloatingType()
    {
        return $this->floatingType;
    }

    /**
     * @param mixed $floatingType
     */
    public function setFloatingType($floatingType)
    {
        $this->floatingType = $floatingType;
    }

    /**
     * @return mixed
     */
    public function getWork()
    {
        return $this->work;
    }

    /**
     * @param mixed $work
     */
    public function setWork($work)
    {
        $this->work = $work;
    }

    /**
     * @return \DateTime
     */
    public function getFloatingDay()
    {
        return $this->floatingDay;
    }

    /**
     * @param \DateTime $floatingDay
     */
    public function setFloatingDay($floatingDay)
    {
        $this->floatingDay = $floatingDay;
    }




    public function __toString()
    {
        //$break = "\r\n";
        $break = "<br>";
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');

        $worked = "N/A";
        if( $this->getWork() === true ) {
            $worked = "Yes";
        }
        if( $this->getWork() === false ) {
            $worked = "No";
        }

        $res = "### Floating Day Request ###".$break;
        $res .= "Status: ".$this->getStatus().$break;
        $res .= "Floating Day Type: ".$this->getFloatingType().$break;
        $res .= "I have worked or plan to work: ".$worked.$break;

        if( $this->getApproverComment() ) {
            $res .= "Approver Comment: ".$this->getApproverComment().$break;
        }

        return $res;
    }

}