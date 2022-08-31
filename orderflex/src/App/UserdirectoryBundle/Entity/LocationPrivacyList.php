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
 * @ORM\Entity()
 * @ORM\Table(name="user_locationPrivacyList")
 */
class LocationPrivacyList extends ListAbstract
{


    /**
     * @ORM\OneToMany(targetEntity="LocationPrivacyList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LocationPrivacyList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="privacy")
     */
    protected $locations;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }


    


    public function addLocation(Location $location)
    {
        if( !$this->locations->contains($location) ) {
            $location->setPrivacy($this);
            $this->locations->add($location);
        }
    }
    public function removeLocation(Location $location)
    {
        $this->locations->removeElement($location);
    }
    public function getLocations()
    {
        return $this->locations;
    }


}