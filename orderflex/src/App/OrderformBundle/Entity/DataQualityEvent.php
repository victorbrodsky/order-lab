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

#[ORM\Table(name: 'scan_dataquality_event')]
#[ORM\Entity]
class DataQualityEvent extends DataQuality
{

    #[ORM\Column(name: 'roles', type: 'json', nullable: true)]
    protected ?array $roles = [];


    #[ORM\ManyToOne(targetEntity: 'DataQualityEventLog', inversedBy: 'dqevents')]
    #[ORM\JoinColumn(name: 'dqeventlog', referencedColumnName: 'id')]
    protected $dqeventlog;




    public function setRoles(array $roles): self
    {
        $this->roles = [];
        foreach ($roles as $role) {
            $this->addRole($role);
        }
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles ?? [];
    }

    public function addRole(string $role): self
    {
        if (null === $this->roles) {
            $this->roles = [];
        }
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
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