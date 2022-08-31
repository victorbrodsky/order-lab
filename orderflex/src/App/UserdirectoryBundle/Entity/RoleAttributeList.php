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
 * @ORM\Table(name="user_roleAttributeList")
 */
class RoleAttributeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="RoleAttributeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="RoleAttributeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\Column(name="value", type="string")
     */
    private $value;

    /**
     * @ORM\ManyToMany(targetEntity="Roles", mappedBy="attributes")
     **/
    private $roles;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }


    

    public function addRole(Roles $role)
    {
        if( !$this->roles->contains($role) ) {
            $this->roles->add($role);
        }
    }
    public function removeRole(Roles $role)
    {
        $this->roles->removeElement($role);
    }
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString() {
        return $this->getName().":".$this->getValue();
    }





}