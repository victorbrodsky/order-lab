<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_stainlist",
 *  indexes={
 *      @ORM\Index( name="stain_name_idx", columns={"name"} )
 *  }
 * )
 */
class StainList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="StainList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="StainList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


}