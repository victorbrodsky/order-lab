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
use Symfony\Bundle\SecurityBundle\Security;


//Called from controller DemoDataController (/demo-data-panther/) and DemoDbCommand

/**
 * @author oli2002
 */
class DemoDbUtil {

    protected $em;
    protected $container;
    protected $security;
    private $baseUrl = 'https://view.online/c/demo-institution/demo-department';
    //private $baseUrl = 'http://127.0.0.1';

    public function __construct(
        EntityManagerInterface $em,
        ContainerInterface $container,
        Security $security=null
    )
    {
        $this->em = $em;
        $this->container = $container;
    }


    public function processDemoDb( $backupPath=NULL )
    {

//        if( false === $this->security->isGranted('ROLE_PLATFORM_ADMIN') ) {
//            return $this->redirect( $this->generateUrl('employees-nopermission') );
//        }

        //php bin/console doctrine:database:create
        //php bin/console doctrine:schema:update --complete --force
        //php bin/console doctrine:migration:status
        //php bin/console doctrine:migration:migrate
        //php bin/console doctrine:migration:sync-metadata-storage
        //php bin/console doctrine:migration:version --add --all

        //Set new DB (use restoreDBWrapper)
        //environment
        //connectionChannel (set http for HaProxy)
        //urlConnectionChannel (set https for HaProxy if using ssl certificate)
        //networkDrivePath
        //mailerDeliveryAddresses
        //instanceId

        //check only if DB exists

        $logger = $this->container->get('logger');
        $userServiceUtil = $this->container->get('user_service_utility');
        $userSecUtil = $this->container->get('user_security_utility');

//        $environment = $userSecUtil->getSiteSettingParameter('environment');
//        if( $environment == 'live' ) {
//            exit("Demo DB cannot be run in live environment");
//        }

        $resetDb = false;

        echo "processDemoDb: start with resetDb=$resetDb <br>";
        $logger->notice("processDemoDb: start with resetDb=$resetDb");

        /////////////// Drop and create new Database ////////////////
        if ($resetDb) {
            $phpPath = $userServiceUtil->getPhpPath();
            $projectRoot = $this->container->get('kernel')->getProjectDir();

            try {
                echo "processDemoDb try: getSiteSettingParameter" . "<br>";
                $environment = $userSecUtil->getSiteSettingParameter('environment');
                if ($environment == 'live') {
                    exit("processDemoDb: Demo DB cannot be run in live environment");
                }
            } catch (\Exception $e) {
                // Handle the exception
                //echo "Error: " . $e->getMessage();
                //exit;
                $create = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:database:create';
                $logger->notice("create command=[" . $create . "]");
                $resCreate = $userServiceUtil->runProcess($create);
                echo "processDemoDb: create resCreate=" . $resCreate . "<br>";
            }

            echo "\n" . "processDemoDb: start." . "\n<br>";
            $logger->notice("processDemoDb: start.");
            $res = '';

            if (!$backupPath) {
                // /usr/local/bin/order-lab-thistenant/orderflex/var/backups/
                $backupPath = $projectRoot . "/var/backups/";
            }
            echo "processDemoDb: backupPath=$backupPath \n<br>";

            //check if $backupPath exists if not create
            if (!file_exists($backupPath)) {
                // Attempt to create the folder with appropriate permissions
                if (!mkdir($backupPath, 0755, true)) {
                    die("processDemoDb: Failed to create directory: $backupPath");
                }
            }

            //exit('111 <br>');

            //1) backup DB (might not be need it)
            echo "processDemoDb: dbManagePython \n<br>";
            $resBackupArr = $userServiceUtil->dbManagePython($backupPath, 'backup');
            $res = $res . implode(',', $resBackupArr);

            //2) reset DB
            //php bin/console doctrine:database:drop --force
            $resPhp = $userServiceUtil->runProcess('php -v');
            echo "php=[" . $resPhp . "]";
            $res = $res . "; " . $resPhp;

            $drop = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:schema:drop --full-database --force --verbose';
            echo "drop command=[" . $drop . "]" . "<br>";
            $logger->notice("drop command=[" . $drop . "]");
            $resDrop = $userServiceUtil->runProcess($drop);
            echo "drop resDrop=" . $resDrop . "<br>";
            $res = $res . "; " . $resDrop;

            //TODO: delete all Uploaded files

    //        //3) create DB: php bin/console doctrine:database:create
    //        $create = $phpPath . ' ' . $projectRoot.'/bin/console doctrine:database:create';
    //        $logger->notice("create command=[".$create."]");
    //        $resCreate = $userServiceUtil->runProcess($create);
    //        echo "create resCreate=".$resCreate."<br>";
    //        $res = $res . "; " . $resCreate;

            //4) update DB: php bin/console doctrine:schema:update --complete --force
            $update = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:schema:update --complete --force';
            $logger->notice("update command=[" . $update . "]");
            $resUpdate = $userServiceUtil->runProcess($update);
            echo "resUpdate=" . $resUpdate . "<br>";
            $res = $res . "; " . $resUpdate;

            //5 php bin/console doctrine:migration:sync-metadata-storage
            $syncStorageCommand = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:migration:sync-metadata-storage';
            $logger->notice("syncStorageCommand=[" . $syncStorageCommand . "]");
            $resSyncStorageCommand = $userServiceUtil->runProcess($syncStorageCommand);
            echo "resSyncStorageCommand=" . $resSyncStorageCommand . "<br>";
            $res = $res . "; " . $resSyncStorageCommand;

            //5 php bin/console doctrine:migrations:version --add --all
            $addAllCommand = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:migrations:version --add --all';
            $logger->notice("addAllCommand=[" . $addAllCommand . "]");
            $resAddAllCommand = $userServiceUtil->runProcess($addAllCommand);
            echo "resAddAllCommand=" . $resAddAllCommand . "<br>";
            $res = $res . "; " . $resAddAllCommand;

            //6 php bin/console doctrine:migration:migrate
            $migrateCommand = $phpPath . ' ' . $projectRoot . '/bin/console doctrine:migration:migrate';
            $logger->notice("migrateCommand=[" . $migrateCommand . "]");
            $resMigrateCommand = $userServiceUtil->runProcess($syncStorageCommand);
            echo "resMigrateCommand=" . $resMigrateCommand . "<br>";
            $res = $res . "; " . $resMigrateCommand;

            //Create python environment if utils/scraper/.venv does not exists
            $folderPath = "/srv/order-lab-tenantappdemo/utils/scraper/venv/bin";
            //$bashPath = $projectRoot.'/../packer/additional.sh '. $projectRoot . '/..';
            $bashPath = $projectRoot . '/..';
            //$bashPath = "srv/order-lab-tenantappdemo";
            echo "\nbashPath=" . $bashPath . "\n<br>";
            $bashPath = realpath($bashPath);
            echo "\nbashPath=" . $bashPath . "\n<br>"; //bashPath=/srv/order-lab-tenantappdemo

            $envFolderPath = $bashPath . "/utils/scraper/venv";
            if (is_dir($envFolderPath)) {
                echo "Environment folder exists!";
            } else {
                //exit('\nSTOP\n');
                //$pythonEnvCommand = 'bash ' . $projectRoot.'/../packer/additional.sh '. $projectRoot . '/../';
                $pythonEnvCommand = 'bash ' . $bashPath . '/packer/additional.sh ' . $bashPath;
                echo "pythonEnvCommand=" . $pythonEnvCommand . "\n<br>";
                $logger->notice("pythonEnvCommand=[" . $pythonEnvCommand . "]");
                $resPythonEnvCommand = $userServiceUtil->runProcess($pythonEnvCommand);
                echo "resPythonEnvCommand=" . $resPythonEnvCommand . "\n<br>";
                $res = $res . "; " . $resPythonEnvCommand;
            }
        }
        /////////////// EOF Drop and create new Database ////////////////

        ///////////// 7) initiate DB by running utils/scraper/create_demo_db.py ////////////////
//        $initializePath = 'python' . ' ' . $projectRoot.'/bin/console cron:demo-db-reset';
//        $logger->notice("initializePath=[".$initializePath."]");
//        $resinitializeCommand = $userServiceUtil->runProcess($initializeCommand);
//        echo "resinitializeCommand=".$resinitializeCommand."<br>";
//        $res = $res . "; " . $resinitializeCommand;

        $projectRoot = $this->container->get('kernel')->getProjectDir();
        //echo "projectRoot=".$projectRoot."<br>";
        //For multitenancy is not 'order-lab' anymore, but 'order-lab-tenantapp1'
        $parentRoot = str_replace('orderflex', '', $projectRoot);
        $parentRoot = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, '', $parentRoot);
        $managePackagePath = $parentRoot .
            "utils" .
            DIRECTORY_SEPARATOR . "scraper";
        $pythonScriptPath = $managePackagePath . DIRECTORY_SEPARATOR . "create_demo_db.py";

