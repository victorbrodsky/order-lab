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

    //Outside Report Source
    //$source field - already exists in base abstract class

    //Outside Report ID
    //$orderinfo - already exists in base abstract class

    //Outside Accession ID: [plain text]

    //Outside Accession ID: [plain text]

    //Outside Report Type: [select2, empty for now, add to List Manager]

    //Outside Report Text: [plain text multi-line field]

    //Outside Report Reference Representation (PDF): [upload of a PDF file] (actually a set of 5 fields - links to the Images table where Autopsy Images are)

    //Outside Report Pathologist(s): [Select2, can add new value, separate list in List Manager]

    //Outside Report Issued On (Date & Time):

    //Outside Report Received On (Date & TIme):

    //Attach "Progress & Comments" page to the Outside Report Order



    public function __construct( $status = 'valid', $provider = null, $source = null ) {
        parent::__construct($status,$provider,$source);


    }




}