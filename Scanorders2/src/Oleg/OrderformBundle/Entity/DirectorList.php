<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;
use Oleg\UserdirectoryBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_directorList")
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
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
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
     * Set director
     *
     * @param User $director
     * @return DirectorList
     */
    public function setDirector(User $director = null)
    {
        $this->director = $director;
    
        return $this;
    }

    /**
     * Get director
     *
     * @return User
     */
    public function getDirector()
    {
        return $this->director;
    }

    public function setUserObjectLink(User $user = null) {
        $this->setDirector($user);
    }

    public function getUserObjectLink() {
        return $this->getDirector();
    }

    /**
     * Add courses
     *
     * @param CourseTitleList $courses
     * @return DirectorList
     */
    public function addCourse(CourseTitleList $courses)
    {
        if( !$this->courses->contains($courses) ) {
            $this->courses->add($courses);
        }
    
        return $this;
    }

    /**
     * Remove courses
     *
     * @param CourseTitleList $courses
     */
    public function removeCourse(CourseTitleList $courses)
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