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

namespace App\DemoDbBundle\Util;



use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Panther\Client;


/**
 * @author oli2002
 */
class DemoDbUtil {

    protected $em;
    protected $container;
    private $baseUrl = 'https://view.online/c/demo-institution/demo-department';
    //private $baseUrl = 'http://127.0.0.1';

    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container
    )
    {
        $this->em = $em;
        $this->container = $container;
    }

    //RuntimeException: The port 9515 is already in use
    //https://jelledev.com/how-to-run-multiple-symfony-panther-clients-in-parallel/

    public function getClient() {

        //$availablePort = $this->getAvailablePort();
        //$availablePort = null;
        //echo "availablePort = $availablePort <br>";

        $client = Client::createChromeClient(
            $this->container->get('kernel')->getProjectDir().'/drivers/chromedriver',
            [
                '--remote-debugging-port=9222',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--headless'
            ]
//            [
//                'port' => $availablePort
//            ]
        );

        //$client = self::createPantherClient();
        return $client;
    }

    public function getAvailablePort(): int
    {
        $port = '8080';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //$localSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            //socket_bind($localSocket, "0.0.0.0", $port);
            //socket_listen($localSocket);
        } else {
            // When providing '0' as port, the OS picks a random available port
            $socket = socket_create_listen(0);
            socket_getsockname($socket, $address, $port);
            socket_close($socket);
        }

        return $port;
    }


    public function loginAction() {
        $client = $this->getClient();

        $client->close();
        $client->quit();

        $client = $this->getClient();

        $url = $this->baseUrl.'/directory/login';
        //$url = 'https://view.online/c/demo-institution/demo-department/directory/login';
        //$url = 'http://127.0.0.1/directory/directory/login';
        //$url = '/directory/login';

        //$crawler = $client->refreshCrawler();
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

    public function getUsers() {
        $users = array();
        $users[] = array(
            'userid' => 'johndoe',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'displayName' => 'John Doe',
            'email' => 'cinava@yahoo.com',
            'password' => 'pass',
            'roles' => array('ROLE_USERDIRECTORY_OBSERVER')
        );
        $users[] = array(
            'userid' => 'aeinstein',
            'firstName' => 'Albert',
            'lastName' => 'Einstein',
            'displayName' => 'Albert Einstein',
            'email' => 'cinava@yahoo.com',
            'password' => 'pass',
            'roles' => array('ROLE_USERDIRECTORY_OBSERVER')
        );
        $users[] = array(
            'userid' => 'rrutherford',
            'firstName' => 'Ernest',
            'lastName' => 'Rutherford',
            'displayName' => 'Ernest Rutherford',
            'email' => 'cinava@yahoo.com',
            'password' => 'pass',
            'roles' => array('ROLE_USERDIRECTORY_OBSERVER')
        );

        return $users;
    }

    public function createUsers($client) {

        //array('ROLE_USERDIRECTORY_OBSERVER','ROLE_FELLAPP_OBSERVER')
        //$client = $this->createUser($client,'johndoe','John','Doe','John Doe','pass','cinava@yahoo.com',array('ROLE_USERDIRECTORY_OBSERVER'));
        //$client = $this->createUser($client,'aeinstein','Albert','Einstein','Albert Einstein','pass','cinava@yahoo.com',array('ROLE_USERDIRECTORY_OBSERVER'));
        //$client = $this->createUser($client,'rrutherford','Ernest','Rutherford','Ernest Rutherford','pass','cinava@yahoo.com',array('ROLE_USERDIRECTORY_OBSERVER'));

        foreach( $this->getUsers() as $userArr ) {
            $client = $this->createUser(
                $client,
                $userArr['userid'],
                $userArr['firstName'],
                $userArr['lastName'],
                $userArr['displayName'],
                $userArr['password'],
                $userArr['email'],
                $userArr['roles']
            );
        }


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
        $users = $this->getUsers();
        $this->newTrpProject($client,$users,$this->baseUrl.'/translational-research/project/select-new-project-type');
    }
    public function newTrpProject( $client, $users, $newProjectUrl ) {
        //$newProjectUrl = 'https://view.online/c/demo-institution/demo-department/translational-research/project/select-new-project-type';
        $crawler = $client->request('GET', $newProjectUrl);

        $link = $crawler->selectLink('AP/CP Project Request')->link();
        $client->click($link);

        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','.".$userid."')");

        $crawler = $client->refreshCrawler();
        $form = $crawler->filter('#oleg_translationalresearchbundle_project_submitIrbReview')->form();

        //get userStr for select2 field: 'Ernest Rutherford - rrutherford (Local User)'
        $piArr = $users[0];
        $userStr = $piArr['displayName'] . ' - ' . $piArr['userid'] . ' (Local User)';
        echo "userStr=$userStr <br>";

//        //find user str by $userid
//        $subjectUser = null;
//        $users = $this->em->getRepository(User::class)->findBy(array('primaryPublicUserId'=>$userid));
//        if( count($users) > 1 ) {
//            throw $this->createNotFoundException('Unable to find a Single User. Found users ' . count($users) );
//        }
//        if( count($users) == 1 ) {
//            $subjectUser = $users[0];
//        }
//        //exit('user='.$subjectUser);
//        echo "subjectUser=$subjectUser, ID=".$subjectUser->getId()." <br>";

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

        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','".$userStr."')");
        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','Ernest Rutherford - rrutherford (Local User)')");
        $client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','John Doe - johndoe1 (Local User)')");

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


?>
