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


//This list has a link to the accession list (i.e. Accessions for Follow-Up Lists) via entityNamespace, entityName, entityId

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
 *  name="scan_accessionlisthierarchy"
 * )
 */
class AccessionListHierarchy extends BaseCompositeNode {

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="AccessionListHierarchy", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="AccessionListHierarchy", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     **/
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="AccessionListHierarchy", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="AccessionListHierarchy", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;



    /**
     * Organizational Group Types - mapper between the level number and level title.
     * For example, OrganizationalGroupType with level=1, set this level to 1.
     * Default types have a positive level numbers, all other types have negative level numbers.
     *
     * @ORM\ManyToOne(targetEntity="AccessionListHierarchyGroupType", cascade={"persist"})
     */
    private $organizationalGroupType;

    /**
     * @ORM\ManyToMany(targetEntity="CalllogEntryMessage", mappedBy="accessionLists", cascade={"persist"})
     **/
    private $messages;

    /**
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\Accession", cascade={"persist"})
     */
    private $accession;

    

    public function __construct() {
        parent::__construct();

        $this->messages = new ArrayCollection();
    }



    /**
     * @return mixed
     */
    public function getOrganizationalGroupType()
    {
        return $this->organizationalGroupType;
    }

    /**
     * @param mixed $organizationalGroupType
     */
    public function setOrganizationalGroupType($organizationalGroupType)
    {
        $this->organizationalGroupType = $organizationalGroupType;
    }




    public function addMessage($item)
    {
        if( $item && !$this->messages->contains($item) ) {
            $this->messages->add($item);
        }
        return $this;
    }
    public function removeMessage($item)
    {
        $this->messages->removeElement($item);
    }
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return mixed
     */
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * @param mixed $accession
     */
    public function setAccession($accession)
    {
        $this->accession = $accession;
    }





    public function getClassName() {
        return "AccessionListHierarchy";
    }




    public function __toString() {
        $parentName = "";
        if( $this->getParent() ) {
            $parentName = ", parent=".$this->getParent()->getName();
        }
        $accessionName = "";
        if( $this->getAccession() ) {
            $accessionName = ", accession=".$this->getAccession()->obtainFullValidKeyName();
        }
        return "Accession List: ".$this->getName().", level=".$this->getLevel().", orderinlist=".$this->getOrderinlist().$parentName.$accessionName;
    }




}