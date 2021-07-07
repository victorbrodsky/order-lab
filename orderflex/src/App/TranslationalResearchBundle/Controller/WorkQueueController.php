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
 * User: ch3
 * Date: 9/26/2017
 * Time: 4:49 PM
 */

namespace App\TranslationalResearchBundle\Controller;


use App\TranslationalResearchBundle\Form\FilterWorkQueuesType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use App\OrderformBundle\Form\DataTransformer\AccessionTypeTransformer;
use App\TranslationalResearchBundle\Entity\DataResult;
use App\TranslationalResearchBundle\Entity\Product;
use App\TranslationalResearchBundle\Entity\Project;
use App\TranslationalResearchBundle\Entity\TransResRequest;
use App\TranslationalResearchBundle\Form\FilterRequestType;
use App\TranslationalResearchBundle\Form\TransResRequestType;
use App\UserdirectoryBundle\Form\DataTransformer\GenericTreeTransformer;
use App\UserdirectoryBundle\Form\ListFilterType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
//use Symfony\Component\Stopwatch\Stopwatch;


/**
 * Request FormNode controller.
 */
class WorkQueueController extends OrderAbstractController
{

    /**
     * Finds and displays the filtered work queues lists
     * orderables/queue=ctp-lab /orderables/queue=misi-lab
     *
     * //Route("/orderables/{workqueue}", name="translationalresearch_work_queue_index_filter", methods={"GET"})
     *
     * @Route("/orderables/queue/{workqueue}", name="translationalresearch_work_queue_index", methods={"GET"})
     * @Route("/orderables/", name="translationalresearch_work_queue_index_filter", methods={"GET"})
     *
     * @Route("/orderables/", name="translationalresearch_work_queue_index_filter", methods={"GET"})
     * @Template("AppTranslationalResearchBundle/WorkQueue/index.html.twig")
     */
    public function myRequestsAction(Request $request, $workqueue=NULL) {

        $transresPermissionUtil = $this->container->get('transres_permission_util');

        //$productPermission = $transresPermissionUtil->hasProductPermission($action,$product);
        if( false === $transresPermissionUtil->hasProductPermission('update',null) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $routeName = $request->get('_route');
        $singleWorkqueue = NULL;
        $title = "Work Queues"; // . $workQueuesName;

        ///////////// Filter //////////////////
        $advancedFilter = 0;

        //////// create filter //////////
        //filter%5Bcategories%5D%5B%5D=58&filter%5BrequestId%5D=&filter%5BprojectSearch%5D=&filter%5BworkQueues%5D%5B%5D=1&filter%5BworkQueues%5D%5B%5D=2&filter%5Brequester%5D=&filter%5BprincipalInvestigators%5D=&filter%5BstartDate%5D=&filter%5BendDate%5D=&filter%5BfundingNumber%5D=&filter%5BfundingType%5D=&filter%5Bcomment%5D=&filter%5BsampleName%5D=&filter%5BpriceList%5D=all

        $requestId = null;
        $externalId = null;
        $submitter = null;
        $progressStates = null;
        $billingStates = null;
        $categories = null;
        //$projectFilter = null;
        $projectSearch = null;
        $searchStr = null;
        $startDate = null;
        $endDate = null;
        $principalInvestigators = null;
        $billingContact = null;
        $completedBy = null;
        $fundingNumber = null;
        $fundingType = null;
        //$filterType = null;
        $filterTitle = null;
        $projectSpecialties = array();
        $submitter = null;
        $project = null;
        //$ids = array();
        //$showOnlyMyProjects = false;
        $priceList = null;
        $workQueues = array();

        $projectSpecialtyAllowedRes = $transresUtil->getAllowedProjectSpecialty($user);
        $projectSpecialtyAllowedArr = $projectSpecialtyAllowedRes['projectSpecialtyAllowedArr'];

        if( count($projectSpecialtyAllowedArr) == 0 ) { //testing getTransResAdminEmails
            $sysAdminEmailArr = $transresUtil->getTransResAdminEmails(null,true,true); //send warning email if no specialty
            $errorMsg = "You don't have any allowed project specialty in your profile.".
                "<br>Please contact the system admin(s):".
                "<br>".implode(", ",$sysAdminEmailArr);
            //exit($errorMsg);
            //no allowed specialty
            return array(
                'filterError' => true,
                'title' => $errorMsg,
            );
        }

        $progressStateArr = $transresRequestUtil->getProgressStateArr();
        $billingStateArr = $transresRequestUtil->getBillingStateArr();

        //add "All except Drafts"
        $progressStateArr["All except Drafts"] = "All-except-Drafts";
        $progressStateArr["All except Drafts and Canceled"] = "All-except-Drafts-and-Canceled";

        //$orderableStatusArr = $transresRequestUtil->getOrderableStatusArr();

        $transresPricesList = $transresUtil->getPricesList();

        //show only permitted work queue in select box
        $permittedWorkQueues = $transresRequestUtil->getPermittedWorkQueues($user);
        //TODO: preset only allowed $permittedWorkQueues for All

        $params = array(
            'SecurityAuthChecker' => $this->get('security.authorization_checker'),
            'progressStateArr' => $progressStateArr,
            'billingStateArr' => $billingStateArr,
            //'orderableStatusArr' => $orderableStatusArr,
            'projectSpecialtyAllowedArr' => $projectSpecialtyAllowedArr,
            'transresPricesList' => $transresPricesList,
            'permittedWorkQueues' => $permittedWorkQueues
        );
        $filterform = $this->createForm(FilterWorkQueuesType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));

        $filterform->handleRequest($request);


        $requestId = $filterform['requestId']->getData();
        //$externalId = $filterform['externalId']->getData();
        $orderableStatuses = $filterform['orderableStatus']->getData();
        $progressStates = $filterform['progressState']->getData();
        $billingStates = $filterform['billingState']->getData();
        $projectSpecialties = $filterform['projectSpecialty']->getData();
        $searchStr = $filterform['comment']->getData();
        $sampleName = $filterform['sampleName']->getData();
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $fundingNumber = $filterform['fundingNumber']->getData();
        $fundingType = $filterform['fundingType']->getData();
        //$filterType = trim($request->get('type'));
        $filterTitle = trim($request->get('title'));

        //replace - with space
        //echo "filterType=$filterType <br>"; //All-COVID-19-Requests
        //$filterType = str_replace("-", " ", $filterType);
        //$filterType = str_replace("COVID 19","COVID-19",$filterType); //All COVID 19 Requests => All COVID-19 Requests
        //$filterTypeLowerCase = strtolower($filterType);

        if (isset($filterform['categories'])) {
            $categories = $filterform['categories']->getData();
        }

        $principalInvestigators = null;
        if( isset($filterform['principalInvestigators']) ) {
            $principalInvestigators = $filterform['principalInvestigators']->getData();
        }

        if( isset($filterform['requesters']) ) {
            $requesters = $filterform['requesters']->getData();
        }

//        if( isset($filterform['project']) ) {
//            $projectFilter = $filterform['project']->getData();
//        }
        if(isset($filterform['projectSearch']) ) {
            $projectSearch = $filterform['projectSearch']->getData();
        }
        if(isset($filterform['priceList']) ) {
            $priceList = $filterform['priceList']->getData();
        }
        if(isset($filterform['workQueues']) ) {
            $workQueues = $filterform['workQueues']->getData();
        }
        ///////////// EOF Filetr ///////////////

        ///////////// Predefined Filetr ///////////////
        if( $routeName == 'translationalresearch_work_queue_index') {
            //echo "workqueue=[$workqueue]<br>";

            //$workqueue = $request->query->get('workqueue');
            $workqueueEntity = NULL;

            //Case 'all'
            if( $workqueue == 'all' ) {
                return $this->redirectToRoute('translationalresearch_work_queue_index_filter',
                    array(
                        'title' => "All " . $title,
                        //'filter[projectSpecialty][]' => ""
                        'filter[]' => ""
                    )
                );
            }

            //Case 'incomplete'
            if( strpos($workqueue, '-incomplete') !== false ) {
                //echo "Case incomplete = [$workqueue]<br>";
                //status NOT equal to “Completed”
                $workqueueName = str_replace('-incomplete', '', $workqueue);
                $workqueueName = str_replace('-', ' ', $workqueueName);
                //echo "workqueueName=[$workqueueName]<br>";

                $workqueueEntity = $transresUtil->getWorkQueueObject($workqueueName);
                if( $workqueueEntity ) {

                    $incompleteFilter = array(
                        'filter[workQueues][]' => $workqueueEntity->getId(),
                        'title' => "Incomplete " . $title
                    );

                    $requestedStatusEntity = $em->getRepository('AppTranslationalResearchBundle:OrderableStatusList')->findOneByAbbreviation('requested');
                    if( $requestedStatusEntity ) {
                        $incompleteFilter['filter[orderableStatus][1]'] = $requestedStatusEntity->getId();
                    }

                    $inprogressStatusEntity = $em->getRepository('AppTranslationalResearchBundle:OrderableStatusList')->findOneByAbbreviation('in-progress');
                    if( $inprogressStatusEntity ) {
                        $incompleteFilter['filter[orderableStatus][2]'] = $inprogressStatusEntity->getId();
                    }

                    $pendingStatusEntity = $em->getRepository('AppTranslationalResearchBundle:OrderableStatusList')->findOneByAbbreviation('pending-additional-info');
                    if( $pendingStatusEntity ) {
                        $incompleteFilter['filter[orderableStatus][3]'] = $pendingStatusEntity->getId();
                    }

                    $completedStatusEntity = $em->getRepository('AppTranslationalResearchBundle:OrderableStatusList')->findOneByAbbreviation('completed');
                    if( $completedStatusEntity ) {
                        $incompleteFilter['filter[orderableStatus][4]'] = $completedStatusEntity->getId();
                    }

                    return $this->redirectToRoute('translationalresearch_work_queue_index_filter',
//                        array(
//                            'filter[workQueues][]' => $workqueueEntity->getId(),
//                            'filter[orderableStatus][]' => ''
//                        )
                        $incompleteFilter
                    );
                }
            }

            //All other cases
            if( $workqueue != 'all' ) {
                //echo "Case all<br>";
                $workqueueName = str_replace('-', ' ', $workqueue);
                $workqueueEntity = $transresUtil->getWorkQueueObject($workqueueName);
                //$workQueuesId = NULL;
                //$workQueuesName = NULL;
                //if( $workqueueEntity ) {
                //    $workQueuesId = $workqueueEntity->getId();
                //    $workQueuesName = $workqueueEntity->getName();
                //}
                //echo "workQueuesId=$workQueuesId<br>";
                if( $workqueueEntity ) {
                    return $this->redirectToRoute('translationalresearch_work_queue_index_filter',
                        array(
                            'filter[workQueues][]'=>$workqueueEntity->getId(),
                            'title' => "All " . $title
                        )
                    );
                }
            }

            return $this->redirectToRoute('translationalresearch_work_queue_index_filter');
        }
        ///////////// EOF Predefined Filetr ///////////////

        if( $filterTitle ) {
            $title = $filterTitle;
        }

        //echo "workqueue=$workqueue<br>";

        //$workQueues = $transresUtil->getWorkQueues();

//        $repository = $em->getRepository('AppTranslationalResearchBundle:TransResRequest');
//        $dql =  $repository->createQueryBuilder("transresRequest");
//        $dql->select('transresRequest');
//        $dql->leftJoin('transresRequest.products','products');

        $repository = $em->getRepository('AppTranslationalResearchBundle:Product');
        $dql =  $repository->createQueryBuilder("product");
        $dql->select('product');

        $dql->leftJoin('product.transresRequest','transresRequest');
        $dql->leftJoin('transresRequest.project','project');

        //$dql->leftJoin('project.principalInvestigators','principalInvestigators');
        //$dql->leftJoin('principalInvestigators.infos','principalInvestigatorsInfos');

        $dql->leftJoin('transresRequest.principalInvestigators','requestPrincipalInvestigators');
        $dql->leftJoin('requestPrincipalInvestigators.infos','requestPrincipalInvestigatorsInfos');
        
        $dql->leftJoin('transresRequest.submitter','requestSubmitter');
        $dql->leftJoin('requestSubmitter.infos','requestSubmitterInfos');

        $dql->leftJoin('product.orderableStatus','orderableStatus');

        //products
        $dql->leftJoin('product.category','category');
        $dql->leftJoin('category.workQueues','workQueues');

        //prices
        $dql->leftJoin('category.prices','prices');
        $dql->leftJoin('prices.workQueues','priceWorkQueues');

//        $dql->leftJoin('transresRequest.submitter','submitter');
//        $dql->leftJoin('transresRequest.contact','contact');
//        $dql->leftJoin('transresRequest.project','project');
//        $dql->leftJoin('submitter.infos','submitterInfos');
//        $dql->leftJoin('transresRequest.principalInvestigators','principalInvestigators');

        $dqlParameters = array();

        //$dql->andWhere("transresRequest.id IN (:ids)");
        //$dqlParameters["ids"] = $ids;

        //$workqueueName = str_replace('-',' ',$workqueue);
        //$workqueueEntity = $transresUtil->getWorkQueueObject($workqueueName);
        //$workQueuesId = NULL;
        //$workQueuesName = NULL;
        //if( $workqueueEntity ) {
        //    $workQueuesId = $workqueueEntity->getId();
        //    $workQueuesName = $workqueueEntity->getName();
        //}
        //echo "workQueuesId=$workQueuesId<br>";

        //////////// Process Filter //////////////////

        $dql->andWhere("transresRequest IS NOT NULL");
        //$dql->andWhere("workQueues.id IN (:workQueues) OR priceWorkQueues.id IN (:workQueues)");
        //$dqlParameters["workQueues"] = $workQueuesId;

        if( $workQueues && count($workQueues) > 0 ) {
            $dql->andWhere("workQueues.id IN (:workQueues) OR priceWorkQueues.id IN (:workQueues)");
            $dqlParameters["workQueues"] = $workQueues;

            $workQueueNameArr = array();
            foreach($workQueues as $workQueue) {
                $workQueueNameArr[] = $workQueue->getName();
            }
            //$workqueueEntity = $transresUtil->getWorkQueueObject($workqueueName);
            if( count($workQueueNameArr) > 0 ) {
                $title = $title . " " . implode(", ",$workQueueNameArr);
            }

            if( count($workQueues) == 1 ) {
                $singleWorkqueue = $workQueues[0];
            }
        }

        if( $categories && count($categories) > 0 ) {
            $dql->andWhere("category.id IN (:categoryIds)");
            $dqlParameters["categoryIds"] = $categories;
        }

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $projectSpecialtyIdsArr = array();
            foreach($projectSpecialties as $projectSpecialty) {
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        }

        if( $requestId ) {
            $dql->andWhere('LOWER(transresRequest.oid) LIKE LOWER(:requestId)');
            $dqlParameters['requestId'] = "%".$requestId."%";
        }

        if( $projectSearch ) {
            $projectId = null;
            if (strpos($projectSearch, ', ') !== false) {
                //get id
                $projectSearchArr = explode(", ",$projectSearch);
                if( count($projectSearchArr) > 1 ) {
                    $projectOid = $projectSearchArr[0];
                    //get id (remove APCP or HP)
                    $projectId = (int) filter_var($projectOid, FILTER_SANITIZE_NUMBER_INT);
                }
                if( !$projectId ) {
                    $projectOid = $projectSearch;
                    //get id (remove APCP or HP)
                    $projectId = (int) filter_var($projectOid, FILTER_SANITIZE_NUMBER_INT);
                }
            } else {
                //get id (remove APCP or HP)
                $projectId = (int) filter_var($projectSearch, FILTER_SANITIZE_NUMBER_INT);
            }

            if( $projectId ) {
                //echo "projectId=[".$projectId."] <br>";
                $dql->andWhere("project.id = :projectId");
                $dqlParameters["projectId"] = $projectId;
            }
        }

        if( $orderableStatuses && count($orderableStatuses) > 0 ) {
            $dql->andWhere("orderableStatus.id IN (:orderableStatusIds)");
            $dqlParameters["orderableStatusIds"] = $orderableStatuses;
        }

        if( $requesters ) {
            //TODO:
//            $dql->leftJoin('project.principalInvestigators','projectPrincipalInvestigators');
//            $dql->leftJoin('project.principalIrbInvestigator','projectPrincipalIrbInvestigator');
//            $dql->leftJoin('project.coInvestigators','projectCoInvestigators');
//            $dql->leftJoin('project.pathologists','projectPathologists');
//            $dql->leftJoin('project.billingContact','projectBillingContact');
//            $dql->leftJoin('project.contacts','projectContacts');
//            $dql->leftJoin('project.submitter','projectSubmitter');
//            $dql->leftJoin('project.updateUser','projectUpdateUser');

            $dql->leftJoin('transresRequest.updateUser','requestUpdateUser');
            $dql->leftJoin('transresRequest.completedBy','requestCompletedBy');
            $dql->leftJoin('transresRequest.contact','requestContact');


//            $dql->andWhere(
//                //Request requesters
//                "requestPrincipalInvestigators.id = :userId OR ".
//                "requestContact.id = :userId OR ".
//                "requestSubmitter.id = :userId"
//                //project's requesters
//                . " OR " .
//                "projectPrincipalInvestigators.id = :userId OR ".
//                "projectPrincipalIrbInvestigator.id = :userId OR ".
//                "projectCoInvestigators.id = :userId OR ".
//                "projectPathologists.id = :userId OR ".
//                "projectContacts.id = :userId OR ".
//                "projectBillingContact.id = :userId OR ".
//                "projectSubmitter.id = :userId"
//            );

            $dql->andWhere(
                //Request requesters
                "requestPrincipalInvestigators.id IN (:userId) OR ".
                "requestContact.id IN (:userId) OR ".
                "requestSubmitter.id IN (:userId)"
                //project's requesters
//                . " OR " .
//                "projectPrincipalInvestigators.id = :userId OR ".
//                "projectPrincipalIrbInvestigator.id = :userId OR ".
//                "projectCoInvestigators.id = :userId OR ".
//                "projectPathologists.id = :userId OR ".
//                "projectContacts.id = :userId OR ".
//                "projectBillingContact.id = :userId OR ".
//                "projectSubmitter.id = :userId"
            );

            $dqlParameters["userId"] = $requesters;
        }

        if( $fundingNumber ) {
            $dql->andWhere("LOWER(transresRequest.fundedAccountNumber) LIKE LOWER(:fundedAccountNumber)");
            $dqlParameters["fundedAccountNumber"] = "%".$fundingNumber."%";
            $advancedFilter++;
        }

        if( $fundingType ) {
            //echo "fundingType=" . $fundingType . "<br>";
            if( $fundingType == "Funded" ) {
                $dql->andWhere("transresRequest.fundedAccountNumber IS NOT NULL");
                $advancedFilter++;
            }
            if( $fundingType == "Non-Funded" ) {
                $dql->andWhere("transresRequest.fundedAccountNumber IS NULL");
                $advancedFilter++;
            }
        }

        if( $sampleName ) {
            $dql->leftJoin('transresRequest.dataResults','dataResults');
            $dql->andWhere("LOWER(dataResults.barcode) LIKE LOWER(:sampleName)");
            $dqlParameters["sampleName"] = "%".$sampleName."%";
            $advancedFilter++;
        }

        if( $principalInvestigators && count($principalInvestigators)>0 ) {
            $dql->andWhere("requestPrincipalInvestigators.id IN (:principalInvestigators)");

            $principalInvestigatorsIdsArr = array();
            foreach($principalInvestigators as $principalInvestigator) {
                //echo "PI=".$principalInvestigator."; id=".$principalInvestigator->getId()."<br>";
                $principalInvestigatorsIdsArr[] = $principalInvestigator->getId();
            }
            $dqlParameters["principalInvestigators"] = $principalInvestigatorsIdsArr;   //implode(",",$principalInvestigatorsIdsArr);

            //$dqlParameters["principalInvestigators"] = $principalInvestigators;

            $advancedFilter++;
        }

        if( $progressStates && count($progressStates) > 0 ) {
            $allExceptDraft = "";
            if( in_array("All-except-Drafts", $progressStates )) {
                $allExceptDraft = " OR transresRequest.progressState != 'draft' OR transresRequest.progressState IS NULL";
            }
            if( in_array("All-except-Drafts-and-Canceled", $progressStates )) {
                $allExceptDraft = " OR (transresRequest.progressState != 'draft' AND transresRequest.progressState != 'canceled') OR transresRequest.progressState IS NULL";
            }
            $dql->andWhere("transresRequest.progressState IN (:progressStates)".$allExceptDraft);
            $dqlParameters["progressStates"] = $progressStates;
            $advancedFilter++;
        }

        if( $billingStates && count($billingStates)>0 ) {
            //$dql->andWhere("transresRequest.billingState IN (:billingStates)");
            //$dqlParameters["billingStates"] = implode(",",$billingStates);
            $dql->andWhere("transresRequest.billingState IN (:billingStates)");
            $dqlParameters["billingStates"] = $billingStates;
            $advancedFilter++;
        }

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('transresRequest.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if( $endDate ) {
            $endDate->modify('+1 day');
            $dql->andWhere('transresRequest.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }

        if ($searchStr) {
            $dql->leftJoin('transresRequest.dataResults','dataResults');
            //$dql->andWhere("(category.name LIKE :categoryStr OR category.productId LIKE :categoryStr OR category.feeUnit LIKE :categoryStr OR category.fee LIKE :categoryStr)");
            $commentCriterion = "LOWER(product.comment) LIKE LOWER(:searchStr) OR LOWER(product.note) LIKE LOWER(:searchStr) OR LOWER(transresRequest.comment) LIKE LOWER(:searchStr) OR LOWER(dataResults.comment) LIKE LOWER(:searchStr)";
            $dqlParameters["searchStr"] = "%".$searchStr."%";

            //add search fos bundle comments
            $requestCommentIds = $transresRequestUtil->getRequestIdsByFosComment($searchStr);
            if( count($requestCommentIds) > 0 ) {
                $commentCriterion = $commentCriterion . " OR " . "transresRequest.id IN (:requestCommentIds)";
                $dqlParameters["requestCommentIds"] = $requestCommentIds;
            }

            $dql->andWhere($commentCriterion);

            $advancedFilter++;
        }

        if( $priceList ) {
            if( $priceList != 'all' ) {
                $dql->leftJoin('project.priceList','priceList');
                if( $priceList == 'default' ) {
                    $dql->andWhere("priceList.id IS NULL");
                } else {
                    $dql->andWhere("priceList.id = :priceListId");
                    $dqlParameters["priceListId"] = $priceList;
                }
                $advancedFilter++;
            }
        }

        //////////// EOF Process Filter //////////////////

        $limit = 20;
        $query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        //echo "query=".$query->getSql()."<br>";

        $paginationParams = array(
            'defaultSortFieldName' => 'product.createDate',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

//        if( $timer ) {
//            $event = $stopwatch->stop('createQueryBuilder');
//            echo "createQueryBuilder duration: ".($event->getDuration()/1000)." sec<br>";
//
//            //$time_pre2 = microtime(true);
//            $stopwatch->start('PaginatorResult');
//        }

        //TESTING
        //$query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        $paginator  = $this->get('knp_paginator');
        $products = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );

        $matchingStrProductstIds = $transresUtil->getMatchingProductArrByDqlParameters($dql,$dqlParameters);
        $allProductsts = count($matchingStrProductstIds);
        $allGlobalRequests = $transresUtil->getTotalProductsCount();
        $title = $title . " (Matching " . $allProductsts . ", Total " . $allGlobalRequests . ")";

        $formArray = array(
            'products' => $products,
            'title' => $title,
            'workqueue' => $singleWorkqueue,
            'filterform' => $filterform->createView(),
            'advancedFilter' => $advancedFilter,
        );

        return $formArray;

    }





    /**
     * @Route("/orderable/show/{id}", name="translationalresearch_product_show", methods={"GET"})
     * @Template("AppTranslationalResearchBundle/WorkQueue/new.html.twig")
     */
    public function showAction(Request $request, Product $product)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $title = "Product ".$product;
        $cycle = "show";

        $transresRequest = $product->getTransresRequest();
        $project = $transresRequest->getProject();

        //$productPermission = $transresPermissionUtil->hasProductPermission($action,$product);
        if( false === $transresPermissionUtil->hasProductPermission('update',$product) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        return array(
            'product' => $product,
            'transresRequest' => $transresRequest,
            'project' => $project,
            'title' => $title,
//            'form' => $form->createView(),
            'cycle' => $cycle,
//            'title' => "Work Request ".$transresRequest->getOid() . $feeHtml,
//            'routeName' => $request->get('_route'),
//            //'handsometableData' => json_encode($jsonData)
//            'handsometableData' => $jsonData,
//            'showPackingSlip' => $showPackingSlip,
//            'defaultAccessionType' => null,
//            'defaultAntibodyType' => null,
            //'delete_form' => $deleteForm->createView(),
            //'review_forms' => $reviewFormViews
        );
    }

    /**
     * @Route("/update-product-orderable-status/{id}", name="translationalresearch_product_update_orderablestatus", methods={"GET"})
     */
    public function updateOrderableStatusAction(Request $request, Product $product)
    {
        $transresPermissionUtil = $this->container->get('transres_permission_util');
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        //$productPermission = $transresPermissionUtil->hasProductPermission($action,$product);
        if( false === $transresPermissionUtil->hasProductPermission('update',$product) ) {
            //exit("no permisssion");
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $orderableStatusName = $request->query->get('orderablestatus');
        $workqueueId = $request->query->get('workqueue');
        //echo "workqueue=$workqueue<br>";
        $set = false;

        $orderableStatus = $em->getRepository('AppTranslationalResearchBundle:OrderableStatusList')->findOneByName($orderableStatusName);
        if( !$orderableStatus ) {
            $set = false;
        }

        if( $orderableStatus ) {
            $product->setOrderableStatus($orderableStatus);
            $em->flush();
            $set = true;
        }

        $transresRequest = $product->getTransresRequest();

        $transresRequestUtil->setWorkRequestStatusByOrderableStatus($transresRequest);

        if( $set ) {
            $msg = "Success: Orderable status for product '".$product."' has been updated to $orderableStatusName";
        } else {
            $msg = "Error: Orderable status for product '".$product."' has not been updated to $orderableStatusName";
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msg
        );

        //EventLog
        $eventType = "Orderable Status Changed";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        //translationalresearch_work_queue_index_filter
        $lowercaseName = NULL;
        if( $workqueueId ) {
            $workQueue = $em->getRepository('AppTranslationalResearchBundle:WorkQueueList')->find($workqueueId);
            if ($workQueue) {
                $lowercaseName = strtolower($workQueue->getName()); //ctp lab
                $lowercaseName = str_replace(' ', '-', $lowercaseName);
            }
        }
        if( $lowercaseName ) {
            return $this->redirectToRoute('translationalresearch_work_queue_index',array('workqueue'=>$lowercaseName));
        } else {
            return $this->redirectToRoute('translationalresearch_work_queue_index_filter');
        }

    }

}
