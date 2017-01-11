<?php
/**
 * Created by PhpStorm.
 * User: DevServer
 * Date: 1/26/15
 * Time: 1:35 PM
 */

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_modifierInfo")
 */
class ModifierInfo {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $modifiedBy;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modifiedOn;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $modifierRoles = array();



    public function __construct( $modifiedBy=null ) {
        if( $modifiedBy ) {
            $this->setModifiedBy($modifiedBy);
            $this->setModifierRoles($modifiedBy->getRoles());
        }

        $this->setModifiedOn(new \DateTime());
    }

    public function setInfo($modifiedBy) {
        if( $modifiedBy ) {
            $this->setModifiedBy($modifiedBy);
            $this->setModifierRoles($modifiedBy->getRoles());
        }

        $this->setModifiedOn(new \DateTime());
    }


    public function getModifierRoles()
    {
        return $this->modifierRoles;
    }


    public function setModifierRoles($roles) {
        foreach( $roles as $role ) {
            $this->addModifierRole($role."");
        }
    }

    public function addModifierRole($role) {
        $role = strtoupper($role);
        if( !in_array($role, $this->modifierRoles, true) ) {
            $this->modifierRoles[] = $role;
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * @param mixed $modifiedBy
     */
    public function setModifiedBy($modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;
    }

    /**
     * @return \DateTime
     */
    public function getModifiedOn()
    {
        return $this->modifiedOn;
    }

    /**
     * @param \DateTime $modifiedOn
     */
    public function setModifiedOn($modifiedOn)
    {
        $this->modifiedOn = $modifiedOn;
    }




} 