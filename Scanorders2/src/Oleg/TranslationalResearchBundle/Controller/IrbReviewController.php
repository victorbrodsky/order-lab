<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Entity\IrbReview;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Irbreview controller.
 *
 * @Route("irb-review")
 */
class IrbReviewController extends Controller
{
    /**
     * Lists all irbReview entities.
     *
     * @Route("/", name="translationalresearch_irb-review_index")
     * @Template("OlegTranslationalResearchBundle:IrbReview:index.html.twig")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $irbReviews = $em->getRepository('OlegTranslationalResearchBundle:IrbReview')->findAll();

        return array(
            'irbReviews' => $irbReviews,
        );
    }

    /**
     * Creates a new irbReview entity.
     *
     * @Route("/new", name="translationalresearch_irb-review_new")
     * @Template("OlegTranslationalResearchBundle:IrbReview:new.html.twig")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $irbReview = new Irbreview();
        $form = $this->createForm('Oleg\TranslationalResearchBundle\Form\IrbReviewType', $irbReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($irbReview);
            $em->flush();

            return $this->redirectToRoute('translationalresearch_irb-review_show', array('id' => $irbReview->getId()));
        }

        return array(
            'irbReview' => $irbReview,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a irbReview entity.
     *
     * @Route("/{id}", name="translationalresearch_irb-review_show")
     * @Template("OlegTranslationalResearchBundle:IrbReview:show.html.twig")
     * @Method("GET")
     */
    public function showAction(IrbReview $irbReview)
    {
        $deleteForm = $this->createDeleteForm($irbReview);

        return array(
            'irbReview' => $irbReview,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing irbReview entity.
     *
     * @Route("/{id}/edit", name="translationalresearch_irb-review_edit")
     * @Template("OlegTranslationalResearchBundle:IrbReview:edit.html.twig")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, IrbReview $irbReview)
    {
        $deleteForm = $this->createDeleteForm($irbReview);
        $editForm = $this->createForm('Oleg\TranslationalResearchBundle\Form\IrbReviewType', $irbReview);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('translationalresearch_irb-review_edit', array('id' => $irbReview->getId()));
        }

        return array(
            'irbReview' => $irbReview,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a irbReview entity.
     *
     * @Route("/{id}", name="translationalresearch_irb-review_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, IrbReview $irbReview)
    {
        $form = $this->createDeleteForm($irbReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($irbReview);
            $em->flush();
        }

        return $this->redirectToRoute('translationalresearch_irb-review_index');
    }

    /**
     * Creates a form to delete a irbReview entity.
     *
     * @param IrbReview $irbReview The irbReview entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(IrbReview $irbReview)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('translationalresearch_irb-review_delete', array('id' => $irbReview->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
