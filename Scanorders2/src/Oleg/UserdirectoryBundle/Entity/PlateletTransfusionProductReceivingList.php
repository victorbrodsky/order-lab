<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_plateletTransfusionProductReceivingList")
 */
class PlateletTransfusionProductReceivingList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="PlateletTransfusionProductReceivingList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PlateletTransfusionProductReceivingList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


}