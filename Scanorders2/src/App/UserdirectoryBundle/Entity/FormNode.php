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

namespace Oleg\UserdirectoryBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
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
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\FormNodeRepository")
 * @ORM\Table(name="user_formNode")
 */
class FormNode extends BaseCompositeNode {

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    //@ORM\OrderBy({"lft" = "ASC"})
    //@ORM\OrderBy({"orderinlist" = "ASC"})
    /**
     * @ORM\OneToMany(targetEntity="FormNode", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"orderinlist" = "ASC"})
     **/
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="FormNode", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="FormNode", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

//    /**
//     * Medical, Educational
//     * @ORM\ManyToMany(targetEntity="InstitutionType", inversedBy="institutions")
//     * @ORM\JoinTable(name="user_institutions_types")
//     **/
//    private $types;

//    /**
//     * Organizational Group Types - mapper between the level number and level title.
//     * level int in OrganizationalGroupType corresponds to this level integer: 1-Institution, 2-Department, 3-Division, 4-Service
//     * For example, OrganizationalGroupType with level=1, set this level to 1.
//     * Default types have a positive level numbers, all other types have negative level numbers.
//     *
//     * @ORM\ManyToOne(targetEntity="OrganizationalGroupType", cascade={"persist"})
//     */
//    private $organizationalGroupType;


//    //objectType
//    /**
//     * @ORM\ManyToOne(targetEntity="ObjectTypeList", cascade={"persist"})
//     */
//    private $objectType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $showLabel;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $placeholder;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $visible;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $required;
    
    //Received Form Field Value Entity. Used to overwrite the same values in the formnode's ObjectType
    /**
     * i.e. "Oleg\OlegUserdirectoryBundle\Entity"
     * @ORM\Column(type="string", nullable=true)
     */
    private $receivedValueEntityNamespace;
    /**
     * i.e. "Patient"
     * @ORM\Column(type="string", nullable=true)
     */
    private $receivedValueEntityName;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $receivedValueEntityId;




    //textValue
    //TODO: choose the best way to link to the list holder:
    //1) "hard" link using FK to ObjectTypeText
    //2) "soft" link using entityNamespace="Oleg\UserdirectoryBundle\Entity" and entityName="ObjectTypeText"
//    /**
//     * @ORM\ManyToOne(targetEntity="ObjectTypeText", inversedBy="formNodes", cascade={"persist"})
//     * @ORM\JoinColumn(name="objectTypeText_id", referencedColumnName="id")
//     */
    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeText", mappedBy="formNode")
     */
    private $objectTypeTexts;

//    /**
//     * @ORM\ManyToOne(targetEntity="ObjectTypeString", inversedBy="formNodes", cascade={"persist"})
//     */
    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeString", mappedBy="formNode")
     */
    private $objectTypeStrings;

//    /**
//     * @ORM\ManyToOne(targetEntity="ObjectTypeDropdown", inversedBy="formNodes", cascade={"persist"})
//     */
    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeDropdown", mappedBy="formNode")
     */
    private $objectTypeDropdowns;

//    /**
//     * @ORM\ManyToOne(targetEntity="ObjectTypeDateTime", inversedBy="formNodes", cascade={"persist"})
//     */
    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeDateTime", mappedBy="formNode")
     */
    private $objectTypeDateTimes;

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeCheckbox", mappedBy="formNode")
     */
    private $objectTypeCheckboxs;

    /**
     * @ORM\OneToMany(targetEntity="ObjectTypeRadioButton", mappedBy="formNode")
     */
    private $objectTypeRadioButtons;



    public function __construct($creator=null) {
        parent::__construct($creator);

        $this->objectTypeTexts = new ArrayCollection();
        $this->objectTypeStrings = new ArrayCollection();
        $this->objectTypeDropdowns = new ArrayCollection();
        $this->objectTypeDateTimes = new ArrayCollection();
        $this->objectTypeCheckboxs = new ArrayCollection();
        $this->objectTypeRadioButtons = new ArrayCollection();

    }




    /**
     * @return mixed
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param mixed $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    public function addObjectTypeText($item)
    {
        if( $item && !$this->objectTypeTexts->contains($item) ) {
            $this->objectTypeTexts->add($item);
            $item->setFormNode($this);
        }
        return $this;
    }
    public function removeObjectTypeText($item)
    {
        $this->objectTypeTexts->removeElement($item);
    }
    public function getObjectTypeTexts()
    {
        return $this->objectTypeTexts;
    }

    public function addObjectTypeString($item)
    {
        if( $item && !$this->objectTypeStrings->contains($item) ) {
            $this->objectTypeStrings->add($item);
            $item->setFormNode($this);
        }
        return $this;
    }
    public function removeObjectTypeString($item)
    {
        $this->objectTypeStrings->removeElement($item);
    }
    public function getObjectTypeStrings()
    {
        return $this->objectTypeStrings;
    }

    public function addObjectTypeDropdown($item)
    {
        if( $item && !$this->objectTypeDropdowns->contains($item) ) {
            $this->objectTypeDropdowns->add($item);
            $item->setFormNode($this);
        }
        return $this;
    }
    public function removeObjectTypeDropdown($item)
    {
        $this->objectTypeDropdowns->removeElement($item);
    }
    public function getObjectTypeDropdowns()
    {
        return $this->objectTypeDropdowns;
    }

    public function addObjectTypeDateTime($item)
    {
        if( $item && !$this->objectTypeDateTimes->contains($item) ) {
            $this->objectTypeDateTimes->add($item);
            $item->setFormNode($this);
        }
        return $this;
    }
    public function removeObjectTypeDateTime($item)
    {
        $this->objectTypeDateTimes->removeElement($item);
    }
    public function getObjectTypeDateTimes()
    {
        return $this->objectTypeDateTimes;
    }

    public function addObjectTypeCheckbox($item)
    {
        if( $item && !$this->objectTypeCheckboxs->contains($item) ) {
            $this->objectTypeCheckboxs->add($item);
            $item->setFormNode($this);
        }
        return $this;
    }
    public function removeObjectTypeCheckbox($item)
    {
        $this->objectTypeCheckboxs->removeElement($item);
    }
    public function getObjectTypeCheckboxs()
    {
        return $this->objectTypeCheckboxs;
    }

    public function addObjectTypeRadioButton($item)
    {
        if( $item && !$this->objectTypeRadioButtons->contains($item) ) {
            $this->objectTypeRadioButtons->add($item);
            $item->setFormNode($this);
        }
        return $this;
    }
    public function removeObjectTypeRadioButton($item)
    {
        $this->objectTypeRadioButtons->removeElement($item);
    }
    public function getObjectTypeRadioButtons()
    {
        return $this->objectTypeRadioButtons;
    }

    /**
     * @return mixed
     */
    public function getShowLabel()
    {
        return $this->showLabel;
    }

