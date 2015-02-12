<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_accessionLaborder")
 */
class AccessionLaborder extends AccessionArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="laborder")
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", nullable=true)
     */
    protected $accession;


    //Lab Order contains: Lab Order ID Source, Lab Order ID
    /**
     * @ORM\ManyToOne(targetEntity="GeneralOrder")
     * @ORM\JoinColumn(name="generalorder_id", referencedColumnName="id", nullable=true)
     */
    private $order;

    //Requisition Form Images
    /**
     * ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\DocumentContainer", cascade={"persist","remove"})
     **/
    private $documentContainer;

    //Attach "Progress & Comments" page to the Lab Order
    //TODO: History (Progress & Comments) object is linked to the order

    //Requisition Form Source Location
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
     */
    private $sourceLocation;

    /**
     * Track Locations
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
     * @ORM\JoinTable(name="scan_laborder_location",
     *      joinColumns={@ORM\JoinColumn(name="laborder_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $trackLocations;



    public function __construct( $status = 'valid', $provider = null, $source = null ) {
        parent::__construct($status,$provider,$source);

        $this->trackLocations = new ArrayCollection();
    }



    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }



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

    /**
     * @param mixed $sourceLocation
     */
    public function setSourceLocation($sourceLocation)
    {
        $this->sourceLocation = $sourceLocation;
    }

    /**
     * @return mixed
     */
    public function getSourceLocation()
    {
        return $this->sourceLocation;
    }



    public function getTrackLocations()
    {
        return $this->trackLocations;
    }
    public function addTrackLocation($trackLocation)
    {
        if( $trackLocation && !$this->trackLocations->contains($trackLocation) ) {
            $this->trackLocations->add($trackLocation);
        }
        return $this;
    }
    public function removeTrackLocation($trackLocation)
    {
        $this->trackLocations->removeElement($trackLocation);
    }

}