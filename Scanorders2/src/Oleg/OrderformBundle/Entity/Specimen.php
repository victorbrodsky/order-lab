<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Specimen or Case or Procedure
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\SpecimenRepository")
 * @ORM\Table(name="specimen")
 */
class Specimen
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="proceduretype", type="string", nullable=true, length=300)   
     */
    protected $proceduretype;   
    
    /**
     * Link to a paper or abstract file
     * @ORM\Column(name="paper", type="string", nullable=true, length=300)
     */
    protected $paper;
    
    /**
     * mrn - unique patient number which link to external copath db. 
     * 
     * @ORM\Column(name="mrn", type="string", nullable=true, length=100)
     */
    protected $mrn;
    
    /**
     * Specimen might have many Accession
     * 
     * @ORM\OneToMany(targetEntity="Accession", mappedBy="specimen")
     */
    protected $accession;

    public function __construct() {
        $this->accession = new ArrayCollection();
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

    public function getProceduretype() {
        return $this->proceduretype;
    }

    public function setProceduretype($proceduretype) {
        $this->proceduretype = $proceduretype;
    }

    /**
     * Set paper
     *
     * @param string $paper
     * @return Specimen
     */
    public function setPaper($paper)
    {
        $this->paper = $paper;
    
        return $this;
    }

    /**
     * Get paper
     *
     * @return string 
     */
    public function getPaper()
    {
        return $this->paper;
    }

    /**
     * Set mrn
     *
     * @param string $mrn
     * @return Specimen
     */
    public function setMrn($mrn)
    {
        $this->mrn = $mrn;
    
        return $this;
    }

    /**
     * Get mrn
     *
     * @return string 
     */
    public function getMrn()
    {
        return $this->mrn;
    }

    /**
     * Add accession
     *
     * @param \Oleg\OrderformBundle\Entity\Accession $accession
     * @return Specimen
     */
    public function addAccession(\Oleg\OrderformBundle\Entity\Accession $accession)
    {
        $this->accession[] = $accession;
    
        return $this;
    }

    /**
     * Remove accession
     *
     * @param \Oleg\OrderformBundle\Entity\Accession $accession
     */
    public function removeAccession(\Oleg\OrderformBundle\Entity\Accession $accession)
    {
        $this->accession->removeElement($accession);
    }

    /**
     * Get accession
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccession()
    {
        return $this->accession;
    }
}