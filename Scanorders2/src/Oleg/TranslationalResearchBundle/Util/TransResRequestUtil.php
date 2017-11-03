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
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/25/2017
 * Time: 09:48 AM
 */
class TransResRequestUtil
{

    protected $container;
    protected $em;
    protected $secTokenStorage;
    protected $secAuth;

    public function __construct( $em, $container ) {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }


    public function getTransResRequestTotalFeeHtml( $project ) {

        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest');

        $dql->leftJoin('transresRequest.submitter','submitter');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('submitter.infos','submitterInfos');

        $dqlParameters = array();

        $dql->andWhere("project.id = :projectId");

        $dqlParameters["projectId"] = $project->getId();

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $requests = $query->getResult();

        $total = 0;

        foreach($requests as $request) {
            $subTotal = $this->getTransResRequestFeeHtml($request);
            if( $subTotal ) {
                $total = $total + $subTotal;
            }
        }

        if( $total ) {
            $res = "Total fees: $$total";
            return $res;
        }

        return null;
    }

    public function getTransResRequestFeeHtml( $request ) {
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');

        $completed = $transResFormNodeUtil->getProjectFormNodeFieldByName(
            $request,
            "Completed #",
            "HemePath Translational Research",
            "HemePath Translational Research Request",
            "Request",
            false
        );
        //echo "completed=".$completed."<br>";

        $requestCategoryTypeDropdownObject = $transResFormNodeUtil->getProjectFormNodeFieldByName(
            $request,
            "Category Type",
            "HemePath Translational Research",
            "HemePath Translational Research Request",
            "Request",
            true
        );

        if( $completed && $requestCategoryTypeDropdownObject ) {
            //echo "requestCategoryTypeDropdownObject=".$requestCategoryTypeDropdownObject."<br>";
            //echo "requestCategoryType feeUnit=".$requestCategoryType->getFeeUnit()."<br>";
            //echo "requestCategoryType fee=".$requestCategoryType->getFee()."<br>";

            $fee = $requestCategoryTypeDropdownObject->getFee();

            if( $fee ) {
                $subTotal = intval($completed) * intval($fee);
                return $subTotal;
            }
        }

        return null;
    }

    public function getProgressStateArr() {
        $stateArr = array(
            'active',
            'cancel',
            'pending',
            'investigator',
            'histo',
            'ihc',
            'mol',
            'retrieval',
            'payment',
            'slidescanning',
            'block',
            'complet',
            'suspend'
        );

        $stateChoiceArr = array();

        foreach($stateArr as $state) {
            //$label = $state;
            $label = $this->getProgressStateLabelByName($state);
            $label = $label . " (" . $state . ")";
            $stateChoiceArr[$label] = $state;
        }

        return $stateChoiceArr;
    }


    public function getBillingStateArr() {
        $stateArr = array(
            'active',
            'cancel',
            'missinginfo',
            'invoice',
            'paid',
            'refund',
            'partially_refund',
        );

        $stateChoiceArr = array();

        foreach($stateArr as $state) {
            //$label = $state;
            $label = $this->getBillingStateLabelByName($state);
            $label = $label . " (" . $state . ")";
            $stateChoiceArr[$label] = $state;
        }

        return $stateChoiceArr;
    }


    public function getProgressStateLabelByName( $stateName ) {
        switch ($stateName) {
            case "draft":
                $state = "Draft";
                break;
            case "active":
                $state = "Active";
                break;
            case "cancel":
                $state = "Cancel";
                break;
            case "pending":
                $state = "Pending";
                break;
            case "investigator":
                $state = "Investigator";
                break;
            case "histo":
                $state = "Histo";
                break;
            case "ihc":
                $state = "Ihc";
                break;
            case "mol":
                $state = "Mol";
                break;
            case "retrieval":
                $state = "Retrieval";
                break;
            case "payment":
                $state = "Payment";
                break;
            case "slidescanning":
                $state = "Slide Scanning";
                break;
            case "block":
                $state = "Block";
                break;
            case "complet":
                $state = "Complet";
                break;
            case "suspend":
                $state = "Suspend";
                break;

            default:
                $state = "<$stateName>";

        }
        return $state;
    }
    public function getBillingStateLabelByName( $stateName ) {
        switch ($stateName) {
            case "draft":
                $state = "Draft";
                break;
            case "active":
                $state = "Active";
                break;
            case "cancel":
                $state = "Cancel";
                break;
            case "missinginfo":
                $state = "Pending additional information from submitter";
                break;
            case "invoice":
                $state = "Invoice";
                break;
            case "paid":
                $state = "Paid";
                break;
            case "refund":
                $state = "Refund";
                break;
            case "partially_refund":
                $state = "Partially Refund";
                break;

            default:
                $state = "<$stateName>";

        }
        return $state;
    }
    public function getRequestStateLabelByName( $stateName, $statMachineType ) {
        if( $statMachineType == 'progress' ) {
            return $this->getProgressStateLabelByName($stateName);
        }
        if( $statMachineType == 'billing' ) {
            return $this->getBillingStateLabelByName($stateName);
        }
        return "<".$stateName.">";
    }

    public function getHtmlClassTransition( $stateStr ) {
        return "btn btn-success transres-review-submit";
    }

