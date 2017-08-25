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

namespace Oleg\TranslationalResearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oleg\UserdirectoryBundle\Entity\GeneralEntity;
use Oleg\UserdirectoryBundle\Entity\UserWrapper;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="transres_project")
 * @ORM\HasLifecycleCallbacks
 */
class Project {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $submitter;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="updateAuthor", referencedColumnName="id", nullable=true)
     */
    private $updateUser;

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
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    // Project fields
    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_principalinvestigator",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="principalinvestigator_id", referencedColumnName="id")}
     * )
     **/
    private $principalInvestigators;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_coinvestigator",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="coinvestigator_id", referencedColumnName="id")}
     * )
     **/
    private $coInvestigators;

    /**
     * @ORM\ManyToMany(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinTable(name="transres_project_pathologist",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="pathologist_id", referencedColumnName="id")}
     * )
     **/
    private $pathologists;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $title;

    //Name of PI Who Submitted the IRB
    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $irbSubmitter;

    //Institutional Review Board (IRB)
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $irbNumber;

//    /**
//     * @ORM\Column(type="date", nullable=true)
//     */
//    private $startDate;
//
//    /**
//     * @ORM\Column(type="date", nullable=true)
//     */
//    private $expirationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    private $contact;

    //Is this Research Project Funded by a WCMC Account?
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $funded;

    //If funded please provide account number:
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fundedAccountNumber;

    //Please provide a brief description of the project to include background information,
    // purpose and objective, and a methodology section stating a justification for
    // the size and scope of the project (<250 words). The breadth of information
    // should be adequate for a scientific committee to understand and assess the value of the research.
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    //Provide a Detailed Budget Outline/Summary
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $budgetSummary;

    //Estimated Total Costs
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $totalCost;

    //Project Type
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $projectType;

    //Biostatistical Comment
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $biostatisticalComment;

    //Administrator Comment
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $administratorComment;

    //Primary Reviewer Comment
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $primaryReviewerComment;

    //Please check the box if this project is ready for committee to review
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $readyForReview;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $approvalDate;

    //TODO:
    //Click on the "View Biostatistical Request" button to view the biostatistical request form
    //Click on the "View Master Request" button to view the master request form



    public function __construct($user=null) {
        $this->setSubmitter($user);
        $this->setStatus('draft');
        $this->setCreateDate(new \DateTime());

        $this->principalInvestigators = new ArrayCollection();
        $this->coInvestigators = new ArrayCollection();
        $this->pathologists = new ArrayCollection();
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
     * @return mixed
     */
    public function getSubmitter()
    {
        return $this->submitter;
    }

    /**
     * @param mixed $submitter
     */
    public function setSubmitter($submitter)
    {
        $this->submitter = $submitter;
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
     * @return DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param DateTime $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @return DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdateDate()
    {
        $this->updateDate = new \DateTime();
    }
//    public function setUpdateDate( $date=null )
//    {
//        if( $date ) {
//            $this->updateDate = $date;
//        } else {
//            $this->updateDate = new \DateTime();
//        }
//    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getIrbSubmitter()
    {
        return $this->irbSubmitter;
    }

    /**
     * @param mixed $irbSubmitter
     */
    public function setIrbSubmitter($irbSubmitter)
    {
        $this->irbSubmitter = $irbSubmitter;
    }

    /**
     * @return mixed
     */
    public function getIrbNumber()
    {
        return $this->irbNumber;
    }

    /**
     * @param mixed $irbNumber
     */
    public function setIrbNumber($irbNumber)
    {
        $this->irbNumber = $irbNumber;
    }

//    /**
//     * @return mixed
//     */
//    public function getStartDate()
//    {
//        return $this->startDate;
//    }
//
//    /**
//     * @param mixed $startDate
//     */
//    public function setStartDate($startDate)
//    {
//        $this->startDate = $startDate;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getExpirationDate()
//    {
//        return $this->expirationDate;
//    }
//
//    /**
//     * @param mixed $expirationDate
//     */
//    public function setExpirationDate($expirationDate)
//    {
//        $this->expirationDate = $expirationDate;
//    }

    /**
     * @return mixed
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param mixed $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return mixed
     */
    public function getFunded()
    {
        return $this->funded;
    }

    /**
     * @param mixed $funded
     */
    public function setFunded($funded)
    {
        $this->funded = $funded;
    }

    /**
     * @return mixed
     */
    public function getFundedAccountNumber()
    {
        return $this->fundedAccountNumber;
    }

    /**
     * @param mixed $fundedAccountNumber
     */
    public function setFundedAccountNumber($fundedAccountNumber)
    {
        $this->fundedAccountNumber = $fundedAccountNumber;
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

    /**
     * @return mixed
     */
    public function getBudgetSummary()
    {
        return $this->budgetSummary;
    }

    /**
     * @param mixed $budgetSummary
     */
    public function setBudgetSummary($budgetSummary)
    {
        $this->budgetSummary = $budgetSummary;
    }

    /**
     * @return mixed
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @param mixed $totalCost
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
    }

    /**
     * @return mixed
     */
    public function getProjectType()
    {
        return $this->projectType;
    }

    /**
     * @param mixed $projectType
     */
    public function setProjectType($projectType)
    {
        $this->projectType = $projectType;
    }

    /**
     * @return mixed
     */
    public function getBiostatisticalComment()
    {
        return $this->biostatisticalComment;
    }

    /**
     * @param mixed $biostatisticalComment
     */
    public function setBiostatisticalComment($biostatisticalComment)
    {
        $this->biostatisticalComment = $biostatisticalComment;
    }

    /**
     * @return mixed
     */
    public function getAdministratorComment()
    {
        return $this->administratorComment;
    }

    /**
     * @param mixed $administratorComment
     */
    public function setAdministratorComment($administratorComment)
    {
        $this->administratorComment = $administratorComment;
    }

    /**
     * @return mixed
     */
    public function getPrimaryReviewerComment()
    {
        return $this->primaryReviewerComment;
    }

    /**
     * @param mixed $primaryReviewerComment
     */
    public function setPrimaryReviewerComment($primaryReviewerComment)
    {
        $this->primaryReviewerComment = $primaryReviewerComment;
    }



    public function getPrincipalInvestigators()
    {
        return $this->principalInvestigators;
    }
    public function addPrincipalInvestigator($item)
    {
        if( $item && !$this->principalInvestigators->contains($item) ) {
            $this->principalInvestigators->add($item);
        }
        return $this;
    }
    public function removePrincipalInvestigator($item)
    {
        $this->principalInvestigators->removeElement($item);
    }

    public function getCoInvestigators()
    {
        return $this->coInvestigators;
    }
    public function addCoInvestigator($item)
    {
        if( $item && !$this->coInvestigators->contains($item) ) {
            $this->coInvestigators->add($item);
        }
        return $this;
    }
    public function removeCoInvestigator($item)
    {
        $this->coInvestigators->removeElement($item);
    }

    public function getPathologists()
    {
        return $this->pathologists;
    }
    public function addPathologist($item)
    {
        if( $item && !$this->pathologists->contains($item) ) {
            $this->pathologists->add($item);
        }
        return $this;
    }
    public function removePathologist($item)
    {
        $this->pathologists->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getReadyForReview()
    {
        return $this->readyForReview;
    }

    /**
     * @param mixed $readyForReview
     */
    public function setReadyForReview($readyForReview)
    {
        $this->readyForReview = $readyForReview;
    }

    /**
     * @return mixed
     */
    public function getApprovalDate()
    {
        return $this->approvalDate;
    }

    /**
     * @param mixed $approvalDate
     */
    public function setApprovalDate($approvalDate)
    {
        $this->approvalDate = $approvalDate;
    }




}