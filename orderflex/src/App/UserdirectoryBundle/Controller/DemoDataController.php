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
use Symfony\Component\Panther\Client;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class DemoDataController extends OrderAbstractController
{

    //private $baseUrl = 'https://view.online/c/demo-institution/demo-department';
    private $baseUrl = 'http://localhost';

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
    public function testAction( Request $request, TokenStorageInterface $tokenStorage ) {

//        //authenticate systemuser
//        $logger = $this->container->get('logger');
//        $userSecUtil = $this->container->get('user_security_utility');
//        $firewall = 'ldap_fellapp_firewall';
//        $systemUser = $userSecUtil->findSystemUser();
//        if( $systemUser ) {
//            //$token = new UsernamePasswordToken($systemUser, null, $firewall, $systemUser->getRoles());
//            $token = new UsernamePasswordToken($systemUser, $firewall, $systemUser->getRoles());
//            //$this->container->get('security.token_storage')->setToken($token);
//            $tokenStorage->setToken($token);
//        }
//        $logger->notice("testAction: Logged in as systemUser=".$systemUser);
//        if( $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) { //ROLE_USER
//            $logger->notice("testAction: systemUser is ROLE_PLATFORM_DEPUTY_ADMIN");
//        }

        // makes a real request to an external site
        $browser = new HttpBrowser(HttpClient::create());
        //$crawler = $browser->request('GET', '/directory/user/new');
        $crawler = $browser->request('GET', $this->baseUrl.'/directory/login');

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

    #[Route(path: '/demo-data-panther/', name: 'employees_demo_data_panther', methods: ['GET'])]
    public function testPantherAction( Request $request, TokenStorageInterface $tokenStorage ) {
        //$client = Client::createChromeClient();
        // alternatively, create a Firefox client
        //$client = Client::createFirefoxClient();

        if(0) {
            $client = Client::createChromeClient(
                $this->container->get('kernel')->getProjectDir() . '/drivers/chromedriver', [
                    '--remote-debugging-port=9222',
                    '--no-sandbox',
                    '--disable-dev-shm-usage',
                    '--headless'
                ]
            );
        }

        if(0) {
            $url = 'https://api-platform.com';
            $client->request('GET', $url);
            $client->clickLink('Getting started');
            // wait for an element to be present in the DOM, even if hidden
            //$crawler = $client->waitFor('#bootstrapping-the-core-library');
            // you can also wait for an element to be visible
            $crawler = $client->waitForVisibility('#bootstrapping-the-core-library');

            // get the text of an element thanks to the query selector syntax
            echo $crawler->filter('div:has(> #bootstrapping-the-core-library)')->text();
            // take a screenshot of the current page
            $client->takeScreenshot('screen.png');
        }


        if(0) {
            $url = $this->baseUrl.'/directory/login';
            $crawler = $client->request('GET', $url);
            //$crawler = $client->waitForVisibility('#display-username');
            //echo $crawler->filter('div:has(> #s2id_usernametypeid_show)')->text();

            //$client->waitForEnabled('[type="submit"]');
            //$crawler = $client->waitForVisibility('#login-form');
            $send_button = $crawler->selectButton('submit');

//        $client->clickLink('submit');
//        $form = $send_button->form(array(
//            'PrCompany[email]' => 'test@example.ua',
//            'PrCompany[first_name]' => 'Anton',
//            'PrCompany[last_name]' => 'Tverdiuh',
//            'PrCompany[timezone]' => 'Europe/Amsterdam'
//        ));
            //$crawler = $client->submit($form);

            $client->waitForVisibility('#s2id_usernametypeid_show', 30, 10000);
            $client->waitFor('#usernametypeid_show', 30, 10000);
            //$crawler = $client->waitForVisibility('_usernametype');
            //echo $crawler->filter('#pnotify-notice')->text();

            $form = $crawler->selectButton('Log In')->form();

            //$form['#usernametypeid_show'] = 'local-user'; //4; //'Local User'; 'local-user'
            //$form['#s2id_usernametypeid_show'] = 'local-user'; //4; //'Local User'; 'local-user'
            //https://stackoverflow.com/questions/64695968/symfony-crawler-select-option-in-select-list-without-form
            //$myInput = $crawler->filterXPath(".//select[@id='usernametypeid_show']//option[@value='local-user']");
            $myInput = $crawler->filter('#s2id_usernametypeid_show');
            //$myInput = $crawler->filterXPath(".//select[@id='s2id_usernametypeid_show']//option[@value='local-user']");
            //$form['#usernametypeid_show'] = 'local-user';
            //$client->waitForVisibility('#select2-chosen-1');
            //$myInput->click();

            $form['_display-username'] = 'administrator';
            $form['_password'] = 'demo';

            $client->submit($form);
        }

        $client = $this->loginAction();
        $client->takeScreenshot('test_login.png');

        //$client = $this->createUsers($client);
        //$client->takeScreenshot('test_createuser.png');

        //$client = $this->newTrpProjects($client);

        exit('eof panther');
    }

    public function getClient() {
        $client = Client::createChromeClient(
            $this->container->get('kernel')->getProjectDir().'/drivers/chromedriver',[
                '--remote-debugging-port=9222',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--headless',
                '--port=8080'
            ]
        );
        //$client = self::createPantherClient();
        return $client;
    }

    public function loginAction() {
        $client = $this->getClient();

        $url = $this->baseUrl.'/directory/login';
        $url = 'http://127.0.0.1:8000/directory/directory/login';

        $crawler = $client->refreshCrawler();
        $crawler = $client->request('GET', $url);

        $form = $crawler->selectButton('Log In')->form();

        //Select an option in select2 combobox:
        //Element is not currently visible and may not be manipulated
        //Webscrapper: how select2
        //https://symfony.com/doc/current/components/dom_crawler.html
        $crawler = $client->waitForVisibility('#s2id_usernametypeid_show');
        //$client->waitFor('_usernametype');

        //$myInput = $crawler->filterXPath(".//select[@id='usernametypeid_show']//option[@value='local-user']");
        //$myInput = $crawler->filter('#s2id_usernametypeid_show');
        //$myInput = $crawler->filterXPath(".//select[@id='s2id_usernametypeid_show']//option[@value='local-user']");
        //$myInput = $crawler->filterXPath(".//select[@id='usernametypeid_show']//option[@value='local-user']");
        //$form['_usernametype']->setValues(array('local-user'));
        //$form['registration[birthday][year]']->select(1984);
//        'select2-result-label-17'
        //$client->waitFor('_usernametype');

        //$myInput = $crawler->filterXPath(".//select[@id='s2id_usernametypeid_show']//option[@value='local-user']");
        //$myInput = $crawler->filterXPath(".//div[@id='s2id_usernametypeid_show']//option[@value='local-user']");
        //$myInput->click();
        //$myInput = $crawler->filterXPath(".//div[@id='s2id_usernametypeid_show']");
        //$myInput = $crawler->filterXPath(".//select[@id='s2id_usernametypeid_show']//option[@value='local-user']");

        //Working: executed JS script to click on a select2 and choose select2 element
        $client->executeScript("$('#s2id_usernametypeid_show').select2('val','local-user')");

        //wait for new element on new page to appear
        //$client->waitForVisibility('select2-dropdown-open');
        //$client->waitForVisibility('Local User');


        //$form['#usernametypeid_show'] = 'local-user';
        //$client->waitForVisibility('#select2-chosen-1');
        //$myInput->click(); //error: Element is not currently visible and may not be manipulated

        $form['_display-username'] = 'administrator';
        $form['_password'] = 'demo';

        $client->submit($form);

        return $client;
    }

    public function createUsers($client) {

        //array('ROLE_USERDIRECTORY_OBSERVER','ROLE_FELLAPP_OBSERVER')
        $client = $this->createUser($client,'johndoe','John','Doe','John Doe','pass','cinava@yahoo.com',array('ROLE_USERDIRECTORY_OBSERVER'));
        $client = $this->createUser($client,'aeinstein','Albert','Einstein','Albert Einstein','pass','cinava@yahoo.com',array('ROLE_USERDIRECTORY_OBSERVER'));
        $client = $this->createUser($client,'rrutherford','Ernest','Rutherford','Ernest Rutherford','pass','cinava@yahoo.com',array('ROLE_USERDIRECTORY_OBSERVER'));

        return $client;
    }

    public function createUser($client,$userid,$firstName,$lastName,$displayName,$pass,$email,$roles) {

        $url = $this->baseUrl.'/directory/user/new';
        $crawler = $client->request('GET', $url);
        $form = $crawler->selectButton('Add Employee')->form();

        $client->executeScript("$('#s2id_oleg_userdirectorybundle_user_keytype').select2('val','4')");

        $form['oleg_userdirectorybundle_user[primaryPublicUserId]'] = $userid;
        //$form['oleg_userdirectorybundle_user[keytype]'] = 'Local User';
        //$form['oleg_userdirectorybundle_user[password][first]'] = $pass;
        //$form['oleg_userdirectorybundle_user[password][second]'] = $pass;
        $form['oleg_userdirectorybundle_user[infos][0][displayName]'] = $displayName;
        $form['oleg_userdirectorybundle_user[infos][0][firstName]'] = $firstName;
        $form['oleg_userdirectorybundle_user[infos][0][lastName]'] = $lastName;
        $form['oleg_userdirectorybundle_user[infos][0][email]'] = $email;

        //Add roles 's2id_oleg_userdirectorybundle_user_roles'
        //$('body').scrollTo('#target');
        //$("selector").get(0).scrollIntoView();
        $client->executeScript("document.getElementById('s2id_oleg_userdirectorybundle_user_roles').scrollIntoView();");
        $roleStr = '';
        foreach($roles as $role) {
            //$myInput = $crawler->filter('#s2id_oleg_userdirectorybundle_user_roles');
            //$myInput = $crawler->filterXPath(".//select[@id='s2id_oleg_userdirectorybundle_user_rolesobs']//option[@value='".$role."']");
            //$client->executeScript("$('#oleg_userdirectorybundle_user_roles').select2('val','".$role."')");
            //$myInput->click();
            $roleStr = $roleStr . ", '" . $role . "'";
        }
        //$('#my_select2').select2('val', ["value1", "value2", "value3"]);
        $client->executeScript("$('#oleg_userdirectorybundle_user_roles').select2('val',[".$roleStr."])");

//        $form = $crawler->selectButton('Confirmar Exclusão')->form();
//        $form[($formName . '[ciente]')]->tick();

        $client->submit($form);

        $client->takeScreenshot('test_createuser-'.$userid.'.png');

        return $client;
    }

    public function newTrpProjects( $client ) {
        $this->newTrpProject($client,'johndoe',$this->baseUrl.'/translational-research/project/select-new-project-type');
    }

    public function newTrpProject( $client, $userid, $newProjectUrl ) {
        //$newProjectUrl = 'https://view.online/c/demo-institution/demo-department/translational-research/project/select-new-project-type';
        $crawler = $client->request('GET', $newProjectUrl);

        $link = $crawler->selectLink('AP/CP Project Request')->link();
        $client->click($link);

        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','.".$userid."')");

        $crawler = $client->refreshCrawler();
        $form = $crawler->filter('#oleg_translationalresearchbundle_project_submitIrbReview')->form();

        //find user str by $userid
        $subjectUser = null;
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findBy(array('primaryPublicUserId'=>$userid));
        if( count($users) > 1 ) {
            throw $this->createNotFoundException('Unable to find a Single User. Found users ' . count($users) );
        }
        if( count($users) == 1 ) {
            $subjectUser = $users[0];
        }
        //exit('user='.$subjectUser);
        echo "subjectUser=$subjectUser, ID=".$subjectUser->getId()." <br>";

        $crawler->filter('#s2id_oleg_translationalresearchbundle_project_principalInvestigators')->click();

//        $options = $client->executeScript("
//        $('#individualsfront').on('open',function(){
//            $.each(results, function(key,value){
//            console.log('text:'+value.text);
//        });
//        })
//        ");
//        dump($options);

        //$crawler->filter('#s2id_oleg_translationalresearchbundle_project_principalInvestigators')->sendKeys('John Doe - johndoe2 (Local User)');

        //$form['#oleg_translationalresearchbundle_project[principalInvestigators][]'] = '15';

        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','".$subjectUser->getId()."')");
        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','12')");

        //$crawler = $client->refreshCrawler();
        //$form = $crawler->filter('#oleg_translationalresearchbundle_project_submitIrbReview')->form();
        //$client->waitForVisibility('#s2id_oleg_translationalresearchbundle_project_principalInvestigators');
        //$form['#s2id_oleg_translationalresearchbundle_project_principalInvestigators'] = $userid;
        //$crawler->filter('#s2id_oleg_translationalresearchbundle_project_principalInvestigators')->text($userid);
        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('".$userid."')");

        $client->takeScreenshot('test_newTrpProject-'.'1'.'.png');

        return $client;
    }

}
