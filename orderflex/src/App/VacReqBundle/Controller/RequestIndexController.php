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



use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\VacReqBundle\Entity\VacReqRequestTypeList; //process.py script: replaced namespace by ::class: added use line for classname=VacReqRequestTypeList
use App\UserdirectoryBundle\Entity\User;
use App\VacReqBundle\Entity\VacReqRequest;
use App\VacReqBundle\Form\VacReqFilterType;
use App\VacReqBundle\Form\VacReqRequestType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//vacreq site

class RequestIndexController extends OrderAbstractController
{

    #[Route(path: '/my-requests/', name: 'vacreq_myrequests', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Request/index.html.twig')]
    public function myRequestsAction(Request $request)
    {

        if( false == $this->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $user = $this->getUser();

        ///////// redirect to floating list /////////
        $vacreqUtil = $this->container->get('vacreq_util');
        $redirectArr = $vacreqUtil->redirectIndex($request);
        if( $redirectArr ) {
            return $this->redirect(
                $this->generateUrl($redirectArr['routeName'],$redirectArr['params'])
            );
        }
        ///////// EOF redirect to floating list /////////

        //$vacreqUtil = $this->container->get('vacreq_util');

        //$em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('AppVacReqBundle:VacReqRequest')->findAll();

        //$user = $this->getUser();

        //calculate approved vacation days in total.
        //$totalApprovedDaysString = $vacreqUtil->getApprovedDaysString($user);

        $params = array(
            'sitename' => $this->getParameter('vacreq.sitename'),
            'subjectUser' => $user,
            'title' => "My Business Travel & Vacation Requests",
//            'totalApprovedDaysString' => $totalApprovedDaysString,
//            'accruedDaysString' => $accruedDaysString,
//            'carriedOverDaysString' => $carriedOverDaysString,
//            'remainingDaysString' => $remainingDaysString,
            'filterShowUser' => false,
        );
        return $this->listRequests($params, $request);
    }


    #[Route(path: '/incoming-requests/', name: 'vacreq_incomingrequests', methods: ['GET', 'POST'])]
    #[Template('AppVacReqBundle/Request/index.html.twig')]
    public function incomingRequestsAction(Request $request)
    {
        if( false == $this->isGranted('ROLE_VACREQ_APPROVER') && false == $this->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        ///////// redirect to floating list /////////
        $vacreqUtil = $this->container->get('vacreq_util');
        $redirectArr = $vacreqUtil->redirectIndex($request);
        if( $redirectArr ) {
            return $this->redirect(
                $this->generateUrl($redirectArr['routeName'],$redirectArr['params'])
            );
        }
        ///////// EOF redirect to floating list /////////

        $user = $this->getUser();

        $params = array(
            'sitename' => $this->getParameter('vacreq.sitename'),
            'approver' => $user,
            'title' => "Incoming Business Travel & Vacation Requests",
            'filterShowUser' => true
        );
        return $this->listRequests($params, $request);
    }




    public function listRequests( $params, $request ) {

        $vacreqUtil = $this->container->get('vacreq_util');
        $userTenantUtil = $this->container->get('user_tenant_utility');

        $em = $this->getDoctrine()->getManager();

        $sitename = ( array_key_exists('sitename', $params) ? $params['sitename'] : null);
        $subjectUser = ( array_key_exists('subjectUser', $params) ? $params['subjectUser'] : null); //logged in user
        $approver = ( array_key_exists('approver', $params) ? $params['approver'] : null);
        //echo "approver=".$approver."<br>";s

        $forceShowAllRows = true;
        $forceShowAllRows = false;

        $routeName = $request->get('_route');

        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequest'] by [VacReqRequest::class]
        $repository = $em->getRepository(VacReqRequest::class);
        $dql = $repository->createQueryBuilder("request");

        $dql->select('request as object');

        //COALESCE(requestBusiness.numberOfDays,0) replace NULL with 0 (similar to ISNULL)
        $dql->addSelect('(COALESCE(requestBusiness.numberOfDays,0) + COALESCE(requestVacation.numberOfDays,0)) as thisRequestTotalDays');

        $dql->leftJoin("request.user", "user");
        //$dql->leftJoin("request.submitter", "submitter");
        $dql->leftJoin("user.infos", "infos");
        $dql->leftJoin("request.institution", "institution");
        $dql->leftJoin("request.tentativeInstitution", "tentativeInstitution");

        $dql->leftJoin("request.requestBusiness", "requestBusiness");
        $dql->leftJoin("request.requestVacation", "requestVacation");

        $dql->leftJoin("request.requestType", "requestType");

        //$dql->where("requestBusiness.startDate IS NOT NULL OR requestVacation.startDate IS NOT NULL");

        //my requests
        if( $subjectUser ) {
            $dql->andWhere("(request.user=".$subjectUser->getId()." OR request.submitter=".$subjectUser->getId().")");
        }

        //incoming requests: show all requests with institutions in vacreq roles institutions
        //filter by institutions for any user by using a general sub role name "ROLE_VACREQ_"
        if( false == $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            if( $approver ) {
                //echo "Yes approver <br>";
                $partialRoleName = "ROLE_VACREQ_";  //"ROLE_VACREQ_APPROVER"
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                $vacreqRoles = $em->getRepository(User::class)->
                    findUserRolesBySiteAndPartialRoleName($approver, "vacreq", $partialRoleName, null, false);

                //select all requests with institution is equal or under vacreqRole institution.
                if( count($vacreqRoles) > 0 ) {
                    $instCriterionArr = array();
                    $addedNodes = array();
                    foreach( $vacreqRoles as $vacreqRole ) {
                        $roleInst = $vacreqRole->getInstitution();
                        //echo "roleInst=".$roleInst."<br>";
                        if( !in_array($roleInst->getId(), $addedNodes) ) {
                            $addedNodes[] = $roleInst->getId();
                            //regular institution
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                            $instCriterionArr[] = $em->getRepository(Institution::class)->
                                selectNodesUnderParentNode($roleInst,"institution",false);
                            //regular tentativeInstitution
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                            $instCriterionArr[] = $em->getRepository(Institution::class)->
                                selectNodesUnderParentNode($roleInst,"tentativeInstitution",false);
                        }
                    }
                    if( count($instCriterionArr) > 0 ) {
                        $instCriteriaStr = implode(" OR ",$instCriterionArr);
                        $dql->andWhere($instCriteriaStr);
                    }
                }
            }
            else {
                //echo "No approver <br>";
            }
        }

        //process filter
        $filterRes = $this->processFilter( $dql, $request, $params );
        $filterform = $filterRes['form'];
        $dqlParameters = $filterRes['dqlParameters'];
        $filtered = $filterRes['filtered'];
        $requestTypeAbbreviation = $filterRes['requestTypeAbbreviation'];

        $limit = 30;
        $query = $dql->getQuery(); //$query = $em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        //echo "dql=".$dql."<br>";
        //echo "query=".$query->getSql()."<br>";

        $paginationParams = array(
            'defaultSortFieldName' => 'request.firstDayAway', //createDate
            'defaultSortDirection' => 'DESC',
            'wrap-queries'=>true //use "doctrine/orm": "v2.4.8". ~2.5 causes error: Cannot select distinct identifiers from query with LIMIT and ORDER BY on a column from a fetch joined to-many association. Use walker.
        );

        //if( $routeName == 'vacreq_incomingrequests' ) {
            $paginationParams['defaultSortFieldName'] = 'request.createDate'; //'request.id';
        //}


        $paginator  = $this->container->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );

        //echo "num=".$pagination[0]['thisRequestTotalDays']."<br>";
        //print_r($pagination[0]);
        //echo "count req=".count($pagination)."<br>";
        //exit('1');

        $indexTitle = $params['title'];
        if( $requestTypeAbbreviation == "carryover" ) {
            if( $routeName == "vacreq_incomingrequests" ) {
                $indexTitle = "Incoming Carry Over Requests";
            } else {
                $indexTitle = "My Carry Over Requests";
            }
        }

        $totalItemCount = $pagination->getTotalItemCount();
        if( $totalItemCount > 0 ) {
            $paginationData = $pagination->getPaginationData();
            $indexTitle = $indexTitle." (".$paginationData['firstItemNumber']."-".
                $paginationData['lastItemNumber']." of ".$totalItemCount." matching)";
        }

        $pageTitle = "<p>".$indexTitle ."</p>";

        $downloadLink = NULL;
        $downloadLink2 = NULL;
        $downloadLink3 = NULL;

        if( $requestTypeAbbreviation != "carryover" ) {
            $matchingIds = $vacreqUtil->getVacReqIdsArrByDqlParameters($dql, $dqlParameters);
            //echo "matchingIdsArr count=".count($matchingIdsArr)."<br>";
            //print_r($matchingIdsArr);
            //$limitMatching = 1000;
            $limitMatching = null;
            if ($limitMatching && count($matchingIds) > $limitMatching) {
                $pageTitle = $pageTitle .
                    "Unable to export this quantity of items. ".
                    "Please use filters (such as dates) to decrease the number of matching items below ".
                    $limitMatching;
            } else {
                if ($matchingIds) {

                    $warningOnclick = NULL;
                    $matchingIdsCount = count($matchingIds);
                    if ($matchingIdsCount > 1000) {
                        $minutes = round(($matchingIdsCount / 30) / 60);
                        if ($minutes < 1) {
                            $minutesStr = "1 minute";
                        } else {
                            $minutesStr = $minutes . " minutes";
                        }
                        $warningOnclickMsg = "There are $matchingIdsCount requests to process." .
                            "This might take " . $minutesStr . " (or even to fail) to generate the report." .
                            " If generation will fail, you can try to reduce the number of users by a filter." .
                            " Do you want to continue?";
                        //<button type="button" class="btn btn-default" data-dismiss="modal" onclick="alert('thank you');">Close</button>
                        //$warningOnclick = 'data-dismiss="modal" onclick="alert(\''.$warningOnclickMsg.'\');"';
                        $warningOnclick = 'onsubmit="return confirm(\'' . $warningOnclickMsg . '\');"';
                    }

//                    if (0) {
//                        $downloadUrl = $this->container->get('router')->generate(
//                            'vacreq_download_spreadsheet_get_ids',
//                            array(
//                                'ids' => implode("-", $matchingIds),
//                            ),
//                            UrlGeneratorInterface::ABSOLUTE_URL
//                        );
//                        $downloadLink = '<a href="' . $downloadUrl . '" target="_blank"><i class="fa fa-file-excel-o"></i>download in Excel</a>';
//                        $pageTitle = $indexTitle . " (" . $downloadLink . ")";
//                    }

//                    $downloadUrl = $this->container->get('router')->generate(
//                        'vacreq_download_spreadsheet',
//                        array(),
//                        UrlGeneratorInterface::ABSOLUTE_URL
//                    );
                    $downloadUrl = $userTenantUtil->routerGenerateExternalChanelWrapper('vacreq_download_spreadsheet');
                    $downloadLink =
                        '<form action="' . $downloadUrl . '" method="post" style="display: inline;"' .
                        ' ' . $warningOnclick .
                        '>' .
                        '<input type="hidden" name="ids" value="' . implode("-", $matchingIds) . '">' .
                        '<input class="btn" style="background-color: #D3D3D3;" type="submit" value="Download request list" ' .
                        //$warningOnclick.
                        '>' .
                        '</form>';
                }//if $matchingIds
            }//if else count($matchingIds) > 0

            //////////// Summary Report By Name, make in the same line //////////////
            $matchingUserIds = $vacreqUtil->getVacReqUserIdsArrByDqlParameters($dql, $dqlParameters);
            if ($matchingUserIds) {

                $warningOnclick = NULL;
                $matchingUserIdsCount = count($matchingUserIds);
                if ($matchingUserIdsCount > 150) {
                    $minutes = round(($matchingUserIdsCount/5) / 60);
                    if ($minutes < 1) {
                        $minutesStr = "1 minute";
                    } else {
                        $minutesStr = $minutes . " minutes";
                    }
                    $warningOnclickMsg = "There are $matchingUserIdsCount faculty members to process." .
                        "This might take " . $minutesStr . " (or even to fail) to generate the report." .
                        " If generation will fail, you can try to reduce the number of users by a filter." .
                        " Do you want to continue?";
                    //<button type="button" class="btn btn-default" data-dismiss="modal" onclick="alert('thank you');">Close</button>
                    //$warningOnclick = 'data-dismiss="modal" onclick="alert(\''.$warningOnclickMsg.'\');"';
                    $warningOnclick = 'onsubmit="return confirm(\'' . $warningOnclickMsg . '\');"';
                }

                //current academic year
                //TODO: Summary Report By Name, make in the same line
                $currentYearRangeStr = $vacreqUtil->getCurrentAcademicYearRange();
//                $downloadUrl2 = $this->container->get('router')->generate(
//                    'vacreq_download_summary_report_spreadsheet',
//                    array(),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );
                $downloadUrl2 = $userTenantUtil->routerGenerateExternalChanelWrapper('vacreq_download_summary_report_spreadsheet');
                $downloadLink2 =
                    '<form action="' . $downloadUrl2 . '" method="post" style="display: inline;"' .
                    ' ' . $warningOnclick .
                    '>' .
                    '<input type="hidden" name="year" value="' . $currentYearRangeStr . '">' .
                    '<input type="hidden" name="ids" value="' . implode("-", $matchingUserIds) . '">' .
                    '<input class="btn" style="background-color: #D3D3D3;" type="submit" value="Download summary for ' .
                    $currentYearRangeStr . '" ' .
                    //$warningOnclick.
                    '>' .
                    '</form>';

                //previous academic year
                $previousYearRangeStr = $vacreqUtil->getPreviousAcademicYearRange();
//                $downloadUrl3 = $this->container->get('router')->generate(
//                    'vacreq_download_summary_report_spreadsheet',
//                    array(),
//                    UrlGeneratorInterface::ABSOLUTE_URL
//                );
                $downloadUrl3 = $userTenantUtil->routerGenerateExternalChanelWrapper('vacreq_download_summary_report_spreadsheet');
                $downloadLink3 =
                    '<form action="' . $downloadUrl3 . '" method="post" style="display: inline;"' .
                    ' ' . $warningOnclick .
                    '>' .
                    '<input type="hidden" name="year" value="' . $previousYearRangeStr . '">' .
                    '<input type="hidden" name="ids" value="' . implode("-", $matchingUserIds) . '">' .
                    '<input class="btn" style="background-color: #D3D3D3;" type="submit" value="Download summary for ' .
                    $previousYearRangeStr . '" ' .
                    //$warningOnclick.
                    '>' .
                    '</form>';
            } //if( $matchingUserIds ) {

            //$pageTitle = $indexTitle . " <p class='display: inline;'>" . $downloadLink . $downloadLink2 . $downloadLink3 . "</p>";

            if( $downloadLink || $downloadLink2 || $downloadLink3 ) {
                $pageTitle = $pageTitle .
                    //"<p>".$indexTitle ."</p>".
                    " <p style='display: inline-block !important;'>" .
                    $downloadLink . " " .
                    $downloadLink2 . " " .
                    $downloadLink3 .
                    "</p>";
                //$pageTitle = $pageTitle . " <div>" . $downloadLink2 . " ". $downloadLink3 . "</div>";
            }

//        $items = $pagination->getItems();
//        echo "item count=".count($items)."<br>";
//        foreach($items as $item) {
//            echo "item=".$item[0]."<br>";
//            //print_r($item);
//        }

        } //if not "carryover"

        return array(
            'filterform' => $filterform,
            'vacreqfilter' => $filterform->createView(),
            'pagination' => $pagination,
            'sitename' => $sitename,
            'filtered' => $filtered,
            'routename' => $routeName,
            //'forceShowAllRows' => $forceShowAllRows,
            'title' => $indexTitle,
            'pageTitle' => $pageTitle,
            'requestTypeAbbreviation' => $requestTypeAbbreviation,
            //'totalApprovedDaysString' => $params['totalApprovedDaysString']
        );
    }

    public function processFilter( $dql, $request, $params ) {

        $currentUser = $this->getUser();
        $vacreqUtil = $this->container->get('vacreq_util');

        $dqlParameters = array();
        $filterRes = array();
        $filtered = false;

        //////////////////// get list of users with "unknown" user ////////////////////
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
        $repository = $this->getDoctrine()->getRepository(User::class);
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
        //$queryFilterUser = $em->createQuery($dqlFilterUser);
        $queryFilterUser = $dqlFilterUser->getQuery();
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

        $params['em'] = $em;

        //get request type
        $params['requestTypeAbbreviation'] = "business-vacation";
        $requestParams = $request->query->all();
        $requestTypeId = null;
        if( array_key_exists("filter", $requestParams) ) {
            if( array_key_exists("requestType", $requestParams["filter"]) ) {
                $requestTypeId = $requestParams["filter"]["requestType"];
            }
        }
        //echo "requestTypeId=".$requestTypeId."<br>";
        $requestTypeAbbreviation = null;
        if( $requestTypeId ) {
        //process.py script: replaced namespace by ::class: ['AppVacReqBundle:VacReqRequestTypeList'] by [VacReqRequestTypeList::class]
            $requestType = $em->getRepository(VacReqRequestTypeList::class)->find($requestTypeId);
            if (!$requestType) {
                throw $this->createNotFoundException('Unable to find Request Type by id=' . $requestTypeId);
            }
            //echo "requestTypeAbbreviation=".$requestType->getAbbreviation()."<br>";
            $requestTypeAbbreviation = $requestType->getAbbreviation();
            $params['requestTypeAbbreviation'] = $requestTypeAbbreviation; //$requestType->getAbbreviation();
        }

        //institutional group
//        if( $request->get('_route') == "vacreq_myrequests" ) {
//            $groupParams = array('roleSubStrArr'=>array('ROLE_VACREQ_SUBMITTER'));
//        }
//        if( $request->get('_route') == "vacreq_incomingrequests" ) {
//            //$groupParams = array('roleSubStrArr'=>array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR'));
//            if( $params['requestTypeAbbreviation'] == "business-vacation" ) {
//                $groupParams = array('roleSubStrArr'=>array('ROLE_VACREQ_APPROVER'));
//            } else {
//                $groupParams = array('roleSubStrArr'=>array('ROLE_VACREQ_SUPERVISOR'));
//            }
//        }
//        $organizationalInstitutions = $vacreqUtil->getVacReqOrganizationalInstitutions($currentUser,$groupParams);//, $params['requestTypeAbbreviation']);

        //get submitter groups: VacReqRequest, create
        $groupParams = array();
        $groupParams['statusArr'] = array('default','user-added');
        if( $request->get('_route') == "vacreq_myrequests" ) {
            $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'create');
        }
        if( $request->get('_route') == "vacreq_incomingrequests" ) {
            if( $params['requestTypeAbbreviation'] == "business-vacation" ) {
                $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
                if( $this->isGranted('ROLE_VACREQ_ADMIN') == false ) {
                    $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
                }
            } else {
                $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
            }
        }
        $user = $this->getUser();
        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);

