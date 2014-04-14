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
     * @ORM\ManyToOne(targetEntity="ProjectTitleList", inversedBy="research")
     * @ORM\JoinColumn(name="projectTitle_id", referencedColumnName="id", nullable=true)
     */
    protected $projectTitle;

//    /**
//     * @ORM\ManyToOne(targetEntity="SetTitleList", inversedBy="research", cascade={"persist"})
//     * @ORM\JoinColumn(name="setTitle_id", referencedColumnName="id", nullable=true)
//     */
//    protected $setTitle;

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

    /**
     * @param mixed $projectTitle
     */
    public function setProjectTitle($projectTitle)
    {
        $this->projectTitle = $projectTitle;
    }

    /**
     * @return mixed
     */
    public function getProjectTitle()
    {
        return $this->projectTitle;
    }

//    /**
//     * @param mixed $setTitle
//     */
//    public function setSetTitle($setTitle)
//    {
//        $this->setTitle = $setTitle;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSetTitle()
//    {
//        return $this->setTitle;
//    }


    public function isEmpty()
    {
        if( $this->projectTitle || $this->principalstr ) {
            return false;
        } else {
            return true;
        }
    }

    public function __toString(){

        return "Research: id=".$this->id.", project=".$this->projectTitle.", project type=".$this->getProjectTitle()->getType().", principal=".$this->principal.", countSetTitles=".count($this->projectTitle->getSetTitles())."<br>";
    }

}