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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\UserdirectoryBundle\Entity\BaseCompositeNode;
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
 *  name="scan_projectTitleTree",
 *  indexes={
 *      @ORM\Index( name="projectTitleTree_name_idx", columns={"name"} ),
 *  }
 * )
 */
class ProjectTitleTree extends BaseCompositeNode {

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ProjectTitleTree", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="ProjectTitleTree", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     **/
    protected $children;

    /**
     * Organizational Group Types
     * level int in OrganizationalGroupType corresponds to this level integer: 1-Research Project Title, 2-Research Set Title
     * For example, OrganizationalGroupType with level=1, set this level to 1.
     *
     * @ORM\ManyToOne(targetEntity="ResearchGroupType", cascade={"persist"})
     */
    private $organizationalGroupType;

    /**
     * @ORM\OneToMany(targetEntity="ProjectTitleTree", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ProjectTitleTree", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;


    /**
     * @ORM\OneToMany(targetEntity="Research", mappedBy="projectTitle", cascade={"persist"})
     **/
    private $researches;

    /**
     * keep copy of all users: userWrappers in Research are subset of this userWrappers
     *
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_projectTitleTree_userWrapper",
     *      joinColumns={@ORM\JoinColumn(name="projectTitleTree_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userWrapper_id", referencedColumnName="id")}
     *      )
     **/
    private $userWrappers;


    public function __construct() {
        parent::__construct();

        $this->researches = new ArrayCollection();
        $this->userWrappers = new ArrayCollection();
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


    public function getResearches()
    {
        return $this->researches;
    }
    public function addResearch($item)
    {
        if( !$this->researches->contains($item) ) {
            $this->researches->add($item);
        }

        return $this;
    }
    public function removeResearch($item)
    {
        $this->researches->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getUserWrappers()
    {
        return $this->userWrappers;
    }

    /**
     * Add userWrappers
     *
     * @param $userWrappers
     * @return Research
     */
    public function addUserWrapper($userWrapper)
    {
        if( !$this->userWrappers->contains($userWrapper) ) {
            $this->userWrappers->add($userWrapper);
        }

        return $this;
    }

    /**
     * Remove userWrappers
     *
     * @param userWrappers $userWrappers
     */
    public function removeUserWrapper($userWrappers)
    {
        $this->userWrappers->removeElement($userWrappers);
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

    //return string to match with name of this class instance in the holder (here, Research) object. Used by complex tree
    public function getClassName()
    {
        return "researchprojecttitle";  //"ProjectTitle";
    }
}
