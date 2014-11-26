<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_department")
 */
class Department extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="departments")
     * @ORM\JoinColumn(name="institution", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Division", mappedBy="parent", cascade={"persist"})
     */
    protected $divisions;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_department_head")
     **/
    private $heads;


    public function __construct() {
        $this->divisions = new ArrayCollection();
        $this->heads = new ArrayCollection();
        parent::__construct();
    }


    public function addHead($head)
    {
        if( !$this->heads->contains($head) ) {
            $this->heads->add($head);
        }
        return $this;
    }

    public function removeHead($head)
    {
        $this->heads->removeElement($head);
    }

    public function getHeads()
    {
        return $this->heads;
    }


    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }


    /**
     * Add division
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Division $division
     * @return Department
     */
    public function addDivision(\Oleg\UserdirectoryBundle\Entity\Division $division)
    {
        if( !$this->divisions->contains($division) ) {
            $division->setParent($this);
            $this->divisions->add($division);
        }
    }

    /**
     * Remove division
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Division $division
     */
    public function removeDivision(\Oleg\UserdirectoryBundle\Entity\Division $division)
    {
        $this->divisions->removeElement($division);
    }

    /**
     * Get division
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDivisions()
    {
        return $this->divisions;
    }

    public function getParentName()
    {
        return "Institution";
    }

    public function getClassName()
    {
        return "Department";
    }

}