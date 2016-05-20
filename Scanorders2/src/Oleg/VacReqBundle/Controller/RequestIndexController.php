<?php

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

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_USER') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $vacreqUtil = $this->get('vacreq_util');

        //$em = $this->getDoctrine()->getManager();
        //$entities = $em->getRepository('OlegVacReqBundle:VacReqRequest')->findAll();

        $user = $this->get('security.context')->getToken()->getUser();

        //calculate approved vacation days in total.
        $totalApprovedDaysString = $vacreqUtil->getApprovedDaysString($user);

        $params = array(
            'sitename' => $this->container->getParameter('vacreq.sitename'),
            'subjectUser' => $user,
            'title' => "My Business Travel & Vacation Requests",
            'totalApprovedDaysString' => $totalApprovedDaysString,
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

        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_APPROVER') && false == $this->get('security.context')->isGranted('ROLE_VACREQ_SUPERVISOR') ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $user = $this->get('security.context')->getToken()->getUser();

        //for incomingrequests if requestType is not set, set requestType to 'carryover' if a user has only SUPERVISOR role
//        $requestParams = $request->query->all();
//        $requestTypeId = $requestParams["filter"]["requestType"];
//        if( !$requestTypeId ) {
//            $em = $this->getDoctrine()->getManager();
//            $vacreqUtil = $this->get('vacreq_util');
//            $supervisorInstitutions = $vacreqUtil->getVacReqOrganizationalInstitutions($user, array('roleSubStrArr' => array('ROLE_VACREQ_SUPERVISOR')));
//            echo "supervisorInstitutions=" . count($supervisorInstitutions) . "<br>";
//            if (count($supervisorInstitutions) > 0) {
//                $requestType = $em->getRepository('OlegVacReqBundle:VacReqRequestTypeList')->findOneByAbbreviation("carryover");
//                return $this->redirectToRoute('vacreq_incomingrequests', array('filter[requestType]' => $requestType->getId()));
//            }
//        }

        $params = array(
            'sitename' => $this->container->getParameter('vacreq.sitename'),
            'approver' => $user,
            'title' => "Incoming Business Travel & Vacation Requests",
            'filterShowUser' => true
        );
        return $this->listRequests($params, $request);
    }




    public function listRequests( $params, $request ) {

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
        if( false == $this->get('security.context')->isGranted('ROLE_VACREQ_ADMIN') ) {
            if( $approver ) {
                $partialRoleName = "ROLE_VACREQ_";  //"ROLE_VACREQ_APPROVER"
                $approverRoles = $em->getRepository('OlegUserdirectoryBundle:User')->findUserRolesBySiteAndPartialRoleName($approver, "vacreq", $partialRoleName);
                $instArr = array();
                foreach( $approverRoles as $approverRole ) {
                    $instArr[] = $approverRole->getInstitution()->getId();
                }
                if( count($instArr) > 0 ) {
                    $dql->andWhere("institution.id IN (" . implode(",", $instArr) . ")");
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
            //'wrap-queries'=>true //use "doctrine/orm": "v2.4.8". ~2.5 causes error: Cannot select distinct identifiers from query with LIMIT and ORDER BY on a column from a fetch joined to-many association. Use walker.
        );

        if( $routeName == 'vacreq_incomingrequests' ) {
            $paginationParams['defaultSortFieldName'] = 'request.id';
        }


        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1),   /*page number*/
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

        if( $pagination->getTotalItemCount() > 0 ) {
            $paginationData = $pagination->getPaginationData();
            $indexTitle = $indexTitle." (".$paginationData['firstItemNumber']."-".$paginationData['lastItemNumber']." of ".$pagination->getTotalItemCount().")";
        }

        return array(
            'filterform' => $filterform,
            'vacreqfilter' => $filterform->createView(),
            'pagination' => $pagination,
            'sitename' => $sitename,
            'filtered' => $filtered,
            'routename' => $routeName,
            'title' => $indexTitle,
            'requestTypeAbbreviation' => $requestTypeAbbreviation
        );
    }

    public function processFilter( $dql, $request, $params ) {

        $currentUser = $this->get('security.context')->getToken()->getUser();
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
        $requestTypeId = $requestParams["filter"]["requestType"];
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
        if( $request->get('_route') == "vacreq_myrequests" ) {
            $groupParams = array('roleSubStrArr'=>array('ROLE_VACREQ_SUBMITTER'));
        }
        if( $request->get('_route') == "vacreq_incomingrequests" ) {
            $groupParams = array('roleSubStrArr'=>array('ROLE_VACREQ_APPROVER','ROLE_VACREQ_SUPERVISOR'));
        }
        $organizationalInstitutions = $vacreqUtil->getVacReqOrganizationalInstitutions($currentUser,$groupParams);//, $params['requestTypeAbbreviation']);
        $params['organizationalInstitutions'] = $organizationalInstitutions;

        //tooltip for Academic Year:
        //"Academic Year Start (for [Current Academic Year, show as 2015-2016], pick [first/starting year, show as 2015]"
        $previousYear = date("Y") - 1;
        $currentYear = date("Y");
        $yearRange = $previousYear."-".$currentYear;
        $academicYearTooltip = "Academic Year Start (for ".$yearRange.", pick ".$previousYear.")";
        $params['academicYearTooltip'] = $academicYearTooltip;

        $params['routeName'] = $request->get('_route');

        $vacreqUtil = $this->get('vacreq_util');
        $params['supervisor'] = $vacreqUtil->hasPartialRoleNameAndGroup('ROLE_VACREQ_SUPERVISOR');

        //create filter form
        $filterform = $this->createForm(new VacReqFilterType($params), null);

        $filterform->bind($request);
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
            $where = "";
            if( $where != "" ) {
                $where .= " OR ";
            }
            if( $groups ) {
                $where .= "request.institution=".$groups->getId();
            } else {
                $where .= "request.institution IS NULL";
            }
            $dql->andWhere($where);

            $filtered = true;
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
            $dql->andWhere("(request.firstDayAway between :createDateStart and :createDateEnd OR request.firstDayBackInOffice between :createDateStart and :createDateEnd)");

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

        if( $businessRequest ) {
            $dql->andWhere("requestBusiness.startDate IS NOT NULL");
            $filtered = true;
        }
        if( $vacationRequest ) {
            $dql->andWhere("requestVacation.startDate IS NOT NULL");
            $filtered = true;
        }

        if( $completed ) {
            $dql->andWhere("requestBusiness.status='rejected' OR requestVacation.status='rejected' OR requestBusiness.status='approved' OR requestVacation.status='approved'");
            $filtered = true;
        }
        if( $pending ) {
            $dql->andWhere("requestBusiness.status='pending' OR requestVacation.status='pending'");
            $filtered = true;
        }
        if( $rejected ) {
            $dql->andWhere("requestBusiness.status='rejected' OR requestVacation.status='rejected'");
            $filtered = true;
        }
        if( $approved ) {
            $dql->andWhere("requestBusiness.status='approved' OR requestVacation.status='approved'");
            $filtered = true;
        }

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
