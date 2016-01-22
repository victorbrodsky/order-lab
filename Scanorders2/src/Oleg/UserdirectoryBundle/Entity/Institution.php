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
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\TreeRepository")
 * @ORM\Table(
 *  name="user_institution",
 *  indexes={
 *      @ORM\Index( name="institution_name_idx", columns={"name"} ),
 *  }
 * )
 */
class Institution extends BaseCompositeNode {

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="children", cascade={"persist"})
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
     * Medical, Educational
     * @ORM\ManyToMany(targetEntity="InstitutionType", inversedBy="institutions")
     * @ORM\JoinTable(name="user_institutions_types")
     **/
    private $types;

    /**
     * Organizational Group Types
     * level int in OrganizationalGroupType corresponds to this level integer: 1-Institution, 2-Department, 3-Division, 4-Service
     * For example, OrganizationalGroupType with level=1, set this level to 1.
     *
     * @ORM\ManyToOne(targetEntity="OrganizationalGroupType", cascade={"persist"})
     */
    private $organizationalGroupType;

    //Mapped objects: BaseTitle, BuildingList, Location, Training, Logger
    /**
     * @ORM\ManyToMany(targetEntity="BuildingList", mappedBy="institutions")
     **/
    private $buildings;

    /**
     * @ORM\OneToMany(targetEntity="AdministrativeTitle", mappedBy="institution", cascade={"persist"})
     **/
    private $administrativeTitles;


//    /**
//     * @ORM\OneToMany(targetEntity="Collaboration", mappedBy="institution", cascade={"persist"})
//     **/
//    private $collaborations;
    /**
     * @ORM\ManyToMany(targetEntity="Collaboration", mappedBy="institutions")
     * @ORM\JoinTable(name="user_institutions_collaborations")
     **/
    private $collaborations;

    //dummy field not linked to DB
    //private $institutionspositiontypes;

    //May add additional properties of the tree node


    public function __construct() {
        parent::__construct();

        $this->types = new ArrayCollection();

        $this->buildings = new ArrayCollection();
        $this->administrativeTitles = new ArrayCollection();
        $this->collaborations = new ArrayCollection();
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
    public function removeBuilding($item)
    {
        $this->buildings->removeElement($item);
    }

    public function getAdministrativeTitles()
    {
        return $this->administrativeTitles;
    }
    public function addAdministrativeTitle($item)
    {
        if( !$this->administrativeTitles->contains($item) ) {
            $this->administrativeTitles->add($item);
        }

        return $this;
    }
    public function removeAdministrativeTitle($item)
    {
        $this->administrativeTitles->removeElement($item);
    }

    //collaborations
    public function getCollaborations()
    {
        return $this->collaborations;
    }
    public function addCollaboration($item)
    {
        if( !$this->collaborations->contains($item) ) {
            $this->collaborations->add($item);
        }

        return $this;
    }
    public function removeCollaboration($item)
    {
        $this->collaborations->removeElement($item);
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
        if( $parent && count($parent->getChildren()) > 0 ) {
            $firstSiblingOrgGroupType = $parent->getChildren()->first()->getOrganizationalGroupType();
            if( $firstSiblingOrgGroupType ) {
                $this->setOrganizationalGroupType($firstSiblingOrgGroupType);
            }
        }
    }



//    public function getUserPositionsByUseridAndNodeid($user,$institution) {
//        $positionTypes = $this->getUserPositions();
//        $criteria = Criteria::create()
//            ->where( Criteria::expr()->eq("user", $user) )
//            ->andWhere( Criteria::expr()->eq("institution", $institution) )
//        ;
//        $positionTypesFiltered = $positionTypes->matching($criteria);
//
//        return $positionTypesFiltered;
//    }




//    /**
//     * @param mixed $institutionspositiontypes
//     */
//    public function setInstitutionspositiontypes($institutionspositiontypes)
//    {
//        $this->institutionspositiontypes = $institutionspositiontypes;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getInstitutionspositiontypes()
//    {
//        return $this->institutionspositiontypes;
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
        return "Institution";
    }

}