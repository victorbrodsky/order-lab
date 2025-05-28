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



use App\TranslationalResearchBundle\Entity\AntibodyLabList;
use App\TranslationalResearchBundle\Entity\AntibodyList;
use App\TranslationalResearchBundle\Entity\AntibodyPanelList;
use App\TranslationalResearchBundle\Entity\ProjectGoal;
use App\TranslationalResearchBundle\Entity\TransResRequest; //process.py script: replaced namespace by ::class: added use line for classname=TransResRequest


use App\TranslationalResearchBundle\Entity\Product; //process.py script: replaced namespace by ::class: added use line for classname=Product


use App\UserdirectoryBundle\Entity\Logger; //process.py script: replaced namespace by ::class: added use line for classname=Logger


use App\TranslationalResearchBundle\Entity\RequestCategoryTypeList; //process.py script: replaced namespace by ::class: added use line for classname=RequestCategoryTypeList


use App\TranslationalResearchBundle\Entity\Prices; //process.py script: replaced namespace by ::class: added use line for classname=Prices


use App\TranslationalResearchBundle\Entity\DefaultReviewer; //process.py script: replaced namespace by ::class: added use line for classname=DefaultReviewer


use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User


use App\TranslationalResearchBundle\Entity\CollDivList; //process.py script: replaced namespace by ::class: added use line for classname=CollDivList


use App\TranslationalResearchBundle\Entity\RequesterGroupList; //process.py script: replaced namespace by ::class: added use line for classname=RequesterGroupList


use App\TranslationalResearchBundle\Entity\Project; //process.py script: replaced namespace by ::class: added use line for classname=Project


use App\UserdirectoryBundle\Entity\Roles; //process.py script: replaced namespace by ::class: added use line for classname=Roles


use App\TranslationalResearchBundle\Entity\WorkQueueList; //process.py script: replaced namespace by ::class: added use line for classname=WorkQueueList


use App\TranslationalResearchBundle\Entity\PriceTypeList; //process.py script: replaced namespace by ::class: added use line for classname=PriceTypeList
use App\TranslationalResearchBundle\Form\ReviewBaseType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Cache\Exception\LogicException;
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
//use Symfony\Component\Cache\Simple\ApcuCache;
//use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
//use Zend\Cache\StorageFactory;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Bundle\SecurityBundle\Security;

use Symfony\Component\Workflow\WorkflowInterface;

/**
 * Created by PhpStorm.
 * User: Oleg Ivanov
 * Date: 8/25/2017
 * Time: 09:48 AM
 */
class TransResUtil
{

    protected $container;
    protected $em;
    protected $security;
    protected $session;

    // Symfony will inject the 'blog_publishing' workflow configured before => WorkflowInterface $blogPublishingWorkflow
    //transres_project => WorkflowInterface $transresProjectStateMachine
    protected $transresProjectStateMachine;

    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container,
        Security $security,
        Session $session,
        WorkflowInterface $transresProjectStateMachine
    ) {
        $this->container = $container;
        $this->em = $em;
        $this->security = $security;
        $this->session = $session;
        $this->transresProjectStateMachine = $transresProjectStateMachine;
    }

    public function getWorkflowByName($workflowName) {
        if( $workflowName === 'state_machine.transres_project' ) {
            return $this->transresProjectStateMachine;
        }
        return NULL;
    }
    
    //MAIN method to show allowed transition to state links
    //get links to change states: Reject IRB Review and Approve IRB Review (translationalresearch_transition_action)
    public function getReviewEnabledLinkActions( $review ) {
        //exit("get review links");
        $project = $review->getProject();
        //$workflow = $this->container->get('state_machine.transres_project');
        $workflow = $this->transresProjectStateMachine;
        $transitions = $workflow->getEnabledTransitions($project);
        $user = $this->security->getUser();

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
                if( strpos((string)$transitionName, "missinginfo") !== false ) {
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
                        if( !$this->security->isGranted('ROLE_TRANSRES_ADMIN') ) {
                            continue;
                        }
                    }
//                    $committeReviewer = $this->isProjectStateReviewer($project,$user,"committee_review",true);
//                    if( $committeReviewer ) {
//                        //show link to committee_finalreview_approved
//                    } else {
//                        //echo "not primary or committee reviewer <br>";
//                        if( !$this->security->isGranted('ROLE_TRANSRES_ADMIN') ) {
//                            continue;
//                        }
//                    }

                }
            }

            if( false === $this->isUserAllowedFromThisStateByProjectAndReview($project,$review) ) {
                //echo "Skip is UserAllowedFromThisStateByProjectAndReview<br>";
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
                //if( strpos((string)$transitionName, "missinginfo") !== false ) {
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
        //$workflow = $this->container->get('state_machine.transres_project');
        $workflow = $this->transresProjectStateMachine;
        $transitions = $workflow->getEnabledTransitions($project);

        $links = array();
        foreach( $transitions as $transition ) {

            //$this->printTransition($transition);
            $transitionName = $transition->getName();
            //echo "transitionName=".$transitionName."<br>";

            //quick fix: only for missinginfo state
            if( strpos((string)$transitionName, "missinginfo") !== false ) {
                return;
            }

            //$tos = $transition->getTos();
            $froms = $transition->getFroms();
            foreach( $froms as $from ) {
                //echo "from=".$from."<br>"; //irb_review

                //only if transitionName=irb_review_resubmit == irb class
                $statePrefixArr= explode("_", $transitionName); //irb
                $statePrefix = $statePrefixArr[0];
                if( strpos((string)$review->getStateStr(), $statePrefix) === false ) {
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

    //Used in sendReminderReviewProjectsBySpecialty (ReminderUtil.php)
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
            $reviews = $project->getAdminReviews(true);
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
        if( strpos((string)$state, '_rejected') !== false || $state == 'draft' ) { //|| strpos((string)$state, "_missinginfo") !== false
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

        if( !$project ) {
            return false;
        }

        $user = $this->security->getUser();

        if( $project->getSubmitter() && $project->getSubmitter()->getId() == $user->getId() ) {
            return true;
        }
        if( $project->getPrincipalInvestigators()->contains($user) ) {
            return true;
        }
        if( $project->getPrincipalIrbInvestigators()->contains($user) ) {
            return true;
        }
        if( $project->getSubmitInvestigators()->contains($user) ) {
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
                $user = $this->security->getUser();
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

            if( $this->security->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr) ) {
                return true;
            }
        }

        return false;
    }
    public function isPrimaryReviewer( $project=null, $strictReviewer=false ) { //$projectSpecialty=null
        if( $strictReviewer ) {
            //$strictReviewer - check if user is an admin reviewer of this particular project
            if( $project ) {
                $user = $this->security->getUser();
                if( $this->isReviewsReviewer($user, $project->getFinalReviews()) ) {
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

            if( $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr) ) {
                return true;
            }
        }

        return false;
    }
    public function isAdminOrPrimaryReviewer( $project=null, $projectSpecialty=null ) { //$projectSpecialty=null
        //$projectSpecialty = null;
        $specialtyStr = null;
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
        }
        if( $projectSpecialty ) {
            $specialtyStr = $projectSpecialty->getUppercaseName();
            $specialtyStr = "_" . $specialtyStr;
        }
        if(
        $this->security->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr)
        ) {
            return true;
        }

        if(
        $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr)
        ) {
            return true;
        }

        //if user included in the project's reviewer directly
        if( $project ) {
            if( $this->isProjectReviewer($project) ) {
                return true;
            }
        }

        //check if user is a primary reviewer of this particular project
        if( $project ) {
            $user = $this->security->getUser();
            if( $this->isReviewsReviewer($user, $project->getFinalReviews()) ) {
                return true;
            }
        }

        return false;
    }
    public function hasReviewerRoles() {
        if( $this->security->isGranted('ROLE_TRANSRES_IRB_REVIEWER') ) {
            return true;
        }
        if( $this->security->isGranted('ROLE_TRANSRES_ADMIN') ) {
            return true;
        }
        if( $this->security->isGranted('ROLE_TRANSRES_COMMITTEE_REVIEWER') ) {
            return true;
        }
        if( $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ) {
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

        $user = $this->security->getUser();
        if( $this->isReviewsReviewer($user,$project->getIrbReviews()) ) {
            return true;
        }
        if( $this->isReviewsReviewer($user,$project->getAdminReviews()) ) {
            //TODO: probably need to add the check if admin is funded/non-funded
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
            $this->security->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr)
        ) {
            return true;
        }

        if(
            $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr)
        ) {
            return true;
        }

        if(
            $this->security->isGranted('ROLE_TRANSRES_EXECUTIVE'.$specialtyStr)
        ) {
            return true;
        }

        //if user included in the project's reviewer directly
        if( $project ) {
            if( $this->isProjectReviewer($project) ) {
                return true;
            }
        }

        return false;
    }

    //If user has roles other than requester
    public function isAdvancedUser( $project=null ) {
        //check only if user is admin, executive for the project specialty
        //or user is a primary (final) reviewer of this particular project

//        $projectSpecialty = null;
//        $specialtyStr = null;
//        if( $project ) {
//            $projectSpecialty = $project->getProjectSpecialty();
//        }
//        if( $projectSpecialty ) {
//            $specialtyStr = $projectSpecialty->getUppercaseName();
//            $specialtyStr = "_" . $specialtyStr;
//        }
//        echo "1 specialtyStr=$specialtyStr <br>";

        $specialtyStr = $this->getProjectSpecialtyStr($project);
        //echo "2 specialtyStr=$specialtyStr <br>";

        if(
            $this->security->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr)
        ) {
            return true;
        }

        if(
            $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr)
        ) {
            return true;
        }

        if(
            $this->security->isGranted('ROLE_TRANSRES_EXECUTIVE'.$specialtyStr)
        ) {
            return true;
        }

        if(
            $this->security->isGranted('ROLE_TRANSRES_TECHNICIAN'.$specialtyStr)
        ) {
            return true;
        }

        if(
            $this->security->isGranted('ROLE_TRANSRES_BILLING_ADMIN'.$specialtyStr)
        ) {
            return true;
        }

        //if user included in the project's reviewer directly
        if( $project ) {
            if( $this->isProjectReviewer($project) ) {
                return true;
            }
        }

        return false;
    }

    public function isTech( $project=null ) {
        $specialtyStr = $this->getProjectSpecialtyStr($project);
        //echo "isTech: specialtyStr=$specialtyStr, role=".'ROLE_TRANSRES_TECHNICIAN'.$specialtyStr."<br>";
        if( $this->security->isGranted('ROLE_TRANSRES_TECHNICIAN'.$specialtyStr) ) {
            return true;
        }
        return false;
    }
    public function isAdmin( $project=null ) {
        $specialtyStr = $this->getProjectSpecialtyStr($project);
        if( $this->security->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr) ) {
            return true;
        }
        return false;
    }
    public function isComiteeReviewer( $project=null ) {
        $specialtyStr = $this->getProjectSpecialtyStr($project);
        if( $this->security->isGranted('ROLE_TRANSRES_COMMITTEE_REVIEWER'.$specialtyStr) ) {
            return true;
        }
        return false;
    }

    public function getProjectSpecialtyStr( $project ) {
        $projectSpecialty = null;
        $specialtyStr = "";
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
        }
        if( $projectSpecialty ) {
            $specialtyStr = $projectSpecialty->getUppercaseName();
            $specialtyStr = "_" . $specialtyStr;
        }
        return $specialtyStr;
    }
    
