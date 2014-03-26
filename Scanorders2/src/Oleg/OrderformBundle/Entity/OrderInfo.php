<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * OrderInfo might have many slides
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\OrderInfoRepository")
 * @ORM\Table(name="orderinfo")
 * @ORM\HasLifecycleCallbacks
 */
class OrderInfo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="orderdate", type="datetime", nullable=true)
     *
     */
    private $orderdate;

    /**
     * @ORM\ManyToOne(targetEntity="PathServiceList", inversedBy="orderinfo", cascade={"persist"})
     * @ORM\JoinColumn(name="pathservicelist_id", referencedColumnName="id", nullable=true)
     */
    private $pathologyService;

    /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="orderinfo", cascade={"persist"})
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="FormType", cascade={"persist"})
     * @ORM\JoinColumn(name="formtype_id", referencedColumnName="id")
     */
    private $type;

    /**
     * oid - id of the original order.
     * When Amend order, switch orders to keep the original id and at newly created order set oid of the original order
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $oid;
    
    /**
     * @var string
     *
     * @ORM\Column(name="priority", type="string", length=200)
     * @Assert\NotBlank
     */
    private $priority;
    
    /**
     * @ORM\Column(name="scandeadline", nullable=true, type="datetime")
     */
    private $scandeadline;
    
    /**
     * Return slide(s) by this date even if not scanned
     * @ORM\Column(name="returnoption", type="boolean")
     */
    private $returnoption;

    /**
     * @var string
     *
     * @ORM\Column(name="slideDelivery", type="string", length=200, nullable=true)
     * @Assert\NotBlank
     */
    private $slideDelivery;

    /**
     * @var string
     *
     * @ORM\Column(name="returnSlide", type="string", length=200, nullable=true)
     * @Assert\NotBlank
     */
    private $returnSlide;

    /**
     * @ORM\ManyToMany(targetEntity="User", cascade={"persist"})
     * @ORM\JoinTable(name="provider_orderinfo",
     *      joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="provider_id", referencedColumnName="id")}
     * )
     */
    private $provider;

    /**
     * @ORM\ManyToMany(targetEntity="User", cascade={"persist"})
     * @ORM\JoinTable(name="proxyuser_orderinfo",
     *      joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="proxyuser_id", referencedColumnName="id")}
     * )
     */
    protected $proxyuser;

    /**
     * @ORM\OneToMany(targetEntity="DataQuality", mappedBy="orderinfo", cascade={"persist"})
     */
    private $dataquality;

    /**
     * @ORM\OneToMany(targetEntity="History", mappedBy="orderinfo", cascade={"persist"})
     */
    private $history;

    /////////////////    OBJECTS    //////////////////////

    //cascade={"persist"}   
    /**
     * @ORM\ManyToMany(targetEntity="Patient", inversedBy="orderinfo" )
     * @ORM\JoinTable(name="patient_orderinfo")
     **/
    private $patient;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Educational",
     *      inversedBy="orderinfo",
     *      cascade={"persist"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinColumn(
     *      name="educational_id",
     *      referencedColumnName="id",
     *      onDelete="CASCADE"
     * )
     */
    private $educational;

    //     nullable=true
    /**
     * @ORM\OneToOne(
     *      targetEntity="Research",
     *      inversedBy="orderinfo",
     *      cascade={"persist"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinColumn(
     *      name="research_id",
     *      referencedColumnName="id",
     *      onDelete="CASCADE"
     * )
     */
    private $research;
       
    /**
     * @ORM\ManyToMany(targetEntity="Procedure", inversedBy="orderinfo")
     * @ORM\JoinTable(name="procedure_orderinfo")
     **/
    private $procedure;
    
    /**
     * @ORM\ManyToMany(targetEntity="Accession", inversedBy="orderinfo")
     * @ORM\JoinTable(name="accession_orderinfo")
     **/
    private $accession;
    
    /**
     * @ORM\ManyToMany(targetEntity="Part", inversedBy="orderinfo")
     * @ORM\JoinTable(name="part_orderinfo")
     **/
    private $part;
    
    /**
     * @ORM\ManyToMany(targetEntity="Block", inversedBy="orderinfo")
     * @ORM\JoinTable(name="block_orderinfo")
     **/
    private $block;
    
    /**
     * @ORM\ManyToMany(targetEntity="Slide", inversedBy="orderinfo")
     * @ORM\JoinTable(name="slide_orderinfo")
     **/
    private $slide;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patient = new ArrayCollection();
        $this->procedure = new ArrayCollection();
        $this->accession = new ArrayCollection();
        $this->part = new ArrayCollection();      
        $this->block = new ArrayCollection();
        $this->slide = new ArrayCollection();
        $this->provider = new ArrayCollection();
        $this->proxyuser = new ArrayCollection();
        $this->dataquality = new ArrayCollection();
        $this->history = new ArrayCollection();
    }

    public function __clone() {

        if ($this->id) {
            $this->setId(null);

            $children = $this->getPatient(); // Get current collection

            if( !$children ) return;

            $this->patient = new ArrayCollection();
            $this->procedure = new ArrayCollection();
            $this->accession = new ArrayCollection();
            $this->part = new ArrayCollection();
            $this->block = new ArrayCollection();
            $this->slide = new ArrayCollection();

            //
            $providers = $this->getProvider();
            $proxys = $this->getProxyuser();
            $dataqualities = $this->getDataquality();
            $histories = $this->getHistory();

            $this->provider = new ArrayCollection();
            $this->proxyuser = new ArrayCollection();
            $this->dataquality = new ArrayCollection();
            $this->history = new ArrayCollection();

            foreach( $providers as $thisprov ) {
                $this->addProvider($thisprov);
            }

            foreach( $proxys as $thisproxy ) {
                $this->addProxyuser($thisproxy);
            }

            foreach( $dataqualities as $dataquality ) {
                $this->addDataquality($dataquality);
            }

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
              
        }

    }

    public function removeDepend( $depend ) {
        $class = new \ReflectionClass($depend);
        $className = $class->getShortName();    //Part
        $removeMethod = "remove".$className;
        //echo "orderinfo remove depened:".$removeMethod."<br>";
        $this->$removeMethod($depend);
    }

    public function addDepend( $depend ) {
        $class = new \ReflectionClass($depend);
        $className = $class->getShortName();    //Part
        $addMethod = "add".$className;
        $this->$addMethod($depend);
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

    public function setId($id)
    {
        $this->id = $id;
        return $id;
    }

    /**
    * @ORM\PrePersist
    */
    public function setOrderdate() {
        $this->orderdate = new \DateTime();
    }

    /**
     * Get orderdate
     *
     * @return \DateTime 
     */
    public function getOrderdate()
    {
        return $this->orderdate;
    }

    /**
     * Set pathologyService
     *
     * @param string $pathologyService
     * @return OrderInfo
     */
    public function setPathologyService($pathologyService)
    {
        $this->pathologyService = $pathologyService;
    
        return $this;
    }

    /**
     * Get pathologyService
     *
     * @return string 
     */
    public function getPathologyService()
    {
        return $this->pathologyService;
    }

    /**
     * Set priority
     *
     * @param string $priority
     * @return OrderInfo
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    
        return $this;
    }

    /**
     * Get priority
     *
     * @return string 
     */
    public function getPriority()
    {
        return $this->priority;
    }
    
    public function getScandeadline() {
        return $this->scandeadline;
    }

    public function getReturnoption() {
        return $this->returnoption;
    }

    public function setScandeadline($scandeadline) {
        $this->scandeadline = $scandeadline;
    }

    public function setReturnoption($returnoption) {
        $this->returnoption = $returnoption;
    }


    public function getDataquality()
    {
        return $this->dataquality;
    }

    public function addDataquality($dataquality)
    {
        if( !$this->dataquality->contains($dataquality) ) {
            $this->dataquality->add($dataquality);
        }
    }

    public function removeDataquality($dataquality)
    {
        $this->dataquality->removeElement($dataquality);
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


    /**
     * Set slideDelivery
     *
     * @param string $slideDelivery
     * @return OrderInfo
     */
    public function setSlideDelivery($slideDelivery)
    {
        $this->slideDelivery = $slideDelivery;
    
        return $this;
    }

    /**
     * Get slideDelivery
     *
     * @return string 
     */
    public function getSlideDelivery()
    {
        return $this->slideDelivery;
    }

    /**
     * Set returnSlide
     *
     * @param string $returnSlide
     * @return OrderInfo
     */
    public function setReturnSlide($returnSlide)
    {
        $this->returnSlide = $returnSlide;
    
        return $this;
    }

    /**
     * Get returnSlide
     *
     * @return string 
     */
    public function getReturnSlide()
    {
        return $this->returnSlide;
    }

    /**
     * Set provider
     *
     * @param \stdClass $provider
     * @return OrderInfo
     */
    public function setProvider($provider)
    {
        if( is_array($provider ) ) {
            $this->provider = $provider;
        } else {
            $this->provider->clear();
            $this->provider->add($provider);
        }
    
        return $this;
    }

    /**
     * Get provider
     *
     * @return \stdClass 
     */
    public function getProvider()
    {
        return $this->provider;
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function getType() {
        return $this->type;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setType($type) {
        $this->type = $type;
    }

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
     * Add patient
     *
     * @param \Oleg\OrderformBundle\Entity\Patient $patient
     * @return OrderInfo
     */
    public function addPatient(\Oleg\OrderformBundle\Entity\Patient $patient)
    {
        //echo "OrderInfo addPatient=".$patient."<br>";
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
        return "OrderInfo: id=".$this->id.", oid=".$this->oid.", status=".$this->status.
                ", providerCount=".count($this->getProvider()).", providerName=".$this->getProvider()->first()->getUsername().", providerId=".$this->getProvider()->first()->getId().
                ", edu=".$this->educational.
                ", res=".$this->research.", patientCount=".count($this->patient).
                ", slideCount=".count($this->slide)."<br>";
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

//    public function setBlock($block)
//    {
//        $this->block = $block;
//    }


    public function addProxyuser(\Oleg\OrderformBundle\Entity\User $proxyuser)
    {
        if( $proxyuser ) {
            if( !$this->proxyuser->contains($proxyuser) ) {
                $this->proxyuser->add($proxyuser);
            }
        }

        return $this;
    }

    public function removeProxyuser(\Oleg\OrderformBundle\Entity\User $proxyuser)
    {
        $this->proxyuser->removeElement($proxyuser);
    }

    /**
     * @param mixed $proxyuser
     */
    public function setProxyuser($proxyuser)
    {
        if( $proxyuser ) {
            if( is_array($proxyuser) ) {
                $this->proxyuser = $proxyuser;
            } else {
                $this->proxyuser->clear();
                $this->proxyuser->add($proxyuser);
            }
        }

    }

    /**
     * @return mixed
     */
    public function getProxyuser()
    {
        return $this->proxyuser;
    }

    public function addProvider(\Oleg\OrderformBundle\Entity\User $provider)
    {
        if( !$this->provider->contains($provider) ) {
            $this->provider->add($provider);
        }

        return $this;
    }

    public function removeProvider(\Oleg\OrderformBundle\Entity\User $provider)
    {
        $this->provider->removeElement($provider);
    }




}