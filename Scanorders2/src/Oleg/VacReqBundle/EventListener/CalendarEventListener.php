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
        $startDate = $calendarEvent->getStartDatetime();
        $endDate = $calendarEvent->getEndDatetime();

        // The original request so you can get filters from the calendar
        // Use the filter in your query for example

        $request = $calendarEvent->getRequest();
        $filter = $request->get('filter');


        // load events using your custom logic here,
        // for instance, retrieving events from a repository

        //$vacreqUtil = $this->get('vacreq_util');
        //$requests = $vacreqUtil->getApprovedYearDays( $user, $requestTypeStr, $startStr=null, $endStr=null, $type=null, $asObject=false )


        $requests = $this->em->getRepository('OlegVacReqBundle:VacReqRequest')
            ->createQueryBuilder('request')
            ->where('request.firstDayAway BETWEEN :startDate and :endDate')
            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
            ->getQuery()->getResult();

        // $companyEvents and $companyEvent in this example
        // represent entities from your database, NOT instances of EventEntity
        // within this bundle.
        //
        // Create EventEntity instances and populate it's properties with data
        // from your own entities/database values.

        foreach( $requests as $companyEvent ) {

            // create an event with a start/end time, or an all day event
//            if ($companyEvent->getAllDayEvent() === false) {
//                $eventEntity = new EventEntity($companyEvent->getTitle(), $companyEvent->getStartDatetime(), $companyEvent->getEndDatetime());
//            } else {
//                $eventEntity = new EventEntity($companyEvent->getTitle(), $companyEvent->getStartDatetime(), null, true);
//            }

            $title = $companyEvent->getUser()." ".$companyEvent->getRequestName();

            $eventEntity = new EventEntity($title, $companyEvent->getFirstDayAway(), $companyEvent->getFirstDayBackInOffice(), true);

            //$url = $this->container->generateUrl('vacreq_home', $request->query->all());
            //$url = $this->container->get('router')->generateUrl('vacreq_showuser',array('id'=>$companyEvent->getUser()->getId()));
            //$url = 'www.google.com';
            $url = $this->container->get('router')->generate(
                'vacreq_showuser',
                array(
                    'id' => $companyEvent->getUser()->getId()
                )
                //UrlGeneratorInterface::ABSOLUTE_URL
            );
//            echo "url=".$url;
//            exit();

            //optional calendar event settings
            $eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
            //$eventEntity->setBgColor('#FF0000'); //set the background color of the event's label
            //$eventEntity->setFgColor('#FFFFFF'); //set the foreground color of the event's label
            $eventEntity->setUrl($url); // url to send user to when event label is clicked
            //$eventEntity->setCssClass('my-custom-class'); // a custom class you may want to apply to event labels

            //finally, add the event to the CalendarEvent for displaying on the calendar
            $calendarEvent->addEvent($eventEntity);
        }

//        $datetimeEnd = new \DateTime();
//        $datetimeEnd->modify('+1 day');
//        //$calendarEvent = new CalendarEvent(new \DateTime(), $datetimeEnd);
//
//        //$approvedRequests =
//
//        $eventEntity = new EventEntity("Vacation for Oleg", new \DateTime(), null, true);
//
//        //optional calendar event settings
//        $eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
//        $eventEntity->setBgColor('green'); //set the background color of the event's label
//        $eventEntity->setFgColor('#FFFFFF'); //set the foreground color of the event's label
//        $eventEntity->setUrl('http://www.google.com'); // url to send user to when event label is clicked
//        $eventEntity->setCssClass('my-custom-class'); // a custom class you may want to apply to event labels
//
//        //finally, add the event to the CalendarEvent for displaying on the calendar
//        $calendarEvent->addEvent($eventEntity);

    }
}

