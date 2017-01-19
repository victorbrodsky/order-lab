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
     * Complex Patient List
     * @Route("/patient-list/{listname}", name="calllog_complex_patient_list")
     * @Template("OlegCallLogBundle:PatientList:complex-patient-list.html.twig")
     */
    public function complexPatientListAction(Request $request, $listname)
    {

        //get list name by $listname, convert it to the first char as Upper case and use it to find the list in DB
        //for now use the mock page complex-patient-list.html.twig

        //src/Oleg/CallLogBundle/Resources/views/PatientList/complex-patient-list.html.twig
        return array(
            //'entity' => $entity,
            //'form' => $form->createView(),
            //'cycle' => $cycle,
            'title' => "Complex Patient List",
        );
    }


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

}
