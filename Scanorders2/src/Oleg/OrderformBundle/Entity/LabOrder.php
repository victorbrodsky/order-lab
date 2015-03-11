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
     *
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
//     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="laborder")
//     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", nullable=true)
//     */
//    protected $accession;


//    //Lab Order contains: Lab Order ID Source, Lab Order ID
//    /**
//     * @ORM\ManyToOne(targetEntity="GeneralOrder")
//     * @ORM\JoinColumn(name="generalorder_id", referencedColumnName="id", nullable=true)
//     */
//    private $order;

    ///////////////////// This order (scanorder) specific, unique fields /////////////////////

    //Requisition Form Images
    /**
     * ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\DocumentContainer", cascade={"persist","remove"})
     **/
    private $documentContainer;

    //Requisition Form Source Location: single
//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
//     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
//     */
//    private $sourceLocation;

//    /**
//     * Track Locations: can be many
//     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
//     * @ORM\JoinTable(name="scan_laborder_location",
//     *      joinColumns={@ORM\JoinColumn(name="laborder_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id", unique=true)}
//     *      )
//     **/
//    private $trackLocations;




    public function __construct() {

        //$this->trackLocations = new ArrayCollection();

        //testing
        //echo "doc container=".$this->getDocumentContainer()."<br>";
        if( !$this->getDocumentContainer() ) {
            $this->setDocumentContainer( new DocumentContainer() );
        }
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



//    /**
//     * @param mixed $order
//     */
//    public function setOrder($order)
//    {
//        $this->order = $order;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getOrder()
//    {
//        return $this->order;
//    }



    /**
     * @param mixed $documentContainer
     */
    public function setDocumentContainer($documentContainer)
    {
        $this->documentContainer = $documentContainer;
    }

    /**
     * @return mixed
     */
    public function getDocumentContainer()
    {
        return $this->documentContainer;
    }

//    /**
//     * @param mixed $sourceLocation
//     */
//    public function setSourceLocation($sourceLocation)
//    {
//        $this->sourceLocation = $sourceLocation;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSourceLocation()
//    {
//        return $this->sourceLocation;
//    }



//    public function getTrackLocations()
//    {
//        return $this->trackLocations;
//    }
//    public function addTrackLocation($trackLocation)
//    {
//        if( $trackLocation && !$this->trackLocations->contains($trackLocation) ) {
//            $this->trackLocations->add($trackLocation);
//        }
//        return $this;
//    }
//    public function removeTrackLocation($trackLocation)
//    {
//        $this->trackLocations->removeElement($trackLocation);
//    }

    public function __toString() {
        $res = "Lab Order";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId();
        }
        return $res;
    }

}