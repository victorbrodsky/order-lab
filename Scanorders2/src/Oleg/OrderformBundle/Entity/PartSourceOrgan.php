<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\AccessionArrayFieldAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="partSourceOrgan")
 */
class PartSourceOrgan extends PartArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Part", inversedBy="sourceOrgan", cascade={"persist"})
     * @ORM\JoinColumn(name="part_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $part;

    /**
     * @ORM\ManyToOne(targetEntity="OrganList", inversedBy="part", cascade={"persist"})
     * @ORM\JoinColumn(name="organlist_id", referencedColumnName="id", nullable=true)
     */
    protected $field;

}