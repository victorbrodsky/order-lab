<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\MappedSuperclass
 */
class ObjectTypeReceivingBase extends ListAbstract
{

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $arraySectionIndex;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $arraySectionId;





    public function __construct( $creator = null ) {
        parent::__construct($creator);
    }



    /**
     * @return mixed
     */
    public function getFormNode()
    {
        return $this->formNode;
    }

    /**
     * @param mixed $formNode
     */
    public function setFormNode($formNode)
    {
        $this->formNode = $formNode;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getArraySectionIndex()
    {
        return $this->arraySectionIndex;
    }

    /**
     * @param mixed $arraySectionIndex
     */
    public function setArraySectionIndex($arraySectionIndex)
    {
        $this->arraySectionIndex = $arraySectionIndex;
    }

    /**
     * @return mixed
     */
    public function getArraySectionId()
    {
        return $this->arraySectionId;
    }

    /**
     * @param mixed $arraySectionId
     */
    public function setArraySectionId($arraySectionId)
    {
        $this->arraySectionId = $arraySectionId;
    }




}