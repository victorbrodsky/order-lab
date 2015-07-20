<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_blockOrder")
 */
class BlockOrder extends OrderBase {

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="blockorder")
     **/
    protected $message;


    /**
     * @ORM\ManyToOne(targetEntity="EmbedderInstructionList", cascade={"persist"})
     */
    private $embedderInstruction;





    /**
     * @param mixed $embedderInstruction
     */
    public function setEmbedderInstruction($embedderInstruction)
    {
        $this->embedderInstruction = $embedderInstruction;
    }

    /**
     * @return mixed
     */
    public function getEmbedderInstruction()
    {
        return $this->embedderInstruction;
    }




}