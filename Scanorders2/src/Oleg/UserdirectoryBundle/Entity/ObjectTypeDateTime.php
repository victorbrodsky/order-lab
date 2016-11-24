<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeDateTime")
 */
class ObjectTypeDateTime extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeDateTime", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeDateTime", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


//    /**
//     * @ORM\OneToMany(targetEntity="FormNode", mappedBy="objectTypeDateTime")
//     */
//    private $formNodes;
    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeDateTimes", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    private $formNode;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $value;




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



}