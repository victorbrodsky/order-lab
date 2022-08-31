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

/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/22/14
 * Time: 9:30 AM
 * To change this template use File | Settings | File Templates.
 */

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

//* @ORM\Table(name="scan_research",
// *  indexes={
//    *      @ORM\Index( name="projectTitleStr_idx", columns={"projectTitleStr"} ),
// *      @ORM\Index( name="setTitleStr_idx", columns={"setTitleStr"} )
// *  }

/**
 * @ORM\Entity(repositoryClass="App\OrderformBundle\Repository\ResearchRepository")
 * @ORM\Table(name="scan_research")
 */
class Research
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Message", mappedBy="research")
     */
    private $message;

    /**
     * @ORM\OneToMany(targetEntity="Slide", mappedBy="research")
     */
    private $slides;

    //principal as entered by a user. Use a wrapper because research can have multiple PIs
    /**
     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\UserWrapper", cascade={"persist","remove"})
     * @ORM\JoinTable(name="scan_research_userWrapper",
     *      joinColumns={@ORM\JoinColumn(name="research_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userWrapper_id", referencedColumnName="id")}
     *      )
     **/
    private $userWrappers;

    /**
     * primarySet - name of the primary PI. Indicates if the primaryPrincipal was set by this order
     * @ORM\Column(type="string", nullable=true)
     */
    private $primarySet;


    /**
     * @ORM\ManyToOne(targetEntity="ProjectTitleTree", inversedBy="researches", cascade={"persist"})
     */
    private $projectTitle;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\UserWrapper",cascade={"persist"})
     */
    private $primaryPrincipal;




    public function __construct() {
        $this->slides = new ArrayCollection();

        $this->userWrappers = new ArrayCollection();
    }

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
     * @param \App\OrderformBundle\Entity\Slide $slide
     * @return Block
     */
    public function addSlide(\App\OrderformBundle\Entity\Slide $slide)
    {
        if( !$this->slides->contains($slide) ) {
            $slide->setResearch($this);
            $this->slides->add($slide);
        }

        return $this;
    }

    /**
     * Remove slide
     *
     * @param \App\OrderformBundle\Entity\Slide $slide
     */
    public function removeSlide(\App\OrderformBundle\Entity\Slide $slide)
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

    /**
     * @return mixed
     */
    public function getUserWrappers()
    {
        return $this->userWrappers;
    }

    /**
     * Add userWrappers
     *
     * @param $userWrappers
     * @return Research
     */
    public function addUserWrapper($userWrapper)
    {
        if( !$this->userWrappers->contains($userWrapper) ) {
            $this->userWrappers->add($userWrapper);
//            if( $this->getProjectTitle() ) {
//                $this->getProjectTitle()->addUserWrapper($userWrapper);
//            }
        }

        return $this;
    }

    /**
     * Remove userWrappers
     *
     * @param userWrappers $userWrappers
     */
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
        if( $this->getProjectTitle()."" == "" ) {
            return true;
        } else {
            return false;
        }
    }


    public function __toString(){
        //return "Research: id=".$this->id.", project=".$this->projectTitle.", project type=".$this->getProjectTitle()->getType().", principal=".$this->principal.", countSetTitles=".count($this->projectTitle->getSetTitles())."<br>";
        return "Research: id=".$this->id.", project=".$this->projectTitle."<br>";
    }

}