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
     * @var \DateTime
     *
     * @ORM\Column(name="orderdate", type="datetime", nullable=true)
     *
     */
    protected $orderdate;

//    /**
//     * @ORM\ManyToOne(targetEntity="Status", inversedBy="orderinfo", cascade={"persist"})
//     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=true)
//     */
//    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="FormType", cascade={"persist"})
     * @ORM\JoinColumn(name="formtype", referencedColumnName="id")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="provider", referencedColumnName="id")
     */
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="proxyuser", referencedColumnName="id")
     */
    protected $proxyuser;

    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     * @ORM\JoinColumn(name="institution", referencedColumnName="id")
     */
    protected $institution;



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



}