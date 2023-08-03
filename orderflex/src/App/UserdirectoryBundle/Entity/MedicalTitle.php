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

#[ORM\Table(name: 'user_medicalTitle')]
#[ORM\Entity]
class MedicalTitle extends BaseTitle
{

    #[ORM\ManyToOne(targetEntity: 'MedicalTitleList')]
    #[ORM\JoinColumn(name: 'name', referencedColumnName: 'id', nullable: true)]
    protected $name;

    #[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'medicalTitles')]
    #[ORM\JoinColumn(name: 'fosuser', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $user;

    #[ORM\JoinTable(name: 'user_medicaltitle_medicalspeciality')]
    #[ORM\JoinColumn(name: 'medicaltitle_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'medicalspeciality_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'MedicalSpecialties')]
    protected $specialties;



    #[ORM\JoinTable(name: 'user_medicalTitle_userPosition')]
    #[ORM\JoinColumn(name: 'medicalTitle_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'userPosition_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'PositionTypeList')]
    private $userPositions;



    function __construct($author=null)
    {
        parent::__construct($author);

        $this->specialties = new ArrayCollection();

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


    public function getSpecialties()
    {
        return $this->specialties;
    }
    public function addSpecialty($specialty)
    {
        if( $specialty && !$this->specialties->contains($specialty) ) {
            $this->specialties->add($specialty);
        }
        return $this;
    }
    public function removeSpecialty($specialty)
    {
        $this->specialties->removeElement($specialty);
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
        return "Medical Appointment Title";
    }
}