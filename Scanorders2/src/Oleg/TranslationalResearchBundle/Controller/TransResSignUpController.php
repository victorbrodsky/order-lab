<?php

namespace Oleg\TranslationalResearchBundle\Controller;


use Oleg\UserdirectoryBundle\Controller\SignUpController;
use Oleg\UserdirectoryBundle\Entity\SignUp;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("sign-up")
 */
class TransResSignUpController extends SignUpController
{


    public function __construct() {
        $this->siteName = 'translationalresearch'; //controller is not setup yet, so we can't use $this->container->getParameter('employees.sitename');
        $this->siteNameShowuser = 'translationalresearch';
        $this->siteNameStr = 'Translational Research';
        $this->pathHome = 'translationalresearch_home';
        $this->minimumRoles = array('ROLE_TRANSRES_REQUESTER','ROLE_TRANSRES_APCP');
        $this->roleAdmins = array('ROLE_TRANSRES_ADMIN');
    }

    /**
     * Lists all signUp entities.
     *
     * @Route("/", name="translationalresearch_signup_index")
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
     * @Route("/new", name="translationalresearch_signup_new")
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
     * @Route("/{id}", name="translationalresearch_signup_show")
     * @Method("GET")
     */
    public function showAction(SignUp $signUp)
    {
        return parent::indexAction($signUp);
    }

    /**
     * Displays a form to edit an existing signUp entity.
     *
     * @Route("/{id}/edit", name="translationalresearch_signup_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, SignUp $signUp)
    {
        return parent::editAction($request,$signUp);
    }

    /**
     * Deletes a signUp entity.
     *
     * @Route("/{id}", name="translationalresearch_signup_delete")
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
}
