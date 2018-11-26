<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Entity\DefaultReviewer;
use Oleg\TranslationalResearchBundle\Entity\SpecialtyList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defaultreviewer controller.
 *
 * @Route("default-reviewers")
 */
class DefaultReviewerController extends Controller
{

    /**
     * Lists defaultReviewer states: irb_review, committee_review, final_review
     *
     * @Route("/{specialtyStr}", name="translationalresearch_default-reviewer_index")
     * @Template("OlegTranslationalResearchBundle:DefaultReviewer:index.html.twig")
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
//        $specialty = $em->getRepository('OlegTranslationalResearchBundle:SpecialtyList')->findOneByAbbreviation($specialtyAbbreviation);
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
     * @Route("/stage/{stateStr}/{specialtyStr}", name="translationalresearch_state-default-reviewer_index")
     * @Template("OlegTranslationalResearchBundle:DefaultReviewer:state-default-reviewer-index.html.twig")
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

        //$defaultReviewers = $em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findAll();

        $repository = $em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer');
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
     * @Route("/new/{stateStr}/{specialtyStr}", name="translationalresearch_default-reviewer_new")
     * @Template("OlegTranslationalResearchBundle:DefaultReviewer:new.html.twig")
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

        //$form = $this->createForm('Oleg\TranslationalResearchBundle\Form\DefaultReviewerType', $defaultReviewer);
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
     * @Route("/show/{id}", name="translationalresearch_default-reviewer_show")
     * @Template("OlegTranslationalResearchBundle:DefaultReviewer:new.html.twig")
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
     * @Route("/edit/{id}", name="translationalresearch_default-reviewer_edit")
     * @Template("OlegTranslationalResearchBundle:DefaultReviewer:new.html.twig")
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
        //$editForm = $this->createForm('Oleg\TranslationalResearchBundle\Form\DefaultReviewerType', $defaultReviewer);
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
     * @Route("/delete/{id}", name="translationalresearch_default-reviewer_delete")
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

        $form = $this->createForm('Oleg\TranslationalResearchBundle\Form\DefaultReviewerType', $defaultReviewer, array(
            'disabled' => $disabled,
            'form_custom_value' => $params
        ));

        return $form;
    }



    /**
     * Substitute user
     *
     * @Route("/substitute-user/", name="translationalresearch_substitute_user")
     * @Template("OlegTranslationalResearchBundle:DefaultReviewer:substitute-user.html.twig")
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
        $form = $this->createForm('Oleg\TranslationalResearchBundle\Form\SubstituteUserType', null, array(
            'form_custom_value' => $params
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//            $projectSpecialties = $form->get('projectSpecialty')->getData();
//            $substituteUser = $form->get('substituteUser')->getData();
//            $replaceUser = $form->get('replaceUser')->getData();
//            echo "projectSpecialties=".count($projectSpecialties)."<br>";
//            echo "substituteUser=".$substituteUser."<br>";
//            echo "replaceUser=".$replaceUser."<br>";
//
//            $excludedProjectCompleted = $form->get('excludedProjectCompleted')->getData();
//            $excludedProjectCanceled = $form->get('excludedProjectCanceled')->getData();
//            $excludedProjectDraft = $form->get('excludedProjectDraft')->getData();
//            echo "excludedProjectCompleted=".$excludedProjectCompleted."<br>";
//            echo "excludedProjectCanceled=".$excludedProjectCanceled."<br>";

            $projects = $this->getFilteredProjects($form);


            //exit('submit');

            //get projects

            //$transresUtil->processDefaultReviewersRole($defaultReviewer,$originalReviewer,$originalReviewerDelegate);

            //$this->getDoctrine()->getManager()->flush();

            //Event Log
            //$eventType = "TRP User Substitution";
            //$stateLabel = $transresUtil->getStateSimpleLabelByName($stateStr);
            //$specialtyStr = $defaultReviewer->getProjectSpecialty();
            //$transresUtil->setEventLog($defaultReviewer,$eventType,$msg);

            exit('substituted: projects count='.count($projects));
            return $this->redirectToRoute('translationalresearch_default-reviewer_show', array('id' => $defaultReviewer->getId()));
        }

        return array(
            'form' => $form->createView(),
            'title' => "Batch User Substitution",
        );
    }
    public function getFilteredProjects($form) {

        $em = $this->getDoctrine()->getManager();

        $projectSpecialties = $form->get('projectSpecialty')->getData();
        $substituteUser = $form->get('substituteUser')->getData();
        $replaceUser = $form->get('replaceUser')->getData();
        echo "projectSpecialties=".count($projectSpecialties)."<br>";
        echo "substituteUser=".$substituteUser."<br>";
        echo "replaceUser=".$replaceUser."<br>";

        if( $projectSpecialties && count($projectSpecialties) > 0 ) {
            //ok
        } else {
            return array();
        }
        if( !$substituteUser ) {
            return array();
        }
        if( !$replaceUser ) {
            return array();
        }

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
        $repository = $em->getRepository('OlegTranslationalResearchBundle:Project');
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
            return array();
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

        if( $substituteUser && $substituteUser->getId() ) {

            $projectUsers = array();

            if( $projectPis ) {
                $projectUsers[] = "principalInvestigators.id = :userId";
            }
            if( $projectPisIrb ) {
                $projectUsers[] = "principalIrbInvestigator.id = :userId";
            }
            if( $projectPathologists ) {
                $projectUsers[] = "pathologists.id = :userId";
                //$dql->andWhere("pathologists.id = :userId");
                //$dqlParameters["userId"] = $substituteUser->getId();
                //$projectProcessed = true;
            }
            if( $projectCoInvestigators ) {
                $projectUsers[] = "coInvestigators.id = :userId";
                //$dql->andWhere("coInvestigators.id = :userId");
                //$dqlParameters["userId"] = $substituteUser->getId();
                //$projectProcessed = true;
            }
            if( $projectContacts ) {
                $projectUsers[] = "contacts.id = :userId";
                //$dql->andWhere("contacts.id = :userId");
                //$dqlParameters["userId"] = $substituteUser->getId();
                //$projectProcessed = true;
            }
            if( $projectBillingContact ) {
                $projectUsers[] = "billingContact.id = :userId";
                //$dql->andWhere("billingContact.id = :userId");
                //$dqlParameters["userId"] = $substituteUser->getId();
                //$projectProcessed = true;
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
                $dqlParameters["userId"] = $substituteUser->getId();
                $projectProcessed = true;
            }




        } else {
            return array();
        }

        if( !$projectProcessed ) {
            return array();
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

        echo "<br>";
        foreach($projects as $project) {
            echo "-----" . $project->getId() . "-----<br>";
            if( $project->getPrincipalInvestigators()->contains($substituteUser) ) {
                echo "### User is PI <br>";
            }
            if( $project->getPrincipalIrbInvestigators()->contains($substituteUser) ) {
                echo "### User is IRB PI <br>";
            }
            if( $project->getCoInvestigators()->contains($substituteUser) ) {
                echo "### User is CoInvestigator <br>";
            }
            if( $project->getPathologists()->contains($substituteUser) ) {
                echo "### User is Pathologist <br>";
            }
            if( $project->getContacts()->contains($substituteUser) ) {
                echo "### User is Contact <br>";
            }
            if( $project->getBillingContacts()->contains($substituteUser) ) {
                echo "### User is Billing Contact <br>";
            }

            foreach( $project->getCommitteeReviews() as $review) {
                if( $review->getReviewer() && $review->getReviewer()->getId() == $substituteUser->getId() ) {
                    $primary = "";
                    if( $review->getPrimaryReview() ) {
                        $primary = "(primary)";
                    }
                    echo "*** User is Committee Reviewer ".$primary." <br>";
                }
                if( $review->getReviewerDelegate() && $review->getReviewerDelegate()->getId() == $substituteUser->getId() ) {
                    $primary = "";
                    if( $review->getPrimaryReview() ) {
                        $primary = "(primary)";
                    }
                    echo "*** User is Committee Reviewer Delegate ".$primary."<br>";
                }
            }
            foreach( $project->getFinalReviews() as $review) {
                if( $review->getReviewer() && $review->getReviewer()->getId() == $substituteUser->getId() ) {
                    echo "*** User is Final Reviewer <br>";
                }
                if( $review->getReviewerDelegate() && $review->getReviewerDelegate()->getId() == $substituteUser->getId() ) {
                    echo "*** User is Final Reviewer Delegate <br>";
                }
            }

            echo "<br>";
        }//foreach project

        return $projects;
    }

}
