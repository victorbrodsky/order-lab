<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 5/6/2016
 * Time: 4:25 PM
 */

namespace Oleg\VacReqBundle\EventListener;


use ADesigns\CalendarBundle\Event\CalendarEvent;
use ADesigns\CalendarBundle\Entity\EventEntity;
use Doctrine\ORM\EntityManager;



class CalendarEventListener
{

    protected $em;
    protected $sc;
    protected $container;

    public function __construct( $em, $sc, $container ) {
        $this->em = $em;
        $this->sc = $sc;
        $this->container = $container;
    }

    public function loadEvents(CalendarEvent $calendarEvent)
    {
        //$vacreqUtil = $this->container->get('vacreq_util');
        //$dateformat = 'M d Y';

        $startDate = $calendarEvent->getStartDatetime();
        $endDate = $calendarEvent->getEndDatetime();

        // The original request so you can get filters from the calendar
        // Use the filter in your query for example

        $request = $calendarEvent->getRequest();
        $filter = $request->get('filter');


        $this->setCalendar( $calendarEvent, "requestBusiness", $startDate, $endDate );
        $this->setCalendar( $calendarEvent, "requestVacation", $startDate, $endDate );
        return;

        // load events using your custom logic here,
        // for instance, retrieving events from a repository

//        $requests = $this->em->getRepository('OlegVacReqBundle:VacReqRequest')
//            ->createQueryBuilder('request')
//            ->where('request.firstDayAway BETWEEN :startDate and :endDate')
//            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
//            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
//            ->getQuery()->getResult();

//        $requestsB = $vacreqUtil->getApprovedRequestStartedBetweenDates( "requestBusiness", $startDate, $endDate );
//
//        // $companyEvents and $companyEvent in this example
//        // represent entities from your database, NOT instances of EventEntity
//        // within this bundle.
//        //
//        // Create EventEntity instances and populate it's properties with data
//        // from your own entities/database values.
//
//        foreach( $requests as $request ) {
//
//            // create an event with a start/end time, or an all day event
//
//            $title = $request->getUser() . " " . $request->getRequestName(); //"(ID ".$request->getId().") ".
//
//            //Business
//            if( $request->hasBusinessRequest() && $request->hasVacationRequest() ) {
//                $backgroundColor = "#a1b2db";
//            }
//            if( $request->hasBusinessRequest() ) {
//                //$subRequest = $this->getRequestBusiness();
//                $backgroundColor = "#bce8f1";
//            }
//            if( $request->hasVacationRequest() ) {
//                $backgroundColor = "#b2dba1";
//            }
//
//            $finalStartEndDates = $request->getFinalStartEndDates();
//            $startDate = $finalStartEndDates['startDate'];  //$request->getFirstDayAway();
//            $endDate = $finalStartEndDates['endDate'];    //$request->getFirstDayBackInOffice();
//            $title = $title . " (" . $startDate->format($dateformat) . " - " . $endDate->format($dateformat) .
//                ", back on ".$request->getFirstDayBackInOffice()->format($dateformat).")";
//
//            $eventEntity = new EventEntity($title, $startDate, $endDate, true);
//
//            $url = $this->container->get('router')->generate(
//                'vacreq_showuser',
//                array(
//                    'id' => $request->getUser()->getId()
//                )
//            //UrlGeneratorInterface::ABSOLUTE_URL
//            );
//
//            //optional calendar event settings
//            $eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
//            $eventEntity->setBgColor($backgroundColor); //set the background color of the event's label
//            $eventEntity->setFgColor('#2F4F4F'); //set the foreground color of the event's label
//            $eventEntity->setUrl($url); // url to send user to when event label is clicked
//            //$eventEntity->setCssClass('my-custom-class'); // a custom class you may want to apply to event labels
//
//            //finally, add the event to the CalendarEvent for displaying on the calendar
//            $calendarEvent->addEvent($eventEntity);
//
//        }


    }

    public function setCalendar( $calendarEvent, $requestTypeStr, $startDate, $endDate ) {

        $dateformat = 'M d Y';
        $vacreqUtil = $this->container->get('vacreq_util');

        $requests = $vacreqUtil->getApprovedRequestStartedBetweenDates( $requestTypeStr, $startDate, $endDate );

        if( $requestTypeStr == 'business' || $requestTypeStr == 'requestBusiness' ) {
            $backgroundColor = "#bce8f1";
            $requestName = "Business Travel Request";
        }

        if( $requestTypeStr == 'vacation' || $requestTypeStr == 'requestVacation' ) {
            $backgroundColor = "#b2dba1";
            $requestName = "Vacation Request";
        }

        $getMethod = "get".$requestTypeStr;

        // $companyEvents and $companyEvent in this example
        // represent entities from your database, NOT instances of EventEntity
        // within this bundle.
        //
        // Create EventEntity instances and populate it's properties with data
        // from your own entities/database values.

        foreach( $requests as $requestFull ) {

            $request = $requestFull->$getMethod();
            //echo "ID=".$request->getId();

//            $url = $this->container->get('router')->generate(
//                'vacreq_showuser',
//                array(
//                    'id' => $requestFull->getUser()->getId()
//                )
//            //UrlGeneratorInterface::ABSOLUTE_URL
//            );
            $url = $this->container->get('router')->generate(
                'vacreq_show',
                array(
                    'id' => $requestFull->getId()
                )
            //UrlGeneratorInterface::ABSOLUTE_URL
            );

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

            $eventEntity = new EventEntity($title, $startDate, $endDate, true);

            //optional calendar event settings
            $eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
            $eventEntity->setBgColor($backgroundColor); //set the background color of the event's label
            $eventEntity->setFgColor('#2F4F4F'); //set the foreground color of the event's label
            $eventEntity->setUrl($url); // url to send user to when event label is clicked
            //$eventEntity->setCssClass('my-custom-class'); // a custom class you may want to apply to event labels

            //finally, add the event to the CalendarEvent for displaying on the calendar
            $calendarEvent->addEvent($eventEntity);

        }
    }


}

