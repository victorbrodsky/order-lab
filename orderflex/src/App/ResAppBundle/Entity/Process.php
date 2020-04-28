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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="resapp_process")
 */
class Process {
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    
    /**
     * @ORM\Column(name="queueTimestamp", type="datetime", nullable=true)
     */
    private $queueTimestamp;
    
    /**
     * @ORM\Column(name="startTimestamp", type="datetime", nullable=true)
     */
    private $startTimestamp;
    
    /**
     * @ORM\Column(name="resappId", type="string", nullable=true)
     */
    private $resappId;
    

    /**
     * @ORM\ManyToOne(targetEntity="ReportQueue", inversedBy="processes")
     * @ORM\JoinColumn(name="reportQueue_id", referencedColumnName="id")
     **/
    private $reportQueue;

    /**
     * overwrite, asap
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $argument;
        
    
    
    public function __construct($resappId) {
        $this->setResappId($resappId);
        $this->setQueueTimestamp(new \DateTime());
    }
    
    
    public function getId() {
        return $this->id;
    }    

    public function getResappId() {
        return $this->resappId;
    }

    public function setId($id) {
        $this->id = $id;
    }   

    public function setResappId($resappId) {
        $this->resappId = $resappId;
    }


    public function getReportQueue() {
        return $this->reportQueue;
    }

    public function setReportQueue($reportQueue) {
        $this->reportQueue = $reportQueue;
    }

    public function getStartTimestamp() {
        return $this->startTimestamp;
    }

    public function setStartTimestamp($startTimestamp) {
        $this->startTimestamp = $startTimestamp;
    }

    public function getQueueTimestamp() {
        return $this->queueTimestamp;
    }

    public function setQueueTimestamp($queueTimestamp) {
        $this->queueTimestamp = $queueTimestamp;
    }

    /**
     * @param mixed $argument
     */
    public function setArgument($argument)
    {
        $this->argument = $argument;
    }

    /**
     * @return mixed
     */
    public function getArgument()
    {
        return $this->argument;
    }





    public function __toString() {
        return "Process id=".$this->getId()."<br>";
    }
    
    
}

?>
