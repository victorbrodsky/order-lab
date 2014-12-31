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
    protected $rooms;

    /**
     * @ORM\ManyToOne(targetEntity="FloorList", inversedBy="suites")
     * @ORM\JoinColumn(name="floor_id", referencedColumnName="id")
     **/
    protected $floor;

    /**
     * @ORM\ManyToOne(targetEntity="BuildingList", inversedBy="suites")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id")
     **/
    protected $building;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }



    public function addRoom($room)
    {
        if( !$this->rooms->contains($room) ) {
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




}