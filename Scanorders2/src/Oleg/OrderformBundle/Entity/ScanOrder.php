<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_scanorder")
 */
class ScanOrder {


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="OrderInfo", mappedBy="scanorder")
     **/
    private $orderinfo;


    ///////////////////// This order (scanorder) specific, unique fields /////////////////////

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Service")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=true)
     */
    private $service;

    /**
     * Conflicting accession number is replaced, so keep the reference to dataqualitymrnacc object in the scanorder (unlike to dataqualityage)
     *
     * @ORM\OneToMany(targetEntity="DataQualityMrnAcc", mappedBy="scanorder", cascade={"persist"})
     */
    private $dataqualitymrnacc;




    public function __construct()
    {
        $this->dataqualitymrnacc = new ArrayCollection();
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
     * @return mixed
     */
    public function getDataqualitymrnacc()
    {
        return $this->dataqualitymrnacc;
    }
    public function addDataqualityMrnAcc($dataqualitymrnacc)
    {
        if( !$this->dataqualitymrnacc->contains($dataqualitymrnacc) ) {
            $this->dataqualitymrnacc->add($dataqualitymrnacc);
        }
    }
    public function removeDataqualityMrnAcc($dataqualitymrnacc)
    {
        $this->dataqualitymrnacc->removeElement($dataqualitymrnacc);
    }



    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $orderinfo
     */
    public function setOrderinfo($orderinfo)
    {
        $this->orderinfo = $orderinfo;
        $orderinfo->setScanorder($this);
    }

    /**
     * @return mixed
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }

    public function __toString() {
        $res = "Scan Order";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }


}