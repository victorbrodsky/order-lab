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
abstract class AccessionArrayFieldAbstract extends ArrayFieldAbstract {


//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $field;

    /**
     * Set accession
     *
     * @param \Oleg\OrderformBundle\Entity\Accession $accession
     * @return AccessionArrayFieldAbstract
     */
    public function setAccession(\Oleg\OrderformBundle\Entity\Accession $accession = null)
    {
        $this->accession = $accession;

        return $this;
    }

    /**
     * Get accession
     *
     * @return \Oleg\OrderformBundle\Entity\Accession
     */
    public function getAccession()
    {
        return $this->accession;
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

}