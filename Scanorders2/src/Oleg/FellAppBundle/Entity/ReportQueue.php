<?php

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
     * @ORM\OneToMany(targetEntity="Process", mappedBy="reportQueue", cascade={"persist"})
     **/
    private $processes;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $running;
    
    /**
     * @ORM\OneToOne(targetEntity="Process", cascade={"persist"})
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
