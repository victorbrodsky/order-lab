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

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user_transferdata')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class TransferData {
    
    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'creator_id', referencedColumnName: 'id')]
    protected $creator;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $creationdate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updatedate;

    //Status list: “Ready”, “Completed”, “Failed”
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\TransferStatusList')]
    #[ORM\JoinColumn(name: 'transferstatus_id', referencedColumnName: 'id')]
    private $transferStatus;

    //InterfaceTransferList
    #[ORM\ManyToOne(targetEntity: 'InterfaceTransferList')]
    #[ORM\JoinColumn(name: 'interfacetransfer_id', referencedColumnName: 'id')]
    private $interfaceTransfer;

    //Full class name i.e. 'App\UserdirectoryBundle\Entity\AntibodyList'
    //Obtain by php function: get_class($entity)
    #[ORM\Column(type: 'string', nullable: true)]
    private $className;

    //Move sourceId and globalId to the transferable object (Project)
    //Local ID
    #[ORM\Column(type: 'string', nullable: true)]
    private $localId;

    //NOT USED
    //Global ID localId@instanceId
    #[ORM\Column(type: 'string', nullable: true)]
    private $globalId;

    //NOT USED
    #[ORM\Column(type: 'string', nullable: true)]
    private $instanceId;

    //#[ORM\Column(type: 'string', nullable: true)]
    //private $globalId;

    //'Global ID' is the unique id containing 'Local ID' and 'Instance ID' (i.e. 67@WCMINT - we can omit the 'APCP' prefix here)
    //we can omit the redundant 'Global ID' field because we already have local ID and instance ID

    public function __construct( $creator=NULL ) {
        if( $creator ) {
            $this->setCreator($creator);
        }
        $this->setCreationdate(new \DateTime());
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
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return mixed
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * @param mixed $creationdate
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate;
    }

    /**
     * @return mixed
     */
    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    /**
     * @param \DateTime $updatedate
     */
    #[ORM\PreUpdate]
    public function setUpdatedate()
    {
        $this->updatedate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getTransferStatus()
    {
        return $this->transferStatus;
    }

    /**
     * @param mixed $transferStatus
     */
    public function setTransferStatus($transferStatus)
    {
        $this->transferStatus = $transferStatus;
    }

    /**
     * @return mixed
     */
    public function getInterfaceTransfer()
    {
        return $this->interfaceTransfer;
    }

    /**
     * @param mixed $interfaceTransfer
     */
    public function setInterfaceTransfer($interfaceTransfer)
    {
        $this->interfaceTransfer = $interfaceTransfer;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return mixed
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @param mixed $instanceId
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;
    }

    /**
     * @return mixed
     */
    public function getLocalId()
    {
        return $this->localId;
    }

    /**
     * @param mixed $localId
     */
    public function setLocalId($localId)
    {
        $this->localId = $localId;
    }

    /**
     * @return mixed
     */
    public function getGlobalId()
    {
        return $this->globalId;
    }

    /**
     * @param mixed $globalId
     */
    public function setGlobalId($globalId)
    {
        $this->globalId = $globalId;
    }


    public function createGlobalId()
    {
        return $this->getLocalId().'@'.$this->getInstanceId();
    }



    
    



    public function __toString() {
        return "TransferData id=".$this->getId()."<br>";
    }
    
    
}
