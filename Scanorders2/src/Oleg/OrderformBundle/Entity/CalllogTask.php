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

    /**
     * “Contact Referring Provider”, “Order a medication”, “Order blood products”, “Check lab results”
     *
     * @ORM\ManyToOne(targetEntity="CalllogTaskTypeList")
     * @ORM\JoinColumn(name="calllogTaskType_id", referencedColumnName="id", nullable=true)
     */
    private $calllogTaskType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * “pending”, “completed”, “superseded”, “deleted”
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $systemStatus;

    /**
     * Checkbox (Checkbox hidden on new entry page): 0-Pending, 1-Completed
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
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

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $statusUpdatedDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $statusUpdatedBy;




    public function __construct($creator=null) {
        $this->setCreatedDate(new \DateTime());
        $this->setSystemStatus("pending");

        if( $creator ) {
            $this->setCreatedBy($creator);
        }
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
    public function getCalllogTaskType()
    {
        return $this->calllogTaskType;
    }

    /**
     * @param mixed $calllogTaskType
     */
    public function setCalllogTaskType($calllogTaskType)
    {
        $this->calllogTaskType = $calllogTaskType;
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
    public function getSystemStatus()
    {
        return $this->systemStatus;
    }

    /**
     * @param mixed $systemStatus
     */
    public function setSystemStatus($systemStatus)
    {
        $this->systemStatus = $systemStatus;
    }

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
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param mixed $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * @param mixed $updatedDate
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param mixed $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return mixed
     */
    public function getStatusUpdatedDate()
    {
        return $this->statusUpdatedDate;
    }

    /**
     * @param mixed $statusUpdatedDate
     */
    public function setStatusUpdatedDate($statusUpdatedDate)
    {
        $this->statusUpdatedDate = $statusUpdatedDate;
    }

    /**
     * @return mixed
     */
    public function getStatusUpdatedBy()
    {
        return $this->statusUpdatedBy;
    }

    /**
     * @param mixed $statusUpdatedBy
     */
    public function setStatusUpdatedBy($statusUpdatedBy)
    {
        $this->statusUpdatedBy = $statusUpdatedBy;
    }

    public function getTaskInfo() {
        $creator = $this->getCreatedBy();
        if( $creator ) {
            $creator = " by " . $creator->getUsernameShortest();
        }
        $createdDate = $this->getCreatedDate();
        if( $createdDate ) {
            $createdDateStr = " on " . $createdDate->format('m/d/Y H:i:s');
        } else {
            $createdDateStr = null;
        }

        //status updated
        $statusUpdatedStr = null;
        $statusUpdatedBy = $this->getStatusUpdatedBy();
        if( $statusUpdatedBy ) {
            $statusUpdatedStr = ", updated by ".$statusUpdatedBy->getUsernameShortest();
        }
        $statusUpdatedDate = $this->getStatusUpdatedDate();
        if( $statusUpdatedDate ) {
            $statusUpdatedStr = $statusUpdatedStr . " on " . $statusUpdatedDate->format('m/d/Y H:i:s');
        }

        return "Created" . $creator . $createdDateStr . $statusUpdatedStr;
    }

    public function __toString()
    {
        return "ID=".$this->getId().", status=".$this->getStatus().", type=".$this->getCalllogTaskType().", description=".$this->getDescription().$this->getTaskInfo();
    }
}