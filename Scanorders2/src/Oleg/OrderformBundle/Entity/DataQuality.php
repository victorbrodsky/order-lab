<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="dataquality")
 */
class DataQuality
{

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="OrderInfo", inversedBy="dataquality", cascade={"persist"})
     * @ORM\JoinColumn(name="orderinfo_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $orderinfo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $accession;

    /**
     * @ORM\ManyToOne(targetEntity="AccessionType", cascade={"persist"})
     * @ORM\JoinColumn(name="accessiontype_id", referencedColumnName="id", nullable=true)
     */
    protected $accessiontype;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $newaccession;

    /**
     * @ORM\ManyToOne(targetEntity="AccessionType", cascade={"persist"})
     * @ORM\JoinColumn(name="newaccessiontype_id", referencedColumnName="id", nullable=true)
     */
    protected $newaccessiontype;

//    /**
//     * @ORM\ManyToOne(targetEntity="AccessionAccession", cascade={"persist"})
//     * @ORM\JoinColumn(name="accession_id", referencedColumnName="id", nullable=true)
//     */
//    protected $accession;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="AccessionAccession", cascade={"persist"})
//     * @ORM\JoinColumn(name="newaccession_id", referencedColumnName="id", nullable=true)
//     */
//    protected $newaccession;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $mrn;

    /**
     * @ORM\ManyToOne(targetEntity="MrnType", cascade={"persist"})
     * @ORM\JoinColumn(name="mrntype_id", referencedColumnName="id", nullable=true)
     */
    protected $mrntype;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $creationdate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resolvedate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id")
     */
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="resolver_id", referencedColumnName="id")
     */
    private $resolver;


    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreationdate()
    {
        $this->creationdate = new \DateTime();
    }

    public function getCreationdate()
    {
        return $this->creationdate;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $orderinfo
     */
    public function setOrderinfo($orderinfo)
    {
        $this->orderinfo = $orderinfo;
    }

    /**
     * @return mixed
     */
    public function getOrderinfo()
    {
        return $this->orderinfo;
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
     * @param mixed $resolver
     */
    public function setResolver($resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @return mixed
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * @param \DateTime $resolvedate
     */
    public function setResolvedate($resolvedate)
    {
        $this->resolvedate = $resolvedate;
    }

    /**
     * @return \DateTime
     */
    public function getResolvedate()
    {
        return $this->resolvedate;
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
     * @param mixed $accession
     */
    public function setAccession($accession)
    {
        $this->accession = $accession;
    }

    /**
     * @return mixed
     */
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * @param mixed $mrn
     */
    public function setMrn($mrn)
    {
        $this->mrn = $mrn;
    }

    /**
     * @return mixed
     */
    public function getMrn()
    {
        return $this->mrn;
    }

    /**
     * @param mixed $mrntype
     */
    public function setMrntype($mrntype)
    {
        $this->mrntype = $mrntype;
    }

    /**
     * @return mixed
     */
    public function getMrntype()
    {
        return $this->mrntype;
    }

    /**
     * @param mixed $newaccession
     */
    public function setNewaccession($newaccession)
    {
        $this->newaccession = $newaccession;
    }

    /**
     * @return mixed
     */
    public function getNewaccession()
    {
        return $this->newaccession;
    }

    /**
     * @param mixed $accessiontype
     */
    public function setAccessiontype($accessiontype)
    {
        $this->accessiontype = $accessiontype;
    }

    /**
     * @return mixed
     */
    public function getAccessiontype()
    {
        return $this->accessiontype;
    }

    /**
     * @param mixed $newaccessiontype
     */
    public function setNewaccessiontype($newaccessiontype)
    {
        $this->newaccessiontype = $newaccessiontype;
    }

    /**
     * @return mixed
     */
    public function getNewaccessiontype()
    {
        return $this->newaccessiontype;
    }



}