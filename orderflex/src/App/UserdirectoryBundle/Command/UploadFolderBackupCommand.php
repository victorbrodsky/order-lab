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

class UploadFolderBackupCommand extends Command {

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
            ->setName('cron:upload-folder-backup-command')
            ->setDescription('Create backup of the upload folder')
            ->addArgument('backuppath', InputArgument::OPTIONAL, 'Backup location with ending / (i.e. /mnt/pathology/view-backup/db-backup/)')
        ;
    }

    // /bin/php bin/console cron:upload-folder-backup-command --env=prod /mnt/pathology/view-backup/upload-backup/
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $resStr = "N/A";

        $logger = $this->container->get('logger');
        $logger->notice("cron:upload-folder-backup-command. before.");

        $backupPath = $input->getArgument('backuppath');
        if( !$backupPath) {
            $userSecUtil = $this->container->get('user_security_utility');
            $backupPath = $userSecUtil->getSiteSettingParameter('networkDrivePath');
        }

        if( $backupPath ) {
            $userServiceUtil = $this->container->get('user_service_utility');

            $logger->notice("cron:upload-folder-backup-command. start.");

            //Create backup of 'Uploaded' folder using command directly 'tar -zcf ...'
            //Alternatively, backup/filesbackup.py can be used
            $resArr = $userServiceUtil->createBackupUpload($backupPath);
            $resStr = $resArr['message'];
            if( $resArr['status'] != 'OK' ) {
                $output->writeln('cron:upload-folder-backup-command. Error: '.$resArr['message']);
                return Command::FAILURE;
            }

            //Remove previously created backups: keep only number of backup files (keepnumber)
            //$res = $userServiceUtil->removeOldBackupFiles($backupPath);
            //$resStr = $resStr . "; " . $res;

            //$logger->notice("cron:db-backup-command. after. resStr=".$resStr);
        } else {
            $logger->notice("cron:upload-folder-backup-command. Error: no networkDrivePath.");
            $output->writeln("cron:upload-folder-backup-command. Error: no networkDrivePath.");
            return Command::FAILURE;
        }

        $output->writeln($resStr);
        return Command::SUCCESS;
    }
    
    
    
    
    

} 