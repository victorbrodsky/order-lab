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

    protected $disable = false;
    //protected $disable = true; //disable comments when importing data

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
            //Events::COMMENT_PRE_PERSIST => 'onCommentPrePersistTest',
            //Events::COMMENT_POST_PERSIST => 'onCommentPostPersistTest',
        );
    }

    public function onCommentPrePersist(CommentEvent $event)
    {
        if( $this->disable ) {
            return;
        }

        $comment = $event->getComment();
        $entity = $this->getEntityFromComment($comment);

        if( $entity ) {
            //send comment entity properties
            $comment->setObject($entity);

            $authorTypeArr = $this->getAuthorType($entity);
            if( $authorTypeArr && count($authorTypeArr) > 0 ) {
                $comment->setAuthorType($authorTypeArr['type']);
                $comment->setAuthorTypeDescription($authorTypeArr['description']);
            }
        }

        //$this->sendEmails($event,$comment,$entity);

        //set only eventlog
        //$this->setCommentEventLog($event,$comment,$entity);
    }

    public function onCommentPostPersist(CommentEvent $event)
    {
        if( $this->disable ) {
            return;
        }
        
        $comment = $event->getComment();
        $entity = $this->getEntityFromComment($comment);

        //set only eventlog
        $resArr = $this->setCommentEventLog($event,$comment,$entity);

        //send only emails (Comment takes lots of time - couple seconds delay)
        $this->sendCommentEmails($comment,$entity,$resArr);
    }



    public function setCommentEventLog(CommentEvent $event, $comment=null, $entity=null) {
        $transresUtil = $this->container->get('transres_util');

        if( !$comment ) {
            $comment = $event->getComment();
        }

        if( !$entity ) {
            $entity = $this->getEntityFromComment($comment);
        }

        $eventType = "Comment Posted";
        if( $entity->getEntityName() == "Project" ) {
            $eventType = "Project Comment Posted";
            $stateStr = $this->getStateStrFromComment($comment);
            $stateLabel = $transresUtil->getStateLabelByName($stateStr);
        }

        if( $entity->getEntityName() == "Request" ) {
            $eventType = "Request Comment Posted";
            $stateLabel = null;
        }

        $resArr = $this->getMsgSubjectAndBody($comment,$entity,$stateLabel);
        $body = $resArr['body'];
        //$body = str_replace("\r\n","<br>",$body);
        $transresUtil->setEventLog($entity,$eventType,$body);

        return $resArr;
    }

    public function sendCommentEmails($comment, $entity, $resArr) {
        if( $entity->getEntityName() == "Project" ) {
            $this->sendCommentProjectEmails($comment, $entity, $resArr);
        }

        if( $entity->getEntityName() == "Request" ) {
            $this->sendCommentRequestEmails($comment, $entity, $resArr);
        }
    }

    public function sendCommentProjectEmails($comment=null, $project=null, $resArr=null)
    {
        $transresUtil = $this->container->get('transres_util');

        if( !$project ) {
            $project = $this->getEntityFromComment($comment);
        }

        //send email to all entity related users: admin, primary, requesters, reviewers of this review type.
        $emails = array();

        $stateStr = $this->getStateStrFromComment($comment);
        $reviews = $transresUtil->getReviewsByProjectAndState($project,$stateStr);

        //1) admins
        $adminEmails = $transresUtil->getTransResAdminEmails($project->getProjectSpecialty(),true,true);

        //2) reviewers of this review
        foreach($reviews as $review) {
            $reviewerEmails = $transresUtil->getCurrentReviewersEmails($review);
            $emails = array_merge($emails,$reviewerEmails);
        }

        //3) submitter and contact
        $requesterEmails = $transresUtil->getRequesterMiniEmails($project);
        $emails = array_merge($emails,$requesterEmails);

        $emails = array_unique($emails);

        //$senderEmail = null; //Admin email
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);

        //$break = "\r\n";
        $break = "<br>";

        if( !$resArr ) {
            $stateLabel = $transresUtil->getStateLabelByName($stateStr);
            $resArr = $this->getMsgSubjectAndBody($comment,$project,$stateLabel);
        }

        $subject = $resArr['subject'];
        $body = $resArr['body'];

        //get entity url
        $projectUrl = $transresUtil->getProjectShowUrl($project);
        //$body = $body . $break . $break . "Please click on the URL below to view this ".$project->getEntityName().":" . $break . $projectUrl;
        //To view this project request, please visit the link below
        $body = $body . $break . $break . "To reply to the comment or to view this project request, please visit the link below:" . $break . $projectUrl;

        $emailUtil = $this->container->get('user_mailer_utility');
        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $subject, $body, $adminEmails, $senderEmail );
    }

    public function sendCommentRequestEmails($comment, $transresRequest, $resArr) {
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        if( !$transresRequest ) {
            $transresRequest = $this->getEntityFromComment($comment);
        }

        $project = $transresRequest->getProject();

        //send email to all entity related users: admin, primary, requesters, reviewers of this review type.
        $emails = array();

        //1) admins
        $adminEmails = $transresUtil->getTransResAdminEmails($project->getProjectSpecialty(),true,true);
        //$emails = array_merge($emails,$adminEmails);

//        //2) contact (Billing Contact). Removed by Bing's request: "The email should be sent to the work request submitted and the PI."
//        $contact = $transresRequest->getContact();
//        if( $contact ) {
//            $contactEmail = $contact->getSingleEmail();
//            if( $contactEmail ) {
//                $emails = array_merge($emails,array($contactEmail));
//            }
//        }

        //3) submitster
        $submiiter = $transresRequest->getSubmitter();
        if( $submiiter ) {
            $submiiterEmail = $submiiter->getSingleEmail();
            if( $submiiterEmail ) {
                $emails = array_merge($emails,array($submiiterEmail));
            }
        }

        //4) principalInvestigators
        $piEmailArr = array();
        $pis = $transresRequest->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi ) {
                $piEmailArr[] = $pi->getSingleEmail();
            }
        }
        $emails = array_merge($emails,$piEmailArr);

        $emails = array_unique($emails);

        //print_r($emails);
        //exit("request comment email");

        //$senderEmail = null; //Admin email
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);

        //$break = "\r\n";
        $break = "<br>";

        if( !$resArr ) {
            //$stateStr = $this->getStateStrFromComment($comment);
            //$stateStr = $comment->getThread()->getId();
            //$stateLabel = $transresUtil->getStateLabelByName($stateStr);
            //$transresRequestUtil = $this->container->get('transres_request_util');
            //$stateLabel = $transresRequestUtil->getRequestLabelByStateMachineType();
            //$stateLabel = $transresRequestUtil->getProgressStateLabelByName($stateStr);
            $resArr = $this->getMsgSubjectAndBody($comment,$transresRequest);
        }

        $subject = $resArr['subject'];
        $body = $resArr['body'];

        //get entity url
        $transresRequestUrl = $transresRequestUtil->getRequestShowUrl($transresRequest);
        //$body = $body . $break . $break . "Please click on the URL below to view this ".$transresRequest->getEntityName().":" . $break . $transresRequestUrl;
        $body = $body . $break . $break . "To reply to the comment or to view this work request, please visit the link below:" . $break . $transresRequestUrl;

        $emailUtil = $this->container->get('user_mailer_utility');
        $emailUtil->sendEmail( $emails, $subject, $body, $adminEmails, $senderEmail );
    }

    public function getMsgSubjectAndBody($comment,$entity,$stateLabel=null) {
        //$break = "\r\n";
        $break = "<br>";
        if( $stateLabel ) {
            $stateLabel = " in '".$stateLabel."' stage";
        }
        $subject = "New comment for ".$entity->getDisplayName()." ".$entity->getOid()." has been added".$stateLabel;
        $body = $subject . ":" . $break . "<hr>" . "<b>" . $comment->getBody() . "</b>" . "<hr>";

        return array('subject'=>$subject, 'body'=>$body);
    }

    public function getAuthorType( $entity ) {

        if( !$this->secTokenStorage->getToken() ) {
            //not authenticated
            return null;
        }

        $authorTypeArr = array();

        if( $entity->getEntityName() == "Project" ) {
            $specialtyStr = null;
            $projectSpecialty = $entity->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyStr = $projectSpecialty->getUppercaseName();
                $specialtyStr = "_" . $specialtyStr;
            }
        }

        if( $entity->getEntityName() == "Request" ) {
            $specialtyStr = null;
            $project = $entity->getProject();
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyStr = $projectSpecialty->getUppercaseName();
                $specialtyStr = "_" . $specialtyStr;
            }
        }

        if( $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr) ) {
            //$authorType = "Administrator";
            $authorTypeArr['type'] = "Administrator";
            $authorTypeArr['description'] = "Administrator";
            return $authorTypeArr;
        }
        if( $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr) ) {
            //$authorType = "Primary Reviewer";
            $authorTypeArr['type'] = "Administrator";
            $authorTypeArr['description'] = "Primary Reviewer";
            return $authorTypeArr;
        }

        //if not found
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $user = $this->secTokenStorage->getToken()->getUser();

        //1) check if the user is entity requester
        //$entity = $this->getEntityFromComment($comment);
        if( !$entity ) {
            return null;
        }

        //check if reviewer
        if( $entity->getEntityName() == "Project" ) {
            if ($transresUtil->isProjectReviewer($entity)) {
                //return "Reviewer";
                $authorTypeArr['type'] = "Reviewer";
                $authorTypeArr['description'] = "Reviewer";
                return $authorTypeArr;
            }
        }

        if( $entity->getEntityName() == "Request" ) {
            if( $transresRequestUtil->isRequestStateReviewer($entity,"progress") ) {
                //return "Reviewer";
                $authorTypeArr['type'] = "Reviewer";
                $authorTypeArr['description'] = "Reviewer";
                return $authorTypeArr;
            }
        }

        if( $entity->getEntityName() == "Project" ) {
            return $this->getProjectRequesterAuthorType($entity,$user);
        }

        if( $entity->getEntityName() == "Request" ) {
            return $this->getRequestRequesterAuthorType($entity,$user);
        }

        return null;
    }
    public function getProjectRequesterAuthorType( $entity, $user ) {

        //check if requester
        if( $entity->getSubmitter() && $entity->getSubmitter()->getId() == $user->getId() ) {
            //return "Submitter";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Submitter";
            return $authorTypeArr;
        }
        if( $entity->getPrincipalInvestigators()->contains($user) ) {
            //return "Principal Investigator";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Principal Investigator";
            return $authorTypeArr;
        }
        if( $entity->getCoInvestigators()->contains($user) ) {
            //return "Co-Investigator";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Co-Investigator";
            return $authorTypeArr;
        }
        if( $entity->getPathologists()->contains($user) ) {
            //return "Pathologist";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Pathologist";
            return $authorTypeArr;
        }
        if( $entity->getContacts()->contains($user) ) {
            //return "Contact";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Contact";
            return $authorTypeArr;
        }
        if( $entity->getBillingContacts()->contains($user) ) {
            //return "Billing Contact";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Billing Contact";
            return $authorTypeArr;
        }

        return null;
    }
    public function getRequestRequesterAuthorType( $entity, $user ) {
        //check if requester
        if( $entity->getSubmitter() && $entity->getSubmitter()->getId() == $user->getId() ) {
            //return "Submitter";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Submitter";
            return $authorTypeArr;
        }
        if( $entity->getPrincipalInvestigators()->contains($user) ) {
            //return "Principal Investigator";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Principal Investigator";
            return $authorTypeArr;
        }
        if( $entity->getContacts()->contains($user) ) {
            //return "Contact";
            $authorTypeArr['type'] = "Requester";
            $authorTypeArr['description'] = "Contact";
            return $authorTypeArr;
        }

        return null;
    }

    //http://localhost/order/api/threads/transres-request-20-billing/comments
    public function getEntityFromComment($comment) {
        $entity = null;
        //get entity from "transres-request-20-billing"
        $threadId = $comment->getThread()->getId();
        $idArr = explode("-",$threadId);

        $entityId = null;
        //$stateStr = null;
        if( count($idArr) >= 4 ) {
            $entitySitename = $idArr[0]; //sitename
            $entityName = $idArr[1]; //entity name
            $entityId = $idArr[2]; //entity id
            //$stateStr = $idArr[1];  //irb_review
        }

        if( $entitySitename == "transres" ) {
            $bundleName = 'OlegTranslationalResearchBundle';
        }

        if( $entityName == "Request" ) {
            $entityName = "TransResRequest";
        }
        //exit("Find entity by ID=".$entityId."; namespace=".$bundleName.':'.$entityName);

        if( $bundleName && $entityId ) {
            $entity = $this->em->getRepository($bundleName.':'.$entityName)->find($entityId);
        }

//        if( !$entity ) {
//            exit("No entity found by ID=".$entityId."; namespace=".$bundleName.':'.$entityName);
//        }

        return $entity;
    }

    public function getStateStrFromComment($comment) {
        //$logger = $this->container->get('logger');
        //$entity = null;
        //get state from "transres-request-20-billing" or "transres-Project-9-irb_review"
        $threadId = $comment->getThread()->getId();
        //echo "threadId=".$threadId."<br>";
        //$logger->notice("threadId=".$threadId);
        $idArr = explode("-",$threadId);

        $stateStr = null;
        if( count($idArr) >= 4 ) {
            $stateStr = $idArr[3];  //irb_review
        }

        if( !$stateStr ) {
            throw new \Exception('State not found by threadId='.$threadId);
        }

        return $stateStr;
    }


} 