        if( $userServiceUtil->isWindows() ){
            $pythonEnvPath = $managePackagePath .
                DIRECTORY_SEPARATOR . "venv" .
                DIRECTORY_SEPARATOR . "Scripts" . //Windows
                DIRECTORY_SEPARATOR . "python";
        } else {
            $pythonEnvPath = $managePackagePath .
                DIRECTORY_SEPARATOR . "venv" .
                DIRECTORY_SEPARATOR . "bin" . //Linux
                DIRECTORY_SEPARATOR . "python";
        }

        if( file_exists($pythonEnvPath) ) {
            //echo "The file $filename exists";
        } else {
            $msg = "Error in processDemoDb: The file $pythonEnvPath does not exist.".
                " Make sure pytnon's environment venv has been installed";
            return $res . "; ". $msg;
        }

        $pythonInitCommand = "$pythonEnvPath $pythonScriptPath"
            //.
            //" --user $dbUsername".
            //" --password $dbPassword"
        ;
        echo "processDemoDb: run process with python command=[".$pythonInitCommand."] <br>";
        $logger->notice("processDemoDb: run process with python command=[".$pythonInitCommand."]");
        $res = null;
        //$res = $userServiceUtil->runProcess($pythonInitCommand);
        //$res = $userServiceUtil->runSymfonyProcessRealTime([$pythonInitCommand]);

        //Error: selenium.common.exceptions.SessionNotCreatedException: Message: session not created: probably user data dir
        //ectory is already in use, please specify a unique value for --user-data-dir argument,
        // or don't use --user-data-dir
        //check chrome: ps aux | grep chrome
        //kill -p PID
        //Fix it by:
        //options.add_argument("--headless")  #working in command. Optional: Run in headless mode
        //options.add_argument("--no-sandbox") #working in command.
        //options.add_argument("--disable-dev-shm-usage") #working in command.
        ///////////// EOF 7) initiate DB by running utils/scraper/create_demo_db.py ////////////////

