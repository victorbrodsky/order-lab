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


namespace App\DashboardBundle\Controller;

use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReactController extends OrderAbstractController
{
    //@Template("AppDashboardBundle/Default/index.html.twig")
    /**
     * @Route("/react2/{reactRouting}", name="dashboard_home_react_dashboard", defaults={"reactRouting": null})
     */
    public function index()
    {
        return $this->render('AppDashboardBundle/React/index.html.twig', array('testflag'=>'testflag1'));
//        return $this->render('AppDashboardBundle/React/dashboard.html.twig', array('testflag'=>'testflag1'));
    }

    /**
     * Test React
     * https://www.twilio.com/blog/building-a-single-page-application-with-symfony-php-and-react
     *
     * @Route("/api/charts", name="dashboard_api_charts", options={"expose"=true})
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCharts()
    {
        $users = [
            [
                'id' => 1,
                'name' => '111 Olususi Oluyemi',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/women/50.jpg'
            ],
            [
                'id' => 2,
                'name' => '111 Camila Terry',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/men/42.jpg'
            ],
            [
                'id' => 3,
                'name' => '111 Joel Williamson',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/women/67.jpg'
            ],
            [
                'id' => 4,
                'name' => '111 Deann Payne',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/women/50.jpg'
            ],
            [
                'id' => 5,
                'name' => '111 Donald Perkins',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation',
                'imageURL' => 'https://randomuser.me/api/portraits/men/89.jpg'
            ]
        ];

        $response = new Response();

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        $response->setContent(json_encode($users));

        return $response;
    }

    /**
     * Test React
     * https://www.twilio.com/blog/building-a-single-page-application-with-symfony-php-and-react
     *
     * @Route("/api/session-flash-bag", name="dashboard_api_session_flash_bag", options={"expose"=true})
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSessionFlashBagAction()
    {
        if( $this->get('security.authorization_checker')->isGranted('ROLE_DASHBOARD_USER') ) {
            //ok
        } else {
            return $this->redirect($this->generateUrl($this->getParameter('dashboard.sitename') . '-nopermission'));
        }

        $dashboardUtil = $this->container->get('dashboard_util');

        $flashBag = $dashboardUtil->getSessionFlashBag();

//        $notices = $this->container->get('session')->getFlashBag()->get('notice', []);
//        $warnings = $this->container->get('session')->getFlashBag()->get('warning', []);
//        $errors = $this->container->get('session')->getFlashBag()->get('error', []);
//
//        $flashBag = array(
//            'notice' => $notices,
//            'warning' => $warnings,
//            'error' => $errors,
//        );

        $response = new Response();

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        $response->setContent(json_encode($flashBag));

        return $response;
    }

}
