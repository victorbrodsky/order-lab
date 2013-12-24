<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="statusgroup")
 */
class StatusGroup
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Status", mappedBy="group")
     */
    protected $status;



    public function __toString()
    {
        return $this->name;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return StatusGroup
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add status
     *
     * @param \Oleg\OrderformBundle\Entity\Status $status
     * @return StatusGroup
     */
    public function addStatus(\Oleg\OrderformBundle\Entity\Status $status)
    {
        $this->status[] = $status;
    
        return $this;
    }

    /**
     * Remove status
     *
     * @param \Oleg\OrderformBundle\Entity\Status $status
     */
    public function removeStatus(\Oleg\OrderformBundle\Entity\Status $status)
    {
        $this->status->removeElement($status);
    }

    /**
     * Get status
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStatus()
    {
        return $this->status;
    }
}