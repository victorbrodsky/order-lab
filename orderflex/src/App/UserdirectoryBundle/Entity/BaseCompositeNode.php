<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\Criteria;
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
 */

#[ORM\MappedSuperclass]
abstract class BaseCompositeNode extends ListAbstract implements CompositeNodeInterface {
    //children
    //parent

    #[Gedmo\TreeLeft]
    #[ORM\Column(type: 'integer')]
    protected $lft;

    #[Gedmo\TreeRight]
    #[ORM\Column(type: 'integer')]
    protected $rgt;

    #[Gedmo\TreeLevel]
    #[ORM\Column(type: 'integer')]
    protected $level;

    #[Gedmo\TreeRoot]
    #[ORM\Column(name: 'root', type: 'integer', nullable: true)]
    protected $root;

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
//        $items = $this->children;
//        $criteria = Criteria::create()
//            //->where(Criteria::expr()->eq("user", $user))
//            ->orderBy(array("orderinlist" => Criteria::DESC))
//        ;
//        $itemsFiltered = $items->matching($criteria);
//
//        return $itemsFiltered[0];
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
        //if( !$this->getLevel() ) {
            if (count($parent->getChildren()) > 0) {
                //$firstSiblingLevel = $parent->getChildren()->first()->getLevel();
                //$this->setLevel($firstSiblingLevel);
                $defaultChild = $this->getFirstDefaultChild($parent);
                if( $defaultChild ) {
                    //echo "def level=".$defaultChild->getLevel()."<br>";
                    //exit('exit');
                    $defaultSiblingLevel = $defaultChild->getLevel();
                    $this->setLevel($defaultSiblingLevel);
                }
            }
        //}
    }
    //get the first child with positive level OrganizationalGroupType
    public function getFirstDefaultChild( $parent ) {
        foreach( $parent->getChildren() as $child ) {
            if( method_exists($child,'getOrganizationalGroupType') ) {
                if( $child->getOrganizationalGroupType() && intval($child->getOrganizationalGroupType()->getLevel()) >= 0 ) {
                    return $child;
                }
            }
        }
        return $parent->getChildren()->first();
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


    public function getEntityBreadcrumbs( $topToBottom=true ) {
        $breadcrumbsArr = array();
        $breadcrumbsArr = $this->getIdBreadcrumbsIter($this,$breadcrumbsArr,false);
        if( $topToBottom ) {
            $breadcrumbsArr = array_reverse($breadcrumbsArr);
        }
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

    public function getTreeName($separator=" => ") {
        $breadCrumbs = $this->getEntityBreadcrumbs();
        $strArr = array();
        foreach( $breadCrumbs as $breadCrumb ) {
            $strArr[] = $breadCrumb->getName()."";
        }
        return implode($separator,$strArr);
    }
    public function getTreeNameReverse($separator=" <= ") {
        $breadCrumbs = $this->getEntityBreadcrumbs();
        $strArr = array();
        foreach( $breadCrumbs as $breadCrumb ) {
            $strArr[] = $breadCrumb->getName()."";
        }
        $strArr = array_reverse($strArr);
        return implode($separator,$strArr);
    }

    public function getTreeAbbreviation($separator=" => ") {
        $breadCrumbs = $this->getEntityBreadcrumbs();
        $strArr = array();
        foreach( $breadCrumbs as $breadCrumb ) {
            $strArr[] = $breadCrumb->getAbbreviation()."";
            //echo "added ".$breadCrumb->getId().", ".$breadCrumb->getAbbreviation().""."<br>";
        }
        return implode($separator,$strArr);
    }

    //WCM Department of Pathology and Laboratory Medicine
    public function getTreeRootAbbreviationChildName($separator=" => ") {
        $breadCrumbs = $this->getEntityBreadcrumbs();
        $strArr = array();
        foreach( $breadCrumbs as $breadCrumb ) {
            $strArr[] = $breadCrumb->getAbbreviation()."";
            //echo "added ".$breadCrumb->getId().", ".$breadCrumb->getAbbreviation().""."<br>";
        }
        //$strArr = array_reverse($strArr);
        return implode($separator,$strArr);
    }

    public function getTreeNameWithoutRoot($separator=" => ") {
        $breadCrumbs = $this->getEntityBreadcrumbs();
        $strArr = array();
        foreach( $breadCrumbs as $breadCrumb ) {
            if( $breadCrumb->getLevel() > 0 ) {
                $strArr[] = $breadCrumb->getName()."";
            }
        }
        return implode($separator,$strArr);
    }
    //get at least one tree name without root, get root name for root
    //for root - "Root Name"
    //for root->children1->children2 - "Children1 Name => Children2 Name"
    public function getOneTreeNameWithoutRoot($separator=" => ") {
        $name = $this->getTreeNameWithoutRoot($separator);
        if( !$name ) {
            $name = $this->getName();
        }
        return $name;
    }


    public function printTree( $delimeter=null ) {

        if( $delimeter ) {
            echo $this->addSpaces($this->getLevel());
        }
        echo $this;
        if( $delimeter ) {
            echo "$delimeter";
        }

        foreach( $this->getChildren() as $subCategory ) {
            if( count($subCategory->getChildren()) > 0 ) {
                $subCategory->printTree($delimeter);
            } else {
                if( $delimeter ) {
                    echo $this->addSpaces($subCategory->getLevel());
                }
                echo $subCategory;
                if( $delimeter ) {
                    echo "$delimeter";
                }
            }
        }
    }
    public function addSpaces($level) {
        $spaces = "";
        for( $x = 1; $x <= intval($level); $x++ ) {
            $spaces = $spaces . "-";
        }
        return $spaces.$level.":";
    }

    public function getFlatTree( $flatTree=array() ) {

        $flatTree[] = $this; //->getTreeNameWithoutRoot(" > ");

        foreach( $this->getChildren() as $subCategory ) {
            if( count($subCategory->getChildren()) > 0 ) {
                $flatTree = $subCategory->getFlatTree($flatTree);
            } else {
                $flatTree[] = $subCategory; //->getTreeNameWithoutRoot(" > ");
            }
        }

        return $flatTree;
    }

    //make select ID as "name_id"
    public function printTreeSelectList( $nodes=array(), $nameMethod="getNodeNameWithParent", $asLabelValue=true, $types=array() ) {
        //echo $this."; typescount=".count($types)."; thistype=".$this->getType()."<br>";

        if( count($types) > 0 ) {
            if( in_array($this->getType(),$types) ) {
                //ok: this type is in the provided types
            } else {
                //echo "Not in types:".$this."<br>";
                return $nodes;
            }
        }

        $name = $this->$nameMethod()."";
        //echo "id=".$this->getId().": ".$name."<br>";
        if( $name ) {
            //echo "id=".$this->getId().": ".$name."<br>";
            if( $asLabelValue ) {
                $nodes[$name] = $this->getName()."_".$this->getId();
            } else {
                $nodes[$this->getName()."_".$this->getId()] = $name;
            }
        }//if name

        foreach( $this->getChildren() as $subCategory ) {

            if( count($types) > 0 ) {
                if( in_array($subCategory->getType(),$types) ) {
                    //ok: this type is in the provided types
                } else {
                    //echo "Not in types:".$subCategory."<br>";
                    continue;
                }
            }

            //echo "id=".$subCategory->getId().": ".$subCategory->getName()."<br>";
            if( count($subCategory->getChildren()) > 0 ) {
                $nodes = $subCategory->printTreeSelectList($nodes,$nameMethod,$asLabelValue,$types);
            } else {
                $name = $subCategory->$nameMethod()."";
                if( $name ) {
                    if( $asLabelValue ) {
                        $nodes[$name] = $subCategory->getName() . "_" . $subCategory->getId(); //label => value
                    } else {
                        $nodes[$subCategory->getName() . "_" . $subCategory->getId()] = $name; //value => label
                    }
                }//if name
            }//if/else children

        }

        return $nodes;
    }

//    //make a full tree as entity except root
//    public function getFullTreeAsEntity_ORIG( $nodes=array(), $types=array() ) {
//        //echo $this."; typescount=".count($types)."; thistype=".$this->getType()."<br>";
////        $nodes = array(
////            "node1_id" => array(id, name, array(id, name, array(id, name, array()))),
////            "node2_id" => array(id, name, array()),
////            "node3_id" => array(id, name, array(id, name, array(id, name, array()))),
////        );
//
//        if( count($types) > 0 ) {
//            if( in_array($this->getType(),$types) ) {
//                //ok: this type is in the provided types
//            } else {
//                //echo "Not in types:".$this."<br>";
//                return $nodes;
//            }
//        }
//
//        $childrenArr = array();
//        //$id = $this->getId();
//        //$name = $this->getName();
//        //$nodes[] = array($id,$name,$childrenArr);
//
//        foreach( $this->getChildren() as $child ) {
//
//            if( count($types) > 0 ) {
//                if( in_array($child->getType(),$types) ) {
//                    //ok: this type is in the provided types
//                } else {
//                    //echo "Not in types:".$subCategory."<br>";
//                    continue;
//                }
//            }
//
//            //echo "id=".$subCategory->getId().": ".$subCategory->getName()."<br>";
//            //$childrenArr = array();
//            if( count($child->getChildren()) > 0 ) {
//                $childrenArr = $child->getFullTreeAsEntity($childrenArr,$types);
//            } else {
//                $childrenArr = array();
//                //$id = $child->getId();
//                //$name = $child->getName();
//                //$childrenArr[] = array($id,$name,$childrenArr);
//            }//if/else children
//
//            $id = $child->getId();
//            $name = $child->getName();
//            //$childrenArr[] = array($id,$name,$childrenArr);
//            $nodes[] = array($id,$name,$childrenArr);
//
//        }
//
//        //$id = $this->getId();
//        //$name = $this->getName();
//        //$nodes[] = array($id,$name,$childrenArr);
//
//        return $nodes;
//    }
    //make a full tree as entity except root
    public function getFullTreeAsEntity( $nodes=array(), $types=array() ) {
        //echo $this."; typescount=".count($types)."; thistype=".$this->getType()."<br>";
        if( count($types) > 0 ) {
            if( in_array($this->getType(),$types) ) {
                //ok: this type is in the provided types
            } else {
                //echo "Not in types:".$this."<br>";
                return $nodes;
            }
        }

        foreach( $this->getChildren() as $child ) {

            if( count($types) > 0 ) {
                if( in_array($child->getType(),$types) ) {
                    //ok: this type is in the provided types
                } else {
                    //echo "Not in types:".$subCategory."<br>";
                    continue;
                }
            }

            //echo "id=".$subCategory->getId().": ".$subCategory->getName()."<br>";
            if( count($child->getChildren()) > 0 ) {
                $childrenArr = array();
                $childrenArr = $child->getFullTreeAsEntity($childrenArr,$types);
            } else {
                $childrenArr = array();
            }//if/else children

            $id = $child->getId();
            $name = $child->getName();
            $nodes[] = array($id,$name,$childrenArr);

        }

        return $nodes;
    }

    public function  getFlatFullTreeAsEntity1( $flatTreeArr=array(), $types=array() ) {
        //echo $this."; typescount=".count($types)."; thistype=".$this->getType()."<br>";
        if( count($types) > 0 ) {
            if( in_array($this->getType(),$types) ) {
                //ok: this type is in the provided types
            } else {
                //echo "Not in types:".$this."<br>";
                return $flatTreeArr;
            }
        }

        foreach( $this->getChildren() as $child ) {

            if( count($types) > 0 ) {
                if( in_array($child->getType(),$types) ) {
                    //ok: this type is in the provided types
                } else {
                    //echo "Not in types:".$subCategory."<br>";
                    continue;
                }
            }

            //echo "id=".$subCategory->getId().": ".$subCategory->getName()."<br>";
            if( count($child->getChildren()) > 0 ) {
                $childrenArr = array();
                $childrenArr = $child->getFlatFullTreeAsEntity($childrenArr,$types);
            } else {
                $childrenArr = array();
            }//if/else children

            //$id = $child->getId();
            //$name = $child->getName();
            $flatTreeArr[] = array($child);

        }

        return $flatTreeArr;
    }
    public function getFlatFullTreeAsEntity( $flatTreeArr=array(), $types=array() ) {
        $flatArray = array();
        $flatArray = $this->flatten($flatArray,$this);
        return $flatArray;
    }
    function flatten($flatArray,$element)
    {
        $flatArray[] = $element->getName();
        //$flatArray = array();
        foreach( $element->getChildren() as $child ) {
            $children = $child->getChildren();
            if( count($children) > 0 ) {
                foreach($children as $child) {
                    //$flatArray = array_merge($flatArray, flatten($node['child']));
                    $this->flatten($flatArray,$child);
                    //$flatArray[] = $child;
                }
                //unset($node['child']);
                $flatArray[] = $child->getName();
            } else {
                $flatArray[] = $child->getName();
            }
        }

        return $flatArray;
    }

    public function getListElement() {
        $element['id'] = $this->getId();
        $element['text'] = $this->getNodeNameWithParent();
        return $element;
    }

    public function selectNodesUnderParentNode( $parentNode, $field, $default=true ) {

        if( $default ) {
            $comparatorLft = "<";
            $comparatorRgt = ">";
        } else {
            $comparatorLft = ">";
            $comparatorRgt = "<";
        }

        $criteriastr = "";
        $criteriastr .= $field.".root = " . $parentNode->getRoot();
        $criteriastr .= " AND ";
        $criteriastr .= $field.".lft $comparatorLft " . $parentNode->getLft(); //Default: lft < getLft
        $criteriastr .= " AND ";
        $criteriastr .= $field.".rgt $comparatorRgt " . $parentNode->getRgt(); //Default: rgt > getRgt
        $criteriastr .= " OR ";
        $criteriastr .= $field.".id = " . $parentNode->getId();

        $criteriastr = "(".$criteriastr.")";

        return $criteriastr;
    }

    //TODO: rewrite it using recursive
    public function getTreeName_OLD($separator=" => ") {

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
            $treeNameStr = implode($separator,$treeName);
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

    public function getNodeNameWithParent($separator=": ") {

        $treeNameStr = $this->getName()."";
        //echo "treeNameStr=".$treeNameStr."<br>";

        $parent = $this->getParent();
        //echo "parent=".$parent."<br>";

        if( $parent ) {
            $treeNameStr = $parent->getName() . $separator . $treeNameStr;
        }

        return $treeNameStr;
    }
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