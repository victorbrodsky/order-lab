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



    //"Laboratory Test Title"
    //(Select2 - this should be added to List Manager as "Laboratory Tests" and the "Laboratory Test ID Type" +
    //"Laboratory Test ID" + "Laboratory Test Title" fields should work exactly like the Grant Issuer, Grand ID, and Grant Title -
    //all three should be on the same list in List Manager)

    /**
     * @ORM\ManyToOne(targetEntity="LabTest", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $labTest;





    /**
     * @param mixed $labTest
     */
    public function setLabTest($labTest)
    {
        $this->labTest = $labTest;
    }

    /**
     * @return mixed
     */
    public function getLabTest()
    {
        return $this->labTest;
    }





    public function __toString() {
        $res = "Lab Order";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }

}