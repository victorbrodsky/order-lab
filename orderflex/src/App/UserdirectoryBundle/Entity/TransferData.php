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

    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\TransferStatusList')]
    #[ORM\JoinColumn(name: 'transferstatus_id', referencedColumnName: 'id')]
    private $transferStatus;

    //InterfaceTransferList
    #[ORM\ManyToOne(targetEntity: 'InterfaceTransferList')]
    #[ORM\JoinColumn(name: 'interfacetransfer_id', referencedColumnName: 'id')]
    private $interfaceTransfer;

    //use instead linking to the antibody object
    ///// Object Data /////
    #[ORM\Column(type: 'string', nullable: true)]
    protected $entityId;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $entityNamespace;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $entityName;
    ///// EOF Object Data /////

    

    public function __construct(Document $document) {
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
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return mixed
     */
    public function getEntityNamespace()
    {
        return $this->entityNamespace;
    }

    /**
     * @param mixed $entityNamespace
     */
    public function setEntityNamespace($entityNamespace)
    {
        $this->entityNamespace = $entityNamespace;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param mixed $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
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



    
    



    public function __toString() {
        return "TransferData id=".$this->getId()."<br>";
    }
    
    
}
