<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="accessiontype")
 */
class AccessionType
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=500)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     */
    protected $creator;


    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="datetime")
     * @Assert\NotBlank
     */
    protected $createdate;


    /**
     * @ORM\OneToMany(targetEntity="AccessionAccession", mappedBy="accessiontype")
     */
    protected $accessionaccession;


    public function __construct() {
        $this->accessionaccession = new ArrayCollection();
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
     * @return OrganList
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
     * Set type
     *
     * @param string $type
     * @return OrganList
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Set createdate
     *
     * @param \DateTime $createdate
     * @return OrganList
     */
    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;
    
        return $this;
    }

    /**
     * Get createdate
     *
     * @return \DateTime 
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * Set creator
     *
     * @param string $creator
     * @return OrganList
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    
        return $this;
    }

    /**
     * Get creator
     *
     * @return string 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    public function addAccessionaccession(\Oleg\OrderformBundle\Entity\AccessionAccession $accessionaccession)
    {
        if( !$this->accessionaccession->contains($accessionaccession) ) {
            $this->accessionaccession->add($accessionaccession);
        }
        return $this;
    }

    public function removeAccessionaccession(\Oleg\OrderformBundle\Entity\AccessionAccession $accessionaccession)
    {
        $this->accessionaccession->removeElement($accessionaccession);
    }

    public function getAccessionaccession()
    {
        return $this->accessionaccession;
    }

    public function __toString()
    {
        return $this->name;
    }
}