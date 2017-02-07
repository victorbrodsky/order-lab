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



//    /**
//     * Alerts
//     * @Route("/alerts/", name="calllog_alerts")
//     * @Template("OlegCallLogBundle:Default:under_construction.html.twig")
//     */
//    public function alertsAction(Request $request)
//    {
//        return;
//    }


    /**
     * Resources
     * @Route("/resources/", name="calllog_resources")
     * @Template("OlegCallLogBundle:CallLog:resources.html.twig")
     */
    public function resourcesAction(Request $request)
    {
        //return $this->redirectToRoute('user_admin_index');

//        $msg = "Notify Test!!!";
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            $msg
//        );
//            $this->get('session')->getFlashBag()->add(
//                'pnotify',
//                $msg
//            );

        return array(
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'cycle' => $cycle,
            'title' => "Resources",
        );
    }


//    /**
//     * Resources
//     * @Route("/check-encounter-location/", name="calllog_check_encounter_location", options={"expose"=true})
//     * @Method("POST")
//     */
//    public function checkLocationAction(Request $request)
//    {
//        exit("Not used");
//    }

}
