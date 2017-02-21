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
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="scan_externalId")
 */
class ExternalId {


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="externalIds")
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", nullable=true)
     */
    private $message;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $externalId;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
     */
    private $sourceSystem;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
     */
    private $precedingRelaySystem;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
     */
    private $nextRelaySystem;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
     */
    private $targetSystem;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $externalSourceIdentifier;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $externalTargetIdentifier;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $receivedOn;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $transmittedOn;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="submitter", referencedColumnName="id", nullable=true)
     */
    private $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="transmitter", referencedColumnName="id", nullable=true)
     */
    private $transmitter;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedOn;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updatedBy", referencedColumnName="id", nullable=true)
     */
    private $updatedBy;




    public function __construct() {
        $this->setReceivedOn(new \DateTime());
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
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param mixed $externalId
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
    }


    /**
     * @return mixed
     */
    public function getSourceSystem()
    {
        return $this->sourceSystem;
    }

    /**
     * @param mixed $sourceSystem
     */
    public function setSourceSystem($sourceSystem)
    {
        $this->sourceSystem = $sourceSystem;
    }

    /**
     * @return mixed
     */
    public function getPrecedingRelaySystem()
    {
        return $this->precedingRelaySystem;
    }

    /**
     * @param mixed $precedingRelaySystem
     */
    public function setPrecedingRelaySystem($precedingRelaySystem)
    {
        $this->precedingRelaySystem = $precedingRelaySystem;
    }

    /**
     * @return mixed
     */
    public function getNextRelaySystem()
    {
        return $this->nextRelaySystem;
    }

    /**
     * @param mixed $nextRelaySystem
     */
    public function setNextRelaySystem($nextRelaySystem)
    {
        $this->nextRelaySystem = $nextRelaySystem;
    }

    /**
     * @return mixed
     */
    public function getTargetSystem()
    {
        return $this->targetSystem;
    }

    /**
     * @param mixed $targetSystem
     */
    public function setTargetSystem($targetSystem)
    {
        $this->targetSystem = $targetSystem;
    }

    /**
     * @return mixed
     */
    public function getExternalSourceIdentifier()
    {
        return $this->externalSourceIdentifier;
    }

    /**
     * @param mixed $externalSourceIdentifier
     */
    public function setExternalSourceIdentifier($externalSourceIdentifier)
    {
        $this->externalSourceIdentifier = $externalSourceIdentifier;
    }

    /**
     * @return mixed
     */
    public function getExternalTargetIdentifier()
    {
        return $this->externalTargetIdentifier;
    }

    /**
     * @param mixed $externalTargetIdentifier
     */
    public function setExternalTargetIdentifier($externalTargetIdentifier)
    {
        $this->externalTargetIdentifier = $externalTargetIdentifier;
    }

    /**
     * @return \DateTime
     */
    public function getReceivedOn()
    {
        return $this->receivedOn;
    }

    /**
     * @param \DateTime $receivedOn
     */
    public function setReceivedOn($receivedOn)
    {
        $this->receivedOn = $receivedOn;
    }

    /**
     * @return \DateTime
     */
    public function getTransmittedOn()
    {
        return $this->transmittedOn;
    }

    /**
     * @param \DateTime $transmittedOn
     */
    public function setTransmittedOn($transmittedOn)
    {
        $this->transmittedOn = $transmittedOn;
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
    public function getTransmitter()
    {
        return $this->transmitter;
    }

    /**
     * @param mixed $transmitter
     */
    public function setTransmitter($transmitter)
    {
        $this->transmitter = $transmitter;
    }



    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * @param \DateTime $updatedOn
     * @ORM\PreUpdate
     */
    public function setUpdatedOn()
    {
        $this->updatedOn = new \DateTime();
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





    public function __toString() {
        $res = "External Id";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }

}