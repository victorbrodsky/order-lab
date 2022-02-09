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


use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\VacReqBundle\Entity\VacReqRequest;
use App\VacReqBundle\Entity\VacReqRequestFloating;
use App\VacReqBundle\Form\VacReqFilterType;
use App\VacReqBundle\Form\VacReqFloatingDayType;
use App\VacReqBundle\Form\VacReqRequestFloatingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//vacreq site

class FloatingDayController extends OrderAbstractController
{

    /**
     * @Route("/incoming-requests/floating-day", name="vacreq_floatingrequests", methods={"GET", "POST"})
     * @Template("AppVacReqBundle/FloatingDay/index.html.twig")
     */
    public function incomingFloatingRequestsAction(Request $request)
    {
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') && false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //exit('incomingFloatingRequestsAction');
        $em = $this->getDoctrine()->getManager();
        $vacreqUtil = $this->get('vacreq_util');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $routeName = $request->get('_route');
        $sitename = $this->getParameter('vacreq.sitename');
        $filtered = false;
        $indexTitle = "Floating Day Incoming Requests";
        $pageTitle = $indexTitle;
        $requestTypeAbbreviation = "floatingday";

        //////////////// create vacreq filter ////////////////
        $params = array(
            //'cycle' => 'show'
            'em' => $em,
            'routeName' => $routeName,
            'filterShowUser' => true,
            'requestTypeAbbreviation' => $requestTypeAbbreviation,
        );

        $supervisorRole = false;
        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            $supervisorRole = true;
        }
        $params['supervisor'] = $supervisorRole;

        //////////////////// get list of users with "unknown" user ////////////////////
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('AppUserdirectoryBundle:User');
        $dqlFilterUser = $repository->createQueryBuilder('user');
        $dqlFilterUser->select('user');
        $dqlFilterUser->leftJoin("user.infos","infos");
        $dqlFilterUser->leftJoin("user.employmentStatus", "employmentStatus");
        $dqlFilterUser->leftJoin("employmentStatus.employmentType", "employmentType");
        //filter out system user
        $dqlFilterUser->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'");
        //filter out Pathology Fellowship Applicants
        $dqlFilterUser->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL");
        //$dqlFilterUser->where("user.keytype IS NOT NULL");
        $dqlFilterUser->orderBy("infos.lastName","ASC");
        $queryFilterUser = $em->createQuery($dqlFilterUser);
        $filterUsers = $queryFilterUser->getResult();
        //echo "count=".count($filterUsers)."<br>";
        //add unknown dummy user
//        $unknown = new User();
//        $unknown->setDisplayName("unknown");
//        $em->persist($unknown);
        //$filterUsers[] = $unknown;
//        array_unshift($filterUsers, $unknown);
        $params['filterUsers'] = $filterUsers;
        //////////////////// EOF get list of users with "unknown" user ////////////////////

