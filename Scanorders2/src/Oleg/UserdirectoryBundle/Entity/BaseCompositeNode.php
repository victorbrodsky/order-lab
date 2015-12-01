<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

//TODO: turn to BaseCompositeNode: Research, Education in scan order
/**
 * Use Composite pattern:
 * The composite pattern describes that a group of objects is to be treated in the same
 * way as a single instance of an object. The intent of a composite is to "compose" objects into tree structures
 * to represent part-whole hierarchies. Implementing the composite pattern lets clients treat individual objects
 * and compositions uniformly.
 * Use Doctrine Extension Tree for tree manipulation.
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseCompositeNode extends ListAbstract implements CompositeNodeInterface {  //extends ListAbstract

    //children

    //parent

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;



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
    public function setParent(CompositeNodeInterface $parent = null)
    {
        $this->parent = $parent;

        //change level of this entity to the first child level of the parent
        if( count($parent->getChildren()) > 0 ) {
            $firstSiblingLevel = $parent->getChildren()->first()->getLevel();
            $this->setLevel($firstSiblingLevel);
        }
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

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }


    public function getEntityBreadcrumbs() {
        $breadcrumbsArr = array();
        $breadcrumbsArr = $this->getIdBreadcrumbsIter($this,$breadcrumbsArr,false);
        $breadcrumbsArr = array_reverse($breadcrumbsArr);
        //print_r($breadcrumbsArr);
        return $breadcrumbsArr;
    }
    public function getIdBreadcrumbs() {
        $breadcrumbsArr = array();
        $breadcrumbsArr = $this->getIdBreadcrumbsIter($this,$breadcrumbsArr,true);
        $breadcrumbsArr = array_reverse($breadcrumbsArr);
        //print_r($breadcrumbsArr);
        return $breadcrumbsArr;
    }
    public function getIdBreadcrumbsStr() {
        $breadcrumbsArr = array();
        $breadcrumbsArr = $this->getIdBreadcrumbsIter($this,$breadcrumbsArr,true);
        $breadcrumbsArr = array_reverse($breadcrumbsArr);
        //print_r($breadcrumbsArr);
        return implode(",",$breadcrumbsArr);
    }
    public function getIdBreadcrumbsIter($node,$breadcrumbsArr,$byId) {
        if( $byId ) {
            $breadcrumbsArr[] = $node->getId();
        } else {
            $breadcrumbsArr[] = $node;
        }
        if( $node->getParent() ) {
            $breadcrumbsArr = $this->getIdBreadcrumbsIter($node->getParent(),$breadcrumbsArr,$byId);
        }
        return $breadcrumbsArr;
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

    //TODO: rewrite it using recursive
    public function getTreeName() {

        $treeName = array();

        $department = null;
        $division = null;
        $inst = null;

        if( $this."" ) {
            $treeName[] = $this."";
            $department = $this->getParent();
        }

        if( $department ) {
            $treeName[] = $department."";
            if( $department && $department->getParent() ) {
                $division = $department->getParent();
            }

        }
        if( $division ) {
            $treeName[] = $division."";
            $inst = $division->getParent();
        }

        if( $inst ) {
            $treeName[] = $inst."";
        }

        $treeName = array_reverse($treeName);

        if( count($treeName) == 1 ) {
            $treeNameStr = $treeName[0];
        } else {
            $treeNameStr = implode(" => ",$treeName);
        }

        return $treeNameStr;
    }
//    public function getTreeNameRecursive($treeName) {
//        $parent = $this->getParent();
//        if( $parent ) {
//            $parent->getTreeNameRecursive($treeName);
//        } else {
//            //$treeName[] = $this."";
//            $treeName .= $this."";
//            return $treeName;
//        }
//    }

    public function getNodeNameWithRoot() {

        $treeNameStr = $this."";

        $root = $this->getRootName($this);
        //echo "root=".$root."<br>";

        if( $root && $root != $this ) {
            $treeNameStr = $treeNameStr . " (" . $root . ")";
        }

        return $treeNameStr;
    }
    public function getRootName($node) {

        $parent = $node->getParent();

        if( $parent && $parent->getParent() ) {
            //echo "parent=".$parent."<br>";
            $parent = $this->getRootName($parent);
        }

        return $parent;
    }

    public function __toString()
    {
        if( $this->getAbbreviation() && $this->getAbbreviation() != "" ) {
            return $this->getAbbreviation()."";
        }

        return $this->getName()."";
    }


}