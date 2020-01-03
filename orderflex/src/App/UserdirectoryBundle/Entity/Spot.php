<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\UserdirectoryBundle\Entity;

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
     * @ORM\ManyToOne(targetEntity="Tracker", inversedBy="spots", cascade={"persist"})
     * @ORM\JoinColumn(name="tracker_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $tracker;

    //Source System
    /**
     * @ORM\ManyToOne(targetEntity="SourceSystemList")
     */
    private $source;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $creation;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $spottedOn;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedOn;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $updatedBy;


    //Location Spot Purpose
    /**
     * @ORM\ManyToOne(targetEntity="SpotPurpose", cascade={"persist"})
     * @ORM\JoinColumn(name="spotPurpose_id", referencedColumnName="id", nullable=true)
     */
    private $spotPurpose;

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


//    //Entity: [Patient/Encounter/Procedure/Accession/Part/Block/Slide] Dropdown
//    /**
//     * @ORM\ManyToOne(targetEntity="SpotEntity", cascade={"persist"})
//     */
//    private $spotEntity;
//
//    //Patient's MRN Type:
//    /**
//     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\MrnType", cascade={"persist"})
//     */
//    private $mrnType;
//
//    //Patient's MRN:
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $mrn;


    public function __construct( $author=null, $source = null )
    {
        $this->setAuthor($author);
        $this->setSource($source);

        $this->setCreation( new \DateTime() );
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
     * @param \DateTime $updatedOn
     * @ORM\PrePersist
     */
    public function setUpdatedOn() //$updated=null
    {
//        if( $updated ) {
//            $this->updatedOn = $updated;
//        } else {
//            if( $this->id ) {
//                $this->updatedOn = new \DateTime();
//            }
//        }
        $this->updatedOn = new \DateTime();
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
     * @param mixed $spotPurpose
     */
    public function setSpotPurpose($spotPurpose)
    {
        $this->spotPurpose = $spotPurpose;
    }

    /**
     * @return mixed
     */
    public function getSpotPurpose()
    {
        return $this->spotPurpose;
    }

    /**
     * @param mixed $tracker
     */
    public function setTracker($tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * @return mixed
     */
    public function getTracker()
    {
        return $this->tracker;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    //empty if this spot's currentLocation and intendedLocation are empty
    public function isEmpty() {
        $emptyCount = 0;
        if( $this->getCurrentLocation() ) {
            if( $this->getCurrentLocation()->isEmpty() ) {
                $emptyCount++;
            }
        } else {
            $emptyCount++;
        }

        if( $this->getIntendedLocation() ) {
            if( $this->getIntendedLocation()->isEmpty() ) {
                $emptyCount++;
            }
        } else {
            $emptyCount++;
        }

        if( $emptyCount === 2 ) {
            $empty = true;
        } else {
            $empty = false;
        }
        return $empty;
    }


}