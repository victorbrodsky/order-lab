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

/**
 * @ORM\Entity
 * @ORM\Table(name="user_location")
 * @ORM\HasLifecycleCallbacks
 */
class Location extends ListAbstract
{

    const STATUS_UNVERIFIED = 0;    //unverified (not trusted)
    const STATUS_VERIFIED = 1;      //verified by admin


    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * status: valid, invalid
     * @ORM\Column(type="integer", options={"default" = 0}, nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="boolean", options={"default" = 1}, nullable=true)
     */
    protected $removable;

    /**
     * @ORM\ManyToMany(targetEntity="LocationTypeList", inversedBy="locations", cascade={"persist"})
     * @ORM\JoinTable(name="user_location_locationtype")
     **/
    protected $locationTypes;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $pager;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $mobile;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fax;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\OneToOne(targetEntity="GeoLocation", cascade={"persist"})
     **/
    protected $geoLocation;

    /**
     * @ORM\ManyToOne(targetEntity="BuildingList", inversedBy="locations", cascade={"persist"})
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id")
     */
    private $building;

    //floor has many suites. However, floor and suite exist in parallel in the Location object
    /**
     * @ORM\ManyToOne(targetEntity="FloorList",cascade={"persist"})
     **/
    protected $floor;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $floorSide;

    /**
     * @ORM\ManyToOne(targetEntity="SuiteList",cascade={"persist"})
     **/
    protected $suite;

    //suite has many rooms. However, suite and room exist in parallel in the Location object
    /**
     * @ORM\ManyToOne(targetEntity="RoomList",cascade={"persist"})
     **/
    protected $room;

    /**
     * @ORM\ManyToOne(targetEntity="MailboxList",cascade={"persist"})
     **/
    protected $mailbox;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comment;

