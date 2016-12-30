<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeDropdown")
 */
class ObjectTypeDropdown extends ObjectTypeReceivingBase
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
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="objectTypeDropdowns", cascade={"persist"})
     * @ORM\JoinColumn(name="formNode_id", referencedColumnName="id")
     */
    protected $formNode;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $idValues;



    public function __construct($creator=null)
    {
        parent::__construct($creator);
        $this->idValues = array();
    }


    /**
     * @return mixed
     */
    public function getIdValues()
    {
        return $this->idValues;
    }
    /**
     * @param mixed $values
     */
    public function setIdValues($values)
    {
        if( $values ) {
            foreach( $values as $value ) {
                $this->addIdValue($value);
            }
        }
    }
    public function addIdValue($value) {
        $this->idValues[] = $value;
        return $this;
    }

}