<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseCompositeNode extends ListAbstract implements ComponentCategoryInterface {  //extends ListAbstract

    //children

    //parent

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


    //May add additional properties of the tree node


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

    public function __toString()
    {
        if( $this->getAbbreviation() && $this->getAbbreviation() != "" ) {
            return $this->getAbbreviation()."";
        }

        return $this->getName()."";
    }


}