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

namespace App\OrderformBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Entity\BaseCompositeNode;
use App\UserdirectoryBundle\Entity\ComponentCategoryInterface;
use App\UserdirectoryBundle\Entity\CompositeNodeInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Use Composite pattern:
 * The composite pattern describes that a group of objects is to be treated in the same
 * way as a single instance of an object. The intent of a composite is to "compose" objects into tree structures
 * to represent part-whole hierarchies. Implementing the composite pattern lets clients treat individual objects
 * and compositions uniformly.
 * Use Doctrine Extension Tree for tree manipulation.
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="App\UserdirectoryBundle\Repository\TreeRepository")
 * @ORM\Table(
 *  name="scan_messageCategory",
 *  indexes={
 *      @ORM\Index( name="messageCategory_name_idx", columns={"name"} ),
 *  }
 * )
 */
class MessageCategory extends BaseCompositeNode {

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="MessageCategory", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="MessageCategory", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
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


    /**
     * Message Type Classifiers - mapper between the level number and level title.
     * level corresponds to this level integer: 1-Message Class, 2-Message Subclass, 3-Service, 4-Issue
     * Default types have a positive level numbers, all other types have negative level numbers.
     *
     * @ORM\ManyToOne(targetEntity="MessageTypeClassifiers", cascade={"persist"})
     */
    private $organizationalGroupType;

//    /**
//     * a single form node can be used only by one message category
//     * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\FormNode", cascade={"persist"})
//     */
    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\FormNode")
     * @ORM\JoinTable(name="scan_messageCategory_formNode",
     *      joinColumns={@ORM\JoinColumn(name="messageCategory_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="formNode_id", referencedColumnName="id")}
     *      )
     **/
    private $formNodes;
    


    public function __construct($author=null) {
        parent::__construct($author);

        $this->formNodes = new ArrayCollection();
    }


    /**
     * @param mixed $organizationalGroupType
     */
    public function setOrganizationalGroupType($organizationalGroupType)
    {
        $this->organizationalGroupType = $organizationalGroupType;
        $this->setLevel($organizationalGroupType->getLevel());
    }

    /**
     * @return mixed
     */
    public function getOrganizationalGroupType()
    {
        return $this->organizationalGroupType;
    }

    /**
     * @return mixed
     */
    public function getFormNodes()
    {
        return $this->formNodes;
    }
    public function addFormNode($item)
    {
        if( !$this->formNodes->contains($item) ) {
            $this->formNodes->add($item);
        }
        return $this;
    }
    public function removeFormNode($item)
    {
        $this->formNodes->removeElement($item);
    }
    public function clearFormNodes()
    {
        $this->formNodes->clear();
    }



    /**
     * Overwrite base setParent method: adjust this organizationalGroupType according to the first parent child
     * @param mixed $parent
     */
    public function setParent(CompositeNodeInterface $parent = null)
    {
        $this->parent = $parent;

        //change organizationalGroupType of this entity to the first child organizationalGroupType of the parent if does not exist
        if( !$this->getOrganizationalGroupType() ) {
            if( $parent && count($parent->getChildren()) > 0 ) {
                //$firstSiblingOrgGroupType = $parent->getChildren()->first()->getOrganizationalGroupType();
                //$this->setOrganizationalGroupType($firstSiblingOrgGroupType);
                $defaultChild = $this->getFirstDefaultChild($parent);
                $defaultSiblingOrgGroupType = $defaultChild->getOrganizationalGroupType();
                if( $defaultSiblingOrgGroupType ) {
                    $this->setOrganizationalGroupType($defaultSiblingOrgGroupType);
                } else {
                    //get default organizational group
                }
            }
        }
    }


    //If Message Type is a child node of "Pathology Call Log Entry",
    // show the child node of "Pathology Call Log Entry" followed by all children,
    // for example: "Transfusion Medicine : First Dose Plasma";
    // If Message Type is not a child node of "Pathology Call Log Entry",
    // show the entire "linage", for example: "Note: Encounter Note: Whatever"
    public function getNodeNameWithParents($separator=": ", $untilParentName="Pathology Call Log Entry") {
        $treeName = array();
        $nodes = $this->getEntityBreadcrumbs(false); //bottom to top
        foreach( $nodes as $node ) {
            //echo "node:".$node->getName()."<br>";
            //$treeName[] = $node->getName()."";
            if( $node->getName()."" == $untilParentName ) {
                break;
            }
            $treeName[] = $node->getName().""; //do not show $untilParentName
        }
        //echo "<br>";
        //$treeName = array_reverse($treeName);
        return implode($separator,$treeName);
    }

    //print this node and all children nodes
    public function printTreeSelectListIncludingThis( $asLabelValue=true, $showTypesArr=array() ) {
        return $this->printTreeSelectList(array(),"getNodeNameWithParentsUntilThisParent",$asLabelValue,$showTypesArr);
    }
    public function getNodeNameWithParentsUntilThisParent() {
        return $this->getNodeNameWithParents(": ","Encounter Note");
    }
//    public function getNodeNameWithParentsUntilThisParent() {
//        $parent = $this->getParent();
//        echo "parent=".$parent->getName()."<br>";
//        return $this->getNodeNameWithParents(": ",$parent->getName()."");
//    }

    public function getClassName() {
        return "MessageCategory";
    }




    public function __toString() {
        $parentName = "";
        if( $this->getParent() ) {
            $parentName = ", parent=".$this->getParent()->getName();
        }
        return "Message Category (ID# ".$this->getId()."): ".$this->getName().", level=".$this->getLevel().", orderinlist=".$this->getOrderinlist().$parentName;
    }

}