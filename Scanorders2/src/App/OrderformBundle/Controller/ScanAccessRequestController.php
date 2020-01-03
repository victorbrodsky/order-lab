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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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

    /**
     * @Route("/access-requests/new/create", name="scan_access_request_new_plain")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreatePlainAction(Request $request)
    {

        $userSecUtil = $this->get('user_security_utility');

        $user = $this->get('security.token_storage')->getToken()->getUser();

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
            //return $this->redirect($this->generateUrl($this->container->getParameter('scan.sitename').'_login'));

            //exit('nopermission create scan access request for non ldap user');

            $this->get('session')->getFlashBag()->add(
                'warning',
                "You don't have permission to visit this page on Scan Order site."."<br>".
                "If you already applied for access, then try to " . "<a href=".$this->generateUrl($this->container->getParameter('scan.sitename').'_logout',true).">Re-Login</a>"
            );
            return $this->redirect( $this->generateUrl('main_common_home') );
        }

        $roles = array(
            "unnaproved" => "ROLE_SCANORDER_UNAPPROVED",
            "banned" => "ROLE_SCANORDER_BANNED",
        );

        return $this->accessRequestCreateNew($user->getId(),$this->container->getParameter('scan.sitename'),$roles);
    }

    /**
     * @Route("/access-requests/new", name="scan_access_request_new")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestCreateAction()
    {

        $sitename = $this->container->getParameter('scan.sitename');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $userSecUtil = $this->get('user_security_utility');
        if( false === $userSecUtil->hasGlobalUserRole('ROLE_SCANORDER_UNAPPROVED',$user) ) {
            return $this->redirect($this->generateUrl($sitename.'_login'));
        }

        $roles = array(
            "unnaproved" => "ROLE_SCANORDER_UNAPPROVED",
            "banned" => "ROLE_SCANORDER_BANNED",
        );

        return $this->accessRequestCreateNew($user->getId(),$sitename,$roles);
    }

    /**
     * @Route("/access-requests/new/pending", name="scan_access_request_create")
     * @Method("POST")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request.html.twig")
     */
    public function accessRequestAction(Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id = $user->getId();
        $sitename = $this->container->getParameter('scan.sitename');

        return $this->accessRequestCreate($request,$id,$sitename);
    }


    /**
     * Lists all Access Request.
     *
     * @Route("/access-requests", name="scan_accessrequest_list")
     * @Method("GET")
     * @Template("AppOrderformBundle:AccessRequest:access_request_list.html.twig")
     */
    public function accessRequestIndexAction(Request $request)
    {
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_SCANORDER_PROCESSOR') ) {
            return $this->redirect( $this->generateUrl('scan-nopermission') );
        }

        return $this->accessRequestIndexList($request,$this->container->getParameter('scan.sitename'));
    }


    /**
     * @Route("/access-requests/change-status/{id}/{status}", name="scan_accessrequest_change", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestChangeAction(Request $request, $id, $status)
    {
        return parent::accessRequestChangeAction($request, $id, $status);
    }

    //overwrite parent class methods
    public function addOptionalApproveRoles($entity) {
        $entity->addRole('ROLE_SCANORDER_ORDERING_PROVIDER');

        //add WCMC institional scope to pacsvendor created users
        $creator = $this->get('security.token_storage')->getToken()->getUser();
        $orderSecUtil = $this->container->get('order_security_utility');
        $orderSecUtil->addInstitutionalPhiScopeWCMC($entity,$creator);
    }
    public function removeOptionalDeclineRoles($entity) {
        $entity->removeRole('ROLE_SCANORDER_ORDERING_PROVIDER');
    }


    /**
     * @Route("/access-requests/{id}", name="scan_accessrequest_management", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request_management.html.twig")
     */
    public function accessRequestManagementAction(Request $request, $id )
    {
        return parent::accessRequestManagementAction($request,$id);
    }

    /**
     * @Route("/access-requests/submit/{id}", name="scan_accessrequest_management_submit", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request_management.html.twig")
     */
    public function accessRequestManagementSubmitAction(Request $request, $id )
    {
        return parent::accessRequestManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/deny-access-request/{userId}", name="scan_accessrequest_remove", requirements={"userId" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function accessRequestRemoveAction(Request $request, $userId )
    {
        return parent::accessRequestRemoveAction($request,$userId);
    }

    /**
     * @Route("/authorized-users/", name="scan_authorized_users")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:authorized_users.html.twig")
     */
    public function authorizedUsersAction(Request $request )
    {
        return parent::authorizedUsersAction($request);
    }

    /**
     * @Route("/authorization-user-manager/{id}", name="scan_authorization_user_management", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request_management.html.twig")
     */
    public function authorizationManagementAction( Request $request, $id )
    {
        return parent::authorizationManagementAction($request,$id);
    }

    /**
     * @Route("/authorization-user-manager/submit/{id}", name="scan_authorization_user_management_submit", requirements={"id" = "\d+"})
     * @Method("POST")
     * @Template("AppUserdirectoryBundle:AccessRequest:access_request_management.html.twig")
     */
    public function authorizationManagementSubmitAction( Request $request, $id )
    {
        return parent::authorizationManagementSubmitAction($request,$id);
    }

    /**
     * @Route("/revoke-access-authorization/{userId}", name="scan_authorization_remove", requirements={"userId" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function authorizationRemoveAction(Request $request,$userId)
    {
        return parent::authorizationRemoveAction($request,$userId);
    }

    /**
     * @Route("/add-authorized-user/", name="scan_add_authorized_user")
     * @Method("GET")
     * @Template("AppUserdirectoryBundle:AccessRequest:add_authorized_user.html.twig")
     */
    public function addAuthorizedUserAction( Request $request )
    {
        return parent::addAuthorizedUserAction($request);
    }

//    /**
//     * @Route("/add-authorized-user/submit/", name="scan_add_authorized_user_submit")
//     * @Method("POST")
//     * @Template("AppUserdirectoryBundle:AccessRequest:add_authorized_user.html.twig")
//     */
//    public function addAuthorizedUserSubmitAction( Request $request )
//    {
//        return parent::addAuthorizedUserSubmitAction($request);
//    }

}
