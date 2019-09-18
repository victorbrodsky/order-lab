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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_calllogTask")
 */
class CalllogTask
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CalllogEntryMessage", inversedBy="calllogTasks", cascade={"persist"})
     * @ORM\JoinColumn(name="calllogEntryMessage_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $calllogEntryMessage;

    //Task Type
    //list titled “Task Type” with the following values: “Contact Referring Provider”, “Order a medication”, “Order blood products”, “Check lab results”.

    //Task Description
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    //Status (Checkbox hidden on new entry page)
    //“Pending”, “Completed”, “Superseded”, “Deleted”
    /**
     * @ORM\Column(name="status", type="string", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $createdDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $updatedBy;




    public function __construct() {
        $this->synonyms = new ArrayCollection();
    }



    /**
     * @return mixed
     */
    public function getCalllogEntryMessage()
    {
        return $this->calllogEntryMessage;
    }

    /**
     * @param mixed $calllogEntryMessage
     */
    public function setCalllogEntryMessage($calllogEntryMessage)
    {
        $this->calllogEntryMessage = $calllogEntryMessage;
    }




}