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
 * Date: 9/22/15
 * Time: 12:34 PM
 */

namespace App\FellAppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="fellapp_rank")
 */
class Rank {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\OneToOne(targetEntity="FellowshipApplication", mappedBy="rank")
     */
    private $fellapp;


    /**
     * @ORM\Column(name="rank", type="integer", nullable=true)
     */
    private $rank;


    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $userroles = array();

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $creationdate;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updateuser_id", referencedColumnName="id", nullable=true)
     */
    private $updateuser;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $updateuserroles = array();

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedate;







    public function __construct() {
        $this->creationdate = new \DateTime();

    }



    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getFellapp()
    {
        return $this->fellapp;
    }

    /**
     * @param mixed $fellapp
     */
    public function setFellapp($fellapp)
    {
        $this->fellapp = $fellapp;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getUserroles()
    {
        return $this->userroles;
    }
    /**
     * @param array $userroles
     */
    public function setUserroles($userroles)
    {
        if( $userroles ) {
            foreach( $userroles as $role ) {
                $this->addUserrole($role."");
            }
        }
    }
    public function addUserrole($role) {
        $this->userroles[] = $role;
        return $this;
    }

    /**
     * @return array
     */
    public function getUpdateuserroles()
    {
        return $this->updateuserroles;
    }
    /**
     * @param array $updateuserroles
     */
    public function setUpdateuserroles($updateuserroles)
    {
        if( $updateuserroles ) {
            foreach( $updateuserroles as $role ) {
                $this->addUpdateuserrole($role."");
            }
        }
    }
    public function addUpdateuserrole($role) {
        $this->updateuserroles[] = $role;
        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * @param \DateTime $creationdate
     */
    public function setCreationdate($creationdate)
    {
        $this->creationdate = $creationdate;
    }

    /**
     * @return mixed
     */
    public function getUpdateuser()
    {
        return $this->updateuser;
    }

    /**
     * @param mixed $updateuser
     */
    public function setUpdateuser($updateuser)
    {
        $this->updateuser = $updateuser;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    /**
     * @param \DateTime $updatedate
     * @ORM\PreUpdate
     */
    public function setUpdatedate()
    {
        $this->updatedate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }



} 