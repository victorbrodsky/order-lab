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

//TenantUrlList is the Tenant ID (i.e. 'c/wcm/pathology' or 'c/lmh/pathology')
//Attach to: TenantList

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

#[Gedmo\Tree(type: 'nested')]
#[ORM\Table(name: 'user_tenanturllist')]
#[ORM\Index(name: 'tenanturl_name_idx', columns: ['name'])]
#[ORM\Entity(repositoryClass: 'App\UserdirectoryBundle\Repository\TreeRepository')]
class TenantUrlList extends BaseCompositeNode
{

    #[ORM\OneToMany(targetEntity: 'TenantUrlList', mappedBy: 'original')]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'TenantUrlList', inversedBy: 'synonyms')]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id')]
    protected $original;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: 'TenantUrlList', inversedBy: 'children', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    protected $parent;

    #[ORM\OneToMany(targetEntity: 'TenantUrlList', mappedBy: 'parent', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected $children;

    /**
     * Organizational Group Types
     * level int in OrganizationalGroupType corresponds to this level integer: 1-Comment Category, 2-Comment Name
     * For example, OrganizationalGroupType with level=1, set this level to 1.
     */
    //#[ORM\ManyToOne(targetEntity: 'CommentGroupType', cascade: ['persist'])]
    //private $organizationalGroupType;

    #[ORM\OneToMany(targetEntity: TenantList::class, mappedBy: 'tenantUrl')]
    private $tenants;


    public function __construct($author=null) {
        parent::__construct($author);

        $this->tenants = new ArrayCollection();
    }


    public function getTenants()
    {
        return $this->tenants;
    }
    public function addTenant( $item )
    {
        if( !$this->tenants->contains($item) ) {
            $this->tenants->add($item);
        }

        return $this;
    }
    public function removeTenant($item)
    {
        if( $this->tenants->contains($item) ) {
            $this->tenants->removeElement($item);
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
        return "TenantUrl";
        //return "hostedusergroup";
    }

}