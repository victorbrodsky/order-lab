<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="DirectorList")
 */
class DirectorList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="DirectorList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="DirectorList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="director_id", referencedColumnName="id")
     */
    protected $director;

    /**
     * @ORM\ManyToMany(targetEntity="CourseTitleList", mappedBy="directors", cascade={"persist"})
     **/
    private $courses;

    //use name as $dircetorstr


    public function __construct() {
        $this->courses = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
    }

    /**
     * Add synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\DirectorList $synonyms
     * @return DirectorList
     */
    public function addSynonym(\Oleg\OrderformBundle\Entity\DirectorList $synonyms)
    {
        if( !$this->synonyms->contains($synonyms) ) {
            $this->synonyms->add($synonyms);
        }

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \Oleg\OrderformBundle\Entity\DirectorList $synonyms
     */
    public function removeSynonym(\Oleg\OrderformBundle\Entity\DirectorList $synonyms)
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
     * Set director
     *
     * @param \Oleg\OrderformBundle\Entity\User $director
     * @return DirectorList
     */
    public function setDirector(\Oleg\OrderformBundle\Entity\User $director = null)
    {
        $this->director = $director;
    
        return $this;
    }

    /**
     * Get director
     *
     * @return \Oleg\OrderformBundle\Entity\User 
     */
    public function getDirector()
    {
        return $this->director;
    }

    public function setUserObjectLink($user) {
        $this->setDirector($user);
    }

    public function getUserObjectLink() {
        return $this->getDirector();
    }

    /**
     * Add courses
     *
     * @param \Oleg\OrderformBundle\Entity\CourseTitleList $courses
     * @return DirectorList
     */
    public function addCourse(\Oleg\OrderformBundle\Entity\CourseTitleList $courses)
    {
        if( !$this->courses->contains($courses) ) {
            $this->courses->add($courses);
        }
    
        return $this;
    }

    /**
     * Remove courses
     *
     * @param \Oleg\OrderformBundle\Entity\CourseTitleList $courses
     */
    public function removeCourse(\Oleg\OrderformBundle\Entity\CourseTitleList $courses)
    {
        $this->courses->removeElement($courses);
    }

    /**
     * Get courses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCourses()
    {
        return $this->courses;
    }

}