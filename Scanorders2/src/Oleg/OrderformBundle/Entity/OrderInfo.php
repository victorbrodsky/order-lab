<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderInfo
 *
 * @ORM\Table()
 * @ORM\Entity
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
     * @ORM\Column(name="orderdate", type="datetime")
     */
    private $orderdate;

    /**
     * @var string
     *
     * @ORM\Column(name="pathologyService", type="string", length=255)
     */
    private $pathologyService;

    /**
     * @var string
     *
     * @ORM\Column(name="priority", type="string", length=255)
     */
    private $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="slideDelivery", type="string", length=255)
     */
    private $slideDelivery;

    /**
     * @var string
     *
     * @ORM\Column(name="returnSlide", type="string", length=255)
     */
    private $returnSlide;

    /**
     * @var \stdClass
     *
     * @ORM\Column(name="provider", type="object")
     */
    private $provider;


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
     * Set orderdate
     *
     * @param \DateTime $orderdate
     * @return OrderInfo
     */
    public function setOrderdate($orderdate)
    {
        $this->orderdate = $orderdate;
    
        return $this;
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
}
