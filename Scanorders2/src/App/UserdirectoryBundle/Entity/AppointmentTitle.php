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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_appointmentTitle")
 */
class AppointmentTitle extends BaseTitle
{

    /**
     * @ORM\ManyToOne(targetEntity="AppTitleList")
     * @ORM\JoinColumn(name="name", referencedColumnName="id", nullable=true)
     **/
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="appointmentTitles")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

//    /**
//     * @ORM\Column(name="position", type="string", nullable=true)
//     */
//    protected $position;
    /**
     * @ORM\ManyToMany(targetEntity="PositionTrackTypeList", inversedBy="appointmentTitles")
     * @ORM\JoinTable(name="user_appointmenttitle_position")
     **/
    private $positions;

    /**
     * @ORM\ManyToOne(targetEntity="ResidencyTrackList")
     * @ORM\JoinColumn(name="residencyTrack_id", referencedColumnName="id")
     **/
    private $residencyTrack;

    /**
     * @ORM\ManyToOne(targetEntity="FellowshipTypeList")
     * @ORM\JoinColumn(name="fellowshipType_id", referencedColumnName="id")
     **/
    private $fellowshipType;


    public function __construct($creator=null) {
        $this->positions = new ArrayCollection();
    }


    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $fellowshipType
     */
    public function setFellowshipType($fellowshipType)
    {
        $this->fellowshipType = $fellowshipType;
    }

    /**
     * @return mixed
     */
    public function getFellowshipType()
    {
        return $this->fellowshipType;
    }

    /**
     * @param mixed $residencyTrack
     */
    public function setResidencyTrack($residencyTrack)
    {
        $this->residencyTrack = $residencyTrack;
    }

    /**
     * @return mixed
     */
    public function getResidencyTrack()
    {
        return $this->residencyTrack;
    }



    public function addPosition($item)
    {
        if( $item && !$this->positions->contains($item) ) {
            $this->positions->add($item);
        }
        return $this;
    }
    public function removePosition($item)
    {
        $this->positions->removeElement($item);
    }
    public function getPositions()
    {
        return $this->positions;
    }



    public function __toString() {
        return "Academic Appointment Title";
    }
}