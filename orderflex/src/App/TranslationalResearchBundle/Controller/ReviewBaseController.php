<?php

namespace App\TranslationalResearchBundle\Controller;

use App\TranslationalResearchBundle\Entity\IrbReview;
use App\TranslationalResearchBundle\Entity\Project;
use App\TranslationalResearchBundle\Form\ReviewBaseType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Irbreview controller.
 */
#[Route(path: 'review')]
class ReviewBaseController extends OrderAbstractController
{
    /**
     * Lists all irbReview entities.
     */
    #[Route(path: '/{stateStr}', name: 'translationalresearch_review_index', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Review/index.html.twig')]
    public function indexAction(Request $request, $stateStr)
    {
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:IrbReview'] by [IrbReview::class]
        $irbReviews = $em->getRepository(IrbReview::class)->findAll();

        return array(
            'irbReviews' => $irbReviews,
        );
    }

    /**
     * Creates a new irbReview entity.
     */
    #[Route(path: '/new', name: 'translationalresearch_review_new', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Review/new.html.twig')]
    public function newAction(Request $request)
    {
        $irbReview = new Irbreview();
        $form = $this->createForm('App\TranslationalResearchBundle\Form\IrbReviewType', $irbReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($irbReview);
            $em->flush();

            return $this->redirectToRoute('translationalresearch_review_show', array('id' => $irbReview->getId()));
        }

        return array(
            'irbReview' => $irbReview,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Review entity.
     */
    #[Route(path: '/{stateStr}/{reviewId}/show', name: 'translationalresearch_review_show', methods: ['GET'])]
    #[Template('AppTranslationalResearchBundle/Review/edit.html.twig')]
    public function showAction(Request $request, $stateStr, $reviewId)
    {

        $em = $this->getDoctrine()->getManager();
        //$user = $this->getUser();
        $transresUtil = $this->container->get('transres_util');
        $cycle = "show";

        $reviewEntityName = $transresUtil->getReviewClassNameByState($stateStr);
        if( !$reviewEntityName ) {
            throw $this->createNotFoundException('Unable to find Review Entity Name by state='.$stateStr);
        }
        //$review = $em->getRepository('AppTranslationalResearchBundle:'.$reviewEntityName)->find($reviewId);
        $review = $em->getRepository('App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName)->find($reviewId);
        if( !$review ) {
            throw $this->createNotFoundException('Unable to find '.$reviewEntityName.' by id='.$reviewId);
        }

        if( $transresUtil->isUserAllowedReview($review) === false ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $form = $this->createReviewForm($request, $review, $cycle, $stateStr);

        return array(
            'review' => $review,
            'form' => $form->createView(),
            'stateStr' => $stateStr,
            'title' => $transresUtil->getStateSimpleLabelByName($stateStr),
            'cycle' => $cycle
            //'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing irbReview entity.
     */
    #[Route(path: '/{stateStr}/{reviewId}/submit', name: 'translationalresearch_review_edit', methods: ['GET', 'POST'])]
    #[Template('AppTranslationalResearchBundle/Review/edit.html.twig')]
    public function editAction(Request $request, $stateStr, $reviewId)
    {
        if( false === $this->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        //$em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $transresUtil = $this->container->get('transres_util');
        $cycle = "edit";

        $testing = false;
        //$testing = true;

//        $reviewEntityName = $transresUtil->getReviewClassNameByState($stateStr);
//        if( !$reviewEntityName ) {
//            throw $this->createNotFoundException('Unable to find Review Entity Name by state='.$stateStr);
//        }
//        $review = $em->getRepository('AppTranslationalResearchBundle:'.$reviewEntityName)->find($reviewId);
//        if( !$review ) {
//            throw $this->createNotFoundException('Unable to find '.$reviewEntityName.' by id='.$reviewId);
//        }
        $review = $transresUtil->getReviewByReviewidAndState($reviewId,$stateStr);
        //echo "reviewID=".$review->getId();

        if( !$review ) {
            $this->addFlash(
                'warning',
                "Review edit: unable to find a pending review by review ID $reviewId and project state ".$stateStr
            );
        }

        if( $transresUtil->isUserAllowedReview($review) === false || $transresUtil->isReviewCorrespondsToState($review) === false ) {
            return $this->redirect( $this->generateUrl($this->getParameter('translationalresearch.sitename').'-nopermission') );
        }

//        $disabled = true;
//        if(
//            $this->isGranted('ROLE_TRANSRES_ADMIN') ||
//            $this->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER') ||
//            $this->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER_DELEGATE')
//        ) {
//            $disabled = false;
//        }
//
//        //can be edited if the logged in user is a reviewer or reviewerDelegate for this review object
//        if( $user == $review->getReviewer() || $user == $review->getReviewerDelegate() ) {
//            $disabled = false;
//        }
        //$deleteForm = $this->createDeleteForm($review);
//        $form = $this->createForm('App\TranslationalResearchBundle\Form\ReviewBaseType', $review, array(
//            'disabled' => $disabled
//        ));
        $form = $this->createReviewForm($request, $review, $cycle, $stateStr);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $review->setReviewedBy($user);

            if( !$testing ) {
                $this->getDoctrine()->getManager()->flush();
            }

            //set project next transit state depends on the decision
            //send notification emails
            //set eventLog
            $transresUtil->processProjectOnReviewUpdate($review,$stateStr,$request,$testing);

            if( $testing ) {
                exit("testing: exit submit review");
            }

            //return $this->redirectToRoute('translationalresearch_review_show', array('stateStr'=>$stateStr,'reviewId' => $review->getId()));
            return $this->redirectToRoute('translationalresearch_project_show', array('id' => $review->getProject()->getId()));
        }

        return array(
            'review' => $review,
            'form' => $form->createView(),
            'stateStr' => $stateStr,
            'title' => $transresUtil->getStateSimpleLabelByName($stateStr),
            'cycle' => $cycle
            //'delete_form' => $deleteForm->createView(),
        );
    }

//    /**
//     * Deletes a irbReview entity.
//     *
//     * @Route("/{id}", name="translationalresearch_review_delete", methods={"DELETE"})
//     */
//    public function deleteAction(Request $request, IrbReview $irbReview)
//    {
//        $form = $this->createDeleteForm($irbReview);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $em->remove($irbReview);
//            $em->flush();
//        }
//
//        return $this->redirectToRoute('translationalresearch_review_index');
//    }

//    /**
//     * Creates a form to delete a irbReview entity.
//     *
//     * @param IrbReview $irbReview The irbReview entity
//     *
//     * @return \Symfony\Component\Form\Form The form
//     */
//    private function createDeleteForm(IrbReview $irbReview)
//    {
//        return $this->createFormBuilder()
//            ->setAction($this->generateUrl('translationalresearch_review_delete', array('id' => $irbReview->getId())))
//            ->setMethod('DELETE')
//            ->getForm()
//        ;
//    }

    private function createReviewForm( $request, $review, $cycle, $stateStr )
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $transresUtil = $this->container->get('transres_util');
        $routeName = $request->get('_route');

        $reviewEntityName = $transresUtil->getReviewClassNameByState($stateStr);
        if( !$reviewEntityName ) {
            throw $this->createNotFoundException('Unable to find Review Entity Name by state='.$stateStr);
        }

        $disabled = false;

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $user,
            'transresUtil' => $transresUtil,
            //'SecurityAuthChecker' => $this->container->get('security.authorization_checker'),
            'review' => $review,
            'routeName' => $routeName,
            'stateStr' => $stateStr,
            'disabledReviewerFields' => false,
            'standAlone' => true,
        );

        if( $cycle == "show" ) {
            $disabled = true;
        }

        $params['admin'] = false;
        if(
            $this->isGranted('ROLE_TRANSRES_ADMIN') ||
            $this->isGranted('ROLE_TRANSRES_PRIMARY_REVIEWER')
        ) {
            $params['admin'] = true;
        }

        //check if reviewer
//        if( $transresUtil->isProjectReviewer($user,array($review)) ) {
//            $params['isReviewer'] = true;
//        }

        $form = $this->createForm(ReviewBaseType::class, $review, array(
            'data_class' => 'App\\TranslationalResearchBundle\\Entity\\'.$reviewEntityName,
            'form_custom_value' => $params,
            'disabled' => $disabled,
        ));

        return $form;
    }

}
