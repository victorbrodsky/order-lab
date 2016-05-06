<?php

namespace Oleg\VacReqBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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



        return;
    }

}