//    public function isAdminOrPrimaryReviewerOrExecutive_ORIG( $project=null ) {
//        //TODO: implement check only if user is admin, executive for the project specialty
//        //TODO: or user is a primary (final) reviewer of this particular project
//        if(
//            $this->security->isGranted('ROLE_TRANSRES_ADMIN_APCP') ||
//            $this->security->isGranted('ROLE_TRANSRES_ADMIN_HEMATOPATHOLOGY') ||
//            $this->security->isGranted('ROLE_TRANSRES_ADMIN_COVID19') ||
//            $this->security->isGranted('ROLE_TRANSRES_ADMIN_MISI') ||
//            $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_APCP') ||
//            $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_HEMATOPATHOLOGY') ||
//            $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_COVID19') ||
//            $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_MISI') ||
//            $this->security->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') ||
//            $this->security->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP') ||
//            $this->security->isGranted('ROLE_TRANSRES_EXECUTIVE_COVID19') ||
//            $this->security->isGranted('ROLE_TRANSRES_EXECUTIVE_MISI')
//        ) {
//            return true;
//        }
//        return false;
//    }

    //check if user:
    //listed as project requesters (principalInvestigators and principalIrbInvestigator, Co-Investigators, pathologists, Contacts, Billing contacts)
    //Site Admin/Executive Committee/Platform Admin/Deputy Platform Admin) and
    //ONLY for Non-Funded projects with status = Final Approved or Closed
    //rename isAdminPiBillingAndApprovedClosed( $project ) to isAdminPrimaryRevExecutiveOrRequester
    public function isAdminPrimaryRevExecutiveOrRequester( $project ) {
        //hide the remaining budget for non-funded
        if( $project->getFunded() ) {
            return false;
        }

        //hide the remaining budget for non-funded, closed projects
        //ONLY for projects with status = Final Approved or Closed
        if(
            $project->getState() == "final_approved"
            || $project->getState() == "closed"
        ) {
            //Continue: show remaining budget only for "final_approved" projects
        } else {
            //hide the remaining budget for all not "final_approved" projects
            return false;
        }

        //Site Admin/Executive Committee/Platform Admin/Deputy Platform Admin)
        if( $this->isAdminOrPrimaryReviewerOrExecutive($project) ) {
            return true;
        }

        //isProjectRequester: principalInvestigators and principalIrbInvestigator,
        // Co-Investigators, pathologists, Contacts, Billing contacts
        if( $this->isProjectRequester($project) === true ) {
            return true;
        }

        return false;
    }

    public function showRemainingBudgetForProjects( $projects ) {
        //echo "projects=".count($projects)."<br>";
        $showRemainingBudget = false;
        foreach( $projects as $project ) {
            if( $this->isAdminPrimaryRevExecutiveOrRequester($project) ) {
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

        if( $this->isAdminPrimaryRevExecutiveOrRequester($project) ) {
            $remainingBudgetValue = $project->getRemainingBudget();
            //echo "remainingBudget=[$remainingBudget] <br>";

            if( $remainingBudgetValue !== NULL ) {
                $remainingBudget = $this->dollarSignValue($remainingBudgetValue);
                $adminEmailsStr = $this->getAdminEmailsStr($project,false);
                $trpName = $this->getBusinessEntityAbbreviation();

                $divWell = '<div class="well well-sm">';

                if( $remainingBudgetValue > 0 ) {
                    $alertClass = "alert-secondary";
                } else {
                    $alertClass = "alert-warning";
                }
                $div = '<div id="project-remaining-budget-note-alert" class="alert '.$alertClass.'" role="alert">';

                $note = "Based on this project’s approved budget, invoices, work requests, ".
                        "and the items selected below, the remaining budget appears to be ".
                        "<span id='project-remaining-budget-amount'>".$remainingBudget."</span>".
                        "." .
                        "<br>If you have any questions, please email the $trpName administrator ".$adminEmailsStr;

                $note = $divWell.
                            $div.
                                "<p>".
                                    "<h4>" . $note . "</h4>".
                                "</p>".
                            "</div>".
                        "</div>";

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

        $adminEmailsStr = $this->getAdminEmailsStrBySpecialty($project,$projectSpecialty,$all);

        return $adminEmailsStr;
    }
    //Add project to pass it to getTransResAdminEmails
    public function getAdminEmailsStrBySpecialty($project,$projectSpecialty=NULL,$all=true) {
        $adminEmailsStr = "";
        $adminUsers = $this->getTransResAdminEmails($project, false, true); //replaced $projectSpecialty by project
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

        if( !$adminEmailsStr ) {
            $adminEmailsStr = $this->getTransresSiteProjectParameter('fromEmail',null,$projectSpecialty);
            //echo "trpemail=$adminEmailsStr <br>";
        }

        if( !$adminEmailsStr ) {
            $userSecUtil = $this->container->get('user_security_utility');
            $adminEmails = $userSecUtil->getUserEmailsByRole(null,"Platform Administrator");
            if (count($adminEmails) > 0) {
                if( $all ) {
                    $adminEmailsStr = implode(", ", $adminEmails);
                } else {
                    $adminEmailsStr = $adminEmails[0];
                }
            }
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
        //$user = $this->security->getUser();

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

            $adminEmails = $this->getTransResAdminEmails($project, true, true); //send ProjectOverBudgetEmail

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

        $adminEmails = $this->getTransResAdminEmails($project, true, true); //send ProjectApprovedBudgetUpdateEmail

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

        $adminEmails = $this->getTransResAdminEmails($project, true, true); //send ProjectNoBudgetUpdateEmail

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
            strpos((string)$projectState, '_rejected') !== false
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
        //$workflow = $this->container->get('state_machine.transres_project');
        $workflow = $this->transresProjectStateMachine;
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
        $user = $this->security->getUser();
        $transresUtil = $this->container->get('transres_util');
        //$workflow = $this->container->get('state_machine.transres_project');
        $workflow = $this->transresProjectStateMachine;
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

                        //In the notification email body add a sentence as a separate paragraph above the links stating
                        // “The project description is attached to this email.”
                        $body = $body.$break.$break. "The project description is attached to this email.";

                        /////////////////// Email to Admin ///////////////////////
                        //get project url
                        $projectUrl = $transresUtil->getProjectShowUrl($project);
                        $emailBody = $body . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;

                        //To review this project request, please visit the link below: LINK-TO-REVIEW-PROJECT-REQUEST
                        $emailBody = $emailBody .$break.$break. "To review this project request, please visit the link below:";
                        $emailBody = $emailBody . $break. $projectReviewUrl;

                        //send notification emails (project transition: committee recomendation - committe_review)
                        $admins = $this->getTransResAdminEmails($project,true,true); //set Transition

                        $attachmentArr = $this->getProjectAttachments($project);

                        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
                        $emailUtil->sendEmail( $admins, $subject, $emailBody, null, $senderEmail, $attachmentArr );

                        if( $subject && $emailBody ) {
                            $emailResAdmin = "Email To: ".implode("; ",$admins);
                            $emailResAdmin = $emailResAdmin . $break . "Subject: " . $subject . "<br>" . "Body: " . $emailBody;
                            $emailResAdmin = str_replace($break, "<br>", $emailResAdmin);
                            $emailRes[] = $emailResAdmin;
                        }
                        /////////////////// EOF Email to Admin ///////////////////////

                        /////////////////// Email to Primary Reviewer, TO: PRIMARY COMMITTEE REVIEWER ONLY ///////////////////////
                        $emailBody = $body .$break.$break. "At the time of this notification, the status of this project request is '$originalStateLabel'.";

                        //In the notification email body add a sentence as a separate paragraph above the links stating
                        // “The project description is attached to this email.”
                        $emailBody = $emailBody.$break.$break. "The project description is attached to this email.";

                        //To review this project request, please visit the link below: LINK-TO-REVIEW-PROJECT-REQUEST
                        $emailBody = $emailBody .$break.$break. "To review this project request, please visit the link below:";
                        $emailBody = $emailBody . $break. $projectReviewUrl;

                        //send notification emails (project transition: committee recomendation - committe_review)
                        $primaryReviewerEmails = $this->getCommiteePrimaryReviewerEmails($project); //ok

                        $attachmentArr = $this->getProjectAttachments($project);

                        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
                        $emailUtil->sendEmail( $primaryReviewerEmails, $subject, $emailBody, null, $senderEmail, $attachmentArr );

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

                        //TODO: fix it!
                        if( $this->session ) {
                            $this->session->getFlashBag()->add(
                                'notice',
                                $subject
                            );
                        }

                        return true;
                    }
                }

                if( $to === "final_approved" ) {
                    $project->setApprovalDate(new \DateTime());

                    //update expiration date only once on final_approved
                    if( !$project->getExpectedExpirationDate() ) {
                        $this->calculateAndSetProjectExpectedExprDate($project); //Status changed
                    }
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

                //TODO: fix it!
                if( $this->session ) {
                    $this->session->getFlashBag()->add(
                        'notice',
                        $this->getNotificationMsgByStates($originalStateStr, $to, $project)    //"Successful action: ".$label
                    );
                }
                return true;
            } catch (LogicException $e) {
                //event log

                $logger = $this->container->get('logger');
                $logger->error("Action failed (setTransition): ".$this->getTransitionLabelByName($transitionName).", Error:".$e);

                //TODO: fix it!
                if( $this->session ) {
                    $this->session->getFlashBag()->add(
                        'warning',
                        "Action failed (setTransition): " . $this->getTransitionLabelByName($transitionName) . "<br> Error:" . $e
                    );
                }
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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:DefaultReviewer'] by [DefaultReviewer::class]
            $defaultReviewers = $this->em->getRepository(DefaultReviewer::class)->findBy(
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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:DefaultReviewer'] by [DefaultReviewer::class]
            $defaultReviewers = $this->em->getRepository(DefaultReviewer::class)->findBy(
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

                        //add reviewProjectType boolean
                        $reviewEntity->setReviewProjectType($defaultReviewer->getReviewProjectType());
                        
                        $project->addAdminReview($reviewEntity);
                    }
                }
            }
        }

        $committeeReviewState = "committee_review";
        if( $currentState == $committeeReviewState || $addForAllStates === true || $addForAllStates === $irbReviewState ) {

            //$defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findByState($committeeReviewState,array("primaryReview"=>"DESC"));
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:DefaultReviewer'] by [DefaultReviewer::class]
            $defaultReviewers = $this->em->getRepository(DefaultReviewer::class)->findBy(
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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:DefaultReviewer'] by [DefaultReviewer::class]
            $defaultReviewers = $this->em->getRepository(DefaultReviewer::class)->findBy(
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

        $specialtyName = '';
        if( $specialty ) {
            $specialtyName = $specialty->getName();
        }

        //$defaultReviewers = $this->em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findByState($state,array('primaryReview' => 'DESC'));
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:DefaultReviewer'] by [DefaultReviewer::class]
        $defaultReviewers = $this->em->getRepository(DefaultReviewer::class)->findBy(
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
            if( $defaultReviewer->getState() == "admin_review" ) {
                $reviewProjectType = $defaultReviewer->getReviewProjectType(); //Funded, Non-Funded
                if( $reviewProjectType ) {
                    //Admin Reviewer for non-funded AP/CP projects. Admin Reviewer for funded AP/CP projects.
                    $reviewProjectType = "Admin Reviewer for $reviewProjectType $specialtyName projects";
                    //$info .= " (<font color=\"#8063FF\">".$reviewProjectType."</font>)";
                } else {
                    //Admin Reviewer for all AP/CP projects
                    $reviewProjectType = "Admin Reviewer for all $specialtyName projects";
                }
                $info .= " (<font color=\"#8063FF\">".$reviewProjectType."</font>)";
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

    public function processProjectGoals( $project ) {
        $user = $this->security->getUser();
        foreach( $project->getProjectGoals() as $projectGoal ) {
            if( $projectGoal->getAuthor() ) {
                //set update author and date only if description or status changed
                //This information can not be retrieved here, but it can be done in the doctrine listener
                //$projectGoal->setUpdateAuthor($user);
                //$projectGoal->setUpdatedate(new \DateTime());
            } else {
                $projectGoal->setAuthor($user);
            }

            //set orderinlist if not set
            if( $project->getId() ) {
                if (!$projectGoal->getOrderinlist()) {
                    $orderinlist = $this->findNextProjectGoalOrderinlist($project->getId());
                    if ($orderinlist) {
                        $projectGoal->setOrderinlist($orderinlist);
                    }
                }
            }

            //set status
            if( $projectGoal->getStatus() === NULL ) {
                $projectGoal->setStatus('enable');
            }
        }
    }

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

        if( $state == "irb_review" || $state == "draft" ) { //strpos((string)$state, "irb_") !== false
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
            //$this->addTesterRole($reviewer);
        }
        //remove role: make sure if the user is not a default reviewer in all other objects. Or don't remove role at all.
        //if( $originalReviewer && $originalReviewer != $reviewer ) {
            //$originalReviewer->removeRole($reviewerRole);
        //}

        $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
        if( $reviewerDelegate ) {
            $reviewerDelegate->addRole($reviewerRole);
            //$this->addTesterRole($reviewerDelegate);
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

    public function getTransitionLabelByName( $transitionName, $review=null ) {

        //$returnLabel = "<$transitionName>";
        //$userSecUtil = $this->container->get('user_security_utility');
        $humanName = $this->getHumanName();

        //echo "transitionName=$transitionName <br>";

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
                $label = "Approve Project Request as a Result of Scientific Committee Review";
                $labeled = "Approved Project Request as a Result of Scientific Committee Review";
                //echo "Reviewer=".$review->getReviewer()."<br>";
                if( $review && method_exists($review, 'getPrimaryReview') ) {
                    //echo "committee_review_approved PrimaryReview =".$review->getPrimaryReview()."<br>";
                    if( $review->getPrimaryReview() === true ) {
                        //$label = $label . " as Primary Reviewer";
                        //$labeled = $labeled . " as Primary Reviewer";
                    } else {
                        $userInfo = $this->getReviewerInfo($review);
                        $label = "Recommend Approval as a Result of Scientific Committee Review" . $userInfo;
                        $labeled = "Recommended Approval as a Result of Scientific Committee Review" . $userInfo;
                    }
                }
                //echo "label=$label<br>";
                break;
            case "committee_review_rejected":
                $label = "Reject Project Request as a Result of Scientific Committee Review";
                $labeled = "Rejected Project Request as a Result of Scientific Committee Review";
                if( $review && method_exists($review, 'getPrimaryReview') ) {
                    if ($review->getPrimaryReview() === true) {
                        //$label = $label . " as Primary Reviewer";
                        //$labeled = $labeled . " as Primary Reviewer";
                    } else {
                        $userInfo = $this->getReviewerInfo($review);
                        //$label = "Recommend Reject Committee Review".$userInfo;
                        $label = "Recommend Rejection as a Result of Scientific Committee Review" . $userInfo;
                        $labeled = "Recommended Rejection as a Result of Scientific Committee Review" . $userInfo;
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
                $label = "Resubmit Project Request to Scientific Committee Review";
                $labeled = "Resubmitted Project Request to Scientific Committee Review";
                break;

            case "final_review_approved":
                $label = "Approve Project Request as a Result of Financial and Programmatic Review";
                $labeled = "Approved Project Request as a Result of Financial and Programmatic Review";
                break;
            case "final_review_rejected":
                $label = "Reject Project Request as a Result of Financial and Programmatic Review";
                $labeled = "Rejected Project Request as a Result of Financial and Programmatic Review";
                break;
//            case "final_review_missinginfo":
//                $label = "Request additional information from submitter for Final Review";
//                $labeled = "Requested additional information from submitter for Final Review";
//                break;
            case "final_review_resubmit":
                $label = "Resubmit Project Request to Financial and Programmatic Review";
                $labeled = "Resubmitted Project Request to Financial and Programmatic Review";
                break;

            case "committee_finalreview_approved":
                $label = "Provide Financial and Programmatic Approval";
                $labeled = "Provided Financial and Programmatic Approval";
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

            //$returnLabel = ucwords($label);
            $returnLabel = $label; //testing lowercase
        }

        //convert to lowercase with the first letter as a capital:
        //Are you sure you want to Approve Project Request -> Are you sure you want to approve project request
        $returnLabel = strtolower($returnLabel);
        $returnLabel = ucfirst($returnLabel); //converts the first character of a string to uppercase

        //if not the actual reviewer show name "(as Mister John)"
        //if( $transitionName != "committee_finalreview_approved" ) {
        //TODO: to diffirentiate, add if actual user can not use this $transitionName
        if( strpos((string)$transitionName, "finalreview_approved") === false ) {
            $user = $this->security->getUser();
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
                if (strpos((string)$returnLabel, "(as ") === false) {
                    $userInfo = $this->getReviewerInfo($review);
                    $returnLabel = $returnLabel . $userInfo;
                }
            }
        }

        //echo "returnLabel=$returnLabel<br>";
        return $returnLabel;
    }
    public function getReviewerInfo($review) {
        $userInfo = "";
        if( $review && $this->security->isGranted('ROLE_TRANSRES_ADMIN') ) {
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
                $state = "Scientific Committee Review";
                break;
            case "committee_rejected":
                $state = "Scientific Committee Review Rejected";
                break;
//            case "committee_missinginfo":
//                $state = "Pending additional information from submitter for Committee Review";
//                break;

            case "final_review":
                $state = "Financial and Programmatic Review";
                break;
            case "final_approved":
                $state = "Approved";
                break;
            case "final_rejected":
                $state = "Financial and Programmatic Review Rejected";
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
                $state = "Scientific Committee Review";
                break;
            case "committee_rejected":
                $state = "Scientific Committee Review Rejected";
                break;
//            case "committee_missinginfo":
//                $state = "Request additional information from submitter for Committee Review";
//                break;

            case "final_review":
                $state = "Financial and Programmatic Review";
                break;
            case "final_approved":
                $state = "Approved";
                break;
            case "final_rejected":
                $state = "Financial and Programmatic Review Rejected";
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
        if( strpos((string)$state, "irb_") !== false ) {
            if( $asClassName ) {
                return "IrbReview";
            } else {
                return "irb_review";
            }
        }
        if( strpos((string)$state, "admin_") !== false ) {
            if( $asClassName ) {
                return "AdminReview";
            } else {
                return "admin_review";
            }
        }
        if( strpos((string)$state, "committee_") !== false ) {
            if( $asClassName ) {
                return "CommitteeReview";
            } else {
                return "committee_review";
            }
        }
        if( strpos((string)$state, "final_") !== false ) {
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
        if( strpos((string)$transitionName, "_approved") !== false ) {
            if( strpos((string)$transitionName, "finalreview_approved") !== false ) {
                return "btn btn-warning transres-review-submit"; //btn-primary
            }
            return "btn btn-success transres-review-submit";
        }
        if( strpos((string)$transitionName, "_missinginfo") !== false ) {
            return "btn btn-warning transres-review-submit transres-missinginfo";
        }
        if( strpos((string)$transitionName, "_rejected") !== false ) {
            return "btn btn-danger transres-review-submit";
        }
        if( strpos((string)$transitionName, "_resubmit") !== false ) {
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
//        if( strpos((string)$transitionName, "_approved") !== false ) {
//            return "approved";
//        }
//        if( strpos((string)$transitionName, "_missinginfo") !== false ) {
//            return "missinginfo";
//        }
//        if( strpos((string)$transitionName, "_rejected") !== false ) {
//            return "rejected";
//        }
//        if( strpos((string)$transitionName, "_resubmit") !== false ) {
//            return null;
//        }
//
//
//        return null;
//    }

    //Test: {"transitionName":"irb_review_approved","id":"3958","reviewId":"3974"} =>
    //http://127.0.0.1/translational-research/project-review-transition/irb_review_approved/3564/1396
    public function getReviewByReviewidAndState($reviewId, $state) {

        $reviewEntityName = $this->getReviewClassNameByState($state);
        if( !$reviewEntityName ) {
            throw new \Exception('Unable to find Review Entity Name by state='.$state);
        }

        //echo "reviewId=".$reviewId."<br>";
        //echo "reviewEntityName=".$reviewEntityName."<br>";
        //exit('111');

        //$reviewObject = $this->em->getRepository('AppTranslationalResearchBundle:'.$reviewEntityName)->find($reviewId);
        $reviewObject = $this->em->getRepository('App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName)->find($reviewId);
        if( !$reviewObject ) {
            throw new \Exception('Unable to find '.$reviewEntityName.' by id='.$reviewId."; state=$state");
        }
        //exit('eof');

        return $reviewObject;
    }

    //Used in DashboardUtil.php (getDiffDaysByProjectState, getStateExitDate)
    //Used in this (getSingleReviewByProject, getNextStateReviewersEmails)
    public function getReviewsByProjectAndState( $project, $state ) {
        $reviewEntityName = $this->getReviewClassNameByState($state);
        if( !$reviewEntityName ) {
            throw new \Exception('Unable to find Review Entity Name by state='.$state);
        }

        $reviews = $this->findReviewObjectsByProjectAndAnyReviewers($reviewEntityName,$project); //DB array
        //echo "state=$state, reviews count=".count($reviews)."<br>";
        //exit('000');

        //filter by project funded/non-funded
        if( $state == "admin_review" ) {
            $newAdminReviews = array();
            //$funded = $project->getFunded();
            foreach($reviews as $adminReview) {
                if( $project->isAdminReviewerByType($adminReview) ) {
                    $newAdminReviews[] = $adminReview;
                }
            }

            return $newAdminReviews;
        }

        return $reviews;
    }

    public function getSingleReviewByProject($project) {
        //echo $project->getId().": state=".$project->getState()."<br>"; //testing
        $reviews = $this->getReviewsByProjectAndState($project,$project->getState());
        //take the first review
        if( count($reviews) == 1 ) {
            return $reviews[0];
        }
        return null;
    }

    public function getSingleTransitionNameByProject($project) {
        //$workflow = $this->container->get('state_machine.transres_project');
        $workflow = $this->transresProjectStateMachine;
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
        //$workflow = $this->container->get('state_machine.transres_project');
        $workflow = $this->transresProjectStateMachine;
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
        //$repository = $this->em->getRepository('AppTranslationalResearchBundle:' . $reviewObjectClassName);
        $repository = $this->em->getRepository('App\\TranslationalResearchBundle\\Entity\\' . $reviewObjectClassName);
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

        //echo "projectId=".$project->getId()."<br>";
        //echo "reviewObjectClassName=".$reviewObjectClassName."<br>";
        //echo "reviewObjects count=".count($reviewObjects)."<br>";
        //exit('000');

        return $reviewObjects;
    }

    //NOT USED: roles are not relable for each project
    //add user's validation (rely on Role): $from=irb_review => user has role _IRB_REVIEW_
    public function isUserAllowedFromThisStateByRole($from) {

        if(
            $this->security->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER')
        ) {
            return true;
        }

        $user = $this->security->getUser();
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

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        if( $role && $this->em->getRepository(User::class)->isUserHasSiteAndPartialRoleName($user,$sitename,$role) ) {
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
//        $user = $this->security->getUser();
//
//        //check if reviewer
//        //$project, $user, $stateStr=null, $onlyReviewer=false
//        if( $this->isProjectStateReviewer($project,$user) ) {
//            return true;
//        }
//
//        //check if submitter and project state has _missinginfo
////        if( strpos((string)$stateStr, "_missinginfo") !== false ) {
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
        //echo "is UserAllowedFromThisStateByProjectAndReview: reviewer=".$review->getReviewer()." <br>";
        $user = $this->security->getUser();

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

            //check if funded/non-funded admin reviewer if $review is AdminReview
            if( $review instanceof AdminReview ) {
                if( $project->isAdminReviewerByType($review) ) {
                    return true;
                }
            } else {
                return true; //admin if $review is not AdminReview (???)
            }

            //return true;
        }

//        if( $this->isPrimaryReviewer($project) ) {
//            return true;
//        }

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

        if( strpos((string)$stateStr, "_missinginfo") !== false ) {
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

        //return false;
        //return true;

        //echo $stateStr.": reviews count=".count($reviews)."<br>";
        //exit('111');
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
        //$workflow = $this->container->get('state_machine.transres_project');
        $workflow = $this->transresProjectStateMachine;

        //$userUtil = $this->container->get('user_utility');
        //$workflow = $userUtil->getWorkflowByString('state_machine.transres_project');

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
        if( strpos((string)$state,"_") !== false ) {
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
            $this->security->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER')
        ) {
            //echo "isUserAllowedReview: admin ok <br>";
            return true;
        }

        $user = $this->security->getUser();
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
        //$projectStateReviewable = false;
        $projectState = $project->getState();
        //echo "projectId=".$project->getId()."<br>";
        //echo "projectState=".$projectState."<br>";

        //check if the $review->getStateStr() has prefix (i.e. irb)
        //only if transitionName=irb_review_resubmit == irb class
        $statePrefixArr= explode("_", $projectState); //irb,review
        $statePrefix = $statePrefixArr[0]; //irb
        if( strpos((string)$review->getStateStr(), $statePrefix) !== false ) {
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
//        $user = $this->security->getUser();
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

        //$workflow = $this->container->get('state_machine.transres_project');
        $workflow = $this->transresProjectStateMachine;
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

            if( strpos((string)$transitionName, '_rejected') !== false ) {
                echo "to: No<br>";
                $transitionNameNo = $transitionName;
                $tos = $transition->getTos();
                if( count($tos) > 1 ) {
                    throw new \Exception("State machine must have only one to state. To count=".count($tos));
                }
                $toNo = $tos[0];
            }

            if (strpos((string)$transitionName, '_approved') !== false ) {
                echo "to: Yes<br>";
                $transitionNameYes = $transitionName;
                $tos = $transition->getTos();
                if (count($tos) > 1) {
                    throw new \Exception("State machine must have only one to state. To count=" . count($tos));
                }
                $toYes = $tos[0];
            }

            if (strpos((string)$transitionName, '_missinginfo') !== false ) {
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

                //TODO: fix it!
                if( $this->session ) {
                    $this->session->getFlashBag()->add(
                        'notice',
                        "Successful transition: " . $transitionNameFinal . "; Project request is in " . $this->getStateLabelByProject($project) . " stage."
                    );
                }

            } catch (LogicException $e) {
                throw new \Exception("Can not change project's state: transitionNameFinal=" . $transitionNameFinal);
            }
        }

        echo "setProjectState: appliedTransition= $appliedTransition <br>";

        return $appliedTransition;
    }

    //Event Log
    public function setEventLog($project, $eventType, $event, $testing=false) {
        $user = $this->security->getUser();
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
        $admins = $this->getTransResAdminEmails($project,true,true); //send NotificationEmails (function NOT USED)
        $emails = array_merge($emails,$admins);

        //project's submitter only
        $submitter = $project->getSubmitter()->getSingleEmail(false);
        $emails = array_merge($emails, array($submitter));

        // 2) project's Requester (submitter, principalInvestigators, submitInvestigators, coInvestigators, pathologists)
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

        $emails = array_unique($emails);

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $subject, $body, null, $senderEmail );

    }

    //Use to send notification emails for project transition (awaiting review, missing info, rejected, final, closed)
    public function sendTransitionEmail($project,$originalStateStr,$testing=false,$reason=NULL) {
        $emailUtil = $this->container->get('user_mailer_utility');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->security->getUser();
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

            //In the notification email body add a sentence as a separate paragraph above the links stating
            // “The project description is attached to this email.”
            $body = $body.$break.$break. "The project description is attached to this email.";

            $body = $body . $break.$break;
            $body = $body . "To review this project request, please visit the link below:";
            $body = $body . $break. $projectReviewUrl;

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project,true,true); //send TransitionEmail

            $attachmentArr = $this->getProjectAttachments($project);

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail, $attachmentArr );
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

            //In the notification email body add a sentence as a separate paragraph above the links stating
            // “The project description is attached to this email.”
            $body = $body.$break.$break. "The project description is attached to this email.";

            //To supply the requested information and re-submit for review, please visit:
            $projectResubmitUrl = $this->getProjectResubmitUrl($project);
            $body = $body . $break.$break. "To supply the requested information and re-submit for review, please visit the following link:".$break.$projectResubmitUrl;

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project,true,true); ////send TransitionEmail

            $attachmentArr = $this->getProjectAttachments($project);

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail, $attachmentArr );
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
            $statusChangeMsg = $this->getNotificationMsgByStates($originalStateStr,$currentStateStr,$project,$reason);
            //get project url
            $projectUrl = $this->getProjectShowUrl($project);
            $body = $statusChangeMsg . $break.$break. "To view the details of this project request, please visit the link below:".$break.$projectUrl;

            //In the notification email body add a sentence as a separate paragraph above the links stating
            // “The project description is attached to this email.”
            $body = $body.$break.$break. "The project description is attached to this email.";

            //If you have any questions, please contact
            // [FirstNameOfCurrentTRPAdminForCorrespondingSpecialty-AP/CPorHemePath
            // LastNameOfCurrentTRPAdminForCorrespondingSpecialty-AP/CPorHemePath
            // email@domain.tld – list all users with TRP sysadmin roles associated with project specialty separated by comma ]
            $body = $body . $break.$break. "If you have any questions, please contact";
            $admins = $this->getTransResAdminEmails($project,false,true); //send TransitionEmail
            $adminInfos = array();
            foreach( $admins as $admin ) {
                $adminInfos[] = $admin->getUsernameOptimal() . " " . $admin->getSingleEmail(false);
            }
            if( count($adminInfos) > 0 ) {
                $body = $body . " " . implode(", ",$adminInfos);
            }

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project,true,true); //send TransitionEmail

            $attachmentArr = $this->getProjectAttachments($project);

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail, $attachmentArr );
        }

        //Case: Final Approved
        if(
            $currentStateStr == "final_approved"
        ) {
            $emailRecipients = $this->getRequesterMiniEmails($project);

            $subject = "Project request $oid has been approved";

            //"Additional information has been requested for the project with ID $id '".$title."' for the '".$fromLabel."' stage.";
            $body = $this->getNotificationMsgByStates($originalStateStr,$currentStateStr,$project,$reason);

            //Add note about links via VPN
            $body = $body.$break.$break."The links below are accessible while you are connected to the institution's network or by first connecting via VPN.";

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

            //In the notification email body add a sentence as a separate paragraph above the links stating
            // “The project description is attached to this email.”
            $body = $body.$break.$break. "The project description is attached to this email.";

            //get project url
            $projectUrl = $this->getProjectShowUrl($project);
            $body = $body . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project,true,true); //send TransitionEmail

            //testing
//            $logger = $this->container->get('logger');
//            $logger->notice('emailRecipients:['.implode("|",$emailRecipients).']');
//            $logger->notice('adminsCcs:['.implode("|",$adminsCcs).']');
//            $logger->notice('senderEmail:['.$senderEmail.']');

            $attachmentArr = $this->getProjectAttachments($project);

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail, $attachmentArr );
        }

        //All other cases: final approved, closes ...
        if( $subject && $body ) {
            //ok
        } else {
            $emailRecipients = $this->getRequesterMiniEmails($project);

            $subject = "Project request $oid status has been changed from '$originalStateLabel' to '$currentStateLabel'";
            $subject = $subject . " by " . $user;

            //"Additional information has been requested for the project with ID $id '".$title."' for the '".$fromLabel."' stage.";
            $body = $this->getNotificationMsgByStates($originalStateStr,$currentStateStr,$project,$reason);

            //In the notification email body add a sentence as a separate paragraph above the links stating
            // “The project description is attached to this email.”
            $body = $body.$break.$break. "The project description is attached to this email.";

            //get project url
            $projectUrl = $this->getProjectShowUrl($project);
            $body = $body . $break.$break. "To view this project request, please visit the link below:".$break.$projectUrl;

            //Admins as css
            $adminsCcs = $this->getTransResAdminEmails($project,true,true); //send TransitionEmail

            $attachmentArr = $this->getProjectAttachments($project);

            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
            $emailUtil->sendEmail( $emailRecipients, $subject, $body, $adminsCcs, $senderEmail, $attachmentArr );
        }

        if( $subject && $body ) {
            $msg = "Email To: ".implode("; ",$emailRecipients);
            $msg = $msg . $break . "Email Css: ".implode("; ",$adminsCcs);
            $msg = $msg . $break . "Subject: " . $subject . "<br>" . "Body: " . $body;
            $msg = str_replace($break, "<br>", $msg);
        }

        return $msg;
    }


    //1) if project specified => filter ROLE_TRANSRES_ADMIN by project's funded/non-funded
    //2) get all users with admin and ROLE_TRANSRES_PRIMARY_REVIEWER, ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE
    //Replaced $projectSpecialty by $project
    //Must return at least one TRP admin
    public function getTransResAdminEmails($project=null, $asEmail=true, $onlyAdmin=false) {
        $users = array();
        $admins = array();

        $projectSpecialty = NULL;
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
        }

        if( $projectSpecialty ) {
            $specialtyPostfix = $projectSpecialty->getUppercaseName();
            $specialtyPostfix = "_" . $specialtyPostfix;
        } else {
            $specialtyPostfix = null;
        }
        //echo "specialtyPostfix="."ROLE_TRANSRES_ADMIN".$specialtyPostfix." <br>";

        //1) try to get specific admins from project
        if( $project ) {
            $admins = $project->getAdminUserReviewers(true);
            //echo "admins1=".count($admins)."<br>";
        }

        //2) get admins from DB
        if( count($admins) == 0 ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            $admins = $this->em->getRepository(User::class)->findUsersByRoles(array("ROLE_TRANSRES_ADMIN" . $specialtyPostfix));
            //echo "admins2=".count($admins)."<br>";
        }

        foreach( $admins as $user ) {
            if( $user ) {
                if( $asEmail ) {
                    $userEmail = $user->getSingleEmail(false);
                    if( in_array($userEmail, $users) == false ) {
                        $users[] = $userEmail;
                    }
                } else {
                    if( in_array($user, $users) == false ) {
                        $users[] = $user;
                    }
                }
            }
        }

        if( $onlyAdmin ) {
            return $users;
        }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $primarys = $this->em->getRepository(User::class)->findUsersByRoles(array("ROLE_TRANSRES_PRIMARY_REVIEWER".$specialtyPostfix));
        foreach( $primarys as $user ) {
            if( $user ) {
                if( $asEmail ) {
                    $userEmail = $user->getSingleEmail(false);
                    if( in_array($userEmail, $users) == false ) {
                        $users[] = $userEmail;
                    }
                } else {
                    if( in_array($user, $users) == false ) {
                        $users[] = $user;
                    }
                }
            }
        }

        return $users;
    }
    //project's Requester (submitter, principalInvestigators, submitInvestigators, coInvestigators, pathologists)
    public function getRequesterEmails($project, $asEmail=true, $withBillingContact=true) {
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

        //2b submitInvestigators
        $submitInvestigators = $project->getSubmitInvestigators();
        foreach( $submitInvestigators as $submitInvestigator ) {
            if( $submitInvestigator ) {
                if( $asEmail ) {
                    $resArr[] = $submitInvestigator->getSingleEmail(false);
                } else {
                    $resArr[] = $submitInvestigator;
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
        if( $withBillingContact ) {
            $billingContacts = $project->getBillingContacts();
            foreach ($billingContacts as $billingContact) {
                if ($billingContact) {
                    if ($asEmail) {
                        $resArr[] = $billingContact->getSingleEmail(false);
                    } else {
                        $resArr[] = $billingContact;
                    }
                }
            }
        }

        $resArr = array_unique($resArr);

        return $resArr;
    }
    //project's Requester (submitter, principalInvestigators, submitInvestigators, coInvestigators, pathologists)
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

        $resArr = array_unique($resArr);

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

        $resArr = array_unique($resArr);

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
        $reviews = $this->getReviewsByProjectAndState($project,$nextStateStr); //filtered by funded/non-funded project
        foreach($reviews as $review) {
            $currentReviewerEmails = $this->getCurrentReviewersEmails($review); //ok
            $emails = array_merge($emails,$currentReviewerEmails);
        }

        $emails = array_unique($emails);

        return $emails;
    }

    public function sendComputationalEmail($project) {
        $compEmails = array();

        //Get compEmailUsers from TRP site settings (Deprecated. Replaced by ROLE_TRANSRES_BIOINFORMATICIAN)
        //$compEmailUsers = $this->getTransresSiteProjectParameter('compEmailUsers',$project);

        //Get all users with role ROLE_TRANSRES_BIOINFORMATICIAN
        $compEmailUsers = $this->em->getRepository(User::class)->findUsersByRoles(array("ROLE_TRANSRES_BIOINFORMATICIAN"));
        //echo "compEmailUsers=".count($compEmailUsers).'<br>';

        //dump($compEmailUsers);
        foreach($compEmailUsers as $compEmailUser) {
            //echo "compEmails=".$compEmailUser->getSingleEmail().'<br>';
            $compEmails[] = $compEmailUser->getSingleEmail();
        }

        //echo "compEmails count=".count($compEmails).'<br>';
        if( count($compEmails) == 0 ) {
            //exit('emails 0');
            return 'Email has not been sent: Computational pathology users are not specified';
        }

        $compEmailSubject = $this->getTransresSiteProjectParameter('compEmailSubject',$project);
        if( !$compEmailSubject ) {
            $compEmailSubject = 'Project Request #'.$project->getOid().
                ' specified bioinformatician or computational pathology involvement';
        }
        $compEmailSubject = $this->replaceTextByNamingConvention($compEmailSubject,$project,null,null);

        $compEmailBody = $this->getTransresSiteProjectParameter('compEmailBody',$project);
        if( !$compEmailBody ) {
            $compEmailBody = '
            [[PROJECT SUBMITTER]] has specified the need for a bioinformatician / informatics support'.
            ' or computational pathology involvement while submitting Project ID [[PROJECT ID]]'.
            ' Titled [[PROJECT TITLE]] for PI [[PROJECT PIS]] on [[PROJECT SUBMISSION DATE]].'.
            '<br><br>[[PROJECT COMPUTATIONAL CATEGORIES]]'.
            '<br><br>[[PROJECT STAT/INFORMATICS SUPPORT]].'.
            '<br><br>The project details can be reviewed below: <br>[[PROJECT SHOW URL]]'.
            '<br><br>Contact information of the submitter: <br>[[PROJECT SUBMITTER DETAILS]]';
        }
        $compEmailBody = $this->replaceTextByNamingConvention($compEmailBody,$project,null,null);

        //dump($compEmails);
        //dump($compEmailSubject);
        //dump($compEmailBody);
        //exit('sendComputationalEmail');

        $emailUtil = $this->container->get('user_mailer_utility');
        $senderEmail = $this->getTransresSiteProjectParameter('fromEmail',$project);
        $adminsCcs = $this->getTransResAdminEmails($project,true,true); //new project after save
        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail($compEmails,$compEmailSubject,$compEmailBody,$adminsCcs,$senderEmail);

        return 'Subject:'.$compEmailSubject.'; receivers: '.implode(', ',$compEmails);
    }

    public function getTransResCollaborationDivs() {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:CollDivList'] by [CollDivList::class]
        $collDivs = $this->em->getRepository(CollDivList::class)->findBy(
            array(
                'type' => array("default","user-added")
            ),
            array('orderinlist' => 'ASC')
        );

        return $collDivs;
    }
    public function getCollaborationDivObject( $collDivStr ) {
        //echo "requesterGroupStr=".$requesterGroupStr."<br>";
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:CollDivList'] by [CollDivList::class]
        $collDiv = $this->em->getRepository(CollDivList::class)->findOneByUrlSlug($collDivStr);

        if( !$collDiv ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequesterGroupList'] by [RequesterGroupList::class]
            $repository = $this->em->getRepository(RequesterGroupList::class);
            $dql =  $repository->createQueryBuilder("list");

            $dql->andWhere("LOWER(list.urlSlug) = LOWER(:urlSlug)");
            $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
            $query->setParameter('urlSlug',$collDivStr);
            $collDivs = $query->getResult();
            //echo "requesterGroups=".count($requesterGroups)."<br>";

            if( count($collDivs) == 1 ) {
                $collDiv = $collDivs[0];
            }
        }

        if( !$collDiv ) {
            //throw new \Exception( "Project Collaboration Division is not found by name '".$collDivStr."'" );
            return NULL;
        }

        return $collDiv;
    }

    public function getTransResProjectSpecialties( $userAllowed=true ) {

        $user = $this->security->getUser();

        $specialties = $this->em->getRepository(SpecialtyList::class)->findBy(
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
//    //experimental
//    public function getTransResProjectSpecialtiesQuery($userAllowed = true) {
//        $user = $this->security->getUser();
//
//        $qb = $this->em->createQueryBuilder();
//        $qb->select('s')
//            ->from(SpecialtyList::class, 's')
//            ->where($qb->expr()->in('s.type', [':default', ':user_added']))
//            ->orderBy('s.orderinlist', 'ASC')
//            ->setParameter(':default', 'default')
//            ->setParameter(':user_added', 'user-added');
//
//        if ($userAllowed && $user) {
//            $qb->andWhere(':user MEMBER OF s.allowedUsers')
//                ->setParameter(':user', $user);
//        }
//
//        return $qb->getQuery()->getResult();
//    }

    //Specialties filtered by enableProjectOnConfig
    public function getTransResEnableProjectOnConfigSpecialties( $userAllowed=true ) {
        $specialties = $this->getTransResProjectSpecialties($userAllowed);

        $allowedSpecialties = array();

        foreach($specialties as $specialty) {
            if( $this->getTransresSiteProjectParameter('enableProjectOnConfig',null,$specialty) === true ) {
                $allowedSpecialties[] = $specialty;
            }
        }

        return $allowedSpecialties;
    }

    //Specialties filtered by enableProjectOnNavbar
    public function getTransResEnableProjectOnNavbar( $userAllowed=true ) {
        $specialties = $this->getTransResProjectSpecialties($userAllowed);

        $allowedSpecialties = array();

        foreach($specialties as $specialty) {
            if( $this->getTransresSiteProjectParameter('enableProjectOnNavbar',null,$specialty) === true ) {
                $allowedSpecialties[] = $specialty;
            }
        }

        return $allowedSpecialties;
    }

    //Get list of the projects visible to admin, technicians, executives ...
    public function getProjectsAllowedByUser( $user ) {

        //echo "getProjectsAllowedByUser:". $user->getId() ."<br>";
        if( !$user ) {
            return null;
        }

        $transresPermissionUtil  = $this->container->get('transres_permission_util');
        
//        //Check if logged in user has general role: admin, technicians, executives
//        if( $transresPermissionUtil->hasProjectPermission('view') == false ) {
//            return null;
//        }

        //getTransResProjectSpecialties will return all users that associated with the partial role, even project requester
        $specialties = $this->getTransResProjectSpecialties($userAllowed=true);

        //get project with these specialties
        $repository = $this->em->getRepository(Project::class);
        $dql = $repository->createQueryBuilder("project");
        //$dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $specialtyIds = array();
        foreach( $specialties as $specialtyObject ) {
            //Check if logged in user has general role: admin, technicians, executives
            if( $transresPermissionUtil->hasProjectPermission('view',null,$specialtyObject) ) {
                $specialtyIds[] = $specialtyObject->getId();
            }
        }

        //echo "specialtyIds count=".count($specialtyIds)."<br>";
        if( count($specialtyIds) > 0 ) {
            $dql->leftJoin("project.projectSpecialty", "projectSpecialty");
            $specialtyStr = "projectSpecialty.id IN (".implode(",",$specialtyIds).")";
            //echo "specialtyStr=$specialtyStr<br>";
            $dql->andWhere($specialtyStr);
        } else {
            //Logged in user does not have any specific specialty roles such as admin, technicians, executives
            return null;
        }

        //And user is associated
        $dql->leftJoin('project.principalInvestigators','principalInvestigators');
        $dql->leftJoin('project.principalIrbInvestigator','principalIrbInvestigator');
        $dql->leftJoin('project.submitInvestigators','submitInvestigators');
        $dql->leftJoin('project.coInvestigators','coInvestigators');
        $dql->leftJoin('project.pathologists','pathologists');
        $dql->leftJoin('project.contacts','contacts');
        $dql->leftJoin('project.billingContact','billingContact');
        $dql->leftJoin('project.submitter','submitter');
        $showAssCriterion =
            "principalInvestigators.id = (:assUserId) OR ".
            "principalIrbInvestigator.id = (:assUserId) OR ".
            "submitInvestigators.id = (:assUserId) OR ".
            "coInvestigators.id = (:assUserId) OR ".
            "pathologists.id = (:assUserId) OR ".
            "contacts.id = (:assUserId) OR ".
            "billingContact.id = (:assUserId) OR ".
            "submitter.id = (:assUserId)";

        $dql->andWhere($showAssCriterion);
        $dqlParameters["assUserId"] = $user->getId();

        $query = $dql->getQuery();

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $projects = $query->getResult();
        return $projects;
    }

    public function getTransResRequesterGroups() {

        //$user = $this->security->getUser();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequesterGroupList'] by [RequesterGroupList::class]
        $groups = $this->em->getRepository(RequesterGroupList::class)->findBy(
            array(
                'type' => array("default","user-added")
            ),
            array('orderinlist' => 'ASC')
        );

        return $groups;
    }

    public function getRequesterGroupObject( $requesterGroupStr ) {
        //echo "requesterGroupStr=".$requesterGroupStr."<br>";
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequesterGroupList'] by [RequesterGroupList::class]
        $requesterGroup = $this->em->getRepository(RequesterGroupList::class)->findOneByUrlSlug($requesterGroupStr);

        if( !$requesterGroup ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequesterGroupList'] by [RequesterGroupList::class]
            $repository = $this->em->getRepository(RequesterGroupList::class);
            $dql =  $repository->createQueryBuilder("list");

            $dql->andWhere("LOWER(list.urlSlug) = LOWER(:urlSlug)");
            $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
            $query->setParameter('urlSlug',$requesterGroupStr);
            $requesterGroups = $query->getResult();
            //echo "requesterGroups=".count($requesterGroups)."<br>";

            if( count($requesterGroups) == 1 ) {
                $requesterGroup = $requesterGroups[0];
            }
        }

        if( !$requesterGroup ) {
            //throw new \Exception( "Project requester group is not found by name '".$requesterGroupStr."'" );
            return NULL;
        }

        return $requesterGroup;
    }

    public function getTransResAntibodyLabs( $onlyWithPanel=false ) {
        $repository = $this->em->getRepository(AntibodyLabList::class);
        $dql =  $repository->createQueryBuilder("list");

        $dql->andWhere("list.type = :typedef OR list.type = :typeadd");

        //Check if antibody has panels to show it on the navbar's "Antibody Panel List"
        if( $onlyWithPanel ) {
            $dql->leftJoin("list.antibodies", "antibodies");
            //$dql->leftJoin("antibodies.antibodyLabs", "antibodyLabs");
            $dql->leftJoin("antibodies.antibodyPanels", "antibodyPanels");

            //$dql->andWhere("antibodyLabs IS NOT NULL");
            $dql->andWhere("antibodyPanels IS NOT NULL");
        }

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
        );

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters($parameters);

        $labs = $query->getResult();

        return $labs;
    }

    //$labsStr = 'TRP|MISI'
    public function getTransResAntibodyPanels( $labsStr ) {
        $repository = $this->em->getRepository(AntibodyPanelList::class);
        $dql =  $repository->createQueryBuilder("list");

        $dql->leftJoin('list.antibodies','antibodies');
        $dql->leftJoin('antibodies.antibodyLabs','antibodyLabs');

        $dql->andWhere("list.type = :typedef OR list.type = :typeadd");

        //echo "labsStr=$labsStr <br>";
        $labFullNameArr = array();
        $labsArr = array();
        if( $labsStr ) {
            $labsArr = explode('|',$labsStr);
            $labsCriterionArr = array();
            foreach($labsArr as $lab) {
                $labsCriterionArr[] = "antibodyLabs.abbreviation = '".$lab."'";

                //get AntibodyLabList entity and then get fulle name
                $labEntity = $this->em->getRepository(AntibodyLabList::class)->findOneByAbbreviation($lab);
                if( $labEntity ) {
                    $labFullNameArr[] = $labEntity->getFullName(); //Multiparametric In Situ (MISI)
                }
            }
            $labsCriterionStr = implode(' OR ',$labsCriterionArr);
            //echo "labsCriterionStr=$labsCriterionStr <br>";
            if( $labsCriterionStr ) {
                $dql->andWhere($labsCriterionStr);
            }
        }

        $parameters = array(
            'typedef' => 'default',
            'typeadd' => 'user-added',
        );

        //Sort by list orderinlist
        $dql->orderBy("list.orderinlist","ASC");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters($parameters);

        $panels = $query->getResult();

        $res = array(
            'panels' => $panels,
            //'labsStr' => $labsStr,
            'labsArr' => $labFullNameArr
        );

        return $res;
    }
    
    public function getAntibodiesByPanel( $panel ) {
        $repository = $this->em->getRepository(AntibodyList::class);
        $dql =  $repository->createQueryBuilder("list");

        $dql->leftJoin('list.antibodyPanels','antibodyPanels');

        $dql->where("antibodyPanels.id = :panelId");
        $dql->andWhere("list.type = :typedef OR list.type = :typeadd");

        $parameters = array(
            'panelId' => $panel->getId(),
            'typedef' => 'default',
            'typeadd' => 'user-added',
        );

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters($parameters);

        $antibodies = $query->getResult();

        return $antibodies;
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

        $user = $this->security->getUser();

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

        //$thread = $this->container->get('fos_comment.manager.thread')->findThreadById($threadId);
        $thread = $this->container->get('user_comment_utility')->findThreadById($threadId);
        //echo "thread=[$thread] <br>";

        if( $thread ) {
            //$thread->setCommentable(false);
            //$comments = $this->container->get('fos_comment.manager.comment')->findCommentTreeByThread($thread);
            $comments = $this->container->get('user_comment_utility')->findCommentTreeByThread($thread);
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
//    public function getCommentTreeStr($comments,$newline,$level=0) {
//        $res = "";
//        foreach($comments as $commentArr) {
//            $comment = $commentArr['comment'];
//            $res = $res . $this->getCommentPrefixSpace($level) . $comment->getCommentShort() . $newline;
//            $children = $commentArr['children'];
//            $res = $res . $this->getCommentTreeStr($children,$newline,($level+1));
//            //$res = $res . $newline;
//        }
//        return $res;
//    }
    public function getCommentTreeStr($comments,$newline,$level=0) {
        $res = "";
        foreach($comments as $comment) {
            //$comment = $commentArr['comment'];
            //$res = $res . $this->getCommentPrefixSpace($level) . $comment->getCommentShort() . $newline;
            //$children = $commentArr['children'];
            //$res = $res . $this->getCommentTreeStr($children,$newline,($level+1));
            //$res = $res . $newline;

            $res = $res . $this->getCommentPrefixSpace($level) . $comment->getCommentShort() . $newline;
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
        //$commentManager = $this->container->get('fos_comment.manager.comment');
        //$threadManager = $this->container->get('fos_comment.manager.thread');
        $commentThreadManager = $this->container->get('user_comment_utility');

        //FosCommentListenerUtil
        //$commentThreadManager = $this->container->get('user_comment_listener_utility');

        $author = $this->security->getUser();

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

        $thread = $commentThreadManager->findThreadById($threadId);

        if( null === $thread ) {
            $thread = $commentThreadManager->createThread();
            $thread->setId($threadId);

            //$permalink = $uri . "/project/review/" . $entity->getId();
            $thread->setPermalink($permalink);

            $thread->setCommentable(true);
            $thread->incrementNumberOfComments();

            // Add the thread
            $commentThreadManager->saveThread($thread);
        }

        //set Author
        $parentComment = null;
        $comment = $commentThreadManager->createComment($thread,$parentComment);
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

//        if ($commentThreadManager->saveComment($comment) !== false) {
//            //exit("Comment saved successful!!!");
//            //return $this->getViewHandler()->handle($this->onCreateCommentSuccess($form, $threadId, null));
//            //View::createRouteRedirect('fos_comment_get_thread_comment', array('id' => $id, 'commentId' => $form->getData()->getId()), 201);
//        }

        $this->container->get('user_comment_listener_utility')->onCommentPrePersist($comment);
        $this->container->get('user_comment_utility')->saveComment($comment);
        $this->container->get('user_comment_listener_utility')->onCommentPostPersist($comment);

        return $comment;
    }
//    public function getAuthorType( $entity ) {
//
//        if( !$this->security ) {
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
//        if( $this->security->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyStr) ) {
//            //$authorType = "Administrator";
//            $authorTypeArr['type'] = "Administrator";
//            $authorTypeArr['description'] = "Administrator";
//            return $authorTypeArr;
//        }
//        if( $this->security->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER'.$specialtyStr) ) {
//            //$authorType = "Primary Reviewer";
//            $authorTypeArr['type'] = "Administrator";
//            $authorTypeArr['description'] = "Primary Reviewer";
//            return $authorTypeArr;
//        }
//
//        //if not found
//        $transresUtil = $this->container->get('transres_util');
//        $transresRequestUtil = $this->container->get('transres_request_util');
//        $user = $this->security->getUser();
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
        //echo "specialtyAbbreviation=".$specialtyAbbreviation."<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialty = $this->em->getRepository(SpecialtyList::class)->findOneByAbbreviation($specialtyAbbreviation);

        if( !$specialty ) {
            $specialtyAbbreviationLower = strtolower($specialtyAbbreviation);
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
            $specialty = $this->em->getRepository(SpecialtyList::class)->findOneByAbbreviation($specialtyAbbreviationLower);
        }

        if( !$specialty ) {
            //throw new \Exception( "Project specialty is not found by name '".$specialtyAbbreviation."'" );
            return NULL;
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

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $repository = $this->em->getRepository(SpecialtyList::class);
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
        
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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

        $user = $this->security->getUser();
        if( $this->isReviewer($user,$reviewObject) ) {
            return true;
        }



        return false;
    }

    //get list of projects: 1) state final_approved, 2) irbExpirationDate, 3) logged in user is requester, 4) reviewer
    public function getAvailableProjects( $finalApproved=true, $notExpired=true, $requester=true, $reviewer=true, $orderBy="project.id", $orderDir="DESC" ) {

        //$transresRequestUtil = $this->container->get('transres_request_util');

        $user = $this->security->getUser();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(Project::class);
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

        $dql->leftJoin('project.submitInvestigators','submitInvestigators');
        $dql->leftJoin('project.coInvestigators','coInvestigators');
        $dql->leftJoin('project.pathologists','pathologists');
        $dql->leftJoin('project.billingContact','billingContact');
        $dql->leftJoin('project.contacts','contacts');

        //$dql->orderBy("project.id","DESC");
        $dql->orderBy($orderBy,$orderDir);

        $dqlParameters = array();

        //1) state final_approved
        if( $finalApproved ) {
            $dql->andWhere("project.state = 'final_approved'");
            //$dqlParameters = array("state" => "final_approved");
        }

        //2) irb/iacuc ExpirationDate (implicitExpirationDate) and expectedExpirationDate
        if( $notExpired ) {
            //check implicitExpirationDate
            $dql->andWhere("project.implicitExpirationDate IS NULL OR project.implicitExpirationDate >= CURRENT_DATE()");

            //check expectedExpirationDate
            $dql->andWhere("project.expectedExpirationDate IS NULL OR project.expectedExpirationDate >= CURRENT_DATE()");
        }

        //3) logged in user is requester (only if not admin)
        if( $requester ) {
            if (!$this->security->isGranted("ROLE_TRANSRES_ADMIN")) {
                $myRequestProjectsCriterion =
                    "principalInvestigators.id = :userId OR " .
                    "principalIrbInvestigator.id = :userId OR " .
                    "submitInvestigators.id = :userId OR " .
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
        if( !$this->security->isGranted("ROLE_TRANSRES_ADMIN") ) {
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
        $user = $this->security->getUser();
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(Project::class);
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

        $dql->leftJoin('project.submitInvestigators','submitInvestigators');
        $dql->leftJoin('project.coInvestigators','coInvestigators');
        $dql->leftJoin('project.pathologists','pathologists');
        $dql->leftJoin('project.billingContact','billingContact');
        $dql->leftJoin('project.contacts','contacts');

        $dql->leftJoin("project.projectSpecialty", "projectSpecialty");

        $dql->orderBy("project.id","DESC");

        $dqlParameters = array();

        //1) logged in user is requester (only if not admin)
        if(
            //!$this->security->isGranted("ROLE_TRANSRES_ADMIN") &&
            //!$this->security->isGranted("ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY")  &&
            //!$this->security->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP')
            !$this->isAdminOrPrimaryReviewerOrExecutive() &&
            !$this->security->isGranted("ROLE_TRANSRES_TECHNICIAN")
        ) {
            $myRequestProjectsCriterion =
                "principalInvestigators.id = :userId OR " .
                "principalIrbInvestigator.id = :userId OR " .
                "submitInvestigators.id = :userId OR " .
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
            'fullClassName' => "App\\TranslationalResearchBundle\\Entity\\Project",
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

            $text = str_replace("[[PROJECT SUBMITTER]]", $project->getSubmitter()->getUsernameOptimal(), $text);

            if( strpos((string)$text, '[[PROJECT SUBMITTER DETAILS]]') !== false ) {
                $text = str_replace("[[PROJECT SUBMITTER DETAILS]]", $project->getProjectSubmitterDetails('<br>'), $text);
            }

            $projectUpdater = $project->getUpdateUser();
            if( $projectUpdater ) {
                $text = str_replace("[[PROJECT UPDATER]]", $projectUpdater->getUsernameShortest(), $text);
                //$text = str_replace("[[PROJECT UPDATER]]", $projectUpdater."", $text);
            }

            if( strpos((string)$text, '[[PROJECT UPDATE DATE]]') !== false ) {
                $projectUpdateDate = $project->getUpdateDate();
                if( $projectUpdateDate ) {
                    $user = $this->security->getUser();
                    $userServiceUtil = $this->container->get('user_service_utility');
                    $projectUpdateDate = $userServiceUtil->convertFromUtcToUserTimezone($projectUpdateDate,$user);
                    $projectUpdateDateStr = $projectUpdateDate->format('m/d/Y \a\t H:i:s');
                    $text = str_replace("[[PROJECT UPDATE DATE]]", $projectUpdateDateStr, $text);
                }
            }

            if( strpos((string)$text, '[[PROJECT TITLE SHORT]]') !== false ) {
                $title = $this->tokenTruncate($project->getTitle(), 15);
                $text = str_replace("[[PROJECT TITLE SHORT]]", $title, $text);
            }

            if( strpos((string)$text, '[[PROJECT PIS]]') !== false ) {
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

            if( strpos((string)$text, '[[PROJECT STATUS]]') !== false ) {
                $state = $this->getStateLabelByProject($project);
                $text = str_replace("[[PROJECT STATUS]]", $state, $text);
            }

            if( strpos((string)$text, '[[PROJECT STATUS COMMENTS]]') !== false ) {
                //$project,$newline="<br>",$state=null,$user=null
                $reviewComments = $this->getReviewComments($project,"<hr>");
                if( $reviewComments ) {
                    $reviewComments = "<hr>" . $reviewComments;
                } else {
                    $reviewComments = "No comments";
                }
                $text = str_replace("[[PROJECT STATUS COMMENTS]]", $reviewComments, $text);
            }

            if( strpos((string)$text, '[[PROJECT SHOW URL]]') !== false ) {
                $projectShowUrl = $this->getProjectShowUrl($project);
                if ($projectShowUrl) {
                    //echo "Project URL=".$projectShowUrl."\n";
                    $text = str_replace("[[PROJECT SHOW URL]]", $projectShowUrl, $text);
                }
            }

            if( strpos((string)$text, '[[PROJECT EDIT URL]]') !== false ) {
                $projectEditUrl = $this->getProjectEditUrl($project);
                if ($projectEditUrl) {
                    $text = str_replace("[[PROJECT EDIT URL]]", $projectEditUrl, $text);
                }
            }

            if( strpos((string)$text, '[[PROJECT PATHOLOGIST LIST]]') !== false ) {
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

            if( strpos((string)$text, '[[PROJECT BILLING CONTACT LIST]]') !== false ) {
                $billingContact = $project->getBillingContact();

                if( !$billingContact ) {
                    $billingContact = "No Billing Contact";
                }

                $text = str_replace("[[PROJECT BILLING CONTACT LIST]]", $billingContact."", $text);
            }

            if( strpos((string)$text, '[[PROJECT REQUESTS URL]]') !== false ) {
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

            if( strpos((string)$text, '[[PROJECT NON-CANCELED INVOICES URL]]') !== false ) {
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
            if( strpos((string)$text, '[[PROJECT PRICE LIST]]') !== false ) {
                $priceList = $project->getPriceList();
                if( $priceList ) {
                    $priceListStr = "'".$priceList->getName()."'";
                } else {
                    //$priceListStr = "Default";
                    $priceListStr = "";
                }
                $text = str_replace("[[PROJECT PRICE LIST]]", $priceListStr, $text);
            }

            if( strpos((string)$text, '[[PROJECT APPROVED BUDGET]]') !== false ) {
                $approvedBudget = $project->getApprovedProjectBudget();
                if( !$approvedBudget ) {
                    $approvedBudget = 0;
                }
                $approvedBudgetStr = $this->dollarSignValue($approvedBudget);
                $text = str_replace("[[PROJECT APPROVED BUDGET]]", $approvedBudgetStr, $text);
            }

            if( strpos((string)$text, '[[PROJECT REMAINING BUDGET]]') !== false ) {
                $remainingBudget = $project->getRemainingBudget();
                if( !$remainingBudget ) {
                    $remainingBudget = 0;
                }
                $remainingBudgetStr = $this->dollarSignValue($remainingBudget);
                $text = str_replace("[[PROJECT REMAINING BUDGET]]", $remainingBudgetStr, $text);
            }

            //[[PROJECT OVER BUDGET]] the same as negative project remaining budget
            if( strpos((string)$text, '[[PROJECT OVER BUDGET]]') !== false ) {
                $remainingBudget = $project->getRemainingBudget();
                if( $remainingBudget < 0 ) {
                    $remainingBudgetStr = $this->dollarSignValue($remainingBudget);
                } else {
                    $remainingBudgetStr = "'No Over Budget'";
                }
                $text = str_replace("[[PROJECT OVER BUDGET]]", $remainingBudgetStr, $text);
            }

            if( strpos((string)$text, '[[PROJECT SUBSIDY]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true);
                $subsidy = $invoicesInfos['subsidy'];
                if( !$subsidy ) {
                    $subsidy = 0;
                }
                $subsidy = $this->dollarSignValue($subsidy);
                $text = str_replace("[[PROJECT SUBSIDY]]", $subsidy, $text);
            }

            if( strpos((string)$text, '[[PROJECT VALUE]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true);
                $grandTotal = $invoicesInfos['grandTotal']; //grand total including subsidy
                if( !$grandTotal ) {
                    $grandTotal = 0;
                }
                $grandTotal = $this->dollarSignValue($grandTotal);
                $text = str_replace("[[PROJECT VALUE]]", $grandTotal, $text);
            }

            if( strpos((string)$text, '[[PROJECT FUNDED]]') !== false ) {
                $isFunded = $project->isFunded(); //"Funded" or "Non-funded"
                $isFunded = strtolower($isFunded);
                $text = str_replace("[[PROJECT FUNDED]]", $isFunded, $text);
            }

            if( strpos((string)$text, '[[PROJECT NUMBER INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.count
                $invoiceCount = $invoicesInfos['count'];
                if( !$invoiceCount ) {
                    $invoiceCount = 0;
                }
                $text = str_replace("[[PROJECT NUMBER INVOICES]]", $invoiceCount, $text);
            }

            if( strpos((string)$text, '[[PROJECT NUMBER PAID INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.paidCount
                $invoiceCount = $invoicesInfos['paidCount'];
                if( !$invoiceCount ) {
                    $invoiceCount = 0;
                }
                $text = str_replace("[[PROJECT NUMBER PAID INVOICES]]", $invoiceCount, $text);
            }

            if( strpos((string)$text, '[[PROJECT AMOUNT PAID INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.paidAmount
                $paidAmount = $invoicesInfos['paidAmount'];
                if( !$paidAmount ) {
                    $paidAmount = 0;
                }
                $paidAmount = $this->dollarSignValue($paidAmount);
                $text = str_replace("[[PROJECT AMOUNT PAID INVOICES]]", $paidAmount, $text);
            }

            if( strpos((string)$text, '[[PROJECT NUMBER OUTSTANDING INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.outstandingCount
                $invoiceCount = $invoicesInfos['outstandingCount'];
                if( !$invoiceCount ) {
                    $invoiceCount = 0;
                }
                $text = str_replace("[[PROJECT NUMBER OUTSTANDING INVOICES]]", $invoiceCount, $text);
            }

            if( strpos((string)$text, '[[PROJECT AMOUNT OUTSTANDING INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.paidAmount
                $outstandingAmount = $invoicesInfos['outstandingAmount'];
                if( !$outstandingAmount ) {
                    $outstandingAmount = 0;
                }
                $outstandingAmount = $this->dollarSignValue($outstandingAmount);
                $text = str_replace("[[PROJECT AMOUNT OUTSTANDING INVOICES]]", $outstandingAmount, $text);
            }

            if( strpos((string)$text, '[[PROJECT VALUE WITHOUT INVOICES]]') !== false ) {
                $invoicesInfos = $project->getInvoicesInfosByProject(true); //invoicesInfos.grandTotalWithoutInvoices
                $grandTotalWithoutInvoices = $invoicesInfos['grandTotalWithoutInvoices'];
                if( !$grandTotalWithoutInvoices ) {
                    $grandTotalWithoutInvoices = 0;
                }
                $grandTotalWithoutInvoices = $this->dollarSignValue($grandTotalWithoutInvoices);
                $text = str_replace("[[PROJECT VALUE WITHOUT INVOICES]]", $grandTotalWithoutInvoices, $text);
            }

            if( strpos((string)$text, '[[PROJECT EXPIRATION DATE]]') !== false ) {
                $expectedExpirationDate = $project->getExpectedExpirationDate();
                if( $expectedExpirationDate ) {
                    //$user = $this->security->getUser();
                    //$userServiceUtil = $this->container->get('user_service_utility');
                    //$expectedExpirationDate = $userServiceUtil->convertFromUtcToUserTimezone($expectedExpirationDate,$user);
                    $expectedExpirationDateStr = $expectedExpirationDate->format('m/d/Y');
                    $text = str_replace("[[PROJECT EXPIRATION DATE]]", $expectedExpirationDateStr, $text);
                }
            }

            //Reactivation
            if( strpos((string)$text, '[[PROJECT REACTIVATION REQUESTER]]') !== false ) {
                $projectReactivationRequester = $project->getTargetStateRequester();
                if( $projectReactivationRequester ) {
                    $text = str_replace("[[PROJECT REACTIVATION REQUESTER]]", $projectReactivationRequester->getUsernameShortest(), $text);
                }
            }

            if( strpos((string)$text, '[[CURRENT DATETIME]]') !== false ) {
                $date = new \DateTime();
                $dateStr = $date->format('m/d/Y \a\t H:i');
                $text = str_replace("[[CURRENT DATETIME]]", $dateStr, $text);
            }

            //[[PROJECT CLOSURE REASON]]
            if( strpos((string)$text, '[[PROJECT CLOSURE REASON]]') !== false ) {
                $closureReason = $project->getClosureReason();
                if( $closureReason ) {
                    $closureReason = str_replace("\n\n","<br>",$closureReason);
                    $text = str_replace("[[PROJECT CLOSURE REASON]]", $closureReason, $text);
                }
            }

            //[[LATEST PROJECT REACTIVATION REASON]] - latest project reactivation reason (replace inside the sender function),
            if( strpos((string)$text, '[[LATEST PROJECT REACTIVATION REASON]]') !== false ) {
                $reactivationReason = $project->getReactivationReason();
                if( $reactivationReason ) {
                    $reactivationReason = str_replace("\n\n","<br>",$reactivationReason);
                    $text = str_replace("[[LATEST PROJECT REACTIVATION REASON]]", $reactivationReason, $text);
                }
            }

            //[[PROJECT TARGET REACTIVATION STATUS]] - project reactivation target status (replace inside the sender function)
            if( strpos((string)$text, '[[PROJECT TARGET REACTIVATION STATUS]]') !== false ) {
                $reactivationTargetState = $project->getTargetState();
                if( $reactivationTargetState ) {
                    $reactivationTargetState = $this->getStateSimpleLabelByName($reactivationTargetState);
                    $text = str_replace("[[PROJECT TARGET REACTIVATION STATUS]]", $reactivationTargetState, $text);
                }
            }

            //[[PREVIOS PROJECT REACTIVATION REASON]] - previous project reactivation reason(s)
            if( strpos((string)$text, '[[PREVIOS PROJECT REACTIVATION REASON]]') !== false ) {
                $reactivationReason = $project->getReactivationReason();
                if( $reactivationReason ) {
                    $reactivationReason = str_replace("\n\n","<br>",$reactivationReason);
                    $text = str_replace("[[PREVIOS PROJECT REACTIVATION REASON]]", $reactivationReason, $text);
                }
            }

            //[[PROJECT REACTIVATION APPROVE URL]]
            if( strpos((string)$text, '[[PROJECT REACTIVATION APPROVE URL]]') !== false ) {
                $reactivationLink = $this->container->get('router')->generate(
                    'translationalresearch_project_reactivation_approve',
                    array(
                        'id' => $project->getId(),
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $reactivationLink = '<a href="'.$reactivationLink.'">'.$reactivationLink.'</a>';

                $text = str_replace("[[PROJECT REACTIVATION APPROVE URL]]", $reactivationLink, $text);
            }

            //[[PROJECT REACTIVATION DENY URL]]
            if( strpos((string)$text, '[[PROJECT REACTIVATION DENY URL]]') !== false ) {
                $reactivationLink = $this->container->get('router')->generate(
                    'translationalresearch_project_reactivation_deny',
                    array(
                        'id' => $project->getId(),
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $reactivationLink = '<a href="'.$reactivationLink.'">'.$reactivationLink.'</a>';

                $text = str_replace("[[PROJECT REACTIVATION DENY URL]]", $reactivationLink, $text);
            }

            //[[PROJECT COMPUTATIONAL CATEGORIES]]
            if( strpos((string)$text, '[[PROJECT COMPUTATIONAL CATEGORIES]]') !== false ) {
                $projectCompCategories = $project->getProjectCompCategories();
                if( $projectCompCategories ) {
                    $projectCompCategories = 'Submitter specified the following computational data analysis categories: '.
                        $projectCompCategories;
                }
                $text = str_replace("[[PROJECT COMPUTATIONAL CATEGORIES]]", $projectCompCategories, $text);
            }
            //[[PROJECT STAT/INFORMATICS SUPPORT]]
            if( strpos((string)$text, '[[PROJECT STAT/INFORMATICS SUPPORT]]') !== false ) {
                $projectInformaticsSupport = $project->getProjectInformaticsSupport();
                if( $projectInformaticsSupport ) {
                    $projectInformaticsSupport = 'Submitter specified the following estimated quantity '.
                        'of needed statistical or informatics support hours: '.
                        $projectInformaticsSupport;
                }
                $text = str_replace("[[PROJECT STAT/INFORMATICS SUPPORT]]", $projectInformaticsSupport, $text);
            }

        }//project

        if( $transresRequest ) {
            //echo "[[REQUEST ID]]=".$transresRequest->getOid()."<br>";
            $text = str_replace("[[REQUEST ID]]", $transresRequest->getOid(), $text);

            $creationDate = $transresRequest->getCreateDate();
            if( $creationDate ) {
                $text = str_replace("[[REQUEST SUBMISSION DATE]]", $creationDate->format("m/d/Y"), $text);
            }

            $submitter = $transresRequest->getSubmitter();
            if( $submitter ) {
                $text = str_replace("[[REQUEST SUBMITTER]]", $submitter->getUsernameShortest(), $text);
            }

            if( strpos((string)$text, '[[REQUEST UPDATE DATE]]') !== false ) {
                $updateDate = $transresRequest->getUpdateDate();
                if ($updateDate) {
                    $text = str_replace("[[REQUEST UPDATE DATE]]", $updateDate->format("m/d/Y"), $text);
                }
            }

            if( strpos((string)$text, '[[REQUEST PROGRESS STATUS]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $state = $transresRequest->getProgressState();
                $state = $transresRequestUtil->getProgressStateLabelByName($state);
                $text = str_replace("[[REQUEST PROGRESS STATUS]]", $state, $text);
            }

            if( strpos((string)$text, '[[REQUEST BILLING STATUS]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $state = $transresRequest->getBillingState();
                $state = $transresRequestUtil->getBillingStateLabelByName($state);
                $text = str_replace("[[REQUEST BILLING STATUS]]", $state, $text);
            }

            if( strpos((string)$text, '[[REQUEST SHOW URL]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $requestShowUrl = $transresRequestUtil->getRequestShowUrl($transresRequest);
                if ($requestShowUrl) {
                    $text = str_replace("[[REQUEST SHOW URL]]", $requestShowUrl, $text);
                }
            }

            if( strpos((string)$text, '[[REQUEST CHANGE PROGRESS STATUS URL]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $requestChangeProgressStatusUrl = $transresRequestUtil->getRequestChangeProgressStateUrl($transresRequest);
                if ($requestChangeProgressStatusUrl) {
                    $text = str_replace("[[REQUEST CHANGE PROGRESS STATUS URL]]", $requestChangeProgressStatusUrl, $text);
                }
            }

            if( strpos((string)$text, '[[REQUEST NEW INVOICE URL]]') !== false ) {
                $transresRequestUtil = $this->container->get('transres_request_util');
                $requestNewInvoiceUrl = $transresRequestUtil->getRequestNewInvoiceUrl($transresRequest);
                if ($requestNewInvoiceUrl) {
                    $text = str_replace("[[REQUEST NEW INVOICE URL]]", $requestNewInvoiceUrl, $text);
                }
            }

            if( strpos((string)$text, '[[REQUEST VALUE]]') !== false ) {
                $invoicesInfos = $transresRequest->getInvoicesInfosByRequest(true);
                $grandTotal = $invoicesInfos['grandTotal']; //grand total including subsidy
                if( !$grandTotal ) {
                    $grandTotal = 0;
                }
                $grandTotal = $this->dollarSignValue($grandTotal);
                $text = str_replace("[[REQUEST VALUE]]", $grandTotal, $text);
            }

        }//$transresRequest
        //else {
            //echo "NO transresRequest!!! <br>"; //testing
        //}

        if( $invoice ) {
            $text = str_replace("[[INVOICE ID]]", $invoice->getOid(), $text);

            //[[INVOICE DUE DATE AND DAYS AGO]]
            $dueDateStr = $invoice->getDueAndDaysStr();
            $text = str_replace("[[INVOICE DUE DATE AND DAYS AGO]]", $dueDateStr, $text);

            //[[INVOICE AMOUNT DUE]]
            $invoiceDue = $invoice->getDue();
            if( !$invoiceDue ) {
                $invoiceDue = "N/A";
            }
            $text = str_replace("[[INVOICE AMOUNT DUE]]", $invoiceDue, $text);

            if( strpos((string)$text, '[[INVOICE SHOW URL]]') !== false ) {
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

        //only project list fields
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

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
            $project = $this->em->getRepository(Project::class)->find($projectId);
            if( !$project ) {
                continue;
            }

            if( $transresPermissionUtil->hasProjectPermission("view",$project) === false ) {
                continue;
            }

            $projectRequests = 0;
            $projectTotalInvoices = 0;
            $projectTotalTotal = 0;
            $projectTotalPaid = 0;
            $projectTotalDue = 0;

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
                $data[8] = $request['oid'];

                //Funding Number
                $data[9] = $request['fundedAccountNumber'];

                //Completion Status
                $data[10] = $transresRequestUtil->getProgressStateLabelByName($request['progressState']);

                //Invoice(s) Issued (Latest)
                $latestInvoice = $transresRequestUtil->getLatestInvoice(null,$request['id']);
                $latestInvoicesCount = 0;
                if( $latestInvoice ) {
                    $latestInvoicesCount = 1;
                    $totalInvoices++;
                    $projectTotalInvoices++;
                }
                $data[11] = $latestInvoicesCount;

                if( $latestInvoice ) {
                    //# Total($)
                    $total = $latestInvoice->getTotal();
                    $totalTotal = $totalTotal + $total;
                    $projectTotalTotal = $projectTotalTotal + $total;
                    $total = "$".$total;
                    $data[12] = $total;

                    //# Paid($)
                    $paid = $latestInvoice->getPaid();
                    $paidTotal = $paidTotal + $paid;
                    $projectTotalPaid = $projectTotalPaid + $paid;
                    if(!$paid) {
                        $paid = 0;
                    }
                    $paid = "$".$paid;
                    $data[13] = $paid;

                    //# Due($)
                    $due = $latestInvoice->getDue();
                    $dueTotal = $dueTotal + $due;
                    $projectTotalDue = $projectTotalDue + $due;

                    $due = "$".$due;
                    $data[14] = $due;

                    //Comment
                    $comment = $latestInvoice->getComment();
                    $data[15] = $comment;
                } else {
                    $data[12] = null;
                    $data[13] = null;
                    $data[14] = null;
                    $data[15] = null;
                }

                $projectRequests = $projectRequests + 1;

                $spoutRow = WriterEntityFactory::createRowFromArray($data, $requestStyle);
                $writer->addRow($spoutRow);

                $rowCount = $rowCount + 1;
            }//foreach request

            $totalRequests = $totalRequests + $projectRequests;

            //$data = array();
            $data = $this->fillOutProjectCellsSpout($writer,$project); //0-7

            //Request Total
            $data[8] = "Project Totals";

            //Empty 9-J, 10-K
            $data[9] = null;
            $data[10] = null;

            //This Project Total Invoices
            $data[11] = $projectTotalInvoices;

            //This Project Total Total
            $projectTotalTotal = "$".$projectTotalTotal;
            $data[12] = $projectTotalTotal;

            //This Project Total Paid
            $projectTotalPaid = "$".$projectTotalPaid;
            $data[13] = $projectTotalPaid;

            //This Project Total Due
            $projectTotalDue = "$".$projectTotalDue;
            $data[14] = $projectTotalDue;

            $data[15] = null; //add footer to the project total

            //set color light green to the last Total row
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);

            $writer->addRow($spoutRow);

            $this->em->clear();

            $rowCount = $rowCount + 1;
        }//projects

        $writer->close();
    }
    //use https://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/
    public function createProjectListExcelSheets($projectIdsArr,$limit=null)
    {

        //$transresRequestUtil = $this->container->get('transres_request_util');
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');

        $author = $this->security->getUser();
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
            ->setDescription('Projects list in spreadsheet format')
            ->setSubject('PHP spreadsheet manipulation')
            ->setKeywords('spreadsheet php office')
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

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
            $project = $this->em->getRepository(Project::class)->find($projectId);
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
        //$autosize = true;
        $autosize = false;
        if( $autosize ) {
            $cellIterator = $ews->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
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

        $author = $this->security->getUser();
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
            ->setDescription('Projects list in spreadsheet format')
            ->setSubject('PHP spreadsheet manipulation')
            ->setKeywords('spreadsheet php office')
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

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
            $project = $this->em->getRepository(Project::class)->find($projectId);
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
        //$autosize = true;
        $autosize = false;
        if( $autosize ) {
            foreach ($ea->getWorksheetIterator() as $worksheet) {

                $ea->setActiveSheetIndex($ea->getIndex($worksheet));

                $sheet = $ea->getActiveSheet();
                $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
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
            $user = $this->security->getUser();
        }

        $partialRoleStr = "_".$specialtyObject->getUppercaseName();

        //if admin or deputy admin
        $adminRole = "ROLE_TRANSRES_ADMIN"."_".$partialRoleStr;
        if( $this->security->isGranted($adminRole) ) {
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
//            if( $this->security->isGranted($role) ) {
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

    //Create spreadsheet by Spout
    //http://opensource.box.com/spout/getting-started/
    //https://hotexamples.com/examples/box.spout.writer/WriterFactory/-/php-writerfactory-class-examples.html
    public function createAntibodyExcelSpout($antibodyIdsArr,$fileName,$limit=null,$onlyPublic=false) {
        //echo "antibodys=".count($antibodyIdsArr)."<br>";
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

        //only project list fields
        $spoutRow = WriterEntityFactory::createRowFromArray(
            [
                'Antibody ID',                  //0 - A
                'Category Tags',                //1 - B
                'Show on public list',          //2 - C
                'Name',                         //3 - D
                'Company',                      //4 - E
                'Clone',                        //5 - F
                'Host',                         //6 - G
                'Reactivity',                   //7 - H
                'Storage',                      //8 - I
                'Associated Antibodies',        //9 - J
                'Datasheet',                    //10 - K
            ],
            $headerStyle
        );
        $writer->addRow($spoutRow);

        $rowCount = 2;
        $count = 0;

        foreach( $antibodyIdsArr as $antibodyId ) {

            //echo "antibodyId=$antibodyId <br>";

            if( $limit && ($count++ > $limit) ) {
                break;
            }

            $antibody = $this->em->getRepository(AntibodyList::class)->find($antibodyId);
            if( !$antibody ) {
                continue;
            }

            if( $onlyPublic === true ){
                if( $antibody->getOpenToPublic() !== true ) {
                    continue;
                }
            }

            $data[0] = $antibody->getId();

            $tags = $antibody->getCategoryTags();
            $tagsStr = "";
            foreach($tags as $tag) {
                if( $tagsStr ) {
                    $tagsStr = $tagsStr . ", ";
                }
                $tagsStr = $tagsStr . $tag->getName();
            }

            $openToPublic = "No";
            if( $antibody->getOpenToPublic() === true ) {
                $openToPublic = "Yes";
            }

            $associatesStr = "";
            foreach($antibody->getAssociates() as $associate) {
                if( $associatesStr ) {
                    $associatesStr = $associatesStr . ", ";
                }
                $associatesStr = $associatesStr . $associate->listName();
            }

            $data[1] = $tagsStr;
            $data[2] = $openToPublic;
            $data[3] = $antibody->getName();
            $data[4] = $antibody->getCompany();
            $data[5] = $antibody->getClone();
            $data[6] = $antibody->getHost();
            $data[7] = $antibody->getReactivity();
            $data[8] = $antibody->getStorage();
            $data[9] = $associatesStr;
            $data[10] = $antibody->getDatasheet();

            //set color light green to the last Total row
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);

            $writer->addRow($spoutRow);

            $this->em->clear();

            $rowCount = $rowCount + 1;
        }//antibody

        //exit('111');
        $writer->close();
    }

    //TODO: export list of antibodies to PDF
    public function createAntibodyPdf($antibodyIdsArr,$fileName,$limit=null,$onlyPublic=false) {

        echo "PDF export under construction";
        return null;

        $logger = $this->container->get('logger');
        $router = $this->container->get('router');

        //$url = 'translationalresearch_antibodies_public_react';
        $url = "translationalresearch_antibodies_public";
        
        $pageUrl = $router->generate(
            $url,
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        ); //this does not work from console: 'order' is missing

        $logger->notice("pageUrl= $pageUrl");

        $PHPSESSID = NULL;
        $userUtil = $this->container->get('user_utility');
        $session = $userUtil->getSession();
        if( $session ) {
            $logger->notice("generateApplicationPdf: has session");
            //$session = $request->getSession();
            if( $session && $session->getId() ) {
                //$logger->notice("1before session save: ".dump($session));
                $session->save();
                //$logger->notice("after save session");
                session_write_close();
                $logger->notice("after session_write_close");
                $PHPSESSID = $session->getId();
            }
        } else {
            $logger->notice("generateApplicationPdf: no session");
            //take care of authentication
            //$userUtil = $this->container->get('user_utility');
            //$session = $userUtil->getSession(); //$this->container->get('session');
            if( $session && $session->getId() ) {
                //$logger->notice("2before session save: ".dump($session));
                $session->save();
                session_write_close();
                $PHPSESSID = $session->getId();
            }
        }

        $uploadAntibodiesPath = "antibodypdfs";
        $reportPath = $this->container->get('kernel')->getProjectDir() .
            DIRECTORY_SEPARATOR . 'public' .
            "Uploaded" . DIRECTORY_SEPARATOR . "transres" .
            DIRECTORY_SEPARATOR . $uploadAntibodiesPath;

        if( !file_exists($reportPath) ) {
            mkdir($reportPath, 0700, true);
            chmod($reportPath, 0700);
        }

        $timestamp = time();
        $outdir = $reportPath.'/temp_'.$timestamp.'/';
        $antibodiesPdfPath = $outdir . "antibodies" . ".pdf";

        $logger->notice("antibodiesPdfPath= $antibodiesPdfPath");
        //$logger->notice("pageUrl= $pageUrl");

        $this->container->get('knp_snappy.pdf')->generate(
            $pageUrl,
            $antibodiesPdfPath,
            array(
                'cookie' => array(
                    'PHPSESSID' => $PHPSESSID
                )
            )
        //array('cookie' => array($session->getName() => $session->getId()))
        );

        return $antibodiesPdfPath;
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
                $roles = "list.roles NOT LIKE '%ROLE_TESTER%' OR
                (
                    list.roles LIKE '%ROLE_TESTER%' AND
                    (
                    list.roles LIKE '%ROLE_PLATFORM_ADMIN%' OR
                    list.roles LIKE '%ROLE_PLATFORM_DEPUTY_ADMIN%' OR
                    list.roles LIKE '%ROLE_SUPER_DEPUTY_ADMIN%'
                    )
                )";
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")

                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")

                    //->where("((employmentType.name != 'Pathology Fellowship Applicant' AND employmentType.name != 'Pathology Residency Applicant') OR employmentType.id IS NULL)")
                    //->where("employmentType.name != 'Pathology Residency Applicant' OR employmentType.name != 'Pathology Residency Applicant' OR employmentType.id IS NULL")
                    //->where("employmentType.name NOT LIKE 'Pathology%Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")

                    ->andWhere("employmentStatus.terminationDate IS NULL")
//                    ->andWhere("(list.roles != 'ROLE_TESTER' AND
//                    list.roles != 'ROLE_PLATFORM_DEPUTY_ADMIN' AND
//                    list.roles != 'ROLE_PLATFORM_DEPUTY_ADMIN' AND
//                    list.roles != 'ROLE_SUPER_DEPUTY_ADMIN')")
                    ->andWhere($roles)
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            };
        }

        return function(EntityRepository $er) {
            return $er->createQueryBuilder('list')
                ->leftJoin("list.employmentStatus", "employmentStatus")
                ->leftJoin("employmentStatus.employmentType", "employmentType")
                ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                //->where("employmentType.name NOT LIKE 'Pathology % Applicant' OR employmentType.id IS NULL")
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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialties = $this->em->getRepository(SpecialtyList::class)->findBy(
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
            $user = $this->security->getUser();
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
                false == $this->security->isGranted($role) ||
                false == $user->hasRole($role)
            ) {
                $user->addRole($role);
                $flushUser = true;
                $roleAddedArr[] = $role;
            }
        }

//        if( $specialtyObject ) {
//            $specialtyRole = $this->getSpecialtyRole($specialtyObject);
//            if( false == $this->security->isGranted($specialtyRole) ) {
//                $user->addRole($specialtyRole);
//                $flushUser = true;
//                $roleAddedArr[] = $specialtyRole;
//            }
//            $specialtyStr = $specialtyObject."";
//        } else {
//            $specialtyStr = null;
//        }

        //Why to add tester role?
//        $environment = $userSecUtil->getSiteSettingParameter('environment');
//        if( $environment != 'live' ) {
//            if(
//                false == $this->security->isGranted('ROLE_TESTER') ||
//                false == $user->hasRole('ROLE_TESTER')
//            ) {
//                $user->addRole('ROLE_TESTER');
//                $flushUser = true;
//                $roleAddedArr[] = 'ROLE_TESTER';
//            }
//        }

        if( $flushUser ) {
            //exit('flush user');
            $this->em->flush($user);

//            $this->session->getFlashBag()->add(
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

        //2b submitInvestigators
        $submitInvestigators = $project->getSubmitInvestigators();
        foreach( $submitInvestigators as $submitInvestigator ) {
            if( $submitInvestigator ) {
                $resArr[] = $submitInvestigator;
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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $repository = $this->em->getRepository(TransResRequest::class);
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
    public function getNotificationMsgByStates( $fromStateStr, $toStateStr, $project, $reason=NULL ) {

        $msg = null;

        $id = $project->getOid();

        $title = $project->getTitle();

        $fromLabel = $this->getStateSimpleLabelByName($fromStateStr);
        $toLabel = $this->getStateSimpleLabelByName($toStateStr);

        //Case1: irb_review -> admin_review
        //Project request [xxx] "[project title]" has successfully passed the "IRB review" stage and is now awaiting "Admin review".
        if( strpos((string)$fromStateStr, "_review") !== false && strpos((string)$toStateStr, "_review") !== false ) {
            $msg = "Project request $id '".$title."' has successfully passed the '".$fromLabel."' stage and is now awaiting '".$toLabel."'.";
        }

        //Case2: final_review -> final_approved
        //Project request [xxx] "[project title]" has successfully passed all stages of review and received final approval.
        if( strpos((string)$fromStateStr, "_review") !== false && strpos((string)$toStateStr, "_approved") !== false ) {
            $msg = "Project request $id '".$title."' has successfully passed all stages of review and has received final approval.";
        }

        //Case3: irb_review -> irb_rejected
        if( strpos((string)$fromStateStr, "_review") !== false && strpos((string)$toStateStr, "_rejected") !== false ) {
            $msg = "Project request $id '".$title."' has been rejected as a result of '".$fromLabel."'.";
        }

        //Case4: irb_review -> irb_missinginfo
        if( strpos((string)$fromStateStr, "_review") !== false && strpos((string)$toStateStr, "_missinginfo") !== false ) {
            $msg = "Additional information has been requested for the project with ID $id '".$title."' for the '".$fromLabel."' stage.";
        }

        //Case5: irb_missinginfo -> irb_review
        if( strpos((string)$fromStateStr, "_missinginfo") !== false && strpos((string)$toStateStr, "_review") !== false ) {
            $msg = "Project request $id '".$title."' has been re-submitted for '".$toLabel."' stage.";
        }

        if( !$msg ) {
            $user = $this->security->getUser();
            $msg = "The status of the project request $id '".$title."' has been changed from '".$fromLabel."' to '".$toLabel."'";
            $msg = $msg . " by " . $user . ".";
        }

        if( $reason ) {
            $msg = $msg . " Reason: " . $reason . ".";
        }

        return $msg;
    }

    public function getTotalProjectCount() {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(Project::class);
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

    public function getTotalAntibodyCount() {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(AntibodyList::class);
        $dql = $repository->createQueryBuilder("antibody");
        $dql->select('COUNT(antibody)');

        $query = $dql->getQuery();

        //$count = -1;
        $count = $query->getSingleScalarResult();
        //$resArr = $query->getOneOrNullResult();
        //print_r($resArr);
        //echo "count=".$count."<br>";

        return $count;
    }
    public function getAntibodyIdsArrByDqlParameters($dql,$dqlParameters) {
        $dql->select('ent.id');

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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $repository = $this->em->getRepository(TransResRequest::class);
        $dql = $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest');

        $query = $dql->getQuery();
        
        $requests = $query->getResult();

        return $requests;
    }
    public function getTotalRequestCount() {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResRequest'] by [TransResRequest::class]
        $repository = $this->em->getRepository(TransResRequest::class);
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

    //filter products by work queue type ($workQueues as array: MISI or CTP)
    public function getTotalProductsCount( $workQueues=array() ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Product'] by [Product::class]
        $repository = $this->em->getRepository(Product::class);
        $dql = $repository->createQueryBuilder("product");
        $dql->select('COUNT(product)');

        $dqlParameters = array();

        if( $workQueues && count($workQueues) > 0 ) {
            //products
            $dql->leftJoin('product.category', 'category');
            $dql->leftJoin('category.workQueues', 'workQueues');

            //prices
            $dql->leftJoin('category.prices', 'prices');
            //$dql->leftJoin('prices.workQueues', 'priceWorkQueues');

            //$dql->andWhere("workQueues.id IN (:workQueues)");
            //$dql->andWhere("priceWorkQueues.id IN (:workQueues)");

            //issue (rare, special cases) (same as in WorkQueueController, filter by $workQueues):
            // it shows requests with both queues in default price and in specific price
            //for example, if product has default MISI and specific CTP, this product will be shown for both MISI and CTP work queue filter

            //TODO: must filter by project price list
            //$dql->andWhere("workQueues.id IN (:workQueues) OR priceWorkQueues.id IN (:workQueues)");

            $dql->andWhere("workQueues.id IN (:workQueues)"); //use only workQueues in the default price list

            $dqlParameters["workQueues"] = $workQueues;
        }

        $query = $dql->getQuery();

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        //$count = -1;
        $count = $query->getSingleScalarResult();
        //$resArr = $query->getOneOrNullResult();
        //print_r($resArr);
        //echo "count=".$count."<br>";

        return $count;
    }
    public function getMatchingProductArrByDqlParameters($dql,$dqlParameters) {
        $dql->select('product.id');

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

    //TODO: optimize to get user only: add displayname to the User and auto populate it from infos
    public function getAppropriatedUsers() {
        //$users = $this->em->getRepository('AppUserdirectoryBundle:User')->findAll();
    
        //$users = $this->em->getRepository('AppUserdirectoryBundle:User')->findBy(array('createdby'=>array('googleapi')));
        //return $users;
    
        //Multiple (384 - all users in DB) FROM scan_perSiteSettings t0 WHERE t0.fosuser = ?
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $repository = $this->em->getRepository(User::class);
        $dql = $repository->createQueryBuilder("list");
        $dql->select('list');
    
    //        if(0) {
    //            $dql->leftJoin("list.employmentStatus", "employmentStatus");
    //            $dql->leftJoin("employmentStatus.employmentType", "employmentType");
    //        }
    
        if(1) { //testing
            $dql->leftJoin("list.infos", "infos");
            $dql->where("list.createdby != 'googleapi'"); //googleapi is used only by fellowship application population
            $dql->orderBy("infos.lastName", "ASC");
        }
    
        //$dql->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL");
        //added additional filters
        //$dql->andWhere("list.keytype IS NOT NULL AND list.primaryPublicUserId != 'system'");
        //$dql->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)");
        //$dql->andWhere("(list.testingAccount = false OR list.testingAccount IS NULL)");
        //Currently working employee
        //$dql->andWhere("(employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate IS NULL)");
        //$curdate = date("Y-m-d", time());
        //$dql->andWhere("(employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."')");
        //$dql->orderBy("infos.displayName","ASC");
        //$dql->setMaxResults(10); //testing
    
        $query = $dql->getQuery();
    
        //https://phpdox.net/demo/Symfony2/classes/Doctrine_ORM_Query.xhtml
        //$query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true); //this will reduce queries, but make getUsernameOptimal (or UserInfo) empty
        //doctrine cache queries
        //$query->useQueryCache(true);
        //$query->useResultCache(true);
    
        $users = $query->getResult();

        //dump($users);
        //exit('users='.count($users)); //2105

        //$query->setHint(Query::HINT_REFRESH_ENTITY, true);
        //dump($users);
        //exit('111');
    
    //        foreach($users as $user) {
    //            echo $user."";
    //        }
    //        exit('111');
    
        return $users;
    }
    public function getAppropriatedUsers_TEST() {
        //$users = $this->em->getRepository('AppUserdirectoryBundle:User')->findAll();

        //$users = $this->em->getRepository('AppUserdirectoryBundle:User')->findBy(array('createdby'=>array('googleapi')));
        //return $users;

        //Multiple (384 - all users in DB) FROM scan_perSiteSettings t0 WHERE t0.fosuser = ?
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $repository = $this->em->getRepository(User::class);
        $dql = $repository->createQueryBuilder("list");
        //$dql->select('list.id as id, list.username as username');
        $dql->select('list');

        if(1) { //testing
            $dql->leftJoin("list.infos", "infos");
            $dql->where("list.createdby != 'googleapi'"); //googleapi is used only by fellowship application population
            $dql->orderBy("infos.lastName", "ASC");
        }
        
        $query = $dql->getQuery();

        //https://phpdox.net/demo/Symfony2/classes/Doctrine_ORM_Query.xhtml
        //$query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true); //this will reduce queries, but make getUsernameOptimal (or UserInfo) empty
        //doctrine cache queries
        //$query->useQueryCache(true);
        //$query->useResultCache(true);

        $users = $query->getResult();

        //dump($users);
        //exit('users='.count($users));

        return $users;
    }
    
    public function getUserOptimalName( $user ) {
        //return $userId;
        //$this->em->clear();
        //$user = $this->em->getRepository('AppUserdirectoryBundle:User')->find($userId);
        //return $user."";
        //$optimalName = $user->getDisplayName();
        $optimalName = $user->getUsernameOptimal();
        return $optimalName;
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
        //$transresPricesList['Default'] = 'default';
        $transresPricesList['External'] = 'external';

        foreach($prices as $price) {
            $transresPricesList[$price->getName()] = $price->getId();
        }

        return $transresPricesList;
    }

    //Use to return array name-id for select box
    public function getDbPriceLists() {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:PriceTypeList'] by [PriceTypeList::class]
        $repository = $this->em->getRepository(PriceTypeList::class);
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

//        if(0) {
//            //Testing: Add 'Default' price list
//            $priceListsArr = array('Default' => NULL);
//            foreach ($priceLists as $priceList) {
//                $priceListsArr[$priceList->getName()] = $priceList->getId();
//            }
//            return $priceListsArr;
//        }

        return $priceLists;
    }

    //show current review's recommendations for committee review status for primary reviewer
    public function showProjectReviewInfo($project) {
        $user = $this->security->getUser();
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

    //NOT USED, USE universal getTransresSiteProjectParameter
    //Use when value is a Doctrine Array
    public function getTransresSiteProjectArrayParameter( $fieldName, $project=null, $projectSpecialty=null, $useDefault=false, $testing=false ) {
        $value = $this->getTransresSiteProjectParameterSingle($fieldName,$project,$projectSpecialty,$useDefault,$testing);

        //value might be array
        if( is_array($value) || $value instanceof PersistentCollection || $value instanceof ArrayCollection ) {
            echo $fieldName.": value is array, count=".count($value)."<br>";
        } else {
            echo $fieldName.": value is not array <br>";
        }

        //echo "value1=[$value] <br>";

        if( count($value) == 0 ) {
            $value = $this->getTransresSiteProjectParameterSingle($fieldName,NULL,NULL,$useDefault,$testing);
        } else {
            //echo "NOTNULL value2=[$value] <br>";
        }

        return $value;
    }
    public function getTransresSiteProjectParameter( $fieldName, $project=null, $projectSpecialty=null, $useDefault=false, $testing=false ) {
        $value = $this->getTransresSiteProjectParameterSingle($fieldName,$project,$projectSpecialty,$useDefault,$testing);
        //echo "value1=[$value] <br>";

        //value might be array
        $valueEmpty = false;
        if( is_array($value) || $value instanceof PersistentCollection || $value instanceof ArrayCollection ) {
            //echo $fieldName.": value is array, count=".count($value)."<br>";
            if( count($value) == 0 ) {
                $valueEmpty = true;
            }
        } else {
            //echo $fieldName.": value is not array <br>";
            if( $value === NULL ) {
                $valueEmpty = true;
            }
        }

        if( $valueEmpty ) {
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

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:TransResSiteParameters'] by [TransResSiteParameters::class]
        $repository = $em->getRepository(TransResSiteParameters::class);
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

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $entities = $query->getResult();
        //echo "projectSpecialty count=".count($entities)."<br>";

        if( count($entities) > 0 ) {
            return $entities[0];
        }

        //Create New
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialty = $em->getRepository(SpecialtyList::class)->findOneByAbbreviation($specialtyStr);

        $user = $this->security->getUser();
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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
                    $specialties = $this->em->getRepository(SpecialtyList::class)->findBy(
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
        //$parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
        $parts = preg_split('/([\s\n\r]+)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $postfix = null;
        $length = 0;
        $last_part = 0;
        for (; $last_part < $parts_count; ++$last_part) {
            $length += strlen((string)$parts[$last_part]);
            if ($length > $your_desired_width) {
                $postfix = "...";
                break;
            }
        }

        $res = implode(array_slice($parts, 0, $last_part));
        $res = trim((string)$res) . $postfix;
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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
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
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
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
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
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
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters($dqlParameters);

        $loggers = $query->getResult();

        //echo "loggers=".count($loggers)."<br>";
        //exit();

        return count($loggers);
    }

    public function getLoginCount( $startDate, $endDate, $site='translationalresearch', $unique=false ) {
        $dqlParameters = array();

        //get the date from event log
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
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
        } else {
            //$dql->andWhere("logger.siteName IS NOT NULL");
        }

        //$dql->andWhere("logger.creationdate > :startDate AND logger.creationdate < :endDate");
        $dql->andWhere('logger.creationdate >= :startDate');
        //$startDate->modify('-1 day');
        $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');

        $dql->andWhere('logger.creationdate <= :endDate');
        $endDate->modify('+1 day');
        $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');

        //$dql->orderBy("logger.id","DESC");
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $repository = $this->em->getRepository(Logger::class);
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
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

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
        if( !$request ) {
            return NULL;
        }
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
            //echo "1value=".$value."<br>";
            $value = str_replace(",","",$value);
            $value = $this->toDecimal($value);
            //echo "2value=".$value."<br>";
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
    
    public function orderableProjectSpecialties( $fee, $asObject=true ) {

        //1) get all specialties
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialties = $this->em->getRepository(SpecialtyList::class)->findBy(
            array(
                'type' => array("default", "user-added")
            ),
            array('orderinlist' => 'ASC')
        );

        //2) get diff fee.projectSpecialties
        $orderableSpcialties = array();
        $hideSpecialties = $fee->getProjectSpecialties();
        foreach($specialties as $specialty) {
            if( $hideSpecialties->contains($specialty) ) {
            } else {
                if( $asObject ) {
                    $orderableSpcialties[] = $specialty;
                } else {
                    $orderableSpcialties[] = $specialty->getId();
                }
            }

        }

        return $orderableSpcialties;
    }

    public function orderableProjectReverseSpecialties( $hideSpecialtiesArr, $asObject=true ) {
        //1) get all specialties
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:SpecialtyList'] by [SpecialtyList::class]
        $specialties = $this->em->getRepository(SpecialtyList::class)->findBy(
            array(
                'type' => array("default", "user-added")
            ),
            array('orderinlist' => 'ASC')
        );

        //2) get diff specialties
        $orderableSpcialties = array();
        //$hideSpecialties = $fee->getProjectSpecialties();
        foreach($specialties as $specialty) {
            if( in_array($specialty->getId(),$hideSpecialtiesArr) ) {
            } else {
                if( $asObject ) {
                    $orderableSpcialties[] = $specialty;
                } else {
                    $orderableSpcialties[] = $specialty->getId();
                }
            }

        }

        return $orderableSpcialties;
    }


    //Run when specialty is added via Site Setting's '2) Populate All Lists with Default Values (Part A)'
    //Run when add specialty via Platform List Manager's (directory/admin/list-manager/?filter%5Bsearch%5D=specialty):
    //'Translational Research Project Specialty List, class: [SpecialtyList]' => 'Create a new entry'
    public function addTransresRolesBySpecialty($specialty) {
        if( !$specialty ) {
            return NULL;
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $transresUtil = $this->container->get('transres_util');

        $rolename = $specialty->getRolename(); //MISI
        if( !$rolename ) {
            throw new \Exception('Rolename in the Project Specialty is empty');
            //exit('Rolename in the Project Specialty is empty');
        }

        //9 roles (i.e. 'ROLE_TRANSRES_TECHNICIAN_MISI')
        $transresRoleBases = array(
            'ROLE_TRANSRES_TECHNICIAN'              => array(
                'Translational Research [[ROLENAME]] Technician',
                "View and Edit a Translational Research [[ROLENAME]] Request",
                50,
            ),

            'ROLE_TRANSRES_REQUESTER'               => array(
                'Translational Research [[ROLENAME]] Project Requester',
                "Submit, View and Edit a Translational Research [[ROLENAME]] Project",
                30,
            ),

            'ROLE_TRANSRES_BILLING_ADMIN'           => array(
                'Translational Research [[ROLENAME]] Billing Administrator',
                "Create, View, Edit and Send an Invoice for Translational Research [[ROLENAME]] Project",
                50,
            ),

            'ROLE_TRANSRES_EXECUTIVE'               => array(
                'Translational Research [[ROLENAME]] Executive Committee',
                'Full View Access for [[ROLENAME]] Translational Research site',
                70
            ),

            'ROLE_TRANSRES_ADMIN'                   => array(
                'Translational Research [[ROLENAME]] Admin',
                'Full Access for Translational Research [[ROLENAME]] site',
                90
            ),

            'ROLE_TRANSRES_IRB_REVIEWER'            => array(
                "Translational Research [[ROLENAME]] IRB Reviewer",
                "[[ROLENAME]] IRB Review",
                50,
            ),

            'ROLE_TRANSRES_COMMITTEE_REVIEWER'      => array(
                "Translational Research [[ROLENAME]] Scientific Committee Reviewer",
                "[[ROLENAME]] Scientific Committee Review",
                50,
            ),

            'ROLE_TRANSRES_PRIMARY_COMMITTEE_REVIEWER' => array(
                "Translational Research [[ROLENAME]] Primary Scientific Committee Reviewer",
                "[[ROLENAME]] Scientific Committee Review",
                50
            ),

            'ROLE_TRANSRES_PRIMARY_REVIEWER' => array(
                "Translational Research [[ROLENAME]] Financial and Programmatic Reviewer",
                "Review for all states for [[ROLENAME]]",
                80
            ),
        );

        $sitenameAbbreviation = "translationalresearch"; //"translational-research";

        foreach($transresRoleBases as $transresRoleBase=>$roleInfoArr) {

            $role = $transresRoleBase."_".$rolename; //ROLE_TRANSRES_TECHNICIAN_MISI

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
            $entity = $this->em->getRepository(Roles::class)->findOneByName($role);

            if( $entity ) {
                continue;
            }

//            $entity = new Roles();
//
//            //$entity, $count, $user, $name=null
//            $count = null;
//            $entity = $this->setDefaultList( $entity, $count, $user, $role );
//            $entity->setType('default');
//
//            $alias = $roleInfoArr[0];
//            $description = $roleInfoArr[1];
//            $level = $roleInfoArr[2];
//
//            if( $alias ) {
//                $alias = str_replace('[[ROLENAME]]',$rolename,$alias);
//            }
//            if( $description ) {
//                $description = str_replace('[[ROLENAME]]',$rolename,$description);
//            }
//
//            $entity->setName( $role );
//            $entity->setAlias( trim((string)$alias) );
//            $entity->setDescription( trim((string)$description) );
//            $entity->setLevel($level);
//
//            //set sitename
//            if( $sitenameAbbreviation ) {
//                $userSecUtil->addSingleSiteToEntity($entity,$sitenameAbbreviation);
//            }

            $alias = $roleInfoArr[0];
            $description = $roleInfoArr[1];
            $level = $roleInfoArr[2];

            if( $alias ) {
                $alias = str_replace('[[ROLENAME]]',$rolename,$alias);
            }
            if( $description ) {
                $description = str_replace('[[ROLENAME]]',$rolename,$description);
            }

            $alias = trim((string)$alias);
            $description = trim((string)$description);

            //$roleName, $sitenameAbbreviation=NULL, $alias=NULL, $description=NULL, $level=NULL
            $entity = $userSecUtil->createNewRole(
                $role,
                $sitenameAbbreviation,
                $alias,         //"Translational Research AP/CP Technician"
                $description,   //"View and Edit a Translational Research AP/CP Request"
                $level
            );

            if( $entity ) {
                $this->em->persist($entity);
                $this->em->flush();
                //break; //testing

                $msg = "Added role=[$role]: alias=[$alias], description=[$description] <br>";

                //TODO: fix it!
                //Flash
                if( $this->session ) {
                    $this->session->getFlashBag()->add(
                        'notice',
                        $msg
                    );
                }

                //eventlog
                $eventType = "New Role Created";
                $transresUtil->setEventLog($entity,$eventType,$msg);
            }

        }//foreach

        //exit("EOF addTransresRoles");
    }
    public function addTransresRolesBySpecialtyWorkQueue() {
        $rolePartialName = "ROLE_TRANSRES_TECHNICIAN";
        $this->addTransresSingleRoleBySpecialtyWorkQueue($rolePartialName);

        $rolePartialName = "ROLE_TRANSRES_ADMIN";
        $this->addTransresSingleRoleBySpecialtyWorkQueue($rolePartialName);
    }
    public function addTransresSingleRoleBySpecialtyWorkQueue($rolePartialName) {

        if( !$rolePartialName ) {
            return NULL;
        }

        $transresUtil = $this->container->get('transres_util');
        $userSecUtil = $this->container->get('user_security_utility');

        $sitenameAbbreviation = "translationalresearch";

        //1) find all roles by 'ROLE_TRANSRES_TECHNICIAN'
        //$rolePartialName = "ROLE_TRANSRES_TECHNICIAN";
        //get only enabled roles
        $statusArr = array("default","user-added");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $trpRoles = $this->em->getRepository(User::class)->findRolesBySiteAndPartialRoleName(
            $sitenameAbbreviation,  //$sitename
            $rolePartialName,       //$rolePartialName
            null,                   //$institutionId
            $statusArr              //$statusArr
        );

        $workQueues = $this->getWorkQueues(); //get only enabled work queues
        //echo "workQueues count=".count($workQueues)."<br><br>";

        //$testing = true;
        $testing = false;

        foreach($trpRoles as $trpRole) {
            //echo "<br><br>$trpRole <br>";

            if( strpos((string)$trpRole, 'QUEUE') !== false ) {
                continue; //skip: already QUEUE role
            }

            $alias = $trpRole->getAlias();
            $description = $trpRole->getDescription();
            $level = $trpRole->getLevel();

            //echo "Add QUEUE=$trpRole <br>";

            foreach($workQueues as $workQueue) {
                $workQueueName = $workQueue->getName();
                $workQueueAbbreviation = $workQueue->getAbbreviation();
                $workQueueRoleName = $trpRole . "_" . $workQueueAbbreviation;

                //echo "workQueueRoleName=[$workQueueRoleName] <br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
                $workQueueRoleEntity = $this->em->getRepository(Roles::class)->findOneByName(trim((string)$workQueueRoleName));
                if( $workQueueRoleEntity ) {
                    continue; //skip: already exists
                }

                //$prefix = ", Work Queue ";
                //$postfix = "";

                $prefix = " (Work Queue ";
                $postfix = ")";

                $newAlias = $alias . $prefix . $workQueueName . $postfix;
                $newDescription = $description . $prefix . $workQueueName . $postfix;

                if( $testing ) {
                    echo "add workQueueRoleName=[$workQueueRoleName] <br>";
                    echo "add newAlias=[$newAlias] <br>";
                    echo "add newDescription=[$newDescription] <br>";
                    echo "add level=[$level] <br>";
                }

                //$roleName, $sitenameAbbreviation=NULL, $alias=NULL, $description=NULL, $level=NULL
                $newRole = $userSecUtil->createNewRole(
                    $workQueueRoleName,
                    $sitenameAbbreviation,
                    $newAlias,         //"Translational Research AP/CP Technician"
                    $newDescription,   //"View and Edit a Translational Research AP/CP Request"
                    $level
                );

                if( $newRole ) {

                    if ($testing == false) {
                        $this->em->persist($newRole);
                        $this->em->flush();
                    }

                    $msg = "Added role=[$workQueueRoleName]: alias=[$alias], description=[$description] <br>";

                    //Flash
                    //TODO: fix it!
                    if( $this->session ) {
                        $this->session->getFlashBag()->add(
                            'notice',
                            $msg
                        );
                    }

                    //eventlog
                    $eventType = "New Role Created";
                    $transresUtil->setEventLog($newRole,$eventType,$msg);

                    if ($testing) {
                        break;//testing
                        echo "added <br><br>";
                    }
                }
            }//foreach $workQueues

            if( $testing ) {
                break;//testing
            }

        }//foreach $trpRoles

    }
    public function getWorkQueues() {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:WorkQueueList'] by [WorkQueueList::class]
        $workQueues = $this->em->getRepository(WorkQueueList::class)->findBy(
            array(
                'type' => array("default","user-added")
            ),
            array('orderinlist' => 'ASC')
        );

        return $workQueues;
    }

    public function getWorkQueueObject($name) {
        //echo "specialtyAbbreviation=".$specialtyAbbreviation."<br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:WorkQueueList'] by [WorkQueueList::class]
        $entity = $this->em->getRepository(WorkQueueList::class)->findOneByname($name);

        if( !$entity ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:WorkQueueList'] by [WorkQueueList::class]
            $entity = $this->em->getRepository(WorkQueueList::class)->findOneByAbbreviation($name);
        }

        if( !$entity ) {
            $name = strtolower($name);
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:WorkQueueList'] by [WorkQueueList::class]
            $entity = $this->em->getRepository(WorkQueueList::class)->findOneByAbbreviation($name);
        }

        if( !$entity ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:WorkQueueList'] by [WorkQueueList::class]
            $repository = $this->em->getRepository(WorkQueueList::class);
            $dql =  $repository->createQueryBuilder("workQueue");
            $dql->select('workQueue');

            $dql->where("LOWER(workQueue.name) = :workQueueName");

            $name = strtolower($name);
            $dqlParameters['workQueueName'] = $name;

            $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
            $query->setParameters($dqlParameters);

            $entity = $query->getOneOrNullResult();
        }

        if( !$entity ) {
            throw new \Exception( "Work Queue is not found by name '".$name."'" );
        }

//        if( $specialty->getType() == 'default' || $specialty->getType() == 'user-added' ) {
//            //OK
//        } else {
//            return NULL;
//        }

        return $entity;
    }

    public function syncFeeAndWorkQueue( $testing=false ) {

        //$testing = false;
        //$testing = true;

        //get only fees without Work Queues
        $withWorkQueue = false;

        $logCtp = array();
        $ctpWorkQueue = $this->getWorkQueueObject("CTP Lab");
        if( $ctpWorkQueue ) {
            $trpFees = $this->getFees('TRP-',$withWorkQueue);
            foreach ($trpFees as $trpFee) {
                $res = $this->assignWorkQueueToFee($trpFee, $ctpWorkQueue, $testing);
                if( $res ) {
                    $logCtp[] = $trpFee->getShortInfo();
                }
            }
        }

        $logMisi = array();
        $misiWorkQueue = $this->getWorkQueueObject("MISI Lab");
        if( $misiWorkQueue ) {
            $misiFees = $this->getFees('MISI-',$withWorkQueue);
            foreach ($misiFees as $misiFee) {
                $res = $this->assignWorkQueueToFee($misiFee, $misiWorkQueue, $testing);
                if( $res ) {
                    $logMisi[] = $misiFee->getShortInfo();
                }
            }
        }

        $ctpMsg = count($logCtp)." CTP Lab assigned: ".implode(", ",$logCtp);
        $misiMsg = count($logMisi)." MISI Lab assigned: ".implode(", ",$logMisi);

        if( $testing == false ) {
            if( count($logCtp) > 0 || count($logMisi) > 0 ) {
                $this->em->flush();
            }

            //event log
            if( count($logCtp) > 0 ) {
                $eventType = "List Updated";
                $this->setEventLog(null,$eventType,$ctpMsg);
            }
            if( count($logMisi) > 0 ) {
                $eventType = "List Updated";
                $this->setEventLog(null,$eventType,$misiMsg);
            }
        }

        return "Assigned Lab:<br>" . $ctpMsg . "<br><br>" . $misiMsg;
    }
    //Get fee schedules (product/services) (RequestCategoryTypeList) from DB
    //$productId - full or partial productId
    //$withWorkQueue - NULL (don't use queues), TRUE (only with queues), FALSE (only without queues)
    public function getFees( $productId=NULL, $withWorkQueue=NULL ) {

        $dqlParameters = array();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequestCategoryTypeList'] by [RequestCategoryTypeList::class]
        $repository = $this->em->getRepository(RequestCategoryTypeList::class);
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dqlParameters["typedef"] = 'default';
        $dqlParameters["typeadd"] = 'user-added';

        if( $productId ) {
            $dql->andWhere("list.productId LIKE :productId");
            $dqlParameters["productId"] = "%".$productId."%";
        }

        if( $withWorkQueue !== NULL ) {
            if( $withWorkQueue === true ) {
                $dql->leftJoin("list.workQueues", "workQueues");
                $dql->andWhere("workQueues IS NOT NULL");
            }
            if( $withWorkQueue === false ) {
                $dql->leftJoin("list.workQueues", "workQueues");
                $dql->andWhere("workQueues IS NULL");
            }
        }

        $dql->orderBy("list.orderinlist","ASC");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $categories = $query->getResult();

        return $categories;
    }
    public function assignWorkQueueToFee( $fee, $workQueue, $testing=false ) {
        if( !$fee ) {
            return false;
        }
        if( !$workQueue ) {
            return false;
        }

        $currentWorkQueues = $fee->getWorkQueues();

        //add only if WorkQueue does not exists
        if( $currentWorkQueues && count($currentWorkQueues) == 0 ) {

            //assign Work Queue to default price list
            if( $testing ) {
                echo "added (" . $fee->getShortInfo() . ") $workQueue to default price list <br>";
            }
            $fee->addWorkQueue($workQueue);

//            //assign Work Queue to specific price list
//            if( 0 ) { //Don't use workQueues for specific price list
//                foreach ($fee->getPrices() as $specificPrice) {
//                    $specificPriceWorkQueues = $specificPrice->getWorkQueues();
//                    if ($specificPriceWorkQueues && count($specificPriceWorkQueues) == 0) {
//                        if ($testing) {
//                            echo "added (" . $fee->getShortInfo() . ") $workQueue to $specificPrice price list <br>";
//                        }
//                        $specificPrice->addWorkQueue($workQueue);
//                    }
//                }
//            }

            return true;
        }

        return false;
    }

    //Project exp date can be updated on final approval, edit project
    //use the value from the “Default duration of a project request before expiration (in months)”
    // to calculate the value (current date + this default duration) into the “Expected Expiration Date:” field.
    public function calculateAndSetProjectExpectedExprDate( $project, $useProjectSubmissionDate=false, $useProjectApprovalDate=false ) {

        //only for non-funded projects. clear for all funded projects.
        if( $project->getFunded() ) {
            return false;
        }

//        //update expiration date only once
//        if( $project->getExpectedExpirationDate() ) {
//            return false;
//        }

        //projectExprDuration -> setExpectedExpirationDate
        $projectExprDuration = $this->getTransresSiteProjectParameter('projectExprDuration',$project); //Month
        if( !$projectExprDuration ) {
            //default to 12
            $projectExprDuration = 12;
        }

        $projectExprDuration = intval($projectExprDuration);

        $addMonthStr = "+".$projectExprDuration." months";

        $expectedExprDate = NULL;
        $createDate = NULL;

        if( $useProjectSubmissionDate ) {
            $createDate = $project->getCreateDate();
            if( $createDate ) {
                $expectedExprDate = clone $createDate;
                $expectedExprDate->modify($addMonthStr);
            }
        }
        
        if( $useProjectApprovalDate ) {
            $approvalDate = $project->getApprovalDate();
            if( $approvalDate ) {
                $expectedExprDate = clone $approvalDate;
                $expectedExprDate->modify($addMonthStr);
            } else {
                $errorMsg = $project->getOid()." approval date is NULL";
                return $errorMsg;
            }
        }

        if( !$expectedExprDate ) {
            //use now date
            $expectedExprDate = new \DateTime($addMonthStr);
        }

        if( $expectedExprDate) {

            $originalExpDateStr = NULL;
            $originalExpDate = $project->getExpectedExpirationDate();
            if( $originalExpDate ) {
                $originalExpDateStr = $originalExpDate->format('d-m-Y');
            }

            $project->setExpectedExpirationDate($expectedExprDate);

            $res = $project->getOid().": original expDate=".$originalExpDateStr.", new exprDate=".$expectedExprDate->format('d-m-Y');

            //for echo
            $createDateStr = NULL;
            if( $createDate ) {
                $createDateStr = $createDate->format('d-m-Y');
                $status = $project->getState();
                $res = $project->getOid() ." (created ". $createDateStr. ", " . $status ."): original expDate=$originalExpDateStr, new exprDate=".$expectedExprDate->format('d-m-Y');
            }

            return $res;
        }

        return false;
    }

    public function getProjectExprDurationEmail($project=NULL) {
        $projectExprDurationEmail = $this->getTransresSiteProjectParameter('projectExprDurationEmail',$project); //Month
        if( !$projectExprDurationEmail ) {
            //default to 6
            $projectExprDurationEmail = 6;
        }
        return $projectExprDurationEmail;
    }

    public function isProjectExpired( $project ) {
        //only for non-funded projects. clear for all funded projects.
        if( $project->getFunded() ) {
            return false;
        }

        $now = new \DateTime();
        $expirationDate = $project->getExpectedExpirationDate();
        if( $expirationDate && $now > $expirationDate ) {
            return true;
        }

        return false;
    }

    public function getExpectedExpirationDateChoices() {
        $expectedExpirationDateChoices = array(
            'All' => 'All',
            'Expired' => 'Expired',
            'Expiring' => 'Expiring',
            'Current/Non-expired' => 'Current/Non-expired',
            //'test' => 'test'
        );

        return $expectedExpirationDateChoices;
    }

    public function getProjectRequesterGroupChoices() {
        $choiceRequesterGroups = array(
            'Any Requester Group' => 'Any',
            'No Requester Group' => 'None'
        );

        $requesterGroups = $this->getTransResRequesterGroups();
        foreach($requesterGroups as $requesterGroup) {
            $choiceRequesterGroups[$requesterGroup->getName()] = $requesterGroup->getId();
        }

        return $choiceRequesterGroups;
    }

    //on the user facing fee schedule page, show internal pricing if the logged
    // in user is associated with (PI, etc) and has any projects (even closed)
    // that have “Internal pricing” attribute associated with it
    public function getUserAssociatedSpecificPriceList( $asPriceTypeList=FALSE ) {
        
        //get the project where this user is PI and priceList is not NULL

        $user = $this->security->getUser();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Project'] by [Project::class]
        $repository = $this->em->getRepository(Project::class);
        $dql =  $repository->createQueryBuilder("project");

        //$dql->select('project');

        //$dql->distinct('project.priceList');
        $dql->select('DISTINCT (project.priceList)');

        //$dql->select('DISTINCT (project.submitter)');

        //projectType
        //$dql->select('DISTINCT (project.projectType)');

        if(1) {
            $dql->leftJoin('project.priceList', 'priceList');
            $dql->leftJoin('project.submitter', 'submitter');
            $dql->leftJoin('project.principalInvestigators', 'principalInvestigators');
            $dql->leftJoin('project.principalIrbInvestigator', 'principalIrbInvestigator');
            $dql->leftJoin('project.submitInvestigators', 'submitInvestigators');
            $dql->leftJoin('project.coInvestigators', 'coInvestigators');
            $dql->leftJoin('project.pathologists', 'pathologists');
            $dql->leftJoin('project.billingContact', 'billingContact');
            $dql->leftJoin('project.contacts', 'contacts');
        }

        //$dql->orderBy("project.id","DESC");
        //$dql->groupBy('project.priceList');
        //$dql->groupBy('project.projectType');
        //$dql->groupBy('project.submitter');

        $dqlParameters = array();

        $dql->andWhere("project.priceList IS NOT NULL");

        //3) logged in user is requester (only if not admin)
        $myRequestProjectsCriterion =
            "principalInvestigators.id = :userId OR " .
            "principalIrbInvestigator.id = :userId OR " .
            "submitInvestigators.id = :userId OR " .
            "coInvestigators.id = :userId OR " .
            "pathologists.id = :userId OR " .
            "contacts.id = :userId OR " .
            "billingContact.id = :userId OR " .
            "submitter.id = :userId";

        $dqlParameters["userId"] = $user->getId();
        $dql->andWhere($myRequestProjectsCriterion);

        $query = $dql->getQuery();

        //echo "projectId=".$project->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";
        //echo "query=".$query->getSql()."<br>";

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $projectPriceLists = $query->getResult();
        //echo "projectPriceLists=".count($projectPriceLists)."<br>";

        $priceListArr = array();

        if(1) {
            foreach( $projectPriceLists as $projectPriceList ) {
                //dump($projectPriceList);
                //echo "projectPriceList=".$projectPriceList."<br>";

                $priceListId = $projectPriceList[1];
                //echo "priceListId=" . $priceListId . "<br>";

                if( $asPriceTypeList ) {
                    if( $priceListId ) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:PriceTypeList'] by [PriceTypeList::class]
                        $priceList = $this->em->getRepository(PriceTypeList::class)->find($priceListId);
                        if ($priceList) {
                            $priceListArr[] = $priceList;
                        }
                    }
                } else {
                    $priceListArr[] = $priceListId;
                }
            }
        }

        //testing
        //$priceListArr = array(3);

//        echo "priceListArr=".count($priceListArr)."<br>";
//        foreach ($priceListArr as $priceList) {
//            echo "priceList=".$priceList."<br>";
//        }

        //exit("111");
        return $priceListArr;

    }

    //$fee - RequestCategoryTypeList
    //$specificPriceListArr - array of specific price lists (PriceTypeList) that allow to view by a user
    public function getPriceListsWithNonEmptyPrices( $fee, $specificPriceListArr ) {
        
        //1) check if default fees are not NULL
        $feeFirstItem = $fee->getFee();
        $feeAdditionalItem = $fee->getFeeAdditionalItem();
        if( $feeFirstItem || $feeAdditionalItem ) {
            return TRUE;
        }

        if( $specificPriceListArr ) {

            if(0) {
                //2) check if $specificPriceListArr fees are not NULL
                foreach ($specificPriceListArr as $specificPriceListId) {
                    //check if $specificPriceListType exists in one of the specific prices ($fee->getPrices())
                    foreach ($fee->getPrices() as $specificPrice) {
                        if ($specificPrice->getPriceList()) {
                            if ($specificPrice->getPriceList()->getId() == $specificPriceListId) {
                                $specificFee = $specificPrice->getFee();
                                $specificFeeAdditionalItem = $specificPrice->getFeeAdditionalItem();
                                if ($specificFee || $specificFeeAdditionalItem) {
                                    return TRUE;
                                }
                            }
                        }
                    }
                }
            }

            if(0) {
                //2) check if $specificPriceListArr (list of priceTypeList user can view) exists in the specific prices ($fee->getPrices()) as priceList
                //via loop of existing specific prices
                foreach ($fee->getPrices() as $specificPrice) {
                    $thisPriceType = $specificPrice->getPriceList(); //PriceTypeList
                    if ($thisPriceType) {
                        if (in_array($thisPriceType, $specificPriceListArr)) {
                            if ($thisPriceType) {
                                $specificFee = $thisPriceType->getFee();
                                $specificFeeAdditionalItem = $thisPriceType->getFeeAdditionalItem();
                                if ($specificFee || $specificFeeAdditionalItem) {
                                    return TRUE;
                                }
                            }
                        }
                    }
                }
            }

            if(1) {
                //via doctrine
                $specificPrices = $this->findSpecificPriceByFeePriceType($fee, $specificPriceListArr);
                if (count($specificPrices) > 0) {
                    return TRUE;
                }
            }

        }

        return FALSE;
    }
    
    public function findSpecificPriceByFeePriceType($fee, $priceTypeListIds) {
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:Prices'] by [Prices::class]
        $repository = $this->em->getRepository(Prices::class);
        $dql =  $repository->createQueryBuilder("prices");
        $dql->select('prices');

        $dql->leftJoin("prices.requestCategoryType", "requestCategoryType");
        $dql->leftJoin("prices.priceList", "priceList");

        $dql->andWhere("requestCategoryType.id = :feeId");
        $dqlParameters["feeId"] = $fee->getId();

        $dql->andWhere("priceList.id IN (:priceListIds)");
        $dqlParameters["priceListIds"] = $priceTypeListIds;

        $dql->andWhere("prices.fee IS NOT NULL OR prices.feeAdditionalItem IS NOT NULL");

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $prices = $query->getResult();

        return $prices;
    }

    //true or false. If true project will be shown with different tissue questions (for example, CP project)
    public function specialProjectSpecialty( $project ) {
        if( !$project ) {
            return false;
        }
        
        $projectSpecialty = $project->getProjectSpecialty();
        if( $projectSpecialty ) {
            $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();
            if( $projectSpecialtyAbbreviation == 'cp' ) {
                return true;
            }
        }
        return false;
    }
    public function specialExtraProjectSpecialty( $project ) {
        if( !$project ) {
            return false;
        }

        $projectSpecialty = $project->getProjectSpecialty();
        if( $projectSpecialty ) {
            $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();
            //echo "projectSpecialtyAbbreviation=$projectSpecialtyAbbreviation<br>";
            if( $projectSpecialtyAbbreviation == 'cp' || $projectSpecialtyAbbreviation == 'ap-cp' ) {
                return true;
            }
        }
        return false;
    }

    public function getProjectAttachments( $project ) {
        $attachmentArr = array();

        //Export project summary to a PDF ($projectPdfs)
        $pdfPath = null;
        $pdf = $project->getSingleProjectPdf();
        if( $pdf ) {
            $pdfPath = $pdf->getServerPath();
            if( !file_exists($pdfPath) ) {
                $pdfPath = null;
            }
        }

        if( !$pdfPath ) {
            $transresPdfUtil = $this->container->get('transres_pdf_generator');
            $res = $transresPdfUtil->generateAndSaveProjectPdf($project);

            $filename = $res['filename'];
            $filsize = $res['size'];
            //echo "filsize=$filsize; filename=$filename <br>";

            if ($filename && $filsize) {
                //exit("OK: filsize=$filsize; filename=$filename");
                $pdf = $project->getSingleProjectPdf();
                if( $pdf && $pdf->pathExist() ) {
                    $pdfPath = $pdf->getServerPath();
                    if( !file_exists($pdfPath) ) {
                        $pdfPath = null;
                    }
                }
            }
        }

        if( $pdfPath ) {
            $pdfName = $pdf->getDescriptiveFilename();
            $attachmentArr[] = array('path'=>$pdfPath,'name'=>$pdfName);
        }

        //Project Intake Form Documents (documents)
        $doc = $project->getSingleDocument();
        if( $doc && $doc->pathExist() ) {
            $docAttachmentArr = $doc->getAttachmentElementArr();
            if ($docAttachmentArr) {
                $attachmentArr[] = $docAttachmentArr;
            }
        }

        //check for IRB approval letter (irbApprovalLetters)
        $doc = $project->getSingleIrbApprovalLetter();
        if( $doc && $doc->pathExist() ) {
            $docAttachmentArr = $doc->getAttachmentElementArr();
            if ($docAttachmentArr) {
                $attachmentArr[] = $docAttachmentArr;
            }
        }

        //Human Tissue Form ($humanTissueForms)
        $doc = $project->getSingleHumanTissueForm();
        if( $doc && $doc->pathExist() ) {
            $docAttachmentArr = $doc->getAttachmentElementArr();
            if ($docAttachmentArr) {
                $attachmentArr[] = $docAttachmentArr;
            }
        }

        return $attachmentArr;
    }

    //NOT USED
    //Testing select from manytomany by not existed IDs
    public function feeFilterTest() {
        $specialties = array(7,8);
        $specialtiesStr = implode(",", $specialties);
        echo "specialties=".$specialtiesStr." <br>";

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:RequestCategoryTypeList'] by [RequestCategoryTypeList::class]
        $repository = $this->em->getRepository(RequestCategoryTypeList::class); //fee schedule list
        $dql =  $repository->createQueryBuilder("list");
        //$dql->select("DISTINCT(list.id) as id, list.name");
        $dql->select("list");
        $dql->leftJoin("list.projectSpecialties", "projectSpecialties");
        //$dql->innerJoin("list.projectSpecialties", "projectSpecialties");

        if(0) {
            $dql->leftJoin("list.projectSpecialties", "projectSpecialties");
            //$specialtyStr = "projectSpecialties.id IN ($specialtiesStr)";
            $specialtyStr = "projectSpecialties.id = $specialtiesStr"; //=67, !=106 total=109
            $specialtyStr = "projectSpecialties.id IN (7,8)";
            //$specialtyStr = "projectSpecialties.id IN (7,8) AND projectSpecialties.requestCategories IS NULL";
            echo "specialtyStr: $specialtyStr <br>";
            $dql->where($specialtyStr);
            //$dql->andWhere($specialtyStr);
        }

//SELECT t0.id FROM public.transres_requestcategorytypelist t0
//LEFT JOIN public.transres_requestcategory_specialty t2 ON t0.id=t2.requestcategorytypelist_id
//LEFT JOIN public.transres_specialtylist t1 ON t1.id = t2.specialtylist_id
//WHERE t1.id NOT IN (5)
//GROUP BY t0.id
//ORDER BY t0.id DESC

        //https://stackoverflow.com/questions/48942150/many-to-many-relation-select-all-a-except-for-those-linked-to-b
/* Working
        SELECT  a.*
FROM    public.transres_requestcategorytypelist a
WHERE   NOT EXISTS (SELECT 1
                    FROM public.transres_requestcategory_specialty b
                    WHERE a.id = b.requestcategorytypelist_id
        AND b.specialtylist_id IN (7, 8))

SELECT  a.*
FROM    public.transres_requestcategorytypelist a
WHERE   NOT EXISTS (
    SELECT 1
    FROM public.transres_requestcategory_specialty b
    WHERE a.id = b.requestcategorytypelist_id
    AND b.specialtylist_id IN (7,5)
)


SELECT a.*
FROM public.transres_requestcategorytypelist a
LEFT JOIN public.transres_requestcategory_specialty j
    ON a.id = j.requestcategorytypelist_id AND j.specialtylist_id IN (7, 8)
WHERE
    j.requestcategorytypelist_id IS NULL;
*/
//        if(0) {
//            //FROM transres_requestcategory_specialty b
//            $dql = "
//                SELECT list
//                FROM AppTranslationalResearchBundle:RequestCategoryTypeList list
//                WHERE NOT EXISTS (
//                  SELECT 1
//                  FROM transres_requestcategory_specialty b
//                  WHERE list.id = b.requestcategorytypelist_id
//                  AND b.specialtylist_id IN (7, 8)
//                )
//            ";
//            //$query = $this->em->createQuery($sql1); //->setParameter('ids', $specialtiesStr);
//        }
        //Working
        if(1) {
            $conn = $this->em->getConnection();

            $dql = "
                SELECT list.id as id
                FROM transres_requestcategorytypelist list
                WHERE NOT EXISTS (
                  SELECT 1
                  FROM transres_requestcategory_specialty b
                  WHERE list.id = b.requestcategorytypelist_id
                  AND b.specialtylist_id IN ($specialtiesStr)
                )
            ";

            $res = $conn->executeQuery($dql)->fetchAll(\PDO::FETCH_COLUMN);
            dump($res);
            exit('111');
        }
        if(1) {
            //https://stackoverflow.com/questions/31536137/doctrine-not-exists-subquery
            $sub = $this->em->createQueryBuilder();
            $sub->select("t");
            //$sub->from("AppTranslationalResearchBundle:SpecialtyList","t");
            $sub->from(SpecialtyList::class,"t");
            $sub->leftJoin("t.requestCategories","requestCategories");
            $sub->andWhere('projectSpecialties = list.id');
            $sub->andWhere("projectSpecialties IN ($specialtiesStr)");

            $dql->andWhere($dql->expr()->not($dql->expr()->exists($sub->getDQL())));
        }
        if(0) {
            //https://stackoverflow.com/questions/43162939/doctrine-query-with-join-table
            $dql->join('list.projectSpecialties', 'projectSpecialties', 'WITH', 'list MEMBER OF projectSpecialties.requestCategories')
                ->where('projectSpecialties IN (7,8)')
//                //->setParameter('ids', 7)
            ;

            //public function innerJoin($join, $alias, $conditionType = null, $condition = null);
//            $dql->join('list.projectSpecialties', 'projectSpecialties', 'WHERE', 'NOT EXISTS (SELECT 1 FROM )')
//                ->where('projectSpecialties NOT IN (7,8)')
//            ;
        }
        if(0) {
            //https://stackoverflow.com/questions/43162939/doctrine-query-with-join-table
            $dql->join(
                'list.projectSpecialties', 'projectSpecialties',
                'ON',
                'list.id = projectSpecialties.requestcategorytypelist_id AND projectSpecialties.specialtylist_id IN (7,8)'
            )
                ->where('projectSpecialties.requestcategorytypelist_id IS NULL')
//                //->setParameter('ids', 7)
            ;

            //public function innerJoin($join, $alias, $conditionType = null, $condition = null);
//            $dql->join('list.projectSpecialties', 'projectSpecialties', 'WHERE', 'NOT EXISTS (SELECT 1 FROM )')
//                ->where('projectSpecialties NOT IN (7,8)')
//            ;

        }

        if(0) {
            $specialtyStr = "NOT EXISTS (SELECT 1 FROM projectSpecialties WHERE projectSpecialties.id IN (7,8) )";
            echo "specialtyStr: $specialtyStr <br>";
            $dql->where($specialtyStr);
            //$dql->andWhere($specialtyStr);
        }

//        if(0) {
//            $query = $this->em->createQuery(
//                'SELECT list,specialty
//                FROM AppTranslationalResearchBundle:RequestCategoryTypeList list 
//                INNER JOIN AppTranslationalResearchBundle:SpecialtyList specialty 
//                WHERE list.id = :id
//                '
//            )->setParameter('id', $specialtiesStr);
//        }

//        if(0) {
//            $sql = "
//                SELECT list
//                FROM AppTranslationalResearchBundle:RequestCategoryTypeList list
//                INNER JOIN AppTranslationalResearchBundle:SpecialtyList specialty
//                WHERE list.id = :id
//            ";
//            $query = $this->em->createQuery($sql)->setParameter('id', $specialtiesStr);
//        }

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
        echo "query=" . $query->getSql() . "<br>";

        //$dqlParameters['ids'] = $specialties;
        //$query->setParameters( $dqlParameters );
        $lists = $query->getResult();
        //$lists = $query->getOneOrNullResult();

        echo "count=".count($lists)."<br>";
        foreach ($lists as $list) {
            //dump($list);
            //echo $list['id']." ".$list['name']."<br>";
            $specArr = array();
            foreach ($list->getProjectSpecialties() as $spec) {
                $specArr[] = $spec . " (".$spec->getId().")";
            }
            echo $list->getId() . ": hide specialties for " . implode(", ", $specArr) . "<br>";
        }
        dump($lists);
        exit('111');
    }


    //Used by 127.0.0.1/translational-research/antibody-create-panels
    public function processExcelMisiPanels($filename, $startRaw=2, $endRaw=null) {
        exit('<br>exit processExcelMisiPanels: run only once');

        $testing = false;
        //$testing = true;

        if (file_exists($filename)) {
            echo "EXISTS: The file $filename <br><br>";
        } else {
            echo "Does Not EXISTS: The file $filename <br><br>";
        }

        //set_time_limit(18000); //18000 seconds => 5 hours 3600sec=>1 hour
        //ini_set('memory_limit', '7168M');

        //$transresUtil = $this->container->get('transres_util');
        //$logger = $this->container->get('logger');

        $misiLab = $this->em->getRepository(AntibodyLabList::class)->findOneByAbbreviation("MISI");
        if( !$misiLab ) {
            exit("Lab is not found by name MISI");
        }

        //$inputFileName = __DIR__ . "/" . $filename;
        echo "==================== Processing $filename =====================<br>";

        try {
            if(1) {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filename);
                $reader->setReadDataOnly(true);
                $objPHPExcel = $reader->load($filename);
            }
        } catch( \Exception $e ) {
            $error = 'Error loading file "'.pathinfo($filename,PATHINFO_BASENAME).'": '.$e->getMessage();
            //$logger->error($error);
            die($error);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        //$highestColumn = $sheet->getHighestColumn();
        $highestColumn = 'D'; //max column for this file
        echo "highestRow=".$highestRow."; highestColum=".$highestColumn."<br>";

        //$currentDate = date('Y-m-d H:i:s');
        //$newline = "\n\r";

        $startRaw = 3;
        $limitRow = 464;

        $previousRequestId = null;
        $batchSize = 20;
        $count = 0;
        $panel = 1;
        $thisReactivity = NULL;
        $panelArr = array();

        //for each request in excel (start at row 2)
        for( $row = $startRaw; $row <= $limitRow; $row++ ) {

            $count++;

            //stop for testing
//            if( $panel > 4 ) {
//                exit("Exit on panel $panel");
//            }

            //Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);

            $antibodyName   = trim($rowData[0][0]);
            $host           = trim($rowData[0][1]);
            $clone          = trim($rowData[0][2]);
            $reactivity     = trim($rowData[0][3]);

            if( !str_contains($antibodyName,'###') ) {
                //do not add ### row
                //echo $count.": row=[$row]: [$antibodyName] [$host] [$clone] [$reactivity] <br>";
                $panelArr[$panel][] = array($antibodyName,$host,$clone,$reactivity);
            }

            //dump($rowData);
            //exit('111');

            if( $reactivity ) {
                $thisReactivity = $reactivity;
                //echo "1 thisReactivity=>($thisReactivity) <br>";
            }
            //echo "2 thisReactivity=>($thisReactivity) <br>";

            if( str_contains($antibodyName,'###') ) {

                //dump($panelArr);
                //exit('111');

//                if( !$thisReactivity ) {
//                    exit("Logical error: Reactivity not found for panel=$panel, antibody=$antibodyName");
//                }

                //process this panel
                //1) find or create panel by name $panel
                $panelObject = $this->findOrCreatePanel($panel,$testing);
                if( !$panelObject ) {
                    exit("Panel not found by name $panel");
                }

                //$this->em->flush(); //testing

                //2 find or create antibodies from array $panelArr
                $antibodyCount = $this->processPanel($panelArr[$panel],$thisReactivity,$panelObject,$misiLab,$testing);
                echo "EOF panel: $panel thisReactivity=$thisReactivity, antibody count=".$antibodyCount."<br><br>";
                //exit('111');

                $thisReactivity = NULL;
                $panel++;
            }


            //if( ($count % $batchSize) === 0 ) {
            //    $this->em->flush();
            //}

        }//for

        exit('eof processExcelMisiPanels');
        //$this->em->flush();
    }

    public function processPanel( $panelArr, $thisReactivity, $panelObject, $misiLab, $testing=false ) {
        $logger = $this->container->get('logger');
        echo "processPanel: panelObject ID=".$panelObject->getId()."<br>";

        if( !$thisReactivity ) {
            exit("Logical error: Reactivity [$thisReactivity] not found for antibody=".$panelArr[0][0]);
        }

        $antibodyCount = 0;
        foreach($panelArr as $antibodyData) {
            $antibodyName   = trim($antibodyData[0]);
            $host           = trim($antibodyData[1]);
            $clone          = trim($antibodyData[2]);
            //$reactivity     = trim($antibodyData[3]);
            echo "processPanel: [$antibodyName] [$host] [$clone] [$thisReactivity] <br>";

            if( !$antibodyName || !$host || !$clone || !$thisReactivity ) {
                //exit("Logical error: Some parameters are empty: [$antibodyName] [$host] [$clone] [$thisReactivity]");
            }

            $antibody = $this->findOrCreateAntibody($antibodyName,$host,$clone,$thisReactivity,$testing);
            //echo "Antibody found/created: ".$antibody->getName()."<br>";
            if( !$antibody ) {
                exit("Antibody not found/create by name $antibodyName");
            }

            $antibody->addAntibodyPanel($panelObject);
            $antibody->addAntibodyLab($misiLab);
            $antibodyCount++;
            if( !$testing ) {
                $this->em->flush();
                $logger->notice("processPanel: Antibody [$antibodyName] updated with panel and lab");
            }
        }
        return $antibodyCount;
    }

    public function findOrCreatePanel( $panelName, $testing=false ) {
        $logger = $this->container->get('logger');
        $panelName = $panelName.""; //convert to string
        $panelObject = $this->em->getRepository(AntibodyPanelList::class)->findOneByName($panelName);
        if( !$panelObject ) {
            $userSecUtil = $this->container->get('user_security_utility');
            $user = $this->security->getUser();
            $panelObject = new AntibodyPanelList($user);
            $count = NULL;
            $userSecUtil->setDefaultList( $panelObject, $count, $user, $panelName );
            $panelObject->setType('default');
            echo "findOrCreatePanel: New panel created: [$panelName] <br>";
            if( !$testing ) {
                $this->em->persist($panelObject);
                $this->em->flush();
                $logger->notice("findOrCreatePanel: New panel created: [$panelName]");
            }
        } else {
            echo "findOrCreatePanel: panel found by name [$panelName] <br>";
        }
        return $panelObject;
    }

    //check if antibody already exists by name, host, clone, reactivity and if LAB is not MISI,
    //if antibody does not exist => create new
    public function findOrCreateAntibody( $name, $host, $clone, $reactivity, $testing=false ) {
        $logger = $this->container->get('logger');
        $repository = $this->em->getRepository(AntibodyList::class);
        $dql =  $repository->createQueryBuilder("list");

        $dql->leftJoin('list.antibodyLabs','antibodyLabs');

        $dql->where("LOWER(list.name) LIKE LOWER(:name)");
        $dql->andWhere("LOWER(list.host) LIKE LOWER(:host)");
        $dql->andWhere("LOWER(list.clone) LIKE LOWER(:clone)");
        $dql->andWhere("LOWER(list.reactivity) LIKE LOWER(:reactivity)"); //has string?
        //$dql->andWhere("LOWER(list.reactivity) = LOWER(:reactivity)");
        $dql->andWhere("LOWER(antibodyLabs.name) = LOWER(:antibodyLab)");
        $dql->andWhere("LOWER(antibodyLabs.abbreviation) = LOWER(:antibodyLab)");

        //$dql->andWhere("list.type = :typedef OR list.type = :typeadd");

        $parameters = array(
            'name' => $name,
            'host' => $host,
            'clone' => $clone,
            'reactivity' => $reactivity,
            'antibodyLab' => 'MISI'
            //'typedef' => 'default',
            //'typeadd' => 'user-added',
        );

        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

        $query->setParameters($parameters);

        $antibodies = $query->getResult();
        echo "findOrCreateAntibody: found antibodies=".count($antibodies)."<br>";

        if( count($antibodies) == 1 ) {
            //exit("!!! Antibodies found name=[$name], ID=".$antibodies[0]->getId());
            echo "Antibodies found name=[$name] <br>";
            return $antibodies[0];
        }

        if( count($antibodies) > 1 ) {
            exit("Multiple antibodies found");
        }


        if( count($antibodies) == 0 ) {
            echo "findOrCreateAntibody: create new antibody: [$name] [$host] [$clone] [$reactivity]<br>";
            //exit('Created again?');
            $userSecUtil = $this->container->get('user_security_utility');
            $user = $this->security->getUser();
            $antibody = new AntibodyList($user);
            $count = NULL;
            $userSecUtil->setDefaultList( $antibody, $count, $user, $name );
            $antibody->setType('default');

            $antibody->setHost($host);
            $antibody->setClone($clone);
            $antibody->setReactivity($reactivity);
            if( !$testing ) {
                echo "findOrCreateAntibody: create new antibody: before flash. Antibody ID=".$antibody->getId()."<br>";
                $this->em->persist($antibody);
                $this->em->flush();
                $logger->notice("findOrCreateAntibody: New antibody created: [$name] [$host] [$clone] [$reactivity]");
            }
            return $antibody;
        }

        //logical error
        exit("findOrCreateAntibody: logical error");
        return NULL;
    }

    function findProjectGoals( $projectId, $description=null ) {
        if( $projectId ) {
            $repository = $this->em->getRepository(ProjectGoal::class);
            $dql =  $repository->createQueryBuilder("projectGoal");

            if( $description ) {
                $dql->where("projectGoal.description = :description AND projectGoal.project = :projectId");
                $parameters = array(
                    'description' => $description,
                    'projectId' => $projectId
                );
            } else {
                $dql->where("projectGoal.project = :projectId");
                $parameters = array(
                    'projectId' => $projectId
                );
            }
            $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);

            $query->setParameters($parameters);

            $projectGoals = $query->getResult();

            return $projectGoals;
        }
        return NULL;
    }

    public function findNextProjectGoalOrderinlist( $projectId ) {
        //$goals = $this->findProjectGoals();
        $repository = $this->em->getRepository(ProjectGoal::class);
        $dql =  $repository->createQueryBuilder("projectGoal");
        $dql->select("projectGoal.orderinlist");
        $dql->where("projectGoal.project = :projectId");
        $parameters = array(
            'projectId' => $projectId
        );
        $dql->orderBy('projectGoal.orderinlist', 'DESC');
        $query = $dql->getQuery(); //$query = $this->em->createQuery($dql);
        $query->setParameters($parameters);
        $query->setMaxResults(1);
        $projectGoals = $query->getResult();

        if( count($projectGoals) == 0 ) {
            return 1;
        }

        $orderinlist = $projectGoals[0]['orderinlist'];
        if( $orderinlist ) {
            $orderinlist = (int)$orderinlist + 1;
        } else {
            $orderinlist = 1;
        }
        //exit('$orderinlist='.$orderinlist);
        return $orderinlist;
    }

    public function processExistingProjectGoals( $transresRequest, $form ) {
        $break = "<br>";

        $existingProjectGoalsIds = $form->get('existingProjectGoals')->getData();
        //dump($existingProjectGoalsIds);

        $projectGoalMsgArr = array();

        foreach($existingProjectGoalsIds as $projectGoalId) {
            $projectGoal = $this->em->getRepository(ProjectGoal::class)->find($projectGoalId);
            //echo $transresRequest->getId().": Project Goal = ".$projectGoal->getId()."<br>";
            $transresRequest->addProjectGoal($projectGoal);
            $this->em->flush();
            $descripton = $this->tokenTruncate($projectGoal->getDescription(), 100);
            $projectGoalMsgArr[] = "Added project goal ID ".$projectGoal->getId(). ", description=".$descripton;
        }

        $projectGoalMsg = "";
        if( count($projectGoalMsgArr) > 0 ) {
            $projectGoalMsg = $break.$break.implode($break, $projectGoalMsgArr).$break.$break;
        }

        //exit('processExistingProjectGoals');
        return $projectGoalMsg;
    }
    
}