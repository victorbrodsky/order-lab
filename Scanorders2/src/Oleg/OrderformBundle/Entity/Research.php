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

    public function isEmpty()
    {
        //return $this->getProjectTitle()->isEmtpy();
        if( $this->getProjectTitle() && $this->getProjectTitle()->getName() != '' ) {
            return false;
        } else {
            return true;
        }
    }

    public function __toString(){
        //return "Research: id=".$this->id.", project=".$this->projectTitle.", project type=".$this->getProjectTitle()->getType().", principal=".$this->principal.", countSetTitles=".count($this->projectTitle->getSetTitles())."<br>";
        return "Research: id=".$this->id.", project=".$this->projectTitle."<br>";
    }

}