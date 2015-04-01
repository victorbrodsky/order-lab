<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\AccessionRepository")
 * @ORM\Table(name="scan_accession")
 */
class Accession extends ObjectAbstract {

    /**
     * Accession Number
     * @ORM\OneToMany(targetEntity="AccessionAccession", mappedBy="accession", cascade={"persist"})
     */
    protected $accession;

    /**
     * @ORM\OneToMany(targetEntity="AccessionAccessionDate", mappedBy="accession", cascade={"persist"})
     */
    protected $accessionDate;

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

//    /**
//     * @ORM\ManyToMany(targetEntity="ResultInfo", mappedBy="accession")
//     **/
//    protected $resultinfo;

    ///////////////// additional extra fields not shown on scan order /////////////////
//    /**
//     * @ORM\OneToMany(targetEntity="AccessionLaborder", mappedBy="accession", cascade={"persist"})
//     */
//    private $laborder;

//    /**
//     * @ORM\OneToMany(targetEntity="AccessionOutsidereport", mappedBy="accession", cascade={"persist"})
//     */
//    private $outsidereport;
    ///////////////// EOF additional extra fields not shown on scan order /////////////////



    public function __construct( $withfields=false, $status='invalid', $provider=null, $source=null ) {
        parent::__construct($status,$provider,$source);
        $this->part = new ArrayCollection();

        //fields:
        $this->accession = new ArrayCollection();
        $this->accessionDate = new ArrayCollection();

        //extra
        //$this->laborder = new ArrayCollection();
        //$this->outsidereport = new ArrayCollection();

        if( $withfields ) {
            $this->addAccession( new AccessionAccession($status,$provider,$source) );
            $this->addAccessionDate( new AccessionAccessionDate($status,$provider,$source) );

            //testing data structure
            //$this->addExtraFields($status,$provider,$source);
        }
    }

    public function makeDependClone() {
        $this->accession = $this->cloneDepend($this->accession,$this);
        $this->accessionDate = $this->cloneDepend($this->accessionDate,$this);

        //extra fields
        //$this->laborder = $this->cloneDepend($this->laborder,$this);
        //$this->outsidereport = $this->cloneDepend($this->outsidereport,$this);
    }

    public function __toString()
    {
        $accNameStr = "";
        foreach( $this->accession as $accession ) {
            $accNameStr = $accNameStr." ".$accession->getField()."(keytype=".$accession->getKeytype().")"."(".$accession->getStatus().")";
        }
        return "Accession: id=".$this->id.
            ", accessionCount=".count($this->accession).
            ", accessions#=".$accNameStr.
            //", parentId=".$this->getParent()->getId().
            ", partCount=".count($this->part).
            ", status=".$this->status."<br>";
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

    public function addAccession( $accession )
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

    public function clearAccession()
    {
        $this->accession->clear();
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
            $this->part->add($part);
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

    /**
     * @param mixed $accessionDate
     */
    public function setAccessionDate($accessionDate)
    {
        $this->accessionDate = $accessionDate;
    }
    /**
     * @return mixed
     */
    public function getAccessionDate()
    {
        return $this->accessionDate;
    }

    public function addAccessionDate($accessionDate)
    {
        if( $accessionDate == null ) {
            $accessionDate = new AccessionAccessionDate();
        }

        if( !$this->accessionDate->contains($accessionDate) ) {
            $accessionDate->setAccession($this);
            $this->accessionDate->add($accessionDate);
        }

        return $this;
    }
    public function removeAccessionDate($accessionDate)
    {
        $this->accessionDate->removeElement($accessionDate);
    }




    ///////////////////////// Extra fields /////////////////////////
    public function addExtraFields($status,$provider,$source) {
        //$this->addLaborder( new AccessionLaborder($status,$provider,$source) );
        //$this->addOutsidereport( new AccessionOutsidereport($status,$provider,$source) );
    }



//    public function getLaborder()
//    {
//        return $this->laborder;
//    }
//    public function addLaborder($laborder)
//    {
//        if( $laborder && !$this->laborder->contains($laborder) ) {
//            $this->laborder->add($laborder);
//            $laborder->setAccession($this);
//        }
//
//        return $this;
//    }
//    public function removeLaborder($laborder)
//    {
//        $this->laborder->removeElement($laborder);
//    }

//    public function getOutsidereport()
//    {
//        return $this->outsidereport;
//    }
//    public function addOutsidereport($outsidereport)
//    {
//        if( $outsidereport && !$this->outsidereport->contains($outsidereport) ) {
//            $this->outsidereport->add($outsidereport);
//            $outsidereport->setAccession($this);
//        }
//
//        return $this;
//    }
//    public function removeOutsidereport($outsidereport)
//    {
//        $this->outsidereport->removeElement($outsidereport);
//    }
    ///////////////////////// EOF Extra fields /////////////////////////



    //parent, children, key field methods
    public function setParent($parent) {
        $this->setProcedure($parent);
        return $this;
    }

    public function getParent() {
        return $this->getProcedure();
    }

    public function getChildren() {
        return $this->getPart();
    }

    public function addChildren($child) {
        $this->addPart($child);
    }

    public function removeChildren($child) {
        $this->removePart($child);
    }
    
    public function setChildren($children) {
        $this->setPart($children);
    }

    //don't use 'get' because later repo functions relay on "get" keyword

    //object info for blue strip
    public function obtainFullObjectName() {

        $fullNameArr = array();

        //accession
        $key = $this->obtainValidField('accession');
        if( $key ) {
            if( $key->getKeytype() ) {
                $fullNameArr[] = $key->getKeytype()->getOptimalName() . " - " . $key->getField();
            } else {
                $fullNameArr[] = $key->getField();
            }
        }

        $fullName = implode(", ",$fullNameArr);

        return $fullName;
    }


    public function obtainKeyField() {
        return $this->getAccession();
    }

//    public function obtainExtraKey() {
//        $extra = array();
//        $extra['keytype'] = $this->getAccession()->getKeytype()->getId();
//        return $extra;
//    }

    public function obtainKeyFieldName() {
        return "accession";
    }

    public function createKeyField() {
        //echo "creating a new keyfield <br>";
        $this->addAccession( new AccessionAccession() );
        return $this->obtainKeyField();
    }

    public function filterArrayFields( $user, $strict = false ) {

        parent::filterArrayFields($user,$strict);
        $this->getProcedure()->filterArrayFields($user,$strict);
        return $this;

    }

    public function getArrayFields() {
        $fieldsArr = array(
            'Accession', 'AccessionDate',
            //extra fields
            //'Laborder', 'Outsidereport'
        );
        return $fieldsArr;
    }

}