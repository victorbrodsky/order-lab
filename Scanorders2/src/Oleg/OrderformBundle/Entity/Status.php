<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_status")
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
     * @ORM\OneToMany(targetEntity="Status", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderinfo = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
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