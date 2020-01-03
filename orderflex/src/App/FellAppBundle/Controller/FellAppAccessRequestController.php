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

namespace App\FellAppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\AccessRequestController;

/**
 * AccessRequest controller.
 */
class FellAppAccessRequestController extends AccessRequestController
{

    public function __construct() {
        $this->siteName = 'fellapp';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Fellowship Applications';
        $this->roleBanned = 'ROLE_FELLAPP_BANNED';
        $this->roleUser = 'ROLE_FELLAPP_USER';
        $this->roleUnapproved = 'ROLE_FELLAPP_UNAPPROVED';
        $this->roleEditor = 'ROLE_FELLAPP_COORDINATOR';
    }

    /**
     * @Route("/access-requests/new/create", name="fellapp_access_request_new_plain")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreatePlainAction(Request $request)
    {
        return parent::accessRequestCreatePlain($request);
    }

    /**
     * @Route("/access-requests/new", name="scan_access_request_new")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction()
    {
        return parent::accessRequestCreateAction();
    }

    /**
     * @Route("/access-requests/new/pending", name="fellapp_access_request_create")
     * @Method("POST")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestAction(Request $request)
    {
        return parent::accessRequestAction($request);
    }

    /**
     * Lists all Access Request.
     *
     * @Route("/access-requests", name="fellapp_accessrequest_list")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request_list.html.twig")
     */
    public function accessRequestIndexAction(Request $request)
    {
        return parent::accessRequestIndexAction($request);
    }

    /**
     * @Route("/access-requests/change-status/{id}/{status}", name="fellapp_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction(Request $request, $id, $status)
    {
        return parent::accessRequestChangeAction($request, $id, $status);
    }

    /**
     * @Route("/access-requests/{id}", name="fellapp_accessrequest_management", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request_management.html.twig")
     */
    public function accessRequestManagementAction(Request $request, $id )
    {
        return parent::accessRequestManagementAction($request,$id);
    }

    /**
     * @Route("/access-requests/submit/{id}", name="fellapp_accessrequest_management_submit", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request_management.html.twig")
     */
    public function accessRequestManagementSubmitAction(Request $request, $id )
    {
        return parent::accessRequestManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/deny-access-request/{userId}", name="fellapp_accessrequest_remove", requirements={"userId" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestRemoveAction(Request $reques, $userId )
    {
        return parent::accessRequestRemoveAction($reques, $userId);
    }

    /**
     * @Route("/authorized-users/", name="fellapp_authorized_users")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:authorized_users.html.twig")
     */
    public function authorizedUsersAction(Request $request )
    {
        return parent::authorizedUsersAction($request);
    }

    /**
     * @Route("/authorization-user-manager/{id}", name="fellapp_authorization_user_management", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request_management.html.twig")
     */
    public function authorizationManagementAction( Request $request, $id )
    {
        return parent::authorizationManagementAction($request,$id);
    }

    /**
     * @Route("/authorization-user-manager/submit/{id}", name="fellapp_authorization_user_management_submit", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request_management.html.twig")
     */
    public function authorizationManagementSubmitAction( Request $request, $id )
    {
        return parent::authorizationManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/revoke-access-authorization/{userId}", name="fellapp_authorization_remove", requirements={"userId" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function authorizationRemoveAction(Request $request, $userId)
    {
        return parent::authorizationRemoveAction($request,$userId);
    }

    /**
     * @Route("/add-authorized-user/", name="fellapp_add_authorized_user")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:add_authorized_user.html.twig")
     */
    public function addAuthorizedUserAction( Request $request )
    {
        return parent::addAuthorizedUserAction($request);
    }

//    /**
//     * @Route("/add-authorized-user/submit/", name="fellapp_add_authorized_user_submit")
//     * @Method("POST")
//     * @Template("AppUserdirectoryBundle:AccessRequest:add_authorized_user.html.twig")
//     */
//    public function addAuthorizedUserSubmitAction( Request $request )
//    {
//        return parent::addAuthorizedUserSubmitAction($request);
//    }

}
