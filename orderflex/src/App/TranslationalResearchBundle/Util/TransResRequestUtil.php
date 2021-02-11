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

        $priceList = $request->getPriceList();

        foreach($request->getProducts() as $product) {
            $requested = $product->getRequested();
            $completed = $product->getCompleted();
            $category = $product->getCategory();
            //echo "requested=$requested <br>";
            $fee = 0;
            $feeAdditionalItem = 0;
            $units = 0;
            if( $category ) {
                //$fee = $category->getFee();
                $fee = $category->getPriceFee($priceList);
                $feeAdditionalItem = $category->getPriceFeeAdditionalItem($priceList);
            }
            if( $requested ) {
                $units = intval($requested);
            }
            if( $completed ) {
                $units = intval($completed);
            }
            //echo "units=$units; fee=$fee <br>";
            if( $fee && $units ) {
                //$subTotal = $subTotal + ($units * intval($fee));
                $subTotal = $subTotal + $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$units);
            }
        }

        return $subTotal;
    }

    //Used to generate spreadsheet
    public function getTransResRequestProductInfoArr( $request ) {
        $subTotal = 0;
        $totalProducts = 0;
        $productInfoArr = array();

        $priceList = $request->getPriceList();

        foreach($request->getProducts() as $product) {
            $requested = $product->getRequested();
            $completed = $product->getCompleted();

            $categoryStr = NULL;
            $category = $product->getCategory();
            if( $category ) {
                $categoryStr = $category->getShortInfo($request);
            }

            $comment = $product->getComment();
            $note = $product->getNote();
            //echo "requested=$requested <br>";
            $fee = 0;
            $feeAdditionalItem = 0;
            $units = 0;
            if( $category ) {
                //$fee = $category->getFee();
                $fee = $category->getPriceFee($priceList);
                $feeAdditionalItem = $category->getPriceFeeAdditionalItem($priceList);
            }
            if( $requested ) {
                $units = intval($requested);
            }
            if( $completed ) {
                $units = intval($completed);
            }
            //echo "units=$units; fee=$fee <br>";
            if( $fee && $units ) {
                //$subTotal = $subTotal + ($units * intval($fee));
                $subTotal = $subTotal + $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$units);
            }

//            'productRequested' => $requested,
//            'productCompleted' => $completed,
//            'productCategory' => $category,
//            'productComment' => $comment,
//            'productNote' => $note,
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
//            'productRequested' => $requested,
//            'productCompleted' => $completed,
//            'productCategory' => $category,
//            'productComment' => $comment,
//            'productNote' => $note,
        );

        return $res;
    }

