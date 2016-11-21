<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_CCIPlateletTypeTransfusedList")
 */
class CCIPlateletTypeTransfusedList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="CCIPlateletTypeTransfusedList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="CCIPlateletTypeTransfusedList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


}