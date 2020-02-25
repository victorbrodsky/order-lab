<?php

namespace App\TranslationalResearchBundle\Controller;

use App\TranslationalResearchBundle\Entity\DefaultReviewer;
use App\TranslationalResearchBundle\Entity\SpecialtyList;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defaultreviewer controller.
 */
class DefaultReviewerController extends OrderAbstractController
{

    /**
     * Lists defaultReviewer states: irb_review, committee_review, final_review
     *
     * @Route("/default-reviewers/{specialtyStr}", name="translationalresearch_default-reviewer_index")
     * @Template("AppTranslationalResearchBundle/DefaultReviewer/index.html.twig")
     * @Method("GET")
     */
    public function indexAction(Request $request, $specialtyStr)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();

        //$specialty is a url prefix (i.e. "new-ap-cp-project")
//        $specialtyAbbreviation = SpecialtyList::getProjectAbbreviationFromUrlPrefix($specialty);
//        if( !$specialtyAbbreviation ) {
//            throw new \Exception( "Project specialty abbreviation is not found by name '".$specialty."'" );
//        }
//        $specialty = $em->getRepository('AppTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation($specialtyAbbreviation);
//        if( !$specialty ) {
//            throw new \Exception( "Project specialty is not found by name '".$specialtyAbbreviation."'" );
//        }
        //$specialty is a url prefix (i.e. "new-ap-cp-project")
        $specialty = $transresUtil->getSpecialtyObject($specialtyStr);

        $states = array(
            'irb_review',
            'admin_review',
            'committee_review',
            'final_review'
        );

        return array(
            'states' => $states,
            'specialty' => $specialty,
            'title' => "Default Reviewers for ".$specialty
        );
    }

    /**
     * Lists all defaultReviewer entities for a particular state.
     *
     * @Route("/default-reviewers/stage/{stateStr}/{specialtyStr}", name="translationalresearch_state-default-reviewer_index")
     * @Template("AppTranslationalResearchBundle/DefaultReviewer/state-default-reviewer-index.html.twig")
     * @Method("GET")
     */
    public function stateDefaultReviewerIndexAction(Request $request, $stateStr, $specialtyStr)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $em = $this->getDoctrine()->getManager();

        //$specialty is a url prefix (i.e. "new-ap-cp-project")
        $specialty = $transresUtil->getSpecialtyObject($specialtyStr);

        //$defaultReviewers = $em->getRepository('AppTranslationalResearchBundle:DefaultReviewer')->findAll();

        $repository = $em->getRepository('AppTranslationalResearchBundle:DefaultReviewer');
        $dql =  $repository->createQueryBuilder("defaultReviewer");
        $dql->select('defaultReviewer');

        $dql->leftJoin('defaultReviewer.reviewer','reviewer');
        $dql->leftJoin('reviewer.infos','reviewerInfos');
        $dql->leftJoin('defaultReviewer.reviewerDelegate','reviewerDelegate');
        $dql->leftJoin('reviewerDelegate.infos','reviewerDelegateInfos');
        $dql->leftJoin('defaultReviewer.projectSpecialty','projectSpecialty');

        $dql->where('defaultReviewer.state=:state AND projectSpecialty.id=:specialty');

        $limit = 30;
        $query = $em->createQuery($dql);

        $query->setParameters(array(
            "state" => $stateStr,
            "specialty" => $specialty->getId()
        ));

        $paginationParams = array(
            'defaultSortFieldName' => 'defaultReviewer.id',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

        $paginator  = $this->get('knp_paginator');
        $defaultReviewers = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );

        //get state string: irb_review=>IRB Review
        $stateLabel = $transresUtil->getStateSimpleLabelByName($stateStr);


        return array(
            'defaultReviewers' => $defaultReviewers,
            'stateStr' => $stateStr,
            'specialty' => $specialty,
            'title' => "Default Reviewers for ".$specialty." ".$stateLabel
        );
    }

    /**
     * Creates a new defaultReviewer entity.
     *
     * @Route("/default-reviewers/new/{stateStr}/{specialtyStr}", name="translationalresearch_default-reviewer_new")
     * @Template("AppTranslationalResearchBundle/DefaultReviewer/new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, $stateStr, $specialtyStr)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $cycle = "new";

        //$specialty is a url prefix (i.e. "new-ap-cp-project")
        $specialty = $transresUtil->getSpecialtyObject($specialtyStr);

        $defaultReviewer = new Defaultreviewer();
        $defaultReviewer->setState($stateStr);
        $defaultReviewer->setProjectSpecialty($specialty);

        //$form = $this->createForm('App\TranslationalResearchBundle\Form\DefaultReviewerType', $defaultReviewer);
        $form = $this->createDefaultReviewForm($cycle,$defaultReviewer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $transresUtil->processDefaultReviewersRole($defaultReviewer);

            $em = $this->getDoctrine()->getManager();
            $em->persist($defaultReviewer);
            $em->flush();

            //Event Log
            $eventType = "Default Reviewer Created";
            $reviewersArr = $transresUtil->getCurrentReviewersEmails($defaultReviewer,false);
            $reviewer = $reviewersArr['reviewer'];
            $reviewerDelegate = $reviewersArr['reviewerDelegate'];
            $msg = "Default Reviewer Object ($stateStr, $specialtyStr) has been created with reviewer=".$reviewer . " ; reviewerDelegate=".$reviewerDelegate;
            $transresUtil->setEventLog($defaultReviewer,$eventType,$msg);

            return $this->redirectToRoute('translationalresearch_default-reviewer_show', array('id' => $defaultReviewer->getId()));
        }

        //get state string: irb_review=>IRB Review
        $transresUtil = $this->container->get('transres_util');
        $stateLabel = $transresUtil->getStateSimpleLabelByName($stateStr);

        return array(
            'cycle' => $cycle,
            'defaultReviewer' => $defaultReviewer,
            'specialty' => $specialty,
            'form' => $form->createView(),
            'title' => "Create a new Default Reviewer for ".$stateLabel
        );
    }

    /**
     * Finds and displays a defaultReviewer entity.
     *
     * @Route("/default-reviewers/show/{id}", name="translationalresearch_default-reviewer_show")
     * @Template("AppTranslationalResearchBundle/DefaultReviewer/new.html.twig")
     * @Method("GET")
     */
    public function showAction(DefaultReviewer $defaultReviewer)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $cycle = "show";

        $deleteForm = $this->createDeleteForm($defaultReviewer);

        $form = $this->createDefaultReviewForm($cycle,$defaultReviewer);

        return array(
            'cycle' => $cycle,
            'form' => $form->createView(),
            'defaultReviewer' => $defaultReviewer,
            'specialty' => $defaultReviewer->getProjectSpecialty(),
            'title' => "Default Reviewer ".$defaultReviewer->getReviewer(),
            'delete_form' => $deleteForm->createView(),
        );
