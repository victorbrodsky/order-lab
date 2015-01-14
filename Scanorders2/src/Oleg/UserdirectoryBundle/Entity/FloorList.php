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
     * @ORM\ManyToMany(targetEntity="SuiteList", mappedBy="floors")
     **/
    protected $suites;

    /**
     * @ORM\ManyToMany(targetEntity="RoomList", mappedBy="floors")
     **/
    protected $rooms;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->suites = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }


    public function addSuite($suite)
    {
        if( $suite && !$this->suites->contains($suite) ) {
            $this->suites->add($suite);
        }

        return $this;
    }
    public function removeSuite($suite)
    {
        $this->suites->removeElement($suite);
        $suite->removeFloor($this);
    }
    public function getSuites()
    {
        return $this->suites;
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
        $room->removeFloor($this);
    }
    public function getRooms()
    {
        return $this->rooms;
    }

}