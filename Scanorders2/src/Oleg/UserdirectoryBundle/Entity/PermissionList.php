<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_permissionList")
 */
class PermissionList extends ListAbstract {

    /**
     * @ORM\OneToMany(targetEntity="PermissionList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PermissionList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    //Objects: Patient, Encounter, Procedure, Accession, Part, Block, Slide, Image, Image Analysis, Order, Report, Patient Attributes, Patient Birth Date, etc
    /**
     * @ORM\ManyToOne(targetEntity="PermissionObjectList")
     */
    private $permissionObjectList;

    //Actions: create, retrieve, update, delete
    /**
     * @ORM\ManyToOne(targetEntity="PermissionActionList")
     */
    private $permissionActionList;






    /**
     * @param mixed $permissionActionList
     */
    public function setPermissionActionList($permissionActionList)
    {
        $this->permissionActionList = $permissionActionList;
    }

    /**
     * @return mixed
     */
    public function getPermissionActionList()
    {
        return $this->permissionActionList;
    }

    /**
     * @param mixed $permissionObjectList
     */
    public function setPermissionObjectList($permissionObjectList)
    {
        $this->permissionObjectList = $permissionObjectList;
    }

    /**
     * @return mixed
     */
    public function getPermissionObjectList()
    {
        return $this->permissionObjectList;
    }

}