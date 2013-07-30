<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * OrderInfo might have many slides
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\OrderInfoRepository")
 * @ORM\Table(name="orderinfo")
 * @ORM\HasLifecycleCallbacks
 */
class OrderInfo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="orderdate", type="datetime", nullable=true)
     *
     */
    private $orderdate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="pathologyService", nullable=true, type="string", length=200)
     */
    private $pathologyService;

    /**
     * status - status of the order i.e. complete, in process, returned ...
     * @var string
     *
     * @ORM\Column(name="status", nullable=true, type="string", length=100)
     */
    private $status;
    
    /**
     * type - type of the order: single, multi, edu, res
     * @var string
     *
     * @ORM\Column(name="type", nullable=true, type="string", length=100)
     */
    private $type;
    
    /**
     * @var string
     *
     * @ORM\Column(name="priority", type="string", length=200)
     * @Assert\NotBlank
     */
    private $priority;
    
    /**
     * @ORM\Column(name="scandeadline", nullable=true, type="datetime")
     */
    private $scandeadline;
    
    /**
     * @ORM\Column(name="returnoption", nullable=true, type="datetime")
     */
    private $returnoption;

    /**
     * @var string
     *
     * @ORM\Column(name="slideDelivery", type="string", length=200)
     * @Assert\NotBlank
     */
    private $slideDelivery;

    /**
     * @var string
     *
     * @ORM\Column(name="returnSlide", type="string", length=200)
     * @Assert\NotBlank
     */
    private $returnSlide;

    /**
     * provider is a string with logged user name
     * @var \stdClass
     *
     * @ORM\Column(name="provider", type="string", length=200)
     * @Assert\NotBlank
     */
    private $provider;

    /**
     * @ORM\OneToMany(targetEntity="Patient", mappedBy="orderinfo", cascade={"persist"})
     */
    protected $patient;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patient = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
    * @ORM\PrePersist
    */
    public function setOrderdate() {
        $this->orderdate = new \DateTime();
    }

    /**
     * Get orderdate
     *
     * @return \DateTime 
     */
    public function getOrderdate()
    {
        return $this->orderdate;
    }

    /**
     * Set pathologyService
     *
     * @param string $pathologyService
     * @return OrderInfo
     */
    public function setPathologyService($pathologyService)
    {
        $this->pathologyService = $pathologyService;
    
        return $this;
    }

    /**
     * Get pathologyService
     *
     * @return string 
     */
    public function getPathologyService()
    {
        return $this->pathologyService;
    }

    /**
     * Set priority
     *
     * @param string $priority
     * @return OrderInfo
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    
        return $this;
    }

    /**
     * Get priority
     *
     * @return string 
     */
    public function getPriority()
    {
        return $this->priority;
    }
    
    public function getScandeadline() {
        return $this->scandeadline;
    }

    public function getReturnoption() {
        return $this->returnoption;
    }

    public function setScandeadline($scandeadline) {
        $this->scandeadline = $scandeadline;
    }

    public function setReturnoption($returnoption) {
        $this->returnoption = $returnoption;
    }

    /**
     * Set slideDelivery
     *
     * @param string $slideDelivery
     * @return OrderInfo
     */
    public function setSlideDelivery($slideDelivery)
    {
        $this->slideDelivery = $slideDelivery;
    
        return $this;
    }

    /**
     * Get slideDelivery
     *
     * @return string 
     */
    public function getSlideDelivery()
    {
        return $this->slideDelivery;
    }

    /**
     * Set returnSlide
     *
     * @param string $returnSlide
     * @return OrderInfo
     */
    public function setReturnSlide($returnSlide)
    {
        $this->returnSlide = $returnSlide;
    
        return $this;
    }

    /**
     * Get returnSlide
     *
     * @return string 
     */
    public function getReturnSlide()
    {
        return $this->returnSlide;
    }

    /**
     * Set provider
     *
     * @param \stdClass $provider
     * @return OrderInfo
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    
        return $this;
    }

    /**
     * Get provider
     *
     * @return \stdClass 
     */
    public function getProvider()
    {
        return $this->provider;
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function getType() {
        return $this->type;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setType($type) {
        $this->type = $type;
    }
      
//    public function removeAllChildren() {
//        $this->patient->clear();
//    }
    

    /**
     * Add patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     * @return OrderInfo
     */
    public function addPatient(\Oleg\OrderformBundle\Entity\Patient $patient)
    {
        //$this->patient[] = $patient;
        //return $this;
        
        $patient->setOrderinfo($this);
        //$this->patient->add($patient);
        $this->patient[] = $patient;
        return $this;
    }

    /**
     * Remove patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     */
    public function removePatient(\Oleg\OrderformBundle\Entity\Patient $patient)
    {
        $this->patient->removeElement($patient);
    }

    /**
     * Get patient
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPatient()
    {
        return $this->patient;
    }
    
    public function setPatient($patient) {
        $this->patient = $patient;
    }
}