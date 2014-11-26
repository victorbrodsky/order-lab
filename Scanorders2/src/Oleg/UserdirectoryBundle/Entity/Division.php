<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_division")
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
    protected $parent;

    /**
     * Children
     * @ORM\OneToMany(targetEntity="Service", mappedBy="parent", cascade={"persist"})
     */
    protected $services;


    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_division_head")
     **/
    private $heads;


    public function __construct() {
        $this->services = new ArrayCollection();
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
     * Add service
     *
     * @param \Oleg\UserdirectoryBundle\Entity\Service $service
     * @return
     */
    public function addService(\Oleg\UserdirectoryBundle\Entity\Service $service)
    {
        if( !$this->services->contains($service) ) {
            $service->setParent($this);
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

    public function getParentName()
    {
        return "Department";
    }
    public function getClassName()
    {
        return "Division";
    }

}