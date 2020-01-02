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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_locationTypeList")
 */
class LocationTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="LocationTypeList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LocationTypeList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="Location", mappedBy="locationTypes")
     **/
    private $locations;


    public function __construct($creator=null) {

        $this->synonyms = new ArrayCollection();
        $this->assistant = new ArrayCollection();

        $this->locations = new ArrayCollection();
    }


    public function getLocations()
    {
        return $this->locations;
    }
    public function addLocation($location)
    {
        if( !$this->locations->contains($location) ) {
            $this->locations->add($location);
        }

        return $this;
    }
    public function removeLocation($location)
    {
        $this->locations->removeElement($location);
    }

}