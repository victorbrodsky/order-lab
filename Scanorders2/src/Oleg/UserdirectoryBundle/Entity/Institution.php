<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *  name="user_institution",
 *  indexes={
 *      @ORM\Index( name="name_idx", columns={"name"} ),
 *  }
 * )
 */
class Institution extends ListAbstract
{
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $abbreviation;

    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="parent", cascade={"persist"})
     */
    protected $departments;

    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->departments = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Institution $synonyms
     * @return Institution
     */
    public function addSynonym(\Oleg\UserdirectoryBundle\Entity\Institution $synonyms)
    {
        $this->synonyms[] = $synonyms;
    
        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Institution $synonyms
     */
    public function removeSynonym(\Oleg\UserdirectoryBundle\Entity\Institution $synonyms)
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
     * @param \Oleg\UserdirectoryBundle\Entity\Institution $original
     * @return Institution
     */
    public function setOriginal(\Oleg\UserdirectoryBundle\Entity\Institution $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    /**
     * Get original
     *
     * @return \Oleg\UserdirectoryBundle\Entity\Institution
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Add department
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
     * @return Institution
     */
    public function addDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
    {
        if( !$this->departments->contains($department) ) {
            $department->setParent($this);
            $this->departments->add($department);
        }
    }
    /**
     * Remove department
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Department $department
     */
    public function removeDepartment(\Oleg\UserdirectoryBundle\Entity\Department $department)
    {
        $this->departments->removeElement($department);
    }
    /**
     * Get order
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * @param mixed $abbreviation
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    /**
     * @return mixed
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }



}