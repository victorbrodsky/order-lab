<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\DocumentContainer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedureOrder")
 */
class ProcedureOrder extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="procedureorder")
     **/
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="ProcedureList", cascade={"persist"})
     * @ORM\JoinColumn(name="procedurelist_id", referencedColumnName="id", nullable=true)
     */
    protected $type;




    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }




    public function __toString() {
        $res = "Procedure Order";
        if( $this->getId() ) {
            $res = $res . " with ID=" . $this->getId() . ", type=" . $this->getType();
        }
        return $res;
    }

}