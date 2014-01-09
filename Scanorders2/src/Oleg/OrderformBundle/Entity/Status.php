<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

//(repositoryClass="Oleg\OrderformBundle\Repository\StatusRepository")
/**
 * @ORM\Entity
 * @ORM\Table(name="status")
 */
class Status extends ListAbstract
{

    /**
     * action: show this 'action' column in the Action menu and the 'name' column in the Filter menu. (i.e. Cancel)
     * @ORM\Column(type="string", nullable=true)
     */
    protected $action;

    /**
     * @ORM\OneToMany(targetEntity="OrderInfo", mappedBy="status")
     */
    protected $orderinfo;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderinfo = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString() {
        return $this->name;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Status
     */
    public function setAction($action)
    {
        $this->action = $action;
    
        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Add orderinfo
     *
     * @param \Oleg\OrderformBundle\Entity\OrderInfo $orderinfo
     * @return Status
     */
    public function addOrderinfo(\Oleg\OrderformBundle\Entity\OrderInfo $orderinfo)
    {
        //echo "Status addOrderinfo=".$orderinfo."<br>";
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
}