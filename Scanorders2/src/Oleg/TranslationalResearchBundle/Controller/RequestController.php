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

namespace Oleg\TranslationalResearchBundle\Controller;


use Doctrine\Common\Collections\ArrayCollection;
use Oleg\TranslationalResearchBundle\Entity\Product;
use Oleg\TranslationalResearchBundle\Entity\Project;
use Oleg\TranslationalResearchBundle\Entity\TransResRequest;
use Oleg\TranslationalResearchBundle\Form\FilterRequestType;
use Oleg\TranslationalResearchBundle\Form\TransResRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Request FormNode controller.
 */
class RequestController extends Controller
{

    /**
     * Creates a new request entity with formnode.
     *
     * @Route("/project/{id}/request/new/", name="translationalresearch_request_new")
     * @Route("/request/new/", name="translationalresearch_new_standalone_request")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newFormNodeAction(Request $request, Project $project=null)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transResFormNodeUtil = $this->get('transres_formnode_util');
        $transresRequestUtil = $this->get('transres_request_util');
        $transresUtil = $this->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $cycle = "new";

        $formnode = false;

        $testing = false;
        //$testing = true;

        $transresRequest = $this->createRequestEntity($user,null);

        //add one Product or Service
        $product = new Product($user);
        $transresRequest->addProduct($product);

        $title = "Create a new Request";

        if( $project ) {
            $transresRequest->setProject($project);
            $title = "Create a new Request for project ID ".$project->getOid();

            $projectFundedAccountNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
            if( $projectFundedAccountNumber ) {
                $transresRequest->setFundedAccountNumber($projectFundedAccountNumber);
            }

            //pre-populate Request's Billing Contact by Project's Billing Contact
            if( $project->getBillingContact() ) {
                $transresRequest->setContact($project->getBillingContact());
            }

            //pre-populate Request's Support End Date by Project's IRB Expiration Date
            if( $project->getIrbExpirationDate() ) {
                $transresRequest->setSupportEndDate($project->getIrbExpirationDate());
            }

            //pre-populate PIs
            $transreqPis = $project->getPrincipalInvestigators();
            foreach( $transreqPis as $transreqPi ) {
                $transresRequest->addPrincipalInvestigator($transreqPi);
            }
        }

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //new

        $messageTypeId = true;//testing
        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        $messageCategory = $transresRequest->getMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //exit("Project submitted");

            $project = $transresRequest->getProject();

            //set project's funded account number
            $changedMsg = "";
            //$changedProjectFundNumber = false;
            $originalFundedAccountNumber = $project->getFundedAccountNumber();
            $fundedAccountNumber = $transresRequest->getFundedAccountNumber();
            if( $fundedAccountNumber && $fundedAccountNumber != $originalFundedAccountNumber ) {
                $project->setFundedAccountNumber($fundedAccountNumber);
                //set formnode field
                $transresRequestUtil->setValueToFormNodeProject($project, "If funded, please provide account number", $fundedAccountNumber);
                //$changedProjectFundNumber = true;
                $changedMsg = $changedMsg . "<br>Project's Account Fund Number has been updated: ";
                $changedMsg = $changedMsg . "<br>Original account number " . $originalFundedAccountNumber;
                $changedMsg = $changedMsg . "<br>New account number " . $project->getFundedAccountNumber();
            }

            //set submitter to product
            foreach($transresRequest->getProducts() as $product) {
                if( !$product->getSubmitter() ) {
                    $product->setSubmitter($user);
                }
            }

            //new
            if ($form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName()) {
                //Save Project as Draft => state='draft'
                $transresRequest->setProgressState('draft');
                $transresRequest->setBillingState('draft');
            }

            //new
            if ($form->getClickedButton() && 'saveAsComplete' === $form->getClickedButton()->getName()) {
                //Complete Submission => state='submit'
                $transresRequest->setProgressState('active');
                $transresRequest->setBillingState('active');
            }

            if( !$testing ) {
                $em->persist($transresRequest);
                $em->flush();

                //set oid
                $transresRequest->generateOid();
                $em->flush();
            }

            //process form nodes
            if( $formnode ) {
                $formNodeUtil = $this->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request, $transresRequest->getMessageCategory(), $transresRequest, $testing);
            }

            $msg = "New Request has been successfully submitted for the project ID ".$project->getOid();
            $msg = $msg . $changedMsg;

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Request Created";
            $msg = "New Request with ID ".$transresRequest->getOid()." has been successfully submitted for the project ID ".$project->getOid();
            $msg = $msg . $changedMsg;
            $transresUtil->setEventLog($transresRequest,$eventType,$msg);

            $subject = "New Request has been successfully submitted for the project ID ".$project->getOid();
            $transresRequestUtil->sendRequestNotificationEmails($transresRequest,$subject,$msg,$testing);

            return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
        }


        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId,
            'routeName' => $request->get('_route')
        );
    }



    /**
     * Get TransResRequest Edit page
     *
     * @Route("/request/edit/{id}", name="translationalresearch_request_edit")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TransResRequest $transresRequest)
    {

        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transResFormNodeUtil = $this->get('transres_formnode_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $formnode = false;
        $cycle = "edit";
        $formtype = "translationalresearch-request";

        $class = new \ReflectionClass($transresRequest);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //Oleg\UserdirectoryBundle\Entity

        $testing = false;
        //$testing = true;

        $project = $transresRequest->getProject();

        //$projectFundedAccountNumber = $transResFormNodeUtil->getProjectFormNodeFieldByName($project,"If funded, please provide account number");
        //if( $projectFundedAccountNumber ) {
        //    $transresRequest->setFundedAccountNumber($projectFundedAccountNumber);
        //}

        $transresRequest = $this->createRequestEntity($user,$transresRequest);

        // Create an ArrayCollection of the current Tag objects in the database
        $originalProducts = new ArrayCollection();
        foreach($transresRequest->getProducts() as $product) {
            $originalProducts->add($product);
        }

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //edit

        $messageTypeId = true;//testing
        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        //$categoryStr = "Pathology Call Log Entry";
        $messageCategory = $transresRequest->getMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //exit("Request update submitted");

            //set project's funded account number
            $changedMsg = "";
            //$changedProjectFundNumber = false;
            $originalFundedAccountNumber = $project->getFundedAccountNumber();
            $fundedAccountNumber = $transresRequest->getFundedAccountNumber();
            if( $fundedAccountNumber && $fundedAccountNumber != $originalFundedAccountNumber ) {
                $project->setFundedAccountNumber($fundedAccountNumber);
                //set formnode field
                $transresRequestUtil->setValueToFormNodeProject($project, "If funded, please provide account number", $fundedAccountNumber);
                //$changedProjectFundNumber = true;
                $changedMsg = $changedMsg . "<br>Project's Account Fund Number has been updated: ";
                $changedMsg = $changedMsg . "<br>Original account number " . $originalFundedAccountNumber;
                $changedMsg = $changedMsg . "<br>New account number " . $project->getFundedAccountNumber();
            }

            //update updateBy
            $transresRequest->setUpdateUser($user);

            //process Product or Service sections
            // remove the relationship between the tag and the Task
            foreach($originalProducts as $product) {
                if( false === $transresRequest->getProducts()->contains($product) ) {
                    // remove the Task from the Tag
                    $transresRequest->getProducts()->removeElement($product);
                    // if it was a many-to-one relationship, remove the relationship like this
                    $product->setTransresRequest(null);
                    $em->persist($product);
                    // if you wanted to delete the Tag entirely, you can also do that
                    $em->remove($product);
                }
            }


            //edit
            if ($form->getClickedButton() && 'saveAsDraft' === $form->getClickedButton()->getName()) {
                //Save Project as Draft => state='draft'
                $transresRequest->setProgressState('draft');
                $transresRequest->setBillingState('draft');
            }

            //edit
            if ($form->getClickedButton() && 'saveAsComplete' === $form->getClickedButton()->getName()) {
                //Complete Submission => state='submit'
                if( $transresRequest->getProgressState() == 'draft' ) {
                    $transresRequest->setProgressState('active');
                    $transresRequest->setBillingState('active');
                }
            }

            if( !$testing ) {
                $em->persist($transresRequest);
                $em->flush();
            }

            //testing
//            print "<pre>";
//            var_dump($_POST);
//            print "</pre><br>";
//            echo "formnode[420]=".$_POST['formnode[420]']."<br>";
//            echo "formnode[421]=".$_POST['formnode[421]']."<br>";

            //process form nodes
            if( $formnode ) {
                $formNodeUtil = $this->get('user_formnode_utility');
                $formNodeUtil->processFormNodes($request, $transresRequest->getMessageCategory(), $transresRequest, $testing); //testing
            }

            $msg = "Request ".$transresRequest->getOid()." has been successfully updated for the project ID ".$project->getOid();
            $msg = $msg . $changedMsg;

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Request Updated";
            $msg = "Request ".$transresRequest->getOid() ." has been updated.";
            $msg = $msg . $changedMsg;
            $transresUtil->setEventLog($transresRequest,$eventType,$msg);

            $subject = "Request ".$transresRequest->getOid()." has been successfully updated for the project ID ".$project->getOid();
            $transresRequestUtil->sendRequestNotificationEmails($transresRequest,$subject,$msg,$testing);

            return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
        }

        $eventType = "Request Viewed";
        $msg = "Request ".$transresRequest->getOid() ." has been viewed on the edit page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'formtype' => $formtype,
            'title' => "Edit Request ".$transresRequest->getOid(),
            'triggerSearch' => 0,
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId,
            'entityNamespace' => $classNamespace,
            'entityName' => $className,
            'entityId' => $transresRequest->getId(),
            'sitename' => $this->container->getParameter('translationalresearch.sitename'),
            'routeName' => $request->get('_route')
        );
    }

    /**
     * Finds and displays a request entity.
     *
     * @Route("/request/show/{id}", name="translationalresearch_request_show")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
     * @Method("GET")
     */
    public function showAction(Request $request, TransResRequest $transresRequest)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        //$em = $this->getDoctrine()->getManager();
        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $cycle = "show";
        $project = $transresRequest->getProject();

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //show

