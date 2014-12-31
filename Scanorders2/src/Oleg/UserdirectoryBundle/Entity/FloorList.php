<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_floorlist")
 */
class FloorList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="FloorList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="FloorList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="SuiteList", mappedBy="floor")
     **/
    protected $suites;

    /**
     * @ORM\OneToMany(targetEntity="RoomList", mappedBy="floor")
     **/
    protected $rooms;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->suites = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }


    public function addSuite($suite)
    {
        if( !$this->suites->contains($suite) ) {
            $this->suites->add($suite);
            $suite->setFloor($this);
        }

        return $this;
    }
    public function removeSuite($suite)
    {
        $this->suites->removeElement($suite);
    }
    public function getSuites()
    {
        return $this->suites;
    }


    public function addRoom($room)
    {
        if( !$this->rooms->contains($room) ) {
            $this->rooms->add($room);
            $room->setFloor($this);
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

}