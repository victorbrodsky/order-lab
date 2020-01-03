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
 * @ORM\Table(name="user_roomlist")
 */
class RoomList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="RoomList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="RoomList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="SuiteList", inversedBy="rooms")
     * @ORM\JoinTable(name="user_rooms_suites")
     **/
    private $suites;

    /**
     * @ORM\ManyToMany(targetEntity="FloorList", inversedBy="rooms")
     * @ORM\JoinTable(name="user_rooms_floors")
     **/
    private $floors;

    /**
     * @ORM\ManyToMany(targetEntity="BuildingList", inversedBy="rooms")
     * @ORM\JoinTable(name="user_rooms_buildings")
     **/
    private $buildings;

//    /**
//     * @ORM\ManyToMany(targetEntity="Department", inversedBy="rooms")
//     * @ORM\JoinTable(name="user_rooms_departments")
//     **/
//    private $departments;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->floors = new ArrayCollection();
        $this->suites = new ArrayCollection();
        $this->buildings = new ArrayCollection();
        //$this->departments = new ArrayCollection();
    }



    public function getSuites()
    {
        return $this->suites;
    }
    public function addSuite($suite)
    {
        if( $suite && !$this->suites->contains($suite) ) {
            $this->suites->add($suite);
            $suite->addRoom($this);
        }
        return $this;
    }
    public function removeSuite($suite)
    {
        $this->suites->removeElement($suite);
    }


    public function getFloors()
    {
        return $this->floors;
    }
    public function addFloor($floor)
    {
        if( $floor && !$this->floors->contains($floor) ) {
            $this->floors->add($floor);
            $floor->addRoom($this);
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
        if( !$this->buildings->contains($building) ) {
            $this->buildings->add($building);
            $building->addRoom($this);
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
//        if( !$this->departments->contains($department) ) {
//            $this->departments->add($department);
//            $department->addRoom($this);
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