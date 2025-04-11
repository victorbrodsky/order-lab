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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class DbBackupCommand extends Command {

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
            ->setDescription('Backup DB')
            ->addArgument('backuppath', InputArgument::REQUIRED, 'Backup location with ending / (i.e. /mnt/pathology/view-backup/db-backup/)')
        ;
    }

    //Cron job to create backup DB only using manage_postgres_db.py via dbManagePython.
    //Upload files backup is done by cron using filesbackup.py directly
    // /bin/php bin/console cron:db-backup-command --env=prod /mnt/pathology/view-backup/db-backup/
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $resStr = "N/A";

        $logger = $this->container->get('logger');
        $logger->notice("cron:db-backup-command. before.");

        $backupPath = $input->getArgument('backuppath');
        if( !$backupPath) {
            $userSecUtil = $this->container->get('user_security_utility');
            $backupPath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        }

        if( $backupPath ) {
            $userServiceUtil = $this->container->get('user_service_utility');

            $logger->notice("cron:db-backup-command. start.");

            $resStr = NULL;
            //dbManagePython is a wraper for a python's script order-lab\utils\db-manage\postgres-manage-python\manage_postgres_db.py
            $res = $userServiceUtil->dbManagePython($backupPath, 'backup');
            $resStr = implode(', ', $res);

            if(0) {
                //Create backup of 'Uploaded' folder using command directly 'tar -zcf ...'
                //Alternatively, backup/filesbackup.py can be used
                $res = $userServiceUtil->createBackupUpload($backupPath);
                $resStr = $resStr . "; " . $res;
            }

            //Remove previously created backups: keep only number of backup files (keepnumber)
            $res = $userServiceUtil->removeOldBackupFiles($backupPath);
            $resStr = $resStr . "; " . $res;

            //$logger->notice("cron:db-backup-command. after. resStr=".$resStr);
        } else {
            $logger->notice("cron:db-backup-command. Error: no networkDrivePath.");
        }

        $output->writeln($resStr);

        return Command::SUCCESS;
    }
    
    
    
    
    

} 