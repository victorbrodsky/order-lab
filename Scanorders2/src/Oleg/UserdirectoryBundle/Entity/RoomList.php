<?php

namespace Oleg\UserdirectoryBundle\Entity;

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


    //TODO: make many to many ?
    /**
     * @ORM\ManyToOne(targetEntity="SuiteList", inversedBy="rooms")
     * @ORM\JoinColumn(name="suite_id", referencedColumnName="id")
     **/
    private $suite;

    /**
     * @ORM\ManyToMany(targetEntity="FloorList", mappedBy="rooms")
     **/
    private $floors;

    /**
     * @ORM\ManyToMany(targetEntity="BuildingList", inversedBy="rooms")
     * @ORM\JoinTable(name="user_rooms_buildings")
     **/
    private $buildings;

    /**
     * @ORM\ManyToMany(targetEntity="Department", inversedBy="suites")
     * @ORM\JoinTable(name="user_rooms_departments")
     **/
    private $departments;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->floors = new ArrayCollection();
        $this->buildings = new ArrayCollection();
        $this->departments = new ArrayCollection();
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


    public function getFloors()
    {
        return $this->floor;
    }
    public function addFloor($floor)
    {
        if( !$this->floors->contains($floor) ) {
            $this->floors->add($floor);
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


    public function getDepartments()
    {
        return $this->departments;
    }
    public function addDepartment($department)
    {
        if( !$this->departments->contains($department) ) {
            $this->departments->add($department);
            $department->addRoom($this);
        }

        return $this;
    }
    public function removeDepartment($department)
    {
        $this->departments->removeElement($department);
    }


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