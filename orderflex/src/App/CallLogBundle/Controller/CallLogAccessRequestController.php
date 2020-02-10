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

namespace App\CallLogBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Controller\AuthorizedUserController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\AccessRequestController;

/**
 * calllog
 */
class CallLogAccessRequestController extends AccessRequestController
{

    public function __construct() {
        $this->siteName = 'calllog';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Call Log Book';
        $this->roleBanned = 'ROLE_CALLLOG_BANNED';
        $this->roleUser = 'ROLE_CALLLOG_USER';
        $this->roleUnapproved = 'ROLE_CALLLOG_UNAPPROVED';
        $this->roleEditor = 'ROLE_CALLLOG_ADMIN';
    }

    /**
     * @Route("/access-requests/new/create", name="calllog_access_request_new_plain")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    public function accessRequestCreatePlainAction(Request $request)
    {
        //exit('accessRequestCreatePlainAction');
        return parent::accessRequestCreatePlain($request);
    }

    /**
     * @Route("/access-requests/new", name="calllog_access_request_new")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    public function accessRequestCreateAction()
    {
        return parent::accessRequestCreateAction();
    }

    /**
     * @Route("/access-requests/new/pending", name="calllog_access_request_create")
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
     * @Route("/access-requests", name="calllog_accessrequest_list")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_list.html.twig")
     */
    public function accessRequestIndexAction(Request $request)
    {
        return parent::accessRequestIndexAction($request);
    }

    /**
     * @Route("/access-requests/change-status/{id}/{status}", name="calllog_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction(Request $request, $id, $status)
    {
        return parent::accessRequestChangeAction($request, $id, $status);
    }

    /**
     * @Route("/access-requests/{id}", name="calllog_accessrequest_management", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function accessRequestManagementAction(Request $request, $id )
    {
        return parent::accessRequestManagementAction($request,$id);
    }

    /**
     * @Route("/access-requests/submit/{id}", name="calllog_accessrequest_management_submit", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function accessRequestManagementSubmitAction(Request $request, $id )
    {
        return parent::accessRequestManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/deny-access-request/{userId}", name="calllog_accessrequest_remove", requirements={"userId" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestRemoveAction(Request $request, $userId )
    {
        return parent::accessRequestRemoveAction($request,$userId);
    }

    /**
     * @Route("/authorized-users/", name="calllog_authorized_users")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/authorized_users.html.twig")
     */
    public function authorizedUsersAction(Request $request )
    {
        return parent::authorizedUsersAction($request);
    }

    /**
     * @Route("/authorization-user-manager/{id}", name="calllog_authorization_user_management", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function authorizationManagementAction( Request $request, $id )
    {
        return parent::authorizationManagementAction($request,$id);
    }

    /**
     * @Route("/authorization-user-manager/submit/{id}", name="calllog_authorization_user_management_submit", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function authorizationManagementSubmitAction( Request $request, $id )
    {
        return parent::authorizationManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/revoke-access-authorization/{userId}", name="calllog_authorization_remove", requirements={"userId" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function authorizationRemoveAction(Request $request, $userId)
    {
        return parent::authorizationRemoveAction($request,$userId);
    }


    /**
     * @Route("/add-authorized-user/", name="calllog_add_authorized_user")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
     */
    public function addAuthorizedUserAction( Request $request )
    {
        return parent::addAuthorizedUserAction($request);
    }

//    /**
//     * @Route("/add-authorized-user/submit/", name="calllog_add_authorized_user_submit")
//     * @Method("POST")
//     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
//     */
//    public function addAuthorizedUserSubmitAction( Request $request )
//    {
//        return parent::addAuthorizedUserSubmitAction($request);
//    }

}
