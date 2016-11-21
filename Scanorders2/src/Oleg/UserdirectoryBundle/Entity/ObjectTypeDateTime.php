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


    /**
     * @ORM\OneToMany(targetEntity="FormNode", mappedBy="objectTypeDateTime")
     */
    private $formNodes;

    /**
     * @ORM\Column(type="datetime", nullable=true)
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