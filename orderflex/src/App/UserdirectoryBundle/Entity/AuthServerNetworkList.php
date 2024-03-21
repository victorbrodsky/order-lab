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

//Dual Authentication Server Network Accessibility and Role (aka Server Role and Network Access):
//[Intranet (Solo) / Intranet (Tandem) / Internet (Solo) / Internet (Tandem) / Internet (Hub)]

#[ORM\Table(name: 'user_authservernetworklist')]
#[ORM\Entity]
class AuthServerNetworkList extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'AuthServerNetworkList', mappedBy: 'original')]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'AuthServerNetworkList', inversedBy: 'synonyms')]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id')]
    protected $original;

    //Add ManyToMany hostedGroup holders,
    // each of this HostedGroupHolder has one HostedUserGroupList (nested tree) as url (c/wcm/pathology), server parameters, footer parameters, etc
    #[ORM\OneToMany(targetEntity: HostedGroupHolder::class, mappedBy: 'serverNetwork', cascade: ['persist', 'remove'])]
    private $hostedGroupHolders;


    public function __construct($author=null) {
        parent::__construct($author);
        $this->hostedGroupHolders = new ArrayCollection();
    }



    public function getHostedGroupHolders()
    {
        return $this->hostedGroupHolders;
    }
    public function addHostedGroupHolder( $item )
    {
        if( !$this->hostedGroupHolders->contains($item) ) {
            $this->hostedGroupHolders->add($item);
            $item->setServerNetwork($this);
        }

        return $this;
    }
    public function removeHostedGroupHolder($item)
    {
        if( $this->hostedGroupHolders->contains($item) ) {
            //$item->setServerNetwork(NULL);
            $this->hostedGroupHolders->removeElement($item);
        }

        return $this;
    }

}