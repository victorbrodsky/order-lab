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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Table(name: 'user_fellowshipSubspecialty')]
#[ORM\Entity]
class FellowshipSubspecialty extends ListAbstract
{

    #[ORM\OneToMany(targetEntity: 'FellowshipSubspecialty', mappedBy: 'original', cascade: ['persist'])]
    protected $synonyms;

    #[ORM\ManyToOne(targetEntity: 'FellowshipSubspecialty', inversedBy: 'synonyms', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'original_id', referencedColumnName: 'id', nullable: true)]
    protected $original;




    //ResidencySpecialty - parent (parent is just a name for this regular field, it's not tree structure)
    #[ORM\ManyToOne(targetEntity: 'ResidencySpecialty', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
    protected $parent;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $boardCertificateAvailable;



    //Fellowship application fields
    #[ORM\ManyToOne(targetEntity: 'Institution')]
    #[ORM\JoinColumn(name: 'institution_id', referencedColumnName: 'id', nullable: true)]
    protected $institution;

    #[ORM\JoinTable(name: 'user_fellowshipSubspecialty_coordinator')]
    #[ORM\JoinColumn(name: 'fellowshipSubspecialty_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'coordinator_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'User')]
    private $coordinators;

    #[ORM\JoinTable(name: 'user_fellowshipSubspecialty_director')]
    #[ORM\JoinColumn(name: 'fellowshipSubspecialty_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'director_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'User')]
    private $directors;

    #[ORM\JoinTable(name: 'user_fellowshipSubspecialty_interviewer')]
    #[ORM\JoinColumn(name: 'fellowshipSubspecialty_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'interviewer_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'User')]
    private $interviewers;

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

    //Show an additional section with screening questions on the Fellowship Application page
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $screeningQuestions;
    

    public function __construct($author=null) {

        $this->coordinators = new ArrayCollection();
        $this->directors = new ArrayCollection();
        $this->interviewers = new ArrayCollection();

    }


    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $boardCertificateAvailable
     */
    public function setBoardCertificateAvailable($boardCertificateAvailable)
    {
        $this->boardCertificateAvailable = $boardCertificateAvailable;
    }

    /**
     * @return mixed
     */
    public function getBoardCertificateAvailable()
    {
        return $this->boardCertificateAvailable;
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

    /**
     * @return mixed
     */
    public function getScreeningQuestions()
    {
        return $this->screeningQuestions;
    }

    /**
     * @param mixed $screeningQuestions
     */
    public function setScreeningQuestions($screeningQuestions)
    {
        $this->screeningQuestions = $screeningQuestions;
    }

    



    //$methodStr: getInterviewers
    public function isUserExistByMethodStr( $user, $methodStr ) {
        foreach( $this->$methodStr() as $thisUser ) {
            if( $thisUser->getId() == $user->getId() ) {
                return true;
            }
        }
        return false;
    }

    //Clinical Informatics (WCM => Pathology)" becomes
    //"WCM Department of Pathology and Laboratory Medicine - Clinical Informatics
    public function getNameInstitution() {
        $name = $this->getName();
        $institution = null;
        if( $this->getInstitution() ) {
            $institution = $this->getInstitution()->getTreeRootAbbreviationChildName(' ');
            return $institution . " - " . $name;
        }
        return $name;
    }

    public function getClassName()
    {
        return "FellowshipSubspecialty";
    }


}