<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Message, Holder of different orders (i.e. scanorder, laborder)
 *
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\OrderInfoRepository")
 * @ORM\Table(name="scan_orderinfo",
 *  indexes={
 *      @ORM\Index( name="oid_idx", columns={"oid"} )
 *  }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class OrderInfo {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="orderdate", type="datetime", nullable=true)
     *
     */
    private $orderdate;

    /**
     * MessageCategory with subcategory (parent-children hierarchy)
     *
     * @ORM\ManyToOne(targetEntity="MessageCategory", cascade={"persist"})
     */
    private $messageCategory;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $provider;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $proxyuser;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     */
    private $institution;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     */
    private $status;

    /**
     * Equipment associated with this order (object)
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Equipment")
     */
    private $equipment;

    /**
     * Purpose of Order (string)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $purpose;

    /**
     * Order priority: routine, stat
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $priority;

    /**
     * Order completetion deadline
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deadline;

    /**
     * Return order if not completed by deadline
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $returnoption;

//    /**
//     * Order delivery (string): I'll give slides to ...
//     *
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $delivery;


    /**
     * oid - id of the original order. Required for amend logic.
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

//    /**
//     * History of the order
//     *
//     * @ORM\ManyToMany(targetEntity="History")
//     * @ORM\JoinTable(name="scan_orderinfo_history",
//     *      joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="history_id", referencedColumnName="id", unique=true)}
//     *      )
//     **/
//    private $history;


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
//    private $purpose;

    /**
     * Conflicting accession number is replaced, so keep the reference to dataqualitymrnacc object in the orderinfo (unlike to dataqualityage)
     * Any message (order) has referenece to patient and accession, hence can create conflict
     *
     * @ORM\OneToMany(targetEntity="DataQualityMrnAcc", mappedBy="orderinfo", cascade={"persist"})
     */
    private $dataqualitymrnacc;

    /**
     * @ORM\OneToMany(targetEntity="History", mappedBy="orderinfo", cascade={"persist"})
     */
    private $history;

    /**
     * Tracking: can be many
     * One-To-Many unidirectional with Join table
     *
     * @ORM\ManyToMany(targetEntity="Tracking")
     * @ORM\JoinTable(name="scan_orderinfo_tracking",
     *      joinColumns={@ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tracking_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $tracking;


    /////////////////    HIERARCHY OBJECTS    //////////////////////
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
    /////////////////   EOF  HIERARCHY OBJECTS    //////////////////////


    /**
     * One-To-Many Unidirectional
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\GeneralEntity")
     * @ORM\JoinTable(name="scan_orderinfo_input",
     *      joinColumns={@ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="input_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $inputs;

    /**
     * One-To-Many Unidirectional
     *
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\GeneralEntity")
     * @ORM\JoinTable(name="scan_orderinfo_output",
     *      joinColumns={@ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="output_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $outputs;


    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", mappedBy="associations")
     **/
    private $backAssociations;

    /**
     * @ORM\ManyToMany(targetEntity="OrderInfo", inversedBy="backAssociations")
     * @ORM\JoinTable(name="associations",
     *      joinColumns={@ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="association_id", referencedColumnName="id")}
     *      )
     **/
    private $associations;


    /**
     * Source: can be many
     * One-To-Many unidirectional with Join table
     *
     * @ORM\ManyToMany(targetEntity="Endpoint", cascade={"persist"})
     * @ORM\JoinTable(name="scan_source_orderinfo",
     *      joinColumns={@ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="source_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $sources;

    /**
     * Destinations: can be many
     * One-To-Many unidirectional with Join table
     *
     * @ORM\ManyToMany(targetEntity="Endpoint", cascade={"persist"})
     * @ORM\JoinTable(name="scan_destination_orderinfo",
     *      joinColumns={@ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="destination_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $destinations;



    ////////////////////////// Specific Orders //////////////////////////

    //cascade={"persist","remove"}
    /**
     * @ORM\OneToOne(targetEntity="ScanOrder", inversedBy="orderinfo", cascade={"persist","remove"})
     **/
    private $scanorder;

    /**
     * @ORM\OneToOne(targetEntity="SlideReturnRequest", inversedBy="orderinfo", cascade={"persist","remove"})
     **/
    private $slideReturnRequest;

    /**
     * @ORM\OneToOne(targetEntity="LabOrder", inversedBy="orderinfo", cascade={"persist","remove"})
     */
    private $laborder;

    /**
     * @ORM\OneToOne(targetEntity="Report", inversedBy="orderinfo", cascade={"persist","remove"})
     */
    private $report;

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
        $this->dataqualitymrnacc = new ArrayCollection();
        $this->history = new ArrayCollection();
        $this->tracking = new ArrayCollection();

        //TODO: test cloning
        $this->sources = new ArrayCollection();
        $this->destinations = new ArrayCollection();

        //links
        //TODO: test cloning
        $this->inputs = new ArrayCollection();
        $this->outputs = new ArrayCollection();
        $this->associations = new ArrayCollection();
        $this->backAssociations = new ArrayCollection();

        //Initialize specific orders
//        if( !$this->getScanorder() ) {
//            $this->setScanorder(new ScanOrder());
//        }
//        if( !$this->getLaborder() ) {
//            $this->setLaborder(new LabOrder());
//        }
//        $this->setScanorder(null);
//        $this->setLaborder(null);
//        $this->setSlideReturnRequest(null);

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

            //links
            $this->inputs = new ArrayCollection();
            $this->outputs = new ArrayCollection();
            $this->associations = new ArrayCollection();
            $this->backAssociations = new ArrayCollection();

            $this->sources = new ArrayCollection();
            $this->destinations = new ArrayCollection();

            //
            $provider = $this->getProvider();
            $proxyuser = $this->getProxyuser();
            $dataqualitiesmrnacc = $this->getDataqualityMrnAcc();
            $histories = $this->getHistory();
            $trackings = $this->getTracking();

            //$this->setProvider( new ArrayCollection() );
            //$this->proxyuser = new ArrayCollection();
            $this->dataqualitymrnacc = new ArrayCollection();
            $this->history = new ArrayCollection();
            $this->tracking = new ArrayCollection();

            $this->setProvider( $provider );
            $this->setProxyuser( $proxyuser );

            foreach( $dataqualitiesmrnacc as $dataqualitymrnacc ) {
                $this->addDataqualityMrnAcc($dataqualitymrnacc);
            }

            foreach( $histories as $history ) {
                $this->addHistory($history);
            }

            foreach( $trackings as $tracking ) {
                $this->addTracking($tracking);
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





    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $deadline
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
    }

    /**
     * @return mixed
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

//    /**
//     * @param mixed $delivery
//     */
//    public function setDelivery($delivery)
//    {
//        $this->delivery = $delivery;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDelivery()
//    {
//        return $this->delivery;
//    }

    /**
     * @param mixed $equipment
     */
    public function setEquipment($equipment)
    {
        $this->equipment = $equipment;
    }

    /**
     * @return mixed
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }


    /**
     * @ORM\PrePersist
     */
    public function setOrderdate($date=null) {
        if( $date ) {
            $this->orderdate = $date;
        } else {
            $this->orderdate = new \DateTime();
        }
    }

    /**
     * @return \DateTime
     */
    public function getOrderdate()
    {
        return $this->orderdate;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $proxyuser
     */
    public function setProxyuser($proxyuser)
    {
        $this->proxyuser = $proxyuser;
    }

    /**
     * @return mixed
     */
    public function getProxyuser()
    {
        return $this->proxyuser;
    }

    /**
     * @param mixed $purpose
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
    }

    /**
     * @return mixed
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * @param mixed $returnoption
     */
    public function setReturnoption($returnoption)
    {
        $this->returnoption = $returnoption;
    }

    /**
     * @return mixed
     */
    public function getReturnoption()
    {
        return $this->returnoption;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $messageCategory
     */
    public function setMessageCategory($messageCategory)
    {
        $this->messageCategory = $messageCategory;
    }

    /**
     * @return mixed
     */
    public function getMessageCategory()
    {
        return $this->messageCategory;
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


    public function getDataqualityMrnAcc()
    {
        return $this->dataqualitymrnacc;
    }

    public function setDataqualityMrnAcc($dataqualitiesmrnacc)
    {
        if( $dataqualitiesmrnacc == null ) {
            $dataqualitiesmrnacc = new ArrayCollection();
        }
        $this->dataqualitymrnacc = $dataqualitiesmrnacc;
    }

    public function addDataqualityMrnAcc($dataqualitymrnacc)
    {
        if( !$this->dataqualitymrnacc->contains($dataqualitymrnacc) ) {
            $this->dataqualitymrnacc->add($dataqualitymrnacc);
        }
    }

    public function removeDataqualityMrnAcc($dataqualitymrnacc)
    {
        $this->dataqualitymrnacc->removeElement($dataqualitymrnacc);
    }

    public function getHistory()
    {
        return $this->history;
    }
    public function addHistory($history)
    {
        if( !$this->history->contains($history) ) {
            $this->history->add($history);
        }
    }
    public function removeHistory($history)
    {
        $this->history->removeElement($history);
    }

    public function getTracking()
    {
        return $this->tracking;
    }
    public function addTracking($tracking)
    {
        if( $tracking && !$this->tracking->contains($tracking) ) {
            $this->tracking->add($tracking);
        }
        return $this;
    }
    public function removeTracking($tracking)
    {
        $this->tracking->removeElement($tracking);
    }

    public function getSources()
    {
        return $this->sources;
    }
    public function addSource($item)
    {
        if( $item && !$this->sources->contains($item) ) {
            $this->sources->add($item);
        }
        return $this;
    }
    public function removeSource($item)
    {
        $this->sources->removeElement($item);
    }

    public function getDestinations()
    {
        return $this->destinations;
    }
    public function addDestination($item)
    {
        if( $item && !$this->destinations->contains($item) ) {
            $this->destinations->add($item);
        }
        return $this;
    }
    public function removeDestination($item)
    {
        $this->destinations->removeElement($item);
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

//    public function getStatus() {
//        return $this->status;
//    }
//
//    public function setStatus($status) {
//        $this->status = $status;
//    }

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


    //Links
    public function getInputs()
    {
        return $this->inputs;
    }
    public function addInput($input)
    {
        if( !$this->inputs->contains($input) ) {
            $this->inputs->add($input);
        }
    }
    public function removeInput($input)
    {
        $this->inputs->removeElement($input);
    }

    public function getOutputs()
    {
        return $this->outputs;
    }
    public function addOutput($output)
    {
        if( !$this->outputs->contains($output) ) {
            $this->outputs->add($output);
        }
    }
    public function removeOutput($output)
    {
        $this->outputs->removeElement($output);
    }

    public function getAssociations()
    {
        return $this->associations;
    }
    public function addAssociation($item)
    {
        if( !$this->associations->contains($item) ) {
            $this->associations->add($item);
        }
    }
    public function removeAssociation($item)
    {
        $this->associations->removeElement($item);
    }

    public function getBackAssociations()
    {
        return $this->backAssociations;
    }
    public function addBackAssociation($item)
    {
        if( !$this->backAssociations->contains($item) ) {
            $this->backAssociations->add($item);
        }
    }
    public function removeBackAssociation($item)
    {
        $this->backAssociations->removeElement($item);
    }


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
        return "OrderInfo: id=".$this->getId().", oid=".$this->oid.", status=".$this->getStatus().", category=".$this->getMessageCategory().
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


    /**
     * @param mixed $slideReturnRequest
     */
    public function setSlideReturnRequest($slideReturnRequest)
    {
        $this->slideReturnRequest = $slideReturnRequest;
    }

    /**
     * @return mixed
     */
    public function getSlideReturnRequest()
    {
        return $this->slideReturnRequest;
    }


    /**
     * @param mixed $laborder
     */
    public function setLaborder($laborder)
    {
        $this->laborder = $laborder;
    }

    /**
     * @return mixed
     */
    public function getLaborder()
    {
        return $this->laborder;
    }

    /**
     * @param mixed $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return mixed
     */
    public function getReport()
    {
        return $this->report;
    }




    /////////////////////// EOF Getters and Setters of Specific Orders ///////////////////////


}