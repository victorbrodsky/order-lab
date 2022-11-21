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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

        $users = array();

//        $em = $this->getDoctrine()->getManager();
//
//        $repository = $em->getRepository('AppUserdirectoryBundle:User');
//        $dql =  $repository->createQueryBuilder("user");
//        $dql->select('user');
//        $dql->leftJoin("user.infos","infos");
//        $dql->orderBy("infos.lastName","ASC");
//        $query = $em->createQuery($dql);
//
//        //$users = $query->getResult();
//
//        $limit = 1;
//
//        $paginationParams = array(
//            'defaultSortFieldName' => 'user.id',
//            'defaultSortDirection' => 'DESC',
//            'wrap-queries' => true
//        );
//
//        $page = $request->query->get('page', 1);
//        $paginator  = $this->container->get('knp_paginator');
//        $users = $paginator->paginate(
//            $query,
//            $page,   /*page number*/
//            $limit,                            /*limit per page*/
//            $paginationParams
//        );

        return array(
            'title' => 'Employment dates',
            'entities' => $users
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

        $limit = 20;

        $paginationParams = array(
            'defaultSortFieldName' => 'user.id',
            'defaultSortDirection' => 'DESC',
            'wrap-queries' => true
        );

        $page = $request->query->get('page', 1);
        $paginator  = $this->container->get('knp_paginator');
        $users = $paginator->paginate(
            $query,
            $page,   /*page number*/
            $limit,                            /*limit per page*/
            $paginationParams
        );
        //echo "page=".$page.", users=".count($users)."<br>";
        //exit('111');

        $jsonArray = array();
        foreach($users as $user) {
            //$jsonArray['LastName'] = $user->getLastName();
            //$jsonArray['FirstName'] = $user->getFirstName();
            //$jsonArray['Degree'] = $user->getFirstName();
            //$jsonArray['Email'] = $user->getSingleEmail();
            //$jsonArray['Institution'] = $user->getFirstName();
            //$jsonArray['Title'] = $user->getFirstName();
            //$jsonArray['StartDate'] = $user->getFirstName();
            //$jsonArray['EndDate'] = $user->getFirstName();
            //$jsonArray['Status'] = $user->getFirstName();

            $link = $this->container->get('router')->generate(
                'employees_showuser',
                array(
                    'id' => $user->getId()
                ),
                //UrlGeneratorInterface::ABSOLUTE_PATH
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //$link = ' <a href="' . $link . '">'.$user->getId().'</a>';

//            $degree = $user->getSingleDegree();
//            if( !$degree ) {
//                $degree = ""; //"N/A        ";
//            }

            //$instNameStr = "";
            $institutions = $user->getDeduplicatedInstitutions();
            $instNames = array();
            foreach( $institutions as $instRes ) {
                foreach( $instRes as $instId => $instArr ) {
                    $instNames[] = $instArr['instName'];
                }
            }
            $instNameStr = implode("; ", $instNames);

            $degreeTitle = $user->getDegreesTitles();

            $degree = $degreeTitle['degree'];
            $titles = $degreeTitle['title'];

            //if( !$degree ) {
                //$degree = "N/A        ";
            //}

            $startEndDate = $user->getEmploymentStartEndDates();
            $startDate = $startEndDate['startDate'];
            $endDate = $startEndDate['endDate'];

            $jsonArray[] = array(
                'id'            => $user->getId(),
                'showlink'      => $link,
                'LastName'      => $user->getSingleLastName(),
                'FirstName'     => $user->getSingleFirstName(),
                'Degree'        => $degree,
                'Email'         => $user->getSingleEmail(),
                'Institution'   => $instNameStr,
                'Title'         => $titles,
                'StartDate'     => $startDate,
                'EndDate'       => $endDate,

            );
            //$jsonArray['FirstName'] = $user->getFirstName();
            //$jsonArray['Degree'] = $user->getFirstName();
            //$jsonArray['Email'] = $user->getSingleEmail();
        }

//        return new JsonResponse([
//            [
//                'title' => 'The Princess Bride',
//                'count' => 0
//            ]
//            $jsonArray
//        ]);

        $info = array(
            'seed' => "abc",
            'results' => $limit,
            'page' => $page,
            'version' => 1
        );

        $results = array(
            'results' => $jsonArray,
            'info'    => $info
        );

        //return new JsonResponse($results);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent(json_encode($results));

        return $response;
    }

    /**
     * @Route("/update-users-dates/", name="employees_update_users_date")
     */
    public function updateUsersDateAction( Request $request ) {

    }

}
