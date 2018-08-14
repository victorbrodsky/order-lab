<?php

namespace Oleg\TranslationalResearchBundle\Controller;


use Oleg\UserdirectoryBundle\Controller\SignUpController;
use Oleg\UserdirectoryBundle\Entity\SignUp;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;


class TransResSignUpController extends SignUpController
{


    public function __construct() {
        $this->siteName = 'translationalresearch'; //controller is not setup yet, so we can't use $this->container->getParameter('employees.sitename');
        $this->siteNameShowuser = 'translationalresearch';
        $this->siteNameStr = 'Translational Research';
        $this->pathHome = 'translationalresearch_home';
        $this->minimumRoles = array('ROLE_TRANSRES_REQUESTER_APCP', 'ROLE_TRANSRES_REQUESTER_HEMATOPATHOLOGY');
        $this->roleAdmins = array('ROLE_TRANSRES_ADMIN_APCP','ROLE_TRANSRES_ADMIN_HEMATOPATHOLOGY');
    }

    /**
     * Lists all signUp entities.
     *
     * @Route("/signup-list", name="translationalresearch_signup_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * http://localhost/order/directory/sign-up/new
     * Creates a new signUp entity.
     *
     * @Route("/sign-up", name="translationalresearch_signup_new")
     * @Method({"GET", "POST"})
     */
    public function newSignUpAction(Request $request)
    {
        return parent::newSignUpAction($request);
    }

    /**
     * @Route("/activate-account/{registrationLinkID}", name="translationalresearch_activate_account")
     * @Method({"GET", "POST"})
     */
    public function activateAccountAction(Request $request, $registrationLinkID)
    {
        return parent::activateAccountAction($request,$registrationLinkID);
    }

    /**
     * Finds and displays a signUp entity.
     *
     * @Route("/signup-show/{id}", name="translationalresearch_signup_show")
     * @Method("GET")
     */
    public function showAction(SignUp $signUp)
    {
        return parent::indexAction($signUp);
    }

    /**
     * Displays a form to edit an existing signUp entity.
     *
     * @Route("/signup-edit/{id}", name="translationalresearch_signup_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, SignUp $signUp)
    {
        return parent::editAction($request,$signUp);
    }

    /**
     * Deletes a signUp entity.
     *
     * @Route("/signup-delete/{id}", name="translationalresearch_signup_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, SignUp $signUp)
    {
        return parent::deleteAction($request,$signUp);
    }

    /**
     * @Route("/forgot-password", name="translationalresearch_forgot_password")
     * @Method({"GET", "POST"})
     */
    public function forgotPasswordAction(Request $request)
    {
        return parent::forgotPasswordAction($request);
    }

    /**
     * http://localhost/order/directory/reset-password
     *
     * @Route("/reset-password/{resetPasswordLinkID}", name="translationalresearch_reset_password")
     * @Method({"GET", "POST"})
     */
    public function resetPasswordAction(Request $request, $resetPasswordLinkID)
    {
        return parent::resetPasswordAction($request,$resetPasswordLinkID);
    }
}
