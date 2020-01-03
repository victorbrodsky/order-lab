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

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_scanorder")
 */
class ScanOrder extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="scanorder")
     **/
    protected $message;


    /**
//     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Service")
//     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=true)
//     */
//    private $service;
    /**
     * Originating Organizational Group Institution; it is for specifying "from" which organization the order is coming.
     * Described in: https://bitbucket.org/weillcornellpathology/scanorder/issues/467/reminder-complete-blocker-ticket-for
     * It might be make a sense to have this Originating Organizational Group Institution in the Message object
     *
     * This serves as default institution to set scan order scope (who can view this order: users from the with the same institutional scope can view this order)
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
     */
    private $scanOrderInstitutionScope;

    /**
     * Order delivery (string): I'll give slides to ...
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $delivery;




    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        $message->setScanorder($this);
    }

    /**
     * @param mixed $scanOrderInstitutionScope
     */
    public function setScanOrderInstitutionScope($scanOrderInstitutionScope)
    {
        $this->scanOrderInstitutionScope = $scanOrderInstitutionScope;
    }

    /**
     * @return mixed
     */
    public function getScanOrderInstitutionScope()
    {
        return $this->scanOrderInstitutionScope;
    }




//    /**
//     * @param mixed $service
//     */
//    public function setService($service)
//    {
//        $this->service = $service;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getService()
//    {
//        return $this->service;
//    }


    /**
     * @param mixed $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return mixed
     */
    public function getDelivery()
    {
        return $this->delivery;
    }




    public function __toString() {
        $res = "Scan Order";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }


}