    /**
     * Associated NYPH Code
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $associatedCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $associatedClia;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $associatedCliaExpDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $associatedPfi;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_location_assistant",
     *      joinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="assistant_id", referencedColumnName="id")}
     * )
     **/
    private $assistant;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="locations")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $institution;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ic;

    /**
     * @ORM\ManyToOne(targetEntity="LocationPrivacyList", inversedBy="locations")
     * @ORM\JoinColumn(name="privacy_id", referencedColumnName="id")
     **/
    private $privacy;


    public function __construct($creator=null) {

        parent::__construct($creator);

        $this->assistant = new ArrayCollection();
        $this->locationTypes = new ArrayCollection();

        $this->setRemovable(true);
        $this->setStatus(self::STATUS_UNVERIFIED);
    }


    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return mixed
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param mixed $floor
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;
    }

    /**
     * @return mixed
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @return mixed
     */
    public function getFloorSide()
    {
        return $this->floorSide;
    }

    /**
     * @param mixed $floorSide
     */
    public function setFloorSide($floorSide)
    {
        $this->floorSide = $floorSide;
    }


    /**
     * @param mixed $geoLocation
     */
    public function setGeoLocation($geoLocation)
    {
        $this->geoLocation = $geoLocation;
    }

    /**
     * @return mixed
     */
    public function getGeoLocation()
    {
        return $this->geoLocation;
    }


    public function getLocationTypes()
    {
        return $this->locationTypes;
    }
    public function addLocationType($type)
    {
        if( $type && !$this->locationTypes->contains($type) ) {
            $this->locationTypes->add($type);
        }

        return $this;
    }
    public function removeLocationType($type)
    {
        $this->locationTypes->removeElement($type);
    }

    /**
     * @param mixed $mailbox
     */
    public function setMailbox($mailbox)
    {
        $this->mailbox = $mailbox;
    }

    /**
     * @return mixed
     */
    public function getMailbox()
    {
        return $this->mailbox;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $pager
     */
    public function setPager($pager)
    {
        $this->pager = $pager;
    }

    /**
     * @return mixed
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $removable
     */
    public function setRemovable($removable)
    {
        $this->removable = $removable;
    }

    /**
     * @return mixed
     */
    public function getRemovable()
    {
        return $this->removable;
    }

    /**
     * @param mixed $room
     */
    public function setRoom($room)
    {
        $this->room = $room;
    }

    /**
     * @return mixed
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param mixed $suite
     */
    public function setSuite($suite)
    {
        $this->suite = $suite;
    }

    /**
     * @return mixed
     */
    public function getSuite()
    {
        return $this->suite;
    }


    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusStr()
    {
        return $this->getStatusStrByStatus($this->getStatus());
    }

    public function getStatusStrByStatus($status)
    {
        $str = $status;

        if( $status == self::STATUS_UNVERIFIED )
            $str = "Pending Administrative Review";

        if( $status == self::STATUS_VERIFIED )
            $str = "Verified by Administration";

        return $str;
    }

    //interface function
    public function getAuthor()
    {
        return $this->getCreator();
    }
    public function setAuthor($author)
    {
        return $this->setCreator($author);
    }
    public function getUpdateAuthor()
    {
        return $this->getUpdatedby();
    }
    public function setUpdateAuthor($author)
    {
        return $this->setUpdatedby($author);
    }



    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;

        //set name if empty
        //if( $institution && !$this->getName() ) {
        //    $this->setName($institution->getName()." Location");
        //}
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

//    /**
//     * @param mixed $department
//     */
//    public function setDepartment($department)
//    {
//        $this->department = $department;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDepartment()
//    {
//        return $this->department;
//    }
//
//    /**
//     * @param mixed $division
//     */
//    public function setDivision($division)
//    {
//        $this->division = $division;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDivision()
//    {
//        return $this->division;
//    }
//
//    /**
//     * @param mixed $service
//     */
//    public function setService($service)
//    {
//        $this->service = $service;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getService()
//    {
//        return $this->service;
//    }

    /**
     * Add assistant
     *
     * @param \App\OrderformBundle\Entity\User $assistant
     * @return User
     */
    public function addAssistant($assistant)
    {
        if( !$this->assistant->contains($assistant) ) {
            $this->assistant->add($assistant);
        }

        return $this;
    }
    /**
     * Remove assistant
     *
     * @param \App\OrderformBundle\Entity\User $assistant
     */
    public function removeAssistant($assistant)
    {
        $this->assistant->removeElement($assistant);
    }

    /**
     * Get assistant
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssistant()
    {
        return $this->assistant;
    }

    /**
     * @param mixed $associatedCode
     */
    public function setAssociatedCode($associatedCode)
    {
        $this->associatedCode = $associatedCode;
    }

    /**
     * @return mixed
     */
    public function getAssociatedCode()
    {
        return $this->associatedCode;
    }

    /**
     * @param mixed $associatedClia
     */
    public function setAssociatedClia($associatedClia)
    {
        $this->associatedClia = $associatedClia;
    }

    /**
     * @return mixed
     */
    public function getAssociatedClia()
    {
        return $this->associatedClia;
    }

    /**
     * @param mixed $associatedCliaExpDate
     */
    public function setAssociatedCliaExpDate($associatedCliaExpDate)
    {
        $this->associatedCliaExpDate = $associatedCliaExpDate;
    }

    /**
     * @return mixed
     */
    public function getAssociatedCliaExpDate()
    {
        return $this->associatedCliaExpDate;
    }

    /**
     * @param mixed $associatedPfi
     */
    public function setAssociatedPfi($associatedPfi)
    {
        $this->associatedPfi = $associatedPfi;
    }

    /**
     * @return mixed
     */
    public function getAssociatedPfi()
    {
        return $this->associatedPfi;
    }

    /**
     * @param mixed $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return mixed
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $ic
     */
    public function setIc($ic)
    {
        $this->ic = $ic;
    }

    /**
     * @return mixed
     */
    public function getIc()
    {
        return $this->ic;
    }

    /**
     * @param mixed $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * @return mixed
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }


    public function __toString() {

        return $this->getNameFull();
    }

    public function getShortName() {
        $name = "";
        if( $this->getGeoLocation() != "" ) {
            $name = $this->getGeoLocation()."";
        }
        return $name;
    }

    public function getNameFull($html=false) {

        $name = "";

        if( $this->user ) {
            $name = $name . $this->user->getUsernameOptimal() . "'s ";
        }

//        if( $html ) {
//            $name = $name . "<strong>" . $this->name . "</strong>";
//        } else {
//            $name = $name . $this->name;
//        }

        $locationFullname = $this->getLocationFullBuildingName($html);

        $name = $name . $locationFullname;

        return $name;
    }

    public function getLocationFullBuildingName($html=false, $withType=true) {

        $name = "";

//        if( $html ) {
//            $name = $name . "<strong>" . $this->name . "</strong>";
//        } else {
//            $name = $name . $this->name;
//        }

        $detailsArr = array();

        if( $this->getRoom() ) {
            $detailsArr[] = $this->getRoom();
        }

        if( $this->getSuite() ) {
            $detailsArr[] = $this->getSuite();
        }

        if( $this->getBuilding() ) {
            $detailsArr[] = $this->getBuilding()."";
        }

        if( $this->getInstitution() && $this->getBuilding() == null ) {
            $detailsArr[] = $this->getInstitution()->getName()."";
        }

        if( $this->getMailbox() ) {
            $detailsArr[] = $this->getMailbox();
        }

        //print_r($detailsArr);
        //exit();

        if( count($detailsArr) > 0 ) {
            $detailsStr = implode(", ",$detailsArr);
            if( $withType ) {
                $name = $this->getLocationTypesStr($html);
                $name = $name . " (" . $detailsStr . ")";
            } else {
                $name = $detailsStr;
            }
        }

        return $name;
    }

    public function getLocationAddress($delimeter=", ") {
        $address = null;
        $buildingLevelStr = null;
        $geoLevelStr = null;
        $detailsArr = array();

        if( $this->getRoom() ) {
            $detailsArr[] = $this->getRoom();
        }

        if( $this->getSuite() ) {
            $detailsArr[] = $this->getSuite();
        }

//        if( $this->getBuilding() ) {
//            $detailsArr[] = $this->getBuilding()."";
//        }

        if( $this->getInstitution() && $this->getBuilding() == null ) {
            $detailsArr[] = $this->getInstitution()->getName()."";
        }

        if( $this->getMailbox() ) {
            $detailsArr[] = $this->getMailbox();
        }

        if( count($detailsArr) > 0 ) {
            $buildingLevelStr = implode($delimeter,$detailsArr);
        }

        $geoLocation = $this->getGeoLocation();
        if( $geoLocation ) {
            $geoLevelStr = $geoLocation->getFullGeoLocation($delimeter);
        }

        if( $buildingLevelStr ) {
            $address = $buildingLevelStr . $delimeter;
        }

        if( $geoLevelStr ) {
            $address = $address . $geoLevelStr;
        }

        return $address;
    }

    public function getLocationNameNoType() {
        $detailsArr = array();

        if( $this->getRoom() ) {
            $detailsArr[] = $this->getRoom();
        }

        if( $this->getSuite() ) {
            $detailsArr[] = $this->getSuite();
        }

//        if( $this->getBuilding() ) {
//            $detailsArr[] = $this->getBuilding()."";
//        }

//        if( $this->getInstitution() && $this->getBuilding() == null ) {
//            $detailsArr[] = $this->getInstitution()->getName()."";
//        }

        if( $this->getMailbox() ) {
            $detailsArr[] = $this->getMailbox();
        }

        return implode(", ",$detailsArr);
    }

    public function getStringify() {
        $stringify = $this->getShortName() . $this->getLocationAddress() . $this->getLocationTypesStr(); //$this->getLocationFullBuildingName()
        return $stringify;
    }
    public function getHashName() {
        $stringify = $this->getStringify();
        $hash = hash("sha1",$stringify);
        return $hash;
    }

    public function hasLocationTypeName( $typeName ) {
        foreach( $this->getLocationTypes() as $loctype ) {
            if( $loctype->getName()."" == $typeName ) {
                return true;
            }
        }
        return false;
    }

    public function getLocationTypesStr($html=false) {
        $locnameArr = array();

        if( $html ) {
            $name = "<strong>" . $this->name . "</strong>";
        } else {
            $name = $this->name;
        }

        // If the location type = "Employee Desk", add " (Desk)" after the location name
        foreach( $this->getLocationTypes() as $loctype ) {
            if( $loctype->getName()."" == "Employee Desk" ) {
                $locnameArr[] = "Desk";
            }
            if( $loctype->getName()."" == "Employee Cubicle" ) {
                $locnameArr[] = "Cubicle";
            }
        }

        if( count($locnameArr) > 0 ) {
            $name = $name . " (" . implode(",",$locnameArr) . ")";
        }

        return $name;
    }


    //set suite, room, floor, building, institution relationship
    /**
     * @ORM\PreFlush
     */
    public function setRoomSuiteFloorBuilding()
    {
        //exit('set room, suite, floor, building');
        //add institution to building
        if( $this->building ) {
            if( $this->institution ) {
                $this->building->addInstitution($this->institution);
            }
        }

        //add room to suit
        if( $this->suite ) {
            if( $this->room ) {
                $this->room->addSuite($this->suite);
            }
        }

        //add room and suite to floor
        if( $this->floor ) {
            if( $this->room ) {
                $this->room->addFloor($this->floor);
            }
            if( $this->suite ) {
                $this->suite->addFloor($this->floor);
            }
        }

        //add room and suite to building
        if( $this->building ) {
            if( $this->room ) {
                $this->room->addBuilding($this->building);
            }
            if( $this->suite ) {
                $this->suite->addBuilding($this->building);
            }
        }

        //add room and suite to department
//        if( $this->department ) {
//            if( $this->room ) {
//                $this->room->addDepartment($this->department);
//            }
//            if( $this->suite ) {
//                $this->suite->addDepartment($this->department);
//            }
//        }

        //echo "set room suite floor building <br>";
    }


    public function createFullTitle()
    {
        return $this->getNameFull();
    }

    public function isEmpty($withLocationType=true)
    {
        $empty = true;
        //echo "isEmpty Location: ".$this."<br>";
        //echo "isEmpty Location ID=".$this->getId()."<br>";
        //echo "isEmpty Location Name=".$this->getName()."<br>";

        if ($this->getId()) {
            return false;
        } else {
            //return true;
        }

        if( $this->getName() ) {
            return false;
        } else {
            //return true;
//            $institution = $this->getInstitution();
//            if( $institution ) {
//                $this->setName($institution->getName()." Location");
//            }
        }

        //be strict: location name must be set. If not then it's empty
        if ($this->getName() == null) {
            return true;
        }

        if( $withLocationType == true ) {
            if( $this->getLocationTypes() ) {
                return false;
            }
        }

        if( $this->getInstitution() ) {
            return false;
        }

        if( $this->getPhone() ) {
            return false;
        }
        if( $this->getFax() ) {
            return false;
        }
        if( $this->getPager() ) {
            return false;
        }
        if( $this->getMobile() ) {
            return false;
        }
        if( $this->getEmail() ) {
            return false;
        }
        if( $this->getBuilding() ) {
            return false;
        }
        if( $this->getRoom() ) {
            return false;
        }
        if( $this->getSuite() ) {
            return false;
        }
        if( $this->getFloor() ) {
            return false;
        }
        if( $this->getMailbox() ) {
            return false;
        }
        if( $this->getComment() ) {
            return false;
        }

        if( $this->getGeoLocation() && $this->getGeoLocation()->getFullGeoLocation() ) {
            return false;
        }

        return $empty;
    }

    public function isHomeAndEmpty() {

        $empty = false;

        if( $this->getName() == "Home" ) {
            $empty = true;
//            if( $this->isEmpty(false) ) {
//                $empty = true;
//            }
            if( $this->getPhone() ) {
                return false;
            }
            if( $this->getFax() ) {
                return false;
            }
            if( $this->getPager() ) {
                return false;
            }
            if( $this->getMobile() ) {
                return false;
            }
            if( $this->getEmail() ) {
                return false;
            }
            if( $this->getBuilding() ) {
                return false;
            }
            if( $this->getRoom() ) {
                return false;
            }
            if( $this->getSuite() ) {
                return false;
            }
            if( $this->getFloor() ) {
                return false;
            }
            if( $this->getMailbox() ) {
                return false;
            }
            if( $this->getComment() ) {
                return false;
            }

            if( $this->getGeoLocation() && $this->getGeoLocation()->getFullGeoLocation() ) {
                return false;
            }

        } else {
            $empty = false;
        }

        //echo $this->getName().": Location empty=".$empty."<br>";
        return $empty;
    }

}