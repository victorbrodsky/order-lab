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


use App\VacReqBundle\Form\VacReqCalendarFilterType;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\VacReqBundle\Form\VacReqHolidayFilterType;
use App\VacReqBundle\Util\ICalendar;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//use ADesigns\CalendarBundle\Event\CalendarEvent;
//use ADesigns\CalendarBundle\Entity\EventEntity;

//vacreq site

class CalendarController extends OrderAbstractController
{

    /**
     * Template("AppVacReqBundle/Calendar/calendar.html.twig")
     * show the names of people who are away that day (one name per "event"/line).
     *
     * @Route("/away-calendar/", name="vacreq_awaycalendar", methods={"GET"})
     * @Template("AppVacReqBundle/Calendar/calendar-tattali.html.twig")
     */
    public function awayCalendarAction(Request $request) {

        if(
            false == $this->isGranted('ROLE_VACREQ_OBSERVER') &&
            false == $this->isGranted('ROLE_VACREQ_SUBMITTER') &&
            false == $this->isGranted('ROLE_VACREQ_PROXYSUBMITTER') &&
            false == $this->isGranted('ROLE_VACREQ_APPROVER') &&
            false == $this->isGranted('ROLE_VACREQ_SUPERVISOR')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $vacreqUtil = $this->container->get('vacreq_util');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $params = array();
        $params['em'] = $em;
        $params['supervisor'] = $this->isGranted('ROLE_VACREQ_SUPERVISOR');

        ///// NOT USED /////
//        if(0) {
//            //get submitter groups: VacReqRequest, create
//            $groupParams = array();
//
//            $groupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'create');
//            $groupParams['permissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus');
//            if ($this->isGranted('ROLE_VACREQ_ADMIN') == false) {
//                $groupParams['exceptPermissions'][] = array('objectStr' => 'VacReqRequest', 'actionStr' => 'changestatus-carryover');
//            }
//
//            //to get the select filter with all groups under the supervisor group, find the first upper supervisor of this group.
//            if ($this->isGranted('ROLE_VACREQ_SUPERVISOR')) {
//                $subjectUser = $user;
//            } else {
//                $groupParams['asSupervisor'] = true;
//                $subjectUser = $vacreqUtil->getClosestSupervisor($user);
//            }
//            //echo "subjectUser=".$subjectUser."<br>";
//            if (!$subjectUser) {
//                $subjectUser = $user;
//            }
//
//            $organizationalInstitutions = $vacreqUtil->getGroupsByPermission($subjectUser,$groupParams);
//        }
        ///// EOF NOT USED /////

        $organizationalInstitutions = $vacreqUtil->getAllGroupsByUser($user);
//        foreach($organizationalInstitutions as $id=>$organizationalInstitution) {
//            echo $id.": group=".$organizationalInstitution."<br>";
//        }

        //$params['organizationalInstitutions'] = $organizationalInstitutions;
        $params['organizationalInstitutions'] = $userServiceUtil->flipArrayLabelValue($organizationalInstitutions);   //flipped

        $groupId = $request->query->get('group');
        //echo "groupId=".$groupId."<br>";

        $params['groupId'] = $groupId;

        $filterform = $this->createForm(VacReqCalendarFilterType::class, null, array('form_custom_value'=>$params));


        return array(
            'vacreqfilter' => $filterform->createView(),
            'groupId' => $groupId
        );
    }


    
    /**
     * NOT USED
     *
     * @Route("/vacreq-import-holiday-dates/", name="vacreq_import_holiday_dates", methods={"GET"}, options={"expose"=true})
     */
    public function importHolidayDatesAction(Request $request)
    {

        if ( false == $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $response = new Response();

        $holidayDatesUrl = $request->get('holidayDatesUrl');
        echo "holidayDatesUrl=$holidayDatesUrl <br>";
        
        //https://www.apptha.com/blog/import-google-calendar-events-in-php/
        /* Getting events from isc file */
        $obj = new ICalendar();

        try{
            $icsEvents = $obj->getIcsEventsAsArray( $holidayDatesUrl );
        }

        catch(\Exception $e) {
            //echo "Error:".$e->getMessage();
            //exit();
            $response->setContent($e->getMessage());
            return $response;
        }

        dump($icsEvents);

        //Header
//        "BEGIN" => "VCALENDAR"
//        "PRODID" => "-//Google Inc//Google Calendar 70.9054//EN"
//        "VERSION" => "2.0"
//        "CALSCALE" => "GREGORIAN"
//        "METHOD" => "PUBLISH"
//        "X-WR-CALNAME" => "Holidays in United States"
//        "X-WR-TIMEZONE" => "UTC"
//        "X-WR-CALDESC" => "Holidays and Observances in United States"

        //Event
//        "BEGIN" => "VEVENT"
//        "DTSTART;VALUE=DATE" => "20241111"
//        "DTEND;VALUE=DATE" => "20241112"
//        "DTSTAMP" => "20230117T161120Z"
//        "UID" => "20241111_8ab7b5tg01ghdu8ufobkduimfk@google.com"
//        "CLASS" => "PUBLIC"
//        "CREATED" => "20220922T154625Z"
//        "DESCRIPTION" => "Public holiday"
//        "LAST-MODIFIED" => "20220922T154625Z"
//        "SEQUENCE" => "0"
//        "STATUS" => "CONFIRMED"
//        "SUMMARY" => "Veterans Day"
//        "TRANSP" => "TRANSPARENT"
//        "END" => "VEVENT

        //add the retrieved US holiday titles and dates for the next 20 years from the downloaded file
        // to the Platform List Manager into a new Platform list manager list titled “Holidays”
        // Title: [holiday title],
        // New “Date” Attribute for each item in this list: [date],
        // a New “Country” attribute for each item in this list, set to [US] by default for imported values) and
        // a new “Observed By” field empty for now but showing all organizational groups in a Select2 drop down menu.

        $count = 0;

        foreach($icsEvents as $event) {
            //echo $event;
            if( isset($event['BEGIN']) ) {
                if( trim($event['BEGIN']) == 'VCALENDAR' ) {
                    continue;
                }
            } else {
                continue;
            }

            $valueBegin = trim($event['BEGIN']);
            //echo "valueBegin=[$valueBegin] <br>";
            if( $valueBegin != 'VEVENT' ) {
               continue;
            }

            $count++;

            //$class = isset($event['CLASS']) ? trim($event['CLASS']) : NULL; //PUBLIC
            $summary = isset($event['SUMMARY']) ? trim($event['SUMMARY']) : NULL; //Thanksgiving Day
            $date = isset($event['DTSTART;VALUE=DATE']) ? trim($event['DTSTART;VALUE=DATE']) : NULL; //20221124

            echo $count . ": " . $date . ", " . $summary . "<br>";
        }

        exit("count=".$count);

        //parse the downloaded file and add the retrieved US holiday titles and dates
        // for the next 20 years from the downloaded file to the Platform List Manager
        // into a new Platform list manager list titled “Holidays”

        $response->setContent("OK");
        return $response;
    }
    
    /**
     * @Route("/holiday-dates/", name="vacreq_holiday_dates", methods={"GET"})
     * @Template("AppVacReqBundle/Holidays/holiday-dates.html.twig")
     */
    public function holidayDatesAction(Request $request) {

        if(
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $filterParams = $request->query->all();
        if( count($filterParams) == 0 ) {
            $thisYear = date("Y");
            $nextYear = (int)$thisYear + 1;
            //$endYear = date("Y+1");
            //$defaultYears = array('2021','2022');
            $defaultYears = "$thisYear,$nextYear";
            return $this->redirect( $this->generateUrl('vacreq_holiday_dates',
                array(
                    'filter[years]' => $defaultYears, //$currentYear,
                    //'filter[years]' => +2020%2C+2021
                    //'filter[endYear]' => $endYear,
                )
            ));
        }


        $em = $this->getDoctrine()->getManager();

        //$holidays = $em->getRepository('AppVacReqBundle:VacReqHolidayList')->findAll();
        //echo "holidays count=".count($holidays)."<br>";

        $repository = $em->getRepository('AppVacReqBundle:VacReqHolidayList');
        $dql = $repository->createQueryBuilder("holiday");

        //process filter
        $params = array();
        $filterRes = $this->processFilter( $dql, $request, $params );
        $filterform = $filterRes['form'];
        $dqlParameters = $filterRes['dqlParameters'];
        $filtered = $filterRes['filtered'];

        $limit = 30;
        $query = $em->createQuery($dql);
        //echo "query=".$query->getSql()."<br>";

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        $paginationParams = array(
            'defaultSortFieldName' => 'holiday.holidayDate', //createDate
            'defaultSortDirection' => 'ASC',
            'wrap-queries'=>true //use "doctrine/orm": "v2.4.8". ~2.5 causes error: Cannot select distinct identifiers from query with LIMIT and ORDER BY on a column from a fetch joined to-many association. Use walker.
        );

        $paginator  = $this->container->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );

        $dql->select('holiday');

        $title = 'Holiday Dates';

        $routeName = $request->get('_route');

        return array(
            'filterform' => $filterform->createView(),
            'pagination' => $pagination,
            'title' => $title,
            'routename' => $routeName,
            //'pageTitle' => $pageTitle,
            //'holidays' => $holidays
            //'vacreqfilter' => $filterform->createView(),
            //'groupId' => $groupId
        );
    }
    public function processFilter( $dql, $request, $params ) {
        $dqlParameters = array();
        $filterRes = array();
        $filtered = false;

        $em = $this->getDoctrine()->getManager();
        $params['em'] = $em;

        //create filter form
        $filterform = $this->createForm(VacReqHolidayFilterType::class, null, array(
            'method' => 'GET',
            'form_custom_value' => $params
        ));

        $filterform->handleRequest($request);

        if( $filterform->has('years') ) {
            $years = $filterform['years']->getData();
        } else {
            $years = null;
        }

        if( $years ) {
            $yearsArr = explode(",",$years);
            $yearWhereArr = array();
            foreach($yearsArr as $year) {
                $yearWhereArr[] = "(YEAR(holiday.holidayDate) = $year)";
            }
            $yearWhereStr = implode(" OR ",$yearWhereArr);
            $dql->andWhere($yearWhereStr);
        }

        $filterRes['form'] = $filterform;
        $filterRes['dqlParameters'] = $dqlParameters;
        $filterRes['filtered'] = $filtered;

        return $filterRes;
    }

    /**
     * @Route("/observed-holidays/", name="vacreq_observed_holidays", methods={"GET"})
     * @Template("AppVacReqBundle/Holidays/observed-holidays.html.twig")
     */
    public function observedHolidaysction(Request $request) {

        if(
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $filterParams = $request->query->all();
        if( count($filterParams) == 0 ) {
            $thisYear = date("Y");
            $defaultYears = $thisYear;
            return $this->redirect( $this->generateUrl(
                'vacreq_observed_holidays',
                array(
                    'filter[years]' => $defaultYears, //$currentYear,
                )
            ));
        }
        
        $em = $this->getDoctrine()->getManager();

        //$holidays = $em->getRepository('AppVacReqBundle:VacReqHolidayList')->findAll();
        //echo "holidays count=".count($holidays)."<br>";

        $repository = $em->getRepository('AppVacReqBundle:VacReqHolidayList');
        $dql = $repository->createQueryBuilder("holiday");

        //process filter
        $params = array();
        $filterRes = $this->processFilter( $dql, $request, $params );
        $filterform = $filterRes['form'];
        $dqlParameters = $filterRes['dqlParameters'];
        $filtered = $filterRes['filtered'];

        $limit = 30;
        $query = $em->createQuery($dql);
        //echo "query=".$query->getSql()."<br>";

        if( count($dqlParameters) > 0 ) {
            $query->setParameters( $dqlParameters );
        }

        $paginationParams = array(
            'defaultSortFieldName' => 'holiday.holidayDate', //createDate
            'defaultSortDirection' => 'ASC',
            'wrap-queries'=>true //use "doctrine/orm": "v2.4.8". ~2.5 causes error: Cannot select distinct identifiers from query with LIMIT and ORDER BY on a column from a fetch joined to-many association. Use walker.
        );

        $paginator  = $this->container->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1),   /*page number*/
            $limit,                                         /*limit per page*/
            $paginationParams
        );

        $dql->select('holiday');

        $title = 'Observed Holidays';

        $routeName = $request->get('_route');

        return array(
            'filterform' => $filterform->createView(),
            'pagination' => $pagination,
            'title' => $title,
            'routename' => $routeName,
            //'pageTitle' => $pageTitle,
            //'holidays' => $holidays
            //'vacreqfilter' => $filterform->createView(),
            //'groupId' => $groupId
        );
    }

    /**
     * @Route("/update-holiday-dates/", name="vacreq_update_holiday_dates", methods={"GET"})
     * @Template("AppVacReqBundle/Holidays/holiday-dates.html.twig")
     */
    public function updateHolidayDatesAction(Request $request) {

        if(
            false == $this->isGranted('ROLE_VACREQ_ADMIN')
        ) {
            return $this->redirect( $this->generateUrl('vacreq-nopermission') );
        }

        $country = 'USA';
        $year = (int) date('Y');
        $startYear = $year - 20;
        $endYear = $year + 20;
        //$year = 2025;

        // Use the factory to create a new holiday provider instance
        //$holidays = Yasumi::create($country, $year);
        //dump($holidays);

        $vacreqCalendarUtil = $this->container->get('vacreq_calendar_util');
        //$holidays = $vacreqCalendarUtil->getHolidaysPerYear($country,2023);
        //dump($holidays);

        $res = $vacreqCalendarUtil->processHolidaysRangeYears($country,$startYear,$endYear);
        //dump($holidays);

        //Flash
        $this->addFlash(
            'notice',
            $res
        );

        return $this->redirect( $this->generateUrl('vacreq_holiday_dates') );
    }

    /**
     * @Route("/save-observed-holidays-ajax/", name="vacreq_save_observed_holidays_ajax", methods={"GET"}, options={"expose"=true})
     */
    public function saveObservedHolidaysAjaxAction(Request $request)
    {

        if ( false == $this->isGranted('ROLE_VACREQ_ADMIN') ) {
            return $this->redirect($this->generateUrl('vacreq-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $response = new Response();

        $checkedHolidays = $request->get('checkedHolidays');
        //echo "checkedHolidays=".count($checkedHolidays)."<br>";

        $unCheckedHolidays = $request->get('unCheckedHolidays');
        //echo "unCheckedHolidays=".count($unCheckedHolidays)."<br>";
        //exit(1);

        //TODO: process unchecked
        //TODO: confirmed success holidays
        //if( !$checkedHolidays ) {
        //    $response->setContent("Nothing to do: Holidays are not selected");
        //    return $response;
        //}

        //dump($checkedHolidays);

        $errorArr = array();
        $noteArr = array();
        //$count = 0;

        foreach($checkedHolidays as $checkedHolidayId) {
            //echo $count . ": checkedHoliday=" . $checkedHoliday . "<br>";
            $holiday = $em->getRepository('AppVacReqBundle:VacReqHolidayList')->find($checkedHolidayId);
            if( !$holiday ) {
                $errorArr[] = "VacReqHolidayList not found by checked ID $checkedHolidayId";
                continue;
            }

            $name = $holiday->getName(); //name + date
            $holidayName = $holiday->getHolidayName();
            $holidayDate = $holiday->getHolidayDate();
            $holidayDateStr = "N/A";
            if( $holidayDate ) {
                $holidayDateStr = $holidayDate->format('d-m-Y');
            }
            $country = $holiday->getCountry();
            $institutions = $holiday->getInstitutions();
            //echo $count . ": $name, $holidayName, $holidayDateStr, $country, ".$holiday->getInstitutionsStr()." <br>";

            //add "active" flag to holiday list
            $holiday->setObserved(true);
            //$errorArr[] = "Saved $holiday";
            $em->flush();

            $noteArr[] = $holiday->getHolidayName()." (".$holidayDateStr.")"." is set to active";

            //$count++;

            //save the checked holiday names only (NOT dates) in a new list
            // in Platform List Manager titled “Observed holidays” in step E above.
//            $observedHoliday = $em->getRepository('AppVacReqBundle:VacReqObservedHolidayList')->find($name);
//            if( $observedHoliday ) {
//                //update
//                $observedHoliday->setHolidayName($holidayName);
//                //$observedHoliday->setHolidayDate($holidayDate);
//            }

        }

        foreach($unCheckedHolidays as $unCheckedHolidayId) {
            $holiday = $em->getRepository('AppVacReqBundle:VacReqHolidayList')->find($unCheckedHolidayId);
            if( !$holiday ) {
                $errorArr[] = "VacReqHolidayList not found by unchecked ID $unCheckedHolidayId";
                continue;
            }
            $holiday->setObserved(false);
            //$errorArr[] = "Saved $holiday";
            $em->flush();

            $holidayDate = $holiday->getHolidayDate();
            $holidayDateStr = "N/A";
            if( $holidayDate ) {
                $holidayDateStr = $holidayDate->format('d-m-Y');
            }

            $noteArr[] = $holiday->getHolidayName()." (".$holidayDateStr.")"." is set to inactive";
        }

        //exit("count=".$count);

        $res = array();
        if( count($errorArr) ) {
            $res['flag'] = "NOTOK: ".implode("; ",$errorArr);
            $res['note'] = "Error: ".implode("; ",$errorArr);
        } else {
            $res['flag'] = "OK";
            $res['note'] = "Successfully Saved<br>".implode("; ",$errorArr);
        }

        $response->setContent($res);
        return $response;
    }


}
