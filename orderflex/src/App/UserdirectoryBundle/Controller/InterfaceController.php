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

use App\UserdirectoryBundle\Entity\InterfaceTransferList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


class InterfaceController extends OrderAbstractController
{
    
    #[Route(path: '/interface-log', name: 'employees_logger_interface', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Logger/index.html.twig')]
    public function interfaceLoggerAction(Request $request)
    {
        exit("interface-log is under Construction");

        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        $params = array(
            'sitename'=>$this->getParameter('employees.sitename')
        );
        return $this->listLogger($params,$request);
    }



    #[Route(path: '/interface-test', name: 'employees_interface_test', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/Logger/index.html.twig')]
    public function interfaceManagerAction(Request $request)
    {
        if (false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN')) {
            return $this->redirect($this->generateUrl($this->getParameter('employees.sitename') . '-nopermission'));
        }

        //exit("Under Construction: interface-manager");
        $em = $this->getDoctrine()->getManager();
        $transfers = $em->getRepository(InterfaceTransferList::class)->findAll();

        $transfer = NULL;
        if( count($transfers) > 0 ) {
            $transfer = $transfers[0];
        }

        $interfaceTransferUtil = $this->container->get('interface_transfer_utility');
        $interfaceTransferUtil->transferFile($transfer);

        exit();
    }

}