        //testing
//        echo "requestTypeAbbreviation=".$requestTypeAbbreviation."<br>";
//        foreach( $organizationalInstitutions as $organizationalInstitution ) {
//            echo "organizationalInstitution=".$organizationalInstitution."<br>";
//        }

        if(
            count($organizationalInstitutions) == 0 &&
            ($requestTypeAbbreviation && $requestTypeAbbreviation != "carryover")
        ) {
            if( $this->isGranted('ROLE_VACREQ_ADMIN') ) {
                $groupPageUrl = $this->generateUrl(
                    "vacreq_approvers",
                    array(),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $warningMsg = "No submitter/approver groups have been set up. Such groups can be set up " .
                    '<a href="' . $groupPageUrl . '" target="_blank">here</a>.' .
                    " Once they are set up, this page will show cumulative summary data.";
            } else {
                //regular user
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                $adminUsers = $em->getRepository(User::class)->findUserByRole("ROLE_VACREQ_ADMIN", "infos.lastName", true);
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

                $warningMsg = "No submitter/approver groups have been set up." .
                    " Please contact the site administrator to create a group and/or get a Submitter role for your account.".$emailStr;
                    " Once they are set up, this page will show cumulative summary data.";
            }
            //Flash
            $this->addFlash(
                'warning',
                $warningMsg
            );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $params['organizationalInstitutions'] = $userServiceUtil->flipArrayLabelValue($organizationalInstitutions); //flipped //$organizationalInstitutions;

        //tentative institutions
        $tentativeGroupParams = array(); //'asObject'=>true
        $tentativeGroupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
        if( $this->isGranted('ROLE_VACREQ_ADMIN') == false ) {
            $tentativeGroupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
        }
        $tentativeInstitutions = $vacreqUtil->getGroupsByPermission($user,$tentativeGroupParams);
        //testing
//        foreach( $tentativeInstitutions as $tentativeInstitution ) {
//            echo "tentativeInstitution=".$tentativeInstitution."<br>";
//        }

        //tooltip for Academic Year:
        //"Academic Year Start (for [Current Academic Year, show as 2015-2016], pick [first/starting year, show as 2015]"
        $previousYear = date("Y") - 1;
        $currentYear = date("Y");
        $yearRange = $previousYear."-".$currentYear;
        $academicYearTooltip = "Academic Year Start (for ".$yearRange.", pick ".$previousYear.")";
        $params['academicYearTooltip'] = $academicYearTooltip;

        $params['routeName'] = $request->get('_route');

        $approverRole = false;
        if( $this->isGranted('ROLE_VACREQ_APPROVER') ||
            $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            $approverRole = true;
        }
        $params['approverRole'] = $approverRole;

        $supervisorRole = false;
        if( $this->isGranted('ROLE_VACREQ_SUPERVISOR') ||
            $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            $supervisorRole = true;
        }
        $params['supervisor'] = $supervisorRole;

        //create filter form
        $filterform = $this->createForm(VacReqFilterType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));

        //Use the Symfony\Component\Form\Form::handleRequest method instead. If you want to test whether the form was submitted separately, you can use the Symfony\Component\Form\Form::isSubmitted method
        //$filterform->submit($request);
        $filterform->handleRequest($request);

        //echo "<pre>";
        //print_r($filterform['startdate']);
        //echo "</pre>";

        $startdate = $filterform['startdate']->getData();
        $enddate = $filterform['enddate']->getData();

        ////////////// Optional filters //////////////

        //filter type. By default select only business-vacation type requests.
        $requestTypeAbbreviation = "business-vacation";
        if( $filterform->has('requestType') ) {
            $requestType = $filterform['requestType']->getData();
            //echo "requestType=".$requestType."<br>";
            if( $requestType && $requestType->getAbbreviation() ) {
                $requestTypeAbbreviation = $requestType->getAbbreviation();
            }
        }
        //echo "requestTypeAbbreviation=(".$requestTypeAbbreviation.")<br>";
        //$dql->andWhere("requestType.abbreviation = '".$requestTypeAbbreviation."'");
        $dql->andWhere("requestType.abbreviation = :requestTypeAbbreviation");
        $dqlParameters['requestTypeAbbreviation'] = $requestTypeAbbreviation;

        //$subjectUser = ( array_key_exists('user', $filterform) ? $filterform['user']->getData() : null);
        if( $filterform->has('user') ) {
            $subjectUser = $filterform['user']->getData();
        } else {
            $subjectUser = null;
        }
        //echo "user=".$subjectUser."<br>";

        if( $filterform->has('submitter') ) {
            $submitter = $filterform['submitter']->getData();
        } else {
            $submitter = null;
        }

        if( $filterform->has('organizationalInstitutions') ) {
            $groups = $filterform['organizationalInstitutions']->getData();
        } else {
            $groups = null;
        }

        if( $filterform->has('academicYear') ) {
            $academicYear = $filterform['academicYear']->getData();
        } else {
            $academicYear = null;
        }

        if( $filterform->has('vacationRequest') ) {
            $vacationRequest = $filterform['vacationRequest']->getData();
        } else {
            $vacationRequest = null;
        }

        if( $filterform->has('businessRequest') ) {
            $businessRequest = $filterform['businessRequest']->getData();
        } else {
            $businessRequest = null;
        }
        ////////////// EOF Optional filters //////////////


        //$completed = $filterform['completed']->getData();
        $completed = null;
        $pending = $filterform['pending']->getData();
        $approved = $filterform['approved']->getData();
        $rejected = $filterform['rejected']->getData();

        $cancellationRequest = $filterform['cancellationRequest']->getData();
        $cancellationRequestApproved = $filterform['cancellationRequestApproved']->getData();
        $cancellationRequestRejected = $filterform['cancellationRequestRejected']->getData();

        //$year = $filterform['year']->getData();
        //echo "userID=".$subjectUser."<br>";

        if( $subjectUser && $subjectUser->getId() ) {
            $where = "";
            if( $where != "" ) {
                $where .= " OR ";
            }
            if( $subjectUser ) {
                //$where .= "request.user=".$subjectUser->getId();
                $where .= "request.user=:subjectUserId";
                $dqlParameters['subjectUserId'] = $subjectUser->getId();
            } else {
                $where .= "request.user IS NULL";
            }
            $dql->andWhere($where);

            $filtered = true;
        }

        if( $submitter && $submitter->getId() ) {
            $where = "";
            if( $where != "" ) {
                $where .= " OR ";
            }
            if( $submitter ) {
                //$where .= "request.submitter=".$submitter->getId();
                $where .= "request.submitter=:submitterUserId";
                $dqlParameters['submitterUserId'] = $submitter->getId();
            } else {
                $where .= "request.submitter IS NULL";
            }
            $dql->andWhere($where);

            $filtered = true;
        }

        if( $groups && $groups->getId() ) {
            //echo "groupId=".$groups->getId()."<br>";
            $where = "";
            if( $where != "" ) {
                $where .= " OR ";
            }
            if( $groups ) {
                if(0) {
                    //add institution hierarchy: "Pathology and Laboratory Medicine" institution is under "WCM-NYP Collaboration" institution.
                    //$where .= "institution=".$groups->getId();
                    //$where .= $em->getRepository('AppUserdirectoryBundle:Institution')->selectNodesUnderParentNode($groups,"institution",false);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    $where .= $em->getRepository(Institution::class)->getCriterionStrForCollaborationsByNode(
                        $groups,
                        "institution",
                        array("Union", "Intersection", "Untrusted Intersection"),
                        true,
                        false
                    );
//                $where .= $em->getRepository('AppUserdirectoryBundle:Institution')->getCriterionStrUnderlyingCollaborationsByNode(
//                    $groups,
//                    "institution",
//                    array("Union", "Intersection", "Untrusted Intersection")
//                //,true
//                //,false
//                );
                } else {
                    $where .= "institution.id = :institutionId";
                    $dqlParameters['institutionId'] = $groups->getId();
                }
            } else {
                $where .= "institution IS NULL";
            }
            //echo "group where=".$where."<br>";
            $dql->andWhere($where);

            $filtered = true;
        }

