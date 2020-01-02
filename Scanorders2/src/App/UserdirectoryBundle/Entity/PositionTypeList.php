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
 * @ORM\Table(name="user_positionTypeList")
 */
class PositionTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PositionTypeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PositionTypeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="UserPosition", mappedBy="positionTypes")
     **/
    private $userPositions;



    public function __construct() {
        $this->userPositions = new ArrayCollection();
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

}