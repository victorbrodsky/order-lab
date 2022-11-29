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

use App\UserdirectoryBundle\Form\UserDatesFilterType;
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
     * @Route("/employment-dates/edit", name="employees_user_dates_edit")
     * @Template("AppUserdirectoryBundle/UserDates/user_dates.html.twig")
     */
    public function userDatesAction( Request $request ) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

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

        $params = array();
        $filterform = $this->createForm(UserDatesFilterType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));

        $filterform->handleRequest($request);

        if( $filterform->isSubmitted() && $filterform->isValid() ) {
            $users = $filterform["users"]->getData();
            $useridsArr = array();
            foreach( $users as $thisUser ) {
                $useridsArr[] = $thisUser->getId();
            }
            $userids = implode("-",$useridsArr);
        }

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
            'entities' => $users,
            'filterform' => $filterform->createView()
        );
    }

    /**
     * @Route("/users/api", name="employees_users_api", options={"expose"=true})
     */
    public function getUsersApiAction( Request $request ) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

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

        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");
        $dql->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL");

        $params = array();
        $filterform = $this->createForm(UserDatesFilterType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));

        $filterform->handleRequest($request);

        $queryParameters = array();
        if( $filterform->isSubmitted() && $filterform->isValid() ) {
            //echo "filterform=OK <br>";
            //dump($filterform);

            $users = $filterform["users"]->getData();
            if( $users && count($users) > 0 ) {
                //echo "users=OK <br>";
                $useridsArr = array();
                foreach ($users as $thisUser) {
                    $useridsArr[] = $thisUser->getId();
                }
                //$userids = implode(",",$useridsArr);
                $dql->andWhere('user.id IN (:userids)');
                $queryParameters['userids'] = $useridsArr;
            }

            $search = $filterform["search"]->getData();
            //echo "search=$search <br>";
            if( $search ) {
                $dql->andWhere('infos.displayName LIKE :search OR infos.email LIKE :search');
                //$queryParameters['search'] = $useridsArr;
                $queryParameters['search'] = "%" . $search . "%";
            }

            $roles = $filterform["roles"]->getData();
            if( $roles && count($roles) > 0 ) {
//                $rolesArr = array();
//                foreach ($roles as $role) {
//                    $rolesArr[] = "'".$role->getName()."'";
//                }
//                $rolesArr = implode(",",$rolesArr);
//                $dql->andWhere('user.roles IN (:roles)');
//                $queryParameters['roles'] = $rolesArr;

                $rolesArr = array();
                foreach ($roles as $role) {
                    $rolesArr[] = "user.roles LIKE " . "'%" . $role->getName() . "%'";
                }
                $rolesStr = implode(" OR ", $rolesArr);
                $dql->andWhere($rolesStr);
            }

            $startdate = $filterform["startdate"]->getData();
            if( $startdate ) {
                //$dql->andWhere('employmentStatus.hireDate ');
                //$dql->andWhere("(employmentStatus.hireDate > :startdate AND :createDateEnd OR request.firstDayBackInOffice between :createDateStart AND :createDateEnd)");
                $dql->andWhere("(employmentStatus.hireDate > :startdate)");
                $startdate = $startdate->format('Y-m-d H:i:s');
                $queryParameters['startdate'] = $startdate;
            }

            $enddate = $filterform["enddate"]->getData();
            if( $enddate ) {
                $dql->andWhere("(employmentStatus.terminationDate < :enddate)");
                $enddate = $enddate->format('Y-m-d H:i:s');
                $queryParameters['enddate'] = $enddate;
            }

//            if( $startdate && $enddate ) {
//
//            }

            $status = $filterform["status"]->getData();
            if( $status ) {
                if( $status == 'locked' ) {
                    //$enabled = false;
                    $dql->andWhere("user.enabled = false");
                }
            }

            //exit('111');
        }
        //exit('111');

        $dql->orderBy("infos.lastName","ASC");
        $query = $em->createQuery($dql);

        $query->setParameters($queryParameters);

        //$query->setMaxResults(30);
        //$totalUsers = $query->getResult();

        $limit = 20; //20;

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

            $showLink = $this->container->get('router')->generate(
                'employees_showuser',
                array(
                    'id' => $user->getId()
                ),
                //UrlGeneratorInterface::ABSOLUTE_PATH
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            //$showLink = ' <a href="' . $showLink . '">'.$user->getId().'</a>';

            $editLink = $this->container->get('router')->generate(
                'employees_user_edit',
                array(
                    'id' => $user->getId()
                ),
                //UrlGeneratorInterface::ABSOLUTE_PATH
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            //<a href="{{ path(sitename~'_logger_user_all',{'id':user.id}) }}" target="_blank">Event Log</a>

            $eventlogLink = $this->container->get('router')->generate(
                'employees_logger_user_all',
                array(
                    'id' => $user->getId()
                ),
                //UrlGeneratorInterface::ABSOLUTE_PATH
                UrlGeneratorInterface::ABSOLUTE_URL
            );

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

            //$degree = $degreeTitle['degree'];
            $titles = $degreeTitle['title'];

            $degree = $user->getSingleSalutation();

            //if( !$degree ) {
                //$degree = "N/A        ";
            //}

            $startEndDate = $user->getEmploymentStartEndDates();
            $startDate = $startEndDate['startDate'];
            $endDate = $startEndDate['endDate'];

            $terminationStr = $user->getEmploymentTerminatedStr();
            if( $terminationStr ) {
                $status = $terminationStr; //"terminated";
            }

            $jsonArray[] = array(
                'id'            => $user->getId(),
                'showLink'      => $showLink,
                'editLink'      => $editLink,
                'eventlogLink'  => $eventlogLink,
                'LastName'      => $user->getSingleLastName(),
                'FirstName'     => $user->getSingleFirstName(),
                'Degree'        => $degree,
                'Email'         => $user->getSingleEmail(),
                'Institution'   => $instNameStr,
                'Title'         => $titles,
                'StartDate'     => $startDate,
                'EndDate'       => $endDate,
                'status'        => $status

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

        $totalCount = $users->getTotalItemCount();
        //echo "totalCount=$totalCount <br>";
        $totalPages = ceil($totalCount/$limit);
        //echo "totalPages=$totalPages <br>";
        //exit('111');

        $info = array(
            'seed' => "abc",
            'results' => $limit,
            'page' => $page,
            'version' => 1
        );

        $results = array(
            'results' => $jsonArray,
            'info'    => $info,
            'totalPages'   => $totalPages,
            'totalUsers' => $totalCount
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
     * @Route("/update-users-dates/", name="employees_update_users_date", options={"expose"=true})
     */
    public function updateUsersDateAction( Request $request ) {
        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        exit('Not implemented');
    }

}
