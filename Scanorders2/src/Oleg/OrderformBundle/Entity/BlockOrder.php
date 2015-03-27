<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_blockOrder")
 */
class BlockOrder {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="OrderInfo", mappedBy="blockorder")
     **/
    private $orderinfo;

    //Instruction for Embedder (List manager: datetime, author, author roles)
    /**
     * @ORM\ManyToOne(targetEntity="InstructionList", cascade={"persist"})
     */
    private $instruction;

    //Block processed datetime
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processedDate;

    //Block processed by (User)
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $processedByUser;

    //Block embedded datetime
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $embeddedDate;

    //Block embedded by (User)
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $embeddedByUser;






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


}