<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="LessonTitleList")
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
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\LessonTitleList $synonyms
     * @return LessonTitleList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\LessonTitleList $synonyms)
    {
        $this->synonyms->add($synonyms);

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\LessonTitleList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\LessonTitleList $synonyms)
    {
        $this->synonyms->removeElement($synonyms);
    }

    /**
     * Get synonyms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * @param mixed $original
     */
    public function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
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