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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/5/16
 * Time: 5:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Table(name: 'user_permission')]
#[ORM\Entity]
class Permission
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'PermissionList')]
    #[ORM\JoinColumn(name: 'permission', referencedColumnName: 'id', nullable: true)]
    private $permission;

    /**
     * If institution is not provided then this permission is for all institutions
     */
    #[ORM\JoinTable(name: 'user_permission_institution')]
    #[ORM\JoinColumn(name: 'permission_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'institution_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'Institution')]
    private $institutions;

    #[ORM\ManyToOne(targetEntity: 'Roles', inversedBy: 'permissions')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $role;

    #[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'permissions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $user;


    public function __construct() {
        $this->institutions = new ArrayCollection();
        //$this->permissions = new ArrayCollection();
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }
    /**
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }
//    public function addPermission($item)
//    {
//        if( $item && !$this->permissions->contains($item) ) {
//            $this->permissions->add($item);
//        }
//    }
//    public function removePermission($item)
//    {
//        $this->permissions->removeElement($item);
//    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
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
     * @return mixed
     */
    public function getInstitutions()
    {
        return $this->institutions;
    }

    public function addInstitution(\App\UserdirectoryBundle\Entity\Institution $institution)
    {
        if( $institution && !$this->institutions->contains($institution) ) {
            $this->institutions->add($institution);
        }
    }

    public function removeInstitution(\App\UserdirectoryBundle\Entity\Institution $institution)
    {
        $this->institutions->removeElement($institution);
    }


    public function __toString() {
        $res = "Permission ID " . $this->getId();

        if( $this->getPermission() ) {
            $res .= ", PermissionList: ID=".$this->getPermission()->getId().", name='" . $this->getPermission()->getName() . "'";
        }

        if( count($this->getInstitutions()) > 0 ) {
            $res .= "; Institutions: ";
            foreach( $this->getInstitutions() as $inst ) {
                $res .= $inst." ";
            }
        }

        return $res;
    }
}