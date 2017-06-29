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

namespace Oleg\UserdirectoryBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DataBackupManagement extends Controller
{

    /**
     * Resources:
     * https://blogs.msdn.microsoft.com/brian_swan/2010/07/01/restoring-a-sql-server-database-from-php/
     * https://channaly.wordpress.com/2012/01/31/backup-and-restoring-mssql-database-with-php/
     * https://blogs.msdn.microsoft.com/brian_swan/2010/04/06/backup-and-restore-a-database-with-the-sql-server-driver-for-php/
     * Bundle (no MSSQL): https://github.com/dizda/CloudBackupBundle
     *
     * @Route("/data-backup-management/", name="employees_data_backup_management")
     * @Template("OlegUserdirectoryBundle:DataBackup:data_backup_management.html.twig")
     * @Method("GET")
     */
    public function dataBackupManagementAction(Request $request) {

        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }

        $sitename = "employees";
        $form = null;

        return array(
            'sitename' => $sitename,
            'title' => "Data Backup Management",
            'cycle' => 'new',
            'networkDrivePath' => $networkDrivePath
        );
    }


    /**
     * @Route("/create-backup/", name="employees_create_backup")
     * @Template("OlegUserdirectoryBundle:DataBackup:create_backup.html.twig")
     * @Method("GET")
     */
    public function createBackupAction(Request $request) {

        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }

        $sitename = "employees";
        $form = null;

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            //create backup

            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'sitename' => $sitename,
            'title' => "Create Backup",
            'cycle' => 'new'
        );
    }


    /**
     * @Route("/restore-backup/", name="employees_restore_backup")
     * @Template("OlegUserdirectoryBundle:DataBackup:restore_backup.html.twig")
     * @Method("GET")
     */
    public function restoreBackupAction(Request $request) {

        if( false === $this->get('security.context')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->get('session')->getFlashBag()->add(
                'pnotify-error',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }

        $sitename = "employees";
        $form = null;

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ) {

            //create backup

            return $this->redirect($this->generateUrl('employees_data_backup_management'));
        }

        return array(
            //'entity' => $entity,
            'form' => $form->createView(),
            'sitename' => $sitename
        );
    }

}