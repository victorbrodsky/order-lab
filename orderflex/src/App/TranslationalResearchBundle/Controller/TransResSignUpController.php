<?php

namespace App\TranslationalResearchBundle\Controller;


use App\UserdirectoryBundle\Controller\SignUpController;
use App\UserdirectoryBundle\Entity\SignUp;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class TransResSignUpController extends SignUpController
{


    public function __construct() {
        $this->siteName = 'translationalresearch'; //controller is not setup yet, so we can't use $this->getParameter('employees.sitename');
        $this->siteNameShowuser = 'translationalresearch';
        $this->siteNameStr = 'Translational Research';
        $this->pathHome = 'translationalresearch_home';
        $this->minimumRoles = array('ROLE_TRANSRES_REQUESTER_APCP', 'ROLE_TRANSRES_REQUESTER_HEMATOPATHOLOGY', 'ROLE_TRANSRES_REQUESTER_COVID19', 'ROLE_TRANSRES_REQUESTER_MISI');
        $this->roleAdmins = array('ROLE_TRANSRES_ADMIN_APCP','ROLE_TRANSRES_ADMIN_HEMATOPATHOLOGY','ROLE_TRANSRES_ADMIN_COVID19','ROLE_TRANSRES_ADMIN_MISI');
    }

    /**
     * Lists all signUp entities.
     *
     * @Route("/signup-list", name="translationalresearch_signup_index", methods={"GET"})
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * http://localhost/order/directory/sign-up/new
     * Creates a new signUp entity.
     *
     * @Route("/sign-up", name="translationalresearch_signup_new", methods={"GET","POST"})
     */
    public function newSignUpAction(Request $request)
    {
        return parent::newSignUpAction($request);
    }

    /**
     * @Route("/activate-account/{registrationLinkID}", name="translationalresearch_activate_account", methods={"GET","POST"})
     */
    public function activateAccountAction(Request $request, TokenStorageInterface $tokenStorage, $registrationLinkID)
    {
        return parent::activateAccountAction($request,$tokenStorage,$registrationLinkID);
    }

    /**
     * Finds and displays a signUp entity.
     *
     * @Route("/signup-show/{id}", name="translationalresearch_signup_show", methods={"GET"})
     */
    public function showAction(SignUp $signUp)
    {
        return parent::indexAction($signUp);
    }

    /**
     * Displays a form to edit an existing signUp entity.
     *
     * @Route("/signup-edit/{id}", name="translationalresearch_signup_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, SignUp $signUp)
    {
        return parent::editAction($request,$signUp);
    }

    /**
     * Deletes a signUp entity.
     *
     * @Route("/signup-delete/{id}", name="translationalresearch_signup_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, SignUp $signUp)
    {
        return parent::deleteAction($request,$signUp);
    }

    /**
     * @Route("/forgot-password", name="translationalresearch_forgot_password", methods={"GET","POST"})
     */
    public function forgotPasswordAction(Request $request)
    {
        return parent::forgotPasswordAction($request);
    }

    /**
     * http://localhost/order/directory/reset-password
     *
     * @Route("/reset-password/{resetPasswordLinkID}", name="translationalresearch_reset_password", methods={"GET","POST"})
     */
    public function resetPasswordAction(Request $request, $resetPasswordLinkID)
    {
        return parent::resetPasswordAction($request,$resetPasswordLinkID);
    }
}
