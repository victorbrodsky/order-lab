<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 1/27/2020
 * Time: 4:48 PM
 */

namespace App\VacReqBundle\EventListener;



use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


//Based on depreciated tattali/calendar-bundle

class CalendarSubscriber implements EventSubscriberInterface
{

    protected $em;
    protected $container;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container ) {
        $this->em = $em;
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

//    public function onCalendarSetData_ORIG(CalendarEvent $calendar)
//    {
//        //exit('111');
//
//        $start = $calendar->getStart();
//        $end = $calendar->getEnd();
//        $filters = $calendar->getFilters();
//
//        // You may want to make a custom query from your database to fill the calendar
//
//        $calendar->addEvent(new Event(
//            'Event 1',
//            new \DateTime('Tuesday this week'),
//            new \DateTime('Wednesdays this week')
//        ));
//
//        // If the end date is null or not defined, it creates a all day event
//        $calendar->addEvent(new Event(
//            'All day event',
//            new \DateTime('Friday this week')
//        ));
//    }

    public function onCalendarSetData(CalendarEvent $calendarEvent) {

        $startDate = $calendarEvent->getStart();
        $endDate = $calendarEvent->getEnd();

        // The original request so you can get filters from the calendar
        // Use the filter in your query for example

        $groupId = NULL;
        $filters = $calendarEvent->getFilters();
        $groupId = $filters['groupId'];
        //echo "filter:".$groupId.";";

        $filter = array('groupId'=>$groupId);

        $this->setCalendar( $calendarEvent, "requestBusiness", $startDate, $endDate, $filter );
        $this->setCalendar( $calendarEvent, "requestVacation", $startDate, $endDate, $filter );
    }

    public function setCalendar( $calendarEvent, $requestTypeStr, $startDate, $endDate, $filter ) {

        //echo "ID";
        $dateformat = 'M d Y';

        //$vacreqUtil = $this->container->get('vacreq_util');
        //$requests = $vacreqUtil->getApprovedRequestStartedBetweenDates( $requestTypeStr, $startDate, $endDate );

        if( isset($filter['groupId']) ) {
            $groupId = $filter['groupId'];
        } else {
            $groupId = NULL;
        }

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
            $subjectUserId = $requestFull->getUser()->getId()."-".$requestFull->getId();
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

            //$title = "EventID=".$request->getId();
            //echo $title;

            $eventEntity = new Event($title, $startDate, $endDate);

            //optional calendar event settings
            $eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
            //$eventEntity->setBgColor($backgroundColorCalendar); //set the background color of the event's label
            //$eventEntity->setFgColor('#2F4F4F'); //set the foreground color of the event's label

            $eventEntity->setOptions([
                'backgroundColor' => $backgroundColorCalendar,
                'textColor' => '#2F4F4F',
            ]);

            if( $url ) {
                //$eventEntity->setUrl($url); // url to send user to when event label is clicked
                $eventEntity->addOption(
                    'url',
                    $url
                );
            }

            //finally, add the event to the CalendarEvent for displaying on the calendar
            $calendarEvent->addEvent($eventEntity);

//            $calendarEvent->addEvent(new Event(
//                'Event 1',
//                new \DateTime('Tuesday this week'),
//                new \DateTime('Wednesdays this week')
//            ));

        }//foreach

    }

}