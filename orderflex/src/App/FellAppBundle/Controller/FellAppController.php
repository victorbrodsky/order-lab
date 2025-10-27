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

namespace App\FellAppBundle\Controller;



use App\FellAppBundle\Entity\GoogleFormConfig;
use App\FellAppBundle\Form\ApplyFellowshipApplicationType;
use App\UserdirectoryBundle\Entity\EventTypeList; //process.py script: replaced namespace by ::class: added use line for classname=EventTypeList


use App\UserdirectoryBundle\Entity\Institution;
use App\UserdirectoryBundle\Entity\Logger; //process.py script: replaced namespace by ::class: added use line for classname=Logger


use App\FellAppBundle\Entity\FellAppStatus; //process.py script: replaced namespace by ::class: added use line for classname=FellAppStatus


use App\UserdirectoryBundle\Entity\Roles; //process.py script: replaced namespace by ::class: added use line for classname=Roles


use App\UserdirectoryBundle\Entity\Document; //process.py script: replaced namespace by ::class: added use line for classname=Document


use App\FellAppBundle\Entity\Process; //process.py script: replaced namespace by ::class: added use line for classname=Process


use App\UserdirectoryBundle\Entity\FellowshipSubspecialty; //process.py script: replaced namespace by ::class: added use line for classname=FellowshipSubspecialty
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use App\FellAppBundle\Entity\FellowshipApplication;
use App\FellAppBundle\Entity\Interview;
use App\FellAppBundle\Form\InterviewType;
use App\UserdirectoryBundle\Entity\User;
use App\OrderformBundle\Helper\ErrorHelper;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\FellAppBundle\Form\FellAppFilterType;
use App\FellAppBundle\Form\FellowshipApplicationType;
use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraints\DateTime;


class FellAppController extends OrderAbstractController {

