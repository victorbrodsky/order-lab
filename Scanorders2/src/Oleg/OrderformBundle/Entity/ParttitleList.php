<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_parttitleList")
 */
class ParttitleList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ParttitleList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ParttitleList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="PartParttitle", mappedBy="field")
     */
    protected $part;



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->part = new ArrayCollection();
    }


    public function getPart()
    {
        return $this->part;
    }
    public function addPart($item)
    {
        if( $item && !$this->part->contains($item) ) {
            $this->part->add($item);
        }
        return $this;
    }
    public function removePart($item)
    {
        $this->part->removeElement($item);
    }
}