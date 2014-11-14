<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_boardCertifiedSpecialties")
 */
class BoardCertifiedSpecialties extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="BoardCertifiedSpecialties", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="BoardCertifiedSpecialties", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    

}