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

/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 1/26/15
 * Time: 1:35 PM
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_modifierInfo")
 */
class ModifierInfo {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $modifiedBy;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modifiedOn;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $modifierRoles = array();



    public function __construct( $modifiedBy=null ) {
        if( $modifiedBy ) {
            $this->setModifiedBy($modifiedBy);
            $this->setModifierRoles($modifiedBy->getRoles());
        }

        $this->setModifiedOn(new \DateTime());
    }

    public function setInfo($modifiedBy) {
        if( $modifiedBy ) {
            $this->setModifiedBy($modifiedBy);
            $this->setModifierRoles($modifiedBy->getRoles());
        }

        $this->setModifiedOn(new \DateTime());
    }


    public function getModifierRoles()
    {
        return $this->modifierRoles;
    }


    public function setModifierRoles($roles) {
        foreach( $roles as $role ) {
            $this->addModifierRole($role."");
        }
    }

    public function addModifierRole($role) {
        $role = strtoupper($role);
        if( !in_array($role, $this->modifierRoles, true) ) {
            $this->modifierRoles[] = $role;
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * @param mixed $modifiedBy
     */
    public function setModifiedBy($modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * @return \DateTime
     */
    public function getModifiedOn()
    {
        return $this->modifiedOn;
    }

    /**
     * @param \DateTime $modifiedOn
     */
    public function setModifiedOn($modifiedOn)
    {
        $this->modifiedOn = $modifiedOn;
    }




} 