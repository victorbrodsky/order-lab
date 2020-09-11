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

namespace App\ResAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="resapp_inputdatafile")
 */
class InputDataFile {
    
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
     * ERAS (Electronic Residency Application Service) files in PDF
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\Document", cascade={"persist","remove"})
     * @ORM\JoinTable(name="resapp_inputdatafile_erasfile",
     *      joinColumns={@ORM\JoinColumn(name="inputdatafile_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="erasfile_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @ORM\OrderBy({"createdate" = "ASC"})
     **/
    private $erasFiles;

    /**
     * @ORM\OneToOne(targetEntity="ResidencyApplication")
     * @ORM\JoinColumn(name="resapp_id", referencedColumnName="id")
     */
    private $resapp;

    
    public function __construct() {
        $this->setCreationdate(new \DateTime());
        $this->setStatus("active");
        //$this->setDocument($document);

        $this->coverLetters = new ArrayCollection();
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

    public function addErasFile($item)
    {
        if( $item && !$this->erasFiles->contains($item) ) {
            $this->erasFiles->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeErasFile($item)
    {
        $this->erasFiles->removeElement($item);
        $item->clearUseObject();
    }
    public function getErasFiles()
    {
        return $this->erasFiles;
    }

    /**
     * @return mixed
     */
    public function getResapp()
    {
        return $this->resapp;
    }

    /**
     * @param mixed $resapp
     */
    public function setResapp($resapp)
    {
        $this->resapp = $resapp;
    }
    
    



    public function __toString() {
        return "InputDataFile id=".$this->getId()."<br>";
    }
    
    
}

?>
