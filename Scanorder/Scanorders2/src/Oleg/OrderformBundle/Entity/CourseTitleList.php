<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_courseTitleList")
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

    //list of the lesson titles belongs to this course title.
    /**
     * @ORM\OneToMany(targetEntity="LessonTitleList", mappedBy="courseTitle", cascade={"persist"})
     */
    protected $lessonTitles;

    /**
     * @ORM\ManyToMany(targetEntity="DirectorList", inversedBy="courses", cascade={"persist"})
     * @ORM\JoinTable(name="scan_courses_directors")
     **/
    protected $directors;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $primaryDirector;


    public function __construct() {
        //$this->educational = new ArrayCollection();
        $this->lessonTitles = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
        $this->directors = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param CourseTitleList $synonyms
     * @return CourseTitleList
     */
    public function addSynonym(CourseTitleList $synonyms)
    {
        $this->synonyms->add($synonyms);

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param CourseTitleList $synonyms
     */
    public function removeSynonym(CourseTitleList $synonyms)
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


    public function addLessonTitle(LessonTitleList $lessonTitle = null)
    {
        if( $lessonTitle && !$this->lessonTitles->contains($lessonTitle) ) {
            $this->lessonTitles->add($lessonTitle);
            $lessonTitle->setCourseTitle($this);
        }
        return $this;
    }

    public function removeLessonTitle(LessonTitleList $lessonTitle)
    {
        $this->lessonTitles->removeElement($lessonTitle);
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

    /**
     * @param mixed $primaryDirector
     */
    public function setPrimaryDirector($primaryDirector)
    {
        $this->primaryDirector = $primaryDirector;
    }

    /**
     * @return mixed
     */
    public function getPrimaryDirector()
    {
        return $this->primaryDirector;
    }


    /**
     * Add directors
     *
     * @param DirectorList $directors
     * @return CourseTitleList
     */
    public function addDirector(DirectorList $director)
    {
        if( !$this->directors->contains($director) ) {
            $this->directors->add($director);
        }

        return $this;
    }

    /**
     * Remove directors
     *
     * @param DirectorList $directors
     */
    public function removeDirector(DirectorList $director)
    {
        $this->directors->removeElement($director);
    }

    /**
     * Get directors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDirectors() {

        $resArr = new ArrayCollection();
        foreach( $this->directors as $director ) {

            if( $director->getId()."" == $this->getPrimaryDirector()."" ) {  //this director is a primary one => put as the first element

                $firstEl = $resArr->first();
                if( count($this->directors) > 1 && $firstEl ) {

                    $resArr->set(0,$director); //set( mixed $key, mixed $value ) Adds/sets an element in the collection at the index / with the specified key.
                    $resArr->add($firstEl);
                } else {
                    $resArr->add($director);
                }
            } else {    //this director is not a primary one
                $resArr->add($director);
            }
        }

        return $resArr;
    }


    public function setDirectors( $directors )
    {
        //set primary Director
        if( $directors->first() ) {
            $this->primaryDirector = $directors->first()->getId();
        } else {
            $this->primaryDirector = NULL;
        }

        $this->directors = $directors;

        return $this;
    }

}