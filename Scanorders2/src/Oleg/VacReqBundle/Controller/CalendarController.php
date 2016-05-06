<?php

namespace Oleg\VacReqBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use ADesigns\CalendarBundle\Event\CalendarEvent;
use ADesigns\CalendarBundle\Entity\EventEntity;

//vacreq site

class CalendarController extends Controller
{

    /**
     * show the names of people who are away that day (one name per "event"/line).
     *
     * @Route("/away-calendar/", name="vacreq_awaycalendar")
     * @Method({"GET"})
     * @Template("OlegVacReqBundle:Calendar:calendar.html.twig")
     */
    public function awayCalendarAction(Request $request) {

//        $datetimeEnd = new \DateTime();
//        $datetimeEnd->modify('+1 day');
//        $calendarEvent = new CalendarEvent(new \DateTime(), $datetimeEnd);
//
//        //$approvedRequests =
//
//        $eventEntity = new EventEntity("Vacation for Oleg", new \DateTime(), null, true);
//
//        //optional calendar event settings
//        $eventEntity->setAllDay(true); // default is false, set to true if this is an all day event
//        $eventEntity->setBgColor('#FF0000'); //set the background color of the event's label
//        $eventEntity->setFgColor('#FFFFFF'); //set the foreground color of the event's label
//        $eventEntity->setUrl('http://www.google.com'); // url to send user to when event label is clicked
//        $eventEntity->setCssClass('my-custom-class'); // a custom class you may want to apply to event labels
//
//        //finally, add the event to the CalendarEvent for displaying on the calendar
//        $calendarEvent->addEvent($eventEntity);

        return;
    }

}
