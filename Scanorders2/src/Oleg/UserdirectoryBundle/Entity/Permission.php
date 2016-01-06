<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 1/5/16
 * Time: 5:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_permission")
 */
class Permission
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="PermissionList")
     * @ORM\JoinColumn(name="permission", referencedColumnName="id", nullable=true)
     */
    private $permission;

    /**
     * If institution is not provided then this permission is for all institutions
     * @ORM\ManyToMany(targetEntity="Institution")
     * @ORM\JoinTable(name="user_permission_institution",
     *      joinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
     *      )
     */
    private $institutions;

    /**
     * @ORM\ManyToOne(targetEntity="Roles", inversedBy="permissions")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $role;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="permissions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;


    public function __construct() {
        $this->institutions = new ArrayCollection();
    }



    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

    /**
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getInstitutions()
    {
        return $this->institutions;
    }

    public function addInstitution(\Oleg\UserdirectoryBundle\Entity\Institution $institution)
    {
        if( $institution && !$this->institutions->contains($institution) ) {
            $this->institutions->add($institution);
        }
    }

    public function removeInstitution(\Oleg\UserdirectoryBundle\Entity\Institution $institution)
    {
        $this->institutions->removeElement($institution);
    }


}