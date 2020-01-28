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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 5/6/2016
 * Time: 4:25 PM
 */

namespace App\VacReqBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

//Based on depreciated adesigns/calendar-bundle

//use ADesigns\CalendarBundle\Event\CalendarEvent;
//use ADesigns\CalendarBundle\Entity\EventEntity;


class CalendarEventListener
{

    protected $em;
    protected $container;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->em = $em;
        $this->container = $container;
    }

    public function loadEvents(CalendarEvent $calendarEvent)
    {
        //ADesigns\CalendarBundle is not compatible with Symfony 4
        return;
    }

    public function loadEventsOrig(CalendarEvent $calendarEvent)
    {
        //$vacreqUtil = $this->container->get('vacreq_util');
        //$dateformat = 'M d Y';

        $startDate = $calendarEvent->getStartDatetime();
        $endDate = $calendarEvent->getEndDatetime();

        // The original request so you can get filters from the calendar
        // Use the filter in your query for example

        $request = $calendarEvent->getRequest();
        $groupId = $request->get('groupId');
        //echo "filter:".$filter.";";

        $filter = array('groupId'=>$groupId);

        $this->setCalendar( $calendarEvent, "requestBusiness", $startDate, $endDate, $filter );
        $this->setCalendar( $calendarEvent, "requestVacation", $startDate, $endDate, $filter );

        return;
    }

    public function setCalendar( $calendarEvent, $requestTypeStr, $startDate, $endDate, $filter ) {
        //echo "ID";
        $dateformat = 'M d Y';

        //$vacreqUtil = $this->container->get('vacreq_util');
        //$requests = $vacreqUtil->getApprovedRequestStartedBetweenDates( $requestTypeStr, $startDate, $endDate );

        $groupId = $filter['groupId'];

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequest');
        $dql = $repository->createQueryBuilder('request');

        $dql->select('request');
        //$dql->select('DISTINCT requestType.startDate,requestType.endDate,requestType.id as requestTypeId,request.id as requestId');

        //$dql->leftJoin("request.user", "user");

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $dql->leftJoin("request.requestBusiness", "requestType");
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $dql->leftJoin("request.requestVacation", "requestType");
        }

        $dql->where("requestType.id IS NOT NULL");
        //$dql->andWhere('requestType.status = :statusApproved');
        $dql->andWhere('requestType.status = :statusApproved OR requestType.status = :statusPending');
        $dql->andWhere('(requestType.startDate BETWEEN :startDate and :endDate)');

        //$dql->andWhere('request.institution = :groupId');
        if( $groupId ) {
            $dql->leftJoin("request.institution","institution");
            $institution = $this->em->getRepository('AppUserdirectoryBundle:Institution')->find($groupId);
            $instStr = $this->em->getRepository('AppUserdirectoryBundle:Institution')->selectNodesUnderParentNode($institution,"institution",false);
            //echo "instStr=".$instStr."<br>";
            $dql->andWhere($instStr);
        }

        //select user, distinct start, end dates
        //$dql->groupBy('request.user,requestType.startDate,requestType.endDate');

        $query = $this->em->createQuery($dql);

        $query->setParameter('statusPending', 'pending');
        $query->setParameter('statusApproved', 'approved');
        $query->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
        $query->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));

        $requests = $query->getResult();

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $backgroundColor = "#bce8f1";
            $requestName = "Business Travel";
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $backgroundColor = "#b2dba1";
            $requestName = "Vacation";
        }

        $getMethod = "get".$requestTypeStr;

        // $companyEvents and $companyEvent in this example
        // represent entities from your database, NOT instances of EventEntity
        // within this bundle.
        //
        // Create EventEntity instances and populate it's properties with data
        // from your own entities/database values.

        $requestArr = array();

        foreach( $requests as $requestFull ) {

            $request = $requestFull->$getMethod(); //sub request
            //echo "ID=".$request->getId();

            //check if dates not exact
            $subjectUserId = $requestFull->getUser()->getId();
            //init array with key as user id
            if( !array_key_exists($subjectUserId, $requestArr) ) {
                $requestArr[$subjectUserId] = array();
            }
            //check if date is already exists
            if( in_array($request->getStartDate(), $requestArr[$subjectUserId]) ) {
                continue;
            } else {
                array_push($requestArr[$subjectUserId], $request->getStartDate());
            }

            //isGranted by action might be heavy method
            $fast = true; //if fast is true => calendar appears in 2-3 sec, otherwise ~25 sec
            if( $fast ) {
                //$url = null;
                $url = $this->container->get('router')->generate(
                    'vacreq_showuser',
                    array(
                        'id' => $requestFull->getUser()->getId()
                    )
                    //UrlGeneratorInterface::ABSOLUTE_URL
                );
            } else {
                if ($this->container->get('security.authorization_checker')->isGranted("read", $requestFull)) {
                    $url = $this->container->get('router')->generate(
                        'vacreq_show',
                        array(
                            'id' => $requestFull->getId()
                        )
                    //UrlGeneratorInterface::ABSOLUTE_URL
                    );
                } else {
                    $url = $this->container->get('router')->generate(
                        'vacreq_showuser',
                        array(
                            'id' => $requestFull->getUser()->getId()
                        )
                    //UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }
            }

            //$userNameLink = '<a href="'.$url.'">'.$requestFull->getUser().'</a>';

            // create an event with a start/end time, or an all day event
            $title = "";
            //$title .= "(ID ".$requestFull->getId().") ";
            //$title .= "(EID ".$requestFull->getExportId().") ";
            $title .= $requestFull->getUser() . " " . $requestName;
            //$title .= $userNameLink . " " . $requestName;

            //$finalStartEndDates = $request->getFinalStartEndDates();
            $startDate = $request->getStartDate();
            $endDate = $request->getEndDate();
            $title .= " (" . $startDate->format($dateformat) . " - " . $endDate->format($dateformat);
            //$title .= ", back on ".$requestFull->getFirstDayBackInOffice()->format($dateformat).")";
            $title .= ")";

            if( $request->getStatus() == 'pending' ) {
                $backgroundColorCalendar = "#fcf8e3";
                $title = $title." Pending Approval";
            } else {
                $backgroundColorCalendar = $backgroundColor;
            }

            $eventEntity = new EventEntity($title, $startDate, $endDate, true);

            //optional calendar event settings
            $eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
            $eventEntity->setBgColor($backgroundColorCalendar); //set the background color of the event's label
            $eventEntity->setFgColor('#2F4F4F'); //set the foreground color of the event's label

            if( $url ) {
                $eventEntity->setUrl($url); // url to send user to when event label is clicked
            }

            //$eventEntity->setCssClass('my-custom-class'); // a custom class you may want to apply to event labels

            //finally, add the event to the CalendarEvent for displaying on the calendar
            $calendarEvent->addEvent($eventEntity);

        }
    }



}

