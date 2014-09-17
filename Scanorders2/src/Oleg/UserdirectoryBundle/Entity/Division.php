<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    protected $parent;

    /**
     * Children
     * @ORM\OneToMany(targetEntity="Service", mappedBy="parent", cascade={"persist"})
     */
    protected $services;




    public function __construct() {
        $this->synonyms = new ArrayCollection();
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