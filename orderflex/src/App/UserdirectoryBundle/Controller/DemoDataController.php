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

namespace App\UserdirectoryBundle\Controller;



use App\UserdirectoryBundle\Controller\OrderAbstractController;

use App\UserdirectoryBundle\Entity\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DemoDataController extends OrderAbstractController
{

    #[Route(path: '/reset-demo-data/', name: 'employees_reset_demo_data', methods: ['GET'])]
    public function resetDemoDataAction(Request $request)
    {

        if (!$this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }


        //Flash
        $this->addFlash(
            'notice',
            "Demo Data"
        );

        return $this->redirectToRoute('employees_home');
    }
    



}
