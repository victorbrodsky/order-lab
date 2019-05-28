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
     * Organizational Group Types - mapper between the level number and level title.
     * level int in OrganizationalGroupType corresponds to this level integer: 1-Institution, 2-Department, 3-Division, 4-Service
     * For example, OrganizationalGroupType with level=1, set this level to 1.
     * Default types have a positive level numbers, all other types have negative level numbers.
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
//    /**
//     * @ORM\ManyToMany(targetEntity="Institution", mappedBy="collaborations")
//     * @ORM\JoinTable(name="user_institutions_collaborations")
//     **/
//    private $collaborationInstitutions;

    //Collaboration
//    /**
//     * @ORM\ManyToMany(targetEntity="Institution", mappedBy="collaborations")
//     */
//    private $collaborationInstitutions;
//
//    /**
//     * @ORM\ManyToMany(targetEntity="Institution", inversedBy="collaborationInstitutions")
//     * @ORM\JoinTable(name="user_collaboration_institution",
//     *      joinColumns={@ORM\JoinColumn(name="collaboration_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="institution_id", referencedColumnName="id")}
//     *      )
//     */
//    private $collaborations;
    /**
     * Mapped reference to the Collaboration node
     *
     * @ORM\ManyToMany(targetEntity="Institution", mappedBy="collaborationInstitutions")
     */
    private $collaborations;

    /**
     * Collaboration Institutions under this Collaboration node
     *
     * @ORM\ManyToMany(targetEntity="Institution", inversedBy="collaborations")
     * @ORM\JoinTable(name="user_collaborationInstitution_collaboration",
     *      joinColumns={@ORM\JoinColumn(name="collaborationInstitution_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="collaboration_id", referencedColumnName="id")}
     *      )
     */
    private $collaborationInstitutions;

    /**
     * @ORM\ManyToOne(targetEntity="CollaborationTypeList")
     * @ORM\JoinColumn(name="collaborationType_id", referencedColumnName="id", nullable=true)
     */
    private $collaborationType;


    //dummy field not linked to DB
    //private $institutionspositiontypes;

    //May add additional properties of the tree node


    public function __construct() {
        parent::__construct();

        $this->types = new ArrayCollection();

        $this->buildings = new ArrayCollection();
        $this->administrativeTitles = new ArrayCollection();

        $this->collaborationInstitutions = new ArrayCollection();
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
    //collaborationInstitutions
    public function getCollaborationInstitutions()
    {
        //fix for: Expected value of type \"Doctrine\\Common\\Collections\\Collection|array\" for association
        // field \"Oleg\\UserdirectoryBundle\\Entity\\Institution#$collaborationInstitutions\", got \"NULL\" instead.
        if( $this->collaborationInstitutions == NULL ) {
            $this->collaborationInstitutions = new ArrayCollection();
        }
        return $this->collaborationInstitutions;
    }
    public function addCollaborationInstitution($item)
    {
        if( !$this->collaborationInstitutions->contains($item) ) {
            $this->collaborationInstitutions->add($item);
        }

        return $this;
    }
    public function removeCollaborationInstitution($item)
    {
        $this->collaborationInstitutions->removeElement($item);
    }

    /**
     * @param mixed $collaborationType
     */
    public function setCollaborationType($collaborationType)
    {
        $this->collaborationType = $collaborationType;
    }

    /**
     * @return mixed
     */
    public function getCollaborationType()
    {
        return $this->collaborationType;
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

        //change organizationalGroupType of this entity to the first child organizationalGroupType of the parent if does not exist
        if( !$this->getOrganizationalGroupType() ) {
            if ($parent && count($parent->getChildren()) > 0) {
                //$firstSiblingOrgGroupType = $parent->getChildren()->first()->getOrganizationalGroupType();
                //$this->setOrganizationalGroupType($firstSiblingOrgGroupType);
                $defaultChild = $this->getFirstDefaultChild($parent);
                $defaultSiblingOrgGroupType = $defaultChild->getOrganizationalGroupType();
                if( $defaultSiblingOrgGroupType ) {
                    $this->setOrganizationalGroupType($defaultSiblingOrgGroupType);
                }
            }
        }
    }

    public function hasInstitutionType($typeStr) {
        foreach( $this->getTypes() as $type ) {
            if( $type->getName()."" == $typeStr ) {
                return true;
            }
        }
        return false;
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


    public function getNameShortName() {
        $name = $this->getName();
        $shortName = $this->getShortname();
        if( $shortName ) {
            $name = $name . " (" . $shortName . ")";
        }
        return $name;
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