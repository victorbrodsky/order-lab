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