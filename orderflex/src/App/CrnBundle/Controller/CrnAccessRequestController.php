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

namespace App\CrnBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use App\UserdirectoryBundle\Controller\AuthorizedUserController;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\AccessRequestController;

/**
 * crn
 */
class CrnAccessRequestController extends AccessRequestController
{

    public function __construct() {
        $this->siteName = 'crn';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Critical Result Notification';
        $this->roleBanned = 'ROLE_CRN_BANNED';
        $this->roleUser = 'ROLE_CRN_USER';
        $this->roleUnapproved = 'ROLE_CRN_UNAPPROVED';
        $this->roleEditor = 'ROLE_CRN_ADMIN';
    }

    /**
     * @Route("/access-requests/new/create", name="crn_access_request_new_plain", methods={"GET"})
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    public function accessRequestCreatePlainAction(Request $request)
    {
        //exit('accessRequestCreatePlainAction');
        return parent::accessRequestCreatePlain($request);
    }

    /**
     * @Route("/access-requests/new", name="crn_access_request_new", methods={"GET"})
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    public function accessRequestCreateAction(Request $request)
    {
        return parent::accessRequestCreateAction($request);
    }

    /**
     * @Route("/access-requests/new/pending", name="crn_access_request_create", methods={"POST"})
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    public function accessRequestAction(Request $request)
    {
        return parent::accessRequestAction($request);
    }

    /**
     * Lists all Access Request.
     *
     * @Route("/access-requests", name="crn_accessrequest_list", methods={"GET"})
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_list.html.twig")
     */
    public function accessRequestIndexAction(Request $request)
    {
        return parent::accessRequestIndexAction($request);
    }

    /**
     * @Route("/access-requests/change-status/{id}/{status}", name="crn_accessrequest_change", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template()
     */
    public function accessRequestChangeAction(Request $request, $id, $status)
    {
        return parent::accessRequestChangeAction($request, $id, $status);
    }

    /**
     * @Route("/access-requests/{id}", name="crn_accessrequest_management", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function accessRequestManagementAction(Request $request, $id )
    {
        return parent::accessRequestManagementAction($request,$id);
    }

    /**
     * @Route("/access-requests/submit/{id}", name="crn_accessrequest_management_submit", methods={"POST"}, requirements={"id" = "\d+"})
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function accessRequestManagementSubmitAction(Request $request, $id )
    {
        return parent::accessRequestManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/deny-access-request/{userId}", name="crn_accessrequest_remove", methods={"GET"}, requirements={"userId" = "\d+"})
     * @Template()
     */
    public function accessRequestRemoveAction(Request $request, $userId )
    {
        return parent::accessRequestRemoveAction($request,$userId);
    }

    /**
     * @Route("/authorized-users/", name="crn_authorized_users", methods={"GET"})
     * @Template("AppUserdirectoryBundle/AccessRequest/authorized_users.html.twig")
     */
    public function authorizedUsersAction(Request $request )
    {
        return parent::authorizedUsersAction($request);
    }

    /**
     * @Route("/authorization-user-manager/{id}", name="crn_authorization_user_management", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function authorizationManagementAction( Request $request, $id )
    {
        return parent::authorizationManagementAction($request,$id);
    }

    /**
     * @Route("/authorization-user-manager/submit/{id}", name="crn_authorization_user_management_submit", methods={"POST"}, requirements={"id" = "\d+"})
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    public function authorizationManagementSubmitAction( Request $request, $id )
    {
        return parent::authorizationManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/revoke-access-authorization/{userId}", name="crn_authorization_remove", methods={"GET"}, requirements={"userId" = "\d+"})
     * @Template()
     */
    public function authorizationRemoveAction(Request $request, $userId)
    {
        return parent::authorizationRemoveAction($request,$userId);
    }


    /**
     * @Route("/add-authorized-user/", name="crn_add_authorized_user", methods={"GET"})
     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
     */
    public function addAuthorizedUserAction( Request $request )
    {
        return parent::addAuthorizedUserAction($request);
    }

//    /**
//     * @Route("/add-authorized-user/submit/", name="crn_add_authorized_user_submit", methods={"POST"})
//     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
//     */
//    public function addAuthorizedUserSubmitAction( Request $request )
//    {
//        return parent::addAuthorizedUserSubmitAction($request);
//    }

}
