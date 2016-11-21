<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_CCIUnitPlateletCountDefaultValueList")
 */
class CCIUnitPlateletCountDefaultValueList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="CCIUnitPlateletCountDefaultValueList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="CCIUnitPlateletCountDefaultValueList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


}