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
abstract class PartArrayFieldAbstract extends ArrayFieldAbstract {


    /**
     * Set part
     *
     * @param \Oleg\OrderformBundle\Entity\Part $part
     * @return PartArrayFieldAbstract
     */
    public function setPart(\Oleg\OrderformBundle\Entity\Part $part = null)
    {
        $this->part = $part;

        return $this;
    }

    /**
     * Get part
     *
     * @return \Oleg\OrderformBundle\Entity\Part
     */
    public function getPart()
    {
        return $this->part;
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

    public function __toString() {
        return $this->field."";
    }


    //set and get parent
    public function setParent($parent)
    {
        $this->setPart($parent);
        return $this;
    }
    public function getParent()
    {
        return $this->getPart();
    }

}