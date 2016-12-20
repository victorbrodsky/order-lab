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
    protected $indexSectionArray;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $parentIndexSectionArray;





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
    public function getIndexSectionArray()
    {
        return $this->indexSectionArray;
    }

    /**
     * @param mixed $indexSectionArray
     */
    public function setIndexSectionArray($indexSectionArray)
    {
        $this->indexSectionArray = $indexSectionArray;
    }

    /**
     * @return mixed
     */
    public function getParentIndexSectionArray()
    {
        return $this->parentIndexSectionArray;
    }

    /**
     * @param mixed $parentIndexSectionArray
     */
    public function setParentIndexSectionArray($parentIndexSectionArray)
    {
        $this->parentIndexSectionArray = $parentIndexSectionArray;
    }



}