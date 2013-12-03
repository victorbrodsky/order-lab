<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\StatusRepository")
 * @ORM\Table(name="status")
 */
class Status
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * name: show name in MyOrder table (i.e. Cancelled)
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * action: show this 'action' column in the Action menu and the 'name' column in the Filter menu. (i.e. Cancel)
     * @ORM\Column(type="string", length=200)
     * @Assert\NotBlank
     */
    protected $action;

    /**
     * @ORM\ManyToOne(targetEntity="StatusType", inversedBy="status", cascade={"persist"})
     * @ORM\JoinColumn(name="statustype_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $type;

    //Group can be removed(?). It is not used for now and probably will not be used in the future.
    /**
     * @ORM\ManyToOne(targetEntity="StatusGroup", inversedBy="status", cascade={"persist"})
     * @ORM\JoinColumn(name="statusgroup_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $group;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Status
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
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
     * Set type
     *
     * @param \Oleg\OrderformBundle\Entity\StatusType $type
     * @return Status
     */
    public function setType(\Oleg\OrderformBundle\Entity\StatusType $type = null)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return \Oleg\OrderformBundle\Entity\StatusType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set group
     *
     * @param \Oleg\OrderformBundle\Entity\StatusGroup $group
     * @return Status
     */
    public function setGroup(\Oleg\OrderformBundle\Entity\StatusGroup $group = null)
    {
        $this->group = $group;
    
        return $this;
    }

    /**
     * Get group
     *
     * @return \Oleg\OrderformBundle\Entity\StatusGroup 
     */
    public function getGroup()
    {
        return $this->group;
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