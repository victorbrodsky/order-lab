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



use App\UserdirectoryBundle\Entity\EmploymentType; //process.py script: replaced namespace by ::class: added use line for classname=EmploymentType
use App\UserdirectoryBundle\Entity\EmploymentStatus;
use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\UserDatesFilterType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserDatesController extends OrderAbstractController
{

    #[Route(path: '/employment-dates/view', name: 'employees_user_dates_show', options: ['expose' => true])]
    #[Route(path: '/employment-dates/edit', name: 'employees_user_dates_edit', options: ['expose' => true])]
    #[Template('AppUserdirectoryBundle/UserDates/user_dates.html.twig')]
    public function userDatesAction( Request $request ) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //check if lastAdCheck is more than 1 day => check activeAD
        $authUtil = $this->container->get('authenticator_utility'); //AuthUtil
        $authUtil->checkUsersAD();

        $cycle = 'show';
        if( $request->get('_route') == 'employees_user_dates_edit' ) {
            $cycle = 'edit';
        }

        $params = array();
        $filterform = $this->createForm(UserDatesFilterType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));

        $filterform->handleRequest($request);

        if( $filterform->isSubmitted() && $filterform->isValid() ) {
            $users = $filterform["users"]->getData();
            //$useridsArr = array();
            //foreach( $users as $thisUser ) {
            //    $useridsArr[] = $thisUser->getId();
            //}
            //$userids = implode("-",$useridsArr);
        }

        $users = array();

        return array(
            'title' => 'Employment dates',
            'entities' => $users,
            'filterform' => $filterform->createView(),
            'cycle' => $cycle
        );
    }

    #[Route(path: '/users/api', name: 'employees_users_api', options: ['expose' => true])]
    public function getUsersApiAction( Request $request ) {
        //exit("getUsersApiAction exit");
        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $authUtil = $this->container->get('authenticator_utility');
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select('user');
        $dql->leftJoin("user.infos","infos");

        $dql->leftJoin("user.trainings", "trainings");
        $dql->leftJoin("trainings.degree", "trainingsdegree");

        $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
        $dql->leftJoin("administrativeTitles.institution", "institution");

        $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
        $dql->leftJoin("appointmentTitles.institution", "appointmentInstitution");

        $dql->leftJoin("user.medicalTitles", "medicalTitles");
        $dql->leftJoin("medicalTitles.institution", "medicalInstitution");

        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");

        //$dql->where("(employmentType.name != 'Pathology Fellowship Applicant' AND employmentType.name != 'Pathology Residency Applicant') OR employmentType.id IS NULL");
        //$dql->andWhere("(user.createdby != 'resapp_migration' OR user.createdby != 'csv-eras')");

        $whereStr = "(employmentType.name != 'Pathology Fellowship Applicant')";
        //$whereStr = "(employmentType.name NOT LIKE 'Pathology % Applicant')";
        $whereStr = $whereStr . " OR  employmentType.id IS NULL";
        $dql->where($whereStr);

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
                $searchStr = "user.primaryPublicUserId LIKE :search";
                //$searchStr = $searchStr . " OR " . "user.id LIKE :search";
                $searchStr = $searchStr . " OR " . "LOWER(infos.email) LIKE :search";
                $searchStr = $searchStr . " OR " . "LOWER(infos.lastName) LIKE :search";
                $searchStr = $searchStr . " OR " . "LOWER(infos.firstName) LIKE :search";
                $searchStr = $searchStr . " OR " . "LOWER(infos.displayName) LIKE :search";
                $searchStr = $searchStr . " OR " . "infos.preferredPhone LIKE :search";
                $searchStr = $searchStr . " OR " . "LOWER(user.usernameCanonical) LIKE :search";

                $dql->andWhere($searchStr);
                //$queryParameters['search'] = $useridsArr;
                $queryParameters['search'] = "%" . strtolower($search) . "%";
            }

            $roles = $filterform["roles"]->getData();
            if( $roles && count($roles) > 0 ) {
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
//                'Inactive institutional AD account while site access is not locked' => 'adinactive-not-locked',
//                'Locked (no site access) while institutional AD account is active' => 'adactive-locked',
//                'Active institutional AD account' => 'adactive',
//                'Inactive institutional AD account' => 'adinactive',
//                'Locked (no site access)' => 'locked',
//                'Active Account' => 'active',
//                'Ended employment' => 'terminated',
                if( $status == 'adinactive-not-locked' ) {
                    $dql->andWhere("(user.activeAD = false AND user.enabled = true)");
                }
                if( $status == 'adactive-locked' ) {
                    $dql->andWhere("(user.activeAD = true AND user.enabled = false)");
                }
                if( $status == 'adactive' ) {
                    $dql->andWhere("user.activeAD = true");
                }
                if( $status == 'adinactive' ) {
                    $dql->andWhere("user.activeAD = false");
                }
                if( $status == 'locked' ) {
                    $dql->andWhere("user.enabled = false");
                }
                if( $status == 'active' ) {
                    //enable, termination date is null or in future
                    //Don't filter by usertype ldap-user or ldap2-user
                    $statusStr =
                        "(user.enabled = true"
                        ." AND (employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > :todayDate)"
                        .")"
                    ;
                    $dql->andWhere($statusStr);

                    //$nowDate = new \DateTime();
                    $nowDate = new \DateTime('today midnight');
                    $nowDateStr = $nowDate->format('Y-m-d H:i:s');
                    $queryParameters['todayDate'] = $nowDateStr;
                }
                if( $status == 'terminated' ) {
                    //termination date is not null and in past
                    $statusStr =
                        "(employmentStatus.terminationDate IS NOT NULL AND employmentStatus.terminationDate < :todayDate)"
                    ;
                    $dql->andWhere($statusStr);

                    //$nowDate = new \DateTime();
                    $nowDate = new \DateTime('today midnight');
                    $nowDateStr = $nowDate->format('Y-m-d H:i:s');
                    $queryParameters['todayDate'] = $nowDateStr;
                }
            }

            //exit('111');
        }
        //exit('111');

        //$dql->orderBy("infos.lastName","ASC");
        $sort = $request->query->get('sort', null);
        $sortDirection = $request->query->get('direction', 'desc');
        if( $sort == 'user.activeAD' ) {
            //https://stackoverflow.com/questions/28852390/how-can-i-order-null-values-first-on-a-doctrine-2-collection-using-annotations
            $dql->addSelect('CASE WHEN user.activeAD IS NOT NULL THEN TRUE ELSE FALSE END AS HIDDEN myActiveADIsNull');
            $dql->orderBy("myActiveADIsNull",$sortDirection);
            $dql->addOrderBy("user.activeAD",$sortDirection);
        }
        if( $sort == 'institution.name' ) {
            //https://stackoverflow.com/questions/28852390/how-can-i-order-null-values-first-on-a-doctrine-2-collection-using-annotations
            //$dql->addSelect('CASE WHEN user.activeAD IS NOT NULL THEN TRUE ELSE FALSE END AS HIDDEN myActiveADIsNull');
            $dql->orderBy("institution.name",$sortDirection);
            $dql->addOrderBy("appointmentInstitution.name",$sortDirection);
            $dql->addOrderBy("medicalInstitution.name",$sortDirection);
        }

        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        $query->setParameters($queryParameters);

        //$query->setMaxResults(30);
        //$totalUsers = $query->getResult();

        $limit = 20; //20;

        $paginationParams = array(
            'defaultSortFieldName' => 'infos.lastName',
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

        $cwids = array();
        $jsonArray = array();
        foreach($users as $user) {

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

            $eventlogLink = $this->container->get('router')->generate(
                'employees_logger_user_all',
                array(
                    'id' => $user->getId()
                ),
                //UrlGeneratorInterface::ABSOLUTE_PATH
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $institutions = $user->getDeduplicatedInstitutions();
            $instNames = array();
            foreach( $institutions as $instRes ) {
                foreach( $instRes as $instId => $instArr ) {
                    $instNames[] = $instArr['instName'];
                }
            }
            $instNameStr = implode("; ", $instNames);

            $degreeTitle = $user->getDegreesTitles();

            $titles = $degreeTitle['title'];

            $degree = $user->getSingleSalutation();

            $startEndDate = $user->getEmploymentStartEndDates();
            $startDate = $startEndDate['startDate'];
            $endDate = $startEndDate['endDate'];

            $locked = 'Enabled';
            if( $user->isLocked() ) {
                $locked = 'Locked';
            }

//            $status = "";
//            $terminationStr = $user->getEmploymentTerminatedStr(false);
//            if( $terminationStr ) {
//                $status = $terminationStr; //"terminated";
//            }
//            $adStatus = $user->getAdStatusStr();
//            if( $adStatus ) {
//                if( $status ) {
//                    $status = $status . "; ";
//                }
//                $status = $status . $adStatus;
//            }
            $status = $user->getFullStatusStr(false,false,false); //$short=true, $withBrackets=true, $withLockStatus=true

            //get usernametype => local or ldap
            $userKeyTypeAbbreviation = "";
            $userKeyType = $user->getKeyType();
            if( $userKeyType ) {
                $userKeyTypeAbbreviation = $userKeyType->getAbbreviation(); //ldap-user, ldap2-user, local-user
            }

            $cwid = $user->getCleanUsername();
            $cwids[] = $cwid;

            //try AD for each
//            $adStatus = "AD Inactive";
//            $res = $authUtil->searchLdap($cwid,$ldapType=1,$withWarning=true);
//            if( $res ) {
//                $adStatus = "AD Active";
//            }
//            $status = $status . "; " . $adStatus;

            $lastLogin = $user->getLastLogin();
            if( $lastLogin ) {
                $lastLogin = $lastLogin->format('m/d/Y');
            }


            $jsonArray[] = array(
                'id'            => $user->getId(),
                'cwid'          => $cwid,
                'showLink'      => $showLink,
                'editLink'      => $editLink,
                'eventlogLink'  => $eventlogLink,
                'LastName'      => $user->getSingleLastName(),
                'FirstName'     => $user->getSingleFirstName(),
                'Degree'        => $degree,
                'Email'         => $user->getSingleEmail(),
                'Institution'   => $instNameStr,
                'Title'         => $titles,
                'LastLogin'     => $lastLogin,
                'StartDate'     => $startDate,
                'EndDate'       => $endDate,
                'locked'        => $locked,
                'status'        => $status,
                'keytype'       => $userKeyTypeAbbreviation,
                'checkLdapStatus' => false

            );
        }//foreach
        //dump($jsonArray);
        //exit('111');

        if(0) {
            $ldapType = 1;
            $withWarning = true;
            $ldapUsers = $authUtil->getADUsersByCwids($cwids, $ldapType, $withWarning); //cwid => dn

            $newJsonArray = array();
            foreach ($jsonArray as $thisUser) {
                $cwid = $thisUser['cwid'];
                if (isset($ldapUsers[$cwid])) {
                    $thisUser['adStatus'] = true; //$ldapUsers[$cwid];
                    if ($thisUser['status']) {
                        $thisUser['status'] = $thisUser['status'] . ";";
                    }
                    $thisUser['status'] = $thisUser['status'] . " Active in AD";
                } else {
                    $thisUser['adStatus'] = false;
                    if ($thisUser['status']) {
                        $thisUser['status'] = $thisUser['status'] . ";";
                    }
                    $thisUser['status'] = $thisUser['status'] . " Inactive in AD";
                }
                $newJsonArray[] = $thisUser;
            }
        }

        //dump($newJsonArray);
        //exit('111');

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

    #Change user dates by "Employment dates" page using react
    #[Route(path: '/update-users-dates/', name: 'employees_update_users_date', options: ['expose' => true])]
    public function updateUsersDateAction( Request $request ) {
        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $sitename = $this->getParameter('employees.sitename');
        $results = 'ok';

        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        $datas = json_decode($request->getContent(), true);
        //dump($datas);
        //exit('111');

        $deactivateData = $datas['deactivateData'];
        $modifiedData = $datas['modifiedData'];
        //dump($deactivateData);
        //dump($modifiedData);
        //exit('222');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
        $employmentType = $em->getRepository(EmploymentType::class)->findOneByName("Full Time");
        if( !$employmentType ) {
            $results = 'Unable to find EmploymentType entity by name='."Full Time";
            //throw new EntityNotFoundException('Unable to find entity by name='."Full Time");
        }

        $testing = false;
        //$testing = true;

        $eventArr = $this->processData($deactivateData,$request,true,$testing);
        //dump($eventArr);
        //exit('111');
        $eventStr = implode("<br>",$eventArr);

        $eventArr = $this->processData($modifiedData,$request,false,$testing);
        $eventStr = $eventStr . "<br><br>" . implode("<br>",$eventArr);

        $this->addFlash(
            'notice',
            $eventStr
        );

        //exit('Not implemented');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent(json_encode($results));

        return $response;
    }

    #Change user dates by "Manage Groups" page on the vacreq system
    #[Route(path: '/update-users-dates-vacreq/', name: 'employees_update_users_date_vacreq', options: ['expose' => true])]
    public function updateUsersDateVacReqAction( Request $request ) {
        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $sitename = $this->getParameter('employees.sitename');
        $results = 'ok';

        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        //dump($request);
        //exit('111');

        //$deactivateData = $request->query->get('deactivateData');
        //$modifiedData = $request->query->get('modifiedData');
        //dump($deactivateData);
        //dump($modifiedData);
        //exit('111');

//        $userId = json_decode($request->get('userId'));
//        $startDate = json_decode($request->get('startDate'));
//        $endDate = json_decode($request->get('endDate'));
        $userId = $request->get('userId');
        $emplstatusId = $request->get('emplstatusId');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $effort = $request->get('effort');
        echo "userId=$userId, startDate=$startDate, endDate=$endDate, effort=$effort <br>";
        //exit('111');

        $userData = array(
            'emplstatusId' => $emplstatusId,
            'userId' => $userId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'effort' => $effort
        );
        $modifiedData = array($userData);

        //$datas = json_decode($request->getContent());
        //dump($datas);
        //exit('111');

        //$deactivateData = $datas['deactivateData'];
        //$modifiedData = $datas['modifiedData'];
        //dump($deactivateData);
        //dump($modifiedData);
        //exit('222');

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EmploymentType'] by [EmploymentType::class]
        $employmentType = $em->getRepository(EmploymentType::class)->findOneByName("Full Time");
        if( !$employmentType ) {
            $results = 'Unable to find EmploymentType entity by name='."Full Time";
            //throw new EntityNotFoundException('Unable to find entity by name='."Full Time");
        }

        $testing = false;
        //$testing = true;

        //$eventArr = $this->processData($deactivateData,$request,true,$testing);
        //dump($eventArr);
        //exit('111');
        //$eventStr = implode("<br>",$eventArr);

        $noteStr = "on the vacation system's Manage Groups page";
        $eventArr = $this->processData($modifiedData,$request,false,$testing,$noteStr);
        $eventStr = implode("<br>",$eventArr);

//        $this->addFlash(
//            'notice',
//            $eventStr
//        );

        //exit('Not implemented');

        //$response = new Response();
        //$response->headers->set('Content-Type', 'application/json');
        //$response->setStatusCode(200);
        //$response->headers->set('Access-Control-Allow-Origin', '*');
        //$response->setContent(json_encode($results));

        $response = new Response($eventStr);
        return $response;
    }

    public function processData($inputData,$request,$withLocking=false,$testing=false,$noteStr='with bulk updates') {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $sitename = $this->getParameter('employees.sitename');
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        //dump($inputData);
        //echo "data len=".count($inputData)."<br>";

        $processedUserIdArr = array();
        $eventArr = array();

        foreach($inputData as $data) {
            //dump($data);
            $emplstatusId = $data['emplstatusId'];
            $userId = $data['userId'];
            $startDateStr = $data['startDate'];
            $endDateStr = $data['endDate'];
            $effort = $data['effort'];
            //echo "emplstatusId=$emplstatusId, userId=$userId, startDateStr=$startDateStr, endDateStr=$endDateStr <br>";
            //exit("111");

            $employmentStatus = $em->getRepository(EmploymentStatus::class)->find($emplstatusId);

            if( !$employmentStatus ) {
                $eventArr[] = "Ignore logical error: EmploymentStatus not found by ID=$emplstatusId";
                continue;
            }

            $event = null;
            $changeArr = array();

            $user = $employmentStatus->getUser();
            if( !$user ) {
                $eventArr[] = "Ignore logical error: EmploymentStatus does not have associated user";
                continue;
            }

            $origianlEnableStatus = $user->isEnabled();

            //Save the start and end dates into the existing array field in the user profile (we have one already - please don’t create a new one)
            $originalStartDate = $employmentStatus->getHireDate();
            if( $originalStartDate ) {
                $originalStartDate = $originalStartDate->format('m/d/Y');
            } else {
                $originalStartDate = "None";
            }
            $originalEndDate = $employmentStatus->getTerminationDate();
            if( $originalEndDate ) {
                $originalEndDate = $originalEndDate->format('m/d/Y');
            } else {
                $originalEndDate = "None";
            }
            $originalEffort = $employmentStatus->getEffort();

            //if( $startDateStr ) {
            if( $originalStartDate != $startDateStr ) {
                if( $startDateStr ) {
                    $startDate = \DateTime::createFromFormat('m/d/Y H:i', $startDateStr . " 00:00");
                    $startDate = $userServiceUtil->convertFromUserTimezonetoUTC($startDate, $user);
                    //echo "startDate=".$startDate->format('m/d/Y H:i')."<br>";
                } else {
                    $startDate = null;
                    $startDateStr = "None";
                }
                $employmentStatus->setHireDate($startDate);
                if( $originalStartDate != $startDateStr ) {
                    $changeArr[] = "Start date changed from $originalStartDate to $startDateStr for EmploymentStatus ID=$emplstatusId";
                }
            }
            //}
            //if( $endDateStr ) {
            if( $originalEndDate != $endDateStr ) {
                if( $endDateStr ) {
                    $endDate = \DateTime::createFromFormat('m/d/Y H:i', $endDateStr . " 00:00");
                    $endDate = $userServiceUtil->convertFromUserTimezonetoUTC($endDate, $user);
                    //echo "endDate=".$endDate->format('m/d/Y H:i')."<br>";
                } else {
                    $endDate = null;
                    $endDateStr = "None";
                }
                $employmentStatus->setTerminationDate($endDate);
                if( $originalEndDate != $endDateStr ) {
                    $changeArr[] = "End date changed from $originalEndDate to $endDateStr for EmploymentStatus ID=$emplstatusId";
                }
            }
            //}
            if( $originalEffort != $effort ) {
                if( $effort == '' || $effort == 0 ) {
                    $effort = NULL;
                }
                $employmentStatus->setEffort($effort);
                $changeArr[] = "Effort changed from $originalEffort to $effort for EmploymentStatus ID=$emplstatusId";
            }

            //lock user account
            if( $withLocking ) {
                if ($origianlEnableStatus !== false) {
                    $user->setEnabled(false);
                    $changeArr[] = "User $user is locked by $currentUser";
                }
            }

            if (count($changeArr) > 0) {
                //$user->addEmploymentStatus($employmentStatus);

                if( !$testing ) {
                    $em->flush();
                }

                $event = "User profile of " . $user . " has been changed by " . $currentUser . " $noteStr:" . "<br>";
                $changeStr = implode("; ", $changeArr);

                $event = $event . $changeStr;

                //Event Log
                if( !$testing ) {
                    $userSecUtil->createUserEditEvent($sitename, $event, $currentUser, $user, $request, 'User record updated');
                }
            } else {
                $event = "User profile of " . $user . " has not been changed by " . $currentUser . " $noteStr";
            }

            $eventArr[] = $event . "<br>";
        }

        return $eventArr;
    }

    //Process Dates: set start/end employment dates
    public function processData_ORIG($inputData,$request,$withLocking=false,$testing=false,$noteStr='with bulk updates') {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $sitename = $this->getParameter('employees.sitename');
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        //dump($inputData);
        //echo "data len=".count($inputData)."<br>";


        $processedUserIdArr = array();
        $eventArr = array();

        foreach($inputData as $data) {
            //dump($data);
            $emplstatusId = $data['emplstatusId'];
            $userId = $data['userId'];
            $startDateStr = $data['startDate'];
            $endDateStr = $data['endDate'];
            //echo "userId=$userId, startDateStr=$startDateStr, endDateStr=$endDateStr <br>";
            //exit("111");

            if( in_array($userId,$processedUserIdArr)  ) {
                //$eventArr[] = "Skip repeated user with id=".$userId;
                continue;
            }

            $processedUserIdArr[] = $userId;

            if( !$userId ) {
                $eventArr[] = "Ignore logical error: user id is empty";
                continue;
            }

            $user = $em->getRepository(User::class)->find($userId);
            if( !$user ) {
                $eventArr[] = "Ignore: user not found by user id ".$userId;
                continue;
            }

            $event = null;
            $changeArr = array();

            $origianlEnableStatus = $user->isEnabled();

            //Save the start and end dates into the existing array field in the user profile (we have one already - please don’t create a new one)
            //get latest employement status
            $latestEmploymentStatus = $user->getLatestEmploymentStatus();
            if( !$latestEmploymentStatus ) {
                $eventArr[] = "Ignore: latest employment status not found for ".$user;
                continue;
            }

            $originalStartDate = $latestEmploymentStatus->getHireDate();
            if( $originalStartDate ) {
                $originalStartDate = $originalStartDate->format('m/d/Y');
            } else {
                $originalStartDate = "None";
            }
            $originalEndDate = $latestEmploymentStatus->getTerminationDate();
            if( $originalEndDate ) {
                $originalEndDate = $originalEndDate->format('m/d/Y');
            } else {
                $originalEndDate = "None";
            }

            //if( $startDateStr ) {
                if( $originalStartDate != $startDateStr ) {
                    if( $startDateStr ) {
                        $startDate = \DateTime::createFromFormat('m/d/Y H:i', $startDateStr . " 00:00");
                        $startDate = $userServiceUtil->convertFromUserTimezonetoUTC($startDate, $user);
                        //echo "startDate=".$startDate->format('m/d/Y H:i')."<br>";
                    } else {
                        $startDate = null;
                        $startDateStr = "None";
                    }
                    $latestEmploymentStatus->setHireDate($startDate);
                    if( $originalStartDate != $startDateStr ) {
                        $changeArr[] = "Start date changed from $originalStartDate to $startDateStr";
                    }
                }
            //}
            //if( $endDateStr ) {
                if( $originalEndDate != $endDateStr ) {
                    if( $endDateStr ) {
                        $endDate = \DateTime::createFromFormat('m/d/Y H:i', $endDateStr . " 00:00");
                        $endDate = $userServiceUtil->convertFromUserTimezonetoUTC($endDate, $user);
                        //echo "endDate=".$endDate->format('m/d/Y H:i')."<br>";
                    } else {
                        $endDate = null;
                        $endDateStr = "None";
                    }
                    $latestEmploymentStatus->setTerminationDate($endDate);
                    if( $originalEndDate != $endDateStr ) {
                        $changeArr[] = "End date changed from $originalEndDate to $endDateStr";
                    }
                }
            //}

            //lock user account
            if( $withLocking ) {
                if ($origianlEnableStatus !== false) {
                    $user->setEnabled(false);
                    $changeArr[] = "User $user is locked by $currentUser";
                }
            }

            if (count($changeArr) > 0) {
                //$user->addEmploymentStatus($employmentStatus);

                if( !$testing ) {
                    $em->flush();
                }

                $event = "User profile of " . $user . " has been changed by " . $currentUser . " $noteStr:" . "<br>";
                $changeStr = implode("; ", $changeArr);

                $event = $event . $changeStr;

                //Event Log
                if( !$testing ) {
                    $userSecUtil->createUserEditEvent($sitename, $event, $currentUser, $user, $request, 'User record updated');
                }
            } else {
                $event = "User profile of " . $user . " has not been changed by " . $currentUser . " $noteStr";
            }

            $eventArr[] = $event . "<br>";
        }

        return $eventArr;
    }

    //NOT USED
    public function processModifiedData($modifiedData,$request,$testing=false) {
        $em = $this->getDoctrine()->getManager();
        $currentUser = $this->getUser();
        $sitename = $this->getParameter('employees.sitename');
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');

        $eventArr = array();

        foreach($modifiedData as $data) {
            //dump($data);
            $userId = $data['userId'];
            $startDateStr = $data['startDate'];
            $endDateStr = $data['endDate'];
            //echo "userId=$userId, startDateStr=$startDateStr, endDateStr=$endDateStr <br>";
            //exit("111");

            if( !$userId ) {
                $eventArr[] = "User id does not exist";
                continue;
            }

            $user = $em->getRepository(User::class)->find($userId);
            if( !$user ) {
                $eventArr[] = "User not found by user id ".$userId;
                continue;
            }

            $event = null;
            $changeArr = array();

            //Save the start and end dates into the existing array field in the user profile (we have one already - please don’t create a new one)
            //get latest employement status
            $latestEmploymentStatus = $user->getLatestEmploymentStatus();
            if( !$latestEmploymentStatus ) {
                $eventArr[] = "Latest employment status not found for ".$user;
                continue;
            }

            $originalStartDate = $latestEmploymentStatus->getHireDate();
            if( $originalStartDate ) {
                $originalStartDate = $originalStartDate->format('m/d/Y');
            }
            $originalEndDate = $latestEmploymentStatus->getTerminationDate();
            if( $originalEndDate ) {
                $originalEndDate = $originalEndDate->format('m/d/Y');
            }

            if( $startDateStr ) {
                if( $originalStartDate != $startDateStr ) {
                    $startDate = \DateTime::createFromFormat('m/d/Y H:i', $startDateStr." 00:00");
                    $startDate = $userServiceUtil->convertFromUserTimezonetoUTC($startDate,$user);
                    //echo "startDate=".$startDate->format('m/d/Y H:i')."<br>";
                    $latestEmploymentStatus->setHireDate($startDate);
                    $changeArr[] = "Start date changed from $originalStartDate to $startDateStr";
                }
            }
            if( $endDateStr ) {
                if( $originalEndDate != $endDateStr ) {
                    $endDate = \DateTime::createFromFormat('m/d/Y H:i', $endDateStr." 00:00");
                    $endDate = $userServiceUtil->convertFromUserTimezonetoUTC($endDate,$user);
                    //echo "endDate=".$endDate->format('m/d/Y H:i')."<br>";
                    $latestEmploymentStatus->setTerminationDate($endDate);
                    $changeArr[] = "End date changed from $originalEndDate to $endDateStr";
                }
            }

            if (count($changeArr) > 0) {
                //$user->addEmploymentStatus($employmentStatus);

                if( !$testing ) {
                    $em->flush();
                }

                $event = "User profile of " . $user . " has been changed by " . $currentUser . " with bulk updates:" . "<br>";
                $changeStr = implode("; ", $changeArr);

                $event = $event . $changeStr;

                //Event Log
                if( !$testing ) {
                    $userSecUtil->createUserEditEvent($sitename, $event, $currentUser, $user, $request, 'User record updated');
                }
            } else {
                $event = "User profile of " . $user . " has not been changed by " . $currentUser . " with bulk updates";
            }

            $eventArr[] = $event . "<br>";
        }

        return $eventArr;
    }
}