    /**
     * @param mixed $showLabel
     */
    public function setShowLabel($showLabel)
    {
        $this->showLabel = $showLabel;
    }

    /**
     * @return mixed
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param mixed $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return mixed
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param mixed $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    

    /**
     * @return mixed
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param mixed $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return mixed
     */
    public function getReceivedValueEntityNamespace()
    {
        return $this->receivedValueEntityNamespace;
    }

    /**
     * @param mixed $receivedValueEntityNamespace
     */
    public function setReceivedValueEntityNamespace($receivedValueEntityNamespace)
    {
        $this->receivedValueEntityNamespace = $receivedValueEntityNamespace;
    }

    /**
     * @return mixed
     */
    public function getReceivedValueEntityName()
    {
        return $this->receivedValueEntityName;
    }

    /**
     * @param mixed $receivedValueEntityName
     */
    public function setReceivedValueEntityName($receivedValueEntityName)
    {
        $this->receivedValueEntityName = $receivedValueEntityName;
    }

    /**
     * @return mixed
     */
    public function getReceivedValueEntityId()
    {
        return $this->receivedValueEntityId;
    }

    /**
     * @param mixed $receivedValueEntityId
     */
    public function setReceivedValueEntityId($receivedValueEntityId)
    {
        $this->receivedValueEntityId = $receivedValueEntityId;
    }



    public function setReceivedValueEntity($object) {
        $class = new \ReflectionClass($object);
        $className = $class->getShortName();
        $classNamespace = $class->getNamespaceName();

        if( $className && !$this->getReceivedValueEntityName() ) {
            $this->setReceivedValueEntityName($className);
        }

        if( $classNamespace && !$this->getReceivedValueEntityNamespace() ) {
            $this->setReceivedValueEntityNamespace($classNamespace);
        }

        if( !$this->getReceivedValueEntityId() && $object->getId() ) {
            $this->setReceivedValueEntityId($object->getId());
        }
    }

//    /**
//     * @param mixed $organizationalGroupType
//     */
//    public function setOrganizationalGroupType($organizationalGroupType)
//    {
//        $this->organizationalGroupType = $organizationalGroupType;
//        $this->setLevel($organizationalGroupType->getLevel());
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getOrganizationalGroupType()
//    {
//        return $this->organizationalGroupType;
//


//    /**
//     * Overwrite base setParent method: adjust this organizationalGroupType according to the first parent child
//     * @param mixed $parent
//     */
//    public function setParent(CompositeNodeInterface $parent = null)
//    {
//        $this->parent = $parent;
//
//        //change organizationalGroupType of this entity to the first child organizationalGroupType of the parent if does not exist
//        if( !$this->getOrganizationalGroupType() ) {
//            if ($parent && count($parent->getChildren()) > 0) {
//                //$firstSiblingOrgGroupType = $parent->getChildren()->first()->getOrganizationalGroupType();
//                //$this->setOrganizationalGroupType($firstSiblingOrgGroupType);
//                $defaultChild = $this->getFirstDefaultChild($parent);
//                $defaultSiblingOrgGroupType = $defaultChild->getOrganizationalGroupType();
//                if( $defaultSiblingOrgGroupType ) {
//                    $this->setOrganizationalGroupType($defaultSiblingOrgGroupType);
//                }
//            }
//        }
//    }

    public function getTreeNameObjectType() {
        //$treeName = $this->getTreeName();
        $treeName = "";
        if( $this->getParent() ) {
            $treeName = $treeName . $this->getParent()."=>";
        }
        if( $this->getName() ) {
            $treeName = $treeName . $this->getName();
        }
        if( $this->getObjectType() ) {
            $treeName = $treeName . " [". $this->getObjectType()->getName(). "]";
        }
        return $treeName;
    }

    public function __toString()
    {
        if( $this->getAbbreviation() && $this->getAbbreviation() != "" ) {
            return $this->getAbbreviation()."";
        }

        return $this->getName()."";
    }

    public function getClassName()
    {
        return "FormNode";
    }

}