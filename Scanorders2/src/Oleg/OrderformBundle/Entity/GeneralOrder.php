<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_generalOrder")
 */
class GeneralOrder extends OrderAbstract {


//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $status;

//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $ordername;
//
//    /**
//     * @ORM\Column(type="string", nullable=true)
//     */
//    private $ordernumber;
//
//    /**
//     * @ORM\Column(type="text", nullable=true)
//     */
//    private $comment;

//    //Attach "Progress & Comments" page to the Lab Order
//    /**
//     * @ORM\ManyToMany(targetEntity="History")
//     * @ORM\JoinTable(name="scan_generalorder_history",
//     *      joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="history_id", referencedColumnName="id", unique=true)}
//     *      )
//     **/
//    private $history;



    public function __construct() {
//        $this->history = new ArrayCollection();
    }



//    /**
//     * @param mixed $ordername
//     */
//    public function setOrdername($ordername)
//    {
//        $this->ordername = $ordername;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getOrdername()
//    {
//        return $this->ordername;
//    }
//
//    /**
//     * @param mixed $ordernumber
//     */
//    public function setOrdernumber($ordernumber)
//    {
//        $this->ordernumber = $ordernumber;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getOrdernumber()
//    {
//        return $this->ordernumber;
//    }

//    /**
//     * @param mixed $status
//     */
//    public function setStatus($status)
//    {
//        $this->status = $status;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getStatus()
//    {
//        return $this->status;
//    }

//    /**
//     * @param mixed $comment
//     */
//    public function setComment($comment)
//    {
//        $this->comment = $comment;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getComment()
//    {
//        return $this->comment;
//    }
//
//
//    public function getHistory()
//    {
//        return $this->history;
//    }
//    public function addHistory($history)
//    {
//        if( !$this->history->contains($history) ) {
//            $this->history->add($history);
//            $history->setOrder($this);
//        }
//    }
//    public function removeHistory($history)
//    {
//        $this->history->removeElement($history);
//    }
//
//
//
//    public function __toString() {
//        return $this->getOrdername()." ".$this->getOrdernumber();
//    }

}