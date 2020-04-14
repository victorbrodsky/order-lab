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


//This list has a link to the patient list (i.e. PathologyCallComplexPatients) via entityNamespace, entityName, entityId

//      name="scan_patientListHierarchy",
//*     indexes={
//*         @ORM\Index( name="patientListHierarchy_name_idx", columns={"name"} ),
// *    }

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
     * @ORM\ManyToOne(targetEntity="PatientListHierarchyGroupType", cascade={"persist"})
     */
    private $organizationalGroupType;

    /**
     * @ORM\ManyToMany(targetEntity="CalllogEntryMessage", mappedBy="patientLists", cascade={"persist"})
     **/
    private $calllogEntryMessages;

//    /**
//     * @ORM\ManyToMany(targetEntity="CrnEntryMessage", mappedBy="patientLists", cascade={"persist"})
//     **/
//    private $crnEntryMessages;

    /**
     * @ORM\ManyToOne(targetEntity="App\OrderformBundle\Entity\Patient", cascade={"persist"})
     */
    private $patient;



    

    public function __construct() {
        parent::__construct();

        $this->calllogEntryMessages = new ArrayCollection();
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


    /**
     * @return mixed
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param mixed $patient
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
    }


    public function addCalllogEntryMessage($item)
    {
        if( $item && !$this->calllogEntryMessages->contains($item) ) {
            $this->calllogEntryMessages->add($item);
        }
        return $this;
    }
    public function removeCalllogEntryMessage($item)
    {
        $this->calllogEntryMessages->removeElement($item);
    }
    public function getCalllogEntryMessages()
    {
        return $this->calllogEntryMessages;
    }

//    /**
//     * @return mixed
//     */
//    public function getCrnEntryMessages()
//    {
//        return $this->crnEntryMessages;
//    }
//
//    /**
//     * @param mixed $crnEntryMessages
//     */
//    public function setCrnEntryMessages($crnEntryMessages)
//    {
//        $this->crnEntryMessages = $crnEntryMessages;
//    }




    public function getClassName() {
        return "AccessionListHierarchy";
    }




    public function __toString() {
        $parentName = "";
        if( $this->getParent() ) {
            $parentName = ", parent=".$this->getParent()->getName();
        }
        $patientName = "";
        if( $this->getPatient() ) {
            $patientName = ", patient=".$this->getPatient()->obtainPatientInfoTitle();
        }
        return "Patient List: ".$this->getName().", level=".$this->getLevel().", orderinlist=".$this->getOrderinlist().$parentName.$patientName;
    }




}