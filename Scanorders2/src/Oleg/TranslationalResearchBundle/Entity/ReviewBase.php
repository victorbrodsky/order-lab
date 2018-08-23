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
 * Date: 9/6/2017
 * Time: 4:33 PM
 */

namespace Oleg\TranslationalResearchBundle\Entity;


use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
class ReviewBase
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="reviewer", referencedColumnName="id")
     */
    protected $reviewer;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="reviewerDelegate", referencedColumnName="id", nullable=true)
     */
    protected $reviewerDelegate;

//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
//     * @ORM\JoinColumn(name="reviewerAdmin", referencedColumnName="id", nullable=true)
//     */
//    protected $reviewerAdmin;

//    /**
//     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
//     * @ORM\JoinColumn(name="reviewerPrimary", referencedColumnName="id", nullable=true)
//     */
//    protected $reviewerPrimary;

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\User")
     * @ORM\JoinColumn(name="reviewedBy", referencedColumnName="id", nullable=true)
     */
    protected $reviewedBy;

    /**
     * valid, invalid
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedate;

    /**
     * approved, rejected, pending additional information from submitter, pending review
     * @ORM\Column(type="string", nullable=true)
     */
    protected $decision;

//    /**
//     * @ORM\Column(type="text", nullable=true)
//     */
//    protected $comment;


    public function __construct($reviewer=null) {
        $this->setReviewer($reviewer);
        $this->setCreatedate(new \DateTime());

        $this->setStatus($this->getStateStr());

        //$this->comments = new ArrayCollection();
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
    public function getReviewedBy()
    {
        return $this->reviewedBy;
    }

    /**
     * @param mixed $reviewedBy
     */
    public function setReviewedBy($reviewedBy)
    {
        $this->reviewedBy = $reviewedBy;
    }



    /**
     * @return \DateTime
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * @param \DateTime $createdate
     */
    public function setCreatedate($createdate)
    {
        $this->createdate = $createdate;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedate()
    {
        return $this->updatedate;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedate()
    {
        $this->updatedate = new \DateTime();
    }

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

//    /**
//     * @return mixed
//     */
//    public function getComment()
//    {
//        return $this->comment;
//    }
//
//    /**
//     * @param mixed $comment
//     */
//    public function setComment($comment)
//    {
//        $this->comment = $comment;
//    }

    /**
     * @return mixed
     */
    public function getDecision()
    {
        return $this->decision;
    }

    /**
     * @param mixed $decision
     */
    public function setDecision($decision)
    {
        $this->decision = $decision;
    }

    public function getStateStr() {
        return "undefined_review";
    }

    public function getSubmittedReviewerInfo() {
        if( $this->getDecision() ) {
            $decision = $this->getDecision();
            if( $decision == "missinginfo" ) {
                $decision = "Missing Information";
            }
        } else {
            $decision = "Pending";
        }
        $info =
            "Decision: ".ucwords($decision).
            ", submitted by ".$this->getReviewedBy().
            " on ".$this->getUpdatedate()->format('m/d/Y H:i:s');
            //", comment: ".$this->getComment();
        return $info;
    }

    public function getDecisionStr() {
        $decision = $this->getDecision();
        if( $decision ) {
            if( $decision == "approved" ) {
                $decision = "approved";
            }
            if( $decision == "rejected" ) {
                $decision = "rejected";
            }
            if( $decision == "missinginfo" ) {
                $decision = "held until additional information is received";
            }
        } else {
            $decision = "Pending";
        }
        return $decision;
    }

    public function setDecisionByTransitionName( $transitionName ) {

        //irb_review_approved => IRB Review Approved
        //irb_review_rejected => IRB Review Rejected
        //irb_review_missinginfo => IRB Review Missinginfo
        //irb_review_resubmit => IRB Review Resubmit
        $decision = null;

        if( strpos($transitionName, "_approved") !== false ) {
            $decision = "approved";
        }
        if( strpos($transitionName, "_missinginfo") !== false ) {
            $decision = "missinginfo";
        }
        if( strpos($transitionName, "_rejected") !== false ) {
            $decision = "rejected";
        }
        if( strpos($transitionName, "_resubmit") !== false ) {
            $decision = null;
        }

        $this->setDecision($decision);

        return $decision;
    }

//    public function getStateFromEntity() {
////        $state = NULL;
////        $className = get_class($this);
////        if( $className == "IrbReview" ) {
////            $state = "irb_review";
////        }
////        if( $className == "IrbReview" ) {
////            $state = "irb_review";
////        }
////        if( $className == "IrbReview" ) {
////            $state = "irb_review";
////        }
////        if( $className == "IrbReview" ) {
////            $state = "irb_review";
////        }
//        //return $state;
//    }

    public function getEntityName() {
        return "Project";
    }

    public function __toString() {
        return "Review id=[".$this->getId()."], state=".$this->getStateStr();
    }
}