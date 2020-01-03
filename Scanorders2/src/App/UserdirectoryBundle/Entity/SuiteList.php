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
 * @ORM\Table(name="user_suitelist")
 */
class SuiteList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="SuiteList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="SuiteList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="RoomList", mappedBy="suites")
     **/
    private $rooms;

    /**
     * @ORM\ManyToMany(targetEntity="FloorList", inversedBy="suites")
     * @ORM\JoinTable(name="user_suites_floors")
     **/
    private $floors;

    /**
     * @ORM\ManyToMany(targetEntity="BuildingList", inversedBy="suites")
     * @ORM\JoinTable(name="user_suites_buildings")
     **/
    private $buildings;

//    /**
//     * @ORM\ManyToMany(targetEntity="Department", inversedBy="suites")
//     * @ORM\JoinTable(name="user_suites_departments")
//     **/
//    private $departments;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->floors = new ArrayCollection();
        $this->buildings = new ArrayCollection();
        //$this->departments = new ArrayCollection();
    }



    public function addRoom($room)
    {
        if( $room && !$this->rooms->contains($room) ) {
            $this->rooms->add($room);
        }

        return $this;
    }
    public function removeRoom($room)
    {
        $this->rooms->removeElement($room);
    }
    public function getRooms()
    {
        return $this->rooms;
    }


    public function getFloors()
    {
        return $this->floors;
    }
    public function addFloor($floor)
    {
        if( $floor && !$this->floors->contains($floor) ) {
            $this->floors->add($floor);
            $floor->addSuite($this);
        }
        return $this;
    }
    public function removeFloor($floor)
    {
        $this->floors->removeElement($floor);
    }


    public function getBuildings()
    {
        return $this->buildings;
    }
    public function addBuilding($building)
    {
        if( $building && !$this->buildings->contains($building) ) {
            $this->buildings->add($building);
            $building->addSuite($this);
        }

        return $this;
    }
    public function removeBuilding($building)
    {
        $this->buildings->removeElement($building);
    }


//    public function getDepartments()
//    {
//        return $this->departments;
//    }
//    public function addDepartment($department)
//    {
//        if( $department && !$this->departments->contains($department) ) {
//            $this->departments->add($department);
//            $department->addSuite($this);
//        }
//
//        return $this;
//    }
//    public function removeDepartment($department)
//    {
//        $this->departments->removeElement($department);
//    }

    public function getFullName() {
        $names = array();
        foreach( $this->getBuildings() as $building ) {
            $names[] = $building."";
        }

        $namesStr = implode(",", $names);

        if( $namesStr ) {
            $fullName = $this->getName() . " (" . implode(",", $names) . ")";
        } else {
            $fullName = $this->getName();
        }

        return $fullName;
    }


}