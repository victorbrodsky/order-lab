<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_history")
 * @ORM\HasLifecycleCallbacks
 */
class History
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
     * @ORM\Column(type="string", nullable=true)
     */
    private $currentid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changedate", type="datetime")
     *
     */
    private $changedate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="provider", referencedColumnName="id")
     */
    private $provider;

    /**
     * @ORM\ManyToOne(targetEntity="Status", cascade={"persist"})
     * @ORM\JoinColumn(name="currentstatus", referencedColumnName="id", nullable=true)
     */
    private $currentstatus;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $roles = array();

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $selectednote;

    /**
     * User id
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="viewed", referencedColumnName="id", nullable=true)
     */
    private $viewed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="vieweddate", type="datetime", nullable=true)
     *
     */
    private $vieweddate;

    /**
     * @ORM\ManyToOne(targetEntity="ProgressCommentsEventTypeList")
     * @ORM\JoinColumn(name="eventtype_id", referencedColumnName="id", nullable=true)
     **/
    private $eventtype;


//    /**
//     * @ORM\ManyToOne(targetEntity="OrderInfo", inversedBy="history", cascade={"persist"})
//     * @ORM\JoinColumn(name="orderinfo", referencedColumnName="id", onDelete="CASCADE", nullable=true)
//     */
//    private $orderinfo;
    //Fields specifying a subject entity
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $orderNamespace;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $orderName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $orderId;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $orderProvider;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     */
    private $orderProxyuser;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id")
     */
    private $orderInstitution;



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

    public function setRoles($roles) {
        foreach( $roles as $role ) {
            $this->addRole($role."");
        }
    }

    public function getRoles() {
        return $this->roles;
    }

    public function addRole($role) {
        $this->roles[] = $role;
        //$this->roles->add($role);
    }

    public function hasProviderRole($role)
    {
        if( !is_array($this->getRoles()) ) {
            return false;
        }
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @ORM\PrePersist
     */
    public function setChangedate($date=null) {
        if( $date ) {
            $this->changedate = $date;
        } else {
            $this->changedate = new \DateTime();
        }
    }

    /**
     * @return \DateTime
     */
    public function getChangedate()
    {
        return $this->changedate;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $currentstatus
     */
    public function setCurrentstatus($currentstatus)
    {
        $this->currentstatus = $currentstatus;
    }

    /**
     * @return mixed
     */
    public function getCurrentstatus()
    {
        return $this->currentstatus;
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
     * @param mixed $currentid
     */
    public function setCurrentid($currentid)
    {
        $this->currentid = $currentid;
    }

    /**
     * @return mixed
     */
    public function getCurrentid()
    {
        return $this->currentid;
    }

    /**
     * @param mixed $viewed
     */
    public function setViewed($viewed)
    {
        $this->viewed = $viewed;
    }

    /**
     * @return mixed
     */
    public function getViewed()
    {
        return $this->viewed;
    }

    /**
     * @param mixed $vieweddate
     */
    public function setVieweddate($vieweddate)
    {
        $this->vieweddate = $vieweddate;
    }

    /**
     * @return mixed
     */
    public function getVieweddate()
    {
        return $this->vieweddate;
    }

    //set and get order object as object's simple fields
    public function setOrderinfo($order)
    {
        $this->setOrder($order);
    }
    public function getOrderinfo()
    {
        return $this->getOrder();
    }
    public function setOrder($order)
    {
        //$this->orderinfo = $order;

        //get classname, order name and id of subject order
        $class = new \ReflectionClass($order);
        $className = $class->getShortName();
        $classNamespace = $class->getNamespaceName();

        //set classname, order name, id, provider and proxyuser of subject order
        $this->setOrderNamespace($classNamespace);
        $this->setOrderName($className);
        $this->setOrderId($order->getId());
        $this->setOrderProvider($order->getProvider());
        $this->setOrderProxyuser($order->getProxyuser());
        $this->setOrderInstitution($order->getInstitution());
    }
    public function getOrder()
    {
        //return $this->orderinfo;
        $res = array(
            'orderNamespace' => $this->getOrderNamespace(),
            'orderName' => $this->getOrderName(),
            'orderId' => $this->getOrderId(),
            'orderProvider' => $this->getOrderProvider(),
            'orderProxyuser' => $this->getOrderProxyuser(),
            'orderInstitution' => $this->getOrderInstitution()
        );
        return $res;
    }


    /**
     * @param mixed $selectednote
     */
    public function setSelectednote($selectednote)
    {
        $this->selectednote = $selectednote;
    }

    /**
     * @return mixed
     */
    public function getSelectednote()
    {
        return $this->selectednote;
    }

    /**
     * @param mixed $eventtype
     */
    public function setEventtype($eventtype)
    {
        $this->eventtype = $eventtype;
    }

    /**
     * @return mixed
     */
    public function getEventtype()
    {
        return $this->eventtype;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderName
     */
    public function setOrderName($orderName)
    {
        $this->orderName = $orderName;
    }

    /**
     * @return mixed
     */
    public function getOrderName()
    {
        return $this->orderName;
    }

    /**
     * @param mixed $orderNamespace
     */
    public function setOrderNamespace($orderNamespace)
    {
        $this->orderNamespace = $orderNamespace;
    }

    /**
     * @return mixed
     */
    public function getOrderNamespace()
    {
        return $this->orderNamespace;
    }

    /**
     * @param mixed $orderProvider
     */
    public function setOrderProvider($orderProvider)
    {
        $this->orderProvider = $orderProvider;
    }

    /**
     * @return mixed
     */
    public function getOrderProvider()
    {
        return $this->orderProvider;
    }

    /**
     * @param mixed $orderProxyuser
     */
    public function setOrderProxyuser($orderProxyuser)
    {
        $this->orderProxyuser = $orderProxyuser;
    }

    /**
     * @return mixed
     */
    public function getOrderProxyuser()
    {
        return $this->orderProxyuser;
    }

    /**
     * @param mixed $orderInstitution
     */
    public function setOrderInstitution($orderInstitution)
    {
        $this->orderInstitution = $orderInstitution;
    }

    /**
     * @return mixed
     */
    public function getOrderInstitution()
    {
        return $this->orderInstitution;
    }






}