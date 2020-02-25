<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\TranslationalResearchBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use App\TranslationalResearchBundle\Form\AccountConfirmationType;
use App\UserdirectoryBundle\Controller\AuthorizedUserController;
use App\UserdirectoryBundle\Entity\AdministrativeTitle;
use App\UserdirectoryBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\AccessRequestController;

/**
 * AccessRequest controller.
 */
class TranslationalResearchAccessRequestController extends AccessRequestController
{

    public function __construct() {
        $this->siteName = 'translationalresearch';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Translational Research';
        $this->roleBanned = 'ROLE_TRANSRES_BANNED';
        $this->roleUser = 'ROLE_TRANSRES_USER';
        $this->roleUnapproved = 'ROLE_TRANSRES_UNAPPROVED';
        $this->roleEditor = 'ROLE_TRANSRES_ADMIN';
    }

    /**
     * @Route("/access-requests/new/create", name="translationalresearch_access_request_new_plain")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    public function accessRequestCreatePlainAction(Request $request)
    {
        //exit('accessRequestCreatePlainAction');
        return parent::accessRequestCreatePlain($request);
    }

    /**
     * @Route("/access-requests/new", name="translationalresearch_access_request_new")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    public function accessRequestCreateAction()
    {
        return parent::accessRequestCreateAction();
    }

    /**
     * @Route("/access-requests/new/pending", name="translationalresearch_access_request_create")
     * @Method("POST")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    public function accessRequestAction(Request $request)
    {
        return parent::accessRequestAction($request);
    }

    /**
     * Lists all Access Request.
     *
     * @Route("/access-requests", name="translationalresearch_accessrequest_list")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_list.html.twig")
     */
    public function accessRequestIndexAction(Request $request)
    {
        return parent::accessRequestIndexAction($request);
    }

    /**
     * @Route("/access-requests/change-status/{id}/{status}", name="translationalresearch_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction(Request $request, $id, $status)
    {
        return parent::accessRequestChangeAction($request, $id, $status);
    }

    /**
     * @Route("/access-requests/{id}", name="translationalresearch_accessrequest_management", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function accessRequestManagementAction( Request $request, $id )
    {
        return parent::accessRequestManagementAction($request,$id);
    }

    /**
     * @Route("/access-requests/submit/{id}", name="translationalresearch_accessrequest_management_submit", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function accessRequestManagementSubmitAction(Request $request, $id )
    {
        return parent::accessRequestManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/deny-access-request/{userId}", name="translationalresearch_accessrequest_remove", requirements={"userId" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestRemoveAction(Request $request, $userId )
    {
        return parent::accessRequestRemoveAction($request,$userId);
    }

    /**
     * @Route("/authorized-users/", name="translationalresearch_authorized_users")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/authorized_users.html.twig")
     */
    public function authorizedUsersAction(Request $request )
    {
        return parent::authorizedUsersAction($request);
    }

    /**
     * @Route("/authorization-user-manager/{id}", name="translationalresearch_authorization_user_management", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function authorizationManagementAction( Request $request, $id )
    {
        return parent::authorizationManagementAction($request,$id);
    }

    /**
     * @Route("/authorization-user-manager/submit/{id}", name="translationalresearch_authorization_user_management_submit", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function authorizationManagementSubmitAction( Request $request, $id )
    {
        return parent::authorizationManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/revoke-access-authorization/{userId}", name="translationalresearch_authorization_remove", requirements={"userId" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function authorizationRemoveAction(Request $request, $userId)
    {
        return parent::authorizationRemoveAction($request,$userId);
    }


    /**
     * @Route("/add-authorized-user/", name="translationalresearch_add_authorized_user")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
     */
    public function addAuthorizedUserAction( Request $request )
    {
        return parent::addAuthorizedUserAction($request);
    }

//    /**
//     * @Route("/add-authorized-user/submit/", name="translationalresearch_add_authorized_user_submit")
//     * @Method("POST")
//     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
//     */
//    public function addAuthorizedUserSubmitAction( Request $request )
//    {
//        return parent::addAuthorizedUserSubmitAction($request);
//    }

