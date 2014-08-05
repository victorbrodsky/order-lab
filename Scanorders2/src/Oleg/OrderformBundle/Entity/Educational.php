<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

//indexes={@Index(name="user_idx", columns={"email"})}
//,@ORM\@Index(name="lessonTitleStr_idx", columns={"lessonTitleStr"})
//@ORM\Table(indexes={@ORM\Index(name="email_address_idx", columns={"email_address"})})

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\EducationalRepository")
 * @ORM\Table( name="educational",
 *  indexes={
 *      @ORM\Index( name="courseTitleStr_idx", columns={"courseTitleStr"} ),
 *      @ORM\Index( name="lessonTitleStr_idx", columns={"lessonTitleStr"} )
 *  }
 * )
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
     * @ORM\OneToOne(targetEntity="OrderInfo", mappedBy="educational")
     */
    protected $orderinfo;

    /**
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="educational")
     */
    protected $slides;

    //directors
    /**
     * Keep info as director name as entered by a user and id to a director object
     * @ORM\OneToMany(targetEntity="DirectorWrapper", mappedBy="educational", cascade={"persist"})
     * @ORM\JoinColumn(name="director_id", referencedColumnName="id", nullable=true)
     */
    protected $directorWrappers;

    //course
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $courseTitleStr;

    /**
     * @ORM\ManyToOne(targetEntity="CourseTitleList", inversedBy="educational", cascade={"persist"})
     * @ORM\JoinColumn(name="courseTitle_id", referencedColumnName="id", nullable=true)
     */
    protected $courseTitle;

    //lesson
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lessonTitleStr;

    /**
     * @ORM\ManyToOne(targetEntity="LessonTitleList", cascade={"persist"})
     * @ORM\JoinColumn(name="lessonTitle_id", referencedColumnName="id", nullable=true)
     */
    protected $lessonTitle;

    /**
     * primarySet - name of the primary Director. Indicates if the primaryDirector was set by this order
     * @ORM\Column(type="string", nullable=true)
     */
    protected $primarySet;


    public function __construct() {
        $this->directorWrappers = new ArrayCollection();
        $this->slides = new ArrayCollection();
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

//    /**
//     * @param mixed $slide
//     */
//    public function setSlide($slide)
//    {
//        $this->slide = $slide;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSlide()
//    {
//        return $this->slide;
//    }
    /**
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     * @return Block
     */
    public function addSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slides->contains($slide) ) {
            $slide->setEducational($this);
            $this->slides->add($slide);
        }

        return $this;
    }

    /**
     * Remove slide
     *
     * @param \Oleg\OrderformBundle\Entity\Slide $slide
     */
    public function removeSlide(\Oleg\OrderformBundle\Entity\Slide $slide)
    {
        $this->slides->removeElement($slide);
    }

    /**
     * Get slide
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSlides()
    {
        return $this->slides;
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

    /**
     * @param mixed $courseTitleStr
     */
    public function setCourseTitleStr($courseTitleStr)
    {
        $this->courseTitleStr = $courseTitleStr;
    }

    /**
     * @return mixed
     */
    public function getCourseTitleStr()
    {
        return $this->courseTitleStr;
    }

    /**
     * @param mixed $lessonTitle
     */
    public function setLessonTitle($lessonTitle)
    {
        $this->lessonTitle = $lessonTitle;
    }

    /**
     * @return mixed
     */
    public function getLessonTitle()
    {
        return $this->lessonTitle;
    }

    /**
     * @param mixed $lessonTitleStr
     */
    public function setLessonTitleStr($lessonTitleStr)
    {
        $this->lessonTitleStr = $lessonTitleStr;
    }

    /**
     * @return mixed
     */
    public function getLessonTitleStr()
    {
        return $this->lessonTitleStr;
    }

    /**
     * @return mixed
     */
    public function getDirectorWrappers()
    {
        //entity is DirectorWrapper class => order will show the same order as entered by a user
        return $this->directorWrappers;

        //entity is DirectorList class => we can shows Primary Director as the first Director
        //return $this->getCourseTitle()->getDirectors(); //to keep order according to Primary Director
    }

    /**
     * Add DirectorWrappers
     *
     * @param \Oleg\OrderformBundle\Entity\DirectorList $Director
     * @return Educational
     */
    public function addDirectorWrapper($director)
    {
        $directorWrapper = new directorWrapper();
        $directorWrapper->setDirectorStr( $director->getName() );
        $directorWrapper->setDirector( $director );

        if( !$this->directorWrappers->contains($directorWrapper) ) {
            $this->directorWrappers->add($directorWrapper);
            $directorWrapper->setEducational($this);
        }

        return $this;
    }

    /**
     * Remove directorWrappers
     *
     * @param \Oleg\OrderformBundle\Entity\DirectorWrappers $directorWrappers
     */
    public function removeDirectorWrapper(\Oleg\OrderformBundle\Entity\DirectorWrapper $directorWrapper)
    {
        $this->directorWrappers->removeElement($directorWrapper);
    }

    /**
     * @param mixed $primarySet
     */
    public function setPrimarySet($primarySet)
    {
        $this->primarySet = $primarySet;
    }

    /**
     * @return mixed
     */
    public function getPrimarySet()
    {
        return $this->primarySet;
    }




    public function isEmpty()
    {
        if( $this->getCourseTitleStr() == '' ) {
            return true;
        } else {
            return false;
        }
    }


    public function __toString(){

        //return "Educational: id=".$this->id.", course=".$this->courseTitle.", course type=".$this->getCourseTitle()->getType().", director=".$this->director.", countLessonTitles=".count($this->courseTitle->getLessonTitles())."<br>";
        return "Educational: id=".$this->id.", course=".$this->courseTitle."<br>";
    }


}