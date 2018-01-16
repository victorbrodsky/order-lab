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
use Oleg\TranslationalResearchBundle\Entity\Invoice;
use Oleg\TranslationalResearchBundle\Entity\InvoiceItem;
use Oleg\TranslationalResearchBundle\Entity\TransResSiteParameters;
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

        //echo "total=".$total."<br>";
        if( $total ) {
            $res = "Total fees: $".$total;
            return $res;
        }

        return null;
    }

    public function getTransResRequestFeeHtml( $request ) {
        $subTotal = 0;

        foreach($request->getProducts() as $product) {
            $requested = $product->getRequested();
            $completed = $product->getCompleted();
            $category = $product->getCategory();
            //echo "requested=$requested <br>";
            $fee = 0;
            $units = 0;
            if( $category ) {
                $fee = $category->getFee();
            }
            if( $requested ) {
                $units = intval($requested);
            }
            if( $completed ) {
                $units = intval($completed);
            }
            //echo "units=$units; fee=$fee <br>";
            if( $fee && $units ) {
                $subTotal = $subTotal + ($units * intval($fee));
            }
        }

        return $subTotal;
    }

    //TODO: modify for multiple sections
    public function getTransResRequestFormnodeFeeHtml( $request ) {

        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $formNodeUtil = $this->container->get('user_formnode_utility');

        $completedEntities = $transResFormNodeUtil->getProjectFormNodeFieldByName(
            $request,
            "Completed #",
            "HemePath Translational Research Request",
            "Request",
            "Product or Service",
            null,
            true
        );
        //echo "completedEntities=".count($completedEntities)."<br>";
//        $formNodeValues = $completedEntities['formNodeValue'];
//        foreach($formNodeValues as $resArr) {
//            $formNodeValue = $resArr['formNodeValue'];
//            echo "formNodeValue=".$formNodeValue."<br>";
//            $arraySectionIndex = $resArr['arraySectionIndex'];
//            echo "arraySectionIndex=" . $arraySectionIndex . "<br>";
//        }
//        return 1;

        $requestedEntities = $transResFormNodeUtil->getProjectFormNodeFieldByName(
            $request,
            "Requested #",
            "HemePath Translational Research Request",
            "Request",
            "Product or Service",
            null,
            true
        );
        //echo "requestedEntities=".count($requestedEntities)."<br>";

        $requestCategoryTypeComplexResults = $this->getMultipleProjectFormNodeFieldByName(
            $request,
            "Category Type",
            "HemePath Translational Research Request",
            "Request",
            "Product or Service"
        );
        //echo "requestCategoryTypeComplexResults=".count($requestCategoryTypeComplexResults)."<br>";
//        $res = array(
//            'formNodeValue' => $formNodeValue,
//            'formNodeId' => $formNode->getId(),
//            'arraySectionId' => $result->getArraySectionId(),
//            'arraySectionIndex' => $result->getArraySectionIndex(),
//        );
//        $resArr[] = $res;

        $subTotal = 0;

        //2) group by arraySectionIndex
        foreach($requestCategoryTypeComplexResults as $complexRes) {

            $arraySectionIndex = $complexRes['arraySectionIndex'];
            //echo "arraySectionIndex=".$arraySectionIndex."<br>";
            $dropdownObject = $complexRes['dropdownObject'];

            $requested = $this->findByArraySectionIndex($requestedEntities,$arraySectionIndex);
            //echo "requested=".$requested."<br>";
            $completed = $this->findByArraySectionIndex($completedEntities,$arraySectionIndex);
            //echo "completed=".$completed."<br>";
            //echo "###<br>";

            $fee = $dropdownObject->getFee();

            if( $fee ) {
                $subTotal = $subTotal + intval($completed) * intval($fee);
                //return $subTotal;
            }
        }

        return $subTotal;
    }
    public function findByArraySectionIndex($entities, $arraySectionIndex) {
//        foreach($entities as $entity) {
////            if( $entity->getArraySectionIndex() == $arraySectionIndex ) {
////                return $entity;
////            }
//        }
        $formNodeValues = $entities['formNodeValue'];
        if( !is_array($formNodeValues) ) {
            return null;
        }
        foreach($formNodeValues as $resArr) {
            $formNodeValue = $resArr['formNodeValue'];
            //echo "formNodeValue=".$formNodeValue."<br>";
            $thisArraySectionIndex = $resArr['arraySectionIndex'];
            //echo "arraySectionIndex=" . $arraySectionIndex . "<br>";
            if( $thisArraySectionIndex == $arraySectionIndex ) {
                return $formNodeValue;
            }
        }
        return null;
    }
    public function getMultipleProjectFormNodeFieldByName(
        $entity,
        $fieldName,
        $parentNameStr = "HemePath Translational Research",
        $formNameStr = "HemePath Translational Research Project",
        $entityFormNodeSectionStr = "Project"
    )
    {
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $formNodeUtil = $this->container->get('user_formnode_utility');

        $value = null;
        $receivingEntity = null;

        //1) get FormNode by fieldName
        //echo "getting formnode <br>";
        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents($fieldName, $parentNameStr, $formNameStr, $entityFormNodeSectionStr);

        //2) get field for this particular project
        $class = new \ReflectionClass($entity);
        $className = $class->getShortName();
        $classNamespace = $class->getNamespaceName();
        $entityMapper = array(
            'entityNamespace' => $classNamespace,   //"Oleg\\TranslationalResearchBundle\\Entity",
            'entityName' => $className, //"Project",
            'entityId' => $entity->getId(),
        );

        $results = $formNodeUtil->getFormNodeValueByFormnodeAndReceivingmapper($fieldFormNode,$entityMapper,true);

        $resArr = array();
        foreach( $results as $result ) {
            $arraySectionIndex = $result->getArraySectionIndex();
            //echo "result ID= ".$result->getId()." <br>";
            //$formNodeValue = $formNodeUtil->processFormNodeValue($fieldFormNode,$result,$result->getValue(),true);
            //echo "formNodeValue= $formNodeValue <br>";
            //$dropdownObject = $formNodeUtil->getReceivingObject($fieldFormNode,$result->getId());
            //echo "dropdownObject ID= ".$dropdownObject->getId()." <br>";
            $dropdownObject = $this->em->getRepository('OlegTranslationalResearchBundle:RequestCategoryTypeList')->find($result->getValue());
            //echo "category=".$dropdownObject."<br>";
            $thisRes = array(
                'arraySectionIndex'=>$arraySectionIndex,
                'dropdownObject'=>$dropdownObject
            );
            $resArr[] = $thisRes;
        }

        return $resArr;
    }
    public function getSingleTransResRequestFeeHtml_OLD( $request ) {

        $transResFormNodeUtil = $this->container->get('transres_formnode_util');

        $completed = $transResFormNodeUtil->getProjectFormNodeFieldByName(
            $request,
            "Completed #",
            "HemePath Translational Research",
            "HemePath Translational Research Request",
            "Request",
            false
        );

        //
        $completed = str_replace(" ","",$completed);

        if( !$completed ) {
            $completed = $transResFormNodeUtil->getProjectFormNodeFieldByName(
                $request,
                "Requested #",
                "HemePath Translational Research",
                "HemePath Translational Research Request",
                "Request",
                false
            );
        }

        $completed = str_replace(" ","",$completed);
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
            'draft',
            'active',
            'canceled',
            'investigator',
            'histo',
            'ihc',
            'mol',
            'retrieval',
            'payment',
            'slidescanning',
            'block',
            'suspended',
            'other',
            'completed'
        );

        $stateChoiceArr = array();

        foreach($stateArr as $state) {
            //$label = $state;
            $label = $this->getProgressStateLabelByName($state);
            //$label = $label . " (" . $state . ")";
            $stateChoiceArr[$label] = $state;
        }

        return $stateChoiceArr;
    }


    public function getBillingStateArr() {
        $stateArr = array(
            'draft',
            'active',
            'approvedInvoicing',
            'canceled',
            'missinginfo',
            'invoiced',
            'paid',
            'refunded',
            'partiallyRefunded',
        );

        $stateChoiceArr = array();

        foreach($stateArr as $state) {
            //$label = $state;
            $label = $this->getBillingStateLabelByName($state);
            //$label = $label . " (" . $state . ")";
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
            case "canceled":
                $state = "Canceled";
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
            case "suspended":
                $state = "Suspended";
                break;
            case "other":
                $state = "Other";
                break;
            case "completed":
                $state = "Completed";
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
            case "approvedInvoicing":
                $state = "Approved/Ready for Invoicing";
                break;
            case "canceled":
                $state = "Canceled";
                break;
            case "missinginfo":
                $state = "Pending additional information from submitter";
                break;
            case "invoiced":
                $state = "Invoiced";
                break;
            case "paid":
                $state = "Paid";
                break;
            case "refunded":
                $state = "Refunded";
                break;
            case "partiallyRefunded":
                $state = "Partially Refunded";
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
    public function getRequestLabelByStateMachineType( $transresRequest, $statMachineType ) {
        if( $statMachineType == 'progress' ) {
            return $this->getRequestStateLabelByName($transresRequest->getProgressState(),$statMachineType);
        }
        if( $statMachineType == 'billing' ) {
            return $this->getRequestStateLabelByName($transresRequest->getBillingState(),$statMachineType);
        }
        return "<Unknown State for ".$transresRequest.">";
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
//        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents(
//            "Category Type",
//            "HemePath Translational Research",
//            "HemePath Translational Research Request",
//            "Request" //Product or Service
//        );
        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents(
            "Category Type",
            "HemePath Translational Research Request",
            "Product or Service"
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
//        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents(
//            "Comment",
//            "HemePath Translational Research",
//            "HemePath Translational Research Request",
//            "Request"
//        );
        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents(
            "Comment",
            "HemePath Translational Research Request",
            "Product or Service"
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

    //Used by twig Request's review to check if message ...Please review this request...
    //For now it is only isAdminOrPrimaryReviewer
    public function isRequestStateReviewer($transresRequest,$statMachineType) {
        if( $statMachineType == 'progress' ) {
            return $this->isRequestProgressReviewer($transresRequest);
        }
        if( $statMachineType == 'billing' ) {
            return $this->isRequestBillingReviewer($transresRequest);
        }
        return false;
    }
    //For now it is only isAdminOrPrimaryReviewer
    public function isRequestProgressReviewer($transresRequest) {
        return $this->isRequestReviewer($transresRequest);
    }
    public function isRequestBillingReviewer($transresRequest) {
        return $this->isRequestReviewer($transresRequest);
    }
    //Request can be reviewd only by isAdminOrPrimaryReviewer
    public function isRequestReviewer($transresRequest) {
        $transresUtil = $this->container->get('transres_util');
        $project = $transresRequest->getProject();

        if( $transresUtil->isAdminOrPrimaryReviewer() ) {
            return true;
        }

//        if( $transresUtil->isProjectRequester($project) ) {
//            return true;
//        }

//        if( $this->isRequestRequester($transresRequest) ) {
//            return true;
//        }

        return false;
    }

    //return true if request's submitter or principalInvestigators
    public function isRequestRequester( $transresRequest ) {
        $user = $this->secTokenStorage->getToken()->getUser();
        //submitter
        $submitter = $transresRequest->getSubmitter();
        if( $submitter ) {
            if ($submitter->getId() == $user->getId()) {
                return true;
            }
        }

        //principalInvestigators
        $pis = $transresRequest->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi->getId() == $user->getId() ) {
                return true;
            }
        }

        return false;
    }

    public function isRequestProgressReviewable($transresRequest) {
        return $this->isRequestReviewableByRequestAndStateMachineType($transresRequest,"progress");
    }
    public function isRequestBillingReviewable($transresRequest) {
        return $this->isRequestReviewableByRequestAndStateMachineType($transresRequest,"billing");
    }
    public function isRequestReviewableByRequestAndStateMachineType( $transresRequest, $statMachineType ) {
        $workflow = $this->getWorkflowByStateMachineType($statMachineType);
        $transitions = $workflow->getEnabledTransitions($transresRequest);
        foreach( $transitions as $transition ) {
            $tos = $transition->getTos();
            if( count($tos) > 0 ) {
                return true;
            }
        }
        return false;
    }

    public function isRequestCanBeCreated( $project ) {
        $transresUtil = $this->container->get('transres_util');
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');

        //1) is_granted('ROLE_TRANSRES_REQUESTER')
        if( $this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER') === false && $transresUtil->isAdminOrPrimaryReviewer() === false ) {
            return -1;
        }

        //2) project.state == "final_approved"
        if( $project->getState() != "final_approved" ) {
            return -2;
        }

        //3) Request can not be submitted for the expired project
        if( $project->getIrbExpirationDate() ) {
            //use simple project's field
            $expDate = $project->getIrbExpirationDate();
        } else {
            //use formnode project's field if the simple field is null
            $expirationDate = $transResFormNodeUtil->getProjectFormNodeFieldByName($project, "IRB Expiration Date");
            //echo "expirationDate=$expirationDate<br>";
            $expDate = date_create_from_format('m/d/Y', $expirationDate);
            //echo "exp_date=".$expDate->format("d-m-Y H:i:s")."<br>";
        }
        if( new \DateTime() > $expDate ) {
            //echo "expired<br>";
            return -3;
        }
        //echo "not expired<br>";

        return 1;
    }

    public function getReviewEnabledLinkActions( $transresRequest, $statMachineType ) {
        //exit("get review links");
        $transresUtil = $this->container->get('transres_util');
        $project = $transresRequest->getProject();
        $user = $this->secTokenStorage->getToken()->getUser();

        $links = array();

        ////////// Check permission //////////
        //Request's review can be done only by isAdminOrPrimaryReviewer
        $verified = false;
        if( $statMachineType == 'progress' ) {
            if( $transresUtil->isAdminOrPrimaryReviewer() === false && $this->isRequestProgressReviewer($transresRequest) === false ) {
                //exit("return: progress not allowed");
                return $links;
            }
            $workflow = $this->container->get('state_machine.transres_request_progress');
            $transitions = $workflow->getEnabledTransitions($transresRequest);
            $verified = true;
        }
        if( $statMachineType == 'billing' ) {
            if( $transresUtil->isAdminOrPrimaryReviewer() === false && $this->isRequestBillingReviewer($transresRequest) === false ) {
                //exit("return: billing not allowed");
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

                //exception: ok: if $to == "approvedInvoicing" and TRP Administrator
                if( $to == "approvedInvoicing" && !$this->secAuth->isGranted('ROLE_TRANSRES_ADMIN') ) {
                    continue; //skip this $to state
                }

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
            throw new \Exception('Request object does not exist');
        }

        if( !$transresRequest->getId() ) {
            throw new \Exception('Request object ID is null');
        }

        //echo "transitionName=".$transitionName."<br>";
        $user = $this->secTokenStorage->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $transresPdfUtil = $this->get('transres_pdf_generator');
        $break = "\r\n";
        $addMsg = "";

        if( $statMachineType == 'progress' ) {
            $workflow = $this->container->get('state_machine.transres_request_progress');
            $transitions = $workflow->getEnabledTransitions($transresRequest);
            $originalStateStr = $transresRequest->getProgressState();
            $setState = "setProgressState";
        }
        if( $statMachineType == 'billing' ) {
            $workflow = $this->container->get('state_machine.transres_request_billing');
            $transitions = $workflow->getEnabledTransitions($transresRequest);
            $originalStateStr = $transresRequest->getBillingState();
            $setState = "setBillingState";
        }

        if( !$to ) {
            //Get Transition and $to
            $transition = $this->getTransitionByName($transresRequest,$transitionName,$statMachineType);
            if( !$transition ) {
                throw new \Exception($statMachineType.' transition not found by name '.$transitionName);
            }
            $tos = $transition->getTos();
            if (count($tos) != 1) {
                throw new \Exception('Available to state is not a single state; count=' . $tos . ": " . implode(",", $tos));
            }
            $to = $tos[0];
        }
        //echo "to=".$to."<br>";

        //$label = $this->getTransitionLabelByName($transitionName);
        //$label = $transitionName;
        //echo "label=".$label."<br>";

        $originalStateLabel = $this->getRequestStateLabelByName($originalStateStr,$statMachineType);

        // Update the currentState on the post
        if( $workflow->can($transresRequest, $transitionName) ) {
            try {

                //$review->setDecisionByTransitionName($transitionName);
                //$review->setReviewedBy($user);

                $workflow->apply($transresRequest, $transitionName);
                //change state
                $transresRequest->$setState($to); //i.e. 'irb_review'

                //check and add reviewers for this state by role? Do it when project is created?
                //$this->addDefaultStateReviewers($project);

                //write to DB
                if( !$testing ) {
                    $this->em->flush();
                }

                $label = $this->getRequestStateLabelByName($to,$statMachineType);
                $subject = "Project ID ".$transresRequest->getProject()->getOid().": Request ID ".$transresRequest->getId()." has been sent to the status '$label' from '".$originalStateLabel."'";
                $body = $subject;
                //get request url
                $requestUrl = $this->getRequestShowUrl($transresRequest);
                $emailBody = $body . $break.$break. "Please click on the URL below to view this project:".$break.$requestUrl;

                //send confirmation email
                $this->sendRequestNotificationEmails($transresRequest,$subject,$emailBody,$testing);

                //Exception: Changing the status of request to "Approved/Ready for Invoicing" should send an email notification
                // to the users with the role of "Translational Research Billing Administrator"
                if( $to == "approvedInvoicing" ) {
                    //what if the Request has been moved to this stage multiple times?
                    if( 1 ) {
                        //Create new invoice entity and pdf
                        $invoice = $this->createNewInvoice($transresRequest, $user);
                        $invoice = $this->createSubmitNewInvoice($transresRequest, $invoice);

                        //generate Invoice PDF
                        $res = $transresPdfUtil->generateInvoicePdf($invoice,$user);
                        $filename = $res['filename'];
                        $pdf = $res['pdf'];
                        $size = $res['size'];
                        $msgPdf = "PDF has been created with filename=".$filename."; size=".$size;

                        $this->sendRequestBillingNotificationEmails($transresRequest, $invoice, $label, $originalStateLabel, $testing);

                        $addMsg = $addMsg . "<br>New Invoice ID" . $invoice->getOid() . " has been successfully created for the request ID " . $transresRequest->getOid();
                        $addMsg = $addMsg . "<br>" . $msgPdf;
                    }
                }

                //event log
                //$this->setEventLog($project,$review,$transitionName,$originalStateStr,$body,$testing);
                $eventType = "Request State Changed";
                $transresUtil->setEventLog($transresRequest,$eventType,$body,$testing);

                $this->container->get('session')->getFlashBag()->add(
                    'notice',
                    "Successful action: ".$label . $addMsg
                );
                return true;
            } catch (\LogicException $e) {

                //event log

                $this->container->get('session')->getFlashBag()->add(
                    'warning',
                    "Action failed: ".$label
                );
                return false;
            }//try
        }
    }

    public function getTransitionByName( $transresRequest, $transitionName, $statMachineType ) {
        $workflow = $this->getWorkflowByStateMachineType($statMachineType);
        $transitions = $workflow->getEnabledTransitions($transresRequest);
        foreach( $transitions as $transition ) {
            if( $transition->getName() == $transitionName ) {
                return $transition;
            }
        }
        return null;
    }

    public function getWorkflowByStateMachineType($statMachineType) {
        if( $statMachineType == 'progress' ) {
            return $this->container->get('state_machine.transres_request_progress');
        }
        if( $statMachineType == 'billing' ) {
            return $this->container->get('state_machine.transres_request_billing');
        }
        return null;
    }

    public function getRequestShowUrl($transresRequest) {
        $url = $this->container->get('router')->generate(
            'translationalresearch_request_show',
            array(
                'id' => $transresRequest->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $url;
    }

    //set transresRequest's $fundedAccountNumber to the project's formnode (i.e. $fundedAccountNumber => "If funded, please provide account number")
    public function setValueToFormNodeProject( $project, $fieldName, $value ) {
        //echo "value=$value<br>";

        //if( $fieldName != "If funded, please provide account number" ) {
            //only supported and tested for the string formnode field
            //return;
        //}

        $formNodeUtil = $this->container->get('user_formnode_utility');
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        //$transResFormNodeUtil->setProjectFormNodeFieldByName($project,$fieldName,$value);

        //1) get project's formnode
        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents(
            $fieldName
        );
        //echo "fieldFormNode=".$fieldFormNode->getId()."<br>";
        if( !$fieldFormNode ) {
            return array();
        }

        //2) get objectTypeDropdowns by:
        $mapper = array(
            "entityName" => "Project",
            "entityNamespace" => "Oleg\\TranslationalResearchBundle\\Entity",
            "entityId" => $project->getId(),
        );
        $receivingValue = null;
        $compareType = null;
        $receivingObjects = $formNodeUtil->getFormNodeListRecordsByReceivingObjectValue($fieldFormNode,$receivingValue,$mapper,$compareType);

        //echo "receivingObjects count=".count($receivingObjects)."<br>";
        //foreach($receivingObjects as $receivingObject){
        //    echo "receivingObject ID=".$receivingObject->getId()."<br>";
        //}

        if( count($receivingObjects) == 0 ) {
            throw new \Exception("receivingObjects are not found for the project ID ".$project->getId()." and fieldName=".$fieldName." => "."failed to set value".$value);
        }

        $receivingObject = $receivingObjects[0];
        $receivingObject->setValue($value);

        //echo "receivingObject ID=".$receivingObject->getId().": updated value=".$value."<br>";

        //$this->em->flush($receivingObject);

        //exit('exit setValueToFormNodeProject');
        
        return $receivingObject;
    }

    public function getRequestItems($request) {
        $user = $this->secTokenStorage->getToken()->getUser();
        $invoiceItemsArr = new ArrayCollection();
        foreach( $request->getProducts() as $product ) {
            //Invoice's quantity field is pre-populated by the Request's "Requested #"
            $invoiceItem = new InvoiceItem($user);

            $invoiceItem->setProduct($product);

            $quantity = null;
            $requested = $product->getRequested();
            if( $requested ) {
                $quantity = $requested;
            } else {
                $completed = $product->getCompleted();
                if( $completed ) {
                    $quantity = $completed;
                }
            }
            $invoiceItem->setQuantity($quantity);

            $category = $product->getCategory();

            //ItemCode
            $itemCode = $category->getProductId();
            $invoiceItem->setItemCode($itemCode);

            //Description
            $name = $category->getName();
            $invoiceItem->setDescription($name);

            //UnitPrice
            $fee = $category->getFee();
            $invoiceItem->setUnitPrice($fee);

            //Total
            $total = intval($requested) * intval($fee);
            $invoiceItem->setTotal($total);

            $invoiceItemsArr->add($invoiceItem);
        }

        return $invoiceItemsArr;
    }
    public function getRequestItemsFormNode($request) {
        $user = $this->secTokenStorage->getToken()->getUser();
        //$user = null; //testing
        $invoiceItemsArr = new ArrayCollection();

        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $formNodeUtil = $this->container->get('user_formnode_utility');

        $completedEntities = $transResFormNodeUtil->getProjectFormNodeFieldByName(
            $request,
            "Completed #",
            "HemePath Translational Research Request",
            "Request",
            "Product or Service",
            null,
            true
        );
        //echo "completedEntities=".count($completedEntities)."<br>";
//        $formNodeValues = $completedEntities['formNodeValue'];
//        foreach($formNodeValues as $resArr) {
//            $formNodeValue = $resArr['formNodeValue'];
//            echo "formNodeValue=".$formNodeValue."<br>";
//            $arraySectionIndex = $resArr['arraySectionIndex'];
//            echo "arraySectionIndex=" . $arraySectionIndex . "<br>";
//        }
//        return 1;

        $requestedEntities = $transResFormNodeUtil->getProjectFormNodeFieldByName(
            $request,
            "Requested #",
            "HemePath Translational Research Request",
            "Request",
            "Product or Service",
            null,
            true
        );
        //echo "requestedEntities=".count($requestedEntities)."<br>";

        $requestCategoryTypeComplexResults = $this->getMultipleProjectFormNodeFieldByName(
            $request,
            "Category Type",
            "HemePath Translational Research Request",
            "Request",
            "Product or Service"
        );
        //echo "requestCategoryTypeComplexResults=".count($requestCategoryTypeComplexResults)."<br>";

        //2) group by arraySectionIndex
        foreach($requestCategoryTypeComplexResults as $complexRes) {

            $arraySectionIndex = $complexRes['arraySectionIndex'];
            //echo "arraySectionIndex=".$arraySectionIndex."<br>";
            $dropdownObject = $complexRes['dropdownObject'];

            $requested = $this->findByArraySectionIndex($requestedEntities,$arraySectionIndex);
            //echo "requested=".$requested."<br>";
            $completed = $this->findByArraySectionIndex($completedEntities,$arraySectionIndex);
            //echo "completed=".$completed."<br>";
            //echo "###<br>";

            //$fee = $dropdownObject->getFee();

//            if( $fee ) {
//                $subTotal = $subTotal + intval($completed) * intval($fee);
//                //return $subTotal;
//            }

            $invoiceItem = new InvoiceItem($user);
            $invoiceItem->setQuantity($completed);

            //ItemCode
            $itemCode = $dropdownObject->getProductId();
            $invoiceItem->setItemCode($itemCode);

            //Description
            $name = $dropdownObject->getName();
            $invoiceItem->setDescription($name);

            //UnitPrice
            $fee = $dropdownObject->getFee();
            $invoiceItem->setUnitPrice($fee);

            //Total
            $total = intval($completed) * intval($fee);
            $invoiceItem->setTotal($total);

            $invoiceItemsArr->add($invoiceItem);
        }

        //$invoiceItemsArr->add(new InvoiceItem($user));
        //$invoiceItemsArr->add(new InvoiceItem($user));
        //$invoiceItemsArr->add(new InvoiceItem($user));
        return $invoiceItemsArr;
    }
    
    public function getInvoiceLogo($invoice, $transresRequest=null) {

        //Get $transresRequest if null
        if( !$transresRequest ) {
            $transresRequests = $invoice->getTransresRequests();
            //echo "count=" . count($transresRequests) . "<br>";
            if (count($transresRequests) > 0) {
                $transresRequest = $transresRequests[0];
            }
        }

        if( !$transresRequest ) {
            return $this->getDefaultStaticLogo();
        }

        //Get project's projectSpecialty
        $project = $transresRequest->getProject();
        $projectSpecialty = $project->getProjectSpecialty();
        $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();

        //find site parameters
        $siteParameter = $this->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
        if( !$siteParameter ) {
            return $this->getDefaultStaticLogo();
            //throw new \Exception("getInvoiceLogo: SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
        }

        if( $siteParameter ) {
            $logoDocuments = $siteParameter->getTransresLogos();
            if( count($logoDocuments) > 0 ) {
                $logoDocument = $logoDocuments->first(); //DESC order => the most recent first
                $docPath = $logoDocument->getAbsoluteUploadFullPath();
                //$docPath = $logoDocument->getRelativeUploadFullPath();
                //echo "docPath=" . $docPath . "<br>";
                if( $docPath ) {
                    return $docPath;
                }
            }
        }

        return $this->getDefaultStaticLogo();
    }
    public function getDefaultStaticLogo() {
        //<img src="{{ asset(bundleFileName) }}" alt="{{ title }}"/>
        $filename = "wcmc_logo.jpg";
        $bundleFileName = "bundles\\olegtranslationalresearch\\images\\".$filename;
        return $bundleFileName;
    }

    public function sendRequestNotificationEmails($transresRequest, $subject, $body, $testing=false) {
        //if( !$appliedTransition ) {
        //    return null;
        //}

        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');

        $senderEmail = null; //Admin email
        $emails = array();

        //send to the
        // 1) admins and primary reviewers
        $admins = $transresUtil->getTransResAdminEmails(); //ok
        $emails = array_merge($emails,$admins);

        // 2) a) submitter, b) principalInvestigators, c) contact
        //a submitter
        if( $transresRequest->getSubmitter() ) {
            $submitterEmail = $transresRequest->getSubmitter()->getSingleEmail();
            if( $submitterEmail ) {
                $emails = array_merge($emails,array($submitterEmail));
            }
        }

        //b principalInvestigators
        $piEmailArr = array();
        $pis = $transresRequest->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi ) {
                $piEmailArr[] = $pi->getSingleEmail();
            }
        }
        $emails = array_merge($emails,$piEmailArr);

        //c contact
        if( $transresRequest->getContact() ) {
            $contactEmail = $transresRequest->getContact()->getSingleEmail();
            if( $submitterEmail ) {
                $emails = array_merge($emails,array($contactEmail));
            }
        }

        $emails = array_unique($emails);

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $subject, $body, null, $senderEmail );

    }

    public function sendRequestBillingNotificationEmails($transresRequest,$invoice,$newStateLabel,$originalStateLabel,$testing=false) {
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $emailUtil = $this->container->get('user_mailer_utility');

        $senderEmail = null; //Admin email
        $newline = "\r\n";

        $project = $transresRequest->getProject();
        $projectTitle = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Title");
        
        $emails = array();

        //1) get ROLE_TRANSRES_BILLING_ADMIN
        $billingUsers = $this->em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole("ROLE_TRANSRES_BILLING_ADMIN");
        foreach( $billingUsers as $user ) {
            if( $user ) {
                $emails[] = $user->getSingleEmail();
            }
        }

        //2) Request's billing contact
        $billingContact = $transresRequest->getContact();
        if( $billingContact ) {
            $billingContactEmail = $billingContact->getSingleEmail();
            if( $billingContactEmail ) {
                $emails[] = $billingContactEmail;
            }
        }

        //Subject: Draft Translation Research Invoice for Request [Request ID] of Project [Project Title]
        $subject = "Draft Translation Research Invoice for Request ".$transresRequest->getOid()." of Project ".$projectTitle;

        //1) Preview Invoice PDF
        $invoicePdfViewUrl = $this->container->get('router')->generate(
            'translationalresearch_invoice_download_recent',
            array(
                'oid'=>$invoice->getOid()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $body = "Please review the draft invoice pdf for request ".$transresRequest->getOid().
            " of project ".$projectTitle.":".$invoicePdfViewUrl.$newline;

        //2) To issue the invoice to FirstNameOfSubmitter LastNameOfSubmitter (WCMC CWID: xxx) at
        // submitter'semail@some.com as it, please follow this link
        //Send the most recent Invoice PDF by Email (sendInvoicePDFByEmail)
        $sendPdfEmailUrl = $this->container->get('router')->generate(
            'translationalresearch_invoice_send_pdf_email',
            array(
                'oid'=>$invoice->getOid()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        //Get PI
        $pi = $invoice->getPrincipalInvestigator();
        if( !$pi ) {
            return "There is no PI. Email has not been sent.";
        }
        $piEmail = $pi->getSingleEmail();
        if( !$piEmail ) {
            return "There is no PI's email. Email has not been sent.";
        }

        $body = $body . $newline."To issue the invoice to ".$pi.
            " at email ".$piEmail." please follow this link:".$newline.$sendPdfEmailUrl.$newline;

        //3 To edit the invoice and generate an updated copy, please follow this link
        $editInvoiceUrl = $this->container->get('router')->generate(
            'translationalresearch_invoice_edit',
            array(
                'oid'=>$invoice->getOid()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $body = $body . $newline. "To edit the invoice and generate an updated copy, please follow this link:" .
            $editInvoiceUrl.$newline;

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $subject, $body, null, $senderEmail );
    }

    public function createNewInvoice($transresRequest,$user) {
        $userDownloadUtil = $this->container->get('user_download_utility');

        $project = $transresRequest->getProject();
        $projectSpecialty = $project->getProjectSpecialty();
        $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();

        $siteParameter = $this->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
        if( !$siteParameter ) {
            throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
        }

        $invoice = new Invoice($user);

        $invoice = $this->generateInvoiceOid($transresRequest,$invoice);

        $transresRequest->addInvoice($invoice);

        $newline = "\n";
        //$newline = "<br>";

        //pre-populate salesperson
        $transresRequestContact = $transresRequest->getContact();
        if( $transresRequestContact ) {
            $invoice->setSalesperson($transresRequestContact);
        }

        ////////////// from //////////////
        if( $siteParameter->getTransresFromHeader() ) {
            $from = $siteParameter->getTransresFromHeader();
        } else {
            $from = "";
            //$from = "Weill Cornell Medicine".$newline."Department of Pathology and".$newline."Laboratory Medicine";
            //$from = $from . $newline . "1300 York Avenue, C302/Box 69 New York, NY 10065";
        }

        if( $invoice->getSalesperson() ) {
            $sellerStr = "";

            $phone = $invoice->getSalesperson()->getSinglePhoneAndPager();
            if( isset($phone['phone']) ) {
                $from = $from . $newline . "Tel: " .$phone['phone'];
                $sellerStr = $sellerStr . " Tel: " .$phone['phone'];
            }

            $fax = $invoice->getSalesperson()->getAllFaxes();
            if( $fax ) {
                $from = $from . $newline . "Fax: " . $fax;
                $sellerStr = $sellerStr . " Fax: " . $fax;
            }

            $email = $invoice->getSalesperson()->getSingleEmail();
            if( $email ) {
                $from = $from . $newline . "Email: " . $email;
                $sellerStr = $sellerStr . " Email: " . $email;
            }
        }

        $invoice->setInvoiceFrom($from);
        ////////////// EOF from //////////////

        //footer:
        if( $siteParameter->getTransresFooter() ) {
            $footer = $siteParameter->getTransresFooter();
        } else {
            $footer = "";
            //$footer = "Make check payable & mail to: Weill Cornell Medicine, 1300 York Ave, C302/Box69, New York, NY 10065 (Attn: TPR Billing)";
        }
        $invoice->setFooter($footer);

        //footer2:
        $invoice->setFooter2($sellerStr);

//        //footer3:
//        $footer3 = "------------------ Detach and return with payment ------------------";
//        $invoice->setFooter3($footer3);

        //pre-populate dueDate +30 days
        $dueDateStr = date('Y-m-d', strtotime("+30 days"));
        $dueDate = new \DateTime($dueDateStr);
        $invoice->setDueDate($dueDate);

        //pre-populate PIs: use the first one from request
        $transreqPis = $transresRequest->getPrincipalInvestigators();
        if( count($transreqPis) > 0 ) {
            $invoice->setPrincipalInvestigator($transreqPis[0]);
        }

        //invoiceTo (text): the first PI
        $billToUser = null;
        $billToUser = $invoice->getPrincipalInvestigator();
        if( $billToUser ) {
            $userlabel = $userDownloadUtil->getLabelSingleUser($billToUser,$newline,true);
            if( $userlabel ) {
                $invoice->setInvoiceTo($userlabel);
            }
        }

        //populate invoice items corresponding to the multiple requests
        $invoiceItems = $this->getRequestItems($transresRequest);
        foreach( $invoiceItems as $invoiceItem ) {
            $invoice->addInvoiceItem($invoiceItem);
        }

        //calculate Subtotal and Total
        $total = $this->getTransResRequestFeeHtml($transresRequest);
        $invoice->setSubTotal($total);
        $invoice->setTotal($total);

        return $invoice;
    }
    public function createSubmitNewInvoice( $transresRequest, $invoice ) {
        $transresUtil = $this->container->get('transres_util');

        $invoice = $this->generateInvoiceOid($transresRequest,$invoice);

        $this->em->persist($invoice);
        $this->em->flush();

//        $msg = "New Invoice has been successfully created for the request ID ".$transresRequest->getOid();
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            $msg
//        );

        $eventType = "Invoice Created";
        $msg = "New Invoice with ID ".$invoice->getOid()." has been successfully submitted for the request ID ".$transresRequest->getOid();
        $transresUtil->setEventLog($invoice,$eventType,$msg);

        return $msg;
    }
    
    public function isInvoiceBillingContact( $invoice, $user ) {
        //ROLE_TRANSRES_BILLING_ADMIN role
        if( $this->secAuth->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
            return true;
        }
        
        //Invoice's billing contact (salesperson)
        $salesperson = $invoice->getSalesperson();
        if( $salesperson->getId() == $user->getId() ) {
            return true;
        }

        return false;
    }

    public function generateInvoiceOid( $transresRequest, $invoice ) {
        $version = 1;

        $latestVersion = $this->getLatestInvoiceVersion($transresRequest);

        if( $latestVersion ) {
            $version = intval($latestVersion) + 1;
        }

        $invoice->setVersion($version);

        foreach($transresRequest->getInvoices() as $inv){
            $inv->setLatestVersion(false);
            //$this->em->persist($inv);
        }
        $invoice->setLatestVersion(true);
            
        $invoice->generateOid($transresRequest);

        return $invoice;
    }

    //return version if exists, null if not exists
    public function getLatestInvoiceVersion( $transresRequest ) {
        //1) Find all invoices for the given $transresRequest
        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:Invoice');
        $dql = $repository->createQueryBuilder("invoice");
        $dql->select('invoice');
        $dql->leftJoin('invoice.transresRequests','transresRequests');

        $dqlParameters = array();

        $dql->andWhere("transresRequests.id = :transresRequestId");

        $dql->orderBy("invoice.version","DESC");
        $dql->setMaxResults(1);

        $dqlParameters["transresRequestId"] = $transresRequest->getId();

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $existingInvoices = $query->getResult();

        $version = 1;

        if( count($existingInvoices) > 0 ) {
            $existingInvoice = $existingInvoices[0];
            return $existingInvoice->getVersion();
        }

        return null;
    }

    public function getInvoiceComplexVersions( $limit=100 ) {
        $versions = array();
        $versions['Latest'] = 'Latest';
        $versions['Old'] = 'Old';
        for($i = 1; $i <= $limit; $i++) {
            $versions[$i] = $i;
        }

        return $versions;
    }

    public function getInvoiceFilterPresetType() {
        $filterTypes = array(
            'All Invoices',
            'My Invoices',
            'All Issued Invoices',
            'All Pending Invoices',

            "Latest Versions of All Invoices",
            "Latest Versions of Issued (Unpaid) Invoices",
            "Latest Versions of Pending (Unissued) Invoices",
            "Latest Versions of Paid Invoices",
            "Latest Versions of Partially Paid Invoices",
            "Latest Versions of Paid and Partially Paid Invoices",
            "Latest Versions of Canceled Invoices",

            "Old Versions of All Invoices",
            "Old Versions of Issued (Unpaid) Invoices",
            "Old Versions of Pending (Unissued) Invoices",
            "Old Versions of Paid Invoices",
            "Old Versions of Partially Paid Invoices",
            "Old Versions of Paid and Partially Paid Invoices",
            "Old Versions of Canceled Invoices"
        );

        return $filterTypes;
    }

    public function findCreateSiteParameterEntity($specialtyStr) {
        $em = $this->em;
        $user = $this->secTokenStorage->getToken()->getUser();

        //$entity = $em->getRepository('OlegTranslationalResearchBundle:TransResSiteParameters')->findOneByOid($specialtyStr);

        $repository = $em->getRepository('OlegTranslationalResearchBundle:TransResSiteParameters');
        $dql = $repository->createQueryBuilder("siteParameter");
        $dql->select('siteParameter');
        $dql->leftJoin('siteParameter.projectSpecialty','projectSpecialty');

        $dqlParameters = array();

        $dql->where("projectSpecialty.abbreviation = :specialtyStr");

        $dqlParameters["specialtyStr"] = $specialtyStr;

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
        $specialty = $em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation($specialtyStr);
        if( !$specialty ) {
            throw new \Exception("SpecialtyList is not found by specialty abbreviation '" . $specialtyStr . "'");
        } else {
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

        return null;
    }

    //send by email to recipient (principalInvestigator)
    public function sendInvoicePDFByEmail($invoice) {

        $emailUtil = $this->container->get('user_mailer_utility');
        $transresUtil = $this->container->get('transres_util');

        $msg = "";
        $transresRequest = null;
        $siteParameter = null;
        $attachmentPath = null;
        $ccs = null;

        $pi = $invoice->getPrincipalInvestigator();

        if( !$pi ) {
            return "There is no PI. Email has not been sent.";
        }

        $piEmail = $pi->getSingleEmail();
        if( !$piEmail ) {
            return "There is no PI's email. Email has not been sent.";
        }

        //Attachment: Invoice PDF
        $invoicePDF = $invoice->getRecentPDF();
        if( $invoicePDF ) {
            $attachmentPath = $invoicePDF->getAbsoluteUploadFullPath();
        }


        $salesperson = $invoice->getSalesperson();
        if( $salesperson ) {
            $salespersonEmail = $salesperson->getSingleEmail();
            if( $salespersonEmail ) {
                $ccs = $salespersonEmail;
            }
        }

        if( !$ccs ) {
            $submitter = $invoice->getSubmitter();
            if( $submitter ) {
                $submitterEmail = $submitter->getSingleEmail();
                if( $submitterEmail ) {
                    $ccs = $submitterEmail;
                }
            }
        }

        //find default site parameters
        $transresRequests = $invoice->getTransresRequests();
        //echo "count=" . count($transresRequests) . "<br>";
        if (count($transresRequests) > 0) {
            $transresRequest = $transresRequests[0];

            $project = $transresRequest->getProject();
            $projectSpecialty = $project->getProjectSpecialty();
            $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();

            $siteParameter = $this->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
            if( !$siteParameter ) {
                throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
            }
        }

        if( $siteParameter ) {
            $emailBody = $siteParameter->getTransresNotificationEmail();
        } else {
            $emailBody = "Please find the attached invoice in PDF.";
        }

        //Change Invoice status to Unpaid/Issued
        $invoice->setStatus("Unpaid/Issued");
        $this->em->persist($invoice);
        $this->em->flush($invoice);

        //send by email
        $subject = "Invoice for the Request ID ".$transresRequest->getOid();

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $piEmail, $subject, $emailBody, $ccs, $ccs, $attachmentPath );

        //event log
        $eventType = "Invoice PDF sent";
        $transresUtil->setEventLog($transresRequest,$eventType,$emailBody);

        $msg = $subject . " has been sent by email to " . $piEmail . " with CC to " . $ccs;

        return $msg;
    }
}