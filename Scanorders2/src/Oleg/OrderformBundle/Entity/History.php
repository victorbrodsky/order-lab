<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="history")
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

//    /**
//     * @ORM\ManyToMany(targetEntity="OrderInfo", cascade={"persist"})
//     * @ORM\JoinTable(name="history_orderinfo",
//     *      joinColumns={@ORM\JoinColumn(name="history_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")}
//     * )
//     */
//    private $orderinfo;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $currentid;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $newid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changedate", type="datetime")
     *
     */
    private $changedate;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id")
     */
    private $provider;

    /**
     * @ORM\ManyToOne(targetEntity="Status", cascade={"persist"})
     * @ORM\JoinColumn(name="currentstatus_id", referencedColumnName="id", nullable=true)
     */
    private $currentstatus;

    /**
     * @ORM\ManyToOne(targetEntity="Status", cascade={"persist"})
     * @ORM\JoinColumn(name="newstatus_id", referencedColumnName="id", nullable=true)
     */
    private $newstatus;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $roles = array();

    /**
     * @ORM\Column(type="text", nullable=true, length=5000)
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="viewed_id", referencedColumnName="id", nullable=true)
     */
    private $viewed;

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
        $this->roles = roles;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function addRole($role) {
        $this->roles[] = $role;
        //$this->roles->add($role);
    }


    /**
     * @ORM\PrePersist
     */
    public function setChangedate() {
        $this->changedate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getChangedate()
    {
        return $this->changedate;
    }

    /**
     * @param mixed $newstatus
     */
    public function setNewstatus($newstatus)
    {
        $this->newstatus = $newstatus;
    }

    /**
     * @return mixed
     */
    public function getNewstatus()
    {
        return $this->newstatus;
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
     * @param mixed $newid
     */
    public function setNewid($newid)
    {
        $this->newid = $newid;
    }

    /**
     * @return mixed
     */
    public function getNewid()
    {
        return $this->newid;
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



}