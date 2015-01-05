<?php

namespace Oleg\UserdirectoryBundle\Entity;

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
     * @ORM\OneToMany(targetEntity="RoomList", mappedBy="suite")
     **/
    private $rooms;

//    /**
//     * @ORM\ManyToOne(targetEntity="FloorList", inversedBy="suites")
//     * @ORM\JoinColumn(name="floor_id", referencedColumnName="id")
//     **/
//    protected $floor;
    /**
     * @ORM\ManyToMany(targetEntity="FloorList", mappedBy="suites")
     **/
    private $floors;

//    /**
//     * @ORM\ManyToOne(targetEntity="BuildingList", inversedBy="suites")
//     * @ORM\JoinColumn(name="building_id", referencedColumnName="id")
//     **/
    /**
     * @ORM\ManyToMany(targetEntity="BuildingList", mappedBy="suites")
     **/
    private $buildings;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->rooms = new ArrayCollection();
        $this->floors = new ArrayCollection();
        $this->buildings = new ArrayCollection();
    }



    public function addRoom($room)
    {
        if( $room && !$this->rooms->contains($room) ) {
            $this->rooms->add($room);
            $room->setSuite($this);
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
        }

        return $this;
    }
    public function removeBuilding($building)
    {
        $this->buildings->removeElement($building);
    }




}