    //get Request IDs for specified RequestCategoryTypeList
    public function getRequestIdsFormNodeByCategory( $categoryType ) {

        if( !$categoryType ) {
            return array();
        }
        //echo $categoryType->getId().": categoryType=".$categoryType->getOptimalAbbreviationName()."<br>";

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $ids = array();

        //1) get formnode by category type name "Category Type" under formnode "HemePath Translational Research Request"->"Request"
        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents(
            "Category Type",
            "HemePath Translational Research",
            "HemePath Translational Research Request",
            "Request"
        );
        //echo "fieldFormNode=".$fieldFormNode->getId()."<br>";
        if( !$fieldFormNode ) {
            return array();
        }

        //2) get objectTypeDropdowns by:
        // value=$categoryType->getId(), entityNamespace="Oleg\TranslationalResearchBundle\Entity" , entityName="TransResRequest"
        $mapper = array(
            "entityName" => "TransResRequest",
            "entityNamespace" => "Oleg\\TranslationalResearchBundle\\Entity",
        );
        $objectTypeDropdowns = $formNodeUtil->getFormNodeListRecordsByReceivingObjectValue($fieldFormNode,$categoryType->getId(),$mapper,"exact");
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

    public function getRequestIdsFormNodeByComment( $commentStr ) {

        if( !$commentStr ) {
            return array();
        }
        //echo "commentStr=".$commentStr."<br>";

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $ids = array();
        $objectTypeDropdowns = array();

        //1) get formnode by category type name "Category Type" under formnode "HemePath Translational Research Request"->"Request"
        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents(
            "Comment",
            "HemePath Translational Research",
            "HemePath Translational Research Request",
            "Request"
        );
        //echo "fieldFormNode=".$fieldFormNode->getId()."<br>";
        if( !$fieldFormNode ) {
            return array();
        }

        //2) get objectTypeDropdowns by:
        // value=$categoryType->getId(), entityNamespace="Oleg\TranslationalResearchBundle\Entity" , entityName="TransResRequest"
        $mapper = array(
            "entityName" => "TransResRequest",
            "entityNamespace" => "Oleg\\TranslationalResearchBundle\\Entity",
        );
        $objectTypeDropdowns = $formNodeUtil->getFormNodeListRecordsByReceivingObjectValue($fieldFormNode,$commentStr,$mapper,"like");
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

    public function isRequestProgressReviewer($transresRequest) {

        return true;
    }

    public function isRequestBillingReviewer($transresRequest) {

        return true;
    }

    public function getReviewEnabledLinkActions( $transresRequest, $statMachineType ) {
        //exit("get review links");
        $transresUtil = $this->container->get('transres_util');
        $project = $transresRequest->getProject();
        $user = $this->secTokenStorage->getToken()->getUser();

        $links = array();

        ////////// Check permission //////////
        $verified = false;
        if( $statMachineType == 'progress' ) {
            if( $transresUtil->isAdminOrPrimaryReviewer() === false && $this->isRequestProgressReviewer($transresRequest) === false ) {
                exit("return: progress not allowed");
                return $links;
            }
            $workflow = $this->container->get('state_machine.transres_request_progress');
            $transitions = $workflow->getEnabledTransitions($transresRequest);
            $verified = true;
        }
        if( $statMachineType == 'billing' ) {
            if( $transresUtil->isAdminOrPrimaryReviewer() === false && $this->isRequestBillingReviewer($transresRequest) === false ) {
                exit("return: billing not allowed");
                return $links;
            }
            $workflow = $this->container->get('state_machine.transres_request_billing');
            $transitions = $workflow->getEnabledTransitions($transresRequest);
            $verified = true;
        }
        if( $verified == false ) {
            return $links;
        }
        ////////// EOF Check permission //////////

        foreach( $transitions as $transition ) {

            //$this->printTransition($transition);
            $transitionName = $transition->getName();
            //echo "transitionName=".$transitionName."<br>";

//            if( false === $this->isUserAllowedFromThisStateByProjectOrReview($project,$review) ) {
//                continue;
//            }

            $tos = $transition->getTos();
            //$froms = $transition->getFroms();
            foreach( $tos as $to ) {
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
                    'translationalresearch_request_transition_action_by_review',
                    array(
                        'transitionName'=>$transitionName,
                        'id'=>$transresRequest->getId(),
                        'statMachineType'=>$statMachineType
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                //$thisUrl = "#";

                //$label = ucfirst($transitionName)." (mark as ".ucfirst($to);
                $label = $this->getRequestStateLabelByName($to,$statMachineType);

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



    //change transition (by the $transitionName) of the project
    public function setRequestTransition( $transresRequest, $statMachineType, $transitionName, $to, $testing ) {

        if( !$transresRequest ) {
            throw $this->createNotFoundException('Request object does not exist');
        }

        if( !$transresRequest->getId() ) {
            throw $this->createNotFoundException('Request object ID is null');
        }

        //echo "transitionName=".$transitionName."<br>";
        $user = $this->secTokenStorage->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $break = "\r\n";

        if( $statMachineType == 'progress' ) {
            $workflow = $this->container->get('state_machine.transres_request_progress');
            $transitions = $workflow->getEnabledTransitions($transresRequest);
        }
        if( $statMachineType == 'billing' ) {
            $workflow = $this->container->get('state_machine.transres_request_billing');
            $transitions = $workflow->getEnabledTransitions($transresRequest);
        }

        if( !$to ) {
            //Get Transition and $to
            $transition = $transresUtil->getTransitionByName($transresRequest, $transitionName);
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
                $subject = "Project ID ".$project->getOid()." has been sent to the stage '$label' from '".$originalStateLabel."'";
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
}