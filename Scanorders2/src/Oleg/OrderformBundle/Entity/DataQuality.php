<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;


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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="provider", referencedColumnName="id")
     */
    protected $provider;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="resolver", referencedColumnName="id")
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


}