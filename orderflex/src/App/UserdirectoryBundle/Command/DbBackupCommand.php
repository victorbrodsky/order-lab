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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class DbBackupCommand extends Command {

    //protected static $defaultName = 'cron:db-backup-command';
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
            ->setName('cron:db-backup-command')
            ->setDescription('Backup DB');
    }


    //TODO: 315(F): F- Implement a view.med.cornell.edu cron job that reaches out to view.online
    //TODO: and picks up/downloads the back up files every 12 hours from the back up destination folder
    //Cron job to back up DB.
    // /bin/php bin/console cron:db-backup-command --env=prod
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $resStr = "N/A";

        $logger = $this->container->get('logger');
        $logger->notice("cron:db-backup-command. before.");

        $userSecUtil = $this->container->get('user_security_utility');
        $networkDrivePath = $userSecUtil->getSiteSettingParameter('networkDrivePath');

        if( $networkDrivePath ) {
            $userServiceUtil = $this->container->get('user_service_utility');

            $logger->notice("cron:db-backup-command. start.");

            $resStr = NULL;
            //$res = $userServiceUtil->dbManagePython($networkDrivePath, 'backup');
            //$resStr = implode(', ', $res);

            //$res = $userServiceUtil->createBackupUpload();
            //$resStr = $resStr . "; " . $res;

            $logger->notice("cron:db-backup-command. after. resStr=".$resStr);
        } else {
            $logger->notice("cron:db-backup-command. Error: no networkDrivePath.");
        }

        $output->writeln($resStr);

        return Command::SUCCESS;
    }
    
    
    
    
    

} 