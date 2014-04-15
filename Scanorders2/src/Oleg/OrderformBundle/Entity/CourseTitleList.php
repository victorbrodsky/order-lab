<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="CourseTitleList")
 */
class CourseTitleList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="CourseTitleList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="CourseTitleList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\OneToMany(targetEntity="Educational", mappedBy="courseTitle")
     */
    protected $educational;

    //list of the lesson titles belongs to this course title.
    /**
     * @ORM\OneToMany(targetEntity="LessonTitleList", mappedBy="courseTitle", cascade={"persist"})
     */
    protected $lessonTitles;


    public function __construct() {
        $this->educational = new ArrayCollection();
        $this->lessonTitles = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
    }

    public function addEducational(\Oleg\OrderformBundle\Entity\Educational $educational)
    {
        if( !$this->educational->contains($educational) ) {
            $this->educational->add($educational);
        }
        return $this;
    }

    public function removeEducational(\Oleg\OrderformBundle\Entity\Educational $educational)
    {
        $this->educational->removeElement($educational);
    }

    public function getEducational()
    {
        return $this->educational;
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\CourseTitleList $synonyms
     * @return CourseTitleList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\CourseTitleList $synonyms)
    {
        $this->synonyms->add($synonyms);

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\CourseTitleList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\CourseTitleList $synonyms)
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


    public function addLessonTitles(\Oleg\OrderformBundle\Entity\LessonTitleList $lessonTitles)
    {
        if( !$this->lessonTitles->contains($lessonTitles) ) {
            $this->lessonTitles->add($lessonTitles);
        }
        return $this;
    }

    public function removeLessonTitles(\Oleg\OrderformBundle\Entity\LessonTitleList $lessonTitles)
    {
        $this->lessonTitles->removeElement($lessonTitles);
    }

    /**
     * @return mixed
     */
    public function getLessonTitles()
    {
        return $this->lessonTitles;
    }

    public function setLessonTitles( $lessonTitles )
    {
        if( $lessonTitles ) {
            $this->addLessonTitles($lessonTitles);
        } else {
            $this->lessonTitles = new ArrayCollection();
        }
        return $this;
    }



}