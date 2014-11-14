<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_roleAttributeList")
 */
class RoleAttributeList extends ListAbstract
{

    /**
     * @ORM\Column(name="value", type="string")
     */
    protected $value;

//    /**
//     * @ORM\ManyToOne(targetEntity="Roles", inversedBy="attributes")
//     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
//     **/
//    protected $role;
    /**
     * @ORM\ManyToMany(targetEntity="Roles", mappedBy="attributes")
     **/
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity="RoleAttributeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="RoleAttributeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }


    

    public function addRole(Roles $role)
    {
        if( !$this->roles->contains($role) ) {
            $this->roles->add($role);
        }
    }
    public function removeRole(Roles $role)
    {
        $this->roles->removeElement($role);
    }
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString() {
        return $this->getName().":".$this->getValue();
    }





}