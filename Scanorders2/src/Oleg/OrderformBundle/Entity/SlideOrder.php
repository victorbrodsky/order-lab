<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_slideOrder")
 */
class SlideOrder extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="slideorder")
     **/
    protected $message;



}