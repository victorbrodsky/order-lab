<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_partDiffDisident",
 *  indexes={
 *      @ORM\Index( name="partdiffdisident_field_idx", columns={"field"} )
 *  }
 * )
 */
class PartDiffDisident extends PartArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="diffDisident")
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $part;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;
}