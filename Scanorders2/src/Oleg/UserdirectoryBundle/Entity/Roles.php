<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_roles")
 */
class Roles extends ListAbstract {

    /**
     * Alias is a display name for each role, i.e.: ROLE_SCANORDER_ADMIN => Administrator
     * @ORM\Column(type="string", nullable=true)
     */
    private $alias;

    /**
     * @ORM\ManyToMany(targetEntity="RoleAttributeList", inversedBy="roles", cascade={"persist"})
     * @ORM\JoinTable(name="user_roles_attributes")
     **/
    private $attributes;

    /**
     * @ORM\OneToMany(targetEntity="Roles", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Roles", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    //institution: currently used to check if the user can view fellowship application (FellAppUtil.php)
    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    private $institution;

    //fellowship type
    /**
     * @ORM\ManyToOne(targetEntity="FellowshipSubspecialty")
     */
    private $fellowshipSubspecialty;

    /**
     * Each single role page should show the whole associated list of the answered
     * permissions list items ("Submit Orders", "Add a New Slide", etc) and
     * the answers themselves for each (WCMC, NYP) in a Select2 box for each permission.
     *
     * @ORM\OneToMany(targetEntity="Permission", mappedBy="role", cascade={"persist","remove"})
     */
    private $permissions;

    /**
     * @ORM\ManyToMany(targetEntity="SiteList")
     * @ORM\JoinTable(name="user_roles_sites",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="site_id", referencedColumnName="id")}
     *      )
     **/
    private $sites;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
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
        $this->fellowshipSubspecialty = $fellowshipSubspecialty;
    }

    /**
     * @return mixed
     */
    public function getFellowshipSubspecialty()
    {
        return $this->fellowshipSubspecialty;
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
            if( $site->getName()."" == $sitename ) {
                return true;
            }
        }
        return false;
    }

}