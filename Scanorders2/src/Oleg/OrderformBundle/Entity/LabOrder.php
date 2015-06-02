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
     *Message
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="laborder")
     **/
    private $message;

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
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        $message->setLaborder($this);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
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