//    //NOT USED
//    public function getTransResRequestFormnodeFeeHtml( $request ) {
//
//        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
//        $formNodeUtil = $this->container->get('user_formnode_utility');
//
//        $completedEntities = $transResFormNodeUtil->getProjectFormNodeFieldByName(
//            $request,
//            "Completed #",
//            "HemePath Translational Research Request",
//            "Request",
//            "Product or Service",
//            null,
//            true
//        );
//        //echo "completedEntities=".count($completedEntities)."<br>";
////        $formNodeValues = $completedEntities['formNodeValue'];
////        foreach($formNodeValues as $resArr) {
////            $formNodeValue = $resArr['formNodeValue'];
////            echo "formNodeValue=".$formNodeValue."<br>";
////            $arraySectionIndex = $resArr['arraySectionIndex'];
////            echo "arraySectionIndex=" . $arraySectionIndex . "<br>";
////        }
////        return 1;
//
//        $requestedEntities = $transResFormNodeUtil->getProjectFormNodeFieldByName(
//            $request,
//            "Requested #",
//            "HemePath Translational Research Request",
//            "Request",
//            "Product or Service",
//            null,
//            true
//        );
//        //echo "requestedEntities=".count($requestedEntities)."<br>";
//
//        $requestCategoryTypeComplexResults = $this->getMultipleProjectFormNodeFieldByName(
//            $request,
//            "Category Type",
//            "HemePath Translational Research Request",
//            "Request",
//            "Product or Service"
//        );
//        //echo "requestCategoryTypeComplexResults=".count($requestCategoryTypeComplexResults)."<br>";
////        $res = array(
////            'formNodeValue' => $formNodeValue,
////            'formNodeId' => $formNode->getId(),
////            'arraySectionId' => $result->getArraySectionId(),
////            'arraySectionIndex' => $result->getArraySectionIndex(),
////        );
////        $resArr[] = $res;
//
//        $subTotal = 0;
//
//        //2) group by arraySectionIndex
//        foreach($requestCategoryTypeComplexResults as $complexRes) {
//
//            $arraySectionIndex = $complexRes['arraySectionIndex'];
//            //echo "arraySectionIndex=".$arraySectionIndex."<br>";
//            $dropdownObject = $complexRes['dropdownObject'];
//
//            $requested = $this->findByArraySectionIndex($requestedEntities,$arraySectionIndex);
//            //echo "requested=".$requested."<br>";
//            $completed = $this->findByArraySectionIndex($completedEntities,$arraySectionIndex);
//            //echo "completed=".$completed."<br>";
//            //echo "###<br>";
//
//            $fee = $dropdownObject->getFee();
//
//            if( $fee ) {
//                $subTotal = $subTotal + intval($completed) * intval($fee);
//                //return $subTotal;
//            }
//        }
//
//        return $subTotal;
//    }
//    public function findByArraySectionIndex($entities, $arraySectionIndex) {
//        $formNodeValues = $entities['formNodeValue'];
//        if( !is_array($formNodeValues) ) {
//            return null;
//        }
//        foreach($formNodeValues as $resArr) {
//            $formNodeValue = $resArr['formNodeValue'];
//            //echo "formNodeValue=".$formNodeValue."<br>";
//            $thisArraySectionIndex = $resArr['arraySectionIndex'];
//            //echo "arraySectionIndex=" . $arraySectionIndex . "<br>";
//            if( $thisArraySectionIndex == $arraySectionIndex ) {
//                return $formNodeValue;
//            }
//        }
//        return null;
//    }
//    public function getMultipleProjectFormNodeFieldByName(
//        $entity,
//        $fieldName,
//        $parentNameStr = "HemePath Translational Research",
//        $formNameStr = "HemePath Translational Research Project",
//        $entityFormNodeSectionStr = "Project"
//    )
//    {
//        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
//        $formNodeUtil = $this->container->get('user_formnode_utility');
//
//        $value = null;
//        $receivingEntity = null;
//
//        //1) get FormNode by fieldName
//        //echo "getting formnode <br>";
//        $fieldFormNode = $transResFormNodeUtil->getFormNodeByFieldNameAndParents($fieldName, $parentNameStr, $formNameStr, $entityFormNodeSectionStr);
//
//        //2) get field for this particular project
//        $class = new \ReflectionClass($entity);
//        $className = $class->getShortName();
//        $classNamespace = $class->getNamespaceName();
//        $entityMapper = array(
//            'entityNamespace' => $classNamespace,   //"App\\TranslationalResearchBundle\\Entity",
//            'entityName' => $className, //"Project",
//            'entityId' => $entity->getId(),
//        );
//
//        $resArr = array();
//
//        $results = $formNodeUtil->getFormNodeValueByFormnodeAndReceivingmapper($fieldFormNode,$entityMapper,true);
//        if( $results ) {
//            foreach ($results as $result) {
//                $arraySectionIndex = $result->getArraySectionIndex();
//                //echo "result ID= ".$result->getId()." <br>";
//                //$formNodeValue = $formNodeUtil->processFormNodeValue($fieldFormNode,$result,$result->getValue(),true);
//                //echo "formNodeValue= $formNodeValue <br>";
//                //$dropdownObject = $formNodeUtil->getReceivingObject($fieldFormNode,$result->getId());
//                //echo "dropdownObject ID= ".$dropdownObject->getId()." <br>";
//                $dropdownObject = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->find($result->getValue());
//                //echo "category=".$dropdownObject."<br>";
//                $thisRes = array(
//                    'arraySectionIndex' => $arraySectionIndex,
//                    'dropdownObject' => $dropdownObject
//                );
//                $resArr[] = $thisRes;
//            }
//        }
//
//        return $resArr;
//    }
//    public function getSingleTransResRequestFeeHtml_OLD( $request ) {
//
//        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
//
//        $completed = $transResFormNodeUtil->getProjectFormNodeFieldByName(
//            $request,
//            "Completed #",
//            "HemePath Translational Research",
//            "HemePath Translational Research Request",
//            "Request",
//            false
//        );
//
//        //
//        $completed = str_replace(" ","",$completed);
//
//        if( !$completed ) {
//            $completed = $transResFormNodeUtil->getProjectFormNodeFieldByName(
//                $request,
//                "Requested #",
//                "HemePath Translational Research",
//                "HemePath Translational Research Request",
//                "Request",
//                false
//            );
//        }
//
//        $completed = str_replace(" ","",$completed);
//        //echo "completed=".$completed."<br>";
//
//        $requestCategoryTypeDropdownObject = $transResFormNodeUtil->getProjectFormNodeFieldByName(
//            $request,
//            "Category Type",
//            "HemePath Translational Research",
//            "HemePath Translational Research Request",
//            "Request",
//            true
//        );
//
//        if( $completed && $requestCategoryTypeDropdownObject ) {
//            //echo "requestCategoryTypeDropdownObject=".$requestCategoryTypeDropdownObject."<br>";
//            //echo "requestCategoryType feeUnit=".$requestCategoryType->getFeeUnit()."<br>";
//            //echo "requestCategoryType fee=".$requestCategoryType->getFee()."<br>";
//
//            $fee = $requestCategoryTypeDropdownObject->getFee();
//
//            if( $fee ) {
//                $subTotal = intval($completed) * intval($fee);
//                return $subTotal;
//            }
//        }
//
//        return null;
//    }

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

    public function getRequestItems($request) {
        $user = $this->secTokenStorage->getToken()->getUser();
        $invoiceItemsArr = new ArrayCollection();
        $priceList = $request->getPriceList();

        foreach( $request->getProducts() as $product ) {
            //Invoice's quantity field is pre-populated by the Request's "Requested #"
            $invoiceItem = new InvoiceItem($user);

            $invoiceItem->setProduct($product);

            //Invoice should pull the Quantity from the "Completed Quantity" field
            // (IF "Completed Quantity" field has a value; if it has no value, pull the number from the Requested Quantity field)
            $quantity = $product->getCompleted();
            if( !$quantity ) {
                $quantity = $product->getRequested();
            }
            $invoiceItem->setQuantity($quantity);

            //TODO: split quantity to "requested quantity" and "completed quantity" from Work Request
            //TODO: add "comment" from Work Request

            $category = $product->getCategory();
            $fee = 0;
            $feeAdditionalItem = 0;

            if( $category ) {
                //ItemCode
                $itemCode = $category->getProductId($priceList); //original productId can be obtained by $invoiceItem->getProduct()->getCategory()->getProductId()
                //$itemCode = $category->getProductId();
                $invoiceItem->setItemCode($itemCode);

                //Description
                $name = $category->getName();
                $invoiceItem->setDescription($name);

                //UnitPrice
                //$fee = $category->getFee();
                $fee = $category->getPriceFee($priceList);
                $feeAdditionalItem = $category->getPriceFeeAdditionalItem($priceList);

                $invoiceItem->setUnitPrice($fee);
                $invoiceItem->setAdditionalUnitPrice($feeAdditionalItem);
            }

            if( $quantity && $fee ) {
                //Total
                //$total = intval($quantity) * intval($fee);
                $total = $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$quantity);

                $invoiceItem->setTotal($total);
            }

            $invoiceItemsArr->add($invoiceItem);
        }

        return $invoiceItemsArr;
    }

