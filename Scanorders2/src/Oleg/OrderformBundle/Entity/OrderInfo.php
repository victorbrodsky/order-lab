<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\OrderInfoRepository")
 * @ORM\Table(name="scan_orderinfo",
 *  indexes={
 *      @ORM\Index( name="oid_idx", columns={"oid"} )
 *  }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class OrderInfo extends OrderAbstract {

    /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="orderinfo", cascade={"persist"})
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    private $status;

    /**
     * oid - id of the original order.
     * When Amend order, switch orders to keep the original id and at newly created order set oid of the original order
     * @var string
     * @ORM\Column(type="integer", nullable=true)
     */
    private $oid;

    /**
     * Account of the order
     *
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="orderinfo", cascade={"persist"})
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true)
     */
    private $account;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Educational",
     *      inversedBy="orderinfo",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *      name="educational_id",
     *      referencedColumnName="id"
     * )
     */
    private $educational;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Research",
     *      inversedBy="orderinfo",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *      name="research_id",
     *      referencedColumnName="id"
     * )
     */
    private $research;

//    //private $scanner;
//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Equipment")
//     */
//    private $equipment;

    /**
     * History of the order
     *
     * @ORM\ManyToMany(targetEntity="History")
     * @ORM\JoinTable(name="scan_orderinfo_history",
     *      joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="history_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $history;


    //    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Service")
//     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=true)
//     */
//    private $service;

//    /**
//     * @ORM\Column(type="string",nullable=true)
//     */
//    private $priority;

    //private $scandeadline;
//    /**
//     * @ORM\Column(type="datetime", nullable=true)
//     */
//    private $deadline;

    //Return slide(s) by this date even if not scanned
//    /**
//     * @ORM\Column(name="returnoption", type="boolean")
//     */
//    private $returnoption;

    //private $slideDelivery;
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $delivery;

//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
//     * @ORM\JoinColumn(name="returnSlide", referencedColumnName="id", nullable=true)
//     * @Assert\NotBlank
//     **/
//    private $returnSlide;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    protected $purpose;

//    /**
//     * Conflicting accession number is replaced, so keep the reference to dataqualitymrnacc object in the orderinfo (unlike to dataqualityage)
//     *
//     * @ORM\OneToMany(targetEntity="DataQualityMrnAcc", mappedBy="orderinfo", cascade={"persist"})
//     */
//    private $dataqualitymrnacc;

