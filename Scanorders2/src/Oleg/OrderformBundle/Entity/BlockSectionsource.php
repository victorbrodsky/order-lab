<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_blockSectionsource",
 *  indexes={
 *      @ORM\Index( name="block_field_idx", columns={"field"} )
 *  }
 * )
 */
class BlockSectionsource extends BlockArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="sectionsource", cascade={"persist"})
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $block;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

}