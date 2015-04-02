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


    public function __construct() {
        $this->attributes = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
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



}