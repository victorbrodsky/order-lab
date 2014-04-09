<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="roles")
 */
class Roles extends ListAbstract {

    /**
     * Alias is a display name for each role, i.e.: ROLE_ADMIN => Administrator
     * @ORM\Column(type="string", nullable=true)
     */
    protected $alias;

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


}