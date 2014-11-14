<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//use disident (disease identify) as diagnosis, because diagnosis causes problem with symfony2&doctrine(?)
/**
 * @ORM\Entity
 * @ORM\Table(name="scan_partDisident")
 */
class PartDisident extends PartArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="disident", cascade={"persist"})
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $part;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $field;

}