    /**
     * Show home page
     *
     *
     */
    #[Route(path: '/', name: 'fellapp_home')]
    #[Route(path: '/my-interviewees/', name: 'fellapp_myinterviewees')]
    #[Route(path: '/send-rejection-emails', name: 'fellapp_send_rejection_emails')]
    #[Route(path: '/accepted-fellows', name: 'fellapp_accepted_fellows')]
    #[Template('AppFellAppBundle/Default/home.html.twig')]
    public function indexAction(Request $request) {
        //echo "fellapp home <br>";

        $route = $request->get('_route');
        //echo "route".$route."<br>";
        //exit();

        if( $route == "fellapp_home" ) {
            if( false == $this->isGranted("read","FellowshipApplication") ){
                //check if has role interviewer => redirect to 'fellapp_myinterviewees'
                //if( $this->isGranted("ROLE_FELLAPP_INTERVIEWER") ) {
                if( $this->isGranted("create","Interview") ) {
                    return $this->redirect( $this->generateUrl('fellapp_myinterviewees') );
                }
                //exit("no permission: read");
                return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            }
        }

        if( $route == "fellapp_myinterviewees" ) {
            if(
                false == $this->isGranted("read","FellowshipApplication") &&
                false == $this->isGranted("create","Interview")
            ){
                return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            }
        }

        if( $route == "fellapp_send_rejection_emails" ) {
            if(
                false == $this->isGranted("ROLE_FELLAPP_COORDINATOR") &&
                false == $this->isGranted("ROLE_FELLAPP_DIRECTOR")
            ) {
                return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            }
            if( false == $this->isGranted("read","FellowshipApplication") ){
                return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            }
        }

        if( $route == "fellapp_accepted_fellows" ) {
            if( false == $this->isGranted("read","FellowshipApplication") ){
                return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            }
        }

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');

        //echo "fellapp user ok <br>";

        //$user = $this->getUser();
        $user = $this->getUser();
        $fellappUtil = $this->container->get('fellapp_util');
        $userServiceUtil = $this->container->get('user_service_utility');


        $fellowshipTypes = array();
        $globalFellTypes = array();
        $serverRole = $userSecUtil->getSiteSettingParameter('authServerNetwork');
        //echo '$serverRole='.$serverRole.'<br>';
        if( $serverRole."" != 'Internet (Hub)' ) {
            $fellowshipTypes = $fellappUtil->getFellowshipTypesByUser($user);
            //echo "fellowshipTypes count=".count($fellowshipTypes)."<br>";
        } else {
            $globalFellTypes = $fellappUtil->getGlobalFellowshipTypesByInstitution(null, 'id-text'); //return as array
            //echo "globalFellTypes count=".count($globalFellTypes)."<br>";
        }
        //echo "fellowshipTypes 2 count=".count($fellowshipTypes)."<br>";
        //echo "globalFellTypes 2 count=".count($globalFellTypes)."<br>";
        //exit('111');

        $searchFlag = false;

        $defaultStartDates = NULL;
        //echo "defaultStartDates=$defaultStartDates <br>"; //testing
        $currentYears = $fellappUtil->getAcademicStartYearByFellowships($fellowshipTypes);
        if( $currentYears ) {
            $currentYearArr = array();
            foreach($currentYears as $thisCurrentYear) {
                //echo "thisCurrentYear=$thisCurrentYear <br>"; //testing
                $currentYearArr[] = $thisCurrentYear + 2;
            }
            $currentYear = implode(",",$currentYearArr);
            $defaultStartDates = $currentYear;
            //echo "defaultStartDates1=$defaultStartDates <br>";
        }
        if( !$defaultStartDates ) {
            //$startEndDates = $fellappUtil->getAcademicYearStartEndDates(null,false,+2);
            //$currentYear = $startEndDates['currentYear'];
            //$currentYear = date("Y")+2;
            $currentYear = $fellappUtil->getDefaultAcademicStartYear();
            $currentYear = $currentYear + 2;
            //$currentYear = $currentYear + 3;
            $defaultStartDates = $currentYear; //"2012,2013,2014,2015";
            //echo "defaultStartDates2=$defaultStartDates <br>";
        }
        //echo "defaultStartDates=$defaultStartDates <br>"; //testing

        if( count($fellowshipTypes) == 0 && count($globalFellTypes) == 0 ) {
//            $linkUrl = $this->generateUrl(
//                "fellowshipsubspecialtys-list",
//                array(),
//                UrlGeneratorInterface::ABSOLUTE_URL
//            );
            //$warningMsg = "No fellowship types (subspecialties) are found for WCMC Pathology and Laboratory Medicine department.";
            //$warningMsg = $warningMsg." ".'<a href="'.$linkUrl.'" target="_blank">Please associate the department with the appropriate fellowship subspecialties.</a>';
            //$warningMsg = $warningMsg."<br>"."For example, choose an appropriate subspecialty and set the institution to 'Weill Cornell Medical College => Pathology and Laboratory Medicine'";
            $linkUrl = $this->generateUrl(
                "fellapp_fellowshiptype_settings",
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $warningMsg = "No fellowship types (subspecialties) are found.";
            $warningMsg = $warningMsg."<br>".'<a href="'.$linkUrl.'" target="_blank">Please add a new fellowship application type.</a>';

            $this->addFlash(
                'warning',
                //'No Fellowship Types (Subspecialties) are found for WCMC Pathology and Laboratory Medicine department.
                // Please assign the WCMC department to the appropriate Fellowship Subspecialties'
                $warningMsg
            );
            //return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            return $this->redirect( $this->generateUrl('fellapp-nopermission',array('empty'=>true)) );
        }

        if( $route == "fellapp_accepted_fellows" ) {
            //pre-set startDate:
            // Years: [Select2] allowing multiselect, dynamically listing years from 1900 to [current year + 4], with 12 years [current year - 10] through [current year + 2] pre-selected by default
            //https://bootstrap-datepicker.readthedocs.io/en/latest/options.html#multidate
            //https://bootstrap-datepicker.readthedocs.io/en/latest/markup.html#daterange
            $currentYearInt = date("Y");
            $currentYearInt = intval($currentYearInt);
            $defaultStartDates = array();
            for($x = $currentYearInt-9; $x <= $currentYearInt; $x++) {
                //echo "The number is: $x <br>";
                $defaultStartDates[] = $x;
            }
            for($x = $currentYearInt+1; $x <= $currentYearInt+2; $x++) {
                //echo "The number is: $x <br>";
                $defaultStartDates[] = $x;
            }
            $defaultStartDates = implode(",",$defaultStartDates);//"2012,2013,2014,2015";
        }
        //echo "currentYear=".$currentYear."<br>";
        //$currentYear1 = date("Y")+2;
        //$currentYear2 = date("Y")+3;
        //$currentYear3 = date("Y")+3;
        //$defaultStartDates = array($currentYear1,$currentYear3,$currentYear3);
        //$defaultStartDates = "2019,2020,2021";
        //$defaultStartDates = "2019 2020 2021";
        //$defaultStartDates = $currentYear;


        //echo '1$globalFellTypes='.count($globalFellTypes).'<br>';
//        foreach($globalFellTypes as $globalFellType) {
//            echo '$globalFellType='.$globalFellType."<br>";
//        }

        $fellTypes = $userServiceUtil->flipArrayLabelValue($fellowshipTypes); //flipped
        $globalFellTypes = $userServiceUtil->flipArrayLabelValue($globalFellTypes); //flipped

        //echo '2 $globalFellTypes='.count($globalFellTypes).'<br>';
//        foreach($globalFellTypes as $globalFellType) {
//            echo '$globalFellType='.$globalFellType."<br>";
//        }

        //create fellapp filter
        $params = array(
            'fellTypes' => $fellTypes,
            'globalFellTypes' => $globalFellTypes,
            'defaultStartDates' => $defaultStartDates
        );
        $filterform = $this->createForm(FellAppFilterType::class, null,array(
            'method' => 'GET',
            'form_custom_value'=>$params
        ));

        //$filterform->submit($request);  //use bind instead of handleRequest. handleRequest does not get filter data
        $filterform->handleRequest($request);

        $filter = $filterform['filter']->getData(); //fellowship specialty
        $globalfilter = $filterform['globalfilter']->getData(); //fellowship specialty
        $search = $filterform['search']->getData();
        $startDates = $filterform['startDates']->getData(); //startDates: currentYear is year only i.e. 2021
        $hidden = $filterform['hidden']->getData();
        $archived = $filterform['archived']->getData();
        $complete = $filterform['complete']->getData();
        $interviewee = $filterform['interviewee']->getData();
        $active = $filterform['active']->getData();
        $reject = $filterform['reject']->getData();
        $declined = $filterform['declined']->getData();
        //$onhold = $filterform['onhold']->getData();
        $priority = $filterform['priority']->getData();
        $draft = $filterform['draft']->getData();

        $accepted = $filterform['accepted']->getData();
        $acceptedandnotified = $filterform['acceptedandnotified']->getData();
        $rejectedandnotified = $filterform['rejectedandnotified']->getData();

        //$page = $request->get('page');
        //echo "0startDates=".$startDates->format('Y-m-d')."<br>";
        //echo "active=".$active."<br>";
        //echo "filter=".$filter."<br>";
        //echo "<br>search=".$search."<br>";
        //exit('1');

        $filterParams = $request->query->all();

        if( $route == "fellapp_accepted_fellows" && count($filterParams) == 0 ) {
            $fellowshipTypeId = null;
            if( count($fellowshipTypes) == 1 ) {
                $firstFellType = reset($fellowshipTypes);
                //echo "firstFellType id=".key($fellowshipTypes)."";
                //exit();
                $fellowshipTypeId = key($fellowshipTypes);
            }
            return $this->redirect( $this->generateUrl($route,
                array(
                    'filter[startDates]' => $defaultStartDates, //$currentYear,
                    'filter[accepted]' => 1,
                    'filter[acceptedandnotified]' => 1,
                    'filter[filter]' => $fellowshipTypeId,
                )
            ));
        }

        if( $route == "fellapp_send_rejection_emails" && count($filterParams) == 0 ) {
            $fellowshipTypeId = null;
            if( count($fellowshipTypes) == 1 ) {
                $firstFellType = reset($fellowshipTypes);
                //echo "firstFellType id=".key($fellowshipTypes)."";
                //exit();
                $fellowshipTypeId = key($fellowshipTypes);
            }
            //Show only "Active", "Priority", "Complete", "Interviewee", "Rejected"
            //filter[startDates]=2021&
            //filter[active]=1&filter[priority]=1&filter[complete]=1&filter[interviewee]=1&filter[reject]=1
            return $this->redirect( $this->generateUrl($route,
                array(
                    'filter[startDates]' => $defaultStartDates, //$currentYear,
                    'filter[active]' => 1,
                    'filter[complete]' => 1,
                    'filter[interviewee]' => 1,
                    'filter[priority]' => 1,
                    'filter[reject]' => 1,
                    'filter[filter]' => $fellowshipTypeId,
                )
            ));
        }

        if( count($filterParams) == 0 ) {
            $fellowshipTypeId = null;
            if( count($fellowshipTypes) == 1 ) {
                $firstFellType = reset($fellowshipTypes);
                //echo "firstFellType id=".key($fellowshipTypes)."";
                //exit();
                $fellowshipTypeId = key($fellowshipTypes);
            }
            return $this->redirect( $this->generateUrl($route, //'fellapp_home',
                array(
                    'filter[startDates]' => $defaultStartDates, //$currentYear,
                    'filter[active]' => 1,
                    'filter[complete]' => 1,
                    'filter[interviewee]' => 1,
                    //'filter[onhold]' => 1,
                    'filter[priority]' => 1,
                    'filter[accepted]' => 1,
                    'filter[acceptedandnotified]' => 1,
                    'filter[filter]' => $fellowshipTypeId,
                )
            ) );
        }

        //force check: check user role. Change filter according to the user roles
        if( $filter && $fellappUtil->hasSameFellowshipTypeId($user,$filter) == false ) {
            //exit('no permission');
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //$fellApps = $em->getRepository('AppUserdirectoryBundle:FellowshipApplication')->findAll();
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $repository = $this->getDoctrine()->getRepository(FellowshipApplication::class);
        $dql =  $repository->createQueryBuilder("fellapp");
        $dql->select('fellapp');
        //$dql->groupBy('fellapp');
        $dql->orderBy("fellapp.id","DESC");
        $dql->leftJoin("fellapp.appStatus", "appStatus");
        $dql->leftJoin("fellapp.fellowshipSubspecialty", "fellowshipSubspecialty");
        $dql->leftJoin("fellapp.user", "applicant");
        $dql->leftJoin("applicant.infos", "applicantinfos");
        //$dql->leftJoin("applicant.credentials", "credentials");
        $dql->leftJoin("fellapp.examinations", "examinations");
        $dql->leftJoin("fellapp.trainings", "trainings");
        $dql->leftJoin("fellapp.rank", "rank");

        $parameters = array();

        if( $search ) {
            //echo "<br>search=".$search."<br>";
            //$dql->andWhere("LOWER(applicantinfos.firstName) LIKE LOWER('%".$search."%') OR LOWER(applicantinfos.lastName) LIKE LOWER('%".$search."%')");
            $dql->andWhere(
                "LOWER(applicantinfos.firstName) LIKE LOWER(:search)".
                " OR LOWER(applicantinfos.lastName) LIKE LOWER(:search)"
            );
            $parameters["search"] = '%'.$search.'%';;
            $searchFlag = true;
        }

        //echo "filter=".$filter."<br>";
        //exit('111');
        $fellSubspecId = null;
        if( $filter ) { //&& $filter != "ALL"
            $dql->andWhere("fellowshipSubspecialty.id = ".$filter);
            $searchFlag = true;
            $fellSubspecId = $filter;
        }

        //$globalfilter
        $globalfilterId = null;
        if( $globalfilter ) { //&& $filter != "ALL"
            $dql->andWhere("fellowshipSubspecialty.id = ".$globalfilter);
            $searchFlag = true;
            $globalfilterId = $fellSubspecId = $globalfilter;
        }

        //if( $filter == "ALL" ) {
//        if( !$filter ) {
//            $felltypeArr = array();
//            foreach( $fellowshipTypes as $fellowshipTypeID => $fellowshipTypeName ) {
//                //if( $fellowshipTypeID != "ALL" ) {
//                    //echo "fellowshipType=".$fellowshipTypeID."<br>";
//                    //$dql->orWhere("fellowshipSubspecialty.id = ".$fellowshipTypeID);
//                $felltypeArr[] = "fellowshipSubspecialty.id = ".$fellowshipTypeID;
//                //}
//            }
//            $dql->andWhere( implode(" OR ", $felltypeArr) );
//            $searchFlag = true;
//            //$fellSubspecId = $filter;
//        }

        $orWhere = array();
        //$orWhere[] = "appStatus.id IS NULL"; //ignore status if no status is selected

        if( $hidden ) {
            $orWhere[] = "appStatus.name = 'hide'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $archived ) {
            $orWhere[] = "appStatus.name = 'archive'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $complete ) {
            $orWhere[] = "appStatus.name = 'complete'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $interviewee ) {
            $orWhere[] = "appStatus.name = 'interviewee'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $active ) {
            $orWhere[] = "appStatus.name = 'active'";
            $searchFlag = true;
        } else {
            //$searchFlag = true;
        }

        if( $draft ) {
            $orWhere[] = "appStatus.name = 'draft'";
            $searchFlag = true;
        }

        if( $reject ) {
            $orWhere[] = "appStatus.name = 'reject'";
            $searchFlag = true;
        }

        if( $declined ) {
            $orWhere[] = "appStatus.name = 'declined'";
            $searchFlag = true;
        }

//        if( $onhold ) {
//            $orWhere[] = "appStatus.name = 'onhold'";
//            $searchFlag = true;
//        }

        if( $priority ) {
            $orWhere[] = "appStatus.name = 'priority'";
            $searchFlag = true;
        }

        if( $accepted ) {
            $orWhere[] = "appStatus.name = 'accepted'";
            $searchFlag = true;
        }
        if( $acceptedandnotified ) {
            $orWhere[] = "appStatus.name = 'acceptedandnotified'";
            $searchFlag = true;
        }
        if( $rejectedandnotified ) {
            $orWhere[] = "appStatus.name = 'rejectedandnotified'";
            $searchFlag = true;
        }

        if( count($orWhere) > 0 ) {
            $orWhereStr = implode(" OR ",$orWhere);
            $dql->andWhere("(".$orWhereStr.")");
        }

        if( $startDates ) {
            //echo "startDate=$startDates <br>";
            if(1) {
                //date as string
                $startDateCriterions = array();
                $startDatesArr = explode(",",$startDates);
                $startYearStr = $startDates;    //$startDatesArr[0];
                foreach ($startDatesArr as $startDate) {
                    //$startDatesArr = explode("-", $startDate); //2009-01-01 00:00:00.000000
                    //$startYearStr = $startDatesArr[0];
                    //echo "startDate=$startDate <br>";

//                    if(0) {
//                        $bottomDate = $startDate . "-01-01";
//                        $topDate = $startDate . "-12-31";
//                        echo "old: bottomDate=$bottomDate, topDate=$topDate <br>";
//                        $startDateCriterions[] = "(" . "fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" . ")";
//                    }
                    if(1) {
                        $startEndDates = $fellappUtil->getAcademicYearStartEndDates($startDate);
                        $startDate = $startEndDates['startDate'];
                        $endDate = $startEndDates['endDate'];
                        //echo "new: startDate=$startDate, endDate=$endDate <br>";
                        $startDateCriterions[] = "(" . "fellapp.startDate BETWEEN '" . $startDate . "'" . " AND " . "'" . $endDate . "'" . ")";
                        //$startDateCriterions[] = "("."fellapp.startDate >= '" . $startDate . "'" . " AND " . "fellapp.startDate < " . "'" . $endDate . "'".")";
                    }

                    //echo "bottomDate=$bottomDate, topDate=$topDate <br>";
                    //$startDateCriterions[] = "("."fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'".")";
                    //$startDateCriterions[] = "("."fellapp.startDate >= '" . $topDate . "'" . " AND " . "fellapp.startDate < " . "'" . $bottomDate . "'".")";
                }
                $startDateCriterion = implode(" OR ",$startDateCriterions);
                $dql->andWhere($startDateCriterion);
                if ($startDates != $defaultStartDates) {
                    $searchFlag = true;
                }
            } else {
                //date as DateTime object
                $startYearStr = $startDates->format('Y');
                //$bottomDate = $startYearStr."-01-01";
                //$topDate = $startYearStr."-12-31";
                $startEndDates = $fellappUtil->getAcademicYearStartEndDates($startYearStr);
                $topDate = $startEndDates['startDate'];
                $bottomDate = $startEndDates['endDate'];
                //echo "new: topDate=$topDate, bottomDate=$bottomDate <br>";
                $dql->andWhere("fellapp.startDate BETWEEN '" . $bottomDate . "'" . " AND " . "'" . $topDate . "'" );

                if( $startYearStr != $currentYear ) {
                    $searchFlag = true;
                }
            }
        } else {
            $startYearStr = $currentYear;
        }
        //echo "startYearStr=$startYearStr <br>"; //testing

        if( $route == "fellapp_myinterviewees" ) {
            $dql->leftJoin("fellapp.interviews", "interviews");
            $dql->andWhere("interviews.interviewer = " . $user->getId() );
        }

        //echo "dql=".$dql."<br>";

        $limit = 200;
        //$limit = 10; //testing
        $query = $dql->getQuery();
        //echo "query=".$query->getSql()."<br>";

        if( count($parameters) > 0 ) {
            $query->setParameters($parameters);
        }

        $paginator  = $this->container->get('knp_paginator');
        $fellApps = $paginator->paginate(
            $query,
            $request->query->get('page', 1), /*page number*/
            //$request->query->getInt('page', 1),
            $limit,      /*limit per page*/
            array('wrap-queries' => true)
        );


        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:EventTypeList'] by [EventTypeList::class]
        $eventtype = $em->getRepository(EventTypeList::class)->findOneByName("Import of Fellowship Applications Spreadsheet");
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Logger'] by [Logger::class]
        $lastImportTimestamps = $this->getDoctrine()->getRepository(Logger::class)->findBy(array('eventType'=>$eventtype),array('creationdate'=>'DESC'),1);
        if( count($lastImportTimestamps) != 1 ) {
            $lastImportTimestamp = null;
        } else {
            $lastImportTimestamp = $lastImportTimestamps[0]->getCreationdate();
        }

        $accessreqs = $fellappUtil->getActiveAccessReq();
        $accessreqsCount = 0;
        if( is_array($accessreqs) ) {
            $accessreqsCount = count($accessreqs);
        }

        //use date from the filter ($startYearStr) instead of $currentYear

        $complete = $fellappUtil->getFellAppByStatusAndYear('complete',$fellSubspecId,$startYearStr);
        $completeTotal = $fellappUtil->getFellAppByStatusAndYear('complete',$fellSubspecId);

        $hidden = $fellappUtil->getFellAppByStatusAndYear('hide',$fellSubspecId,$startYearStr);
        $hiddenTotal = $fellappUtil->getFellAppByStatusAndYear('hide',$fellSubspecId);

        $archived = $fellappUtil->getFellAppByStatusAndYear('archive',$fellSubspecId,$startYearStr);
        $archivedTotal = $fellappUtil->getFellAppByStatusAndYear('archive',$fellSubspecId);

        $draft = $fellappUtil->getFellAppByStatusAndYear('draft',$fellSubspecId,$startYearStr);
        $draftTotal = $fellappUtil->getFellAppByStatusAndYear('draft',$fellSubspecId);

        $active = $fellappUtil->getFellAppByStatusAndYear('active',$fellSubspecId,$startYearStr);
        $activeTotal = $fellappUtil->getFellAppByStatusAndYear('active',$fellSubspecId);

        $interviewee = $fellappUtil->getFellAppByStatusAndYear('interviewee',$fellSubspecId,$startYearStr);
        $intervieweeTotal = $fellappUtil->getFellAppByStatusAndYear('interviewee',$fellSubspecId);

        $reject = $fellappUtil->getFellAppByStatusAndYear('reject',$fellSubspecId,$startYearStr);
        $rejectTotal = $fellappUtil->getFellAppByStatusAndYear('reject',$fellSubspecId);

        $declined = $fellappUtil->getFellAppByStatusAndYear('declined',$fellSubspecId,$startYearStr);
        $declinedTotal = $fellappUtil->getFellAppByStatusAndYear('declined',$fellSubspecId);

        //$onhold = $fellappUtil->getFellAppByStatusAndYear('onhold',$fellSubspecId,$startYearStr);
        //$onholdTotal = $fellappUtil->getFellAppByStatusAndYear('onhold',$fellSubspecId);

        $priority = $fellappUtil->getFellAppByStatusAndYear('priority',$fellSubspecId,$startYearStr);
        $priorityTotal = $fellappUtil->getFellAppByStatusAndYear('priority',$fellSubspecId);

        $accepted = $fellappUtil->getFellAppByStatusAndYear('accepted',$fellSubspecId,$startYearStr);
        $acceptedTotal = $fellappUtil->getFellAppByStatusAndYear('accepted',$fellSubspecId);

        $acceptedandnotified = $fellappUtil->getFellAppByStatusAndYear('acceptedandnotified',$fellSubspecId,$startYearStr);
        $acceptedandnotifiedTotal = $fellappUtil->getFellAppByStatusAndYear('acceptedandnotified',$fellSubspecId);

        $rejectedandnotified = $fellappUtil->getFellAppByStatusAndYear('rejectedandnotified',$fellSubspecId,$startYearStr);
        $rejectedandnotifiedTotal = $fellappUtil->getFellAppByStatusAndYear('rejectedandnotified',$fellSubspecId);

        $idsArr = array();
        foreach( $fellApps as $fellApp ) {
            $idsArr[] = $fellApp->getId();
        }

        //Showing applications of your interviewees: 25 evaluations received, 10 awaited
        $awaitedInterviews = null;
        $receivedInterviews = null;
        if( $route == "fellapp_myinterviewees" ) {

            if( $fellSubspecId ) {
                $fellSubspecArg = $fellSubspecId;
            } else {
                $fellSubspecArg = $fellowshipTypes;
            }

            $awaitedInterviews = count($fellappUtil->getFellAppByStatusAndYear('interviewee-not',$fellSubspecArg,$startYearStr,$user));
            $receivedInterviews = count($fellappUtil->getFellAppByStatusAndYear('interviewee',$fellSubspecArg,$startYearStr,$user));
            //echo "awaitedInterviews=".$awaitedInterviews."<br>";
            //echo "receivedInterviews=".$receivedInterviews."<br>";
        }

        //allowPopulateFellApp
        //$userUtil = new UserUtil();
        //$allowPopulateFellApp = $userUtil->getSiteSetting($em,'AllowPopulateFellApp');
        $allowPopulateFellApp = $userSecUtil->getSiteSettingParameter('AllowPopulateFellApp',$this->getParameter('fellapp.sitename'));

        //At the top of the homepage, show either "Now accepting applications" if the
        // "accepting applications" status from json is enabled, or show "Not accepting applications now."
        $acceptingApplication = NULL;
        if( $route == "fellapp_home" ) {
            $acceptingApplication = "Not accepting applications now";
            $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
            $configFileContent = $googlesheetmanagement->getConfigOnGoogleDrive();
            //dump($configFileContent);
            //exit('111');
            if( $configFileContent ) {
                $configFileContent = json_decode($configFileContent, true);
                $acceptingSubmissions = $configFileContent['acceptingSubmissions'];
                if ($acceptingSubmissions || $acceptingSubmissions == 'true') {
                    $acceptingApplication = "Now accepting applications";
                }
            } else {
                $this->addFlash(
                    'warning',
                    "Google configuration file can not be retrieved from Google Drive.".
                    " Please verify if the 'Full path to the credential authentication JSON file for Google'".
                    " parameter in the site settings has been provided and exist on the server"
                );
            }
            $acceptingApplication = "- ".$acceptingApplication;
        }

        //emailAcceptSubject emailAcceptBody
        $acceptedEmailSubject = $userSecUtil->getSiteSettingParameter('acceptedEmailSubject',$this->getParameter('fellapp.sitename'));
        $acceptedEmailBody = $userSecUtil->getSiteSettingParameter('acceptedEmailBody',$this->getParameter('fellapp.sitename'));
        $rejectedEmailSubject = $userSecUtil->getSiteSettingParameter('rejectedEmailSubject',$this->getParameter('fellapp.sitename'));
        $rejectedEmailBody = $userSecUtil->getSiteSettingParameter('rejectedEmailBody',$this->getParameter('fellapp.sitename'));


        return array(
            'entities' => $fellApps,
            'pathbase' => 'fellapp',
            'lastImportTimestamp' => $lastImportTimestamp,
            'allowPopulateFellApp' => $allowPopulateFellApp,
            'acceptingApplication' => $acceptingApplication,
            'fellappfilter' => $filterform->createView(),
            //'startDate' => $startDate,
            'filter' => $fellSubspecId,
            'accessreqs' => $accessreqsCount,
            'currentYear' => $startYearStr, //$currentYear, //TODO: adopt the currentYear to currentYears in controller and html
            'hiddenTotal' => count($hiddenTotal),
            'archivedTotal' => count($archivedTotal),
            'hidden' => count($hidden),
            'archived' => count($archived),
            'active' => count($active),
            'activeTotal' => count($activeTotal),
            'draft' => count($draft),
            'draftTotal' => count($draftTotal),
            'reject' => count($reject),
            'rejectTotal' => count($rejectTotal),
            'declined' => count($declined),
            'declinedTotal' => count($declinedTotal),
            //'onhold' => count($onhold),
            //'onholdTotal' => count($onholdTotal),
            'priority' => count($priority),
            'priorityTotal' => count($priorityTotal),
            'complete' => count($complete),
            'completeTotal' => count($completeTotal),
            'interviewee' => count($interviewee),
            'intervieweeTotal' => count($intervieweeTotal),

            'accepted' => count($accepted),
            'acceptedTotal' => count($acceptedTotal),
            'acceptedandnotified' => count($acceptedandnotified),
            'acceptedandnotifiedTotal' => count($acceptedandnotifiedTotal),
            'rejectedandnotified' => count($rejectedandnotified),
            'rejectedandnotifiedTotal' => count($rejectedandnotifiedTotal),

            'acceptedEmailSubject' => $acceptedEmailSubject,
            'acceptedEmailBody' => $acceptedEmailBody,
            'rejectedEmailSubject' => $rejectedEmailSubject,
            'rejectedEmailBody' => $rejectedEmailBody,

            'awaitedInterviews' => $awaitedInterviews,
            'receivedInterviews' => $receivedInterviews,
            'searchFlag' => $searchFlag,
            'serverTimeZone' => "", //date_default_timezone_get(),
            'fellappids' => implode("-",$idsArr),
            'route_path' => $route,
            'fellowshipTypes' => $fellowshipTypes,
            'serverRole' => $serverRole,
            'static' => false, //static=true => dynamically load the email's warning, subject and body
            'spinnerColor' => "#428bca"
        );
    }

//    //check for active access requests
    //    public function getActiveAccessReq() {
    //        if( !$this->isGranted('ROLE_FELLAPP_ADMIN') ) {
    //            return null;
    //        }
    //        $userSecUtil = $this->container->get('user_security_utility');
    //        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->getParameter('fellapp.sitename'),AccessRequest::STATUS_ACTIVE);
    //        return $accessreqs;
    //    }
    //@Route("/edit/{id}", name="fellapp_edit")
    //@Route("/edit-with-default-interviewers/{id}", name="fellapp_edit_default_interviewers")
    #[Route(path: '/show/{id}', name: 'fellapp_show')]
    #[Route(path: '/download/{id}', name: 'fellapp_download')]
    #[Template('AppFellAppBundle/Form/new.html.twig')]
    public function showAction(Request $request, Security $security, TokenStorageInterface $tokenStorage, $id) {

        //echo "clientip=".$request->getClientIp()."<br>";
        //$ip = $this->container->get('request')->getClientIp();
        //echo "ip=".$ip."<br>";

//        if( false == $this->isGranted("read","FellowshipApplication") ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }

        //ini_set('memory_limit', '7168M');

        //error_reporting(E_ERROR | E_PARSE);

        $em = $this->getDoctrine()->getManager();
        $logger = $this->container->get('logger');
        $routeName = $request->get('_route');
        $userSecUtil = $this->container->get('user_security_utility');

        //$user = $this->container->get('security')->getUser();
        $user = $this->getUser();
        //dump($user);
        //if( $user ) {
            //exit('user ok');
        //} else {
        //    exit('no user');
        //}
//        if( $this->container->get('security.token_storage')->getToken() ) {
//            exit('token ok');
//        } else {
//            exit('no token');
//        }
//        $user = NULL;
//        //if( $this->container->get('security.token_storage')->getToken() ) {
//            $user = $this->getUser();
//        //}


        $actionStr = "viewed";
        $eventType = 'Fellowship Application Page Viewed';

        //admin can edit
        if( $routeName == "fellapp_edit" ) {
            $actionStr = "viewed on edit page";
            $eventType = 'Fellowship Application Page Viewed';
        }

        //download: user or localhost
        if( $routeName == 'fellapp_download' ) {
            //$user = $this->getUser();
            //download link can be accessed by a console as localhost with role PUBLIC_ACCESS, so simulate login manually           
            if( !($user instanceof User) ) {
                $firewall = 'ldap_fellapp_firewall';               
                $systemUser = $userSecUtil->findSystemUser();
                if( $systemUser ) {
                    //$token = new UsernamePasswordToken($systemUser, null, $firewall, $systemUser->getRoles());
                    $token = new UsernamePasswordToken($systemUser, $firewall, $systemUser->getRoles());
                    //$this->container->get('security.token_storage')->setToken($token);
                    $tokenStorage->setToken($token);
                }
                $logger->notice("Download view: Logged in as systemUser=".$systemUser.", ID=".$systemUser->getId());
            } else {
                $logger->notice("Download view: Token user is valid security user=".$user.", ID=".$user->getId());
            }
        }

        
        //echo "fellapp download!!!!!!!!!!!!!!! <br>";       

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $entity = $em->getRepository(FellowshipApplication::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        //testing
        //$fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        //$fellappRecLetterUtil->generateFellappRecLetterId($entity);
        //exit('testing');

//        if( false == $this->isGranted("interview",$entity) ) {
//            exit('fellapp interview permission not ok ID:'.$entity->getId());
//        }

        //user who has the same fell type can view or edit
        //can use hasFellappPermission or isGranted("read",$entity). isGranted("read",$entity) fellapp voter contains hasFellappPermission
        //$fellappUtil = $this->container->get('fellapp_util');
        //if( $fellappUtil->hasFellappPermission($user,$entity) == false ) {
        if( false == $this->isGranted("read",$entity) ) {
            //exit('fellapp read permission not ok ID:'.$entity->getId());
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
        //exit('fellapp permission ok ID:'.$entity->getId());

        if( $routeName == "fellapp_edit" ) {
            if( false == $this->isGranted("update",$entity) ) {
                //exit('fellapp update permission not ok ID:'.$entity->getId());
                return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            }
        }
//        else {
//            if( false == $this->isGranted("read",$entity) ) {
//                return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//            }
//        }

        $args = $this->getShowParameters($routeName,$entity,$security); //edit

        if( $routeName == 'fellapp_download' ) {
            return $this->render('AppFellAppBundle/Form/download.html.twig', $args);
        }

        //event log
        //$event = "Fellowship Application with ID".$id." has been ".$actionStr." by ".$user;
        //$userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,$request,$eventType);
        
        return $this->render('AppFellAppBundle/Form/new.html.twig', $args);
    }

    #[Route(path: '/new/', name: 'fellapp_new')]
    #[Template('AppFellAppBundle/Form/new.html.twig')]
    public function newAction(Request $request, Security $security) {

        //coordinator and director can create
//        if( false == $this->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->isGranted('ROLE_FELLAPP_DIRECTOR') ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }
        if( false == $this->isGranted("create","FellowshipApplication") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //$user = $this->getUser();
        $user = $this->getUser();

        //$user = new User();
        $addobjects = true;
        $applicant = new User($addobjects);
        $applicant->setPassword("");
        $applicant->setCreatedby('manual');
        $applicant->setAuthor($user);

        $fellowshipApplication = new FellowshipApplication($user);
        $fellowshipApplication->setTimestamp(new \DateTime());

        $applicant->addFellowshipApplication($fellowshipApplication);

        $routeName = $request->get('_route');
        $args = $this->getShowParameters($routeName,$fellowshipApplication,$user,$security); // new

        if( count($args) == 0 ) {
            $linkUrl = $this->generateUrl(
                "fellapp_fellowshiptype_settings",
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $warningMsg = "No fellowship types (subspecialties) are found.";
            $warningMsg = $warningMsg."<br>".'<a href="'.$linkUrl.'" target="_blank">Please add a new fellowship application type.</a>';

            $this->addFlash(
                'warning',
                $warningMsg
            );
            //return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            return $this->redirect( $this->generateUrl('fellapp-nopermission',array('empty'=>true)) );
        }

        return $this->render('AppFellAppBundle/Form/new.html.twig', $args);
    }


    public function getShowParameters($routeName, $entity, $user=null, $security=null, $institutionId=null) {

        $userSecUtil = $this->container->get('user_security_utility');
        //$user = $this->getUser();

//        echo "user=".$user."<br>";
        if( !$user || !($user instanceof User) ) {
            //echo "no user object <br>";
            $user = $userSecUtil->findSystemUser();
        }
        
        $em = $this->getDoctrine()->getManager();

//        if( $id ) {
//            //$fellApps = $em->getRepository('AppFellAppBundle:FellowshipApplication')->findAll();
//            $entity = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find($id);
//
//            if( !$entity ) {
//                throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
//            }
//        } else {
//            if( !$entity ) {
//                throw $this->createNotFoundException('Fellowship Application entity was not provided: id='.$id.", entity=".$entity);
//            }
//        }

        //add empty fields if they are not exist
        $fellappUtil = $this->container->get('fellapp_util');

        $fellTypes = array();
        $globalFellTypes = array();

        if( $routeName == "fellapp_apply" || $routeName == "fellapp_apply_post" ) {
            $globalFellTypes = $fellappUtil->getGlobalFellowshipTypesByInstitution($institution=null,$asArray=false); //return as entities
            if( count($globalFellTypes) == 0 ) {
                return array();
            }
        } else {
            $fellTypes = $fellappUtil->getFellowshipTypesByInstitution(true);
            if( count($fellTypes) == 0 ) {
                return array();
            }
        }

        $fellappVisas = $fellappUtil->getFellowshipVisaStatuses(false,false);
        //var_dump($fellappVisas);
        //exit('111');
        
        $fellappUtil->addEmptyFellAppFields($entity); //testing

        $captchaSiteKey = null;

        if( $routeName == "fellapp_show" ) {
            $cycle = 'show';
            $disabled = true;
            $method = "GET";
            $action = $this->generateUrl('fellapp_edit', array('id' => $entity->getId()));
        }

        if( $routeName == "fellapp_new" ) {
            $cycle = 'new';
            $disabled = false;
            $method = "POST";
            $action = $this->generateUrl('fellapp_create_applicant');
        }

        if( $routeName == "fellapp_apply" ) {
            $cycle = 'new';
            $disabled = false;
            $method = "POST";
            $action = $this->generateUrl('fellapp_apply_post'); // /apply use the same post submit as /new form
        }

        if( $routeName == "fellapp_edit" ) {
            $cycle = 'edit';
            $disabled = false;
            $method = "PUT";
            $action = $this->generateUrl('fellapp_update', array('id' => $entity->getId()));
        }

        if( $routeName == "fellapp_update" ) {
            $cycle = 'edit';
            $disabled = false;
            $method = "PUT";
            $action = $this->generateUrl('fellapp_update', array('id' => $entity->getId()));
        }

        if( $routeName == "fellapp_edit_default_interviewers" ) {
            $cycle = 'edit';
            $disabled = false;
            $method = "PUT";
            $action = $this->generateUrl('fellapp_update', array('id' => $entity->getId()));
            $fellappUtil->addDefaultInterviewers($entity);

            $this->addFlash(
                'pnotify',
                "Important Note: Please manually review added default interviewers in the 'Interviews' section and click 'Update' button to save the changes!"
            );
        }

        if( $routeName == "fellapp_download" ) {
            $cycle = 'download';
            $disabled = true;
            $method = "GET";
            $action = ""; //null; //$this->generateUrl('fellapp_update', array('id' => $entity->getId()));
        }

        if( $routeName == "fellapp_apply" || $routeName == "fellapp_apply_post" ) {
            if ($userSecUtil->getSiteSettingParameter('captchaEnabled') === true) {
                $captchaSiteKey = $userSecUtil->getSiteSettingParameter('captchaSiteKey');
            }
        }

        $institutions = $fellappUtil->getFellowshipInstitutions($institutionId);
        //dump($institutions);
        //exit('111');
        //echo '$institutions='.count($institutions).'<br>';
        //foreach ($institutions as $institution) {
        //    echo '$institutions='.$institution->getTreeAbbreviation().'<br>';
        //}
        //exit('111');

        $roles = $user ? $user->getRoles() : [];

        $params = array(
            'cycle' => $cycle,
            'em' => $em,
            'user' => $entity->getUser(),
            'cloneuser' => null,
            'roles' => $roles, //$user->getRoles(),
            'container' => $this->container,
            'fellappTypes' => $fellTypes,
            'globalFellappTypes' => $globalFellTypes,
            'institutions' => $institutions,
            'fellappVisas' => $fellappVisas,
            'routeName' => $routeName,
            //'security' => $security
        );

//        $form = $this->createForm(
//            new FellowshipApplicationType($params),
//            $entity,
//            array(
//                'disabled' => $disabled,
//                'method' => $method,
//                'action' => $action
//            )
//        );
        $form = $this->createForm(
            FellowshipApplicationType::class, //method: get Show Parameters
            $entity,
            array(
                'disabled' => $disabled,
                'method' => $method,
                'action' => $action,
                'form_custom_value' => $params
            )
        );

        //clear em, because createUserEditEvent will flush em
        $em = $this->getDoctrine()->getManager();
        $em->clear();

        return array(
            'form_pure' => $form,
            'form' => $form->createView(),
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'cycle' => $cycle,
            'sitename' => $this->getParameter('fellapp.sitename'),
            'route_path' => $routeName,
            'captchaSiteKey' => $captchaSiteKey
        );
    }

//    /**
    //     * -NOT-USED
    //     * @Route("/update-NOT-USED/{id}", name="fellapp_update-NOT-USED", methods={"PUT"})
    //     * @Template("AppFellAppBundle/Form/new.html.twig")
    //     */
    //    public function updateNotUsedAction(Request $request, $id) {
    //
    ////        if( false == $this->isGranted('ROLE_FELLAPP_COORDINATOR') && false == $this->isGranted('ROLE_FELLAPP_DIRECTOR') ){
    ////            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
    ////        }
    ////        if( false == $this->isGranted("update","FellowshipApplication") ){
    ////            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
    ////        }
    //
    //        //echo "update <br>";
    //        //exit('update');
    //
    //        //ini_set('memory_limit', '3072M'); //3072M
    //
    //        $userSecUtil = $this->container->get('user_security_utility');
    //        //$user = $this->getUser();
    //        $user = $this->getUser();
    //
    //        $entity = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->find($id);
    //
    //        if( !$entity ) {
    //            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
    //        }
    //
    //        //user who has the same fell type can view or edit
    //        $fellappUtil = $this->container->get('fellapp_util');
    //        if( $fellappUtil->hasFellappPermission($user,$entity) == false ) {
    //            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
    //        }
    //
    //        if( false == $this->isGranted("update",$entity) ){
    //            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
    //        }
    //
    //        // Create an ArrayCollection of the current interviews
    //        $originalInterviews = new ArrayCollection();
    //        foreach( $entity->getInterviews() as $interview) {
    //            $originalInterviews->add($interview);
    //        }
    //
    //        $originalReports = new ArrayCollection();
    //        foreach( $entity->getReports() as $report ) {
    //            $originalReports->add($report);
    //        }
    //
    //        $cycle = 'edit';
    //        //$user = $this->getUser();
    //        $user = $this->getUser();
    //
    //        $params = array(
    //            'cycle' => $cycle,
    //            'em' => $this->getDoctrine()->getManager(),
    //            'user' => $entity->getUser(),
    //            'cloneuser' => null,
    //            'roles' => $user->getRoles(),
    //            'container' => $this->container,
    //            'cycle_type' => "update",
    //            'security' => $this->security
    //        );
    //        $form = $this->createForm( FellowshipApplicationType::class, $entity, array('form_custom_value' => $params) ); //update
    //        //$routeName = $request->get('_route');
    //        //$args = $this->getShowParameters($routeName,null,$entity);
    //        //$form = $args['form_pure'];
    //
    //        $form->handleRequest($request);
    //
    //        if( !$form->isSubmitted() ) {
    //            echo "form is not submitted<br>";
    //            $form->submit($request);
    //        }
    //
    //
    ////        if ($form->isDisabled()) {
    ////            echo "form is disabled<br>";
    ////            exit();
    ////        }
    ////        if (count($form->getErrors(true)) > 0) {
    ////            echo "form has errors<br>";
    ////        }
    ////        echo "errors:<br>";
    ////        $string = (string) $form->getErrors(true);
    ////        echo "string errors=".$string."<br>";
    ////        echo "getErrors count=".count($form->getErrors())."<br>";
    //        //echo "getErrorsAsString()=".$form->getErrorsAsString()."<br>";
    ////        print_r($form->getErrors());
    ////        echo "<br>string errors:<br>";
    ////        print_r($form->getErrorsAsString());
    ////        echo "<br>";
    ////        exit();
    //
    //        if(0) {
    //            $errorHelper = new ErrorHelper();
    //            $errors = $errorHelper->getErrorMessages($form);
    //            echo "<br>form errors:<br>";
    //            print_r($errors);
    //
    //            //echo "<br><br>getErrors:<br>";
    //            //var_dump($form->getErrors());die;
    //        }
    //
    //
    //
    //        $force = false;
    //        //$force = true;
    //        if( $form->isValid() || $force ) {
    //
    //            //exit('form valid');
    //
    //            /////////////// Process Removed Collections ///////////////
    //            $removedCollections = array();
    //
    //            $removedInfo = $this->removeCollection($originalInterviews,$entity->getInterviews(),$entity);
    //            if( $removedInfo ) {
    //                $removedCollections[] = $removedInfo;
    //            }
    //            /////////////// EOF Process Removed Collections ///////////////
    //
    //            $this->calculateScore($entity);
    //
    //            $this->processDocuments($entity);
    //
    //            $this->assignFellAppAccessRoles($entity);
    //
    //            //set update author application
    //            $em = $this->getDoctrine()->getManager();
    //            $userUtil = $this->container->get('user_utility');
    //            //$userUtil = new UserUtil();
    //            //$secTokenStorage = $this->container->get('security.token_storage');
    //            $userUtil->setUpdateInfo($entity);
    //
    //
    //            /////////////// Add event log on edit (edit or add collection) ///////////////
    //            /////////////// Must run before flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
    //            $changedInfoArr = $this->setEventLogChanges($entity);
    //
    //            //report (Complete Application PDF) diff
    //            $reportsDiffInfoStr = $this->recordToEvenLogDiffCollection($originalReports,$entity->getReports(),"Report");
    //            //echo "reportsDiffInfoStr=".$reportsDiffInfoStr."<br>";
    //            //exit('report');
    //
    //            //set Edit event log for removed collection and changed fields or added collection
    //            if( count($changedInfoArr) > 0 || count($removedCollections) > 0 || $reportsDiffInfoStr ) {
    //                $event = "Fellowship Application ".$entity->getId()." information has been changed by ".$user.":"."<br>";
    //                $event = $event . implode("<br>", $changedInfoArr);
    //                $event = $event . "<br>" . implode("<br>", $removedCollections);
    //                $event = $event . $reportsDiffInfoStr;
    //                //echo "Diff event=".$event."<br>";
    //                //$userSecUtil = $this->container->get('user_security_utility');
    //                $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,$request,'Fellowship Application Updated');
    //            }
    //
    //            $em = $this->getDoctrine()->getManager();
    //            $em->persist($entity);
    //            $em->flush();
    //
    //            //don't regenerate report if it was added.
    //            //Regenerate if: report does not exists (reports count == 0) or if original reports are the same as current reports
    //            //echo "report count=".count($entity->getReports())."<br>";
    //            //echo "reportsDiffInfoStr=".$reportsDiffInfoStr."<br>";
    //            if( count($entity->getReports()) == 0 || $reportsDiffInfoStr == "" ) {
    //                $fellappRepGen = $this->container->get('fellapp_reportgenerator');
    //                $fellappRepGen->addFellAppReportToQueue( $id, 'overwrite' );
    //                $this->addFlash(
    //                    'notice',
    //                    'A new Complete Fellowship Application PDF will be generated.'
    //                );
    //                //echo "Regenerate!!!! <br>";
    //            } else {
    //                //echo "NO Regenerate!!!! <br>";
    //            }
    //            //exit('report regen');
    //
    //            //set logger for update
    //            //$logger = $this->container->get('logger');
    //            //$logger->notice("update: timezone=".date_default_timezone_get());
    //            //$userSecUtil = $this->container->get('user_security_utility');
    //            //$user = $em->getRepository('AppUserdirectoryBundle:User')->find($user->getId()); //fetch user from DB otherwise keytype is null
    //            $event = "Fellowship Application with ID " . $id . " has been updated by " . $user;
    //            $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,$request,'Fellowship Application Updated');
    //            //exit('event='.$event);
    //
    //            return $this->redirect($this->generateUrl('fellapp_show',array('id' => $entity->getId())));
    //        } else {
    //            echo "getErrors count=".count($form->getErrors(true))."<br>";
    //            $string = (string) $form->getErrors(true);
    //            //echo "Error:<br>$string<br><br><pre>";
    //            //print_r($form->getErrors());
    //            //echo "</pre>";
    //
    //            $msg = 'Fellowship Form has an error (ID# '.$entity->getId().'): '.$form->getErrors(true);
    //            //$userSecUtil = $this->container->get('user_security_utility');
    //            //$userSecUtil->sendEmailToSystemEmail("Fellowship Form has an error (ID# ".$entity->getId().")", $msg);
    //            exit($msg."<br>Notification email has been sent to the system administrator.");
    //            //throw new \Exception($msg);
    //        }
    //
    //        //echo 'form invalid <br>';
    //        //exit('form invalid');
    //
    //        return array(
    //            'form' => $form->createView(),
    //            'entity' => $entity,
    //            'pathbase' => 'fellapp',
    //            'cycle' => $cycle,
    //            'sitename' => $this->getParameter('fellapp.sitename')
    //        );
    //    }
    //EOF -NOT-USED
    /**
     * Separate edit/update controller action to insure csrf token is valid
     * Displays a form to edit an existing fellapp entity.
     */
    #[Route(path: '/edit/{id}', name: 'fellapp_edit', methods: ['GET', 'POST'])]
    #[Route(path: '/edit-with-default-interviewers/{id}', name: 'fellapp_edit_default_interviewers', methods: ['GET', 'POST'])]
    #[Template('AppFellAppBundle/Form/edit.html.twig')]
    public function editAction(Request $request, Security $security, FellowshipApplication $entity)
    {
        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application');
        }

        $em = $this->getDoctrine()->getManager();

        $id = $entity->getId();

        $userSecUtil = $this->container->get('user_security_utility');
        //$fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        //$user = $this->getUser();
        $user = $this->getUser();
        $routeName = $request->get('_route');

        //user who has the same fell type can view or edit
        $fellappUtil = $this->container->get('fellapp_util');
        if( $fellappUtil->hasFellappPermission($user,$entity) == false ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        if( false == $this->isGranted("update",$entity) ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //ini_set('memory_limit', '7168M');

        ////// PRE Update INFO //////
        // Create an ArrayCollection of the current interviews
        $originalInterviews = new ArrayCollection();
        foreach( $entity->getInterviews() as $interview) {
            $originalInterviews->add($interview);
        }

        $originalReports = new ArrayCollection();
        foreach( $entity->getReports() as $report ) {
            $originalReports->add($report);
        }
        ////// EOF PRE Update INFO //////

        if( $routeName == "fellapp_edit_default_interviewers" ) {
            $fellappUtil->addDefaultInterviewers($entity);
        }

        $cycle = "edit";

        $form = $this->createFellAppEditForm($entity,$cycle,$security);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {

            ////// set status edit GET POST application//////
            $btnSubmit = $request->request->get('btnSubmit');
            echo "btnSubmit=$btnSubmit <br>";
            if ($btnSubmit === 'fellapp-draft') {
                $initialStatusName = "draft";
                //exit("Handle draft logic: skip required fields, save partial data");
            } elseif ($btnSubmit === 'fellapp-submit' ) {
                $initialStatusName = "active";
                //exit("Validate and process full application");
            } elseif ($btnSubmit === 'fellapp-update' ) {
                $initialStatusName = null;
                //exit("Validate and process full application");
            } else {
                //exit("Unknown button");
                $initialStatusName = "draft";
            }

            if( $initialStatusName ) {
                $initialStatus = $em->getRepository(FellAppStatus::class)->findOneByName($initialStatusName);
                //exit("initialStatusName=$initialStatusName, initialStatus=$initialStatus");
                if (!$initialStatus) {
                    //exit("Unable to find FellAppStatus by name=$initialStatusName");
                    throw new EntityNotFoundException('Unable to find FellAppStatus by name=' . "$initialStatusName");
                }
                $entity->setAppStatus($initialStatus);
            }
            //exit("initialStatusName=$initialStatusName, initialStatus=$initialStatus");
            ////// EOF set status //////
            //exit("initialStatusName=$initialStatusName, initialStatus=$initialStatus");
            ////// EOF set status //////

            //$this->getDoctrine()->getManager()->flush();
            //return $this->redirect($this->generateUrl('fellapp_show',array('id' => $entity->getId())));

            /////////////// Process Removed Collections ///////////////
            $removedCollections = array();

            $removedInfo = $this->removeCollection($originalInterviews,$entity->getInterviews(),$entity);
            if( $removedInfo ) {
                $removedCollections[] = $removedInfo;
            }
            /////////////// EOF Process Removed Collections ///////////////

            $this->calculateScore($entity);

            $this->processDocuments($entity);

            $this->assignFellAppAccessRoles($entity);

            //DO NOT update reference hash ID, once it's generated. This hash ID will be used to auto attach recommendation letter to the reference's application.
            //$fellappRecLetterUtil->generateFellappRecLetterId($entity);

            $entity->autoSetRecLetterReceived();

            //set update author application
            //$em = $this->getDoctrine()->getManager();
            //$userUtil = new UserUtil();
            //$secTokenStorage = $this->container->get('security.token_storage');
            $userUtil = $this->container->get('user_utility');
            $userUtil->setUpdateInfo($entity);


            /////////////// Add event log on edit (edit or add collection) ///////////////
            /////////////// Must run before flash DB. When DB is flashed getEntityChangeSet() will not work ///////////////
            $changedInfoArr = $this->setEventLogChanges($entity);

            //report (Complete Application PDF) diff
            $reportsDiffInfoStr = $this->recordToEvenLogDiffCollection($originalReports,$entity->getReports(),"Report");
            //echo "reportsDiffInfoStr=".$reportsDiffInfoStr."<br>";
            //exit('report');

//            //set Edit event log for removed collection and changed fields or added collection
//            if( count($changedInfoArr) > 0 || count($removedCollections) > 0 || $reportsDiffInfoStr ) {
//                $event = "Fellowship Application ".$entity->getId()." information has been changed by ".$user.":"."<br>";
//                $event = $event . implode("<br>", $changedInfoArr);
//                $event = $event . "<br>" . implode("<br>", $removedCollections);
//                $event = $event . $reportsDiffInfoStr;
//                //echo "Diff event=".$event."<br>";
//                $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,$request,'Fellowship Application Updated');
//            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //set Edit event log for removed collection and changed fields or added collection
            if( count($changedInfoArr) > 0 || count($removedCollections) > 0 || $reportsDiffInfoStr ) {
                $event = "Fellowship Application ".$entity->getId()." information has been changed by ".$user.":"."<br>";
                $event = $event . implode("<br>", $changedInfoArr);
                $event = $event . "<br>" . implode("<br>", $removedCollections);
                $event = $event . $reportsDiffInfoStr;
                //echo "Diff event=".$event."<br>";
                $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,$request,'Fellowship Application Updated');
            }

            //don't regenerate report if it was added.
            //Regenerate if: report does not exists (reports count == 0) or if original reports are the same as current reports
            //echo "report count=".count($entity->getReports())."<br>";
            //echo "reportsDiffInfoStr=".$reportsDiffInfoStr."<br>";
            if( count($entity->getReports()) == 0 || $reportsDiffInfoStr == "" ) {
                $fellappRepGen = $this->container->get('fellapp_reportgenerator');
                $fellappRepGen->addFellAppReportToQueue( $entity->getId(), 'overwrite' );
                $this->addFlash(
                    'notice',
                    'A new Complete Fellowship Application PDF will be generated.'
                );
                //echo "Regenerate!!!! <br>";
            } else {
                //echo "NO Regenerate!!!! <br>";
            }
            //exit('report regen');

            //set logger for update
            //$user = $em->getRepository('AppUserdirectoryBundle:User')->find($user->getId()); //fetch user from DB otherwise keytype is null
            $event = "Fellowship Application with ID " . $id . " has been updated by " . $user;
            $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,$request,'Fellowship Application Updated');
            //exit('event='.$event);

            //return $this->redirect($this->generateUrl('fellapp_show',array('id' => $entity->getId())));

            //redirect to a simple confirmation page
            $this->addFlash(
                'notice',
                'Fellowship Application with ID '.$id.' has been updated.'
            );
            return $this->redirect($this->generateUrl('fellapp_simple_confirmation',array('id' => $id)));

        } else {
//            if( !$form->isSubmitted() ){
//                echo "form is not submitted<br>";
//            }
//            if( !$form->isValid() ){
//                echo "form is not valid<br>";
//            }

            if( $routeName == "fellapp_edit_default_interviewers" ) {
                $this->addFlash(
                    'pnotify',
                    "Important Note: Please manually review added default interviewers in the 'Interviews' section and click 'Update' button to save the changes!"
                );
            }

            //event log
            $em = $this->getDoctrine()->getManager();
            $actionStr = "viewed on edit page";
            $eventType = 'Fellowship Application Page Viewed';
            //$user = $em->getRepository('AppUserdirectoryBundle:User')->find($user->getId()); //fetch user from DB otherwise keytype is null
            $event = "Fellowship Application with ID".$id." has been ".$actionStr." by ".$user;

            $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,$request,$eventType);
        }

        return array(
            'form' => $form->createView(),
            'entity' => $entity,
            'pathbase' => 'fellapp',
            'cycle' => $cycle,
            'sitename' => $this->getParameter('fellapp.sitename')
        );
    }
    private function createFellAppEditForm( FellowshipApplication $entity, $cycle, $security )
    {
        //$user = $this->getUser();
        $user = $this->getUser();
        $fellappUtil = $this->container->get('fellapp_util');

        $fellTypes = $fellappUtil->getFellowshipTypesByInstitution(true);
        if( count($fellTypes) == 0 ) {
            return array();
        }

        $fellappVisas = $fellappUtil->getFellowshipVisaStatuses(false,false);

        $params = array(
            'cycle' => $cycle,
            'em' => $this->getDoctrine()->getManager(),
            'user' => $entity->getUser(),
            'cloneuser' => null,
            'roles' => $user->getRoles(),
            'container' => $this->container,
            'cycle_type' => "update",
            'fellappTypes' => $fellTypes,
            'fellappVisas' => $fellappVisas,
            //'security' => $security
        );
        //Edit Form
        $form = $this->createForm( FellowshipApplicationType::class, $entity, array(
            'form_custom_value' => $params
        )); //update

        return $form;
    }


    public function calculateScore($entity) {
        $count = 0;
        $score = 0;
        foreach( $entity->getInterviews() as $interview ) {
            $totalRank = $interview->getTotalRank();
            if( $totalRank ) {
                $score = $score + $totalRank;
                $count++;
            }
        }
        if( $count > 0 ) {
            $score = $score/$count;
            $score = round($score,1);
        }

        $entity->setInterviewScore($score);
    }

    public function setEventLogChanges($entity) {

        $em = $this->getDoctrine()->getManager();

        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener

        $eventArr = array();

        //log simple fields
        $changeset = $uow->getEntityChangeSet($entity);
        $eventArr = $this->addChangesToEventLog( $eventArr, $changeset );

        //interviews
        foreach( $entity->getInterviews() as $subentity ) {
            $changeset = $uow->getEntityChangeSet($subentity);
            $text = "("."interview ".$this->getEntityId($subentity).")";
            $eventArr = $this->addChangesToEventLog( $eventArr, $changeset, $text );
        }

        return $eventArr;
    }
    public function removeCollection($originalArr,$currentArr,$entity) {
        $em = $this->getDoctrine()->getManager();
        $removeArr = array();

        foreach( $originalArr as $element ) {
            if( false === $currentArr->contains($element) ) {
                $removeArr[] = "<strong>"."Removed: ".$element." ".$this->getEntityId($element)."</strong>";

                if( $element instanceof Interview ) {
                    $entity->removeInterview($element);
                    //$element->setInterviewer(NULL);
                    $em->remove($element);
                }
            }
        } //foreach

        return implode("<br>", $removeArr);
    }
    public function addChangesToEventLog( $eventArr, $changeset, $text="" ) {

        $changeArr = array();

        //process $changeset: author, subjectuser, oldvalue, newvalue
        foreach( $changeset as $key => $value ) {
            if( $value[0] != $value[1] ) {

                if( is_object($key) ) {
                    //if $key is object then skip it, because we don't want to have non-informative record such as: credentials(stateLicense New): old value=, new value=Credentials
                    continue;
                }

                $field = $key;

                $oldValue = $value[0];
                $newValue = $value[1];

                if( $oldValue instanceof \DateTime ) {
                    $oldValue = $this->convertDateTimeToStr($value[0]);
                }
                if( $newValue instanceof \DateTime ) {
                    $newValue = $this->convertDateTimeToStr($value[1]);
                }

                if( is_array($oldValue) ) {
                    $oldValue = implode(", ",$oldValue);
                }
                if( is_array($newValue) ) {
                    $newValue = implode(", ",$newValue);
                }

                $event = "<strong>".$field.$text."</strong>".": "."old value=".$oldValue.", new value=".$newValue;
                //echo "event =".$event."<br>";
                //exit();

                $changeArr[] = $event;
            }
        }

        if( count($changeArr) > 0 ) {
            $eventArr[] = implode("<br>", $changeArr);
        }

        return $eventArr;

    }

    //record diff
    public function recordToEvenLogDiffCollection($originalArr,$currentArr,$text) {
        $removeArr = array();

        $original = $this->listToArray($originalArr);
        $new = $this->listToArray($currentArr);

        $diff = array_diff($original, $new);

        if( count($original) != count($new) || count($diff) != 0 ) {
            $removeArr[] = "<strong>"."Original ".$text.": ".implode(", ",$original)."</strong>";
            $removeArr[] = "<strong>"."New ".$text.": ".implode(", ",$new)."</strong>";
        }

        return implode("<br>", $removeArr);
    }
    public function listToArray($collection) {
        $resArr = array();
        foreach( $collection as $item ) {
            $resArr[] = $item."";
        }
        return $resArr;
    }

    public function convertDateTimeToStr($datetime) {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');
        $dateStr = $transformer->transform($datetime);
        return $dateStr;
    }
    public function getEntityId($entity) {
        if( $entity->getId() ) {
            return "ID=".$entity->getId();
        }
        return "New";
    }


    #[Route(path: '/applicant/new', name: 'fellapp_create_applicant', methods: ['POST'])]
    #[Template('AppFellAppBundle/Form/new.html.twig')]
    public function createApplicantAction( Request $request, Security $security )
    {
        //exit("createApplicantAction");
        if( false == $this->isGranted("create","FellowshipApplication") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        $em = $this->getDoctrine()->getManager();
        //$user = $this->getUser();
        $user = $this->getUser();

        $fellowshipApplication = new FellowshipApplication($user); //new POST

//        $initialStatusName = 'active';
//        $initialStatus = $em->getRepository(FellAppStatus::class)->findOneByName($initialStatusName);
//        if( !$initialStatus ) {
//            //exit("Unable to find FellAppStatus by name=$initialStatusName");
//            throw new EntityNotFoundException('Unable to find FellAppStatus by name='."$initialStatusName");
//        }
//        $fellowshipApplication->setAppStatus($initialStatus);

        if( !$fellowshipApplication->getUser() ) {
            //new applicant
            $addobjects = false;
            $applicant = new User($addobjects);
            $applicant->setPassword("");
            $applicant->setCreatedby('manual');
            $applicant->setAuthor($user);
            $applicant->addFellowshipApplication($fellowshipApplication);
        }

        //add empty fields if they are not exist
        $fellappUtil = $this->container->get('fellapp_util');
        $fellappUtil->addEmptyFellAppFields($fellowshipApplication);

        $fellappVisas = $fellappUtil->getFellowshipVisaStatuses(false,false);

        $fellTypes = $fellappUtil->getFellowshipTypesByInstitution(true);

        $params = array(
            'cycle' => 'new',
            'em' => $this->getDoctrine()->getManager(),
            'user' => $fellowshipApplication->getUser(),
            'cloneuser' => null,
            'roles' => $user->getRoles(),
            'container' => $this->container,
            'fellappTypes' => $fellTypes, //FellowshipSubspecialty::class new
            'fellappVisas' => $fellappVisas,
            //'security' => $security
        );
        //$form = $this->createForm( new FellowshipApplicationType($params), $fellowshipApplication );
        $form = $this->createForm( FellowshipApplicationType::class, $fellowshipApplication, array('form_custom_value' => $params) ); //new

        $form->handleRequest($request);

        ///////// testing "Save as Draft"
//        dump($request->request);
//        $btnSubmit = $request->request->get('btnSubmit');
//        echo "btnSubmit=$btnSubmit <br>";
//        if ($btnSubmit === 'draft') {
//            exit("Handle draft logic: skip required fields, save partial data");
//        } elseif ($btnSubmit === 'active') {
//            exit("Validate and process full application");
//        } else {
//            exit("Unknown button");
//        }
        /////////

        if( !$form->isSubmitted() ) {
            //echo "form is not submitted<br>";
            $form->submit($request);
        }

        $applicant = $fellowshipApplication->getUser();

        if( !$fellowshipApplication->getFellowshipSubspecialty() ) {
            $form['fellowshipSubspecialty']->addError(new FormError('Please select in the Fellowship Type before uploading'));
        }
        if( !$applicant->getEmail() ) {
            $form['user']['infos'][0]['email']->addError(new FormError('Please fill in the email before uploading'));
        }
        if( !$applicant->getFirstName() ) {
            $form['user']['infos'][0]['firstName']->addError(new FormError('Please fill in the First Name before uploading'));
        }
        if( !$applicant->getLastName() ) {
            $form['user']['infos'][0]['lastName']->addError(new FormError('Please fill in the Last Name before uploading'));
        }

        if( $form->isValid() ) {

            ////// set status new post application //////
            $btnSubmit = $request->request->get('btnSubmit');
            //echo "btnSubmit=$btnSubmit <br>";

            $initialStatusName = null;
            if ($btnSubmit === 'fellapp-draft') {
                $initialStatusName = "draft";
                //exit("Handle draft logic: skip required fields, save partial data");
            } elseif ($btnSubmit === 'fellapp-submit' ) {
                $initialStatusName = "active";
                //exit("Validate and process full application");
            }
//            elseif ($btnSubmit === 'fellapp-update' ) {
//                $initialStatusName = null;
                //exit("Validate and process full application");
            else {
                //exit("Unknown button");
                $initialStatusName = "draft";
            }

            if( $initialStatusName ) {
                $initialStatus = $em->getRepository(FellAppStatus::class)->findOneByName($initialStatusName);
                //exit("initialStatusName=$initialStatusName, initialStatus=$initialStatus");
                if (!$initialStatus) {
                    //exit("Unable to find FellAppStatus by name=$initialStatusName");
                    throw new EntityNotFoundException('Unable to find FellAppStatus by name=' . "$initialStatusName");
                }
                $fellowshipApplication->setAppStatus($initialStatus);
            }
            //exit("initialStatusName=$initialStatusName, initialStatus=$initialStatus");
            ////// EOF set status //////

            //set user
            $userSecUtil = $this->container->get('user_security_utility');
            $userkeytype = $userSecUtil->getUsernameType('local-user');
            if( !$userkeytype ) {
                throw new EntityNotFoundException('Unable to find local user keytype');
            }
            $applicant->setKeytype($userkeytype);

            $currentDateTime = new \DateTime();
            $currentDateTimeStr = $currentDateTime->format('m-d-Y-h-i-s');

            //Last Name + First Name + Email
            $applicantname = $applicant->getLastName()."_".$applicant->getFirstName()."_".$applicant->getEmail()."_".$currentDateTimeStr;
            $applicant->setPrimaryPublicUserId($applicantname);

            //set unique username
            $applicantnameUnique = $applicant->createUniqueUsername();
            $applicant->setUsername($applicantnameUnique);
            $applicant->setUsernameCanonical($applicantnameUnique);

            $applicant->setEmailCanonical($applicant->getEmail());
            $applicant->setPassword("");
            $applicant->setCreatedby('manual');

            $default_time_zone = $this->getParameter('default_time_zone');
            $applicant->getPreferences()->setTimezone($default_time_zone);
            $applicant->setLocked(true);

            //exit('form valid');

            $this->calculateScore($fellowshipApplication);

            $this->processDocuments($fellowshipApplication);

            $this->assignFellAppAccessRoles($fellowshipApplication);

            //create reference hash ID
            $fellappRecLetterUtil->generateFellappRecLetterId($fellowshipApplication);

            $fellowshipApplication->autoSetRecLetterReceived();

            //set update author application
//            $em = $this->getDoctrine()->getManager();
//            $userUtil = new UserUtil();
//            $sc = $this->container->get('security.context');
//            $userUtil->setUpdateInfo($fellowshipApplication,$em,$sc);

            //exit('eof new applicant');

            $em = $this->getDoctrine()->getManager();
            $em->persist($fellowshipApplication);
            $em->persist($applicant);
            $em->flush();

            //update report if report does not exists
            //if( count($entity->getReports()) == 0 ) {
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            $fellappRepGen->addFellAppReportToQueue( $fellowshipApplication->getId(), 'overwrite' );
            $this->addFlash(
                'notice',
                'A new Complete Fellowship Application PDF will be generated.'
            );
            //}

            //set logger for update
            $userSecUtil = $this->container->get('user_security_utility');
            $event = "Fellowship Application with ID " . $fellowshipApplication->getId() . " has been created by " . $user;
            $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$fellowshipApplication,$request,'Fellowship Application Updated');

            return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellowshipApplication->getId())));
        }

        //echo 'form invalid <br>';
        //exit('form invalid');

        return array(
            'form' => $form->createView(),
            'entity' => $fellowshipApplication,
            'pathbase' => 'fellapp',
            'cycle' => 'new',
            'sitename' => $this->getParameter('fellapp.sitename')
        );

    }

    //assign ROLE_FELLAPP_INTERVIEWER corresponding to application
    public function assignFellAppAccessRoles($application) {

        $em = $this->getDoctrine()->getManager();

        $fellowshipSubspecialty = $application->getFellowshipSubspecialty();

        //////////////////////// INTERVIEWER ///////////////////////////
        $interviewerRoleFellType = null;
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Roles'] by [Roles::class]
        $interviewerFellTypeRoles = $em->getRepository(Roles::class)->findByFellowshipSubspecialty($fellowshipSubspecialty);
        foreach( $interviewerFellTypeRoles as $role ) {
            if( strpos((string)$role,'INTERVIEWER') !== false ) {
                $interviewerRoleFellType = $role;
                break;
            }
        }
        if( !$interviewerRoleFellType ) {
            //throw new EntityNotFoundException('Unable to find role by FellowshipSubspecialty='.$fellowshipSubspecialty);
            $logger = $this->container->get('logger');
            $logger->warning('Unable to find role by FellowshipSubspecialty='.$fellowshipSubspecialty);
            return false;
        }

        foreach( $application->getInterviews() as $interview ) {
            $interviewer = $interview->getInterviewer();
            if( $interviewer ) {

                //add general interviewer role                
                //$interviewer->addRole('ROLE_FELLAPP_USER');
                //$interviewer->addRole('ROLE_FELLAPP_INTERVIEWER');

                //add specific interviewer role
                $interviewer->addRole($interviewerRoleFellType->getName());

            }
        }
        //////////////////////// EOF INTERVIEWER ///////////////////////////


        //////////////////////// OBSERVER ///////////////////////////
        foreach( $application->getObservers() as $observer ) {
            if( $observer ) {
                //add general observer role
                //$observer->addRole('ROLE_FELLAPP_USER');
                $observer->addRole('ROLE_FELLAPP_OBSERVER');
            }
        }
        //////////////////////// EOF OBSERVER ///////////////////////////

    }


    //process upload documents: CurriculumVitae(documents), FellowshipApplication(coverLetters), Examination(scores), FellowshipApplication(lawsuitDocuments), FellowshipApplication(reprimandDocuments)
    public function processDocuments($application) {

        $em = $this->getDoctrine()->getManager();

        //Avatar
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'avatar' );

        //CurriculumVitae
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'cv' );

