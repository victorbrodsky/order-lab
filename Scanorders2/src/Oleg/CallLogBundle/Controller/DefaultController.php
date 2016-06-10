<?php

namespace Oleg\CallLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{

    /**
     * @Route("/about", name="calllog_about_page")
     * @Template("OlegUserdirectoryBundle:Default:about.html.twig")
     */
    public function aboutAction(Request $request)
    {
        return array('sitename' => $this->container->getParameter('calllog.sitename'));
    }


    /**
     * @Route("/", name="calllog_home")
     * @Template("OlegCallLogBundle:CallLog:home.html.twig")
     */
    public function homeAction(Request $request)
    {

        $title = "Call Case Log";

        return array(
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'cycle' => $cycle,
            'title' => $title,
        );

    }


    /**
     * Alerts
     * @Route("/alerts/", name="calllog_alerts")
     * @Template("OlegCallLogBundle:Default:under_construction.html.twig")
     */
    public function alertsAction(Request $request)
    {
        return;
    }

    /**
     * Call Entry
     * @Route("/call-entry/", name="calllog_callentry")
     * @Template("OlegCallLogBundle:Default:under_construction.html.twig")
     */
    public function callEntryAction(Request $request)
    {
        return;
    }

    /**
     * Complex Patient List
     * @Route("/complex-patient-list/", name="calllog_complex_patient_list")
     * @Template("OlegCallLogBundle:Default:under_construction.html.twig")
     */
    public function complexPatientListAction(Request $request)
    {
        return;
    }


    /**
     * Resources
     * @Route("/resources/", name="calllog_resources")
     * @Template("OlegCallLogBundle:Default:under_construction.html.twig")
     */
    public function resourcesAction(Request $request)
    {
        return;
    }

}
