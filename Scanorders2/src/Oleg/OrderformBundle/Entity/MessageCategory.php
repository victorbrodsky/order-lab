<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
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
class MessageCategory extends ListAbstract implements ComponentCategoryInterface {

    /**
     * @ORM\OneToMany(targetEntity="MessageCategory", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="MessageCategory", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    // Composites' fields

    //parent
    /**
     * @ORM\ManyToOne(targetEntity="MessageCategory", inversedBy="children")
     **/
    private $parent;

    //children
    /**
     * @ORM\OneToMany(targetEntity="MessageCategory", mappedBy="parent", cascade={"persist","remove"})
     **/
    private $children;

    //left
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lft;

    //right
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rgt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $level;



    public function __construct() {
        parent::__construct();
        $this->children = new ArrayCollection();
    }





    public function getChild( $index ) {
        return $this->children->get($index);
    }

    public function getChildren()
    {
        return $this->children;
    }
    public function addChild($item)
    {
        if( !$this->children->contains($item) ) {
            $this->children->add($item);
            $item->setParent($this);
        }
    }
    public function removeChild($item)
    {
        $this->children->removeElement($item);
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }



    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $lft
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    /**
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param mixed $rgt
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }

    /**
     * @return mixed
     */
    public function getRgt()
    {
        return $this->rgt;
    }



    public function getClassName() {
        return "MessageCategory";
    }
//    public function getParentClassName() {
//        return $this->getClassName();
//    }


    public function printTree() {

        echo $this;

        foreach( $this->getChildren() as $subCategory ) {

            if( count($subCategory->getChildren()) > 0 ) {
                $subCategory->printTree();
            } else {
                echo $subCategory;
            }

        }

    }

    public function __toString() {
        $parentName = "";
        if( $this->getParent() ) {
            $parentName = ", parent=".$this->getParent()->getName();
        }
        return "Category: ".$this->getName().", level=".$this->getLevel().", orderinlist=".$this->getOrderinlist().$parentName;
    }

}