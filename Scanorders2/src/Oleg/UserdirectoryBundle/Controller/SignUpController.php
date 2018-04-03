<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\SignUp;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Signup controller.
 *
 * @Route("sign-up")
 */
class SignUpController extends Controller
{
    /**
     * Lists all signUp entities.
     *
     * @Route("/", name="signup_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $signUps = $em->getRepository('OlegUserdirectoryBundle:SignUp')->findAll();

        return $this->render('OlegUserdirectoryBundle:SignUp:index.html.twig', array(
            'signUps' => $signUps,
        ));
    }

    /**
     * Creates a new signUp entity.
     *
     * @Route("/new", name="signup_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $signUp = new Signup();
        $form = $this->createForm('Oleg\UserdirectoryBundle\Form\SignUpType', $signUp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($signUp);
            $em->flush();

            return $this->redirectToRoute('signup_show', array('id' => $signUp->getId()));
        }

        return $this->render('OlegUserdirectoryBundle:SignUp:new.html.twig', array(
            'signUp' => $signUp,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a signUp entity.
     *
     * @Route("/{id}", name="signup_show")
     * @Method("GET")
     */
    public function showAction(SignUp $signUp)
    {
        $deleteForm = $this->createDeleteForm($signUp);

        return $this->render('OlegUserdirectoryBundle:SignUp:new.html.twig', array(
            'signUp' => $signUp,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing signUp entity.
     *
     * @Route("/{id}/edit", name="signup_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, SignUp $signUp)
    {
        $deleteForm = $this->createDeleteForm($signUp);
        $editForm = $this->createForm('Oleg\UserdirectoryBundle\Form\SignUpType', $signUp);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('signup_edit', array('id' => $signUp->getId()));
        }

        return $this->render('OlegUserdirectoryBundle:SignUp:new.html.twig', array(
            'signUp' => $signUp,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a signUp entity.
     *
     * @Route("/{id}", name="signup_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, SignUp $signUp)
    {
        $form = $this->createDeleteForm($signUp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($signUp);
            $em->flush();
        }

        return $this->redirectToRoute('signup_index');
    }

    /**
     * Creates a form to delete a signUp entity.
     *
     * @param SignUp $signUp The signUp entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(SignUp $signUp)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('signup_delete', array('id' => $signUp->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
