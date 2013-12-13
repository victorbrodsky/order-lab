<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//(repositoryClass="Oleg\OrderformBundle\Repository\ScanRepository")
/**
 * @ORM\Entity
 * @ORM\Table(name="scan")
 */
class Scan extends SlideArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Slide", inversedBy="scan")
     * @ORM\JoinColumn(name="slide_id", referencedColumnName="id")
     */
    protected $slide;

    /**
     * @ORM\Column(name="mag", type="string", length=50)
     */
    protected $field;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=500)
     */
    protected $scanregion;
    
    /**
     * Note/Reason for Scan
     * @ORM\Column(type="text", nullable=true, length=5000)    
     */
    protected $note;
    
    /**
     * status - status of the personal scan slide i.e. complete, in process, returned, canceled ...
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    protected $status;

    /**
     * date of scan performed
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $scandate;

    /**
     * Set scanregion
     *
     * @param string $scanregion
     * @return Scan
     */
    public function setScanregion($scanregion)
    {
        $this->scanregion = $scanregion;
    
        return $this;
    }

    /**
     * Get scanregion
     *
     * @return string 
     */
    public function getScanregion()
    {
        return $this->scanregion;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return Scan
     */
    public function setNote($note)
    {
        $this->note = $note;
    
        return $this;
    }

    /**
     * Get note
     *
     * @return string 
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Scan
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    public function setProvider($provider)
    {
        if( $provider ) {
            $this->provider = $provider;
        } else {
            $this->provider = $this->getSlide()->getProvider();
        }

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set scandate
     *
     * @param \DateTime $scandate
     * @return Scan
     */
    public function setScandate($scandate)
    {
        $this->scandate = $scandate;
    
        return $this;
    }

    /**
     * Get scandate
     *
     * @return \DateTime 
     */
    public function getScandate()
    {
        return $this->scandate;
    }

    public function __toString() {
        return $this->scanregion."";
    }

}