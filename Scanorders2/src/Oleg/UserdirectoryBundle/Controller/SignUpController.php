<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\SignUp;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Signup controller.
 *
 * @Route("sign-up")
 */
class SignUpController extends Controller
{

    protected $router;
    protected $siteName;
    protected $siteNameShowuser;
    protected $siteNameStr;

    public function __construct() {
        $this->siteName = 'employees'; //controller is not setup yet, so we can't use $this->container->getParameter('employees.sitename');
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Employee Directory';
    }

    /**
     * Lists all signUp entities.
     *
     * @Route("/", name="employees_signup_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $signUps = $em->getRepository('OlegUserdirectoryBundle:SignUp')->findAll();

        return $this->render('OlegUserdirectoryBundle:SignUp:index.html.twig', array(
            'signUps' => $signUps,
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));
    }

    /**
     * http://localhost/order/directory/sign-up/new
     * Creates a new signUp entity.
     *
     * @Route("/new", name="employees_signup_new")
     * @Method({"GET", "POST"})
     */
    public function newSignUpAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $signUp = new Signup();
        $form = $this->createForm('Oleg\UserdirectoryBundle\Form\SignUpType', $signUp);
        $form->handleRequest($request);

        $password = $form->get("password")->getData();

        if( $form->isSubmitted() ) {
            if( !$password ) {
                $form->get('password')->addError(new FormError('Please make sure your password is between 8 and 25 characters and contains at least one letter and at least one number.'));
            }
            if( !$signUp->getUserName() ) {
                $form->get('userName')->addError(new FormError('Please enter your User Name.'));
            }
            //if( !$signUp->getEmail() ) {
            //    $form->get('email')->addError(new FormError('The email value should not be blank.'));
            //}
        }

        if( $form->isSubmitted() && $form->isValid() ) {

            //TODO: hash password

            exit('flush');
            $em->persist($signUp);
            $em->flush();

            return $this->redirectToRoute('signup_show', array('id' => $signUp->getId()));
        }
        //exit('new');

        return $this->render('OlegUserdirectoryBundle:SignUp:new.html.twig', array(
            'signUp' => $signUp,
            'form' => $form->createView(),
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName
        ));
    }

    /**
     * Finds and displays a signUp entity.
     *
     * @Route("/{id}", name="employees_signup_show")
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
     * @Route("/{id}/edit", name="employees_signup_edit")
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
            'title' => "Sign Up for ".$this->siteNameStr,
            'sitenamefull' => $this->siteNameStr,
            'sitename' => $this->siteName,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a signUp entity.
     *
     * @Route("/{id}", name="employees_signup_delete")
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