        //get submitter groups: VacReqRequest, create
        $groupParams = array();
        $groupParams['statusArr'] = array('default','user-added');
        if( $request->get('_route') == "vacreq_myrequests" ) {
            $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        }
        if( $request->get('_route') == "vacreq_incomingrequests" ) {
            if( $params['requestTypeAbbreviation'] == "business-vacation" ) {
                $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
                if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') == false ) {
                    $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
                }
            } else {
                $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
            }
        }
        if( $request->get('_route') == "vacreq_floatingrequests" ) {
            $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
            if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') == false ) {
                $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
            }
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);

        $userServiceUtil = $this->get('user_service_utility');
        $params['organizationalInstitutions'] = $userServiceUtil->flipArrayLabelValue($organizationalInstitutions); //flipped //$organizationalInstitutions;


        $filterform = $this->createForm(VacReqFilterType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));
        //////////////// EOF create vacreq filter ////////////////

        //$filterform->submit($request);  //use bind instead of handleRequest. handleRequest does not get filter data
        $filterform->handleRequest($request);
        
        $repository = $em->getRepository('AppVacReqBundle:VacReqRequestFloating');
        $dql = $repository->createQueryBuilder("request");

        $dql->select('request');

        //COALESCE(requestBusiness.numberOfDays,0) replace NULL with 0 (similar to ISNULL)
        //$dql->addSelect('(COALESCE(requestBusiness.numberOfDays,0) + COALESCE(requestVacation.numberOfDays,0)) as thisRequestTotalDays');

        $dql->leftJoin("request.user", "user");
        //$dql->leftJoin("request.submitter", "submitter");
        $dql->leftJoin("user.infos", "infos");
        $dql->leftJoin("request.institution", "institution");

        $limit = 30;
        $query = $em->createQuery($dql);

        $paginationParams = array(
            //'defaultSortFieldName' => 'request.firstDayAway', //createDate
            'defaultSortDirection' => 'DESC',
            'wrap-queries'=>true //use "doctrine/orm": "v2.4.8". ~2.5 causes error: Cannot select distinct identifiers from query with LIMIT and ORDER BY on a column from a fetch joined to-many association. Use walker.
        );

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );
        

        return array(
            'filterform' => $filterform,
            'vacreqfilter' => $filterform->createView(),
            'pagination' => $pagination,
            'sitename' => $sitename,
            'filtered' => $filtered,
            'routename' => $routeName,
            'title' => $indexTitle,
            'pageTitle' => $pageTitle,
            'requestTypeAbbreviation' => $requestTypeAbbreviation,
            //'totalApprovedDaysString' => $params['totalApprovedDaysString']
        );
    }

    /**
     * @Route("/floating-day-request", name="vacreq_floating_day", methods={"GET"})
     * @Template("AppVacReqBundle/FloatingDay/floating-day.html.twig")
     */
    public function FloatingDayAction(Request $request) {

        if(
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_OBSERVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUBMITTER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $userSecUtil = $this->container->get('user_security_utility');
        $userServiceUtil = $this->get('user_service_utility');
        $vacreqUtil = $this->get('vacreq_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = new VacReqRequestFloating($user);
        
        $params = array();
        $params['em'] = $em;
        //$params['supervisor'] = $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR');

//        $floatingNote = "The Juneteenth Holiday may be used as a floating holiday
//        only if you have an NYPH appointment. You can request a floating holiday however,
//        it must be used in the same fiscal year ending June 30, 2022. It cannot be carried over";
        $floatingNote = $userSecUtil->getSiteSettingParameter('floatingDayNote','vacreq');

        //$title = "Floating Day (The page and functionality are under construction!)";
        $title = $userSecUtil->getSiteSettingParameter('floatingDayName','vacreq');
        $title = $title . " - The page and functionality are under construction!";

        $cycle = 'new';

        $form = $this->createRequestForm($entity,$cycle,$request);

        $form->handleRequest($request);


        if( $form->isSubmitted() && $form->isValid() ) { //new
        
        
        }
        
        

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'cycle' => $cycle,
            'title' => $title,
            'floatingNote' => $floatingNote
        );
    }

    public function createRequestForm( $entity, $cycle, $request ) {

        $em = $this->getDoctrine()->getManager();
        $userServiceUtil = $this->get('user_service_utility');
        $vacreqUtil = $this->get('vacreq_util');
        $routeName = $request->get('_route');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $admin = false;
        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
            $admin = true;
        }

        $roleApprover = false;
        if( $this->get('security.authorization_checker')->isGranted("changestatus", $entity) ) {
            $roleApprover = true;
        }

        $groupParams = array();

        $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') == false ) {
            $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
        }

        $tentativeInstitutions = null;

        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);
        //echo "1 organizationalInstitutions count=".count($organizationalInstitutions)."<br>";

        //include this request institution to the $organizationalInstitutions array
        $organizationalInstitutions = $vacreqUtil->addRequestInstitutionToOrgGroup( $entity, $organizationalInstitutions );
        //echo "2 organizationalInstitutions count=".count($organizationalInstitutions)."<br>";

        //include this request institution to the $tentativeInstitutions array
        //$tentativeInstitutions = $vacreqUtil->addRequestInstitutionToOrgGroup( $entity, $tentativeInstitutions, "tentativeInstitution" );

        if( count($organizationalInstitutions) == 0 ) {
            //If count($organizationalInstitutions) == 0 then try to run http://hosthame/order/directory/admin/sync-db/

            if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
                //admin user
                $groupPageUrl = $this->generateUrl(
                    "vacreq_approvers",
                    array(),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $warningMsg = "You don't have any group and/or assigned Submitter roles for the Business/Vacation Request site.".
                    ' <a href="'.$groupPageUrl.'" target="_blank">Please create a group and/or assign a Submitter role to your user account.</a> ';
            } else {
                //regular user
                $adminUsers = $em->getRepository('AppUserdirectoryBundle:User')->findUserByRole("ROLE_VACREQ_ADMIN", "infos.lastName", true);
                $emails = array();
                foreach ($adminUsers as $adminUser) {
                    $singleEmail = $adminUser->getSingleEmail();
                    if ($singleEmail) {
                        $emails[] = $adminUser->getSingleEmail();
                    }
                }
                $emailStr = "";
                if (count($emails) > 0) {
                    $emailStr = " Administrator email(s): " . implode(", ", $emails);
                }

                $warningMsg = "You don't have any group and/or assigned Submitter roles for the Business/Vacation Request site.".
                    " Please contact the site administrator to create a group and/or get a Submitter role for your account.".$emailStr;
            }
            //Flash
            $this->get('session')->getFlashBag()->add(
                'warning',
                $warningMsg
            );
        }

        //get holidays url
        $userSecUtil = $this->container->get('user_security_utility');
        $holidaysUrl = $userSecUtil->getSiteSettingParameter('holidaysUrl','vacreq');
        if( !$holidaysUrl ) {
            throw new \InvalidArgumentException('holidaysUrl is not defined in Site Parameters.');
        }
        $holidaysUrl = '<a target="_blank" href="'.$holidaysUrl.'">holidays</a>';

        //echo "roleApprover=".$roleApprover."<br>";
        //echo "roleCarryOverApprover=".$roleCarryOverApprover."<br>";

        $params = array(
            'container' => $this->container,
            'em' => $em,
            'user' => $entity->getUser(),
            'cycle' => $cycle,
            'roleAdmin' => $admin,
            'roleApprover' => $roleApprover,
            'organizationalInstitutions' => $userServiceUtil->flipArrayLabelValue($organizationalInstitutions),
            'tentativeInstitutions' => $userServiceUtil->flipArrayLabelValue($tentativeInstitutions),
            'holidaysUrl' => $holidaysUrl,
            'maxCarryOverVacationDays' => $userSecUtil->getSiteSettingParameter('maxCarryOverVacationDays','vacreq'),
            'noteForCarryOverDays' => $userSecUtil->getSiteSettingParameter('noteForCarryOverDays','vacreq'),
            //'maxVacationDays' => $userSecUtil->getSiteSettingParameter('maxVacationDays','vacreq'),
            //'noteForVacationDays' => $userSecUtil->getSiteSettingParameter('noteForVacationDays','vacreq'),
        );
        
        //set default floating day
        $floatingDayType = NULL;
        $parameters = array();
        $repository = $em->getRepository('AppVacReqBundle:VacReqFloatingTypeList');
        $dql = $repository->createQueryBuilder('list');
        $dql->andWhere("(list.type = :typedef OR list.type = :typeadd)");
        $dql->orderBy("list.orderinlist","ASC");
        $parameters['typedef'] = 'default';
        $parameters['typeadd'] = 'user-added';
        $query = $em->createQuery($dql);
        if( count($parameters) > 0 ) {
            $query->setParameters($parameters);
        }
        $floatingDayTypes = $query->getResult();
        if( count($floatingDayTypes) > 0 ) {
            $floatingDayType = $floatingDayTypes[0];
        }

        $params['defaultFloatingDayType'] = $floatingDayType;

        $disabled = false;
        $method = 'GET';

        if( $cycle == 'show' ) {
            $disabled = true;
        }

        if( $cycle == 'new' ) {
            $method = 'POST';
        }

        if( $cycle == 'edit' ) {
            $method = 'POST';
        }

        if( $cycle == 'review' ) {
            $method = 'POST';
        }

        $params['review'] = false;
        if( $request ) {
            if( $routeName == 'vacreq_review' ) {
                $params['review'] = true;
            }
        }

        $form = $this->createForm(
            VacReqRequestFloatingType::class,
            $entity,
            array(
                'form_custom_value' => $params,
                'disabled' => $disabled,
                'method' => $method,
                //'action' => $action
            )
        );

        return $form;
    }

}