//    //NOT USED
//    public function getRequestItemsFormNode($request) {
//        $user = $this->secTokenStorage->getToken()->getUser();
//        //$user = null; //testing
//        $invoiceItemsArr = new ArrayCollection();
//
//        $transResFormNodeUtil = $this->container->get('transres_formnode_util');
//        $formNodeUtil = $this->container->get('user_formnode_utility');
//
//        $completedEntities = $transResFormNodeUtil->getProjectFormNodeFieldByName(
//            $request,
//            "Completed #",
//            "HemePath Translational Research Request",
//            "Request",
//            "Product or Service",
//            null,
//            true
//        );
//        //echo "completedEntities=".count($completedEntities)."<br>";
////        $formNodeValues = $completedEntities['formNodeValue'];
////        foreach($formNodeValues as $resArr) {
////            $formNodeValue = $resArr['formNodeValue'];
////            echo "formNodeValue=".$formNodeValue."<br>";
////            $arraySectionIndex = $resArr['arraySectionIndex'];
////            echo "arraySectionIndex=" . $arraySectionIndex . "<br>";
////        }
////        return 1;
//
//        $requestedEntities = $transResFormNodeUtil->getProjectFormNodeFieldByName(
//            $request,
//            "Requested #",
//            "HemePath Translational Research Request",
//            "Request",
//            "Product or Service",
//            null,
//            true
//        );
//        //echo "requestedEntities=".count($requestedEntities)."<br>";
//
//        $requestCategoryTypeComplexResults = $this->getMultipleProjectFormNodeFieldByName(
//            $request,
//            "Category Type",
//            "HemePath Translational Research Request",
//            "Request",
//            "Product or Service"
//        );
//        //echo "requestCategoryTypeComplexResults=".count($requestCategoryTypeComplexResults)."<br>";
//
//        //2) group by arraySectionIndex
//        foreach($requestCategoryTypeComplexResults as $complexRes) {
//
//            $arraySectionIndex = $complexRes['arraySectionIndex'];
//            //echo "arraySectionIndex=".$arraySectionIndex."<br>";
//            $dropdownObject = $complexRes['dropdownObject'];
//
//            $requested = $this->findByArraySectionIndex($requestedEntities,$arraySectionIndex);
//            //echo "requested=".$requested."<br>";
//            $completed = $this->findByArraySectionIndex($completedEntities,$arraySectionIndex);
//            //echo "completed=".$completed."<br>";
//            //echo "###<br>";
//
//            //$fee = $dropdownObject->getFee();
//
////            if( $fee ) {
////                $subTotal = $subTotal + intval($completed) * intval($fee);
////                //return $subTotal;
////            }
//
//            $invoiceItem = new InvoiceItem($user);
//            $invoiceItem->setQuantity($completed);
//
//            //ItemCode
//            $itemCode = $dropdownObject->getProductId();
//            $invoiceItem->setItemCode($itemCode);
//
//            //Description
//            $name = $dropdownObject->getName();
//            $invoiceItem->setDescription($name);
//
//            //UnitPrice
//            $fee = $dropdownObject->getFee();
//            $invoiceItem->setUnitPrice($fee);
//
//            //Total
//            $total = intval($completed) * intval($fee);
//            $invoiceItem->setTotal($total);
//
//            $invoiceItemsArr->add($invoiceItem);
//        }
//
//        //$invoiceItemsArr->add(new InvoiceItem($user));
//        //$invoiceItemsArr->add(new InvoiceItem($user));
//        //$invoiceItemsArr->add(new InvoiceItem($user));
//        return $invoiceItemsArr;
//    }

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
        $admins = $transresUtil->getTransResAdminEmails($project->getProjectSpecialty(),$asEmail,true); //ok
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
        $invoiceItems = $this->getRequestItems($transresRequest);
        foreach( $invoiceItems as $invoiceItem ) {
            $invoice->addInvoiceItem($invoiceItem);
        }

        //calculate Subtotal and Total
        $total = $this->getTransResRequestFeeHtml($transresRequest);
        $invoice->setSubTotal($total);
        $invoice->setTotal($total);
        $invoice->setDue($total);

