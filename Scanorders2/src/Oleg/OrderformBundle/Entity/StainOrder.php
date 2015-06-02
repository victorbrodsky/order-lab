<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_stainOrder")
 */
class StainOrder {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="stainorder")
     **/
    private $message;


    //Instruction (List manager: datetime, author, author roles)
    /**
     * @ORM\ManyToOne(targetEntity="InstructionList", cascade={"persist"})
     */
    private $instruction;

    //Prepared On
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processedDate;

    //Prepared By
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $processedByUser;

    //Slide Stainer Device [select2, empty for now; link to Equipment table, filter by Type="Slide Stainer"]

//    //Microscopic Image
//    /**
//     * Microscopic Image
//     * device: Microscopic Image Device: [select2, one choice for now - "Olympus Camera" - link to Equipment table, filter by Type="Microscope Camera"]
//     * @ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\DocumentContainer", cascade={"persist","remove"})
//     **/
//    private $documentContainer;
//
//    //Microscopic Image Magnification: [select2, 100X, 83X, 60X, 40X, 20X, 10X, 4X, 2X]
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $imageMagnification;




//    /**
//     * @param mixed $documentContainer
//     */
//    public function setDocumentContainer($documentContainer)
//    {
//        $this->documentContainer = $documentContainer;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDocumentContainer()
//    {
//        return $this->documentContainer;
//    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $instruction
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * @return mixed
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $processedByUser
     */
    public function setProcessedByUser($processedByUser)
    {
        $this->processedByUser = $processedByUser;
    }

    /**
     * @return mixed
     */
    public function getProcessedByUser()
    {
        return $this->processedByUser;
    }

    /**
     * @param mixed $processedDate
     */
    public function setProcessedDate($processedDate)
    {
        $this->processedDate = $processedDate;
    }

    /**
     * @return mixed
     */
    public function getProcessedDate()
    {
        return $this->processedDate;
    }

//    /**
//     * @param mixed $imageMagnification
//     */
//    public function setImageMagnification($imageMagnification)
//    {
//        $this->imageMagnification = $imageMagnification;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getImageMagnification()
//    {
//        return $this->imageMagnification;
//    }





}