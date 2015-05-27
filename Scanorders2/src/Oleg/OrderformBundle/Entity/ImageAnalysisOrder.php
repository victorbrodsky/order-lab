<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_imageAnalysisOrder")
 */
class ImageAnalysisOrder {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="OrderInfo", mappedBy="imageAnalysisOrder")
     **/
    private $orderinfo;


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

    //Image Analysis Algorithm (http://indicalab.com/products/)
    /**
     * @ORM\ManyToOne(targetEntity="ImageAnalysisAlgorithmList", cascade={"persist"})
     */
    private $imageAnalysisAlgorithm;




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
     * @param mixed $orderinfo
     */
    public function setOrderinfo($orderinfo)
    {
        $this->orderinfo = $orderinfo;
    }

    /**
     * @return mixed
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
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

    /**
     * @param mixed $imageAnalysisAlgorithm
     */
    public function setImageAnalysisAlgorithm($imageAnalysisAlgorithm)
    {
        $this->imageAnalysisAlgorithm = $imageAnalysisAlgorithm;
    }

    /**
     * @return mixed
     */
    public function getImageAnalysisAlgorithm()
    {
        return $this->imageAnalysisAlgorithm;
    }



}