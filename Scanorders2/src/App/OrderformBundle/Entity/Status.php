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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_status")
 */
class Status extends ListAbstract
{

    /**
     * action: show this 'action' column in the Action menu and the 'name' column in the Filter menu. (i.e. Cancel)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $action;

//    /**
//     * @ORM\OneToMany(targetEntity="Message", mappedBy="status")
//     */
//    protected $message;

    /**
     * @ORM\OneToMany(targetEntity="Status", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


//    /**
//     * Constructor
//     */
//    public function __construct()
//    {
//        //$this->message = new ArrayCollection();
//        $this->synonyms = new ArrayCollection();
//    }

    public function __toString() {
        return $this->name;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Status
     */
    public function setAction($action)
    {
        $this->action = $action;
    
        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

//    /**
//     * Add message
//     *
//     * @param \App\OrderformBundle\Entity\Message $message
//     * @return Status
//     */
//    public function addMessage(\App\OrderformBundle\Entity\Message $message)
//    {
//        //echo "Status addMessage=".$message."<br>";
//        if( !$this->message->contains($message) ) {
//            $this->message->add($message);
//        }
//    }
//
//    /**
//     * Remove message
//     *
//     * @param \App\OrderformBundle\Entity\Message $message
//     */
//    public function removeMessage(\App\OrderformBundle\Entity\Message $message)
//    {
//        $this->message->removeElement($message);
//    }
//
//    /**
//     * Get message
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getMessage()
//    {
//        return $this->message;
//    }
}