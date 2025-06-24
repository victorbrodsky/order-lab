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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/9/2017
 * Time: 10:10 AM
 */

namespace App\ResAppBundle\Controller;




use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\UserRequestController;


class ResAppUserRequestController extends UserRequestController
{

    public function __construct() {
        $this->siteName = 'resapp';
        $this->siteNameShowuser = 'resapp';
        $this->siteNameStr = 'Residency Applications';
        $this->roleEditor = 'ROLE_RESAPP_COORDINATOR';
    }


    /**
     * Displays a form to create a new UserRequest entity.
     */
    #[Route(path: '/account-requests/new', name: 'resapp_accountrequest_new', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/UserRequest/account_request.html.twig')]
    public function newAction( Request $request )
    {
        return parent::newAction();
    }

    /**
     * Creates a new UserRequest entity.
     */
    #[Route(path: '/account-requests/new', name: 'resapp_accountrequest_create', methods: ['POST'])]
    #[Template('AppUserdirectoryBundle/UserRequest/account_request.html.twig')]
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }


    /**
     * Lists all UserRequest entities.
     */
    #[Route(path: '/account-requests', name: 'resapp_accountrequest', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/UserRequest/index.html.twig')]
    public function indexAction( Request $request )
    {
        return parent::indexAction($request);
    }


    #[Route(path: '/account-requests/{id}/{status}/status', name: 'resapp_accountrequest_status', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('AppUserdirectoryBundle/UserRequest/index.html.twig')]
    public function statusAction($id, $status)
    {
        return parent::statusAction($id,$status);
    }

    /**
     * Update (Approve) a new UserRequest entity.
     */
    #[Route(path: '/account-requests-approve', name: 'resapp_accountrequest_approve', methods: ['POST'])]
    #[Template('AppUserdirectoryBundle/UserRequest/index.html.twig')]
    public function approveUserAccountRequestAction(Request $request)
    {
        return parent::approveUserAccountRequestAction($request);
    }

}