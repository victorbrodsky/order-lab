<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity()
 * @ORM\Table(name="user_eventObjectTypeList")
 */
class EventObjectTypeList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="EventObjectTypeList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="EventObjectTypeList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

}