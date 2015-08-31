<?php

namespace Oleg\FellAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fellapp_process")
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
     * @ORM\Column(name="fellappId", type="string", nullable=true)
     */
    private $fellappId;
    

    /**
     * @ORM\ManyToOne(targetEntity="ReportQueue", inversedBy="processes")
     * @ORM\JoinColumn(name="reportQueue_id", referencedColumnName="id")
     **/
    private $reportQueue;
        
    
    
    public function __construct($fellappId) {
        $this->setFellappId($fellappId);
        $this->setQueueTimestamp(new \DateTime());
    }
    
    
    public function getId() {
        return $this->id;
    }    

    public function getFellappId() {
        return $this->fellappId;
    }

    public function setId($id) {
        $this->id = $id;
    }   

    public function setFellappId($fellappId) {
        $this->fellappId = $fellappId;
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

    public function __toString() {
        return "Process id=".$this->getId()."<br>";
    }
    
    
}

?>
