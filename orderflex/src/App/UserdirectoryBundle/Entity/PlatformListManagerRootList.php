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
 * @ORM\Entity
 * @ORM\Table(name="user_platformListManagerRootList",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="platformlistid_unique", columns={"linkToListId"}),
 *          @ORM\UniqueConstraint(name="platformlist_unique", columns={"linkToListId", "listName"})
 *     }
 * )
 */
class PlatformListManagerRootList extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="PlatformListManagerRootList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PlatformListManagerRootList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


//    /**
//     * @ORM\Column(type="string")
//     */
//    protected $listId;

    /**
     * Database Entity Name
     * @ORM\Column(type="string")
     */
    protected $listName;

    /**
     * @ORM\Column(type="string")
     */
    protected $listRootName;




//    /**
//     * @return mixed
//     */
//    public function getListId()
//    {
//        return $this->listId;
//    }
//
//    /**
//     * @param mixed $listId
//     */
//    public function setListId($listId)
//    {
//        $this->listId = $listId;
//    }

    /**
     * @return mixed
     */
    public function getListName()
    {
        return $this->listName;
    }

    /**
     * @param mixed $listName
     */
    public function setListName($listName)
    {
        $this->listName = $listName;
    }

    /**
     * @return mixed
     */
    public function getListRootName()
    {
        return $this->listRootName;
    }

    /**
     * @param mixed $listRootName
     */
    public function setListRootName($listRootName)
    {
        $this->listRootName = $listRootName;
    }




}