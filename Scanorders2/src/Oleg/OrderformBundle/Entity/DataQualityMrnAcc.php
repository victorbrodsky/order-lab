<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_dataquality_mrnacc")
 */
class DataQualityMrnAcc extends DataQuality
{

    /**
     * conflicting accession number is replaced, so keep the reference to dataqualityaccmrn object in the scanorder (unlike to dataqualityage)
     * @ORM\ManyToOne(targetEntity="ScanOrder", inversedBy="dataqualitymrnacc")
     * @ORM\JoinColumn(name="scanorder", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $scanorder;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $accession;

    /**
     * @ORM\ManyToOne(targetEntity="AccessionType", cascade={"persist"})
     * @ORM\JoinColumn(name="accessiontype", referencedColumnName="id", nullable=true)
     */
    protected $accessiontype;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $newaccession;

    /**
     * @ORM\ManyToOne(targetEntity="AccessionType", cascade={"persist"})
     * @ORM\JoinColumn(name="newaccessiontype", referencedColumnName="id", nullable=true)
     */
    protected $newaccessiontype;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $mrn;

    /**
     * @ORM\ManyToOne(targetEntity="MrnType", cascade={"persist"})
     * @ORM\JoinColumn(name="mrntype", referencedColumnName="id", nullable=true)
     */
    protected $mrntype;





    /**
     * @param mixed $scanorder
     */
    public function setScanorder($scanorder)
    {
        $this->scanorder = $scanorder;
    }

    /**
     * @return mixed
     */
    public function getScanorder()
    {
        return $this->scanorder;
    }

    /**
     * @param mixed $accession
     */
    public function setAccession($accession)
    {
        $this->accession = $accession;
    }

    /**
     * @return mixed
     */
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * @param mixed $mrn
     */
    public function setMrn($mrn)
    {
        $this->mrn = $mrn;
    }

    /**
     * @return mixed
     */
    public function getMrn()
    {
        return $this->mrn;
    }

    /**
     * @param mixed $mrntype
     */
    public function setMrntype($mrntype)
    {
        $this->mrntype = $mrntype;
    }

    /**
     * @return mixed
     */
    public function getMrntype()
    {
        return $this->mrntype;
    }

    /**
     * @param mixed $newaccession
     */
    public function setNewaccession($newaccession)
    {
        $this->newaccession = $newaccession;
    }

    /**
     * @return mixed
     */
    public function getNewaccession()
    {
        return $this->newaccession;
    }

    /**
     * @param mixed $accessiontype
     */
    public function setAccessiontype($accessiontype)
    {
        $this->accessiontype = $accessiontype;
    }

    /**
     * @return mixed
     */
    public function getAccessiontype()
    {
        return $this->accessiontype;
    }

    /**
     * @param mixed $newaccessiontype
     */
    public function setNewaccessiontype($newaccessiontype)
    {
        $this->newaccessiontype = $newaccessiontype;
    }

    /**
     * @return mixed
     */
    public function getNewaccessiontype()
    {
        return $this->newaccessiontype;
    }



}