//    /**
//     * @ORM\OneToMany(targetEntity="History", mappedBy="orderinfo", cascade={"persist"})
//     */
//    private $history;




    /////////////////    OBJECTS    //////////////////////
    /**
     * @ORM\ManyToMany(targetEntity="Patient", inversedBy="orderinfo" )
     * @ORM\JoinTable(name="scan_patient_orderinfo")
     **/
    private $patient;

    /**
     * @ORM\ManyToMany(targetEntity="Encounter", inversedBy="orderinfo")
     * @ORM\JoinTable(name="scan_encounter_orderinfo")
     **/
    private $encounter;

    /**
     * @ORM\ManyToMany(targetEntity="Procedure", inversedBy="orderinfo")
     * @ORM\JoinTable(name="scan_procedure_orderinfo")
     **/
    private $procedure;
    
    /**
     * @ORM\ManyToMany(targetEntity="Accession", inversedBy="orderinfo")
     * @ORM\JoinTable(name="scan_accession_orderinfo")
     **/
    private $accession;
    
    /**
     * @ORM\ManyToMany(targetEntity="Part", inversedBy="orderinfo")
     * @ORM\JoinTable(name="scan_part_orderinfo")
     **/
    private $part;
    
    /**
     * @ORM\ManyToMany(targetEntity="Block", inversedBy="orderinfo")
     * @ORM\JoinTable(name="scan_block_orderinfo")
     **/
    private $block;
    
    /**
     * @ORM\ManyToMany(targetEntity="Slide", inversedBy="orderinfo")
     * @ORM\JoinTable(name="scan_slide_orderinfo")
     **/
    private $slide;





    ////////////////////////// Specific Orders //////////////////////////

    /**
     * @ORM\OneToOne(targetEntity="ScanOrder", inversedBy="orderinfo", cascade={"persist","remove"})
     **/
    private $scanorder;




    ////////////////////////// EOF Specific Orders //////////////////////////




    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patient = new ArrayCollection();
        $this->encounter = new ArrayCollection();
        $this->procedure = new ArrayCollection();
        $this->accession = new ArrayCollection();
        $this->part = new ArrayCollection();      
        $this->block = new ArrayCollection();
        $this->slide = new ArrayCollection();
        //$this->dataqualitymrnacc = new ArrayCollection();
        $this->history = new ArrayCollection();

        //Initialize specific orders
        if( !$this->getScanorder() ) {
            $this->setScanorder(new ScanOrder());
        }

    }

    public function __clone() {

        //throw new \Exception('Cloning orderinfo');

        if( $this->getId() ) {
            $this->setId(null);

            $children = $this->getPatient(); // Get current collection

            if( !$children ) return;

            $this->patient = new ArrayCollection();
            $this->encounter = new ArrayCollection();
            $this->procedure = new ArrayCollection();
            $this->accession = new ArrayCollection();
            $this->part = new ArrayCollection();
            $this->block = new ArrayCollection();
            $this->slide = new ArrayCollection();

            //
            $provider = $this->getProvider();
            $proxyuser = $this->getProxyuser();
            //$dataqualitiesmrnacc = $this->getDataqualityMrnAcc();
            $histories = $this->getHistory();

            //$this->setProvider( new ArrayCollection() );
            //$this->proxyuser = new ArrayCollection();
            //$this->dataqualitymrnacc = new ArrayCollection();
            $this->history = new ArrayCollection();

            $this->setProvider( $provider );
            $this->setProxyuser( $proxyuser );

            //foreach( $dataqualitiesmrnacc as $dataqualitymrnacc ) {
            //    $this->addDataqualityMrnAcc($dataqualitymrnacc);
            //}

            foreach( $histories as $history ) {
                $this->addHistory($history);
            }

            foreach( $children as $child ) {
                //echo "1 clone Children: ".$child;
                $this->removeDepend($child);

                $cloneChild = clone $child;

                //$cloneChild->removeOrderinfo($this);

                $cloneChild->cloneChildren($this);

                //$this->patient->add($cloneChild);
                $this->addDepend($cloneChild);  //$orderinfo->addPatient();
                //$cloneChild->addOrderInfo($this);
                //echo "2 cloned Children: ".$cloneChild;
            }


            //Specific Orders
//            $scanorder = $this->getScanorder();
//            $this->setScanorder($scanorder);
              
        }

    }

    public function removeDepend( $depend ) {
        $class = new \ReflectionClass($depend);
        $className = $class->getShortName();    //Part
        $removeMethod = "remove".$className;
        $this->$removeMethod($depend);
    }

    public function addDepend( $depend ) {
        $class = new \ReflectionClass($depend);
        $className = $class->getShortName();    //Part
        $addMethod = "add".$className;
        $this->$addMethod($depend);
    }



//    /**
//     * @param mixed $service
//     */
//    public function setService($service)
//    {
//        $this->service = $service;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getService()
//    {
//        return $this->service;
//    }

//    /**
//     * Set priority
//     *
//     * @param string $priority
//     * @return OrderInfo
//     */
//    public function setPriority($priority)
//    {
//        $this->priority = $priority;
//
//        return $this;
//    }
//
//    /**
//     * Get priority
//     *
//     * @return string
//     */
//    public function getPriority()
//    {
//        return $this->priority;
//    }
    
//    public function getScandeadline() {
//        return $this->scandeadline;
//    }
//
//    public function getReturnoption() {
//        return $this->returnoption;
//    }
//
//    public function setScandeadline($scandeadline) {
//        $this->scandeadline = $scandeadline;
//    }
//
//    public function setReturnoption($returnoption) {
//        $this->returnoption = $returnoption;
//    }


