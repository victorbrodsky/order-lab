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

//#[Gedmo\Tree(type: 'nested')]
//#[ORM\Table(name: 'user_institution')]
//#[ORM\Index(name: 'institution_name_idx', columns: ['name'])]
//#[ORM\Entity(repositoryClass: 'App\UserdirectoryBundle\Repository\TreeRepository')]

//#[ORM\Table(name: 'user_hostedusergrouplist')]
//#[ORM\Entity]

class HostedUserGroupList extends ListAbstract
{

    //#[ORM\OneToMany(targetEntity: 'HostedUserGroupList', mappedBy: 'original')]
    protected $synonyms;

    //#[ORM\ManyToOne(targetEntity: 'HostedUserGroupList', inversedBy: 'synonyms')]
    //#[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id')]
    protected $original;


}