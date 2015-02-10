<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_encounterPatlastname")
 */
class EncounterPatlastname extends EncounterArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Encounter", inversedBy="patlastname", cascade={"persist"})
     * @ORM\JoinColumn(name="encounter_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $encounter;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $alias;





    /**
     * @param mixed $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
//        if( $alias ) {
//            $this->setStatus('alias');
//        }
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }



}