        //echo "groups=".$groups."<br>";
        //if( $groups == null && $request->get('_route') == "vacreq_incomingrequests" ) {
        if( $request->get('_route') == "vacreq_incomingrequests" ) {

            $instWhereArr = array();

            if( $groups ) {
                $instArr = array();
                foreach ($organizationalInstitutions as $instId => $instNameStr) {
                    $instArr[] = $instId;
                }
                if (count($instArr) > 0) {
                    $instWhereArr[] = "institution.id IN (" . implode(",", $instArr) . ")";
                }
            }

            $tentativeInstArr = array();
            foreach( $tentativeInstitutions as $instId => $instNameStr ) {
                //echo "tentativeInstitution: id=".$instId."; name=".$instNameStr."<br>";
                $tentativeInstArr[] = $instId;
            }
            if( count($tentativeInstArr) > 0 ) {
                $instWhereArr[] = "(tentativeInstitution.id IN (" . implode(",", $tentativeInstArr) . ") OR tentativeInstitution.id is NULL)";
            }

            if( count($instWhereArr) > 0 ) {
                $dql->andWhere(implode(" AND ", $instWhereArr)); //OR
            }
        }

        if( $academicYear ) {

            $academicYear = $academicYear->format('Y');
            $academicYear = $academicYear + 1; //the user should pick the start of the academic year (2015) to see 2015-2016
            //echo "academicYear=".$academicYear."<br>";

            //echo "<br>Request Start: getEdgeAcademicYearDate<br>";
            $startAcademicYearStr = $vacreqUtil->getEdgeAcademicYearDate( $academicYear, "Start" );
            $startAcademicYearDate = new \DateTime($startAcademicYearStr);
            $startAcademicYearDate = $this->convertFromUserTimezonetoUTC($startAcademicYearDate,$currentUser);
            $startAcademicYearDate->setTime(00, 00, 00);
            //echo "start year date:".$startAcademicYearDate->format('Y-m-d H:i:s')."<br>";

            //echo "<br>Request End: getEdgeAcademicYearDate<br>";
            $endAcademicYearStr = $vacreqUtil->getEdgeAcademicYearDate( $academicYear, "End" );
            $endAcademicYearDate = new \DateTime($endAcademicYearStr);
            $endAcademicYearDate = $this->convertFromUserTimezonetoUTC($endAcademicYearDate,$currentUser);
            $endAcademicYearDate->setTime(23, 59, 59);
            //echo "end year date:".$endAcademicYearDate->format('Y-m-d H:i:s')."<br>";

            //requests with firstDayAway or firstDayBackInOffice inside the academic year
            $dql->andWhere("(request.firstDayAway between :createDateStart AND :createDateEnd OR request.firstDayBackInOffice between :createDateStart AND :createDateEnd)");

            $dqlParameters['createDateStart'] = $startAcademicYearDate;
            $dqlParameters['createDateEnd'] = $endAcademicYearDate;

            $filtered = true;
        }

