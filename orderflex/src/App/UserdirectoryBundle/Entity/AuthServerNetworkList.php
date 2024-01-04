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

    //hostedUserGroup is the Tenant ID (i.e. 'c/wcm/pathology' or 'c/lmh/pathology')
    //HostedUserGroupList attach here. Different user's groups can have different tenant ids
//    #[ORM\JoinTable(name: 'user_servernetwork_hostedusergroup')]
//    #[ORM\JoinColumn(name: 'servernetwork_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
//    #[ORM\InverseJoinColumn(name: 'hostedusergroup_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
//    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\HostedUserGroupList', cascade: ['persist', 'remove'])]
//    #[ORM\OrderBy(['createdate' => 'DESC'])]
//    private $hostedUserGroups;
//    #[ORM\JoinTable(name: 'user_servernetwork_hostedusergroup')]
//    #[ORM\ManyToMany(targetEntity: HostedUserGroupList::class, inversedBy: 'serverNetworks', cascade: ['persist'])]
//    private $hostedUserGroups;

    //Alternative implementation:
    //Add ManyToMany hostedGroup holders,
    // each of this holder has many HostedUserGroupList (nested tree), server parameters, footer parameters, etc
    #[ORM\OneToMany(targetEntity: HostedGroupHolder::class, mappedBy: 'serverNetwork', cascade: ['persist', 'remove'])]
    private $hostedGroupHolders;

    //MOVED custom page parameters to HostedUserGroupList (Tenant ID)
    //Homepage and About Us Page Content
    //For example, if Server Role and Network Access field is set to "Internet (Hub)", the home page will look different
    //the the home page for Internet (Solo)


    public function __construct($author=null) {

        parent::__construct($author);

        //$this->hostedUserGroups = new ArrayCollection();
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
            $this->hostedGroupHolders->removeElement($item);
            //$item->setServerNetwork(NULL);
        }

        return $this;
    }

//    public function getHostedUserGroups()
//    {
//        return $this->hostedUserGroups;
//    }
//    public function addHostedUserGroup( $item )
//    {
//        if( !$this->hostedUserGroups->contains($item) ) {
//            $this->hostedUserGroups->add($item);
//        }
//
//        return $this;
//    }
//    public function removeHostedUserGroup($item)
//    {
//        if( $this->hostedUserGroups->contains($item) ) {
//            $this->hostedUserGroups->removeElement($item);
//        }
//
//        return $this;
//    }

}