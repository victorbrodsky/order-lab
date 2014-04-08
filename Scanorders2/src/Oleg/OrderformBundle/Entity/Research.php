<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\ResearchRepository")
 * @ORM\Table(name="research")
 */
class Research
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $project;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $settitle;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="principal_id", referencedColumnName="id")
     */
    protected $principal;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $principalstr;

    /**
     * @ORM\OneToOne(targetEntity="OrderInfo", mappedBy="research")
     */
    protected $orderinfo;

    /**
     * @ORM\OneToOne(targetEntity="Slide", mappedBy="research")
     */
    protected $slide;

//    public function __clone() {
//        if ($this->id) {
//            $this->setId(null);
//        }
//    }

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
     * Set project
     *
     * @param string $project
     * @return Research
     */
    public function setProject($project)
    {
        $this->project = $project;
    
        return $this;
    }

    /**
     * Get project
     *
     * @return string 
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $principal
     */
    public function setPrincipal($principal)
    {
        $this->principal = $principal;
    }

    /**
     * @return mixed
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    /**
     * @param mixed $principalstr
     */
    public function setPrincipalstr($principalstr)
    {
        $this->principalstr = $principalstr;
    }

    /**
     * @return mixed
     */
    public function getPrincipalstr()
    {
        return $this->principalstr;
    }

    /**
     * @param mixed $settitle
     */
    public function setSettitle($settitle)
    {
        $this->settitle = $settitle;
    }

    /**
     * @return mixed
     */
    public function getSettitle()
    {
        return $this->settitle;
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

    /**
     * @param mixed $slide
     */
    public function setSlide($slide)
    {
        $this->slide = $slide;
    }

    /**
     * @return mixed
     */
    public function getSlide()
    {
        return $this->slide;
    }



    public function isEmpty()
    {
        if( $this->project || $this->settitle || $this->principalstr ) {
            return false;
        } else {
            return true;
        }
    }

    public function __toString(){

        return "Research: id=".$this->id.", project=".$this->project.", principal=".$this->principal."<br>";
    }

}