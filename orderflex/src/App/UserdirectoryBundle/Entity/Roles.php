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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'user_roles')]
#[ORM\Entity]
class Roles extends ListAbstract {

    /**
     * Alias is a display name for each role, i.e.: ROLE_SCANORDER_ADMIN => Administrator
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $alias;

    #[ORM\JoinTable(name: 'user_roles_attributes')]
    #[ORM\ManyToMany(targetEntity: 'RoleAttributeList', inversedBy: 'roles', cascade: ['persist'])]
    private $attributes;

    #[ORM\OneToMany(targetEntity: 'Roles', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'Roles', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;


    //institution: currently used to check if the user can view fellowship application (FellAppImportPopulateUtil.php)
    #[ORM\ManyToOne(targetEntity: 'Institution')]
    private $institution;

    //fellowship type
    #[ORM\ManyToOne(targetEntity: 'FellowshipSubspecialty')]
    private $fellowshipSubspecialty;

    /**
     * Keep "Subspecialty" to be compatible with fellapp source code
     */
    #[ORM\ManyToOne(targetEntity: 'ResidencySpecialty')]
    private $residencySubspecialty;

    /**
     * Residency Type (ResidencyTrackList)
     */
    #[ORM\ManyToOne(targetEntity: 'ResidencyTrackList')]
    private $residencyTrack;

    /**
     * Each single role page should show the whole associated list of the answered
     * permissions list items ("Submit Orders", "Add a New Slide", etc) and
     * the answers themselves for each (WCMC, NYP) in a Select2 box for each permission.
     */
    #[ORM\OneToMany(targetEntity: 'Permission', mappedBy: 'role', cascade: ['persist', 'remove'])]
    private $permissions;

    //@ORM\ManyToMany(targetEntity="SiteList", inversedBy="roles", cascade={"persist"})
    #[ORM\JoinTable(name: 'user_roles_sites')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'site_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'SiteList', cascade: ['persist'])]
    private $sites;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $level;


    public function __construct() {
        $this->attributes = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->sites = new ArrayCollection();
    }

    /**
     * @param mixed $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }


    public function addAttribute(RoleAttributeList $attribute)
    {
        if( !$this->attributes->contains($attribute) ) {
            //$attribute->setRole($this);
            $this->attributes->add($attribute);
        }
    }
    public function removeAttribute(RoleAttributeList $attribute)
    {
        $this->attributes->removeElement($attribute);
    }
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }


    /**
     * @param mixed $fellowshipSubspecialty
     */
    public function setFellowshipSubspecialty($fellowshipSubspecialty)
    {
        if( $fellowshipSubspecialty instanceof FellowshipSubspecialty ) {
            $this->fellowshipSubspecialty = $fellowshipSubspecialty;
        }
    }

    /**
     * @return mixed
     */
    public function getFellowshipSubspecialty()
    {
        return $this->fellowshipSubspecialty;
    }

    /**
     * @return mixed
     */
    public function getResidencySubspecialty()
    {
        return $this->residencySubspecialty;
    }

    /**
     * @param mixed $residencySubspecialty
     */
    public function setResidencySubspecialty($residencySubspecialty)
    {
        $this->residencySubspecialty = $residencySubspecialty;
    }

    /**
     * @return mixed
     */
    public function getResidencyTrack()
    {
        return $this->residencyTrack;
    }

    /**
     * @param mixed $residencyTrack
     */
    public function setResidencyTrack($residencyTrack)
    {
        $this->residencyTrack = $residencyTrack;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }
    public function addPermission($item)
    {
        if( $item && !$this->permissions->contains($item) ) {
            $this->permissions->add($item);
            $item->setRole($this);
        }
    }
    public function removePermission($item)
    {
        $this->permissions->removeElement($item);
    }


    public function getSites()
    {
        return $this->sites;
    }
    public function addSite($item)
    {
        if( $item && !$this->sites->contains($item) ) {
            $this->sites->add($item);
        }
    }
    public function removeSite($item)
    {
        $this->sites->removeElement($item);
    }

    public function hasSite( $sitename ) {
        foreach( $this->getSites() as $site ) {
            if( $site->getName()."" == $sitename || $site->getAbbreviation()."" == $sitename ) {
                return true;
            }
        }
        return false;
    }

}