<?php

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

    /**
     * @ORM\OneToMany(targetEntity="FormNode", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
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

    //textValue
    //TODO: choose the best way to link to the list holder:
    //1) "hard" link using FK to ObjectTypeText
    //2) "soft" link using entityNamespace="Oleg\UserdirectoryBundle\Entity" and entityName="ObjectTypeText"
    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeText", inversedBy="formNodes", cascade={"persist"})
     * @ORM\JoinColumn(name="objectTypeText_id", referencedColumnName="id")
     */
    private $objectTypeText;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectTypeString", inversedBy="formNodes", cascade={"persist"})
     */
    private $objectTypeString;




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




    public function __construct($creator=null) {
        parent::__construct($creator);

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

    /**
     * @return mixed
     */
    public function getObjectTypeText()
    {
        return $this->objectTypeText;
    }

    /**
     * @param mixed $objectTypeText
     */
    public function setObjectTypeText($objectTypeText)
    {
        $this->objectTypeText = $objectTypeText;
    }

    /**
     * @return mixed
     */
    public function getObjectTypeString()
    {
        return $this->objectTypeString;
    }

    /**
     * @param mixed $objectTypeString
     */
    public function setObjectTypeString($objectTypeString)
    {
        $this->objectTypeString = $objectTypeString;
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