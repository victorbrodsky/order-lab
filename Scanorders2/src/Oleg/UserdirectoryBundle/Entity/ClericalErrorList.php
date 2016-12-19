<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_clericalErrorList")
 */
class ClericalErrorList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ClericalErrorList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ClericalErrorList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


}