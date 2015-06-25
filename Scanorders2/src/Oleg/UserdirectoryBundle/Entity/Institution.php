<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
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
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\TreeRepository")
 * @ORM\Table(
 *  name="user_institution",
 *  indexes={
 *      @ORM\Index( name="name_idx", columns={"name"} ),
 *  }
 * )
 */
class Institution extends BaseCompositeNode {

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Institution", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     **/
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="Institution", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\ManyToMany(targetEntity="BuildingList", mappedBy="institutions")
     **/
    private $buildings;

    /**
     * Medical, Educational
     * @ORM\ManyToMany(targetEntity="InstitutionType", inversedBy="institutions")
     * @ORM\JoinTable(name="user_institutions_types")
     **/
    private $types;

    //    /**
//     * @ORM\OneToMany(targetEntity="Department", mappedBy="parent", cascade={"persist"})
//     */
//    protected $departments;
//    /**
//     * @ORM\ManyToMany(targetEntity="User")
//     * @ORM\JoinTable(name="user_institution_head")
//     **/
//    private $heads;
//    /**
//     * //Position Type: Head, Manager, Primary Contact, Transcriptionist
//     * @ORM\ManyToMany(targetEntity="InstitutionType", inversedBy="institutions")
//     * @ORM\JoinTable(name="user_institutions_types")
//     **/
//    private $positionTypes;
    /**
     * @ORM\OneToMany(targetEntity="UserPosition", mappedBy="institution", cascade={"persist","remove"})
     */
    private $userPositions;

    /**
     * Organizational Group Types
     * level int in OrganizationalGroupType corresponds to this level integer: 1-Institution, 2-Department, 3-Division, 4-Service
     * For example, OrganizationalGroupType with level=1, set this level to 1.
     *
     * @ORM\ManyToOne(targetEntity="OrganizationalGroupType", cascade={"persist"})
     */
    private $organizationalGroupType;


    //May add additional properties of the tree node


    public function __construct() {
        parent::__construct();

        $this->buildings = new ArrayCollection();
        $this->types = new ArrayCollection();
        $this->userPositions = new ArrayCollection();
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




    public function addUserPosition($item)
    {
        if( !$this->userPositions->contains($item) ) {
            $this->userPositions->add($item);
        }
        return $this;
    }
    public function removeUserPosition($item)
    {
        $this->userPositions->removeElement($item);
    }
    public function getUserPositions()
    {
        return $this->userPositions;
    }


    public function getBuildings()
    {
        return $this->buildings;
    }
    public function addBuilding($building)
    {
        if( !$this->buildings->contains($building) ) {
            $this->buildings->add($building);
        }

        return $this;
    }
    public function removeBuilding($building)
    {
        $this->buildings->removeElement($building);
    }

    public function addType($type)
    {
        if( !$this->types->contains($type) ) {
            $this->types->add($type);
        }
        return $this;
    }

    public function removeType($type)
    {
        $this->types->removeElement($type);
    }

    public function getTypes()
    {
        return $this->types;
    }


    /**
     * Overwrite base setParent method: adjust this organizationalGroupType according to the first parent child
     * @param mixed $parent
     */
    public function setParent(CompositeNodeInterface $parent = null)
    {
        $this->parent = $parent;

        //change organizationalGroupType of this entity to the first child organizationalGroupType of the parent
        if( count($parent->getChildren()) > 0 ) {
            $firstSiblingOrgGroupType = $parent->getChildren()->first()->getOrganizationalGroupType();
            $this->setOrganizationalGroupType($firstSiblingOrgGroupType);
        }
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
        return "Institution";
    }

}