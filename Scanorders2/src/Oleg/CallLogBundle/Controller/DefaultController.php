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

        //testing
        //metaphone (if enabled)
        //$userServiceUtil = $this->get('user_service_utility');
        //$userServiceUtil->metaphoneTest();

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
