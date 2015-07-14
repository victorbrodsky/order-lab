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
     * This serves as default institution to set scan order scope (who can view this order: users from the with the same institutional scope can view this order)
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     */
    private $scanOrderInstitutionScope;

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

    /**
     * @param mixed $scanOrderInstitutionScope
     */
    public function setScanOrderInstitutionScope($scanOrderInstitutionScope)
    {
        $this->scanOrderInstitutionScope = $scanOrderInstitutionScope;
    }

    /**
     * @return mixed
     */
    public function getScanOrderInstitutionScope()
    {
        return $this->scanOrderInstitutionScope;
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