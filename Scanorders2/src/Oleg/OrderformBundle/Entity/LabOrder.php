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
class LabOrder extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="laborder")
     **/
    protected $message;

    //"Signing Provider" field: message.proxyusers

    //"Receiving Providers" field: message.orderRecipients

    //"Laboratory Test ID Type" field (just like MRN Type; Select2)
    /**
     * @ORM\ManyToOne(targetEntity="LabTestType", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $labTestType;

    //"Laboratory Test ID" field (just like MRN)
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $labTestId;

    /**
     * "Laboratory Test Title"
     * @ORM\Column(type="string", nullable=true)
     */
    private $labTestTitle;

    //"Laboratory Test Title"
    //(Select2 - this should be added to List Manager as "Laboratory Tests" and the "Laboratory Test ID Type" +
    //"Laboratory Test ID" + "Laboratory Test Title" fields should work exactly like the Grant Issuer, Grand ID, and Grant Title -
    //all three should be on the same list in List Manager)

    //Oleg Note: Lab Order Requisition is still an order. Still, it can be shown as a list, with some additional field ($abbreviation,$orderinlist...),
    //as exception by separate implementation of controller and view.



    /**
     * @param mixed $labTestId
     */
    public function setLabTestId($labTestId)
    {
        $this->labTestId = $labTestId;
    }

    /**
     * @return mixed
     */
    public function getLabTestId()
    {
        return $this->labTestId;
    }

    /**
     * @param mixed $labTestType
     */
    public function setLabTestType($labTestType)
    {
        $this->labTestType = $labTestType;
    }

    /**
     * @return mixed
     */
    public function getLabTestType()
    {
        return $this->labTestType;
    }

    /**
     * @param mixed $labTestTitle
     */
    public function setLabTestTitle($labTestTitle)
    {
        $this->labTestTitle = $labTestTitle;
    }

    /**
     * @return mixed
     */
    public function getLabTestTitle()
    {
        return $this->labTestTitle;
    }





    public function __toString() {
        $res = "Lab Order";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }

}