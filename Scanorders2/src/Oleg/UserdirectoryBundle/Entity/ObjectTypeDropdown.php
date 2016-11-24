<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeDropdown")
 */
class ObjectTypeDropdown extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeDropdown", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeDropdown", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeDropdowns", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    private $formNode;




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