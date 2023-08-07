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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 6/29/2017
 * Time: 11:23 AM
 */

namespace App\UserdirectoryBundle\Controller;


use App\UserdirectoryBundle\Form\BackupManagementType;
use App\UserdirectoryBundle\Entity\SiteParameters;
use Doctrine\DBAL\Configuration;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CronManagementController extends OrderAbstractController
{
    #[Route(path: '/general-cron-jobs', name: 'employees_general_cron_jobs', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/CronJobs/general_cron_jobs.html.twig')]
    public function generalCronJobsAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //exit("Not implemented");

        $userServiceUtil = $this->container->get('user_service_utility');

        $title = "Cron Jobs Management";
        //$note = "Unique 'idname' must be included somwhere in the command";
        $note = "";

        $entity = $userServiceUtil->getSingleSiteSettingParameter();

        //$form = $this->createEditForm($entity, $cycle="show");

        return array(
            'entity' => $entity,
            //'form' => $form->createView(),
            'title' => $title,
            'note' => $note,
            'sitename' => "employees"
            //'cycle' => $cycle
        );
    }

    #[Route(path: '/health-monitor', name: 'employees_health_monitor', methods: ['GET'])]
    #[Template('AppUserdirectoryBundle/CronJobs/health_monitor.html.twig')]
    public function healthMonitorCronJobsAction(Request $request)
    {
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //exit("Not implemented");

        $userServiceUtil = $this->container->get('user_service_utility');

        $title = "Cron jobs management for health monitor";
        $note = "Unique 'idname' must be included somwhere in the command";

        $entity = $userServiceUtil->getSingleSiteSettingParameter();

        //$form = $this->createEditForm($entity, $cycle="show");

        return array(
            'entity' => $entity,
            //'form' => $form->createView(),
            'title' => $title,
            'note' => $note,
            //'cycle' => $cycle,
            'sitename' => "employees"
        );
    }

    private function createEditForm( SiteParameters $entity, $cycle ) {
        $params = array(
            'cycle' => $cycle
        );

        $disabled = false;
        if( $cycle == "show" ) {
            $disabled = true;
        }

        $form = $this->createForm(BackupManagementType::class, $entity, array(
            'form_custom_value' => $params,
            'disabled' => $disabled
        ));

        return $form;
    }






}