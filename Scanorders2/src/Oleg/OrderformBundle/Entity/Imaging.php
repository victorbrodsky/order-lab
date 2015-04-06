<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//(repositoryClass="Oleg\OrderformBundle\Repository\ImagingRepository")
/**
 * Slide Imaging: Scan, Microscopic Image, Whole Slide Image
 *
 * @ORM\Entity
 * @ORM\Table(name="scan_imaging")
 */
class Imaging extends SlideArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Slide", inversedBy="scan")
     * @ORM\JoinColumn(name="slide", referencedColumnName="id")
     */
    protected $slide;

    //TODO: convert to MagList?
    /**
     * @ORM\Column(name="mag", type="string", nullable=true)
     */
    protected $field;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $scanregion;
    
    /**
     * Note/Reason for Scan
     * @ORM\Column(type="text", nullable=true)    
     */
    protected $note;
    

    /**
     * date of scan performed
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $scandate;


    //Extra fields for datastructure: Whole Slide Image
    //Fields existing in this abstract object: Whole Slide Scan Date & Time, Whole Slide Scanned By, Whole Slide Image Magnification
    //Fields existing in message: Scan Order Source, Order ID, Whole Slide Scanner Device
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $imageId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $imageLink;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Equipment")
     */
    private $equipment;

    //Microscopic Image
    /**
     * Microscopic Image
     * device: Microscopic Image Device: [select2, one choice for now - "Olympus Camera" - link to Equipment table, filter by Type="Microscope Camera"]
     * @ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\DocumentContainer", cascade={"persist","remove"})
     **/
    private $documentContainer;


    //Microscopic Image Magnification: [select2, 100X, 83X, 60X, 40X, 20X, 10X, 4X, 2X]
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $imageMagnification;




    /**
     * @param mixed $documentContainer
     */
    public function setDocumentContainer($documentContainer)
    {
        $this->documentContainer = $documentContainer;
    }

    /**
     * @return mixed
     */
    public function getDocumentContainer()
    {
        return $this->documentContainer;
    }

    /**
     * @param mixed $equipment
     */
    public function setEquipment($equipment)
    {
        $this->equipment = $equipment;
    }

    /**
     * @return mixed
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @param mixed $imageId
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;
    }

    /**
     * @return mixed
     */
    public function getImageId()
    {
        return $this->imageId;
    }

    /**
     * @param mixed $imageLink
     */
    public function setImageLink($imageLink)
    {
        $this->imageLink = $imageLink;
    }

    /**
     * @return mixed
     */
    public function getImageLink()
    {
        return $this->imageLink;
    }

    /**
     * Set scanregion
     *
     * @param string $scanregion
     * @return Imaging
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
     * @return Imaging
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
     * Set scandate
     *
     * @param \DateTime $scandate
     * @return Imaging
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


    //object info for blue strip
    public function obtainFullObjectName() {

        $fullNameArr = array();

        //field -> mag
        $mag = $this->getField();
        if( $mag && $mag != "" ) {
            $fullNameArr[] = $mag."";
        }

        //imageId
        $imageId = $this->getImageId();
        if( $imageId ) {
            $fullNameArr[] = $imageId."";
        }

        $fullName = implode(": ",$fullNameArr);

        return $fullName;
    }

}