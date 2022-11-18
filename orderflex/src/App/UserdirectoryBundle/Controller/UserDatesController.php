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
 *
 *  Created by Oleg Ivanov
 */

namespace App\UserdirectoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class UserDatesController extends OrderAbstractController
{

    /**
     * @Route("/employment-dates", name="employees_user_dates")
     * @Template("AppUserdirectoryBundle/UserDates/user_dates.html.twig")
     */
    public function aboutAction( Request $request ) {

        // [Checkmark column with title of “Deactivate”] |
        // LastName |
        // FirstName |
        // Degree(s) |
        // Email |
        // Organizational Group(s) |
        // Title(s) |
        // Latest Employment Start Date |
        // Latest Employment End Date |
        // Account Status |
        // Action

        return array(
            'title' => 'Employment dates'
        );
    }

    /**
     * @Route("/users/api", name="employees_users_api", options={"expose"=true})
     */
    public function getUsersApiAction( Request $request ) {

        // [Checkmark column with title of “Deactivate”] |
        // LastName |
        // FirstName |
        // Degree(s) |
        // Email |
        // Organizational Group(s) |
        // Title(s) |
        // Latest Employment Start Date |
        // Latest Employment End Date |
        // Account Status |
        // Action

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('AppUserdirectoryBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin("user.infos","infos");
        $dql->orderBy("infos.lastName","ASC");
        $query = $em->createQuery($dql);

        //$users = $query->getResult();

        $limit = 3;

        $paginationParams = array(
            'defaultSortFieldName' => 'user.id',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

        $paginator  = $this->container->get('knp_paginator');
        $users = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                            /*limit per page*/
            $paginationParams
        );


        $jsonArray = array();
        foreach($users as $user) {
            $jsonArray['LastName'] = $user->getLastName();
            $jsonArray['FirstName'] = $user->getFirstName();
            //$jsonArray['Degree'] = $user->getFirstName();
            $jsonArray['Email'] = $user->getSingleEmail();
            //$jsonArray['Institution'] = $user->getFirstName();
            //$jsonArray['Title'] = $user->getFirstName();
            //$jsonArray['StartDate'] = $user->getFirstName();
            //$jsonArray['EndDate'] = $user->getFirstName();
            //$jsonArray['Status'] = $user->getFirstName();
        }

//        return new JsonResponse([
//            [
//                'title' => 'The Princess Bride',
//                'count' => 0
//            ]
//            $jsonArray
//        ]);

        $response = new Response();

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        $response->setContent(json_encode($jsonArray));

        return $response;
    }

}
