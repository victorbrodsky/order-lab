<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Oleg\UserdirectoryBundle\Entity\BaseCompositeNode;
use Oleg\UserdirectoryBundle\Entity\ComponentCategoryInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * Use Composite pattern:
 * The composite pattern describes that a group of objects is to be treated in the same
 * way as a single instance of an object. The intent of a composite is to "compose" objects into tree structures
 * to represent part-whole hierarchies. Implementing the composite pattern lets clients treat individual objects
 * and compositions uniformly.
 *
 * @ORM\Entity
 * @ORM\Table(name="scan_messageCategory")
 */
class MessageCategory extends BaseCompositeNode {

    /**
     * @ORM\ManyToOne(targetEntity="MessageCategory", inversedBy="children")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="MessageCategory", mappedBy="parent", cascade={"persist","remove"})
     **/
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="MessageCategory", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="MessageCategory", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;




    public function getClassName() {
        return "MessageCategory";
    }




    public function __toString() {
        $parentName = "";
        if( $this->getParent() ) {
            $parentName = ", parent=".$this->getParent()->getName();
        }
        return "Category: ".$this->getName().", level=".$this->getLevel().", orderinlist=".$this->getOrderinlist().$parentName;
    }

}