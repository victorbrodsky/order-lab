<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="specialStains")
 */
class SpecialStains extends BlockArrayFieldAbstract
{

//    /**
//     * @ORM\ManyToOne(targetEntity="Slide", inversedBy="specialStains")
//     * @ORM\JoinColumn(name="slide_id", referencedColumnName="id", nullable=true)
//     */
//    protected $slide;

    /**
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="specialStains")
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id", nullable=true)
     */
    protected $block;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="StainList", inversedBy="specialstain")
     * @ORM\JoinColumn(name="stainlist_id", referencedColumnName="id", nullable=true)
     */
    protected $staintype;

    /**
     * @param mixed $staintype
     */
    public function setStaintype($staintype)
    {
        $this->staintype = $staintype;
    }

    /**
     * @return mixed
     */
    public function getStaintype()
    {
        return $this->staintype;
    }



}