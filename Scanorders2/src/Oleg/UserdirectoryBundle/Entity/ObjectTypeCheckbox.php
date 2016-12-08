<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeCheckbox")
 */
class ObjectTypeCheckbox extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeCheckbox", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeCheckbox", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeCheckboxs", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    private $formNode;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $value;




    public function __construct( $creator = null ) {
        parent::__construct($creator);

        //$this->formNodes = new ArrayCollection();
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

//    public function addFormNode($item)
//    {
//        if( $item && !$this->formNodes->contains($item) ) {
//            $this->formNodes->add($item);
//            //$item->setObjectType($this);
//        }
//        return $this;
//    }
//    public function removeFormNode($item)
//    {
//        $this->formNodes->removeElement($item);
//    }
//    public function getFormNodes()
//    {
//        return $this->formNodes;
//    }


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


}