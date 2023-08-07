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

namespace App\VacReqBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;

use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\AccessRequestController;

/**
 * vacreq
 */
class VacReqAccessRequestController extends AccessRequestController
{

    public function __construct() {
        $this->siteName = 'vacreq';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Vacation Request';
        $this->roleBanned = 'ROLE_VACREQ_BANNED';
        $this->roleUser = 'ROLE_VACREQ_USER';
        $this->roleUnapproved = 'ROLE_VACREQ_UNAPPROVED';
        $this->roleEditor = 'ROLE_VACREQ_ADMIN';
    }

    #[Route(path: '/access-requests/new/create', name: 'vacreq_access_request_new_plain', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request.html.twig')]
    public function accessRequestCreatePlainAction(Request $request)
    {
        //exit('accessRequestCreatePlainAction');
        return parent::accessRequestCreatePlain($request);
    }

    #[Route(path: '/access-requests/new', name: 'vacreq_access_request_new', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request.html.twig')]
    public function accessRequestCreateAction(Request $request)
    {
        return parent::accessRequestCreateAction($request);
    }

    #[Route(path: '/access-requests/new/pending', name: 'vacreq_access_request_create', methods: ['POST'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request.html.twig')]
    public function accessRequestAction(Request $request)
    {
        return parent::accessRequestAction($request);
    }

    /**
     * Lists all Access Request.
     */
    #[Route(path: '/access-requests', name: 'vacreq_accessrequest_list', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_list.html.twig')]
    public function accessRequestIndexAction(Request $request)
    {
        return parent::accessRequestIndexAction($request);
    }

    #[Route(path: '/access-requests/change-status/{id}/{status}', name: 'vacreq_accessrequest_change', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function accessRequestChangeAction(Request $request, $id, $status)
    {
        return parent::accessRequestChangeAction($request, $id, $status);
    }

    #[Route(path: '/access-requests/{id}', name: 'vacreq_accessrequest_management', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function accessRequestManagementAction(Request $request, $id )
    {
        return parent::accessRequestManagementAction($request,$id);
    }

    #[Route(path: '/access-requests/submit/{id}', name: 'vacreq_accessrequest_management_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function accessRequestManagementSubmitAction(Request $request, $id )
    {
        return parent::accessRequestManagementSubmitAction($request,$id);
    }

    #[Route(path: '/deny-access-request/{userId}', name: 'vacreq_accessrequest_remove', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function accessRequestRemoveAction(Request $request, $userId )
    {
        return parent::accessRequestRemoveAction($request,$userId);
    }

    #[Route(path: '/authorized-users/', name: 'vacreq_authorized_users', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/authorized_users.html.twig')]
    public function authorizedUsersAction(Request $request )
    {
        return parent::authorizedUsersAction($request);
    }

    #[Route(path: '/authorization-user-manager/{id}', name: 'vacreq_authorization_user_management', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function authorizationManagementAction( Request $request, $id )
    {
        return parent::authorizationManagementAction($request,$id);
    }

    #[Route(path: '/authorization-user-manager/submit/{id}', name: 'vacreq_authorization_user_management_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function authorizationManagementSubmitAction( Request $request, $id )
    {
        return parent::authorizationManagementSubmitAction($request,$id);
    }

    #[Route(path: '/revoke-access-authorization/{userId}', name: 'vacreq_authorization_remove', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function authorizationRemoveAction(Request $request, $userId)
    {
        return parent::authorizationRemoveAction($request,$userId);
    }


    #[Route(path: '/add-authorized-user/', name: 'vacreq_add_authorized_user', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig')]
    public function addAuthorizedUserAction( Request $request )
    {
        return parent::addAuthorizedUserAction($request);
    }

//    /**
//     * @Route("/add-authorized-user/submit/", name="vacreq_add_authorized_user_submit", methods={"POST"})
//     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
//     */
//    public function addAuthorizedUserSubmitAction( Request $request )
//    {
//        return parent::addAuthorizedUserSubmitAction($request);
//    }

    public function getOrganizationalGroup() {
        $vacreqUtil = $this->container->get('vacreq_util');
        $organizationalGroups = $vacreqUtil->getAllGroups();
        return $organizationalGroups;
    }

    public function getGroupNote() {
        $vacreqUtil = $this->container->get('vacreq_util');
        $note = "<b>"."Please choose an appropriate 'Organizational Group' below. Your vacation/business requests will be reviewed by this group's approvers."."</b>";

        $organizationalGroupArr = array();
        $organizationalGroups = $vacreqUtil->getAllGroups(false);
        foreach($organizationalGroups as $instId=>$organizationalGroupName) {
            //echo "instId=$instId, organizationalGroupName=$organizationalGroupName <br>";
            $organizationalGroupArr[] = $organizationalGroupName;
        }

        if( count($organizationalGroupArr) > 0 ) {
            $noteInst = implode("<br>",$organizationalGroupArr);
        } else {
            $noteInst = NULL;
        }

        $note = $note."<br>".$noteInst;

        return $note;
    }

    public function getReasonNote() {
        $note = "Please indicate the reason for access request and indicate if you would like to become the approver.";
        //$note = "<b>".$note."</b>";
        return $note;
    }
}