//        return $this->render('defaultreviewer/show.html.twig', array(
//            'defaultReviewer' => $defaultReviewer,
//            'delete_form' => $deleteForm->createView(),
//        ));
    }

    /**
     * Displays a form to edit an existing defaultReviewer entity.
     *
     * @Route("/default-reviewers/edit/{id}", name="translationalresearch_default-reviewer_edit")
     * @Template("AppTranslationalResearchBundle/DefaultReviewer/new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, DefaultReviewer $defaultReviewer)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $cycle = "edit";

        $specialtyStr = $defaultReviewer->getProjectSpecialty();
        $stateStr = $defaultReviewer->getState();
        //get state string: irb_review=>IRB Review
        $stateLabel = $transresUtil->getStateSimpleLabelByName($stateStr);

        $originalReviewer = $defaultReviewer->getReviewer();
        $originalReviewerDelegate = $defaultReviewer->getReviewerDelegate();

        $deleteForm = $this->createDeleteForm($defaultReviewer);
        //$editForm = $this->createForm('App\TranslationalResearchBundle\Form\DefaultReviewerType', $defaultReviewer);
        $form = $this->createDefaultReviewForm($cycle,$defaultReviewer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $transresUtil->processDefaultReviewersRole($defaultReviewer,$originalReviewer,$originalReviewerDelegate);

            $this->getDoctrine()->getManager()->flush();

            //Event Log
            $eventType = "Default Reviewer Updated";
            $reviewersArr = $transresUtil->getCurrentReviewersEmails($defaultReviewer,false);
            $reviewer = $reviewersArr['reviewer'];
            $reviewerDelegate = $reviewersArr['reviewerDelegate'];
            $stateStr = $defaultReviewer->getState();
            //get state string: irb_review=>IRB Review
            $stateLabel = $transresUtil->getStateSimpleLabelByName($stateStr);
            $specialtyStr = $defaultReviewer->getProjectSpecialty();
            $msg = "Default Reviewer Object ($stateLabel, $specialtyStr) has been updated:"; //with reviewer=".$reviewer . " ; reviewerDelegate=".$reviewerDelegate;
            $msg = $msg . "<br>Original reviewer=".$originalReviewer.";<br> New reviewer=".$reviewer;
            $msg = $msg . "<br>Original reviewerDelegate=".$originalReviewerDelegate.";<br> New reviewerDelegate=".$reviewerDelegate;
            $transresUtil->setEventLog($defaultReviewer,$eventType,$msg);

            return $this->redirectToRoute('translationalresearch_default-reviewer_show', array('id' => $defaultReviewer->getId()));
        }

        return array(
            'cycle' => $cycle,
            'defaultReviewer' => $defaultReviewer,
            'specialty' => $defaultReviewer->getProjectSpecialty(),
            'form' => $form->createView(),
            'title' => "Default Reviewer for ".$specialtyStr." ".$stateLabel,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a defaultReviewer entity.
     *
     * @Route("/default-reviewers/delete/{id}", name="translationalresearch_default-reviewer_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, DefaultReviewer $defaultReviewer)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');
        $specialtyStr = $defaultReviewer->getProjectSpecialty();

        $form = $this->createDeleteForm($defaultReviewer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //remove roles
            $roles = $defaultReviewer->getRoleByState();
            $reviewerRole = $roles['reviewer'];
            //$reviewerDelegateRole = $roles['reviewerDelegate'];
            $reviewer = $defaultReviewer->getReviewer();
            //remove role: make sure if the user is not a default reviewer in all other objects. Or don't remove role at all.
            //if( $reviewer ) {
            //    $reviewer->removeRole($reviewerRole);
            //}
            $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
            //remove role: make sure if the user is not a default reviewer in all other objects. Or don't remove role at all.
            //if( $reviewerDelegate && $reviewerDelegateRole ) {
            //    $reviewerDelegate->removeRole($reviewerDelegateRole);
            //}

            $em = $this->getDoctrine()->getManager();
            $em->remove($defaultReviewer);
            $em->flush();

            //Event Log
            $eventType = "Default Reviewer Deleted";
            $stateStr = $defaultReviewer->getState();
            $msg = "Default Reviewer Object ($stateStr, $specialtyStr) has been deleted with reviewer=".$reviewer . " ; reviewerDelegate=".$reviewerDelegate;
            $transresUtil->setEventLog($defaultReviewer,$eventType,$msg);
        }

        return $this->redirectToRoute('translationalresearch_default-reviewer_index',array("specialtyStr"=>$specialtyStr->getAbbreviation() ));
    }

    /**
     * Creates a form to delete a defaultReviewer entity.
     *
     * @param DefaultReviewer $defaultReviewer The defaultReviewer entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(DefaultReviewer $defaultReviewer)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('translationalresearch_default-reviewer_delete', array('id' => $defaultReviewer->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function createDefaultReviewForm($cycle,$defaultReviewer) {

        $transresUtil = $this->container->get('transres_util');
        
        if( $cycle == "new" ) {
            $disabled = false;
        }
        if( $cycle == "show" ) {
            $disabled = true;
        }
        if( $cycle == "edit" ) {
            $disabled = false;
        }

        $params = array(
            'showPrimaryReview'=>false,
            'transresUtil' => $transresUtil
        );

        //echo "state=[".$defaultReviewer->getState()."]<br>";
        if( $defaultReviewer->getState() == "committee_review" ) {
            $params['showPrimaryReview'] = true;
        }

        $form = $this->createForm('App\TranslationalResearchBundle\Form\DefaultReviewerType', $defaultReviewer, array(
            'disabled' => $disabled,
            'form_custom_value' => $params
        ));

        return $form;
    }



    /**
     * Substitute user
     *
     * @Route("/substitute-user/", name="translationalresearch_substitute_user")
     * @Template("AppTranslationalResearchBundle/DefaultReviewer/substitute-user.html.twig")
     * @Method({"GET", "POST"})
     */
    public function substituteUserAction(Request $request)
    {
        if (false == $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN')) {
            return $this->redirect($this->generateUrl('translationalresearch-nopermission'));
        }

        $transresUtil = $this->container->get('transres_util');

        //$specialtyStr = $defaultReviewer->getProjectSpecialty();
        //$stateStr = $defaultReviewer->getState();
        //get state string: irb_review=>IRB Review
        //$stateLabel = $transresUtil->getStateSimpleLabelByName($stateStr);

        //$form = $this->createDefaultReviewForm($cycle,$defaultReviewer);
        $params = array(
            'showPrimaryReview'=>false,
            'transresUtil' => $transresUtil
        );
        $form = $this->createForm('App\TranslationalResearchBundle\Form\SubstituteUserType', null, array(
            'form_custom_value' => $params
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $projectsMsg = $this->getFilteredProjects($form);
            $this->container->get('session')->getFlashBag()->add(
                'notice',
                $projectsMsg
            );

            $requestsMsg = $this->getFilteredRequests($form);
            //exit("Update Request Exit: $requestsMsg");
            $this->container->get('session')->getFlashBag()->add(
                'notice',
                $requestsMsg
            );

            $invoicesMsg = $this->getFilteredInvoices($form);
            //exit("Update Invoice Exit: $invoicesMsg");
            $this->container->get('session')->getFlashBag()->add(
                'notice',
                $invoicesMsg
            );

            //TODO: test substituted user's roles?

            //exit('substituted: projects count='.count($projects));
            return $this->redirectToRoute('translationalresearch_substitute_user');
        }

        return array(
            'form' => $form->createView(),
            'title' => "Batch User Substitution",
            'cycle' => 'new'
        );
    }
    public function getFilteredProjects($form) {

        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');

        $testing = false;
        //$testing = true;

        $projectSpecialties = $form->get('projectSpecialty')->getData();
        $substituteUser = $form->get('substituteUser')->getData();
        $replaceUser = $form->get('replaceUser')->getData();
        echo "projectSpecialties=".count($projectSpecialties)."<br>";
        echo "substituteUser=".$substituteUser."<br>";
        echo "replaceUser=".$replaceUser."<br>";

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            //ok
        } else {
            return "No projects to update: Project specialty is not specified";
        }
        if( !$substituteUser ) {
            return "No project requests to update: User to replace with is not specified";
        }
        if( !$replaceUser ) {
            return "No project requests to update: User to be replaced is not specified";
        }
        if($replaceUser->getId() == $substituteUser->getId()) {
            return "No requests to update: substitute and replace users are the same";
        }

        $substituteUserId = $substituteUser->getId();
        //$replaceUserId = $replaceUser->getId();

        $excludedProjectCompleted = $form->get('excludedProjectCompleted')->getData();
        $excludedProjectCanceled = $form->get('excludedProjectCanceled')->getData();
        $excludedProjectDraft = $form->get('excludedProjectDraft')->getData();
        //echo "excludedProjectCompleted=".$excludedProjectCompleted."<br>";
        //echo "excludedProjectCanceled=".$excludedProjectCanceled."<br>";

        //project's requester
        $projectPis = $form->get('projectPis')->getData();
        $projectPisIrb = $form->get('projectPisIrb')->getData();
        $projectPathologists = $form->get('projectPathologists')->getData();
        $projectCoInvestigators = $form->get('projectCoInvestigators')->getData();
        $projectContacts = $form->get('projectContacts')->getData();
        $projectBillingContact = $form->get('projectBillingContact')->getData();

        //IRB Reviewers
        $projectReviewerIrb = $form->get('projectReviewerIrb')->getData();
        $projectReviewerIrbDelegate = $form->get('projectReviewerIrbDelegate')->getData();
        //Admin Reviewer
        $projectReviewerAdmin = $form->get('projectReviewerAdmin')->getData();
        $projectReviewerAdminDelegate = $form->get('projectReviewerAdminDelegate')->getData();
        //Committee Reviewer
        $projectReviewerCommittee = $form->get('projectReviewerCommittee')->getData();
        $projectReviewerCommitteeDelegate = $form->get('projectReviewerCommitteeDelegate')->getData();
        //Primary Committee Reviewer
        $projectReviewerPrimaryCommittee = $form->get('projectReviewerPrimaryCommittee')->getData();
        $projectReviewerPrimaryCommitteeDelegate = $form->get('projectReviewerPrimaryCommitteeDelegate')->getData();
        //Final Reviewer
        $projectReviewerFinal = $form->get('projectReviewerFinal')->getData();
        $projectReviewerFinalDelegate = $form->get('projectReviewerFinalDelegate')->getData();


        ///////////// Filter Projects //////////////////
        $repository = $em->getRepository('AppTranslationalResearchBundle:Project');
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

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            $dql->leftJoin('project.projectSpecialty','projectSpecialty');
            $projectSpecialtyIdsArr = array();
            foreach($projectSpecialties as $projectSpecialty) {
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        } else {
            return "No projects to update: project specialty is not specified";
        }

        if( $excludedProjectCompleted ) {
            $dql->andWhere("project.state != 'closed'");
        }
        if( $excludedProjectCanceled ) {
            $dql->andWhere("project.state != 'canceled'");
        }
        if( $excludedProjectDraft ) {
            $dql->andWhere("project.state != 'draft'");
        }

        $projectProcessed = false;

        if( $substituteUser && $substituteUserId ) {

            $projectUsers = array();

            if( $projectPis ) {
                $projectUsers[] = "principalInvestigators.id = :userId";
            }
            if( $projectPisIrb ) {
                $projectUsers[] = "principalIrbInvestigator.id = :userId";
            }
            if( $projectPathologists ) {
                $projectUsers[] = "pathologists.id = :userId";
            }
            if( $projectCoInvestigators ) {
                $projectUsers[] = "coInvestigators.id = :userId";
            }
            if( $projectContacts ) {
                $projectUsers[] = "contacts.id = :userId";
            }
            if( $projectBillingContact ) {
                $projectUsers[] = "billingContact.id = :userId";
            }

            //IRB Reviewers
            if( $projectReviewerIrb ) {
                $projectUsers[] = "irbReviewer.id = :userId";
            }
            if( $projectReviewerIrbDelegate ) {
                $projectUsers[] = "irbReviewerDelegate.id = :userId";
            }

            //Admin Reviewer
            if( $projectReviewerAdmin ) {
                $projectUsers[] = "adminReviewer.id = :userId";
            }
            if( $projectReviewerAdminDelegate ) {
                $projectUsers[] = "adminReviewerDelegate.id = :userId";
            }

            //Committee Reviewer
            if( $projectReviewerCommittee ) {
                $projectUsers[] = "committeeReviewer.id = :userId";
            }
            if( $projectReviewerCommitteeDelegate ) {
                $projectUsers[] = "committeeReviewerDelegate.id = :userId";
            }

            //Primary Committee Reviewer
            if( $projectReviewerPrimaryCommittee ) {
                $projectUsers[] = "(committeeReviewer.id = :userId AND committeeReviews.primaryReview = TRUE)";
            }
            if( $projectReviewerPrimaryCommitteeDelegate ) {
                $projectUsers[] = "(committeeReviewerDelegate.id = :userId AND committeeReviews.primaryReview = TRUE)";
            }

            //Final Reviewer
            if( $projectReviewerFinal ) {
                $projectUsers[] = "finalReviewer.id = :userId";
            }
            if( $projectReviewerFinalDelegate ) {
                $projectUsers[] = "finalReviewerDelegate.id = :userId";
            }

            if( count($projectUsers) > 0 ) {
                $projectUsersStr = implode(" OR ",$projectUsers);
                $dql->andWhere($projectUsersStr);
                $dqlParameters["userId"] = $substituteUserId;
                $projectProcessed = true;
            }

        } else {
            return "No project requests to update: User to replace with is not specified";
        }

        if( !$projectProcessed ) {
            return "No projects to update";
        }



        $query = $dql->getQuery();

        //echo "projectId=".$project->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";
        //echo "query=".$query->getSql()."<br>";

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $projects = $query->getResult();
        ///////////// EOF Filter Projects //////////////////

        $msgProjects = array();

        echo "<br>";
        foreach($projects as $project) {

            $toFlush = false;
            //$toFlushReviewer = false;
            $msgArr = array();

            echo "-----" . $project->getId() . "-----<br>";
            if( $project->getPrincipalInvestigators()->contains($substituteUser) ) {
                echo "### User is PI <br>";
                if( $projectPis ) {
                    $project->removePrincipalInvestigator($substituteUser);
                    $project->addPrincipalInvestigator($replaceUser);
                    $toFlush = true;
                    $msgArr[] = $this->getMsg("PI",$substituteUser,$replaceUser); //"PI substituted from " . $substituteUser . " to " . $replaceUser;
                }
            }
            if( $project->getPrincipalIrbInvestigators()->contains($substituteUser) ) {
                echo "### User is IRB PI <br>";
                if( $projectPisIrb ) {
                    $currentUser = $project->getPrincipalIrbInvestigator();
                    if( $currentUser && $currentUser->getId() == $substituteUserId ) {
                        $project->setPrincipalIrbInvestigator($replaceUser);
                        $toFlush = true;
                        $msgArr[] = $this->getMsg("PI listed on IRB",$substituteUser,$replaceUser); //"PI listed on IRB substituted from " . $substituteUser . " to " . $replaceUser;
                    }
                }
            }
            if( $project->getPathologists()->contains($substituteUser) ) {
                echo "### User is Pathologist <br>";
                if( $projectPathologists ) {
                    $project->removePathologist($substituteUser);
                    $project->addPathologist($replaceUser);
                    $toFlush = true;
                    $msgArr[] = $this->getMsg("Pathologist",$substituteUser,$replaceUser);//"Pathologist substituted from " . $substituteUser . " to " . $replaceUser;
                }
            }
            if( $project->getCoInvestigators()->contains($substituteUser) ) {
                echo "### User is CoInvestigator <br>";
                if( $projectCoInvestigators ) {
                    $project->removeCoInvestigator($substituteUser);
                    $project->addCoInvestigator($replaceUser);
                    $toFlush = true;
                    $msgArr[] = $this->getMsg("CoInvestigator",$substituteUser,$replaceUser); //"CoInvestigator substituted from " . $substituteUser . " to " . $replaceUser;
                }
            }
            if( $project->getContacts()->contains($substituteUser) ) {
                echo "### User is Contact <br>";
                if( $projectContacts ) {
                    $project->removeContact($substituteUser);
                    $project->addContact($replaceUser);
                    $toFlush = true;
                    $msgArr[] = $this->getMsg("Contact",$substituteUser,$replaceUser); //"Contact substituted from " . $substituteUser . " to " . $replaceUser;
                }
            }
            if( $project->getBillingContacts()->contains($substituteUser) ) {
                echo "### User is Billing Contact <br>";
                if( $projectBillingContact ) {
                    $currentUser = $project->getBillingContact();
                    if( $currentUser && $currentUser->getId() == $substituteUserId ) {
                        $project->setBillingContact($replaceUser);
                        $toFlush = true;
                        $msgArr[] = $this->getMsg("Billing Contact",$substituteUser,$replaceUser); //"Billing Contact substituted from " . $substituteUser . " to " . $replaceUser;
                    }
                }
            }


            //IRB Reviewer
            foreach( $project->getIrbReviews() as $review) {
                if( $review->getReviewer() && $review->getReviewer()->getId() == $substituteUserId ) {
                    echo "*** User is IRB Reviewer <br>";
                    if( $projectReviewerIrb ) {
                        $review->setReviewer($replaceUser);
                        $this->flushObject($review,$testing);
                        $msgArr[] = $this->getMsg("IRB Reviewer",$substituteUser,$replaceUser); //"IRB Reviewer substituted from " . $substituteUser . " to " . $replaceUser;
                    }
                }
                if( $review->getReviewerDelegate() && $review->getReviewerDelegate()->getId() == $substituteUserId ) {
                    echo "*** User is IRB Reviewer Delegate <br>";
                    if( $projectReviewerIrbDelegate ) {
                        $review->setReviewerDelegate($replaceUser);
                        $this->flushObject($review,$testing);
                        $msgArr[] = $this->getMsg("IRB Reviewer Delegate",$substituteUser,$replaceUser); //"IRB Reviewer Delegate substituted from " . $substituteUser . " to " . $replaceUser;
                    }
                }
            }
            //Admin Reviewer
            foreach( $project->getAdminReviews() as $review) {
                if( $review->getReviewer() && $review->getReviewer()->getId() == $substituteUserId ) {
                    if( $projectReviewerAdmin ) {
                        echo "*** User is Admin Reviewer <br>";
                        $review->setReviewer($replaceUser);
                        $this->flushObject($review,$testing);
                        $msgArr[] = $this->getMsg("Admin Reviewer",$substituteUser,$replaceUser); //"Admin Reviewer substituted from " . $substituteUser . " to " . $replaceUser;
                    }
                }
                if( $review->getReviewerDelegate() && $review->getReviewerDelegate()->getId() == $substituteUserId ) {
                    if( $projectReviewerAdminDelegate ) {
                        echo "*** User is Admin Reviewer Delegate <br>";
                        $review->setReviewerDelegate($replaceUser);
                        $this->flushObject($review,$testing);
                        $msgArr[] = $this->getMsg("Admin Reviewer Delegate",$substituteUser,$replaceUser); //"Admin Reviewer Delegate substituted from " . $substituteUser . " to " . $replaceUser;
                    }
                }
            }
            //Committee Reviewer
            foreach( $project->getCommitteeReviews() as $review) {
                //Reviewer
                if( $review->getReviewer() && $review->getReviewer()->getId() == $substituteUserId ) {
                    if( $review->getPrimaryReview() ) {
                        if( $projectReviewerPrimaryCommittee ) {
                            echo "*** User is Committee Reviewer (primary) <br>";
                            $review->setReviewer($replaceUser);
                            $this->flushObject($review,$testing);
                            $msgArr[] = $this->getMsg("Primary Committee Reviewer",$substituteUser,$replaceUser); //"Primary Committee Reviewer substituted from " . $substituteUser . " to " . $replaceUser;
                        }
                    } else {
                        if( $projectReviewerCommittee ) {
                            echo "*** User is Committee Reviewer <br>";
                            $review->setReviewer($replaceUser);
                            $this->flushObject($review,$testing);
                            $msgArr[] = $this->getMsg("Committee Reviewer",$substituteUser,$replaceUser); //"Committee Reviewer substituted from " . $substituteUser . " to " . $replaceUser;
                        }
                    }
                }
                //Delegate
                if( $review->getReviewerDelegate() && $review->getReviewerDelegate()->getId() == $substituteUserId ) {
                    if( $review->getPrimaryReview() ) {
                        if( $projectReviewerPrimaryCommitteeDelegate ) {
                            echo "*** User is Committee Reviewer Delegate (primary) <br>";
                            $review->setReviewerDelegate($replaceUser);
                            $this->flushObject($review,$testing);
                            $msgArr[] = $this->getMsg("Primary Committee Reviewer Delegate",$substituteUser,$replaceUser); //"Primary Committee Reviewer Delegate substituted from " . $substituteUser . " to " . $replaceUser;
                        }
                    } else {
                        if( $projectReviewerCommitteeDelegate ) {
                            echo "*** User is Committee Reviewer Delegate <br>";
                            $review->setReviewerDelegate($replaceUser);
                            $this->flushObject($review,$testing);
                            $msgArr[] = $this->getMsg("Committee Reviewer Delegate",$substituteUser,$replaceUser); //"Committee Reviewer Delegate substituted from " . $substituteUser . " to " . $replaceUser;
                        }
                    }
                }
            }
            //Final Reviewer
            foreach( $project->getFinalReviews() as $review) {
                if( $review->getReviewer() && $review->getReviewer()->getId() == $substituteUserId ) {
                    if( $projectReviewerFinal ) {
                        echo "*** User is Final Reviewer <br>";
                        $review->setReviewer($replaceUser);
                        $this->flushObject($review,$testing);
                        $msgArr[] = $this->getMsg("Final Reviewer",$substituteUser,$replaceUser); //"Final Reviewer substituted from " . $substituteUser . " to " . $replaceUser;
                    }
                }
                if( $review->getReviewerDelegate() && $review->getReviewerDelegate()->getId() == $substituteUserId ) {
                    if( $projectReviewerFinalDelegate ) {
                        echo "*** User is Final Reviewer Delegate <br>";
                        $review->setReviewerDelegate($replaceUser);
                        //$toFlushReviewer = true;
                        $this->flushObject($review,$testing);
                        $msgArr[] = $this->getMsg("Final Reviewer Delegate",$substituteUser,$replaceUser); //"Final Reviewer Delegate substituted from " . $substituteUser . " to " . $replaceUser;
                    }
                }
            }

            echo "<br>";

            if( $toFlush && !$testing ) {
                $em->flush($project);
                echo "updated project <br>";
            }

            //eventlog
            if( count($msgArr) > 0 ) {
                $eventType = "Project User Substituted";
                $msg = implode("<br>", $msgArr);
                //$msgProjects[] = "----- Project ".$project->getOid()." -----<br>".$msg;
                $msgProjects[] = "----- Project ".$transresUtil->getProjectShowUrl($project,$project->getOid(),true)." -----<br>".$msg;
                $transresUtil->setEventLog($project, $eventType, $msg, $testing);
            }

        }//foreach project

        if( count($msgProjects) > 0 ) {
            $msgProjectsStr = implode("<br>", $msgProjects);
        } else {
            $msgProjectsStr = "No project requests to update based on the specified criteria.";
        }

        return $msgProjectsStr;
    }

    public function getFilteredRequests($form) {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        $testing = false;
        //$testing = true;

        $projectSpecialties = $form->get('projectSpecialty')->getData();
        $substituteUser = $form->get('substituteUser')->getData();
        $replaceUser = $form->get('replaceUser')->getData();
        echo "projectSpecialties=" . count($projectSpecialties) . "<br>";
        echo "substituteUser=" . $substituteUser . "<br>";
        echo "replaceUser=" . $replaceUser . "<br>";

        if ($projectSpecialties && count($projectSpecialties) > 0) {
            //ok
        } else {
            return "No requests to update: Project specialty is not specified";
        }
        if (!$substituteUser) {
            return "No requests to update: Substitute user is not specified";
        }
        if (!$replaceUser) {
            return "No requests to update: Replace user is not specified";
        }
        if($replaceUser->getId() == $substituteUser->getId()) {
            return "No requests to update: substitute and replace users are the same";
        }

        $substituteUserId = $substituteUser->getId();
        //$replaceUserId = $replaceUser->getId();

        $excludedRequestCompleted = $form->get('excludedRequestCompleted')->getData();
        $excludedRequestCanceled = $form->get('excludedRequestCanceled')->getData();

        $requestPis = $form->get('requestPis')->getData();
        $requestBillingContact = $form->get('requestBillingContact')->getData();

        ///////////// Filter Requests //////////////////
        $repository = $em->getRepository('AppTranslationalResearchBundle:TransResRequest');
        $dql = $repository->createQueryBuilder("request");
        $dql->select('request');

        $dql->leftJoin('request.project', 'project');
        $dql->leftJoin('request.principalInvestigators', 'principalInvestigators');
        $dql->leftJoin('request.contact', 'contact');

        $dql->orderBy("request.id", "DESC");

        $dqlParameters = array();

        if ($projectSpecialties && count($projectSpecialties) > 0) {
            $dql->leftJoin('project.projectSpecialty', 'projectSpecialty');
            $projectSpecialtyIdsArr = array();
            foreach ($projectSpecialties as $projectSpecialty) {
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        } else {
            return "No requests to update: project specialty is not specified";
        }

        if ($excludedRequestCompleted) {
            $dql->andWhere("request.progressState != 'completed'");
        }
        if ($excludedRequestCanceled) {
            $dql->andWhere("request.progressState != 'canceled'");
        }

        $requestProcessed = false;

        if ($substituteUser && $substituteUserId) {

            $requestUsers = array();

            if ($requestPis) {
                $requestUsers[] = "principalInvestigators.id = :userId";
            }
            if ($requestBillingContact) {
                $requestUsers[] = "contact.id = :userId";
            }

            if (count($requestUsers) > 0) {
                $requestUsersStr = implode(" OR ", $requestUsers);
                $dql->andWhere($requestUsersStr);
                $dqlParameters["userId"] = $substituteUserId;
                $requestProcessed = true;
            }

        } else {
            return "No requests to update: Substitute user is not specified";
        }

        if (!$requestProcessed) {
            return "No requests to update";
        }


        $query = $dql->getQuery();

        //echo "requestId=".$request->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";
        //echo "query=".$query->getSql()."<br>";

        if (count($dqlParameters) > 0) {
            $query->setParameters($dqlParameters);
        }

        $requests = $query->getResult();
        echo "Requests count=".count($requests)."<br>";
        ///////////// EOF Filter Requests //////////////////

        $msgRequests = array();

        echo "<br>";
        foreach($requests as $request) {

            $toFlush = false;
            //$toFlushReviewer = false;
            $msgArr = array();

            echo "-----" . $request->getId() . "-----<br>";
            if( $request->getPrincipalInvestigators()->contains($substituteUser) ) {
                if( $requestPis ) {
                    echo "### User is PI <br>";
                    $request->removePrincipalInvestigator($substituteUser);
                    $request->addPrincipalInvestigator($replaceUser);
                    $toFlush = true;
                    $msgArr[] = $this->getMsg("PI",$substituteUser,$replaceUser); //"PI substituted from " . $substituteUser . " to " . $replaceUser;
                }
            }
            if( $request->getContact() && $request->getContact()->getId() == $substituteUserId ) {
                if( $requestBillingContact ) {
                    echo "### User is Billing Contact <br>";
                    $request->setContact($replaceUser);
                    $toFlush = true;
                    $msgArr[] = $this->getMsg("Billing Contact",$substituteUser,$replaceUser); //"PI substituted from " . $substituteUser . " to " . $replaceUser;
                }
            }

            if( $toFlush && !$testing ) {
                $em->flush($request);
                echo "updated request <br>";
            }

            //eventlog
            if( count($msgArr) > 0 ) {
                $eventType = "Request User Substituted";
                $msg = implode("<br>", $msgArr);
                //$msgRequests[] = "----- Request ".$request->getOid()." -----<br>".$msg;
                $msgRequests[] = "----- Request ".$transresRequestUtil->getRequestShowUrl($request,true,$request->getOid(),true)." -----<br>".$msg;
                $transresUtil->setEventLog($request, $eventType, $msg, $testing);
            }
        }

        if( count($msgRequests) > 0 ) {
            $msgRequestsStr = implode("<br>", $msgRequests);
        } else {
            $msgRequestsStr = "No work requests to update based on the specified criteria.";
        }

        return $msgRequestsStr;
    }

    public function getFilteredInvoices($form) {
        $em = $this->getDoctrine()->getManager();
        $transresUtil = $this->container->get('transres_util');
        $transresRequestUtil = $this->container->get('transres_request_util');

        $testing = false;
        //$testing = true;

        $projectSpecialties = $form->get('projectSpecialty')->getData();
        $substituteUser = $form->get('substituteUser')->getData();
        $replaceUser = $form->get('replaceUser')->getData();
        echo "projectSpecialties=" . count($projectSpecialties) . "<br>";
        echo "substituteUser=" . $substituteUser . "<br>";
        echo "replaceUser=" . $replaceUser . "<br>";

        if ($projectSpecialties && count($projectSpecialties) > 0) {
            //ok
        } else {
            return "No invoices to update: Project specialty is not specified";
        }
        if (!$substituteUser) {
            return "No invoices to update: Substitute user is not specified";
        }
        if (!$replaceUser) {
            return "No invoices to update: Replace user is not specified";
        }
        if($replaceUser->getId() == $substituteUser->getId()) {
            return "No invoices to update: substitute and replace users are the same";
        }

        $substituteUserId = $substituteUser->getId();
        //$replaceUserId = $replaceUser->getId();

        $excludedInvoicePaid = $form->get('excludedInvoicePaid')->getData();
        $excludedInvoicePartiallyPaid = $form->get('excludedInvoicePartiallyPaid')->getData();
        $excludedInvoiceCanceled = $form->get('excludedInvoiceCanceled')->getData();

        if( 0 ) {
            $invoicePi = $form->get('invoicePi')->getData();
        } else {
            $invoicePi = null;
        }
        $invoiceBillingContact = $form->get('invoiceBillingContact')->getData();
        $invoiceSalesperson = $form->get('invoiceSalesperson')->getData();

        ///////////// Filter Invoices //////////////////
        $repository = $em->getRepository('AppTranslationalResearchBundle:Invoice');
        $dql = $repository->createQueryBuilder("invoice");
        $dql->select('invoice');

        $dql->leftJoin('invoice.transresRequest', 'transresRequest');
        $dql->leftJoin('transresRequest.project', 'project');

        $dql->leftJoin('invoice.principalInvestigator', 'principalInvestigator');
        $dql->leftJoin('invoice.salesperson', 'salesperson');
        $dql->leftJoin('invoice.billingContact', 'billingContact');

        $dql->orderBy("invoice.id", "DESC");

        $dqlParameters = array();

        if ($projectSpecialties && count($projectSpecialties) > 0) {
            $dql->leftJoin('project.projectSpecialty', 'projectSpecialty');
            $projectSpecialtyIdsArr = array();
            foreach ($projectSpecialties as $projectSpecialty) {
                $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
            }
            $dql->andWhere("projectSpecialty.id IN (:projectSpecialtyIdsArr)");
            $dqlParameters["projectSpecialtyIdsArr"] = $projectSpecialtyIdsArr;
        } else {
            return "No invoices to update: project specialty is not specified";
        }

        if ($excludedInvoicePaid) {
            $dql->andWhere("invoice.status != 'Paid in Full'");
        }
        if ($excludedInvoicePartiallyPaid) {
            $dql->andWhere("invoice.status != 'Paid Partially'");
        }
        if ($excludedInvoiceCanceled) {
            $dql->andWhere("invoice.status != 'Canceled'");
        }

        $invoiceProcessed = false;

        if ($substituteUser && $substituteUserId) {

            $invoiceUsers = array();

            if ($invoicePi) {
                $invoiceUsers[] = "principalInvestigator.id = :userId";
            }
            if ($invoiceBillingContact) {
                $invoiceUsers[] = "billingContact.id = :userId";
            }
            if ($invoiceSalesperson) {
                $invoiceUsers[] = "salesperson.id = :userId";
            }

            if (count($invoiceUsers) > 0) {
                $invoiceUsersStr = implode(" OR ", $invoiceUsers);
                $dql->andWhere($invoiceUsersStr);
                $dqlParameters["userId"] = $substituteUserId;
                $invoiceProcessed = true;
            }

        } else {
            return "No invoices to update: Substitute user is not specified";
        }

        if (!$invoiceProcessed) {
            return "No invoices to update";
        }


        $query = $dql->getQuery();

        //echo "invoiceId=".$invoice->getId()."<br>";
        //echo "reviewId=".$reviewId."<br>";
        //echo "query=".$query->getSql()."<br>";

        if (count($dqlParameters) > 0) {
            $query->setParameters($dqlParameters);
        }

        $invoices = $query->getResult();
        echo "Invoices count=".count($invoices)."<br>";
        ///////////// EOF Filter Invoices //////////////////

        $msgInvoices = array();

        echo "<br>";
        foreach($invoices as $invoice) {

            $toFlush = false;
            //$toFlushReviewer = false;
            $msgArr = array();

            echo "-----" . $invoice->getId() . "-----<br>";
            if( $invoice->getPrincipalInvestigator() && $invoice->getPrincipalInvestigator()->getId() == $substituteUserId ) {
                if( $invoicePi ) {
                    echo "### User is PI <br>";
                    $invoice->setPrincipalInvestigator($replaceUser);
                    $toFlush = true;
                    $msgArr[] = $this->getMsg("PI",$substituteUser,$replaceUser);
                }
            }
            if( $invoice->getBillingContact() && $invoice->getBillingContact()->getId() == $substituteUserId ) {
                if( $invoiceBillingContact ) {
                    echo "### User is Billing Contact <br>";
                    $invoice->setBillingContact($replaceUser);
                    $toFlush = true;
                    $msgArr[] = $this->getMsg("Billing Contact",$substituteUser,$replaceUser);
                }
            }
            if( $invoice->getSalesperson() && $invoice->getSalesperson()->getId() == $substituteUserId ) {
                if( $invoiceSalesperson ) {
                    echo "### User is Billing Contact <br>";
                    $invoice->setSalesperson($replaceUser);
                    $toFlush = true;
                    $msgArr[] = $this->getMsg("Salesperson",$substituteUser,$replaceUser);
                }
            }

            if( $toFlush && !$testing ) {
                $em->flush($invoice);
                echo "updated invoice <br>";
            }

            //eventlog
            if( count($msgArr) > 0 ) {
                $eventType = "Invoice User Substituted";
                $msg = implode("<br>", $msgArr);
                //$msgInvoices[] = "----- Invoice ".$invoice->getOid()." -----<br>".$msg;
                $msgInvoices[] = "----- Invoice ".$transresRequestUtil->getInvoiceShowUrl($invoice,true,$invoice->getOid(),true)." -----<br>".$msg;
                $transresUtil->setEventLog($invoice, $eventType, $msg, $testing);
            }
        }//foreach invoice

        if( count($msgInvoices) > 0 ) {
            $msgInvoicesStr = implode("<br>", $msgInvoices);
        } else {
            $msgInvoicesStr = "No invoices to update based on the specified criteria.";
        }

        return $msgInvoicesStr;
    }

    public function flushObject($entity,$testing) {
        if( $entity && !$testing ) {
            //exit('exit on flush review');
            $em = $this->getDoctrine()->getManager();
            $em->flush($entity);
            echo "updated reviewer <br>";
        }
    }

    public function getMsg($name,$substituteUser,$replaceUser) {
        return $name . " " . $substituteUser . "  substituted with " . $replaceUser;
    }

}
