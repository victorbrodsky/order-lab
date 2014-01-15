<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\AccessionArrayFieldAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="accessionaccession")
 */
class AccessionAccession extends AccessionArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="accession", cascade={"persist"})
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $accession;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $field;

    /**
     * original accession # enetered by user
     * @ORM\Column(type="string", nullable=true)
     */
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="AccessionType", inversedBy="accessionaccession", cascade={"persist"})
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
        $extra = array();
        $extra['keytype'] = $this->getKeytype()->getId();
        return $extra;
    }

    public function setExtra($extraEntity)
    {
        $this->setKeytype($extraEntity);
    }

}