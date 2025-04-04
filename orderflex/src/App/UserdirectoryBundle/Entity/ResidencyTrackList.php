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

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Table(name: 'user_residencyTrackList')]
#[ORM\Entity]
class ResidencyTrackList extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'ResidencyTrackList', mappedBy: 'original')]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'ResidencyTrackList', inversedBy: 'synonyms')]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id')]
    protected $original;

    #[ORM\ManyToOne(targetEntity: 'Institution')]
    #[ORM\JoinColumn(name: 'institution_id', referencedColumnName: 'id', nullable: true)]
    private $institution;

//    /**
    //     * @ORM\OneToOne(targetEntity="App\ResAppBundle\Entity\ResAppTypeConfig", cascade={"persist", "remove"})
    //     * @ORM\JoinColumn(name="resapptypeconfig_id", referencedColumnName="id", nullable=true)
    //     */
    //    private $resappTypeConfig;
    #[ORM\JoinTable(name: 'user_residencytrack_coordinator')]
    #[ORM\JoinColumn(name: 'residencytrack_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'coordinator_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'User')]
    private $coordinators;

    #[ORM\JoinTable(name: 'user_residencytrack_director')]
    #[ORM\JoinColumn(name: 'residencytrack_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'director_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'User')]
    private $directors;

    #[ORM\JoinTable(name: 'user_residencytrack_interviewer')]
    #[ORM\JoinColumn(name: 'residencytrack_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'interviewer_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'User')]
    private $interviewers;

    //Expected Duration (in years)
    //A- For AP/CP, “Expected Duration (in years): 4
    //B- For AP/EXP, “Expected Duration (in years): 4
    //C- For CP/EXP, “Expected Duration (in years): 4
    //D- For AP, “Expected Duration (in years): 3
    //E- For CP, “Expected Duration (in years): 3
    #[ORM\Column(type: 'integer', nullable: true)]
    private $duration;

    /**
     * Application season start date
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $seasonYearStart;

    /**
     * Application season end date
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $seasonYearEnd;


    public function __construct( $author = null ) {
        $this->coordinators = new ArrayCollection();
        $this->directors = new ArrayCollection();
        $this->interviewers = new ArrayCollection();

        parent::__construct($author);
    }
    
    

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

//    /**
//     * @return mixed
//     */
//    public function getResappTypeConfig()
//    {
//        return $this->resappTypeConfig;
//    }
//
//    /**
//     * @param mixed $resappTypeConfig
//     */
//    public function setResappTypeConfig($resappTypeConfig)
//    {
//        $this->resappTypeConfig = $resappTypeConfig;
//    }

    public function addCoordinator($item)
    {
        if( $item && !$this->coordinators->contains($item) ) {
            $this->coordinators->add($item);
        }
        return $this;
    }
    public function removeCoordinator($item)
    {
        $this->coordinators->removeElement($item);
    }
    public function getCoordinators()
    {
        return $this->coordinators;
    }

    public function addDirector($item)
    {
        if( $item && !$this->directors->contains($item) ) {
            $this->directors->add($item);
        }
        return $this;
    }
    public function removeDirector($item)
    {
        $this->directors->removeElement($item);
    }
    public function getDirectors()
    {
        return $this->directors;
    }

    public function addInterviewer($item)
    {
        if( $item && !$this->interviewers->contains($item) ) {
            $this->interviewers->add($item);
        }
        return $this;
    }
    public function removeInterviewer($item)
    {
        $this->interviewers->removeElement($item);
    }
    public function getInterviewers()
    {
        return $this->interviewers;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getSeasonYearStart()
    {
        return $this->seasonYearStart;
    }

    /**
     * @param mixed $seasonYearStart
     */
    public function setSeasonYearStart($seasonYearStart)
    {
        $this->seasonYearStart = $seasonYearStart;
    }

    /**
     * @return mixed
     */
    public function getSeasonYearEnd()
    {
        return $this->seasonYearEnd;
    }

    /**
     * @param mixed $seasonYearEnd
     */
    public function setSeasonYearEnd($seasonYearEnd)
    {
        $this->seasonYearEnd = $seasonYearEnd;
    }


    

    public function getClassName()
    {
        return "ResidencyTrackList";
    }
}