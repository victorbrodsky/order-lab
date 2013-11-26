<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Procedure
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ProcedureRepository")
 * @ORM\Table(name="procedures")
 */
class Procedure extends OrderAbstract
{
    
//    /**
//     * @ORM\Id
//     * @ORM\Column(type="integer")
//     * @ORM\GeneratedValue(strategy="AUTO")
//     */
//    protected $id;

//    /**
//     * @ORM\ManyToOne(targetEntity="ProcedureList", inversedBy="procedure", cascade={"persist"})
//     * @ORM\JoinColumn(name="procedurelist_id", referencedColumnName="id", nullable=true)
//     */
//    protected $proceduretype;
    /**
     * @ORM\OneToMany(targetEntity="ProcedureName", mappedBy="procedure", cascade={"persist"})
     */
    protected $name;
    
    /**
     * parent
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="procedure")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id")
     */
    protected $patient; 
    
    /**
     * Procedure might have many Accession (children)
     * 
     * @ORM\OneToMany(targetEntity="Accession", mappedBy="procedure")
     */
    protected $accession;
    
    
    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="procedure")
     **/
    protected $orderinfo; 

    public function __construct( $withfields=false, $validity=0 ) {
        parent::__construct();
        $this->accession = new ArrayCollection();
        //$this->orderinfo = new ArrayCollection();

        //fields:
        $this->name = new \Doctrine\Common\Collections\ArrayCollection();

        if( $withfields ) {
            $this->addName( new ProcedureName($validity) );
        }
    }   

    /**
     * Get id
     *
     * @return integer 
     */
//    public function getId()
//    {
//        return $this->id;
//    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function addName($name)
    {
        if( $name ) {
            if( !$this->name->contains($name) ) {
                $name->setProcedure($this);
                $this->name->add($name);
            }
        }

        return $this;
    }

    public function removeName($name)
    {
        $this->name->removeElement($name);
    }

    public function clearName()
    {
        $this->name->clear();
    }

    /**
     * Add accession
     *
     * @param \Oleg\OrderformBundle\Entity\Accession $accession
     * @return Procedure
     */
    public function addAccession(\Oleg\OrderformBundle\Entity\Accession $accession)
    {
        if( !$this->accession->contains($accession) ) {
            $accession->setProcedure($this);
            $this->accession[] = $accession;
        }
    
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
    public function setAccession(\Doctrine\Common\Collections\ArrayCollection $accession)
    {
        $this->accession = $accession;
    }

    /**
     * Set patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     * @return Procedure
     */
    public function setPatient(\Oleg\OrderformBundle\Entity\Patient $patient = null)
    {
        $this->patient = $patient;
    
        return $this;
    }

    /**
     * Get patient
     *
     * @return \Oleg\OrderformBundle\Entity\Patient 
     */
    public function getPatient()
    {
        return $this->patient;
    }

    public function clearAccession(){
//        foreach( $this->accession as $thisaccession ) {
//            $this->removeAccession($thisaccession);
//        }
        $this->accession->clear();
    }

    public function __toString() {
        return 'Procedure: id=' . $this->getId() . "<br>";
    }


//    /**
//     * Add orderinfo
//     *
//     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
//     * @return Procedure
//     */
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