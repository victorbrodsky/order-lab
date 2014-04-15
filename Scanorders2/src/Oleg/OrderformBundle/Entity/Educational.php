<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\EducationalRepository")
 * @ORM\Table(name="educational")
 */
class Educational
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CourseTitleList", inversedBy="educational")
     * @ORM\JoinColumn(name="courseTitle_id", referencedColumnName="id", nullable=true)
     */
    protected $courseTitle;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="director_id", referencedColumnName="id")
     */
    protected $director;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $directorstr;

    /**
     * @ORM\OneToOne(targetEntity="OrderInfo", mappedBy="educational")
     */
    protected $orderinfo;

    /**
     * @ORM\OneToOne(targetEntity="Slide", mappedBy="educational")
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
     * @param mixed $director
     */
    public function setDirector($director)
    {
        $this->director = $director;
    }

    /**
     * @return mixed
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * @param mixed $directorstr
     */
    public function setDirectorstr($directorstr)
    {
        $this->directorstr = $directorstr;
    }

    /**
     * @return mixed
     */
    public function getDirectorstr()
    {
        return $this->directorstr;
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
     * @param mixed $courseTitle
     */
    public function setCourseTitle($courseTitle)
    {
        $this->courseTitle = $courseTitle;
    }

    /**
     * @return mixed
     */
    public function getCourseTitle()
    {
        return $this->courseTitle;
    }




    public function isEmpty()
    {
        if( $this->courseTitle || $this->directorstr != '' ) {
            return false;
        } else {
            return true;
        }
    }


    public function __toString(){

        //return "Educational: id=".$this->id.", course=".$this->courseTitle.", course type=".$this->getCourseTitle()->getType().", director=".$this->director.", countLessonTitles=".count($this->courseTitle->getLessonTitles())."<br>";
        return "Educational: id=".$this->id.", course=".$this->courseTitle.", director=".$this->director."<br>";
    }


}