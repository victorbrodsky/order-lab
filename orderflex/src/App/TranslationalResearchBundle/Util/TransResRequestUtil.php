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





use App\TranslationalResearchBundle\Entity\Product;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use App\TranslationalResearchBundle\Entity\Invoice;
use App\TranslationalResearchBundle\Entity\InvoiceItem;
use App\TranslationalResearchBundle\Entity\TransResSiteParameters;
use Symfony\Component\Intl\NumberFormatter\NumberFormatter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//use Box\Spout\Common\Type;
//use Box\Spout\Writer\Style\Border;
//use Box\Spout\Writer\Style\BorderBuilder;
//use Box\Spout\Writer\Style\Color;
//use Box\Spout\Writer\Style\StyleBuilder;
//use Box\Spout\Writer\WriterFactory;

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
class TransResRequestUtil
{

    protected $container;
    protected $em;
    protected $secTokenStorage;
    protected $secAuth;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }


    public function getTransResRequestTotalFeeHtml( $project ) {

        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:TransResRequest');
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
            $subTotal = $this->getTransResRequestSubTotal($request);
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

//    //Unbilled work request total amount
//    //Used to calculate fee on request list and dashboard (used to be getTransResRequestFeeHtml)
//    public function getTransResRequestSubTotal_ORIG( $request ) {
//        $subTotal = 0;
//
//        $priceList = $request->getPriceList();
//
//        foreach($request->getProducts() as $product) {
//            $quantitiesArr = $product->calculateQuantities($priceList);
//            $initialQuantity = $quantitiesArr['initialQuantity'];
//            $additionalQuantity = $quantitiesArr['additionalQuantity'];
//            $initialFee = $quantitiesArr['initialFee'];
//            $additionalFee = $quantitiesArr['additionalFee'];
//
//            //echo "units=$units; fee=$fee <br>";
//            if( $initialFee && $initialQuantity ) {
//                //$subTotal = $subTotal + ($units * intval($fee));
//                //$subTotal = $subTotal + $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$initialQuantity,$units);
//                $subTotal = $subTotal + $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
//            }
//        }
//
//        return $subTotal;
//    }
    //Unbilled work request total amount
    //Used to calculate fee on request list and dashboard (used to be getTransResRequestFeeHtml)
    public function getTransResRequestSubTotal( $request ) {
        return $request->getTransResRequestSubTotal();
    }

    //Used to generate spreadsheet
    public function getTransResRequestProductInfoArr( $request ) {
        $subTotal = 0;
        $totalProducts = 0;
        $productInfoArr = array();

        $priceList = $request->getPriceList();

        foreach($request->getProducts() as $product) {
            $requested = $product->getRequested();
            $completed = $product->getQuantity(); //getCompleted();

            $categoryStr = NULL;
            $category = $product->getCategory();
            if( $category ) {
                $categoryStr = $category->getShortInfo($request);
            }

            $comment = $product->getComment();
            $note = $product->getNote();

            $quantitiesArr = $product->calculateQuantities($priceList);
            $initialQuantity = $quantitiesArr['initialQuantity'];
            $additionalQuantity = $quantitiesArr['additionalQuantity'];
            $initialFee = $quantitiesArr['initialFee'];
            $additionalFee = $quantitiesArr['additionalFee'];

            //echo "units=$units; fee=$fee <br>";
            if( $initialFee && $initialQuantity ) {
                //$subTotal = $subTotal + ($units * intval($fee));
                //$subTotal = $subTotal + $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$initialQuantity,$units);
                //$subTotal = $subTotal + $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
                $subTotal = $subTotal + $request->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
            }

            $productInfoArr[] = array(
                'productRequested' => $requested,
                'productCompleted' => $completed,
                'productCategory' => $categoryStr,
                'productComment' => $comment,
                'productNote' => $note
            );

            $totalProducts++;
        }

        $res = array(
            'totalProducts' => $totalProducts,
            'totalFee' => $subTotal,
            'productInfoArr' => $productInfoArr
        );

        return $res;
    }

    public function getFilterPendingRequestArr($title=null) {
        $res = array(
            'filter[progressState][0]' => "active",
            'filter[progressState][1]' => "pendingInvestigatorInput",
            'filter[progressState][2]' => "pendingHistology",
            'filter[progressState][3]' => "pendingImmunohistochemistry",
            'filter[progressState][4]' => "pendingMolecular",
            'filter[progressState][5]' => "pendingCaseRetrieval",
            'filter[progressState][6]' => "pendingTissueMicroArray",
            'filter[progressState][7]' => "pendingSlideScanning"
        );

        if( $title ) {
            $res['title'] = $title;
        }

        return $res;
    }

    public function getProgressStateArr() {
//        $stateArr = array(
//            'draft',
//            'active',
//            'canceled',
//            'investigator',
//            'histo',
//            'ihc',
//            'mol',
//            'retrieval',
//            'payment',
//            'slidescanning',
//            'block',
//            'suspended',
//            'other',
//            'completed',
//            'completedNotified'
//        );
        $stateArr = array(
            'draft',
            'active',
            'canceled',
            'completed',
            'completedNotified',
            'pendingInvestigatorInput',
            'pendingHistology',
            'pendingImmunohistochemistry',
            'pendingMolecular',
            'pendingCaseRetrieval',
            'pendingTissueMicroArray',
            'pendingSlideScanning'
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
            'partiallyPaid',
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


    public function getProgressStateLabelByName( $stateName, $asButtonLabel=false ) {
        if( $asButtonLabel ) {
            $singleQuote = "&#39;";
        } else {
            $singleQuote = "'";
        }
        $buttonLabel = null;
        switch ($stateName) {
            
            //6 cases
            case "draft":
                $state = "Draft";
                break;
            case "active":
                $state = "Active";
                $buttonLabel = "Revert to ".$singleQuote."Active".$singleQuote;
                break;
            case "canceled":
                $state = "Canceled";
                $buttonLabel = "Cancel";
                break;
            case "completed":
                $state = "Completed";
                break;
            case "completedNotified":
                $state = "Completed and Notified";
                break;

            //7 cases
            case "pendingInvestigatorInput":
                $state = "Pending Investigator".$singleQuote."s Input";
                break;
            case "pendingHistology":
                $state = "Pending Histology";
                break;
            case "pendingImmunohistochemistry":
                $state = "Pending Immunohistochemistry";
                break;
            case "pendingMolecular":
                $state = "Pending Molecular";
                break;
            case "pendingCaseRetrieval":
                $state = "Pending Case Retrieval";
                break;
            case "pendingTissueMicroArray":
                $state = "Pending Tissue MicroArray";
                break;
            case "pendingSlideScanning":
                $state = "Pending Slide Scanning";
                break;

            default:
                if( $stateName ) {
                    $state = "<$stateName>";
                    $buttonLabel = "<$stateName>";
                } else {
                    $state = null;
                    $buttonLabel = "Undefined State";
                }
        }

        if( !$buttonLabel ) {
            $buttonLabel = $state;
        }

        if( $asButtonLabel ) {
            return $buttonLabel;
        }

        return $state;
    }
    public function getBillingStateLabelByName( $stateName, $asButtonLabel=false ) {
        if( $asButtonLabel ) {
            $singleQuote = "&#39;";
        } else {
            $singleQuote = "'";
        }
        $buttonLabel = null;
        switch ($stateName) {
            case "draft":
                $state = "Draft";
                break;
            case "active":
                $state = "Active";
                $buttonLabel = "Revert to ".$singleQuote."Active".$singleQuote;
                break;
            case "approvedInvoicing":
                $state = "Approved/Ready for Invoicing";
                break;
            case "canceled":
                $state = "Canceled";
                $buttonLabel = "Cancel";
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
            case "partiallyPaid":
                $state = "Partially Paid";
                break;
            case "refunded":
                $state = "Refunded Fully";
                break;
            case "partiallyRefunded":
                $state = "Refunded Partially";
                break;

            default:
                if( $stateName ) {
                    $state = "<$stateName>";
                } else {
                    $state = null;
                }
        }

        if( !$buttonLabel ) {
            $buttonLabel = $state;
        }

        if( $asButtonLabel ) {
            return $buttonLabel;
        }

        return $state;
    }
    public function getRequestStateLabelByName( $stateName, $statMachineType, $asButtonLabel=false ) {
        if( $statMachineType == 'progress' ) {
            return $this->getProgressStateLabelByName($stateName,$asButtonLabel);
        }
        if( $statMachineType == 'billing' ) {
            return $this->getBillingStateLabelByName($stateName,$asButtonLabel);
        }
        return "<".$stateName.">";
    }
    public function getRequestLabelByStateMachineType( $transresRequest, $statMachineType) {
        if( $statMachineType == 'progress' ) {
            return $this->getRequestStateLabelByName($transresRequest->getProgressState(),$statMachineType);
        }
        if( $statMachineType == 'billing' ) {
            return $this->getRequestStateLabelByName($transresRequest->getBillingState(),$statMachineType);
        }
        return "<Unknown State for ".$transresRequest.">";
    }

    public function getRequestReviewStrByStateMachineType( $transresRequest, $statMachineType) {
        $stateLabel = $this->getRequestLabelByStateMachineType($transresRequest,$statMachineType);
        if( $statMachineType == 'progress' ) {
            $str = 'The completion progress status of this work request is "'.$stateLabel.'".'.
                " Please review this work request, enter a comment (if any), and press the button to change the completion progress status";
        }
        if( $statMachineType == 'billing' ) {
            $str = 'The billing progress status of this work request is "'.$stateLabel.'".'.
            " Please review this work request, enter a comment (if any), and press the button to change the billing progress status";
        }
        return $str;
    }
    
    public function getHtmlClassTransition( $transitionName ) {
        //echo "transitionName=$transitionName<br>"; //canceled_active
        //return "btn btn-success transres-review-submit";

        if( strpos($transitionName, "_cancel") !== false ) {
            return "btn btn-danger transres-review-submit"; //btn-primary
        }
        if( strpos($transitionName, "_pending") !== false ) {
            return "btn btn-warning transres-review-submit";
        }
        if( strpos($transitionName, "_completed") !== false ) {
            return "btn btn-success transres-review-submit";
        }

        return "btn btn-primary transres-review-submit";
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
        // value=$categoryType->getId(), entityNamespace="App\TranslationalResearchBundle\Entity" , entityName="TransResRequest"
        $mapper = array(
            "entityName" => "TransResRequest",
            "entityNamespace" => "App\\TranslationalResearchBundle\\Entity",
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
        // value=$categoryType->getId(), entityNamespace="App\TranslationalResearchBundle\Entity" , entityName="TransResRequest"
        $mapper = array(
            "entityName" => "TransResRequest",
            "entityNamespace" => "App\\TranslationalResearchBundle\\Entity",
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

    /////////////////// Main method to check if the current user can change the Request's State ///////////////////
    //Used by twig Request's review to check if message ...Please review this request...
    //For now it is only isAdminOrPrimaryReviewer
    public function isRequestStateReviewer($transresRequest,$statMachineType=null) {
        if( $statMachineType == 'progress' ) {
            return $this->isRequestProgressReviewer($transresRequest);
        }
        if( $statMachineType == 'billing' ) {
            return $this->isRequestBillingReviewer($transresRequest);
        }
        if( !$statMachineType ) {
            if( $this->isRequestProgressReviewer($transresRequest) ) {
                return true;
            }
            if( $this->isRequestBillingReviewer($transresRequest) ) {
                return true;
            }
        }
        return false;
    }
    //Main method to check if the current user can change the Request's Progress State
    public function isRequestProgressReviewer($transresRequest) {
        $transresUtil = $this->container->get('transres_util');

        $project = $transresRequest->getProject();
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyPostfix = $projectSpecialty->getUppercaseName();
                $specialtyPostfix = "_" . $specialtyPostfix;
            } else {
                $specialtyPostfix = null;
            }
        }

        //For now it is only isAdminOrPrimaryReviewer
        if( $transresUtil->isAdminOrPrimaryReviewer($project) ) {
            return true; //admin or primary reviewer or delegate
        }

        if( $this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN'.$specialtyPostfix) ) {
            return true;
        }

        //TODO: who can change the request's progress state?

//        $project = $transresRequest->getProject();
//        if( $transresUtil->isProjectRequester($project) ) {
//            return true;
//        }

//        if( $this->isRequestRequester($transresRequest) ) {
//            return true;
//        }

        return false;
    }
    //Main method to check if the current user can change the Request's Billing State
    public function isRequestBillingReviewer($transresRequest) {
        $transresUtil = $this->container->get('transres_util');

        $project = $transresRequest->getProject();
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyPostfix = $projectSpecialty->getUppercaseName();
                $specialtyPostfix = "_" . $specialtyPostfix;
            } else {
                $specialtyPostfix = null;
            }
        }

        //For now it is only isAdminOrPrimaryReviewer
        if( $transresUtil->isAdminOrPrimaryReviewer($project) ) {
            return true; //admin or primary reviewer or delegate
        }

        if( $this->secAuth->isGranted('ROLE_TRANSRES_BILLING_ADMIN'.$specialtyPostfix) ) {
            return true;
        }

        //TODO: who can change the request's billing state?

//        $project = $transresRequest->getProject();
//        if( $transresUtil->isProjectRequester($project) ) {
//            return true;
//        }

//        if( $this->isRequestRequester($transresRequest) ) {
//            return true;
//        }

        return false;
    }
    /////////////////// EOF Main method to check if the current user can change the Request's State ///////////////////

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
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');

        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyPostfix = $projectSpecialty->getUppercaseName();
                $specialtyPostfix = "_" . $specialtyPostfix;
            } else {
                $specialtyPostfix = null;
            }
        }

        //1) is_granted('ROLE_TRANSRES_REQUESTER')
        if(
            $transresUtil->isProjectRequester($project) === false &&
            $this->secAuth->isGranted('ROLE_TRANSRES_REQUESTER'.$specialtyPostfix) === false &&
            $transresUtil->isAdminOrPrimaryReviewer($project) === false
        ) {
            return -1;
        }

        //2) project.state == "final_approved"
        if( $project->getState() != "final_approved" ) {
            return -2;
        }

        //3) Request can not be submitted for the expired project
        $expDate = null;
        if( $project->getImplicitExpirationDate() ) {
            //use simple project's field
            $expDate = $project->getImplicitExpirationDate();
        } else {
            //use formnode project's field if the simple field is null
            //$expirationDate = $transResFormNodeUtil->getProjectFormNodeFieldByName($project, "IRB Expiration Date");
            //echo "expirationDate=$expirationDate<br>";
            //$expDate = date_create_from_format('m/d/Y', $expirationDate);
            //echo "exp_date=".$expDate->format("d-m-Y H:i:s")."<br>";
        }
        if( $expDate && new \DateTime() > $expDate ) {
            //echo "expired<br>";
            return -3;
        }
        //echo "not expired<br>";

        return 1;
    }

    public function getReviewEnabledLinkActions( $transresRequest, $statMachineType, $asHrefArray=true ) {
        //exit("get review links");
        $transresUtil = $this->container->get('transres_util');
        //$project = $transresRequest->getProject();
        //$user = $this->secTokenStorage->getToken()->getUser();

        $project = $transresRequest->getProject();
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyPostfix = $projectSpecialty->getUppercaseName();
                $specialtyPostfix = "_" . $specialtyPostfix;
            } else {
                $specialtyPostfix = null;
            }
        }

        $links = array();

        ////////// Check permission //////////
        //Request's review can be done only by isAdminOrPrimaryReviewer
        $verified = false;
        if( $statMachineType == 'progress' ) {
            if( $transresUtil->isAdminOrPrimaryReviewer($project) === false && $this->isRequestProgressReviewer($transresRequest) === false ) {
                //exit("return: progress not allowed");
                return $links;
            }
            $workflow = $this->container->get('state_machine.transres_request_progress');
            $transitions = $workflow->getEnabledTransitions($transresRequest);
            $verified = true;
        }
        if( $statMachineType == 'billing' ) {
            if( $transresUtil->isAdminOrPrimaryReviewer($project) === false && $this->isRequestBillingReviewer($transresRequest) === false ) {
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
                if( $to == "approvedInvoicing" && !$this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyPostfix) ) {
                    continue; //skip this $to state
                }

                //exception: Only admin can set status to "completedNotified"
                if( $to == "completedNotified" &&
                    (
                        !$this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyPostfix) &&
                        !$this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN'.$specialtyPostfix)
                    )
                ) {
                    continue; //skip this $to state
                }

                if( $to == "canceled" ) {
                    //do not show canceled for non-admin and non-technician
                    if( !$this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyPostfix) && !$this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN'.$specialtyPostfix) ) {
                        continue; //skip this $to state
                    }
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
                $label = $this->getRequestStateLabelByName($to,$statMachineType,true);

                if( $asHrefArray ) {
                    $classTransition = $this->getHtmlClassTransition($transitionName);

                    $generalDataConfirmation = "general-data-confirm='Are you sure you want to $label?'";

                    //don't show confirmation modal
//                if( strpos($transitionName, "missinginfo") !== false ) {
//                    $generalDataConfirmation = "";
//                }

                    $thisLink = "<a " .
                        //"general-data-confirm='Are you sure you want to $label?'".
                        $generalDataConfirmation .
                        "href=" . $thisUrl . " class='" . $classTransition . "'>" . $label . "</a>";

                    $links[] = $thisLink;

                } else {
                    $links[] = array('url'=>$thisUrl,'label'=>$label);
                }

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

        $project = $transresRequest->getProject();
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyPostfix = $projectSpecialty->getUppercaseName();
                $specialtyPostfix = "_" . $specialtyPostfix;
            } else {
                $specialtyPostfix = null;
            }
        }

        //echo "transitionName=".$transitionName."<br>";
        $user = $this->secTokenStorage->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $transresPdfUtil = $this->container->get('transres_pdf_generator');
        //$break = "\r\n";
        $break = "<br>";
        $addMsg = "";

        if( $statMachineType == 'progress' ) {
            $workflow = $this->container->get('state_machine.transres_request_progress');
            $originalStateStr = $transresRequest->getProgressState();
            $setState = "setProgressState";
        }
        if( $statMachineType == 'billing' ) {
            $workflow = $this->container->get('state_machine.transres_request_billing');
            $originalStateStr = $transresRequest->getBillingState();
            $setState = "setBillingState";
        }

        if( !$to ) {
            //Get Transition and $to
            $transition = $this->getTransitionByName($transresRequest,$transitionName,$statMachineType);
            if( !$transition ) {
                //throw new \Exception("Work Request ID ".$transresRequest->getOid()."(FROM Original State '".$originalStateStr."'): '".$statMachineType."' (TO) transition not found by name ".$transitionName);
                //second click on the "old" transition
                $stateLabel = $this->getRequestLabelByStateMachineType($transresRequest,$statMachineType);
                $this->container->get('session')->getFlashBag()->add(
                    'warning',
                    "It is not possible anymore to change the $statMachineType status for this work request " .
                    $transresRequest->getOid(). " with the current status '" . $stateLabel . "'"
                );
                return false;
            }
            $tos = $transition->getTos();
            if (count($tos) != 1) {
                throw new \Exception('Available to state is not a single state; count=' . $tos . ": " . implode(",", $tos));
            }
            $to = $tos[0];
            //exit("Work Request ID ".$transresRequest->getOid()."(FROM Original State '".$originalStateStr."'): '".$statMachineType."' (TO) transition found by name ".$transitionName."; TO=".$to);

        }
        //echo "to=".$to."<br>";

        //exception
        if( $to == "completedNotified" ) {
            //only Admin can change the status to completedNotified
            if( !$this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyPostfix) && !$this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN'.$specialtyPostfix) ) {
                $toLabel = $this->getRequestStateLabelByName($to,$statMachineType);
                $this->container->get('session')->getFlashBag()->add(
                    'warning',
                    "Only Admins and Technicians can change the status of the Request to " . $toLabel
                );
                return false;
            }
        }

        //exception
        if( $to == "canceled" ) {
            //only Admin can change the status to canceled
            if( !$this->secAuth->isGranted('ROLE_TRANSRES_ADMIN'.$specialtyPostfix) && !$this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN'.$specialtyPostfix) ) {
                $toLabel = $this->getRequestStateLabelByName($to,$statMachineType);
                $this->container->get('session')->getFlashBag()->add(
                    'warning',
                    "Only Admin and Technician can change the status of the Request to " . $toLabel
                );
                return false;
            }
        }

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

                //When the Work Request status is set to "Completed and Notified",
                // change the Work Request Billing Status to "Approved/Ready for Invoicing".
                if( $to == "completedNotified" ) {
                    $transresRequest->setBillingState('approvedInvoicing');
                    $transresRequest->setCompletedBy($user);
                }
                if( $to == "completed" ) {
                    //$transresRequest->setBillingState('approvedInvoicing');
                    $transresRequest->setCompletedBy($user);
                }

                //write to DB
                if( !$testing ) {
                    $this->em->flush();
                }

                //If Work Request’s Progress Status is changed
                if( $statMachineType == 'progress' ) {
                    $this->syncRequestStatus($transresRequest,$to,$testing);
                }

                $label = $this->getRequestStateLabelByName($to,$statMachineType);

                $msgInfo = "The status of the work request ".$transresRequest->getOid()." has been changed from '".$originalStateLabel."' to '".$label."'";
                $msgInfo = $msgInfo . " by " . $user;

                //Exception: Changing the status of request to "Approved/Ready for Invoicing" should send an email notification
                // to the users with the role of "Translational Research Billing Administrator"
                if( $to == "approvedInvoicing" ) {
                    //what if the Request has been moved to this stage multiple times?
                    if( 1 ) {
                        //Create new invoice entity and pdf
                        $invoice = $this->createNewInvoice($transresRequest, $user);
                        $msgInvoice = $this->createSubmitNewInvoice($transresRequest, $invoice);

                        //generate Invoice PDF
                        $res = $transresPdfUtil->generateInvoicePdf($invoice,$user);
                        $filename = $res['filename'];
                        //$pdf = $res['pdf'];
                        $size = $res['size'];
                        $msgPdf = "PDF has been created with filename=".$filename."; size=".$size;

                        $addMsg = $addMsg . "<br>New Invoice ID" . $invoice->getOid() . " has been successfully created for the request ID " . $transresRequest->getOid();
                        $addMsg = $addMsg . "<br>" . $msgPdf;

                        $emailMsg = $this->sendRequestBillingNotificationEmails($transresRequest,$invoice,$testing);

                        $addMsg = $addMsg . "<br>" . $emailMsg;
                        $msgInfo = $addMsg;
                    }
                }
                elseif ( $to == "completed" ) {
                    $emailMsg = $this->sendRequestCompletedEmails($transresRequest,$statMachineType,$label,$testing);
                    $addMsg = $addMsg . "<br>" . $emailMsg;
                    $msgInfo = $addMsg;
                }
                elseif ( $to == "completedNotified" ) {
                    $emailMsg = $this->sendRequestCompletedNotifiedEmails($transresRequest,$statMachineType,$label,$testing);
                    $addMsg = $addMsg . "<br>" . $emailMsg;
                } else {
                    //All other status change cases
                    //The status of the work request APCP28-REQ27 has been changed from 'Active' to 'Completed'
                    //$subject = "Project ID ".$transresRequest->getProject()->getOid().": Request ID ".$transresRequest->getId()." has been sent to the status '$label' from '".$originalStateLabel."'";
                    //The status of the work request APCP28-REQ27 has been changed from 'Active' to 'Completed'
                    $subject = "The status of the work request ".$transresRequest->getOid()." has been changed from '".$originalStateLabel."' to '".$label."'";
                    $subject = $subject . " by " . $user;

                    //Body: The status of the work request APCP28-REQ27 has been changed from 'Active' to 'Completed'
                    //get request url
                    $requestUrl = $this->getRequestShowUrl($transresRequest);
                    $emailBody = $subject . $break.$break. "To view this work request, please visit the link below:".$break.$requestUrl;
                    //$msgInfo = $emailBody;

                    //add the following sentence into the body of the email:
                    // This request is being processed and a notification will be sent out once it has been completed and the deliverables (if any) are ready for pick up. There are no materials ready for pick up yet.
                    $emailBody = $emailBody . $break.$break .
                        "This request is being processed and a notification will be sent out once it has been completed and the deliverables (if any) are ready for pick up. There are no materials ready for pick up yet.";

                    //send confirmation email
                    $msgInfo = $this->sendRequestNotificationEmails($transresRequest,$subject,$emailBody,$testing);
                }

                //event log
                //$this->setEventLog($project,$review,$transitionName,$originalStateStr,$body,$testing);
                $eventType = "Request State Changed";
                $transresUtil->setEventLog($transresRequest,$eventType,$msgInfo,$testing);

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
            //echo "compare [".$transition->getName()."] ?= [".$transitionName."<br>";
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

    public function getRequestShowUrl($transresRequest,$asHref=true,$title=null,$newPage=false) {
        $transresUtil = $this->container->get('transres_util');
        $router = $transresUtil->getRequestContextRouter();
        $url = $router->generate(
            'translationalresearch_request_show',
            array(
                'id' => $transresRequest->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if( !$title ) {
            $title = $url;
        }

        if( $asHref ) {
            if( $newPage ) {
                $url = '<a target="_blank" href="'.$url.'">'.$title.'</a>';
            } else {
                $url = '<a href="'.$url.'">'.$title.'</a>';
            }
        }
        
        return $url;
    }
    public function getRequestChangeProgressStateUrl($transresRequest,$asHref=true,$title=null,$newPage=false) {
        $transresUtil = $this->container->get('transres_util');
        $router = $transresUtil->getRequestContextRouter();
        $url = $router->generate(
            'translationalresearch_request_review_progress_state',
            array(
                'id' => $transresRequest->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if( !$title ) {
            $title = $url;
        }

        if( $asHref ) {
            if( $newPage ) {
                $url = '<a target="_blank" href="'.$url.'">'.$title.'</a>';
            } else {
                $url = '<a href="'.$url.'">'.$title.'</a>';
            }
        }

        return $url;
    }
    public function getRequestNewInvoiceUrl($transresRequest,$asHref=true,$title=null,$newPage=false) {
        $transresUtil = $this->container->get('transres_util');
        $router = $transresUtil->getRequestContextRouter();
        $url = $router->generate(
            'translationalresearch_invoice_new',
            array(
                'id' => $transresRequest->getId(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if( !$title ) {
            $title = $url;
        }

        if( $asHref ) {
            if( $newPage ) {
                $url = '<a target="_blank" href="'.$url.'">'.$title.'</a>';
            } else {
                $url = '<a href="'.$url.'">'.$title.'</a>';
            }
        }

        return $url;
    }

    public function getInvoiceShowUrl($invoice,$asHref=true,$title=null,$newPage=false) {
        $transresUtil = $this->container->get('transres_util');
        $router = $transresUtil->getRequestContextRouter();
        $url = $router->generate(
            'translationalresearch_invoice_show',
            array(
                'oid' => $invoice->getOid(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if( !$title ) {
            $title = $url;
        }

        if( $asHref ) {
            if( $newPage ) {
                $url = '<a target="_blank" href="'.$url.'">'.$title.'</a>';
            } else {
                $url = '<a href="'.$url.'">'.$title.'</a>';
            }
        }

        return $url;
    }
    public function getInvoiceEditUrl($invoice,$asHref=true) {
        $transresUtil = $this->container->get('transres_util');
        $router = $transresUtil->getRequestContextRouter();
        $url = $router->generate(
            'translationalresearch_invoice_edit',
            array(
                'oid' => $invoice->getOid(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if( $asHref ) {
            $url = '<a href="'.$url.'">'.$url.'</a>';
        }

        return $url;
    }

    public function getSendInvoiceByEmailUrl($invoice,$asHref=true) {
        $transresUtil = $this->container->get('transres_util');
        $router = $transresUtil->getRequestContextRouter();
        $url = $router->generate(
            'translationalresearch_invoice_send_pdf_email',
            array(
                'oid' => $invoice->getOid(),
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        if( $asHref ) {
            $url = '<a href="'.$url.'">'.$url.'</a>';
        }

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
            "entityNamespace" => "App\\TranslationalResearchBundle\\Entity",
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
            return null;
            //throw new \Exception("receivingObjects are not found for the project ID ".$project->getId()." and fieldName=".$fieldName." => "."failed to set value".$value);
        }

        $receivingObject = $receivingObjects[0];
        $receivingObject->setValue($value);

        //echo "receivingObject ID=".$receivingObject->getId().": updated value=".$value."<br>";

        //$this->em->flush($receivingObject);

        //exit('exit setValueToFormNodeProject');
        
        return $receivingObject;
    }

    //Used in create New Invoice
    public function createRequestItems($request) {
        $user = $this->secTokenStorage->getToken()->getUser();
        $invoiceItemsArr = new ArrayCollection();
        $priceList = $request->getPriceList();

        foreach( $request->getProducts() as $product ) {
            //Invoice's quantity field is pre-populated by the Request's "Requested #"
            $invoiceItem = new InvoiceItem($user);

            $invoiceItem->setProduct($product);

            $quantitiesArr = $product->calculateQuantities($priceList);
            $initialQuantity = $quantitiesArr['initialQuantity'];
            $additionalQuantity = $quantitiesArr['additionalQuantity'];
            $initialFee = $quantitiesArr['initialFee'];
            $additionalFee = $quantitiesArr['additionalFee'];
            $categoryItemCode = $quantitiesArr['categoryItemCode'];
            $categoryName = $quantitiesArr['categoryName'];

            $invoiceItem->setQuantity($initialQuantity);
            $invoiceItem->setAdditionalQuantity($additionalQuantity);
            $invoiceItem->setUnitPrice($initialFee);
            $invoiceItem->setAdditionalUnitPrice($additionalFee);
            $invoiceItem->setItemCode($categoryItemCode);
            $invoiceItem->setDescription($categoryName);

            // add/show somehow "comment" from Work Request ?

            if( $initialQuantity && $initialFee ) {
                //Total
                //$total = $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
                $total = $request->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);

                $invoiceItem->setTotal($total);
            }

            $invoiceItemsArr->add($invoiceItem);
        }

        return $invoiceItemsArr;
    }
    
    public function getDefaultFile($fieldName, $invoice, $transresRequest=null) {

        //Get $transresRequest if null
        if( !$transresRequest ) {
            $transresRequest = $invoice->getTransresRequest();
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

            $getMethod = "get".$fieldName;
            //echo "getMethod=$getMethod<br>";

            $logoDocuments = $siteParameter->$getMethod();
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
        //$bundleFileName = "bundles\\olegtranslationalresearch\\images\\".$filename;
        $bundleFileName = "orderassets\\AppTranslationalResearchBundle\\images\\".$filename;
        return $bundleFileName;
    }

    //get emails: submitter, principalInvestigators, contact
    public function getRequestEmails($transresRequest) {
        $transresUtil = $this->container->get('transres_util');

        $emails = array();

        //$project = $transresRequest->getProject();
        // 1) admins and primary reviewers
        //$admins = $transresUtil->getTransResAdminEmails($project->getProjectSpecialty(),true,true); //ok
        //$emails = array_merge($emails,$admins);

        // 2) a) submitter, b) principalInvestigators, c) contact
        //a) submitter
        if( $transresRequest->getSubmitter() ) {
            $submitterEmail = $transresRequest->getSubmitter()->getSingleEmail(false);
            if( $submitterEmail ) {
                $emails = array_merge($emails,array($submitterEmail));
            }
        }

        //the rest emails: send only in main activities (completed,canceled,completedNotified)
        $progressStatus = $transresRequest->getProgressState();
        if( $progressStatus == 'canceled' || $progressStatus == 'completed' || $progressStatus == 'completedNotified' ) {
            //b) principalInvestigators
            $piEmailArr = array();
            $pis = $transresRequest->getPrincipalInvestigators();
            foreach ($pis as $pi) {
                if ($pi) {
                    $piEmailArr[] = $pi->getSingleEmail(false);
                }
            }
            $emails = array_merge($emails, $piEmailArr);

            //c) contact
            if ($transresRequest->getContact()) {
                $contactEmail = $transresRequest->getContact()->getSingleEmail(false);
                if ($submitterEmail) {
                    $emails = array_merge($emails, array($contactEmail));
                }
            }
        }

        $emails = array_unique($emails);

        return $emails;
    }
    //get emails: admins, technicians
    public function getRequestAdminTechEmails($transresRequest,$asEmail=true) {
        $transresUtil = $this->container->get('transres_util');

        $emails = array();

        $project = $transresRequest->getProject();
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyPostfix = $projectSpecialty->getUppercaseName();
                $specialtyPostfix = "_" . $specialtyPostfix;
            } else {
                $specialtyPostfix = null;
            }
        }

        // 1) admins and primary reviewers
        $admins = $transresUtil->getTransResAdminEmails($project,$asEmail,true); //get admin and tech email
        $emails = array_merge($emails,$admins);

        // 2) Technicians
        $technicians = $this->em->getRepository('AppUserdirectoryBundle:User')->findUsersByRoles(array("ROLE_TRANSRES_TECHNICIAN".$specialtyPostfix));
        foreach( $technicians as $technician ) {
            if( $technician ) {
                if( $asEmail ) {
                    $emails[] = $technician->getSingleEmail(false);
                } else {
                    $emails[] = $technician;
                }
            }
        }

        $emails = array_unique($emails);

        return $emails;
    }
    public function getTechnicianEmails($projectSpecialty=null,$asEmail=true) {
        $transresUtil = $this->container->get('transres_util');

        $emails = array();

        if( $projectSpecialty ) {
            $specialtyPostfix = $projectSpecialty->getUppercaseName();
            $specialtyPostfix = "_" . $specialtyPostfix;
        } else {
            $specialtyPostfix = null;
        }

        //Technicians
        $technicians = $this->em->getRepository('AppUserdirectoryBundle:User')->findUsersByRoles(array("ROLE_TRANSRES_TECHNICIAN".$specialtyPostfix));
        foreach( $technicians as $technician ) {
            if( $technician ) {
                if( $asEmail ) {
                    $emails[] = $technician->getSingleEmail(false);
                } else {
                    $emails[] = $technician;
                }
            }
        }

        $emails = array_unique($emails);

        return $emails;
    }

    public function sendRequestNotificationEmails($transresRequest, $subject, $body, $testing=false) {
        //if( !$appliedTransition ) {
        //    return null;
        //}
        //$break = "\r\n";
        $break = "<br>";

        //$transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');
        $transresUtil = $this->container->get('transres_util');

        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$transresRequest->getProject());

        $emails = $this->getRequestEmails($transresRequest);

        $admins = $this->getRequestAdminTechEmails($transresRequest); //admins, technicians

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $subject, $body, $admins, $senderEmail );

        $res = "To: ".implode(", ",$emails);
        $res = $res . $break . "Css: ".implode(", ",$admins);
        $res = $res . $break . "Subject: ".$subject;
        $res = $res . $break . "Body: ".$body;

        return $res;
    }

    //Changing the status of request to "Approved/Ready for Invoicing" (approvedInvoicing) should send an email notification
    public function sendRequestBillingNotificationEmails($transresRequest,$invoice,$testing=false) {
        //$transResFormNodeUtil = $this->container->get('transres_formnode_util');
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');

        //$newline = "\r\n";
        $newline = "<br>";

        $project = $transresRequest->getProject();
        $projectTitle = $project->getTitle();
        if( !$projectTitle ) {
            //$projectTitle = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Title");
            $projectTitle = $project->getTitle();
        }

        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyPostfix = $projectSpecialty->getUppercaseName();
                $specialtyPostfix = "_" . $specialtyPostfix;
            } else {
                $specialtyPostfix = null;
            }
        }
        
//        $emails = array();
//        //0) get ROLE_TRANSRES_ADMIN
//        $adminUsers = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserByRole("ROLE_TRANSRES_ADMIN".$specialtyPostfix);
//        foreach( $adminUsers as $user ) {
//            if( $user ) {
//                $emails[] = $user->getSingleEmail(false);
//            }
//        }

        //0) get admins, technicians
        $emails = $this->getRequestAdminTechEmails($transresRequest); //admins, technicians

        //1) get ROLE_TRANSRES_BILLING_ADMIN
        $billingUsers = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserByRole("ROLE_TRANSRES_BILLING_ADMIN".$specialtyPostfix);
        foreach( $billingUsers as $user ) {
            if( $user ) {
                $emails[] = $user->getSingleEmail(false);
            }
        }

//        //2) Request's billing contact (PI side)
//        $billingContact = $transresRequest->getContact();
//        if( $billingContact ) {
//            $billingContactEmail = $billingContact->getSingleEmail(false);
//            if( $billingContactEmail ) {
//                $emails[] = $billingContactEmail;
//            }
//        }

        //Subject: Draft Translation Research Invoice for Request [Request ID] of Project [Project Title]
        //$subject = "Draft Translational Research Invoice for Request ".$transresRequest->getOid()." of Project ".$projectTitle;
        $subject = "Draft Invoice for the ".$transresUtil->getBusinessEntityName()." for Request ".$transresRequest->getOid()." of Project ".$projectTitle;

        //1) Preview Invoice PDF
        $invoicePdfViewUrl = $this->container->get('router')->generate(
            'translationalresearch_invoice_download_recent',
            array(
                'id'=>$invoice->getId()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $body = "Please review the draft invoice pdf for request ".$transresRequest->getOid().
            " of project ".$projectTitle.":".$newline.$invoicePdfViewUrl.$newline;

        //2) To issue the invoice to FirstNameOfSubmitter LastNameOfSubmitter at
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
            //return "There is no PI. Email has not been sent.";
            //throw new \Exception("There is no PI. Email has not been sent.");
            //Use submitter
            $pi = $invoice->getSubmitter();
        }
        if( !$pi ) {
            throw new \Exception("There is no PI. Email has not been sent.");
        }

        $piEmailArr = array();
        $piEmail = $pi->getSingleEmail(false);
        if( $piEmail ) {
            $piEmailArr[] = $piEmail;
        } else {
            //return "There is no PI's email. Email has not been sent.";
            throw new \Exception("There is no PI's email. Email has not been sent.");
        }
        //Invoice's Billing Contact
        $invoiceBillingContact = $invoice->getBillingContact();
        if( $invoiceBillingContact ) {
            $invoiceBillingContactEmail = $invoiceBillingContact->getSingleEmail(false);
            if( $invoiceBillingContactEmail) {
                $piEmailArr[] = $invoiceBillingContactEmail;
            }
        }

        $body = $body . $newline."To issue the invoice to ".$pi->getUsernameOptimal().
            " at email ".implode(", ",$piEmailArr)." please follow this link:".$newline.$sendPdfEmailUrl.$newline;

        //3 To edit the invoice and generate an updated copy, please follow this link
        $editInvoiceUrl = $this->container->get('router')->generate(
            'translationalresearch_invoice_edit',
            array(
                'oid'=>$invoice->getOid()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $body = $body . $newline. "To edit the invoice and generate an updated copy, please follow this link:".$newline.
            $editInvoiceUrl.$newline;

        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $subject, $body, null, $senderEmail );

        $body = "Notification Email has been sent:".$newline.$body;
        $eventType = "Email Sent";
        if( !$testing ) {
            $transresUtil->setEventLog($transresRequest, $eventType, $body, $testing);
        }

        return "Notification Email has been sent to ".implode(", ",$emails);
    }

    //email to sys admins saying "Completed with link in the body of the email to change request status to "Completed and Notified"
    public function sendRequestCompletedEmails($transresRequest,$statMachineType,$label,$testing=false) {
        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');

        //$newline = "\r\n";
        $newline = "<br>";

        //$project = $transresRequest->getProject();
        //$projectTitle = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"Title");

        $requestOid = $transresRequest->getOid();
        $emails = array();

        $project = $transresRequest->getProject();
        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyPostfix = $projectSpecialty->getUppercaseName();
                $specialtyPostfix = "_" . $specialtyPostfix;
            } else {
                $specialtyPostfix = null;
            }
        }

        //1) get ROLE_TRANSRES_BILLING_ADMIN
        //$adminUsers = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserByRole("ROLE_TRANSRES_ADMIN".$specialtyPostfix);
        $adminUsers = $this->getRequestAdminTechEmails($transresRequest,false); //admins, technicians
        foreach( $adminUsers as $user ) {
            if( $user ) {
                $emails[] = $user->getSingleEmail(false);
            }
        }

        //Add trp@med.cornell.edu to site settings and use it for Cc for Work Request status change to "Completed" and "Completed and Notified"
        $ccNotifyEmail = $transresUtil->getTransresSiteProjectParameter('notifyEmail',$project);

        //Subject: Draft Translation Research Invoice for Request [Request ID] of Project [Project Title]
        //$subject = "Request ".$transresRequest->getOid()." has been sent to ".$label;
        //Subject: Work request APCP28-REQ27 has been completed and is ready for submitter notification
        $subject = "Work request $requestOid has been completed and is ready for submitter notification";

        //The status of the work request APCP28-REQ27 has been set to 'Completed'.
        //$body = $subject . ".". $newline."Please confirm the '$label' status by clicking the following link ".
        //    "and changing the status to 'Completed and Notified'".$newline;
        $body = "The status of the work request $requestOid has been set to 'Completed'.";

        //Please review the content of the request and verify that the work has indeed been completed:
        $requestUrl = $this->getRequestShowUrl($transresRequest);
        $body = $body. $newline.$newline. "Please review the content of the work request and verify that the work has indeed been completed:";
        $body = $body . $newline . $requestUrl;

        //Once you are ready to notify the requestors of the completion status, please visit the following link and change the status to 'Completed and Notified' in order to send out the email notification:
        $body = $body . $newline.$newline . "Once you are ready to notify the requestors of the completion status, 
        please visit the following link and change the status 
        to 'Completed and Notified' in order to send out the email notification:";

        //2 get allowed transactions as array with labels, data
        //$linksArray = $this->getReviewEnabledLinkActions($transresRequest,$statMachineType,false);
        //foreach($linksArray as $link) {
        //    if( $link['label'] == "Completed and Notified" ) {
        //        $body = $body . $link['url'] . $newline;
        //    }
        //}
        //completed_completedNotified
        $completedNotifiedUrl = $this->container->get('router')->generate(
            'translationalresearch_request_transition_action_by_review',
            array(
                'transitionName'=>'completed_completedNotified',
                'id'=>$transresRequest->getId(),
                'statMachineType'=>$statMachineType
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $completedNotifiedUrl = '<a href="'.$completedNotifiedUrl.'">'.$completedNotifiedUrl.'</a>';
        $body = $body . $newline . $completedNotifiedUrl . $newline;
        //echo "body=$body";
        //exit('eof');

        //3) Send email        $emails, $subject, $message, $ccs=null, $fromEmail=null
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
        $emailUtil->sendEmail( $emails, $subject, $body, $ccNotifyEmail, $senderEmail );

        //4) set event log
        if( !$testing ) {
            $body = "Notification Email has been sent to ".implode(", ",$emails).":<br>".$body;
            //$body = str_replace($newline,"<br>",$body);
            $eventType = "Email Sent";
            $transresUtil->setEventLog($transresRequest, $eventType, $body, $testing);
        }

        return "Notification Email has been sent to ".implode(", ",$emails);
    }

    //send a second email to both Requesters and PIs
    public function sendRequestCompletedNotifiedEmails($transresRequest,$statMachineType,$label,$testing=false) {
        $transresUtil = $this->container->get('transres_util');
        $emailUtil = $this->container->get('user_mailer_utility');

        //$newline = "\r\n";
        //$newline = "<br>";
        $emails = array();

        //get Request's PI emails
        $pis = $transresRequest->getPrincipalInvestigators();
        foreach( $pis as $pi ) {
            if( $pi ) {
                $emails[] = $pi->getSingleEmail(false);
            }
        }

        //get submitter
        $submitter = $transresRequest->getSubmitter();
        if( $submitter ) {
            $emails[] = $submitter->getSingleEmail(false);
        }

        $emails = array_unique($emails);

        //find default site parameters
        if( $transresRequest ) {
            $project = $transresRequest->getProject();
            $projectSpecialty = $project->getProjectSpecialty();
            $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();

            $siteParameter = $this->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
            if( !$siteParameter ) {
                throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
            }
        }

        if( $siteParameter ) {
            $emailBody = $siteParameter->getRequestCompletedNotifiedEmail();
            $emailBody = $transresUtil->replaceTextByNamingConvention($emailBody,$project,$transresRequest,null);
        } else {
            $emailBody = "Request ".$transresRequest->getOid()." status has been changed to 'Completed and Notified'";
        }

        if( $siteParameter ) {
            $emailSubject = $siteParameter->getRequestCompletedNotifiedEmailSubject();
            $emailSubject = $transresUtil->replaceTextByNamingConvention($emailSubject,$project,$transresRequest,null);
        } else {
            $emailSubject = "Request ".$transresRequest->getOid()." status has been changed to 'Completed and Notified'";
        }

        //send ccs to admin
        $admins = $this->getRequestAdminTechEmails($transresRequest); //admins, technicians

        //Add trp@med.cornell.edu to site settings and use it for Cc for Work Request status change to "Completed" and "Completed and Notified"
        $ccNotifyEmail = $transresUtil->getTransresSiteProjectParameter('notifyEmail',$project);
        if( $ccNotifyEmail ) {
            $admins[] = $ccNotifyEmail;
            $admins = array_unique($admins);
        }

        //send by email
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $emails, $emailSubject, $emailBody, $admins, $senderEmail );

        //4) set event log
        if( !$testing ) {
            $body = "Notification Email has been sent to ".implode(", ",$emails).":<br>".$emailBody;
            //$body = str_replace($newline,"<br>",$body);
            $eventType = "Email Sent";
            $transresUtil->setEventLog($transresRequest, $eventType, $body, $testing);
        }

        return "Notification Email has been sent to ".implode(", ",$emails);
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
        $invoice->setStatus("Pending");

        $invoice = $this->generateInvoiceOid($transresRequest,$invoice);

        $transresRequest->addInvoice($invoice);

        $newline = "\n";
        //$newline = "<br>";

        //pre-populate salesperson from default salesperson
        if( $siteParameter->getInvoiceSalesperson() ) {
            $salesperson = $siteParameter->getInvoiceSalesperson();
            if( $salesperson ) {
                $invoice->setSalesperson($salesperson);
            }
        }

        //pre-populate irbNumber
        $irbNumber = $project->getIrbNumber();
        if( $irbNumber ) {
            $invoice->setIrbNumber($irbNumber);
        }

        //pre-populate fundedAccountNumber
        $transresFundedAccountNumber = $transresRequest->getFundedAccountNumber();
        if( $transresFundedAccountNumber ) {
            $invoice->setFundedAccountNumber($transresFundedAccountNumber);
        }

        ////////////// from //////////////
        if( $siteParameter->getTransresFromHeader() ) {
            $from = $siteParameter->getTransresFromHeader();
        } else {
            $from = "";
            //$from = "Weill Cornell Medicine".$newline."Department of Pathology and".$newline."Laboratory Medicine";
            //$from = $from . $newline . "1300 York Avenue, C302/Box 69 New York, NY 10065";
        }

        $sellerStr = "";
        if( $invoice->getSalesperson() ) {
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

            $email = $invoice->getSalesperson()->getSingleEmail(false);
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
        //$dueDateStr = date('Y-m-d', strtotime("+30 days"));
        //$dueDate = new \DateTime($dueDateStr);
        //$invoice->setDueDate($dueDate);
        $invoice->reSetDueDate();

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

        //Pre-set PI's Billiong Contact from Request's contact
        $transreqContact = $transresRequest->getContact();
        if( $transreqContact ) {
            $invoice->setBillingContact($transreqContact);
        }

        //populate invoice items corresponding to the multiple requests
        $invoiceItems = $this->createRequestItems($transresRequest);
        foreach( $invoiceItems as $invoiceItem ) {
            $invoice->addInvoiceItem($invoiceItem);
        }

        //calculate Subtotal and Total
        $total = $this->getTransResRequestSubTotal($transresRequest);
        $invoice->setSubTotal($total);
        $invoice->setTotal($total);
        $invoice->setDue($total);

        return $invoice;
    }
    public function createSubmitNewInvoice( $transresRequest, $invoice, $updateWorkRequest=true ) {
        $transresUtil = $this->container->get('transres_util');
        //$transresRequestUtil = $this->container->get('transres_request_util');

        $invoice = $this->generateInvoiceOid($transresRequest,$invoice);

        //testing
//        foreach( $invoice->getInvoiceItems() as $invoiceItem ) {
//            $itemCode = $invoiceItem->getItemCode();
//            echo "1 ItemCode=" . $itemCode . "<br>";
//        }
        //exit('EOF getInvoiceItems');

//        //use the values in Invoice’s Quantity fields to overwrite/update the associated Request’s "Completed #" fields
//        $this->updateRequestCompletedFieldsByInvoice($invoice);

        if( $updateWorkRequest ) {
            //update parent work request products by invoice's invoiceItems
            $this->updateWorkRequestProductsByInvoice($invoice); //createSubmitNewInvoice
        }

//        //testing
//        foreach( $invoice->getInvoiceItems() as $invoiceItem ) {
//            $itemCode = $invoiceItem->getItemCode();
//            echo "2 ItemCode=" . $itemCode . "<br>";
//        }
        //exit('EOF getInvoiceItems');

        $this->updateInvoiceStatus($invoice);

        //update subsidy for new invoice
        $subsidy = $this->updateInvoiceSubsidy($invoice);
        //exit("create new invoice: subsidy=$subsidy"); //testing

        $this->em->persist($invoice);
        $this->em->flush();

//        $msg = "New Invoice has been successfully created for the request ID ".$transresRequest->getOid();
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            $msg
//        );

//        //testing
//        foreach( $invoice->getInvoiceItems() as $invoiceItem ) {
//            $itemCode = $invoiceItem->getItemCode();
//            echo "3 ItemCode=" . $itemCode . "<br>";
//        }
        //exit('EOF createSubmitNewInvoice');

        $eventType = "Invoice Created";
        $msg = "New Invoice with ID ".$invoice->getOid()." has been successfully submitted for the request ID ".$transresRequest->getOid();
        $transresUtil->setEventLog($invoice,$eventType,$msg);

        return $msg;
    }

    //NOT USED anymore
    //Used in create new invoice (createSubmitNewInvoice) by "Create new invoice" action
    // or by changing status of work request to "invoiced"
    // or by update invoice
    //update parent work requests fields by invoice if status is not "canceled"
    public function updateRequestCompletedFieldsByInvoice($invoice) {
        $transresUtil = $this->container->get('transres_util');

        $testing = false;
        //$testing = true;

        if( strtolower($invoice->getStatus()) == strtolower("Canceled") ) {
            return null;
        }

        $transresRequest = $invoice->getTransresRequest();
        if( !$transresRequest ) {
            return null;
        }

        foreach( $invoice->getInvoiceItems() as $invoiceItem ) {
            $requestProduct = $invoiceItem->getProduct();
            if( !$requestProduct ) {
                continue;
            }

            if( $testing ) {
                $itemCode = $invoiceItem->getItemCode();
                echo "ItemCode=" . $itemCode . "<br>";
            }

            /////////// Update Quantity on Work Request ////////////
            $requestQuant = $requestProduct->getCompleted();
            $invoiceQuant = $invoiceItem->getTotalQuantity();
            if( $invoiceQuant && $requestQuant != $invoiceQuant ) {

                //eventLog changes
                $eventType = "Work Request Quantity Updated by Invoice"; //"Request Updated";

                $categoryStr = "Unknown category";
                $category = $requestProduct->getCategory();
                if( $category ) {
                    $categoryStr = $category->getOptimalAbbreviationName();
                }
                
                //$msg = "Request's (".$transresRequest->getOid(). ") completed value ".$requestQuant.
                //    " has been updated by the invoice's (".$invoice->getOid() . ") quantity value " . $invoiceQuant;
                $msg = "Quantity of $categoryStr for Work Request ".$transresRequest->getOid().
                    " changed from old value $requestQuant to new value $invoiceQuant";

                if( !$testing ) {
                    $transresUtil->setEventLog($transresRequest, $eventType, $msg);
                }

                $requestProduct->setCompleted($invoiceQuant);
            }
            /////////// EOF Update Quantity on Work Request ////////////

        }

        if( $testing ) {
            exit('EOF update RequestCompletedFieldsByInvoice');
        }

        return $transresRequest;
    }

    //Run after invoice is flushed (? Maybe not)
    //update parent work request products by invoice's invoiceItems
    public function updateWorkRequestProductsByInvoice( $invoice ) {

        //return null;

        if( strtolower($invoice->getStatus()) == strtolower("Canceled") ) {
            return null;
        }

        $transresRequest = $invoice->getTransresRequest();
        if( !$transresRequest ) {
            return null;
        }

        $transresUtil = $this->container->get('transres_util');
        $userServiceUtil = $this->container->get('user_service_utility');

        $user = $this->secTokenStorage->getToken()->getUser();

        $newline = "\n";

        $currentDate = new \DateTime();
        $currentDate = $userServiceUtil->convertFromUtcToUserTimezone($currentDate, $user);
        $currentDateStr = $currentDate->format('m/d/Y \a\t H:i:s');

//        $originalProducts = new ArrayCollection();
//        foreach ($transresRequest->getProducts() as $product) {
//            $originalProducts->add($product);
//        }

        $invoiceItemProducts = array();

        //detect adding (case 8D) or editing existing product (case E, F)
        foreach( $invoice->getInvoiceItems() as $invoiceItem ) {

            $invoiceProduct = $invoiceItem->getProduct();

            //TODO: set product to NULL if itemCode is NULL
            $itemCode = $invoiceItem->getItemCode();
            //echo "ItemCode=" . $itemCode . ", product=".$invoiceProduct."<br>";
            //continue; //testing

//            if (!$invoiceProduct) {
//                //exit("skip: no request product with item code=".$invoiceItem->getItemCode().", invoiceItemId=".$invoiceItem->getId());
//                continue;
//            }

            if (!$invoiceProduct) {
                //echo "NULL category: itemCode=".$itemCode."<br>";
                //try to find category by itemCode
                //$category = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->findOneByProductId($itemCode);
                $category = $this->getOneValidFeeScheduleByProductId($itemCode);
                //echo "found category=[".$category."] by itemCode=$itemCode"."<br>";

                $invoiceProduct = $this->createAndAddProductToInvoiceItemByItemCode($invoiceItem, $category);
            }

            if( !$invoiceProduct ) {
                //exit("skip: no request product with item code=".$invoiceItem->getItemCode().", invoiceItemId=".$invoiceItem->getId());
                continue;
            }

            $invoiceItemProducts[$invoiceProduct->getId()] = $invoiceProduct->getId();

            //////////////////// Case D: adding new invoice item with existing category ////////////////////
            //itemCode existed (Case D) => add as new Product
            //1) find product and 2) if not found => create new Product
            //for each invoice item in invoice => check if product does not exist in the work request
            //foreach invoice item: detect if this invoice item does not exists in the original work request
            $invoiceProduct = $invoiceItem->getProduct();

            //Check if $invoiceProduct exists in work request:
            //if exists => check if invoice item is modified => if ItemCode is modified => modify corresponding product
            //if does not exist => add $invoiceProduct to Work Request
            if( $this->findProductInWorkRequest($invoiceProduct,$transresRequest) ) {

                //Check if quantity is edited (replace function updateRequestCompletedFieldsByInvoice)
                /////////// Update Quantity on Work Request ////////////
                $requestQuant = $invoiceProduct->getQuantity(); //getCompleted();
                $invoiceQuant = $invoiceItem->getTotalQuantity();
                if( $invoiceQuant && $requestQuant != $invoiceQuant ) {

                    //eventLog changes
                    $eventType = "Work Request Quantity Updated by Invoice"; //"Request Updated";

                    $categoryStr = "Unknown category";
                    $category = $invoiceProduct->getCategory();
                    if( $category ) {
                        $categoryStr = $category->getOptimalAbbreviationName();
                    }

                    //$msg = "Request's (".$transresRequest->getOid(). ") completed value ".$requestQuant.
                    //    " has been updated by the invoice's (".$invoice->getOid() . ") quantity value " . $invoiceQuant;
                    $msg = "Quantity of $categoryStr for Work Request ".$transresRequest->getOid().
                        " changed from old value $requestQuant to new value $invoiceQuant";

                    $transresUtil->setEventLog($transresRequest, $eventType, $msg);

                    $invoiceProduct->setCompleted($invoiceQuant);
                }
                /////////// EOF Update Quantity on Work Request ////////////

                //Check if Item Code is edited
                $category = $invoiceProduct->getCategory();
                if( $category ) {
                    $productId = $category->getProductId();
                }

                if( $productId."" == $itemCode."" ) {
                    //itemCode is the same
                    //If Description changed => show description in show/edit work request page
                } else {
                    //itemCode is edited => change ItemCode (it means find category by productId and change category in product)
                    $itemCode = $invoiceItem->getItemCode();
                    if ($itemCode) {
                        //$newInvoiceItemCategory = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->findOneByProductId($itemCode);
                        $newInvoiceItemCategory = $this->getOneValidFeeScheduleByProductId($itemCode);
                    } else {
                        $newInvoiceItemCategory = NULL; //"Not Existed Fee Schedule";
                    }

                    if( $newInvoiceItemCategory ) {
                        $invoiceProduct->setCategory($newInvoiceItemCategory);

                        $msg = $newline.$newline."System Note: Item code $productId updated to $itemCode by $user on $currentDateStr.";
                        $productComment = $invoiceProduct->getComment();
                        if( $productComment ) {
                            $productComment = $productComment . $msg;
                        } else {
                            $productComment = $msg;
                        }
                        if( $productComment ) {
                            $invoiceProduct->setComment($productComment);
                        }

                        //Event Log
                        $eventType = "Work Request Item Updated via Invoice";
                        $transresUtil->setEventLog($transresRequest, $eventType, $msg);
                    }

                }

            } else {
                //New product, not existing in the parent Work Request =>
                //If ItemCode has a category (fee schedule) (Case D) => add to Work Request => EventLog
                //If ItemCode does not have a category (fee schedule) (Case E) => don’t push it back to the parent work request => EventLog

                $category = $invoiceProduct->getCategory();
                $itemCode = $invoiceItem->getItemCode();
                if( !$itemCode ) {
                    $itemCode = "Empty Item Code";
                }

                if( $category ) {
                    //Case D: itemCode exists in the fee schedule => add to the parent Work Request
                    //Add to the product comment: "Item added during invoice generation by FirstName LastName on MM/DD/YYYY at HH:MM.";
                    $addToProductComment = $newline.$newline."System Note: Item added during invoice generation by $user on $currentDateStr";
                    $productComment = $invoiceProduct->getComment();
                    if( $productComment ) {
                        $productComment = $productComment . $addToProductComment;
                    } else {
                        $productComment = $addToProductComment;
                    }
                    if( $productComment ) {
                        $invoiceProduct->setComment($productComment);
                    }

                    $transresRequest->addProduct($invoiceProduct);
                    $this->em->flush();

                    $msg = "Product from Invoice ".$invoice->getOid()." with the item code '$itemCode' (existed in the fee schedule " .
                        "'" . $category->getOptimalAbbreviationName() . "') has been added to the Work Request ID " .
                        $transresRequest->getOid() . " by " . $user;
                } else {
                    //Case E: itemCode does not existed in the fee schedule => don’t push it back to the parent work request => eventLog
                    $msg = "New invoice item with the item code '$itemCode' (not existed in the fee schedule) has been added in the latest invoice " .
                        $invoice->getOid() . " for the Work Request ID " . $transresRequest->getOid() . " by " . $user;
                }

                //eventLog changes
                $eventType = "Work Request Item Updated via Invoice"; //"Work Request Quantity Updated by Invoice"; //"Request Updated";

                $transresUtil->setEventLog($transresRequest, $eventType, $msg);
            }


            if(0) {
                //if( $this->findProductInWorkRequestAndInvoice($invoiceProduct,$transresRequest,$invoice) === NULL ) {
                if ($this->findProductInWorkRequestAndInvoiceItem($invoiceProduct, $transresRequest, $invoiceItem) === FALSE) {

                    //get $category by item code
                    $category = NULL;
                    $itemCode = $invoiceItem->getItemCode();
                    if ($itemCode) {
                        //$category = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->findOneByProductId($itemCode);
                        $category = $this->getOneValidFeeScheduleByProductId($itemCode);
                    } else {
                        $itemCode = "Empty Item Code";
                    }

                    if ($category) {
                        echo "Adding new found by $itemCode: category=" . $category . "<br>";
                        //exit(111);

                        $newProduct = new Product($user);
                        $newProduct->setCategory($category);

                        $initialQuantity = $invoiceItem->getQuantity();                 //initial Quantity
                        $additionalQuantity = $invoiceItem->getAdditionalQuantity();    //additional Quantity
                        if ($initialQuantity === NULL) {
                            $initialQuantity = 0;
                        }
                        if ($additionalQuantity === NULL) {
                            $additionalQuantity = 0;
                        }

                        $totalQuantity = $initialQuantity + $additionalQuantity;

                        $newProduct->setRequested($totalQuantity);
                        $newProduct->setCompleted($totalQuantity);

//                  $description = $invoiceItem->getDescription();
//                  if( $description ) {
//                      //$newProduct->setNote($description);
//                      $description = "This product has been added by invoice ID ".$invoice->getOid()." with the description: ".$description;
//                      $newProduct->setComment($description);
//                  }

                        $transresRequest->addProduct($newProduct);
                        $invoiceItem->setProduct($newProduct);

                        $this->em->persist($newProduct);
                        $this->em->flush();

                        $msg = "New product with the item code '$itemCode' with existing fee schedule " .
                            "'" . $category->getOptimalAbbreviationName() . "' has been added to the Work Request ID " .
                            $transresRequest->getOid() . " via Invoice by " . $user;
                    } else {
                        //itemCode not existed (Case E) => info
                        $msg = "New invoice item with the item code '$itemCode' without existing fee schedule has been added in the latest invoice " .
                            $invoice->getOid() . " for the Work Request ID " . $transresRequest->getOid() . " by " . $user;
                    }

                    //eventLog changes
                    $eventType = "Work Request Item Updated via Invoice"; //"Work Request Quantity Updated by Invoice"; //"Request Updated";

                    $transresUtil->setEventLog($transresRequest, $eventType, $msg);

                } else {
                    echo "Product with ID=" . $invoiceProduct->getId() . " already exists in work request. InvoiceID=" . $invoice->getId() . ", workRequestId=" . $transresRequest->getId() . ", invoiceItemId=" . $invoiceItem->getId() . "<br>";
                }
            }//if(0)
            //////////////////// EOF Case D: adding new invoice item with existing category ////////////////////

            //Case F: edited invoice item: itemCode changed => EventLog

            //Case F2: edited invoice item: itemCode the same => info
            //to make it easier, just always show a locked/uneditable “Invoiced Description” field (plus quantities and prices, cases A, B)
            //for each item on the Work Request View and Edit pages, pulled from the lates invoice

            //Missing case in 8:
            //if the invoice item is removed from the invoice and
            // then a new invoice item is added with the same values (item code, description, quantities and prices).
            // In this case, I will identify the matching product from the parent work request
            // and do not add this invoice item to the work request again to avoid duplication.

            //Missing case in 8:
            //All fields in the existing invoice item are changed

        } //foreach invoiceItem in invoice
        //exit("foreach invoiceItem in invoice");

        //detect removal (228: 8, Case C)
        foreach( $transresRequest->getProducts() as $product ) {
            if( !isset($invoiceItemProducts[$product->getId()]) ) {
                //product does not exists anymore in the invoice via $invoiceItemProducts
                $product->setNotInInvoice(true);
            }
        }
        
        return $transresRequest;
    }

    //NOT USED
    public function processProductFromInvoiceItem($invoice) {
        foreach( $invoice->getInvoiceItems() as $invoiceItem ) {
            //echo "process $invoiceItem <br>";
            $removeProduct = false;
            $invoiceProduct = $invoiceItem->getProduct();
            if( $invoiceProduct ) {
                $itemCode = $invoiceItem->getItemCode();
                //set product to NULL if itemCode does not have fee schedule
                if ($itemCode) {
                    //$newInvoiceItemCategory = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->findOneByProductId($itemCode);
                    $newInvoiceItemCategory = $this->getOneValidFeeScheduleByProductId($itemCode);
                    //echo "itemCode=".$itemCode." => category=".$newInvoiceItemCategory."<br>";
                    if ($newInvoiceItemCategory) {
                        //fee schedule exists:
                        $removeProduct = false;
                    } else {
                        $removeProduct = true;
                    }
                } else {
                    $removeProduct = true;
                }
            }
            if ($removeProduct) {
                //echo "remove product $removeProduct <br>";
                $invoiceProduct = NULL; //"Not Existed Fee Schedule";
                $invoiceItem->setProduct(NULL);
                $this->em->persist($invoiceItem);
                $this->em->persist($invoiceProduct);
                $this->em->remove($invoiceProduct);
            }
        }//foreach
        //exit('processProductFromInvoiceItem');
    }
    
    public function updateInvoiceStatus($invoice) {
        $paid = $invoice->getPaid();

        //Change invoice status to "Partially Paid" if the paid amount is not equal to total amount.
        if( $paid && $paid != $invoice->getTotal() ) {
            $invoice->setStatus("Paid Partially");
        }

        if( $paid && $paid == $invoice->getTotal() ) {
            $invoice->setStatus("Paid in Full");
        }

        return $invoice;
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
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Invoice');
        $dql = $repository->createQueryBuilder("invoice");
        $dql->select('invoice');
        $dql->leftJoin('invoice.transresRequest','transresRequest');

        $dqlParameters = array();

        $dql->andWhere("transresRequest.id = :transresRequestId");

        $dql->orderBy("invoice.version","DESC"); //$dql->orderBy("invoice.id","DESC");
        $dql->setMaxResults(1);

        $dqlParameters["transresRequestId"] = $transresRequest->getId();

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $existingInvoices = $query->getResult();

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

        $transresUtil = $this->container->get('transres_util');
        //$filterTypes = array();

        if(
            $transresUtil->isAdminOrPrimaryReviewer() ||
            $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE_HEMATOPATHOLOGY') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE_APCP') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE_COVID19') ||
            $this->secAuth->isGranted('ROLE_TRANSRES_EXECUTIVE_MISI')
        ) {
            //Show all invoices filter
            $filterTypes = array(
                //'My Invoices (I am Submitter, Salesperson or PI)',
                //"Invoices Sent to Me", -  the same as "Invoices where I am a PI"
                "My Invoices",
                "My Outstanding Invoices",
                "Invoices where I am the PI",
                "Issued invoices I generated", //the same as "Invoices where I am a Salesperson"
                "Invoices where I am the salesperson",
                //"Unpaid Invoices sent to Me",
                "Unpaid Invoices where I am the PI",

                '[[hr]]',

                "Latest Versions of All Invoices Except Canceled",
                "Latest Versions of All Invoices",
                "Latest Versions of Issued (Unpaid) Invoices",
                "Latest Versions of Pending (Unissued) Invoices",
                "Latest Versions of Paid Invoices",
                "Latest Versions of Partially Paid Invoices",
                "Latest Versions of Paid and Partially Paid Invoices",
                "Latest Versions of Canceled Invoices",

                '[[hr]]',

                'All Invoices',
                'All Issued Invoices',
                'All Pending Invoices',

                '[[hr]]',

                "Old Versions of All Invoices",
                "Old Versions of Issued (Unpaid) Invoices",
                "Old Versions of Pending (Unissued) Invoices",
                "Old Versions of Paid Invoices",
                "Old Versions of Partially Paid Invoices",
                "Old Versions of Paid and Partially Paid Invoices",
                "Old Versions of Canceled Invoices"
            );

            return $filterTypes;
        } else {
            //Show only My Invoices
            $filterTypes = array(
                //'My Invoices (I am Submitter, Salesperson or PI)',
                //"Invoices Sent to Me", -  the same as "Invoices where I am a PI"
                "My Invoices",
                "My Outstanding Invoices",
                "Invoices where I am the PI",
                "Issued invoices I generated", //the same as "Invoices where I am a Salesperson"
                "Invoices where I am the salesperson",
                //"Unpaid Invoices sent to Me",
                "Unpaid Invoices where I am the PI",
            );

            return $filterTypes;
        }
    }

    //get allowed filter request types for logged in user
    public function getRequestFilterPresetType() {
        $transresUtil = $this->container->get('transres_util');
        $user = $this->secTokenStorage->getToken()->getUser();

        //get all enabled project specialties
        $specialties = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findBy(
            array(
                'type' => array("default","user-added")
            ),
            array('orderinlist' => 'ASC')
        );

        $allowSpecialties = array();
        foreach($specialties as $projectSpecialty) {
            $allowSpecialties[] = $projectSpecialty->getUppercaseFullName();
        }

        $filterTypes = array(
            'My Submitted Requests',
            "My Draft Requests",
            'Submitted Requests for My Projects',
            'Draft Requests for My Projects',
            //'Requests I Completed',
            //'[[hr]]'
        );

        if( $this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN') || $transresUtil->isAdminOrPrimaryReviewer() ) {
            $filterTypes[] = 'Requests I Completed';
        }

        $filterTypes[] = '[[hr]]';

        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() === false && $this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN') === false ) {
            return $filterTypes;
        }

        $filterTypes[] = 'All Requests';
        foreach($allowSpecialties as $allowSpecialty) {
            $filterTypes[] = "All $allowSpecialty Requests";
        }

        $filterTypes[] = 'All Requests (including Drafts)';
        $filterTypes[] = '[[hr]]';

        $filterTypes[] = 'All Pending Requests';
        foreach($allowSpecialties as $allowSpecialty) {
            $filterTypes[] = "All $allowSpecialty Pending Requests";
        }
        $filterTypes[] = '[[hr]]';

        $filterTypes[] = 'All Active Requests';
        foreach($allowSpecialties as $allowSpecialty) {
            $filterTypes[] = "All $allowSpecialty Active Requests";
        }
        $filterTypes[] = '[[hr]]';

        $filterTypes[] = 'All Completed Requests';
        foreach($allowSpecialties as $allowSpecialty) {
            $filterTypes[] = "All $allowSpecialty Completed Requests";
        }
        $filterTypes[] = '[[hr]]';


        $filterTypes[] = 'All Completed and Notified Requests';
        foreach($allowSpecialties as $allowSpecialty) {
            $filterTypes[] = "All $allowSpecialty Completed and Notified Requests";
        }
        //$filterTypes[] = '[[hr]]';

        //TODO: add all the Work Queues as named in the list manager + appended “ Work Queue”
        //(So there should be two new links: “CTP Lab Work Queue” and “MISI Lab Work Queue”

        return $filterTypes;
    }

    public function findCreateSiteParameterEntity($specialtyStr) {
        $transresUtil = $this->container->get('transres_util');
        return $transresUtil->findCreateSiteParameterEntity($specialtyStr);
    }

    //send by email to recipient (principalInvestigator)
    public function sendInvoicePDFByEmail($invoice) {

        $emailUtil = $this->container->get('user_mailer_utility');
        $transresUtil = $this->container->get('transres_util');

        $transresRequest = null;
        $siteParameter = null;
        $attachmentPath = null;
        $ccs = null;

        $piEmailArr = $this->getInvoicePis($invoice);

        if( count($piEmailArr) == 0 ) {
            return "There are no PI and/or Billing Contact emails. Email has not been sent.";
        }

        //Attachment: Invoice PDF
        $invoicePDF = $invoice->getRecentPDF();
        if( $invoicePDF ) {
            //$attachmentPath = $invoicePDF->getAbsoluteUploadFullPath();
            $attachmentPath = $invoicePDF->getAttachmentEmailPath(); //test is implemented
        }
        //$logger = $this->container->get('logger');
        //$logger->notice("attachmentPath=".$attachmentPath);


        $salesperson = $invoice->getSalesperson();
        if( $salesperson ) {
            $salespersonEmail = $salesperson->getSingleEmail(false);
            if( $salespersonEmail ) {
                $ccs = $salespersonEmail;
            }
        }

        if( !$ccs ) {
            $submitter = $invoice->getSubmitter();
            if( $submitter ) {
                $submitterEmail = $submitter->getSingleEmail(false);
                if( $submitterEmail ) {
                    $ccs = $submitterEmail;
                }
            }
        }

        //find default site parameters
        $transresRequest = $invoice->getTransresRequest();
        if( $transresRequest ) {
            $project = $transresRequest->getProject();
            $projectSpecialty = $project->getProjectSpecialty();
            $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();

            $siteParameter = $this->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
            if( !$siteParameter ) {
                throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
            }
        }

        //Change Invoice status to Unpaid/Issued
        $invoice->setStatus("Unpaid/Issued");
        //if paid == total =>
        //overwrite status if paid is not null: "Paid Partially" or "Paid in Full"
        $this->updateInvoiceStatus($invoice);
        $this->em->persist($invoice);
        $this->em->flush($invoice);

        if( $siteParameter ) {
            $emailBody = $siteParameter->getTransresNotificationEmail();
            $emailBody = $transresUtil->replaceTextByNamingConvention($emailBody,$project,$transresRequest,$invoice);
        } else {
            $emailBody = "Please find the attached invoice in PDF.";
        }

        if( $siteParameter ) {
            $emailSubject = $siteParameter->getTransresNotificationEmailSubject();
            $emailSubject = $transresUtil->replaceTextByNamingConvention($emailSubject,$project,$transresRequest,$invoice);
        } else {
            $emailSubject = "Pathology Translational Research Invoice ".$invoice->getOid();
        }

        // Verify that the updated (version greater than 1) invoice is sent with
        // an email body that makes it clear the attached invoice has been “updated”
        // (For example, it contains two sentences similar to:
        // “The attached invoice
        // for your work request has been updated. If you have received any previous
        // versions of the invoice for the same work request, please use this updated
        // invoice instead.”
        // If this or similar text is absent, please add it via an
        // if statement to all invoices in this situation where (a) invoice version
        // is greater than 1 and (b) event log indicates any of the previous versions
        // for this invoice have been sent out.
        if( $this->isInvoiceAlreadySent($invoice) ) {
            $newline =  "<br>\n";
            $invoiceAlreadySentTxt = "The attached invoice for your work request has been updated. ".
                "If you have received any previous versions of the invoice for the same work request, ".
                "please use this updated invoice instead.";

            $emailBody = $emailBody . $newline.$newline. $invoiceAlreadySentTxt;
        }

        //send by email
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);
        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $piEmailArr, $emailSubject, $emailBody, $ccs, $senderEmail, $attachmentPath );

        $msg =  "Invoice ".$invoice->getOid()." PDF has been sent by email to " . implode(", ",$piEmailArr) . " with CC to " . $ccs;
        $msg = $msg . ".<br> Subject: " . $emailSubject . ".<br> Body: " . $emailBody . "<br>attachmentPath=" . $attachmentPath;

        //event log
        $eventType = "Invoice PDF Issued";
        $transresUtil->setEventLog($invoice,$eventType,$msg);

        return $msg;
    }
    public function isInvoiceAlreadySent($invoice) {
        $version = $invoice->getVersion();
        //echo "version=$version <br>";
        if( $version > 1 ) {
            $dqlParameters = array();

            $repository = $this->em->getRepository('AppUserdirectoryBundle:Logger');
            $dql = $repository->createQueryBuilder("logger");
            $dql->innerJoin('logger.eventType', 'eventType');

            //$dql->where("logger.entityNamespace = 'App\TranslationalResearchBundle\Entity'");

            $dql->andWhere("logger.entityName = 'Invoice'");

            $dql->andWhere("eventType.name = :eventTypeName");
            $dqlParameters['eventTypeName'] = "Invoice PDF Issued";

            //$dql->andWhere("logger.entityId = :entityId");
            //$dqlParameters['entityId'] = $invoice->getId();

            $transresRequest = $invoice->getTransresRequest();
            $oid = $transresRequestOid = $transresRequest->getOid();
            //echo "oid=[$oid]<br>";
            $dql->andWhere("logger.event LIKE :eventStr");
            $dqlParameters['eventStr'] = '%'.$oid.'%';

            $dql->orderBy("logger.id","DESC");
            $query = $this->em->createQuery($dql);

            if( count($dqlParameters) > 0 ) {
                $query->setParameters($dqlParameters);
            }

            $loggers = $query->getResult();
            //echo "loggers=".count($loggers)."<br>";

            if( count($loggers) > 0 ) {
                return true;
            }
        }
        return false;
    }

    public function sendNewInvoicePDFGeneratedEmail($invoice) {

        $emailUtil = $this->container->get('user_mailer_utility');
        $transresUtil = $this->container->get('transres_util');

        $newline = "<br>";
        $siteParameter = null;
        $attachmentPath = null;
        $salespersonEmail = null;
        $ccs = null;

        $transresRequest = $invoice->getTransresRequest();
        $project = $transresRequest->getProject();

        if( $project ) {
            $projectSpecialty = $project->getProjectSpecialty();
            if( $projectSpecialty ) {
                $specialtyPostfix = $projectSpecialty->getUppercaseName();
                $specialtyPostfix = "_" . $specialtyPostfix;
            } else {
                $specialtyPostfix = null;
            }
        }

        $invoicePisStr = $this->getInvoicePisStr($invoice);

        if( !$invoicePisStr ) {
            return "There are no PI to send invoice by emails. Email has not been sent.";
        }

        $salesperson = $invoice->getSalesperson();
        if( $salesperson ) {
            $salespersonEmail = $salesperson->getSingleEmail(false);
        } else {
            return "There is no sales person. Email has not been sent.";
        }


        $invoiceShowUrl = $this->getInvoiceShowUrl($invoice);
        $invoiceEditUrl = $this->getInvoiceEditUrl($invoice);
        $sendInvoiceByEmailUrl = $this->getSendInvoiceByEmailUrl($invoice);

        //$emailSubject = "Draft Translational Research Invoice for work request ".$transresRequest->getOid()." has been generated";
        $emailSubject = "Draft Invoice for the ".$transresUtil->getBusinessEntityName()." for work request ".$transresRequest->getOid()." has been generated";

        //Please review the draft invoice pdf for work request APCP12-REQ12 by visiting:
        $body = "Please review the draft invoice pdf for work request ".$transresRequest->getOid()." by visiting:";
        $body = $body . $newline . $invoiceShowUrl;

        //To issue the invoice to Name - email (WCMC CWID) at email email@med.cornell.edu, email@med.cornell.edu please visit this link:
        //http://localhost/order/translational-research/invoice/send-invoice-pdf-by-email/APCP12-REQ12-V2
        $body = $body . $newline.$newline . "To issue the invoice to ".$invoicePisStr." please visit this link:";
        $body = $body . $newline . $sendInvoiceByEmailUrl;

        //To edit the invoice and generate an updated copy, please visit this link:
        $body = $body . $newline.$newline . "To edit the invoice and generate an updated copy, please visit this link:";
        $body = $body . $newline . $invoiceEditUrl;

        //send by email
        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);

        //Billing Admin as CC
        $ccs = array();
        $billingUsers = $this->em->getRepository('AppUserdirectoryBundle:User')->findUserByRole("ROLE_TRANSRES_BILLING_ADMIN".$specialtyPostfix);
        foreach( $billingUsers as $billingUser ) {
            if( $billingUser ) {
                $ccs[] = $billingUser->getSingleEmail(false);
            }
        }

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
        $emailUtil->sendEmail( $salespersonEmail, $emailSubject, $body, $ccs, $senderEmail );

        $msg =  "Invoice ".$invoice->getOid()." PDF has been sent by email to " . $salespersonEmail . " with CC to " . implode(", ",$ccs);
        $msg = $msg . ".<br> Subject: " . $emailSubject . ".<br> Body: " . $body;

        return $msg;
    }

    public function getInvoicePis($invoice,$asEmail=true) {
        $piEmailArr = array();

        $pi = $invoice->getPrincipalInvestigator();

        if( !$pi ) {
            //return "There is no PI. Email has not been sent.";
            //use submitter
            $pi = $invoice->getSubmitter();
        }

        if( $asEmail ) {
            $piEmail = $pi->getSingleEmail(false);
            if( $piEmail ) {
                $piEmailArr[] = $piEmail;
            }
        } else {
            $piEmailArr[] = $pi;
        }

        //Invoice's Billing Contact
        $invoiceBillingContact = $invoice->getBillingContact();
        if( $invoiceBillingContact ) {
            if( $asEmail ) {
                $invoiceBillingContactEmail = $invoiceBillingContact->getSingleEmail(false);
                if ($invoiceBillingContactEmail) {
                    $piEmailArr[] = $invoiceBillingContactEmail;
                }
            } else {
                $piEmailArr[] = $invoiceBillingContact;
            }
        }

        return $piEmailArr;
    }
    public function getInvoicePisStr($invoice) {
        $pis = $this->getInvoicePis($invoice,false);
        $piArr = array();
        foreach($pis as $pi) {
            $piArr[] = $pi->getUsernameOptimal();
        }
        if( count($piArr) > 0 ) {
            return " (".implode(", ",$piArr).")";
        }

        return null;
    }

    public function getInvoiceStatuses($withNotRealStatus=true) {
        $statuses = array(
            "Pending" => "Pending",
            "Unpaid/Issued" => "Unpaid/Issued",
            "Paid in Full" => "Paid in Full",
            "Paid Partially" => "Paid Partially",
            "Refunded Fully" => "Refunded Fully",
            "Refunded Partially" => "Refunded Partially",
            "Canceled" => "Canceled",
            //"Latest Versions of All Invoices" => "Latest Versions of All Invoices",
            //"Latest Versions of All Invoices Except Canceled" => "Latest Versions of All Invoices Except Canceled"
            //"All Invoices Except Canceled" => "All Invoices Except Canceled"
        );

        if( $withNotRealStatus ) {
            $statuses["All Invoices Except Canceled"] = "All Invoices Except Canceled";
        }
        return $statuses;
    }

//    //get Issued Invoices
//    public function getInvoicesInfosByRequest_ORIG($transresRequest) {
//        $invoicesInfos = array();
//        $count = 0;
//        $total = 0.00;
//        $paid = 0.00;
//        $due = 0.00;
//        $subsidy = 0.00;
//        $grandTotal = 0.00;
//
//        //check if progressState != draft, canceled
//        $progressState = $transresRequest->getProgressState();
//        //check if billingState != draft, canceled
//        $billingState = $transresRequest->getBillingState();
//
//        $skip = false;
//        if( $progressState == 'draft' || $progressState == 'canceled' ) {
//            $skip = true;
//        }
//        if( $billingState == 'draft' || $billingState == 'canceled' ) {
//            $skip = true;
//        }
//
//        if( $skip == false ) {
//            foreach ($transresRequest->getInvoices() as $invoice) {
//                if( $invoice->getLatestVersion() && $invoice->getStatus() != 'Canceled' ) {
//                    $count++;
//                    $total = $total + $invoice->getTotal();
//                    $paid = $paid + $invoice->getPaid();
//                    $due = $due + $invoice->getDue();
//
//                    //$subsidy = $subsidy + $invoice->getSubsidy();
//                    $subsidy = $subsidy + $this->getInvoiceSubsidy($invoice);
//
//                    $grandTotal = $total + $subsidy;
//                }//if invoice latest
//            }//foreach invoice
//        }//$skip == false
//
//        //echo "total=$total<br>";
//        //echo "paid=$paid<br>";
//
//        if( $count > 0 ) {
//            //if ($total > 0) {
//                $total = $this->toDecimal($total);
//            //}
//            //if ($paid > 0) {
//                $paid = $this->toDecimal($paid);
//            //}
//            //if ($due > 0) {
//                $due = $this->toDecimal($due);
//            //}
//            //if ($subsidy > 0) {
//                $subsidy = $this->toDecimal($subsidy);
//            //}
//
//            $grandTotal = $this->toDecimal($grandTotal);
//        } else {
//            $total = null;
//            $paid = null;
//            $due = null;
//            $subsidy = null;
//            $grandTotal = null;
//        }
//
//        //echo "paid=$paid<br>";
//
//        $invoicesInfos['count'] = $count;
//        $invoicesInfos['total'] = $total;
//        $invoicesInfos['paid'] = $paid;
//        $invoicesInfos['due'] = $due;
//        $invoicesInfos['subsidy'] = $subsidy;
//        $invoicesInfos['grandTotal'] = $grandTotal;
//
//        return $invoicesInfos;
//    }
    public function getInvoicesInfosByRequest($transresRequest) {
        $admin = false;
        $transresRequestUtil = $this->container->get('transres_request_util');
        if( $transresRequestUtil->isUserHasInvoicePermission($invoice = NULL, "update") ) {
            $admin = true;
        }
        return $transresRequest->getInvoicesInfosByRequest($admin);
    }

    public function toDecimal($number) {
        return number_format((float)$number, 2, '.', '');
    }

//    public function getTotalFeesByQuantity($fee,$feeAdditionalItem,$initialQuantity,$quantity) {
//        $quantity = intval($quantity);
//        //$fee = intval($fee);
//        $fee = $this->toDecimal($fee);
//        if( $feeAdditionalItem ) {
//            //$feeAdditionalItem = intval($feeAdditionalItem);
//            $feeAdditionalItem = $this->toDecimal($feeAdditionalItem);
//        } else {
//            $feeAdditionalItem = $fee;
//        }
//
//        $initialTotal = $this->toDecimal($initialQuantity * $fee);
//        $additionalTotal = $this->toDecimal($quantity * $feeAdditionalItem);
//
//        $total = $initialTotal + $additionalTotal;
//
//        if ($total > 0) {
//            $total = $this->toDecimal($total);
//        }
//        return $total;
//    }

    //check if user allowed to access by the project's specialty
    public function isUserAllowedAccessInvoiceBySpecialty($invoice) {
        $transresUtil = $this->container->get('transres_util');
        $transresRequest = $invoice->getTransresRequest();
        if( $transresRequest ) {
            //ok
        } else {
            return true;
        }
        $project = $transresRequest->getProject();
        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) ) {
            return true;
        }
        return false;
    }

    public function isUserHasInvoicePermission( $invoice, $action ) {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        return $transresPermissionUtil->isUserHasInvoicePermission($invoice,$action);
    }

    public function getLatestInvoice( $transresRequest, $transresRequestId=null ) {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Invoice');
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.transresRequest','transresRequest');

        $dql->where("transresRequest.id = :transresRequestId");
        $dql->orderBy("invoice.id","DESC");

        $query = $this->em->createQuery($dql);

        if( $transresRequest ) {
            $transresRequestId = $transresRequest->getId();
        }

        $query->setParameters(array(
            "transresRequestId" => $transresRequestId
        ));

        $invoices = $query->getResult();

        if( count($invoices) > 0 ) {
            $latestInvoice = $invoices[0];
            if( $latestInvoice->getLatestVersion() ) {
                return $latestInvoice;
            }
        }

        return null;
    }

    public function getLatestPackingSlipPdf( $transresRequest ) {
        $pdfs = $transresRequest->getPackingSlipPdfs();

        if( count($pdfs) > 0 ) {
            $latestPdf = $pdfs[0];
            return $latestPdf;
        }

        return null;
    }

    public function getTransresSiteParameter( $fieldName, $transresRequest=NULL ) {
        $value = $this->getTransresSiteParameterSingle($fieldName,$transresRequest);

        if( $value === NULL ) {
            $value = $this->getTransresSiteParameterSingle($fieldName,NULL);
        }

        return $value;
    }
    public function getTransresSiteParameterSingle( $fieldName, $transresRequest=NULL ) {
        $transresUtil = $this->container->get('transres_util');
        $project = $transresRequest->getProject();
        return $transresUtil->getTransresSiteProjectParameter($fieldName,$project);

//        if( !$fieldName ) {
//            throw new \Exception("Field name is empty");
//        }
//
//        $projectSpecialtyAbbreviation = NULL;
//
//        if( $transresRequest ) {
//            $project = $transresRequest->getProject();
//            $projectSpecialty = $project->getProjectSpecialty();
//            $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();
//        }
//
//        $siteParameter = $this->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
//        if( !$siteParameter ) {
//            throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
//        }
//
//        $getMethod = "get".$fieldName;
//
//        $value = $siteParameter->$getMethod();
//
//        return $value;
    }

    //E-Mail Packing Slip to PIs and Submitter
    public function sendPackingSlipPdfByEmail($transresRequest,$pdf,$subject,$body) {
        $emailUtil = $this->container->get('user_mailer_utility');
        $transresUtil = $this->container->get('transres_util');

        if( !$pdf ) {
            return "Error: Packing Slip PDF not found";
        }

        //get emails: admins and primary reviewers, submitter, principalInvestigators, contact
        $emails = $this->getRequestEmails($transresRequest);

        if( count($emails) == 0 ) {
            return "Error: Packing Slip PDF ".$pdf->getUniquename()." has not been sent, because the email list (admins and primary reviewers, submitter, principalInvestigators, contact) is empty.";
        }

        //$user = $this->secTokenStorage->getToken()->getUser();
        //$senderEmail = $user->getSingleEmail(false);

        $project = $transresRequest->getProject();

        $adminEmailInfos = array();
        $adminEmails = array();
        $asEmail=false;
        //$onlyAdmin=true;
        //$admins = $transresUtil->getTransResAdminEmails($project->getProjectSpecialty(),$asEmail,$onlyAdmin);
        $admins = $this->getRequestAdminTechEmails($transresRequest,$asEmail); //admins, technicians
        foreach($admins as $admin) {
            $adminSingleEmail = $admin->getSingleEmail(false);
            $adminEmailInfos[] = $admin->getUsernameOptimal()." (".$adminSingleEmail.")";
            $adminEmails[] = $adminSingleEmail;
        }

        // The Translational Research group is working on your request (REQ-ID)
        // and is planning to deliver the items listed in the attached document.
        // Please review the items and comments (if any), and if you have any concerns,
        // contact the Translational Research group by emailing [FirstName LastName] (email@address). (mailto: link)
        //list all users with Translational Research Administrator roles
//        $body = "The Translational Research group is working on your request ".$transresRequest->getOid().
//        " and is planning to deliver the items listed in the attached document.".
//        " Please review the items and comments (if any), and if you have any concerns,".
//        " contact the Translational Research group by emailing ".implode(", ",$adminEmailInfos);
        //Replace [[EMAILS]] with
        $body = str_replace("[[EMAILS]]",implode(", ",$adminEmailInfos),$body);

        //$attachmentPath = $pdf->getAbsoluteUploadFullPath(); //original but it does not work in swift from console and web
        $attachmentPath = $pdf->getAttachmentEmailPath(); //test is implemented //TODO: test and replace all other getAbsoluteUploadFullPath to getServerPath for email attachment
        //$logger = $this->container->get('logger');
        //$logger->notice("attachmentPath=".$attachmentPath);

        $senderEmail = $transresUtil->getTransresSiteProjectParameter('fromEmail',$project);

        //                    $emails, $subject, $message, $ccs=null, $fromEmail=null, $attachmentPath=null
        $emailUtil->sendEmail($emails,$subject,$body,$adminEmails,$senderEmail,$attachmentPath);

        $msg = "Packing Slip PDF ".$pdf->getUniquename()." has been sent to:<br>".implode(", ",$emails)."<br> Subject: ".$subject."<br> Body: ".$body.".";

        //event log
        $eventType = "Invoice PDF Issued";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg."<br>attachmentPath=".$attachmentPath);

        return $msg;
    }

    public function syncRequestStatus($transresRequest,$toState,$testing) {
        $eventlog = false;
        $billingState = null;

        //5 - If Work Request’s Progress Status is changed to “Canceled”
        // (both via Action button dropdown menu - add it - or via Edit),
        // set its Billing Status to “Canceled” and
        // record auto-action to the Event log as a separate Billing Status event
        // (different from the event log entry for the manual progress setting).
        if( $toState == "canceled" ) {
            $billingState = "canceled";
            $transresRequest->setBillingState($billingState);
            $eventlog = true;
        }

        //6 - If Work Request’s Progress Status is changed to “Draft”
        // (both at time of initial submission or via Edit), set its Billing Status to “Draft”
        // and record auto-action to the Event log as a separate Billing Status event
        // (different from the event log entry for the manual progress setting).
        if( $toState == "draft" ) {
            $billingState = "draft";
            $transresRequest->setBillingState($billingState);
            $eventlog = true;
        }

        //7- If Work Request’s Progress Status is changed to “Active”
        // (both at time of submission or via Edit), set its Billing Status to “Active”
        // and record auto-action to the Event log as a separate Billing Status event
        // (different from the event log entry for the manual progress setting).
        if( $toState == "active" ) {
            $billingState = "active";
            $transresRequest->setBillingState($billingState);
            $eventlog = true;
        }

        //record auto-action to the Event log as a separate Billing Status event
        $msg = null;
        if( $eventlog ) {

            if( !$testing ) {
                $this->em->flush($transresRequest);
            }
            
            $billingLabel = $this->getRequestStateLabelByName($billingState,"billing");
            $progressLabel = $this->getRequestStateLabelByName($toState,"progress");
            //event log
            $transresUtil = $this->container->get('transres_util');
            $eventType = "Request State Changed";
            $msg = "Work Request ID ".$transresRequest->getOid()." billing state has been changed to ".$billingLabel.
                ", triggered by progress status change to ".$progressLabel;;
            $transresUtil->setEventLog($transresRequest,$eventType,$msg);
        }

        return $msg;
    }

    public function syncInvoiceRequestStatus($invoice,$invoiceState) {

        $transresRequest = $invoice->getTransresRequest();

        $eventlog = false;
        $billingState = null;

        //8- If associated Invoice’s Status is changed to “Unpaid / Issued”,
        // set the associated Work Request’s Billing Status to “Invoiced” and
        // record auto-action to the Event log as a separate Billing Status event
        // (different from the event log entry for the manual progress setting).
        if( $invoiceState == "Unpaid/Issued" ) {
            $billingState = "invoiced";
            $transresRequest->setBillingState($billingState);
            $eventlog = true;
        }

        //9- If associated Invoice’s Status is changed to “Paid in Full”,
        // set the associated Work Request’s Billing Status to “Paid” and
        // record auto-action to the Event log as a separate Billing Status event
        // (different from the event log entry for the manual progress setting).
        if( $invoiceState == "Paid in Full" ) {
            $billingState = "paid";
            $transresRequest->setBillingState($billingState);
            $eventlog = true;
        }

        //10- If associated Invoice’s Status is changed to “Refunded Fully”,
        // set the associated Work Request’s Billing Status to “Refunded Fully” and
        // record auto-action to the Event log as a separate Billing Status event
        // (different from the event log entry for the manual progress setting).
        if( $invoiceState == "Refunded Fully" ) {
            $billingState = "refunded";
            $transresRequest->setBillingState($billingState);
            $eventlog = true;
        }

        //11- If associated Invoice’s Status is changed to “Refunded Partially”,
        // set the associated Work Request’s Billing Status to “Refunded Partially” and
        // record auto-action to the Event log as a separate Billing Status event
        // (different from the event log entry for the manual progress setting).
        if( $invoiceState == "Refunded Partially" ) {
            $billingState = "partiallyRefunded";
            $transresRequest->setBillingState($billingState);
            $eventlog = true;
        }

        //12- If associated Invoice’s Status is changed to “Paid Partially”
        // via Edit or automatically by entering the paid amount,
        // set the associated Work Request’s Billing Status to “Paid Partially” and
        // record auto-action to the Event log as a separate Billing Status event
        // (different from the event log entry for the manual progress setting).
        if( $invoiceState == "Paid Partially" ) {
            $billingState = "partiallyPaid";
            $transresRequest->setBillingState($billingState);
            $eventlog = true;
        }

        //record auto-action to the Event log as a separate Billing Status event
        $msg = null;
        if( $eventlog ) {

            $this->em->flush($transresRequest);

            $stateLabel = $this->getRequestStateLabelByName($billingState,"billing");
            //event log
            $transresUtil = $this->container->get('transres_util');
            $eventType = "Request State Changed";
            $msg = "Work Request ID ".$transresRequest->getOid()." billing state has been changed to ".$stateLabel.
                ", triggered by invoice status change to ".$invoiceState;
            $transresUtil->setEventLog($transresRequest,$eventType,$msg);
        }

        return $msg;
    }

    //search fos bundle comments user_fosComment: 'thread_id = transres-Request-13541-billing'
    public function getRequestIdsByFosComment( $searchStr ) {
        $repository = $this->em->getRepository('AppUserdirectoryBundle:FosComment');
        $dql =  $repository->createQueryBuilder("foscomment");
        $dql->select('foscomment');


        $dql->where("LOWER(foscomment.body) LIKE LOWER(:searchStr)");
        $dql->andWhere("foscomment.entityName = 'TransResRequest'");
        //$dql->andWhere("(foscomment.entityName IS NULL OR foscomment.entityName = 'TransResRequest')");

        $query = $this->em->createQuery($dql);

        $query->setParameters(array(
            "searchStr" => "%".$searchStr."%",
        ));

        $comments = $query->getResult();
        //echo "comments count=".count($comments)."<br>";

        $requestIds = array();

        foreach($comments as $comment) {
            $requestId = $comment->getEntityId();
            if( $requestId ) {
                $requestIds[] = $requestId;
            } else {
                //OLD CASE when entityName was not recorded. Delete when all comments will be re-populated with object properties
                $commentId = $comment->getThread();
                //echo "commentId=".$commentId."<br>";
                //get request ID from $commentId 'transres-Request-13541-billing'
                $commentIdArr = explode("-", $commentId);
                if (count($commentIdArr) >= 3) {
                    $requestId = $commentIdArr[2];
                    if ($requestId) {
                        $requestIds[] = $requestId;
                        //echo "requestId=".$requestId."<br>";
                    }
                }
            }
        }
        //echo "requestIds count=".count($requestIds)."<br>";

        return $requestIds;
    }

    //Not Used. Where used in DashboardUtil
    public function getOverdueInvoices($projectSpecialty=null) {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Invoice');
        $dql =  $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.transresRequest','transresRequest');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("invoice.status = :unpaid AND invoice.latestVersion = TRUE"); //Unpaid/Issued
        $params["unpaid"] = "Unpaid/Issued";

        if( $projectSpecialty ) {
            $dql->andWhere("projectSpecialty.id = :specialtyId");
            $params["specialtyId"] = $projectSpecialty->getId();
        }

        /////////1. (dueDate < currentDate - invoiceDueDateMax) //////////////
        //overDueDate = currentDate - invoiceDueDateMax;
        $overDueDate = new \DateTime();
        //echo "overDueDate=".$overDueDate->format('Y-m-d H:i:s').$newline;
        $dql->andWhere("invoice.dueDate IS NOT NULL AND invoice.dueDate < :overDueDate");
        $params["overDueDate"] = $overDueDate->format('Y-m-d H:i:s');
        ////////////// EOF //////////////

        $query = $this->em->createQuery($dql);

        $query->setParameters(
            $params
        );

        $invoices = $query->getResult();
        //echo "$projectSpecialty count invoices=".count($invoices)."$newline";

        return $invoices;
    }

    public function getMatchingStrInvoiceByDqlParameters($dql,$dqlParameters) {
        $dql->select('invoice.id,invoice.total,invoice.paid,invoice.due,invoice.createDate');
        //$dql->groupBy('invoice.id');

        $query = $dql->getQuery();

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $results = $query->getScalarResult();
        //print_r($results);
        //echo "<br><br>";

        //All Invoices (188 matching for Total: $61,591.00, Paid: $30,000.00, Unpaid: $31591.00)

        $invoiceIds = array();
        $totalSum = 0;
        $paidSum = 0;
        $dueSum = 0;
        //$totalSum = $this->toDecimal($totalSum);

        //use createDate to get min and max dates
        $minDate = null;
        $maxDate = null;

        $counter = 0;
        foreach($results as $idParams) {
            //echo "id=".$idTotal.":$total"."<br>";
            //print_r($idTotal);
            $id = $idParams['id'];
            $total = $idParams['total'];
            $paid = $idParams['paid'];
            $due = $idParams['due'];
            //$total = $this->toDecimal($total);
            //echo "id=".$id.": $$total"."<br>";
            $totalSum = $totalSum + $total;
            $paidSum = $paidSum + $paid;
            $dueSum = $dueSum + $due;

            //min and max dates
            $createDateStr = $idParams['createDate']; //2018-01-30 17:24:39
            if( $createDateStr ) {
                //echo $id.": createDateStr=$createDateStr<br>";
                //$createDate = \DateTime::createFromFormat('Y-m-d H:i:s', $createDateStr);
                //$createDate = strtotime($createDateStr);
                $createDate = new \DateTime($createDateStr);
                //echo $id."origDate=$createDateStr; newDate=".$createDate->format("m/d/Y")."<br>";

                if( !$minDate ) {
                    $minDate = $createDate;
                }
                if( !$maxDate ) {
                    $maxDate = $createDate;
                }

                if( $createDate && $minDate ) {
                    //echo $id.": start comparing dates:<br>";
                    if( $createDate < $minDate ) {
                        //echo $id.": assign mindate<br>";
                        $minDate = $createDate;
                    }
                    if( $createDate > $maxDate ) {
                        //echo $id.": assign maxdate<br>";
                        $maxDate = $createDate;
                    }
                } else {
                    //echo $id.": NO comparing dates:<br>";
                }
            }

            $invoiceIds[] = $id;

            $counter++;
        }//foreach

//        if( !$minDate ) {
//            echo "no min date<br>";
//        }
//        if( !$maxDate ) {
//            echo "no max date<br>";
//        }

        $dateStr = "";
        if( $minDate && $maxDate ) {
            $minDateStr = $minDate->format("m/d/Y");
            $maxDateStr = $maxDate->format("m/d/Y");
            //echo "minDate=$minDateStr; maxDate=$maxDateStr <br>";
            //$minDateStr = $minDate;
            //$maxDateStr = $maxDate;
            //over X months [MM/DD/YYYY]-[MM/DD/YYYY]
            $diff = $maxDate->diff($minDate);
            if( $diff ) {
                $diffMonth = (($diff->format('%y') * 12) + $diff->format('%m')); //full months difference;
                $diffDays = $diff->days;
                //$diffDays = intval($diffDays);
            }
            //echo "days=".$diffDays."<br>";
            //echo "months=".$diffMonth."<br>";

//            if( $diffMonth == 1 ) {
//                $diffMonthStr = "over " . $diffMonth . " month ";
//            } elseif( $diffMonth > 1 ) {
//                $diffMonthStr = "over " . $diffMonth . " months ";
//            } else {
//                $diffMonthStr = "over less than a month ";
//            }

            $diffMonthStr = "";

            //Case 1) date1-date2 <=28 days then you could just say “over less than a month”
            if( $diffDays <= 28 ) {
                $diffMonthStr = "over less than a month ";
            }

            //Case 2) if the difference is >= 29 days but <2 months, then show “over about X weeks”
            if( $diffDays >= 29 && $diffMonth < 2 ) {
                $weeks = round($diffDays/7);
                $diffMonthStr = "over about $weeks weeks ";
            }

            //Case 3) anything 2 months and more is “over X months”
            if( $diffMonth >= 2 ) {
                $diffMonthStr = "over $diffMonth months ";
            }

            $dateStr = " " . $diffMonthStr . $minDateStr . "-" . $maxDateStr;
        } else {
            //echo "no min/max date<br>";
        }

        //123 matching for $456
        if( $counter ) {
            //544 matching over X months [MM/DD/YYYY]-[MM/DD/YYYY]
            $result = $counter . " matching$dateStr; Total: " . $this->getNumberFormat($totalSum) . ", Paid: " . $this->getNumberFormat($paidSum) . ", Unpaid: " . $this->getNumberFormat($dueSum);
        } else {
            $result = $counter . " matching";
        }

        //exit($result);

        $resultArr = array(
            'resultStr' => $result,
            'ids' => $invoiceIds
        );

        return $resultArr;
    }
    public function getNumberFormat($number,$digits=null) {
        return $this->toMoney($number,'');
        //return number_format($number,$digits);
    }
    function toMoneyOld($val,$symbol='$',$r=2) {
        //https://stackoverflow.com/questions/6369887/alternative-to-money-format-function-in-php-on-windows-platform
        //echo "val=".$val."<br>";
        if( !$val ) {
            $val = 0;
        }
        //$n = (float)$val;
        $n = $val;
        //$c = is_float($n) ? 1 : number_format($n,$r);
        //$d = '.';
        $t = ',';
        $sign = ($n < 0) ? '-' : '';
        $i = $n=number_format(abs($n),$r);
        //$j = (($j = $i.length) > 3) ? $j % 3 : 0;
        $j = (($j = strlen($i)) > 3) ? $j % 3 : 0;

        return  $symbol.$sign .($j ? substr($i,0, $j) + $t : '').preg_replace('/(\d{3})(?=\d)/',"$1" + $t,substr($i,$j)) ;
    }
    function toMoney($val,$symbol='$',$r=2)
    {
        $fmt = new \NumberFormatter( 'en_US', \NumberFormatter::CURRENCY );
        return $fmt->formatCurrency($val, "USD")."\n";

    }

    //NOT USED
    public function getTotalStrInvoice() {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Invoice');
        $dql = $repository->createQueryBuilder("invoice");
        //$dql->select('COUNT(invoice)');
        $dql->select('invoice.total');
        //$dql->groupBy('invoice.id');

        $query = $dql->getQuery();

        $results = $query->getScalarResult();

        $totalSum = 0;
        $counter = 0;
        foreach($results as $idTotal) {
            $total = $idTotal['total'];
            $totalSum = $totalSum + $total;
            $counter++;
        }

        //123 matching for $456
        if( $counter ) {
            $result = $counter . " total for Total $" . $totalSum;
        } else {
            $result = $counter . " total";
        }

        return $result;
    }

    public function getLatestInvoiceLists( $projectSpecialty ) {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Invoice');
        $dql = $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.transresRequest','transresRequest');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('project.projectSpecialty','projectSpecialty');

        $dql->where("invoice.latestVersion = TRUE");

        $params = array();
        if( $projectSpecialty ) {
            $dql->andWhere("projectSpecialty.id = :specialtyId");
            $params["specialtyId"] = $projectSpecialty->getId();
        }

        $query = $dql->getQuery();

        $query->setParameters(
            $params
        );

        $invoices = $query->getResult();

        return $invoices;
    }

    public function getProjectMiniRequests($projectId) {
        $repository = $this->em->getRepository('AppTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest.id,transresRequest.oid,transresRequest.fundedAccountNumber,transresRequest.progressState');

        //$dql->leftJoin('transresRequest.submitter','submitter');
        $dql->leftJoin('transresRequest.project','project');
        //$dql->leftJoin('submitter.infos','submitterInfos');

        $dqlParameters = array();

        $dql->andWhere("project.id = :projectId");

        $dqlParameters["projectId"] = $projectId;

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $requests = $query->getResult();

        return $requests;
    }

    public function createtInvoicesCsvSpout( $ids, $fileName, $limit=null ) {
        //$writer = WriterFactory::create(Type::XLSX); //cell type can not be set in xlsx 
        //$writer = WriterFactory::create(Type::CSV);
        $writer = WriterEntityFactory::createCSVWriter();
        $writer->openToBrowser($fileName);

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            //->setFontItalic()
            ->setFontSize(12)
            ->setFontColor(Color::BLACK)
            ->setShouldWrapText()
            ->setBackgroundColor(Color::toARGB("E0E0E0"))
            ->build();

        $regularStyle = (new StyleBuilder())
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
//                'Invoice Number',               //0 - A
//                'Fund Number',                  //1 - B
//                'IRB (IACUC) Number',           //2 - C
//                'Salesperson',                  //3 - D
//                'Generated',                    //4 - E
//                'Updated',                      //5 - F
//                'Version',                      //6 - G
//                'Due Date',                     //7 - H
//                'Status',                       //8 - I
//                'Bill To',                      //9 - J
//                'Total $',                        //10 - K
//                'Paid $',                         //11 - L
//                'Due $',                          //12 - M
//            ],
//            $headerStyle
//        );
        $spoutRow = WriterEntityFactory::createRowFromArray(
            [
                'Invoice Number',               //0 - A
                'Fund Number',                  //1 - B
                'IRB (IACUC) Number',           //2 - C
                'Salesperson',                  //3 - D
                'Generated',                    //4 - E
                'Updated',                      //5 - F
                'Version',                      //6 - G
                'Due Date',                     //7 - H
                'Status',                       //8 - I
                'Bill To',                      //9 - J
                'Total $',                        //10 - K
                'Paid $',                         //11 - L
                'Due $',                          //12 - M
            ],
            $headerStyle
        );
        $writer->addRow($spoutRow);

        $count = 0;
        $totalInvoices = 0;
        $totalTotal = 0;
        $paidTotal = 0;
        $dueTotal = 0;

        foreach( $ids as $invoiceId ) {

            if( $limit && ($count++ > $limit) ) {
                break;
            }

            $invoice = $this->em->getRepository('AppTranslationalResearchBundle:Invoice')->find($invoiceId);
            if( !$invoice ) {
                continue;
            }

//            if( $this->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) === false ) {
//                continue;
//            }

            $totalInvoices++;

            $data = array();

            $data[0] = $invoice->getOid();

            $data[1] = $invoice->getFundedAccountNumber();

            $data[2] = $invoice->getProjectIrbIacucNumber();

            $data[3] = $invoice->getSalesperson()."";

            $createDateStr = null;
            $createDate = $invoice->getCreateDate();
            if( $createDate ) {
                $createDateStr = $createDate->format("m/d/Y");
            }
            $data[4] = $createDateStr;

            $updateDateStr = null;
            $updateDate = $invoice->getUpdateDate();
            if( $updateDate ) {
                $updateDateStr = $updateDate->format("m/d/Y");
            }
            $data[5] = $updateDateStr;

            $version = $invoice->getVersion();
            if( $invoice->getLatestVersion() ) {
                $version = $version . " (Latest)";
            }
            $data[6] = $version;

            $dueDateStr = null;
            $dueDate = $invoice->getDueDate();
            if( $dueDate ) {
                $dueDateStr = $dueDate->format("m/d/Y");
            }
            $data[7] = $dueDateStr;

            $data[8] = $invoice->getStatus();

            //{{ invoice.invoiceTo|length > 25 ? invoice.invoiceTo|slice(0, 25) ~ '...' : invoice.invoiceTo  }}
            $data[9] = $invoice->getInvoiceTo();

            $total = $invoice->getTotal();
            $totalTotal = $totalTotal + $total;
            $data[10] = $total;

            $paid = $invoice->getPaid();
            $paidTotal = $paidTotal + $paid;
            $data[11] = $paid;

            $due = $invoice->getDue();
            $dueTotal = $dueTotal + $due;
            $data[12] = $due;

            //$writer->addRowWithStyle($data,$regularStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $regularStyle);
            $writer->addRow($spoutRow);

        }//invoices

        $data = array();
        $data[0] = "Total Number Invoices";
        $data[1] = $totalInvoices;
        $data[2] = null;
        $data[3] = null;
        $data[4] = null;
        $data[5] = null;
        $data[6] = null;
        $data[7] = null;
        $data[8] = null;
        $data[9] = null;
        $data[10] = $totalTotal;
        $data[11] = $paidTotal;
        $data[12] = $dueTotal;
        //$writer->addRowWithStyle($data,$footerStyle);
        $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
        $writer->addRow($spoutRow);

        $writer->close();
    }


    public function createtWorkRequestCsvSpout( $ids, $fileName, $limit=null ) {

        $transresUtil = $this->container->get('transres_util');
        $trpBusinessNameAbbreviation = $transresUtil->getBusinessEntityAbbreviation();
        
        //$writer = WriterFactory::create(Type::XLSX); //cell type can not be set in xlsx
        //$writer = WriterFactory::create(Type::CSV);
        $writer = WriterEntityFactory::createCSVWriter();
        $writer->openToBrowser($fileName);

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            //->setFontItalic()
            ->setFontSize(12)
            ->setFontColor(Color::BLACK)
            ->setShouldWrapText()
            ->setBackgroundColor(Color::toARGB("E0E0E0"))
            ->build();

        $regularStyle = (new StyleBuilder())
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

        $spoutRow = WriterEntityFactory::createRowFromArray(
            [
                'Work Request ID',                          //0 - A
                'Submitter',                                //1 - B
                'Submitted',                                //2 - C
                'Fund Number',                              //3 - D
                'Completion Status',                        //4 - E
                'Billing Status',                           //5 - F
                'Total Fees (Total Work Request Fee)',      //6 - G
                'Invoices',                                 //7 - H
                'Invoice Status',                           //8 - I
                'Invoice Total (Total Amount of the most recent issued Invoice)',                            //9 - J
                'Invoice Paid',                             //10 - K
                'Invoice Due',                              //11 - L

                'Products/Services Category',               //12 - M
                "Requestd Quantity",                        //13
                "Completed Quantity",                       //14
                "Comment",                                  //15
                "Note ($trpBusinessNameAbbreviation tech)"                           //16
            ],
            $headerStyle
        );
        $writer->addRow($spoutRow);

        $count = 0;
        $countRequest = 0;
        $countInvoices = 0;
        $totalTotalFees = 0;
        $totalInvoiceFees = 0;
        $paidTotal = 0;
        $dueTotal = 0;
        $productServices = 0;

        foreach( $ids as $requestId ) {

            if( !$requestId ) {
                continue;
            }

            if( $limit && ($count++ > $limit) ) {
                break;
            }

            $transResRequest = $this->em->getRepository('AppTranslationalResearchBundle:TransResRequest')->find($requestId);
            if( !$transResRequest ) {
                continue;
            }

            $countRequest++;

            $data = array();

            $data[0] = $transResRequest->getOid();

            $submitter = $transResRequest->getSubmitter();
            if( $submitter ) {
                $submitter = $submitter->getNameEmail();
            }
            $data[1] = $submitter;

            $createdDateStr = NULL;
            $createdDate = $transResRequest->getCreateDate();
            if( $createdDate ) {
                $createdDateStr = $createdDate->format('m/d/Y');
            }
            $data[2] = $createdDateStr;

            $data[3] = $transResRequest->getFundedAccountNumber();

            $data[4] = $this->getProgressStateLabelByName($transResRequest->getProgressState());

            $data[5] = $this->getBillingStateLabelByName($transResRequest->getProgressState());

            $productInfoArr = $this->getTransResRequestProductInfoArr($transResRequest);
//                'totalProducts' => $totalProducts,
//                'totalFee' => $subTotal,
//                'productRequested' => $requested,
//                'productCompleted' => $completed,
//                'productCategory' => $category,
//                'productComment' => $comment,
//                'productNote' => $note,

            //            'Total Fees',                       //6 - G
            $totalFee = $productInfoArr['totalFee'];
            $data[6] = $totalFee;
            $totalTotalFees = $totalTotalFees + $totalFee;

//            'Invoices',                         //7 - H
            $invoiceCount = 0;
            $invoices = $transResRequest->getInvoices();
            if( $invoices ) {
                $invoiceCount = count($invoices);
            }
            $countInvoices = $countInvoices + $invoiceCount;
            $data[7] = $invoiceCount;
            
//            'Invoice Status',                   //8 - I
            $status = NULL;
            $latestInvoice = $this->getLatestInvoice($transResRequest);
            if( $latestInvoice && $latestInvoice->getOid() ) {
                $status = $latestInvoice->getStatus();
            }
            $data[8] = $status;

//            'Invoice Total',                    //9 - J
            $invoicesInfos = $this->getInvoicesInfosByRequest($transResRequest);
//            $invoicesInfos['count'] = $count;
//            $invoicesInfos['total'] = $total;
//            $invoicesInfos['paid'] = $paid;
//            $invoicesInfos['due'] = $due;
            $invoiceFees = $invoicesInfos['total'];
            $data[9] = $invoiceFees;
            $totalInvoiceFees = $totalInvoiceFees + $invoiceFees;

//            'Invoice Paid',                     //10 - K
            $data[10] = $invoicesInfos['paid'];
            $paidTotal = $paidTotal + $invoicesInfos['paid'];

//            'Invoice Due',                      //11 - L
            $data[11] = $invoicesInfos['due'];
            $dueTotal = $dueTotal + $invoicesInfos['due'];

//            'Products/Services',                //12 - M
//                'totalProducts' => $totalProducts,
//                'totalFee' => $subTotal,
//                'productRequested' => $requested,
//                'productCompleted' => $completed,
//                'productCategory' => $category,
//                'productComment' => $comment,
//                'productNote' => $note,

            $productArr = $productInfoArr['productInfoArr'];
            $productServices = $productServices + $productInfoArr['totalProducts'];
            if( $productInfoArr['totalProducts'] > 0 ) {
                $productData = array();
                $productData[0] = null;
                $productData[1] = null;
                $productData[2] = null;
                $productData[3] = null;
                $productData[4] = null;
                $productData[5] = null;
                $productData[6] = null;
                $productData[7] = null;
                $productData[8] = null;
                $productData[9] = null;
                $productData[10] = null;
                $productData[11] = null;
                $productData[12] = null;

                $productData[13] = "Product or Service";
                $productData[14] = "Requestd Quantity";
                $productData[15] = "Completed Quantity";
                $productData[16] = "Comment";
                $productData[17] = "Note ($trpBusinessNameAbbreviation tech)";

//                'Products/Services Category',               //12 - M
//                "Requestd Quantity",                        //13
//                "Completed Quantity",                       //14
//                "Comment",                                  //15
//                "Note (TRP tech)"                           //16

                //$writer->addRowWithStyle($data,$footerStyle);
                //$productSpoutRow = WriterEntityFactory::createRowFromArray($productData, $footerStyle);
                //$writer->addRow($productSpoutRow);

                foreach( $productArr as $productInfo ) {
//                    $productInfoArr[] = array(
//                        'productRequested' => $requested,
//                        'productCompleted' => $completed,
//                        'productCategory' => $category,
//                        'productComment' => $comment,
//                        'productNote' => $note
//                    );

                    $thisProductData = array();
                    $thisProductData[0] = null;
                    $thisProductData[1] = null;
                    $thisProductData[2] = null;
                    $thisProductData[3] = null;
                    $thisProductData[4] = null;
                    $thisProductData[5] = null;
                    $thisProductData[6] = null;
                    $thisProductData[7] = null;
                    $thisProductData[8] = null;
                    $thisProductData[9] = null;
                    $thisProductData[10] = null;
                    $thisProductData[11] = null;

                    $thisProductData[12] = $productInfo["productCategory"]; //"Product or Service";
                    $thisProductData[13] = $productInfo["productRequested"]; //"Requestd Quantity";
                    $thisProductData[14] = $productInfo["productCompleted"]; //"Completed Quantity";
                    $thisProductData[15] = $productInfo["productComment"]; //"Comment";
                    $thisProductData[16] = $productInfo["productNote"]; //"Note (TRP tech)";

                    $thisProductSpoutRow = WriterEntityFactory::createRowFromArray($thisProductData, $regularStyle);
                    $writer->addRow($thisProductSpoutRow);

                }

            } else {
                $data[12] = NULL;
                $data[13] = NULL;
                $data[14] = NULL;
                $data[15] = NULL;
                $data[16] = NULL;
            }


            //$writer->addRowWithStyle($data,$regularStyle);
            $spoutRow = WriterEntityFactory::createRowFromArray($data, $regularStyle);
            $writer->addRow($spoutRow);

        }//invoices

        $data = array();
        $data[0] = "Total Number of Work Requests";
        $data[1] = $countRequest;
        $data[2] = null;
        $data[3] = null;
        $data[4] = null;
        $data[5] = null;
        $data[6] = $totalInvoiceFees;
        $data[7] = $countInvoices;
        $data[8] = null;
        $data[9] = null;
        $data[10] = $paidTotal;
        $data[11] = $dueTotal;
        $data[12] = $productServices;
        $data[13] = NULL;
        $data[14] = NULL;
        $data[15] = NULL;
        $data[16] = NULL;
        //$writer->addRowWithStyle($data,$footerStyle);
        $spoutRow = WriterEntityFactory::createRowFromArray($data, $footerStyle);
        $writer->addRow($spoutRow);

        $writer->close();
    }

    public function createWorkRequesterEmails( $ids, $fileName, $limit=null ) {

        //$transresUtil = $this->container->get('transres_util');

        $emails = array();
        $count = 0;
        $totalCount = 0;

        foreach( $ids as $requestId ) {

            if (!$requestId) {
                continue;
            }

            if ($limit && ($count++ > $limit)) {
                break;
            }

            $transResRequest = $this->em->getRepository('AppTranslationalResearchBundle:TransResRequest')->find($requestId);
            if (!$transResRequest) {
                continue;
            }

            $submitter = $transResRequest->getSubmitter();
            if ($submitter) {
                $email = $submitter->getSingleEmail();
                if( $email ) {
                    $emails[$submitter->getId()] = $email;
                    $totalCount++;
                }
            }

        }

        if( count($emails) > 0 ) {
            $emailsStr = implode("; ", $emails);

            echo count($emails)." requester's emails from $totalCount work requests: <br><br>";

            echo $emailsStr;
        }

        exit();
    }

    public function getProductServiceByProjectSpecialty( $projectSpecialty, $project=null ) {

        $repository = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->orderBy("list.orderinlist","ASC");

        $dqlParameters = array();
        
        $dqlParameters["typedef"] = 'default';
        $dqlParameters["typeadd"] = 'user-added';

        //show only with $fee for this price list
        if(1) {
            $feeRestriction = "(list.fee IS NOT NULL)";
            if ($project) {
                $priceList = $project->getPriceList();
                if ($priceList) {
                    $priceListId = $priceList->getId();
                    if ($priceListId) {
                        $dql->leftJoin('list.prices','prices');
                        $dql->leftJoin('prices.priceList','priceList');
                        //$specificFeeRestriction = "(priceList.id = $priceListId AND prices.fee IS NOT NULL AND prices.fee <> '0')";
                        $specificFeeRestriction = "(priceList.id = $priceListId AND prices.fee IS NOT NULL)";
                        $feeRestriction = $feeRestriction . " OR ";
                        $feeRestriction = $feeRestriction . $specificFeeRestriction;
                        //echo $this->priceList.": feeRestriction = $feeRestriction<br>";
                    }
                }
                $dql->andWhere($feeRestriction);
            }
        }

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $fees = $query->getResult();

        //filter by specialty
        $filteredFees = new ArrayCollection();
        if( $projectSpecialty ) {
            //$projectSpecialtyId = $projectSpecialty->getId();
            foreach( $fees as $fee ) {
                $feeSpecialties = $fee->getProjectSpecialties();
                //echo "specialties=".$fee->getProductId()." ".$fee->getProjectSpecialtiesStr()."<br>";
                if( !$feeSpecialties->contains($projectSpecialty) ) {
                    //echo "specialties=".$fee->getProductId()." ".$fee->getProjectSpecialtiesStr()."<br>";
                    $filteredFees[] = $fee;
                }
            }

            return $filteredFees;
        }

        return $fees;
    }
    public function getProductServiceByProjectSpecialty_ORIG($projectSpecialty,$asCombobox=true,$max=3) {

        $repository = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->orderBy("list.orderinlist","ASC");

        $dqlParameters = array();

        $dqlParameters["typedef"] = 'default';
        $dqlParameters["typeadd"] = 'user-added';

        if( $projectSpecialty ) {
            if(0) {
                $dql->leftJoin('list.projectSpecialties','projectSpecialties');
                //$dql->andWhere("projectSpecialties.id IN (:projectSpecialtyIdsArr)");       //show categories with this specialty only
                //$dql->andWhere("projectSpecialties.id IS NULL OR projectSpecialties.id NOT IN (:projectSpecialtyIdsArr)"); //do show categories with this specialty only
                $dql->andWhere("projectSpecialties IS NULL OR projectSpecialties.id NOT IN (:projectSpecialtyIdsArr)");
                $projectSpecialtyIdsArr = array();
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
                $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
            } else {
//                //$dql->innerJoin('list.projectSpecialties','projectSpecialties');
//                $dql->leftJoin('list.projectSpecialties','projectSpecialties');
//                //$dql->andWhere("projectSpecialties IS NULL OR projectSpecialties.id NOT IN (:projectSpecialtyId)");
//                //$dql->andWhere("projectSpecialties IS NULL OR projectSpecialties.id NOT IN (:projectSpecialtyId)");
//
//                $dql->groupBy("list");
//                $dql->groupBy("projectSpecialties");
//                $dql->having("projectSpecialties.id != 5");
//
//                $projectSpecialtyIdsArr = array();
//                $projectSpecialtyId = $projectSpecialty->getId();
//                $projectSpecialtyIdsArr[] = $projectSpecialtyId;
//                //$dqlParameters["projectSpecialtyId"] = $projectSpecialtyId;

                $inverseProjectSpecialtyIdsArr = array();
                $inverseProjectSpecialtys = $this->getReversedSpecialties($projectSpecialty);
                foreach($inverseProjectSpecialtys as $inverseProjectSpecialty) {
                    echo "$inverseProjectSpecialty <br>";
                    $inverseProjectSpecialtyIdsArr[] = $inverseProjectSpecialty->getId();
                }

                $dql->leftJoin('list.projectSpecialties','projectSpecialties');
                $dql->andWhere("projectSpecialties IS NULL OR projectSpecialties.id != 5");
                //$projectSpecialtyIdsArr = array();
                //$projectSpecialtyIdsArr[] = $projectSpecialty->getId();
                //$dqlParameters["inverseProjectSpecialtyIdsArr"] = $inverseProjectSpecialtyIdsArr;
            }
        }

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        if( $max ) {
            $query->setMaxResults($max);
        }

        $products = $query->getResult();

        if( !$asCombobox ) {
            return $products;
        }

        $productsCombobox = array();
        foreach($products as $product) {
            if( $asCombobox ) {
                $productsCombobox[] = array('id'=>$product->getId(),'text'=>$product->getOptimalAbbreviationName());
            }
        }

        return $productsCombobox;
    }
//    public function getProductServiceByProjectSpecialtyTest($projectSpecialty) {
//        $dqlParameters = array();
//
//        $repository = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList');
//        $dql =  $repository->createQueryBuilder("list");
//        $dql->select('list');
//
//        $dql->orderBy("list.orderinlist","ASC");
//
//        //$dql->innerJoin('list.projectSpecialties','projectSpecialties');
//        $dql->leftJoin('list.projectSpecialties','projectSpecialties');
//        //$dql->andWhere("projectSpecialties IS NULL OR projectSpecialties.id NOT IN (:projectSpecialtyId)");
//        $dql->andWhere("projectSpecialties IS NULL OR projectSpecialties.id IN (:projectSpecialtyId)");
//
//        //$dql->groupBy("list");
//        //$dql->groupBy("list.id, projectSpecialties.id");
//        //$dql->having("projectSpecialties.id IN (5)");
//
//        //$dql->where("NOT EXISTS(projectSpecialties.id NOT IN (:projectSpecialtyId))");
//
//        $projectSpecialtyIdsArr = array();
//        $projectSpecialtyId = $projectSpecialty->getId();
//        $projectSpecialtyIdsArr[] = $projectSpecialtyId;
//        $dqlParameters["projectSpecialtyId"] = $projectSpecialtyId;
//
//        $query = $this->em->createQuery($dql);
//
//        if( count($dqlParameters) > 0 ) {
//            $query->setParameters($dqlParameters);
//        }
//
//        $products = $query->getResult();
//        return $products;
//    }
//    public function getProductServiceByProjectSpecialtyTest2($projectSpecialty = null)
//    {
//        $projectSpecialtyId = $projectSpecialty->getId();
//
//        $repository1 = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList');
//        //$qb = $this->createQueryBuilder('u');
//        $qb = $repository1->createQueryBuilder("list");
//        $qb->orderBy("list.orderinlist","ASC");
//
//        //Create subquery
//        $repository2 = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList');
//        $sub = $repository2->createQueryBuilder('s');
//        $sub = $sub->innerJoin('s.requestCategories', 'requestCategories')
//            ->where("requestCategories.id = list.id")
//            ->andWhere("requestCategories.id IN ($projectSpecialtyId)")
//        ;
//
//        $qb->andWhere($qb->expr()->not($qb->expr()->exists($sub->getDQL())));
//
//        return $qb->getQuery()->getResult();
//    }
//    public function getProductServiceByProjectSpecialtyTest3($projectSpecialty = null)
//    {
//        $projectSpecialtyId = $projectSpecialty->getId();
//
//        $repository1 = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList');
//        $qb = $repository1->createQueryBuilder("s");
//
//        //Create subquery
//        $repository2 = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList');
//        $sub = $repository2->createQueryBuilder('list');
//        $sub = $sub->innerJoin('list.projectSpecialties', 'projectSpecialties')
//            ->where("list.id = projectSpecialties.id")
//            ->andWhere("projectSpecialties.id != $projectSpecialtyId")
//        ;
//        $sub->orderBy("list.orderinlist","ASC");
//
//        $qb->andWhere($qb->expr()->not($qb->expr()->exists($sub->getDQL())));
//
//        return $sub->getQuery()->getResult();
//    }

//    public function getReversedSpecialties($projectSpecialty) {
//        $dqlParameters = array();
//        $repository = $this->em->getRepository('AppTranslationalResearchBundle:SpecialtyList');
//        $dql =  $repository->createQueryBuilder("list");
//        $dql->select('list');
//
//        $dql->where("list.id != :projectSpecialtyId");
//
//        $projectSpecialtyId = $projectSpecialty->getId();
//        $dqlParameters["projectSpecialtyId"] = $projectSpecialtyId;
//
//        $query = $this->em->createQuery($dql);
//
//        if( count($dqlParameters) > 0 ) {
//            $query->setParameters($dqlParameters);
//        }
//
//        $specialties = $query->getResult();
//
//        return $specialties;
//    }

    public function getFeeSchedule( $transresRequest ) {

        $repository = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->orderBy("list.orderinlist","ASC");

        $dqlParameters = array();

        $dqlParameters["typedef"] = 'default';
        $dqlParameters["typeadd"] = 'user-added';

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $categories = $query->getResult();

        $priceList = $transresRequest->getPriceList();

        $productsArr = array();
        foreach($categories as $category) {

            $categoryName = $category->getName();

            $initialQuantityDefault = $category->getPriceInitialQuantity(NULL);
            $initialFeeDefault = $category->getPriceFee(NULL);
            $additionalFeeDefault = $category->getPriceFeeAdditionalItem(NULL);
            $categoryItemCodeDefault = $category->getProductId(NULL);

            $initialQuantity = $category->getPriceInitialQuantity($priceList);
            $initialFee = $category->getPriceFee($priceList);
            $additionalFee = $category->getPriceFeeAdditionalItem($priceList);
            $categoryItemCode = $category->getProductId($priceList);

            //if( $initialFeeDefault ) {

                $productsArr[$category->getId()] = array(
                    'id' => $category->getId(),
                    //'text'=>$category->getOptimalAbbreviationName(),
                    'name' => $category->getName(),

                    'initialQuantityDefault' => $initialQuantityDefault,
                    'initialFeeDefault' => $initialFeeDefault,
                    'additionalFeeDefault' => $additionalFeeDefault,
                    'categoryItemCodeDefault' => $categoryItemCodeDefault,

                    'initialQuantity' => $initialQuantity,
                    'initialFee' => $initialFee,
                    'additionalFee' => $additionalFee,
                    'categoryItemCode' => $categoryItemCode,
                );

            //}
        }

//        $productsJson = NULL;
//        if( count($productsArr) > 0 ) {
//            $productsJson = json_encode($productsArr);
//        }
//        return $productsJson;

        return $productsArr;
    }

    public function getOneValidFeeScheduleByProductId( $productId ) {

        $repository = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->andWhere("list.productId = :productId");

        $dql->orderBy("list.orderinlist","ASC");

        $dqlParameters = array();

        $dqlParameters["typedef"] = 'default';
        $dqlParameters["typeadd"] = 'user-added';
        $dqlParameters["productId"] = $productId;

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $categories = $query->getResult();

        $category = NULL;
        if( count($categories) > 0 ) {
            $category = $categories[0];
        }

        return $category;
    }

    public function getInvoiceItemInfoHtml( $invoiceItem ) {
        $transresUtil = $this->container->get('transres_util');
        $row = "";
        $priceList = NULL;

        //description
        $maxLen = 60;
        $descriptionStr = $invoiceItem->getDescription();
        if( $descriptionStr && strlen($descriptionStr) > $maxLen ) {
            $descriptionStr = $transresUtil->tokenTruncate($descriptionStr,$maxLen)."...";
        }
        //$row1 = $row1 . "<td>" . $descriptionStr . "</td>";

        //quantity (integer), unitPrice (type="decimal", precision=15, scale=2), additionalUnitPrice (type="decimal", precision=15, scale=2)
        //$secondRaw = false;
        $itemCode = $invoiceItem->getItemCodeWithPriceListAbbreviation(); //getItemCode();

        //limit item code length
        if (strlen($itemCode) > 30) {
            $itemCode = substr($itemCode, 0, 27) . '...';
        }

        $quantity = $invoiceItem->getQuantity();
        $additionalQuantity = $invoiceItem->getAdditionalQuantity();
        $unitPrice = $this->toDecimal($invoiceItem->getUnitPrice());
        $additionalUnitPrice = $this->toDecimal($invoiceItem->getAdditionalUnitPrice());

        //echo "quantity=$quantity, additionalQuantity=$additionalQuantity, unitPrice=$unitPrice, additionalUnitPrice=$additionalUnitPrice <br>";

        //if( $quantity > 1 ) {
//        if( $additionalQuantity > $quantity ) {
//            if( $unitPrice != $additionalUnitPrice ) {
//                $secondRaw = true;
//            }
//        }
        $secondRaw = $invoiceItem->hasSecondRaw();
        //echo "secondRaw=$secondRaw <br>";

        if( $secondRaw ) {
            //$quantityFirst = 1;
            //$quantityAdditional = $quantity - 1;
            $quantityFirst = $quantity;
            $quantityAdditional = $additionalQuantity;
            $totalFeeFirst = $this->toDecimal($unitPrice*$quantityFirst);
            $totalFeeAdditional = $this->toDecimal($additionalUnitPrice*$quantityAdditional);

            $useMergeCell = true;
            //$useMergeCell = false;

            if( $useMergeCell ) {

                if( substr_count($itemCode,'-') < 2 ) {
                    //echo 'true';
                    $itemCode = $itemCode . "-";
                }

                $row1 =
                      "<td rowspan='2' style='vertical-align: middle;'>" . $descriptionStr . "</td>"
                    . "<td class='text-center'>" . $quantityFirst . "</td>"
                    . "<td class='text-center'>" . $itemCode . "f" . "</td>"
                    . "<td class='text-right'>" . $unitPrice . "</td>"
                    . "<td class='text-right'>" . $totalFeeFirst . "</td>"
                ;

                $row2 =
                      "<td class='text-center'>" . $quantityAdditional . "</td>"
                    . "<td class='text-center'>" . $itemCode . "a" . "</td>"
                    . "<td class='text-right'>" . $additionalUnitPrice . "</td>"
                    . "<td class='text-right'>" . $totalFeeAdditional . "</td>"
                ;
            } else {
                $row1 =
                    "<td>" . $descriptionStr . "</td>"
                    . "<td class='text-center'>" . $quantityFirst . "</td>"
                    . "<td class='text-center'>" . $itemCode . "f" . "</td>"
                    . "<td class='text-right'>" . $unitPrice . "</td>"
                    . "<td class='text-right'>" . $totalFeeFirst . "</td>"
                ;

                //<img src="{{ asset('orderassets/AppUserdirectoryBundle/form/img/users-1-64x64.png') }}" alt="Employee Directory" height="18" width="18">
                //$imageL = "\\orderassets\\AppTranslationalResearchBundle\\images\\"."branch-char.jpeg";
                $imageL = "\\orderassets\\AppTranslationalResearchBundle\\images\\"."branch-char-arrow.jpg";

                $row2 =
                    //"<td>" . "L" . "</td>" .
                    "<td>" . '<img src="'.$imageL.'" height="18" width="18">' . "</td>" .
                    "<td class='text-center'>" . $quantityAdditional . "</td>"
                    . "<td class='text-center'>" . $itemCode . "a" . "</td>"
                    . "<td class='text-right'>" . $additionalUnitPrice . "</td>"
                    . "<td class='text-right'>" . $totalFeeAdditional . "</td>"
                ;
            }

            $row = "<tr>" . $row1 . "</tr>" . "<tr>" . $row2 . "</tr>";
        } else {
            //always as a single row
            $quantity = $quantity + $additionalQuantity;
            $totalFee = $this->toDecimal($unitPrice*$quantity);
            $row1 =
                  "<td>" . $descriptionStr . "</td>"
                . "<td class='text-center'>" . $quantity . "</td>"
                . "<td class='text-center'>" . $itemCode . "</td>"
                . "<td class='text-right'>" . $unitPrice . "</td>"
                . "<td class='text-right'>" . $totalFee . "</td>"
            ;
            $row = "<tr>" . $row1 . "</tr>";
        }

        return $row;
    }

//    //Calculate subsidy based only on the work request's products.
//    //If invoice is edited manually (products added or removed, price changed, discount applied), subsidy will not be changed.
//    //Used only in getSubsidyInfo($invoice) and
//    public function calculateSubsidyByRequest_ORIG($request) {
//        //$request = $invoice->getTransresRequest();
//        $priceList = $request->getPriceList($request);
//        $subsidy = 0;
//
//        foreach( $request->getProducts() as $product ) {
//
//            //$quantity = $product->getQuantity();
//            //echo "quantity=$quantity <br>";
//
//            //default fee
//            $quantitiesArr = $product->calculateQuantities(NULL);
//            $initialQuantity = $quantitiesArr['initialQuantity'];
//            $additionalQuantity = $quantitiesArr['additionalQuantity'];
//            $initialFee = $quantitiesArr['initialFee'];
//            $additionalFee = $quantitiesArr['additionalFee'];
//
//            $totalDefault = $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
//
//            //special fee
//            $quantitiesArr = $product->calculateQuantities($priceList);
//            $initialQuantity = $quantitiesArr['initialQuantity'];
//            $additionalQuantity = $quantitiesArr['additionalQuantity'];
//            $initialFee = $quantitiesArr['initialFee'];
//            $additionalFee = $quantitiesArr['additionalFee'];
//
//            $totalSpecial = $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
//
//            if( $totalDefault && $totalSpecial && $totalDefault != $totalSpecial ) {
//                //echo "totalDefault=$totalDefault totalSpecial=$totalSpecial <br>";
//                $diff = $this->toDecimal($totalDefault - $totalSpecial);
//
////                if( $diff > 0 ) {
////                    $subsidy = $subsidy + $diff;
////                }
//
//                //subsidy can be negative. Show negative subsidy only to admin
//                $subsidy = $subsidy + $diff;
//            }
//
//        }
//
//        $subsidy = $this->toDecimal($subsidy);
//        //echo "res subsidy=$subsidy <br>";
//
//        return $subsidy;
//    }
//    //Calculate subsidy based only on the work request's products.
//    //If invoice is edited manually (products added or removed, price changed, discount applied), subsidy will not be changed.
//    //Used only in getSubsidyInfo($invoice) and
//    public function calculateSubsidyByRequest($request) {
//        return $request->calculateSubsidyByRequest();
//    }

//    public function calculateDefaultTotalByInvoice($invoice) {
//        $transresRequest = $invoice->getTransresRequest();
//        return $transresRequest->calculateDefaultTotalByRequest();
//    }
//    public function calculateDefaultTotalByRequest_ORIG($transresRequest) {
//        $totalDefault = 0;
//
//        foreach( $transresRequest->getProducts() as $product ) {
//
//            //default
//            $quantitiesArr = $product->calculateQuantities(NULL);
//            $initialQuantity = $quantitiesArr['initialQuantity'];
//            $additionalQuantity = $quantitiesArr['additionalQuantity'];
//            $initialFee = $quantitiesArr['initialFee'];
//            $additionalFee = $quantitiesArr['additionalFee'];
//            $totalDefault = $totalDefault + $this->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
//        }
//
//        $totalDefault = $this->toDecimal($totalDefault);
//
//        return $totalDefault;
//    }
//    public function calculateDefaultTotalByRequest($transresRequest) {
//        return $transresRequest->calculateDefaultTotalByRequest();
//    }

    //Used on the invoice new/edit, and pdf page
    //"[Internal pricing] has been used to generate this invoice. Subsidy: $[XX.XX]"
    public function getSubsidyInfo($invoice,$showNegativeSubsidy=true) {
        $res = "";
        $request = $invoice->getTransresRequest();
        //$priceList = $request->getPriceList($request);
        $priceList = $request->getPriceList();

        $subsidy = $invoice->getSubsidy();
        //echo "Invoice subsidy=".$subsidy."<br>";
        if( !$subsidy ) {
            //$subsidy = $this->calculateSubsidyByRequest($request);
            $subsidy = $request->calculateSubsidyByRequest();
            //echo "Calculate subsidy=".$subsidy."<br>";
        }
        //$showNegativeSubsidy = true; //testing
        //$subsidy = -100000000.98; //testing
        //echo "Final subsidy=".$subsidy."<br>";

        if( $priceList ) {
            //This invoice utilizes internal pricing
            $priceListName = $priceList->getName();
            $res = "This invoice utilizes " . strtolower($priceListName).".";
        }

        $showSubsidy = false;
        if( $showNegativeSubsidy ) {
            if( $subsidy > 0 ) {
                $showSubsidy = true;
            } else {
                //negative subsidy: additional check if admin or technician
                $transresRequestUtil = $this->container->get('transres_request_util');
                if( $transresRequestUtil->isUserHasInvoicePermission($invoice, "update") ) {
                    $showSubsidy = true;
                }
            }
        } else {
            if( $subsidy > 0 ) {
                $showSubsidy = true;
            }
        }

        //if( $showNegativeSubsidy || (!$showNegativeSubsidy && $subsidy > 0) ) {
        if( $showSubsidy ) {

            //If the price difference is equal to zero or below, DO NOT show the “Subsidy: $[XX.XX]” portion
            //This invoice utilizes internal pricing. Total subsidy: $[XX.XX]
            //$res = $priceList->getName()." has been used to generate this invoice. Subsidy: $".$subsidy;

            //$-20
//            $subsidy = number_format($subsidy, 2, '.', ',');
//            $res = $res . " Total subsidy: $" .
//                "<span id='invoice-subsidy-info'>" . $subsidy . "</span>";

            //-$20
            if( $subsidy >= 0 ) {
                //$20,000.98
                $subsidy = number_format($subsidy, 2, '.', ',');
                $res = $res . " Total subsidy: " .
                    "<span id='invoice-subsidy-info'>$" . $subsidy . "</span>";
            } else {
                //-$20,000.98
                $subsidy = abs($subsidy);
                $subsidy = number_format($subsidy, 2, '.', ',');
                $res = $res . " Total subsidy: " .
                    "<span id='invoice-subsidy-info'>-$" . $subsidy . "</span>";
            }
        }
        
        return $res;
    }

//    //Calculate subsidy based only on the invoice's invoiceItem.
//    //Used only in updateInvoiceSubsidy($invoice)
//    public function calculateSubsidyInvoiceItems_ORIG($invoice) {
//        $request = $invoice->getTransresRequest();
//
//        $subsidy = 0;
//
//        $totalInvoiceDefault = 0;
//        $totalInvoiceFinal = $invoice->getTotal();
//        $invoiceItems = $invoice->getInvoiceItems();
//
//        foreach( $invoiceItems as $invoiceItem ) {
//
//            $initialQuantity = $invoiceItem->getQuantity();                 //initial Quantity
//            $additionalQuantity = $invoiceItem->getAdditionalQuantity();    //additional Quantity
//            $itemCode = $invoiceItem->getItemCode();
//            //$unitPrice = $invoiceItem->getUnitPrice();
//            //$additionalUnitPrice = $invoiceItem->getAdditionalUnitPrice();
//            $total = $invoiceItem->getTotal();
//            $category = NULL;
//
//            //remove -i from itemCode "TRP-0001-i"
//            if( strpos($itemCode, '-') !== false ) {
//                $itemCodeArr = explode('-',$itemCode);
//                if( count($itemCodeArr) > 2 ) {
//                    $itemCode = $itemCodeArr[0].$itemCodeArr[1];
//                }
//            }
//
//            //try to fnd category by
//            $product = $invoiceItem->getProduct();
//
//            if( $product ) {
//                //echo "product=".$product."<br>";
//                $category = $product->getCategory();
//            }
//
//            if( !$category ) {
//                //echo "NULL category: itemCode=".$itemCode."<br>";
//                //try to find category by itemCode
//                //$category = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->findOneByProductId($itemCode);
//                $category = $this->getOneValidFeeScheduleByProductId($itemCode);
//                //echo "found category=[".$category."] by itemCode=$itemCode"."<br>";
//
//                //create and add product to InvoiceItem without Product by ItemCode
//                if( !$invoiceItem->getProduct() ) {
//                    $this->createAndAddProductToInvoiceItemByItemCode($invoiceItem, $category);
//                }
//            }
//
//            if( $category ) {
//
//                //default quantity
//                //$initialQuantity = $category->getInitialQuantity();
//
//                //default fee
//                $fee = $category->getPriceFee();
//                $feeAdditionalItem = $category->getPriceFeeAdditionalItem();
//                //$totalDefault = $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$initialQuantity,$additionalQuantity);
//                $totalDefault = $request->getTotalFeesByQuantity($fee,$feeAdditionalItem,$initialQuantity,$additionalQuantity);
//
//                if( !$totalDefault ) {
//                    $totalDefault = $total;
//                }
//
//                $totalInvoiceDefault = $totalInvoiceDefault + $totalDefault;
//                echo "category $itemCode default total=".$totalDefault.": totalInvoiceDefault=[$totalInvoiceDefault]<br>";
//
//            } else {
//                $totalInvoiceDefault = $totalInvoiceDefault + $total;
//                echo "invoice item total=".$total.": totalInvoiceDefault=[$totalInvoiceDefault]<br>";
//            }
//
//        }//foreach
//
//        if( $totalInvoiceDefault && $totalInvoiceFinal ) {
//            echo "calculate subsidy: totalInvoiceDefault=[$totalInvoiceDefault] - totalInvoiceFinal=[$totalInvoiceFinal]<br>";
//            $subsidy = $totalInvoiceDefault - $totalInvoiceFinal;
//        }
//
//        //Subsidy does not include administrative fee
//        $administrativeFee = $invoice->getAdministrativeFee();
//        echo "before admin fee: invoice subsidy=[$subsidy]<br>";
//        if( $administrativeFee ) {
//            $subsidy = $subsidy + $administrativeFee;
//            echo "after admin fee: invoice subsidy=[$subsidy]<br>";
//        }
//
//        $subsidy = $this->toDecimal($subsidy);
//
//        return $subsidy;
//    }
    //Calculate subsidy based only on the invoice's invoiceItem.
    //Used only in updateInvoiceSubsidy($invoice)
    //Calculate default only based on the invoice items with category (with existing fee schedule) applying discount
    public function calculateSubsidyInvoiceItems($invoice) {
        $transresRequest = $invoice->getTransresRequest();
        $priceList = $transresRequest->getPriceList();

        $subsidy = 0;

        $totalInvoiceDefault = 0;
        $totalClean = 0; //clean total - invoice item total: only items with existing fee schedule (existing category)
        $totalInvoiceFinal = $invoice->getTotal();
        $invoiceItems = $invoice->getInvoiceItems();

        $discountNumeric = $invoice->getDiscountNumeric();
        $discountPercent = $invoice->getDiscountPercent();
        //$administrativeFee = $invoice->getAdministrativeFee();

        foreach( $invoiceItems as $invoiceItem ) {

            $initialQuantity = $invoiceItem->getQuantity();                 //initial Quantity
            $additionalQuantity = $invoiceItem->getAdditionalQuantity();    //additional Quantity
            $itemCode = $invoiceItem->getItemCode();
            //$unitPrice = $invoiceItem->getUnitPrice();
            //$additionalUnitPrice = $invoiceItem->getAdditionalUnitPrice();
            $total = $invoiceItem->getTotal();
            $category = NULL;

            //remove -i from itemCode "TRP-0001-i"
            if( strpos($itemCode, '-') !== false ) {
                $itemCodeArr = explode('-',$itemCode);
                if( count($itemCodeArr) > 2 ) {
                    $itemCode = $itemCodeArr[0].$itemCodeArr[1];
                }
            }

            //try to fnd category by
            $product = $invoiceItem->getProduct();

            if( $product ) {
                //echo "product=".$product."<br>";
                $category = $product->getCategory();
            }

            if( !$category ) {
                //echo "NULL category: itemCode=".$itemCode."<br>";
                //try to find category by itemCode
                //$category = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->findOneByProductId($itemCode);
                $category = $this->getOneValidFeeScheduleByProductId($itemCode);
                //echo "found category=[".$category."] by itemCode=$itemCode"."<br>";

                //create and add product to InvoiceItem without Product by ItemCode
                if( !$invoiceItem->getProduct() ) {
                    $this->createAndAddProductToInvoiceItemByItemCode($invoiceItem, $category);
                }
            }

            if( $category ) {

                //default quantity
                $quantitiesArr = $product->calculateQuantities($priceList);
                $initialQuantity = $quantitiesArr['initialQuantity'];
                $additionalQuantity = $quantitiesArr['additionalQuantity'];
                $initialFee = $quantitiesArr['initialFee'];
                $additionalFee = $quantitiesArr['additionalFee'];

                //echo "units=$units; fee=$fee <br>";
                if( $initialFee && $initialQuantity ) {
                    $totalClean = $totalClean + $transresRequest->getTotalFeesByQuantity($initialFee,$additionalFee,$initialQuantity,$additionalQuantity);
                }

                //default fee
                $fee = $category->getPriceFee();
                $feeAdditionalItem = $category->getPriceFeeAdditionalItem();
                //$totalDefault = $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$initialQuantity,$additionalQuantity);
                $totalDefault = $transresRequest->getTotalFeesByQuantity($fee,$feeAdditionalItem,$initialQuantity,$additionalQuantity);

                if( !$totalDefault ) {
                    $totalDefault = $total;
                }

                $totalInvoiceDefault = $totalInvoiceDefault + $totalDefault;
                //echo "category $itemCode default total=".$totalDefault.": totalInvoiceDefault=[$totalInvoiceDefault]<br>";

            } else {
                //$totalInvoiceDefault = $totalInvoiceDefault + $total;
                //echo "invoice item total=".$total.": totalInvoiceDefault=[$totalInvoiceDefault]<br>";
            }

        }//foreach

//        if( $totalInvoiceDefault && $totalInvoiceFinal ) {
//            echo "calculate subsidy: totalInvoiceDefault=[$totalInvoiceDefault] - totalInvoiceFinal=[$totalInvoiceFinal]<br>";
//            $subsidy = $totalInvoiceDefault - $totalInvoiceFinal;
//        }

        if( $totalClean ) {
            if( $discountNumeric ) {
                $discount = (float)$discountNumeric;
            }
            if( $discountPercent ) {
                $discount = $totalClean * ((float)$discountPercent/100);
            }

            $totalClean = (float)$totalClean - (float)$discount;
        }
        if( $totalInvoiceDefault && $totalClean ) {
            //echo "calculate subsidy: totalInvoiceDefault=[$totalInvoiceDefault] - totalClean=[$totalClean]<br>";
            $subsidy = (float)$totalInvoiceDefault - (float)$totalClean;
        }

//        //Subsidy does not include administrative fee
//        $administrativeFee = $invoice->getAdministrativeFee();
//        echo "before admin fee: invoice subsidy=[$subsidy]<br>";
//        if( $administrativeFee ) {
//            $subsidy = $subsidy + $administrativeFee;
//            echo "after admin fee: invoice subsidy=[$subsidy]<br>";
//        }

        $subsidy = $this->toDecimal($subsidy);

        return $subsidy;
    }
    //Used when creating new invoice (via createSubmitNewInvoice), updating invoice (via edit or update-invoice-ajax)
    public function updateInvoiceSubsidy($invoice) {
        $subsidy = $this->calculateSubsidyInvoiceItems($invoice);
        //echo "subsidy=[".$subsidy."]<br>";

//        if( $subsidy > 0 ) {
//            //echo "update subsidy<br>";
//            $invoice->setSubsidy($subsidy);
//        } else {
//            //echo "Don't update subsidy<br>";
//        }

        $invoice->setSubsidy($subsidy);

        //exit("update Invoice Subsidy: subsidy=$subsidy");
        return $subsidy;
    }

    public function createAndAddProductToInvoiceItemByItemCode( $invoiceItem, $category ) {
        if( $invoiceItem->getProduct() ) {
            return $invoiceItem->getProduct();
        }

        //it does not make a sense to create a product without category (fee schedule)
        if( !$category ) {
            return NULL;
        }

        $user = $this->secTokenStorage->getToken()->getUser();

        if( !$invoiceItem->getSubmitter() ) {
            $invoiceItem->setSubmitter($user);
        }

        $product = new Product($user);

        $product->setCategory($category);

        $initialQuantity = $invoiceItem->getQuantity();                 //initial Quantity
        $additionalQuantity = $invoiceItem->getAdditionalQuantity();    //additional Quantity

        if( $initialQuantity === NULL ) {
            $initialQuantity = 0;
        }
        if( $additionalQuantity === NULL ) {
            $additionalQuantity = 0;
        }

        $totalQuantity = $initialQuantity + $additionalQuantity;

        $product->setRequested($totalQuantity);
        $product->setCompleted($totalQuantity);

        $description = $invoiceItem->getDescription();

        if( $description ) {
            $product->setNote($description);
            $product->setComment($description);
        }

        $invoiceItem->setProduct($product);
        $this->em->persist($product);

        return $product;
    }

    public function getInvoiceSubsidy( $invoice ) {
        $subsidy = $invoice->getSubsidy();

        $showSubsidy = false;

        if( $subsidy > 0 ) {
            $showSubsidy = true;
        } else {
            //negative subsidy: additional check if admin or technician
            $transresRequestUtil = $this->container->get('transres_request_util');
            if( $transresRequestUtil->isUserHasInvoicePermission($invoice, "update") ) {
                $showSubsidy = true;
            }
        }

        if( $showSubsidy ) {
            $subsidy = $this->toDecimal($subsidy);
        } else {
            $subsidy = 0.00;
        }

        return $subsidy;
    }
    
    public function getInvoiceTotalWithSubsidy($invoice) {
        $total = $invoice->getTotal();
        $subsidy = $this->getInvoiceSubsidy($invoice);
        $grandTotal = (float)$total + (float)$subsidy;
        $grandTotal = $this->toDecimal($grandTotal);
        return $grandTotal;
    }

    public function exportUnpaidInvoices( $idsArr, $template ) {

        $colArr = array(
            "Counter",
            "Company Code",
            "GL Account",
            "Debit Amount",
            "Credit Amount",
            "Fund",
            "WBS",
            "WBS Exp - Original Doc Number",
            "WBS Exp - Reason for 90 days past original Expense",
            "Internal Order",
            "Personnel No",
            "Text"
        );

        //load spreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template);

        //Calculation::getInstance($spreadsheet)->disableCalculationCache();
        //Calculation::getInstance($spreadsheet)->clearCalculationCache();

//        $spreadsheet->getSecurity()->setLockWindows(true);
//        $spreadsheet->getSecurity()->setLockStructure(true);
//        $spreadsheet->getSecurity()->setWorkbookPassword('secret');
//        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
//        $spreadsheet->getActiveSheet()->getProtection()->setSort(true);
//        $spreadsheet->getActiveSheet()->getProtection()->setInsertRows(true);
//        $spreadsheet->getActiveSheet()->getProtection()->setFormatCells(true);

        //change it
        $sheet = $spreadsheet->getActiveSheet();
        //$sheet->setCellValue('A1', 'New Value');

        //Somehow column M is not calculated.
        //To enable the formula in column M, overwrite style in column M by style from column N.
        $spreadsheet->getActiveSheet()
            ->duplicateStyle(
                $spreadsheet->getActiveSheet()->getStyle('N9'),
                'M9:M958'
            );

        $userServiceUtil = $this->container->get('user_service_utility');
        $user = $this->secTokenStorage->getToken()->getUser();
        //get column index
        $headerRowIndex = 7;
        $highestColumn = $sheet->getHighestColumn(); //AD
        //echo "highestColumn=$highestColumn <br>";
        $rowData = $sheet->rangeToArray(
            'A' . $headerRowIndex . ':' . $highestColumn . $headerRowIndex,
            NULL, // Value that should be returned for empty cells
            TRUE, // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
            FALSE // Should values be formatted (the equivalent of getFormattedValue() for each cell)
                  //TRUE - Should the array be indexed by cell row and cell column
        );

        $colIndexArr = $this->generateColIndexArrayFromRow($rowData,$highestColumn,$colArr);
        //dump($colIndexArr);
        //exit('111');

        $totalDue = 0;
        $piArr = array();
        $projectIdArr = array();

        //Start row
        $row = 9;

        $replacedColumn = NULL;
        //$replacedColumn = 'O';
        //$replacedColumn = 'M';

        $manuallyPopulateM = false;
        //$manuallyPopulateM = true;

        $overwriteFormula = false;
        //$overwriteFormula = true;

        $testing = false;
        //$testing = true;

        foreach( $idsArr as $invoiceId ) {
            if( !$invoiceId ) {
                continue;
            }

            $invoice = $this->em->getRepository('AppTranslationalResearchBundle:Invoice')->find($invoiceId);
            if( !$invoice ) {
                continue;
            }

            if( $invoice->getLatestVersion() !== true ) {
                continue;
            }

            if( $invoice->getStatus() != 'Unpaid/Issued' && $invoice->getStatus() != 'Paid Partially' ) {
                continue;
            }

            $pi = $invoice->getPrincipalInvestigator();
            if( $pi ) {
                $piArr[$pi->getUsernameShortest()] = $pi->getUsernameShortest();
            }

            if(0) {
                $transresRequest = $invoice->getTransresRequest();
                if ($transresRequest) {
                    $project = $transresRequest->getProject();
                    if ($project) {
                        $projectIdArr[] = $project->getOid();
                    }
                }
            }


            /////////// 1 row: GL Account = 700031 ////////////

            //Company Code
            $col = $colIndexArr['Company Code']; //1 - B
            $cell = $sheet->getCellByColumnAndRow($col,$row);
            $cell->setValue('WCMC');

            //GL Account = 700031
            $col = $colIndexArr['GL Account']; //2
            $cell = $sheet->getCellByColumnAndRow($col,$row);
            $cell->setValue("700031");

            //Debit Amount
            $col = $colIndexArr['Debit Amount'];
            $cell = $sheet->getCellByColumnAndRow($col,$row);
            $due = $invoice->getDue();
            if( $due ) {
                $due = $this->toDecimal($due);
                $cell->setValue($due);
            }

            //WBS (fundedAccountNumber)
//            $col = $colIndexArr['WBS'];
//            $cell = $sheet->getCellByColumnAndRow($col,$row);
//            $wbs = $invoice->getFundedAccountNumber();
//            if( $wbs ) {
//                $cell->setValue($wbs);
//            }
            //If the fund number length is 8 => F column (Fund), if 10 => G column (WBS)
            $accountNumber = $invoice->getFundedAccountNumber();
            if( $accountNumber ) {
                $accountNumberLength = strlen($accountNumber);
                if( $accountNumberLength == 8 ) {
                    $col = $colIndexArr['Fund'];
                    $cell = $sheet->getCellByColumnAndRow($col, $row);
                    $cell->setValue($accountNumber);
                } else {
                    $col = $colIndexArr['WBS'];
                    $cell = $sheet->getCellByColumnAndRow($col,$row);
                    $cell->setValue($accountNumber);
                }
            }

            //TEXT (invoice ID)
            $col = $colIndexArr['Text'];
            $cell = $sheet->getCellByColumnAndRow($col,$row);
            $text = $invoice->getOid();
            if( $text ) {
                $cell->setValue($text);
            }

            //Set the first digit of the account manually
            if($manuallyPopulateM) {
                $cell = $sheet->getCell('M'.$row);
                $cell->setValue(7);
                //$cell->setCalculatedValue(7);
            }
            if( $overwriteFormula ) {
                $cell = $sheet->getCell('N'.$row);

                //M: =IF(MID(C9,1,1)="9",MID(C9,1,2),MID(C9,1,1))
                //N: =IF(ISNUMBER(D9),D9,E9*-1)

                //E4: =SUMIF(M9:M958,"5",N9:N958)
                //E5: =SUMIF(M9:M958,"7",N9:N958)

                $cell = $sheet->getCell('N'.$row);
                $cell->setValue('=SUMIF(MID(C'.$row.',1,1),"5",N9:N958)');
            }
            if(0) {
                //M9:=IF(MID(C9,1,1)="9",MID(C9,1,2),MID(C9,1,1))
                $cell = $sheet->getCell('M'.$row);
                //$cell->setValue('=IF(MID(C'.$row.',1,1)="9",MID(C'.$row.',1,2),MID(C'.$row.',1,1))');
                //$cell->setValue("=IF(MID(C$row,1,1)='9',MID(C'.$row.',1,2),MID(C$row,1,1))");
                //$cell->setValue('=SUM(D1:D2)');
                $cell->setCalculatedValue(7);
            }
            //Somehow column M is not calculated.
            //Solution: Create new column 'O' instead of 'M'. Use 'O' instead of 'M' in calculation E4, E5, H4, H5
            if($replacedColumn) {
                $cell = $sheet->getCell($replacedColumn.$row);
                //$cell->setValue("=IF(MID(C$row,1,1)='9',MID(C'.$row.',1,2),MID(C$row,1,1))");
                $cell->setValue("=SUM(C9:C11)");
                //$cell->setValue('{IF(MID(C9,1,1)="9",MID(C9,1,2),MID(C9,1,1))}');
            }

            $row++;
            /////////// EOF 1 row: GL Account = 700031 ////////////



            /////////// 2 row: GL Account = 500031 ///////////

            //Company Code
            $col = $colIndexArr['Company Code'];
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $cell->setValue('WCMC');

            //GL Account = 500031
            $col = $colIndexArr['GL Account'];
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $cell->setValue("500031");

            //Credit Amount
            $col = $colIndexArr['Credit Amount'];
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $due = $invoice->getDue();
            if ($due) {
                $due = $this->toDecimal($due);
                $cell->setValue($due);
                $totalDue = $totalDue + $due;
            }

            //Fund -  please request JV fund transfer to TRP account 61211820
            $col = $colIndexArr['Fund'];
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $cell->setValue("61211820");

            //TEXT (invoice ID)
            $col = $colIndexArr['Text'];
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $text = $invoice->getOid();
            if ($text) {
                $cell->setValue($text);
            }

            //Set the first digit of the account manually
            if($manuallyPopulateM) {
                $cell = $sheet->getCell('M'.$row);
                $cell->setValue(5);
                //$cell->setCalculatedValue(5);
            }
            if( $overwriteFormula ) {
                $cell = $sheet->getCell('N'.$row);

                //M: =IF(MID(C9,1,1)="9",MID(C9,1,2),MID(C9,1,1))
                //N: =IF(ISNUMBER(D9),D9,E9*-1)

                //E4: =SUMIF(M9:M958,"5",N9:N958)
                //E5: =SUMIF(M9:M958,"7",N9:N958)

                $cell = $sheet->getCell('N'.$row);
                //$cell->setValue('=SUMIF(MID(C'.$row.',1,1),"5",N9:N958)');
                $cell->setValue('=SUMIF(MID(C9,1,1),"5",N9:N958)');
            }

            //Somehow column M is not calculated.
            //Solution: Create new column 'O' instead of 'M'. Use 'O' instead of 'M' in calculation E4, E5, H4, H5
            if($replacedColumn) {
                $cell = $sheet->getCell($replacedColumn.$row);
                $cell->setValue("=IF(MID(C$row,1,1)='9',MID(C'.$row.',1,2),MID(C$row,1,1))");
            }

            $row++;
            /////////// EOF 2 row: GL Account = 500031 ///////////

        } //foreach $invoiceId

        if($testing) {
            $cell = $sheet->getCell('C9');
            echo "C9:".$cell->getValue()."<br>"; //700031

            //=MID("apple",2,3) returns "ppl".
            // 1 2 3 4 5
            // a p p l e

            $cell = $sheet->getCell('M9');
            //$cell->setValue('=IF(MID(C9,1,1)="9",MID(C9,1,2),MID(C9,1,1))');
            $cell->setValue('=MID(C9,1,1)');
            //$sheet->setCellValue('M9','=MID(C9,1,1)');
            //$cell->setValue('{IF(ISNUMBER(D9),D9,E9*-1)}');
            //$cell = $sheet->getCell('M9');
            echo "M9:".$cell->getValue()."=>".$cell->getCalculatedValue()."<br>";  //=IF(MID(C9,1,1)="9",MID(C9,1,2),MID(C9,1,1))
            //echo "M9:".$cell->getValue()."<br>";            //=IF(MID(C9,1,1)="9",MID(C9,1,2),MID(C9,1,1))
            //$style = $cell->getStyle();
            //echo "style=$style <br>";
            //dump($style);

            $cell = $sheet->getCell('N9');
            $cell->setValue('=MID(C9,1,1)');
            echo "N9:".$cell->getValue()."=>".$cell->getCalculatedValue()."<br>";  //N9:90
            //echo "N9:".$cell->getValue()."<br>";            //N9:=IF(ISNUMBER(D9),D9,E9*-1)

            //$style = $cell->getStyle();
            //echo "style=$style <br>";
            //dump($style);

            exit('111');
        }

        //Somehow column M is not calculated.
        //Solution: Create new column 'O' instead of 'M'. Use 'O' instead of 'M' in calculation E4, E5, H4, H5
        if($replacedColumn) {
            //Total 5xxxxx
            $cell = $sheet->getCell('E4');
            $cell->setValue('=SUMIF('.$replacedColumn.'9:'.$replacedColumn.'958,"5",N9:N958)');

            //Total 7xxxxx
            $cell = $sheet->getCell('E5');
            $cell->setValue('=SUMIF('.$replacedColumn.'9:'.$replacedColumn.'958,"7",N9:N958)');

            //Total 94xxxx
            $cell = $sheet->getCell('H4');
            $cell->setValue('=SUMIF('.$replacedColumn.'9:'.$replacedColumn.'958,"94",N9:N958)');

            //Total 96xxxx
            $cell = $sheet->getCell('H5');
            $cell->setValue('=SUMIF('.$replacedColumn.'9:'.$replacedColumn.'958,"96",N9:N958)');
        }

        //write it again to Filesystem with the same name (=replace)
        //$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        //$writer->save($fileName);

        $piStr = NULL;
        if( count($piArr) > 0 ) {
            $piStr = "-PI-".implode("-",$piArr);
        }

        $projectIds = NULL;
        if( count($projectIdArr) > 0 ) {
            $projectIds = "-Project-".implode("-",$projectIdArr);
        }

        $generatedStr = NULL;
        $now = new \DateTime();
        $dateTimeUser = $userServiceUtil->convertFromUtcToUserTimezone($now,$user);
        $generatedStr = "-Generated-on-".$dateTimeUser->format('m-d-Y \a\t H-i-s');
        $generatedStr = str_replace(" ", "-", $generatedStr);

        //Unpaid-Billing-Summary-PI-FirstName-LastName-Project-ID-ID1-ID2-ID3-Generated-on-MM-DD-YYYY-at-HH-MM-SS.xlsx
        $fileName = "Unpaid-Billing-Summary".$piStr.$projectIds.$generatedStr.".xlsx";
        $fileName = str_replace(" ", "-", $fileName);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');

        // Write file to the browser
        $writer->save('php://output');
        exit();
    }
    public function generateColIndexArrayFromRow($rowData,$highestColumn,$colArr) {
        $colIndexArr = array();
        $colIndex = 0;

        //dump($rowData);
        //exit('111');

        foreach( $rowData[0] as $cell ) {

            foreach($colArr as $colTitle) {
                if( $colTitle ) {
                    if ($cell . "" == $colTitle . "") {
                        //$colArr[$colTitle] = $colIndex;
                        $colIndexArr[$colTitle] = $colIndex+1;
                    } else {
                        //$colArr[$colTitle] = null;
                    }
                }
            }
            $colIndex++;
        }

        //dump($colIndexArr);
        //exit('111');
        return $colIndexArr;
    }

    //$product - work request product
    public function getInvoiceItemInfoByProduct( $product, $cycle=NULL ) {
        $invoiceItem = $this->findInvoiceItemByProduct($product);

        if( !$invoiceItem ) {
            return NULL;
        }
        //echo "invoiceItem Id=".$invoiceItem->getId()."<br>";

        $itemInfo = $this->getInvoiceItemInfoArr($invoiceItem);

        //dump($itemInfo);

        return $itemInfo;
    }
    //Used only in transRequestMacros.twig to display invoice item via:
    // getInvoiceItemInfoByProduct (product item) and
    // getNewInvoiceItemWithoutCategory (invoice items without or with non existed category)
    public function getInvoiceItemInfoArr($invoiceItem) {
        //echo "invoice=$invoiceItem<br>";
        $invoice = $invoiceItem->getInvoice();

        $url = $this->container->get('router')->generate(
            'translationalresearch_invoice_show',
            array(
                'oid'=>$invoice->getOid()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $link = "<a target='_blank' " .
            "href=" . $url . ">" . $invoice->getOid() . "</a>";

        $administrativeFee = $invoice->getAdministrativeFee();
        $discountNumeric = $invoice->getDiscountNumeric();
        $discountPercent = $invoice->getDiscountPercent();
        $invoiceSubTotal = $invoice->getSubTotal();
        $invoiceTotal = $invoice->getTotal();

        $itemCode = $invoiceItem->getItemCode();
        $description = $invoiceItem->getDescription();
        $quantity = $thisQuantity = $invoiceItem->getQuantity();
        $additionalQuantity = $thisAdditionalQuantity = $invoiceItem->getAdditionalQuantity();
        $unitPrice = $invoiceItem->getUnitPrice();
        $additionalUnitPrice = $invoiceItem->getAdditionalUnitPrice();
        $itemTotal = $invoiceItem->getTotal();

        if( $quantity === NULL ) {
            $thisQuantity = 0;
        }
        if( $thisAdditionalQuantity === NULL ) {
            $thisAdditionalQuantity = 0;
        }
        $totalQuantity = $thisQuantity + $thisAdditionalQuantity;

        //TODO: Cases
        $product = $invoiceItem->getProduct();
        $transresRequest = $invoice->getTransresRequest();
        $priceList = $transresRequest->getPriceList();

        if( $product ) {
            $productRes = $product->calculateQuantities($priceList);
        } else {
            $productRes = array();
        }

//            $productRes = array(
//                'initialQuantity' => $initialQuantity,
//                'additionalQuantity' => $additionalQuantity, //$additionalQuantity
//                'initialFee' => $initialFee,
//                'additionalFee' => $additionalFee,
//                'categoryItemCode' => $categoryItemCode,
//                'categoryName' => $categoryName
//            );

        $totalQuantityStr = NULL;
        // Case 9d:
        //If the “Invoiced Additional Quantity” equals 0 or is NULL,
        // and the prices on the latest invoice are unchanged (same as on the fee schedule)
        // show just one line: Invoiced Total Quantity: X at $10.00
        if( !$additionalQuantity ) {

            if( $unitPrice == $additionalUnitPrice ) {
                $additionalUnitPrice = NULL;

                $totalQuantityStr = $quantity . " at $" . $unitPrice;
                $unitPrice = NULL;
                $additionalQuantity = NULL;
                $quantity = NULL;
            }

//            //item does not exists in the original work request
//            //If $additionalUnitPrice and $additionalQuantity are NULL => Invoiced Total Quantity: X at $10.00
//            if( !$additionalUnitPrice && !$additionalQuantity ) {
//                $totalQuantityStr = $quantity . " at $" . $unitPrice;
//                $quantity = NULL;
//            }

            if( !$totalQuantityStr ) {
                if ($quantity == 1) {
                    //AND the initial quantity equals 1, instead of three lines above, show just one line:
                    //Invoiced Total Quantity: 1
                    $totalQuantityStr = 1;
                    $quantity = NULL; //hide Initial Quantity
                } else {
                    //Invoiced Total Quantity: X at $10.00
                    $totalQuantityStr = $quantity . " at $" . $unitPrice;
                    $quantity = NULL; //hide Initial Quantity
                }
            }

        }

        //Hide invoice price if the same as in product
        if( $productRes && count($productRes) > 0 ) {
            if ($unitPrice && $additionalUnitPrice) {
                $initialFee = $productRes['initialFee'];
                $additionalFee = $productRes['additionalFee'];
                if ($unitPrice == $initialFee) {
                    $unitPrice = NULL;
                }
                if ($additionalFee == $additionalUnitPrice) {
                    $additionalUnitPrice = NULL;
                }
            }

            if ($description == $productRes['categoryName']) {
                $description = NULL;
            }

            if ($itemCode == $productRes['categoryItemCode']) {
                $itemCode = NULL;
            }

            //if( )
        } else {
//            //item does not exists in the original work request
//            //If $additionalUnitPrice and $additionalQuantity are NULL => Invoiced Total Quantity: X at $10.00
//            if( !$additionalUnitPrice && !$additionalQuantity ) {
//                $totalQuantityStr = $quantity . " at $" . $unitPrice;
//            }
        }

        $itemInfo = array(
            "invoiceLink" => $link,
            "invoiceSubTotal" => $invoiceSubTotal,
            "administrativeFee" => $administrativeFee,
            "discountNumeric" => $discountNumeric,
            "discountPercent" => $discountPercent,
            "invoiceTotal" => $invoiceTotal,

            "itemCode" => $itemCode,
            "description" => $description,
            "totalQuantity" => $totalQuantity,
            "quantity" => $quantity,
            "additionalQuantity" => $additionalQuantity,
            "unitPrice" => $unitPrice,
            "additionalUnitPrice" => $additionalUnitPrice,
            "itemTotal" => $itemTotal,

            "totalQuantityStr" => $totalQuantityStr,
        );

        return $itemInfo;
    }
    public function getInvoiceInfoArr($invoice) {
        $url = $this->container->get('router')->generate(
            'translationalresearch_invoice_show',
            array(
                'oid'=>$invoice->getOid()
            ),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $link = "<a target='_blank' " .
            "href=" . $url . ">" . $invoice->getOid() . "</a>";

        $administrativeFee = $invoice->getAdministrativeFee();
        $discountNumeric = $invoice->getDiscountNumeric();
        $discountPercent = $invoice->getDiscountPercent();
        $invoiceSubTotal = $invoice->getSubTotal();
        $invoiceTotal = $invoice->getTotal();
        $invoiceSubsidy = $invoice->getSubsidy();

        $invoiceInfo = array(
            "invoiceLink" => $link,
            "invoiceSubTotal" => $invoiceSubTotal,
            "administrativeFee" => $administrativeFee,
            "discountNumeric" => $discountNumeric,
            "discountPercent" => $discountPercent,
            "invoiceTotal" => $invoiceTotal,
            "invoiceSubsidy" => $invoiceSubsidy
        );

        return $invoiceInfo;
    }

    public function getNewInvoiceItemWithoutCategory($transresRequest, $cycle) {
        //Case 228: 8 E (new invoice item without or with new category)
        $newInvoiceItems = array();

        //get latest invoice
        $latestInvoice = $this->getLatestInvoice($transresRequest);

        if( !$latestInvoice ) {
            return NULL;
        }

        //foreach invoice item: detect if this invoice item does not exists in the original work request
        foreach($latestInvoice->getInvoiceItems() as $invoiceItem ) {
            $invoiceProduct = $invoiceItem->getProduct();
            //echo "1invoiceItem=$invoiceItem <br>";
            //echo "invoiceProduct=$invoiceProduct <br>";
            //if( $this->findProductInWorkRequestAndInvoice($invoiceProduct,$transresRequest,$latestInvoice) === NULL ) {
            if( $this->findProductInWorkRequest($invoiceProduct,$transresRequest) === NULL ) {
                //echo "2invoiceItem=$invoiceItem <br>";
                $itemInfo = $this->getInvoiceItemInfoArr($invoiceItem);
                $newInvoiceItems[] = $itemInfo;
            }
            
        }

        //echo "newInvoiceItems=".count($newInvoiceItems)."<br>";

        return $newInvoiceItems;
    }

    public function getInvoiceInfo($transresRequest, $cycle=NULL) {
        //get latest invoice
        $latestInvoice = $this->getLatestInvoice($transresRequest);

        if( !$latestInvoice ) {
            return NULL;
        }

        $invoiceInfoArr = $this->getInvoiceInfoArr($latestInvoice);

        return $invoiceInfoArr;
    }

    //Find corresponding Invoice Item by a work request product
    //$product - work request product
    public function findInvoiceItemByProduct($product) {

        $productId = NULL;
        if( $product ) {
            $productId = $product->getId();
        } else {
            return NULL;
        }
        //echo "productId=$productId <br>";

        $transresRequest = $product->getTransresRequest();
        if( !$transresRequest ) {
            return NULL;
        }

        $invoiceItem = NULL;

        $repository = $this->em->getRepository('AppTranslationalResearchBundle:InvoiceItem');
        $dql =  $repository->createQueryBuilder("invoiceItem");
        $dql->select('invoiceItem');

        $dql->leftJoin('invoiceItem.invoice','invoice');
        $dql->leftJoin('invoiceItem.product','product');
        $dql->leftJoin('invoice.transresRequest','transresRequest');
        //$dql->leftJoin('submitter.infos','submitterInfos');

        $dqlParameters = array();

        $dql->where("product.id = :productId");
        $dql->andWhere("transresRequest.id = :transresRequestId");

        //sort by ID and get the most recent invoice. The largest ID will be the first
        $dql->orderBy("invoiceItem.id","DESC");

        $dqlParameters["productId"] = $productId;
        $dqlParameters["transresRequestId"] = $transresRequest->getId();

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $invoiceItems = $query->getResult();

        //echo "invoiceItems=".count($invoiceItems)."<br>";
        if( count($invoiceItems) > 0 ) {
            $invoiceItem = $invoiceItems[0];
        }

        return $invoiceItem;
    }

//    //Find if the product exists in both: work request and invoice
//    public function findProductInWorkRequestAndInvoice($product,$transresRequest,$invoice) {
//        if( $this->findProductInWorkRequest($product,$transresRequest) && $this->findProductInInvoice($product,$invoice) ) {
//            return TRUE;
//        }
//        return FALSE;
//    }
//    public function findProductInWorkRequestAndInvoiceItem($product,$transresRequest,$invoiceItem) {
//        if( $this->findProductInWorkRequest($product,$transresRequest) && $this->findProductInInvoiceItem($product,$invoiceItem) ) {
//            return TRUE;
//        }
//        return FALSE;
//    }
    //Find if the $product exists in work request
    public function findProductInWorkRequest($product,$transresRequest) {
        
        if( $product ) {
            $productId = $product->getId();
        } else {
            return NULL;
        }
        //echo "productId=$productId <br>";

        if( !$transresRequest ) {
            return NULL;
        }

        //$invoiceItem = NULL;

        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Product');
        $dql =  $repository->createQueryBuilder("product");
        $dql->select('product');

        $dql->leftJoin('product.transresRequest','transresRequest');

        $dqlParameters = array();

        $dql->where("product.id = :productId");
        $dql->andWhere("transresRequest.id = :transresRequestId");

        //sort by ID and get the most recent invoice. The largest ID will be the first
        $dql->orderBy("product.id","DESC");

        $dqlParameters["productId"] = $productId;
        $dqlParameters["transresRequestId"] = $transresRequest->getId();

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $products = $query->getResult();

        //echo "products=".count($products)."<br>";

        $product = NULL;
        if( count($products) > 0 ) {
            $product = $products[0];
        }

        return $product;
    }

    public function getInvoiceShowUrlByWorkRequest($transresRequest) {
        $latestInvoice = $this->getLatestInvoice($transresRequest);
        if( $latestInvoice ) {
            //$invoice,$asHref=true,$title=null,$newPage=false
            $title = $latestInvoice->getOid();
            return $this->getInvoiceShowUrl($latestInvoice,true,$title,true);
        }
        return NULL;
    }

    public function getLatestInvoiceId($transresRequest) {
        $latestInvoice = $this->getLatestInvoice($transresRequest);
        if( $latestInvoice ) {
            return $latestInvoice->getOid();
        }
        return NULL;
    }
    
    public function getInvoiceOidSplitWithUrls($invoice) {
        $transresUtil = $this->container->get('transres_util');
        $router = $transresUtil->getRequestContextRouter();

        $url = NULL;

        $idColor = $transresUtil->getPriceListColorByInvoice($invoice);

        $invoiceOid = $invoice->getOid(); //APCP3355-REQ20458-V46

        $invoiceOidArr = explode("-",$invoiceOid);

        if( count($invoiceOidArr) == 3 ) {
            $projectId = $invoiceOidArr[0]; //APCP3355
            $requestId = $invoiceOidArr[1]; //REQ20458
            $invoiceId = $invoiceOidArr[2]; //V46

            $projectIdNum = preg_replace("/[^0-9.]/","",$projectId); //3355
            $projectUrl = $router->generate(
                'translationalresearch_project_show',
                array(
                    'id' => $projectIdNum,
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $projectUrl = '<a target="_blank" style="color:'.$idColor.'" href="'.$projectUrl.'">'.$projectId.'</a>';

            $requestIdNum = str_replace("REQ","",$requestId); //20458
            $requestUrl = $router->generate(
                'translationalresearch_request_show',
                array(
                    'id' => $requestIdNum,
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $requestUrl = '<a target="_blank" style="color:'.$idColor.'" href="'.$requestUrl.'">'.$requestId.'</a>';

            $invoiceUrl = $router->generate(
                'translationalresearch_invoice_show',
                array(
                    'oid' => $invoice->getOid(),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $invoiceUrl = '<a target="_blank" style="color:'.$idColor.'" href="'.$invoiceUrl.'">'.$invoiceId.'</a>';

            $url = $projectUrl."-".$requestUrl."-".$invoiceUrl;
        }

        if( !$url ) {
            $url = $router->generate(
                'translationalresearch_invoice_show',
                array(
                    'oid' => $invoice->getOid(),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $url = '<a target="_blank" style="color:'.$idColor.'" href="'.$url.'">'.$invoiceOid.'</a>';
        }

        return $url;
    }

    public function updateInvoiceByWorkRequest( $transresRequest, $updateInvoiceAnswer ) {
        $transresPdfUtil = $this->container->get('transres_pdf_generator');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->secTokenStorage->getToken()->getUser();

        $invoice = NULL;
        $msgInvoice = NULL;
        //$break = "<br>";
        $addMsg = $msgInfo = "";

        if( $updateInvoiceAnswer == 'update' || $updateInvoiceAnswer == 'update-send' ) {

            $latestInvoice = $this->getLatestInvoice($transresRequest);

            //Create new invoice entity and pdf
            $invoice = $this->createNewInvoice($transresRequest, $user);

            //carrying over/copying from the previous latest invoice version the (a) Administrative Fee and (b) non-fee-schedule items
            if ($latestInvoice) {

                //(a) Administrative Fee
                $administrativeFee = $latestInvoice->getAdministrativeFee();
                if ($administrativeFee) {
                    $invoice->setAdministrativeFee($administrativeFee);
                }

                $discountNumeric = $latestInvoice->getDiscountNumeric();
                if ($discountNumeric) {
                    $invoice->setDiscountNumeric($discountNumeric);
                }

                $discountPercent = $latestInvoice->getDiscountPercent();
                if ($discountPercent) {
                    $invoice->setDiscountPercent($discountPercent);
                }

                //(b) non-fee-schedule items
                foreach($latestInvoice->getInvoiceItems() as $invoiceItem) {
                    //echo "invoiceItem=".$invoiceItem."<br>";

                    //remove product if item code does not exists in fee schedule
                    if( !$invoiceItem->getProduct() ) {
                        //echo "added invoiceItem=".$invoiceItem."<br>";
                        $invoice->addInvoiceItem($invoiceItem);
                    }
                }
                //exit('111');

                //calculate Subtotal and Total
//                $total = $this->getTransResRequestSubTotal($transresRequest);
//                $invoice->setSubTotal($total);
//                $invoice->setTotal($total);
//                $invoice->setDue($total);

                $subTotal = $invoice->calculateSubTotal();
                $invoice->setSubTotal($subTotal);
                //$invoice->setTotal($subTotal);
                //$invoice->setDue($subTotal);

                $total = $invoice->calculateTotal();
                //$invoice->setSubTotal($total);
                $invoice->setTotal($total);
                $invoice->setDue($total);

                $subsidy = $this->updateInvoiceSubsidy($invoice);
            }

            $updateWorkRequest = false;
            $msgInvoice = $this->createSubmitNewInvoice($transresRequest,$invoice,$updateWorkRequest);

            //generate Invoice PDF
            $res = $transresPdfUtil->generateInvoicePdf($invoice,$user);
            $filename = $res['filename'];
            //$pdf = $res['pdf'];
            $size = $res['size'];
            $msgPdf = "PDF has been created with filename=".$filename."; size=".$size;

            $addMsg = $addMsg . "<br>New Invoice ID" . $invoice->getOid() . " has been successfully created for the request ID " . $transresRequest->getOid();
            $addMsg = $addMsg . "<br>" . $msgPdf;

            if( $updateInvoiceAnswer == 'update-send' ) {

                $emailMsg = $this->sendInvoicePDFByEmail($invoice);

                $addMsg = $addMsg . "<br>" . $emailMsg;
                $msgInfo = $addMsg;
            }

            //event log
            //$this->setEventLog($project,$review,$transitionName,$originalStateStr,$body,$testing);
            $eventType = "Request State Changed";
            $transresUtil->setEventLog($transresRequest,$eventType,$msgInfo,false);
        }

        return $msgInfo;
    }

//    //Find if the $product exists in latest invoice
//    public function findProductInInvoice($product,$invoice) {
//
//        if( $product ) {
//            $productId = $product->getId();
//        } else {
//            return NULL;
//        }
//        //echo "productId=$productId <br>";
//
//        if( !$invoice ) {
//            return NULL;
//        }
//
//        //$invoiceItem = NULL;
//
//        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Invoice');
//        $dql =  $repository->createQueryBuilder("invoice");
//        $dql->select('invoice');
//
//        $dql->leftJoin('invoice.invoiceItems','invoiceItems');
//        $dql->leftJoin('invoiceItems.product','product');
//
//        $dqlParameters = array();
//
//        $dql->where("product.id = :productId");
//        $dql->andWhere("invoice.id = :invoiceId");
//        $dql->andWhere("invoice.latestVersion = TRUE");
//
//        //sort by ID and get the most recent invoice. The largest ID will be the first
//        $dql->orderBy("product.id","DESC");
//
//        $dqlParameters["productId"] = $productId;
//        $dqlParameters["invoiceId"] = $invoice->getId();
//
//        $query = $this->em->createQuery($dql);
//
//        if( count($dqlParameters) > 0 ) {
//            $query->setParameters($dqlParameters);
//        }
//
//        $invoices = $query->getResult();
//
//        //echo "products=".count($products)."<br>";
//
//        $invoice = NULL;
//        if( count($invoices) == 1 ) {
//            $invoice = $invoices[0];
//        }
//
//        return $invoice;
//    }
//    public function findProductInInvoiceItem($product,$invoiceItem) {
//        if( $product ) {
//            $productId = $product->getId();
//        } else {
//            return NULL;
//        }
//        //echo "productId=$productId <br>";
//
//        if( !$invoiceItem ) {
//            return NULL;
//        }
//
//        //$invoiceItem = NULL;
//
//        $repository = $this->em->getRepository('AppTranslationalResearchBundle:InvoiceItem');
//        $dql =  $repository->createQueryBuilder("invoiceItem");
//        $dql->select('invoiceItem');
//
//        $dql->leftJoin('invoiceItem.invoice','invoice');
//        $dql->leftJoin('invoiceItem.product','product');
//
//        $dqlParameters = array();
//
//        $dql->where("product.id = :productId");
//        $dql->andWhere("invoiceItem.id = :invoiceItemId");
//        $dql->andWhere("invoice.latestVersion = TRUE");
//
//        //sort by ID and get the most recent invoice. The largest ID will be the first
//        $dql->orderBy("invoiceItem.id","DESC");
//
//        $dqlParameters["productId"] = $productId;
//        $dqlParameters["invoiceItemId"] = $invoiceItem->getId();
//
//        $query = $this->em->createQuery($dql);
//
//        if( count($dqlParameters) > 0 ) {
//            $query->setParameters($dqlParameters);
//        }
//
//        $invoiceItems = $query->getResult();
//
//        //echo "products=".count($products)."<br>";
//
//        $invoiceItem = NULL;
//        if( count($invoiceItems) == 1 ) {
//            $invoiceItem = $invoiceItems[0];
//        }
//
//        return $invoiceItem;
//    }
}



