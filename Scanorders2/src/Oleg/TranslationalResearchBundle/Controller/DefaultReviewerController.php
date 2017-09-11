<?php

namespace Oleg\TranslationalResearchBundle\Controller;

use Oleg\TranslationalResearchBundle\Entity\DefaultReviewer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Defaultreviewer controller.
 *
 * @Route("translationalresearch_default-reviewer")
 */
class DefaultReviewerController extends Controller
{
    /**
     * Lists all defaultReviewer entities.
     *
     * @Route("/", name="translationalresearch_default-reviewer_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $defaultReviewers = $em->getRepository('OlegTranslationalResearchBundle:DefaultReviewer')->findAll();

        return $this->render('defaultreviewer/index.html.twig', array(
            'defaultReviewers' => $defaultReviewers,
        ));
    }

    /**
     * Creates a new defaultReviewer entity.
     *
     * @Route("/new", name="translationalresearch_default-reviewer_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $defaultReviewer = new Defaultreviewer();
        $form = $this->createForm('Oleg\TranslationalResearchBundle\Form\DefaultReviewerType', $defaultReviewer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($defaultReviewer);
            $em->flush();

            return $this->redirectToRoute('translationalresearch_default-reviewer_show', array('id' => $defaultReviewer->getId()));
        }

        return $this->render('defaultreviewer/new.html.twig', array(
            'defaultReviewer' => $defaultReviewer,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a defaultReviewer entity.
     *
     * @Route("/{id}", name="translationalresearch_default-reviewer_show")
     * @Method("GET")
     */
    public function showAction(DefaultReviewer $defaultReviewer)
    {
        $deleteForm = $this->createDeleteForm($defaultReviewer);

        return $this->render('defaultreviewer/show.html.twig', array(
            'defaultReviewer' => $defaultReviewer,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing defaultReviewer entity.
     *
     * @Route("/{id}/edit", name="translationalresearch_default-reviewer_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, DefaultReviewer $defaultReviewer)
    {
        $deleteForm = $this->createDeleteForm($defaultReviewer);
        $editForm = $this->createForm('Oleg\TranslationalResearchBundle\Form\DefaultReviewerType', $defaultReviewer);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('translationalresearch_default-reviewer_edit', array('id' => $defaultReviewer->getId()));
        }

        return $this->render('defaultreviewer/edit.html.twig', array(
            'defaultReviewer' => $defaultReviewer,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a defaultReviewer entity.
     *
     * @Route("/{id}", name="translationalresearch_default-reviewer_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, DefaultReviewer $defaultReviewer)
    {
        $form = $this->createDeleteForm($defaultReviewer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($defaultReviewer);
            $em->flush();
        }

        return $this->redirectToRoute('translationalresearch_default-reviewer_index');
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
}
