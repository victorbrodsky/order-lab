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


}