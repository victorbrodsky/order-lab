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
 * @ORM\Table(name="user_institutiontype")
 */
class InstitutionType extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="InstitutionType", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="InstitutionType", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="Institution", mappedBy="types")
     **/
    private $institutions;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->institutions = new ArrayCollection();
    }



    public function addInstitution($institution)
    {
        if( $institution && !$this->institutions->contains($institution) ) {
            $this->institutions->add($institution);
        }

        return $this;
    }
    public function removeInstitution($institution)
    {
        $this->institutions->removeElement($institution);
    }
    public function getInstitutions()
    {
        return $this->institutions;
    }

}