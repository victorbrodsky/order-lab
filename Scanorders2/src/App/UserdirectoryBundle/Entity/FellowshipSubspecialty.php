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

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_fellowshipSubspecialty")
 */
class FellowshipSubspecialty extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="FellowshipSubspecialty", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="FellowshipSubspecialty", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;




    //ResidencySpecialty - parent
    /**
     * @ORM\ManyToOne(targetEntity="ResidencySpecialty", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     **/
    protected $parent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $boardCertificateAvailable;



    //Fellowship application fields

    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     * @ORM\JoinColumn(name="institution_id", referencedColumnName="id", nullable=true)
     **/
    protected $institution;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_fellowshipSubspecialty_coordinator",
     *      joinColumns={@ORM\JoinColumn(name="fellowshipSubspecialty_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="coordinator_id", referencedColumnName="id")}
     * )
     **/
    private $coordinators;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_fellowshipSubspecialty_director",
     *      joinColumns={@ORM\JoinColumn(name="fellowshipSubspecialty_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="director_id", referencedColumnName="id")}
     * )
     **/
    private $directors;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="user_fellowshipSubspecialty_interviewer",
     *      joinColumns={@ORM\JoinColumn(name="fellowshipSubspecialty_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="interviewer_id", referencedColumnName="id")}
     * )
     **/
    private $interviewers;



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

    //$methodStr: getInterviewers
    public function isUserExistByMethodStr( $user, $methodStr ) {
        foreach( $this->$methodStr() as $thisUser ) {
            if( $thisUser->getId() == $user->getId() ) {
                return true;
            }
        }
        return false;
    }



    public function getClassName()
    {
        return "FellowshipSubspecialty";
    }


}