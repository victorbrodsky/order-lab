<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ScanRepository")
 * @ORM\Table(name="scan")
 */
class Scan {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    //Scan belongs to exactly one Slide => Scan has only one Slide, but Slide can have many Scans
    /**
     * @ORM\OneToOne(targetEntity="Slide", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="slide_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    //protected $slide;

    /**
     * @ORM\Column(name="mag", type="string", length=50)
     * @Assert\NotBlank
     */
    protected $mag;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=200)
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
     * @ORM\ManyToOne(targetEntity="OrderInfo", inversedBy="scan", cascade={"persist"})
     * @ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id", nullable=true)    
     */
    //protected $orderinfo;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mag
     *
     * @param string $mag
     * @return Scan
     */
    public function setMag($mag)
    {
        $this->mag = $mag;
    
        return $this;
    }

    /**
     * Get mag
     *
     * @return string 
     */
    public function getMag()
    {
        return $this->mag;
    }

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
     * Set slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return Scan
     */
//    public function setSlide(\Oleg\OrderformBundle\Entity\Slide $slide = null)
//    {
//        $this->slide = $slide;
//    
//        return $this;
//    }

    /**
     * Get slide
     *
     * @return \Oleg\OrderformBundle\Entity\Slide 
     */
//    public function getSlide()
//    {
//        return $this->slide;
//    }

    /**
     * Set orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Scan
     */
    public function setOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo = null)
    {
        $this->orderinfo = $orderinfo;
    
        return $this;
    }

    /**
     * Get orderinfo
     *
     * @return \Oleg\OrderformBundle\Entity\OrderInfo 
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
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
}