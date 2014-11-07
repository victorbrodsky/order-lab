<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_dataquality_event")
 */
class DataQualityEvent extends DataQuality
{

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $roles = array();


    /**
     * @ORM\ManyToOne(targetEntity="DataQualityEventLog", inversedBy="dqevents")
     * @ORM\JoinColumn(name="dqeventlog", referencedColumnName="id")
     **/
    protected $dqeventlog;




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

    /**
     * @param mixed $dqeventlog
     */
    public function setDqeventlog($dqeventlog)
    {
        $this->dqeventlog = $dqeventlog;
    }

    /**
     * @return mixed
     */
    public function getDqeventlog()
    {
        return $this->dqeventlog;
    }





}