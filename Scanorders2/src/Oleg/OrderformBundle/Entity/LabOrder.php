<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\DocumentContainer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_laborder")
 */
class LabOrder { //extends AccessionArrayFieldAbstract {

    /**
     * @var integer
     *OrderInfo
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="OrderInfo", mappedBy="laborder")
     **/
    private $orderinfo;

//    /**
//     * Lab Order might have many Requisition Form
//     * @ORM\OneToMany(targetEntity="RequisitionForm", mappedBy="laborder", cascade={"persist","remove"})
//     */
//    private $requisitionForms;





    public function __construct() {
        //$this->requisitionForms = new ArrayCollection();
    }





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
     * @param mixed $orderinfo
     */
    public function setOrderinfo($orderinfo)
    {
        $this->orderinfo = $orderinfo;
        $orderinfo->setLaborder($this);
    }

    /**
     * @return mixed
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }


//    public function getRequisitionForms()
//    {
//        return $this->requisitionForms;
//    }
//    public function addRequisitionForm($item)
//    {
//        if( !$this->requisitionForms->contains($item) ) {
//            $this->requisitionForms->add($item);
//        }
//    }
//    public function removeRequisitionForm($item)
//    {
//        $this->requisitionForms->removeElement($item);
//    }



    public function __toString() {
        $res = "Lab Order";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }

}