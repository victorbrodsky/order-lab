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


use Oleg\TranslationalResearchBundle\Entity\Project;
use Oleg\TranslationalResearchBundle\Entity\TransResRequest;
use Oleg\TranslationalResearchBundle\Form\FilterRequestType;
use Oleg\TranslationalResearchBundle\Form\TransResRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


/**
 * Request FormNode controller.
 */
class RequestController extends Controller
{


    /**
     * Creates a new request entity with formnode.
     *
     * @Route("/project/{id}/request/new/", name="translationalresearch_request_new")
     * @Template("OlegTranslationalResearchBundle:Request:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newFormNodeAction(Request $request, Project $project)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $cycle = "new";

        $testing = false;
        //$testing = true;

        $transresRequest = $this->createRequestEntity($user,null);
        $transresRequest->setProject($project);

        $form = $this->createRequestForm($transresRequest,$cycle,$request); //new

        $messageTypeId = true;//testing
        $formnodetrigger = 1;
        if( $messageTypeId ) {
            $formnodetrigger = 0; //build formnodes from top to bottom
        }

        //top message category id
        $formnodeTopHolderId = null;
        //$categoryStr = "Pathology Call Log Entry";
        //$messageCategory = $em->getRepository('OlegOrderformBundle:MessageCategory')->findOneByName($categoryStr);
        $messageCategory = $transresRequest->getMessageCategory();
        if( $messageCategory ) {
            $formnodeTopHolderId = $messageCategory->getId();
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //exit("Project submitted");

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

                //$project->generateOid();
                $em->flush();
            }

            //process form nodes
            $formNodeUtil = $this->get('user_formnode_utility');
            $formNodeUtil->processFormNodes($request,$transresRequest->getMessageCategory(),$transresRequest,$testing);

            $msg = "New Request has been successfully submitted for the project ID ".$project->getOid();

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Request Created";
            $msg = "New Request with ID ".$transresRequest->getId()." has been successfully submitted for the project ID ".$project->getOid();
            $transresUtil->setEventLog($transresRequest,$eventType,$msg);

            return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
        }


        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Create Request for project ID ".$project->getOid(),
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId
        );
    }



    /**
     * Get TransResRequest Edit page
     *
     * @Route("/request/edit/{id}", name="translationalresearch_request_edit")
     * @Template("OlegTranslationalResearchBundle:Request:edit.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TransResRequest $transresRequest)
    {

        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_REQUESTER')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        //$userSecUtil = $this->get('user_security_utility');
        $transresUtil = $this->container->get('transres_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $cycle = "edit";
        $formtype = "translationalresearch-request";

        $class = new \ReflectionClass($transresRequest);
        $className = $class->getShortName();          //ObjectTypeText
        $classNamespace = $class->getNamespaceName(); //Oleg\UserdirectoryBundle\Entity

        $testing = false;
        //$testing = true;

        $project = $transresRequest->getProject();

        $transresRequest = $this->createRequestEntity($user,$transresRequest);

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
            $formNodeUtil = $this->get('user_formnode_utility');
            $formNodeUtil->processFormNodes($request,$transresRequest->getMessageCategory(),$transresRequest,$testing); //testing

            $msg = "Request ID ".$transresRequest->getId()." has been successfully updated for the project ID ".$project->getOid();

            if( $testing ) {
                exit('form is submitted and finished, msg='.$msg);
            }

            $this->get('session')->getFlashBag()->add(
                'notice',
                $msg
            );

            $eventType = "Request Updated";
            $msg = "Request ID ".$transresRequest->getId() ." has been updated.";
            $transresUtil->setEventLog($transresRequest,$eventType,$msg);

            return $this->redirectToRoute('translationalresearch_request_show', array('id' => $transresRequest->getId()));
        }

        $eventType = "Request Viewed";
        $msg = "Request ID ".$transresRequest->getId() ." has been viewed on the edit page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'project' => $project,
            'edit_form' => $form->createView(),
            'cycle' => $cycle,
            'formtype' => $formtype,
            'title' => "Edit Request ID ".$transresRequest->getId(),
            'triggerSearch' => 0,
            'formnodetrigger' => $formnodetrigger,
            'formnodeTopHolderId' => $formnodeTopHolderId,
            'entityNamespace' => $classNamespace,
            'entityName' => $className,
            'entityId' => $transresRequest->getId(),
            'sitename' => $this->container->getParameter('translationalresearch.sitename'),
        );
    }

    /**
     * Finds and displays a request entity.
     *
     * @Route("/request/show/{id}", name="translationalresearch_request_show")
     * @Template("OlegTranslationalResearchBundle:Request:show.html.twig")
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
        $msg = "Request ID ".$transresRequest->getId() ." has been viewed on the show review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'project' => $transresRequest->getProject(),
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => "Request ID ".$transresRequest->getOid() . $feeHtml,
            //'delete_form' => $deleteForm->createView(),
            //'review_forms' => $reviewFormViews
        );

//        return array(
//            'project' => $project,
//            'cycle' => 'show',
//            'delete_form' => $deleteForm->createView(),
//            'title' => "Project ID ".$project->getId()
//        );
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
        $searchStr = $filterform['comment']->getData();
        //////// EOF create filter //////////


        //////////////// get Requests IDs with the form node filter ////////////////
        $ids = array();
        if( $category ) {
            $categoryIds = $transresRequestUtil->getRequestIdsFormNodeByCategory($category);
            $ids = array_merge($ids, $categoryIds);
        }
        if( $searchStr ) {
            $commentIds = $transresRequestUtil->getRequestIdsFormNodeByComment($searchStr);
            $ids = array_merge($ids, $commentIds);
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

        $dql->andWhere("project.id = :projectId");

        $dqlParameters["projectId"] = $project->getId();

        ///////// filters //////////
        if( $submitter ) {
            $dql->andWhere("submitter.id = :submitterId");
            $dqlParameters["submitterId"] = $submitter->getId();
        }

        if( $progressStates && count($progressStates)>0 ) {
            $dql->andWhere("transresRequest.progressState IN (:progressStates)");
            $dqlParameters["progressStates"] = implode(",",$progressStates);
        }

        if( $billingStates && count($billingStates)>0 ) {
            $dql->andWhere("transresRequest.billingState IN (:billingStates)");
            $dqlParameters["billingStates"] = implode(",",$billingStates);
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
        //echo "transresRequests count=".count($transresRequests)."<br>";

        $requestTotalFeeHtml = $transresRequestUtil->getTransResRequestTotalFeeHtml($project);

        return array(
            'transresRequests' => $transresRequests,
            'project' => $project,
            'filterform' => $filterform->createView(),
            'title' => $title . " (". $requestTotalFeeHtml . ")",
            'requestTotalFeeHtml' => null //$requestTotalFeeHtml
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
        $progressStates = $filterform['progressState']->getData();
        $billingStates = $filterform['billingState']->getData();
        $category = $filterform['category']->getData();
        $searchStr = $filterform['comment']->getData();

        if( isset($filterform['submitter']) ) {
            $submitter = $filterform['submitter']->getData();
        }
        if( isset($filterform['project']) ) {
            $project = $filterform['project']->getData();
        }
        //////// EOF create filter //////////

        //////////////// get Requests IDs with the form node filter ////////////////
        $ids = array();
        if( $category ) {
            $categoryIds = $transresRequestUtil->getRequestIdsFormNodeByCategory($category);
            $ids = array_merge($ids, $categoryIds);
        }
        if( $searchStr ) {
            $commentIds = $transresRequestUtil->getRequestIdsFormNodeByComment($searchStr);
            $ids = array_merge($ids, $commentIds);
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
            'requestTotalFeeHtml' => null //$requestTotalFeeHtml
        );
    }



    public function createRequestEntity($user,$transresRequest=null) {

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
        if( !$transresRequest->getMessageCategory() ) {
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
        $msg = "Request ID ".$transresRequest->getId() ." has been viewed on the progress review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'statMachineType' => 'progress',
            'title' => "Progress Review Request ID ".$transresRequest->getId(),
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
        $msg = "Request ID ".$transresRequest->getId() ." has been viewed on the billing review page.";
        $transresUtil->setEventLog($transresRequest,$eventType,$msg);

        return array(
            'transresRequest' => $transresRequest,
            'project' => $transresRequest->getProject(),
            'form' => $form->createView(),
            'cycle' => $cycle,
            'statMachineType' => 'billing',
            'title' => "Billing Review Request ID ".$transresRequest->getId(),
        );
    }




}