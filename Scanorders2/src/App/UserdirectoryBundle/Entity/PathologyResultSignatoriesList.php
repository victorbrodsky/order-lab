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
 * @ORM\Table(name="user_pathologyResultSignatoriesList")
 */
class PathologyResultSignatoriesList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PathologyResultSignatoriesList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PathologyResultSignatoriesList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    //user ID set by setObject($user)

//    /**
//     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
//     * @ORM\JoinTable(name="scan_pathologyResultSignatory_userWrapper",
//     *      joinColumns={@ORM\JoinColumn(name="pathologyResultSignatory_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="userWrapper_id", referencedColumnName="id")}
//     *      )
//     **/
//    private $userWrappers;
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper",cascade={"persist","remove"})
     */
    private $userWrapper;



    public function __construct($creator=null) {
        parent::__construct($creator);

//        $this->userWrappers = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getUserWrapper()
    {
        return $this->userWrapper;
    }

    /**
     * @param mixed $userWrapper
     */
    public function setUserWrapper($userWrapper)
    {
        $this->userWrapper = $userWrapper;
    }



//    public function getUserWrappers()
//    {
//        return $this->userWrappers;
//    }
//    public function addUserWrapper($item)
//    {
//        if( $item && !$this->userWrappers->contains($item) ) {
//            $this->userWrappers->add($item);
//        }
//        return $this;
//    }
//    public function removeUserWrapper($item)
//    {
//        $this->userWrappers->removeElement($item);
//    }

//    public function addUserWrapperAsUser($user)
//    {
//        if( $user ) {
//            $userWrapper = new UserWrapper();
//            $userWrapper->setUser($user);
//            $this->addUserWrapper($userWrapper);
//        }
//        return $this;
//    }
//    public function getUserWrapperAsUser()
//    {
//        $userWrapper = null;
//        $userWrapper = $this->getUserWrappers()->first();
//        if( $userWrapper ) {
//            $userWrapper = $userWrapper->getUser();
//        }
//        return $userWrapper;
//    }


}