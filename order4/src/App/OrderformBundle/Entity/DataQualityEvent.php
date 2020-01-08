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

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_dataquality_event")
 */
class DataQualityEvent extends DataQuality
{

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $roles = array();


    /**
     * @ORM\ManyToOne(targetEntity="DataQualityEventLog", inversedBy="dqevents")
     * @ORM\JoinColumn(name="dqeventlog", referencedColumnName="id")
     **/
    protected $dqeventlog;




    public function setRoles($roles) {
        foreach( $roles as $role ) {
            $this->addRole($role."");
        }
    }

    public function getRoles() {
        return $this->roles;
    }

    public function addRole($role) {
        $this->roles[] = $role;
    }

    /**
     * @param mixed $dqeventlog
     */
    public function setDqeventlog($dqeventlog)
    {
        $this->dqeventlog = $dqeventlog;
    }

    /**
     * @return mixed
     */
    public function getDqeventlog()
    {
        return $this->dqeventlog;
    }





}