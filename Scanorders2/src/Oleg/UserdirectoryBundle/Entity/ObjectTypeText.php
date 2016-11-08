<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_objectTypeText")
 */
class ObjectTypeText extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeText", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeText", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\OneToMany(targetEntity="FormNode", mappedBy="objectTypeText")
     */
    private $formNodes;




    public function __construct() {
        parent::__construct();

        $this->formNodes = new ArrayCollection();
    }





    public function addFormNode($item)
    {
        if( $item && !$this->formNodes->contains($item) ) {
            $this->formNodes->add($item);
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

}