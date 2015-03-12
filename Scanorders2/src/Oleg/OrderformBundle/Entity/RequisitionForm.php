<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\DocumentContainer;
use Symfony\Component\Validator\Constraints as Assert;

///**
// * @ORM\Entity
// * @ORM\Table(name="scan_requisitionForm")
// */
class RequisitionForm {

//    /**
//     * @var integer
//     *
//     * @ORM\Column(name="id", type="integer")
//     * @ORM\Id
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    private $id;
//
//    /**
//     * @ORM\OneToOne(targetEntity="OrderInfo", mappedBy="requisitionForm")
//     **/
//    private $orderinfo;
//
//
//
//
//
//
//    public function __toString() {
//        $res = "Requisition Form";
//        if( $this->getId() ) {
//            $res = $res . " with ID=" . $this->getId();
//        }
//        return $res;
//    }

}