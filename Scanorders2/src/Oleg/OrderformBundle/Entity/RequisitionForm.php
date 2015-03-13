<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\DocumentContainer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_requisitionForm")
 */
class RequisitionForm {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="LabOrder", inversedBy="requisitionForms")
     **/
    private $laborder;







    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $laborder
     */
    public function setLaborder($laborder)
    {
        $this->laborder = $laborder;
    }

    /**
     * @return mixed
     */
    public function getLaborder()
    {
        return $this->laborder;
    }




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