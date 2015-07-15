<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_partParttitle")
 */
class PartParttitle extends PartArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="parttitle", cascade={"persist"})
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $part;

    /**
     * @ORM\ManyToOne(targetEntity="ParttitleList", inversedBy="part", cascade={"persist"})
     * @ORM\JoinColumn(name="parttitlelist_id", referencedColumnName="id", nullable=true)
     */
    protected $field;


}