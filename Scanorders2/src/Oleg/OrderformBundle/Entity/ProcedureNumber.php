<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedureNumber")
 */
class ProcedureNumber extends ProcedureArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="number", cascade={"persist"})
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $procedure;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * original procedure # enetered by user
     * @ORM\Column(type="string", nullable=true)
     */
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="ProcedureType", inversedBy="procedurenumber", cascade={"persist"})
     * @ORM\JoinColumn(name="keytype_id", referencedColumnName="id", nullable=true)
     */
    protected $keytype;


    /**
     * @param mixed $keytype
     */
    public function setKeytype($keytype)
    {
        $this->keytype = $keytype;
    }

    /**
     * @return mixed
     */
    public function getKeytype()
    {
        return $this->keytype;
    }

    /**
     * @param mixed $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

    public function obtainExtraKey()
    {
//        $extra = array();
//
//        if( !$this->getKeytype() ) {
//            $keytypeid = '';
//        } else {
//            $keytypeid = $this->getKeytype()->getId();
//        }
//
//        $extra['keytype'] = $keytypeid;
//        return $extra;

        $extra = array();
        $extra['keytype'] = $this->getKeytype()->getId();
        return $extra;

    }

    public function setExtra($extraEntity)
    {
        $this->setKeytype($extraEntity);
    }

}