//    public function getDataqualityMrnAcc()
//    {
//        return $this->dataqualitymrnacc;
//    }
//
//    public function setDataqualityMrnAcc($dataqualitiesmrnacc)
//    {
//        if( $dataqualitiesmrnacc == null ) {
//            $dataqualitiesmrnacc = new ArrayCollection();
//        }
//        $this->dataqualitymrnacc = $dataqualitiesmrnacc;
//    }
//
//    public function addDataqualityMrnAcc($dataqualitymrnacc)
//    {
//        if( !$this->dataqualitymrnacc->contains($dataqualitymrnacc) ) {
//            $this->dataqualitymrnacc->add($dataqualitymrnacc);
//        }
//    }
//
//    public function removeDataqualityMrnAcc($dataqualitymrnacc)
//    {
//        $this->dataqualitymrnacc->removeElement($dataqualitymrnacc);
//    }

    public function getHistory()
    {
        return $this->history;
    }
    public function addHistory($history)
    {
        if( !$this->history->contains($history) ) {
            $this->history->add($history);
            $history->setOrder($this);
        }
    }
    public function removeHistory($history)
    {
        $this->history->removeElement($history);
    }


//    /**
//     * Set slideDelivery
//     *
//     * @param string $slideDelivery
//     * @return OrderInfo
//     */
//    public function setSlideDelivery($slideDelivery)
//    {
//        $this->slideDelivery = $slideDelivery;
//
//        return $this;
//    }
//
//    /**
//     * Get slideDelivery
//     *
//     * @return string
//     */
//    public function getSlideDelivery()
//    {
//        return $this->slideDelivery;
//    }
//
//
//    public function setReturnSlide($returnSlide)
//    {
//        $this->returnSlide = $returnSlide;
//
//        return $this;
//    }
//
//    public function getReturnSlide()
//    {
//        return $this->returnSlide;
//    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

