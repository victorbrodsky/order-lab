<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

//*  indexes={
//    *      @ORM\Index( name="courseTitleStr_idx", columns={"courseTitleStr"} ),
// *      @ORM\Index( name="lessonTitleStr_idx", columns={"lessonTitleStr"} )
// *  }

/**
 * @ORM\Entity(repositoryClass="Oleg\OrderformBundle\Repository\EducationalRepository")
 * @ORM\Table( name="scan_educational")
 */
class Educational
{
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="educational")
     */
    private $message;

    /**
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="educational")
     */
    private $slides;

    //directors
    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_educational_userWrapper",
     *      joinColumns={@ORM\JoinColumn(name="educational_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userWrapper_id", referencedColumnName="id")}
     *      )
     **/
    private $userWrappers;

    /**
     * @ORM\ManyToOne(targetEntity="CourseTitleTree", inversedBy="educationals", cascade={"persist"})
     */
    private $courseTitle;

    /**
     * primaryDirector
     *
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\UserWrapper",cascade={"persist"})
     */
    private $primaryPrincipal;


    /**
     * primarySet - name of the primary Director. Indicates if the primaryDirector was set by this order
     * @ORM\Column(type="string", nullable=true)
     */
    private $primarySet;


    public function __construct() {
        $this->userWrappers = new ArrayCollection();
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
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Slide $slide
     * @return Block
     */
    public function addSlide(Slide $slide)
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
     * @param Slide $slide
     */
    public function removeSlide(Slide $slide)
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


    public function getUserWrappers()
    {
        return $this->userWrappers;
    }
    public function addUserWrapper($userWrapper)
    {
        if( !$this->userWrappers->contains($userWrapper) ) {
            $this->userWrappers->add($userWrapper);
//            if( $this->getCourseTitle() ) {
//                $this->getCourseTitle()->addUserWrapper($userWrapper);
//            }
        }

        return $this;
    }
    public function removeUserWrapper($userWrappers)
    {
        $this->userWrappers->removeElement($userWrappers);
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

    /**
     * @param mixed $primaryPrincipal
     */
    public function setPrimaryPrincipal($primaryPrincipal)
    {
        $this->primaryPrincipal = $primaryPrincipal;
    }

    /**
     * @return mixed
     */
    public function getPrimaryPrincipal()
    {
        return $this->primaryPrincipal;
    }



    public function isEmpty()
    {
        if( $this->getCourseTitle()."" == '' ) {
            return true;
        } else {
            return false;
        }
    }


    public function __toString(){
        return "Educational: id=".$this->id.", course=".$this->courseTitle."<br>";
    }


}