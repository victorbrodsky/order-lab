<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oleg\OrderformBundle\Entity\ArrayFieldAbstract;

/**
 * @ORM\MappedSuperclass
 */
abstract class ProcedureArrayFieldAbstract extends ArrayFieldAbstract {


//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $field;

    /**
     * Set Procedure
     *
     * @param \Oleg\OrderformBundle\Entity\Procedure $Procedure
     * @return ProcedureArrayFieldAbstract
     */
    public function setProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure = null)
    {
        $this->procedure = $procedure;

        return $this;
    }

    /**
     * Get procedure
     *
     * @return \Oleg\OrderformBundle\Entity\Procedure
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * @param mixed $field
     */
    public function setField($field=null)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    //set and get parent
    public function setParent($parent)
    {
        $this->setProcedure($parent);
        return $this;
    }
    public function getParent()
    {
        return $this->getProcedure();
    }

}