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
            $reviewerDelegateRole = $roles['reviewerDelegate'];
            $reviewer = $defaultReviewer->getReviewer();
            if( $reviewer ) {
                $reviewer->removeRole($reviewerRole);
            }
            $reviewerDelegate = $defaultReviewer->getReviewerDelegate();
            if( $reviewerDelegate && $reviewerDelegateRole ) {
                $reviewerDelegate->removeRole($reviewerDelegateRole);
            }

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
}