        //echo "startdate=".$startdate."<br>";
        if( $startdate ) {
            $dql->andWhere("request.createDate >= :startdate");

            $startdate = $this->convertFromUserTimezonetoUTC($startdate,$currentUser);
            $startdate->setTime(00, 00, 00);
            $dqlParameters['startdate'] = $startdate;

            $filtered = true;
        }

        if( $enddate ) {
            $dql->andWhere("request.createDate <= :enddate");

            $enddate = $this->convertFromUserTimezonetoUTC($enddate,$currentUser);
            $enddate->setTime(23, 59, 59);
            $dqlParameters['enddate'] = $enddate;

            $filtered = true;
        }

        if( $vacationRequest || $businessRequest ) {
            $requestStatusCriterionArr = array();
            if( $businessRequest ) {
                $requestStatusCriterionArr[] = "requestBusiness.startDate IS NOT NULL";
            }
            if( $vacationRequest ) {
                $requestStatusCriterionArr[] = "requestVacation.startDate IS NOT NULL";
            }

            if( count($requestStatusCriterionArr) > 0 ) {
                $dql->andWhere(implode(" OR ", $requestStatusCriterionArr));
                $filtered = true;
            }
        }

        if( $completed || $pending || $rejected || $approved ) {
            $requestStatusCriterionArr = array();
            if( $requestTypeAbbreviation == "business-vacation" ) {
                if ($completed) {
                    $requestStatusCriterionArr[] = "requestBusiness.status='rejected' OR requestVacation.status='rejected' OR requestBusiness.status='approved' OR requestVacation.status='approved'";
                }
                if ($pending) {
                    $requestStatusCriterionArr[] = "requestBusiness.status='pending' OR requestVacation.status='pending'";
                }
                if ($rejected) {
                    $requestStatusCriterionArr[] = "requestBusiness.status='rejected' OR requestVacation.status='rejected'";
                }
                if ($approved) {
                    $requestStatusCriterionArr[] = "requestBusiness.status='approved' OR requestVacation.status='approved'";
                }
                if ($cancellationRequest) {
                    $requestStatusCriterionArr[] = "request.extraStatus = 'Cancellation Requested'";
                }
                if ($cancellationRequestApproved) {
                    $requestStatusCriterionArr[] = "request.extraStatus = 'Cancellation Approved (Canceled)'";
                }
                if ($cancellationRequestRejected) {
                    $requestStatusCriterionArr[] = "request.extraStatus = 'Cancellation Denied (Approved)'";
                }
            }
            if( $requestTypeAbbreviation == "carryover" ) {
                if ($completed) {
                    $requestStatusCriterionArr[] = "request.status='rejected' OR request.status='approved'";
                }
                if ($pending) {
                    $requestStatusCriterionArr[] = "request.status='pending'";
                }
                if ($rejected) {
                    $requestStatusCriterionArr[] = "request.status='rejected'";
                }
                if ($approved) {
                    $requestStatusCriterionArr[] = "request.status='approved'";
                }
                if ($cancellationRequestApproved) {
                    $requestStatusCriterionArr[] = "request.status='canceled'";
                }
            }

            if( count($requestStatusCriterionArr) > 0 ) {
                $dql->andWhere(implode(" OR ", $requestStatusCriterionArr));
                $filtered = true;
            }
        }

//        if( $cancellationRequest || $cancellationRequestApproved || $cancellationRequestRejected ) {
//            $requestStatusCriterionArr = array();
//            if ($cancellationRequest) {
//                $requestStatusCriterionArr[] = "request.extraStatus = 'Cancellation Requested'";
//            }
//            if ($cancellationRequestApproved) {
//                $requestStatusCriterionArr[] = "request.extraStatus = 'Cancellation Approved (Canceled)'";
//            }
//            if ($cancellationRequestRejected) {
//                $requestStatusCriterionArr[] = "request.extraStatus = 'Cancellation Denied (Approved)'";
//            }
//
//            $dql->andWhere(implode(" OR ",$requestStatusCriterionArr));
//            $filtered = true;
//        }

        $filterRes['form'] = $filterform;
        $filterRes['dqlParameters'] = $dqlParameters;
        $filterRes['filtered'] = $filtered;
        $filterRes['requestTypeAbbreviation'] = $requestTypeAbbreviation;


        return $filterRes;
    }

    //convert given datetime from user's timezone to UTC. Use UTC in DB query. 12:00 => 17:00 +5
    public function convertFromUserTimezonetoUTC($datetime,$user) {

        //$user_tz = 'America/New_York';
        $user_tz = $user->getPreferences()->getTimezone();

        //echo "input datetime=".$datetime->format('Y-m-d H:i')."<br>";
        $datetimeTz = new \DateTime($datetime->format('Y-m-d'), new \DateTimeZone($user_tz) );
        $datetimeUTC = $datetimeTz->setTimeZone(new \DateTimeZone('UTC'));
        //echo "output datetime=".$datetimeUTC->format('Y-m-d H:i')."<br>";

        return $datetimeUTC;
    }

}
