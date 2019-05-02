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

namespace Oleg\VacReqBundle\Controller;

use Oleg\UserdirectoryBundle\Entity\User;
use Oleg\VacReqBundle\Entity\VacReqRequest;
use Oleg\VacReqBundle\Form\VacReqFilterType;
use Oleg\VacReqBundle\Form\VacReqRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//vacreq site

class RequestIndexController extends Controller
{

    /**
     * Creates a new VacReqRequest entity.
     *
     * @Route("/my-requests/", name="vacreq_myrequests")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Request:index.html.twig")
     */
    public function myRequestsAction(Request $request)
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        //$vacreqUtil = $this->get('vacreq_util');

        //$em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('OlegVacReqBundle:VacReqRequest')->findAll();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        //calculate approved vacation days in total.
        //$totalApprovedDaysString = $vacreqUtil->getApprovedDaysString($user);

        $params = array(
            'sitename' => $this->container->getParameter('vacreq.sitename'),
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


    /**
     * Creates a new VacReqRequest entity.
     *
     * @Route("/incoming-requests/", name="vacreq_incomingrequests")
     * @Method({"GET", "POST"})
     * @Template("OlegVacReqBundle:Request:index.html.twig")
     */
    public function incomingRequestsAction(Request $request)
    {

        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') && false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $params = array(
            'sitename' => $this->container->getParameter('vacreq.sitename'),
            'approver' => $user,
            'title' => "Incoming Business Travel & Vacation Requests",
            'filterShowUser' => true
        );
        return $this->listRequests($params, $request);
    }




