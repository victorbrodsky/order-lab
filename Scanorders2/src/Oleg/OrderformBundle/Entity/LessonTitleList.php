<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_lessonTitleList")
 */
class LessonTitleList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="LessonTitleList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="LessonTitleList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="CourseTitleList", inversedBy="lessonTitles", cascade={"persist"})
     * @ORM\JoinColumn(name="courseTitle_id", referencedColumnName="id", nullable=true)
     */
    protected $courseTitle;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
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


}