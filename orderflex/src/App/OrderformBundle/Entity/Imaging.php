<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//(repositoryClass="App\OrderformBundle\Repository\ImagingRepository")
/**
 * Slide Imaging: Scan, Microscopic Image, Whole Slide Image
 *
 * @ORM\Entity
 * @ORM\Table(name="scan_imaging")
 */
class Imaging extends ObjectAbstract
{

    /**
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="imaging")
     **/
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="Slide", inversedBy="scan")
     * @ORM\JoinColumn(name="slide", referencedColumnName="id")
     */
    private $slide;

//    /**
//     * @ORM\Column(name="magnification", type="string", nullable=true)
//     */
//    private $magnification;
    /**
     * @ORM\ManyToOne(targetEntity="Magnification")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     **/
    private $magnification;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $scanregion;
    
    /**
     * Note/Reason for Scan
     * @ORM\Column(type="text", nullable=true)    
     */
    private $note;
    

    /**
     * date of scan performed
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $scandate;


    //Extra fields for datastructure: Whole Slide Image
    //Fields existing in this abstract object: Whole Slide Scan Date & Time, Whole Slide Scanned By, Whole Slide Image Magnification
    //Fields existing in message: Scan Order Source, Order ID, Whole Slide Scanner Device
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $imageId;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $imageLink;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Equipment")
     */
    private $equipment;

    //Microscopic Image
    /**
     * Microscopic Image
     * device: Microscopic Image Device: [select2, one choice for now - "Olympus Camera" - link to Equipment table, filter by Type="Microscope Camera"]
     * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\DocumentContainer", cascade={"persist","remove"})
     **/
    private $documentContainer;


    //Microscopic Image Magnification: [select2, 100X, 83X, 60X, 40X, 20X, 10X, 4X, 2X]
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $imageMagnification;



    public function __construct( $status='valid', $provider=null, $source=null )
    {
        parent::__construct($status,$provider,$source);
    }

    public function makeDependClone() {
        //empty method
    }




    public function setSlide(\App\OrderformBundle\Entity\Slide $slide = null)
    {
        $this->slide = $slide;
        return $this;
    }
    public function getSlide()
    {
        return $this->slide;
    }

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

//    /**
//     * @param mixed $imageLink
//     */
//    public function setImageLink($imageLink)
//    {
//        $this->imageLink = $imageLink;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getImageLink()
//    {
//        return $this->imageLink;
//    }

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

    /**
     * @param mixed $magnification
     */
    public function setMagnification($magnification)
    {
        $this->magnification = $magnification;
    }

    /**
     * @return mixed
     */
    public function getMagnification()
    {
        return $this->magnification;
    }



    public function __toString() {
        return "Imaging: id=".$this->getId().", ".$this->scanregion."<br>";
    }


    //object info for blue strip
    public function obtainFullObjectName() {

        $fullNameArr = array();

        //magnification
        $magnification = $this->getMagnification();
        if( $magnification && $magnification != "" ) {
            $fullNameArr[] = $magnification."";
        }

        //imageId
//        $imageId = $this->getImageId();
//        if( $imageId ) {
//            $fullNameArr[] = $imageId."";
//        }

        $fullName = implode(": ",$fullNameArr);

        return $fullName;
    }


    public function getChildren() {
        return null;    //new ArrayCollection();
    }

    public function obtainKeyField() {
        return null;
    }

    public function getParent() {
        return $this->getSlide();
    }

    public function setParent($parent) {
        if( $parent ) {
            $this->setSlide($parent);
        }
    }

}