<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class OrderAbstract
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * status: use to indicate if the entity with this key is reserved only but not submitted
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $creationdate;

    public function __construct() {
        $this->orderinfo = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreationdate()
    {
        $this->creationdate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreationdate()
    {
        return $this->creationdate;
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
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     */
    public function addOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo=null)
    {
        if( !$this->orderinfo->contains($orderinfo) ) {
            $this->orderinfo->add($orderinfo);
        }
    }

    /**
     * Remove orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     */
    public function removeOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
    {
        $this->orderinfo->removeElement($orderinfo);
    }

    /**
     * Get orderinfo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
    }

    public function getValidChild()
    {
        $validChild = null;
        $count = 0;
        //echo "number of children=: ".count($this->getChildren())."<br>";
        foreach( $this->getChildren() as $child) {
            //echo "get valid: ".$child."<br>";
            if( $child->getStatus()."" == "valid" ) {
                $validChild = $child;
                $count++;
            }
        }
//        if( $count > 1 ) {
//            throw $this->createNotFoundException( 'This Object must have only one valid child. Number of valid children=' . $count );
//        }
        return $validChild;
    }

    //return the key field with validity 1 or return a single existing key field
    public function getValidKeyfield()
    {
        if( count($this->obtainKeyField()) == 1 ) {
            return $this->obtainKeyField()->first();
        }

        $validChild = null;
        $count = 0;
        //echo "number of children=: ".count($this->getChildren())."<br>";
        foreach( $this->obtainKeyField() as $child) {
            //echo "get valid: ".$child."<br>";
            if( $child->getValidity() == 1 ) {
                $validChild = $child;
                $count++;
            }
        }
//        if( $count > 1 ) {
//            throw $this->createNotFoundException( 'This Object must have only one valid child. Number of valid children=' . $count );
//        }
        return $validChild;
    }

}