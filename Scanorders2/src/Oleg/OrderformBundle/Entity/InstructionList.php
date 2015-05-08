<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_instructionList")
 */
class InstructionList extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="InstructionList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="InstructionList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $instruction;




//    public function __construct() {
//        parent::__construct();
//        $this->synonyms = new ArrayCollection();
//    }




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


}