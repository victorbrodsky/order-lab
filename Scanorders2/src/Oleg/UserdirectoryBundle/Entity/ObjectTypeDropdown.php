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
     * @ORM\OneToMany(targetEntity="FormNode", mappedBy="objectTypeDropdown")
     */
    private $formNodes;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $value;





    public function __construct( $creator = null ) {
        parent::__construct($creator);

        $this->formNodes = new ArrayCollection();
    }





    public function addFormNode($item)
    {
        if( $item && !$this->formNodes->contains($item) ) {
            $this->formNodes->add($item);
            //$item->setObjectType($this);
        }
        return $this;
    }
    public function removeFormNode($item)
    {
        $this->formNodes->removeElement($item);
    }
    public function getFormNodes()
    {
        return $this->formNodes;
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