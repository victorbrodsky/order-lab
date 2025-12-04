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

#[ORM\Table(name: 'user_training')]
#[ORM\Entity]
class Training extends BaseUserAttributes
{

    #[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'trainings')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private $user;

    #[ORM\Column(type: 'date', nullable: true)]
    private $startDate;

    #[ORM\Column(type: 'date', nullable: true)]
    private $completionDate;

    #[ORM\ManyToOne(targetEntity: 'CompletionReasonList')]
    private $completionReason;

    #[ORM\ManyToOne(targetEntity: 'TrainingDegreeList')]
    private $degree;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $appendDegreeToName;

    /**
     * Contains children - FellowshipSubspecialty
     */
    #[ORM\ManyToOne(targetEntity: 'ResidencySpecialty')]
    private $residencySpecialty;

    #[ORM\ManyToOne(targetEntity: 'ResidencyTrackList')]
    private $residencyTrack;

    #[ORM\ManyToOne(targetEntity: 'FellowshipSubspecialty')]
    private $fellowshipSubspecialty;

    #[ORM\JoinTable(name: 'user_trainings_majors')]
    #[ORM\ManyToMany(targetEntity: 'MajorTrainingList')]
    private $majors;

    #[ORM\JoinTable(name: 'user_trainings_minors')]
    #[ORM\ManyToMany(targetEntity: 'MinorTrainingList')]
    private $minors;

    #[ORM\JoinTable(name: 'user_trainings_honors')]
    #[ORM\ManyToMany(targetEntity: 'HonorTrainingList')]
    private $honors;

    #[ORM\ManyToOne(targetEntity: 'FellowshipTitleList')]
    private $fellowshipTitle;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $appendFellowshipTitleToName;

    #[ORM\ManyToOne(targetEntity: 'Institution')]
    private $institution;

    /**
     * Graduate, Undergraduate, Medical, Residency, Post-Residency Fellowship, GME, Other
     */
    #[ORM\ManyToOne(targetEntity: 'TrainingTypeList')]
    private $trainingType;

    #[ORM\ManyToOne(targetEntity: 'JobTitleList', cascade: ['persist', 'remove'])]
    private $jobTitle;

    #[ORM\OneToOne(targetEntity: 'GeoLocation', cascade: ['persist'])]
    private $geoLocation;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;



    public function __construct($author=null) {

        $this->majors = new ArrayCollection();
        $this->minors = new ArrayCollection();
        $this->honors = new ArrayCollection();

        parent::__construct($author);
    }




    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $completionDate
     */
    public function setCompletionDate($completionDate)
    {
        $this->completionDate = $completionDate;
    }

    /**
     * @return mixed
     */
    public function getCompletionDate()
    {
        return $this->completionDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $completionReason
     */
    public function setCompletionReason($completionReason)
    {
        $this->completionReason = $completionReason;
    }

    /**
     * @return mixed
     */
    public function getCompletionReason()
    {
        return $this->completionReason;
    }

    /**
     * @param mixed $degree
     */
    public function setDegree($degree)
    {
        $this->degree = $degree;
    }

    /**
     * @return mixed
     */
    public function getDegree()
    {
        return $this->degree;
    }

    /**
     * @param mixed $appendDegreeToName
     */
    public function setAppendDegreeToName($appendDegreeToName)
    {
        $this->appendDegreeToName = $appendDegreeToName;
    }

    /**
     * @return mixed
     */
    public function getAppendDegreeToName()
    {
        return $this->appendDegreeToName;
    }

    /**
     * @param mixed $residencySpecialty
     */
    public function setResidencySpecialty($residencySpecialty)
    {
        $this->residencySpecialty = $residencySpecialty;
    }

    /**
     * @return mixed
     */
    public function getResidencySpecialty()
    {
        return $this->residencySpecialty;
    }

    /**
     * @return mixed
     */
    public function getResidencyTrack()
    {
        return $this->residencyTrack;
    }

    /**
     * @param mixed $residencyTrack
     */
    public function setResidencyTrack($residencyTrack)
    {
        $this->residencyTrack = $residencyTrack;
    }

    /**
     * @param mixed $fellowshipSubspecialty
     */
    public function setFellowshipSubspecialty($fellowshipSubspecialty)
    {
        if( $fellowshipSubspecialty instanceof FellowshipSubspecialty ) {
            $this->fellowshipSubspecialty = $fellowshipSubspecialty;
        }
    }

    /**
     * @return mixed
     */
    public function getFellowshipSubspecialty()
    {
        return $this->fellowshipSubspecialty;
    }


    /**
     * @param mixed $appendFellowshipTitleToName
     */
    public function setAppendFellowshipTitleToName($appendFellowshipTitleToName)
    {
        $this->appendFellowshipTitleToName = $appendFellowshipTitleToName;
    }

    /**
     * @return mixed
     */
    public function getAppendFellowshipTitleToName()
    {
        return $this->appendFellowshipTitleToName;
    }

    /**
     * @param mixed $fellowshipTitle
     */
    public function setFellowshipTitle($fellowshipTitle)
    {
        $this->fellowshipTitle = $fellowshipTitle;
    }

    /**
     * @return mixed
     */
    public function getFellowshipTitle()
    {
        return $this->fellowshipTitle;
    }

    public function getMajors()
    {
        return $this->majors;
    }
    public function addMajor($major)
    {
        if( $major && !$this->majors->contains($major) ) {
            $this->majors->add($major);
        }

        return $this;
    }
    public function removeMajor($major)
    {
        $this->majors->removeElement($major);
    }

    public function getMinors()
    {
        return $this->minors;
    }
    public function addMinor($minor)
    {
        if( $minor && !$this->minors->contains($minor) ) {
            $this->minors->add($minor);
        }

        return $this;
    }
    public function removeMinor($minor)
    {
        $this->minors->removeElement($minor);
    }


    public function addHonor($honor)
    {
        if( $honor && !$this->honors->contains($honor) ) {
            $this->honors->add($honor);
        }

        return $this;
    }
    public function removeHonor($honor)
    {
        $this->honors->removeElement($honor);
    }
    public function getHonors()
    {
        return $this->honors;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $trainingType
     */
    public function setTrainingType($trainingType)
    {
        $this->trainingType = $trainingType;
    }

    /**
     * @return mixed
     */
    public function getTrainingType()
    {
        return $this->trainingType;
    }

    /**
     * @param mixed $jobTitle
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;
    }

    /**
     * @return mixed
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @return mixed
     */
    public function getGeoLocation()
    {
        return $this->geoLocation;
    }

    /**
     * @param mixed $geoLocation
     */
    public function setGeoLocation($geoLocation)
    {
        $this->geoLocation = $geoLocation;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function isEmpty() {

        if( $this->getStartDate() ) {
            return false;
        }

        if( $this->getCompletionDate() ) {
            return false;
        }

        if( $this->getInstitution() ) {
            return false;
        }

        if( $this->getDegree() ) {
            return false;
        }

        if( $this->getGeoLocation() ) {
            $locationStr = $this->getGeoLocation()->getFullGeoLocation();
            if ($locationStr) {
                return false;
            }
        }

        if( $this->getResidencyTrack() ) {
            return false;
        }

        return true;
    }


    public function __toString() {
        return "Training";
    }

}