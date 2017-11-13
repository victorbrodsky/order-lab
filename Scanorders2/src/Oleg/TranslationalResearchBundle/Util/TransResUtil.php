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

namespace Oleg\TranslationalResearchBundle\Util;


use Doctrine\Common\Collections\ArrayCollection;
use Oleg\TranslationalResearchBundle\Entity\AdminReview;
use Oleg\TranslationalResearchBundle\Entity\CommitteeReview;
use Oleg\TranslationalResearchBundle\Entity\FinalReview;
use Oleg\TranslationalResearchBundle\Entity\IrbReview;
use Oleg\TranslationalResearchBundle\Entity\SpecialtyList;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/25/2017
 * Time: 09:48 AM
 */
class TransResUtil
{

    protected $container;
    protected $em;
    //protected $secToken;
    protected $secTokenStorage;
    protected $secAuth;

    public function __construct( $em, $container ) {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        //$this->secToken = $container->get('security.token_storage')->getToken(); //$user = $this->secToken->getUser();
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }

    //NOT USED
    //get links to change states: Reject IRB Review and Approve IRB Review (translationalresearch_transition_action)
    public function getEnabledLinkActions( $project, $classEdit=null, $classTransition=null ) {
        $workflow = $this->container->get('state_machine.transres_project');
        $transitions = $workflow->getEnabledTransitions($project);

        $links = array();
        foreach( $transitions as $transition ) {

            //$this->printTransition($transition);
            $transitionName = $transition->getName();

            //$tos = $transition->getTos();
            $froms = $transition->getFroms();
            foreach( $froms as $from ) {
                //echo "from=".$from."<br>"; //irb_review

                //add user's validation: $from=irb_review => user has role _IRB_REVIEW_
                if( false === $this->isUserAllowedFromThisStateByRole($from) ) {
                    continue;
                }

                //don't sent state $to (get it from transition object)
                $thisUrl = $this->container->get('router')->generate(
                    'translationalresearch_transition_action',
                    array(
                        'transitionName'=>$transitionName,
                        'id'=>$project->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                //$label = ucfirst($transitionName)." (mark as ".ucfirst($to);
                $label = $this->getTransitionLabelByName($transitionName);

                $classTransition = $this->getHtmlClassTransition($transitionName);

                $thisLink = "<a ".
                    "general-data-confirm='Are you sure you want to $label?'".
                    "href=".$thisUrl." class='".$classTransition."'>".$label."</a>";
                $links[] = $thisLink;

            }//foreach

        }//foreach

        if(0) {
            ////////// add links to edit if the current state is "_rejected" //////////
            $froms = $transition->getFroms();
            $fromState = $froms[0];
            $showEditLink = false;
            $editLinkLabel = "Edit Project";
            if (strpos($fromState, '_rejected') !== false || $fromState == 'draft') {
                if ($this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER')) {
                    $showEditLink = true;
                    $editLinkLabel = "Edit Project as Requester";
                }
            }
            if (
                $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
                $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
            ) {
                $showEditLink = true;
                $editLinkLabel = "Edit Project as Primary Reviewer";
            }
            if ($this->secAuth->isGranted('ROLE_TRANSRES_ADMIN')) {
                $showEditLink = true;
                $editLinkLabel = "Edit Project as Administrator";
            }

            if ($showEditLink) {
                $thisUrl = $this->container->get('router')->generate(
                    'translationalresearch_project_edit',
                    array(
                        'id' => $project->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $editLink = "<a " .
                    //"general-data-confirm='Are you sure you want to $label?'".
                    "href=" . $thisUrl . " class='" . $classEdit . "'>" . $editLinkLabel . "</a>";
                //$links[] = $editLink;
                array_unshift($links, $editLink);
            }
            ////////// EOF add links to edit if the current state is "_rejected" //////////
        }

        //echo "count=".count($links)."<br>";

        return $links;
    }

    //MAIN method to show allowed transition to state links
    //get links to change states: Reject IRB Review and Approve IRB Review (translationalresearch_transition_action)
    public function getReviewEnabledLinkActions( $review ) {
        //exit("get review links");
        $project = $review->getProject();
        $workflow = $this->container->get('state_machine.transres_project');
        $transitions = $workflow->getEnabledTransitions($project);
        $user = $this->secTokenStorage->getToken()->getUser();

        $links = array();

        //check if review is reviewable from this state
        if( $this->isReviewCorrespondsToState($review) === false ) {
            //echo "Review ".$review->getStateStr()." does not corresponds to state=".$project->getState()."<br>";
            return $links;
        }

        //if not admin - check if the logged in user is a reviewer for this review object (show committee review links according to the assigned reviewer)
        if( $this->isAdminOrPrimaryReviewer() === false ) {
            if( $this->isReviewsReviewer($user, array($review)) === false && $this->isProjectRequester($project) === false ) {
                //exit("return: is not reviewer or requester");
                return $links;
            }
        }

        //add current state of the review object
        if( $review->getDecision() ) {
            $links[] = "<p>(Current " . $review->getSubmittedReviewerInfo() . ")</p>";
        }

//        $stateStr = $this->getAllowedFromState($project);
//
//        if( !$stateStr ) {
//            return $links;
//        }
//
//        if( $review->getStateStr() === "committee_review" ) {
//            if( strpos($stateStr, "missinginfo") !== false ) {
//                return $links;
//            }
//        }
//
//        if( false === $this->isUserAllowedFromThisStateByProjectOrReview($project,$review) ) {
//            return $links;
//        }

        foreach( $transitions as $transition ) {

            //$this->printTransition($transition);
            $transitionName = $transition->getName();
            //echo "transitionName=".$transitionName."<br>";

            if( $this->isExceptionTransition($transitionName) === true ) {
                continue;
            }

            if( $review->getStateStr() === "committee_review" ) {
                if( strpos($transitionName, "missinginfo") !== false ) {
                    continue;
                }
            }

            if( false === $this->isUserAllowedFromThisStateByProjectOrReview($project,$review) ) {
                continue;
            }

            //$tos = $transition->getTos();
            $froms = $transition->getFroms();
            foreach( $froms as $from ) {
                //echo "from=".$from."<br>"; //irb_review

                //add user's validation: $from=irb_review => user has role _IRB_REVIEW_
//                if( false === $this->isUserAllowedFromThisStateByRole($from) ) {
//                    continue;
//                }
//                if( false === $this->isUserAllowedFromThisStateByProjectOrReview($project,$review) ) {
//                    continue;
//                }

                //don't sent state $to (get it from transition object)
                $thisUrl = $this->container->get('router')->generate(
                    'translationalresearch_transition_action_by_review',
                    array(
                        'transitionName'=>$transitionName,
                        'id'=>$project->getId(),
                        'reviewId'=>$review->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                //$label = ucfirst($transitionName)." (mark as ".ucfirst($to);
                $label = $this->getTransitionLabelByName($transitionName,$review);

                $classTransition = $this->getHtmlClassTransition($transitionName);

                $generalDataConfirmation = "general-data-confirm='Are you sure you want to $label?'";

                //don't show confirmation modal
//                if( strpos($transitionName, "missinginfo") !== false ) {
//                    $generalDataConfirmation = "";
//                }

                $thisLink = "<a ".
                    //"general-data-confirm='Are you sure you want to $label?'".
                    $generalDataConfirmation.
                    "href=".$thisUrl." class='".$classTransition."'>".$label."</a>";
                $links[] = $thisLink;

            }//foreach

        }//foreach

        //echo "count=".count($links)."<br>";
        //exit();

        return $links;
    }

    //NOT USED
    //if status is missing and user is requester => add button "resubmit
    public function getResubmitButtons($review) {
        //exit("getResubmitButtons <br>");
        $project = $review->getProject();
        $workflow = $this->container->get('state_machine.transres_project');
        $transitions = $workflow->getEnabledTransitions($project);

        $links = array();
        foreach( $transitions as $transition ) {

            //$this->printTransition($transition);
            $transitionName = $transition->getName();
            //echo "transitionName=".$transitionName."<br>";

            //quick fix: only for missinginfo state
            if( strpos($transitionName, "missinginfo") !== false ) {
                return;
            }

            //$tos = $transition->getTos();
            $froms = $transition->getFroms();
            foreach( $froms as $from ) {
                //echo "from=".$from."<br>"; //irb_review

                //only if transitionName=irb_review_resubmit == irb class
                $statePrefixArr= explode("_", $transitionName); //irb
                $statePrefix = $statePrefixArr[0];
                if( strpos($review->getStateStr(), $statePrefix) === false ) {
                    continue;
                }

                //don't sent state $to (get it from transition object)
                $thisUrl = $this->container->get('router')->generate(
                    'translationalresearch_transition_action_by_review',
                    array(
                        'transitionName'=>$transitionName,
                        'id'=>$project->getId(),
                        'reviewId'=>$review->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                //$label = ucfirst($transitionName)." (mark as ".ucfirst($to);
                $label = $this->getTransitionLabelByName($transitionName);

                $classTransition = $this->getHtmlClassTransition($transitionName);

                $thisLink = "<a ".
                    "general-data-confirm='Are you sure you want to $label?'".
                    "href=".$thisUrl." class='".$classTransition."'>".$label."</a>";
                $links[] = $thisLink;

            }//foreach

        }//foreach

        //echo "count=".count($links)."<br>";

        return $links;
    }

    //$transitionName - transition name, for example, committee_review_missinginfo or final_review_missinginfo
    public function isExceptionTransition( $transitionName ) {
        if( $transitionName == "committee_review_missinginfo" ) {
            return true;
        }
        if( $transitionName == "final_review_missinginfo" ) {
            return true;
        }
        return false;
    }

    //NOT USED
    //get Review links for this user: irb_review => "IRB Review" or "IRB Review as Admin"
    //project/review/2/6 - project ID 2, review ID 6
    public function getProjectReviewLinks( $project, $user ) {
        //get project reviews for appropriate state (i.e. irb_review)
        $links = array();

        $state = $project->getState();

        $reviews = array();

        if( $state == "irb_review" ) {
            $reviews = $project->getIrbReviews();
            //$reviewEntityName = "IrbReview";
        }
        if( $state == "admin_review" ) {
            $reviews = $project->getAdminReviews();
            //$reviewEntityName = "AdminReview";
        }
        if( $state == "committee_review" ) {
            $reviews = $project->getCommitteeReviews();
            //$reviewEntityName = "CommitteeReview";
        }
        if( $state == "final_review" ) {
            $reviews = $project->getFinalReviews();
            //$reviewEntityName = "FinalReview";
        }

        //$reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);

        foreach($reviews as $review) {
            $reviewer = $review->getReviewer();
            $reviewerDelegate = $review->getReviewerDelegate();
            if(
                $reviewer && $reviewer->getId() == $user->getId() ||
                $reviewerDelegate && $reviewerDelegate->getId() == $user->getId()
            ) {
                $thisUrl = $this->container->get('router')->generate(
                    'translationalresearch_review_edit',
                    array(
                        'stateStr' => $state,
                        'reviewId'=>$review->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $label = $this->getStateSimpleLabelByName($state);
                $link = "<a ".
                    "href=".$thisUrl." target='_blank'>".$label."</a>";
                $links[] = $link;
            }
        }

        return $links;
    }

    public function isProjectEditableByRequester( $project ) {
        $state = $project->getState();
        if( strpos($state, '_rejected') !== false || $state == 'draft' ) { //|| strpos($state, "_missinginfo") !== false
            if( $this->isProjectRequester($project) === true ) {
                return true;
            }
        }
        if( $this->isProjectStateRequesterResubmit($project) ) {
            return true;
        }
        return false;
    }
    public function isProjectRequester( $project ) {
        $user = $this->secTokenStorage->getToken()->getUser();
        if( $project->getSubmitter() && $project->getSubmitter()->getId() == $user->getId() ) {
            return true;
        }
        if( $project->getPrincipalInvestigators()->contains($user) ) {
            return true;
        }
        if( $project->getCoInvestigators()->contains($user) ) {
            return true;
        }
        if( $project->getPathologists()->contains($user) ) {
            return true;
        }
        if( $project->getContacts()->contains($user) ) {
            return true;
        }
        if( $project->getBillingContacts()->contains($user) ) {
            return true;
        }
        return false;
    }
    public function isRequesterOrAdmin( $project ) {
        if( $this->isProjectRequester($project) === true ) {
            return true;
        }
        if( $this->isAdminOrPrimaryReviewer() === true ) {
            return true;
        }

        return false;
    }
    public function isAdminOrPrimaryReviewer() {
        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
        ) {
            return true;
        }
        return false;
    }
    public function hasReviewerRoles() {
        if( $this->secAuth->isGranted('ROLE_TRANSRES_IRB_REVIEWER') ) {
            return true;
        }
        if( $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return true;
        }
        if( $this->secAuth->isGranted('ROLE_TRANSRES_COMMITTEE_REVIEWER') ) {
            return true;
        }
        if( $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ) {
            return true;
        }
        return false;
    }
    public function isProjectReviewer( $project ) {
        $user = $this->secTokenStorage->getToken()->getUser();
        if( $this->isReviewsReviewer($user,$project->getIrbReviews()) ) {
            return true;
        }
        if( $this->isReviewsReviewer($user,$project->getAdminReviews()) ) {
            return true;
        }
        if( $this->isReviewsReviewer($user,$project->getCommitteeReviews()) ) {
            return true;
        }
        if( $this->isReviewsReviewer($user,$project->getFinalReviews()) ) {
            return true;
        }
        return false;
    }

    public function printTransition($transition) {
        echo $transition->getName().": ";
        $froms = $transition->getFroms();
        foreach( $froms as $from ) {
            echo "from=".$from.", ";
        }
        $tos = $transition->getTos();
        foreach( $tos as $to ) {
            echo "to=".$to.", ";
        }
        echo "<br>";
    }

    public function getTransitionByName( $project, $transitionName ) {
        $workflow = $this->container->get('state_machine.transres_project');
        $transitions = $workflow->getEnabledTransitions($project);
        foreach( $transitions as $transition ) {
            if( $transition->getName() == $transitionName ) {
                return $transition;
            }
        }
        return null;
    }

    //change transition (by the $transitionName) of the project
    public function setTransition( $project, $review, $transitionName, $to=null, $testing=false ) {

        if( !$review ) {
            throw $this->createNotFoundException('Review object does not exist');
        }

        if( !$review->getId() ) {
            throw $this->createNotFoundException('Review object ID is null');
        }

        //echo "transitionName=".$transitionName."<br>";
        $user = $this->secTokenStorage->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $workflow = $this->container->get('state_machine.transres_project');
        $break = "\r\n";

        if( !$to ) {
            //Get Transition and $to
            $transition = $transresUtil->getTransitionByName($project, $transitionName);
            $tos = $transition->getTos();
            if (count($tos) != 1) {
                throw $this->createNotFoundException('Available to state is not a single state; count=' . $tos . ": " . implode(",", $tos));
            }
            $to = $tos[0];
        }
        //echo "to=".$to."<br>";

        $label = $this->getTransitionLabelByName($transitionName);
        //echo "label=".$label."<br>";

        $originalStateStr = $project->getState();
        $originalStateLabel = $this->getStateLabelByName($originalStateStr);

        // Update the currentState on the post
        if( $workflow->can($project, $transitionName) ) {
            try {

                //$decision = $this->getDecisionByTransitionName($transitionName);
                //$review->setDecision($decision);
                $review->setDecisionByTransitionName($transitionName);

                $review->setReviewedBy($user);

                //check if like/dislike
                if( $review->getStateStr() === "committee_review" ) {
                    if( $review->getPrimaryReview() !== true ) {

                        if( !$testing ) {
                            $this->em->flush($review);
                        }

                        $recommended = true;
                        $label = $this->getStateLabelByName($project->getState());
                        $subject = "Project ID ".$project->getOid(). " (" .$label. "). Recommendation: ".$review->getDecision();
                        $body = $subject;
                        //get project url
                        $projectUrl = $transresUtil->getProjectShowUrl($project);
                        $emailBody = $body . $break.$break. "Please click on the URL below to view this project:".$break.$projectUrl;

                        //send notification emails
                        $this->sendNotificationEmails($project,$review,$subject,$emailBody,$testing);

                        //event log
                        //$this->setEventLog($project,$review,$transitionName,$originalStateStr,$body,$testing);
                        $eventType = "Review Submitted";
                        $this->setEventLog($project,$eventType,$body,$testing);

                        $this->container->get('session')->getFlashBag()->add(
                            'notice',
                            "Successful action: ".$label
                        );

                        return true;
                    }
                }

                $workflow->apply($project, $transitionName);
                //change state
                $project->setState($to); //i.e. 'irb_review'

                //check and add reviewers for this state by role? Do it when project is created?
                //$this->addDefaultStateReviewers($project);

                //write to DB
                if( !$testing ) {
                    $this->em->flush();
                }

                $recommended = false;
                $label = $this->getTransitionLabelByName($transitionName,$review);
                $subject = "Project ID ".$project->getOid()." has been sent to the status '$label' from '".$originalStateLabel."'";
                $body = $subject;
                //get project url
                $projectUrl = $transresUtil->getProjectShowUrl($project);
                $emailBody = $body . $break.$break. "Please click on the URL below to view this project:".$break.$projectUrl;

                //send confirmation email
                $this->sendNotificationEmails($project,$review,$subject,$emailBody,$testing);

                //event log
                //$this->setEventLog($project,$review,$transitionName,$originalStateStr,$body,$testing);
                $eventType = "Review Submitted";
                $this->setEventLog($project,$eventType,$body,$testing);

                $this->container->get('session')->getFlashBag()->add(
                    'notice',
                    "Successful action: ".$label
                );
                return true;
            } catch (LogicException $e) {

                //event log

                $this->container->get('session')->getFlashBag()->add(
                    'warning',
                    "Action failed: ".$label
                );
                return false;
            }//try
        }
    }

    //TODO: create default reviewers object: set reviewer and delegate reviewer for each review state.
    //add reviewers according to their roles and state
    //for example, state=irb_review => roles=ROLE_TRANSRES_IRB_REVIEWER, ROLE_TRANSRES_IRB_REVIEWER_DELEGATE
    public function addDefaultStateReviewers( $project, $addForAllStates=true ) {

        $currentState = $project->getState();
        //echo "project state=".$currentState."<br>";

        $irbReviewState = "irb_review";
        if( $currentState == $irbReviewState || $addForAllStates ) {
            $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findBy(
                array(
                    "state" => $irbReviewState,
                    "projectSpecialty" => $project->getProjectSpecialty()
                )
            );
            //echo "defaultReviewers count=".count($defaultReviewers)."<br>";
            //reviewer delegate should be added to the specific reviewer => no delegate role is required?
            foreach ($defaultReviewers as $defaultReviewer) {
                //1) create IrbReview entity
                $reviewer = $defaultReviewer->getReviewer();
                if ($reviewer) {
                    if(false === $this->isReviewsReviewer($reviewer,$project->getIrbReviews()) ) {
                        //echo "reviewer=".$reviewer."<br>";
                        $reviewEntity = new IrbReview($reviewer);
                        $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
                        if ($reviewerDelegate) {
                            $reviewEntity->setReviewerDelegate($reviewerDelegate);
                        }
                        $project->addIrbReview($reviewEntity);
                    }
                }
            }
        }

        $adminReviewState = "admin_review";
        if( $currentState == $adminReviewState || $addForAllStates ) {
            //$defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findByState($adminReviewState);
            $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findBy(
                array(
                    "state" => $adminReviewState,
                    "projectSpecialty" => $project->getProjectSpecialty()
                )
            );
            //reviewer delegate should be added to the specific reviewer => no delegate role is required?
            foreach ($defaultReviewers as $defaultReviewer) {
                //1) create IrbReview entity
                $reviewer = $defaultReviewer->getReviewer();
                if ($reviewer) {
                    if(false === $this->isReviewsReviewer($reviewer,$project->getAdminReviews()) ) {
                        $reviewEntity = new AdminReview($reviewer);
                        $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
                        if ($reviewerDelegate) {
                            $reviewEntity->setReviewerDelegate($reviewerDelegate);
                        }
                        $project->addAdminReview($reviewEntity);
                    }
                }
            }
        }

        $committeeReviewState = "committee_review";
        if( $currentState == $committeeReviewState || $addForAllStates ) {

            //$defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findByState($committeeReviewState,array("primaryReview"=>"DESC"));
            $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findBy(
                array(
                    "state" => $committeeReviewState,
                    "projectSpecialty" => $project->getProjectSpecialty()
                )
            );
            //reviewer delegate should be added to the specific reviewer => no delegate role is required?
            foreach ($defaultReviewers as $defaultReviewer) {
                //1) create CommitteeReview entity
                $reviewer = $defaultReviewer->getReviewer();
                if ($reviewer) {
                    if(false === $this->isReviewsReviewer($reviewer,$project->getCommitteeReviews()) ) {
                        $reviewEntity = new CommitteeReview($reviewer);
                        $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
                        if ($reviewerDelegate) {
                            $reviewEntity->setReviewerDelegate($reviewerDelegate);
                        }

                        //add primaryReview boolean
                        $reviewEntity->setPrimaryReview($defaultReviewer->getPrimaryReview());

                        $project->addCommitteeReview($reviewEntity);
                    }
                }
            }

        }

        $finalReviewState = "final_review";
        if( $currentState == $finalReviewState || $addForAllStates ) {

            //$defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findByState($finalReviewState);
            $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findBy(
                array(
                    "state" => $finalReviewState,
                    "projectSpecialty" => $project->getProjectSpecialty()
                )
            );
            //reviewer delegate should be added to the specific reviewer => no delegate role is required?
            foreach ($defaultReviewers as $defaultReviewer) {
                //1) create FinalReview entity
                $reviewer = $defaultReviewer->getReviewer();
                if ($reviewer) {
                    if(false === $this->isReviewsReviewer($reviewer,$project->getFinalReviews()) ) {
                        $reviewEntity = new FinalReview($reviewer);
                        $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
                        if ($reviewerDelegate) {
                            $reviewEntity->setReviewerDelegate($reviewerDelegate);
                        }
                        $project->addFinalReview($reviewEntity);
                    }
                }
            }

        }


        return $project;
    }

    public function getDefaultReviewerInfo( $state, $specialty ) {
        $infos = array();

        //$defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findByState($state,array('primaryReview' => 'DESC'));
        $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findBy(
            array(
                'state'=>$state,
                'projectSpecialty'=>$specialty->getId()
            ),
            array('primaryReview' => 'DESC')
        );

        foreach ($defaultReviewers as $defaultReviewer) {
            $info = "";
            if( $defaultReviewer->getReviewer() ) {
                $reviewerUrl = $this->container->get('router')->generate(
                    'translationalresearch_showuser',
                    array(
                        'id'=>$defaultReviewer->getReviewer()->getId()
                    )
                );
                $reviewerLink = "<a ".
                    "href=".$reviewerUrl." target='_blank'>".$defaultReviewer->getReviewer()."</a>";
                $info .= "Reviewer: ".$reviewerLink;
            }
            if( $defaultReviewer->getReviewerDelegate() ) {
                $reviewerUrl = $this->container->get('router')->generate(
                    'translationalresearch_showuser',
                    array(
                        'id'=>$defaultReviewer->getReviewerDelegate()->getId()
                    )
                );
                $reviewerLink = "<a ".
                    "href=".$reviewerUrl." target='_blank'>".$defaultReviewer->getReviewerDelegate()."</a>";
                $info .= ", Reviewer Delegate: ".$reviewerLink;
            }
            if( $defaultReviewer->getState() == "committee_review" ) {
                if( $defaultReviewer->getPrimaryReview() === true ) {
                    $info .= " (<font color=\"#8063FF\">Primary Reviewer</font>)";
                }
            }
            if( $info ) {
                $info .= "<br>";
            }
            $infos[] = $info;
        }
        return $infos;
    }

    public function isReviewsReviewer($reviewerUser, $projectReviewers ) {
        if( !$reviewerUser || !$reviewerUser->getId() ) {
            return false;
        }
        foreach($projectReviewers as $projectReviewer ) {
            //echo "userID=".$reviewerUser->getId().": review ID=".$projectReviewer->getId()."<br>";
            if( $projectReviewer->getReviewer()->getId() ) {
                //echo "userID=".$reviewerUser->getId().": reviewerID=".$projectReviewer->getReviewer()->getId()."<br>";
                if ($projectReviewer->getReviewer()->getId() == $reviewerUser->getId()) {
                    return true;
                }
            }
            if( $projectReviewer->getReviewerDelegate() ) {
                if($projectReviewer->getReviewerDelegate()->getId()) {
                    //echo "userID=".$reviewerUser->getId().": ReviewerDelegateID=".$projectReviewer->getReviewerDelegate()->getId()."<br>";
                    if ($projectReviewer->getReviewerDelegate()->getId() == $reviewerUser->getId()) {
                        return true;
                    }
                }
            }
        }
        //echo "not reviewer => return false<br>";
        return false;
    }

    public function isReviewer($reviewerUser, $review ) {
        if( !$reviewerUser || !$reviewerUser->getId() ) {
            return false;
        }
        //echo "reviewer ID=".$review->getId()."<br>";

        if ($review->getReviewer()->getId() ) {
            if ($review->getReviewer()->getId() == $reviewerUser->getId()) {
                return true;
            }
        }
        if ($review->getReviewerDelegate() && $review->getReviewerDelegate()->getId()) {
            if ($review->getReviewerDelegate()->getId() == $reviewerUser->getId()) {
                return true;
            }
        }

        //exit('not reviewer');
        return false;
    }

    public function removeReviewsFromProject($project, $originalReviews, $currentReviews) {
        foreach ($originalReviews as $originalReview) {
            if (false === $currentReviews->contains($originalReview)) {
                // remove the Task from the Tag
                $currentReviews->removeElement($originalReview);

                // if it was a many-to-one relationship, remove the relationship like this
                $originalReview->setProject(null);

                $this->em->persist($originalReview);

                // if you wanted to delete the Tag entirely, you can also do that
                $this->em->remove($originalReview);
            }
        }
        return $project;
    }

    public function processDefaultReviewersRole( $defaultReviewer, $originalReviewer=null, $originalReviewerDelegate=null ) {

        $roles = $defaultReviewer->getRoleByState();
        $reviewerRole = $roles['reviewer'];
        //$reviewerDelegateRole = $roles['reviewerDelegate'];

        $reviewer = $defaultReviewer->getReviewer();
        if( $reviewer ) {
            $reviewer->addRole($reviewerRole);
        }
        //remove role: make sure if the user is not a default reviewer in all other objects. Or don't remove role at all.
        //if( $originalReviewer && $originalReviewer != $reviewer ) {
            //$originalReviewer->removeRole($reviewerRole);
        //}

//        $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
//        if( $reviewerDelegate && $reviewerDelegateRole ) {
//            $reviewerDelegate->addRole($reviewerDelegateRole);
//        }
        $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
        if( $reviewerDelegate ) {
            $reviewerDelegate->addRole($reviewerRole);
        }

        //remove role: make sure if the user is not a default reviewer in all other objects. Or don't remove role at all.
        //if( $originalReviewerDelegate && $originalReviewerDelegate != $reviewerDelegate && $reviewerDelegateRole ) {
            //$originalReviewerDelegate->removeRole($reviewerDelegateRole);
        //}

        return $defaultReviewer;
    }

    //get the review's form page according to the project's current state (i.e. IRB Review Page) and the logged in user
//    public function getReviewLink( $project, $user=null ) {
//
//        //$workflow = $this->container->get('state_machine.transres_project');
//        //$transitions = $workflow->getEnabledTransitions($project);
//        //foreach($transitions as $transition) {
//        //    echo "transition=".$this->printTransition($transition)."<br>";
//        //}
//
//        $class = "btn btn-default";
//
//        //echo "project state=".$project->getState()."<br>";
//
//        switch( $project->getState() ) {
//            case "irb_review":
//                $thisUrl = $this->container->get('router')->generate(
//                    'translationalresearch_review_new',
//                    array(
//                        //'id'=>$project->getId()
//                    ),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );
//                $link = "<a href=".$thisUrl." class='".$class."' target='_blank'>"."IRB Review"."</a>";
//                break;
//            default:
//                $link = "Not Available for ".$project->getState();
//        }
//
//        return $link;
//    }

    //get all reviewers forms, starting with the user's review form
//    public function getReviewFormsHtml($project, $user) {
//        $html = null;
//        switch( $project->getState() ) {
//
//            case "irb_review":
//                $reviewEntityName = "IrbReview";
//                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
//                foreach($reviewObjects as $reviewObject) {
//                    $disabled = true;
//                    if( $reviewObject->getReviewer() == $user || $reviewObject->getReviewerDelegate() == $user ) {
//                        $disabled = false;
//                    }
//                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObject, array(
//                        //'form_custom_value' => $params,
//                        'data_class' => 'Oleg\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName,
//                        'disabled' => $disabled
//                    ));
//                    //$reviewHtml = $this->render('OlegTranslationalResearchBundle:ReviewBaseController:Some.html.twig', array())->getContent();
//                    //$reviewHtml = $this->redirectToRoute('translationalresearch_project_show', array('id' => $project->getId()));
//                    //TODO: use include form translationalresearch_review_edit in twig
//                }
//                break;
//
////            case "admin_review":
////                $reviewEntityName = "AdminReview";
////                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
////                foreach($reviewObjects as $reviewObject) {
////                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObject, array(
////                        //'form_custom_value' => $params,
////                        'data_class' => 'Oleg\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
////                    ));
////                }
////                break;
////
////            case "committee_review":
////                $reviewEntityName = "CommitteeReview";
////                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
////                foreach($reviewObjects as $reviewObject) {
////                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObject, array(
////                        //'form_custom_value' => $params,
////                        'data_class' => 'Oleg\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
////                    ));
////                }
////                break;
////
////            case "final_review":
////                $reviewEntityName = "FinalReview";
////                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
////                foreach($reviewObjects as $reviewObject) {
////                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObject, array(
////                        //'form_custom_value' => $params,
////                        'data_class' => 'Oleg\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
////                    ));
////                }
////                break;
//
//            default:
//                //
//        }
//        return $html;
//    }

//    public function getReviewIds($project, $user) {
//        $reviewIds = array();
//        $reviewIds[] = 4;
//        switch( $project->getState() ) {
//
//            case "irb_review":
//                $reviewEntityName = "IrbReview";
//                //$reviewIds[] = 4;
//                break;
//
//            case "admin_review":
//                $reviewEntityName = "AdminReview";
//
//                break;
//
//            case "committee_review":
//                $reviewEntityName = "CommitteeReview";
//
//                break;
//
//            case "final_review":
//                $reviewEntityName = "FinalReview";
//
//                break;
//
//            default:
//                //
//        }
//        return $reviewIds;
//    }

    public function getTransitionLabelByName( $transitionName, $review=null ) {

        //$returnLabel = "<$transitionName>";

        switch ($transitionName) {
            //initial stages
            case "to_draft":
                $label = "Save Project as Draft";
                $labeled = "Saved as Draft";
                break;
//            case "to_completed":
//                $label = "Complete Submission";
//                $labeled = "Completed Submission";
//                break;
            case "to_review":
                $label = "Submit to IRB Review";
                $labeled = "Submitted to IRB Review";
                break;
            //final stages
            case "approved_closed":
                $label = "Close Project";
                $labeled = "Closed";
                break;
            case "closed_approved":
                $label = "Re-Open previously Final Approved Project";
                $labeled = "Re-Opened (previously Final Approved Project)";
                break;

//            ///// Main Actions /////
            case "irb_review_approved":
                $label = "Approve IRB Review";
                $labeled = "Approved IRB Review";
                break;
            case "irb_review_rejected":
                $label = "Reject IRB Review";
                $labeled = "Rejected IRB Review";
                break;
            case "irb_review_missinginfo":
                $label = "Request additional information from submitter for IRB Review";
                $labeled = "Requested additional information from submitter for IRB Review";
                break;
            case "irb_review_resubmit":
                $label = "Resubmit to IRB Review";
                $labeled = "Resubmitted to IRB Review";
                break;

            case "admin_review_approved":
                $label = "Approve Admin Review";
                $labeled = "Approved Admin Review";
                break;
            case "admin_review_rejected":
                $label = "Reject Admin Review";
                $labeled = "Rejected Admin Review";
                break;
            case "admin_review_missinginfo":
                $label = "Request additional information from submitter for Admin Review";
                $labeled = "Requested additional information from submitter for Admin Review";
                break;
            case "admin_review_resubmit":
                $label = "Resubmit to Admin Review";
                $labeled = "Resubmitted to Admin Review";
                break;

            case "committee_review_approved":
                $label = "Approve Committee Review";
                $labeled = "Approved Committee Review";
                if( method_exists($review, 'getPrimaryReview') ) {
                    if( $review->getPrimaryReview() === true ) {
                        $label = $label . " as Primary Reviewer";
                        $labeled = $labeled . " as Primary Reviewer";
                    } else {
                        $label = "Recommend Approval Committee Review";
                        $labeled = "Recommended Approval Committee Review";
                    }
                }
                break;
            case "committee_review_rejected":
                $label = "Reject Committee Review";
                $labeled = "Rejected Committee Review";
                if( method_exists($review, 'getPrimaryReview') ) {
                    if( $review->getPrimaryReview() === true ) {
                        $label = $label . " as Primary Reviewer";
                    } else {
                        $label = "Recommend Reject Committee Review";
                        $labeled = "Recommended Reject Committee Review";
                    }
                }
                break;
            case "committee_review_missinginfo":
                $label = "Request additional information from submitter for Committee Review";
                $labeled = "Requested additional information from submitter for Committee Review";
                if( method_exists($review, 'getPrimaryReview') ) {
                    if( $review->getPrimaryReview() === true ) {
                        $label = $label . " as Primary Reviewer";
                        $labeled = $labeled . " as Primary Reviewer";
                    }
                }
                break;
            case "committee_review_resubmit":
                $label = "Resubmit to Committee Review";
                $labeled = "Resubmitted to Committee Review";
                break;

            case "final_review_approved":
                $label = "Approve Final Review";
                $labeled = "Approved Final Review";
                break;
            case "final_review_rejected":
                $label = "Reject Final Review";
                $labeled = "Rejected Final Review";
                break;
            case "final_review_missinginfo":
                $label = "Request additional information from submitter for Final Review";
                $labeled = "Requested additional information from submitter for Final Review";
                break;
            case "final_review_resubmit":
                $label = "Resubmit to Final Review";
                $labeled = "Resubmitted to Final Review";
                break;

            default:
                $label = null;
                $labeled = null;
        }

        if( $label ) {
            $returnLabel = $label;
        } else {
            //irb_review_approved => IRB Review Approved
            //irb_review_rejected => IRB Review Rejected
            //irb_review_missinginfo => IRB Review Missinginfo
            //irb_review_resubmit => IRB Review Resubmit
            $label = str_replace("_"," ",$transitionName);
            $label = str_replace("missinginfo","missing information",$label);
            $returnLabel = ucwords($label);
        }

        return $returnLabel;
    }

    public function getStateLabelByProject( $project ) {
        return $this->getStateLabelByName($project->getState());
    }
    public function getStateLabelByName( $stateName ) {
        switch ($stateName) {
            case "start":
                $state = "New Project";
                break;
            case "draft":
                $state = "Draft";
                break;
//            case "completed":
//                $state = "Completed";
//                break;

            case "irb_review":
                $state = "In IRB Review";
                break;
            case "irb_rejected":
                $state = "IRB Review Rejected";
                break;
            case "irb_missinginfo":
                $state = "Pending additional information from submitter for IRB Review";
                break;

            case "admin_review":
                $state = "In Admin Review";
                break;
            case "admin_rejected":
                $state = "Admin Review Rejected";
                break;
            case "admin_missinginfo":
                $state = "Pending additional information from submitter for Admin Review";
                break;

            case "committee_review":
                $state = "In Committee Review";
                break;
            case "committee_rejected":
                $state = "Committee Review Rejected";
                break;
            case "committee_missinginfo":
                $state = "Pending additional information from submitter for Committee Review";
                break;

            case "final_review":
                $state = "In Final Approval";
                break;
            case "final_approved":
                $state = "Approved";
                break;
            case "final_rejected":
                $state = "Final Approval Rejected";
                break;
            case "final_missinginfo":
                $state = "Pending additional information from submitter for Final Review";
                break;

            case "closed":
                $state = "Closed";
                break;

            default:
                $state = "<$stateName>";

        }
        return $state;
    }

    public function getStateSimpleLabelByName( $stateName ) {
        switch ($stateName) {
            case "start":
                $state = "Edit Project";
                break;
            case "draft":
                $state = "Draft";
                break;
//            case "completed":
//                $state = "Completed";
//                break;

            case "irb_review":
                $state = "IRB Review";
                break;
            case "irb_rejected":
                $state = "IRB Review Rejected";
                break;
            case "irb_missinginfo":
                $state = "Request additional information from submitter for IRB Review";
                break;

            case "admin_review":
                $state = "Admin Review";
                break;
            case "admin_rejected":
                $state = "Admin Review Rejected";
                break;
            case "admin_missinginfo":
                $state = "Request additional information from submitter for Admin Review";
                break;

            case "committee_review":
                $state = "Committee Review";
                break;
            case "committee_rejected":
                $state = "Committee Review Rejected";
                break;
            case "committee_missinginfo":
                $state = "Request additional information from submitter for Committee Review";
                break;

            case "final_review":
                $state = "Final Approval";
                break;
            case "final_approved":
                $state = "Approved";
                break;
            case "final_rejected":
                $state = "Final Approval Rejected";
                break;
            case "final_missinginfo":
                $state = "Request additional information from submitter for Final Review";
                break;

            case "closed":
                $state = "Closed";
                break;

            default:
                $state = "<$stateName>";

        }
        return $state;
    }

    public function getReviewClassNameByState($state) {
//        switch( $state ) {
//            case "irb_review":
//                $reviewEntityName = "IrbReview";
//                break;
//            case "admin_review":
//                $reviewEntityName = "AdminReview";
//                break;
//            case "committee_review":
//                $reviewEntityName = "CommitteeReview";
//                break;
//            case "final_review":
//                $reviewEntityName = "FinalReview";
//                break;
//            default:
//                $reviewEntityName = null;
//        }

        //echo "state=".$state."<br>";

        $reviewEntityName = null;

        if( strpos($state, "irb_") !== false ) {
            $reviewEntityName = "IrbReview";
        }
        if( strpos($state, "admin_") !== false ) {
            $reviewEntityName = "AdminReview";
        }
        if( strpos($state, "committee_") !== false ) {
            $reviewEntityName = "CommitteeReview";
        }
        if( strpos($state, "final_") !== false ) {
            $reviewEntityName = "FinalReview";
        }

        return $reviewEntityName;
    }

    public function getStateChoisesArr() {
        $stateArr = array(
            //'start', //Edit Project
            'draft',
//            'completed',

            'irb_review',
            'irb_rejected',
            'irb_missinginfo',

            'admin_review',
            'admin_rejected',
            'admin_missinginfo',

            'committee_review',
            'committee_rejected',
            'committee_missinginfo',

            'final_review',
            'final_approved',
            'final_rejected',
            'final_missinginfo',

            'closed'
        );

        $stateChoiceArr = array();

        foreach($stateArr as $state) {
            //$label = $state;
            $label = $this->getStateLabelByName($state);
            //$label = $label . " (" . $state . ")";
            $stateChoiceArr[$label] = $state;
        }

        return $stateChoiceArr;
    }

    public function getHtmlClassTransition( $transitionName ) {

        //irb_review_approved => IRB Review Approved
        //irb_review_rejected => IRB Review Rejected
        //irb_review_missinginfo => IRB Review Missinginfo
        //irb_review_resubmit => IRB Review Resubmit
        if( strpos($transitionName, "_approved") !== false ) {
            return "btn btn-success transres-review-submit";
        }
        if( strpos($transitionName, "_missinginfo") !== false ) {
            return "btn btn-warning transres-review-submit";
        }
        if( strpos($transitionName, "_rejected") !== false ) {
            return "btn btn-danger transres-review-submit";
        }
        if( strpos($transitionName, "_resubmit") !== false ) {
            return "btn btn-success transres-review-submit";
        }

        return "btn btn-default";
    }

    //NOT USED
//    public function getDecisionByTransitionName( $transitionName ) {
//
//        //irb_review_approved => IRB Review Approved
//        //irb_review_rejected => IRB Review Rejected
//        //irb_review_missinginfo => IRB Review Missinginfo
//        //irb_review_resubmit => IRB Review Resubmit
//        if( strpos($transitionName, "_approved") !== false ) {
//            return "approved";
//        }
//        if( strpos($transitionName, "_missinginfo") !== false ) {
//            return "missinginfo";
//        }
//        if( strpos($transitionName, "_rejected") !== false ) {
//            return "rejected";
//        }
//        if( strpos($transitionName, "_resubmit") !== false ) {
//            return null;
//        }
//
//
//        return null;
//    }

    public function getReviewByReviewidAndState($reviewId, $state) {

        $reviewEntityName = $this->getReviewClassNameByState($state);
        if( !$reviewEntityName ) {
            throw $this->createNotFoundException('Unable to find Review Entity Name by state='.$state);
        }
        //echo "reviewEntityName=".$reviewEntityName."<br>";

        $reviewObject = $this->em->getRepository('OlegTranslationalResearchBundle:'.$reviewEntityName)->find($reviewId);
        if( !$reviewObject ) {
            throw $this->createNotFoundException('Unable to find '.$reviewEntityName.' by id='.$reviewId);
        }

        return $reviewObject;
    }

    public function getReviewsByProjectAndState($project,$state) {
        $reviewEntityName = $this->getReviewClassNameByState($state);
        if( !$reviewEntityName ) {
            throw $this->createNotFoundException('Unable to find Review Entity Name by state='.$state);
        }

        $reviews = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project);

        return $reviews;
    }

    //NOT USED
//    public function getReviewByProjectAndReviewidAndState($project, $reviewId, $state) {
//
//        $reviewEntityName = $this->getReviewClassNameByState($state);
//        if( !$reviewEntityName ) {
//            throw $this->createNotFoundException('Unable to find Review Entity Name by state='.$state);
//        }
//        //echo "reviewEntityName=".$reviewEntityName."<br>";
//
//        if(1) {
//            $reviewObject = $this->em->getRepository('OlegTranslationalResearchBundle:'.$reviewEntityName)->find($reviewId);
//            if( !$reviewObject ) {
//                throw $this->createNotFoundException('Unable to find '.$reviewEntityName.' by id='.$reviewId);
//            }
//            return $reviewObject;
//        } else {
//
//            $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName, $project, null, $reviewId);
//            //echo "reviewObjects count=".count($reviewObjects)."<br>";
//
//            if (count($reviewObjects) == 1) {
//                return $reviewObjects[0];
//            }
//
//            if (count($reviewObjects) == 0) {
//                return null;
//            }
//
//            if (count($reviewObjects) > 1) {
//                //throw new \Exception("No single Review object $reviewEntityName founded: ID ".$reviewId);
//                return $reviewObjects[0];
//            }
//        }
//
//        return null;
//    }

    //create a review form (for example, IrbReview form if logged in user is a reviewer or reviewer delegate)
    //1) if project is in the review state: irb_review, admin_review, committee_review or final_review
    //2) if the current user is added to this project as the reviewer for the state above
    public function getReviewForm($project, $user) {

        switch( $project->getState() ) {

            case "irb_review":
                $reviewEntityName = "IrbReview";
                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
                if( count($reviewObjects) > 0 ) {
                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObjects[0], array(
                        //'form_custom_value' => $params,
                        'data_class' => 'Oleg\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
                    ));
                }
                break;

            case "admin_review":
                $reviewEntityName = "AdminReview";
                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
                if( count($reviewObjects) > 0 ) {
                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObjects[0], array(
                        //'form_custom_value' => $params,
                        'data_class' => 'Oleg\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
                    ));
                }
                break;

            case "committee_review":
                $reviewEntityName = "CommitteeReview";
                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
                if( count($reviewObjects) > 0 ) {
                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObjects[0], array(
                        //'form_custom_value' => $params,
                        'data_class' => 'Oleg\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
                    ));
                }
                break;

            case "final_review":
                $reviewEntityName = "FinalReview";
                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
                if( count($reviewObjects) > 0 ) {
                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObjects[0], array(
                        //'form_custom_value' => $params,
                        'data_class' => 'Oleg\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
                    ));
                }
                break;

            default:
                //
        }

        return $reviewForm;
    }
    //$reviewObjectClassName - review entity class name (i.e. "IrbReview")
    public function findReviewObjectsByProjectAndAnyReviewers( $reviewObjectClassName, $project, $reviewer=null, $reviewId=null ) {
//        $reviewObject = null;
//        if( $reviewObjectClassName && $reviewer ) {
//            $reviewObject = $this->em->getRepository('OlegTranslationalResearchBundle:' . $reviewObjectClassName)->findBy(array(
//                'reviewer' => $reviewer->getId(),
//                'project' => $project->getId()
//            ));
//            if (!$reviewObject) {
//                $reviewObject = $this->em->getRepository('OlegTranslationalResearchBundle:' . $reviewObjectClassName)->findByReviewerDelegate($reviewer);
//            }
//        }
        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:' . $reviewObjectClassName);
        $dql =  $repository->createQueryBuilder("review");
        $dql->select('review');
        $dql->GroupBy('review');
        $dql->leftJoin("review.project", "project");
        $dql->leftJoin("review.reviewer", "reviewer");
        $dql->leftJoin("review.reviewerDelegate", "reviewerDelegate");

        $dql->where("project.id=:projectId");

        $parameters = array("projectId"=>$project->getId());

        if( $reviewer ) {
            $dql->andWhere("reviewer.id=:reviewerId OR reviewerDelegate.id=:reviewerId");
            $parameters['reviewerId'] = $reviewer->getId();
        }

        if( $reviewId ) {
            $dql->andWhere("review.id=:reviewId");
            $parameters['reviewId'] = $reviewId;
        }

        $query = $dql->getQuery();

        //echo "projectId=".$project->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";
        //echo "query=".$query->getSql()."<br>";

        $query->setParameters($parameters);

        $reviewObjects = $query->getResult();

        return $reviewObjects;
    }

    //NOT USED: roles are not relable for each project
    //add user's validation (rely on Role): $from=irb_review => user has role _IRB_REVIEW_
    public function isUserAllowedFromThisStateByRole($from) {

        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
        ) {
            return true;
        }

        $user = $this->secTokenStorage->getToken()->getUser();
        $sitename = $this->container->getParameter('translationalresearch.sitename');

        $role = null;
        if( $from == "irb_review" ) {
            $role = "_IRB_REVIEW_";
        }
        if( $from == "admin_review" ) {
            $role = "_ADMIN_REVIEW_";
        }
        if( $from == "committee_review" ) {
            $role = "_COMMITTEE_REVIEW_";
        }
        if( $from == "final_review" ) {
            $role = "_FINAL_REVIEW_";
        }

        if( $role && $this->em->getRepository('OlegUserdirectoryBundle:User')->isUserHasSiteAndPartialRoleName($user,$sitename,$role) ) {
            return true;
        }

        return false;
    }
    //check if the current logged in user has permission to make changes from the current project state
    public function isUserAllowedFromThisStateByProjectOrReview($project, $review=null) {

        if( !$project ) {
            $project = $review->getProject();
        }

//        $workflow = $this->container->get('state_machine.transres_project');
//        $transitions = $workflow->getEnabledTransitions($project);
//
//        if( count($transitions) != 1 ) {
//            throw new \Exception("Review with ID ".$review->getId()." does not have a project");
//        }
//
//        foreach($transitions as $transition) {
//            $this->printTransition($transition);
//        }
//        exit('1');

        $stateStr = $this->getAllowedFromState($project); //must be equal to the current project state
        if( !$stateStr ) {
            return false;
        }

        if( $this->isAdminOrPrimaryReviewer() ) {
            return true;
        }

        $user = $this->secTokenStorage->getToken()->getUser();

        //check if reviewer
        if( $this->isProjectStateReviewer($project,$user) ) {
            return true;
        }

        //check if submitter and project state has _missinginfo
//        if( strpos($stateStr, "_missinginfo") !== false ) {
//            if( $this->isProjectRequester($project) ) {
//                return true;
//            }
//        }
        if( $this->isProjectStateRequesterResubmit($project) ) {
            return true;
        }

        return false;
    }

    //return true if the project is in missinginfo state and logged in user is a requester or admin
    public function isProjectStateRequesterResubmit($project) {
        $stateStr = $this->getAllowedFromState($project);
        if( !$stateStr ) {
            return false;
        }

        if( strpos($stateStr, "_missinginfo") !== false ) {
            if ($this->isAdminOrPrimaryReviewer()) {
                return true;
            }
            if( $this->isProjectRequester($project) ) {
                return true;
            }
        }
        return false;
    }

    //return true if the project is in review state and logged in user is a reviewer or admin
    public function isProjectStateReviewer($project,$user) {

        $stateStr = $this->getAllowedFromState($project); //must be equal to the current project state
        if( !$stateStr ) {
            return false;
        }

        $reviews = array();

        if( $stateStr == "irb_review" ) {
            $reviews = $project->getIrbReviews();
        }
        if( $stateStr == "admin_review" ) {
            $reviews = $project->getAdminReviews();
        }
        if( $stateStr == "committee_review" ) {
            $reviews = $project->getCommitteeReviews();
        }
        if( $stateStr == "final_review" ) {
            $reviews = $project->getFinalReviews();
        }

        //echo $stateStr.": reviews count=".count($reviews)."<br>";
        if( count($reviews) > 0 ) {
            if ($this->isAdminOrPrimaryReviewer()) {
                return true;
            }
            if ($this->isReviewsReviewer($user, $reviews)) {
                return true;
            }
        }

        return false;
    }

    //only a single from state must exists, but a project can have multiple transitions and tos
    public function getAllowedFromState($project) {

        $stateStr = $project->getState();

        //double check this state by state machine
        $workflow = $this->container->get('state_machine.transres_project');

        $transitions = $workflow->getEnabledTransitions($project);

        foreach( $transitions as $transition ) {
            foreach( $transition->getFroms() as $fromStateStr ) {
                if( $fromStateStr == $stateStr ) {
                    return $stateStr;
                }
            }
        }

        return null;

//        if( count($transitions) == 0 ) {
//            //rejected => no states
//            return null;
//            //throw new \Exception("State Machine: Project does not have any transition. project state=".$project->getState());
//        }
//        //TODO: project can have 3 transitions => rewrite to get a single fromState
//        if( count($transitions) != 1 ) {
//            throw new \Exception("State Machine: Project must have only one transition. Number of transitions=".count($transitions));
//        }
//        $transition = $transitions[0];
//
//        $froms = $transition->getFroms();
//        if( count($froms) != 1 ) {
//            throw new \Exception("State Machine: Project must have only one from state. Number of from states=".count($froms));
//        }
//        $from = $froms[0];
//
//        return $from;
    }

    public function isUserAllowedReview( $review ) {
        //echo "reviewId=".$review->getId()."<br>";
        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
        ) {
            //echo "isUserAllowedReview: admin ok <br>";
            return true;
        }

        $user = $this->secTokenStorage->getToken()->getUser();
        if( $this->isReviewer($user,$review) ) {
            return true;
        }

        return false;
    }

    public function isReviewCorrespondsToState($review) {
        if( !$review ) {
            return false;
        }
        $project = $review->getProject();
        if( !$project ) {
            return false;
        }

        //1) check if project state is reviewable
        $projectStateReviewable = false;
        $projectState = $project->getState();
        //echo "projectId=".$project->getId()."<br>";
        //echo "projectState=".$projectState."<br>";

        //check if the $review->getStateStr() has prefix (i.e. irb)
        //only if transitionName=irb_review_resubmit == irb class
        $statePrefixArr= explode("_", $projectState); //irb,review
        $statePrefix = $statePrefixArr[0]; //irb
        if( strpos($review->getStateStr(), $statePrefix) !== false ) {
            return true;
        }

        return false;

//        if( strpos($projectState, '_missinginfo') !== false ) {
//            $projectStateReviewable = true;
//            if( strpos($projectState, '_missinginfo') !== false ) {
//
//            }
//        }
//
//        if( $projectState == "irb_review" ) {
//            $projectStateReviewable = true;
//        }
//        if( $projectState == "admin_review" ) {
//            $projectStateReviewable = true;
//        }
//        if( $projectState == "committee_review" ) {
//            $projectStateReviewable = true;
//        }
//        if( $projectState == "final_review" ) {
//            $projectStateReviewable = true;
//        }
//
//        if( $projectStateReviewable === false ) {
//            return false;
//        }
//
//        //2) condition to allow edit only if project state is allow to edit this type of review (committee_review)
//        if( $projectState == $review->getStateStr() ) {
//            return true;
//        }
//
//        return false;
    }

    //NOT USED
    public function processProjectOnReviewUpdate( $review, $stateStr, $request, $testing=false ) {

        $project = $review->getProject();
        if( !$project ) {
            throw new \Exception("Review with ID ".$review->getId()." does not have a project");
            //return null;
        }

        $user = $this->secTokenStorage->getToken()->getUser();
        $userSecUtil = $this->container->get('user_security_utility');
        $transresUtil = $this->container->get('transres_util');
        $break = "\r\n";
        //echo "user=".$user."<br>";

        //$currentState = $project->getState();

        //set project next transit state depends on the decision
        $appliedTransition = $this->setProjectState($project,$review,$testing);
        //exit("exit appliedTransition=".$appliedTransition);

        if( $appliedTransition ) {
            $recommended = false;
            $eventType = "Review Submitted";
            $label = $this->getTransitionLabelByName($appliedTransition,$review);
            $subject = "Project ID ".$project->getOid()." has been sent to the status '$label'";
            $body = "Project ID ".$project->getOid()." has been sent to the status '$label'";
        } else {
            $recommended = true;
            $eventType = "Review Submitted";
            $label = $this->getStateLabelByName($project->getState());
            $subject = "Project ID ".$project->getOid(). " (" .$label. "). Recommendation: ".$review->getDecision();
            $body = $subject;
        }

        //get project url
        $projectUrl = $transresUtil->getProjectShowUrl($project);
        $emailBody = $body . $break.$break. "Please click on the URL below to view this project:".$break.$projectUrl;

        //send notification emails
        $this->sendNotificationEmails($project,$review,$subject,$emailBody,$testing);

//        $workflow = $this->container->get('state_machine.transres_project');
//        $transitions = $workflow->getEnabledTransitions($project);
//        $transitionArr = array();
//        foreach ($transitions as $transition) {
//            echo "transition=" . $this->printTransition($transition) . "<br>";
//            $transitionArr[] = $this->printTransition($transition);
//        }
//        $projectTransition = "Project transition " . implode(";",$transitionArr);

//        //Event Log
//        if( $appliedTransition ) {
//            $eventType = "Review Submitted";
//            $event = "Project's (ID# " . $project->getId() . ") review has been successfully submitted. ".$review->getSubmittedReviewerInfo();
//
//            //testing
//            echo "appliedTransition=" . $appliedTransition . "<br>";
//            //echo "printTransition=".$this->printTransition($appliedTransition)."<br>";
//
//            $event .= ";<br> Project transitioned from '" . $this->getStateLabelByName($stateStr) . "'".
//                " to '" . $this->getStateLabelByName($project->getState()) . "'";
//            echo "event=".$event."<br>";
//
//            //exit('1');
//
//        } else {
//            $eventType = "Review Submitting Not Performed";
//            $event = "Project's (ID# " . $project->getId() . ") review submitting not performed. " . $review->getSubmittedReviewerInfo();
//            $event .= ";<br> Project transitioned from '" . $this->getStateLabelByName($stateStr) . "'" .
//                " to '" . $this->getStateLabelByName($project->getState()) . "'";
//            echo "event=".$event."<br>";
//
//            //exit('2');
//        }
//
//        $userSecUtil = $this->container->get('user_security_utility');
//        $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'),$event,$user,$review,$request,$eventType);

        //event log
        //$this->setEventLog($project,$review,$appliedTransition,$stateStr,$eventType,$body,$testing);
        $this->setEventLog($project,$eventType,$body,$testing);
    }
    //used by processProjectOnReviewUpdate
    public function setProjectState( $project, $review, $testing=false ) {

        $appliedTransition = null;

        echo "decision=".$review->getDecision()."<br>";
        if( $review->getDecision() == null ) {
            return $appliedTransition;
        }

        //for not primary Committee review don't change the project state.
        //if( is_a($review,"CommitteeReview") ) {
        if( $review instanceof CommitteeReview ) {
            //echo "CommitteeReview <br>";
            if( $review->getPrimaryReview() === false ) {
                //exit("1");
                return $appliedTransition;
            }
        }

        $workflow = $this->container->get('state_machine.transres_project');
        $transitions = $workflow->getEnabledTransitions($project);

        echo "<pre>";
        print_r($transitions);
        echo "</pre><br><br>";

        $transitionNameYes = null;
        $toYes = null;
        $transitionNameNo = null;
        $toNo = null;
        $transitionNameMissinginfo = null;
        $toMissinginfo = null;

        foreach($transitions as $transition) {
            $transitionName = $transition->getName();
            echo "transitionName=".$transitionName."<br>"; //"irb_review_no" or "to_admin_review"

            if( strpos($transitionName, '_rejected') !== false ) {
                echo "to: No<br>";
                $transitionNameNo = $transitionName;
                $tos = $transition->getTos();
                if( count($tos) > 1 ) {
                    throw new \Exception("State machine must have only one to state. To count=".count($tos));
                }
                $toNo = $tos[0];
            }

            if (strpos($transitionName, '_approved') !== false ) {
                echo "to: Yes<br>";
                $transitionNameYes = $transitionName;
                $tos = $transition->getTos();
                if (count($tos) > 1) {
                    throw new \Exception("State machine must have only one to state. To count=" . count($tos));
                }
                $toYes = $tos[0];
            }

            if (strpos($transitionName, '_missinginfo') !== false ) {
                echo "to: missinginfo<br>";
                $transitionNameMissinginfo = $transitionName;
                $tos = $transition->getTos();
                if (count($tos) > 1) {
                    throw new \Exception("State machine must have only one to state. To count=" . count($tos));
                }
                $toMissinginfo = $tos[0];
            }

        }//foreach

        if( $review->getDecision() == "rejected" && $toNo ) {
            echo "transit project to No: $toNo <br>";
            //$project->setState($toNo);
            $transitionNameFinal = $transitionNameNo;
            //$appliedTransition = true;
        }

        if( $review->getDecision() == "approved" && $toYes ) {
            echo "transit project to Yes: $toYes <br>";
            //$project->setState($toYes);
            $transitionNameFinal = $transitionNameYes;
            //$appliedTransition = true;
        }

        if( $review->getDecision() == "missinginfo" && $toMissinginfo ) {
            echo "transit project to missinginfo: $toMissinginfo <br>";
            //$project->setState($toMissinginfo);
            $transitionNameFinal = $transitionNameMissinginfo;
            //$appliedTransition = true;
        }

        if( $transitionNameFinal ) {
            try {
                echo "try apply transition=$transitionNameFinal <br>";
                $workflow->apply($project, $transitionNameFinal);
                $appliedTransition = $transitionNameFinal;

                //write to DB
                if( !$testing ) {
                    $this->em->flush($project);
                }

                $this->container->get('session')->getFlashBag()->add(
                    'notice',
                    "Successful transition: ".$transitionNameFinal."; Project is ".$this->getStateLabelByProject($project)
                );

            } catch (LogicException $e) {
                throw new \Exception("Can not change project's state: transitionNameFinal=" . $transitionNameFinal);
            }
        }

        echo "setProjectState: appliedTransition= $appliedTransition <br>";

        return $appliedTransition;
    }

    //Event Log
    public function setEventLog($project, $eventType, $event, $testing=false) {
        $user = $this->secTokenStorage->getToken()->getUser();
        $userSecUtil = $this->container->get('user_security_utility');

//        if( $appliedTransition ) {
//            $eventType = "Review Submitted";
//
//            if( !$event ) {
//                $event = "Project's (ID# " . $project->getId() . ") review has been successfully submitted. " . $review->getSubmittedReviewerInfo();
//
//                //testing
//                echo "appliedTransition=" . $appliedTransition . "<br>";
//                //echo "printTransition=".$this->printTransition($appliedTransition)."<br>";
//
//                $event .= ";<br> Project transitioned from '" . $this->getStateLabelByName($originalStateStr) . "'" .
//                    " to '" . $this->getStateLabelByName($project->getState()) . "'";
//                echo "event=" . $event . "<br>";
//
//                //exit('1');
//            }
//
//        } else {
//            $eventType = "Review Submitting Not Performed";
//            if( !$event ) {
//                $event = "Project's (ID# " . $project->getId() . ") review submitting not performed. " . $review->getSubmittedReviewerInfo();
//                $event .= ";<br> Project transitioned from '" . $this->getStateLabelByName($originalStateStr) . "'" .
//                    " to '" . $this->getStateLabelByName($project->getState()) . "'";
//                echo "event=" . $event . "<br>";
//
//                //exit('2');
//            }
//        }

        if( !$testing ) {
            $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'), $event, $user, $project, null, $eventType);
        }
    }

    public function sendNotificationEmails($project, $review, $subject, $body, $testing=false) {
        //if( !$appliedTransition ) {
        //    return null;
        //}

        $emailUtil = $this->container->get('user_mailer_utility');

        $senderEmail = null; //Admin email
        $emails = array();

//        $label = $this->getTransitionLabelByName($appliedTransition,$review);
//        if( $recommended ) {
//            $subject = "Project ID ".$project->getOid()." has been recommended to send to the status '$label'";
//            $body = "Project ID ".$project->getOid()." has been sent to the status '$label'";
//        } else {
//            $subject = "Project ID ".$project->getOid()." has been sent to the status '$label'";
//            $body = "Project ID ".$project->getOid()." has been sent to the status '$label'";
//        }

        //send to the
        // 1) admins and primary reviewers
        $admins = $this->getTransResAdminEmails(); //ok
        $emails = array_merge($emails,$admins);

        // 2) project's Requester (submitter, principalInvestigators, coInvestigators, pathologists)
        $requesterEmails = $this->getRequesterEmails($project); //ok
        $emails = array_merge($emails,$requesterEmails);

        if( $review ) {
            // 3) current project's reviewers
            $currentReviewerEmails = $this->getCurrentReviewersEmails($review); //ok
            $emails = array_merge($emails, $currentReviewerEmails);
        }

        // 4) next state project's reviewers
        $nextStateReviewerEmails = $this->getNextStateReviewersEmails($project,$project->getState());
        $emails = array_merge($emails,$nextStateReviewerEmails);

        $emails = array_unique($emails);

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $subject, $body, null, $senderEmail );

    }
    //get all users with admin and ROLE_TRANSRES_PRIMARY_REVIEWER, ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE
    public function getTransResAdminEmails($asEmail=true) {
        $users = array();

        $admins = $this->em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole("ROLE_TRANSRES_ADMIN");
        foreach( $admins as $user ) {
            if( $user ) {
                if( $asEmail ) {
                    $users[] = $user->getSingleEmail();
                } else {
                    $users[] = $user;
                }
            }
        }

        $primarys = $this->em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole("ROLE_TRANSRES_PRIMARY_REVIEWER");
        foreach( $primarys as $user ) {
            if( $user ) {
                if( $asEmail ) {
                    $users[] = $user->getSingleEmail();
                } else {
                    $users[] = $user;
                }
            }
        }

        return $users;
    }
    //project's Requester (submitter, principalInvestigators, coInvestigators, pathologists)
    public function getRequesterEmails($project, $asEmail=true) {
        $resArr = array();

        //1 submitter
        if( $project->getSubmitter() ) {
            if( $asEmail ) {
                $resArr[] = $project->getSubmitter()->getSingleEmail();
            } else {
                $resArr[] = $project->getSubmitter();
            }
        }

        //2 principalInvestigators
        $pis = $project->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi ) {
                if( $asEmail ) {
                    $resArr[] = $pi->getSingleEmail();
                } else {
                    $resArr[] = $pi;
                }
            }
        }

