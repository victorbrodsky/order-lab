<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_spot")
 * @ORM\HasLifecycleCallbacks
 */
class Spot {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $creation;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $spottedOn;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedOn;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $updatedBy;


    //Location Spot Purpose
    /**
     * @ORM\ManyToOne(targetEntity="LocationSpotPurpose", cascade={"persist"})
     * @ORM\JoinColumn(name="locationSpotPurpose_id", referencedColumnName="id", nullable=true)
     */
    private $locationSpotPurpose;

    //Current Location
    /**
     * @ORM\ManyToOne(targetEntity="Location", cascade={"persist"})
     * @ORM\JoinColumn(name="currentLocation_id", referencedColumnName="id", nullable=true)
     */
    private $currentLocation;

    //Intended Destination
    /**
     * @ORM\ManyToOne(targetEntity="Location", cascade={"persist"})
     * @ORM\JoinColumn(name="intendedLocation_id", referencedColumnName="id", nullable=true)
     */
    private $intendedLocation;




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
     * @param \DateTime $updatedOn
     * @ORM\PrePersist
     */
    public function setUpdatedOn($updated=null)
    {
        if( $updated ) {
            $this->updatedOn = $updated;
        } else {
            $this->updatedOn = new \DateTime();
        }
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

    /**
     * @return mixed
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param \DateTime $creation
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;
    }

    /**
     * @return \DateTime
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * @param \DateTime $spottedOn
     */
    public function setSpottedOn($spottedOn)
    {
        $this->spottedOn = $spottedOn;
    }

    /**
     * @return \DateTime
     */
    public function getSpottedOn()
    {
        return $this->spottedOn;
    }

    /**
     * @param mixed $currentLocation
     */
    public function setCurrentLocation($currentLocation)
    {
        $this->currentLocation = $currentLocation;
    }

    /**
     * @return mixed
     */
    public function getCurrentLocation()
    {
        return $this->currentLocation;
    }

    /**
     * @param mixed $intendedLocation
     */
    public function setIntendedLocation($intendedLocation)
    {
        $this->intendedLocation = $intendedLocation;
    }

    /**
     * @return mixed
     */
    public function getIntendedLocation()
    {
        return $this->intendedLocation;
    }

    /**
     * @param mixed $locationSpotPurpose
     */
    public function setLocationSpotPurpose($locationSpotPurpose)
    {
        $this->locationSpotPurpose = $locationSpotPurpose;
    }

    /**
     * @return mixed
     */
    public function getLocationSpotPurpose()
    {
        return $this->locationSpotPurpose;
    }




}