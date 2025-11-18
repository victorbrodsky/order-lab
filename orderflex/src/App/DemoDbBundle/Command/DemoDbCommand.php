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
 * User: DevServer
 * Date: 8/20/15
 * Time: 2:33 PM
 */

namespace App\DemoDbBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\ORM\EntityManagerInterface;

class DemoDbCommand extends Command {

    private $container;
    private $em;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        parent::__construct();

        $this->container = $container;
        $this->em = $em;
    }

    protected function configure() : void 
    {
        $this
            ->setName('cron:demo-db-reset')
            ->setDescription('Reset Demo DB')
            ->addArgument('baseurl', InputArgument::OPTIONAL,
                'Base url of instance where to generate the Demo DB (i.e. https://view.online/c/demo-institution/demo-department/)')
        ;
    }

    //Cron job to back up DB and Uploaded files.
    //php bin/console cron:demo-db-reset --env=prod https://view.online/c/demo-institution/demo-department/
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        //$logger = $this->container->get('logger');
        $resStr = "Demo DB started";

        $logger = $this->container->get('logger');
        $logger->notice("cron demo-db-reset started");

        $userSecUtil = $this->container->get('user_security_utility');
        //$environment = $userSecUtil->getSiteSettingParameter('environment');

        $baseurl = $input->getArgument('baseurl');

        $environment = NULL;

        try {
            echo "DemoDbCommand execute: try: getSiteSettingParameter" . "\n";
            $environment = $userSecUtil->getSiteSettingParameter('environment');
        } catch (\Exception $e) {
            // Handle the exception
            echo "DemoDbCommand execute: Error: " . $e->getMessage() . "\n";
            //exit;
        }

        //$projectRoot = NULL; //use current project dir of the tenant where command is running
        $projectRoot = "/srv/order-lab-tenantappdemo/orderflex"; //use project dir of the demo tenant

        echo "DemoDbCommand execute: environment=$environment, projectRoot=$projectRoot" . "\n";

        if( $environment && $environment == 'live' ) {
            $resStr = "Demo DB can not be run on live environment. environment=$environment, projectRoot=$projectRoot". "\n";
            $logger->notice("cron demo-db-reset: ".$resStr);
            $output->writeln($resStr);
            return Command::FAILURE;
        }

        if( str_contains($projectRoot, '/order-lab-tenantappdemo/') === false ) {
            $resStr = "Demo DB can be run only in demo tenant. projectRoot=$projectRoot". "\n";
            $logger->notice("cron demo-db-reset: ".$resStr);
            $output->writeln($resStr);
            return Command::FAILURE;
        }

        $demoDbUtil = $this->container->get('demodb_utility');
        $resDemoDbStr = $demoDbUtil->processDemoDb($projectRoot,$backupPath=NULL,$baseurl);
        
//        $client = $demoDbUtil->loginAction();
//        $client->takeScreenshot('test_login.png');
//
//        $users = $demoDbUtil->getUsers(); //testing
//        $vacreqIds = $demoDbUtil->newVacReqs($client, $users);

        $resStr = $resStr . "; " . 'resDemoDbStr=' . $resDemoDbStr . "; " . "Demo DB completed successfully";
        $logger->notice("cron demo-db-reset finished: ".$resStr);

        //Verify DB
        if(1) {
            $userSecUtil = $this->container->get('user_security_utility');
            $toEmailsArr = array('oli2002@med.cornell.edu');
            $verifyErrorRes = $demoDbUtil->verifyDemoDb();
            if ($verifyErrorRes) {
                $verifyMsg = "demo-db-reset: Not all demos were generated for baseurl=$baseurl: " . $verifyErrorRes;
                $logger->error($verifyMsg);
                $userSecUtil->sendEmailToSystemEmail("DB Demos: Error for baseurl=$baseurl", $verifyMsg, $toEmailsArr);
            } else {
                $msg = "demo-db-reset: Verify DB baseurl=$baseurl: ".$resStr;
                $userSecUtil->sendEmailToSystemEmail("DB Demos: Completed Successfully for baseurl=$baseurl", $msg, $toEmailsArr);
            }
        }

        $resVerifyStr = $output->writeln($resStr);
        $logger->notice("cron demo-db-reset Verify DB for baseurl=$baseurl: ".$resVerifyStr);

        return Command::SUCCESS;
    }
    
    
    
    //Selenium scraper need google chrome driver to be installed:
    // wget https://dl.google.com/linux/direct/google-chrome-stable_current_x86_64.rpm
    // sudo dnf install ./google-chrome-stable_current_x86_64.rpm
    // google-chrome --version
} 