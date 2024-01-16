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

//hostedUserGroup is the Tenant ID (i.e. 'c/wcm/pathology' or 'c/lmh/pathology')
//Attach to: AuthServerNetworkList or SiteParameters?
//IF "Server Role and Network Access:" = "Internet (Hub)” serve ORDER home page from "/c/wcm/pathology"
//How to redirect:
// 1) Route Aliasing
// 2) Route Groups and Prefixes https://symfony.com/doc/current/routing.html#route-groups-and-prefixes
// 3) Custom routing

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

#[Gedmo\Tree(type: 'nested')]
#[ORM\Table(name: 'user_hostedusergrouplist')]
#[ORM\Index(name: 'hostedusergroup_name_idx', columns: ['name'])]
#[ORM\Entity(repositoryClass: 'App\UserdirectoryBundle\Repository\TreeRepository')]
class HostedUserGroupList extends BaseCompositeNode
{

    #[ORM\OneToMany(targetEntity: 'HostedUserGroupList', mappedBy: 'original')]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'HostedUserGroupList', inversedBy: 'synonyms')]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id')]
    protected $original;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: 'HostedUserGroupList', inversedBy: 'children', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    protected $parent;

    #[ORM\OneToMany(targetEntity: 'HostedUserGroupList', mappedBy: 'parent', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected $children;

    /**
     * Organizational Group Types
     * level int in OrganizationalGroupType corresponds to this level integer: 1-Comment Category, 2-Comment Name
     * For example, OrganizationalGroupType with level=1, set this level to 1.
     */
    //#[ORM\ManyToOne(targetEntity: 'CommentGroupType', cascade: ['persist'])]
    //private $organizationalGroupType;

//    #[ORM\ManyToMany(targetEntity: AuthServerNetworkList::class, mappedBy: 'hostedUserGroups', cascade: ['persist'])]
//    private $serverNetworks;

    #[ORM\JoinTable(name: 'user_hostedusergroup_hostedgroupholder')]
    #[ORM\ManyToMany(targetEntity: HostedGroupHolder::class, inversedBy: 'hostedUserGroups')]
    private $hostedGroupHolders;

    




    public function __construct($author=null) {
        parent::__construct($author);

        //$this->serverNetworks = new ArrayCollection();
        $this->hostedGroupHolders = new ArrayCollection();
    }


//    /**
//     * @param mixed $organizationalGroupType
//     */
//    public function setOrganizationalGroupType($organizationalGroupType)
//    {
//        $this->organizationalGroupType = $organizationalGroupType;
//        $this->setLevel($organizationalGroupType->getLevel());
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getOrganizationalGroupType()
//    {
//        return $this->organizationalGroupType;
//    }

//    /**
//     * Overwrite base setParent method: adjust this organizationalGroupType according to the first parent child
//     * @param mixed $parent
//     */
//    public function setParent(CompositeNodeInterface $parent = null)
//    {
//        $this->parent = $parent;
//
//        //change organizationalGroupType of this entity to the first child organizationalGroupType of the parent
//        if( $parent && count($parent->getChildren()) > 0 ) {
//            $firstSiblingOrgGroupType = $parent->getChildren()->first()->getOrganizationalGroupType();
//            $this->setOrganizationalGroupType($firstSiblingOrgGroupType);
//        }
//    }

//    public function getServerNetworks()
//    {
//        return $this->serverNetworks;
//    }
//    public function addServerNetwork( $item )
//    {
//        if( !$this->serverNetworks->contains($item) ) {
//            $this->serverNetworks->add($item);
//            $item->addHostedUserGroup($this);
//        }
//
//        return $this;
//    }
//    public function removeServerNetwork($item)
//    {
//        if( $this->serverNetworks->contains($item) ) {
//            $this->serverNetworks->removeElement($item);
//            $item->removeHostedUserGroup($this);
//        }
//
//        return $this;
//    }

    public function getHostedGroupHolders()
    {
        return $this->hostedGroupHolders;
    }
    public function addHostedGroupHolder( $item )
    {
        if( !$this->hostedGroupHolders->contains($item) ) {
            $this->hostedGroupHolders->add($item);
        }

        return $this;
    }
    public function removeHostedGroupHolder($item)
    {
        if( $this->hostedGroupHolders->contains($item) ) {
            $this->hostedGroupHolders->removeElement($item);
        }

        return $this;
    }


    public function __toString()
    {
        if( $this->getAbbreviation() && $this->getAbbreviation() != "" ) {
            return $this->getAbbreviation()."";
        }

        return $this->getName()."";
    }

    //is used to construct parent's show path the same as in ListController.php
    public function getClassName()
    {
        return "HostedUserGroup";
        //return "hostedusergroup";
    }

}