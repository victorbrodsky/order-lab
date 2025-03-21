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

//HostedUserGroupList is the Tenant ID (i.e. 'c/wcm/pathology' or 'c/lmh/pathology')
//Attach to: HostedGroupHolder

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

    #[ORM\OneToMany(targetEntity: HostedGroupHolder::class, mappedBy: 'hostedUserGroup')]
    private $hostedGroupHolders;


    public function __construct($author=null) {
        parent::__construct($author);

        //$this->serverNetworks = new ArrayCollection();
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

    public function getTenantUrl() {
        return $this->getTreeAbbreviation("/");
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