        return $res;
    }


    public function postRestoreDb() {
        //Set new DB (use restoreDBWrapper)
        //environment
        //connectionChannel (set http for HaProxy)
        //urlConnectionChannel (set https for HaProxy if using ssl certificate)
        //networkDrivePath
        //mailerDeliveryAddresses
        //instanceId

        $logger = $this->container->get('logger');
        $userServiceUtil = $this->container->get('user_service_utility');

        //1) Initialize DB with url
        //http://$domainname/order/directory/admin/first-time-login-generation-init/
        //https://view.online/c/demo-institution/demo-department/directory/admin/first-time-login-generation-init/

        $param = $userServiceUtil->getSingleSiteSettingParameter();
        $logger->notice("postRestoreDb: paramId=" . $param->getId());

        $conn = $this->getConnection();

        $siteEmail = 'oli2002@med.cornell.edu';
        $env = 'demo';
        $connectionChannel = 'http';
        $urlConnectionChannel = 'https';
        $networkDrivePathOrig = '';
        $mailerDeliveryAddresses = $siteEmail;
        $instanceId = 'VIEWDEMO';

        $setparams =
            "mailerdeliveryaddresses='$siteEmail'".
            ", environment='$env'".
            ", connectionChannel='$connectionChannel'" .
            ", urlConnectionChannel='$urlConnectionChannel'" .
            ", networkDrivePath='$networkDrivePathOrig'".
            ", mailerDeliveryAddresses='$mailerDeliveryAddresses'".
            ", instanceId='$instanceId'"
        ;
        $sql = "UPDATE user_siteparameters" .
            " SET " . $setparams .
            " WHERE id=" . $param->getId();

        $logger->notice("postRestoreDb: sql=" . $sql);

        $stmt = $conn->prepare($sql);
        $logger->notice("postRestoreDb: after prepare");

        $results = $stmt->executeQuery();
        $logger->notice("postRestoreDb: after executeQuery");

        return $results;
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


    public function loginAction( $password=null ) {
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

        if( !$password ) {
            //$password = 'demo';
            $password = '1234567890_demo';
        }

        $form['_display-username'] = 'administrator';
        $form['_password'] = $password;

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
            'roles' => array('ROLE_USERDIRECTORY_OBSERVER'),
            'userId' => 12
        );
        $users[] = array(
            'userid' => 'aeinstein',
            'firstName' => 'Albert',
            'lastName' => 'Einstein',
            'displayName' => 'Albert Einstein',
            'email' => 'cinava@yahoo.com',
            'password' => 'pass',
            'roles' => array('ROLE_USERDIRECTORY_OBSERVER'),
            'userId' => 15
        );
        $users[] = array(
            'userid' => 'rrutherford',
            'firstName' => 'Ernest',
            'lastName' => 'Rutherford',
            'displayName' => 'Ernest Rutherford',
            'email' => 'cinava@yahoo.com',
            'password' => 'pass',
            'roles' => array('ROLE_USERDIRECTORY_OBSERVER'),
            'userId' => 16
        );

        return $users;
    }

    public function createUsers($client) {

        //array('ROLE_USERDIRECTORY_OBSERVER','ROLE_FELLAPP_OBSERVER')
        //$client = $this->createUser($client,'johndoe','John','Doe','John Doe','pass','cinava@yahoo.com',array('ROLE_USERDIRECTORY_OBSERVER'));
        //$client = $this->createUser($client,'aeinstein','Albert','Einstein','Albert Einstein','pass','cinava@yahoo.com',array('ROLE_USERDIRECTORY_OBSERVER'));
        //$client = $this->createUser($client,'rrutherford','Ernest','Rutherford','Ernest Rutherford','pass','cinava@yahoo.com',array('ROLE_USERDIRECTORY_OBSERVER'));

        //$userIds = array();
        foreach( $this->getUsers() as $userArr ) {
            $userId = $this->createUser(
                $client,
                $userArr['userid'],
                $userArr['firstName'],
                $userArr['lastName'],
                $userArr['displayName'],
                $userArr['password'],
                $userArr['email'],
                $userArr['roles']
            );
            $userArr['userId'] = $userId;
            //$userIds[] = $userId;
        }


        return $userArr;
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

//        $uri = $client->getCurrentURL(); //$client->getResponse()->headers->get('location');
//        echo "uri=$uri <br>";
//        //get id from url 'https://view.online/c/demo-institution/demo-department/directory/user/14'
//        $uriArr = explode('/',$uri);
//        $userId = end($uriArr);
        $userId = $this->getCurrentUrlId($client);

        $client->takeScreenshot('demoDb/test_createuser-'.$userid.'.png');
        $client->takeScreenshot('demoDb/test_createuser-id-'.$userId.'.png');

        return $userId;
    }

    public function newTrpProjects( $client, $users ) {
        //$users = $this->getUsers();
        //$this->newTrpProject($client,$users,$this->baseUrl.'/translational-research/project/select-new-project-type');
        $projectIds = array();
        foreach( $this->getTrpProjects() as $trpProjectArr ) {
            $projectId = $this->newTrpProject($client,$trpProjectArr,$users,$this->baseUrl.'/translational-research/project/select-new-project-type');
            if( $projectId ) {
                $projectIds[] = $projectId;
            }
            //break; //testing
        }
        return $projectIds;
    }
    public function newTrpProject( $client, $trpProjectArr, $users, $newProjectUrl ) {
        //$newProjectUrl = 'https://view.online/c/demo-institution/demo-department/translational-research/project/select-new-project-type';
        $crawler = $client->request('GET', $newProjectUrl);

        $link = $crawler->selectLink('AP/CP Project Request')->link();
        $client->click($link);

        //$uri = $client->getCurrentURL(); //$client->getResponse()->headers->get('location');
        //echo "uri=$uri <br>";

        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','.".$userid."')");

        $crawler = $client->refreshCrawler();
        //$client->waitForVisibility('#oleg_translationalresearchbundle_project_submitIrbReview');
        $form = $crawler->filter('#oleg_translationalresearchbundle_project_submitIrbReview')->form();
        //$form = $crawler->filter('Submit for Review')->form();
        //$form = $crawler->selectButton('Submit for Review')->form();

        //get userStr for select2 field: 'Ernest Rutherford - rrutherford (Local User)'
        //Set PI
        $piArr = $users[0];
        //$userStr = $piArr['displayName'] . ' - ' . $piArr['userid'] . ' (Local User)';
        //echo "userStr=$userStr <br>";
        echo "PI userId=".$piArr['userId']."<br>";

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

        //$crawler->filter('#s2id_oleg_translationalresearchbundle_project_principalInvestigators')->click();

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

        $client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','".$piArr['userId']."')");
        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','Ernest Rutherford - rrutherford (Local User)')");
        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('val','John Doe - johndoe1 (Local User)')");

        $client->takeScreenshot('demoDb/test_newTrpProject-setPi'.'.png');

        //$crawler = $client->refreshCrawler();
        //$form = $crawler->filter('#oleg_translationalresearchbundle_project_submitIrbReview')->form();
        //$client->waitForVisibility('#s2id_oleg_translationalresearchbundle_project_principalInvestigators');
        //$form['#s2id_oleg_translationalresearchbundle_project_principalInvestigators'] = $userid;
        //$crawler->filter('#s2id_oleg_translationalresearchbundle_project_principalInvestigators')->text($userid);
        //$client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_principalInvestigators').select2('".$userid."')");

        //Set billingContact 's2id_oleg_translationalresearchbundle_project_billingContact'
        $billingContactArr = $users[1];
        echo "Billing Contact userId=".$billingContactArr['userId']."<br>";
        $client->executeScript("$('#s2id_oleg_translationalresearchbundle_project_billingContact').select2('val','".$billingContactArr['userId']."')");
        $client->takeScreenshot('demoDb/test_newTrpProject-setBilling'.'.png');

        //Set IRB to Exempt 'oleg_translationalresearchbundle_project_exemptIrbApproval'
        $client->executeScript("$('#oleg_translationalresearchbundle_project_exemptIrbApproval').select2('val','2')");

        //Set oleg_translationalresearchbundle_project_title
        $form['oleg_translationalresearchbundle_project[title]'] = $trpProjectArr['title'];
        $client->takeScreenshot('demoDb/test_newTrpProject-setTitle'.'.png');

        //Set description
        $form['oleg_translationalresearchbundle_project[description]'] = $trpProjectArr['description'];
        $client->takeScreenshot('demoDb/test_newTrpProject-setDescr'.'.png');

        //Set funded
        //$form['oleg_translationalresearchbundle_project[funded]'] = $trpProjectArr['funded'];
        if(0) {
            $client->waitForVisibility('oleg_translationalresearchbundle_project[funded]');
            if ($trpProjectArr['funded']) {
                //$form['#oleg_translationalresearchbundle_project_funded_0'] = 1;
                $form['oleg_translationalresearchbundle_project[funded]']->select('0');
            } else {
                //$form['#oleg_translationalresearchbundle_project_funded_1'] = 1;
                $form['oleg_translationalresearchbundle_project[funded]']->select('1');
            }
        }

        //Set budget
        $form['oleg_translationalresearchbundle_project[totalCost]'] = $trpProjectArr['budget'];
        $client->takeScreenshot('demoDb/test_newTrpProject-'.'totalCost'.'.png');

        //Set required radio boxes: 'oleg_translationalresearchbundle_project[involveHumanTissue]'
        $form['oleg_translationalresearchbundle_project[involveHumanTissue]']->select('No');
        $client->takeScreenshot('demoDb/test_newTrpProject-'.'involveHumanTissue'.'.png');
        $form['oleg_translationalresearchbundle_project[requireTissueProcessing]']->select('No');
        $client->takeScreenshot('demoDb/test_newTrpProject-'.'requireTissueProcessing'.'.png');
        $form['oleg_translationalresearchbundle_project[requireArchivalProcessing]']->select('No');
        $client->takeScreenshot('demoDb/test_newTrpProject-'.'requireArchivalProcessing'.'.png');

        $client->executeScript("document.getElementById('oleg_translationalresearchbundle_project_submitIrbReview').scrollIntoView();");

        $client->submit($form);
        $client->takeScreenshot('demoDb/test_newTrpProject-'.'submit'.'.png');

        $projectId = $this->getCurrentUrlId($client);
        echo "projectId=$projectId <br>";

        return $projectId;
    }

    public function newTrpWorkRequests( $client, $projectIds ) {
        $requestIds = array();
        foreach($projectIds as $projectId) {
            $productId = 0;
            foreach ($this->getTrpWorkRequests() as $trpRequestArr) {

                $url = $this->baseUrl.'/translational-research/project/'.$projectId.'/work-request/new/';
                $crawler = $client->request('GET', $url);

                $requestIds[] = $this->newTrpRequest($client, $crawler, $projectId, $trpRequestArr, $productId);
                //$productId++; 
                //add new product section by clicking 'Add Product or Service'
                //$link = $crawler->selectLink('Add Product or Service')->link();
                //$button = $crawler->selectButton('Add Product or Service')->link();
                //'transres-add-product-btn'
                //$client->executeScript("document.querySelector('#js-scroll-down').click()");
                //$client->executeScript("document.querySelector('.transres-add-product-btn').click()");
                //$client->executeScript("$('.transres-add-product-btn').click()");
                //$client->waitForVisibility('#oleg_translationalresearchbundle_request_products_'.$productId.'_requested');
                //$client->waitForVisibility('s2id_oleg_translationalresearchbundle_request_products_2_category');

                //$client->executeScript("document.getElementById('oleg_translationalresearchbundle_project_submitIrbReview').scrollIntoView();");
                //$client->executeScript('$(".transres-add-product-btn")[0].scrollIntoView(false);');
                //$client->takeScreenshot('demoDb/test_product-'.$productId.'.png');
                //$client->click($button);
            }
        }

        return $requestIds;
    }
    public function newTrpRequest( $client, $crawler, $projectId, $trpRequestArr, $productId ) {
        //https://view.online/c/demo-institution/demo-department/translational-research/project/1/work-request/new/
        //$url = $this->baseUrl.'/translational-research/project/'.$projectId.'/work-request/new/';
        //$crawler = $client->request('GET', $url);
        $client->takeScreenshot('demoDb/test_newRequest-'.$projectId.'.png');

        //$form = $crawler->filter('Complete Submission')->form();
        $client->waitForVisibility('#oleg_translationalresearchbundle_request_saveAsComplete');
        //$form = $crawler->filter('#oleg_translationalresearchbundle_request_saveAsComplete')->form();

        //oleg_translationalresearchbundle_request_products_0_category
        //$client->waitForVisibility('#s2id_oleg_translationalresearchbundle_request_products_'.$productId.'_category');
        //$client->waitForVisibility('oleg_translationalresearchbundle_request[products]['.$productId.'][category]');
        echo "category name = [oleg_translationalresearchbundle_request[products][".$productId."][category]] <br>";
        //exit('111');
        //$form['oleg_translationalresearchbundle_request[products]['.$productId.'][category]']->select($trpRequestArr['serviceId']);
        //$client->executeScript("$('#oleg_translationalresearchbundle_request[products]['".$productId."'][category]').select2('val','2'))");
        $client->executeScript("$('#oleg_translationalresearchbundle_request_products_".$productId."_category').select2('val','".$trpRequestArr['serviceId']."')");
        //'#oleg_translationalresearchbundle_request_products_0_category'
        //s2id_oleg_translationalresearchbundle_request_products_0_category
        echo "category name = [oleg_translationalresearchbundle_request[products][".$productId."][category]] <br>";

        //oleg_translationalresearchbundle_request[products][0][requested]
        //oleg_translationalresearchbundle_request_products_1_requested
        //$client->waitForVisibility('#oleg_translationalresearchbundle_request_products_'.$productId.'_requested');
        //$form['oleg_translationalresearchbundle_request[products]['.$productId.'][requested]']->select($trpRequestArr['quantity']);
        //$client->executeScript("$('oleg_translationalresearchbundle_request_products_".$productId."_requested').select2('val','1')");
        echo "requested = [".'oleg_translationalresearchbundle_request[products]['.$productId.'][requested]'."] <br>";
        //$form['oleg_translationalresearchbundle_request[products]['.$productId.'][requested]'] = $trpRequestArr['quantity'];
        $client->executeScript("$('#oleg_translationalresearchbundle_request_products_".$productId."_requested').val('".$trpRequestArr['quantity']."')");

        //oleg_translationalresearchbundle_request[products][0][comment]
        //$form['oleg_translationalresearchbundle_request[products]['.$productId.'][comment]']->select($trpRequestArr['comment']);
        //$client->executeScript("$('oleg_translationalresearchbundle_request[products]['.$productId.'][comment]').select2('val','1')");
        //$form['oleg_translationalresearchbundle_request[products]['.$productId.'][comment]'] = $trpRequestArr['comment'];
        //$client->executeScript("$('#oleg_translationalresearchbundle_request_products_".$productId."_comment').val('".$trpRequestArr['comment']."')");
        $client->executeScript("$('#oleg_translationalresearchbundle_request_products_".$productId."_comment').val('".$trpRequestArr['comment']."')");

        //$form['oleg_translationalresearchbundle_request_businessPurposes']->select(1);
        //$client->executeScript("$('oleg_translationalresearchbundle_request_businessPurposes').select2('val','1')");

        $client->takeScreenshot('demoDb/test_product-'.$productId.'.png');

        //businessPurposes
        //$client->executeScript("$('#oleg_translationalresearchbundle_request_businessPurposes').val('1')");
        //$client->executeScript("$('oleg_translationalresearchbundle_request[businessPurposes][]').val('1')");
        $client->executeScript("$('#s2id_oleg_translationalresearchbundle_request_businessPurposes').select2('val','1')");
        $client->executeScript('$("#s2id_oleg_translationalresearchbundle_request_businessPurposes")[0].scrollIntoView(false);');

        //Check #confirmationSubmit
        $client->executeScript("$('#confirmationSubmit').prop('checked', true)");
        $client->executeScript('$("#confirmationSubmit")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_product-confirmationSubmit-'.$productId.'.png');

        //Click 'Complete Submission'
        //$client->executeScript("$('#oleg_translationalresearchbundle_request_saveAsComplete').click()");
        //oleg_translationalresearchbundle_request_saveAsComplete
        $form = $crawler->filter('#oleg_translationalresearchbundle_request_saveAsComplete')->form();
        $client->submit($form);
        //$client->executeScript('$("#oleg_translationalresearchbundle_request_saveAsComplete")[0].scrollIntoView(false);');
        //$client->executeScript('$("#oleg_translationalresearchbundle_request_saveAsComplete").scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_request-saveAsComplete-'.$productId.'.png');
    }

    public function newTrpInvoices( $client, $requestIds ) {
        foreach($requestIds as $requestId) {
            // '/translational-research/invoice/new/5'
            $url = $this->baseUrl.'/translational-research/invoice/new/'.$requestId;
            $crawler = $client->request('GET', $url);

            $requestIds[] = $this->newInvoice($client, $crawler, $requestId);
        }

        return $requestIds;
    }
    public function newInvoice( $client, $crawler, $requestId ) {
        //https://view.online/c/demo-institution/demo-department/translational-research/project/1/work-request/new/
        //$url = $this->baseUrl.'/translational-research/project/'.$projectId.'/work-request/new/';
        //$crawler = $client->request('GET', $url);
        $client->takeScreenshot('demoDb/test_invoice-'.$requestId.'.png');

        //$form = $crawler->filter('Complete Submission')->form();
        //$client->waitForVisibility('#oleg_translationalresearchbundle_invoice_saveAndGeneratePdf');
        $form = $crawler->filter('#oleg_translationalresearchbundle_invoice_saveAndGeneratePdf')->form();
        //$form = $crawler->selectButton('Save and Generate PDF Invoice')->form();
        //$form = $crawler->selectButton('#oleg_translationalresearchbundle_invoice_saveAndGeneratePdf')->form();

        $client->submit($form);

        //$client->executeScript('$("#oleg_translationalresearchbundle_invoice_saveAndGeneratePdf").scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_invoice-save-'.$requestId.'.png');
    }

    public function getCurrentUrlId($client) {
        $uri = $client->getCurrentURL(); //$client->getResponse()->headers->get('location');
        echo "uri=$uri <br>";

        //get id from url 'https://view.online/c/demo-institution/demo-department/directory/user/14'
        $uriArr = explode('/',$uri);
        $id = end($uriArr);

//        if( !is_int($id) ) {
//            return NULL;
//        }

        return $id;
    }

    public function getTrpProjects() {
        $projects = array();
        $projects[] = array(
            'title' => 'Inflammatory infiltrates in Post-transplant lymphoproliferative disorders (PTLDs)',
            'description' => 'Post-transplant lymphoproliferative disorders (PTLDs) are Epstein Barr virus (EBV) 
                associated B cell lymphoid proliferations.  The patients who develop these lesions have 
                an unpredictable clinical course and outcome with some patients having lesions that regress 
                following a reduction in immunosuppression and others who despite aggressive theraputic 
                intervention have progressive disease leading to their demise.',
            'budget' => '5000',
            'funded' => 1
        );
        $projects[] = array(
            'title' => 'Characterization of circulating tumor cells in arterial vs. venous blood of patients with Non Small Cell Lung Cancer',
            'description' => 'This is a phase I study to determine whether the incidence and 
                quantity of circulating tumor cells is higher in peripheral arterial compared 
                to venous blood and of the primary tumor. A total of 50 evaluable subjects 
                will be enrolled from 4 cancer centers with early resectable NSCLC and subjects 
                with unresectable or metastatic disease will be enrolled.',
            'budget' => '10000',
            'funded' => 1
        );
        $projects[] = array(
            'title' => 'Assess types of stroma response in fibrogenic myeloid neoplasms',
            'description' => 'Our goal is to assess types of stroma response in fibrogenic myeloid neoplasms, 
                particularly mastocytosis and CIMF. Altered stroma microenvironment is a common 
                feature of many tumors.  There is increasing evidence that these stromal changes, 
                including increased proteases and cytokines, may promote tumor progression.',
            'budget' => '3000',
            'funded' => 1
        );
        return $projects;
    }

    public function approveTrpProjects($client,$projectIds) {
        //$url = $this->baseUrl.'translational-research/projects/';
        //$crawler = $client->request('GET', $url);

        foreach($projectIds as $projectId) {
            echo "projectId=$projectId <br>";
            //Click link: /translational-research/approve-project/3564
            $this->approveTrpProject($client,$projectId);
        }
    }
    public function approveTrpProject( $client, $projectId ) {
        //Click link: /translational-research/approve-project/3564
        $url = $this->baseUrl.'/translational-research/approve-project/'.$projectId;
        $client->request('GET', $url);
        $client->takeScreenshot('demoDb/test_approveTrpProject-'.$projectId.'.png');
    }


    public function getTrpWorkRequests() {
        $requests = array();
        $requests[] = array(
            'serviceId' => '1',
            'quantity' => '3',
            'comment' => 'Request for RNA extraction. For each case below, annotated H&E slide is provided.',
        );
        $requests[] = array(
            'serviceId' => '2',
            'quantity' => '4',
            'comment' => 'Test included in this Panel are:\n\r'.
                'Albumin, Alkaline Phosphatase, Total Bilirubin, Carbon Dioxide (CO2), Aspartate',
        );
        $requests[] = array(
            'serviceId' => '3',
            'quantity' => '5',
            'comment' => 'For case S12-257 A9, already in TRP:\n\r'.
                '1. Please cut 3 additional unstained slides 5-micron.'.
                '2. Please label each slide with Research ID only',
        );
        return $requests;
    }


    public function newFellApps( $client, $users ) {
        //$users = $this->getUsers();
        //$this->newTrpProject($client,$users,$this->baseUrl.'/translational-research/project/select-new-project-type');
        $fellappIds = array();
        foreach( $this->getFellApps() as $fellAppArr ) {
            $fellappId = $this->newFellApp($client,$fellAppArr,$users,$this->baseUrl.'/fellowship-applications/new/');
            if( $fellappId ) {
                $fellappIds[] = $fellappId;
            }
            //break; //testing
        }
        return $fellappIds;
    }
    public function newFellApp( $client, $fellAppArr, $users, $url ) {
        echo "newFellApp: url=$url <br>";
        $crawler = $client->request('GET', $url);

        $client->waitForVisibility('#expandAll');

        //expand all
        $client->executeScript("$('#expandAll').click()");

        $client->waitForVisibility('#oleg_fellappbundle_fellowshipapplication_user_infos_0_firstName');
        $client->takeScreenshot('demoDb/test_newfellapp-'.$fellAppArr['type'].'.png');

        //$form = $crawler->filter('Add Applicant')->form();
        $form = $crawler->selectButton('Add Applicant')->form();

        $client->executeScript("$('#s2id_oleg_fellappbundle_fellowshipapplication_fellowshipSubspecialty').select2('val','1')");

        $date = new \DateTime();
        $year = $date->format('Y');
        $startYear = (int)$year + 1;
        $startDate = '07/01/'.$startYear;
        $endYear = (int)$year + 2;
        $endDate = '07/01/'.$endYear;
        echo "startDate=$startDate, endDate=$endDate <br>";
        $form['oleg_fellappbundle_fellowshipapplication[startDate]'] = $startDate;
        $form['oleg_fellappbundle_fellowshipapplication[startDate]'] = $endDate;
        $client->executeScript('$("#oleg_fellappbundle_fellowshipapplication_user_infos_0_firstName").click()');
        $client->executeScript('$("#oleg_fellappbundle_fellowshipapplication_startDate")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_newfellapp-dates-'.$fellAppArr['type'].'.png');
        exit('fellapp exit');

        //oleg_fellappbundle_fellowshipapplication_user_infos_0_firstName
        $form['oleg_fellappbundle_fellowshipapplication[user][infos][0][firstName]'] = $fellAppArr['firstName'];
        $form['oleg_fellappbundle_fellowshipapplication[user][infos][0][lastName]'] = $fellAppArr['lastName'];
        $form['oleg_fellappbundle_fellowshipapplication[user][infos][0][email]'] = $fellAppArr['email'];

        $form['oleg_fellappbundle_fellowshipapplication[signatureName]'] = $fellAppArr['displayName'];

        $date = new \DateTime();
        $dateStr = $date->format('m/d/Y');
        $form['oleg_fellappbundle_fellowshipapplication[signatureDate]'] = $dateStr;

        $client->takeScreenshot('demoDb/test_newfellapp-before-submit-'.$fellAppArr['type'].'.png');

        $client->submit($form);

        $client->takeScreenshot('demoDb/test_newfellapp-save-'.$fellAppArr['type'].'.png');
    }
    public function getFellApps() {
        $users = array();
        $users[] = array(
            'type' => '1', //'Clinical Informatics',
            'firstName' => 'Joe',
            'lastName' => 'Simpson',
            'displayName' => 'Joe Simpson',
            'email' => 'cinava@yahoo.com',

        );
        $users[] = array(
            'type' => '1', //'Clinical Informatics',
            'firstName' => 'Soleil',
            'lastName' => 'Teresia',
            'displayName' => 'Soleil Teresia',
            'email' => 'cinava@yahoo.com',
        );
        $users[] = array(
            'type' => '1', //'Clinical Informatics',
            'firstName' => 'Haides',
            'lastName' => 'Neon',
            'displayName' => 'Haides Neon',
            'email' => 'cinava@yahoo.com',
        );

        return $users;
    }


    public function newCallLogs( $client, $users ) {
        $callLogIds = array();
        foreach( $this->getCallLogs() as $callLogArr ) {
            $callLogId = $this->newCallLog($client,$callLogArr,$users,$this->baseUrl.'/call-log-book/entry/new');
            if( $callLogId ) {
                $callLogIds[] = $callLogId;
            }
            //break; //testing
        }
        return $callLogIds;
    }
    public function newCallLog( $client, $callLogArr, $users, $url ) {
        echo "newCallLog: url=$url <br>";
        $crawler = $client->request('GET', $url);


        $client->waitForVisibility('oleg_calllogformbundle_messagetype[patient][0][dob][0][field]');
        $client->takeScreenshot('demoDb/test_newcalllog-'.$callLogArr['lastName'].'.png');

        //$form = $crawler->filter('Add Applicant')->form();
        $form = $crawler->selectButton('Find Patient')->form();

        $form['oleg_calllogformbundle_messagetype[patient][0][dob][0][field]'] = $callLogArr['dob'];
        $form['oleg_calllogformbundle_messagetype[patient][0][encounter][0][patfirstname][0][field]'] = $callLogArr['firstName'];
        $form['oleg_calllogformbundle_messagetype[patient][0][encounter][0][patlastname][0][field]'] = $callLogArr['lastName'];

        $client->takeScreenshot('demoDb/test_newcalllog-find-'.$callLogArr['lastName'].'.png');

        $client->submit($form);

        $client->takeScreenshot('demoDb/test_newcalllog-afterfind-'.$callLogArr['lastName'].'.png');
    }
    public function getCallLogs() {
        $users = array();
        $users[] = array(
            'mrntype' => '1',
            'firstName' => 'Andre ',
            'lastName' => 'Castro',
            'dob' => '02/20/1985',
            'history' => 'Splenectomized patient with beta thalassemia major on Luspatercept, 
            transfused every 3 weeks with 1-2 units red cells to maintain pre-transfusion 
            hemoglobin of 9.5-10.5 g/dL. Patient blood type is O+. 
            Unexpected antibodies: anti-I, non-spec, 
            PAN, anti-V and warm autoantibody. Special needs,: E neg, K neg, HbS-'
        );
        $users[] = array(
            'mrntype' => '1',
            'firstName' => 'Callum',
            'lastName' => 'Cruz',
            'dob' => '07/25/1965',
            'history' => 'This patient with a past medical history of myelodysplastic syndrome 
            with excess blasts transformed to acute myeloblastic leukemia (diagnosed in 2021) 
            with relapse in Dec 2022, anemia, coronary artery disease status 
            post circumflex angioplasty in 2016, and hypertension'
        );
        $users[] = array(
            'mrntype' => '1',
            'firstName' => 'Hugo',
            'lastName' => 'Ortiz',
            'dob' => '11/25/1955',
            'history' => 'Paged by BB, work up complete. No abnormal findings. Ok to release further products. SafeTrace updated.'
        );

        return $users;
    }

    //TODO: test it, away person is not set
    public function newVacReqs( $client, $users ) {
        if(0) {
            $vacreqUsers = $this->getVacreqs();
            //1) create group
            $submitter = $vacreqUsers[3];
            $url = $this->baseUrl . '/time-away-request/manage-group/'.$submitter['groupId'];
            $crawler = $client->request('GET', $url);

            $client->waitForVisibility('#s2id_oleg_vacreqbundle_user_participants_users');
            $form = $crawler->selectButton('Add Approver(s)')->form();
            //$approver = $users[]
//        $form = $crawler->selectButton('Add Approver(s)')->form([
//            'oleg_vacreqbundle_user_participants[users][]' => '2',
//        ]);
            $client->executeScript(
                "$('#vacreq-organizational-group-approver').find('#s2id_oleg_vacreqbundle_user_participants_users').select2('val','2')"
            );
            //$client->executeScript("$('#s2id_oleg_vacreqbundle_user_participants_users').select2('val','2')");
            $client->submit($form);
            $client->takeScreenshot('demoDb/test_vacreq-0-group_approver.png');
            //exit('newVacReqs: approver');

            $client->close();
            $client->quit();
            $client = $this->loginAction();
            $crawler = $client->request('GET', $url);
            $submitter = $vacreqUsers[0];
            $form = $crawler->selectButton('Add Submitter(s)')->form();
            $client->executeScript(
                "$('#vacreq-organizational-group-submitter').find('#oleg_vacreqbundle_user_participants_users').select2('val','" . $submitter['userId'] . "')"
            //"$('#vacreq-organizational-group-submitter').find('#oleg_vacreqbundle_user_participants_users').select2('val','12')"
            );
            $submitter = $vacreqUsers[1];
            $client->executeScript(
                "$('#vacreq-organizational-group-submitter').find('#oleg_vacreqbundle_user_participants_users').select2('val','" . $submitter['userId'] . "')"
            //"$('#vacreq-organizational-group-submitter').find('#oleg_vacreqbundle_user_participants_users').select2('val','12')"
            );
            $submitter = $vacreqUsers[2];
            $client->executeScript(
                "$('#vacreq-organizational-group-submitter').find('#oleg_vacreqbundle_user_participants_users').select2('val','" . $submitter['userId'] . "')"
            //"$('#vacreq-organizational-group-submitter').find('#oleg_vacreqbundle_user_participants_users').select2('val','12')"
            );
            //$client->executeScript("$('#s2id_oleg_vacreqbundle_user_participants_users').select2('val','2')");
            $client->submit($form);
            $client->executeScript('$("#vacreq-organizational-group-submitter")[0].scrollIntoView(false);');
            $client->takeScreenshot('demoDb/test_vacreq-0-group-submitter.png');
            //exit('newVacReqs: submitter');
        }
        //$client->executeScript("$('#s2id_oleg_vacreqbundle_user_participants_users').select2('val','2')"); //administrator

        $vacreqIds = array();
        foreach( $this->getVacreqs() as $vacreqArr ) {
            $callLogId = $this->newVacReq($client,$vacreqArr,$users,$this->baseUrl.'/time-away-request/');
            if( $callLogId ) {
                $vacreqIds[] = $callLogId;
            }
            break; //testing
        }
        return $vacreqIds;
    }
    public function newVacReq( $client, $vacreqArr, $users, $url ) {
        echo "newVacReq: url=$url <br>";
        $client->close();
        $client->quit();
        $client = $this->loginAction();
        $crawler = $client->request('GET', $url);

        $client->waitForVisibility('#s2id_oleg_vacreqbundle_request_institution');
        //$client->takeScreenshot('demoDb/test_vacreq-1-'.$vacreqArr['cwid'].'.png');

        //$client->waitForVisibility('#vacreq-request-form');
        //id="vacreq-request-form" name="oleg_vacreqbundle_request"
        //$form = $crawler->filter('Add Applicant')->form();
        //$form = $crawler->filter('oleg_vacreqbundle_request')->form();
        //$form = $crawler->filter('#vacreq-request-form')->form();
        //$form = $crawler->filter('.vacreq-request-form-class')->form();
        //$form = $crawler->selectButton('vacreq-request-form-class')->form();

        echo "newVacReq: groupId=".$vacreqArr['groupId']."<br>";
        $client->executeScript(
            "$('#oleg_vacreqbundle_request_institution').select2('val','".$vacreqArr['groupId']."');"
        );
        $client->takeScreenshot('demoDb/test_vacreq-1-institution-'.$vacreqArr['cwid'].'.png');

        //sleep(10);

        echo "newVacReq: userId=".$vacreqArr['userId']."<br>";

        //TODO: select option for oleg_vacreqbundle_request_user to be populated by setting oleg_vacreqbundle_request_institution
        // CSS and XPath selectors
        //https://github.com/symfony/panther/issues/238
        //https://symfony.com/doc/current/components/dom_crawler.html
        //$("mySelectList option[id='1']").attr("selected", "selected");
        //$client->waitForVisibility("$(#oleg_vacreqbundle_request_user option[id='1']).attr('selected', 'selected')");
        $client->waitForVisibility(".vacreq-person-away");
        //$client->waitFor('.vacreq-person-away', 10); // Wait up to 10 seconds for the element to appe
        //$client->waitFor('[option value="15"]', 10);
        //$client->waitFor('#oleg_vacreqbundle_request_user', 10);
        //css=#myselect option[value=123]
        //$client->waitFor('css=#oleg_vacreqbundle_request_user option[value=15]', 10);

//        $client->executeScript(
//            "$('#s2id_oleg_vacreqbundle_request_user').select2('val','".$vacreqArr['userId']."')"
//        );

        //$client->waitForVisibility("#vacreq-request-form");
        //$form = $crawler->selectButton('Submit')->form();
        //$form = $crawler->filter('#btnCreateVacReq')->form();
        //$form = $crawler->filter('#vacreq-request-form')->form();
        // or by button id (#my-super-button) if the button doesn't have a label
        //$form = $crawler->selectButton('btnCreateVacReq')->form();
        //$form['oleg_vacreqbundle_request[birthday][year]']->select(1984);
        //oleg_vacreqbundle_request[user]
        //$client->waitForVisibility("oleg_vacreqbundle_request[user]");
        //$form['oleg_vacreqbundle_request[user]']->select(12);
        //$form['#oleg_vacreqbundle_request_user']->select(12);
        $client->executeScript(
            "$('#s2id_oleg_vacreqbundle_request_user').select2('val',12);"
        );

        $client->executeScript('$("#s2id_oleg_vacreqbundle_request_user")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_vacreq-2-personaway-'.$vacreqArr['cwid'].'.png');
        //exit('newVacReqs: submitter');

        $todayDate = new \DateTime();
        $startDateStr = $todayDate->format('m/d/Y');
        $todayDate->modify('+3 day');
        $endDateStr = $todayDate->format('m/d/Y');
        echo "startDateStr=$startDateStr, endDateStr=$endDateStr <br>";
        //$(".fellapp-startDate").datepicker().datepicker("setDate", new Date(now.getFullYear() + 2, 6, 1));
        //$form['#oleg_vacreqbundle_request_requestVacation_startDate'] = $startDateStr;
        //$form['#oleg_vacreqbundle_request_requestVacation_endDate'] = $endDateStr;

        $startDateStr = 'new Date()';
        $endDateStr = 'new Date()';
        //$client->executeScript(
        //    "$('#oleg_vacreqbundle_request_requestVacation_startDate').datepicker().datepicker('setDate'," . $startDateStr . ")"
        //);

        $client->executeScript('$("#s2id_oleg_vacreqbundle_request_user")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_vacreq-2-111-personaway-'.$vacreqArr['cwid'].'.png');

        //$client->executeScript(
        //    "$('#oleg_vacreqbundle_request_requestVacation_endDate').datepicker().datepicker('setDate','" . $endDateStr . "')"
        //);

//        $client->executeScript(
//            "$('#oleg_vacreqbundle_request_institution').select2('val','".$vacreqArr['groupId']."')"
//        );
//        $client->executeScript(
//            "$('#s2id_oleg_vacreqbundle_request_user').select2('val',12)"
//        );

        $client->executeScript('$("#s2id_oleg_vacreqbundle_request_user")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_vacreq-2-222-personaway-'.$vacreqArr['cwid'].'.png');

        $client->executeScript('$("#oleg_vacreqbundle_request_requestVacation_numberOfDays")[0].scrollIntoView(false);');
        $client->executeScript('$("#oleg_vacreqbundle_request_requestVacation_startDate")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_vacreq-3-afterdate-'.$vacreqArr['cwid'].'.png');

        //If failed, try to set person away again (vacreq-person-away)
//        $client->executeScript(
//            "$('#s2id_oleg_vacreqbundle_request_user').select2('val','12')"
//        );
//        $client->executeScript(
//            "$('.vacreq-person-away option[value=\"12\"]')"
//        );
//        $client->takeScreenshot('demoDb/test_vacreq-2-personaway2-'.$vacreqArr['cwid'].'.png');

        ////
//        $client->executeScript(
//            "$('#s2id_oleg_vacreqbundle_request_user').select2('val','".$vacreqArr['userId']."')"
//        );
//        $client->executeScript('$("#s2id_oleg_vacreqbundle_request_user")[0].scrollIntoView(false);');
//        $client->takeScreenshot('demoDb/test_vacreq-2-personaway-'.$vacreqArr['cwid'].'.png');

        $client->executeScript('$("#s2id_oleg_vacreqbundle_request_user")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_vacreq-beforesubmit-personaway-'.$vacreqArr['cwid'].'.png');

        $client->executeScript('$("#oleg_vacreqbundle_request_requestVacation_startDate")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_vacreq-beforesubmit-dates-'.$vacreqArr['cwid'].'.png');

        //$client->submit($form);
        $client->executeScript('$("#btnCreateVacReq").click();');

        $client->executeScript('$("#oleg_vacreqbundle_request_user")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_vacreq-4-personaway-aftersubmit-'.$vacreqArr['cwid'].'.png');

        $client->executeScript('$("#btnCreateVacReq")[0].scrollIntoView(false);');
        $client->takeScreenshot('demoDb/test_vacreq-4-aftersubmit-'.$vacreqArr['cwid'].'.png');

        $client->executeScript(
            "$('#s2id_oleg_vacreqbundle_request_user').select2('val',12);"
        );
        $client->executeScript(
            "$('#oleg_vacreqbundle_request_requestVacation_startDate').datepicker().datepicker('setDate'," . $startDateStr . ")"
        );
        $client->executeScript(
            "$('#oleg_vacreqbundle_request_requestVacation_endDate').datepicker().datepicker('setDate','" . $endDateStr . "')"
        );

        $client->executeScript('$("#btnCreateVacReq").click();');
        $client->takeScreenshot('demoDb/test_vacreq-4-aftersubmit2-'.$vacreqArr['cwid'].'.png');
    }
    public function getVacreqs() {
        $users = array();
        $users[] = array(
            'groupId' => 29,
            'userId' => 15,
            'cwid' => 'aeinstein'
        );
        $users[] = array(
            'groupId' => 29,
            'userId' => 16,
            'cwid' => 'rrutherford'
        );
        $users[] = array(
            'groupId' => 29,
            'userId' => 12,
            'cwid' => 'johndoe'
        );
        $users[] = array(
            'groupId' => 29,
            'userId' => 2,
            'cwid' => 'administrator'
        );

        return $users;
    }

}


?>
