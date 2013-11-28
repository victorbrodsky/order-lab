<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\AccessionRepository")
 * @ORM\Table(name="accession")
 */
class Accession extends OrderAbstract {

    /**
     * Accession Number
     * @ORM\OneToMany(targetEntity="AccessionAccession", mappedBy="accession", cascade={"persist"})
     */
    protected $accession;
    
    ///////////////////////////////////////////
    
    //Accession belongs to exactly one Procedure => Accession has only one Procedure
    /**
     * Parent
     * @ORM\ManyToOne(targetEntity="Procedure", inversedBy="accession")
     * @ORM\JoinColumn(name="procedure_id", referencedColumnName="id")
     */
    protected $procedure;
    
    /**
     * Accession might have many parts (children)
     * @ORM\OneToMany(targetEntity="Part", mappedBy="accession")
     */
    protected $part;
    
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="accession")
     **/
    protected $orderinfo;
      
    public function __construct( $withfields=false, $validity=0 ) {
        parent::__construct();
        $this->part = new ArrayCollection();

        //fields:
        $this->accession = new ArrayCollection();

        if( $withfields ) {
            $this->addAccession( new AccessionAccession($validity) );
        }
    }
      
    public function __toString()
    {
        $accNameStr = "";
        foreach( $this->accession as $accession ) {
            $accNameStr = $accNameStr." ".$accession->getField();
        }
        return "Accession: id=".$this->id.", accessionCount=".count($this->accession).", accessions#=".$accNameStr.", partCount=".count($this->part).", status=".$this->status."<br>";
    }

    /**
     * Set accession
     *
     * @param string $accession
     * @return Accession
     */
    public function setAccession($accession)
    {
        $this->accession = $accession;
    
        return $this;
    }

    /**
     * Get accession
     *
     * @return string 
     */
    public function getAccession()
    {
        return $this->accession;
    }

    public function addAccession(\Oleg\OrderformBundle\Entity\AccessionAccession $accession)
    {
        if( $accession ) {
            if( !$this->accession->contains($accession) ) {
                $accession->setAccession($this);
                $this->accession->add($accession);
                //$this->accession[] = $accession;
            }
        }

        return $this;
    }

    public function removeAccession($accession)
    {
        $this->accession->removeElement($accession);
    }

    public function clearAccession()
    {
        $this->accession->clear();
    }

    public function obtainKeyField()
    {
        return $this->getAccession();
    }

    /**
     * Set procedure (parent)
     *
     * @param \Oleg\OrderformBundle\Entity\Procedure $procedure
     * @return Accession
     */
    public function setProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure = null)
    {
        $this->procedure = $procedure;
    
        return $this;
    }

    //parent
    public function setParent($parent)
    {
        $this->setProcedure($parent);
        return $this;
    }

    public function getParent()
    {
        return $this->getProcedure();
    }

    /**
     * Get procedure
     *
     * @return \Oleg\OrderformBundle\Entity\Procedure
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * Add part (child)
     *
     * @param \Oleg\OrderformBundle\Entity\Part $part
     * @return Accession
     */
    public function addPart(\Oleg\OrderformBundle\Entity\Part $part)
    {
        if( !$this->part->contains($part) ) {
            $part->setAccession($this);
            $this->part[] = $part;
        }

        return $this;
    }

    /**
     * Remove part
     *
     * @param \Oleg\OrderformBundle\Entity\Part $part
     */
    public function removePart(\Oleg\OrderformBundle\Entity\Part $part)
    {
        $this->part->removeElement($part);
    }

    /**
     * Get part
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPart()
    {
        return $this->part;
    }
    public function setPart(\Doctrine\Common\Collections\ArrayCollection $part)
    {
        $this->part = $part;
    }

    public function clearPart(){
        $this->part->clear();
    }

}