<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_transfusionAntibodyScreenResultsList")
 */
class TransfusionAntibodyScreenResultsList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="TransfusionAntibodyScreenResultsList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="TransfusionAntibodyScreenResultsList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


}