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

class UploadFolderRestoreCommand extends Command {

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
            ->setName('cron:upload-folder-restore-command')
            ->setDescription('Create restore of the upload folder')
            ->addArgument('backupfilename', InputArgument::REQUIRED, 'Backup file to restore')
        ;
    }

    // /bin/php bin/console cron:upload-folder-restore-command --env=prod /mnt/pathology/view-backup/upload-backup/
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $resStr = "N/A";

        $logger = $this->container->get('logger');
        $logger->notice("cron:upload-folder-restore-command. before.");

        $backupfilename = $input->getArgument('backupfilename');

        if( $backupfilename ) {
            $userServiceUtil = $this->container->get('user_service_utility');

            $logger->notice("cron:upload-folder-restore-command. start.");

            $res = $userServiceUtil->restoreBackupUpload($backupfilename);
            $resStr = $res;

            $logger->notice("cron:upload-folder-restore-command. after. resStr=".$resStr);
        } else {
            $logger->notice("cron:upload-folder-restore-command. Error: no networkDrivePath.");
            $output->writeln("cron:upload-folder-restore-command. Error: no networkDrivePath.");
            return Command::FAILURE;
        }

        $output->writeln($resStr);
        return Command::SUCCESS;
    }
    
    
    
    
    

} 