        //3 coInvestigators
        $cois = $project->getCoInvestigators();
        foreach( $cois as $coi ) {
            if( $coi ) {
                if( $asEmail ) {
                    $resArr[] = $coi->getSingleEmail();
                } else {
                    $resArr[] = $coi;
                }
            }
        }

        //4 pathologists
        $pathologists = $project->getPathologists();
        foreach( $pathologists as $pathologist ) {
            if( $pathologist ) {
                if( $asEmail ) {
                    $resArr[] = $pathologist->getSingleEmail();
                } else {
                    $resArr[] = $pathologist;
                }
            }
        }

        //5 contacts
        $contacts = $project->getContacts();
        foreach( $contacts as $contact ) {
            if( $contact ) {
                if( $asEmail ) {
                    $resArr[] = $contact->getSingleEmail();
                } else {
                    $resArr[] = $contact;
                }
            }
        }

        //6 Billing contacts
        $billingContacts = $project->getBillingContacts();
        foreach( $billingContacts as $billingContact ) {
            if( $billingContact ) {
                if( $asEmail ) {
                    $resArr[] = $billingContact->getSingleEmail();
                } else {
                    $resArr[] = $billingContact;
                }
            }
        }

        return $resArr;
    }

    //current project's reviewers
    public function getCurrentReviewersEmails($review, $asEmail=true) {
        $resArr = array();

        //get reviewers
        $reviewer = $review->getReviewer();
        if( $reviewer ) {
            if( $asEmail ) {
                $resArr[] = $reviewer->getSingleEmail();
            } else {
                $resArr['reviewer'] = $reviewer;//->getUsernameOptimal();
            }
        }

        $reviewerDelegate = $review->getReviewerDelegate();
        if( $reviewerDelegate ) {
            if( $asEmail ) {
                $resArr[] = $reviewerDelegate->getSingleEmail();
            } else {
                $resArr['reviewerDelegate'] = $reviewerDelegate;//->getUsernameOptimal();
            }
        }

        return $resArr;
    }

    //next state project's reviewers
    public function getNextStateReviewersEmails($project, $nextStateStr, $asEmail=true) {
        $emails = array();

        //get next state
        $reviews = $this->getReviewsByProjectAndState($project,$nextStateStr);
        foreach($reviews as $review) {
            $currentReviewerEmails = $this->getCurrentReviewersEmails($review); //ok
            $emails = array_merge($emails,$currentReviewerEmails);
        }

        $emails = array_unique($emails);

        return $emails;
    }

    public function getTransResProjectSpecialties() {
        $specialties = $this->em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->findBy(
            array(
                'type' => array("default","user-added")
            )
        );
        return $specialties;
    }

    public function getCommentAuthorNameByLoggedUser( $comment ) {

        $authorType = $comment->getAuthorTypeDescription();
        if( $authorType ) {
            $authorType = " (".$authorType.")";
        } else {
            $authorType = $comment->getAuthorType();
            if( $authorType ) {
                $authorType = " (".$authorType.")";
            }
        }

        if( !$comment->getAuthor() ) {
            return "Anonymous" . $authorType;
        }

        $user = $this->secTokenStorage->getToken()->getUser();

        if( $this->isAdminOrPrimaryReviewer() ) {
            return $comment->getAuthorName() . $authorType;
        }

        if( $user->getId() == $comment->getAuthor()->getId() ) {
            return $comment->getAuthorName() . $authorType;
        }

        return "Anonymous" . $authorType;
    }

    public function getProjectShowUrl($project) {
        $projectUrl = $this->container->get('router')->generate(
            'translationalresearch_project_show',
            array(
                'id' => $project->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $projectUrl;
    }

    //$specialtyStr: new-ap-cp-project, ap-cp
    public function getSpecialtyObject($specialtyAbbreviation) {
        //echo "specialtyStr=".$specialtyStr."<br>";
        //$specialty is a url prefix (i.e. "new-ap-cp-project")
//        $specialtyAbbreviation = SpecialtyList::getProjectAbbreviationFromUrlPrefix($specialtyStr);
//        if( !$specialtyAbbreviation ) {
//            throw new \Exception( "Project specialty abbreviation is not found by name '".$specialtyStr."'" );
//        }
        $specialty = $this->em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation($specialtyAbbreviation);
        if( !$specialty ) {
            throw new \Exception( "Project specialty is not found by name '".$specialtyAbbreviation."'" );
        }

        return $specialty;
    }

    //show it only to admin, reviewers and reviewedBy users
    public function showReviewedBy( $reviewObject ) {

        if( $this->isAdminOrPrimaryReviewer() ) {
            return true;
        }

        $user = $this->secTokenStorage->getToken()->getUser();
        if( $this->isReviewer($user,$reviewObject) ) {
            return true;
        }



        return false;
    }

}