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

namespace App\OrderformBundle\Controller;

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
 * AccessRequest controller.
 */
class ScanAccessRequestController extends AccessRequestController
{

    public function __construct() {
        $this->siteName = 'scan';
        $this->siteNameShowuser = 'scan';
        $this->siteNameStr = 'Scan Order';
        $this->roleBanned = 'ROLE_SCANORDER_BANNED';
        $this->roleUser = 'ROLE_SCANORDER_SUBMITTER';
        $this->roleUnapproved = 'ROLE_SCANORDER_UNAPPROVED';
        $this->roleEditor = 'ROLE_SCANORDER_PROCESSOR';
    }

    #[Route(path: '/access-requests/new/create', name: 'scan_access_request_new_plain', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request.html.twig')]
    public function accessRequestCreatePlainAction(Request $request)
    {

        $userSecUtil = $this->container->get('user_security_utility');

        $user = $this->getUser();

        //the user might be authenticated by another site. If the user does not have lowest role => assign unapproved role to trigger access request
        if( false === $userSecUtil->hasGlobalUserRole('ROLE_SCANORDER_SUBMITTER',$user) ) {
            //exit('adding unapproved');
            $user->addRole('ROLE_SCANORDER_UNAPPROVED');
        }

//        if( true === $userSecUtil->hasGlobalUserRole('ROLE_SCANORDER_SUBMITTER',$user) ) {
//            return $this->redirect($this->generateUrl('scan-nopermission'));
//        }

        if( false === $userSecUtil->hasGlobalUserRole('ROLE_SCANORDER_UNAPPROVED',$user) ) {

            //relogin the user, because when admin approves accreq, the user must relogin to update the role in security context
            //return $this->redirect($this->generateUrl($this->getParameter('scan.sitename').'_login'));

            //exit('nopermission create scan access request for non ldap user');

            $this->addFlash(
                'warning',
                "You don't have permission to visit this page on Scan Order site."."<br>".
                "If you already applied for access, then try to " . "<a href=".$this->generateUrl($this->getParameter('scan.sitename').'_logout',true).">Re-Login</a>"
            );
            return $this->redirect( $this->generateUrl('main_common_home') );
        }

        $roles = array(
            "unnaproved" => "ROLE_SCANORDER_UNAPPROVED",
            "banned" => "ROLE_SCANORDER_BANNED",
        );

        return $this->accessRequestCreateNew($request,$user->getId(),$this->getParameter('scan.sitename'),$roles);
    }

    #[Route(path: '/access-requests/new', name: 'scan_access_request_new', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request.html.twig')]
    public function accessRequestCreateAction(Request $request)
    {

        $sitename = $this->getParameter('scan.sitename');

        $user = $this->getUser();

        $userSecUtil = $this->container->get('user_security_utility');
        if( false === $userSecUtil->hasGlobalUserRole('ROLE_SCANORDER_UNAPPROVED',$user) ) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
        }

        $roles = array(
            "unnaproved" => "ROLE_SCANORDER_UNAPPROVED",
            "banned" => "ROLE_SCANORDER_BANNED",
        );

        return $this->accessRequestCreateNew($request,$user->getId(),$sitename,$roles);
    }

    #[Route(path: '/access-requests/new/pending', name: 'scan_access_request_create', methods: ['POST'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request.html.twig')]
    public function accessRequestAction(Request $request)
    {

        $user = $this->getUser();
        $id = $user->getId();
        $sitename = $this->getParameter('scan.sitename');

        return $this->accessRequestCreate($request,$id,$sitename);
    }


    /**
     * Lists all Access Request.
     */
    #[Route(path: '/access-requests', name: 'scan_accessrequest_list', methods: ['GET'])]
    #[Template('AppOrderformBundle/AccessRequest/access_request_list.html.twig')]
    public function accessRequestIndexAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        return $this->accessRequestIndexList($request,$this->getParameter('scan.sitename'));
    }


    #[Route(path: '/access-requests/change-status/{id}/{status}', name: 'scan_accessrequest_change', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function accessRequestChangeAction(Request $request, $id, $status)
    {
        return parent::accessRequestChangeAction($request, $id, $status);
    }

    //overwrite parent class methods
    public function addOptionalApproveRoles($entity) {
        $entity->addRole('ROLE_SCANORDER_ORDERING_PROVIDER');

        //add WCMC institional scope to pacsvendor created users
        $creator = $this->getUser();
        $orderSecUtil = $this->container->get('user_security_utility');
        $orderSecUtil->addInstitutionalPhiScopeWCMC($entity,$creator);
    }
    public function removeOptionalDeclineRoles($entity) {
        $entity->removeRole('ROLE_SCANORDER_ORDERING_PROVIDER');
    }


    #[Route(path: '/access-requests/{id}', name: 'scan_accessrequest_management', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function accessRequestManagementAction(Request $request, $id )
    {
        return parent::accessRequestManagementAction($request,$id);
    }

    #[Route(path: '/access-requests/submit/{id}', name: 'scan_accessrequest_management_submit', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function accessRequestManagementSubmitAction(Request $request, $id )
    {
        return parent::accessRequestManagementSubmitAction($request,$id);
    }

    #[Route(path: '/deny-access-request/{userId}', name: 'scan_accessrequest_remove', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function accessRequestRemoveAction(Request $request, $userId )
    {
        return parent::accessRequestRemoveAction($request,$userId);
    }

    #[Route(path: '/authorized-users/', name: 'scan_authorized_users', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/authorized_users.html.twig')]
    public function authorizedUsersAction(Request $request )
    {
        return parent::authorizedUsersAction($request);
    }

    #[Route(path: '/authorization-user-manager/{id}', name: 'scan_authorization_user_management', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function authorizationManagementAction( Request $request, $id )
    {
        return parent::authorizationManagementAction($request,$id);
    }

    #[Route(path: '/authorization-user-manager/submit/{id}', name: 'scan_authorization_user_management_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/access_request_management.html.twig')]
    public function authorizationManagementSubmitAction( Request $request, $id )
    {
        return parent::authorizationManagementSubmitAction($request,$id);
    }

    #[Route(path: '/revoke-access-authorization/{userId}', name: 'scan_authorization_remove', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function authorizationRemoveAction(Request $request,$userId)
    {
        return parent::authorizationRemoveAction($request,$userId);
    }

    #[Route(path: '/add-authorized-user/', name: 'scan_add_authorized_user', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig')]
    public function addAuthorizedUserAction( Request $request )
    {
        return parent::addAuthorizedUserAction($request);
    }

//    /**
//     * @Route("/add-authorized-user/submit/", name="scan_add_authorized_user_submit", methods={"POST"})
//     * @Template("AppUserdirectoryBundle/AccessRequest/add_authorized_user.html.twig")
//     */
//    public function addAuthorizedUserSubmitAction( Request $request )
//    {
//        return parent::addAuthorizedUserSubmitAction($request);
//    }

}