        //FellowshipApplication(coverLetters)
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'coverLetter' );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'lawsuitDocument');
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'reprimandDocument' );

        //Examination
        foreach( $application->getExaminations() as $examination ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
            $em->getRepository(Document::class)->processDocuments( $examination );
        }

        //Reference .documents
        foreach( $application->getReferences() as $reference ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
            $em->getRepository(Document::class)->processDocuments( $reference );
        }

        //Other .documents
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application );

        //.itinerarys
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'itinerary' );

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'report' );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'formReport' );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'manualReport' );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
        $em->getRepository(Document::class)->processDocuments( $application, 'oldReport' );
    }


    #[Route(path: '/change-status/{id}/{status}', name: 'fellapp_status', methods: ['GET'])]
    #[Route(path: '/status/{id}/{status}', name: 'fellapp_status_email', methods: ['GET'])]
    public function statusAction( Request $request, $id, $status ) {

        //$logger = $this->container->get('logger');
        //$logger->notice('statusAction: status='.$status);

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $entity = $this->getDoctrine()->getRepository(FellowshipApplication::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        if( false == $this->isGranted("update","FellowshipApplication") ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "id=$id <br>";
        //echo "status=$status <br>";
        //exit('eof status changed');

        $event = $this->changeFellAppStatus($entity, $status, $request);

        $this->addFlash(
            'notice',
            $event
        );

        if( $request->get('_route') == 'fellapp_status_email' ) {
            return $this->redirect( $this->generateUrl('fellapp_show',array('id' => $id)) );
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode("ok"));
        return $response;
    }
    
    public function changeFellAppStatus($fellapp, $status, $request) {

        $fellappUtil = $this->container->get('fellapp_util');
        $logger = $this->container->get('logger');
        //$user = $this->getUser();
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //$status might have "-noemail". In this case remove "-noemail" and do not send a notification email.
        $sendEmail = true;
        if( strpos((string)$status, "-noemail") !== false ) {
            $sendEmail = false;
            $status = str_replace("-noemail","",$status);
        }

        //get status object
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellAppStatus'] by [FellAppStatus::class]
        $statusObj = $em->getRepository(FellAppStatus::class)->findOneByName($status);
        if( !$statusObj ) {
            $logger->error('statusAction: Unable to find FellAppStatus by name='.$status);
            throw new EntityNotFoundException('Unable to find FellAppStatus by name='.$status);           
        }

        //change status
        $fellapp->setAppStatus($statusObj);

        $em->persist($fellapp);
        $em->flush();

        //Every time an application is marked as "Priority", send an email to the user(s) with the corresponding "Fellowship Prpgram Coordinator" role (Cytopathology, etc), - in our case it will be Jessica - saying:
        if( $status == 'priority' ) {
            //$break = "\r\n";
            $break = "<br>";
            $directorEmails = $fellappUtil->getDirectorsOfFellAppEmails($fellapp);
            $coordinatorEmails = $fellappUtil->getCoordinatorsOfFellAppEmails($fellapp);
            $responsibleEmails = array_unique (array_merge ($coordinatorEmails, $directorEmails));
            $logger->notice("Fellowship application ".$fellapp->getId()." status has been marked as Priority to the directors and coordinators emails " . implode(", ",$responsibleEmails));

            //Subject: FirstName LastName has marked FirstName LastName's FellowshipType fellowship application (ID:id#) as "Priority"
            $emailSubject = $user." has marked ".$fellapp->getUser()->getUsernameShortest()."'s ".$fellapp->getFellowshipSubspecialty().
                " fellowship application (ID:".$fellapp->getId().") as 'Priority'";

            //Body: FirstName LastName (CWID: xxx1234) has marked FirstName LastName's FellowshipType
            // fellowship application (ID:id#) as "Priority" on MM/DD/YYY at HH:MM.
            //Link to the application:
            //Clickable Link leading to the application web page
            //Download the Application PDF:
            //Clickable link to the PDF of the entire application
            $applicationLink = $this->container->get('router')->generate(
                'fellapp_show',
                array(
                    'id' => $fellapp->getId(),
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $linkToGeneratedApplicantPDF = $this->container->get('router')->generate(
                'fellapp_view_pdf',
                array(
                    'id' => $fellapp->getId()
                ),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $currentDate = new \DateTime("now", new \DateTimeZone('America/New_York') );
            $currentDateStr = $currentDate->format('m/d/Y h:i A T');
            $emailBody = $emailSubject." on ".$currentDateStr.".".$break.$break;
            $emailBody .= "Link to the application:".$break;
            $emailBody .= $applicationLink;
            $emailBody .= $break.$break."Download the Application PDF:".$break;
            $emailBody .= $linkToGeneratedApplicantPDF;
            $emailUtil = $this->container->get('user_mailer_utility');
            $emailUtil->sendEmail( $responsibleEmails, $emailSubject, $emailBody );
        }

        if( $sendEmail && $status == 'acceptedandnotified' ) {
            $fellappUtil->sendAcceptedNotificationEmail($fellapp);
        }

        if( $sendEmail && $status == 'rejectedandnotified' ) {
            $fellappUtil->sendRejectedNotificationEmail($fellapp);
        }

        $eventType = 'Fellowship Application Status changed to ' . $statusObj->getAction();

        $userSecUtil = $this->container->get('user_security_utility');
        $event = $eventType . '; application ID ' . $fellapp->getID() . ' by user ' . $user;
        $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$fellapp,$request,$eventType);
        
        return $event;
    }

    #[Route(path: '/move-year/{id}/{year}', name: 'fellapp_application_move_year', methods: ['GET'])]
    public function moveYearAction( Request $request, $id, $year ) {

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        //$user = $this->getUser();
        $user = $this->getUser();

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $entity = $this->getDoctrine()->getRepository(FellowshipApplication::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        if( false == $this->isGranted("update","FellowshipApplication") ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "id=$id <br>";
        //echo "moveYear=$year <br>";
        //exit('eof year move');

        //Set Start/End date +1 year
        $startDate = $entity->getStartDate();
        $startDatePlusOne = clone $startDate;

        $endDate = $entity->getEndDate();
        $endDatePlusOne = clone $endDate;

        $year = intval($year);

        if( $year > 0 ) {
            $year = abs($year);
            $modifyStr = '+'.$year.' year';
        } else {
            $year = abs($year);
            $modifyStr = '-'.$year.' year';
        }

        $startDatePlusOne->modify($modifyStr);
        $endDatePlusOne->modify($modifyStr);
        //echo "Date2=".$startDatePlusOne->format('Y-m-d').", ".$endDatePlusOne->format('Y-m-d')."<br>";

        $entity->setStartDate($startDatePlusOne);
        $entity->setEndDate($endDatePlusOne);

        $em->flush();

        $event = $entity->getApplicantFullName()."’s application for ".$entity->getFellowshipSubspecialty()." has been moved from ".$startDate->format('Y')." to ".$startDatePlusOne->format('Y');

        $this->addFlash(
            'notice',
            $event
        );

        //Event Log
        $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,$request,'Fellowship Application Updated');

        //return $this->redirect( $this->generateUrl('fellapp_home') );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode("ok"));
        return $response;
    }


//    /**
    //     * @Route("/status-sync/", name="fellapp_sincstatus", methods={"GET"})
    //     */
    //    public function syncStatusAction( Request $request ) {
    //
    //        $em = $this->getDoctrine()->getManager();
    //        $applications = $this->getDoctrine()->getRepository('AppFellAppBundle:FellowshipApplication')->findAll();
    //
    //        foreach( $applications as $application ) {
    //            $status = $application->getApplicationStatus();
    //            $statusObj = $em->getRepository('AppFellAppBundle:FellAppStatus')->findOneByName($status);
    //            if( !$statusObj ) {
    //                throw new EntityNotFoundException('Unable to find FellAppStatus by name='.$status);
    //            }
    //            $application->setAppStatus($statusObj);
    //            //$application->setApplicationStatus(NULL);
    //        }
    //
    //        $em->flush();
    //
    //        return $this->redirect( $this->generateUrl('fellapp_home') );
    //    }
    #[Route(path: '/application-evaluation/show/{id}', name: 'fellapp_application_show', methods: ['GET'])]
    #[Route(path: '/application-evaluation/{id}', name: 'fellapp_application_edit', methods: ['GET'])]
    #[Template('AppFellAppBundle/Interview/interview_selector.html.twig')]
    public function applicationAction( Request $request, FellowshipApplication $fellapp )
    {

        //echo "status <br>";

        if( false == $this->isGranted("create", "Interview") ) {
            return $this->redirect($this->generateUrl('fellapp-nopermission'));
        }

        $fellappUtil = $this->container->get('fellapp_util');
        //$user = $this->getUser();
        $user = $this->getUser();
        $routeName = $request->get('_route');
        $cycle = "show";

        if( $routeName == "fellapp_application_edit" ) {
            $cycle = "edit";
        }

        //1) check if this user is an interviewer for this application
        $interviews = $fellappUtil->findInterviewByFellappAndUser($fellapp,$user);
        if( count($interviews) > 0 ) {
            if( count($interviews) == 1 ) {
                $interview = $interviews[0];
                if ($routeName == "fellapp_application_edit") {
                    return $this->redirect($this->generateUrl('fellapp_interview_edit', array('id' => $interview->getId())));
                } else {
                    return $this->redirect($this->generateUrl('fellapp_interview_show', array('id' => $interview->getId())));
                }
            } else {
                if( count($interviews) > 0 ) {
                    //show all interviews selector
                    return array(
                        'fellapp' => $fellapp,
                        'interviews' => $interviews,
                        'cycle' => $cycle,
                        'sitename' => $this->getParameter('fellapp.sitename')
                    );
                }
            }

        } else {
            //this user is not interviewer for this application
            if ($this->isGranted('ROLE_FELLAPP_COORDINATOR') ||
                $this->isGranted('ROLE_FELLAPP_DIRECTOR') ||
                $this->isGranted('ROLE_FELLAPP_ADMIN')
            ) {
                //show all interviews selector
                $interviews = $fellapp->getInterviews();
                return array(
                    'fellapp' => $fellapp,
                    'interviews' => $interviews,
                    'cycle' => $cycle,
                    'sitename' => $this->getParameter('fellapp.sitename')
                );
            }
        }

        return $this->redirect($this->generateUrl('fellapp-nopermission'));
    }

    #[Route(path: '/interview-evaluation/show/{id}', name: 'fellapp_interview_show', methods: ['GET'])]
    #[Route(path: '/interview-evaluation/{id}', name: 'fellapp_interview_edit', methods: ['GET'])]
    #[Template('AppFellAppBundle/Interview/new.html.twig')]
    public function interviewAction( Request $request, $id ) {

        //echo "status <br>";

//        if( false == $this->isGranted('ROLE_FELLAPP_INTERVIEWER') ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }
        if( false == $this->isGranted("create","Interview") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $routeName = $request->get('_route');

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:Interview'] by [Interview::class]
        $interview = $em->getRepository(Interview::class)->find($id);

        if( !$interview ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application Interview by id='.$id);
        }

        //$user = $this->getUser();
        $user = $this->getUser();

        //check if the interviewer is the same as current user
        $interviewer = $interview->getInterviewer();
        //echo "interviewer=".$interviewer."<br>";
        $interviewerId = null;
        if( $interviewer ) {
            $interviewerId = $interviewer->getId();
        } else {
            throw $this->createNotFoundException('Interviewer is undefined');
        }
        //echo $user->getId()."?=".$interviewerId."<br>";

        if( $this->isGranted('ROLE_FELLAPP_COORDINATOR') ||
            $this->isGranted('ROLE_FELLAPP_DIRECTOR') ||
            $this->isGranted('ROLE_FELLAPP_ADMIN')
        ){
            //allow
        } else {
            if ($user->getId() != $interviewerId) {
                return $this->redirect($this->generateUrl('fellapp-nopermission'));
            }
        }

        if( $routeName == "fellapp_interview_edit" && $interview->getTotalRank() && $interview->getTotalRank() > 0 ) {
            return $this->redirect( $this->generateUrl('fellapp_interview_show',array('id' => $interview->getId())) );
        }

        if( $routeName == "fellapp_interview_show" ) {
            $cycle = "show";
            $method = "GET";
            $action = ""; //null;
            $disabled = true;
        }

        if( $routeName == "fellapp_interview_edit" ) {
            $cycle = "edit";
            $method = "POST";
            $action = $this->generateUrl('fellapp_interview_update', array('id' => $interview->getId()));
            $disabled = false;
        }

        $params = array(
            'cycle' => $cycle,
            'container' => $this->container,
            'em' => $em,
            'interviewer' => $interview->getInterviewer(),
            'showFull' => false
        );

        //InterviewType($params)
        $form = $this->createForm(
            //new InterviewType($params),
            InterviewType::class,
            $interview,
            array(
                'form_custom_value' => $params,
                'disabled' => $disabled,
                'method' => $method,
                'action' => $action
            )
        );

        return array(
            'form' => $form->createView(),
            'entity' => $interview,
            'pathbase' => 'fellapp',
            'cycle' => $cycle,
            'sitename' => $this->getParameter('fellapp.sitename')
        );

    }

    #[Route(path: '/interview/update/{id}', name: 'fellapp_interview_update', methods: ['POST'])]
    #[Template('AppFellAppBundle/Interview/new.html.twig')]
    public function interviewUpdateAction( Request $request, $id ) {

        //echo "status <br>";

//        if( false == $this->isGranted('ROLE_FELLAPP_INTERVIEWER') ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }
        if( false == $this->isGranted("create","Interview") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:Interview'] by [Interview::class]
        $interview = $em->getRepository(Interview::class)->find($id);

        //$user = $this->getUser();
        $user = $this->getUser();
        $fellapp = $interview->getFellapp();
        $applicant = $fellapp->getUser();
        $interviewer = $interview->getInterviewer();

        if( !$interview ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application Interview by id='.$id);
        }

        //check if the interviewer is the same as current user (except Admin)
        if( $this->isGranted('ROLE_FELLAPP_COORDINATOR') ||
            $this->isGranted('ROLE_FELLAPP_DIRECTOR') ||
            $this->isGranted('ROLE_FELLAPP_ADMIN')
        ){
            //allow
        } else {
            if( $user->getId() != $interviewer->getId() ) {
                return $this->redirect($this->generateUrl('fellapp-nopermission'));
            }
        }

        $cycle = 'edit';
        $method = "POST";
        $action = $this->generateUrl('fellapp_interview_update', array('id' => $interview->getId()));
        $disabled = false;

        $params = array(
            'cycle' => $cycle,
            'container' => $this->container,
            'em' => $em,
            'interviewer' => $interviewer,
            'showFull' => false
        );
        $form = $this->createForm(
            //new InterviewType($params),
            InterviewType::class,
            $interview,
            array(
                'form_custom_value' => $params,
                'disabled' => $disabled,
                'method' => $method,
                'action' => $action
            )
        );

        $form->handleRequest($request);

        $formCompleted = false;
        if( $interview->getTotalRank() && $interview->getTotalRank() > 0 ) {
            $formCompleted = true;
        }

        if( $form->isValid() && $formCompleted ) {

//            echo "interviewer=".$interviewer."<br>";
//            if( !$interviewer ) {
//                exit('no interviewer');
//            }
//            exit('1');

            //Set an actual submitter of the scores
            $interview->setSubmitter($user);

            $this->calculateScore($fellapp);
            
            //Upon submitting the first interview evaluation form for a given application, 
            //if the current application status is not "Interviewee", automatically switch it to "Interviewee".
            if( $fellapp->getAppStatus()->getName()."" != "interviewee" ) {
                $this->changeFellAppStatus($fellapp, "interviewee", $request);
            }
            
            $em->persist($interview);
            $em->flush();

            ////// Event Log //////
            $eventType = 'Fellowship Interview Evaluation Updated';

            //if the submitting user is different from the intended interviewer, append a sentence
            //"Submitted on behalf of [InterviewerFirstName InterviewerLastName] by [UserFirstName UserLastName]"
            if( $user->getId() == $interviewer->getId() ) {
                $event = 'Fellowship Interview Evaluation for applicant '.$applicant->getUsernameOptimal().
                    ' (ID: '.$fellapp->getId().') has been submitted by ' . $user->getUsernameOptimal();
            } else {
                $event = 'Fellowship Interview Evaluation for applicant '.$applicant->getUsernameOptimal().' (ID: '.$fellapp->getId().')'.
                    ' has been submitted on behalf of ' . $interviewer->getUsernameOptimal() .
                    ' by ' . $user->getUsernameOptimal();
            }

            $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$fellapp,$request,$eventType);
            ////// EOF Event Log //////
            
            //return $this->redirect( $this->generateUrl('fellapp_home'));

            $this->addFlash(
                'notice',
                $event
            );

            return $this->redirect( $this->generateUrl('fellapp_interview_show',array('id' => $interview->getId())) );
        }


        return array(
            'form' => $form->createView(),
            'entity' => $interview,
            'pathbase' => 'fellapp',
            'cycle' => $cycle,
            'sitename' => $this->getParameter('fellapp.sitename')
        );

    }


//    /**
    //     * @Route("/interview/new/{fellappid}/{interviewid}", name="fellapp_interview_new", methods={"GET"})
    //     * @Route("/interview/new/{fellappid}/{interviewid}", name="fellapp_interview_new", methods={"GET"})
    //     * @Template("AppFellAppBundle/Interview/new.html.twig")
    //     */
    //    public function createInterviewAction( Request $request ) {
    //
    //        //echo "status <br>";
    //
    //        if( false == $this->isGranted('ROLE_FELLAPP_INTERVIEWER') ){
    //            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
    //        }
    //
    //        $em = $this->getDoctrine()->getManager();
    //
    //        $interview = $this->getDoctrine()->getRepository('AppFellAppBundle:Interview')->find($id);
    //
    //        if( !$interview ) {
    //            throw $this->createNotFoundException('Unable to find Fellowship Application Interview by id='.$id);
    //        }
    //
    //        $cycle = "new";
    //
    //        $params = array(
    //            'cycle' => $cycle,
    //            'sc' => $this->container->get('security.context'),
    //            'em' => $this->getDoctrine()->getManager(),
    //        );
    //        $form = $this->createForm( new InterviewType($params), $interview );
    //
    //        return array(
    //            'form' => $form->createView(),
    //            'entity' => $interview,
    //            'pathbase' => 'fellapp',
    //            'cycle' => $cycle,
    //            'sitename' => $this->getParameter('fellapp.sitename')
    //        );
    //
    //    }
    #[Route(path: '/remove/{id}', name: 'fellapp_remove')]
    public function removeAction($id) {

        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //echo "remove <br>";
        exit('remove not supported');

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }




    /**
     * Manually import and populate applicants from Google
     */
    #[Route(path: '/populate-import', name: 'fellapp_import_populate')]
    public function importAndPopulateAction(Request $request) {

        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappImportPopulateUtil = $this->container->get('fellapp_importpopulate_util');

        $result = $fellappImportPopulateUtil->processFellAppFromGoogleDrive();

        $this->addFlash(
            'notice',
            $result
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );

//        //1) import
//        $fileDb = $fellappUtil->importFellApp();
//
//        if( $fileDb ) {
//            $event = "Fellowship Application Spreadsheet file has been successful downloaded to the server with id=" . $fileDb->getId().", title=".$fileDb->getUniquename();
//            $flashType = 'notice';
//        } else {
//            $event = "Fellowship Application Spreadsheet download failed!";
//            $flashType = 'warning';
//            $error = true;
//        }
//
//        $this->addFlash(
//            $flashType,
//            $event
//        );
//
//        if( $error ) {
//            return $this->redirect( $this->generateUrl('fellapp_home') );
//        }
//
//        //2) populate
//        $populatedCount = $fellappUtil->populateFellApp();
//
//        if( $populatedCount >= 0 ) {
//            $event = "Populated ".$populatedCount." Fellowship Applicantions.";
//            $flashType = 'notice';
//        } else {
//            $event = "Google API service failed!";
//            $flashType = 'warning';
//        }
//
//        $this->addFlash(
//            $flashType,
//            $event
//        );
//
//        return $this->redirect( $this->generateUrl('fellapp_home') );
    }

    /**
     * Manually import and populate recommendation letters from Google
     */
    #[Route(path: '/populate-import-letters', name: 'fellapp_import_populate_letters')]
    public function importAndPopulateLettersAction(Request $request) {

        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');

        $result = $fellappRecLetterUtil->processFellRecLetterFromGoogleDrive();

        $this->addFlash(
            'notice',
            $result
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }

    /**
     * Show home page
     */
    #[Route(path: '/populate', name: 'fellapp_populate')]
    public function populateSpreadsheetAction(Request $request) {

        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $populatedCount = $fellappUtil->populateFellApp();

        if( $populatedCount >= 0 ) {
            $event = "Populated ".$populatedCount." Fellowship Applicantions.";
            $flashType = 'notice';
        } else {
            $event = "Google API service failed!";
            $flashType = 'warning';
        }

        $this->addFlash(
            $flashType,
            $event
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }


    /**
     * Import spreadsheet to C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\Spreadsheets
     */
    #[Route(path: '/import', name: 'fellapp_import')]
    public function importAction(Request $request) {

        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $fellappUtil = $this->container->get('fellapp_util');
        $fileDb = $fellappUtil->importFellApp();

        if( $fileDb ) {
            $event = "Fellowship Application Spreadsheet file has been successful downloaded to the server with id=" . $fileDb->getId().", title=".$fileDb->getUniquename();
            $flashType = 'notice';
        } else {
            $event = "Fellowship Application Spreadsheet download failed!";
            $flashType = 'warning';
        }

        $this->addFlash(
            $flashType,
            $event
        );

        //exit('import event'.$event);

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }




//    /**
    //     * NOT USED NOW
    //     * update report by js
    //     *
    //     * @Route("/update-report/", name="fellapp_update_report", methods={"POST"}, options={"expose"=true})
    //     */
    //    public function updateReportAction(Request $request) {
    //
    //        $id = $request->get('id');
    //
    //        $em = $this->getDoctrine()->getManager();
    //        $entity = $em->getRepository('AppFellAppBundle:FellowshipApplication')->find($id);
    //
    //        if( !$entity ) {
    //            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
    //        }
    //
    //        echo "reports = " . count($entity->getReports()) . "<br>";
    //        exit();
    //
    //        //update report if report does not exists
    //        if( count($entity->getReports()) == 0 ) {
    //            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
    //            $fellappRepGen->addFellAppReportToQueue( $id, 'overwrite' );
    //        }
    //
    //        $response = new Response();
    //        $response->setContent('Sent to queue');
    //        return $response;
    //    }
    /**
     * Download application using
     * https://github.com/KnpLabs/KnpSnappyBundle
     * https://github.com/devandclick/EnseparHtml2pdfBundle
     */
    #[Route(path: '/download-pdf/{id}', name: 'fellapp_download_pdf', methods: ['GET'])]
    #[Route(path: '/view-pdf/{id}', name: 'fellapp_view_pdf', methods: ['GET'])]
    public function downloadReportAction(Request $request, $id) {

//        if( false == $this->isGranted('ROLE_FELLAPP_USER') ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }
//        if( false == $this->isGranted("read","FellowshipApplication") ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }

        //$user = $this->getUser();
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $entity = $em->getRepository(FellowshipApplication::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        //user who has the same fell type can view or edit
        $fellappUtil = $this->container->get('fellapp_util');
        if( $fellappUtil->hasFellappPermission($user,$entity) == false ) {
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
        if(
            false == $this->isGranted("read",$entity) &&
            false == $this->isGranted("create","Interview")
        ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        //event log
        $userSecUtil = $this->container->get('user_security_utility');
        $event = "Report for Fellowship Application with ID".$id." has been downloaded by ".$user;
        $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$entity,null,'Complete Fellowship Application PDF Downloaded');

        $reportDocument = $entity->getRecentReport();
        //echo "report=".$reportDocument."<br>";
        //exit();

        if( $reportDocument ) {

            $routeName = $request->get('_route');

            if( $routeName == "fellapp_view_pdf" ) {
                return $this->redirect( $this->generateUrl('fellapp_file_view',array('id' => $reportDocument->getId())) );
            } else {
                return $this->redirect( $this->generateUrl('fellapp_file_download',array('id' => $reportDocument->getId())) );
            }

        } else {

            //create report
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            $argument = 'asap';
            //if( $this->isGranted('ROLE_FELLAPP_COORDINATOR') ) {
                //$argument = 'overwrite';
            //}
            $fellappRepGen->addFellAppReportToQueue( $id, $argument );

            //exit('fellapp_download_pdf exit');

            $this->addFlash(
                'warning',
                'Complete Application PDF is not ready yet. Please try again later.'
            );

            return $this->redirect( $this->generateUrl('fellapp_show',array('id' => $id)) );
        }

    }

    /**
     * Download itinerary
     */
    #[Route(path: '/download-itinerary-pdf/{id}', name: 'fellapp_download_itinerary_pdf', methods: ['GET'])]
    public function downloadItineraryAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
        $entity = $em->getRepository(FellowshipApplication::class)->find($id);

        if( !$entity ) {
            throw $this->createNotFoundException('Unable to find Fellowship Application by id='.$id);
        }

        $scheduleDocument = $entity->getRecentItinerary();
        if( $scheduleDocument ) {
            return $this->redirect( $this->generateUrl('fellapp_file_download',array('id' => $scheduleDocument->getId())) );
        }

        return null;
    }

    /**
     * http://127.0.0.1/order/index_dev.php/fellowship-applications/regenerate-all-complete-application-pdfs/2021
     *
     *
     */
    #[Route(path: '/regenerate-all-complete-application-pdfs/{year}', name: 'fellapp_regenerate_reports')]
    #[Template('AppFellAppBundle/Form/new.html.twig')]
    public function regenerateAllReportsAction(Request $request, $year) {

        exit("This method is disabled for security reason.");

        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        if( !$year ) {
            exit("Please provide start year");
        }

        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $numDeleted = $fellappRepGen->regenerateAllReports($year);

        $em = $this->getDoctrine()->getManager();
        //$fellapps = $em->getRepository('AppFellAppBundle:FellowshipApplication')->findAll();
        $fellapps = $fellappRepGen->getFellApplicationsByYear($year);
        
        $estimatedTime = count($fellapps)*5; //5 min for each report
        $this->addFlash(
            'notice',
            'All Application Reports will be regenerated. Estimated processing time for ' . count($fellapps) . ' reports is ' . $estimatedTime . ' minutes. Number of deleted processes in queue ' . $numDeleted
        );

        return $this->redirect( $this->generateUrl('fellapp_home') );
    }

    #[Route(path: '/reset-queue-and-run/', name: 'fellapp_reset_queue_run')]
    #[Template('AppFellAppBundle/Form/new.html.twig')]
    public function resetQueueRunAction(Request $request) {

        //$logger = $this->container->get('logger');
        //$logger->notice("resetQueueRunAction !!!");

        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

//        //testing
//        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
//        $loc = "C:\\Users\\ch3\\Desktop\\";
//        $filepath = $loc."badpdf.pdf";
//        $filepath = $loc."goodpdf.pdf";
//        if( $fellappRepGen->isPdfCorrupted($filepath) ) {
//            echo "corrupted<br>";
//        } else {
//            echo "not corrupted<br>";
//        }
//
//        $filepath = "E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\web\Uploaded\fellapp\documents\5ba3cc18bae60.pdf";
//        $userSecUtil = $this->container->get('user_security_utility');
//        $errorMsg = "convert To Pdf: PDF is corrupted; filePath=".$filepath;
//        $userSecUtil->sendEmailToSystemEmail("Convert to PDF failed", $errorMsg);
//        exit();

        $fellappRepGen = $this->container->get('fellapp_reportgenerator');
        $numUpdated = $fellappRepGen->resetQueueRun();

        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:Process'] by [Process::class]
        $processes = $em->getRepository(Process::class)->findAll();
        $processInfoArr = array();
        foreach($processes as $processe) {
            $processInfoArr[] = $processe->getFellappId();
        }
        $processInfoStr = NULL;
        if( count($processInfoArr) > 0 ) {
            $processInfoStr = " (ID=".implode(", ",$processInfoArr).")";
        }
        $estimatedTime = count($processes)*5; //5 min for each report
        $this->addFlash(
            'notice',
            'Queue with ' . count($processes) . $processInfoStr .
            ' will be re-run. Estimated processing time is ' .
            $estimatedTime . ' minutes. Number of reset processes in queue ' .
            $numUpdated
        );

        //return $this->redirect( $this->generateUrl('fellapp_home') );
        return $this->redirect( $this->generateUrl('main_common_home') );
    }


    
    #[Route(path: '/download-applicants-list-excel/{currentYear}/{fellappTypeId}/{fellappIds}', name: 'fellapp_download_applicants_list_excel')]
    public function downloadApplicantListExcelAction(Request $request, $currentYear, $fellappTypeId, $fellappIds) {

//        if( false == $this->isGranted('ROLE_FELLAPP_COORDINATOR') &&
//            false == $this->isGranted('ROLE_FELLAPP_DIRECTOR') &&
//            false == $this->isGranted('ROLE_FELLAPP_INTERVIEWER') &&
//            false == $this->isGranted('ROLE_FELLAPP_OBSERVER')
//        ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }
        if( false == $this->isGranted("read","FellowshipApplication") ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }
       
        $em = $this->getDoctrine()->getManager();
        $fellowshipSubspecialty = null;
        $institutionNameFellappName = "";
        
        if( $fellappTypeId && $fellappTypeId > 0 ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:FellowshipSubspecialty'] by [FellowshipSubspecialty::class]
            $fellowshipSubspecialty = $em->getRepository(FellowshipSubspecialty::class)->find($fellappTypeId);
        }
        
        if( $fellowshipSubspecialty ) {
            $institution = $fellowshipSubspecialty->getInstitution();
            $institutionNameFellappName = $institution." ".$fellowshipSubspecialty." ";
        }
        
        $fellappUtil = $this->container->get('fellapp_util');

        if(0) {
            //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
            $fileName = $currentYear." ".$institutionNameFellappName."Fellowship Candidate Data generated on ".date('m/d/Y H:i').".xlsx";
            $fileName = str_replace("  ", " ", $fileName);
            $fileName = str_replace(" ", "-", $fileName);

            $excelBlob = $fellappUtil->createApplicantListExcel($fellappIds);

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excelBlob, 'Xlsx');
            //ob_end_clean();
            //$writer->setIncludeCharts(true);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            //header('Content-Disposition: attachment;filename="fileres.xlsx"');

            // Write file to the browser
            $writer->save('php://output');
            exit();
        }

        //Spout
        if(1) {

            //[YEAR] [WCMC (top level of actual institution)] [FELLOWSHIP-TYPE] Fellowship Candidate Data generated on [DATE] at [TIME] EST.xls
            $fileName = $currentYear." ".$institutionNameFellappName."Fellowship Candidate Data generated on ".date('m-d-Y').".xlsx";
            $fileName = str_replace("  ", " ", $fileName);
            $fileName = str_replace(" ", "-", $fileName);
            $fileName = str_replace(",", "-", $fileName);

            $fellappUtil->createApplicantListExcelSpout($fellappIds,$fileName);
            exit();
        }

        exit();      
    }

    #[Route(path: '/send-rejection-emails-action/', name: 'fellapp_send_rejection_emails_action', methods: ['POST'], options: ['expose' => true])]
    #[Template('AppFellAppBundle/Form/send-notification-emails.html.twig')]
    public function sendRejectionEmailsAction(Request $request) {

        if( false == $this->isGranted('ROLE_FELLAPP_ADMIN') ){
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        $logger = $this->container->get('logger');
        $em = $this->getDoctrine()->getManager();

        //show applications for current year (show the same list as home page)
        //$ids = $request->get('ids');
        //echo "ids=".$ids."<br>";
        //$logger->notice("Rejection ids=".$ids);

        //$idsArr = explode(",",$ids);

        $ids = $request->get('ids');

        foreach($ids as $id) {
            //$logger->notice("Rejection id=".$id);
        //process.py script: replaced namespace by ::class: ['AppFellAppBundle:FellowshipApplication'] by [FellowshipApplication::class]
            $fellapp = $em->getRepository(FellowshipApplication::class)->find($id);
            if( $fellapp ) {
                $logger->notice("Rejection email id=".$id);
                //set status to Rejected and Notified
                //send rejection email
                //record to eventlog
                $status = "rejectedandnotified";
                $event = $this->changeFellAppStatus($fellapp, $status, $request);

                $this->addFlash(
                    'notice',
                    $event
                );

//                $this->addFlash(
//                    'notice',
//                    'Rejection notification email has been sent to '.$id
//                );
            }
        }

        //exit('111');
        //exit();

        //$url = $this->generateUrl('main_common_home');
        $url = $this->generateUrl('fellapp_home');
        return new Response($url);
    }






    ///////////////////// un used methods //////////////////////////
    /**
     * Print files belonging to a folder.
     *
     * @param Google_Service_Drive $service Drive API service instance.
     * @param String $folderId ID of the folder to print files from.
     */
    function printFilesInFolder($service, $folderId) {
        $pageToken = NULL;

        do {
            try {
                $parameters = array();
                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }
                $children = $service->children->listChildren($folderId, $parameters);
                echo "count=".count($children->getItems())."<br>";

                foreach ($children->getItems() as $child) {
                    //print 'File Id: ' . $child->getId()."<br>";
                    //print_r($child);
                    $this->printFile($service,$child->getId());
                }
                $pageToken = $children->getNextPageToken();
            } catch (Exception $e) {
                print "An error occurred: " . $e->getMessage();
                $pageToken = NULL;
            }
        } while ($pageToken);
    }

    function getFilesByAuthUrl() {
        $client_id = "1040591934373-hhm896qpgdaiiblaco9jdfvirkh5f65q.apps.googleusercontent.com";
        $client_secret = "RgXkEm2_1T8yKYa3Vw_tIhoO";
        $redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';    //"http://localhost";

        $res = $this->buildService($client_id,$client_secret,$redirect_uri);

        $service = $res['service'];
        $client = $res['client'];

        $authUrl = $client->createAuthUrl();
        echo "authUrl=".$authUrl."<br>";

        // Exchange authorization code for access token
        $accessToken = $client->authenticate('4/OrVeRdkw9eByckCs7Gtn0B4eUwhERny8AqFOAwy29fY');
        $client->setAccessToken($accessToken);

        $files = $service->files->listFiles();
        echo "count files=".count($files)."<br>";
        echo "<pre>"; print_r($files);
    }

    /**
     * Build a Drive service object.
     */
    function buildService($client_id,$client_secret,$redirect_uri) {
        $client = new \Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);

        //$client->addScope("https://www.googleapis.com/auth/drive");
        $client->setScopes(array('https://www.googleapis.com/auth/drive'));
        $client->setAccessType('offline');

        $service = new \Google_Service_Drive($client);

        $res = array(
            'client' => $client,
            'service' => $service
        );
        return $res;
    }

    /**
     * Print a file's metadata.
     *
     * @param apiDriveService $service Drive API service instance.
     * @param string $fileId ID of the file to print metadata for.
     */
    function printFileV1($service, $fileId) {
        $file = null;
        try {
            $file = $service->files->get($fileId);

            print "Title: " . $file->getTitle()."<br>";
            //print "Title: " . $file->getName()."<br>";
            print "ID: " . $file->getId()."<br>";
            print "Size: " . $file->getFileSize()."<br>";
            //print "Size: " . $file->getSize()."<br>";
            //print "URL: " . $file->getDownloadUrl()."<br>";
            print "Description: " . $file->getDescription()."<br>";
            print "MIME type: " . $file->getMimeType()."<br>"."<br>";

        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
        return $file;
    }
    /**
     * Print a file's metadata.
     *
     * @param apiDriveService $service Drive API service instance.
     * @param string $fileId ID of the file to print metadata for.
     */
    function printFile($service, $fileId) {
        $file = null;
        try {
            $file = $service->files->get($fileId);

            //print "Title: " . $file->getTitle()."<br>";
            print "Title: " . $file->getName()."<br>";
            print "ID: " . $file->getId()."<br>";
            //print "Size: " . $file->getFileSize()."<br>";
            print "Size: " . $file->getSize()."<br>";
            //print "URL: " . $file->getDownloadUrl()."<br>";
            print "Description: " . $file->getDescription()."<br>";
            print "MIME type: " . $file->getMimeType()."<br>"."<br>";

        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
        }
        return $file;
    }



    /**
     * Show home page
     */
    #[Route(path: '/test', name: 'fellapp_test', methods: ['GET'])]
    public function testAction() {

        exit('tests');

        //test url on console
//        $fellappUtil = $this->container->get('fellapp_util');
//        $em = $this->getDoctrine()->getManager();
//        $fellowshipApplication = $em->getRepository('AppFellAppBundle:FellowshipApplication')->find(162);
//        $fellappUtil->sendConfirmationEmailsOnApplicationPopulation($fellowshipApplication,$fellowshipApplication->getUser());
//        return new Response("OK Test");
//        exit('email test');

        $googleSheetManagement = $this->container->get('fellapp_googleSheetManagement');

        //$res = $googleSheetManagement->searchSheet();
        //exit('searchSheet res='.$res);

        $excelId = "156lKGi2cxSbHI3sMN8hiRZLZbLuSQVmisZYARxYWZsM";
        $rowId = "cinava7_yahoo.com_Doe_Linda_2016-03-15_17_59_53";

        //$res = $googleSheetManagement->deleteImportedApplicationAndUploadsFromGoogleDrive($excelId,$rowId);
        //exit('googleSheetManagement res='.$res);
        exit('no test');


        //include_once "vendor/google/apiclient/examples/simple-query.php";
        include_once "vendor/google/apiclient/examples/user-example.php";
        //include_once "vendor/google/apiclient/examples/idtoken.php";


        return new Response("OK Test");
    }







    /////////////////////////////////////////////////////////
    //////////////////// PUBLIC APPLY ///////////////////////
    /////////////////////////////////////////////////////////

    //Public open fellowship application
    //http://127.0.0.1/fellowship-applications/apply?program[]=2179
    #[Route(path: '/apply', name: 'fellapp_apply', methods: ["GET"])]
    #[Template('AppFellAppBundle/Form/apply.html.twig')]
    public function applyAction(Request $request, Security $security, TokenStorageInterface $tokenStorage) {
        //exit('applyAction');
//        if( false == $this->isGranted("create","FellowshipApplication") ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }

        $logger = $this->container->get('logger');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();

        $enablePublicFellapp = $userSecUtil->getSiteSettingParameter(
            'enablePublicFellApp',
            $this->getParameter('fellapp.sitename')
        );
        if( !$enablePublicFellapp === true ) {
            $this->addFlash(
                'warning',
                'Submission of the fellowship application form page on this site is disabled'
            );
            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
        }

        // Parse ?program[]=1&program[]=2
        $programIds = $request->query->all('program'); // returns array of IDs

        // Defensive: ensure it's an array of integers
        $institutionId = null;
        $institutionIds = array_filter(array_map('intval', $programIds));
        if( count($institutionIds) > 0 ) {
            $institutionId = $institutionIds[0];
        }
        //dump($institutionIds);
        //exit('111');


        //$user = $this->getUser();
        $user = $this->getUser();
        //echo "user=".$user."<br>";
//        if( !( $user instanceof User ) ) {
//            $userSecUtil = $this->container->get('user_security_utility');
//            $user = $userSecUtil->findSystemUser();
//            //echo "no user object => use system user=[".$user."]<br>";
//        }

        if( 0 && !($user instanceof User) ) {
            $firewall = 'ldap_fellapp_firewall';
            //$userSecUtil = $this->container->get('user_security_utility');
            //$user = $userSecUtil->findSystemUser();
            //fellapp_public_submitter
            $fellappUtil = $this->container->get('fellapp_util');
            $user = $fellappUtil->findFellappDefaultUser();
            if( $user ) {
                //$token = new UsernamePasswordToken($systemUser, null, $firewall, $systemUser->getRoles());
                $token = new UsernamePasswordToken($user, $firewall, $user->getRoles());
                //$this->container->get('security.token_storage')->setToken($token);
                $tokenStorage->setToken($token);
                $logger->notice("applyAction: Logged in as ldap_fellapp_firewall=".$user);
            } else {
                $logger->notice("applyAction: ldap_fellapp_firewall not found");
                $this->addFlash(
                    'warning',
                    "Fellowship public submitter not found. Please contact the system administrator."
                );
                return $this->redirect( $this->generateUrl('fellapp-nopermission',array('empty'=>true)) );
            }
        } else {
            $logger->notice("applyAction: Token user is valid security user=".$user);
        }
        //testing logout
        //$security->logout();
        //$security->logout(false); //This will trigger onLogout event
        //$this->tokenStorage->setToken(null);
        //$userSecUtil = $this->container->get('user_security_utility');
        //$userSecUtil->userLogout(null);

//        if( false == $this->isGranted("create","FellowshipApplication") ){
//            exit('no');
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }

        //$user = new User();
        $addobjects = true;
        //TODO: the anonymous user will be this applicant
        $applicant = new User($addobjects);
        $applicant->setPassword("");
        $applicant->setCreatedby('manual');
        $applicant->setAuthor($user);

        $fellowshipApplication = new FellowshipApplication($user);
        $fellowshipApplication->setTimestamp(new \DateTime());

        $applicant->addFellowshipApplication($fellowshipApplication);

        $routeName = $request->get('_route');
        $args = $this->getShowParameters($routeName,$fellowshipApplication,$user,$security,$institutionId); //apply GET

        // City data will be fetched via AJAX (PUBLIC_ACCESS for city generic endpoint)

        if( count($args) == 0 ) {
            $linkUrl = $this->generateUrl(
                "fellapp_fellowshiptype_settings",
                array(),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $warningMsg = "No fellowship types (subspecialties) are found.";
            $warningMsg = $warningMsg."<br>".'<a href="'.$linkUrl.'" target="_blank">Please add a new fellowship application type.</a>';

            $this->addFlash(
                'warning',
                $warningMsg
            );
            //return $this->redirect( $this->generateUrl('fellapp-nopermission') );
            return $this->redirect( $this->generateUrl('fellapp-nopermission',array('empty'=>true)) );
        }

        //oleg_fellappbundle_googleformconfig[applicationFormNote]
        //TODO: create GoogleFormConfig auto on demo db reset
        $configs = $em->getRepository(GoogleFormConfig::class)->findAll();
        $googleFormConfig = null;
        if( count($configs) > 0 ) {
            $googleFormConfig = $configs[0];
        } else {
            //$entity = new GoogleFormConfig();
            //throw $this->createNotFoundException('Unable to find Google Fellowship Application Form Configuration');
        }
        if( $googleFormConfig ) {
            //echo "controller applicationFormNote=".$googleFormConfig->getApplicationFormNote()."<br>";
            $args['applicationFormNote'] = $googleFormConfig->getApplicationFormNote();
        } else {
            $args['applicationFormNote'] = '
            Please gather all relevant information before filling out this form in order to submit it.
            <br>
            <h4>        
            Application Packet Checklist
            </h4>                 
             <ul style="display:inline-block; text-align:left;">
                <li>USMLE Step 1 and/or COMLEX Level 1 Score and Date passed (USMLE/Comlex 2 and 3 if applicable) in PDF format</li>
                <li>Updated Curriculum Vitae (CV) in PDF format</li>
                <li>Include cover letter and/or personal statement in PDF format</li>
                <li>Check with the fellowship director or coordinator whether there are other items that should be included</li>
                <li>Include photo in JPEG format</li>
                <li>Please leave field empty (blank) if a question does not apply to you</li>          
             </ul>
             <br>
             <br>
            ';
        }
        if( $googleFormConfig ) {
            //oleg_fellappbundle_googleformconfig[signatureStatement]
            $args['signatureStatement'] = $googleFormConfig->getSignatureStatement();
        } else {
            $args['signatureStatement'] = "I hereby certify that all of the information 
            on this application is accurate, complete, and current to the best 
            of my knowledge, and that this application is being made for serious 
            consideration of training in the fellowship indicated. 
            I understand that accepting more than one fellowship position 
            constitutes a violation of professional ethics and may result 
            in the forfeiture of all positions.";
        }

        return $this->render('AppFellAppBundle/Form/apply.html.twig', $args);
        //$args['applicationFormNote'] = $googleFormConfig->getApplicationFormNote();
    }

    #[Route(path: '/check-user-exist', name: 'fellapp_check_user_exist_email', methods: ["POST"], options: ['expose' => true])]
    public function checkUserExistEmailAction(Request $request) {
        $fellappUtil = $this->container->get('fellapp_util');
        //$res = $fellappUtil->checkUserExistByPostRequest($request);

        $email = $request->request->get('email');
        $res = $fellappUtil->checkUserExistByEmail($email);

        if( $res === true ) {
            $res = 'EXIST';
        } else if ( $res === false ) {
            $res = 'DOESNOTEXIST';
        } else {
            $res = 'N/A';
        }

        $response = new Response();
        //$response->headers->set('Content-Type', 'application/json');
        $response->setContent($res);
        return $response;
        //return new JsonResponse(['exists' => $userExists]);
    }

    #[Route(path: '/get-global-fellowship-types/{institution}', name: 'fellapp-global-fellowship-types', options: ['expose' => true])]
    public function getGlobalFellowshipTypes(Institution $institution=null)
    {
//        $cities = $country->getCities()->map(fn($city) => [
//            'id' => $city->getId(),
//            'name' => $city->getName(),
//        ])->toArray();
        $fellappUtil = $this->container->get('fellapp_util');
        $globalFellTypes = $fellappUtil->getGlobalFellowshipTypesByInstitution($institution); //resturn as select2 array

        //return new JsonResponse($globalFellTypes);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($globalFellTypes));
        return $response;
    }


//    public function canonicalize($string)
//    {
//        if (null === $string) {
//            return null;
//        }
//
//        $encoding = mb_detect_encoding($string);
//        $result = $encoding
//            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
//            : mb_convert_case($string, MB_CASE_LOWER);
//
//        return $result;
//    }

    #[Route(path: '/apply', name: 'fellapp_apply_post', methods: ['POST'])]
    #[Template('AppFellAppBundle/Form/new.html.twig')]
    public function applyApplicantAction( Request $request, Security $security )
    {
        //exit("applyApplicantAction");
//        if( false == $this->isGranted("create","FellowshipApplication") ){
//            return $this->redirect( $this->generateUrl('fellapp-nopermission') );
//        }

        $fellappUtil = $this->container->get('fellapp_util');
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        $userSecUtil = $this->container->get('user_security_utility');
        $em = $this->getDoctrine()->getManager();
        $routeName = $request->get('_route');
        $user = $this->getUser(); //in case of apply, it might be fellapp_public_submitter user

        $applicantEmailError = false;

        $fellowshipApplication = new FellowshipApplication($user); //apply POST

        //Find/Create applicant
        
        if ( !$fellowshipApplication->getUser()) {
            //new applicant
            $addobjects = false;
            
            //get $applicantEmail from request
//            dump($request->request->all());
//            exit();
//            $btnSubmit = $request->request->get('btnSubmit');
//            $formData = $request->request->get('oleg_fellappbundle_fellowshipapplication');
//            dump($formData);
//            exit();
//            $applicantEmail = $formData['user']['infos'][0]['email'] ?? null;
            $data = $request->request->all();
            $applicantEmail = $data['oleg_fellappbundle_fellowshipapplication']['user']['infos'][0]['email'] ?? null;

            //$res = $fellappUtil->checkUserExistByPostRequest($request);
            $res = $fellappUtil->checkUserExistByEmail($applicantEmail);
            //echo "applyApplicantAction: res=$res <br>";
            $applicant = null;
            if ($res === true) {
                //find $applicant by email
                //$applicant = $fellappUtil->checkUserExistByPostRequest($request,true);
                $applicant = $fellappUtil->checkUserExistByEmail($applicantEmail, true);
            } else if ($res === false) {
                $applicant = new User($addobjects);
                $applicant->setPassword("");
                $applicant->setCreatedby('manual');
                $applicant->setAuthor($user);
            } else {
                //$form['user']['infos'][0]['email']->addError(new FormError('Please fill in the email before uploading'));
                $applicantEmailError = true;
            }

            if (!$applicant) {
                exit('Unable to find or create Fellowship Applicant for ' . $fellowshipApplication);
                throw $this->createNotFoundException('Unable to find or create Fellowship Applicant for ' . $fellowshipApplication);
            }

            $applicant->addFellowshipApplication($fellowshipApplication);
        }
        
        //add empty fields if they are not exist
        $fellappUtil->addEmptyFellAppFields($fellowshipApplication);

        $fellappVisas = $fellappUtil->getFellowshipVisaStatuses(false,false);

        //Get list of FellowshipSubspecialty, filtered by institution.id = id of [Weill Cornell Medical College => Pathology and Laboratory Medicine]
        //Pathology and Laboratory Medicine instituion can have many fellowship types (FellowshipSubspecialty)
        $fellTypes = $fellappUtil->getFellowshipTypesByInstitution($asEntities=true);

        $globalFellTypes = $fellappUtil->getGlobalFellowshipTypesByInstitution($institution=null,$asArray=false);

        //New: if authServerNetwork == 'Internet (Hub)'
        //Get $fellTypes based on GlobalFellowshipSpecialty - for now, the same to FellowshipSubspecialty.
        //Each record in GlobalFellowshipSubspecialty table will have ManyToOne $institution
        //One institution can have many GlobalFellowshipSubspecialty

        $institutions = $fellappUtil->getFellowshipInstitutions();

        $roles = $user ? $user->getRoles() : [];

        $params = array(
            'cycle' => 'new',
            'em' => $this->getDoctrine()->getManager(),
            'user' => $fellowshipApplication->getUser(),
            'cloneuser' => null,
            'roles' =>  $roles, //$user->getRoles(),
            'container' => $this->container,
            'fellappTypes' => $fellTypes, //FellowshipSubspecialty::class apply
            'globalFellappTypes' => $globalFellTypes,
            'institutions' => $institutions,
            'fellappVisas' => $fellappVisas,
            'routeName' => $routeName
            //'security' => $security
        );

        //$form = $this->createForm( new FellowshipApplicationType($params), $fellowshipApplication );
        $form = $this->createForm( FellowshipApplicationType::class, $fellowshipApplication, array('form_custom_value' => $params) ); //apply POST
        //$form = $this->createForm( ApplyFellowshipApplicationType::class, $fellowshipApplication, array('form_custom_value' => $params) ); //apply POST

        $form->handleRequest($request);
        
        ///////// testing "Save as Draft"
//        dump($request->request);
//        $btnSubmit = $request->request->get('btnSubmit');
//        echo "/applicant/apply POST: btnSubmit=$btnSubmit <br>";
//        if ($btnSubmit === 'fellapp-draft') {
//            exit("Handle draft logic: skip required fields, save partial data");
//        } elseif ($btnSubmit === 'fellapp-submit') {
//            exit("Validate and process full application");
//        } else {
//            exit("Unknown button");
//        }
        /////////

//        if( !$form->isSubmitted() ) {
//            //echo "form is not submitted<br>";
//            $form->submit($request);
//        }

        $applicant = $fellowshipApplication->getUser();
        //$fellowshipSubspecialty = $fellowshipApplication->getFellowshipSubspecialty();
        $globalFellowshipSpecialty = $fellowshipApplication->getGlobalFellowshipSpecialty();
        if( !$globalFellowshipSpecialty ) {
            $form['globalFellowshipSpecialty']->addError(new FormError('Please select the fellowship specialty before uploading.'));
        }
        if( !$applicant->getEmail() ) {
            $form['user']['infos'][0]['email']->addError(new FormError('Please fill in the email before uploading'));
        }
        if( !$applicant->getFirstName() ) {
            $form['user']['infos'][0]['firstName']->addError(new FormError('Please fill in the First Name before uploading'));
        }
        if( !$applicant->getLastName() ) {
            $form['user']['infos'][0]['lastName']->addError(new FormError('Please fill in the Last Name before uploading'));
        }

        if( $applicantEmailError ) {
            $form['user']['infos'][0]['email']->addError(new FormError('Logical error: applicant can not be found by email'));
        }

        //Add institution validation check

        if( $form->isSubmitted() ) {
        //$formData = $request->request->get('oleg_fellappbundle_fellowshipapplication');
        //dump($formData);
        //$data = $request->request->all();
        //dump($data);
        //exit();
//            foreach ($form as $child) {
//                dump($child->getName());
//            }
//            exit();

//            $applicantEmail = $formData['user']['infos'][0]['email'] ?? null;
            if ($userSecUtil->getSiteSettingParameter('captchaEnabled') === true) {
                $captchaRes = $request->request->get('g-recaptcha-response');
                if (!$userSecUtil->captchaValidate($request, $captchaRes)) {
                    echo "Captcha is not valid <br>";
                    //<input type="hidden" id="oleg_fellappbundle_fellowshipapplication_recaptcha" name="oleg_fellappbundle_fellowshipapplication[recaptcha]" class="form-control g-recaptcha1">
                    $form->get('recaptcha')->addError(new FormError('Captcha is required'));
                    //$form['oleg_fellappbundle_fellowshipapplication']['recaptcha']->addError(new FormError('Captcha is required'));
                }
            }
        }

        if( $form->isValid() ) {

            ////// set status new apply post application //////
            $btnSubmit = $request->request->get('btnSubmit');
//            //echo "btnSubmit=$btnSubmit <br>";

            $initialStatusName = null;
            if ($btnSubmit === 'fellapp-draft') {
                $initialStatusName = "draft";
                //exit("Handle draft logic: skip required fields, save partial data");
            } elseif ($btnSubmit === 'fellapp-submit' ) {
                $initialStatusName = "active";
                //exit("Validate and process full application");
            }
            //elseif ($btnSubmit === 'fellapp-update' ) {
            //    $initialStatusName = null;
                //exit("Validate and process full application");
            //}
            else {
                //exit("Unknown button");
                $initialStatusName = "draft";
            }

            if( $initialStatusName ) {
                $initialStatus = $em->getRepository(FellAppStatus::class)->findOneByName($initialStatusName);
                //exit("initialStatusName=$initialStatusName, initialStatus=$initialStatus");
                if (!$initialStatus) {
                    //exit("Unable to find FellAppStatus by name=$initialStatusName");
                    throw new EntityNotFoundException('Unable to find FellAppStatus by name=' . "$initialStatusName");
                }
                $fellowshipApplication->setAppStatus($initialStatus);
            }
            //exit("initialStatusName=$initialStatusName, initialStatus=$initialStatus");
            ////// EOF set status //////

            //set user
            $userkeytype = $userSecUtil->getUsernameType('local-user');
            if( !$userkeytype ) {
                throw new EntityNotFoundException('Unable to find local user keytype');
            }
            $applicant->setKeytype($userkeytype);

            $currentDateTime = new \DateTime();
            $currentDateTimeStr = $currentDateTime->format('m-d-Y-h-i-s');

            //Last Name + First Name + Email
            $applicantname = $applicant->getLastName()."_".$applicant->getFirstName()."_".$applicant->getEmail()."_".$currentDateTimeStr;
            $applicant->setPrimaryPublicUserId($applicantname);

            //set unique username
            $applicantnameUnique = $applicant->createUniqueUsername();
            $applicant->setUsername($applicantnameUnique);
            $applicant->setUsernameCanonical($applicantnameUnique);

            $applicant->setEmailCanonical($applicant->getEmail());
            $applicant->setPassword("");
            $applicant->setCreatedby('manual');

            $default_time_zone = $this->getParameter('default_time_zone');
            $applicant->getPreferences()->setTimezone($default_time_zone);
            $applicant->setLocked(true);
            if( $initialStatusName == "draft" ) {
                $applicant->setLocked(false);
            }

            //exit('form valid');

            $this->calculateScore($fellowshipApplication);

            $this->processDocuments($fellowshipApplication);

            $this->assignFellAppAccessRoles($fellowshipApplication);

            //create reference hash ID
            $fellappRecLetterUtil->generateFellappRecLetterId($fellowshipApplication);

            $fellowshipApplication->autoSetRecLetterReceived();

            //set update author application
//            $em = $this->getDoctrine()->getManager();
//            $userUtil = new UserUtil();
//            $sc = $this->container->get('security.context');
//            $userUtil->setUpdateInfo($fellowshipApplication,$em,$sc);

            //exit('eof new applicant');

            $em = $this->getDoctrine()->getManager();
            $em->persist($fellowshipApplication);
            $em->persist($applicant);
            $em->flush();

//            if( $initialStatusName == "draft" ) {
//                //TODO: send email to a user if draft: Please confirm this email address ...
//                //sendEmailWithActivationLink
//                //employees_activate_account
//                //$signUp = new Signup();
//            }
//            if( $initialStatusName == "draft" ) {
//
//            }

            //update report if report does not exists
            //if( count($entity->getReports()) == 0 ) {
            $fellappRepGen = $this->container->get('fellapp_reportgenerator');
            $fellappRepGen->addFellAppReportToQueue( $fellowshipApplication->getId(), 'overwrite' );
            $this->addFlash(
                'notice',
                'A new Complete Fellowship Application PDF will be generated.'
            );
            //}

            //set logger for update
            $userSecUtil = $this->container->get('user_security_utility');
            $event = "Fellowship Application with ID " .
                $fellowshipApplication->getId() .
                " has been created by " .
                $applicant->getDisplayOrFirstLastname();
            $userSecUtil->createUserEditEvent($this->getParameter('fellapp.sitename'),$event,$user,$fellowshipApplication,$request,'Fellowship Application Updated');

            //return $this->redirect($this->generateUrl('fellapp_show',array('id' => $fellowshipApplication->getId())));

            $this->addFlash(
                'notice',
                $event
            );

            //$security->logout();
            //$security->logout(false); //This will trigger onLogout event
            $userSecUtil = $this->container->get('user_security_utility');
            $userSecUtil->userLogout(null);

            if( $initialStatusName == "draft" ) {
                //Send email with hash:
                //A draft fellowship application has been submitted specifying your email address as belonging to the applicant.
                $fellappUtil->confirmationEmail($applicant);
                return $this->redirect($this->generateUrl('fellapp_login'));
            }
            if( $initialStatusName == "active" ) {
                $fellappUtil->confirmationEmail($applicant);
                //return $this->redirect($this->generateUrl('fellapp_login',array('id' => $fellowshipApplication->getId())));
                return $this->redirect($this->generateUrl('fellapp_login'));
            }
        }

        if( $routeName == "fellapp_apply" || $routeName == "fellapp_apply_post" ) {
            if ($userSecUtil->getSiteSettingParameter('captchaEnabled') === true) {
                $captchaSiteKey = $userSecUtil->getSiteSettingParameter('captchaSiteKey');
            }
        }

        //echo 'form invalid <br>';
        //exit('form invalid');

        return array(
            'form' => $form->createView(),
            'entity' => $fellowshipApplication,
            'pathbase' => 'fellapp',
            'cycle' => 'new',
            'sitename' => $this->getParameter('fellapp.sitename'),
            'captchaSiteKey' => $captchaSiteKey,
            'route_path' => $routeName
        );

    }

}
