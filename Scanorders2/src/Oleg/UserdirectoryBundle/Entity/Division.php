<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="division")
 */
class Division extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="Division", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Division", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;

    /**
     * Parent
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="divisions")
     * @ORM\JoinColumn(name="department", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $department;

    /**
     * Children
     * @ORM\OneToMany(targetEntity="Service", mappedBy="division", cascade={"persist"})
     */
    protected $services;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="division")
     **/
    protected $users;




    public function __construct() {
        $this->synonyms = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Division $synonyms
     * @return Division
     */
    public function addSynonym(\Oleg\UserdirectoryBundle\Entity\Division $synonyms)
    {
        $this->synonyms->add($synonyms);
        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Division $synonyms
     */
    public function removeSynonym(\Oleg\UserdirectoryBundle\Entity\Division $synonyms)
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
     * @param \Oleg\UserdirectoryBundle\Entity\Division $original
     * @return Division
     */
    public function setOriginal(\Oleg\UserdirectoryBundle\Entity\Division $original = null)
    {
        $this->original = $original;
    
        return $this;
    }

    /**
     * Get original
     *
     * @return \Oleg\UserdirectoryBundle\Entity\Division
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @param mixed $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
    }



    /**
     * Add user
     *
     * @param \Oleg\UserdirectoryBundle\Entity\User $user
     * @return
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
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }


    /**
     * Add service
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Service $service
     * @return
     */
    public function addService(\Oleg\UserdirectoryBundle\Entity\Service $service)
    {
        if( !$this->services->contains($service) ) {
            $service->setDivision($this);
            $this->services->add($service);
        }
    }

    /**
     * Remove service
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Service $service
     */
    public function removeService(\Oleg\UserdirectoryBundle\Entity\Service $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServices()
    {
        return $this->services;
    }


}