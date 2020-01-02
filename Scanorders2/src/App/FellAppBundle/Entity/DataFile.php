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

namespace Oleg\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="fellapp_dataFile")
 */
class DataFile {
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $creationdate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedate;

    /**
     * active, completed, failed
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;


    /**
     * @ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $document;

    /**
     * @ORM\OneToOne(targetEntity="FellowshipApplication")
     * @ORM\JoinColumn(name="fellapp_id", referencedColumnName="id")
     */
    private $fellapp;

    
    public function __construct($document) {
        $this->setCreationdate(new \DateTime());
        $this->setStatus("active");
        $this->setDocument($document);
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
     * @ORM\PreUpdate
     */
    public function setUpdatedate()
    {
        $this->updatedate = new \DateTime();
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
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param mixed $document
     */
    public function setDocument($document)
    {
        //1) clear old document if exists
        if( $this->getDocument() ) {
            $this->getDocument()->clearUseObject();
        }

        //2) set document
        $this->document = $document;
        if( $document ) {
            $document->createUseObject($this);
        }
    }

    /**
     * @return mixed
     */
    public function getFellapp()
    {
        return $this->fellapp;
    }

    /**
     * @param mixed $fellapp
     */
    public function setFellapp($fellapp)
    {
        $this->fellapp = $fellapp;
    }
    
    



    public function __toString() {
        return "DataFile id=".$this->getId()."<br>";
    }
    
    
}

?>
