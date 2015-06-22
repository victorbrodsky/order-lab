<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_scanorder")
 */
class ScanOrder extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="scanorder")
     **/
    protected $message;


    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Service")
//     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=true)
//     */
//    private $service;

    /**
     * Order delivery (string): I'll give slides to ...
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $delivery;




    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        $message->setScanorder($this);
    }


//    /**
//     * @param mixed $service
//     */
//    public function setService($service)
//    {
//        $this->service = $service;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getService()
//    {
//        return $this->service;
//    }

    /**
     * @param mixed $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return mixed
     */
    public function getDelivery()
    {
        return $this->delivery;
    }




    public function __toString() {
        $res = "Scan Order";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }


}