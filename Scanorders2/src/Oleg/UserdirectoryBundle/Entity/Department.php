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



    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->divisions = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Department $synonyms
     * @return Department
     */
    public function addSynonym(\Oleg\UserdirectoryBundle\Entity\Department $synonyms)
    {
        $this->synonyms[] = $synonyms;
    
        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Department $synonyms
     */
    public function removeSynonym(\Oleg\UserdirectoryBundle\Entity\Department $synonyms)
    {
        $this->synonyms->removeElement($synonyms);
    }

    /**
     * Get synonyms
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * Set original
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Department $original
     * @return Department
     */
    public function setOriginal(\Oleg\UserdirectoryBundle\Entity\Department $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    /**
     * Get original
     *
     * @return \Oleg\UserdirectoryBundle\Entity\Department
     */
    public function getOriginal()
    {
        return $this->original;
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



}