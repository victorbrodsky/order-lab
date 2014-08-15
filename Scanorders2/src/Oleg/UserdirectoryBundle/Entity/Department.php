<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="department")
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
     * @ORM\OneToMany(targetEntity="OrderInfo", mappedBy="department")
     */
    protected $orderinfo;

    /**
     * @ORM\ManyToOne(targetEntity="Institution", inversedBy="departments")
     * @ORM\JoinColumn(name="institution", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $institution;

    /**
     * @ORM\OneToMany(targetEntity="Division", mappedBy="department", cascade={"persist"})
     */
    protected $divisions;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="department")
     **/
    protected $users;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->orderinfo = new ArrayCollection();
        $this->divisions = new ArrayCollection();
        $this->users = new ArrayCollection();
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
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }



    /**
     * Add orderinfo
     *
     * @param \Oleg\UserdirectoryBundle\Entity\OrderInfo $orderinfo
     * @return Department
     */
    public function addOrderinfo(\Oleg\UserdirectoryBundle\Entity\OrderInfo $orderinfo)
    {
        //echo "Department addOrderinfo=".$orderinfo."<br>";
        if( !$this->orderinfo->contains($orderinfo) ) {
            $this->orderinfo->add($orderinfo);
        }
    }

    /**
     * Remove orderinfo
     *
     * @param \Oleg\UserdirectoryBundle\Entity\OrderInfo $orderinfo
     */
    public function removeOrderinfo(\Oleg\UserdirectoryBundle\Entity\OrderInfo $orderinfo)
    {
        $this->orderinfo->removeElement($orderinfo);
    }

    /**
     * Get orderinfo
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
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
            $division->setDepartment($this);
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

    /**
     * Add user
     *
     * @param \Oleg\UserdirectoryBundle\Entity\User $user
     * @return Department
     */
    public function addUser(\Oleg\UserdirectoryBundle\Entity\User $user)
    {
        if( !$this->users->contains($user) ) {
            $this->users->add($user);
        }
    }
    /**
     * Remove user
     *
     * @param \Oleg\UserdirectoryBundle\Entity\User $user
     */
    public function removeUser(\Oleg\UserdirectoryBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }
    /**
     * Get order
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }



}