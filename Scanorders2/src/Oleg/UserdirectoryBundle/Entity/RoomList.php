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
     * @ORM\ManyToMany(targetEntity="BuildingList", mappedBy="rooms")
     **/
    private $buildings;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->floors = new ArrayCollection();
        $this->buildings = new ArrayCollection();
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
        }
        return $this;
    }
    public function removeBuilding($building)
    {
        $this->buildings->removeElement($building);
    }




}