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

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_tracker")
 */
class Tracker {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\OneToMany(targetEntity="Spot", mappedBy="tracker", cascade={"persist"})
     */
    private $spots;



    public function __construct()
    {
        $this->spots = new ArrayCollection();
    }



    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }



    public function getSpots()
    {
        return $this->spots;
    }
    public function addSpot($item)
    {
        if( $item && !$this->spots->contains($item) ) {
            $this->spots->add($item);
            $item->setTracker($this);
        }
        return $this;
    }
    public function removeSpot($item)
    {
        $this->spots->removeElement($item);
    }

    //empty if all spots are empty
    public function removeEmptySpots() {
        foreach( $this->getSpots() as $spot ) {
            if( $spot->isEmpty() ) {
                //echo "remove spot!!!!! <br>";
                $this->removeSpot($spot);
            }
        }
    }
    public function isEmpty() {
        if( count($this->getSpots()) > 0 ) {
            $empty = false;
        } else {
            $empty = true;
        }
        return $empty;
    }

}