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
     * @Route("/orderables/{workqueue}", name="translationalresearch_work_queue_index_filter", methods={"GET"})
     * @Template("AppTranslationalResearchBundle/WorkQueue/index.html.twig")
     */
    public function myRequestsAction(Request $request, $workqueue) {

        $transresPermissionUtil = $this->container->get('transres_permission_util');

        //$productPermission = $transresPermissionUtil->hasProductPermission($action,$product);
        if( false === $transresPermissionUtil->hasProductPermission('update',null) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        ///////////// Filter //////////////////
        $advancedFilter = 0;

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

        $transresPricesList = $transresUtil->getPricesList();

        $params = array(
            'SecurityAuthChecker' => $this->get('security.authorization_checker'),
            'progressStateArr' => $progressStateArr,
            'billingStateArr' => $billingStateArr,
            'projectSpecialtyAllowedArr' => $projectSpecialtyAllowedArr,
            'transresPricesList' => $transresPricesList
        );
        $filterform = $this->createForm(FilterWorkQueuesType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));

        $filterform->handleRequest($request);
        
        ///////////// EOF Filetr ///////////////

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

        $dql->leftJoin('project.principalInvestigators','principalInvestigators');
        $dql->leftJoin('principalInvestigators.infos','principalInvestigatorsInfos');

        $dql->leftJoin('transresRequest.submitter','submitter');
        $dql->leftJoin('submitter.infos','submitterInfos');

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

        $workqueueName = str_replace('-',' ',$workqueue);

        $workqueueEntity = $transresUtil->getWorkQueueObject($workqueueName);
        $workQueuesId = NULL;
        $workQueuesName = NULL;
        if( $workqueueEntity ) {
            $workQueuesId = $workqueueEntity->getId();
            $workQueuesName = $workqueueEntity->getName();
        }
        //echo "workQueuesId=$workQueuesId<br>";

        $dql->andWhere("transresRequest IS NOT NULL");
        $dql->andWhere("workQueues.id IN (:workQueues) OR priceWorkQueues.id IN (:workQueues)");
        $dqlParameters["workQueues"] = $workQueuesId;

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

        $title = "Work Queues " . $workQueuesName;

        $matchingStrProductstIds = $transresUtil->getMatchingProductArrByDqlParameters($dql,$dqlParameters);
        $allProductsts = count($matchingStrProductstIds);
        $allGlobalRequests = $transresUtil->getTotalProductsCount();
        $title = $title . " (Matching " . $allProductsts . ", Total " . $allGlobalRequests . ")";

        $formArray = array(
            'products' => $products,
            'title' => $title,
            'workqueue' => $workqueue,
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
        //$transresUtil = $this->container->get('transres_util');
        //$transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        //$productPermission = $transresPermissionUtil->hasProductPermission($action,$product);
        if( false === $transresPermissionUtil->hasProductPermission('update',$product) ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $orderableStatusName = $request->query->get('orderablestatus');
        $workqueue = $request->query->get('workqueue');
        echo "workqueue=$workqueue<br>";
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

        if( $set ) {
            $msq = "Success: Orderable status for product '".$product."' has been updated to $orderableStatusName";
        } else {
            $msq = "Error: Orderable status for product '".$product."' has not been updated to $orderableStatusName";
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            $msq
        );

        //translationalresearch_work_queue_index_filter
        return $this->redirectToRoute('translationalresearch_work_queue_index_filter',array('workqueue'=>$workqueue));
    }

}