//        //calculate subsidy based on the work request's products
//        $subsidy = $this->calculateSubsidy($invoice);
//        $invoice->setSubsidy($subsidy);

        return $invoice;
    }
    public function createSubmitNewInvoice( $transresRequest, $invoice ) {
        $transresUtil = $this->container->get('transres_util');
        //$transresRequestUtil = $this->container->get('transres_request_util');

        $invoice = $this->generateInvoiceOid($transresRequest,$invoice);

        //use the values in Invoice’s Quantity fields to overwrite/update the associated Request’s "Completed #" fields
        $this->updateRequestCompletedFieldsByInvoice($invoice);

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

        $eventType = "Invoice Created";
        $msg = "New Invoice with ID ".$invoice->getOid()." has been successfully submitted for the request ID ".$transresRequest->getOid();
        $transresUtil->setEventLog($invoice,$eventType,$msg);

        return $msg;
    }

    public function updateRequestCompletedFieldsByInvoice($invoice) {
        $transresUtil = $this->container->get('transres_util');

        $transresRequest = $invoice->getTransresRequest();
        if( !$transresRequest ) {
            return null;
        }

        foreach( $invoice->getInvoiceItems() as $invoiceItem ) {
            $requestProduct = $invoiceItem->getProduct();
            if( !$requestProduct ) {
                continue;
            }
            $requestQuant = $requestProduct->getCompleted();
            $invoiceQuant = $invoiceItem->getQuantity();
            if( $invoiceQuant && $requestQuant != $invoiceQuant ) {

                //eventLog changes
                $eventType = "Request Updated";
                $msg = "Request's (".$transresRequest->getOid(). ") completed value ".$requestQuant.
                    " has been updated by the invoice's (".$invoice->getOid() . ") quantity value " . $invoiceQuant;
                $transresUtil->setEventLog($transresRequest,$eventType,$msg);

                $requestProduct->setCompleted($invoiceQuant);
            }
        }

        return $transresRequest;
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
    
//    public function isInvoiceBillingContact( $invoice, $user ) {
//        //ROLE_TRANSRES_BILLING_ADMIN role
//        if( $this->secAuth->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
//            return true;
//        }
//        
//        //Invoice's billing contact (salesperson)
//        $salesperson = $invoice->getSalesperson();
//        if( $salesperson->getId() == $user->getId() ) {
//            return true;
//        }
//
//        return false;
//    }

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

//    //get allowed filter request types for logged in user
//    public function getRequestFilterPresetType_ORIG() {
//        $transresUtil = $this->container->get('transres_util');
//        $user = $this->secTokenStorage->getToken()->getUser();
//        $allowHema = false;
//        $allowAPCP = false;
//        $allowCovid19 = false;
//        $allowMisi = false;
//
//        $specialtyHemaObject = $transresUtil->getSpecialtyObject("hematopathology");
//        if( $transresUtil->isUserAllowedSpecialtyObject($specialtyHemaObject, $user) ) {
//            $allowHema = true;
//        }
//
//        $specialtyAPCPObject = $transresUtil->getSpecialtyObject("ap-cp");
//        if( $transresUtil->isUserAllowedSpecialtyObject($specialtyAPCPObject, $user) ) {
//            $allowAPCP = true;
//        }
//
//        $specialtyCovid19Object = $transresUtil->getSpecialtyObject("covid19");
//        if( $transresUtil->isUserAllowedSpecialtyObject($specialtyCovid19Object, $user) ) {
//            $allowCovid19 = true;
//        }
//
//        $specialtyMisiObject = $transresUtil->getSpecialtyObject("misi");
//        if( $transresUtil->isUserAllowedSpecialtyObject($specialtyMisiObject, $user) ) {
//            $allowMisi = true;
//        }
//
//        $filterTypes = array(
//            'My Submitted Requests',
//            "My Draft Requests",
//            'Submitted Requests for My Projects',
//            'Draft Requests for My Projects',
//            //'Requests I Completed',
//            //'[[hr]]'
//        );
//
//        if( $this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN') || $transresUtil->isAdminOrPrimaryReviewer() ) {
//            $filterTypes[] = 'Requests I Completed';
//        }
//
//        $filterTypes[] = '[[hr]]';
//
//        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() === false && $this->secAuth->isGranted('ROLE_TRANSRES_TECHNICIAN') === false ) {
//            return $filterTypes;
//        }
//
//        $filterTypes[] = 'All Requests';
//        if( $allowHema ) {
//            $filterTypes[] = 'All Hematopathology Requests';
//        }
//        if( $allowAPCP ) {
//            $filterTypes[] = 'All AP/CP Requests';
//        }
//        if( $allowCovid19 ) {
//            $filterTypes[] = 'All COVID-19 Requests';
//        }
//        if( $allowMisi ) {
//            $filterTypes[] = 'All MISI Requests';
//        }
//        $filterTypes[] = 'All Requests (including Drafts)';
//        $filterTypes[] = '[[hr]]';
//
//        $filterTypes[] = 'All Pending Requests';
//        if( $allowHema ) {
//            $filterTypes[] = 'All Hematopathology Pending Requests';
//        }
//        if( $allowAPCP ) {
//            $filterTypes[] = 'All AP/CP Pending Requests';
//        }
//        if( $allowCovid19 ) {
//            $filterTypes[] = 'All COVID-19 Pending Requests';
//        }
//        if( $allowMisi ) {
//            $filterTypes[] = 'All MISI Pending Requests';
//        }
//        $filterTypes[] = '[[hr]]';
//
//        $filterTypes[] = 'All Active Requests';
//        if( $allowHema ) {
//            $filterTypes[] = 'All Hematopathology Active Requests';
//        }
//        if( $allowAPCP ) {
//            $filterTypes[] = 'All AP/CP Active Requests';
//        }
//        if( $allowCovid19 ) {
//            $filterTypes[] = 'All COVID-19 Active Requests';
//        }
//        if( $allowMisi ) {
//            $filterTypes[] = 'All MISI Active Requests';
//        }
//        $filterTypes[] = '[[hr]]';
//
//        $filterTypes[] = 'All Completed Requests';
//        if( $allowHema ) {
//            $filterTypes[] = 'All Hematopathology Completed Requests';
//        }
//        if( $allowAPCP ) {
//            $filterTypes[] = 'All AP/CP Completed Requests';
//        }
//        if( $allowCovid19 ) {
//            $filterTypes[] = 'All COVID-19 Completed Requests';
//        }
//        if( $allowMisi ) {
//            $filterTypes[] = 'All MISI Completed Requests';
//        }
//        $filterTypes[] = '[[hr]]';
//
//
//        $filterTypes[] = 'All Completed and Notified Requests';
//        if( $allowHema ) {
//            $filterTypes[] = 'All Hematopathology Completed and Notified Requests';
//        }
//        if( $allowAPCP ) {
//            $filterTypes[] = 'All AP/CP Completed and Notified Requests';
//        }
//        if( $allowCovid19 ) {
//            $filterTypes[] = 'All COVID-19 Completed and Notified Requests';
//        }
//        if( $allowMisi ) {
//            $filterTypes[] = 'All MISI Completed and Notified Requests';
//        }
//        //$filterTypes[] = '[[hr]]';
//
//        return $filterTypes;
//    }
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
        //TODO: what if paid == total? Should we use $transresRequestUtil->updateInvoiceStatus($invoice);
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

        //To issue the invoice to Surya Seshan - svs2002 (WCMC CWID) at email svs2002@med.cornell.edu, led9016@med.cornell.edu please visit this link:
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

//        if( count($piEmailArr) > 0 ) {
//            return " (".implode(", ",$piEmailArr).")";
//        }
//
//        return " (no pis)";
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

    //get Issued Invoices
    public function getInvoicesInfosByRequest($transresRequest) {
        $invoicesInfos = array();
        $count = 0;
        $total = 0.00;
        $paid = 0.00;
        $due = 0.00;
        $subsidy = 0.00;

        foreach( $transresRequest->getInvoices() as $invoice ) {
            if( $invoice->getLatestVersion() ) {
//                if(
//                    $invoice->getStatus() == "Unpaid/Issued" ||
//                    $invoice->getStatus() == "Paid in Full" ||
//                    $invoice->getStatus() == "Paid Partially"
//                ) {
                    $count++;
                    $total = $total + $invoice->getTotal();
                    $paid = $paid + $invoice->getPaid();
                    $due = $due + $invoice->getDue();
                    $subsidy = $subsidy + $invoice->getSubsidy();
//                }
            }
        }

        //echo "total=$total<br>";
        //echo "paid=$paid<br>";

        if( $count > 0 ) {
            if ($total > 0) {
                $total = $this->toDecimal($total);
            }
            if ($paid > 0) {
                $paid = $this->toDecimal($paid);
            }
            if ($due > 0) {
                $due = $this->toDecimal($due);
            }
            if ($subsidy > 0) {
                $subsidy = $this->toDecimal($subsidy);
            }
        } else {
            $total = null;
            $paid = null;
            $due = null;
            $subsidy = null;
        }

        //echo "paid=$paid<br>";

        $invoicesInfos['count'] = $count;
        $invoicesInfos['total'] = $total;
        $invoicesInfos['paid'] = $paid;
        $invoicesInfos['due'] = $due;
        $invoicesInfos['subsidy'] = $subsidy;

        return $invoicesInfos;
    }
    public function toDecimal($number) {
        return number_format((float)$number, 2, '.', '');
    }

    public function getTotalFeesByQuantity($fee,$feeAdditionalItem,$quantity) {
        $quantity = intval($quantity);
        //$fee = intval($fee);
        $fee = $this->toDecimal($fee);
        if( $feeAdditionalItem ) {
            //$feeAdditionalItem = intval($feeAdditionalItem);
            $feeAdditionalItem = $this->toDecimal($feeAdditionalItem);
        } else {
            $feeAdditionalItem = $fee;
        }
        $total = 0;
        if( $quantity == 1 ) {
            $total = $quantity * $fee;
        } elseif ( $quantity > 1 ) {
            $total = 1 * $fee;
            $additionalFee = ($quantity-1) * $feeAdditionalItem;
            $total = $total + $additionalFee;
        }
        if ($total > 0) {
            $total = $this->toDecimal($total);
        }
        return $total;
    }

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

//    public function isInvoiceShowableToUser($invoice) {
//        $user = $this->secTokenStorage->getToken()->getUser();
//        //$transresUtil = $this->container->get('transres_util');
//        $transresRequest = $invoice->getTransresRequest();
//        if( $transresRequest ) {
//            //ok
//        } else {
//            return true;
//        }
//
//        $project = $transresRequest->getProject();
//
//        //check if the user is
//        if( $this->areInvoicesShowableToUser($project) ) {
//            return true;
//        }
//
//        if( $this->isInvoiceBillingContact($invoice,$user) ) {
//            return true;
//        }
//
//        return false;
//    }
//    public function areInvoicesShowableToUser($project) {
//        $user = $this->secTokenStorage->getToken()->getUser();
//        $transresUtil = $this->container->get('transres_util');
//
//        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) ) {
//            return true;
//        }
//
//        //check if the user is
//        // technologists (ROLE_TRANSRES_TECHNICIAN)/sys admin/platform admin/deputy platform admin/executive committee member/default reviewers
//        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() ) {
//            return true;
//        }
//
//        if( $transresUtil->isUserAllowedSpecialtyObject($project->getProjectSpecialty()) ) {
//            return true;
//        }
//
//        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN') ) {
//            return true;
//        }
//
//        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
//            return true;
//        }
//
//        //this also check if isUserAllowedSpecialtyObject
//        if( $transresUtil->isProjectReviewer($project) ) {
//            return true;
//        }
//
//        return false;
//    }
//    public function isUserHasInvoicePermission( $invoice, $action ) {
//        $user = $this->secTokenStorage->getToken()->getUser();
//        $transresUtil = $this->container->get('transres_util');
//
//        $processed = false;
//        if( $invoice ) {
//            if( $this->isUserAllowedAccessInvoiceBySpecialty($invoice) == false ) {
//                return false;
//            }
//        }
//        //exit('1');
//
//        if( $transresUtil->isAdminOrPrimaryReviewerOrExecutive() ) {
//            return true;
//        }
//
//        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_TECHNICIAN') ) {
//            return true;
//        }
//
//        if( $this->container->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
//            return true;
//        }
//
//        if( !$invoice ) {
//            if( $action == "create" ) {
//                return true;
//            } else {
//                //exit("Logical Error: Invoice is NULL and action is $action");
//                return false;
//            }
//        }
//
////        if( $action == "create" ) {
////            $processed = true;
////            if( $this->secAuth->isGranted('ROLE_TRANSRES_BILLING_ADMIN') ) {
////                return true;
////            }
////        }
//
//        $transresRequest = $invoice->getTransresRequest();
//        if( $transresRequest ) {
//            //ok
//        } else {
//            return true;
//        }
//
//        $project = $transresRequest->getProject();
//        if( $project ) {
//            //ok
//        } else {
//            return true;
//        }
//
//        //show: to users associated with this invoice, request or project
//        if( $action == "view" ) {
//            $processed = true;
//
//            if( $this->isInvoiceBillingContact($invoice,$user) ) {
//                return true;
//            }
//
//            //associated with the request as requester
//            if( $this->isRequestRequester($transresRequest) ) {
//                return true;
//            }
//
//            //associated with the request as reviewer
//            if( $this->isRequestStateReviewer($transresRequest) ) {
//                return true;
//            }
//
//            //associated with the project
//            if( $transresUtil->isProjectRequester($project) ) {
//                return true;
//            }
//        }
//
//        //view-pdf: show pdf if user can not edit, but can view
//        if( $action == "view-pdf" ) {
//            $processed = true;
//
//            //if( $this->isUserHasInvoicePermission($invoice,"view") and $this->isUserHasInvoicePermission($invoice,"update") == false ) {
//            //    return true;
//            //}
//
//            //associated with the request as requester
//            if( $this->isRequestRequester($transresRequest) ) {
//                return true;
//            }
//
//            //associated with the request as reviewer
//            if( $this->isRequestStateReviewer($transresRequest) ) {
//                return true;
//            }
//
//            //associated with the project
//            if( $transresUtil->isProjectRequester($project) ) {
//                return true;
//            }
//        }
//
//        //edit: admin, technicians,
//        if( $action == "update" ) {
//            $processed = true;
//
//            if( $this->isInvoiceBillingContact($invoice,$user) ) {
//                return true;
//            }
//        }
//
//        if( $action == "send-invoice-pdf-email" ) {
//            $processed = true;
//
//            if( $this->isInvoiceBillingContact($invoice,$user) ) {
//                return true;
//            }
//        }
//
//        if( $action == "change-status" ) {
//            $processed = true;
//
//            if( $this->isInvoiceBillingContact($invoice,$user) ) {
//                return true;
//            }
//        }
//
//        if( !$processed ) {
//            //exit("Action is invalid: $action");
//            $logger = $this->container->get('logger');
//            $logger->warning("isUserHasInvoicePermission: Action is invalid: $action");
//        }
//
//        return false;
//    }
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

    public function getTransresSiteParameter($fieldName,$transresRequest) {

        if( !$fieldName ) {
            throw new \Exception("Field name is empty");
        }

        $project = $transresRequest->getProject();
        $projectSpecialty = $project->getProjectSpecialty();
        $projectSpecialtyAbbreviation = $projectSpecialty->getAbbreviation();

        $siteParameter = $this->findCreateSiteParameterEntity($projectSpecialtyAbbreviation);
        if( !$siteParameter ) {
            throw new \Exception("SiteParameter is not found by specialty '" . $projectSpecialtyAbbreviation . "'");
        }

        $getMethod = "get".$fieldName;

        $value = $siteParameter->$getMethod();

        return $value;
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

//    public function sendReminderUnpaidInvoices($showSummary=false) {
//        $transresUtil = $this->container->get('transres_util');
//
//        $resultArr = array();
//
//        $projectSpecialties = $transresUtil->getTransResProjectSpecialties(false);
//        foreach($projectSpecialties as $projectSpecialty) {
//            $results = $this->sendReminderUnpaidInvoicesBySpecialty($projectSpecialty,$showSummary);
//            if( $results ) {
//                $resultArr[] = $results;
//            }
//        }
//
//        if( $showSummary ) {
//            return $resultArr;
//        }
//
//        if( count($resultArr) > 0 ) {
//            $result = implode(", ", $resultArr);
//        } else {
//            $result = "There are no unpaid overdue invoices corresponding to the site setting parameters.";
//        }
//
//        return $result;
//    }
//    public function sendReminderUnpaidInvoicesBySpecialty( $projectSpecialty, $showSummary=false ) {
//        $transresUtil = $this->container->get('transres_util');
//        $userSecUtil = $this->container->get('user_security_utility');
//        $emailUtil = $this->container->get('user_mailer_utility');
//        $logger = $this->container->get('logger');
//        $systemuser = $userSecUtil->findSystemUser();
//
//        $invoiceDueDateMax = null;
//        $reminderInterval = null;
//        $maxReminderCount = null;
//        //$newline = "\n";
//        //$newline = "<br>";
//        $resultArr = array();
//        $sentInvoiceEmailsArr = array();
//
//        $testing = false;
//        //$testing = true;
//
//        //$invoiceReminderSchedule: invoiceDueDateMax,reminderIntervalMonths,maxReminderCount (i.e. 3,3,5)
//        $invoiceReminderSchedule = $transresUtil->getTransresSiteProjectParameter('invoiceReminderSchedule',null,$projectSpecialty); //6,9,12,15,18
//
//        if( $invoiceReminderSchedule ) {
//            $invoiceReminderScheduleArr = explode(",",$invoiceReminderSchedule);
//            if( count($invoiceReminderScheduleArr) == 3 ) {
//                $invoiceDueDateMax = $invoiceReminderScheduleArr[0];    //over due in months (integer)
//                $reminderInterval = $invoiceReminderScheduleArr[1];     //reminder interval in months (integer)
//                $maxReminderCount = $invoiceReminderScheduleArr[2];     //max reminder count (integer)
//            }
//        } else {
//            return "No invoiceReminderSchedule is set";
//        }
//        //testing
//        if( $testing ) {
//            echo "Warning testing mode!!! <br>";
//            $invoiceDueDateMax = 1;
//            $reminderInterval = 1;
//            $maxReminderCount = 5;
//        }
//
//        if( !$invoiceDueDateMax ) {
//            return "invoiceDueDateMax is not set. Invoice reminder emails are not sent.";
//        }
//        if( !$reminderInterval ) {
//            return "reminderInterval is not set. Invoice reminder emails are not sent.";
//        }
//        if( !$maxReminderCount ) {
//            return "maxReminderCount is not set. Invoice reminder emails are not sent.";
//        }
//
//        $invoiceDueDateMax = trim($invoiceDueDateMax);
//        $reminderInterval = trim($reminderInterval);
//        $maxReminderCount = trim($maxReminderCount);
//
//        $params = array();
//
//        $invoiceReminderSubject = $transresUtil->getTransresSiteProjectParameter('invoiceReminderSubject',null,$projectSpecialty);
//        if( !$invoiceReminderSubject ) {
//            $invoiceReminderSubject = "[TRP] Translational Research Unpaid Invoice Reminder: [[INVOICE ID]]";
//        }
//
//        $invoiceReminderBody = $transresUtil->getTransresSiteProjectParameter('invoiceReminderBody',null,$projectSpecialty);
//        if( !$invoiceReminderBody ) {
//            $invoiceReminderBody = "Our records show that we have not received the $[[INVOICE AMOUNT DUE]] payment for the attached invoice  [[INVOICE ID]] issued on [[INVOICE DUE DATE AND DAYS AGO]].";
//        }
//
//        $invoiceReminderEmail = $transresUtil->getTransresSiteProjectParameter('invoiceReminderEmail',null,$projectSpecialty);
//        //echo "settings: $invoiceReminderSchedule, $invoiceReminderSubject, $invoiceReminderBody, $invoiceReminderEmail".$newline;
//        //echo "invoiceReminderSchedule=$invoiceReminderSchedule".$newline;
//
//        //Send email reminder email if (issueDate does not exist, so use dueDate):
//        //1. (dueDate < currentDate - invoiceDueDateMax) AND
//        //2. (invoiceLastReminderSentDate IS NULL OR invoiceLastReminderSentDate < currentDate - reminderInterval) AND
//        //3. (invoiceReminderCount < maxReminderCount)
//        //When email sent, set invoiceLastReminderSentDate=currentDate, invoiceReminderCount++
//
//        $repository = $this->em->getRepository('AppTranslationalResearchBundle:Invoice');
//        $dql =  $repository->createQueryBuilder("invoice");
//        $dql->select('invoice');
//
//        $dql->leftJoin('invoice.transresRequest','transresRequest');
//        $dql->leftJoin('transresRequest.project','project');
//        $dql->leftJoin('project.projectSpecialty','projectSpecialty');
//
//        $dql->where("projectSpecialty.id = :specialtyId");
//        $params["specialtyId"] = $projectSpecialty->getId();
//
//        $dql->andWhere("invoice.status = :unpaid AND invoice.latestVersion = TRUE"); //Unpaid/Issued
//        $params["unpaid"] = "Unpaid/Issued";
//
//        /////////1. (dueDate < currentDate - invoiceDueDateMax) //////////////
//        //overDueDate = currentDate - invoiceDueDateMax;
//        $overDueDate = new \DateTime("-".$invoiceDueDateMax." months");
//        //echo "overDueDate=".$overDueDate->format('Y-m-d H:i:s').$newline;
//        $dql->andWhere("invoice.dueDate IS NOT NULL AND invoice.dueDate < :overDueDate");
//        $params["overDueDate"] = $overDueDate->format('Y-m-d H:i:s');
//        ////////////// EOF //////////////
//
//        /////////.2 (invoiceLastReminderSentDate IS NULL OR invoiceLastReminderSentDate < currentDate - reminderInterval) ///////////
//        $overDueReminderDate = new \DateTime("-".$reminderInterval." months");
//        $dql->andWhere("invoice.invoiceLastReminderSentDate IS NULL OR invoice.invoiceLastReminderSentDate < :overDueReminderDate");
//        $params["overDueReminderDate"] = $overDueReminderDate->format('Y-m-d H:i:s');
//        ////////////// EOF //////////////
//
//        /////////3. (invoiceReminderCount < maxReminderCount) ////////////////////////
//        $dql->andWhere("invoice.invoiceReminderCount IS NULL OR invoice.invoiceReminderCount < :maxReminderCount");
//        $params["maxReminderCount"] = $maxReminderCount;
//        ////////////// EOF //////////////
//
//        if( $testing ) {
//            $dql->orWhere("invoice.id=1 OR invoice.id=2");
//            //$dql->orWhere("invoice.id=1");
//        }
//
//        $query = $this->em->createQuery($dql);
//
//        $query->setParameters(
////            array(
////                "unpaid" => "Unpaid/Issued",
////                "overDueDate" => $overDueDate->format('Y-m-d H:i:s'),
////                "overDueReminderDate" => $overDueReminderDate->format('Y-m-d H:i:s'),
////                "maxReminderCount" => $maxReminderCount
////            )
//            $params
//        );
//
//        $invoices = $query->getResult();
//        //echo "$projectSpecialty count invoices=".count($invoices)."$newline";
//
//        if( $showSummary ) {
//            return $invoices;
//        }
//
//        foreach($invoices as $invoice) {
//
////            $dueDateStr = null;
////            $dueDate = $invoice->getDueDate();
////            if( $dueDate ) {
////                $dueDateStr = $dueDate->format('Y-m-d');
////            }
////            $lastSentDateStr = null;
////            $lastSentDate = $invoice->getInvoiceLastReminderSentDate();
////            if( $lastSentDate ) {
////                $lastSentDateStr = $lastSentDate->format('Y-m-d');
////            }
//            //echo "###Reminder email (ID#".$invoice->getId()."): dueDate=".$dueDateStr.", reminderConter=".$invoice->getInvoiceReminderCount().", lastSentDate=".$lastSentDateStr."$newline";
//            //$msg = "Sending reminder email for Invoice ".$invoice->getOid();
//            //": dueDate=".$dueDateStr.", lastSentDate=".$lastSentDateStr.", reminderEmailConter=".$invoice->getInvoiceReminderCount();
//
//            $logger->notice("Sending reminder email for Invoice ".$invoice->getOid());
//            $resultArr[] = $invoice->getOid();
//
//            //set last reminder date
//            $invoice->setInvoiceLastReminderSentDate(new \DateTime());
//
//            //set reminder counter
//            $invoiceReminderCounter = $invoice->getInvoiceReminderCount();
//            if( !$invoiceReminderCounter ) {
//                $invoiceReminderCounter = 0;
//            }
//            $invoiceReminderCounter = intval($invoiceReminderCounter);
//            $invoiceReminderCounter++;
//            $invoice->setInvoiceReminderCount($invoiceReminderCounter);
//
//            //save to DB (disable for testing)
//            if( !$testing ) {
//                $this->em->flush($invoice);
//            }
//
//            ////////////// send email //////////////
//            $piEmailArr = $this->getInvoicePis($invoice);
//            if (count($piEmailArr) == 0) {
//                //return "There are no PI and/or Billing Contact emails. Email has not been sent.";
//                $resultArr[] = "There are no PI and/or Billing Contact emails. Email has not been sent for Invoice ".$invoice->getOid();
//                continue;
//            }
//
//            $salesperson = $invoice->getSalesperson();
//            if ($salesperson) {
//                $salespersonEmail = $salesperson->getSingleEmail(false);
//                if ($salespersonEmail) {
//                    $ccs = $salespersonEmail;
//                }
//            }
//            if (!$ccs) {
//                $submitter = $invoice->getSubmitter();
//                if ($submitter) {
//                    $submitterEmail = $submitter->getSingleEmail(false);
//                    if ($submitterEmail) {
//                        $ccs = $submitterEmail;
//                    }
//                }
//            }
//
//            //Attachment: Invoice PDF
//            $attachmentPath = null;
//            $invoicePDF = $invoice->getRecentPDF();
//            if ($invoicePDF) {
//                $attachmentPath = $invoicePDF->getAbsoluteUploadFullPath();
//            }
//
//            //replace [[...]]
//            $transresRequest = $invoice->getTransresRequest();
//            $project = $transresRequest->getProject();
//            $invoiceReminderSubjectReady = $transresUtil->replaceTextByNamingConvention($invoiceReminderSubject,$project,$transresRequest,$invoice);
//            $invoiceReminderBodyReady = $transresUtil->replaceTextByNamingConvention($invoiceReminderBody,$project,$transresRequest,$invoice);
//
//            //                    $emails, $subject, $message, $ccs=null, $fromEmail=null
//            $emailUtil->sendEmail( $piEmailArr, $invoiceReminderSubjectReady, $invoiceReminderBodyReady, $ccs, $invoiceReminderEmail, $attachmentPath );
//
//            $sentInvoiceEmailsArr[] = "Reminder email for the unpaid Invoice ".$invoice->getOid(). " has been sent to ".implode(";",$piEmailArr) . "; ccs:".$ccs.
//            "<br>Subject: ".$invoiceReminderSubjectReady."<br>Body: ".$invoiceReminderBodyReady;
//            ////////////// EOF send email //////////////
//
//        }//foreach $invoices
//
//        //EventLog
//        if( count($sentInvoiceEmailsArr) > 0 ) {
//            $eventType = "Unpaid Invoice Reminder Email";
//            //$userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'), $result, $systemuser, $invoices, null, $eventType);
//            foreach($sentInvoiceEmailsArr as $invoiceMsg) {
//                //$msg = "Reminder email for the unpaid Invoice ".$invoice->getOid(). " has been sent.";
//                $userSecUtil->createUserEditEvent($this->container->getParameter('translationalresearch.sitename'), $invoiceMsg, $systemuser, $invoice, null, $eventType);
//            }
//        } else {
//            $logger->notice("There are no unpaid overdue invoices corresponding to the site setting parameters for ".$projectSpecialty);
//        }
//
//        $result = implode(", ",$resultArr);
//
//        return $result;
//    }

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


    public function getProductServiceByProjectSpecialty($projectSpecialty,$asCombobox=true) {

        $repository = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList');
        $dql =  $repository->createQueryBuilder("list");
        $dql->select('list');

        $dql->where("list.type = :typedef OR list.type = :typeadd");
        $dql->orderBy("list.orderinlist","ASC");

        $dqlParameters = array();
        
        $dqlParameters["typedef"] = 'default';
        $dqlParameters["typeadd"] = 'user-added';

        if( $projectSpecialty ) {
            $dql->leftJoin('list.projectSpecialties','projectSpecialties');
            $dql->andWhere("projectSpecialties.id IN (:projectSpecialtyIdsArr)");
            $projectSpecialtyIdsArr = array();
            $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $query->setMaxResults(3);

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

    public function getInvoiceItemInfoHtml( $invoiceItem ) {
//        //test
//        $testPrice = $this->toDecimal(NULL);
//        echo "testPrice=$testPrice<br>"; //testPrice=0.00
//        <tr>
//                    {% set strlimit = 60 %}
//                    {% set descriptionStr = invoiceItem.description %}
//                    {% if descriptionStr|length > strlimit %}
//                        {% set descriptionStr = descriptionStr|slice(0, strlimit) ~ '...' %}
//                    {% endif %}
//                    <td>{{ descriptionStr }}</td>
//
//                    <td>{{ invoiceItem.quantity }}</td>
//                    <td>{{ invoiceItem.itemCode }}</td>
//
//                    <td>{{ invoiceItem.unitPrice }}</td>
//
//                    {% if showAdditionalUnitPrice %}
//                        <td>{{ invoiceItem.additionalUnitPrice }}</td>
//                    {% endif %}
//
//                    <td>{{ invoiceItem.total }}</td>
//                </tr>

        $transresUtil = $this->container->get('transres_util');
        $row = "";

        //description
        $maxLen = 60;
        $descriptionStr = $invoiceItem->getDescription();
        if( $descriptionStr && strlen($descriptionStr) > $maxLen ) {
            $descriptionStr = $transresUtil->tokenTruncate($descriptionStr,$maxLen)."...";
        }
        //$row1 = $row1 . "<td>" . $descriptionStr . "</td>";

        //quantity (integer), unitPrice (type="decimal", precision=15, scale=2), additionalUnitPrice (type="decimal", precision=15, scale=2)
        $secondRaw = false;
        $itemCode = $invoiceItem->getItemCode();
        $quantity = $invoiceItem->getQuantity();
        $unitPrice = $this->toDecimal($invoiceItem->getUnitPrice());
        $additionalUnitPrice = $this->toDecimal($invoiceItem->getAdditionalUnitPrice());

        if( $quantity > 1 ) {
            if( $unitPrice != $additionalUnitPrice ) {
                $secondRaw = true;
            }
        }

        if( $secondRaw ) {
            $quantityFirst = 1;
            $quantityAdditional = $quantity - 1;
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

    //Calculate subsidy based only on the work request's products.
    //If invoice is edited manually (products added or removed, price changed, discount applied), subsidy will not be changed.
    public function calculateSubsidy($invoice) {
        $request = $invoice->getTransresRequest();
        $priceList = $request->getPriceList($request);
        $subsidy = 0;

        foreach( $request->getProducts() as $product ) {

            $quantity = $product->getCompleted();
            if( !$quantity ) {
                $quantity = $product->getRequested();
            }

            $category = $product->getCategory();

            if( $category ) {

                //default fee
                $fee = $category->getPriceFee();
                $feeAdditionalItem = $category->getPriceFeeAdditionalItem();
                $totalDefault = $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$quantity);

                //special fee
                $specialFee = $category->getPriceFee($priceList);
                $specialFeeAdditionalItem = $category->getPriceFeeAdditionalItem($priceList);
                $totalSpecial = $this->getTotalFeesByQuantity($specialFee,$specialFeeAdditionalItem,$quantity);

                if( $totalDefault && $totalSpecial && $totalDefault != $totalSpecial ) {
                    $subsidy = $subsidy + ($totalDefault - $totalSpecial);
                }

            }

        }

        $subsidy = $this->toDecimal($subsidy);

        return $subsidy;
    }
    public function calculateDefaultTotal($invoice) {
        $request = $invoice->getTransresRequest();
        $totalDefault = 0;

        foreach( $request->getProducts() as $product ) {

            $quantity = $product->getCompleted();
            if( !$quantity ) {
                $quantity = $product->getRequested();
            }

            $category = $product->getCategory();

            if( $category ) {

                //default fee
                $fee = $category->getPriceFee();
                $feeAdditionalItem = $category->getPriceFeeAdditionalItem();
                $totalDefault = $totalDefault + $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$quantity);

            }

        }

        $totalDefault = $this->toDecimal($totalDefault);

        return $totalDefault;
    }

    //"[Internal pricing] has been used to generate this invoice. Subsidy: $[XX.XX]"
    public function getSubsidyInfo($invoice,$cycle=NULL) {
        $res = "";
        $request = $invoice->getTransresRequest();
        $priceList = $request->getPriceList($request);

        $subsidy = $invoice->getSubsidy();
        if( !$subsidy ) {
            $subsidy = $this->calculateSubsidy($invoice);
        }

        if( $subsidy > 0 ) {
            //$res = $priceList->getName()." has been used to generate this invoice. Subsidy: $".$subsidy;
            $res = $priceList->getName()." applied to this invoice. Total subsidy: $".$subsidy;
            //This invoice utilizes internal pricing
            $priceListName = $priceList->getName();
            if( $priceListName ) {
                if( $cycle == 'new' || $cycle == 'edit' ) {
                    $res = "This invoice utilizes " . strtolower($priceListName) . ". Total subsidy before changing: $" . $subsidy;
                } else {
                    $res = "This invoice utilizes " . strtolower($priceListName) . ". Total subsidy: $" . $subsidy;
                }
                $res = "This invoice utilizes " . strtolower($priceListName) . ". Total subsidy: $" . $subsidy;
                //$res = "<b>".$res."</b>";
            }
        }
        
        return $res;
    }

    //Calculate subsidy based only on the invoice's invoiceItem.
    public function calculateSubsidyInvoiceItems($invoice) {
        $request = $invoice->getTransresRequest();
        $priceList = $request->getPriceList($request);
        $subsidy = 0;

        $totalInvoiceDefault = 0;
        $totalInvoiceFinal = $invoice->getTotal();
        $invoiceItems = $invoice->getInvoiceItems();

        foreach( $invoiceItems as $invoiceItem ) {

            $quantity = $invoiceItem->getQuantity();
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
                $category = $product->getCategory();
            }

            if( !$category ) {
                //echo "NULL category: itemCode=".$itemCode."<br>";
                //try to find category by itemCode
                $category = $this->em->getRepository('AppTranslationalResearchBundle:RequestCategoryTypeList')->findOneByProductId($itemCode);
                //echo "found category=[".$category."] by itemCode=$itemCode"."<br>";
            }

            if( $category ) {

                //default fee
                $fee = $category->getPriceFee();
                $feeAdditionalItem = $category->getPriceFeeAdditionalItem();
                $totalDefault = $this->getTotalFeesByQuantity($fee,$feeAdditionalItem,$quantity);

                if( !$totalDefault ) {
                    $totalDefault = $total;
                }

                $totalInvoiceDefault = $totalInvoiceDefault + $totalDefault;
                //echo "category $itemCode default total=".$totalDefault.": totalInvoiceDefault=[$totalInvoiceDefault]<br>";

                //special fee
//                $specialFee = $category->getPriceFee($priceList);
//                $specialFeeAdditionalItem = $category->getPriceFeeAdditionalItem($priceList);
//                $totalSpecial = $this->getTotalFeesByQuantity($specialFee,$specialFeeAdditionalItem,$quantity);
//                if( $totalDefault && $totalSpecial && $totalDefault != $totalSpecial ) {
//                    $subsidy = $subsidy + ($totalDefault - $totalSpecial);
//                }

            } else {
                //$totalDefault = $this->getTotalFeesByQuantity($unitPrice,$additionalUnitPrice,$quantity);
                $totalInvoiceDefault = $totalInvoiceDefault + $total;
                //echo "invoice item total=".$total.": totalInvoiceDefault=[$totalInvoiceDefault]<br>";
            }

        }//foreach

        if( $totalInvoiceDefault && $totalInvoiceFinal ) {
            //echo "calculate subsidy: totalInvoiceDefault=[$totalInvoiceDefault] - totalInvoiceFinal=[$totalInvoiceFinal]<br>";
            $subsidy = $totalInvoiceDefault - $totalInvoiceFinal;
        }

        $subsidy = $this->toDecimal($subsidy);

        return $subsidy;
    }
    public function updateInvoiceSubsidy($invoice) {
        $subsidy = $this->calculateSubsidyInvoiceItems($invoice);
        //echo "subsidy=[".$subsidy."]<br>";
        if( $subsidy > 0 ) {
            //echo "update subsidy<br>";
            $invoice->setSubsidy($subsidy);
        } else {
            //echo "Don't update subsidy<br>";
        }

        //exit("update Invoice Subsidy: subsidy=$subsidy");
        return $subsidy;
    }

}



