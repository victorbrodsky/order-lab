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

class DefaultController extends OrderAbstractController
{
    /**
     * @Route("/about", name="dashboard_about_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction( Request $request ) {
        return array('sitename'=>$this->getParameter('dashboard.sitename'));
    }

    /**
     * @Route("/", name="dashboard_home")
     * @Template("AppDashboardBundle/Default/index.html.twig")
     */
    public function indexAction( Request $request ) {
        return array('sitename'=>$this->getParameter('dashboard.sitename'));
    }


    /**
     * @Route("/test", name="dashboard_test")
     * @Template("AppDashboardBundle/Default/test.html.twig")
     */
    public function testAction( Request $request ) {

        $testDataArr = array(1,2,3,4,5);

        return array(
            'sitename'=>$this->getParameter('dashboard.sitename'),
            'mytitle' => "This is my test page",
            'testData' => $testDataArr
        );
    }
}
