<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//similar to MessageCategory

/**
 * Use Composite pattern:
 * The composite pattern describes that a group of objects is to be treated in the same
 * way as a single instance of an object. The intent of a composite is to "compose" objects into tree structures
 * to represent part-whole hierarchies. Implementing the composite pattern lets clients treat individual objects
 * and compositions uniformly.
 *
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
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="children")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Institution", mappedBy="parent", cascade={"persist","remove"})
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
     * @ORM\OneToMany(targetEntity="UserPosition", mappedBy="institution", cascade={"persist"})
     */
    private $userPositions;

    /**
     * level int in LevelTitleList corresponds to this level integer: 1-Institution, 2-Department, 3-Division, 4-Service
     * For example, LevelTitleList with level=1, set this level to 1.
     *
     * @ORM\ManyToOne(targetEntity="LevelTitleList", cascade={"persist"})
     */
    private $levelTitle;


    //May add additional properties of the tree node


    public function __construct() {
        parent::__construct();

        $this->buildings = new ArrayCollection();
        $this->types = new ArrayCollection();

        //$this->departments = new ArrayCollection();
        //$this->heads = new ArrayCollection();
        $this->userPositions = new ArrayCollection();
    }



    /**
     * @param mixed $levelTitle
     */
    public function setLevelTitle($levelTitle)
    {
        $this->levelTitle = $levelTitle;
        $this->setLevel($levelTitle->getLevel());
    }

    /**
     * @return mixed
     */
    public function getLevelTitle()
    {
        return $this->levelTitle;
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


//    public function addHead($head)
//    {
//        if( !$this->heads->contains($head) ) {
//            $this->heads->add($head);
//        }
//        return $this;
//    }
//
//    public function removeHead($head)
//    {
//        $this->heads->removeElement($head);
//    }
//
//    public function getHeads()
//    {
//        return $this->heads;
//    }
    

//    /**
//     * Add department
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
//     * @return Institution
//     */
//    public function addDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
//    {
//        if( !$this->departments->contains($department) ) {
//            $department->setParent($this);
//            $this->departments->add($department);
//        }
//    }
//    /**
//     * Remove department
//     *
//     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
//     */
//    public function removeDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
//    {
//        $this->departments->removeElement($department);
//    }
//    /**
//     * Get order
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getDepartments()
//    {
//        return $this->departments;
//    }


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