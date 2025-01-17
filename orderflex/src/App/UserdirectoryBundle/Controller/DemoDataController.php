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

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;


class DemoDataController extends OrderAbstractController
{

    //[Route(path: '/reset-demo-data/', name: 'employees_reset_demo_data', methods: ['GET'])]
    #[Route(path: '/reset-demo-data-ajax/', name: 'employees_reset_demo_data_ajax', methods: ['POST'])]
    public function resetDemoDataAction(Request $request)
    {

        if (!$this->isGranted('ROLE_PLATFORM_ADMIN')) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        //Flash
        $this->addFlash(
            'notice',
            "Demo Data"
        );


        //networkDrivePath
        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        //echo "networkDrivePath=".$networkDrivePath."<br>";
        if( !$networkDrivePath ) {
            //exit("No networkDrivePath is defined");
            $this->addFlash(
                'pnotify-error',
                //'notice',
                "Cannot continue with Backup: No Network Drive Path is defined in the Site Settings"
            );
            return $this->redirect($this->generateUrl('employees_manual_backup_restore'));
        }

        if( $networkDrivePath ) {

            //create backup
            //$res = $this->creatingBackupSQLFull($networkDrivePath); //Use php based pg_dump
            // $res = $this->dbManagePython($networkDrivePath,'backup'); //Use python script pg_dump
            $userServiceUtil = $this->container->get('user_service_utility');
            $res = $userServiceUtil->dbManagePython($networkDrivePath,'backup'); //Working: Use python script pg_dump
            //exit($res);

            $resStatus = $res['status'];
            $resStr = $res['message'];

            if( $resStatus == 'OK' ) {
                $resStr = "Backup successfully created in folder $networkDrivePath";
                $this->addFlash(
                    'notice',
                    $resStr
                );

                //Event Log
                $user = $this->getUser();
                $sitename = $this->getParameter('employees.sitename');
                $userSecUtil->createUserEditEvent($sitename,$resStr,$user,null,$request,'Create Backup Database');

                ///// Run demo db generation /////



                ///// EOF Run demo db generation /////

//                $env = 'demo';
//                $backupFileName = '';
//                $output = $this->restoreDBWrapper($backupFileName,$env);
//                if( $output['status'] == 'OK' ) {
//                    $this->addFlash(
//                        'notice',
//                        "Demo DB restored: ".$output['message']
//                    );
//                } else {
//                    $this->addFlash(
//                        'notice',
//                        "Error Demo DB: ".$output['message']
//                    );
//                }

            } else {
                $this->addFlash(
                    'pnotify-error',
                    $resStr
                );
            }

        } else {
            $this->addFlash(
                'pnotify-error',
                "Error backup"
            );
        }

        return $this->redirect($this->generateUrl('employees_manual_backup_restore'));


        //return $this->redirectToRoute('employees_home');
    }

    #[Route(path: '/demo-data-test/', name: 'employees_demo_data_test', methods: ['GET'])]
    public function testAction( Request $request ) {
        // makes a real request to an external site
        $browser = new HttpBrowser(HttpClient::create());
        //$crawler = $browser->request('GET', '/directory/user/new');
        $crawler = $browser->request('GET', 'https://view.online/c/demo-institution/demo-department/directory/login');

        //$content = $this->client->getResponse()->getContent();
        //dump($crawler);
        //exit("content");

        // select the form and fill in some values
        $form = $crawler->selectButton('Log In')->form();
        $form['_usernametype'] = 'local-user';
        $form['_display-username'] = 'administrator';
        $form['_password'] = 'demo';

        // submits the given form
        $crawler = $browser->submit($form);

        dump($crawler);
        exit('111');
    }


}
