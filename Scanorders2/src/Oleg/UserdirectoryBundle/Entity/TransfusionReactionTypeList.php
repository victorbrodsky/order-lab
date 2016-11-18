<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_transfusionReactionTypeList")
 */
class TransfusionReactionTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="TransfusionReactionTypeList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="TransfusionReactionTypeList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


}