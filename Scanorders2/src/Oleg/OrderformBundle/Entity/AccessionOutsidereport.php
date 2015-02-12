<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_accessionOutsidereport")
 */
class AccessionOutsidereport extends AccessionArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Accession", inversedBy="outsidereport")
     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", nullable=true)
     */
    protected $accession;

    //Outside Report contains: Outside Report Order ID Source, Outside Report Order ID
    /**
     * @ORM\ManyToOne(targetEntity="OutsideReport")
     * @ORM\JoinColumn(name="outsidereport_id", referencedColumnName="id", nullable=true)
     */
    protected $outsidereport;



//    public function __construct( $status = 'valid', $provider = null, $source = null ) {
//        parent::__construct($status,$provider,$source);
//    }



    /**
     * @param mixed $outsidereport
     */
    public function setOutsidereport($outsidereport)
    {
        $this->outsidereport = $outsidereport;
    }

    /**
     * @return mixed
     */
    public function getOutsidereport()
    {
        return $this->outsidereport;
    }



}