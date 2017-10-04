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
                if( false === $this->isUserAllowedFromThisState($from) ) {
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

                $thisLink = "<a ".
                    "general-data-confirm='Are you sure you want to $label?'".
                    "href=".$thisUrl." class='".$classTransition."'>".$label."</a>";
                $links[] = $thisLink;

            }//foreach

        }//foreach

        ////////// add links to edit if the current state is "_rejected" //////////
        $froms = $transition->getFroms();
        $fromState = $froms[0];
        $showEditLink = false;
        $editLinkLabel = "Edit Project";
        if( strpos($fromState, '_rejected') !== false || $fromState == 'draft' || $fromState == 'complete' ) {
            if( $this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER') ) {
                $showEditLink = true;
                $editLinkLabel = "Edit Project as Requester";
            }
        }
        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
        ) {
            $showEditLink = true;
            $editLinkLabel = "Edit Project as Primary Reviewer";
        }
        if( $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ) {
            $showEditLink = true;
            $editLinkLabel = "Edit Project as Administrator";
        }

        if( $showEditLink ) {
            $thisUrl = $this->container->get('router')->generate(
                'translationalresearch_project_edit',
                array(
                    'id'=>$project->getId()
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $editLink = "<a ".
                //"general-data-confirm='Are you sure you want to $label?'".
                "href=".$thisUrl." class='".$classEdit."'>".$editLinkLabel."</a>";
            //$links[] = $editLink;
            array_unshift($links,$editLink);
        }
        ////////// EOF add links to edit if the current state is "_rejected" //////////

        //echo "count=".count($links)."<br>";

        return $links;
    }

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
        if( $state == "irb_admin" ) {
            $reviews = $project->getAdminReviews();
            //$reviewEntityName = "AdminReview";
        }
        if( $state == "irb_committee" ) {
            $reviews = $project->getCommitteeReviews();
            //$reviewEntityName = "CommitteeReview";
        }
        if( $state == "irb_final" ) {
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

    //TODO: requester => project is on the draft stage or in the reject stage
    public function isProjectEditableByRequester( $project ) {
        $state = $project->getState();
        if( strpos($state, '_rejected') !== false || $state == 'draft' || $state == 'complete' ) {
            if( $this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER') ) {
//                $user = $this->secTokenStorage->getToken()->getUser();
//                if( $project->getSubmitter() && $project->getSubmitter()->getId() == $user->getId() ) {
//                    return true;
//                }
//                if( $project->getPrincipalInvestigators()->contains($user) ) {
//                    return true;
//                }
//                if( $project->getCoInvestigators()->contains($user) ) {
//                    return true;
//                }
//                if( $project->getPathologists()->contains($user) ) {
//                    return true;
//                }
                if( $this->isProjectRequester($project) === true ) {
                    return true;
                }
            }
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
        return false;
    }
    public function isRequesterOrAdmin( $project ) {
        if(
            $this->isProjectRequester($project) === true ||
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
        ) {
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
    public function setTransition( $project, $transitionName, $to=null ) {
        $transresUtil = $this->container->get('transres_util');
        $workflow = $this->container->get('state_machine.transres_project');

        if( !$to ) {
            //Get Transition and $to
            $transition = $transresUtil->getTransitionByName($project, $transitionName);
            $tos = $transition->getTos();
            if (count($tos) != 1) {
                throw $this->createNotFoundException('Available to state is not a single state; count=' . $tos . ": " . implode(",", $tos));
            }
            $to = $tos[0];
        }

        $label = $this->getTransitionLabelByName($transitionName);

        // Update the currentState on the post
        if( $workflow->can($project, $transitionName) ) {
            try {
                $workflow->apply($project, $transitionName);
                //change state
                $project->setState($to); //i.e. 'irb_review'

                //check and add reviewers for this state by role? Do it when project is created?
                //$this->addDefaultStateReviewers($project);

                //write to DB
                $this->em->flush($project);

                //send confirmation Emails

                $this->container->get('session')->getFlashBag()->add(
                    'notice',
                    "Successful action: ".$label
                );
                return true;
            } catch (LogicException $e) {
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
            $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findByState($irbReviewState);
            //echo "defaultReviewers count=".count($defaultReviewers)."<br>";
            //reviewer delegate should be added to the specific reviewer => no delegate role is required?
            foreach ($defaultReviewers as $defaultReviewer) {
                //1) create IrbReview entity
                $reviewer = $defaultReviewer->getReviewer();
                if ($reviewer) {
                    if(false === $this->isProjectReviewer($reviewer,$project->getIrbReviews()) ) {
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
            $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findByState($adminReviewState);
            //reviewer delegate should be added to the specific reviewer => no delegate role is required?
            foreach ($defaultReviewers as $defaultReviewer) {
                //1) create IrbReview entity
                $reviewer = $defaultReviewer->getReviewer();
                if ($reviewer) {
                    if(false === $this->isProjectReviewer($reviewer,$project->getAdminReviews()) ) {
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

            $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findByState($committeeReviewState,array("primaryReview"=>"DESC"));
            //reviewer delegate should be added to the specific reviewer => no delegate role is required?
            foreach ($defaultReviewers as $defaultReviewer) {
                //1) create CommitteeReview entity
                $reviewer = $defaultReviewer->getReviewer();
                if ($reviewer) {
                    if(false === $this->isProjectReviewer($reviewer,$project->getCommitteeReviews()) ) {
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

            $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findByState($finalReviewState);
            //reviewer delegate should be added to the specific reviewer => no delegate role is required?
            foreach ($defaultReviewers as $defaultReviewer) {
                //1) create FinalReview entity
                $reviewer = $defaultReviewer->getReviewer();
                if ($reviewer) {
                    if(false === $this->isProjectReviewer($reviewer,$project->getFinalReviews()) ) {
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

    public function getDefaultReviewerInfo($state) {
        $infos = array();
        $defaultReviewers = $this->em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findByState($state,array('primaryReview' => 'DESC'));
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

    public function isProjectReviewer($reviewerUser, $projectReviewers ) {
        if( !$reviewerUser || !$reviewerUser->getId() ) {
            return false;
        }
        foreach($projectReviewers as $projectReviewer ) {
            if ($projectReviewer->getReviewer()->getId() ) {
                if ($projectReviewer->getReviewer()->getId() == $reviewerUser->getId()) {
                    return true;
                }
            }
            if ($projectReviewer->getReviewerDelegate() && $projectReviewer->getReviewerDelegate()->getId()) {
                if ($projectReviewer->getReviewerDelegate()->getId() == $reviewerUser->getId()) {
                    return true;
                }
            }
        }
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
        $reviewerDelegateRole = $roles['reviewerDelegate'];

        $reviewer = $defaultReviewer->getReviewer();
        if( $reviewer ) {
            $reviewer->addRole($reviewerRole);
        }
        if( $originalReviewer && $originalReviewer != $reviewer ) {
            $originalReviewer->removeRole($reviewerRole);
        }

        $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
        if( $reviewerDelegate && $reviewerDelegateRole ) {
            $reviewerDelegate->addRole($reviewerDelegateRole);
        }
        if( $originalReviewerDelegate && $originalReviewerDelegate != $reviewerDelegate && $reviewerDelegateRole ) {
            $originalReviewerDelegate->removeRole($reviewerDelegateRole);
        }

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

    public function getTransitionLabelByName( $transitionName ) {

        //$returnLabel = "<$transitionName>";

        switch ($transitionName) {
            //initial stages
            case "to_draft":
                $label = "Save Project as Draft";
                break;
            case "to_complete":
                $label = "Complete Submission";
                break;
            case "to_review":
                $label = "Submit to IRB Review";
                break;
            //final stages
            case "approved_closed":
                $label = "Close Project";
                break;
            case "closed_approved":
                $label = "Re-Open previously Final Approved Project";
                break;

//            ///// Main Actions /////
//            //IRB Review
//            case "to_irb_review":
//                $label = "Submit to IRB Review";
//                break;
//            case "irb_review_rejected":
//                $label = "Reject IRB Review";
//                break;
//            //ADMIN Review
//            case "to_admin_review":
//                //$label = "Submit to Admin Review";
//                $label = "Approve IRB Review";
//                break;
//            case "admin_review_no":
//                $label = "Reject Admin Review";
//                break;
//            //COMMITTEE Review
//            case "to_committee_review":
//                //$label = "Submit to Committee Review";
//                $label = "Approve Admin Review";
//                break;
//            case "committee_review_no":
//                $label = "Reject Committee Review";
//                break;
//            //FINAL approval
//            case "to_final_review":
//                //$label = "Submit to Final Approval";
//                $label = "Approve Committee Review";
//                break;
//            case "final_review_yes":
//                $label = "Final Approve";
//                break;
//            case "final_review_no":
//                $label = "Reject Final Approval";
//                break;
//
//            case "approved_closed":
//                $label = "Close Approved Project";
//                break;
//            case "closed_approved":
//                $label = "Re-Open Approved Project";
//                break;

            default:
                $label = null;
        }

        if( $label ) {
            $returnLabel = $label;
        } else {
            //irb_review_approved => IRB Review Approved
            //irb_review_rejected => IRB Review Rejected
            //irb_review_missinginfo => IRB Review Missinginfo
            //irb_review_resubmit => IRB Review Resubmit
            $label = str_replace("_"," ",$transitionName);
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
            case "complete":
                $state = "Completed";
                break;

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
            case "complete":
                $state = "Completed";
                break;

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
        switch( $state ) {
            case "irb_review":
                $reviewEntityName = "IrbReview";
                break;
            case "admin_review":
                $reviewEntityName = "AdminReview";
                break;
            case "committee_review":
                $reviewEntityName = "CommitteeReview";
                break;
            case "final_review":
                $reviewEntityName = "FinalReview";
                break;
            default:
                $reviewEntityName = null;
        }
        return $reviewEntityName;
    }

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
    public function findReviewObjectsByProjectAndAnyReviewers( $reviewObjectClassName, $project, $reviewer=null ) {
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

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $reviewObjects = $query->getResult();

        return $reviewObjects;
    }

    //add user's validation (rely on Role): $from=irb_review => user has role _IRB_REVIEW_
    public function isUserAllowedFromThisState($from) {

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

    public function isReviewable($review) {
        if( !$review ) {
            return false;
        }
        $project = $review->getProject();
        if( !$project ) {
            return false;
        }

        //1) check if project state is reviewable
        $projectStateRevaiewable = false;
        $projectState = $project->getState();
        //echo "projectId=".$project->getId()."<br>";
        //echo "projectState=".$projectState."<br>";
        if( $projectState == "irb_review" ) {
            $projectStateRevaiewable = true;
        }
        if( $projectState == "admin_review" ) {
            $projectStateRevaiewable = true;
        }
        if( $projectState == "committee_review" ) {
            $projectStateRevaiewable = true;
        }
        if( $projectState == "final_review" ) {
            $projectStateRevaiewable = true;
        }
        if( $projectStateRevaiewable === false ) {
            return false;
        }

        //2) condition to allow edit only if project state is allow to edit this type of review (committee_review)
        if( $projectState == $review->getStateStr() ) {
            return true;
        }

        return false;
    }

    public function processProjectOnReviewUpdate( $review, $stateStr, $request, $testing=false ) {

        $project = $review->getProject();
        if( !$project ) {
            throw new \Exception("Review with ID ".$review->getId()." does not have a project");
            //return null;
        }

        $user = $this->secTokenStorage->getToken()->getUser();
        echo "user=".$user."<br>";

        //$currentState = $project->getState();

        //set project next transit state depends on the decision
        $appliedTransition = $this->setProjectState($project,$review,$testing);

        //send notification emails
        $this->sendNotificationEmails($project,$review,$appliedTransition,$testing);

//        $workflow = $this->container->get('state_machine.transres_project');
//        $transitions = $workflow->getEnabledTransitions($project);
//        $transitionArr = array();
//        foreach ($transitions as $transition) {
//            echo "transition=" . $this->printTransition($transition) . "<br>";
//            $transitionArr[] = $this->printTransition($transition);
//        }
//        $projectTransition = "Project transition " . implode(";",$transitionArr);

        //Event Log
        if( $appliedTransition ) {
            $eventType = "Review Submitted";
            $event = "Project's (ID# " . $project->getId() . ") review has been successfully submitted. ".$review->getSubmittedReviewerInfo();

            //testing
            echo "appliedTransition=" . $appliedTransition . "<br>";
            //echo "printTransition=".$this->printTransition($appliedTransition)."<br>";

            $event .= ";<br> Project transitioned from '" . $this->getStateLabelByName($stateStr) . "'".
                " to '" . $this->getStateLabelByName($project->getState()) . "'";
            echo "event=".$event."<br>";

            //exit('1');

        } else {
            $eventType = "Review Submitting Not Performed";
            $event = "Project's (ID# " . $project->getId() . ") review submitting not performed. " . $review->getSubmittedReviewerInfo();
            $event .= ";<br> Project transitioned from '" . $this->getStateLabelByName($stateStr) . "'" .
                " to '" . $this->getStateLabelByName($project->getState()) . "'";
            echo "event=".$event."<br>";

            //exit('2');
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'),$event,$user,$review,$request,$eventType);

    }
    public function setProjectState( $project, $review, $testing=false ) {

        $appliedTransition = null;

        echo "decision=".$review->getDecision()."<br>";
        if( $review->getDecision() == null ) {
            return $appliedTransition;
        }

//        if( $review->getDecision() == "Like" || $review->getDecision() == "Dislike" ) {
//            return $stateChanged;
//        }
        //for not primary Committee review don't chnage the project state.
        if( is_a($review,"CommitteeReview") ) {
            if( $review->getPrimaryReview() !== true ) {
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

        foreach($transitions as $transition) {
            $transitionName = $transition->getName();
            echo "transitionName=".$transitionName."<br>"; //"irb_review_no" or "to_admin_review"

            if( strpos($transitionName, '_review_no') !== false ) {
                echo "to: No<br>";
                $transitionNameNo = $transitionName;
                $tos = $transition->getTos();
                if( count($tos) > 1 ) {
                    throw new \Exception("State machine must have only one to state. To count=".count($tos));
                }
                $toNo = $tos[0];
            } else {
                if (strpos($transitionName, 'to_') !== false ) {
                    echo "to: Yes<br>";
                    $transitionNameYes = $transitionName;
                    $tos = $transition->getTos();
                    if (count($tos) > 1) {
                        throw new \Exception("State machine must have only one to state. To count=" . count($tos));
                    }
                    $toYes = $tos[0];
                }
            }

        }//foreach

        if( $review->getDecision() == "Rejected" && $toNo ) {
            echo "transit project to No: $toNo <br>";
            //$project->setState($toNo);
            $transitionNameFinal = $transitionNameNo;
            //$appliedTransition = true;
        }

        if( $review->getDecision() == "Approved" && $toYes ) {
            echo "transit project to Yes: $toYes <br>";
            //$project->setState($toYes);
            $transitionNameFinal = $transitionNameYes;
            //$appliedTransition = true;
        }

        if( $transitionNameFinal ) {
            try {
                echo "try apply transition=$transitionNameFinal <br>";
                $workflow->apply($project, $transitionNameFinal);
                $appliedTransition = $transitionNameFinal;

                //write to DB
                $this->em->flush($project);

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

    public function sendNotificationEmails($project,$review,$appliedTransition,$testing=false) {
        if( !$appliedTransition ) {
            return null;
        }

        $emailUtil = $this->container->get('user_mailer_utility');

        $senderEmail = null; //Admin email
        $emails = array();
        $subject = "Project ID#".$project->getId()." sent to $appliedTransition";
        $body = "Project ID#".$project->getId()." sent to $appliedTransition";


        //send to the
        // 1) admins and primary reviewers
        $admins = $this->getTransResAdminEmails();
        // 2) project's Requester (submitter, principalInvestigators, coInvestigators, pathologists)
        $requesterEmails = $this->getRequesterEmails($project,$review,$appliedTransition);
        // 3) current project's reviewers
        $currentReviewerEmails = $this->getCurrentReviewersEmails($project,$review,$appliedTransition);
        // 4) next state project's reviewers
        $nextStateReviewerEmails = $this->getNextStateReviewersEmails($project,$review,$appliedTransition);


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
    public function getRequesterEmails($project, $review, $appliedTransition, $asEmail=true) {
        $users = array();
        //1 submitter
        if( $project->getSubmitter() ) {
            if( $asEmail ) {
                $users[] = $project->getSubmitter()->getSingleEmail();
            } else {
                $users[] = $project->getSubmitter();
            }
        }

        //2 principalInvestigators
        $pis = $project->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi ) {
                if( $asEmail ) {
                    $users[] = $pi->getSingleEmail();
                } else {
                    $users[] = $pi;
                }
            }
        }

        //3 coInvestigators
        $cois = $project->getCoInvestigators();
        foreach( $cois as $coi ) {
            if( $coi ) {
                if( $asEmail ) {
                    $users[] = $coi->getSingleEmail();
                } else {
                    $users[] = $coi;
                }
            }
        }

        //4 pathologists
        $pathologists = $project->getPathologists();
        foreach( $pathologists as $pathologist ) {
            if( $pathologist ) {
                if( $asEmail ) {
                    $users[] = $pathologist->getSingleEmail();
                } else {
                    $users[] = $pathologist;
                }
            }
        }

        return $users;
    }

    //current project's reviewers
    public function getCurrentReviewersEmails($project, $review, $appliedTransition, $asEmail=true) {
        $users = array();

        //get all same reviews and reviewers

        return $users;
    }

    //next state project's reviewers
    public function getNextStateReviewersEmails($project, $review, $appliedTransition, $asEmail=true) {
        $users = array();

        return $users;
    }

}