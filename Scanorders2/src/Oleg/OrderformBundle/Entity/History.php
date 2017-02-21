<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

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


    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="history", cascade={"persist"})
     * @ORM\JoinColumn(name="message", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $message;


    public function __construct()
    {
        $this->setChangedate();
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
    public function setChangedate() { //$date=null
//        if( $date ) {
//            $this->changedate = $date;
//        } else {
//            $this->changedate = new \DateTime();
//        }
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


    public function setMessage($order)
    {
        $this->message = $order;
    }

    public function getMessage()
    {
        return $this->message;
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



}