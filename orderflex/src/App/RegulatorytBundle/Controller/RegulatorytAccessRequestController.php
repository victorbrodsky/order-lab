<?php
/**
 * Copyright (c) 2017 Cornell University
 */

namespace App\RegulatorytBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\AccessRequestController;

class RegulatorytAccessRequestController extends AccessRequestController
{
    public function __construct() {
        $this->siteName = 'regulatoryt';
        $this->siteNameShowuser = 'employees';
        $this->siteNameStr = 'Regulatory Templates';
        $this->roleBanned = 'ROLE_REGULATORYT_BANNED';
        $this->roleUser = 'ROLE_REGULATORYT_USER';
        $this->roleUnapproved = 'ROLE_REGULATORYT_UNAPPROVED';
        $this->roleEditor = 'ROLE_REGULATORYT_ADMIN';
    }

    #[Route(path: '/access-requests/new/create', name: 'regulatoryt_access_request_new_plain', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request.html.twig')]
    public function accessRequestCreatePlainAction(Request $request)
    {
        return parent::accessRequestCreatePlain($request);
    }

    #[Route(path: '/access-requests/new', name: 'regulatoryt_access_request_new', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request.html.twig')]
    public function accessRequestCreateAction(Request $request)
    {
        return parent::accessRequestCreateAction($request);
    }

    #[Route(path: '/access-requests/new/pending', name: 'regulatoryt_access_request_create', methods: ['POST'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request.html.twig')]
    public function accessRequestAction(Request $request)
    {
        return parent::accessRequestAction($request);
    }

    #[Route(path: '/access-requests', name: 'regulatoryt_accessrequest_list', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_list.html.twig')]
    public function accessRequestIndexAction(Request $request)
    {
        return parent::accessRequestIndexAction($request);
    }

    #[Route(path: '/access-requests/change-status/{id}/{status}', name: 'regulatoryt_accessrequest_change', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function accessRequestChangeAction(Request $request, $id, $status)
    {
        return parent::accessRequestChangeAction($request, $id, $status);
    }

    #[Route(path: '/access-requests/{id}', name: 'regulatoryt_accessrequest_management', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function accessRequestManagementAction(Request $request, $id )
    {
        return parent::accessRequestManagementAction($request,$id);
    }

    #[Route(path: '/access-requests/submit/{id}', name: 'regulatoryt_accessrequest_management_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function accessRequestManagementSubmitAction(Request $request, $id )
    {
        return parent::accessRequestManagementSubmitAction($request,$id);
    }

    #[Route(path: '/deny-access-request/{userId}', name: 'regulatoryt_accessrequest_remove', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function accessRequestRemoveAction(Request $request, $userId )
    {
        return parent::accessRequestRemoveAction($request,$userId);
    }

    #[Route(path: '/authorized-users/', name: 'regulatoryt_authorized_users', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/authorized_users.html.twig')]
    public function authorizedUsersAction(Request $request )
    {
        return parent::authorizedUsersAction($request);
    }

    #[Route(path: '/authorization-user-manager/{id}', name: 'regulatoryt_authorization_user_management', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function authorizationManagementAction( Request $request, $id )
    {
        return parent::authorizationManagementAction($request,$id);
    }

    #[Route(path: '/authorization-user-manager/submit/{id}', name: 'regulatoryt_authorization_user_management_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function authorizationManagementSubmitAction( Request $request, $id )
    {
        return parent::authorizationManagementSubmitAction($request,$id);
    }

    #[Route(path: '/revoke-access-authorization/{userId}', name: 'regulatoryt_authorization_remove', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function authorizationRemoveAction(Request $request, $userId)
    {
        return parent::authorizationRemoveAction($request,$userId);
    }

    #[Route(path: '/add-authorized-user/', name: 'regulatoryt_add_authorized_user', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig')]
    public function addAuthorizedUserAction( Request $request )
    {
        return parent::addAuthorizedUserAction($request);
    }
}
