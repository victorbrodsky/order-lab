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
 * User: ch3
 * Date: 4/11/2016
 * Time: 11:35 AM
 */

namespace Oleg\VacReqBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="vacreq_settings")
 */
class VacReqSettings
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;


    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinTable(name="vacreq_settings_user",
     *      joinColumns={@ORM\JoinColumn(name="settings_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="emailuser_id", referencedColumnName="id")}
     *      )
     **/
    private $emailUsers;




    public function __construct($institution) {
        $this->emailUsers = new ArrayCollection();
        $this->setInstitution($institution);
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
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }


    public function getEmailUsers()
    {
        return $this->emailUsers;
    }
    public function addEmailUser($item)
    {
        if( $item && !$this->emailUsers->contains($item) ) {
            $this->emailUsers->add($item);
        }
        return $this;
    }
    public function removeEmailUser($item)
    {
        $this->emailUsers->removeElement($item);
    }


    public function __toString()
    {
        return "VacReqSettings: institutionId=".$this->getId()." => count emailUsers=".count($this->getEmailUsers())."<br>";
    }
}