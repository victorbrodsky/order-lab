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
use Symfony\Bundle\SecurityBundle\Security;


//Based on tattali/calendar-bundle

class CalendarSubscriber implements EventSubscriberInterface
{

    protected $em;
    protected $container;
    protected $security;
    protected $fast;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container, Security $security ) {
        $this->em = $em;
        $this->container = $container;
        $this->security = $security;

        $this->fast = false;
        //$this->fast = true; //if fast is true => calendar appears in 2-3 sec, otherwise ~25 sec (?)
    }

    public static function getSubscribedEvents() : array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendarEvent) {
        $startDate = $calendarEvent->getStart();
        $endDate = $calendarEvent->getEnd();
        //echo "endDate=".$endDate->format('Y-m-d H:i:s')."<br>";

        // The original request so you can get filters from the calendar
        // Use the filter in your query for example

        $groupId = NULL;
        $filters = $calendarEvent->getFilters();
        $groupId = $filters['groupId'];
        //echo "filter:".$groupId.";";

        $filter = array('groupId'=>$groupId);

        $this->setCalendar($calendarEvent, "requestBusiness", $startDate, $endDate, $filter);
        $this->setCalendar($calendarEvent, "requestVacation", $startDate, $endDate, $filter);

        $this->setFloatingCalendar($calendarEvent, $startDate, $endDate, $filter);

        //set Calendar for observed holidays
        $this->setObservedHolidaysCalendar($calendarEvent, $startDate, $endDate, $filter);
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

//            if( $this->security->isGranted("read", $requestFull) ) {
//                exit('read');
//            }
//            exit('111');

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
            //$fast = false;
            //$fast = true; //if fast is true => calendar appears in 2-3 sec, otherwise ~25 sec
            if( $this->fast ) {
                //$url = null;
                $url = $this->container->get('router')->generate(
                    'vacreq_showuser',
                    array(
                        'id' => $requestFull->getUser()->getId()
                    )
                //UrlGeneratorInterface::ABSOLUTE_URL
                );
            } else {
                if(
                    //$this->container->get('security.authorization_checker')->isGranted("read", $requestFull)
                    false == $this->security->isGranted("read", $requestFull)
                ) {
                    //echo "can read <br>";
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

            //endDate is format: 2023-02-28 00:00:00, and the last day in calendar is 27 February
            //Fix, set endDate to beginning of the next day to show the end day correctly
            //echo "endDate=".$endDate->format('Y-m-d H:i:s')."<br>";
            $endDate->modify('+1 day');

            $eventEntity = new Event($title, $startDate, $endDate);

            //optional calendar event settings
            $eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
            //$eventEntity->setBgColor($backgroundColorCalendar); //set the background color of the event's label
            //$eventEntity->setFgColor('#2F4F4F'); //set the foreground color of the event's label

            $eventEntity->setOptions([
                'backgroundColor' => $backgroundColorCalendar,
                'textColor' => '#2F4F4F',
                'classNames' => 'calendar-custom-class',
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

    public function setFloatingCalendar( $calendarEvent, $startDate, $endDate, $filter ) {

        //echo "ID";
        $dateformat = 'M d Y';

        //$vacreqUtil = $this->container->get('vacreq_util');
        //$requests = $vacreqUtil->getApprovedRequestStartedBetweenDates( $requestTypeStr, $startDate, $endDate );

        if( isset($filter['groupId']) ) {
            $groupId = $filter['groupId'];
        } else {
            $groupId = NULL;
        }

        $repository = $this->em->getRepository('AppVacReqBundle:VacReqRequestFloating');
        $dql = $repository->createQueryBuilder('request');

        $dql->select('request');

        $dql->andWhere('request.status = :statusApproved OR request.status = :statusPending');
        $dql->andWhere('(request.floatingDay BETWEEN :startDate and :endDate)');

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

        //floating day color
        $backgroundColor = "#8c0000"; //"#77d39b";
        $requestName = "Floating Day";

        //$getMethod = "get".$requestTypeStr;

        // $companyEvents and $companyEvent in this example
        // represent entities from your database, NOT instances of EventEntity
        // within this bundle.
        //
        // Create EventEntity instances and populate it's properties with data
        // from your own entities/database values.

        $requestArr = array();

        foreach( $requests as $floatingRequest ) {

            $floatingDay = $floatingRequest->getFloatingDay();
            //$request = $floatingRequest->$getMethod(); //sub request
            //echo "ID=".$request->getId();

            //check if dates not exact
            $subjectUserId = $floatingRequest->getUser()->getId()."-".$floatingRequest->getId();
            //init array with key as user id
            if( !array_key_exists($subjectUserId, $requestArr) ) {
                $requestArr[$subjectUserId] = array();
            }
            //check if date is already exists
            if( in_array($floatingDay, $requestArr[$subjectUserId]) ) {
                continue;
            } else {
                array_push($requestArr[$subjectUserId], $floatingDay);
            }

            //isGranted by action might be heavy method
            //$fast = true; //if fast is true => calendar appears in 2-3 sec, otherwise ~25 sec
            if( $this->fast ) {
                //$url = null;
                $url = $this->container->get('router')->generate(
                    'vacreq_showuser',
                    array(
                        'id' => $floatingRequest->getUser()->getId()
                    )
                );
            } else {
                if( false == $this->security->isGranted("read", $floatingRequest) )
                {
                    $url = $this->container->get('router')->generate(
                        'vacreq_floating_show',
                        array(
                            'id' => $floatingRequest->getId()
                        )
                    );
                } else {
                    $url = $this->container->get('router')->generate(
                        'vacreq_showuser',
                        array(
                            'id' => $floatingRequest->getUser()->getId()
                        )
                    );
                }
            }

            //$userNameLink = '<a href="'.$url.'">'.$floatingRequest->getUser().'</a>';

            // create an event with a start/end time, or an all day event
            //[Floating Day Type] Floating Day (Away) for FirstName LastName
            $title = "";
            //$title .= "(ID ".$requestFull->getId().") ";
            //$title .= "(EID ".$requestFull->getExportId().") ";
            $title .= $floatingRequest->getFloatingType() .
                " Floating Day (Away) for " .
                $floatingRequest->getUser()->getUsernameOptimal();
                //" " . $requestName;
            //$title .= $userNameLink . " " . $requestName;

            $startDate = $floatingDay;
            $endDate = $floatingDay;

            $title .= " (" . $floatingDay->format($dateformat);
            //$title .= ", back on ".$floatingRequest->getFirstDayBackInOffice()->format($dateformat).")";
            $title .= ")";

            if( $floatingRequest->getStatus() == 'pending' ) {
                $backgroundColorCalendar = "#fcf8e3";
                $textColor = '#2F4F4F';
                $title = $title." Pending Approval";
            } else {
                $backgroundColorCalendar = $backgroundColor;
                $textColor = '#d1e6e6';
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
                'textColor' => $textColor, //'#d1e6e6', //'#2F4F4F',
                'classNames' => 'calendar-custom-class',
                //'tooltip' => "tttttt"
                //'overlap' => true
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

//    public function setObservedHolidaysCalendar_test( $calendarEvent, $startDate, $endDate, $filter )
//    {
//        $calendarEvent->addEvent(new Event(
//            'Event 1',
//            new \DateTime('Tuesday this week'),
//            new \DateTime('Wednesdays this week')
//        ));
//    }

    public function setObservedHolidaysCalendar( $calendarEvent, $startDate, $endDate, $filter ) {
        //echo "ID";
        $dateformat = 'M d Y';

        //$vacreqUtil = $this->container->get('vacreq_util');
        //$requests = $vacreqUtil->getApprovedRequestStartedBetweenDates( $requestTypeStr, $startDate, $endDate );

        if( isset($filter['groupId']) ) {
            $groupId = $filter['groupId'];
        } else {
            $groupId = NULL;
        }
        $groupId = 0;
        //exit('$groupId='.$groupId);

        $vacreqCalendarUtil = $this->container->get('vacreq_calendar_util');
        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');
        //exit("groupId=$groupId, $startDateStr, $endDateStr");

        if(0) {
            $holidays = $vacreqCalendarUtil->getTrueListHolidaysInRange($startDate, $endDate);
            echo "holidays=" . count($holidays) . "<br>";
            //exit('111');

            //Error: App\VacReqBundle\Entity\VacReqObservedHolidayList has no field or association named observed
            $groupId = 0;
            $observedHolidays = $vacreqCalendarUtil->getObservedHolidaysByInstitution($groupId);
            echo "observedHolidays=" . count($observedHolidays) . "<br>";
            exit('111');
        }

        //$startDate, $endDate, $institutionId
        $holidays = $vacreqCalendarUtil->getHolidaysInRange($startDateStr,$endDateStr,$groupId);
        //echo "holidays=".count($holidays)."<br>";
        //exit('111');

        //floating day color
        //$backgroundColor = "#8c0000"; //"#77d39b";
        $backgroundColorCalendar = "green"; //"#fcf8e3";
        $textColor = 'white'; //'#2F4F4F';
        //$calendarDayName = "Observed Holiday";

        foreach( $holidays as $holiday ) {

            $holidayStartDate = $holiday->getHolidayDate();
            if( !$holidayStartDate ) {
                continue;
            }

            //$holidayStartDateStr = $holidayStartDate->format($dateformat);

            //New Year’s Day observed (holiday)
            $title = $holiday->getHolidayName() . " (holiday)"; //", ". $holidayStartDateStr;

            $eventEntity = new Event($title, $holidayStartDate, $holidayStartDate);

            //optional calendar event settings
            $eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
            //$eventEntity->setBgColor($backgroundColorCalendar); //set the background color of the event's label
            //$eventEntity->setFgColor('#2F4F4F'); //set the foreground color of the event's label

            $eventEntity->setOptions([
                'backgroundColor' => $backgroundColorCalendar,
                'textColor' => $textColor, //'#d1e6e6', //'#2F4F4F',
                'classNames' => 'calendar-custom-class',
                //'tooltip' => "tttttt"
                //'overlap' => true
            ]);

            //finally, add the event to the CalendarEvent for displaying on the calendar
            $calendarEvent->addEvent($eventEntity);
        }//foreach

    }

}