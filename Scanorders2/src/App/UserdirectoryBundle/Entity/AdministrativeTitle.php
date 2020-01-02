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

/**
 * @ORM\Entity
 * @ORM\Table(name="user_administrativeTitle")
 */
class AdministrativeTitle extends BaseTitle
{

    /**
     * @ORM\ManyToOne(targetEntity="AdminTitleList")
     * @ORM\JoinColumn(name="name", referencedColumnName="id", nullable=true)
     **/
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="administrativeTitles")
     * @ORM\JoinColumn(name="fosuser", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_administrative_boss",
     *      joinColumns={@ORM\JoinColumn(name="administrative_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="boss_id", referencedColumnName="id")}
     * )
     **/
    private $boss;

    /**
     * @ORM\ManyToMany(targetEntity="PositionTypeList")
     * @ORM\JoinTable(name="user_administrative_userPosition",
     *      joinColumns={@ORM\JoinColumn(name="administrative_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userPosition_id", referencedColumnName="id")}
     * )
     **/
    private $userPositions;

    /**
     * Overwrite institution, so we can use join to find out Supervisors for location search.
     *
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="administrativeTitles")
     */
    protected $institution;



    function __construct($author=null)
    {
        parent::__construct($author);

        $this->boss = new ArrayCollection();
        $this->userPositions = new ArrayCollection();
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
     * Add boss
     *
     * @param \Oleg\OrderformBundle\Entity\User $boss
     * @return User
     */
    public function addBoss($boss)
    {
        if( !$this->boss->contains($boss) ) {
            $this->boss->add($boss);
        }

        return $this;
    }
    public function addBos($boss)
    {
        return $this->addBoss($boss);
    }
    /**
     * Remove boss
     *
     * @param \Oleg\OrderformBundle\Entity\User $boss
     */
    public function removeBoss($boss)
    {
        $this->boss->removeElement($boss);
    }
    public function removeBos($boss)
    {
        $this->removeBoss($boss);
    }

    /**
     * Get boss
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBoss()
    {
        return $this->boss;
    }

    public function addUserPosition($item)
    {
        if( !$this->userPositions->contains($item) ) {
            $this->userPositions->add($item);
        }
        return $this;
    }
    public function removeUserPosition($item)
    {
        $this->userPositions->removeElement($item);
    }
    public function getUserPositions()
    {
        return $this->userPositions;
    }



    public function __toString() {
        return "Administrative Title";
    }


}