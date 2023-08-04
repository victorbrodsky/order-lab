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

namespace App\DeidentifierBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\AccessRequestController;

/**
 * AccessRequest controller.
 */
class DeidentifierAccessRequestController extends AccessRequestController
{

    public function __construct() {
        $this->siteName = 'deidentifier';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Deidentifier';
        $this->roleBanned = 'ROLE_DEIDENTIFICATOR_BANNED';
        $this->roleUser = 'ROLE_DEIDENTIFICATOR_USER';
        $this->roleUnapproved = 'ROLE_DEIDENTIFICATOR_UNAPPROVED';
        $this->roleEditor = 'ROLE_DEIDENTIFICATOR_ADMIN';
    }

    /**
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    #[Route(path: '/access-requests/new/create', name: 'deidentifier_access_request_new_plain', methods: ['GET'])]
    public function accessRequestCreatePlainAction(Request $request)
    {
        //exit('accessRequestCreatePlainAction');
        return parent::accessRequestCreatePlain($request);
    }

    /**
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    #[Route(path: '/access-requests/new', name: 'deidentifier_access_request_new', methods: ['GET'])]
    public function accessRequestCreateAction(Request $request)
    {
        return parent::accessRequestCreateAction($request);
    }

    /**
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request.html.twig")
     */
    #[Route(path: '/access-requests/new/pending', name: 'deidentifier_access_request_create', methods: ['POST'])]
    public function accessRequestAction(Request $request)
    {
        return parent::accessRequestAction($request);
    }

    /**
     * Lists all Access Request.
     *
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_list.html.twig")
     */
    #[Route(path: '/access-requests', name: 'deidentifier_accessrequest_list', methods: ['GET'])]
    public function accessRequestIndexAction(Request $request)
    {
        return parent::accessRequestIndexAction($request);
    }

    /**
     * @Template()
     */
    #[Route(path: '/access-requests/change-status/{id}/{status}', name: 'deidentifier_accessrequest_change', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function accessRequestChangeAction(Request $request, $id, $status)
    {
        return parent::accessRequestChangeAction($request, $id, $status);
    }

    /**
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    #[Route(path: '/access-requests/{id}', name: 'deidentifier_accessrequest_management', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function accessRequestManagementAction(Request $request, $id )
    {
        return parent::accessRequestManagementAction($request,$id);
    }

    /**
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    #[Route(path: '/access-requests/submit/{id}', name: 'deidentifier_accessrequest_management_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function accessRequestManagementSubmitAction(Request $request, $id )
    {
        return parent::accessRequestManagementSubmitAction($request,$id);
    }

    /**
     * @Template()
     */
    #[Route(path: '/deny-access-request/{userId}', name: 'deidentifier_accessrequest_remove', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function accessRequestRemoveAction(Request $request, $userId )
    {
        return parent::accessRequestRemoveAction($request,$userId);
    }

    /**
     * @Template("AppUserdirectoryBundle/AccessRequest/authorized_users.html.twig")
     */
    #[Route(path: '/authorized-users/', name: 'deidentifier_authorized_users', methods: ['GET'])]
    public function authorizedUsersAction(Request $request )
    {
        return parent::authorizedUsersAction($request);
    }

    /**
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    #[Route(path: '/authorization-user-manager/{id}', name: 'deidentifier_authorization_user_management', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function authorizationManagementAction( Request $request, $id )
    {
        return parent::authorizationManagementAction($request,$id);
    }

    /**
     * @Template("AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig")
     */
    #[Route(path: '/authorization-user-manager/submit/{id}', name: 'deidentifier_authorization_user_management_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function authorizationManagementSubmitAction( Request $request, $id )
    {
        return parent::authorizationManagementSubmitAction($request,$id);
    }

    /**
     * @Template()
     */
    #[Route(path: '/revoke-access-authorization/{userId}', name: 'deidentifier_authorization_remove', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function authorizationRemoveAction(Request $request, $userId)
    {
        return parent::authorizationRemoveAction($request,$userId);
    }


    /**
     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
     */
    #[Route(path: '/add-authorized-user/', name: 'deidentifier_add_authorized_user', methods: ['GET'])]
    public function addAuthorizedUserAction( Request $request )
    {
        return parent::addAuthorizedUserAction($request);
    }

//    /**
//     * @Route("/add-authorized-user/submit/", name="deidentifier_add_authorized_user_submit", methods={"POST"})
//     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
//     */
//    public function addAuthorizedUserSubmitAction( Request $request )
//    {
//        return parent::addAuthorizedUserSubmitAction($request);
//    }

}
