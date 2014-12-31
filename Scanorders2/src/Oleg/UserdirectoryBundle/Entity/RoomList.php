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
    protected $suite;

    /**
     * @ORM\ManyToOne(targetEntity="FloorList", inversedBy="rooms")
     * @ORM\JoinColumn(name="floor_id", referencedColumnName="id")
     **/
    protected $floor;

    /**
     * @ORM\ManyToOne(targetEntity="BuildingList", inversedBy="rooms")
     * @ORM\JoinColumn(name="building_id", referencedColumnName="id")
     **/
    protected $building;


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