    /**
     * @Route("/generated-users/", name="translationalresearch_generated_users")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/generated_users.html.twig")
     */
    public function generatedUsersAction(Request $request)
    {
//        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_ADMIN')) {
//            return $this->redirect($this->generateUrl('employees-nopermission'));
//        }

        return parent::generatedUsersAction($request);
    }
    /**
     * @Route("/generated-user/{id}", name="translationalresearch_generated_user_management")
     * @Template("AppUserdirectoryBundle/AccessRequest/generated_user_management.html.twig")
     * @Method({"GET", "POST"})
     */
    public function generatedUserManagementAction(Request $request, User $user)
    {
        return parent::generatedUserManagementAction($request,$user);
    }

    /**
     * @Route("/generated-user/approve/{id}", name="translationalresearch_generated_user_approve")
     * @Method({"GET", "POST"})
     */
    public function generatedUserApproveAction(Request $request, User $user)
    {
        return parent::generatedUserApproveAction($request,$user);
    }

    /**
     * Example: http://localhost/order/translational-research/account-confirmation/translationalresearch_project_new/hematopathology
     *
     * @Route("/account-confirmation/{redirectPath}/{specialty}", name="translationalresearch_account_confirmation")
     * @Template("AppTranslationalResearchBundle/AccessRequest/account_confirmation.html.twig")
     * @Method({"GET", "POST"})
     */
    public function accountConfirmationAction(Request $request, $redirectPath, $specialty=null)
    {
        //echo "user=".$user."; redirectPath=".$redirectPath."; specialty=".$specialty."<br>";
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_TRANSRES_USER') ) {
            return $this->redirect( $this->generateUrl($this->container->getParameter('translationalresearch.sitename').'-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $sitename = $this->container->getParameter('translationalresearch.sitename');
        $cycle = "new";

        if( count($user->getAdministrativeTitles()) == 0 ) {
            $user->addAdministrativeTitle(new AdministrativeTitle($user));
        }
        //echo "admins=".count($user->getAdministrativeTitles())."<br>";

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'container' => $this->container,
            'user' => $user,
        );
        $form = $this->createForm(AccountConfirmationType::class, $user, array(
            'form_custom_value' => $params,
        ));

        $form->handleRequest($request);


        if( $form->isSubmitted() && $form->isValid() ) {

            //echo $user->getId().": Display Name=".$user->getSingleEmail(false)."<br>";
            //exit('accountConfirmationAction submit');

            $em->flush();

            if( $specialty ) {
                return $this->redirectToRoute($redirectPath, array('specialtyStr' => $specialty));
            } else {
                return $this->redirectToRoute($redirectPath);
            }
        }

        return array(
            'user' => $user,
            'form' => $form->createView(),
            'title' => "Profile Details for ".$user,
            'cycle' => $cycle,
            'sitename' => $sitename,
            'redirectPath' => $redirectPath,
            'specialty' => $specialty
        );
    }

//    public function createAccountConfirmationForm( $invoice, $cycle, $transresRequest=null ) {
//
//        $em = $this->getDoctrine()->getManager();
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//
//        $params = array(
//            'cycle' => $cycle,
//            'em' => $em,
//            'user' => $user,
//            'invoice' => $invoice,
//            'statuses' => $transresRequestUtil->getInvoiceStatuses(),
//            'principalInvestigators' => $principalInvestigators,
//            //'piEm' => $piEm,
//            'SecurityAuthChecker' => $this->get('security.authorization_checker'),
//        );
//
//        if( $cycle == "new" ) {
//            $disabled = false;
//        }
//
//        if( $cycle == "show" ) {
//            $disabled = true;
//        }
//
//        if( $cycle == "edit" ) {
//            $disabled = false;
//        }
//
//        if( $cycle == "download" ) {
//            $disabled = true;
//        }
//
//        $form = $this->createForm(InvoiceType::class, $invoice, array(
//            'form_custom_value' => $params,
//            'disabled' => $disabled,
//        ));
//
//        return $form;
//    }

}
