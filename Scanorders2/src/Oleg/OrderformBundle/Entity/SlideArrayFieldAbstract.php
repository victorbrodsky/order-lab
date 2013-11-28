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
abstract class SlideArrayFieldAbstract extends ArrayFieldAbstract {


//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $field;

    /**
     * Set slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $part
     * @return PartArrayFieldAbstract
     */
    public function setSlide(\Oleg\OrderformBundle\Entity\Slide $slide = null)
    {
        $this->slide = $slide;

        return $this;
    }

    /**
     * Get slide
     *
     * @return \Oleg\OrderformBundle\Entity\Slide
     */
    public function getSlide()
    {
        return $this->slide;
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

}