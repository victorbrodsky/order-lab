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
    
//    /**
//     * @ORM\Id
//     * @ORM\Column(type="integer")
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    protected $id;

    /**
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
        //$this->orderinfo = new ArrayCollection();

        //fields:
        $this->accession = new \Doctrine\Common\Collections\ArrayCollection();

        if( $withfields ) {
            $this->addAccession( new AccessionAccession($validity) );
        }
    }
      
    public function __toString()
    {
        return "Accession: id=".$this->id.", accessionCount".count($this->accession).", partCount=".count($this->part)."<br>";
    }

//    /**
//     * Get id
//     *
//     * @return integer
//     */
//    public function getId()
//    {
//        return $this->id;
//    }

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

    public function addAccession($accession)
    {
        if( $accession ) {
            if( !$this->accession->contains($accession) ) {
                $accession->setAccession($this);
                $this->accession->add($accession);
            }
        }

        return $this;
    }

    public function removeAccession($accession)
    {
        $this->accession->removeElement($accession);
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
        foreach( $this->part as $thispart ) {
            $this->removePart($thispart);
        }
    }


    /**
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Accession
     */
//    public function addOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
//    {
//        if( !$this->orderinfo->contains($orderinfo) ) {
//            $this->orderinfo->add($orderinfo);
//        }
//    }
//
//    /**
//     * Remove orderinfo
//     *
//     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
//     */
//    public function removeOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
//    {
//        $this->orderinfo->removeElement($orderinfo);
//    }
//
//    /**
//     * Get orderinfo
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getOrderinfo()
//    {
//        return $this->orderinfo;
//    }
}