    public function listRequests( $params, $request ) {

        $vacreqUtil = $this->get('vacreq_util');
        $em = $this->getDoctrine()->getManager();

        $sitename = ( array_key_exists('sitename', $params) ? $params['sitename'] : null);
        $subjectUser = ( array_key_exists('subjectUser', $params) ? $params['subjectUser'] : null); //logged in user
        $approver = ( array_key_exists('approver', $params) ? $params['approver'] : null);

        $routeName = $request->get('_route');

        $repository = $em->getRepository('OlegVacReqBundle:VacReqRequest');
        $dql =  $repository->createQueryBuilder("request");

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
        if( false == $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
            if( $approver ) {
                $partialRoleName = "ROLE_VACREQ_";  //"ROLE_VACREQ_APPROVER"
                $vacreqRoles = $em->getRepository('OlegUserdirectoryBundle:User')->
                    findUserRolesBySiteAndPartialRoleName($approver, "vacreq", $partialRoleName, null, false);

//                $instArr = array();
                //foreach( $vacreqRoles as $vacreqRole ) {
                    //$instArr[] = $vacreqRole->getInstitution()->getId();
                    //echo "vacreqRole=".$vacreqRole."<br>";
                //}
//                if( count($instArr) > 0 ) {
//                    //$dql->andWhere("institution.id IN (" . implode(",", $instArr) . ")");
//                }

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
                            $instCriterionArr[] = $em->getRepository('OlegUserdirectoryBundle:Institution')->
                                selectNodesUnderParentNode($roleInst,"institution",false);
                            //regular tentativeInstitution
                            $instCriterionArr[] = $em->getRepository('OlegUserdirectoryBundle:Institution')->
                                selectNodesUnderParentNode($roleInst,"tentativeInstitution",false);
                        }
                    }
                    if( count($instCriterionArr) > 0 ) {
                        $instCriteriaStr = implode(" OR ",$instCriterionArr);
                        $dql->andWhere($instCriteriaStr);
                    }
                }
            }
        }

        //process filter
        $filterRes = $this->processFilter( $dql, $request, $params );
        $filterform = $filterRes['form'];
        $dqlParameters = $filterRes['dqlParameters'];
        $filtered = $filterRes['filtered'];
        $requestTypeAbbreviation = $filterRes['requestTypeAbbreviation'];

        $limit = 30;
        $query = $em->createQuery($dql);

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

        if( $routeName == 'vacreq_incomingrequests' ) {
            $paginationParams['defaultSortFieldName'] = 'request.createDate'; //'request.id';
        }


        $paginator  = $this->get('knp_paginator');
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

        $pageTitle = $indexTitle;

        if( $requestTypeAbbreviation != "carryover" ) {
            $matchingIds = $vacreqUtil->getVacReqIdsArrByDqlParameters($dql, $dqlParameters);
            //echo "matchingIdsArr count=".count($matchingIdsArr)."<br>";
            //print_r($matchingIdsArr);
            //$limitMatching = 1000;
            $limitMatching = null;
            if( $limitMatching && count($matchingIds) > $limitMatching ) {
                $pageTitle = $indexTitle . "<br>Unable to export this quantity of items. Please use filters (such as dates) to decrease the number of matching items below $limitMatching.";
            } else {
                if ($matchingIds) {

                    if(0) {
                        $downloadUrl = $this->container->get('router')->generate(
                            'vacreq_download_spreadsheet_get_ids',
                            array(
                                'ids' => implode("-", $matchingIds),
                            ),
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                        $downloadLink = '<a href="' . $downloadUrl . '" target="_blank"><i class="fa fa-file-excel-o"></i>download in Excel</a>';
                        $pageTitle = $indexTitle . " (" . $downloadLink . ")";
                    }

                    $downloadUrl = $this->container->get('router')->generate(
                        'vacreq_download_spreadsheet',
                        array(),
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $downloadLink =
                        '<form action="'.$downloadUrl.'" method="post"> 
                        <input type="hidden" name="ids" value="'.implode("-", $matchingIds).'">
                        <input class="btn" type="submit" value="download in Excel">
                        </form>';

                    $pageTitle = $indexTitle . " <p>" . $downloadLink . "</p>";
                }
            }
        }

//        $items = $pagination->getItems();
//        echo "item count=".count($items)."<br>";
//        foreach($items as $item) {
//            echo "item=".$item[0]."<br>";
//            //print_r($item);
//        }

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

    public function processFilter( $dql, $request, $params ) {

        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $vacreqUtil = $this->get('vacreq_util');

        $dqlParameters = array();
        $filterRes = array();
        $filtered = false;

        //////////////////// get list of users with "unknown" user ////////////////////
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
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
        if( $requestTypeId ) {
            $requestType = $em->getRepository('OlegVacReqBundle:VacReqRequestTypeList')->find($requestTypeId);
            if (!$requestType) {
                throw $this->createNotFoundException('Unable to find Request Type by id=' . $requestTypeId);
            }
            //echo "requestTypeAbbreviation=".$requestType->getAbbreviation()."<br>";
            $params['requestTypeAbbreviation'] = $requestType->getAbbreviation();
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
                if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') == false ) {
                    $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
                }
            } else {
                $groupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus-carryover');
            }
        }
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($user,$groupParams);

        //testing
        //foreach( $organizationalInstitutions as $organizationalInstitution ) {
            //echo "organizationalInstitution=".$organizationalInstitution."<br>";
        //}

        if( count($organizationalInstitutions) == 0 ) {
            if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') ) {
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
                $adminUsers = $em->getRepository('OlegUserdirectoryBundle:User')->findUserByRole("ROLE_VACREQ_ADMIN", "infos.lastName", true);
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
            $this->get('session')->getFlashBag()->add(
                'warning',
                $warningMsg
            );
        }

        $userServiceUtil = $this->get('user_service_utility');
        $params['organizationalInstitutions'] = $userServiceUtil->flipArrayLabelValue($organizationalInstitutions); //flipped //$organizationalInstitutions;

        //tentative institutions
        $tentativeGroupParams = array(); //'asObject'=>true
        $tentativeGroupParams['permissions'][] = array('objectStr'=>'VacReqRequest','actionStr'=>'changestatus');
        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN') == false ) {
            $tentativeGroupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
        }
        $tentativeInstitutions = $vacreqUtil->getGroupsByPermission($user,$tentativeGroupParams);
        //testing
        //foreach( $tentativeInstitutions as $tentativeInstitution ) {
            //echo "tentativeInstitution=".$tentativeInstitution."<br>";
        //}

        //tooltip for Academic Year:
        //"Academic Year Start (for [Current Academic Year, show as 2015-2016], pick [first/starting year, show as 2015]"
        $previousYear = date("Y") - 1;
        $currentYear = date("Y");
        $yearRange = $previousYear."-".$currentYear;
        $academicYearTooltip = "Academic Year Start (for ".$yearRange.", pick ".$previousYear.")";
        $params['academicYearTooltip'] = $academicYearTooltip;

        $params['routeName'] = $request->get('_route');

        $approverRole = false;
        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_APPROVER') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            $approverRole = true;
        }
        $params['approverRole'] = $approverRole;

        $supervisorRole = false;
        if( $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_SUPERVISOR') ||
            $this->get('security.authorization_checker')->isGranted('ROLE_VACREQ_ADMIN')
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
                $where .= "request.user=".$subjectUser->getId();
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
                $where .= "request.submitter=".$submitter->getId();
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
                //add institution hierarchy: "Pathology and Laboratory Medicine" institution is under "WCMC-NYP Collaboration" institution.
                //$where .= "institution=".$groups->getId();
                //$where .= $em->getRepository('OlegUserdirectoryBundle:Institution')->selectNodesUnderParentNode($groups,"institution",false);
                $where .= $em->getRepository('OlegUserdirectoryBundle:Institution')->getCriterionStrForCollaborationsByNode(
                    $groups,
                    "institution",
                    array("Union", "Intersection", "Untrusted Intersection"),
                    true,
                    false
                );
//                $where .= $em->getRepository('OlegUserdirectoryBundle:Institution')->getCriterionStrUnderlyingCollaborationsByNode(
//                    $groups,
//                    "institution",
//                    array("Union", "Intersection", "Untrusted Intersection")
//                //,true
//                //,false
//                );
            } else {
                $where .= "institution IS NULL";
            }
            //echo "group where=".$where."<br>";
            $dql->andWhere($where);

            $filtered = true;
        }

        if( $groups == null && $request->get('_route') == "vacreq_incomingrequests" ) {

            $instWhereArr = array();

            $instArr = array();
            foreach( $organizationalInstitutions as $instId => $instNameStr ) {
                $instArr[] = $instId;
            }
            if( count($instArr) > 0 ) {
                $instWhereArr[] = "institution.id IN (" . implode(",", $instArr) . ")";
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

            $startAcademicYearStr = $vacreqUtil->getEdgeAcademicYearDate( $academicYear, "Start" );
            $startAcademicYearDate = new \DateTime($startAcademicYearStr);
            $startAcademicYearDate = $this->convertFromUserTimezonetoUTC($startAcademicYearDate,$currentUser);
            $startAcademicYearDate->setTime(00, 00, 00);
            //echo "start year date:".$startAcademicYearDate->format('Y-m-d H:i:s')."<br>";

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
