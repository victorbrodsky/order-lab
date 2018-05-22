<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="formtype")
 */
class FormType extends ListAbstract
{

    /**
     * Constructor
     */
//    public function __construct()
//    {
//        $this->orderinfo = new \Doctrine\Common\Collections\ArrayCollection();
//    }

}