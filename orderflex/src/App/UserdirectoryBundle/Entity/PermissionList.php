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
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_permissionList")
 */
class PermissionList extends ListAbstract {

    /**
     * @ORM\OneToMany(targetEntity="PermissionList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PermissionList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    //Objects: Patient, Encounter, Procedure, Accession, Part, Block, Slide, Image, Image Analysis, Order, Report, Patient Attributes, Patient Birth Date, etc
    /**
     * @ORM\ManyToOne(targetEntity="PermissionObjectList")
     */
    private $permissionObjectList;

    //Actions: create, retrieve, update, delete
    /**
     * @ORM\ManyToOne(targetEntity="PermissionActionList")
     */
    private $permissionActionList;






    /**
     * @param mixed $permissionActionList
     */
    public function setPermissionActionList($permissionActionList)
    {
        $this->permissionActionList = $permissionActionList;
    }

    /**
     * @return mixed
     */
    public function getPermissionActionList()
    {
        return $this->permissionActionList;
    }

    /**
     * @param mixed $permissionObjectList
     */
    public function setPermissionObjectList($permissionObjectList)
    {
        $this->permissionObjectList = $permissionObjectList;
    }

    /**
     * @return mixed
     */
    public function getPermissionObjectList()
    {
        return $this->permissionObjectList;
    }

}