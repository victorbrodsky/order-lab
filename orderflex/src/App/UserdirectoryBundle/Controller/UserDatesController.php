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
 *
 *  Created by Oleg Ivanov
 */

namespace App\UserdirectoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class UserDatesController extends OrderAbstractController
{

    /**
     * @Route("/employment-dates", name="employees_user_dates")
     * @Template("AppUserdirectoryBundle/UserDates/user_dates.html.twig")
     */
    public function aboutAction( Request $request ) {

        // [Checkmark column with title of “Deactivate”] |
        // LastName |
        // FirstName |
        // Degree(s) |
        // Email |
        // Organizational Group(s) |
        // Title(s) |
        // Latest Employment Start Date |
        // Latest Employment End Date |
        // Account Status |
        // Action

        return array(
            'title' => 'Employment dates'
        );
    }

    

}
