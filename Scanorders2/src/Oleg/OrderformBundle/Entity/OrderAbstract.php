<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
class OrderAbstract {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ordername;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ordernumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="orderdate", type="datetime", nullable=true)
     *
     */
    protected $orderdate;

    /**
     * @ORM\ManyToOne(targetEntity="FormType", cascade={"persist"})
     * @ORM\JoinColumn(name="formtype", referencedColumnName="id")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="provider", referencedColumnName="id")
     */
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="proxyuser", referencedColumnName="id")
     */
    protected $proxyuser;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id")
     */
    protected $institution;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\SourceSystemList")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true)
     */
    protected $source;

    /**
     * @ORM\ManyToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    protected $status;



    /**
     * Order source location
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
     */
    private $sourceLocation;

    /**
     * Order priority: routine, stat
     *
     * @ORM\Column(type="string",nullable=true)
     */
    protected $priority;

    /**
     * Order completetion deadline
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deadline;

    /**
     * Return order if not completed by deadline
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $returnoption;

    /**
     * Order delivery (string): I'll give slides to ...
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $delivery;

    /**
     * Return Location (object)
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location")
     **/
    protected $returnLocation;

    /**
     * Purpose of Order (string)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $purpose;

    /**
     * Equipment associated with this order (object)
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Equipment")
     */
    protected $equipment;



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
     * @param mixed $ordername
     */
    public function setOrdername($ordername)
    {
        $this->ordername = $ordername;
    }

    /**
     * @return mixed
     */
    public function getOrdername()
    {
        return $this->ordername;
    }

    /**
     * @param mixed $ordernumber
     */
    public function setOrdernumber($ordernumber)
    {
        $this->ordernumber = $ordernumber;
    }

    /**
     * @return mixed
     */
    public function getOrdernumber()
    {
        return $this->ordernumber;
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
     * Get orderdate
     *
     * @return \DateTime
     */
    public function getOrderdate()
    {
        return $this->orderdate;
    }


    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

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
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
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

    /**
     * @param mixed $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return mixed
     */
    public function getDelivery()
    {
        return $this->delivery;
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
     * @param mixed $returnLocation
     */
    public function setReturnLocation($returnLocation)
    {
        $this->returnLocation = $returnLocation;
    }

    /**
     * @return mixed
     */
    public function getReturnLocation()
    {
        return $this->returnLocation;
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
     * @param mixed $sourceLocation
     */
    public function setSourceLocation($sourceLocation)
    {
        $this->sourceLocation = $sourceLocation;
    }

    /**
     * @return mixed
     */
    public function getSourceLocation()
    {
        return $this->sourceLocation;
    }




}