<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="slideReturnRequest")
 * @ORM\HasLifecycleCallbacks
 */
class SlideReturnRequest extends OrderAbstract implements OrderSubjectInterface {

    // Any methods defined in the OrderSubjectInterface
    // are already implemented in the OrderAbstract

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="orderdate", type="datetime", nullable=true)
     *
     */
    private $orderdate;

    /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="orderinfo", cascade={"persist"})
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="FormType", cascade={"persist"})
     * @ORM\JoinColumn(name="formtype_id", referencedColumnName="id")
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity="User", cascade={"persist"})
     * @ORM\JoinTable(name="provider_orderinfo",
     *      joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="provider_id", referencedColumnName="id")}
     * )
     */
    private $provider;

    /**
     * @ORM\OneToMany(targetEntity="History", mappedBy="orderinfo", cascade={"persist"})
     */
    private $history;


    //////////////////////////////////
    /**
     * @var string
     *
     * @ORM\Column(name="returnSlide", type="string", nullable=true)
     * @Assert\NotBlank
     */
    protected $returnSlide;

    /**
     * @var string
     *
     * @ORM\Column(name="urgency", type="string", nullable=true)
     * @Assert\NotBlank
     */
    protected $urgency;

    /**
     * @ORM\ManyToMany(targetEntity="Slide", inversedBy="orderinfo")
     * @ORM\JoinTable(name="slide_orderinfo")
     **/
    private $slide;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slide = new ArrayCollection();
        $this->provider = new ArrayCollection();
        $this->history = new ArrayCollection();
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

    public function getHistory()
    {
        return $this->history;
    }

    public function addHistory($history)
    {
        if( !$this->history->contains($history) ) {
            $this->history->add($history);
        }
    }

    public function removeHistory($history)
    {
        $this->history->removeElement($history);
    }

    /**
     * Set returnSlide
     *
     * @param string $returnSlide
     * @return OrderInfo
     */
    public function setReturnSlide($returnSlide)
    {
        $this->returnSlide = $returnSlide;
    
        return $this;
    }

    /**
     * Get returnSlide
     *
     * @return string 
     */
    public function getReturnSlide()
    {
        return $this->returnSlide;
    }

    /**
     * Set provider
     *
     * @param \stdClass $provider
     * @return OrderInfo
     */
    public function setProvider($provider)
    {
        if( is_array($provider ) ) {
            $this->provider = $provider;
        } else {
            $this->provider->clear();
            $this->provider->add($provider);
        }
    
        return $this;
    }

    /**
     * Get provider
     *
     * @return \stdClass 
     */
    public function getProvider()
    {
        return $this->provider;
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function getType() {
        return $this->type;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setType($type) {
        $this->type = $type;
    }

    /**
     * Add slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return OrderInfo
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slide->contains($slide) ) {
            $this->slide->add($slide);
        }    
    }

    /**
     * Remove slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     */
    public function removeSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        $this->slide->removeElement($slide);
    }

    /**
     * Get slide
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSlide()
    {
        return $this->slide;
    }

    public function addProvider(\Oleg\OrderformBundle\Entity\User $provider)
    {
        if( !$this->provider->contains($provider) ) {
            $this->provider->add($provider);
        }

        return $this;
    }

    public function removeProvider(\Oleg\OrderformBundle\Entity\User $provider)
    {
        $this->provider->removeElement($provider);
    }

}