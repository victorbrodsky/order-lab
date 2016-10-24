<?php

namespace Oleg\OrderformBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oleg\UserdirectoryBundle\Entity\BaseCompositeNode;
use Oleg\UserdirectoryBundle\Entity\ComponentCategoryInterface;
use Oleg\UserdirectoryBundle\Entity\CompositeNodeInterface;
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
 *  name="scan_patientLists",
 *  indexes={
 *      @ORM\Index( name="patientListHierarchy_name_idx", columns={"name"} ),
 *  }
 * )
 */
class PatientListHierarchy extends BaseCompositeNode {

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="PatientListHierarchy", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="PatientListHierarchy", mappedBy="parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     **/
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="PatientListHierarchy", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="PatientListHierarchy", inversedBy="synonyms", cascade={"persist"})
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
     * @ORM\OneToMany(targetEntity="PatientList", mappedBy="patientListHierarchy", cascade={"persist","remove"})
     **/
    private $patientLists;
//    /**
//     * @ORM\ManyToMany(targetEntity="PatientList", mappedBy="patientListHierarchy")
//     **/
//    private $patientLists;


    public function __construct() {
        parent::__construct();

        $this->patientLists = new ArrayCollection();
    }



    public function getPatientLists()
    {
        return $this->patientLists;
    }
    public function addPatientList($item)
    {
        if( !$this->patientLists->contains($item) ) {
            $this->patientLists->add($item);
        }

        return $this;
    }
    public function removePatientList($item)
    {
        $this->patientLists->removeElement($item);
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





    public function getClassName() {
        return "PatientListHierarchy";
    }




    public function __toString() {
        $parentName = "";
        if( $this->getParent() ) {
            $parentName = ", parent=".$this->getParent()->getName();
        }
        return "Patient List: ".$this->getName().", level=".$this->getLevel().", orderinlist=".$this->getOrderinlist().$parentName;
    }




}