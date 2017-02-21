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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_reportQueue")
 */
class ReportQueue {
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;           
    
    /**
     * @ORM\OneToMany(targetEntity="Process", mappedBy="reportQueue", cascade={"persist","remove"})
     **/
    private $processes;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $running;
    
    /**
     * @ORM\OneToOne(targetEntity="Process", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="runningProcess_id", referencedColumnName="id")
     **/
    private $runningProcess;
    
    
    
    public function __construct() {
        $this->processes = new ArrayCollection();
    }
    
    
    
    public function getId() {
        return $this->id;
    }
   

    public function setId($id) {
        $this->id = $id;
    }
    
    public function getRunning() {
        return $this->running;
    }

    public function setRunning($running) {
        $this->running = $running;
    }

    
    
    public function addProcess($item)
    {
        if( $item && !$this->processes->contains($item) ) {
            $this->processes->add($item);
            $item->setReportQueue($this);
        }
        return $this;
    }
    public function removeProcess($item)
    {
        $this->processes->removeElement($item);
    }
    public function getProcesses()
    {
        return $this->processes;
    }
    
    public function getRunningProcess() {
        return $this->runningProcess;
    }

    public function setRunningProcess($runningProcess) {
        $this->runningProcess = $runningProcess;
    }



 
    
}

?>
