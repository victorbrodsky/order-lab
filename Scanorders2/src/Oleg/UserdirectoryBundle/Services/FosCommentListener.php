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
 * User: oli2002
 * Date: 10/16/14
 * Time: 9:55 AM
 */

namespace Oleg\UserdirectoryBundle\Services;

use FOS\CommentBundle\Events;
use FOS\CommentBundle\Event\CommentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FosCommentListener implements EventSubscriberInterface {


    private $container;
    private $em;
    protected $secTokenStorage;

    protected $secAuth;

    public function __construct( $container, $secTokenStorage, $em )
    {
        $this->container = $container;
        $this->em = $em;

        $this->secTokenStorage = $secTokenStorage;  //$container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::COMMENT_PRE_PERSIST => 'onCommentPrePersistTest',
        );
    }

    public function onCommentPrePersist(CommentEvent $event)
    {
        $transresUtil = $this->container->get('transres_util');

        $comment = $event->getComment();

        $project = $this->getProjectFromComment($comment);

        $authorTypeArr = $this->getAuthorType($project);

        if( $authorTypeArr && count($authorTypeArr) > 0 ) {
            $comment->setAuthorType($authorTypeArr['type']);
            $comment->setAuthorTypeDescription($authorTypeArr['description']);
        }


        //TODO: send emails
        //1) if reviewer: send emails to the project requesters
        //if( $authorTypeArr['type'] == "Administrator" || $authorTypeArr['type'] == "Reviewer" ) {
        //}
        //2) if requester: send emails to the project reviewers and admin
        //if( $authorTypeArr['type'] == "Requester" ) {
        //}
        //send email to all project related users: admin, primary, requesters, reviewers of this review type.



        //send email to all project related users: admin, primary, requesters, reviewers of this review type.
        $emails = array();

        $stateStr = $this->getStateStrFromComment($comment);
        $reviews = $transresUtil->getReviewsByProjectAndState($project,$stateStr);

        //1) admins
        $adminEmails = $transresUtil->getTransResAdminEmails();
        $emails = array_merge($emails,$adminEmails);

        //2) reviewers of this review
        foreach($reviews as $review) {
            $reviewerEmails = $transresUtil->getCurrentReviewersEmails($review);
            $emails = array_merge($emails,$reviewerEmails);
        }

        //3) requesters
        $requesterEmails = $transresUtil->getRequesterEmails($project);
        $emails = array_merge($emails,$requesterEmails);

        $emails = array_unique($emails);

        $break = "\r\n";
        $senderEmail = null; //Admin email

        $stateLabel = $transresUtil->getStateLabelByName($stateStr);
        $subject = "New Comment for Project ID ".$project->getOid()." has been posted for the stage '".$stateLabel."'";
        $body = $subject . ":" . $break . $comment->getBody();

        //get project url
        $projectUrl = $transresUtil->getProjectShowUrl($project);
        $emailBody = $body . $break.$break. "Please click on the URL below to view this project:".$break.$projectUrl;

        $emailUtil = $this->container->get('user_mailer_utility');
        $emailUtil->sendEmail( $emails, $subject, $emailBody, null, $senderEmail );

        //eventlog
        $eventType = "Comment Posted";
        $transresUtil->setEventLog($project,$eventType,$body);
    }

    public function getAuthorType( $project ) {

        if( !$this->secTokenStorage->getToken() ) {
            //not authenticated
            return null;
        }

        $authorTypeArr = array();

        if( $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ) {
            //$authorType = "Administrator";
            $authorTypeArr['type'] = "Administrator";
            $authorTypeArr['description'] = "Administrator";
            return $authorTypeArr;
        }
        if( $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ) {
            //$authorType = "Primary Reviewer";
            $authorTypeArr['type'] = "Administrator";
            $authorTypeArr['description'] = "Primary Reviewer";
            return $authorTypeArr;
        }
        if( $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE') ) {
            //$comment->setAuthorType("Primary Reviewer Delegate");
            //$authorType = "Primary Reviewer";
            $authorTypeArr['type'] = "Administrator";
            $authorTypeArr['description'] = "Primary Reviewer";
            return $authorTypeArr;
        }

        //if not found
        $transresUtil = $this->container->get('transres_util');
        $user = $this->secTokenStorage->getToken()->getUser();

        //1) check if the user is project requester
        //$project = $this->getProjectFromComment($comment);
        if( !$project ) {
            return null;
        }

        //check if reviewer
        if( $transresUtil->isProjectReviewer($project) ) {
            //return "Reviewer";
            $authorTypeArr['type'] = "Reviewer";
            $authorTypeArr['description'] = "Reviewer";
            return $authorTypeArr;
        }
//                if( $transresUtil->isReviewsReviewer($user,$project->getIrbReviews()) ) {
//                    return "IRB Reviewer";
//                }
//                if( $transresUtil->isReviewsReviewer($user,$project->getAdminReviews()) ) {
//                    return "Admin Reviewer";
//                }
//                if( $transresUtil->isReviewsReviewer($user,$project->getCommitteeReviews()) ) {
//                    return "Committee Reviewer";
//                }
//                if( $transresUtil->isReviewsReviewer($user,$project->getFinalReviews()) ) {
//                    return "Primary Reviewer";
//                }

        //check if requester
        if( $project->getSubmitter() && $project->getSubmitter()->getId() == $user->getId() ) {
            //return "Submitter";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Submitter";
            return $authorTypeArr;
        }
        if( $project->getPrincipalInvestigators()->contains($user) ) {
            //return "Principal Investigator";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Principal Investigator";
            return $authorTypeArr;
        }
        if( $project->getCoInvestigators()->contains($user) ) {
            //return "Co-Investigator";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Co-Investigator";
            return $authorTypeArr;
        }
        if( $project->getPathologists()->contains($user) ) {
            //return "Pathologist";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Pathologist";
            return $authorTypeArr;
        }
        if( $project->getContacts()->contains($user) ) {
            //return "Contact";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Contact";
            return $authorTypeArr;
        }
        if( $project->getBillingContacts()->contains($user) ) {
            //return "Billing Contact";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Billing Contact";
            return $authorTypeArr;
        }

        return null;
    }

    public function getProjectFromComment($comment) {
        $project = null;
        //get project
        $threadId = $comment->getThread()->getId();
        $idArr = explode("-",$threadId);

        $projectId = null;
        //$stateStr = null;
        if( count($idArr) > 1 ) {
            $projectId = $idArr[0]; //7
            //$stateStr = $idArr[1];  //irb_review
        }

        if( $projectId ) {
            $project = $this->em->getRepository('OlegTranslationalResearchBundle:Project')->find($projectId);
        }

        return $project;
    }

    public function getStateStrFromComment($comment) {
        $project = null;
        //get project
        $threadId = $comment->getThread()->getId();
        $idArr = explode("-",$threadId);

        $stateStr = null;
        if( count($idArr) > 1 ) {
            $stateStr = $idArr[1];  //irb_review
        }

        return $stateStr;
    }

} 