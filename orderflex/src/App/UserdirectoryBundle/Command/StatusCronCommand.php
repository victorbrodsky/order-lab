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

namespace App\UserdirectoryBundle\Command;


//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class StatusCronCommand extends Command {

    //protected static $defaultName = 'cron:status';
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
            //->setName('console.command')
            //->setCommand('cron:status')
            ->setDescription('Cron job to check status');
    }

    
    //php bin/console cron:status --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        //Testing dbManagePython
        if(0) {
            $logger = $this->container->get('logger');
            $logger->notice("cron:status. execute.");
            $userServiceUtil = $this->container->get('user_service_utility');
            $userSecUtil = $this->container->get('user_security_utility');
            $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
            $resDbBackup = $userServiceUtil->dbManagePython($networkDrivePath, 'backup');
            $resDbBackupStr = implode(', ', $resDbBackup);
            $resUploadFolderBackup = $userServiceUtil->createBackupUpload();
            $resUploadFolderBackupStr = implode(', ', $resUploadFolderBackup);
            $resStr = $resDbBackupStr . "; " . $resUploadFolderBackupStr;
            $logger->notice("cron:status. after. resStr=" . $resStr);
        }

        $userServiceUtil = $this->container->get('user_service_utility');
        $res = $userServiceUtil->checkStatus();
        $output->writeln($res);
        
        //return true;
        return Command::SUCCESS;
    }

} 