        //$deleteForm = $this->createDeleteForm($project);

        //create a review form (for example, IrbReview form if logged in user is a reviewer or reviewer delegate)
        //1) if project is in the review state: irb_review, admin_review, committee_review or final_review
        //2) if the current user is added to this project as the reviewer for the state above
        //$reviewFormViews = $transresUtil->getReviewForm($project,$user);

        $feeHtml = null;
        $fee = $transresRequestUtil->getTransResRequestFeeHtml($transresRequest);
        if( $fee ) {
            $feeHtml = " (fee $".$fee.")";
        }

        $eventType = "Request Viewed";
        $msg = "Request ".$transresRequest->getOid() ." has been viewed on the show review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Request ".$transresRequest->getOid() . $feeHtml,
            //'delete_form' => $deleteForm->createView(),
            //'review_forms' => $reviewFormViews
        );
    }

    /**
     * Finds and displays all project's requests
     *
     * @Route("/project/{id}/requests", name="translationalresearch_request_index")
     * @Template("OlegTranslationalResearchBundle:Request:index.html.twig")
     * @Method("GET")
     */
    public function indexAction(Request $request, Project $project)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $routeName = $request->get('_route');
        $title = "Requests for the project ID ".$project->getOid();
        $formnode = false;

        //////// create filter //////////
        $progressStateArr = $transresRequestUtil->getProgressStateArr();
        $billingStateArr = $transresRequestUtil->getBillingStateArr();
        $params = array('progressStateArr'=>$progressStateArr,'billingStateArr'=>$billingStateArr,'routeName'=>$routeName);
        $filterform = $this->createForm(FilterRequestType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));

        $filterform->handleRequest($request);
        $submitter = $filterform['submitter']->getData();
        $progressStates = $filterform['progressState']->getData();
        $billingStates = $filterform['billingState']->getData();
        $category = $filterform['category']->getData();
        $projectSpecialty = $filterform['projectSpecialty']->getData();
        $projectFilter = $filterform['project']->getData();

        $searchStr = $filterform['comment']->getData();
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $principalInvestigators = $filterform['principalInvestigators']->getData();
        $accountNumber = $filterform['accountNumber']->getData();
        $billingContact = $filterform['billingContact']->getData();

        //////// EOF create filter //////////

        $advancedFilter = 0;

        //////////////// get Requests IDs with the form node filter ////////////////
        $ids = array();
        if( $formnode ) {
            if ($category) {
                $categoryIds = $transresRequestUtil->getRequestIdsFormNodeByCategory($category);
                $ids = array_merge($ids, $categoryIds);
            }
            if ($searchStr) {
                $commentIds = $transresRequestUtil->getRequestIdsFormNodeByComment($searchStr);
                $ids = array_merge($ids, $commentIds);
            }
            if (count($ids) > 0) {
                $ids = array_unique($ids);
                //print_r($ids);
            }
        }
        //////////////// EOF get Requests IDs with the form node filter ////////////////

        $repository = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest');

        $dql->leftJoin('transresRequest.submitter','submitter');
        $dql->leftJoin('transresRequest.contact','contact');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('submitter.infos','submitterInfos');
        $dql->leftJoin('transresRequest.principalInvestigators','principalInvestigators');

        $dqlParameters = array();

        $dql->andWhere("project.id = :projectId");

        $dqlParameters["projectId"] = $project->getId();

        ///////// filters //////////
        if( $submitter ) {
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
        }

        if( $projectSpecialty ) {
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $dql->andWhere("projectSpecialty.id = :projectSpecialtyId");
            $dqlParameters["projectSpecialtyId"] = $projectSpecialty->getId();
        }

        if( $projectFilter ) {
            $dql->andWhere("project.id = :projectId");
            $dqlParameters["projectId"] = $projectFilter->getId();
        }

        if( $progressStates && count($progressStates)>0 ) {
            $dql->andWhere("transresRequest.progressState IN (:progressStates)");
            $dqlParameters["progressStates"] = implode(",",$progressStates);
        }

        if( $billingStates && count($billingStates)>0 ) {
            $dql->andWhere("transresRequest.billingState IN (:billingStates)");
            $dqlParameters["billingStates"] = implode(",",$billingStates);
        }

        if( !$formnode ) {
            $dql->leftJoin('transresRequest.products','products');
            if ($category) {
                $dql->leftJoin('products.category','category');
                $dql->andWhere("category.id = :categoryId");
                $dqlParameters["categoryId"] = $category;
            }
            if ($searchStr) {
                //$dql->andWhere("(category.name LIKE :categoryStr OR category.productId LIKE :categoryStr OR category.feeUnit LIKE :categoryStr OR category.fee LIKE :categoryStr)");
                $dql->andWhere("products.comment LIKE :searchStr");
                $dqlParameters["searchStr"] = "%".$searchStr."%";
                $advancedFilter++;
            }
        }

        if( $principalInvestigators && count($principalInvestigators)>0 ) {
            $dql->andWhere("principalInvestigators.id IN (:principalInvestigators)");
            $principalInvestigatorsIdsArr = array();
            foreach($principalInvestigators as $principalInvestigator) {
                $principalInvestigatorsIdsArr[] = $principalInvestigator->getId();
            }
            $dqlParameters["principalInvestigators"] = implode(",",$principalInvestigatorsIdsArr);
            $advancedFilter++;
        }

        if( $submitter ) {
            //echo "submitter=".$submitter->getId()."<br>";
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
        }

        if( $startDate ) {
            //echo "startDate=" . $startDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('transresRequest.createDate >= :startDate');
            $dqlParameters['startDate'] = $startDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }
        if( $endDate ) {
            $endDate->modify('+1 day');
            //echo "endDate=" . $endDate->format('Y-m-d H:i:s') . "<br>";
            $dql->andWhere('transresRequest.createDate <= :endDate');
            $dqlParameters['endDate'] = $endDate->format('Y-m-d H:i:s');
            $advancedFilter++;
        }

        if( $billingContact ) {
            $dql->andWhere("contact.id = :billingContactId");
            $dqlParameters["billingContactId"] = $billingContact->getId();
            $advancedFilter++;
        }

        if( $accountNumber ) {
            $dql->andWhere("transresRequest.fundedAccountNumber = :fundedAccountNumber");
            $dqlParameters["fundedAccountNumber"] = $accountNumber;
            $advancedFilter++;
        }

        if( count($ids) > 0 ) {
            //$dql->andWhere("transresRequest.id IN (:ids)");
            //$dqlParameters["ids"] = implode(",",$ids);
            $dql->andWhere("transresRequest.id IN (".implode(",",$ids).")");
        }
        ///////// EOF filters //////////

        $limit = 30;
        $query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        //echo "query=".$query->getSql()."<br>";

        $paginationParams = array(
            'defaultSortFieldName' => 'transresRequest.id',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

        $paginator  = $this->get('knp_paginator');
        $transresRequests = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );
        //echo "transresRequests count=".count($transresRequests)."<br>";

        $requestTotalFeeHtml = $transresRequestUtil->getTransResRequestTotalFeeHtml($project);
        if( $requestTotalFeeHtml ) {
            $requestTotalFeeHtml = " (". $requestTotalFeeHtml . ")";
        }

        return array(
            'transresRequests' => $transresRequests,
            'project' => $project,
            'filterform' => $filterform->createView(),
            'title' => $title . $requestTotalFeeHtml,
            'requestTotalFeeHtml' => null, //$requestTotalFeeHtml
            'advancedFilter' => $advancedFilter
        );
    }

    /**
     * Finds and displays all my requests
     *
     * @Route("/my-requests", name="translationalresearch_my_requests")
     * @Route("/all-requests", name="translationalresearch_all_requests")
     * @Template("OlegTranslationalResearchBundle:Request:all-requests.html.twig")
     * @Method("GET")
     */
    public function myRequestsAction(Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER')) {
            return $this->redirect($this->generateUrl($this->container->getParameter('translationalresearch.sitename') . '-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $routeName = $request->get('_route');
        $title = "My Requests";
        $formnode = false;

        $advancedFilter = 0;

        //////// create filter //////////
        $progressStateArr = $transresRequestUtil->getProgressStateArr();
        $billingStateArr = $transresRequestUtil->getBillingStateArr();
        $params = array('progressStateArr'=>$progressStateArr,'billingStateArr'=>$billingStateArr,'routeName'=>$routeName);
        $filterform = $this->createForm(FilterRequestType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));

        $filterform->handleRequest($request);
        $submitter = null;

        $submitter = $filterform['submitter']->getData();
        $progressStates = $filterform['progressState']->getData();
        $billingStates = $filterform['billingState']->getData();
        $category = $filterform['category']->getData();
        $projectSpecialty = $filterform['projectSpecialty']->getData();
        $projectFilter = $filterform['project']->getData();

        $searchStr = $filterform['comment']->getData();
        $startDate = $filterform['startDate']->getData();
        $endDate = $filterform['endDate']->getData();
        $principalInvestigators = $filterform['principalInvestigators']->getData();
        $accountNumber = $filterform['accountNumber']->getData();
        $billingContact = $filterform['billingContact']->getData();

        if( isset($filterform['submitter']) ) {
            $submitter = $filterform['submitter']->getData();
        }
        if( isset($filterform['project']) ) {
            $project = $filterform['project']->getData();
        }
        //////// EOF create filter //////////

        //////////////// get Requests IDs with the form node filter ////////////////
        $ids = array();
        if( !$formnode ) {
            if ($category) {
                $categoryIds = $transresRequestUtil->getRequestIdsFormNodeByCategory($category);
                $ids = array_merge($ids, $categoryIds);
            }
            if ($searchStr) {
                $commentIds = $transresRequestUtil->getRequestIdsFormNodeByComment($searchStr);
                $ids = array_merge($ids, $commentIds);
            }
        }
        if( count($ids) > 0 ) {
            $ids = array_unique($ids);
            //print_r($ids);
        }
        //////////////// EOF get Requests IDs with the form node filter ////////////////

        $repository = $em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest');

        $dql->leftJoin('transresRequest.submitter','submitter');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('submitter.infos','submitterInfos');

        $dqlParameters = array();

        if( $routeName == "translationalresearch_my_requests" ) {
            $title = "My Requests";
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $user->getId();
        }

        if( $routeName == "translationalresearch_all_requests" ) {
            $title = "All Requests";
        }

        ///////// filters //////////
        if( $projectSpecialty ) {
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $dql->andWhere("projectSpecialty.id = :projectSpecialtyId");
            $dqlParameters["projectSpecialtyId"] = $projectSpecialty->getId();
        }

        if( $projectFilter ) {
            $dql->andWhere("project.id = :projectId");
            $dqlParameters["projectId"] = $projectFilter->getId();
        }

        if( $submitter ) {
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
        }

        if( $project ) {
            $dql->andWhere("project.id = :projectId");
            $dqlParameters["projectId"] = $project->getId();
        }

        if( $progressStates && count($progressStates)>0 ) {
            $dql->andWhere("transresRequest.progressState IN (:progressStates)");
            $dqlParameters["progressStates"] = implode(",",$progressStates);
        }

        if( $billingStates && count($billingStates)>0 ) {
            $dql->andWhere("transresRequest.billingState IN (:billingStates)");
            $dqlParameters["billingStates"] = implode(",",$billingStates);
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

        if( $billingContact ) {
            $dql->andWhere("contact.id = :billingContactId");
            $dqlParameters["billingContactId"] = $billingContact->getId();
            $advancedFilter++;
        }

        if( $accountNumber ) {
            $dql->andWhere("transresRequest.fundedAccountNumber = :fundedAccountNumber");
            $dqlParameters["fundedAccountNumber"] = $accountNumber;
            $advancedFilter++;
        }

        if( $principalInvestigators && count($principalInvestigators)>0 ) {
            $dql->andWhere("principalInvestigators.id IN (:principalInvestigators)");
            $principalInvestigatorsIdsArr = array();
            foreach($principalInvestigators as $principalInvestigator) {
                $principalInvestigatorsIdsArr[] = $principalInvestigator->getId();
            }
            $dqlParameters["principalInvestigators"] = implode(",",$principalInvestigatorsIdsArr);
            $advancedFilter++;
        }

        if( !$formnode ) {
            $dql->leftJoin('transresRequest.products','products');
            if ($category) {
                $dql->leftJoin('products.category','category');
                $dql->andWhere("category.id = :categoryId");
                $dqlParameters["categoryId"] = $category;
            }
            if ($searchStr) {
                //$dql->andWhere("(category.name LIKE :categoryStr OR category.productId LIKE :categoryStr OR category.feeUnit LIKE :categoryStr OR category.fee LIKE :categoryStr)");
                $dql->andWhere("products.comment LIKE :searchStr");
                $dqlParameters["searchStr"] = "%".$searchStr."%";
                $advancedFilter++;
            }
        }

        if( count($ids) > 0 ) {
            //$dql->andWhere("transresRequest.id IN (:ids)");
            //$dqlParameters["ids"] = implode(",",$ids);
            $dql->andWhere("transresRequest.id IN (".implode(",",$ids).")");
        }
        ///////// EOF filters //////////

        $limit = 30;
        $query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        //echo "query=".$query->getSql()."<br>";

        $paginationParams = array(
            'defaultSortFieldName' => 'transresRequest.id',
            'defaultSortDirection' => 'DESC'
        );

        $paginator  = $this->get('knp_paginator');
        $transresRequests = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );

        return array(
            'transresRequests' => $transresRequests,
            'filterform' => $filterform->createView(),
            'title' => $title,
            'requestTotalFeeHtml' => null, //$requestTotalFeeHtml
            'advancedFilter' => $advancedFilter
        );
    }



    public function createRequestEntity($user,$transresRequest=null,$formnode=false) {

        $em = $this->getDoctrine()->getManager();

        if( !$transresRequest ) {
            $transresRequest = new TransResRequest($user);
            $transresRequest->setVersion(1);
        }

        if( !$transresRequest->getInstitution() ) {
            $institution = $em->getRepository('OlegUserdirectoryBundle:Institution')->findOneByName('Pathology and Laboratory Medicine');
            $transresRequest->setInstitution($institution);
        }

        //set order category
        if( $formnode && !$transresRequest->getMessageCategory() ) {
            $categoryStr = "HemePath Translational Research Request";  //"Pathology Call Log Entry";
            //$categoryStr = "Nesting Test"; //testing
            $messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
            if (!$messageCategory) {
                throw new \Exception("Message category is not found by name '" . $categoryStr . "'");
            }
            $transresRequest->setMessageCategory($messageCategory);
        }

        return $transresRequest;
    }
    public function createRequestForm( TransResRequest $transresRequest, $cycle, $request )
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresUtil = $this->container->get('transres_util');
        $routeName = $request->get('_route');

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'transresUtil' => $transresUtil,
            'SecurityAuthChecker' => $this->get('security.authorization_checker'),
            'transresRequest' => $transresRequest,
            'routeName' => $routeName,
            'saveAsDraft' => false,
            'saveAsComplete' => false,
            'updateRequest' => false,
            //'projects' => null,
            'availableProjects' => null
        );

        $params['admin'] = false;

        if(
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
        ) {
            $params['admin'] = true;
        } else {
            //TODO: do not add reviewers
        }

        $disabled = false;

        if( $cycle == "new" ) {
            $disabled = false;
            $params['saveAsDraft'] = true;
            $params['saveAsComplete'] = true;

            if( $routeName == "translationalresearch_new_standalone_request" ) {
                $availableProjects = $transresUtil->getAvailableProjects();
                $params['availableProjects'] = $availableProjects;
            }
        }

        if( $cycle == "show" ) {
            $disabled = true;
            //$params['updateRequest'] = true;
        }

        if( $cycle == "edit" ) {
            $disabled = false;
            $params['saveAsDraft'] = true;
            $params['saveAsComplete'] = true;
        }

        if( $cycle == "set-state" ) {
            $disabled = false;
        }

        $form = $this->createForm(TransResRequestType::class, $transresRequest, array(
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return $form;
    }



    /**
     * @Route("/request/generate-form-node-tree/", name="translationalresearch_generate_form_node_tree_request")
     * @Method("GET")
     */
    public function generateFormNodeAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transResFormNodeUtil = $this->get('transres_formnode_util');
        $count = $transResFormNodeUtil->generateTransResFormNodeRequest();

        exit("Form Node Tree generated: ".$count);
    }




    /**
     * Finds and displays a progress review form for this request entity.
     *
     * @Route("/request/progress/review/{id}", name="translationalresearch_request_review_progress_state")
     * @Template("OlegTranslationalResearchBundle:Request:review.html.twig")
     * @Method("GET")
     */
    public function reviewProgressAction(Request $request, TransResRequest $transresRequest)
    {
        $transresUtil = $this->container->get('transres_util');

        if(
            $transresUtil->isAdminOrPrimaryReviewer()
            //||
            //$transresUtil->isProjectReviewer($transresRequest)
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "show";

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //show

        $eventType = "Request Viewed";
        $msg = "Request ".$transresRequest->getOid() ." has been viewed on the progress review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'statMachineType' => 'progress',
            'title' => "Progress Review Request ".$transresRequest->getOid(),
        );
    }

    /**
     * Finds and displays a billing review form for this request entity.
     *
     * @Route("/request/billing/review/{id}", name="translationalresearch_request_review_billing_state")
     * @Template("OlegTranslationalResearchBundle:Request:review.html.twig")
     * @Method("GET")
     */
    public function reviewBillingAction(Request $request, TransResRequest $transresRequest)
    {
        $transresUtil = $this->container->get('transres_util');

        if(
        $transresUtil->isAdminOrPrimaryReviewer()
            //||
            //$transresUtil->isProjectReviewer($transresRequest)
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "show";

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //show

        $eventType = "Request Viewed";
        $msg = "Request ".$transresRequest->getOid() ." has been viewed on the billing review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'project' => $transresRequest->getProject(),
            'form' => $form->createView(),
            'cycle' => $cycle,
            'statMachineType' => 'billing',
            'title' => "Billing Review Request ".$transresRequest->getOid(),
        );
    }


    /**
     * @Route("/request/update-irb-exp-date/", name="translationalresearch_update_irb_exp_date", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function updateIrbExpDateAction( Request $request ) {
        //set permission: project irb reviewer or admin
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_OBSERVER') ) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $transresRequestUtil = $this->get('transres_request_util');
        $transresUtil = $this->container->get('transres_util');
        $res = "NotOK";

        $projectId = trim( $request->get('projectId') );
        $project = $em->getRepository('OlegTranslationalResearchBundle:Project')->find($projectId);

        if(
            $transresUtil->isAdminOrPrimaryReviewer() ||
            $this->isReviewsReviewer($user,$project->getIrbReviews())
        ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        if( $project ) {
            $originalIrbExpDateStr = "Unknown";
            if( $project->getIrbExpirationDate() ) {
                $originalIrbExpDateStr = $project->getIrbExpirationDate()->format('m/d/Y');
            }

            $value = trim($request->get('value'));

            $irbExpDate = \DateTime::createFromFormat('m/d/Y', $value);

            $project->setIrbExpirationDate($irbExpDate);

            $receivingObject = $transresRequestUtil->setValueToFormNodeProject($project, "IRB Expiration Date", $value);

            //$em->flush($receivingObject);
            //$em->flush($project);
            $em->flush();

            //add eventlog changed IRB
            $eventType = "Project Updated";
            $res = "Project ID ".$project->getOid() ." has been updated: ".
                "IRB Expiration Date changed form ".$originalIrbExpDateStr." to ".$value;
            $transresUtil->setEventLog($project,$eventType,$res);
        }

        $response = new Response($res);
        return $response;
    }

}