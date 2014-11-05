<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_dataquality_eventlog")
 */
class DataQualityEventLog extends DataQuality
{

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $roles = array();




    public function setRoles($roles) {
        foreach( $roles as $role ) {
            $this->addRole($role."");
        }
    }

    public function getRoles() {
        return $this->roles;
    }

    public function addRole($role) {
        $this->roles[] = $role;
    }



}