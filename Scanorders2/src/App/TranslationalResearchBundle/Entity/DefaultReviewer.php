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
 * Created by PhpStorm.
 * User: ch3
 * Date: 9/11/2017
 * Time: 11:49 AM
 */

namespace App\TranslationalResearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="transres_defaultReviewer")
 * @ORM\HasLifecycleCallbacks
 */
class DefaultReviewer
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $creator;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updateUser", referencedColumnName="id", nullable=true)
     */
    private $updateUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $reviewer;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $reviewerDelegate;

    /**
     * Project's state for this reviewer (irb_review, admin_review, committee_review, final_review-primaryReviewer) -
     *  corresponds to the user role:
     * irb_review - ROLE_TRANSRES_IRB_REVIEWER
     * admin_review - ROLE_TRANSRES_ADMIN
     * committee_review - ROLE_TRANSRES_COMMITTEE_REVIEWER
     * final_review - ROLE_TRANSRES_PRIMARY_REVIEWER
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $state;

    /**
     * Used for Committee review. One review should be a primary and this review will change the project state.
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $primaryReview;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\TranslationalResearchBundle\Entity\SpecialtyList", cascade={"persist"})
     */
    private $projectSpecialty;


    public function __construct($creator=null) {
        $this->setCreator($creator);
        $this->setCreateDate(new \DateTime());
    }




    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param \DateTime $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return mixed
     */
    public function getUpdateUser()
    {
        return $this->updateUser;
    }

    /**
     * @param mixed $updateUser
     */
    public function setUpdateUser($updateUser)
    {
        $this->updateUser = $updateUser;
    }

    /**
     * @return mixed
     */
    public function getReviewer()
    {
        return $this->reviewer;
    }

    /**
     * @param mixed $reviewer
     */
    public function setReviewer($reviewer)
    {
        $this->reviewer = $reviewer;
    }

    /**
     * @return mixed
     */
    public function getReviewerDelegate()
    {
        return $this->reviewerDelegate;
    }

    /**
     * @param mixed $reviewerDelegate
     */
    public function setReviewerDelegate($reviewerDelegate)
    {
        $this->reviewerDelegate = $reviewerDelegate;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getPrimaryReview()
    {
        return $this->primaryReview;
    }

    /**
     * @param mixed $primaryReview
     */
    public function setPrimaryReview($primaryReview)
    {
        $this->primaryReview = $primaryReview;
    }

    /**
     * @return mixed
     */
    public function getProjectSpecialty()
    {
        return $this->projectSpecialty;
    }

    /**
     * @param mixed $projectSpecialty
     */
    public function setProjectSpecialty($projectSpecialty)
    {
        $this->projectSpecialty = $projectSpecialty;
    }




    public function getRoleByState() {
        $roles = array();

        $projectSpecialty = $this->getProjectSpecialty();
        if( !$projectSpecialty ) {
            throw new \Exception("getRoleByState: default reviewer does not have ProjectSpecialty");
        }
        $projectSpecialtyStr = "_".$projectSpecialty->getUppercaseName();

        if( $this->getState() == "irb_review" ) {
            $roles['reviewer'] = "ROLE_TRANSRES_IRB_REVIEWER".$projectSpecialtyStr;
        }
        if( $this->getState() == "admin_review" ) {
            $roles['reviewer'] = "ROLE_TRANSRES_ADMIN".$projectSpecialtyStr;
        }

        if( $this->getState() == "committee_review" ) {
            if( $this->getPrimaryReview() === true ) {
                $roles['reviewer'] = "ROLE_TRANSRES_PRIMARY_COMMITTEE_REVIEWER".$projectSpecialtyStr;
            } else {
                $roles['reviewer'] = "ROLE_TRANSRES_COMMITTEE_REVIEWER".$projectSpecialtyStr;
            }
        }

        if( $this->getState() == "final_review" ) {
            $roles['reviewer'] = "ROLE_TRANSRES_PRIMARY_REVIEWER".$projectSpecialtyStr;
        }

        return $roles;
    }


}