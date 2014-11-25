<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\UserdirectoryBundle\Repository\TreeRepository")
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
     * @ORM\OneToMany(targetEntity="Department", mappedBy="parent", cascade={"persist"})
     */
    protected $departments;

    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->departments = new ArrayCollection();
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