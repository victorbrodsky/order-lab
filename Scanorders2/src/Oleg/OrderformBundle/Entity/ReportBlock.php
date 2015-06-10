<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_reportBlock")
 */
class ReportBlock extends ReportBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="reportBlock")
     **/
    protected $message;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $embeddedDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $embeddedByUser;






    /**
     * @param mixed $embeddedByUser
     */
    public function setEmbeddedByUser($embeddedByUser)
    {
        $this->embeddedByUser = $embeddedByUser;
    }

    /**
     * @return mixed
     */
    public function getEmbeddedByUser()
    {
        return $this->embeddedByUser;
    }

    /**
     * @param mixed $embeddedDate
     */
    public function setEmbeddedDate($embeddedDate)
    {
        $this->embeddedDate = $embeddedDate;
    }

    /**
     * @return mixed
     */
    public function getEmbeddedDate()
    {
        return $this->embeddedDate;
    }

}