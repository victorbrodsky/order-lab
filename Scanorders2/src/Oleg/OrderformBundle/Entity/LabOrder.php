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

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\DocumentContainer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_laborder")
 */
class LabOrder extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="laborder")
     **/
    protected $message;

    //"Signing Provider" field: message.proxyusers

    //"Receiving Providers" field: message.orderRecipients



    //"Laboratory Test Title"
    //(Select2 - this should be added to List Manager as "Laboratory Tests" and the "Laboratory Test ID Type" +
    //"Laboratory Test ID" + "Laboratory Test Title" fields should work exactly like the Grant Issuer, Grand ID, and Grant Title -
    //all three should be on the same list in List Manager)

    /**
     * @ORM\ManyToOne(targetEntity="LabTest", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $labTest;





    /**
     * @param mixed $labTest
     */
    public function setLabTest($labTest)
    {
        $this->labTest = $labTest;
    }

    /**
     * @return mixed
     */
    public function getLabTest()
    {
        return $this->labTest;
    }





    public function __toString() {
        $res = "Lab Order";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }

}