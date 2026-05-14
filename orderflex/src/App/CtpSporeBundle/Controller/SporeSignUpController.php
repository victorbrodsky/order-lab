<?php

namespace App\CtpSporeBundle\Controller;


use App\UserdirectoryBundle\Controller\SignUpController;
use App\UserdirectoryBundle\Entity\SignUp;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class SporeSignUpController extends SignUpController
{


    public function __construct() {
        $this->siteName = 'spore';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Prostate Cancer Research Data Explorer';
        $this->pathHome = 'ctpspore_home';
        $this->minimumRoles = array(
            'ROLE_CTPSPORE_USER'
        );
        $this->roleAdmins = array(
            'ROLE_CTPSPORE_ADMIN'
        );
    }

    /**
     * Lists all signUp entities.
     */
    #[Route(path: '/signup-list', name: 'ctpspore_signup_index', methods: ['GET'])]
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * http://localhost/order/directory/sign-up/new
     * Creates a new signUp entity.
     */
    #[Route(path: '/sign-up', name: 'ctpspore_signup_new', methods: ['GET', 'POST'])]
    public function newSignUpAction(Request $request)
    {
        return parent::newSignUpAction($request);
    }

    #[Route(path: '/activate-account/{registrationLinkID}', name: 'ctpspore_activate_account', methods: ['GET', 'POST'])]
    public function activateAccountAction(Request $request, TokenStorageInterface $tokenStorage, $registrationLinkID)
    {
        return parent::activateAccountAction($request,$tokenStorage,$registrationLinkID);
    }

    /**
     * Finds and displays a signUp entity.
     */
    #[Route(path: '/signup-show/{id}', name: 'ctpspore_signup_show', methods: ['GET'])]
    public function showAction(SignUp $signUp)
    {
        return parent::indexAction($signUp);
    }

    /**
     * Displays a form to edit an existing signUp entity.
     */
    #[Route(path: '/signup-edit/{id}', name: 'ctpspore_signup_edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request, SignUp $signUp)
    {
        return parent::editAction($request,$signUp);
    }

    /**
     * Deletes a signUp entity.
     */
    #[Route(path: '/signup-delete/{id}', name: 'ctpspore_signup_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, SignUp $signUp)
    {
        return parent::deleteAction($request,$signUp);
    }

    #[Route(path: '/forgot-password', name: 'ctpspore_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPasswordAction(Request $request)
    {
        return parent::forgotPasswordAction($request);
    }

    /**
     * http://localhost/order/directory/reset-password
     */
    #[Route(path: '/reset-password/{resetPasswordLinkID}', name: 'ctpspore_reset_password', methods: ['GET', 'POST'])]
    public function resetPasswordAction(Request $request, $resetPasswordLinkID)
    {
        return parent::resetPasswordAction($request,$resetPasswordLinkID);
    }
}