//    /**
//     * @param mixed $purpose
//     */
//    public function setPurpose($purpose)
//    {
//        $this->purpose = $purpose;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPurpose()
//    {
//        return $this->purpose;
//    }



    /**
     * @param string $oid
     */
    public function setOid($oid=null)
    {
        if( $oid == null ) {
            $oid = $this->getId();
        }
        $this->oid = $oid;
    }

    /**
     * @return string
     */
    public function getOid()
    {
        return $this->oid;
    }


    /**
     * @param mixed $educational
     */
    public function setEducational($educational)
    {
        $this->educational = $educational;
    }

    /**
     * @return mixed
     */
    public function getEducational()
    {
        return $this->educational;
    }

    /**
     * @param mixed $research
     */
    public function setResearch($research)
    {
        $this->research = $research;
    }

    /**
     * @return mixed
     */
    public function getResearch()
    {
        return $this->research;
    }


    ///////////////////// Hierarchy objects /////////////////////
    /**
     * Add patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     * @return OrderInfo
     */
    public function addPatient(\Oleg\OrderformBundle\Entity\Patient $patient)
    {
        //echo "############### OrderInfo addPatient=".$patient."<br>";
        if( !$this->patient->contains($patient) ) {            
            $this->patient->add($patient);
        }               
    }

    /**
     * Remove patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     */
    public function removePatient(\Oleg\OrderformBundle\Entity\Patient $patient)
    {      
        $this->patient->removeElement($patient);
    }

    /**
     * Get patient
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPatient()
    {
        return $this->patient;
    }

    public function clearPatient()
    {
//        foreach( $this->patient as $thispatient ) {
//            $this->removePatient($thispatient);
//        }
        $this->patient->clear();
    }
    
    /**
     * Add slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return OrderInfo
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slide->contains($slide) ) {
            $this->slide->add($slide);
        }    
    }

    /**
     * Remove slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     */
    public function removeSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        $this->slide->removeElement($slide);
    }

    /**
     * Get slide
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSlide()
    {
        return $this->slide;
    }

    public function addEncounter(\Oleg\OrderformBundle\Entity\Encounter $encounter)
    {
        if( !$this->encounter->contains($encounter) ) {
            $this->encounter->add($encounter);
        }
    }
    public function removeEncounter(\Oleg\OrderformBundle\Entity\Encounter $encounter)
    {
        $this->encounter->removeElement($encounter);
    }
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * Add procedure
     *
     * @param \Oleg\OrderformBundle\Entity\procedure $procedure
     * @return OrderInfo
     */
    public function addProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {         
        if( !$this->procedure->contains($procedure) ) {            
            $this->procedure->add($procedure);
        }   
    }

    /**
     * Remove procedure
     *
     * @param \Oleg\OrderformBundle\Entity\Procedure $procedure
     */
    public function removeProcedure(\Oleg\OrderformBundle\Entity\Procedure $procedure)
    {
        $this->procedure->removeElement($procedure);
    }

    /**
     * Get procedure
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * Add accession
     *
     * @param \Oleg\OrderformBundle\Entity\Accession $accession
     * @return OrderInfo
     */
    public function addAccession(\Oleg\OrderformBundle\Entity\Accession $accession)
    {
        if( !$this->accession->contains($accession) ) {            
            $this->accession->add($accession);
        }
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

    /**
     * Add part
     *
     * @param \Oleg\OrderformBundle\Entity\Part $part
     * @return OrderInfo
     */
    public function addPart(\Oleg\OrderformBundle\Entity\Part $part)
    {     
        if( !$this->part->contains($part) ) {            
            $this->part->add($part);
        }  
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

    /**
     * Add block
     *
     * @param \Oleg\OrderformBundle\Entity\Block $block
     * @return OrderInfo
     */
    public function addBlock(\Oleg\OrderformBundle\Entity\Block $block)
    {      
        if( !$this->block->contains($block) ) {            
            $this->block->add($block);
        }  
    }

    /**
     * Remove block
     *
     * @param \Oleg\OrderformBundle\Entity\Block $block
     */
    public function removeBlock(\Oleg\OrderformBundle\Entity\Block $block)
    {
        $this->block->removeElement($block);
    }

    /**
     * Get block
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBlock()
    {
        return $this->block;
    }
    ///////////////////// EOF Hierarchy objects /////////////////////


//    /**
//     * @param mixed $scanner
//     */
//    public function setScanner($scanner)
//    {
//        $this->scanner = $scanner;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getScanner()
//    {
//        return $this->scanner;
//    }


    public function __toString(){

//        $patient_info = "(";
//        $count = 0;
//        foreach( $this->patient as $patient ) {
//            //$patient_info .= 'id='.$patient->getId().", mrn=".$patient->getMrn(). "; ";
//            $patient_info .= $count.":" . $patient. "; ";
//            $count++;
//        }
//        $patient_info .= ")";

//        return "OrderInfo: id=".$this->id.", ".$this->educational.", ".$this->research.", patientCount=".count($this->patient).":".$patient_info.", slideCount=".count($this->slide)."<br>";
        return "OrderInfo: id=".$this->getId().", oid=".$this->oid.", status=".$this->getStatus().
        ", provider=".$this->getProvider().", providerName=".$this->getProvider()->getUsername().", providerId=".$this->getProvider()->getId().
        ", edu=".$this->educational.
        ", res=".$this->research.", patientCount=".count($this->patient).
        ", slideCount=".count($this->slide)."<br>";
    }


    //TODO: testing
    public function getChildren() {
        return $this->getPatient();
    }
    public function addChildren($child) {
        $this->addPatient($child);
    }
    public function removeChildren($child) {
        $this->removePatient($child);
    }






    /////////////////////// Getters and Setters of Specific Orders ///////////////////////

    /**
     * @param mixed $scanorder
     */
    public function setScanorder($scanorder)
    {
        $this->scanorder = $scanorder;
    }

    /**
     * @return mixed
     */
    public function getScanorder()
    {
        return $this->scanorder;
    }

    /////////////////////// EOF Getters and Setters of Specific Orders ///////////////////////


}