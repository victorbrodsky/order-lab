<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_siteList")
 */
class SiteList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="SiteList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="SiteList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


//    /**
//     * @ORM\ManyToMany(targetEntity="Roles", mappedBy="sites")
//     **/
//    private $roles;




//    public function __construct() {
//        parent::__construct();
//        $this->roles = new ArrayCollection();
//    }




//    public function addRole(Roles $role)
//    {
//        if( !$this->roles->contains($role) ) {
//            $this->roles->add($role);
//        }
//    }
//    public function removeRole(Roles $role)
//    {
//        $this->roles->removeElement($role);
//    }
//    public function getRoles()
//    {
//        return $this->roles;
//    }

}