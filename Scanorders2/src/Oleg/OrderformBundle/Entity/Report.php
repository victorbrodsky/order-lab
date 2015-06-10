<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_report")
 */
class Report extends ReportBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="report")
     **/
    protected $message;


}