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

namespace App\TranslationalResearchBundle\Util;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use App\TranslationalResearchBundle\Entity\AdminReview;
use App\TranslationalResearchBundle\Entity\CommitteeReview;
use App\TranslationalResearchBundle\Entity\FinalReview;
use App\TranslationalResearchBundle\Entity\IrbReview;
use App\TranslationalResearchBundle\Entity\SpecialtyList;
use App\TranslationalResearchBundle\Entity\TransResSiteParameters;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
//use Zend\Cache\StorageFactory;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

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

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        //$this->secToken = $container->get('security.token_storage')->getToken(); //$user = $this->secToken->getUser();
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
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
        if( $this->isAdminOrPrimaryReviewer($project) === false ) {
            if( $this->isReviewsReviewer($user, array($review)) === false && $this->isProjectRequester($project) === false ) {
                //exit("return: is not reviewer or requester");
                return $links;
            }
        }

        foreach( $transitions as $transition ) {

            //$this->printTransition($transition);
            $transitionName = $transition->getName();
            //echo "transitionName=".$transitionName."<br>";

            if( $review->getStateStr() === "committee_review" ) {
                if( strpos($transitionName, "missinginfo") !== false ) {
                    continue;
                }

                //don't show "Recommend..." buttons to primary reviewer for committee_review stage
//                $finalReviewer = $this->isProjectStateReviewer($project,$user,"final_review",true);
//                if( $review->getPrimaryReview() === false && $finalReviewer ) {
//                    //echo "Skip 1<br>";
//                    continue;
//                }
                //don't show "Recommend..." buttons to primary reviewer for committee_review stage
                //$finalReviewer = $this->isProjectStateReviewer($project,$user,"final_review",true);
//                if( $review->getPrimaryReview() === false ) {
//                    continue;
//                }

                //show "Provide Final Approval" only if user is primary committee reviewer and final reviewer for this project
                if( $transitionName == "committee_finalreview_approved" ) {
                    //There should be only one Orange "Provide Final Approval" button for primary reviewer
                    if( method_exists($review, 'getPrimaryReview') ) {
                        if( $review->getPrimaryReview() === false ) {
                            //echo "Skip getPrimaryReview<br>";
                            continue;
                        }
                    }

                    //show "Provide Final Approval" only if user is primary committee reviewer and final reviewer for this project
                    $committeReviewer = $this->isProjectStateReviewer($project,$user,"committee_review",true);
                    $finalReviewer = $this->isProjectStateReviewer($project,$user,"final_review",true);
                    if( $committeReviewer && $finalReviewer ) {
                        //show link to committee_finalreview_approved
                    } else {
                        //echo "not primary or committee reviewer <br>";
                        if( !$this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ) {
                            continue;
                        }
                    }
//                    $committeReviewer = $this->isProjectStateReviewer($project,$user,"committee_review",true);
//                    if( $committeReviewer ) {
//                        //show link to committee_finalreview_approved
//                    } else {
//                        //echo "not primary or committee reviewer <br>";
//                        if( !$this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ) {
//                            continue;
//                        }
//                    }

                }
            }

            if( false === $this->isUserAllowedFromThisStateByProjectAndReview($project,$review) ) {
                //echo "Skip isUserAllowedFromThisStateByProjectAndReview<br>";
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
                $label = $this->getTransitionLabelByName($transitionName,$review); //main

                $classTransition = $this->getHtmlClassTransition($transitionName);

                $generalDataConfirmation = "general-data-confirm='Are you sure you want to $label?'";

                //add class to distinguish irb_review update IRB expiration date
                if( $review->getStateStr() == "irb_review" ) {
                    $classTransition = $classTransition . " transres-irb_review";
                    //$generalDataConfirmation = "";
                }

                //don't show confirmation modal for missinginfo, because missinginfo has JS alert for empty comment
                //if( strpos($transitionName, "missinginfo") !== false ) {
                    //$generalDataConfirmation = "";
                //}

                $callBackFunction = ' general-post-process="transresUpdateProjectSpecificBtn" ';

                $thisLink = "<a ".
                    $generalDataConfirmation.
                    $callBackFunction.
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
                $label = $this->getTransitionLabelByName($transitionName); //not used

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
//    public function isExceptionTransition( $transitionName ) {
//        if( $transitionName == "committee_review_missinginfo" ) {
//            return true;
//        }
//        if( $transitionName == "final_review_missinginfo" ) {
//            return true;
//        }
//        return false;
//    }

    //NOT USED
    //get Review links for this user: irb_review => "IRB Review" or "IRB Review as Admin"
    //project/review/2/6 - project request 2, review ID 6
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

    public function getProjectReviewers( $project, $state=null, $asEmails=false ) {
        //get project reviews for appropriate state (i.e. irb_review)
        $reviewers = array();

        if( !$state ) {
            $state = $project->getState();
        }

        $reviews = array();

        if( $state == "irb_review" ) {
            $reviews = $project->getIrbReviews();
        }
        if( $state == "admin_review" ) {
            $reviews = $project->getAdminReviews();
        }
        if( $state == "committee_review" ) {
            $reviews = $project->getCommitteeReviews();
        }
        if( $state == "final_review" ) {
            $reviews = $project->getFinalReviews();
        }

        foreach($reviews as $review) {
            $reviewer = $review->getReviewer();
            if( $reviewer ) {
                if( $asEmails ) {
                    $reviewers[] = $reviewer->getSingleEmail(false);
                } else {
                    $reviewers[] = $reviewer;
                }
            }

            $reviewerDelegate = $review->getReviewerDelegate();
            if( $reviewerDelegate ) {
                if( $asEmails ) {
                    $reviewers[] = $reviewerDelegate->getSingleEmail(false);
                } else {
                    $reviewers[] = $reviewerDelegate;
                }
            }

        }

        return $reviewers;
    }

    public function isProjectEditableByRequester( $project, $checkProjectSpecialty=true ) {
//        if( $checkProjectSpecialty ) {
//            if ($project && $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false) {
//                return false;
//            }
//        }

        $state = $project->getState();
        if( strpos($state, '_rejected') !== false || $state == 'draft' ) { //|| strpos($state, "_missinginfo") !== false
            if( $this->isProjectRequester($project,$checkProjectSpecialty) === true ) {
                return true;
            }
        }
        if( $this->isProjectStateRequesterResubmit($project,$checkProjectSpecialty) === true ) {
            return true;
        }
        return false;
    }
    public function isProjectRequester( $project, $checkProjectSpecialty=true ) {
//        if( $checkProjectSpecialty ) {
//            if ($project && $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false) {
//                return false;
//            }
//        }

        $user = $this->secTokenStorage->getToken()->getUser();

        if( $project->getSubmitter() && $project->getSubmitter()->getId() == $user->getId() ) {
            return true;
        }
        if( $project->getPrincipalInvestigators()->contains($user) ) {
            return true;
        }
        if( $project->getPrincipalIrbInvestigators()->contains($user) ) {
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
//        if( $project->getBillingContact() ) {
//            if( $project->getBillingContact()->getId() == $user->getId() ) {
//                return true;
//            }
//        }
        return false;
    }
    public function isRequesterOrAdmin( $project ) {
//        if( $project && $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//            return false;
//        }
        if( $this->isProjectRequester($project) === true ) {
            return true;
        }
        if( $this->isAdminOrPrimaryReviewer($project) === true ) {
            return true;
        }

        return false;
    }
    public function isAdminReviewer( $project=null, $strictReviewer=false ) { //$projectSpecialty=null
        if( $strictReviewer ) {
            //$strictReviewer - check if user is an admin reviewer of this particular project
            if( $project ) {
                $user = $this->secTokenStorage->getToken()->getUser();
                if( $this->isReviewsReviewer($user, $project->getAdminReviews()) ) {
                    return true;
                }
            }
        } else {
            $projectSpecialty = null;
            $specialtyStr = null;

            if( $project ) {
                $projectSpecialty = $project->getProjectSpecialty();
            }

            if( $projectSpecialty ) {
                $specialtyStr = $projectSpecialty->getUppercaseName();
                $specialtyStr = "_" . $specialtyStr;
            }

            if( $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr) ) {
                return true;
            }
        }

        return false;
    }
    public function isAdminOrPrimaryReviewer( $project=null ) { //$projectSpecialty=null
        $projectSpecialty = null;
        $specialtyStr = null;
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
        }
        if( $projectSpecialty ) {
            $specialtyStr = $projectSpecialty->getUppercaseName();
            $specialtyStr = "_" . $specialtyStr;
        }
        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr)
            //$this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr)
        ) {
            return true;
        }

        //check if user is a primary reviewer of this particular project
        if( $project ) {
            $user = $this->secTokenStorage->getToken()->getUser();
            if( $this->isReviewsReviewer($user, $project->getFinalReviews()) ) {
                return true;
            }
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
    public function isProjectReviewer( $project, $checkProjectSpecialty=true ) {

        if( !$project ) {
            return false;
        }

//        if( $checkProjectSpecialty ) {
//            if( $project && $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//                return false;
//            }
//        }

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
    public function isAdminOrPrimaryReviewerOrExecutive( $project=null ) {
        //check only if user is admin, executive for the project specialty
        //or user is a primary (final) reviewer of this particular project

        $projectSpecialty = null;
        $specialtyStr = null;
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
        }
        if( $projectSpecialty ) {
            $specialtyStr = $projectSpecialty->getUppercaseName();
            $specialtyStr = "_" . $specialtyStr;
        }

        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr)
        ) {
            return true;
        }

        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr)
        ) {
            return true;
        }

        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE'.$specialtyStr)
        ) {
            return true;
        }

        return false;
    }
    public function isAdminOrPrimaryReviewerOrExecutive_ORIG( $project=null ) {
        //TODO: implement check only if user is admin, executive for the project specialty
        //TODO: or user is a primary (final) reviewer of this particular project
        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN_APCP') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN_HEMATOPATHOLOGY') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN_COVID19') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN_MISI') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_APCP') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_HEMATOPATHOLOGY') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_COVID19') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_MISI') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE_COVID19') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE_MISI')
        ) {
            return true;
        }
        return false;
    }

    //check if user:
    //listed as PIs or Billing contacts or
    //Site Admin/Executive Committee/Platform Admin/Deputy Platform Admin) and
    //ONLY for projects with status = Final Approved or Closed
    public function isAdminPiBillingAndApprovedClosed( $project ) {

        //hide the remaining budget for non-funded
        if( $project->getFunded() ) {
            return false;
        }

        //hide the remaining budget for non-funded, closed projects
        //ONLY for projects with status = Final Approved or Closed
        if(
            $project->getState() == "final_approved"
            //|| $project->getState() == "closed"
        ) {
            //Continue: show remaining budget only for "final_approved" projects
        } else {
            //hide the remaining budget for all not "final_approved" projects
            return false;
        }

        //Site Admin/Executive Committee/Platform Admin/Deputy Platform Admin)
        //TRP Admin/Executive Committee/Deputy/Platform Admin should always see it regardless of the project status
        if( $this->isAdminOrPrimaryReviewerOrExecutive($project) ) {
            return true;
        }

        //only for users listed as PIs or Billing contacts or
        $user = $this->secTokenStorage->getToken()->getUser();

        if( $project->getPrincipalInvestigators()->contains($user) ) {
            return true;
        }
        if( $project->getBillingContacts()->contains($user) ) {
            return true;
        }

        return false;
    }

    public function showRemainingBudgetForProjects( $projects ) {
        //echo "projects=".count($projects)."<br>";
        $showRemainingBudget = false;
        foreach( $projects as $project ) {
            if( $this->isAdminPiBillingAndApprovedClosed($project) ) {
                $showRemainingBudget = true;
                break;
            }
        }
        return $showRemainingBudget;
    }

    public function getProjectRemainingBudgetNote($project) {

        //return NULL; //testing
        if( !$project ) {
            return NULL;
        }

        if( $this->isAdminPiBillingAndApprovedClosed($project) ) {
            //echo "show remaining budget <br>";
            $remainingBudget = $project->getRemainingBudget();

            if( $remainingBudget !== NULL ) {
                //Based on the estimated total costs & the approved budget for the selected project, the remaining budget is $[xxx.xx].
                // If you have questions about this, please [email the system administrator]
                $remainingBudget = $project->toMoney($remainingBudget);

                $remainingBudget = $this->dollarSignValue($remainingBudget);

//                $adminEmailsStr = "";
//                $adminEmails = $this->getTransResAdminEmails($project->getProjectSpecialty(), true, true);
//                if (count($adminEmails) > 0) {
//                    $adminEmailsStr = implode(", ", $adminEmails);
//                }
                $adminEmailsStr = $this->getAdminEmailsStr($project,false);

                $trpName = $this->getBusinessEntityAbbreviation();

//                $note = "Based on the estimated total costs & the approved budget for the selected project, the remaining budget is" .
//                    " " .
//                    "<span id='project-remaining-budget-amount'>".$remainingBudget."</span>".
//                    "." .
//                    "<br>If you have questions about this, please email the $trpName administrator " . $adminEmailsStr;

                //Based on this project’s approved budget, invoices, work requests, and the items selected below,
                // the remaining budget appears to be $20.00.
                // If you have any questions, please email the CTP administrator Name (email)

                $note = "Based on this project’s approved budget, invoices, work requests, ".
                        "and the items selected below, the remaining budget appears to be ".
                        "<span id='project-remaining-budget-amount'>".$remainingBudget."</span>".
                        "." .
                        "<br>If you have any questions, please email the $trpName administrator ".$adminEmailsStr;

                $note = "<h4>" . $note . "</h4>";

                return $note;
            }
        }
        return NULL;
    }
    public function getAdminEmailsStr($project=NULL,$all=true) {

        $projectSpecialty = NULL;
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
        }
        
        $adminEmailsStr = "";
        $adminUsers = $this->getTransResAdminEmails($projectSpecialty, false, true);
        $adminEmails = array();
        foreach($adminUsers as $adminUser) {
            $adminEmails[] = $adminUser->getUsernameOptimal()." (".$adminUser->getSingleEmail(false).")";
            if( !$all ) {
                break;
            }
        }
        if (count($adminEmails) > 0) {
            $adminEmailsStr = implode(", ", $adminEmails);
        }
        return $adminEmailsStr;
    }

    public function sendProjectOverBudgetEmail($transresRequest) {

        //return NULL; //testing

        if( !$transresRequest ) {
            return NULL;
        }

        $project = $transresRequest->getProject();
        if( !$project ) {
            return NULL;
        }

        //overBudgetSendEmail
        $transresUtil = $this->container->get('transres_util');
        $overBudgetSendEmail = $transresUtil->getTransresSiteProjectParameter('overBudgetSendEmail',$project);
        if( $overBudgetSendEmail === TRUE ) {
            //OK: send email
        } else {
            return NULL;
        }

        //send email only if work request state is active
        if( $transresRequest->getProgressState() == 'active' && $transresRequest->getBillingState() == 'active' ) {
            //send over budget email notification
        } else {
            //skip for non-active work request - don't send email
            return NULL;
        }

        $emailUtil = $this->container->get('user_mailer_utility');
        //$transresUtil = $this->container->get('transres_util');
        //$userServiceUtil = $this->container->get('user_service_utility');
        //$user = $this->secTokenStorage->getToken()->getUser();

        $newline = "\r\n";

        $res = NULL;

        //$approvedProjectBudget = $project->getApprovedProjectBudget();
        $remainingBudget = $project->getRemainingBudget();

//        $currentDate = new \DateTime();
//        $dateTimeUser = $userServiceUtil->convertFromUtcToUserTimezone($currentDate,$user);
//        $dateTimeUserStr = $dateTimeUser->format('m-d-Y \a\t H-i-s');

        if( $remainingBudget < 0 ) {

            //$workRequestBudget = "";

            //$invoicesInfos = $project->getInvoicesInfosByProject(true);
            //$subsidy = $invoicesInfos['subsidy'];

            //$subsidy = $this->dollarSignValue($subsidy);
            //$approvedProjectBudget = $this->dollarSignValue($approvedProjectBudget);
            //$remainingBudget = $this->dollarSignValue($remainingBudget);

            $senderEmail = $transresUtil->getTransresSiteProjectParameter('overBudgetFromEmail',$project);
            if( !$senderEmail ) {
                $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
            }

            $adminEmails = $this->getTransResAdminEmails($project->getProjectSpecialty(), true, true);

            //$subject = ""; //222(10) Over budget notification subject:
            $subject = $transresUtil->getTransresSiteProjectParameter('overBudgetSubject',$project);
            if( !$subject ) {
                //$subject = "Budget potentially exceeded for ".$project->getOid()." by work request ".$transresRequest->getOid();
                $subject = "Budget potentially exceeded for [[PROJECT ID]] by work request [[REQUEST ID]]";
            }
            $subject = $transresUtil->replaceTextByNamingConvention($subject,$project,$transresRequest,null);

            //$emailBody = ""; //222(10) Over budget notification body:
            $emailBody = $transresUtil->getTransresSiteProjectParameter('overBudgetBody',$project);
            if( !$emailBody ) {
//                $emailBody = "According to the '[[PROJECT PRICE LIST]]' price list,
//                    the expected value of $workRequestBudget for the work request newly submitted by $user".
//                    " on " . $dateTimeUserStr.
//                    " exceeds by $remainingBudget the approved budget of $approvedProjectBudget
//                    for the project ".$project->getOid(). " '".$project->getTitle()."' with a total current subsidy of $subsidy.";

                $emailBody = "According to the [[PROJECT PRICE LIST]] price list,".
                    " the expected value of [[REQUEST VALUE]] for the work request newly submitted by [[REQUEST SUBMITTER]]".
                    " on [[REQUEST SUBMISSION DATE]]".
                    " exceeds by [[PROJECT OVER BUDGET]] the approved budget of [[PROJECT APPROVED BUDGET]]".
                    " for the project [[PROJECT ID]] '[[PROJECT TITLE]]'".
                    " with a total current subsidy of [[PROJECT SUBSIDY]].";

                $emailBody = $emailBody . $newline.$newline.
                    "This project is [[PROJECT FUNDED]] and has [[PROJECT NUMBER INVOICES]] invoice(s),".
                    " [[PROJECT NUMBER PAID INVOICES]] of them paid, for a total amount of [[PROJECT AMOUNT PAID INVOICES]] collected,".
                    " [[PROJECT AMOUNT OUTSTANDING INVOICES]] in total for outstanding invoices, and [[PROJECT VALUE WITHOUT INVOICES]] in value for work requests without invoices."
                ;

                $emailBody = $emailBody . $newline.$newline.
                    "The total value of all work requests (invoiced or not) is [[PROJECT VALUE]]."
                ;

                $emailBody = $emailBody . $newline.$newline.
                    "The project PI list includes [[PROJECT PIS]].".$newline.
                    "The pathologist list includes [[PROJECT PATHOLOGIST LIST]]."
                ;

                $emailBody = $emailBody . $newline.$newline.
                    "The Billing Contact is [[PROJECT BILLING CONTACT LIST]]."
                ;

                $emailBody = $emailBody . $newline.$newline.
                    "Please obtain the updated estimated budget from the submitter and, if approved,".
                    " update the project request on the following page with the new value to avoid these notifications:".
                    $newline . "[[PROJECT EDIT URL]]"
                ;

                $emailBody = $emailBody . $newline.$newline.
                    "To review the work request, please follow this link:".
                    $newline."[[REQUEST SHOW URL]]"
                ;

                $emailBody = $emailBody . $newline.$newline.
                    "To review all work requests for this project, please follow the link below:".
                    $newline."[[PROJECT REQUESTS URL]]"
                ;

                $emailBody = $emailBody . $newline.$newline.
                    "To review all invoices for this project, please follow the link below:".
                    $newline."[[PROJECT NON-CANCELED INVOICES URL]]" //[Link to list of all latest versions of non-canceled invoices for this project]
                ;

//                $emailBody = $emailBody . $newline.$newline.
//                    ""
//                ;
            }
            //exit($emailBody);
            $emailBody = $transresUtil->replaceTextByNamingConvention($emailBody,$project,$transresRequest,null);

            //                     $emails,      $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $adminEmails, $subject, $emailBody, null, $senderEmail );

            $res = "Over-budget notification email sent with Subject:<br>$subject <br>Body:<br>$emailBody";
        }

        //eventlog
        $eventType = "Project Over Budget Email Sent";
        $transresUtil->setEventLog($project,$eventType,$res);

        return $res;
    }

    //Send ‘approved project budget’ update notifications for non-funded projects
    public function sendProjectApprovedBudgetUpdateEmail($project,$originalApprovedProjectBudget,$approvedProjectBudget=NULL) {
        if( $this->toSendUpdateBudgetNotificationEmail($project) === false ) {
            //don't send email
            return NULL;
        }

        if( !$approvedProjectBudget ) {
            $approvedProjectBudget = $project->getApprovedProjectBudget();
        }

        if( $originalApprovedProjectBudget == $approvedProjectBudget ) {
            return NULL;
        }

        ///////////////////////// Send Email ApprovedBudget /////////////////////////
        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');

        $senderEmail = $transresUtil->getTransresSiteProjectParameter('approvedBudgetFromEmail',$project);
        if( !$senderEmail ) {
            $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
        }

        $adminEmails = $this->getTransResAdminEmails($project->getProjectSpecialty(), true, true);

        $originalApprovedProjectBudget = $this->dollarSignValue($originalApprovedProjectBudget);
        $approvedProjectBudget = $this->dollarSignValue($approvedProjectBudget);

        $approvedBudgetSubject = $transresUtil->getTransresSiteProjectParameter('approvedBudgetSubject',$project);
        if( !$approvedBudgetSubject ) {
            //Approved budget amount updated by [FirstName LastName] for project [ProjectID] from $xxx.xx to $xxx.xx
            //SiteSettings approvedBudgetSubject:
            //Approved budget amount updated by [[PROJECT UPDATER]] for project [[PROJECT ID]] to [[PROJECT APPROVED BUDGET]]
            $approvedBudgetSubject = "Approved budget amount updated by [[PROJECT UPDATER]] for project [[PROJECT ID]] ".
                "from $originalApprovedProjectBudget to $approvedProjectBudget";
        }
        $approvedBudgetSubject = $transresUtil->replaceTextByNamingConvention($approvedBudgetSubject,$project,null,null);

        $approvedBudgetBody = $transresUtil->getTransresSiteProjectParameter('approvedBudgetBody',$project);
        if( !$approvedBudgetBody ) {
            //Approved budget amount updated for project [ProjectID] from $xxx.xx to $xxx.xx by [FirstName LastName] on MM/DD/YYYY at HH:MM.
            //SiteSettings approvedBudgetBody:
            //Approved budget amount updated for project [[PROJECT ID]] to [[PROJECT APPROVED BUDGET]] by [[PROJECT UPDATER]] on [[PROJECT UPDATE DATE]].
            $approvedBudgetBody = "Approved budget amount updated for project [[PROJECT ID]] ".
                "from $originalApprovedProjectBudget to $approvedProjectBudget by [[PROJECT UPDATER]] on [[PROJECT UPDATE DATE]]";
        }
        $approvedBudgetBody = $transresUtil->replaceTextByNamingConvention($approvedBudgetBody,$project,null,null);

        //                     $emails,      $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $adminEmails, $approvedBudgetSubject, $approvedBudgetBody, null, $senderEmail );
        ///////////////////////// EOF Send Email ApprovedBudget /////////////////////////

        $res = "Approved Budget Update notification email sent with Subject:<br>$approvedBudgetSubject <br>Body:<br>$approvedBudgetBody";

        //eventlog
        $eventType = "Project Approved Budget Updated";
        $transresUtil->setEventLog($project,$eventType,$res);

        return $res;
    }
    //Send ‘approved project budget’ update notifications for non-funded projects
    public function sendProjectNoBudgetUpdateEmail($project,$originalNoBudgetLimit,$noBudgetLimit=NULL) {
        //send email only if project state is not 'start', 'draft', 'closed', 'canceled', '*_rejected'
        if( $this->toSendUpdateBudgetNotificationEmail($project) === false ) {
            //don't send email
            return NULL;
        }

        if( !$noBudgetLimit ) {
            $noBudgetLimit = $project->getNoBudgetLimit();
        }

        //Approved budget limit removed: NoBudgetLimit changed from FALSE to TRUE
        if( $originalNoBudgetLimit === FALSE && $noBudgetLimit === TRUE ) {
            //Send email
        } else {
            return NULL;
        }

        ///////////////////////// Send Email NoBudget /////////////////////////
        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');

        $senderEmail = $transresUtil->getTransresSiteProjectParameter('approvedBudgetFromEmail',$project);
        if( !$senderEmail ) {
            $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
        }

        $adminEmails = $this->getTransResAdminEmails($project->getProjectSpecialty(), true, true);

        $budgetLimitRemovalSubject = $transresUtil->getTransresSiteProjectParameter('budgetLimitRemovalSubject',$project);
        if( !$budgetLimitRemovalSubject ) {
            //Approved budget limit removed by [FirstName LastName] for project [ProjectID] from $xxx.xx to $xxx.xx
            $budgetLimitRemovalSubject = "Approved budget limit removed by [[PROJECT UPDATER]] for project [[PROJECT ID]]";
        }
        $budgetLimitRemovalSubject = $transresUtil->replaceTextByNamingConvention($budgetLimitRemovalSubject,$project,null,null);

        //budgetLimitRemovalBody
        $budgetLimitRemovalBody = $transresUtil->getTransresSiteProjectParameter('budgetLimitRemovalBody',$project);
        if( !$budgetLimitRemovalBody ) {
            //Approved budget limit removed for project [ProjectID] by [FirstName LastName] on MM/DD/YYYY at HH:MM.
            $budgetLimitRemovalBody = "Approved budget limit removed for project [[PROJECT ID]] by [[PROJECT UPDATER]] on [[PROJECT UPDATE DATE]].";
        }
        $budgetLimitRemovalBody = $transresUtil->replaceTextByNamingConvention($budgetLimitRemovalBody,$project,null,null);

        //                     $emails,      $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $adminEmails, $budgetLimitRemovalSubject, $budgetLimitRemovalBody, null, $senderEmail );
        ///////////////////////// EOF Send Email NoBudget /////////////////////////

        $res = "Approved Budget Limit Removed notification email sent with Subject:<br>$budgetLimitRemovalSubject <br>Body:<br>$budgetLimitRemovalBody";

        //eventlog
        $eventType = "Project Approved Budget Limit Updated";
        $transresUtil->setEventLog($project,$eventType,$res);

        return $res;
    }
    public function toSendUpdateBudgetNotificationEmail($project) {
        if( !$project ) {
            return false;
        }

        //Send update notifications for non-funded projects
        if( $project->getFunded() ) {
            //exit('exit: funded');
            return false;
        }

        //approvedBudgetSendEmail
        $transresUtil = $this->container->get('transres_util');
        //                                              $fieldName, $project=null, $projectSpecialty=null, $useDefault=false, $testing=false
        //$approvedBudgetSendEmail = $transresUtil->getTransresSiteProjectParameter('approvedBudgetSendEmail',$project,null,false,true);
        $approvedBudgetSendEmail = $transresUtil->getTransresSiteProjectParameter('approvedBudgetSendEmail',$project);
        if( $approvedBudgetSendEmail === TRUE ) {
            //OK: send email
        } else {
            //exit('exit: approvedBudgetSendEmail FALSE='.$approvedBudgetSendEmail);
            return false;
        }

        //Send update notifications email only if project state is not 'start', 'draft', 'closed', 'canceled', '*_rejected'
        $projectState = $project->getState();
        if(
            $projectState == 'start' || $projectState == 'draft' ||
            $projectState == 'closed' || $projectState == 'canceled' ||
            strpos($projectState, '_rejected') !== false
        ) {
            //don't send email
            //exit('exit: getState='.$projectState);
            return false;
        } else {
            //send over budget email notification
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
        //echo $transitionName.": count=".count($transitions)."<br>";
        foreach( $transitions as $transition ) {
            //echo "transitionName:".$transition->getName()." == " . $transitionName;
            if( $transition->getName() == $transitionName ) {
                return $transition;
            }
        }
        return null;
    }

    //change transition (by the $transitionName) of the project
    public function setTransition( $project, $review, $transitionName, $to=null, $testing=false ) {

        if( !$review ) {
            throw new \Exception('Review object does not exist');
        }

        if( !$review->getId() ) {
            throw new \Exception('Review object ID is null');
        }

        //echo "transitionName=".$transitionName."<br>";
        $user = $this->secTokenStorage->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $workflow = $this->container->get('state_machine.transres_project');
        //$break = "\r\n";
        $break = "<br>";

        if( !$to ) {
            //Get Transition and $to
            $transition = $transresUtil->getTransitionByName($project, $transitionName);
            if( $transition ) {
                $tos = $transition->getTos();
                if (count($tos) != 1) {
                    throw new \Exception('Available to state is not a single state; count=' . $tos . ": " . implode(",", $tos));
                }
                $to = $tos[0];
            } else {
                $to = null;
                //exit("transition is null transitionName=".$transitionName);
            }
        }
        //echo "to=".$to."<br>";

        $originalStateStr = $project->getState();
        $originalStateLabel = $this->getStateLabelByName($originalStateStr);

        // Update the currentState on the post
        if( $workflow->can($project, $transitionName) ) {
            try {

                $review->setDecisionByTransitionName($transitionName);

                $review->setReviewedBy($user);

                //check if like/dislike
                if( $review->getStateStr() === "committee_review" ) {
                    if( $review->getPrimaryReview() !== true ) {

                        if( !$testing ) {
                            $this->em->flush($review);
                        }

                        $emailRes = array();
                        $emailUtil = $this->container->get('user_mailer_utility');
                        $projectReviewUrl = $this->getProjectReviewUrl($project);
                        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
                        $subject = "Project request ".$project->getOid(). " has been reviewed by a committee member";
                        $body = $subject . " who is recommending it to be " . $review->getDecisionStr();

                        /////////////////// Email to Admin ///////////////////////
                        //get project url
                        $projectUrl = $transresUtil->getProjectShowUrl($project);
                        $emailBody = $body . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;

                        //To review this project request, please visit the link below: LINK-TO-REVIEW-PROJECT-REQUEST
                        $emailBody = $emailBody .$break.$break. "To review this project request, please visit the link below:";
                        $emailBody = $emailBody . $break. $projectReviewUrl;

                        //send notification emails (project transition: committee recomendation - committe_review)
                        $admins = $this->getTransResAdminEmails($project->getProjectSpecialty(),true,true);
                        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
                        $emailUtil->sendEmail( $admins, $subject, $emailBody, null, $senderEmail );

                        if( $subject && $emailBody ) {
                            $emailResAdmin = "Email To: ".implode("; ",$admins);
                            $emailResAdmin = $emailResAdmin . $break . "Subject: " . $subject . "<br>" . "Body: " . $emailBody;
                            $emailResAdmin = str_replace($break, "<br>", $emailResAdmin);
                            $emailRes[] = $emailResAdmin;
                        }
                        /////////////////// EOF Email to Admin ///////////////////////

                        /////////////////// Email to Primary Reviewer, TO: PRIMARY COMMITTEE REVIEWER ONLY ///////////////////////
                        $emailBody = $body .$break.$break. "At the time of this notification, the status of this project request is '$originalStateLabel'.";

                        //To review this project request, please visit the link below: LINK-TO-REVIEW-PROJECT-REQUEST
                        $emailBody = $emailBody .$break.$break. "To review this project request, please visit the link below:";
                        $emailBody = $emailBody . $break. $projectReviewUrl;

                        //send notification emails (project transition: committee recomendation - committe_review)
                        $primaryReviewerEmails = $this->getCommiteePrimaryReviewerEmails($project); //ok
                        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
                        $emailUtil->sendEmail( $primaryReviewerEmails, $subject, $emailBody, null, $senderEmail );

                        if( $subject && $emailBody ) {
                            $emailResPrimary = "Email To: ".implode("; ",$admins);
                            $emailResPrimary = $emailResPrimary . $break . "Subject: " . $subject . "<br>" . "Body: " . $emailBody;
                            $emailResPrimary = str_replace($break, "<br>", $emailResPrimary);
                            $emailRes[] = $emailResPrimary;
                        }
                        /////////////////// EOF Email to Primary Reviewer, PRIMARY COMMITTEE REVIEWER ONLY ///////////////////////

                        //event log
                        $emailBody = implode("<br><br>",$emailRes);
                        $eventType = "Review Submitted";
                        $this->setEventLog($project,$eventType,$emailBody,$testing);

                        $this->container->get('session')->getFlashBag()->add(
                            'notice',
                            $subject
                        );

                        return true;
                    }
                }

                if( $to === "final_approved" ) {
                    $project->setApprovalDate(new \DateTime());
                }

                $workflow->apply($project, $transitionName);
                //change state
                $project->setState($to); //i.e. 'irb_review'

                $eventResetMsg = null;

                //write to DB
                if( !$testing ) {
                    $this->em->flush();
                }

                //Send transition emails
                $resultMsg = $this->sendTransitionEmail($project,$originalStateStr,$testing);

                //event log
                $eventType = "Review Submitted";
                $this->setEventLog($project,$eventType,$resultMsg,$testing);

                $this->container->get('session')->getFlashBag()->add(
                    'notice',
                    $this->getNotificationMsgByStates($originalStateStr,$to,$project)    //"Successful action: ".$label
                );
                return true;
            } catch (\LogicException $e) {
                //event log

                $logger = $this->container->get('logger');
                $logger->error("Action failed: ".$this->getTransitionLabelByName($transitionName).", Error:".$e);

                $this->container->get('session')->getFlashBag()->add(
                    'warning',
                    "Action failed: ".$this->getTransitionLabelByName($transitionName)."<br> Error:".$e
                );
                return false;
            }//try
        }
    }

    //add reviewers according to their roles and state
    //for example, state=irb_review => roles=ROLE_TRANSRES_IRB_REVIEWER, ROLE_TRANSRES_IRB_REVIEWER_DELEGATE
    public function addDefaultStateReviewers( $project, $addForAllStates=true ) {

        $currentState = $project->getState();
        //echo "project state=".$currentState."<br>";

        $irbReviewState = "irb_review";
        if( $currentState == $irbReviewState || $addForAllStates === true || $addForAllStates === $irbReviewState ) {
            $defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findBy(
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
        if( $currentState == $adminReviewState || $addForAllStates === true || $addForAllStates === $irbReviewState ) {
            //$defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findByState($adminReviewState);
            $defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findBy(
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
        if( $currentState == $committeeReviewState || $addForAllStates === true || $addForAllStates === $irbReviewState ) {

            //$defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findByState($committeeReviewState,array("primaryReview"=>"DESC"));
            $defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findBy(
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
        if( $currentState == $finalReviewState || $addForAllStates === true || $addForAllStates === $irbReviewState ) {

            //$defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findByState($finalReviewState);
            $defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findBy(
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

    public function getDefaultReviewerInfo( $state, $specialty, $asObjects=false ) {
        $infos = array();

        //$defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findByState($state,array('primaryReview' => 'DESC'));
        $defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findBy(
            array(
                'state'=>$state,
                'projectSpecialty'=>$specialty->getId()
            ),
            array('primaryReview' => 'DESC')
        );

        if( $asObjects ) {
            foreach ($defaultReviewers as $defaultReviewer) {
                if( $defaultReviewer->getReviewer() ) {
                    $infos[] = $defaultReviewer->getReviewer();
                }
                if( $defaultReviewer->getReviewerDelegate() ) {
                    $infos[] = $defaultReviewer->getReviewerDelegate();
                }
            }
            return $infos;
        }

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

    //The most reliable method to verify if this logged in user is a particular reviewer of this particular project
    public function isReviewsReviewer($reviewerUser, $projectReviewers ) {
        if( !$reviewerUser || !$reviewerUser->getId() ) {
            return false;
        }
        foreach($projectReviewers as $projectReviewer ) {
            //echo "userID=".$reviewerUser->getId().": review ID=".$projectReviewer->getId()."<br>";
            if( $projectReviewer->getReviewer() && $projectReviewer->getReviewer()->getId() ) {
                //echo "userID=".$reviewerUser->getId().": reviewerID=".$projectReviewer->getReviewer()->getId()."<br>";
                if ($projectReviewer->getReviewer()->getId() == $reviewerUser->getId()) {
                    return true;
                }
            }
            if($projectReviewer->getReviewerDelegate() && $projectReviewer->getReviewerDelegate()->getId()) {
                //echo "userID=".$reviewerUser->getId().": ReviewerDelegateID=".$projectReviewer->getReviewerDelegate()->getId()."<br>";
                if ($projectReviewer->getReviewerDelegate()->getId() == $reviewerUser->getId()) {
                    return true;
                }
            }
        }
        //echo "not reviewer => return false<br>";
        return false;
    }

    public function isReviewer($reviewerUser, $review, $asPrimary=false ) {
        if( !$reviewerUser || !$reviewerUser->getId() ) {
            return false;
        }
        //echo "reviewer ID=".$review->getId()."<br>";

        if ($review->getReviewer() && $review->getReviewer()->getId() ) {
            if ($review->getReviewer()->getId() == $reviewerUser->getId()) {
                if( $asPrimary ) {
                    if( method_exists($review, 'getPrimaryReview') ) {
                        if( $review->getPrimaryReview() ) {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
        }
        if ($review->getReviewerDelegate() && $review->getReviewerDelegate()->getId()) {
            if ($review->getReviewerDelegate()->getId() == $reviewerUser->getId()) {
                if( $asPrimary ) {
                    if( method_exists($review, 'getPrimaryReview') ) {
                        if( $review->getPrimaryReview() ) {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
        }

        //exit('not reviewer');
        return false;
    }

    //Use removeCommitteeReview instead of removeElement.
    // Otherwise id is null:
    //Error: An exception occurred while executing
    // 'UPDATE transres_committeeReview SET primaryReview = ?, id = ?, reviewer = ?, updatedate = ?
    // WHERE id = ?' with params [1, null, 209, "2020-02-19 18:27:10", 10318]:
    // SQLSTATE[23502]: Not null violation: 7 ERROR: null value in column "id" violates not-null constraint
    // DETAIL: Failing row contains (null, 3337, 209, t, committee_review, 2019-12-17 18:04:49, 2020-02-19 18:27:10, null, null, null). with code0
    public function removeReviewsFromProject($project, $originalReviews, $reviewClass) {
        //$reviewClass = "CommitteeReview";
        $getterMethod = "get".$reviewClass."s";
        $removeMethod = "remove".$reviewClass;
        $currentReviews = $project->$getterMethod();
        foreach( $originalReviews as $originalReview ) {
            if (false === $currentReviews->contains($originalReview)) {
                // remove the Task from the Tag
                //$currentReviews->removeElement($originalReview);
                $project->$removeMethod($originalReview);

                // if it was a many-to-one relationship, remove the relationship like this
                $originalReview->setProject(null);

                $this->em->persist($originalReview);

                // if you wanted to delete the Tag entirely, you can also do that
                $this->em->remove($originalReview);
            }
        }

        //TODO: add an appropriate reviewer role
//        foreach( $currentReviews as $currentReview ) {
//            $reviewId = $currentReview->getId();
//            if( !$reviewId ) {
//                //$this->em->persist($currentReview);
//            }
//        }

        return $project;
    }
//    public function removeReviewsFromProject_ORIG($project, $originalReviews, $currentReviews) {
//        foreach ($originalReviews as $originalReview) {
//            if (false === $currentReviews->contains($originalReview)) {
//                // remove the Task from the Tag
//                $currentReviews->removeElement($originalReview);
//
//                // if it was a many-to-one relationship, remove the relationship like this
//                $originalReview->setProject(null);
//
//                $this->em->persist($originalReview);
//
//                // if you wanted to delete the Tag entirely, you can also do that
//                $this->em->remove($originalReview);
//            }
//        }
//        return $project;
//    }

    //Change review's decision according to the current state, because we search pending project assigned to me by decision == NULL
    public function resetReviewDecision($project, $review=null)
    {
        $state = $project->getState();

        //echo "state=" . $state . "<br>";
        //if ($review) {
        //    echo "review getStateStr=" . $review->getStateStr() . "<br>";
        //}
        //exit();

        if( !$state ) {
            return null;
        }

        $pending = NULL;
        $approved = "approved";
        $reset = false;

        if( $state == "irb_review" || $state == "draft" ) { //strpos($state, "irb_") !== false
            //set before stages
            //set after stages
            $this->setProjectDecision($project->getIrbReviews(),$pending);
            $this->setProjectDecision($project->getAdminReviews(),$pending);
            $this->setProjectDecision($project->getCommitteeReviews(),$pending);
            $this->setProjectDecision($project->getFinalReviews(),$pending);
            $reset = true;
        }
        if( $state == "admin_review" ) {
            //set before stages
            $this->setProjectDecision($project->getIrbReviews(),$approved);
            //set after stages
            $this->setProjectDecision($project->getAdminReviews(),$pending);
            $this->setProjectDecision($project->getCommitteeReviews(),$pending);
            $this->setProjectDecision($project->getFinalReviews(),$pending);
            $reset = true;
        }
        if( $state == "committee_review" ) {
            //set before stages
            $this->setProjectDecision($project->getIrbReviews(),$approved);
            $this->setProjectDecision($project->getAdminReviews(),$approved);
            //set after stages
            $this->setProjectDecision($project->getCommitteeReviews(),$pending);
            $this->setProjectDecision($project->getFinalReviews(),$pending);
            $reset = true;
        }
        if( $state == "final_review" ) {
            //set before stages
            $this->setProjectDecision($project->getIrbReviews(),$approved);
            $this->setProjectDecision($project->getAdminReviews(),$approved);
            $this->setProjectDecision($project->getCommitteeReviews(),$approved);
            //set after stages
            $this->setProjectDecision($project->getFinalReviews(),$pending);
            $reset = true;
        }

        $msg = null;
        if( $reset ) {
            $msg = "Reset $state state's reviews decisions and all children reviews to pending";
        }

        $msg = "<br>".$msg;

        return $msg;
    }
    public function setProjectDecision($reviews,$decision) {
        foreach($reviews as $review) {
            if( $review->getStateStr() === "committee_review" ) {
                if( $review->getPrimaryReview() === true ) {
                    $review->setDecision($decision);
                }
            } else {
                $review->setDecision($decision);
            }
        }
    }

    public function processDefaultReviewersRole( $defaultReviewer, $originalReviewer=null, $originalReviewerDelegate=null ) {

        $roles = $defaultReviewer->getRoleByState();
        $reviewerRole = $roles['reviewer'];

        $reviewer = $defaultReviewer->getReviewer();
        if( $reviewer ) {
            $reviewer->addRole($reviewerRole);
            $this->addTesterRole($reviewer);
        }
        //remove role: make sure if the user is not a default reviewer in all other objects. Or don't remove role at all.
        //if( $originalReviewer && $originalReviewer != $reviewer ) {
            //$originalReviewer->removeRole($reviewerRole);
        //}

        $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
        if( $reviewerDelegate ) {
            $reviewerDelegate->addRole($reviewerRole);
            $this->addTesterRole($reviewerDelegate);
        }
        //remove role: make sure if the user is not a default reviewer in all other objects. Or don't remove role at all.
        //if( $originalReviewerDelegate && $originalReviewerDelegate != $reviewerDelegate && $reviewerDelegateRole ) {
            //$originalReviewerDelegate->removeRole($reviewerDelegateRole);
        //}

        return $defaultReviewer;
    }

    public function addTesterRole( $user ) {
        $userSecUtil = $this->container->get('user_security_utility');
        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment != 'live' ) {
            $user->addRole("ROLE_TESTER");
        }

        return $user;
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
//                        'data_class' => 'App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName,
//                        'disabled' => $disabled
//                    ));
//                    //$reviewHtml = $this->render('AppTranslationalResearchBundle/ReviewBaseController/Some.html.twig', array())->getContent();
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
////                        'data_class' => 'App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
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
////                        'data_class' => 'App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
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
////                        'data_class' => 'App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
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

        $userSecUtil = $this->container->get('user_security_utility');
        $humanName = $this->getHumanName();

        switch ($transitionName) {
            //initial stages
            case "to_draft":
                $label = "Save Draft Project Request";
                $labeled = "Saved as Draft";
                break;
//            case "to_completed":
//                $label = "Complete Submission";
//                $labeled = "Completed Submission";
//                break;
            case "to_review":
                $label = "Submit Project Request to $humanName Review";
                $labeled = "Submitted to $humanName Review";
                break;
            //final stages
            case "approved_closed":
                $label = "Close Project Request";
                $labeled = "Closed Project Request";
                break;
            case "closed_approved":
                $label = "Re-Open previously Final Approved Project Request";
                $labeled = "Re-Opened (previously Final Approved Project Request)";
                break;

//            ///// Main Actions /////
            case "irb_review_approved":
                $label = "Approve Project Request as a Result of $humanName Review";
                $labeled = "Approved Project Request as a Result of $humanName Review";
                break;
            case "irb_review_rejected":
                $label = "Reject Project Request as a Result of $humanName Review";
                $labeled = "Rejected Project Request as a Result of $humanName Review";
                break;
            case "irb_review_missinginfo":
                $label = "Request additional information from submitter for $humanName Review";
                $labeled = "Requested additional information from submitter for $humanName Review";
                break;
            case "irb_review_resubmit":
                $label = "Resubmit Project Request to $humanName Review";
                $labeled = "Resubmitted Project Request to $humanName Review";
                break;

            case "admin_review_approved":
                $label = "Approve Project Request as a Result of Admin Review";
                $labeled = "Approved Project Request as a Result of Admin Review";
                break;
            case "admin_review_rejected":
                $label = "Reject Project Request as a Result of Admin Review";
                $labeled = "Rejected Project Request as a Result of Admin Review";
                break;
            case "admin_review_missinginfo":
                $label = "Request additional information from submitter for Admin Review";
                $labeled = "Requested additional information from submitter for Admin Review";
                break;
            case "admin_review_resubmit":
                $label = "Resubmit Project Request to Admin Review";
                $labeled = "Resubmitted Project Request to Admin Review";
                break;

            case "committee_review_approved":
                $label = "Approve Project Request as a Result of Committee Review";
                $labeled = "Approved Project Request as a Result of Committee Review";
                //echo "Reviewer=".$review->getReviewer()."<br>";
                if( $review && method_exists($review, 'getPrimaryReview') ) {
                    //echo "committee_review_approved PrimaryReview =".$review->getPrimaryReview()."<br>";
                    if( $review->getPrimaryReview() === true ) {
                        //$label = $label . " as Primary Reviewer";
                        //$labeled = $labeled . " as Primary Reviewer";
                    } else {
                        $userInfo = $this->getReviewerInfo($review);
                        $label = "Recommend Approval as a Result of Committee Review" . $userInfo;
                        $labeled = "Recommended Approval as a Result of Committee Review" . $userInfo;
                    }
                }
                break;
            case "committee_review_rejected":
                $label = "Reject Project Request as a Result of Committee Review";
                $labeled = "Rejected Project Request as a Result of Committee Review";
                if( $review && method_exists($review, 'getPrimaryReview') ) {
                    if ($review->getPrimaryReview() === true) {
                        //$label = $label . " as Primary Reviewer";
                        //$labeled = $labeled . " as Primary Reviewer";
                    } else {
                        $userInfo = $this->getReviewerInfo($review);
                        //$label = "Recommend Reject Committee Review".$userInfo;
                        $label = "Recommend Rejection as a Result of Committee Review" . $userInfo;
                        $labeled = "Recommended Rejection as a Result of Committee Review" . $userInfo;
                    }
                }
                break;
//            case "committee_review_missinginfo":
//                $label = "Request additional information from submitter for Committee Review";
//                $labeled = "Requested additional information from submitter for Committee Review";
//                if( method_exists($review, 'getPrimaryReview') ) {
//                    if ($review->getPrimaryReview() === true) {
//                        $label = $label . " as Primary Reviewer";
//                        $labeled = $labeled . " as Primary Reviewer";
//                    }
//                }
//                break;
            case "committee_review_resubmit":
                $label = "Resubmit Project Request to Committee Review";
                $labeled = "Resubmitted Project Request to Committee Review";
                break;

            case "final_review_approved":
                $label = "Approve Project Request as a Result of Final Review";
                $labeled = "Approved Project Request as a Result of Final Review";
                break;
            case "final_review_rejected":
                $label = "Reject Project Request as a Result of Final Review";
                $labeled = "Rejected Project Request as a Result of Final Review";
                break;
//            case "final_review_missinginfo":
//                $label = "Request additional information from submitter for Final Review";
//                $labeled = "Requested additional information from submitter for Final Review";
//                break;
            case "final_review_resubmit":
                $label = "Resubmit Project Request to Final Review";
                $labeled = "Resubmitted Project Request to Final Review";
                break;

            case "committee_finalreview_approved":
                $label = "Provide Final Approval";
                $labeled = "Provided Final Approval";
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

        //if not the actual reviewer show name "(as Mister John)"
        //if( $transitionName != "committee_finalreview_approved" ) {
        //TODO: to diffirentiate, add if actual user can not use this $transitionName
        if( strpos($transitionName, "finalreview_approved") === false ) {
            $user = $this->secTokenStorage->getToken()->getUser();
            $showReviewer = false;
            if( $review ) {
                $reviewer = $review->getReviewer();
                $reviewerDelegate = $review->getReviewerDelegate();
                if ($reviewer && $reviewer->getId() != $user->getId()) {
                    $showReviewer = true;
                }
                if ($reviewerDelegate && $reviewerDelegate->getId() != $user->getId()) {
                    $showReviewer = true;
                }
            }
            if( $showReviewer ) {
                if (strpos($returnLabel, "(as ") === false) {
                    $userInfo = $this->getReviewerInfo($review);
                    $returnLabel = $returnLabel . $userInfo;
                }
            }
        }

        return $returnLabel;
    }
    public function getReviewerInfo($review) {
        $userInfo = "";
        if( $review && $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ) {
            $reviewer = $review->getReviewer();
            if( $reviewer ) {
                $userInfo = " (as " . $reviewer->getDisplayName() . ")";
            }
        }
        return $userInfo;
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
                $humanName = $this->getHumanName();
                $state = "$humanName Review";
                break;
            case "irb_rejected":
                $humanName = $this->getHumanName();
                $state = "$humanName Review Rejected";
                break;
            case "irb_missinginfo":
                $humanName = $this->getHumanName();
                $state = "Pending additional information from submitter for $humanName Review";
                break;

            case "admin_review":
                $state = "Admin Review";
                break;
            case "admin_rejected":
                $state = "Admin Review Rejected";
                break;
            case "admin_missinginfo":
                $state = "Pending additional information from submitter for Admin Review";
                break;

            case "committee_review":
                $state = "Committee Review";
                break;
            case "committee_rejected":
                $state = "Committee Review Rejected";
                break;
//            case "committee_missinginfo":
//                $state = "Pending additional information from submitter for Committee Review";
//                break;

            case "final_review":
                $state = "Final Review";
                break;
            case "final_approved":
                $state = "Approved";
                break;
            case "final_rejected":
                $state = "Final Review Rejected";
                break;
//            case "final_missinginfo":
//                $state = "Pending additional information from submitter for Final Review";
//                break;

            case "canceled":
                $state = "Canceled";
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
                $humanName = $this->getHumanName();
                $state = "$humanName Review";
                break;
            case "irb_rejected":
                $humanName = $this->getHumanName();
                $state = "$humanName Review Rejected";
                break;
            case "irb_missinginfo":
                $humanName = $this->getHumanName();
                $state = "Request additional information from submitter for $humanName Review";
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
//            case "committee_missinginfo":
//                $state = "Request additional information from submitter for Committee Review";
//                break;

            case "final_review":
                $state = "Final Review";
                break;
            case "final_approved":
                $state = "Approved";
                break;
            case "final_rejected":
                $state = "Final Review Rejected";
                break;
//            case "final_missinginfo":
//                $state = "Request additional information from submitter for Final Review";
//                break;

            case "canceled":
                $state = "Canceled";
                break;

            case "closed":
                $state = "Closed";
                break;

            default:
                $state = "<$stateName>";

        }
        return $state;
    }

    public function getReviewClassNameByState($state, $asClassName=true) {
        //echo "state=".$state."<br>";
        if( strpos($state, "irb_") !== false ) {
            if( $asClassName ) {
                return "IrbReview";
            } else {
                return "irb_review";
            }
        }
        if( strpos($state, "admin_") !== false ) {
            if( $asClassName ) {
                return "AdminReview";
            } else {
                return "admin_review";
            }
        }
        if( strpos($state, "committee_") !== false ) {
            if( $asClassName ) {
                return "CommitteeReview";
            } else {
                return "committee_review";
            }
        }
        if( strpos($state, "final_") !== false ) {
            if( $asClassName ) {
                return "FinalReview";
            } else {
                return "final_review";
            }
        }

        return $state;
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
//            'committee_missinginfo',

            'final_review',
            'final_approved',
            'final_rejected',
//            'final_missinginfo',

            'canceled',

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
    public function getDefaultStatesArr() {
        $defaultStatesArr = array();
        $states = $this->getStateChoisesArr();
        foreach($states as $state) {
            if( $state != 'draft' ) {
                $defaultStatesArr[] = $state;
            }
        }

        return $defaultStatesArr;
    }

    public function getHtmlClassTransition( $transitionName ) {

        //irb_review_approved => IRB Review Approved
        //irb_review_rejected => IRB Review Rejected
        //irb_review_missinginfo => IRB Review Missinginfo
        //irb_review_resubmit => IRB Review Resubmit
        if( strpos($transitionName, "_approved") !== false ) {
            if( strpos($transitionName, "finalreview_approved") !== false ) {
                return "btn btn-warning transres-review-submit"; //btn-primary
            }
            return "btn btn-success transres-review-submit";
        }
        if( strpos($transitionName, "_missinginfo") !== false ) {
            return "btn btn-warning transres-review-submit transres-missinginfo";
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
            throw new \Exception('Unable to find Review Entity Name by state='.$state);
        }
        //echo "reviewEntityName=".$reviewEntityName."<br>";

        $reviewObject = $this->em->getRepository('AppTranslationalResearchBundle:'.$reviewEntityName)->find($reviewId);
        if( !$reviewObject ) {
            throw new \Exception('Unable to find '.$reviewEntityName.' by id='.$reviewId);
        }

        return $reviewObject;
    }

    public function getReviewsByProjectAndState($project,$state) {
        $reviewEntityName = $this->getReviewClassNameByState($state);
        if( !$reviewEntityName ) {
            throw new \Exception('Unable to find Review Entity Name by state='.$state);
        }

        $reviews = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project);

        return $reviews;
    }

    public function getSingleReviewByProject($project) {
        $reviews = $this->getReviewsByProjectAndState($project,$project->getState());
        //take the first review
        if( count($reviews) == 1 ) {
            return $reviews[0];
        }
        return null;
    }

    public function getSingleTransitionNameByProject($project) {
        $workflow = $this->container->get('state_machine.transres_project');
        $transitions = $workflow->getEnabledTransitions($project);
        //take the first $transition
        if( count($transitions) == 1 ) {
            $transition = $transitions[0];
            return $transition->getName();
        }
        return null;
    }

    //Testing (Not Used): Trying to overwrite the workflow for COVID19: committee_review => irb_review => admin_review => final_review
    //Second approach: create a separate transitions workflow for covid19
    public function getProjectEnabledTransitions($project) {
        $workflow = $this->container->get('state_machine.transres_project');
        $transresUtil = $this->container->get('transres_util');
        if( $project->getProjectSpecialty()->getAbbreviation() == "covid19" ) {
            $transitions = $workflow->getEnabledTransitions($project);
            if( count($transitions) == 1 ) {
                $transition = $transitions[0];
                $transitionName = $transition->getName();
                if( $transitionName == "irb_review" ) {
                    $transitionName = "admin_review_approved";
                    $transition = $transresUtil->getTransitionByName($project, $transitionName);
                    return array($transition);
                }
                if( $transitionName == "committee_review" ) {
                    $transitionName = "to_review";
                    $transition = $transresUtil->getTransitionByName($project, $transitionName);
                    return array($transition);
                }
            }
        } else {
            return $workflow->getEnabledTransitions($project);
        }
    }

    //NOT USED
//    public function getReviewByProjectAndReviewidAndState($project, $reviewId, $state) {
//
//        $reviewEntityName = $this->getReviewClassNameByState($state);
//        if( !$reviewEntityName ) {
//            throw new \Exception('Unable to find Review Entity Name by state='.$state);
//        }
//        //echo "reviewEntityName=".$reviewEntityName."<br>";
//
//        if(1) {
//            $reviewObject = $this->em->getRepository('AppTranslationalResearchBundle:'.$reviewEntityName)->find($reviewId);
//            if( !$reviewObject ) {
//                throw new \Exception('Unable to find '.$reviewEntityName.' by id='.$reviewId);
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
                        'data_class' => 'App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
                    ));
                }
                break;

            case "admin_review":
                $reviewEntityName = "AdminReview";
                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
                if( count($reviewObjects) > 0 ) {
                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObjects[0], array(
                        //'form_custom_value' => $params,
                        'data_class' => 'App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
                    ));
                }
                break;

            case "committee_review":
                $reviewEntityName = "CommitteeReview";
                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
                if( count($reviewObjects) > 0 ) {
                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObjects[0], array(
                        //'form_custom_value' => $params,
                        'data_class' => 'App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
                    ));
                }
                break;

            case "final_review":
                $reviewEntityName = "FinalReview";
                $reviewObjects = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project,$user);
                if( count($reviewObjects) > 0 ) {
                    $reviewForm = $this->createForm(ReviewBaseType::class, $reviewObjects[0], array(
                        //'form_custom_value' => $params,
                        'data_class' => 'App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName
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
//            $reviewObject = $this->em->getRepository('AppTranslationalResearchBundle:' . $reviewObjectClassName)->findBy(array(
//                'reviewer' => $reviewer->getId(),
//                'project' => $project->getId()
//            ));
//            if (!$reviewObject) {
//                $reviewObject = $this->em->getRepository('AppTranslationalResearchBundle:' . $reviewObjectClassName)->findByReviewerDelegate($reviewer);
//            }
//        }
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:' . $reviewObjectClassName);
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
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER')
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

        if( $role && $this->em->getRepository('AppUserdirectoryBundle:User')->isUserHasSiteAndPartialRoleName($user,$sitename,$role) ) {
            return true;
        }

        return false;
    }
//    //Used to show correct approve/reject buttons in transer.html.twig->projectReviewsEnabledLinkActions and to show resubmit buttons
//    //check if the current logged in user has permission to make changes from the current project state
//    //True should be returned for only actual reviewer or reviewer's delegate
//    public function isUserAllowedFromThisStateByProjectOrReview_ORIG($project, $review) {
//
//        if( !$project ) {
//            $project = $review->getProject();
//        }
//
//        if( $project && $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//            return false;
//        }
//
////        $workflow = $this->container->get('state_machine.transres_project');
////        $transitions = $workflow->getEnabledTransitions($project);
////
////        if( count($transitions) != 1 ) {
////            throw new \Exception("Review with ID ".$review->getId()." does not have a project");
////        }
////
////        foreach($transitions as $transition) {
////            $this->printTransition($transition);
////        }
////        //exit('1');
//
//        $stateStr = $this->getAllowedFromState($project); //must be equal to the current project state
//        if( !$stateStr ) {
//            return false;
//        }
//
//        if( $this->isAdminOrPrimaryReviewer($project) ) {
//            return true;
//        }
//
//        $user = $this->secTokenStorage->getToken()->getUser();
//
//        //check if reviewer
//        //$project, $user, $stateStr=null, $onlyReviewer=false
//        if( $this->isProjectStateReviewer($project,$user) ) {
//            return true;
//        }
//
//        //check if submitter and project state has _missinginfo
////        if( strpos($stateStr, "_missinginfo") !== false ) {
////            if( $this->isProjectRequester($project) ) {
////                return true;
////            }
////        }
//        if( $this->isProjectStateRequesterResubmit($project) ) {
//            return true;
//        }
//
//        return false;
//    }
    //Used to show correct approve/reject buttons in transer.html.twig->projectReviewsEnabledLinkActions and to show resubmit buttons
    //Check if the current logged in user is a reviewer of this review or reviewer's delegate
    //True should be returned for only actual reviewer or reviewer's delegate
    public function isUserAllowedFromThisStateByProjectAndReview($project, $review) {
        //echo "isUserAllowedFromThisStateByProjectAndReview: reviewer=".$review->getReviewer()." <br>";
        $user = $this->secTokenStorage->getToken()->getUser();

        if( !$project ) {
            $project = $review->getProject();
        }

//        if( $project && $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//            echo "False isUserAllowedSpecialtyObject<br>";
//            return false;
//        }

        $stateStr = $this->getAllowedFromState($project);
        if( !$stateStr ) {
            //echo "False: no stateStr <br>";
            return false;
        }

//        if( $this->isAdminOrPrimaryReviewer($project) ) {
//            return true;
//        }
        //$strictReviewer = true;
        if( $this->isAdminReviewer($project) ) {
            return true;
        }

        //check if reviewer
        //$project, $user, $stateStr=null, $onlyReviewer=false
//        $onlyReviewer = false;
//        if( $this->isProjectStateReviewer($project,$user,$stateStr,$onlyReviewer) ) {
//            echo "True isProjectStateReviewer<br>";
//            return true;
//        }

        //check if a user is a reviewer
        if( $this->isReviewsReviewer($user, array($review)) ) {
            //echo "True isReviewsReviewer<br>";
            return true;
        }

        if( $this->isProjectStateRequesterResubmit($project) ) {
            //echo "True isProjectStateRequesterResubmit<br>";
            return true;
        }

        //echo "### Exit isUserAllowedFromThisStateByProjectAndReview ###<br>";
        return false;
    }

    //return true if the project is in missinginfo state and logged in user is a requester or admin
    public function isProjectStateRequesterResubmit($project, $checkProjectSpecialty=true) {

//        if( $checkProjectSpecialty ) {
//            if ($project && $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false) {
//                return false;
//            }
//        }

        $stateStr = $this->getAllowedFromState($project);
        if( !$stateStr ) {
            return false;
        }

        if( strpos($stateStr, "_missinginfo") !== false ) {
            if ($this->isAdminReviewer($project)) {
                return true;
            }
            if( $this->isProjectRequester($project,$checkProjectSpecialty) ) {
                return true;
            }
        }
        return false;
    }

    //return true if the project is in review state and logged in user is a reviewer or admin
    public function isProjectStateReviewer($project, $user, $stateStr=null, $onlyReviewer=false) {

//        if( $project && $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//            return false;
//        }

        if( !$stateStr ) {
            $stateStr = $this->getAllowedFromState($project); //must be equal to the current project state
        }

        if( !$stateStr ) {
            return false;
        }

        //echo "stateStr=$stateStr<br>";

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
            if( $onlyReviewer == false ) {
                if( $this->isAdminOrPrimaryReviewer($project) ) {
                    return true;
                }
            }
            if( $this->isReviewsReviewer($user, $reviews) ) {
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

    //return transres-Project-3371-irb_review;  "irb_review" if the current stage is "irb_missinginfo" or "admin_missinginfo"
    public function getProjectThreadIdByCurrentState($project) {
//        $workflow = $this->container->get('state_machine.transres_project');
//        $transitions = $workflow->getEnabledTransitions($project);
//        foreach( $transitions as $transition ) {
//            foreach( $transition->getTos() as $to ) {
//
//            }
//        }
        $prefix = "unknown";
        $state = $project->getState(); //"irb_missinginfo" or "admin_missinginfo"
        //echo "state=$state <br>";
        if( strpos($state,"_") !== false ) {
            $stateArr = explode("_",$state);
            //echo "stateArr=".count($stateArr)."<br>";
            if( count($stateArr) == 2 ) {
                $prefix = $stateArr[0];
            }
        }
        return "transres-Project-".$project->getId()."-".$prefix."_review";
    }

    public function isUserAllowedReview( $review ) {
        //echo "reviewId=".$review->getId()."<br>";
        if(
            $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER')
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
    }

    //NOT USED
//    public function processProjectOnReviewUpdate( $review, $stateStr, $request, $testing=false ) {
//
//        $project = $review->getProject();
//        if( !$project ) {
//            throw new \Exception("Review with ID ".$review->getId()." does not have a project");
//            //return null;
//        }
//
//        $user = $this->secTokenStorage->getToken()->getUser();
//        $userSecUtil = $this->container->get('user_security_utility');
//        $transresUtil = $this->container->get('transres_util');
//        //$break = "\r\n";
//        $break = "<br>";
//        //echo "user=".$user."<br>";
//
//        //$currentState = $project->getState();
//
//        //set project next transit state depends on the decision
//        $appliedTransition = $this->setProjectState($project,$review,$testing);
//        //exit("exit appliedTransition=".$appliedTransition);
//
//        if( $appliedTransition ) {
//            //$recommended = false;
//            $eventType = "Review Submitted";
//            $label = $this->getTransitionLabelByName($appliedTransition,$review);//not used
//            $subject = "Project request ".$project->getOid()." has been sent to the status '$label'";
//            $body = "Project request ".$project->getOid()." has been sent to the status '$label'";
//        } else {
//            //$recommended = true;
//            $eventType = "Review Submitted";
//            $label = $this->getStateLabelByName($project->getState());
//            $subject = "Project request ".$project->getOid(). " (" .$label. "). Recommendation: ".$review->getDecision();
//            $body = $subject;
//        }
//
//        //get project url
//        $projectUrl = $transresUtil->getProjectShowUrl($project);
//        $emailBody = $body . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;
//
//        //send notification emails (not used)
//        $this->sendNotificationEmails($project,$review,$subject,$emailBody,$testing);
//
//        //event log
//        //$this->setEventLog($project,$review,$appliedTransition,$stateStr,$eventType,$body,$testing);
//        $this->setEventLog($project,$eventType,$body,$testing);
//    }
    //used by processProjectOnReviewUpdate
    public function setProjectState( $project, $review, $testing=false ) {

        $appliedTransition = null;

        //echo "decision=".$review->getDecision()."<br>";
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

        //echo "<pre>";
        //print_r($transitions);
        //echo "</pre><br><br>";

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
                    "Successful transition: ".$transitionNameFinal."; Project request is in ".$this->getStateLabelByProject($project)." stage."
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
        if( $this->secTokenStorage->getToken() ) {
            $user = $this->secTokenStorage->getToken()->getUser();
        } else {
            $user = null;
        }
        $userSecUtil = $this->container->get('user_security_utility');
        if( !$testing ) {
            $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'), $event, $user, $project, null, $eventType);
        }
    }

    //NOT USED
    //1) I can change the code to send only notifications emails to admins (not primary reviewers) when status is changed.
    //2) When project is in the particular stage, then the reviewers of this particular stag receive emails too.
    //3) The emails will be send to the project's requesters only when project is approved, closed, rejected or "additional information is required".
    public function sendNotificationEmails($project, $review, $subject, $body, $testing=false) {
        $emailUtil = $this->container->get('user_mailer_utility');
        $transresUtil = $this->container->get('transres_util');

        $emails = array();

        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);

        //send to the
        // 1) admins and primary reviewers
        //                                      $projectSpecialty=null, $asEmail=true, $onlyAdmin=false
        $admins = $this->getTransResAdminEmails($project->getProjectSpecialty(),true,true); //ok
        $emails = array_merge($emails,$admins);

        //project's submitter only
        $submitter = $project->getSubmitter()->getSingleEmail(false);
        $emails = array_merge($emails, array($submitter));

        // 2) project's Requester (submitter, principalInvestigators, coInvestigators, pathologists)
        if( $review ) {
            //3) The emails will be send to the project's requesters only when project is approved, closed, rejected or "additional information is required".
            if(
                $project->getState() == "draft" || $project->getState() == "irb_review" ||
                $project->getState() == "irb_missinginfo" || $project->getState() == "admin_missinginfo" ||
                $project->getState() == "irb_rejected" || $project->getState() == "admin_rejected" || $project->getState() == "committee_rejected" ||
                $project->getState() == "final_approved" || $project->getState() == "final_rejected" ||
                $project->getState() == "closed"
            ) {
                $requesterEmails = $this->getRequesterEmails($project); //ok
                $emails = array_merge($emails, $requesterEmails);
            }
        }

//        //Reviewers
//        if( 0 && $review ) {
//            // 3) current project's reviewers
//            $currentReviewerEmails = $this->getCurrentReviewersEmails($review); //ok
//            $emails = array_merge($emails, $currentReviewerEmails);
//
//            // 4) next state project's reviewers
//            $nextStateReviewerEmails = $this->getNextStateReviewersEmails($project,$project->getState());
//            $emails = array_merge($emails,$nextStateReviewerEmails);
//        }

        $emails = array_unique($emails);

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $subject, $body, null, $senderEmail );

    }

    //Use to send notification emails for project transition (awaiting review, missing info, rejected, final, closed)
    public function sendTransitionEmail($project,$originalStateStr,$testing=false) {
        $emailUtil = $this->container->get('user_mailer_utility');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->secTokenStorage->getToken()->getUser();
        $subject = null;
        $body = null;
        $msg = null;
        //$senderEmail = null; //Admin email
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
        //$break = "\r\n";
        $break = "<br>";
        $oid = $project->getOid();
        $currentStateStr = $project->getState();
        $currentStateLabel = $this->getStateLabelByProject($project);
        $originalStateLabel = $this->getStateLabelByName($originalStateStr);
        //$submitter = $project->getSubmitter();
        //Project request APCP1 'Project Title' submitted by FirstName LastName on MM/DD/YYYY
        $projectInfo = $project->getProjectInfoName();
        $projectReviewUrl = $this->getProjectReviewUrl($project);

        //echo "currentStateStr=$currentStateStr<br>";
        //exit("111");

        //Case: awaiting for review stage: send only to reviewers, ccs: admins
        if(
            $currentStateStr == "irb_review" ||
            $currentStateStr == "admin_review" ||
            $currentStateStr == "committee_review" ||
            $currentStateStr == "final_review"
        ) {
            //get reviewers
            //$emailRecipients = $this->getCurrentReviewersEmails($review);
            //get reviewers based on the current state project's reviewers
            //echo "currentStateStr=$currentStateStr<br>";
            $emailRecipients = $this->getNextStateReviewersEmails($project,$currentStateStr);
            //exit("tos:".implode("; ",$emailRecipients));

            //Subject: Project request APCP28 is ready for your review. Its current status is 'IRB Review'.
            $subject = "Project request $oid is ready for your review. Its current status is '$currentStateLabel'.";

            //Body: Project request APCP28 'Project Title' submitted by FirstName LastName on MM/DD/YYYY is now awaiting your review.
            //At the time of this notification, the status of this project request is 'IRB Review'.
            //To review this project request, please visit the link below: http://LINK TO THE REVIEW PAGE FOR THIS PROJECT REQUEST
            $body = $projectInfo . " is now awaiting your review.";
            $body = $body . " At the time of this notification, the status of this project request is '$currentStateLabel'.";
            $body = $body . $break.$break;
            $body = $body . "To review this project request, please visit the link below:";
            $body = $body . $break. $projectReviewUrl;

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project->getProjectSpecialty(),true,true); //ok

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail );
        }

        //Case: missing info: send to requesters(submitter, contact), ccs: admins
        if(
            $currentStateStr == "irb_missinginfo" ||
            $currentStateStr == "admin_missinginfo"
        ) {

            $projectTitle = $project->getTitle();
            $emailRecipients = $this->getRequesterMiniEmails($project);

            //Please provide additional information for project request APCP1171 'Project test 11'
            $subject = "Please provide additional information for project request $oid '$projectTitle'";

            //"Additional information has been requested for the project with ID $id '".$title."' for the '".$fromLabel."' stage.";
            //$statusChangeMsg = $this->getNotificationMsgByStates($originalStateStr,$currentStateStr,$project);
            //Additional information is needed for the project request APCP1171 'Project test 11' in order to complete the 'IRB Review' stage.
            $body = "Additional information is needed for the project request $oid '$projectTitle' in order to complete the '$originalStateLabel' stage.";

            //The following comment has been provided by the reviewer: [most recent value of comment field added by reviewer]
            $reviewComments = $this->getReviewComments($project,"<hr>");

            $body = $body . $break.$break. "Comment(s):".$break."<hr>".$reviewComments;

            $body = $body . $break.$break. "The review process will resume once the requested information is added.";

            //To supply the requested information and re-submit for review, please visit:
            $projectResubmitUrl = $this->getProjectResubmitUrl($project);
            $body = $body . $break.$break. "To supply the requested information and re-submit for review, please visit the following link:".$break.$projectResubmitUrl;

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project->getProjectSpecialty(),true,true); //ok

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail );
        }

        //Case: rejected: send to requesters(submitter, contact), ccs: admins
        if(
            $currentStateStr == "irb_rejected" ||
            $currentStateStr == "admin_rejected" ||
            $currentStateStr == "committee_rejected" ||
            $currentStateStr == "final_rejected"
        ) {
            $emailRecipients = $this->getRequesterMiniEmails($project);

            //Project request APCP3365 has been rejected at the 'Committee Review' stage
            $subject = "Project request $oid has been rejected at the '$originalStateLabel' stage";

            //"Additional information has been requested for the project with ID $id '".$title."' for the '".$fromLabel."' stage.";
            $statusChangeMsg = $this->getNotificationMsgByStates($originalStateStr,$currentStateStr,$project);
            //get project url
            $projectUrl = $this->getProjectShowUrl($project);
            $body = $statusChangeMsg . $break.$break. "To view the details of this project request, please visit the link below:".$break.$projectUrl;

            //If you have any questions, please contact
            // [FirstNameOfCurrentTRPAdminForCorrespondingSpecialty-AP/CPorHemePath
            // LastNameOfCurrentTRPAdminForCorrespondingSpecialty-AP/CPorHemePath
            // email@domain.tld – list all users with TRP sysadmin roles associated with project specialty separated by comma ]
            $body = $body . $break.$break. "If you have any questions, please contact";
            $admins = $this->getTransResAdminEmails($project->getProjectSpecialty(),false,true);
            $adminInfos = array();
            foreach( $admins as $admin ) {
                $adminInfos[] = $admin->getUsernameOptimal() . " " . $admin->getSingleEmail(false);
            }
            if( count($adminInfos) > 0 ) {
                $body = $body . " " . implode(", ",$adminInfos);
            }

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project->getProjectSpecialty(),true,true); //ok

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail );
        }

        //Case: Final Approved
        if(
            $currentStateStr == "final_approved"
        ) {
            $emailRecipients = $this->getRequesterMiniEmails($project);

            $subject = "Project request $oid has been approved";

            //"Additional information has been requested for the project with ID $id '".$title."' for the '".$fromLabel."' stage.";
            $body = $this->getNotificationMsgByStates($originalStateStr,$currentStateStr,$project);

            //To submit a work request associated with this project request, please visit the link below: LINK-TO-NEW-WORK-REQUEST-PAGE
            $linkNewRequest = $this->container->get('router')->generate(
                'translationalresearch_request_new',
                array(
                    'id' => $project->getId(),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $linkNewRequest = '<a href="'.$linkNewRequest.'">'.$linkNewRequest.'</a>';

            $body = $body .$break.$break .  "To submit a work request associated with this project, please visit the link below:";
            $body = $body . $break . $linkNewRequest;

            //Once you submit any work requests associated with this project, you will be able to access them via the following link: LINK-TO-WORK-REQUESTS-ASSOCIATED-WITH-THIS-PROJECT-ONLY
            $body = $body .$break.$break . "Once you submit any work requests associated with this project, you will be able to access them via the following link:";
            $linkRequestsForThisProject = $this->container->get('router')->generate(
                'translationalresearch_request_index',
                array(
                    'id' => $project->getId(),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $linkRequestsForThisProject = '<a href="'.$linkRequestsForThisProject.'">'.$linkRequestsForThisProject.'</a>';
            $body = $body . $break . $linkRequestsForThisProject;

            //To view work requests (including drafts) for all your projects, please visit the following link: LINK-TO-MY-WORK-REQUESTS
            $body = $body .$break.$break . "To view work requests (including drafts) for all your projects, please visit the following link:";
            $linkMyRequests = $this->container->get('router')->generate(
                'translationalresearch_request_index_filter',
                array(
                    'type' => "my-submitted-requests",
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $linkMyRequests = '<a href="'.$linkMyRequests.'">'.$linkMyRequests.'</a>';
            $body = $body . $break . $linkMyRequests;

            //Any invoices associated with this project request or your other project requests can be accessed via the following link: LINK-TO-MY-INVOICES
            $linkMyInvoices = $this->container->get('router')->generate(
                'translationalresearch_invoice_index_type',
                array(
                    'invoicetype' => "my-invoices",
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $linkMyInvoices = '<a href="'.$linkMyInvoices.'">'.$linkMyInvoices.'</a>';
            $body = $body . $break.$break . "Any invoices associated with this project request or your other project requests can be accessed via the following link:";
            $body = $body . $break . $linkMyInvoices;

            //My Outstanding Invoices
            $linkMyOutstandingInvoices = $this->container->get('router')->generate(
                'translationalresearch_invoice_index_type',
                array(
                    'invoicetype' => "my-outstanding-invoices",
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $linkMyOutstandingInvoices = '<a href="'.$linkMyOutstandingInvoices.'">'.$linkMyOutstandingInvoices.'</a>';
            $body = $body . $break.$break . "Any outstanding invoices associated with this project request or your other project requests can be accessed via the following link:";
            $body = $body . $break . $linkMyOutstandingInvoices;

            //get project url
            $projectUrl = $this->getProjectShowUrl($project);

            $body = $body . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project->getProjectSpecialty(),true,true); //ok

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail );
        }

        //All other cases: final approved, closes ...
        if( $subject && $body ) {
            //ok
        } else {
            $emailRecipients = $this->getRequesterMiniEmails($project);

            $subject = "Project request $oid status has been changed from '$originalStateLabel' to '$currentStateLabel'";
            $subject = $subject . " by " . $user;

            //"Additional information has been requested for the project with ID $id '".$title."' for the '".$fromLabel."' stage.";
            $body = $this->getNotificationMsgByStates($originalStateStr,$currentStateStr,$project);

            //get project url
            $projectUrl = $this->getProjectShowUrl($project);

            $body = $body . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project->getProjectSpecialty(),true,true); //ok

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail );
        }

        if( $subject && $body ) {
            $msg = "Email To: ".implode("; ",$emailRecipients);
            $msg = $msg . $break . "Email Css: ".implode("; ",$adminsCcs);
            $msg = $msg . $break . "Subject: " . $subject . "<br>" . "Body: " . $body;
            $msg = str_replace($break, "<br>", $msg);
        }

        return $msg;
    }


    //get all users with admin and ROLE_TRANSRES_PRIMARY_REVIEWER, ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE
    public function getTransResAdminEmails($projectSpecialty=null, $asEmail=true, $onlyAdmin=false) {
        $users = array();

        if( $projectSpecialty ) {
            $specialtyPostfix = $projectSpecialty->getUppercaseName();
            $specialtyPostfix = "_" . $specialtyPostfix;
        } else {
            $specialtyPostfix = null;
        }

        $admins = $this->em->getRepository('AppUserdirectoryBundle:User')->findUsersByRoles(array("ROLE_TRANSRES_ADMIN".$specialtyPostfix));
        foreach( $admins as $user ) {
            if( $user ) {
                if( $asEmail ) {
                    $users[] = $user->getSingleEmail(false);
                } else {
                    $users[] = $user;
                }
            }
        }

        if( $onlyAdmin ) {
            return $users;
        }

        $primarys = $this->em->getRepository('AppUserdirectoryBundle:User')->findUsersByRoles(array("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyPostfix));
        foreach( $primarys as $user ) {
            if( $user ) {
                if( $asEmail ) {
                    $users[] = $user->getSingleEmail(false);
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
                $resArr[] = $project->getSubmitter()->getSingleEmail(false);
            } else {
                $resArr[] = $project->getSubmitter();
            }
        }

        //2 principalInvestigators
        $pis = $project->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi ) {
                if( $asEmail ) {
                    $resArr[] = $pi->getSingleEmail(false);
                } else {
                    $resArr[] = $pi;
                }
            }
        }

        //2a principalIrbInvestigator
        $principalIrbInvestigators = $project->getPrincipalIrbInvestigators();
        foreach( $principalIrbInvestigators as $principalIrbInvestigator ) {
            if( $principalIrbInvestigator ) {
                if( $asEmail ) {
                    $resArr[] = $principalIrbInvestigator->getSingleEmail(false);
                } else {
                    $resArr[] = $principalIrbInvestigator;
                }
            }
        }

        //3 coInvestigators
        $cois = $project->getCoInvestigators();
        foreach( $cois as $coi ) {
            if( $coi ) {
                if( $asEmail ) {
                    $resArr[] = $coi->getSingleEmail(false);
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
                    $resArr[] = $pathologist->getSingleEmail(false);
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
                    $resArr[] = $contact->getSingleEmail(false);
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
                    $resArr[] = $billingContact->getSingleEmail(false);
                } else {
                    $resArr[] = $billingContact;
                }
            }
        }

        return $resArr;
    }
    //project's Requester (submitter, principalInvestigators, coInvestigators, pathologists)
    public function getRequesterMiniEmails($project, $asEmail=true) {
        $resArr = array();

        //1 submitter
        if( $project->getSubmitter() ) {
            if( $asEmail ) {
                $resArr[] = $project->getSubmitter()->getSingleEmail(false);
            } else {
                $resArr[] = $project->getSubmitter();
            }
        }

        //2 contacts
        $contacts = $project->getContacts();
        foreach( $contacts as $contact ) {
            if( $contact ) {
                if( $asEmail ) {
                    $resArr[] = $contact->getSingleEmail(false);
                } else {
                    $resArr[] = $contact;
                }
            }
        }

        return $resArr;
    }
    //project's Requester (principalInvestigators, submitter, contacts)
    public function getRequesterPisContactsSubmitterEmails($project, $asEmail=true) {
        $resArr = array();

        //1 principalInvestigators
        $pis = $project->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi ) {
                if( $asEmail ) {
                    $resArr[] = $pi->getSingleEmail(false);
                } else {
                    $resArr[] = $pi;
                }
            }
        }

        //2 submitter
        if( $project->getSubmitter() ) {
            if( $asEmail ) {
                $resArr[] = $project->getSubmitter()->getSingleEmail(false);
            } else {
                $resArr[] = $project->getSubmitter();
            }
        }

        //3 contacts
        $contacts = $project->getContacts();
        foreach( $contacts as $contact ) {
            if( $contact ) {
                if( $asEmail ) {
                    $resArr[] = $contact->getSingleEmail(false);
                } else {
                    $resArr[] = $contact;
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
                $resArr[] = $reviewer->getSingleEmail(false);
            } else {
                $resArr['reviewer'] = $reviewer;//->getUsernameOptimal();
            }
        }

        $reviewerDelegate = $review->getReviewerDelegate();
        if( $reviewerDelegate ) {
            if( $asEmail ) {
                $resArr[] = $reviewerDelegate->getSingleEmail(false);
            } else {
                $resArr['reviewerDelegate'] = $reviewerDelegate;//->getUsernameOptimal();
            }
        }

        return $resArr;
    }

    //current project's primary committee reviewers
    public function getCommiteePrimaryReviewerEmails($project) {
        foreach($project->getCommitteeReviews() as $committeeReview) {
            if($committeeReview->getPrimaryReview() === true ) {
                return $this->getCurrentReviewersEmails($committeeReview);
            }
        }

        return null;
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

    public function getTransResProjectSpecialties( $userAllowed=true ) {

        if( $this->secTokenStorage->getToken() ) {
            $user = $this->secTokenStorage->getToken()->getUser();
        } else {
            $user = null;
        }

        $specialties = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findBy(
            array(
                'type' => array("default","user-added")
            ),
            array('orderinlist' => 'ASC')
        );

        $allowedSpecialties = array();

        foreach($specialties as $specialty) {
            if( $userAllowed && $user ) {
                if( $this->isUserAllowedSpecialtyObject($specialty, $user) ) {
                    $allowedSpecialties[] = $specialty;
                }
            } else {
                $allowedSpecialties[] = $specialty;
            }

        }

        return $allowedSpecialties;
    }

    //NOT USED?
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
            return "Anonymous" . $authorType . $comment->getPrefix();
        }

        $user = $this->secTokenStorage->getToken()->getUser();

        //TODO: get project from thread id:
        // Project: transres-Project-2252-admin_review
        // Request: transres-Request-13530-progress

        if( $this->isAdminOrPrimaryReviewer() ) {
            return $comment->getAuthorName() . $authorType . $comment->getPrefix();
        }

        if( $user->getId() == $comment->getAuthor()->getId() ) {
            return $comment->getAuthorName() . $authorType . $comment->getPrefix();
        }

        return "Anonymous" . $authorType . $comment->getPrefix();
    }

    //Get all comments with dates for the current project state
    public function getReviewComments($project,$newline="<br>",$state=null) {
        $comments = null;

        if( !$state ) {
            $state = $project->getState();
        }

        $reviewState = $this->getReviewClassNameByState($state,false);
        //$reviewStateLabel = $this->getStateLabelByName($reviewState);

        //{{ render(controller('AppTranslationalResearchBundle/Project/threadCommentsShow', { 'id': threadId })) }}
        $threadId = "transres-" . $project->getEntityName() . "-" . $project->getId() . "-" . $reviewState;
        //echo "thread=[$threadId] <br>";

        $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($threadId);
        //echo "thread=[$thread] <br>";

        if( $thread ) {
            $thread->setCommentable(false);
            $comments = $this->container->get('fos_comment.manager.comment')->findCommentTreeByThread($thread);
        } else {
            $comments = array();
        }

        //$newline = "<br>";
        $commentStr = $this->getCommentTreeStr($comments,$newline);

        return $commentStr;
    }
    //array:
    //0 => array(
    //   'comment' => CommentInterface,
    //   'children' => array(
    //       0 => array (
    //       'comment' => CommentInterface,
    //       'children' => array(...)
    //   ),
    //1 => array (
    //   'comment' => CommentInterface,
    //   'children' => array(...)
    //)
    public function getCommentTreeStr($comments,$newline,$level=0) {
        $res = "";
        foreach($comments as $commentArr) {
            $comment = $commentArr['comment'];
            $res = $res . $this->getCommentPrefixSpace($level) . $comment->getCommentShort() . $newline;
            $children = $commentArr['children'];
            $res = $res . $this->getCommentTreeStr($children,$newline,($level+1));
            //$res = $res . $newline;
        }
        return $res;
    }
    public function getCommentPrefixSpace($level) {
        $prefix = "";
        for($i=0; $i<$level; $i++) {
            $prefix = $prefix . "---";
        }
        if( $prefix ) {
            $prefix = $prefix . " Reply ";
        }
        //echo $level.": prefix=[$prefix]<br>";
        return $prefix;
    }

    public function addCommentToCurrentProjectState($project,$commentStr) {

        if( !$commentStr ) {
            return null;
        }

        //$userServiceUtil = $this->container->get('user_service_utility');
        $commentManager = $this->container->get('fos_comment.manager.comment');
        $threadManager = $this->container->get('fos_comment.manager.thread');
        $author = $this->secTokenStorage->getToken()->getUser();

        $threadId = $this->getProjectThreadIdByCurrentState($project);

        //$uri . "/project/review/" . $project->getId();
        $permalink = $this->container->get('router')->generate(
            'translationalresearch_project_review',
            array(
                'id' => $project->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if( !$threadId || !$permalink ) {
            //exit("Exit: ThreadId or Permalink not defined.");
            return null;
        }

        //echo "threadId=".$threadId."<br>";
        //echo "permalink=".$permalink."<br>";

        $thread = $threadManager->findThreadById($threadId);

        if( null === $thread ) {
            $thread = $threadManager->createThread();
            $thread->setId($threadId);

            //$permalink = $uri . "/project/review/" . $entity->getId();
            $thread->setPermalink($permalink);

            // Add the thread
            $threadManager->saveThread($thread);
        }

        //set Author
        $parentComment = null;
        $comment = $commentManager->createComment($thread,$parentComment);
        $comment->setAuthor($author);

//        if( $createDateStr ) {
//            $createDate = $this->transformDatestrToDate($createDateStr);
//            $comment->setCreatedAt($createDate);
//        }

        //set Depth
        //$comment->setDepth(0);

        //set Prefix
        //$comment->setPrefix($prefix);

        //set comment body
        $comment->setBody($commentStr);

//        $authorTypeArr = $this->getAuthorType($project);
//        if( $authorTypeArr && count($authorTypeArr) > 0 ) {
//            $comment->setAuthorType($authorTypeArr['type']);
//            $comment->setAuthorTypeDescription($authorTypeArr['description']);
//        }

        if ($commentManager->saveComment($comment) !== false) {
            //exit("Comment saved successful!!!");
            //return $this->getViewHandler()->handle($this->onCreateCommentSuccess($form, $threadId, null));
            //View::createRouteRedirect('fos_comment_get_thread_comment', array('id' => $id, 'commentId' => $form->getData()->getId()), 201);
        }

        return $comment;
    }
//    public function getAuthorType( $entity ) {
//
//        if( !$this->secTokenStorage->getToken() ) {
//            //not authenticated
//            return null;
//        }
//
//        $authorTypeArr = array();
//
//        if( $entity->getEntityName() == "Project" ) {
//            $specialtyStr = null;
//            $projectSpecialty = $entity->getProjectSpecialty();
//            if( $projectSpecialty ) {
//                $specialtyStr = $projectSpecialty->getUppercaseName();
//                $specialtyStr = "_" . $specialtyStr;
//            }
//        }
//
//        if( $entity->getEntityName() == "Request" ) {
//            $specialtyStr = null;
//            $project = $entity->getProject();
//            $projectSpecialty = $project->getProjectSpecialty();
//            if( $projectSpecialty ) {
//                $specialtyStr = $projectSpecialty->getUppercaseName();
//                $specialtyStr = "_" . $specialtyStr;
//            }
//        }
//
//        if( $this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr) ) {
//            //$authorType = "Administrator";
//            $authorTypeArr['type'] = "Administrator";
//            $authorTypeArr['description'] = "Administrator";
//            return $authorTypeArr;
//        }
//        if( $this->secAuth->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr) ) {
//            //$authorType = "Primary Reviewer";
//            $authorTypeArr['type'] = "Administrator";
//            $authorTypeArr['description'] = "Primary Reviewer";
//            return $authorTypeArr;
//        }
//
//        //if not found
//        $transresUtil = $this->container->get('transres_util');
//        $transresRequestUtil = $this->container->get('transres_request_util');
//        $user = $this->secTokenStorage->getToken()->getUser();
//
//        //1) check if the user is entity requester
//        //$entity = $this->getEntityFromComment($comment);
//        if( !$entity ) {
//            return null;
//        }
//
//        //check if reviewer
//        if( $entity->getEntityName() == "Project" ) {
//            if ($transresUtil->isProjectReviewer($entity)) {
//                //return "Reviewer";
//                $authorTypeArr['type'] = "Reviewer";
//                $authorTypeArr['description'] = "Reviewer";
//                return $authorTypeArr;
//            }
//        }
//
//        if( $entity->getEntityName() == "Request" ) {
//            if( $transresRequestUtil->isRequestStateReviewer($entity,"progress") ) {
//                //return "Reviewer";
//                $authorTypeArr['type'] = "Reviewer";
//                $authorTypeArr['description'] = "Reviewer";
//                return $authorTypeArr;
//            }
//        }
//
//        if( $entity->getEntityName() == "Project" ) {
//            return $this->getProjectRequesterAuthorType($entity,$user);
//        }
//
//        //if( $entity->getEntityName() == "Request" ) {
//        //    return $this->getRequestRequesterAuthorType($entity,$user);
//        //}
//
//        return null;
//    }
//    public function getProjectRequesterAuthorType( $entity, $user ) {
//
//        //check if requester
//        if( $entity->getSubmitter() && $entity->getSubmitter()->getId() == $user->getId() ) {
//            //return "Submitter";
//            $authorTypeArr['type'] = "Requester";
//            $authorTypeArr['description'] = "Submitter";
//            return $authorTypeArr;
//        }
//        if( $entity->getPrincipalInvestigators()->contains($user) ) {
//            //return "Principal Investigator";
//            $authorTypeArr['type'] = "Requester";
//            $authorTypeArr['description'] = "Principal Investigator";
//            return $authorTypeArr;
//        }
//        if( $entity->getCoInvestigators()->contains($user) ) {
//            //return "Co-Investigator";
//            $authorTypeArr['type'] = "Requester";
//            $authorTypeArr['description'] = "Co-Investigator";
//            return $authorTypeArr;
//        }
//        if( $entity->getPathologists()->contains($user) ) {
//            //return "Pathologist";
//            $authorTypeArr['type'] = "Requester";
//            $authorTypeArr['description'] = "Pathologist";
//            return $authorTypeArr;
//        }
//        if( $entity->getContacts()->contains($user) ) {
//            //return "Contact";
//            $authorTypeArr['type'] = "Requester";
//            $authorTypeArr['description'] = "Contact";
//            return $authorTypeArr;
//        }
//        if( $entity->getBillingContacts()->contains($user) ) {
//            //return "Billing Contact";
//            $authorTypeArr['type'] = "Requester";
//            $authorTypeArr['description'] = "Billing Contact";
//            return $authorTypeArr;
//        }
//
//        return null;
//    }

    public function getProjectShowUrl( $project, $title=null, $newPage=false ) {
        $router = $this->getRequestContextRouter();
        $projectUrl = $router->generate(
            'translationalresearch_project_show',
            array(
                'id' => $project->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if( !$title ) {
            $title = $projectUrl;
        }

        if( $newPage ) {
            $projectUrl = '<a target="_blank" href="'.$projectUrl.'">'.$title.'</a>';
        } else {
            $projectUrl = '<a href="'.$projectUrl.'">'.$title.'</a>';
        }

        return $projectUrl;
    }
    public function getProjectEditUrl($project) {
        $router = $this->getRequestContextRouter();
        $projectUrl = $router->generate(
            'translationalresearch_project_edit',
            array(
                'id' => $project->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';

        return $projectUrl;
    }
    public function getProjectReviewUrl($project) {
        $router = $this->getRequestContextRouter();
        $projectUrl = $router->generate(
            'translationalresearch_project_review',
            array(
                'id' => $project->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $projectUrl = '<a href="'.$projectUrl.'">'.$projectUrl.'</a>';

        return $projectUrl;
    }
    public function getProjectResubmitUrl($project) {
        //the same as edit, if the project in 'missinginfo' state, then resubmit button will appear on the edit page
        return $this->getProjectEditUrl($project);
    }
    public function getRequestContextRouter() {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if( !$request ) {
            $userSecUtil = $this->container->get('user_security_utility');
            $liveSiteRootUrl = $userSecUtil->getSiteSettingParameter('liveSiteRootUrl');    //http://c.med.cornell.edu/order/
            $liveSiteHost = parse_url($liveSiteRootUrl, PHP_URL_HOST); //c.med.cornell.edu
            //echo "liveSiteHost=".$liveSiteHost."\n";
            //exit('111');

            $connectionChannel = $userSecUtil->getSiteSettingParameter('connectionChannel');
            if( !$connectionChannel ) {
                $connectionChannel = 'http';
            }

            $context = $this->container->get('router')->getContext();
            $context->setHost($liveSiteHost);
            $context->setScheme($connectionChannel);
            //$context->setBaseUrl('/order');
        }
        return $this->container->get('router');
    }

    //$specialtyStr: hematopathology, ap-cp
    public function getSpecialtyObject($specialtyAbbreviation) {
        //echo "specialtyStr=".$specialtyStr."<br>";
        $specialty = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation($specialtyAbbreviation);

        if( !$specialty ) {
            throw new \Exception( "Project specialty is not found by name '".$specialtyAbbreviation."'" );
        }

//        if( $specialty->getType() == 'default' || $specialty->getType() == 'user-added' ) {
//            //OK
//        } else {
//            return NULL;
//        }

        return $specialty;
    }

    public function getTrpSpecialtyObjects($specialtyAbbreviation=null) {
        //echo "specialtyStr=".$specialtyStr."<br>";
        //$specialty = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation($specialtyAbbreviation);

        $repository = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList');
        $dql =  $repository->createQueryBuilder("specialty");

        $dql->andWhere("specialty.type = :typedef OR specialty.type = :typeadd");

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
        );

        if( $specialtyAbbreviation ) {
            $dql->andWhere("specialty.abbreviation = :abbreviation");
            $parameters['abbreviation'] = $specialtyAbbreviation;
        }
        
        $query = $this->em->createQuery($dql);

        $query->setParameters($parameters);

        $specialties = $query->getResult();

        if( count($specialties) == 0 ) {
            throw new \Exception( "Project specialty is not found. specialtyAbbreviation=".$specialtyAbbreviation );
        }

        if( $specialtyAbbreviation ) {
            return $specialties[0];
        }

        return $specialties;
    }

    //NOT USED?
    //show it only to admin, reviewers and reviewedBy users
    public function showReviewedBy( $reviewObject ) {

        if( $this->isAdminOrPrimaryReviewer() ) { //get project from $reviewObject and use it for strict check ?
            return true;
        }

        $user = $this->secTokenStorage->getToken()->getUser();
        if( $this->isReviewer($user,$reviewObject) ) {
            return true;
        }



        return false;
    }

    //get list of projects: 1) state final_approved, 2) irbExpirationDate, 3) logged in user is requester, 4) reviewer
    public function getAvailableProjects( $finalApproved=true, $notExpired=true, $requester=true, $reviewer=true ) {

        //$transresRequestUtil = $this->container->get('transres_request_util');

        $user = $this->secTokenStorage->getToken()->getUser();
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");
        $dql->select('project');

        $dql->leftJoin('project.submitter','submitter');

        $dql->leftJoin('project.principalInvestigators','principalInvestigators');
        $dql->leftJoin('principalInvestigators.infos','principalInvestigatorsInfos');

        $dql->leftJoin('project.principalIrbInvestigator','principalIrbInvestigator');

        $dql->leftJoin('project.irbReviews','irbReviews');
        $dql->leftJoin('irbReviews.reviewer','irbReviewer');
        $dql->leftJoin('irbReviews.reviewerDelegate','irbReviewerDelegate');

        $dql->leftJoin('project.adminReviews','adminReviews');
        $dql->leftJoin('adminReviews.reviewer','adminReviewer');
        $dql->leftJoin('adminReviews.reviewerDelegate','adminReviewerDelegate');

        $dql->leftJoin('project.committeeReviews','committeeReviews');
        $dql->leftJoin('committeeReviews.reviewer','committeeReviewer');
        $dql->leftJoin('committeeReviews.reviewerDelegate','committeeReviewerDelegate');

        $dql->leftJoin('project.finalReviews','finalReviews');
        $dql->leftJoin('finalReviews.reviewer','finalReviewer');
        $dql->leftJoin('finalReviews.reviewerDelegate','finalReviewerDelegate');

        $dql->leftJoin('project.coInvestigators','coInvestigators');
        $dql->leftJoin('project.pathologists','pathologists');
        $dql->leftJoin('project.billingContact','billingContact');
        $dql->leftJoin('project.contacts','contacts');

        $dql->orderBy("project.id","DESC");

        $dqlParameters = array();

        //1) state final_approved
        if( $finalApproved ) {
            $dql->andWhere("project.state = 'final_approved'");
            //$dqlParameters = array("state" => "final_approved");
        }

        //2) irb/iacuc ExpirationDate (implicitExpirationDate)
        if( $notExpired ) {
            //$dql->andWhere("project.irbExpirationDate >= CURRENT_DATE()");
            $dql->andWhere("project.implicitExpirationDate IS NULL OR project.implicitExpirationDate >= CURRENT_DATE()");
        }

        //3) logged in user is requester (only if not admin)
        if( $requester ) {
            if (!$this->secAuth->isGranted("ROLE_TRANSRES_ADMIN")) {
                $myRequestProjectsCriterion =
                    "principalInvestigators.id = :userId OR " .
                    "principalIrbInvestigator.id = :userId OR " .
                    "coInvestigators.id = :userId OR " .
                    "pathologists.id = :userId OR " .
                    "contacts.id = :userId OR " .
                    "billingContact.id = :userId OR " .
                    "submitter.id = :userId";

                $dqlParameters["userId"] = $user->getId();
                $dql->andWhere($myRequestProjectsCriterion);
            }
        }

        //4 logged in user is reviewer (only if not admin)
        if( $reviewer ) {
            $myReviewProjectsCriterion =
                "irbReviewer.id = :userId OR ".
                "irbReviewerDelegate.id = :userId OR ".

                "adminReviewer.id = :userId OR ".
                "adminReviewerDelegate.id = :userId OR ".

                "committeeReviewer.id = :userId OR ".
                "committeeReviewerDelegate.id = :userId OR ".

                "finalReviewer.id = :userId OR ".
                "finalReviewerDelegate.id = :userId"
            ;
            $dqlParameters["userId"] = $user->getId();
            $dql->andWhere($myReviewProjectsCriterion);
        }

        //user specialty, if not admin
        if( !$this->secAuth->isGranted("ROLE_TRANSRES_ADMIN") ) {
            $specialtyIds = array();
            $specialties = $this->getTransResProjectSpecialties();
            foreach( $specialties as $specialtyObject ) {
                //check user's roles
                $partialRoleStr = $specialtyObject->getUppercaseName();
                if( $user->hasPartialRole($partialRoleStr) ) {
                    $specialtyIds[] = $specialtyObject->getId();
                }
            }

            if( count($specialtyIds) > 0 ) {
                $dql->leftJoin("project.projectSpecialty", "projectSpecialty");
                $specialtyStr = "projectSpecialty.id IN (".implode(",",$specialtyIds).")";
                //echo "specialtyStr=$specialtyStr<br>";
                $dql->andWhere($specialtyStr);
            } else {
                $dql->leftJoin("project.projectSpecialty", "projectSpecialty");
                $dql->andWhere("projectSpecialty.id IS NULL");
            }
        }

        $query = $dql->getQuery();

        //echo "projectId=".$project->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";
        //echo "query=".$query->getSql()."<br>";

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $projects = $query->getResult();

        return $projects;

//        //check for Request can not be submitted for the expired project
//        $finalProjects = array();
//        //TODO: this loop can cause delay for large number of projects. => duplicate IRB expiration date in the project
//        foreach($projects as $project) {
//            if( $transresRequestUtil->isRequestCanBeCreated($project) ) {
//                $finalProjects[] = $project;
//            }
//        }
//
//        return $finalProjects;
    }
    //logged in user requester or reviewer or submitter
    public function getAvailableRequesterOrReviewerProjects( $type=null, $limit=null, $search=null ) {
        $user = $this->secTokenStorage->getToken()->getUser();
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Project');
        $dql =  $repository->createQueryBuilder("project");

        //$dql->select('project');
        if( $search ) {
            $dql->select(
                "project.id as id,".
                " project.oid as oid,".
                "principalInvestigatorsInfos.displayName as pis,".
                //" GROUP_CONCAT(DISTINCT principalInvestigatorsInfos.displayName) as pis,".
                //" (SELECT infos.displayName FROM AppUserdirectoryBundle:UserInfo as infos LEFT JOIN infos.user userinfos WHERE userinfos.id = principalInvestigators.id) as pis,".
                " project.title as title"
                );
        } else {
            $dql->select('project');
        }

        $dql->leftJoin('project.submitter','submitter');

        $dql->leftJoin('project.principalInvestigators','principalInvestigators');
        $dql->leftJoin('principalInvestigators.infos','principalInvestigatorsInfos');

        $dql->leftJoin('project.principalIrbInvestigator','principalIrbInvestigator');

        $dql->leftJoin('project.irbReviews','irbReviews');
        $dql->leftJoin('irbReviews.reviewer','irbReviewer');
        $dql->leftJoin('irbReviews.reviewerDelegate','irbReviewerDelegate');

        $dql->leftJoin('project.adminReviews','adminReviews');
        $dql->leftJoin('adminReviews.reviewer','adminReviewer');
        $dql->leftJoin('adminReviews.reviewerDelegate','adminReviewerDelegate');

        $dql->leftJoin('project.committeeReviews','committeeReviews');
        $dql->leftJoin('committeeReviews.reviewer','committeeReviewer');
        $dql->leftJoin('committeeReviews.reviewerDelegate','committeeReviewerDelegate');

        $dql->leftJoin('project.finalReviews','finalReviews');
        $dql->leftJoin('finalReviews.reviewer','finalReviewer');
        $dql->leftJoin('finalReviews.reviewerDelegate','finalReviewerDelegate');

        $dql->leftJoin('project.coInvestigators','coInvestigators');
        $dql->leftJoin('project.pathologists','pathologists');
        $dql->leftJoin('project.billingContact','billingContact');
        $dql->leftJoin('project.contacts','contacts');

        $dql->leftJoin("project.projectSpecialty", "projectSpecialty");

        $dql->orderBy("project.id","DESC");

        $dqlParameters = array();

        //1) logged in user is requester (only if not admin)
        if(
            //!$this->secAuth->isGranted("ROLE_TRANSRES_ADMIN") &&
            //!$this->secAuth->isGranted("ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY")  &&
            //!$this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP')
            !$this->isAdminOrPrimaryReviewerOrExecutive() &&
            !$this->secAuth->isGranted("ROLE_TRANSRES_TECHNICIAN")
        ) {
            $myRequestProjectsCriterion =
                "principalInvestigators.id = :userId OR " .
                "principalIrbInvestigator.id = :userId OR " .
                "coInvestigators.id = :userId OR " .
                "pathologists.id = :userId OR " .
                "contacts.id = :userId OR " .
                "billingContact.id = :userId OR " .
                "submitter.id = :userId";

            $dqlParameters["userId"] = $user->getId();

            //2) logged in user is reviewer (only if not admin)
            $myReviewProjectsCriterion =
                "irbReviewer.id = :userId OR " .
                "irbReviewerDelegate.id = :userId OR " .

                "adminReviewer.id = :userId OR " .
                "adminReviewerDelegate.id = :userId OR " .

                "committeeReviewer.id = :userId OR " .
                "committeeReviewerDelegate.id = :userId OR " .

                "finalReviewer.id = :userId OR " .
                "finalReviewerDelegate.id = :userId";
            $dqlParameters["userId"] = $user->getId();

            $dql->where($myRequestProjectsCriterion . " OR " . $myReviewProjectsCriterion);

            $specialtyIds = array();
            $specialties = $this->getTransResProjectSpecialties();
            foreach ($specialties as $specialtyObject) {
                $partialRoleStr = $specialtyObject->getUppercaseName();
                if( $user->hasPartialRole($partialRoleStr) ) {
                    $specialtyIds[] = $specialtyObject->getId();
                }

                if( count($specialtyIds) > 0 ) {
                    $specialtyStr = "projectSpecialty.id IN (" . implode(",", $specialtyIds) . ")";
                    //echo "specialtyStr=$specialtyStr<br>";
                    $dql->andWhere($specialtyStr);
                } else {
                    $dql->andWhere("projectSpecialty.id IS NULL");
                }
            }
        } //if admin

        if( $search && $search != "prefetchmin" ) {
            if ($type == "oid") {
                $dql->andWhere("LOWER(project.oid) LIKE LOWER(:oid) OR CAST(project.id AS varchar) LIKE :oid");
                $dqlParameters["oid"] =  "%".$search."%";
            }
            if ($type == "title") {
                $dql->andWhere("LOWER(project.title) LIKE LOWER(:title)");
                $dqlParameters["title"] = "%".$search."%";
            }
            if ($type == "pis") {
                $dql->andWhere("LOWER(principalInvestigatorsInfos.displayName) LIKE LOWER(:pis)");
                $dqlParameters["pis"] = "%".$search."%";
            }
            if ($type == "all") {
                $dql->andWhere("LOWER(project.oid) LIKE LOWER(:search) OR CAST(project.id AS varchar) LIKE :search OR LOWER(project.title) LIKE LOWER(:search) OR LOWER(principalInvestigatorsInfos.displayName) LIKE LOWER(:search)");
                $dqlParameters["search"] =  "%".$search."%";
            }
        }

        //if( $limit ) {
        //    $query = $this->em->createQuery($dql)->setMaxResults($limit);
        //} else {
            $query = $dql->getQuery();
        //}

        if( $limit ) {
            $query->setMaxResults($limit);
        }

        //echo "projectId=".$project->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";
        //echo "query=".$query->getSql()."<br>";

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        //$query->setMaxResults(5);

        $projects = $query->getResult();
        //exit("projects count=".count($projects));

        return $projects;
    }

    public function copyFormNodeFieldsToProject( $project, $flushDb=true ) {
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');

        //update project's irbExpirationDate
        $projectIrbExpirationDate = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,$this->getHumanName()." Expiration Date");
        if( $projectIrbExpirationDate ) {
            $expDate = date_create_from_format('m/d/Y', $projectIrbExpirationDate);
            $project->setIrbExpirationDate($expDate);
        } else {
            $project->setIrbExpirationDate(null);
        }

        //update project's fundedAccountNumber
        $projectFundedAccountNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
        $project->setFundedAccountNumber($projectFundedAccountNumber);

        //update project's title
        $projectTitle = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Title");
        $project->setTitle($projectTitle);

        if( $flushDb ) {
            $this->em->flush($project);
        }
    }
    
    public function getProjectIdsFormNodeByFieldName( $search, $fieldName, $compareType="like" ) {
        $ids = array();
        if( !isset($search) ) {
            //echo "no search=".$search."<br>";
            return $ids;
        }
        //echo "search=".$search."<br>";

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $ids = array();

        //1) get formnode by category type name "Category Type" under formnode "HemePath Translational Research Request"->"Request"
        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents(
            $fieldName,
            "HemePath Translational Research",
            "HemePath Translational Research Project",
            "Project"
        );
        //echo "fieldFormNode=".$fieldFormNode->getId()."<br>";
        if( !$fieldFormNode ) {
            return array();
        }

        //2) get objectTypeDropdowns by:
        // value=$categoryType->getId(), entityNamespace="App\TranslationalResearchBundle\Entity" , entityName="TransResRequest"
        $mapper = array(
            "entityName" => "Project",
            "entityNamespace" => "App\\TranslationalResearchBundle\\Entity",
        );
        $objectTypeDropdowns = $formNodeUtil->getFormNodeListRecordsByReceivingObjectValue($fieldFormNode,$search,$mapper,$compareType);
        //echo "objectTypeDropdowns=".count($objectTypeDropdowns)."<br>";

        //3
        foreach($objectTypeDropdowns as $objectTypeDropdown) {
            //echo "id=".$objectTypeDropdown->getEntityId()."<br>";
            $ids[] = $objectTypeDropdown->getEntityId();
        }

        if( count($ids) == 0 ) {
            $ids[] = 0;
        }

        return $ids;
    }

    //test notations by http://127.0.0.1/order/index_dev.php/translational-research/email-notation-test
    public function replaceTextByNamingConvention( $text, $project, $transresRequest, $invoice ) {
        if( $project ) {
            $text = str_replace("[[PROJECT ID]]", $project->getOid(), $text);
            $text = str_replace("[[PROJECT ID TITLE]]", $project->getProjectIdTitle(), $text);
            $text = str_replace("[[PROJECT TITLE]]", $project->getTitle(), $text);

            $projectUpdater = $project->getUpdateUser();
            if( $projectUpdater ) {
                $text = str_replace("[[PROJECT UPDATER]]", $projectUpdater->getUsernameShortest(), $text);
                //$text = str_replace("[[PROJECT UPDATER]]", $projectUpdater."", $text);
            }

            if( strpos($text, '[[PROJECT UPDATE DATE]]') !== false ) {
                $projectUpdateDate = $project->getUpdateDate();
                if( $projectUpdateDate ) {
                    $user = $this->secTokenStorage->getToken()->getUser();
                    $userServiceUtil = $this->container->get('user_service_utility');
                    $projectUpdateDate = $userServiceUtil->convertFromUtcToUserTimezone($projectUpdateDate,$user);
                    $projectUpdateDateStr = $projectUpdateDate->format('m/d/Y \a\t H:i:s');
                    $text = str_replace("[[PROJECT UPDATE DATE]]", $projectUpdateDateStr, $text);
                }
            }

            if( strpos($text, '[[PROJECT TITLE SHORT]]') !== false ) {
                $title = $this->tokenTruncate($project->getTitle(), 15);
                $text = str_replace("[[PROJECT TITLE SHORT]]", $title, $text);
            }

            if( strpos($text, '[[PROJECT PIS]]') !== false ) {
                $pisArr = array();
                $pis = $project->getPrincipalInvestigators();
                foreach($pis as $pi) {
                    $pisArr[] = $pi->getUsernameShortest();
                    //$pisArr[] = $pi."";
                }
                $text = str_replace("[[PROJECT PIS]]", implode(", ",$pisArr), $text);
            }

            $createDateStr = null;
            $createDate = $project->getCreateDate();
            if( $createDate ) {
                $createDateStr = $createDate->format('m/d/Y');
                $text = str_replace("[[PROJECT SUBMISSION DATE]]", $createDateStr, $text);
            }

            if( strpos($text, '[[PROJECT STATUS]]') !== false ) {
                $state = $this->getStateLabelByProject($project);
                $text = str_replace("[[PROJECT STATUS]]", $state, $text);
            }

            if( strpos($text, '[[PROJECT STATUS COMMENTS]]') !== false ) {
                //$project,$newline="<br>",$state=null,$user=null
                $reviewComments = $this->getReviewComments($project,"<hr>");
                if( $reviewComments ) {
                    $reviewComments = "<hr>" . $reviewComments;
                } else {
                    $reviewComments = "No comments";
                }
                $text = str_replace("[[PROJECT STATUS COMMENTS]]", $reviewComments, $text);
            }

            if( strpos($text, '[[PROJECT SHOW URL]]') !== false ) {
                $projectShowUrl = $this->getProjectShowUrl($project);
                if ($projectShowUrl) {
                    //echo "Project URL=".$projectShowUrl."\n";
                    $text = str_replace("[[PROJECT SHOW URL]]", $projectShowUrl, $text);
                }
            }

            if( strpos($text, '[[PROJECT EDIT URL]]') !== false ) {
                $projectEditUrl = $this->getProjectEditUrl($project);
                if ($projectEditUrl) {
                    $text = str_replace("[[PROJECT EDIT URL]]", $projectEditUrl, $text);
                }
            }

            if( strpos($text, '[[PROJECT PATHOLOGIST LIST]]') !== false ) {
                $pisArr = array();
                $pis = $project->getPathologists();
                foreach($pis as $pi) {
                    $pisArr[] = $pi->getUsernameShortest();
                    //$pisArr[] = $pi."";
                }

                if( count($pisArr) > 0 ) {
                    $pathologists = implode(", ",$pisArr);
                } else {
                    $pathologists = "no pathologists";
                }

                $text = str_replace("[[PROJECT PATHOLOGIST LIST]]", $pathologists, $text);
            }

            if( strpos($text, '[[PROJECT BILLING CONTACT LIST]]') !== false ) {
                $billingContact = $project->getBillingContact();

                if( !$billingContact ) {
                    $billingContact = "No Billing Contact";
                }

                $text = str_replace("[[PROJECT BILLING CONTACT LIST]]", $billingContact."", $text);
            }

            if( strpos($text, '[[PROJECT REQUESTS URL]]') !== false ) {
                $linkRequestsForThisProject = $this->container->get('router')->generate(
                    'translationalresearch_request_index',
                    array(
                        'id' => $project->getId(),
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $linkRequestsForThisProject = '<a href="'.$linkRequestsForThisProject.'">'.$linkRequestsForThisProject.'</a>';

                $text = str_replace("[[PROJECT REQUESTS URL]]", $linkRequestsForThisProject, $text);
            }

            if( strpos($text, '[[PROJECT NON-CANCELED INVOICES URL]]') !== false ) {
//                $linkMyInvoices = $this->container->get('router')->generate(
//                    'translationalresearch_invoice_index_type',
//                    array(
//                        'invoicetype' => "Latest Versions of All Invoices Except Canceled",
//                    ),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );

                $linkMyInvoices = $this->container->get('router')->generate(
                    'translationalresearch_invoice_index_filter',
                    array(
                        'filter[idSearch]' => $project->getOid(),
                        'filter[version]' => "Latest",
                        'filter[status][]' => "Latest Versions of All Invoices Except Canceled",
                        //'title' => $invoicetype,
                        //'filterwell' => 'closed'
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $linkMyInvoices = '<a href="'.$linkMyInvoices.'">'.$linkMyInvoices.'</a>';

                $text = str_replace("[[PROJECT NON-CANCELED INVOICES URL]]", $linkMyInvoices, $text);
            }

            //Budget
            if( strpos($text, '[[PROJECT PRICE LIST]]') !== false ) {
                $priceList = $project->getPriceList();
                if( $priceList ) {
                    $priceListStr = "'".$priceList->getName()."'";
                } else {
                    //$priceListStr = "Default";
                    $priceListStr = "";
                }
                $text = str_replace("[[PROJECT PRICE LIST]]", $priceListStr, $text);
            }

            if( strpos($text, '[[PROJECT APPROVED BUDGET]]') !== false ) {
                $approvedBudget = $project->getApprovedProjectBudget();
                if( !$approvedBudget ) {
                    $approvedBudget = 0;
                }
                $approvedBudgetStr = $this->dollarSignValue($approvedBudget);
                $text = str_replace("[[PROJECT APPROVED BUDGET]]", $approvedBudgetStr, $text);
            }

            if( strpos($text, '[[PROJECT REMAINING BUDGET]]') !== false ) {
                $remainingBudget = $project->getRemainingBudget();
                if( !$remainingBudget ) {
                    $remainingBudget = 0;
                }
                $remainingBudgetStr = $this->dollarSignValue($remainingBudget);
                $text = str_replace("[[PROJECT REMAINING BUDGET]]", $remainingBudgetStr, $text);
            }

            //[[PROJECT OVER BUDGET]] the same as negative project remaining budget
            if( strpos($text, '[[PROJECT OVER BUDGET]]') !== false ) {
                $remainingBudget = $project->getRemainingBudget();
                if( $remainingBudget < 0 ) {
                    $remainingBudgetStr = $this->dollarSignValue($remainingBudget);
                } else {
                    $remainingBudgetStr = "'No Over Budget'";
                }
                $text = str_replace("[[PROJECT OVER BUDGET]]", $remainingBudgetStr, $text);
            }

            if( strpos($text, '[[PROJECT SUBSIDY]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true);
                $subsidy = $invoicesInfos['subsidy'];
                if( !$subsidy ) {
                    $subsidy = 0;
                }
                $subsidy = $this->dollarSignValue($subsidy);
                $text = str_replace("[[PROJECT SUBSIDY]]", $subsidy, $text);
            }

            if( strpos($text, '[[PROJECT VALUE]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true);
                $grandTotal = $invoicesInfos['grandTotal']; //grand total including subsidy
                if( !$grandTotal ) {
                    $grandTotal = 0;
                }
                $grandTotal = $this->dollarSignValue($grandTotal);
                $text = str_replace("[[PROJECT VALUE]]", $grandTotal, $text);
            }

            if( strpos($text, '[[PROJECT FUNDED]]') !== false ) {
                $isFunded = $project->isFunded(); //"Funded" or "Non-funded"
                $isFunded = strtolower($isFunded);
                $text = str_replace("[[PROJECT FUNDED]]", $isFunded, $text);
            }

            if( strpos($text, '[[PROJECT NUMBER INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.count
                $invoiceCount = $invoicesInfos['count'];
                if( !$invoiceCount ) {
                    $invoiceCount = 0;
                }
                $text = str_replace("[[PROJECT NUMBER INVOICES]]", $invoiceCount, $text);
            }

            if( strpos($text, '[[PROJECT NUMBER PAID INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.paidCount
                $invoiceCount = $invoicesInfos['paidCount'];
                if( !$invoiceCount ) {
                    $invoiceCount = 0;
                }
                $text = str_replace("[[PROJECT NUMBER PAID INVOICES]]", $invoiceCount, $text);
            }

            if( strpos($text, '[[PROJECT AMOUNT PAID INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.paidAmount
                $paidAmount = $invoicesInfos['paidAmount'];
                if( !$paidAmount ) {
                    $paidAmount = 0;
                }
                $paidAmount = $this->dollarSignValue($paidAmount);
                $text = str_replace("[[PROJECT AMOUNT PAID INVOICES]]", $paidAmount, $text);
            }

            if( strpos($text, '[[PROJECT NUMBER OUTSTANDING INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.outstandingCount
                $invoiceCount = $invoicesInfos['outstandingCount'];
                if( !$invoiceCount ) {
                    $invoiceCount = 0;
                }
                $text = str_replace("[[PROJECT NUMBER OUTSTANDING INVOICES]]", $invoiceCount, $text);
            }

            if( strpos($text, '[[PROJECT AMOUNT OUTSTANDING INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.paidAmount
                $outstandingAmount = $invoicesInfos['outstandingAmount'];
                if( !$outstandingAmount ) {
                    $outstandingAmount = 0;
                }
                $outstandingAmount = $this->dollarSignValue($outstandingAmount);
                $text = str_replace("[[PROJECT AMOUNT OUTSTANDING INVOICES]]", $outstandingAmount, $text);
            }

            if( strpos($text, '[[PROJECT VALUE WITHOUT INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.grandTotalWithoutInvoices
                $grandTotalWithoutInvoices = $invoicesInfos['grandTotalWithoutInvoices'];
                if( !$grandTotalWithoutInvoices ) {
                    $grandTotalWithoutInvoices = 0;
                }
                $grandTotalWithoutInvoices = $this->dollarSignValue($grandTotalWithoutInvoices);
                $text = str_replace("[[PROJECT VALUE WITHOUT INVOICES]]", $grandTotalWithoutInvoices, $text);
            }

        }//project

        if( $transresRequest ) {
            $text = str_replace("[[REQUEST ID]]", $transresRequest->getOid(), $text);

            $creationDate = $transresRequest->getCreateDate();
            if( $creationDate ) {
                $text = str_replace("[[REQUEST SUBMISSION DATE]]", $creationDate->format("m/d/Y"), $text);
            }

            $submitter = $transresRequest->getSubmitter();
            if( $submitter ) {
                $text = str_replace("[[REQUEST SUBMITTER]]", $submitter->getUsernameShortest(), $text);
            }

            if( strpos($text, '[[REQUEST UPDATE DATE]]') !== false ) {
                $updateDate = $transresRequest->getUpdateDate();
                if ($updateDate) {
                    $text = str_replace("[[REQUEST UPDATE DATE]]", $updateDate->format("m/d/Y"), $text);
                }
            }

            if( strpos($text, '[[REQUEST PROGRESS STATUS]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $state = $transresRequest->getProgressState();
                $state = $transresRequestUtil->getProgressStateLabelByName($state);
                $text = str_replace("[[REQUEST PROGRESS STATUS]]", $state, $text);
            }

            if( strpos($text, '[[REQUEST BILLING STATUS]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $state = $transresRequest->getBillingState();
                $state = $transresRequestUtil->getBillingStateLabelByName($state);
                $text = str_replace("[[REQUEST BILLING STATUS]]", $state, $text);
            }

            if( strpos($text, '[[REQUEST SHOW URL]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $requestShowUrl = $transresRequestUtil->getRequestShowUrl($transresRequest);
                if ($requestShowUrl) {
                    $text = str_replace("[[REQUEST SHOW URL]]", $requestShowUrl, $text);
                }
            }

            if( strpos($text, '[[REQUEST CHANGE PROGRESS STATUS URL]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $requestChangeProgressStatusUrl = $transresRequestUtil->getRequestChangeProgressStateUrl($transresRequest);
                if ($requestChangeProgressStatusUrl) {
                    $text = str_replace("[[REQUEST CHANGE PROGRESS STATUS URL]]", $requestChangeProgressStatusUrl, $text);
                }
            }

            if( strpos($text, '[[REQUEST NEW INVOICE URL]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $requestNewInvoiceUrl = $transresRequestUtil->getRequestNewInvoiceUrl($transresRequest);
                if ($requestNewInvoiceUrl) {
                    $text = str_replace("[[REQUEST NEW INVOICE URL]]", $requestNewInvoiceUrl, $text);
                }
            }

            if( strpos($text, '[[REQUEST VALUE]]') !== false ) {
                $invoicesInfos = $transresRequest->getInvoicesInfosByRequest(true);
                $grandTotal = $invoicesInfos['grandTotal']; //grand total including subsidy
                if( !$grandTotal ) {
                    $grandTotal = 0;
                }
                $grandTotal = $this->dollarSignValue($grandTotal);
                $text = str_replace("[[REQUEST VALUE]]", $grandTotal, $text);
            }

        }//$transresRequest

        if( $invoice ) {
            $text = str_replace("[[INVOICE ID]]", $invoice->getOid(), $text);

            //[[INVOICE DUE DATE AND DAYS AGO]]
            $dueDateStr = $invoice->getDueAndDaysStr();
            $text = str_replace("[[INVOICE DUE DATE AND DAYS AGO]]", $dueDateStr, $text);

            //[[INVOICE AMOUNT DUE]]
            $text = str_replace("[[INVOICE AMOUNT DUE]]", $invoice->getDue(), $text);

            if( strpos($text, '[[INVOICE SHOW URL]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $invoiceShowUrl = $transresRequestUtil->getInvoiceShowUrl($invoice);
                if ($invoiceShowUrl) {
                    $text = str_replace("[[INVOICE SHOW URL]]", $invoiceShowUrl, $text);
                }
            }
            
        }//$invoice

        return $text;
    }
//    function daysElapsedString($agoDateTime) {
//        $now = new \DateTime();
//        $diff = $now->diff($agoDateTime);
//        $days = $diff->format("%a");
//        if( $days ) {
//            return $days . " days ago";
//        } else {
//            return "just now";
//        }
//    }


//    //get Issued Invoices
//    public function getInvoicesInfosByProject_ORIG($project) {
//        $transresRequestUtil = $this->container->get('transres_request_util');
//        $invoicesInfos = array();
//        $count = 0;
//        $total = 0.00;
//        $paid = 0.00;
//        $due = 0.00;
//        $subsidy = 0.00;
//        $countRequest = 0;
//        $grandTotal = 0.00;
//
//        foreach($project->getRequests() as $request) {
//            $res = $transresRequestUtil->getInvoicesInfosByRequest($request);
//            $count = $count + $res['count'];
//            $total = $total + $res['total'];
//            $paid = $paid + $res['paid'];
//            $due = $due + $res['due'];
//            $subsidy = $subsidy + $res['subsidy'];
//            $grandTotal = $grandTotal + $res['grandTotal'];
//            $countRequest++;
//        }
//        //echo $project->getOid().": countRequest=$countRequest: ";
//
//        if( $count > 0 && $countRequest > 0 ) {
//            //if ($total > 0) {
//                $total = $transresRequestUtil->toDecimal($total);
//            //}
//            //if ($paid > 0) {
//                $paid = $transresRequestUtil->toDecimal($paid);
//            //}
//            //if ($due > 0) {
//                $due = $transresRequestUtil->toDecimal($due);
//            //}
//            //if ($subsidy > 0) {
//                $subsidy = $transresRequestUtil->toDecimal($subsidy);
//            //}
//
//            //if( $grandTotal > 0 ) {
//                $grandTotal = $transresRequestUtil->toDecimal($grandTotal);
//            //}
//
//            //echo "value<br>";
//        } else {
//            //echo "total=$total<br>";
//            $total = null;
//            $paid = null;
//            $due = null;
//            $subsidy = null;
//            $grandTotal = null;
//        }
//        //echo "total=$total<br>";
//
//        //$this->toDecimal
//
//
//        $invoicesInfos['count'] = $count;
//        $invoicesInfos['total'] = $total; //charge
//        $invoicesInfos['paid'] = $paid;
//        $invoicesInfos['due'] = $due;
//        $invoicesInfos['subsidy'] = $subsidy;
//        $invoicesInfos['grandTotal'] = $grandTotal; //grand total including subsidy
//
//        return $invoicesInfos;
//    }
    public function getInvoicesInfosByProject($project) {
        $admin = false;
        $transresRequestUtil = $this->container->get('transres_request_util');
        if( $transresRequestUtil->isUserHasInvoicePermission($invoice = NULL, "update") ) {
            $admin = true;
        }
        return $project->getInvoicesInfosByProject($admin);
    }

//    public function getTotalFeesByQuantity($fee,$feeAdditionalItem,$quantity) {
//        $quantity = intval($quantity);
//        $fee = intval($fee);
//        $feeAdditionalItem = intval($feeAdditionalItem);
//        $total = 0;
//        if( $quantity == 1 ) {
//            $total = $quantity * $fee;
//        } elseif ( $quantity > 1 ) {
//            $total = 1 * $fee;
//            $additionalFee = ($quantity-1) * $feeAdditionalItem;
//            $total = $total + $additionalFee;
//        }
//        return $total;
//    }

    //Create spreadsheet by Spout
    //http://opensource.box.com/spout/getting-started/
    //https://hotexamples.com/examples/box.spout.writer/WriterFactory/-/php-writerfactory-class-examples.html
    public function createProjectExcelSpout($projectIdsArr,$fileName,$limit=null) {
        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresPermissionUtil  = $this->container->get('transres_permission_util');

        //$writer = WriterFactory::create(Type::CSV);
        //$writer = WriterFactory::create(Type::XLSX);
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser($fileName);

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            //->setFontItalic()
            ->setFontSize(12)
            ->setFontColor(Color::BLACK)
            ->setShouldWrapText()
            ->setBackgroundColor(Color::toARGB("E0E0E0"))
            ->build();

        $requestStyle = (new StyleBuilder())
            ->setFontSize(10)
            //->setShouldWrapText()
            ->build();

        $border = (new BorderBuilder())
            ->setBorderBottom(Color::GREEN, Border::WIDTH_THIN, Border::STYLE_DASHED)
            ->build();
        $footerStyle = (new StyleBuilder())
            ->setFontBold()
            //->setFontItalic()
            ->setFontSize(12)
            ->setFontColor(Color::BLACK)
            ->setShouldWrapText()
            ->setBackgroundColor(Color::toARGB("EBF1DE"))
            ->setBorder($border)
            ->build();

//        $writer->addRowWithStyle(
//            [
//                'Project ID',                   //0 - A
//                'Submission Date',              //1 - B
//                'Principal Investigator(s)',    //2 - C
//                'Project Title',                //3 - D
//                'Funding',                      //4 - E
//                'Status',                       //5 - F
//                'Approval Date',                //6 - G
//                $this->getHumanAnimalName().' Expiration Date', //7 - H
//                'Request ID',                   //8 - I
//                'Fund Number',                  //9 - J
//                'Completion Status',            //10 - K
//                'Invoice(s) Issued',            //11 - L
//                'Most Recent Invoice Total($)', //12 - M
//                'Most Recent Invoice Paid($)',  //13 - N
//                'Most Recent Invoice Due($)',   //14 - O
//                'Most Recent Invoice Comment'   //15 - P
//            ],
//            $headerStyle
//        );
        $spoutRow = WriterEntityFactory::createRowFromArray(
            [
                'Project ID',                   //0 - A
                'Submission Date',              //1 - B
                'Principal Investigator(s)',    //2 - C
                'Project Title',                //3 - D
                'Funding',                      //4 - E
                'Status',                       //5 - F
                'Approval Date',                //6 - G
                $this->getHumanAnimalName().' Expiration Date', //7 - H
                'Request ID',                   //8 - I
                'Fund Number',                  //9 - J
                'Completion Status',            //10 - K
                'Invoice(s) Issued',            //11 - L
                'Most Recent Invoice Total($)', //12 - M
                'Most Recent Invoice Paid($)',  //13 - N
                'Most Recent Invoice Due($)',   //14 - O
                'Most Recent Invoice Comment'   //15 - P
            ],
            $headerStyle
        );
        $writer->addRow($spoutRow);

//        foreach( $projectIdsArr as $projectId ) {
//            $data[0] = 1;
//            $data[1] = 2;
//            $data[2] = 3;
//            $writer->addRow($data);
//        }

        $rowCount = 2;
        $count = 0;
        $totalRequests = 0;
        $totalInvoices = 0;
        $totalTotal = 0;
        $paidTotal = 0;
        $dueTotal = 0;

        foreach( $projectIdsArr as $projectId ) {

            if( $limit && ($count++ > $limit) ) {
                break;
            }

            $project = $this->em->getRepository('AppTranslationalResearchBundle:Project')->find($projectId);
            if( !$project ) {
                continue;
            }

//            if( $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//                continue;
//            }
            if( $transresPermissionUtil->hasProjectPermission("view",$project) === false ) {
                continue;
            }

            //$ews = $this->fillOutProjectCellsSpout($writer,$data,$project); //0-7

            $projectRequests = 0;
            $projectTotalInvoices = 0;
            $projectTotalTotal = 0;
            $projectTotalPaid = 0;
            $projectTotalDue = 0;

            //$workRequests = $project->getRequests();
            $workRequests = $transresRequestUtil->getProjectMiniRequests($projectId);
            foreach($workRequests as $request) {

                //print_r($request);
                //exit('111');
                //$oid = $request['oid'];
                //$data = array();

                //$data = $this->fillOutProjectCellsSpout($writer,$project); //0-7
                $data[0] = $project->getOid();
                $data[1] = null;
                $data[2] = null;
                $data[3] = null;
                $data[4] = null;
                $data[5] = null;
                $data[6] = null;
                $data[7] = null;

                //Request ID
                //$ews->setCellValue('I'.$rowCount, $request->getOid());
                //$data[8] = $request->getOid();
                $data[8] = $request['oid'];

                //Funding Number
                //$ews->setCellValue('J'.$rowCount, $request->getFundedAccountNumber());
                //$data[9] = $request->getFundedAccountNumber();
                $data[9] = $request['fundedAccountNumber'];

                //Completion Status
                //$ews->setCellValue('K'.$rowCount, $transresRequestUtil->getProgressStateLabelByName($request->getProgressState()));
                //$data[10] = $transresRequestUtil->getProgressStateLabelByName($request->getProgressState());
                $data[10] = $transresRequestUtil->getProgressStateLabelByName($request['progressState']);

                //Invoice(s) Issued (Latest)
                $latestInvoice = $transresRequestUtil->getLatestInvoice(null,$request['id']);
                //$latestInvoice = $request->getLatestInvoice();
                //$latestInvoice = null;
                //$latestInvoicesCount = count($request->getInvoices());
                $latestInvoicesCount = 0;
                if( $latestInvoice ) {
                    $latestInvoicesCount = 1;
                    $totalInvoices++;
                    $projectTotalInvoices++;
                }
                //$ews->setCellValue('L'.$rowCount, $latestInvoicesCount);
                $data[11] = $latestInvoicesCount;

                if( $latestInvoice ) {
                    //# Total($)
                    $total = $latestInvoice->getTotal();
                    $totalTotal = $totalTotal + $total;
                    $projectTotalTotal = $projectTotalTotal + $total;
                    //if ($total) {
                        //$ews->setCellValue('M' . $rowCount, $total);
                    $total = "$".$total;
                    $data[12] = $total;
                    //}

                    //# Paid($)
                    $paid = $latestInvoice->getPaid();
                    $paidTotal = $paidTotal + $paid;
                    $projectTotalPaid = $projectTotalPaid + $paid;
                    //if ($paid) {
                        //$ews->setCellValue('N' . $rowCount, $paid);
                    if(!$paid) {
                        $paid = 0;
                    }
                    $paid = "$".$paid;
                    $data[13] = $paid;

                    //}

                    //# Due($)
                    $due = $latestInvoice->getDue();
                    $dueTotal = $dueTotal + $due;
                    $projectTotalDue = $projectTotalDue + $due;
                    //if ($due) {
                        //$ews->setCellValue('O' . $rowCount, $due);

                    $due = "$".$due;
                    $data[14] = $due;
                    //}

                    //Comment
                    $comment = $latestInvoice->getComment();
                    //if( $comment ) {
                        //$ews->setCellValue('P' . $rowCount, $comment);
                        //$ews->getStyle('P' . $rowCount)
                        //    ->getAlignment()->setWrapText(true);
                        $data[15] = $comment;
                    //}
                } else {
                    $data[12] = null;
                    $data[13] = null;
                    $data[14] = null;
                    $data[15] = null;
                }

                $projectRequests = $projectRequests + 1;

                //$writer->addRowWithStyle($data,$requestStyle);
                $spoutRow = WriterEntityFactory::createRowFromArray($data, $requestStyle);
                $writer->addRow($spoutRow);

                $rowCount = $rowCount + 1;
            }//foreach request

            $totalRequests = $totalRequests + $projectRequests;

            //$data = array();
            $data = $this->fillOutProjectCellsSpout($writer,$project); //0-7

            //Request Total
            //$ews->setCellValue('I'.$rowCount, "Project Totals");
            //$ews->getStyle('I'.$rowCount)->applyFromArray($styleBoldArray);
            $data[8] = "Project Totals";

            //Empty 9-J, 10-K
            $data[9] = null;
            $data[10] = null;

            //This Project Total Invoices
            //$ews->setCellValue('L'.$rowCount, $projectTotalInvoices);
            //$ews->getStyle('L'.$rowCount)->applyFromArray($styleBoldArray);
            $data[11] = $projectTotalInvoices;

            //This Project Total Total
            //$ews->setCellValue('M'.$rowCount, $projectTotalTotal);
            //$ews->getStyle('M'.$rowCount)->applyFromArray($styleBoldArray);
            //if( $projectTotalTotal ) {
                $projectTotalTotal = "$".$projectTotalTotal;
            //}
            $data[12] = $projectTotalTotal;

            //This Project Total Paid
            //$ews->setCellValue('N'.$rowCount, $projectTotalPaid);
            //$ews->getStyle('N'.$rowCount)->applyFromArray($styleBoldArray);
            //if( $projectTotalPaid ) {
                $projectTotalPaid = "$".$projectTotalPaid;
            //}
            $data[13] = $projectTotalPaid;

            //This Project Total Due
            //$ews->setCellValue('O'.$rowCount, $projectTotalDue);
            //$ews->getStyle('O'.$rowCount)->applyFromArray($styleBoldArray);
            //if( $projectTotalDue ) {
                $projectTotalDue = "$".$projectTotalDue;
            //}
            $data[14] = $projectTotalDue;

            //set color light green to the last Total row
            //$ews->getStyle('A'.$rowCount.':'.'P'.$rowCount)->applyFromArray($styleLastRow);

            $rowCount = $rowCount + 1;

            //$writer->addRowWithStyle($data,$footerStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
            $writer->addRow($spoutRow);

            $this->em->clear();
        }//projects

        $writer->close();
    }
    //use https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/
    public function createProjectListExcelSheets($projectIdsArr,$limit=null)
    {

        $transresRequestUtil = $this->container->get('transres_request_util');
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');

        $author = $this->container->get('security.token_storage')->getToken()->getUser();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

        //TODO:
        //https://phpspreadsheet.readthedocs.io/en/develop/topics/memory_saving/
        // $cache = new MyCustomPsr16Implementation();
        // use Symfony\Component\Cache\Simple\FilesystemCache;
        // $cache = new FilesystemCache();
        //\PhpOffice\PhpSpreadsheet\Settings::setCache($cache);

        $spreadsheet = new Spreadsheet(); // ea is short for Excel Application

        $spreadsheet->getProperties()
            ->setCreator($author . "")
            ->setTitle('Projects')
            ->setLastModifiedBy($author . "")
            ->setDescription('Projects list in Excel format')
            ->setSubject('PHP Excel manipulation')
            ->setKeywords('excel php office phpexcel lakers')
            ->setCategory('programming');

        $chunkSize = 100;
        if( count($projectIdsArr) > $chunkSize ) {
            $projectIdsChunkArr = array_chunk($projectIdsArr,$chunkSize);
            $counter = 0;
            foreach( $projectIdsChunkArr as $projectIds ) {
                // Create a new worksheet called "My Data"
                $counterTitle = $counter+1;
                $title = "Projects (".$counterTitle." ".($chunkSize*$counterTitle).")";
                $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet,$title);
                // Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
                $spreadsheet->addSheet($myWorkSheet,$counter);

                $myWorkSheet = $this->createProjectSheet($myWorkSheet,$projectIds);

                $counter++;
            }
        }

        return $spreadsheet;
    }
    public function createProjectSheet($ews,$projectIdsArr,$limit=null) {

        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresPermissionUtil  = $this->container->get('transres_permission_util');

        //align all cells to left
        $style = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            )
        );
        //$ews->getDefaultStyle()->applyFromArray($style);
        $ews->getParent()->getDefaultStyle()->applyFromArray($style);

        $styleBoldArray = array(
            'font' => array(
                'bold' => true
            )
        );
        $styleLastRow = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'ebf1de',
                ],
                'endColor' => [
                    'argb' => 'ebf1de',
                ],
            ],
        ];

        $ews->setCellValue('A1', 'Project ID'); // Sets cell 'a1' to value 'ID
        $ews->setCellValue('B1', 'Submission Date');
        $ews->setCellValue('C1', 'Principal Investigator(s)');
        $ews->setCellValue('D1', 'Project Title');
        $ews->setCellValue('E1', 'Funding');
        $ews->setCellValue('F1', 'Status');
        $ews->setCellValue('G1', 'Approval Date');
        $ews->setCellValue('H1', $this->getHumanAnimalName().' Expiration Date');

        $ews->setCellValue('I1', 'Request ID');

        $ews->setCellValue('J1', 'Fund Number');
        $ews->setCellValue('K1', 'Completion Status');

        $ews->setCellValue('L1', 'Invoice(s) Issued');
        $ews->setCellValue('M1', 'Most Recent Invoice Total($)');
        $ews->setCellValue('N1', 'Most Recent Invoice Paid($)');
        $ews->setCellValue('O1', 'Most Recent Invoice Due($)');
        $ews->setCellValue('P1', 'Most Recent Invoice Comment');

        $ews->getStyle('A1:P1')->applyFromArray($styleBoldArray);

        $count = 0;
        $totalRequests = 0;
        $totalInvoices = 0;
        $totalTotal = 0;
        $paidTotal = 0;
        $dueTotal = 0;

        $rowCount = 2;
        foreach( $projectIdsArr as $projectId ) {

            if( $limit && ($count++ > $limit) ) {
                break;
            }

            $project = $this->em->getRepository('AppTranslationalResearchBundle:Project')->find($projectId);
            if( !$project ) {
                continue;
            }

//            if( $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//                continue;
//            }
            if( $transresPermissionUtil->hasProjectPermission("view",$project) === false ) {
                continue;
            }

            //$ews = $this->fillOutProjectCells($ews,$rowCount,$project);

            $projectRequests = 0;
            $projectTotalInvoices = 0;
            $projectTotalTotal = 0;
            $projectTotalPaid = 0;
            $projectTotalDue = 0;

            foreach($project->getRequests() as $request) {

                //$ews = $this->fillOutProjectCells($ews,$rowCount,$project);
                $ews->setCellValue('A'.$rowCount, $project->getOid()); //set just project ID

                //Request ID
                $ews->setCellValue('I'.$rowCount, $request->getOid());

                //Funding Number
                $ews->setCellValue('J'.$rowCount, $request->getFundedAccountNumber());

                //Completion Status
                $ews->setCellValue('K'.$rowCount, $transresRequestUtil->getProgressStateLabelByName($request->getProgressState()));

                //Invoice(s) Issued (Latest)
                $latestInvoice = $transresRequestUtil->getLatestInvoice($request);
                //$latestInvoicesCount = count($request->getInvoices());
                $latestInvoicesCount = 0;
                if( $latestInvoice ) {
                    $latestInvoicesCount = 1;
                    $totalInvoices++;
                    $projectTotalInvoices++;
                }
                $ews->setCellValue('L'.$rowCount, $latestInvoicesCount);

                if( $latestInvoice ) {
                    //# Total($)
                    $total = $latestInvoice->getTotal();
                    $totalTotal = $totalTotal + $total;
                    $projectTotalTotal = $projectTotalTotal + $total;
                    if ($total) {
                        $ews->setCellValue('M' . $rowCount, $total);
                    }

                    //# Paid($)
                    $paid = $latestInvoice->getPaid();
                    $paidTotal = $paidTotal + $paid;
                    $projectTotalPaid = $projectTotalPaid + $paid;
                    if ($paid) {
                        $ews->setCellValue('N' . $rowCount, $paid);

                    }

                    //# Due($)
                    $due = $latestInvoice->getDue();
                    $dueTotal = $dueTotal + $due;
                    $projectTotalDue = $projectTotalDue + $due;
                    if ($due) {
                        $ews->setCellValue('O' . $rowCount, $due);
                    }

                    //Comment
                    $comment = $latestInvoice->getComment();
                    if( $comment ) {
                        $ews->setCellValue('P' . $rowCount, $comment);
                        $ews->getStyle('P' . $rowCount)
                            ->getAlignment()->setWrapText(true);
                    }
                }

                $projectRequests = $projectRequests + 1;

                $rowCount = $rowCount + 1;
            }

            $totalRequests = $totalRequests + $projectRequests;

            $ews = $this->fillOutProjectCells($ews,$rowCount,$project);

            //Request Total
            $ews->setCellValue('I'.$rowCount, "Project Totals");
            $ews->getStyle('I'.$rowCount)->applyFromArray($styleBoldArray);

            //This Project Total Invoices
            $ews->setCellValue('L'.$rowCount, $projectTotalInvoices);
            $ews->getStyle('L'.$rowCount)->applyFromArray($styleBoldArray);

            //This Project Total Total
            $ews->setCellValue('M'.$rowCount, $projectTotalTotal);
            $ews->getStyle('M'.$rowCount)->applyFromArray($styleBoldArray);

            //This Project Total Paid
            $ews->setCellValue('N'.$rowCount, $projectTotalPaid);
            $ews->getStyle('N'.$rowCount)->applyFromArray($styleBoldArray);

            //This Project Total Due
            $ews->setCellValue('O'.$rowCount, $projectTotalDue);
            $ews->getStyle('O'.$rowCount)->applyFromArray($styleBoldArray);

            //set color light green to the last Total row
            $ews->getStyle('A'.$rowCount.':'.'P'.$rowCount)->applyFromArray($styleLastRow);

            $rowCount = $rowCount + 1;

            $this->em->clear();
        }//projects

        //Total
        //$rowCount++;
        $ews->setCellValue('H'.$rowCount, "Totals");
        $ews->getStyle('H'.$rowCount)->applyFromArray($styleBoldArray);
        //Requests total
        $ews->setCellValue('I' . $rowCount, $totalRequests);
        $ews->getStyle('I'.$rowCount)->applyFromArray($styleBoldArray);
        //Invoices total
        $ews->setCellValue('L'.$rowCount, $totalInvoices);
        $ews->getStyle('L'.$rowCount)->applyFromArray($styleBoldArray);
        //Total total
        if( $totalTotal > 0 ) {
            $ews->setCellValue('M' . $rowCount, $totalTotal);
            $ews->getStyle('M'.$rowCount)->applyFromArray($styleBoldArray);
        }
        //Paid total
        if( $paidTotal > 0 ) {
            $ews->setCellValue('N' . $rowCount, $paidTotal);
            $ews->getStyle('N'.$rowCount)->applyFromArray($styleBoldArray);
        }
        //Due total
        if( $dueTotal > 0 ) {
            $ews->setCellValue('O' . $rowCount, $dueTotal);
            $ews->getStyle('O'.$rowCount)->applyFromArray($styleBoldArray);
        }

        //format columns to currency format 2:$rowCount
        $ews->getStyle('M2:M'.$rowCount)
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $ews->getStyle('N2:N'.$rowCount)
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $ews->getStyle('O2:O'.$rowCount)
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        //exit("ids=".$fellappids);


        // Auto size columns for each worksheet
        //\PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        $autosize = true;
        $autosize = false;
        if( $autosize ) {
            $cellIterator = $ews->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            /** @var PHPExcel_Cell $cell */
            foreach ($cellIterator as $cell) {
                $ews->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }


        return $ews;
    }
    //use https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/
    public function createProjectListExcel($projectIdsArr,$limit=null) {

        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');

        $author = $this->container->get('security.token_storage')->getToken()->getUser();
        //$transformer = new DateTimeToStringTransformer(null,null,'d/m/Y');

        //TODO:
        //https://phpspreadsheet.readthedocs.io/en/develop/topics/memory_saving/
        // $cache = new MyCustomPsr16Implementation();
        //
        // use Symfony\Component\Cache\Simple\FilesystemCache;
        // $cache = new FilesystemCache();
        //$cache = new FilesystemCache();
        // Via factory:
//        $cache = StorageFactory::factory([
//            'adapter' => [
//                'name'    => 'apc',
//                'options' => ['ttl' => 3600],
//            ],
//            'plugins' => [
//                'exception_handler' => ['throw_exceptions' => false],
//            ],
//        ]);

        //$cache = new ApcuCache();

        //composer require cache/simple-cache-bridge cache/apcu-adapter
        //$pool = new \Cache\Adapter\Apcu\ApcuCachePool();
        //$cache = new \Cache\Bridge\SimpleCache\SimpleCacheBridge($pool);

        //\PhpOffice\PhpSpreadsheet\Settings::setCache($cache);

        $ea = new Spreadsheet(); // ea is short for Excel Application

        $ea->getProperties()
            ->setCreator($author."")
            ->setTitle('Projects')
            ->setLastModifiedBy($author."")
            ->setDescription('Projects list in Excel format')
            ->setSubject('PHP Excel manipulation')
            ->setKeywords('excel php office phpexcel lakers')
            ->setCategory('programming')
        ;

        $ews = $ea->getSheet(0);
        $ews->setTitle('Projects');

        //align all cells to left
        $style = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            )
        );
        //$ews->getDefaultStyle()->applyFromArray($style);
        $ews->getParent()->getDefaultStyle()->applyFromArray($style);

        $styleBoldArray = array(
            'font' => array(
                'bold' => true
            )
        );

//        $styleLastRow = array(
//            'fill' => array(
//                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
//                'color' => array('rgb' => 'ebf1de')
//            ),
//            'borders' => array(
////                'bottom' => array(
////                    'style' => \PHPExcel_Style_Border::BORDER_THIN
////                ),
//                'allborders' => array(
//                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
//                )
//            )
//        );
        $styleLastRow = [
//            'font' => [
//                'bold' => true,
//            ],
//            'alignment' => [
//                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
//            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'ebf1de',
                ],
                'endColor' => [
                    'argb' => 'ebf1de',
                ],
            ],
        ];

        $ews->setCellValue('A1', 'Project ID'); // Sets cell 'a1' to value 'ID
        $ews->setCellValue('B1', 'Submission Date');
        $ews->setCellValue('C1', 'Principal Investigator(s)');
        $ews->setCellValue('D1', 'Project Title');
        $ews->setCellValue('E1', 'Funding');
        $ews->setCellValue('F1', 'Status');
        $ews->setCellValue('G1', 'Approval Date');
        $ews->setCellValue('H1', $this->getHumanAnimalName().' Expiration Date');

        $ews->setCellValue('I1', 'Request ID');

        $ews->setCellValue('J1', 'Fund Number');
        $ews->setCellValue('K1', 'Completion Status');

        $ews->setCellValue('L1', 'Invoice(s) Issued');
        $ews->setCellValue('M1', 'Most Recent Invoice Total($)');
        $ews->setCellValue('N1', 'Most Recent Invoice Paid($)');
        $ews->setCellValue('O1', 'Most Recent Invoice Due($)');
        $ews->setCellValue('P1', 'Most Recent Invoice Comment');

        $ews->getStyle('A1:P1')->applyFromArray($styleBoldArray);

        $count = 0;
        $totalRequests = 0;
        $totalInvoices = 0;
        $totalTotal = 0;
        $paidTotal = 0;
        $dueTotal = 0;

        $rowCount = 2;
        foreach( $projectIdsArr as $projectId ) {

            if( $limit && ($count++ > $limit) ) {
                break;
            }

            $project = $this->em->getRepository('AppTranslationalResearchBundle:Project')->find($projectId);
            if( !$project ) {
                continue;
            }

//            if( $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//                continue;
//            }
            if( $transresPermissionUtil->hasProjectPermission("view",$project) === false ) {
                continue;
            }


            //$ews = $this->fillOutProjectCells($ews,$rowCount,$project); //A,B,C,D,E,F,G,H

            $projectRequests = 0;
            $projectTotalInvoices = 0;
            $projectTotalTotal = 0;
            $projectTotalPaid = 0;
            $projectTotalDue = 0;

            //$workRequests = $project->getRequests();
            $workRequests = $transresRequestUtil->getProjectMiniRequests($projectId);
            foreach($workRequests as $request) {

                //$oid = $request->getOid();
                $oid = $request['id'];
                //$fundedAccountNumber = $request->getFundedAccountNumber();
                $fundedAccountNumber = $request['fundedAccountNumber'];
                //$progressState = $request->getProgressState();
                $progressState = $request['progressState'];

                //$ews = $this->fillOutProjectCells($ews,$rowCount,$project); //A,B,C,D,E,F,G,H
                $ews->setCellValue('A'.$rowCount, $project->getOid()); //set just project ID

                //Request ID
                $ews->setCellValue('I'.$rowCount, $oid);

                //Funding Number
                $ews->setCellValue('J'.$rowCount, $fundedAccountNumber);

                //Completion Status
                $ews->setCellValue('K'.$rowCount, $transresRequestUtil->getProgressStateLabelByName($progressState));

                //Invoice(s) Issued (Latest)
                //$latestInvoice = $transresRequestUtil->getLatestInvoice($request);
                $latestInvoice = $transresRequestUtil->getLatestInvoice(null,$request['id']);
                //$latestInvoicesCount = count($request->getInvoices());
                $latestInvoicesCount = 0;
                if( $latestInvoice ) {
                    $latestInvoicesCount = 1;
                    $totalInvoices++;
                    $projectTotalInvoices++;
                }
                $ews->setCellValue('L'.$rowCount, $latestInvoicesCount);

                if( $latestInvoice ) {
                    //# Total($)
                    $total = $latestInvoice->getTotal();
                    $totalTotal = $totalTotal + $total;
                    $projectTotalTotal = $projectTotalTotal + $total;
                    if ($total) {
                        $ews->setCellValue('M' . $rowCount, $total);
                    }

                    //# Paid($)
                    $paid = $latestInvoice->getPaid();
                    $paidTotal = $paidTotal + $paid;
                    $projectTotalPaid = $projectTotalPaid + $paid;
                    if ($paid) {
                        $ews->setCellValue('N' . $rowCount, $paid);

                    }

                    //# Due($)
                    $due = $latestInvoice->getDue();
                    $dueTotal = $dueTotal + $due;
                    $projectTotalDue = $projectTotalDue + $due;
                    if ($due) {
                        $ews->setCellValue('O' . $rowCount, $due);
                    }

                    //Comment
                    $comment = $latestInvoice->getComment();
                    if( $comment ) {
                        $ews->setCellValue('P' . $rowCount, $comment);
                        $ews->getStyle('P' . $rowCount)
                            ->getAlignment()->setWrapText(true);
                    }
                }

                $projectRequests = $projectRequests + 1;

                $rowCount = $rowCount + 1;
            }

            $totalRequests = $totalRequests + $projectRequests;

            $ews = $this->fillOutProjectCells($ews,$rowCount,$project); //A,B,C,D,E,F,G,H

            //Request Total
            $ews->setCellValue('I'.$rowCount, "Project Totals");
            $ews->getStyle('I'.$rowCount)->applyFromArray($styleBoldArray);

            //This Project Total Invoices
            $ews->setCellValue('L'.$rowCount, $projectTotalInvoices);
            $ews->getStyle('L'.$rowCount)->applyFromArray($styleBoldArray);

            //This Project Total Total
            $ews->setCellValue('M'.$rowCount, $projectTotalTotal);
            $ews->getStyle('M'.$rowCount)->applyFromArray($styleBoldArray);

            //This Project Total Paid
            $ews->setCellValue('N'.$rowCount, $projectTotalPaid);
            $ews->getStyle('N'.$rowCount)->applyFromArray($styleBoldArray);

            //This Project Total Due
            $ews->setCellValue('O'.$rowCount, $projectTotalDue);
            $ews->getStyle('O'.$rowCount)->applyFromArray($styleBoldArray);

            //set color light green to the last Total row
            $ews->getStyle('A'.$rowCount.':'.'P'.$rowCount)->applyFromArray($styleLastRow);

            $rowCount = $rowCount + 1;

            $this->em->clear();

        }//projects

        //Total
        //$rowCount++;
        $ews->setCellValue('H'.$rowCount, "Totals");
        $ews->getStyle('H'.$rowCount)->applyFromArray($styleBoldArray);
        //Requests total
        $ews->setCellValue('I' . $rowCount, $totalRequests);
        $ews->getStyle('I'.$rowCount)->applyFromArray($styleBoldArray);
        //Invoices total
        $ews->setCellValue('L'.$rowCount, $totalInvoices);
        $ews->getStyle('L'.$rowCount)->applyFromArray($styleBoldArray);
        //Total total
        if( $totalTotal > 0 ) {
            $ews->setCellValue('M' . $rowCount, $totalTotal);
            $ews->getStyle('M'.$rowCount)->applyFromArray($styleBoldArray);
        }
        //Paid total
        if( $paidTotal > 0 ) {
            $ews->setCellValue('N' . $rowCount, $paidTotal);
            $ews->getStyle('N'.$rowCount)->applyFromArray($styleBoldArray);
        }
        //Due total
        if( $dueTotal > 0 ) {
            $ews->setCellValue('O' . $rowCount, $dueTotal);
            $ews->getStyle('O'.$rowCount)->applyFromArray($styleBoldArray);
        }

        //format columns to currency format 2:$rowCount
        $ews->getStyle('M2:M'.$rowCount)
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $ews->getStyle('N2:N'.$rowCount)
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $ews->getStyle('O2:O'.$rowCount)
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);

        //exit("ids=".$fellappids);


        // Auto size columns for each worksheet
        //\PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        $autosize = true;
        $autosize = false;
        if( $autosize ) {
            foreach ($ea->getWorksheetIterator() as $worksheet) {

                $ea->setActiveSheetIndex($ea->getIndex($worksheet));

                $sheet = $ea->getActiveSheet();
                $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                /** @var PHPExcel_Cell $cell */
                foreach ($cellIterator as $cell) {
                    $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
                }
            }
        }


        return $ea;
    }
    public function convertDateToStr($datetime) {
        if( $datetime ) {
            return $datetime->format("m/d/Y");
        }
        return null;
    }
    public function fillOutProjectCells($ews, $rowCount, $project) {
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');

        $ews->setCellValue('A'.$rowCount, $project->getOid());
        $ews->setCellValue('B'.$rowCount, $this->convertDateToStr($project->getCreateDate()) );

        $piArr = array();
        foreach( $project->getPrincipalInvestigators() as $pi) {
            $piArr[] = $pi->getUsernameOptimal();
        }
        $ews->setCellValue('C'.$rowCount, implode("\n",$piArr));
        $ews->getStyle('C'.$rowCount)->getAlignment()->setWrapText(true);

        $projectTitle = $project->getTitle();
        if( !$projectTitle ) {
            //$projectTitle = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Title");
            $projectTitle = $project->getTitle();
        }
        $ews->setCellValue('D'.$rowCount, $projectTitle);

        //Funding
        //if( $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Funded") ) {
        if( $project->getFunded() ) {
            $funded = "Funded";
        } else {
            $funded = "Not Funded";
        }
        $ews->setCellValue('E'.$rowCount, $funded);

        //Status
        $ews->setCellValue('F'.$rowCount, $this->getStateLabelByName($project->getState()));

        //Approval Date
        $ews->setCellValue('G'.$rowCount, $this->convertDateToStr($project->getApprovalDate()) );

        //IRB Expiration Date
        $expDateStr = null;
        if( $project->getImplicitExpirationDate() ) {
            $expDateStr = $project->getImplicitExpirationDate()->format('m/d/Y');
        }
        $ews->setCellValue('H'.$rowCount, $expDateStr);

        return $ews;
    }
    public function fillOutProjectCellsSpout($writer,$project,$writeRow=false) {

        $data = array();

        //$ews->setCellValue('A'.$rowCount, $project->getOid());
        $data[0] = $project->getOid();

        //$ews->setCellValue('B'.$rowCount, $this->convertDateToStr($project->getCreateDate()) );
        $data[1] = $this->convertDateToStr($project->getCreateDate());

        $piArr = array();
        foreach( $project->getPrincipalInvestigators() as $pi) {
            $piArr[] = $pi->getNameEmail(); //getUsernameOptimal();
        }
        //$ews->setCellValue('C'.$rowCount, implode("\n",$piArr));
        //$ews->getStyle('C'.$rowCount)->getAlignment()->setWrapText(true);
        //$data[2] = implode("\n",$piArr);
        $data[2] = implode("; ",$piArr);

        $projectTitle = $project->getTitle();
        if( !$projectTitle ) {
            //$projectTitle = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Title");
            $projectTitle = $project->getTitle();
        }
        //$ews->setCellValue('D'.$rowCount, $projectTitle);
        $data[3] = $projectTitle;

        //Funding
        //if( $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Funded") ) {
        if( $project->getFunded() ) {
            $funded = "Funded";
        } else {
            $funded = "Not Funded";
        }
        //$ews->setCellValue('E'.$rowCount, $funded);
        $data[4] = $funded;

        //Status
        //$ews->setCellValue('F'.$rowCount, $this->getStateLabelByName($project->getState()));
        $data[5] = $this->getStateLabelByName($project->getState());

        //Approval Date
        //$ews->setCellValue('G'.$rowCount, $this->convertDateToStr($project->getApprovalDate()) );
        $data[6] = $this->convertDateToStr($project->getApprovalDate());

        //IRB Expiration Date
        $expDateStr = null;
        if( $project->getImplicitExpirationDate() ) {
            $expDateStr = $project->getImplicitExpirationDate()->format('m/d/Y');
        }
        //$ews->setCellValue('H'.$rowCount, $expDateStr);
        $data[7] = $expDateStr;

        if( $writeRow ) {
            $writer->addRow($data);
        }

        return $data;
    }

    public function getProjectsIdsStr($projects) {
        $idsArr = array();
        foreach($projects as $project) {
            if( $project->getId() ) {
                $idsArr[] = $project->getId();
            }
        }
        if( count($idsArr) > 0 ) {
            return implode(",",$idsArr);
        }
        return null;
    }

    public function isUserAllowedSpecialtyObject( $specialtyObject, $user=null ) {
        if( !$specialtyObject ) {
            return false;
        }

        if( !$user ) {
            $user = $this->secTokenStorage->getToken()->getUser();
        }

        $partialRoleStr = "_".$specialtyObject->getUppercaseName();

        //if admin or deputy admin
        $adminRole = "ROLE_TRANSRES_ADMIN"."_".$partialRoleStr;
        if( $this->secAuth->isGranted($adminRole) ) {
            return true;
        }
        //check user's roles
        if( $user && $user->hasRole($adminRole) ) {
            return true;
        }

        //check if user has role: use $user->hasPartialRole() or isUserHasSiteAndPartialRoleName()
        if( $user->hasPartialRole($partialRoleStr) ) {
            return true;
        }

//        if( $role ) {
//            //check security context
//            if( $this->secAuth->isGranted($role) ) {
//                return true;
//            }
//
//            //check user's roles
//            if( $user && $user->hasRole($role) ) {
//                return true;
//            }
//        }

        return false;
    }

//    public function getSpecialtyRole($specialtyObject) {
//        $role = null;
//        if( $specialtyObject->getAbbreviation() == "hematopathology" ) {
//            $role = "ROLE_TRANSRES_HEMATOPATHOLOGY";
//        }
//        if( $specialtyObject->getAbbreviation() == "ap-cp" ) {
//            $role = "ROLE_TRANSRES_APCP";
//        }
//        return $role;
//    }

    public function userQueryBuilder( $cycle=NULL ) {
        //echo "cycle=$cycle <br>";
        if( $cycle && $cycle == "new" ) {
            return function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->andWhere("employmentStatus.terminationDate IS NULL")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            };
        }

        return function(EntityRepository $er) {
            return $er->createQueryBuilder('list')
                ->leftJoin("list.employmentStatus", "employmentStatus")
                ->leftJoin("employmentStatus.employmentType", "employmentType")
                ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                ->leftJoin("list.infos", "infos")
                ->orderBy("infos.displayName","ASC");
        };
    }

//    public function getAllowedProjectSpecialty_ORIG( $user )
//    {
//        $projectSpecialtyAllowedArr = new ArrayCollection();
//        $projectSpecialtyDeniedArr = new ArrayCollection();
//
//        //check is user is hematopathology user
//        $specialtyHemaObject = $this->getSpecialtyObject("hematopathology");
//        if( $this->isUserAllowedSpecialtyObject($specialtyHemaObject, $user) ) {
//            $projectSpecialtyAllowedArr->add($specialtyHemaObject);
//        } else {
//            $projectSpecialtyDeniedArr->add($specialtyHemaObject);
//        }
//
//        //check is user is ap-cp user
//        $specialtyAPCPObject = $this->getSpecialtyObject("ap-cp");
//        if( $this->isUserAllowedSpecialtyObject($specialtyAPCPObject, $user) ) {
//            $projectSpecialtyAllowedArr->add($specialtyAPCPObject);
//        } else {
//            $projectSpecialtyDeniedArr->add($specialtyAPCPObject);
//        }
//
//        $specialtyCovid19Object = $this->getSpecialtyObject("covid19");
//        if( $this->isUserAllowedSpecialtyObject($specialtyCovid19Object, $user) ) {
//            $projectSpecialtyAllowedArr->add($specialtyCovid19Object);
//        } else {
//            $projectSpecialtyDeniedArr->add($specialtyCovid19Object);
//        }
//
//        $specialtyMisiObject = $this->getSpecialtyObject("misi");
//        if( $this->isUserAllowedSpecialtyObject($specialtyMisiObject, $user) ) {
//            $projectSpecialtyAllowedArr->add($specialtyMisiObject);
//        } else {
//            $projectSpecialtyDeniedArr->add($specialtyMisiObject);
//        }
//
//        $res = array(
//            'projectSpecialtyAllowedArr' => $projectSpecialtyAllowedArr,
//            'projectSpecialtyDeniedArr' => $projectSpecialtyDeniedArr
//        );
//
//        return $res;
//    }
    //Similar to getTransResProjectSpecialties but this method returns allowed and denied arrays
    public function getAllowedProjectSpecialty( $user )
    {
        $projectSpecialtyAllowedArr = new ArrayCollection();
        $projectSpecialtyDeniedArr = new ArrayCollection();

        //get all enabled project specialties
        $specialties = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findBy(
            array(
                'type' => array("default","user-added")
            ),
            array('orderinlist' => 'ASC')
        );

        foreach($specialties as $specialtyObject) {
            if( $this->isUserAllowedSpecialtyObject($specialtyObject, $user) ) {
                $projectSpecialtyAllowedArr->add($specialtyObject);
            } else {
                $projectSpecialtyDeniedArr->add($specialtyObject);
            }
        }

        $res = array(
            'projectSpecialtyAllowedArr' => $projectSpecialtyAllowedArr,
            'projectSpecialtyDeniedArr' => $projectSpecialtyDeniedArr
        );

        return $res;
    }

    public function getObjectDiff($first_array,$second_array) {
        $diff = array_udiff($first_array, $second_array,
            function ($obj_a, $obj_b) {
                if( $obj_a->getId() == $obj_b->getId() ) {
                    return 0;
                }
                return -1;
            }
        );
        return $diff;
    }
    public function getReturnIndexSpecialtyArray( $projectSpecialtyArr, $project=null, $filterType=null ) {
        $resArr = array();

        $count = 0;
        foreach($projectSpecialtyArr as $projectSpecialty) {
            //echo "spec=".$projectSpecialty."<br>";
            $resArr["filter[projectSpecialty][".$count."]"] = $projectSpecialty->getId();
            $count++;
        }

        if( $project && $project->getId() ) {
            $resArr["filter[project]"] = $project->getId();
        }

        if( $filterType ) {
            $resArr["type"] = $filterType;
            $resArr["title"] = $filterType;
        }

        //print_r($resArr);

        return $resArr;
    }

    //check if user does not have ROLE_TRANSRES_REQUESTER and specialty role
    public function addMinimumRolesToCreateProject( $specialtyObject, $user=null ) {
        $userSecUtil = $this->container->get('user_security_utility');

        if( !$user ) {
            $user = $this->secTokenStorage->getToken()->getUser();
        }

        $flushUser = false;
        $roleAddedArr = array();

        //echo "specialtyObject=".$specialtyObject."<br>";

        if( $specialtyObject ) {
            $uppercaseName = $specialtyObject->getUppercaseName();
            $uppercaseName = "_".$uppercaseName;
            $role = 'ROLE_TRANSRES_REQUESTER'.$uppercaseName;
            //echo "check role=".$role."<br>";
            if(
                false == $this->secAuth->isGranted($role) ||
                false == $user->hasRole($role)
            ) {
                $user->addRole($role);
                $flushUser = true;
                $roleAddedArr[] = $role;
            }
        }

//        if( $specialtyObject ) {
//            $specialtyRole = $this->getSpecialtyRole($specialtyObject);
//            if( false == $this->secAuth->isGranted($specialtyRole) ) {
//                $user->addRole($specialtyRole);
//                $flushUser = true;
//                $roleAddedArr[] = $specialtyRole;
//            }
//            $specialtyStr = $specialtyObject."";
//        } else {
//            $specialtyStr = null;
//        }

        $environment = $userSecUtil->getSiteSettingParameter('environment');
        if( $environment != 'live' ) {
            if(
                false == $this->secAuth->isGranted('ROLE_TESTER') ||
                false == $user->hasRole('ROLE_TESTER')
            ) {
                $user->addRole('ROLE_TESTER');
                $flushUser = true;
                $roleAddedArr[] = 'ROLE_TESTER';
            }
        }

        if( $flushUser ) {
            //exit('flush user');
            $this->em->flush($user);

//            $this->container->get('session')->getFlashBag()->add(
//                'warning',
//                "Permission to create a new $specialtyStr project has been automatically granted by the system. Your activities will be recorded."
//            );

            ///////////////// Event Log /////////////////
            $sitename = $this->container->getParameter('translationalresearch.sitename');
            $userSecUtil = $this->container->get('user_security_utility');
            $eventType = "User record updated";
            $eventMsg = "User information of " . $user . " has been automatically changed to be able to access a new $specialtyObject project page" . "<br>";

            if( count($roleAddedArr) > 0 ) {
                $eventMsg = $eventMsg . "Added roles:" . implode(", ", $roleAddedArr);
            }

            $userSecUtil->createUserEditEvent($sitename, $eventMsg, $user, $user, null, $eventType);
            ///////////////// EOF Event Log /////////////////

            return $roleAddedArr;
        }

        return null;
    }

    public function assignMinimumProjectRoles($project) {
        $resArr = array();
        //1 principalInvestigators
        $pis = $project->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi ) {
                $resArr[] = $pi;
            }
        }

        //2 principalIrbInvestigator
        $principalIrbInvestigators = $project->getPrincipalIrbInvestigators();
        foreach( $principalIrbInvestigators as $principalIrbInvestigator ) {
            if( $principalIrbInvestigator ) {
                $resArr[] = $principalIrbInvestigator;
            }
        }

        //3 coInvestigators
        $cois = $project->getCoInvestigators();
        foreach( $cois as $coi ) {
            if( $coi ) {
                $resArr[] = $coi;
            }
        }

        //4 pathologists
        $pathologists = $project->getPathologists();
        foreach( $pathologists as $pathologist ) {
            if( $pathologist ) {
                $resArr[] = $pathologist;
            }
        }

        //5 contacts
        $contacts = $project->getContacts();
        foreach( $contacts as $contact ) {
            if( $contact ) {
                $resArr[] = $contact;
            }
        }

        //6 Billing contacts
        $billingContacts = $project->getBillingContacts();
        foreach( $billingContacts as $billingContact ) {
            if( $billingContact ) {
                $resArr[] = $billingContact;
            }
        }

        foreach($resArr as $user) {
            //echo "user=$user <br>";
            $this->addMinimumRolesToCreateProject($project->getProjectSpecialty(),$user);
        }
        //exit('111');
        return $resArr;
    }

    public function assignMinimumRequestRoles($transresRequest) {
        $contact = $transresRequest->getContact();
        if( $contact ) {
            $project = $transresRequest->getProject();
            $this->addMinimumRolesToCreateProject($project->getProjectSpecialty(),$contact);
        }
    }

    public function getNumberOfFundedRequests( $project ) {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("request");
        $dql->select('request');
        $dql->leftJoin("request.project", "project");

        $dql->where("project.id=:projectId");
        $dql->andWhere('request.fundedAccountNumber IS NOT NULL');

        $parameters = array("projectId"=>$project->getId());

        $query = $dql->getQuery();

        $query->setParameters($parameters);

        $requests = $query->getResult();

        $count = count($requests);

        return $count;
    }

    //$fromStateStr and $toStateStr - state string from workflow (i.e. 'irb_review')
    public function getNotificationMsgByStates( $fromStateStr, $toStateStr, $project ) {

        $msg = null;

        $id = $project->getOid();

        $title = $project->getTitle();

        $fromLabel = $this->getStateSimpleLabelByName($fromStateStr);
        $toLabel = $this->getStateSimpleLabelByName($toStateStr);

        //Case1: irb_review -> admin_review
        //Project request [xxx] "[project title]" has successfully passed the "IRB review" stage and is now awaiting "Admin review".
        if( strpos($fromStateStr, "_review") !== false && strpos($toStateStr, "_review") !== false ) {
            $msg = "Project request $id '".$title."' has successfully passed the '".$fromLabel."' stage and is now awaiting '".$toLabel."'.";
        }

        //Case2: final_review -> final_approved
        //Project request [xxx] "[project title]" has successfully passed all stages of review and received final approval.
        if( strpos($fromStateStr, "_review") !== false && strpos($toStateStr, "_approved") !== false ) {
            $msg = "Project request $id '".$title."' has successfully passed all stages of review and has received final approval.";
        }

        //Case3: irb_review -> irb_rejected
        if( strpos($fromStateStr, "_review") !== false && strpos($toStateStr, "_rejected") !== false ) {
            $msg = "Project request $id '".$title."' has been rejected as a result of '".$fromLabel."'.";
        }

        //Case4: irb_review -> irb_missinginfo
        if( strpos($fromStateStr, "_review") !== false && strpos($toStateStr, "_missinginfo") !== false ) {
            $msg = "Additional information has been requested for the project with ID $id '".$title."' for the '".$fromLabel."' stage.";
        }

        //Case5: irb_missinginfo -> irb_review
        if( strpos($fromStateStr, "_missinginfo") !== false && strpos($toStateStr, "_review") !== false ) {
            $msg = "Project request $id '".$title."' has been re-submitted for '".$toLabel."' stage.";
        }

        if( !$msg ) {
            $user = $this->secTokenStorage->getToken()->getUser();
            $msg = "The status of the project request $id '".$title."' has been changed from '".$fromLabel."' to '".$toLabel."'";
            $msg = $msg . " by " . $user;
        }

        return $msg;
    }

    public function getTotalProjectCount() {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Project');
        $dql = $repository->createQueryBuilder("project");
        $dql->select('COUNT(project)');

        $query = $dql->getQuery();

        //$count = -1;
        $count = $query->getSingleScalarResult();
        //$resArr = $query->getOneOrNullResult();
        //print_r($resArr);
        //echo "count=".$count."<br>";

        return $count;
    }
    public function getProjectIdsArrByDqlParameters($dql,$dqlParameters) {
        $dql->select('project.id');

        $query = $dql->getQuery();

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $result = $query->getScalarResult();
        $ids = array_map('current', $result);
        $ids = array_unique($ids);

        //print_r($ids);
        //echo "count=".$count."<br>";

        return $ids;
    }

    public function getTotalRequests() {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:TransResRequest');
        $dql = $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest');

        $query = $dql->getQuery();
        
        $requests = $query->getResult();

        return $requests;
    }
    public function getTotalRequestCount() {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:TransResRequest');
        $dql = $repository->createQueryBuilder("transresRequest");
        $dql->select('COUNT(transresRequest)');

        $query = $dql->getQuery();

        //$count = -1;
        $count = $query->getSingleScalarResult();
        //$resArr = $query->getOneOrNullResult();
        //print_r($resArr);
        //echo "count=".$count."<br>";

        return $count;
    }
    public function getTotalRequestCountByDqlParameters($dql,$dqlParameters) {
        $dql->select('COUNT(transresRequest)');

        $query = $dql->getQuery();

        //doctrine cache queries
        //$query->useQueryCache(true);
        //$query->useResultCache(true);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $res = $query->getScalarResult();
        //echo "res count=".count($res)."<br>";
        //print_r($res);
        return count($res);

        //$count = $query->getSingleScalarResult();
        //echo "count=".$count."<br>";
        //return $count;
    }
    public function getMatchingRequestArrByDqlParameters($dql,$dqlParameters) {
        $dql->select('transresRequest.id');

        $query = $dql->getQuery();

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $results = $query->getScalarResult();
        //print_r($results);
        //echo "<br><br>";

        //All Invoices (188 matching for Total: $61,591.00, Paid: $30,000.00, Unpaid: $31591.00)

        $requestIds = array();

        $counter = 0;
        foreach($results as $idParams) {
            $id = $idParams['id'];
            $requestIds[] = $id;
            $counter++;
        }//foreach

        return $requestIds;
    }

    public function getAppropriatedUsers() {
        //$users = $this->em->getRepository('AppUserdirectoryBundle:User')->findAll();

        //$users = $this->em->getRepository('AppUserdirectoryBundle:User')->findBy(array('createdby'=>array('googleapi')));
        //return $users;

        //Multiple (384 - all users in DB) FROM scan_perSiteSettings t0 WHERE t0.fosuser = ?
        $repository = $this->em->getRepository('AppUserdirectoryBundle:User');
        $dql = $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->leftJoin("list.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");
        $dql->leftJoin("list.infos", "infos");

        //$dql->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL");
        $dql->where("list.createdby != 'googleapi'"); //googleapi is used only by fellowship application population

        //added additional filters
        //$dql->andWhere("list.keytype IS NOT NULL AND list.primaryPublicUserId != 'system'");
        //$dql->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)");
        //$dql->andWhere("(list.testingAccount = false OR list.testingAccount IS NULL)");
        //Currently working employee
        //$dql->andWhere("(employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate IS NULL)");
        //$curdate = date("Y-m-d", time());
        //$dql->andWhere("(employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."')");

        //$dql->orderBy("infos.displayName","ASC");
        $dql->orderBy("infos.lastName","ASC");

        $query = $dql->getQuery();

        //$query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        //doctrine cache queries
        //$query->useQueryCache(true);
        //$query->useResultCache(true);

        $users = $query->getResult();

        return $users;
    }

    public function getPricesList() {
//        $repository = $this->em->getRepository('AppTranslationalResearchBundle:PriceTypeList');
//        $dql =  $repository->createQueryBuilder("list");
//        $dql->select('list');
//
//        $dql->where("list.type = :typedef OR list.type = :typeadd");
//        $dql->orderBy("list.orderinlist","ASC");
//
//        $dqlParameters = array();
//
//        $dqlParameters["typedef"] = 'default';
//        $dqlParameters["typeadd"] = 'user-added';
//
//        $query = $dql->getQuery();
//
//        if( count($dqlParameters) > 0 ) {
//            $query->setParameters($dqlParameters);
//        }
//
//        $prices = $query->getResult();
        
        $prices = $this->getDbPriceLists();

        $transresPricesList = array();
        $transresPricesList['All'] = 'all';
        $transresPricesList['Default'] = 'default';

        foreach($prices as $price) {
            $transresPricesList[$price->getName()] = $price->getId();
        }

        return $transresPricesList;
    }

    public function getDbPriceLists() {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:PriceTypeList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->orderBy("list.orderinlist","ASC");

        $dqlParameters = array();

        $dqlParameters["typedef"] = 'default';
        $dqlParameters["typeadd"] = 'user-added';

        $query = $dql->getQuery();

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $priceLists = $query->getResult();

        return $priceLists;
    }

    //show current review's reccomendations for committee review status for primary reviewer
    public function showProjectReviewInfo($project) {
        $user = $this->secTokenStorage->getToken()->getUser();
        $res = null;
        //echo "threadId=$threadId<br>";

        if( $project->getState() == "committee_review" ) {
            $show = false;
            if( $this->isAdminOrPrimaryReviewer($project) ) {
                $show = true;
            }
            $reviews = $project->getCommitteeReviews();
            if( $this->isReviewsReviewer($user,$reviews) ) {
                $show = true;
            }
            if( $show ) {
                $resArr = array();
                $primaryReviewer = 0;
                foreach($reviews as $review) {
                    $currentPrimaryReview = false;
                    $reviewStatus = $review->getDecision();
                    //echo "reviewStatus=$reviewStatus<br>";
                    if( $this->isReviewer($user,$review,true) ) {
                        $currentPrimaryReview = true;
                        $primaryReviewer++;
                    }
                    if( $reviewStatus && $reviewStatus != "pending" ) {
                        if( $currentPrimaryReview ) {
                            $reviewStatus = $reviewStatus . "(Primary Review)";
                        }
                        $resArr[] = "<b>".ucfirst($reviewStatus)."</b>";
                    }
                }
                if( $primaryReviewer > 0 ) { //show it only to primary reviewers
                    if( count($resArr) > 0 ) {
                        $res = "<i>"."Committee member recommendation(s): " .  implode(", ", $resArr) . "</i>";
                        $res = "<p>" . $res . "</p>";
                    }
                }
            }
        }

        return $res;
    }

    public function showHumanTissueFormViewUrl($project) {
        //get human tissue form view url
        $humanTissueFormsViewLink = null;
        $humanTissueUrlArr = array();
        $humanTissueForms = $project->getHumanTissueForms();
        if( count($humanTissueForms) > 0 ) {
            foreach($humanTissueForms as $humanTissueForm) {
                $humanTissueUrl = $this->container->get('router')->generate(
                    'fellapp_file_view',
                    array(
                        'id'=>$humanTissueForm->getId()
                    )
                );
                $thisLink = "<a target='_blank' href=".$humanTissueUrl.">".$humanTissueForm->getOriginalnameClean()."</a>";
                $humanTissueUrlArr[] = $thisLink;
            }

            $humanTissueFormsViewLink = "Human Tissue Form: " . implode(", ",$humanTissueUrlArr) . "";
        }

        return $humanTissueFormsViewLink;
    }

    public function getTransresSiteProjectParameter( $fieldName, $project=null, $projectSpecialty=null, $useDefault=false, $testing=false ) {
        $value = $this->getTransresSiteProjectParameterSingle($fieldName,$project,$projectSpecialty,$useDefault,$testing);
        //echo "value1=[$value] <br>";
        if( $value === NULL ) {
            $value = $this->getTransresSiteProjectParameterSingle($fieldName,NULL,NULL,$useDefault,$testing);
            //echo "NULL value2=[$value] <br>";
        } else {
            //echo "NOTNULL value2=[$value] <br>";
        }

        return $value;
    }
    public function getTransresSiteProjectParameterSingle( $fieldName, $project=null, $projectSpecialty=null, $useDefault=false, $testing=false ) {

        if( !$fieldName ) {
            throw new \Exception("Field name is empty");
        }

        $projectSpecialtyAbbreviation = NULL; //Use Default Site Settings

        if( $useDefault === false ) {
            if( !$projectSpecialty ) {
                if( $project ) {
                    $projectSpecialty = $project->getProjectSpecialty();
                    $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();
                } else {
                    //use default $projectSpecialtyAbbreviation=NULL
                }
            }

            if( !$projectSpecialtyAbbreviation ) {
                if( $projectSpecialty ) {
                    $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();
                }
            }
        }

        $siteParameter = $this->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
        if( !$siteParameter ) {
            throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
        }

        $getMethod = "get".$fieldName;

        $value = $siteParameter->$getMethod();

        if( $testing && $value ) {
            if( $projectSpecialtyAbbreviation ) {
                $projectSpecialtyAbbreviation = strtoupper($projectSpecialtyAbbreviation);
            } else {
                $projectSpecialtyAbbreviation = "Default";
            }
            $value = "<b>".$fieldName." (".$projectSpecialtyAbbreviation. " Site Settings)"."</b>:<br>" .$value;
        }

        return $value;
    }
    public function findCreateSiteParameterEntity($specialtyStr=NULL) {
        $em = $this->em;

        //$entity = $em->getRepository('AppTranslationalResearchBundle:TransResSiteParameters')->findOneByOid($specialtyStr);

        $repository = $em->getRepository('AppTranslationalResearchBundle:TransResSiteParameters');
        $dql = $repository->createQueryBuilder("siteParameter");
        $dql->select('siteParameter');
        $dql->leftJoin('siteParameter.projectSpecialty','projectSpecialty');

        $dqlParameters = array();

        if( $specialtyStr ) {
            $dql->where("projectSpecialty.abbreviation = :specialtyStr");
            $dqlParameters["specialtyStr"] = $specialtyStr;
        } else {
            $dql->where("projectSpecialty IS NULL");
        }

        $query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $entities = $query->getResult();
        //echo "projectSpecialty count=".count($entities)."<br>";

        if( count($entities) > 0 ) {
            return $entities[0];
        }

        //Create New
        $specialty = $em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation($specialtyStr);

//        if( !$specialty ) {
//            throw new \Exception("SpecialtyList is not found by specialty abbreviation '" . $specialtyStr . "'");
//        } else {
            if( $this->secTokenStorage->getToken() ) {
                $user = $this->secTokenStorage->getToken()->getUser();
            } else {
                $user = null;
            }
            $entity = new TransResSiteParameters($user);

            $entity->setProjectSpecialty($specialty);

//            //remove null Logo document if exists
//            $logoDocument = $entity->getTransresLogo();
//            if( $logoDocument ) {
//                $entity->setTransresLogo(null);
//                $em->remove($logoDocument);
//            }

            $em->persist($entity);
            $em->flush($entity);

            return $entity;
        //}

        return null;
    }
    public function getTransresSiteParameterFile( $fieldName, $project=null, $projectSpecialty=null) {
        $value = $this->getTransresSiteParameterFileSingle($fieldName, $project, $projectSpecialty);

        if( $value === NULL ) {
            //echo $fieldName.": no specific file <br>";
            //$projectSpecialtyAbbreviation = NULL;
            $value = $this->getTransresSiteParameterFileSingle($fieldName,NULL,NULL);
//            if( $value ) {
//                echo $fieldName.": file value <br>";
//            } else {
//                echo $fieldName.": no file value <br>";
//            }
        }

        return $value;
    }
    public function getTransresSiteParameterFileSingle( $fieldName, $project=null, $projectSpecialty=null ) {

        if( !$fieldName ) {
            throw new \Exception("Field name is empty");
        }

        if( !$projectSpecialty ) {
            //echo "NO projectSpecialty <br>";
            if( $project ) {
                $projectSpecialty = $project->getProjectSpecialty();
            } else {
                if(0) {
                    //use the first project specialty as default
                    $specialties = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findBy(
                        array(
                            'type' => array("default", "user-added")
                        ),
                        array('orderinlist' => 'ASC')
                    );
                    if (count($specialties) > 0) {
                        $projectSpecialty = $specialties[0];
                        //exit("projectSpecialty=$projectSpecialty ");
                    } else {
                        throw new \Exception("SpecialtyList is empty (no items with type 'default' or 'user-added')");
                    }
                }
            }
        }

        $projectSpecialtyAbbreviation = NULL;

        if( $projectSpecialty ) {
            $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();
        }
        //echo "projectSpecialtyAbbreviation=$projectSpecialtyAbbreviation <br>";

        $siteParameter = $this->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
        if( !$siteParameter ) {
            throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
        } else {
            //echo "siteParameterId=".$siteParameter->getId()."<br>";
        }

        $getMethod = "get".$fieldName;

        $documents = $siteParameter->$getMethod();

        if( count($documents) > 0 ) {
            $document = $documents->first(); //DESC order => the most recent first
            return $document;

//            $docPath = $document->getAbsoluteUploadFullPath();
//            //$docPath = $document->getRelativeUploadFullPath();
//            //echo "docPath=" . $docPath . "<br>";
//            if( $docPath ) {
//                return $docPath;
//            }
        }

        return NULL;
    }

    //$type: slash-"IRB/IACUC", brackets-"IRB (IACUC)"
    public function getHumanAnimalName($type="slash") {
        $human = $this->getHumanName();
        $animal = $this->getAnimalName();

        if( $type == "slash" ) {
            return $human."/".$animal;
        }

        if( $type == "brackets" ) {
            return $human." (".$animal.")";
        }

        return $human.",".$animal;
    }
    public function getHumanName() {
        $userSecUtil = $this->container->get('user_security_utility');
        $human = $userSecUtil->getSiteSettingParameter('transresHumanSubjectName');
        if( !$human ) {
            $human = "IRB";
        }
        return $human;
    }
    public function getAnimalName() {
        $userSecUtil = $this->container->get('user_security_utility');
        $animal = $userSecUtil->getSiteSettingParameter('transresAnimalSubjectName');
        if( !$animal ) {
            $animal = "IACUC";
        }
        return $animal;
    }
    public function getBusinessEntityName() {
        $userSecUtil = $this->container->get('user_security_utility');
        $name = $userSecUtil->getSiteSettingParameter('transresBusinessEntityName');
        if( !$name ) {
            $name = "Center for Translational Pathology";
        }
        return $name;
    }
    public function getBusinessEntityAbbreviation() {
        $userSecUtil = $this->container->get('user_security_utility');
        $name = $userSecUtil->getSiteSettingParameter('transresBusinessEntityAbbreviation');
        if( !$name ) {
            $name = "CTP";
        }
        return $name;
    }

    public function tokenTruncate($string, $your_desired_width) {
        if( !$string ) {
            return "";
        }
        $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $postfix = null;
        $length = 0;
        $last_part = 0;
        for (; $last_part < $parts_count; ++$last_part) {
            $length += strlen($parts[$last_part]);
            if ($length > $your_desired_width) {
                $postfix = "...";
                break;
            }
        }

        $res = implode(array_slice($parts, 0, $last_part));
        $res = trim($res) . $postfix;
        //$res = $res . $postfix;
        //echo "res=[".$res."]<br>";

        return $res;    //implode(array_slice($parts, 0, $last_part)).$postfix;
    }

    public function getUnpaidInvoiceRemindersCount( $startDate, $endDate, $projectSpecialtyObjects ) {

        $projectSpecialtyObjectStr = null;
        if( count($projectSpecialtyObjects) > 0 ) {
            $projectSpecialtyObjectStr = $projectSpecialtyObjects[0]->getUppercaseShortName();
            //echo "projectSpecialtyObjectStr=".$projectSpecialtyObjectStr."<br>";
//            $specialtyApcpObject = $this->getSpecialtyObject("ap-cp");
//            if( $specialtyApcpObject ) {
//                $projectSpecialtyObjectStr = $specialtyApcpObject->getName();
//            }
        }

        $dqlParameters = array();

        //get the date from event log
        $repository = $this->em->getRepository('AppUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");
        $dql->innerJoin('logger.eventType', 'eventType');
        //$dql->leftJoin('logger.objectType', 'objectType');
        //$dql->leftJoin('logger.site', 'site');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        //$dql->where("logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());

        //Work Request ID APCP843-REQ16216 billing state has been changed to Invoiced, triggered by invoice status change to Unpaid/Issued
        $dql->where("logger.entityNamespace = 'App\TranslationalResearchBundle\Entity' AND logger.entityName = 'Invoice'");
        //$dql->where("logger.entityName = 'Invoice'");

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Unpaid Invoice Reminder Email";

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        $endDate->modify('+1 day');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        if( $projectSpecialtyObjectStr ) {
            $dql->andWhere("logger.event LIKE :specialtyName");
            $eventStr = "Reminder email for the unpaid Invoice " . $projectSpecialtyObjectStr;
            //echo "eventStr=".$eventStr."<br>";
            $dqlParameters['specialtyName'] = "%" . $eventStr . "%";
            //or use $eventType = "Unpaid Invoice Reminder Email"
        }

        $dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //exit();

        return count($loggers);
    }

    public function getDelayedProjectRemindersCount( $startDate, $endDate, $projectSpecialtyObjects ) {

        $projectSpecialtyObjectStr = null;
        if( count($projectSpecialtyObjects) > 0 ) {
            $projectSpecialtyObjectStr = $projectSpecialtyObjects[0]->getUppercaseShortName();
        }

        $dqlParameters = array();

        //get the date from event log
        $repository = $this->em->getRepository('AppUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");
        $dql->innerJoin('logger.eventType', 'eventType');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());
        //$dql->where("logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());

        //Work Request ID APCP843-REQ16216 billing state has been changed to Invoiced, triggered by invoice status change to Unpaid/Issued
        $dql->where("logger.entityNamespace = 'App\TranslationalResearchBundle\Entity' AND logger.entityName = 'Project'");
        //$dql->where("logger.entityName = 'Invoice'");

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Project Reminder Email";

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        $endDate->modify('+1 day');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        if( $projectSpecialtyObjectStr ) {
            $dql->andWhere("logger.event LIKE :specialtyName");
            $eventStr = "Reminder email for the Project " . $projectSpecialtyObjectStr;
            //echo "eventStr=".$eventStr."<br>";
            $dqlParameters['specialtyName'] = "%" . $eventStr . "%";
            //or use $eventType = "Unpaid Invoice Reminder Email"
        }

        $dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //exit();

        return count($loggers);
    }
    public function getDelayedRequestRemindersCount( $startDate, $endDate, $projectSpecialtyObjects, $states=null ) {

        //$transresRequestUtil = $this->container->get('transres_request_util');

        $projectSpecialtyObjectStr = null;
        if( count($projectSpecialtyObjects) > 0 ) {
            $projectSpecialtyObjectStr = $projectSpecialtyObjects[0]->getUppercaseShortName();
        }

        $dqlParameters = array();

        //get the date from event log
        $repository = $this->em->getRepository('AppUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");
        $dql->innerJoin('logger.eventType', 'eventType');

        //$dql->where("logger.siteName = 'translationalresearch' AND logger.entityName = 'Invoice' AND logger.entityId = ".$invoice->getId());

        //Work Request ID APCP843-REQ16216 billing state has been changed to Invoiced, triggered by invoice status change to Unpaid/Issued
        $dql->where("logger.entityNamespace = 'App\TranslationalResearchBundle\Entity' AND logger.entityName = 'TransResRequest'");
        //$dql->where("logger.entityName = 'Invoice'");

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Work Request Reminder Email";

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        //$startDate->modify('-1 day');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        $endDate->modify('+1 day');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        if( $projectSpecialtyObjectStr ) {
            $dql->andWhere("logger.event LIKE :specialtyName");
            $eventStr = "Reminder email for the Work Request " . $projectSpecialtyObjectStr;
            //echo "eventStr=".$eventStr."<br>";
            $dqlParameters['specialtyName'] = "%" . $eventStr . "%";
            //or use $eventType = "Unpaid Invoice Reminder Email"
        }

        if( $states && count($states) > 0 ) {
            $statesArr = array();
            foreach($states as $state) {
                $statesArr[] = "logger.event LIKE '%$state%'";
            }
            if( count($statesArr) > 0 ) {
                $stateStr = implode(" OR ", $statesArr);
                $stateStr = "(" . $stateStr . ")";
                //echo "stateStr=".$stateStr."<br>";
                $dql->andWhere($stateStr);
            }
        }

        $dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //exit();

        return count($loggers);
    }

    public function getLoginCount( $startDate, $endDate, $site='translationalresearch', $unique=false ) {
        $dqlParameters = array();

        //get the date from event log
        $repository = $this->em->getRepository('AppUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");

        $dql->select("user.id");

        $dql->innerJoin('logger.user', 'user');
        $dql->innerJoin('logger.eventType', 'eventType');

        if( $unique ) {
            $dql->distinct();
        }

        //$dql->where("logger.siteName = 'translationalresearch'");
        //$dql->where("logger.siteName = '".$site."'");

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Successful Login";

        if( $site ) {
            $dql->andWhere("logger.siteName = :siteName");
            $dqlParameters['siteName'] = $site;
        }

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        //$startDate->modify('-1 day');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        $endDate->modify('+1 day');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        //$dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //exit();

        return count($loggers);
    }

    //NOT USED
    public function getLoginsUniqueUser( $startDate, $endDate, $unique=true, $site=null ) {

        $dqlParameters = array();

        //get the date from event log
        $repository = $this->em->getRepository('AppUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");
        //$dql->select("logger");
        //$dql->select("logger.user as user");
        $dql->select("user.id");
        //$dql->select('identity(logger.user)');
        if( $unique ) {
            $dql->distinct();
        }
        //$dql->groupBy("user.id");
        $dql->innerJoin('logger.user', 'user');
        $dql->innerJoin('logger.eventType', 'eventType');

        $dql->where("user.id IS NOT NULL");

        if( $site ) {
            //$dql->where("logger.siteName = 'translationalresearch'");
            $dql->andWhere("logger.siteName = '" . $site . "'");
        }

        $dql->andWhere("eventType.name = :eventTypeName");
        $dqlParameters['eventTypeName'] = "Successful Login";

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        //$startDate->modify('-1 day');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        $endDate->modify('+1 day');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        //$dql->orderBy("logger.id","DESC");
        $query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //$logger = $loggers[0];
        //print_r($loggers);
        //exit();

        return $loggers;
    }

    public function getTrpMessageToUsers($project=null) {
        //$transresUtil = $this->container->get('transres_util');
        $showMessageToUsers = $this->getTransresSiteProjectParameter('showMessageToUsers',$project);
        if( $showMessageToUsers ) {
            $messageToUsers = $this->getTransresSiteProjectParameter('messageToUsers',$project);
        } else {
            $messageToUsers = null;
        }

        return $messageToUsers;
    }

    public function getHumanTissueFormNote($project=null) {
        $messageToUsers = $this->getTransresSiteProjectParameter('humanTissueFormNote',$project);
        return $messageToUsers;
    }

    public function getPriceListColor($priceList) {
        if( $priceList ) {
            return "darkorange";
        }
        return NULL;
    }
    public function getPriceListColorByProject($project) {
        $priceList = $project->getPriceList();
        return $this->getPriceListColor($priceList);
    }
    public function getPriceListColorByRequest($request) {
        $priceList = $request->getPriceList();
        return $this->getPriceListColor($priceList);
    }
    public function getPriceListColorByInvoice($invoice) {
        $request = $invoice->getTransresRequest();
        $priceList = $request->getPriceList();
        return $this->getPriceListColor($priceList);
    }

    public function dollarSignValue($value) {
        if( $value !== NULL ) {
            $value = $this->toDecimal($value);
            //echo "value=".$value."<br>";
            if( $value >= 0 ) {
                $value = $this->toMoney($value);
                $value = "$".$value;
            } else {
                $value = abs($value);
                $value = $this->toMoney($value);
                $value = "-$".$value;
            }
        }

        return $value;
    }
//    public function moneyDollarSignValue($value) {
//        if( $value !== NULL ) {
//            $value = $this->toDecimal($value);
//            echo "value=".$value."<br>";
//            if( $value >= 0 ) {
//                $value = "$".$value;
//            } else {
//                $value = "-$".abs($value);
//            }
//        }
//
//        return $value;
//    }
    public function toDecimal($number) {
        return number_format((float)$number, 2, '.', '');
    }
    public function toMoney($number) {
        return number_format((float)$number, 2, '.', ',');
    }

}